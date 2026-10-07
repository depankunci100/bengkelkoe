<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Models\WoTimeline;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    /**
     * Tampilan Utama Scanner Hub
     */
    public function index(Request $request)
    {
        $mode = $request->get('mode', 'opname'); // 'opname', 'stock_in', 'dispatch'
        $selectedWoId = $request->get('wo_id');

        // Daftar Work Order yang memiliki part atau sedang dalam proses
        $activeWorkOrders = WorkOrder::with(['customer', 'vehicle', 'technician', 'items.part'])
            ->whereIn('status', ['IN_PROGRESS', 'WAITING_APPROVAL', 'QC', 'READY_FOR_PICKUP'])
            ->orderBy('id', 'desc')
            ->take(30)
            ->get();

        $selectedWorkOrder = null;
        if ($selectedWoId) {
            $selectedWorkOrder = WorkOrder::with(['customer', 'vehicle', 'technician', 'items.part.partCategory', 'partsVerifier'])
                ->find($selectedWoId);
        }

        $suppliers = Supplier::with(['sales' => fn($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Riwayat mutasi terbaru untuk scan log
        $recentMovements = StockMovement::with(['part', 'user', 'supplierRelation', 'supplierSales'])
            ->latest('id')
            ->take(10)
            ->get();

        return view('scanner.index', compact(
            'mode',
            'activeWorkOrders',
            'selectedWorkOrder',
            'suppliers',
            'recentMovements'
        ));
    }

    /**
     * Cari Part berdasarkan Barcode / Part Number (AJAX)
     */
    public function lookup(Request $request): JsonResponse
    {
        $code = trim($request->get('code', ''));

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Kode barcode / part number wajib diisi.',
            ], 422);
        }

        $part = Part::with(['supplierRelation.sales', 'partCategory'])
            ->where('barcode', $code)
            ->orWhere('part_number', $code)
            ->first();

        if (!$part) {
            // Coba pencarian partial jika tidak ada exact match
            $part = Part::with(['supplierRelation.sales', 'partCategory'])
                ->where('part_number', 'LIKE', "%{$code}%")
                ->orWhere('name', 'LIKE', "%{$code}%")
                ->first();
        }

        if (!$part) {
            return response()->json([
                'success' => false,
                'message' => "Suku cadang dengan kode/barcode '{$code}' tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'part' => [
                'id' => $part->id,
                'part_number' => $part->part_number,
                'barcode' => $part->effective_barcode,
                'name' => $part->name,
                'brand' => $part->brand,
                'category' => $part->partCategory ? $part->partCategory->name : $part->category,
                'stock' => $part->stock,
                'unit' => $part->unit,
                'cost_price' => (float) $part->cost_price,
                'selling_price' => (float) $part->selling_price,
                'location' => $part->location ?? '-',
                'supplier_id' => $part->supplier_id,
                'supplier_name' => $part->supplierRelation ? $part->supplierRelation->name : ($part->supplier ?? '-'),
                'sales_list' => $part->supplierRelation ? $part->supplierRelation->sales->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'phone' => $s->phone,
                ]) : [],
            ],
        ]);
    }

    /**
     * Simpan Hasil Scan Stock Opname (Penyesuaian Fisik)
     */
    public function storeOpname(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'physical_stock' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $part = Part::findOrFail($validated['part_id']);
        $beforeStock = $part->stock;
        $physicalStock = (int) $validated['physical_stock'];
        $diff = $physicalStock - $beforeStock;

        $note = $validated['notes'] ?: "Stock Opname Scan (Fisik: {$physicalStock}, Sistem: {$beforeStock}, Selisih: " . ($diff >= 0 ? "+{$diff}" : $diff) . ")";

        $part->update(['stock' => $physicalStock]);

        $movement = StockMovement::create([
            'part_id' => $part->id,
            'type' => 'ADJUSTMENT',
            'quantity' => abs($diff),
            'before_stock' => $beforeStock,
            'after_stock' => $physicalStock,
            'notes' => $note,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Stok {$part->name} berhasil disesuaikan menjadi {$physicalStock} {$part->unit}.",
            'diff' => $diff,
            'movement' => [
                'id' => $movement->id,
                'part_name' => $part->name,
                'part_number' => $part->part_number,
                'before_stock' => $beforeStock,
                'after_stock' => $physicalStock,
                'diff_text' => ($diff >= 0 ? "+{$diff}" : "{$diff}") . " {$part->unit}",
                'created_at' => Carbon::now()->format('H:i:s'),
            ],
        ]);
    }

    /**
     * Simpan Barang Masuk Hasil Scan (Stock IN)
     */
    public function storeStockIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'quantity' => 'required|integer|min:1',
            'cost_price' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_sales_id' => 'nullable|exists:supplier_sales,id',
            'batch_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        $part = Part::findOrFail($validated['part_id']);
        $beforeStock = $part->stock;
        $qty = (int) $validated['quantity'];
        $newStock = $beforeStock + $qty;

        $costPrice = isset($validated['cost_price']) && $validated['cost_price'] > 0
            ? (float) $validated['cost_price']
            : (float) $part->cost_price;

        $updateData = ['stock' => $newStock];
        if (isset($validated['cost_price']) && $validated['cost_price'] > 0) {
            $updateData['cost_price'] = $costPrice;
        }
        if (!empty($validated['supplier_id'])) {
            $updateData['supplier_id'] = $validated['supplier_id'];
        }
        $part->update($updateData);

        $notes = $validated['notes'] ?: "Penerimaan Barang Masuk via Barcode Scanner (+{$qty} {$part->unit})";
        if (!empty($validated['batch_reference'])) {
            $notes .= " | Batch: {$validated['batch_reference']}";
        }

        $movement = StockMovement::create([
            'part_id' => $part->id,
            'type' => 'IN',
            'quantity' => $qty,
            'before_stock' => $beforeStock,
            'after_stock' => $newStock,
            'cost_price' => $costPrice,
            'total_cost' => $costPrice * $qty,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'supplier_sales_id' => $validated['supplier_sales_id'] ?? null,
            'batch_reference' => $validated['batch_reference'] ?? null,
            'notes' => $notes,
            'user_id' => auth()->id(),
        ]);

        $movement->load(['supplierRelation', 'supplierSales']);

        return response()->json([
            'success' => true,
            'message' => "Berhasil menambah {$qty} {$part->unit} {$part->name}. Stok sekarang: {$newStock}.",
            'movement' => [
                'id' => $movement->id,
                'part_name' => $part->name,
                'part_number' => $part->part_number,
                'qty' => "+{$qty} {$part->unit}",
                'before_stock' => $beforeStock,
                'after_stock' => $newStock,
                'batch' => $validated['batch_reference'] ?? '-',
                'sales_name' => $movement->supplierSales ? $movement->supplierSales->name : '-',
                'created_at' => Carbon::now()->format('H:i:s'),
            ],
        ]);
    }

    /**
     * Simpan Pengeluaran Barang Bebas (Stock OUT)
     */
    public function storeStockOut(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'required|string|max:255',
        ]);

        $part = Part::findOrFail($validated['part_id']);
        $qty = (int) $validated['quantity'];

        if ($part->stock < $qty) {
            return response()->json([
                'success' => false,
                'message' => "Stok {$part->name} tidak mencukupi (Tersedia: {$part->stock}, Diminta: {$qty}).",
            ], 422);
        }

        $beforeStock = $part->stock;
        $newStock = $beforeStock - $qty;
        $part->update(['stock' => $newStock]);

        $movement = StockMovement::create([
            'part_id' => $part->id,
            'type' => 'OUT',
            'quantity' => $qty,
            'before_stock' => $beforeStock,
            'after_stock' => $newStock,
            'notes' => $validated['notes'],
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Pengeluaran {$qty} {$part->unit} {$part->name} berhasil dicatat. Sisa stok: {$newStock}.",
            'movement' => [
                'id' => $movement->id,
                'part_name' => $part->name,
                'part_number' => $part->part_number,
                'qty' => "-{$qty} {$part->unit}",
                'before_stock' => $beforeStock,
                'after_stock' => $newStock,
                'notes' => $validated['notes'],
                'created_at' => Carbon::now()->format('H:i:s'),
            ],
        ]);
    }

    /**
     * Lookup Work Order untuk Verifikasi Pengeluaran Barang
     */
    public function lookupWorkOrder(Request $request): JsonResponse
    {
        $query = trim($request->get('wo', ''));

        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor atau ID Work Order wajib disertakan.',
            ], 422);
        }

        $workOrder = WorkOrder::with([
            'customer',
            'vehicle',
            'technician',
            'partsVerifier',
            'items' => fn($q) => $q->where('type', 'PART')->with('part.partCategory'),
        ])
        ->where('wo_number', $query)
        ->orWhere('id', $query)
        ->first();

        if (!$workOrder) {
            return response()->json([
                'success' => false,
                'message' => "Work Order '{$query}' tidak ditemukan.",
            ], 404);
        }

        $progress = $workOrder->partVerificationProgress();

        return response()->json([
            'success' => true,
            'work_order' => [
                'id' => $workOrder->id,
                'wo_number' => $workOrder->wo_number,
                'status' => $workOrder->status,
                'customer_name' => $workOrder->customer->name,
                'customer_phone' => $workOrder->customer->phone,
                'vehicle_plate' => $workOrder->vehicle->license_plate,
                'vehicle_model' => "{$workOrder->vehicle->brand} {$workOrder->vehicle->model}",
                'technician_name' => $workOrder->technician ? $workOrder->technician->name : 'Belum Ditugaskan',
                'parts_verified_at' => $workOrder->parts_verified_at ? $workOrder->parts_verified_at->format('d/m/Y H:i') : null,
                'parts_verifier' => $workOrder->partsVerifier ? $workOrder->partsVerifier->name : null,
                'progress' => $progress,
                'items' => $workOrder->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'part_id' => $item->part_id,
                        'part_number' => $item->part ? $item->part->part_number : '-',
                        'barcode' => $item->part ? $item->part->effective_barcode : '-',
                        'name' => $item->item_name,
                        'location' => $item->part ? ($item->part->location ?? '-') : '-',
                        'quantity' => (float) $item->quantity,
                        'verified_quantity' => (float) $item->verified_quantity,
                        'is_verified' => (bool) $item->isFullyVerified(),
                        'approval_status' => $item->approval_status,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Verifikasi Item Work Order melalui Scan Barcode
     */
    public function verifyWorkOrderItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'barcode_or_code' => 'required|string',
            'quantity' => 'nullable|numeric|min:0.1',
        ]);

        $workOrder = WorkOrder::with('items.part')->findOrFail($validated['work_order_id']);
        $code = trim($validated['barcode_or_code']);
        $scanQty = (float) ($validated['quantity'] ?? 1);

        // Cari part berdasarkan barcode / kode
        $scannedPart = Part::findByBarcodeOrNumber($code);
        if (!$scannedPart) {
            // Coba cari nama serupa jika kode persis tidak match
            $scannedPart = Part::where('name', 'LIKE', "%{$code}%")->first();
        }

        if (!$scannedPart) {
            return response()->json([
                'success' => false,
                'error_type' => 'PART_NOT_FOUND',
                'message' => "Suku cadang dengan kode '{$code}' tidak terdaftar di sistem bengkel.",
            ], 404);
        }

        // Cari apakah part ini ada dalam item Work Order
        $item = $workOrder->items()
            ->where('type', 'PART')
            ->where('part_id', $scannedPart->id)
            ->first();

        if (!$item) {
            // ERROR: Salah ambil barang! Barang tidak ada di SPK
            return response()->json([
                'success' => false,
                'error_type' => 'NOT_IN_WORK_ORDER',
                'message' => "PERINGATAN: '{$scannedPart->name}' ({$scannedPart->part_number}) BUKAN bagian dari Work Order {$workOrder->wo_number}! Jangan dikeluarkan ke teknisi.",
                'scanned_part' => [
                    'name' => $scannedPart->name,
                    'part_number' => $scannedPart->part_number,
                ],
            ], 422);
        }

        // Cek jika sudah terpenuhi
        $currentVerified = (float) $item->verified_quantity;
        $targetQty = (float) $item->quantity;

        if ($currentVerified >= $targetQty && $item->is_verified) {
            return response()->json([
                'success' => false,
                'error_type' => 'ALREADY_COMPLETED',
                'message' => "Barang '{$item->item_name}' sudah lengkap terverifikasi ({$currentVerified}/{$targetQty}). Tidak perlu scan lagi.",
                'item' => [
                    'id' => $item->id,
                    'name' => $item->item_name,
                    'quantity' => $targetQty,
                    'verified_quantity' => $currentVerified,
                    'is_verified' => true,
                ],
            ], 422);
        }

        // Tambah kuantitas terverifikasi
        $newVerified = min($targetQty, $currentVerified + $scanQty);
        $isComplete = $newVerified >= $targetQty;

        $item->update([
            'verified_quantity' => $newVerified,
            'is_verified' => $isComplete,
            'verified_at' => Carbon::now(),
            'verified_by' => auth()->id(),
        ]);

        // Cek progres keseluruhan Work Order
        $progress = $workOrder->fresh()->partVerificationProgress();

        if ($progress['is_complete'] && !$workOrder->parts_verified_at) {
            $workOrder->update([
                'parts_verified_at' => Carbon::now(),
                'parts_verified_by' => auth()->id(),
            ]);

            WoTimeline::create([
                'work_order_id' => $workOrder->id,
                'status' => $workOrder->status,
                'title' => 'Pengeluaran Suku Cadang Terverifikasi',
                'description' => "Seluruh suku cadang ({$progress['total']} item) telah selesai diverifikasi & dikeluarkan oleh petugas gudang " . auth()->user()->name,
                'user_id' => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Barang '{$item->item_name}' berhasil diverifikasi ({$newVerified}/{$targetQty}).",
            'item' => [
                'id' => $item->id,
                'name' => $item->item_name,
                'part_number' => $scannedPart->part_number,
                'quantity' => $targetQty,
                'verified_quantity' => $newVerified,
                'is_verified' => $isComplete,
            ],
            'progress' => $progress,
            'all_complete' => $progress['is_complete'],
        ]);
    }

    /**
     * Selesaikan Verifikasi Manual untuk Semua Part dalam Work Order
     */
    public function completeWorkOrderVerification(Request $request, WorkOrder $workOrder)
    {
        $workOrder->load('items.part');

        $partItems = $workOrder->items()->where('type', 'PART')->get();

        foreach ($partItems as $item) {
            $item->update([
                'verified_quantity' => $item->quantity,
                'is_verified' => true,
                'verified_at' => Carbon::now(),
                'verified_by' => auth()->id(),
            ]);
        }

        $workOrder->update([
            'parts_verified_at' => Carbon::now(),
            'parts_verified_by' => auth()->id(),
        ]);

        WoTimeline::create([
            'work_order_id' => $workOrder->id,
            'status' => $workOrder->status,
            'title' => 'Pengeluaran Suku Cadang Terverifikasi',
            'description' => "Seluruh suku cadang ({$partItems->count()} item) dikonfirmasi & dikeluarkan oleh " . auth()->user()->name,
            'user_id' => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Seluruh suku cadang Work Order berhasil diverifikasi dan siap diserahkan ke teknisi.',
            ]);
        }

        return back()->with('success', 'Seluruh suku cadang Work Order berhasil diverifikasi dan siap diserahkan ke teknisi.');
    }

    /**
     * Cetak Bukti Pengeluaran Barang / Picking Slip Work Order
     */
    public function printPickingSlip(WorkOrder $workOrder)
    {
        $workOrder->load([
            'customer',
            'vehicle',
            'technician',
            'partsVerifier',
            'items' => fn($q) => $q->where('type', 'PART')->with('part.partCategory'),
        ]);

        return view('scanner.picking-slip', compact('workOrder'));
    }

    /**
     * Cetak Label Stiker Barcode Suku Cadang
     */
    public function printBarcode(Part $part)
    {
        $part->load(['partCategory', 'supplierRelation']);
        return view('scanner.barcode-label', compact('part'));
    }
}
