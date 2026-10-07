<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Inspection;
use App\Models\InspectionItem;
use App\Models\Invoice;
use App\Models\Part;
use App\Models\Service;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierSales;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WhatsappLog;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Models\WoTimeline;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'ALL');
        $search = $request->query('search');
        $supplierId = $request->query('supplier_id');
        $salesId = $request->query('supplier_sales_id');

        $query = WorkOrder::with(['customer', 'vehicle', 'technician'])->latest();

        if ($status !== 'ALL') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('wo_number', 'like', "%{$search}%")
                    ->orWhere('complaint', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('vehicle', function ($v) use ($search) {
                        $v->where('plate_number', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    });
            });
        }

        $workOrders = $query->paginate(10)->withQueryString();

        // Hitung count per status untuk tab
        $counts = [
            'ALL' => WorkOrder::count(),
            'DRAFT' => WorkOrder::where('status', 'DRAFT')->count(),
            'INSPECTION' => WorkOrder::where('status', 'INSPECTION')->count(),
            'WAITING_APPROVAL' => WorkOrder::where('status', 'WAITING_APPROVAL')->count(),
            'IN_PROGRESS' => WorkOrder::where('status', 'IN_PROGRESS')->count(),
            'QC' => WorkOrder::where('status', 'QC')->count(),
            'READY_FOR_PICKUP' => WorkOrder::where('status', 'READY_FOR_PICKUP')->count(),
            'COMPLETED' => WorkOrder::where('status', 'COMPLETED')->count(),
        ];

        return view('work-orders.index', compact('workOrders', 'status', 'search', 'counts'));
    }

    public function create(Request $request)
    {
        $customers = Customer::with('vehicles')->orderBy('name')->get();
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $selectedCustomerId = $request->query('customer_id');
        $selectedVehicleId = $request->query('vehicle_id');

        return view('work-orders.create', compact('customers', 'technicians', 'selectedCustomerId', 'selectedVehicleId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'technician_id' => 'nullable|exists:users,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_sales_id' => 'nullable|exists:supplier_sales,id',
            'complaint' => 'required|string',
            'odometer_in' => 'nullable|integer',
        ]);

        $woNumber = WorkOrder::generateWoNumber();
        $initialStatus = $request->filled('technician_id') ? 'INSPECTION' : 'DRAFT';

        $wo = WorkOrder::create([
            'wo_number' => $woNumber,
            'customer_id' => $validated['customer_id'],
            'vehicle_id' => $validated['vehicle_id'],
            'technician_id' => $validated['technician_id'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'supplier_sales_id' => $validated['supplier_sales_id'] ?? null,
            'created_by' => auth()->id(),
            'status' => $initialStatus,
            'complaint' => $validated['complaint'],
            'odometer_in' => $validated['odometer_in'] ?? null,
            'approval_token' => WorkOrder::generateApprovalToken(),
        ]);

        // Update odometer kendaraan
        if ($request->filled('odometer_in')) {
            Vehicle::where('id', $validated['vehicle_id'])->update([
                'odometer' => $validated['odometer_in']
            ]);
        }

        // Catat Timeline Audit
        $salesDesc = "";
        if ($wo->supplier_sales_id && $wo->supplierSales) {
            $salesDesc = " [Sales Pemasok: {$wo->supplierSales->name} ({$wo->supplier->name})]";
        }

        WoTimeline::create([
            'work_order_id' => $wo->id,
            'user_id' => auth()->id(),
            'title' => 'WO Dibuat',
            'description' => 'Work order didaftarkan ke sistem dengan keluhan: ' . $wo->complaint . $salesDesc,
            'status' => 'DRAFT',
        ]);

        if ($wo->technician_id) {
            $tech = User::find($wo->technician_id);
            WoTimeline::create([
                'work_order_id' => $wo->id,
                'user_id' => auth()->id(),
                'title' => 'Teknisi Ditugaskan',
                'description' => "Ditugaskan ke {$tech->name} ({$tech->specialization}) untuk inspeksi fisik",
                'status' => 'INSPECTION',
            ]);
        }

        // Buat Template Inspeksi awal otomatis
        $inspection = Inspection::create([
            'work_order_id' => $wo->id,
            'vehicle_id' => $wo->vehicle_id,
            'technician_id' => $wo->technician_id,
            'status' => 'IN_PROGRESS',
        ]);

        // Default item checklist standar
        $defaultChecks = [
            ['ENGINE', 'Oli Mesin'],
            ['ENGINE', 'Air Radiator & Reservoir'],
            ['ENGINE', 'Filter Udara'],
            ['BRAKE', 'Kampas Rem Depan'],
            ['BRAKE', 'Kampas Rem Belakang'],
            ['ELECTRICAL', 'Aki / Battery (Voltase & Fisik)'],
            ['ELECTRICAL', 'Lampu Utama, Sein & Rem'],
            ['SUSPENSION', 'Shockbreaker & Kaki-kaki'],
        ];

        foreach ($defaultChecks as $chk) {
            InspectionItem::create([
                'inspection_id' => $inspection->id,
                'category' => $chk[0],
                'item_name' => $chk[1],
                'condition' => 'GOOD',
            ]);
        }

        return redirect()->route('work-orders.show', $wo->id)
            ->with('success', "Work Order {$wo->wo_number} berhasil dibuat!");
    }

    public function show($id)
    {
        $wo = WorkOrder::with([
            'customer',
            'vehicle',
            'technician',
            'items' => function ($q) {
                $q->with(['service', 'part']);
            },
            'inspection.items',
            'invoice.payments',
            'timelines.user',
            'whatsappLogs',
        ])->findOrFail($id);

        $services = Service::where('is_active', true)->orderBy('name')->get();
        $parts = Part::where('is_active', true)->orderBy('name')->get();
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();

        return view('work-orders.show', compact('wo', 'services', 'parts', 'technicians'));
    }

    public function assignSales(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_sales_id' => 'nullable|exists:supplier_sales,id',
        ]);

        $workOrder->update($validated);
        $workOrder->load(['supplier', 'supplierSales']);

        $salesInfo = $workOrder->supplierSales ? "{$workOrder->supplierSales->name} ({$workOrder->supplier?->name})" : "Tidak ada";

        WoTimeline::create([
            'work_order_id' => $workOrder->id,
            'user_id' => auth()->id(),
            'title' => 'Sales Pemasok Diperbarui',
            'description' => "Penanggung jawab order pemasok diubah menjadi: {$salesInfo}",
            'status' => $workOrder->status,
        ]);

        return back()->with('success', "Sales pemasok Work Order berhasil diperbarui ke: {$salesInfo}");
    }

    public function addItem(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'type' => 'required|in:SERVICE,PART',
            'quantity' => 'required|numeric|min:1',
        ]);

        if ($request->type === 'SERVICE') {
            $request->validate(['service_id' => 'required|exists:services,id']);
            $service = Service::findOrFail($request->service_id);
            $itemName = $service->name;
            $unitPrice = $service->price;
        } else {
            $request->validate(['part_id' => 'required|exists:parts,id']);
            $part = Part::findOrFail($request->part_id);
            $itemName = $part->name;
            $unitPrice = $part->selling_price;
        }

        $subtotal = $unitPrice * $request->quantity;

        WorkOrderItem::create([
            'work_order_id' => $workOrder->id,
            'type' => $request->type,
            'service_id' => $request->service_id ?? null,
            'part_id' => $request->part_id ?? null,
            'item_name' => $itemName,
            'quantity' => $request->quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'approval_status' => 'PENDING',
            'is_additional' => false,
        ]);

        $workOrder->recalculateTotals();

        return back()->with('success', "Item {$itemName} berhasil ditambahkan ke estimasi WO.");
    }

    public function addAdditionalItem(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'type' => 'required|in:SERVICE,PART',
            'item_name' => 'required|string',
            'quantity' => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|min:0',
            'reason' => 'required|string',
        ]);

        $subtotal = $request->unit_price * $request->quantity;

        WorkOrderItem::create([
            'work_order_id' => $workOrder->id,
            'type' => $request->type,
            'item_name' => $request->item_name,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'subtotal' => $subtotal,
            'approval_status' => 'PENDING',
            'is_additional' => true,
            'notes' => 'Alasan: ' . $request->reason,
        ]);

        $workOrder->recalculateTotals();

        WoTimeline::create([
            'work_order_id' => $workOrder->id,
            'user_id' => auth()->id(),
            'title' => 'Pekerjaan Tambahan Diajukan',
            'description' => "Item tambahan: {$request->item_name} (Rp " . number_format($subtotal, 0, ',', '.') . "). Alasan: {$request->reason}",
            'status' => $workOrder->status,
        ]);

        return back()->with('success', "Pekerjaan tambahan '{$request->item_name}' berhasil diajukan untuk konfirmasi pelanggan.");
    }

    public function removeItem(WorkOrder $workOrder, WorkOrderItem $item)
    {
        $itemName = $item->item_name;
        $item->delete();
        $workOrder->recalculateTotals();

        return back()->with('success', "Item {$itemName} berhasil dihapus dari daftar estimasi.");
    }

    public function assignTechnician(Request $request, WorkOrder $workOrder)
    {
        $request->validate(['technician_id' => 'required|exists:users,id']);
        $tech = User::findOrFail($request->technician_id);

        $workOrder->update(['technician_id' => $tech->id]);

        // Update di inspeksi juga
        if ($workOrder->inspection) {
            $workOrder->inspection->update(['technician_id' => $tech->id]);
        }

        WoTimeline::create([
            'work_order_id' => $workOrder->id,
            'user_id' => auth()->id(),
            'title' => 'Teknisi Ditugaskan',
            'description' => "Unit dialihkan ke penanganan teknisi: {$tech->name}",
            'status' => $workOrder->status,
        ]);

        return back()->with('success', "Teknisi berhasil ditugaskan ke {$tech->name}.");
    }

    public function updateStatus(Request $request, WorkOrder $workOrder)
    {
        $request->validate(['status' => 'required|string']);
        $newStatus = $request->status;
        $notes = $request->notes;

        $desc = match ($newStatus) {
            'INSPECTION' => 'Inspeksi & diagnosa awal dimulai oleh teknisi.',
            'WAITING_APPROVAL' => 'Estimasi biaya telah disiapkan dan dikirimkan ke pelanggan.',
            'APPROVED' => 'Persetujuan pengerjaan servis telah diterima dari pelanggan.',
            'IN_PROGRESS' => 'Teknisi mulai melakukan pengerjaan, servis, dan penggantian sparepart.',
            'QC' => 'Pengerjaan teknis selesai, masuk tahap Quality Control & pengujian.',
            'READY_FOR_PICKUP' => 'Kendaraan telah lulus QC, siap diambil & kasir dapat menerbitkan invoice.',
            'COMPLETED' => 'Kendaraan telah diserahkan kembali ke customer dengan pembayaran lunas.',
            'REJECTED' => 'Pelanggan menolak estimasi perbaikan yang diajukan.',
            'CANCELLED' => 'Work order resmi dibatalkan.',
            default => 'Status work order diperbarui menjadi ' . $newStatus,
        };

        if ($notes) {
            $desc .= ' Catatan: ' . $notes;
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'IN_PROGRESS' && !$workOrder->started_at) {
            $updateData['started_at'] = Carbon::now();

            // Potong stok sparepart saat pengerjaan dimulai
            foreach ($workOrder->items()->where('type', 'PART')->get() as $item) {
                if ($item->part_id) {
                    $part = Part::find($item->part_id);
                    if ($part) {
                        $before = $part->stock;
                        $part->decrement('stock', (int) $item->quantity);
                        StockMovement::create([
                            'part_id' => $part->id,
                            'work_order_id' => $workOrder->id,
                            'type' => 'OUT',
                            'quantity' => $item->quantity,
                            'before_stock' => $before,
                            'after_stock' => $part->fresh()->stock,
                            'notes' => 'Penggunaan WO: ' . $workOrder->wo_number,
                            'user_id' => auth()->id(),
                        ]);
                    }
                }
            }
        }

        if ($newStatus === 'READY_FOR_PICKUP') {
            // Auto-generate invoice jika belum ada
            if (!$workOrder->invoice) {
                $invNumber = Invoice::generateInvoiceNumber();
                Invoice::create([
                    'invoice_number' => $invNumber,
                    'work_order_id' => $workOrder->id,
                    'customer_id' => $workOrder->customer_id,
                    'subtotal' => $workOrder->subtotal,
                    'discount_type' => $workOrder->discount_type ?? 'FIXED',
                    'discount_percent' => $workOrder->discount_percent ?? 0,
                    'discount' => $workOrder->discount ?? 0,
                    'discount_reason' => $workOrder->discount_reason ?? null,
                    'tax' => $workOrder->tax,
                    'grand_total' => $workOrder->grand_total,
                    'amount_paid' => 0,
                    'balance_due' => $workOrder->grand_total,
                    'payment_status' => 'UNPAID',
                    'cashier_id' => auth()->id(),
                    'issued_at' => Carbon::now(),
                ]);
            }
        }

        if ($newStatus === 'COMPLETED' && !$workOrder->completed_at) {
            $updateData['completed_at'] = Carbon::now();
        }

        $workOrder->update($updateData);

        WoTimeline::create([
            'work_order_id' => $workOrder->id,
            'user_id' => auth()->id(),
            'title' => match ($newStatus) {
                'INSPECTION' => 'Inspeksi Dimulai',
                'WAITING_APPROVAL' => 'Menunggu Approval',
                'APPROVED' => 'Disetujui Pelanggan',
                'IN_PROGRESS' => 'Pengerjaan Dimulai',
                'QC' => 'Quality Control (QC)',
                'READY_FOR_PICKUP' => 'Siap Diambil',
                'COMPLETED' => 'Selesai & Diserahkan',
                'REJECTED' => 'Estimasi Ditolak',
                'CANCELLED' => 'WO Dibatalkan',
                default => 'Update Status',
            },
            'description' => $desc,
            'status' => $newStatus,
        ]);

        return back()->with('success', "Status Work Order {$workOrder->wo_number} berhasil diubah ke {$newStatus}!");
    }

    public function sendWhatsappApproval(Request $request, WorkOrder $workOrder)
    {
        $request->validate(['message' => 'required|string']);

        if (!$workOrder->approval_token) {
            $workOrder->update(['approval_token' => WorkOrder::generateApprovalToken()]);
        }

        $workOrder->update([
            'status' => 'WAITING_APPROVAL',
            'approval_status' => 'PENDING',
            'approval_sent_at' => Carbon::now(),
            'approval_expires_at' => Carbon::now()->addHours(24),
        ]);

        // Catat log WhatsApp
        WhatsappLog::create([
            'work_order_id' => $workOrder->id,
            'customer_name' => $workOrder->customer->name,
            'phone' => $workOrder->customer->phone,
            'message' => $request->message,
            'status' => 'SENT',
        ]);

        // Catat Timeline Audit
        WoTimeline::create([
            'work_order_id' => $workOrder->id,
            'user_id' => auth()->id(),
            'title' => 'Approval Dikirim',
            'description' => "Link approval estimasi dikirimkan ke WhatsApp customer ({$workOrder->customer->phone})",
            'status' => 'WAITING_APPROVAL',
        ]);

        return back()->with('success', 'Estimasi dan link persetujuan berhasil dikirim ke WhatsApp customer!');
    }
}
