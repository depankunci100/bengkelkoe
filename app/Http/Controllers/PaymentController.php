<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\WoTimeline;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['invoice.customer', 'invoice.workOrder', 'cashier'])
            ->latest()
            ->paginate(10);

        return view('payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        $selectedInvoiceId = $request->query('invoice_id');
        $invoice = null;

        if ($selectedInvoiceId) {
            $invoice = Invoice::with(['customer', 'workOrder.vehicle', 'payments'])->find($selectedInvoiceId);
        }

        $unpaidInvoices = Invoice::with(['customer', 'workOrder.vehicle'])
            ->whereIn('payment_status', ['UNPAID', 'PARTIAL'])
            ->latest()
            ->get();

        return view('payments.create', compact('invoice', 'unpaidInvoices', 'selectedInvoiceId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:CASH,BANK_TRANSFER,QRIS,DEBIT_CREDIT',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        $paymentNumber = Payment::generatePaymentNumber();

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'payment_number' => $paymentNumber,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'cashier_id' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
            'paid_at' => Carbon::now(),
        ]);

        // Hitung ulang status pembayaran invoice
        $invoice->recalculatePaymentStatus();

        // Jika invoice lunas, otomatis update status Work Order menjadi COMPLETED
        if ($invoice->payment_status === 'PAID') {
            $wo = $invoice->workOrder;
            if ($wo && $wo->status === 'READY_FOR_PICKUP') {
                $wo->update([
                    'status' => 'COMPLETED',
                    'completed_at' => Carbon::now(),
                ]);

                WoTimeline::create([
                    'work_order_id' => $wo->id,
                    'user_id' => auth()->id(),
                    'title' => 'Pembayaran Lunas & Unit Selesai',
                    'description' => "Invoice {$invoice->invoice_number} telah dibayar lunas via kasir. Unit resmi diserahterimakan.",
                    'status' => 'COMPLETED',
                ]);
            }
        }

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', "Pembayaran sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " berhasil diproses!");
    }
}
