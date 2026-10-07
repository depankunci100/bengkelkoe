<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierReturn;
use App\Models\WorkshopSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SupplierReturnController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'ALL');
        $search = $request->query('search');

        $query = SupplierReturn::with(['part', 'supplier', 'supplierSales', 'stockMovement', 'user'])->latest();

        if ($status !== 'ALL') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhere('batch_reference', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('part', function ($p) use ($search) {
                        $p->where('name', 'like', "%{$search}%")
                            ->orWhere('part_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('supplier', function ($s) use ($search) {
                        $s->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('supplierSales', function ($ss) use ($search) {
                        $ss->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $returns = $query->paginate(10)->withQueryString();

        return view('supplier-returns.index', compact('returns', 'status', 'search'));
    }

    public function create(Request $request)
    {
        $selectedPartId = $request->query('part_id');
        $selectedMovementId = $request->query('movement_id');

        $selectedPart = $selectedPartId ? Part::find($selectedPartId) : null;
        $selectedMovement = $selectedMovementId ? StockMovement::with(['supplierRelation', 'supplierSales'])->find($selectedMovementId) : null;

        if ($selectedMovement && !$selectedPart) {
            $selectedPart = $selectedMovement->part;
        }

        $parts = Part::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::with('sales')->where('is_active', true)->orderBy('name')->get();

        return view('supplier-returns.create', compact('parts', 'suppliers', 'selectedPart', 'selectedMovement'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'supplier_sales_id' => 'nullable|exists:supplier_sales,id',
            'stock_movement_id' => 'nullable|exists:stock_movements,id',
            'batch_reference' => 'nullable|string|max:100',
            'quantity' => 'required|numeric|min:0.01',
            'cost_price' => 'required|numeric|min:0',
            'settlement_type' => 'required|in:REPLACEMENT,REFUND',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $part = Part::findOrFail($validated['part_id']);
        $quantity = (float) $validated['quantity'];
        $costPrice = (float) $validated['cost_price'];
        $totalAmount = round($quantity * $costPrice, 2);

        $returnNumber = SupplierReturn::generateReturnNumber();

        // 1. Kurangi stok fisik karena barang rusak dikeluarkan untuk diretur
        $beforeStock = $part->stock;
        $afterStock = max(0, $beforeStock - $quantity);
        $part->update(['stock' => $afterStock]);

        // 2. Catat StockMovement tipe OUT (RETUR KE SUPPLIER)
        $salesName = "";
        if (!empty($validated['supplier_sales_id'])) {
            $sales = \App\Models\SupplierSales::find($validated['supplier_sales_id']);
            $salesName = $sales ? " [Sales: {$sales->name}]" : "";
        }

        $stockMovement = StockMovement::create([
            'part_id' => $part->id,
            'type' => 'OUT',
            'quantity' => $quantity,
            'cost_price' => $costPrice,
            'selling_price' => $part->selling_price,
            'batch_reference' => $validated['batch_reference'] ?? null,
            'supplier' => Supplier::find($validated['supplier_id'])?->name,
            'supplier_id' => $validated['supplier_id'],
            'supplier_sales_id' => $validated['supplier_sales_id'] ?? null,
            'before_stock' => $beforeStock,
            'after_stock' => $afterStock,
            'notes' => "Pengembalian barang rusak ke supplier ({$returnNumber}){$salesName}. Alasan: {$validated['reason']}",
            'user_id' => auth()->id(),
        ]);

        // 3. Buat faktur pengembalian barang ke supplier
        $supplierReturn = SupplierReturn::create([
            'return_number' => $returnNumber,
            'supplier_id' => $validated['supplier_id'],
            'supplier_sales_id' => $validated['supplier_sales_id'] ?? null,
            'part_id' => $part->id,
            'stock_movement_id' => $validated['stock_movement_id'] ?? $stockMovement->id,
            'batch_reference' => $validated['batch_reference'] ?? null,
            'quantity' => $quantity,
            'cost_price' => $costPrice,
            'total_amount' => $totalAmount,
            'settlement_type' => $validated['settlement_type'],
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'PENDING',
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('supplier-returns.show', $supplierReturn->id)
            ->with('success', "Faktur retur {$supplierReturn->return_number} berhasil diterbitkan senilai Rp " . number_format($totalAmount, 0, ',', '.') . "!");
    }

    public function show($id)
    {
        $return = SupplierReturn::with([
            'part',
            'supplier.sales',
            'supplierSales',
            'stockMovement',
            'user',
        ])->findOrFail($id);

        return view('supplier-returns.show', compact('return'));
    }

    public function updateStatus(Request $request, $id)
    {
        $return = SupplierReturn::with(['part', 'supplier', 'supplierSales'])->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:PENDING,ACCEPTED,COMPLETED,REJECTED',
            'notes' => 'nullable|string',
        ]);

        $prevStatus = $return->status;
        $newStatus = $validated['status'];

        $updateData = [
            'status' => $newStatus,
        ];

        if ($validated['notes']) {
            $updateData['notes'] = ($return->notes ? $return->notes . "\n" : "") . "[" . Carbon::now()->format('d/m/Y H:i') . "] " . $validated['notes'];
        }

        if ($newStatus === 'COMPLETED' && $prevStatus !== 'COMPLETED') {
            $updateData['resolved_at'] = Carbon::now();

            // Jika settlement adalah REPLACEMENT, barang pengganti baru sudah datang -> masukkan stok kembali!
            if ($return->settlement_type === 'REPLACEMENT') {
                $part = $return->part;
                $before = $part->stock;
                $after = $before + $return->quantity;
                $part->update(['stock' => $after]);

                StockMovement::create([
                    'part_id' => $part->id,
                    'type' => 'IN',
                    'quantity' => $return->quantity,
                    'cost_price' => $return->cost_price,
                    'selling_price' => $part->selling_price,
                    'batch_reference' => $return->batch_reference ? "REPL-" . $return->batch_reference : "REPL-" . $return->return_number,
                    'supplier' => $return->supplier?->name,
                    'supplier_id' => $return->supplier_id,
                    'supplier_sales_id' => $return->supplier_sales_id,
                    'before_stock' => $before,
                    'after_stock' => $after,
                    'notes' => "Penerimaan barang pengganti baru dari retur {$return->return_number}",
                    'user_id' => auth()->id(),
                ]);
            }
        } elseif ($newStatus === 'REJECTED' && in_array($prevStatus, ['PENDING', 'ACCEPTED'])) {
            // Jika retur ditolak oleh supplier, kembalikan stok fisik ke gudang
            $part = $return->part;
            $before = $part->stock;
            $after = $before + $return->quantity;
            $part->update(['stock' => $after]);

            StockMovement::create([
                'part_id' => $part->id,
                'type' => 'IN',
                'quantity' => $return->quantity,
                'cost_price' => $return->cost_price,
                'selling_price' => $part->selling_price,
                'supplier_id' => $return->supplier_id,
                'supplier_sales_id' => $return->supplier_sales_id,
                'before_stock' => $before,
                'after_stock' => $after,
                'notes' => "Pengembalian stok: Retur {$return->return_number} ditolak oleh supplier",
                'user_id' => auth()->id(),
            ]);
        }

        $return->update($updateData);

        return redirect()->route('supplier-returns.show', $return->id)
            ->with('success', "Status pengembalian barang berhasil diperbarui menjadi {$newStatus}.");
    }

    public function print($id)
    {
        $return = SupplierReturn::with([
            'part',
            'supplier',
            'supplierSales',
            'stockMovement',
            'user',
        ])->findOrFail($id);

        $settings = [
            'name' => WorkshopSetting::get('workshop_name', 'SIM BENGKEL AUTO SERVICE'),
            'address' => WorkshopSetting::get('workshop_address', 'Jl. Raya Otomotif No. 88, Surabaya'),
            'phone' => WorkshopSetting::get('workshop_phone', '031-8976543'),
            'email' => WorkshopSetting::get('workshop_email', 'info@simbengkel.com'),
            'footer' => WorkshopSetting::get('invoice_footer', 'Faktur pengembalian barang resmi dari SIM BENGKEL untuk supplier.'),
        ];

        return view('supplier-returns.print', compact('return', 'settings'));
    }
}
