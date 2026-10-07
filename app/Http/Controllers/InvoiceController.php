<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\WorkOrder;
use App\Models\WoTimeline;
use App\Models\WorkshopSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'ALL');
        $search = $request->query('search');

        $query = Invoice::with(['customer', 'workOrder.vehicle', 'cashier'])->latest();

        if ($status !== 'ALL') {
            $query->where('payment_status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('workOrder.vehicle', function ($v) use ($search) {
                        $v->where('plate_number', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->paginate(10)->withQueryString();

        return view('invoices.index', compact('invoices', 'status', 'search'));
    }

    public function create(Request $request)
    {
        $selectedWoId = $request->query('work_order_id');
        $selectedWorkOrder = null;

        if ($selectedWoId) {
            $selectedWorkOrder = WorkOrder::with([
                'customer',
                'vehicle',
                'items.service',
                'items.part',
                'invoice',
            ])->find($selectedWoId);
        }

        // Ambil daftar Work Order yang belum memiliki invoice atau sedang dipilih
        $workOrders = WorkOrder::with(['customer', 'vehicle', 'invoice'])
            ->where(function ($q) use ($selectedWoId) {
                $q->doesntHave('invoice');
                if ($selectedWoId) {
                    $q->orWhere('id', $selectedWoId);
                }
            })
            ->whereIn('status', ['APPROVED', 'IN_PROGRESS', 'QC', 'READY_FOR_PICKUP', 'COMPLETED'])
            ->latest()
            ->get();

        return view('invoices.create', compact('workOrders', 'selectedWorkOrder', 'selectedWoId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'discount_type' => 'required|in:FIXED,PERCENT',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
            'include_tax' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $wo = WorkOrder::with(['customer', 'items', 'invoice'])->findOrFail($validated['work_order_id']);

        // Jika sudah ada invoice untuk WO ini, redirect ke invoice tersebut
        if ($wo->invoice) {
            return redirect()->route('invoices.show', $wo->invoice->id)
                ->with('info', "Work Order {$wo->wo_number} sudah memiliki invoice {$wo->invoice->invoice_number}.");
        }

        $subtotal = (float) $wo->subtotal;
        if ($subtotal <= 0) {
            $wo->recalculateTotals();
            $subtotal = (float) $wo->subtotal;
        }

        $discountType = $validated['discount_type'];
        $discountValue = (float) ($validated['discount_value'] ?? 0);
        $discountReason = $validated['discount_reason'] ?? null;
        $includeTax = $request->boolean('include_tax', true);

        if ($discountType === 'PERCENT') {
            $discountPercent = min(100, max(0, $discountValue));
            $discountAmount = round(($subtotal * $discountPercent) / 100, 2);
        } else {
            $discountAmount = min($subtotal, max(0, $discountValue));
            $discountPercent = $subtotal > 0 ? round(($discountAmount / $subtotal) * 100, 2) : 0;
        }

        $taxableBase = max(0, $subtotal - $discountAmount);
        $tax = $includeTax ? round($taxableBase * 0.11, 2) : 0;
        $grandTotal = $taxableBase + $tax;

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'work_order_id' => $wo->id,
            'customer_id' => $wo->customer_id,
            'subtotal' => $subtotal,
            'discount_type' => $discountType,
            'discount_percent' => $discountPercent,
            'discount' => $discountAmount,
            'discount_reason' => $discountReason,
            'tax' => $tax,
            'grand_total' => $grandTotal,
            'amount_paid' => 0,
            'balance_due' => $grandTotal,
            'payment_status' => 'UNPAID',
            'cashier_id' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
            'issued_at' => Carbon::now(),
        ]);

        // Sinkronisasi data diskon ke WorkOrder
        $wo->update([
            'discount_type' => $discountType,
            'discount_percent' => $discountPercent,
            'discount' => $discountAmount,
            'discount_reason' => $discountReason,
            'tax' => $tax,
            'grand_total' => $grandTotal,
            'status' => in_array($wo->status, ['COMPLETED', 'READY_FOR_PICKUP']) ? $wo->status : 'READY_FOR_PICKUP',
        ]);

        // Catat di timeline WO
        $discDesc = $discountAmount > 0 
            ? " dengan diskon " . ($discountType === 'PERCENT' ? "{$discountPercent}%" : "Rp " . number_format($discountAmount, 0, ',', '.')) . ($discountReason ? " ({$discountReason})" : "")
            : "";

        WoTimeline::create([
            'work_order_id' => $wo->id,
            'user_id' => auth()->id(),
            'title' => 'Invoice Diterbitkan',
            'description' => "Faktur invoice {$invoice->invoice_number} berhasil diterbitkan senilai Rp " . number_format($grandTotal, 0, ',', '.') . $discDesc,
            'status' => $wo->status,
        ]);

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', "Invoice {$invoice->invoice_number} berhasil diterbitkan dengan total Rp " . number_format($grandTotal, 0, ',', '.') . "!");
    }

    public function show($id)
    {
        $invoice = Invoice::with([
            'customer',
            'workOrder.vehicle',
            'workOrder.items.service',
            'workOrder.items.part',
            'payments.cashier',
            'cashier'
        ])->findOrFail($id);

        return view('invoices.show', compact('invoice'));
    }

    public function updateDiscount(Request $request, $id)
    {
        $invoice = Invoice::with(['workOrder', 'payments'])->findOrFail($id);

        if ($invoice->payment_status === 'PAID') {
            return back()->with('error', 'Diskon tidak dapat diubah karena invoice sudah dibayar lunas.');
        }

        $validated = $request->validate([
            'discount_type' => 'required|in:FIXED,PERCENT',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
            'include_tax' => 'nullable|boolean',
        ]);

        $subtotal = (float) $invoice->subtotal;
        $discountType = $validated['discount_type'];
        $discountValue = (float) ($validated['discount_value'] ?? 0);
        $discountReason = $validated['discount_reason'] ?? null;
        $includeTax = $request->boolean('include_tax', (float)$invoice->tax > 0);

        if ($discountType === 'PERCENT') {
            $discountPercent = min(100, max(0, $discountValue));
            $discountAmount = round(($subtotal * $discountPercent) / 100, 2);
        } else {
            $discountAmount = min($subtotal, max(0, $discountValue));
            $discountPercent = $subtotal > 0 ? round(($discountAmount / $subtotal) * 100, 2) : 0;
        }

        $taxableBase = max(0, $subtotal - $discountAmount);
        $tax = $includeTax ? round($taxableBase * 0.11, 2) : 0;
        $grandTotal = $taxableBase + $tax;

        if ($grandTotal < (float) $invoice->amount_paid) {
            return back()->with('error', 'Grand total baru (Rp ' . number_format($grandTotal, 0, ',', '.') . ') tidak boleh lebih kecil dari pembayaran yang sudah diterima (Rp ' . number_format($invoice->amount_paid, 0, ',', '.') . ').');
        }

        $balanceDue = max(0, $grandTotal - (float) $invoice->amount_paid);
        $paymentStatus = $balanceDue <= 0 && (float)$invoice->amount_paid > 0 ? 'PAID' : ((float)$invoice->amount_paid > 0 ? 'PARTIAL' : 'UNPAID');

        $invoice->update([
            'discount_type' => $discountType,
            'discount_percent' => $discountPercent,
            'discount' => $discountAmount,
            'discount_reason' => $discountReason,
            'tax' => $tax,
            'grand_total' => $grandTotal,
            'balance_due' => $balanceDue,
            'payment_status' => $paymentStatus,
        ]);

        if ($invoice->workOrder) {
            $invoice->workOrder->update([
                'discount_type' => $discountType,
                'discount_percent' => $discountPercent,
                'discount' => $discountAmount,
                'discount_reason' => $discountReason,
                'tax' => $tax,
                'grand_total' => $grandTotal,
            ]);

            WoTimeline::create([
                'work_order_id' => $invoice->workOrder->id,
                'user_id' => auth()->id(),
                'title' => 'Diskon Invoice Disesuaikan',
                'description' => "Diskon disesuaikan menjadi " . ($discountType === 'PERCENT' ? "{$discountPercent}%" : "Rp " . number_format($discountAmount, 0, ',', '.')) . " (" . ($discountReason ?? 'Penyesuaian kasir') . "). Grand total baru: Rp " . number_format($grandTotal, 0, ',', '.'),
                'status' => $invoice->workOrder->status,
            ]);
        }

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Diskon invoice berhasil diperbarui!');
    }

    public function print($id)
    {
        $invoice = Invoice::with([
            'customer',
            'workOrder.vehicle',
            'workOrder.items.service',
            'workOrder.items.part',
            'payments.cashier',
            'cashier'
        ])->findOrFail($id);

        $settings = [
            'name' => WorkshopSetting::get('workshop_name', 'SIM BENGKEL AUTO SERVICE'),
            'address' => WorkshopSetting::get('workshop_address', 'Jl. Raya Otomotif No. 88, Surabaya'),
            'phone' => WorkshopSetting::get('workshop_phone', '031-8976543'),
            'email' => WorkshopSetting::get('workshop_email', 'info@simbengkel.com'),
            'footer' => WorkshopSetting::get('invoice_footer', 'Terima kasih atas kunjungan Anda. Garansi servis 14 hari atau 1.000 KM.'),
        ];

        return view('invoices.print', compact('invoice', 'settings'));
    }
}
