<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\PartCategory;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $supplierId = $request->query('supplier_id');
        $stockFilter = $request->query('stock_status'); // 'low', 'empty'

        $query = Part::with(['partCategory', 'supplierRelation'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where(function ($q) use ($category) {
                $q->where('category', $category)
                  ->orWhereHas('partCategory', function ($qc) use ($category) {
                      $qc->where('name', $category)->orWhere('code', $category);
                  });
            });
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($stockFilter === 'low') {
            $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
        } elseif ($stockFilter === 'empty') {
            $query->where('stock', '<=', 0);
        }

        $parts = $query->paginate(15)->withQueryString();
        $categories = PartCategory::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('parts.index', compact('parts', 'search', 'category', 'supplierId', 'stockFilter', 'categories', 'suppliers'));
    }

    public function create()
    {
        $categories = PartCategory::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('parts.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_number' => 'required|string|unique:parts,part_number',
            'barcode' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'brand' => 'required|string',
            'category' => 'nullable|string',
            'part_category_id' => 'nullable|exists:part_categories,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'location' => 'nullable|string',
            'supplier' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        if (!empty($validated['part_category_id'])) {
            $cat = PartCategory::find($validated['part_category_id']);
            if ($cat && empty($validated['category'])) {
                $validated['category'] = $cat->name;
            }
        }
        if (empty($validated['category'])) {
            $validated['category'] = 'Lain-lain';
        }

        if (!empty($validated['supplier_id'])) {
            $sup = Supplier::find($validated['supplier_id']);
            if ($sup && empty($validated['supplier'])) {
                $validated['supplier'] = $sup->name;
            }
        }

        $part = Part::create($validated);

        // Catat initial stock movement
        if ($part->stock > 0) {
            StockMovement::create([
                'part_id' => $part->id,
                'type' => 'IN',
                'quantity' => $part->stock,
                'cost_price' => $part->cost_price,
                'selling_price' => $part->selling_price,
                'supplier' => $part->supplier,
                'supplier_id' => $part->supplier_id,
                'before_stock' => 0,
                'after_stock' => $part->stock,
                'notes' => 'Saldo awal stok sparepart',
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('parts.index')
            ->with('success', "Sparepart {$part->name} ({$part->part_number}) berhasil ditambahkan!");
    }

    public function show($id)
    {
        $part = Part::with([
            'movements.user',
            'movements.workOrder',
            'movements.supplierRelation',
            'movements.supplierSales',
            'returns.supplier',
            'returns.supplierSales',
            'partCategory',
            'supplierRelation'
        ])->findOrFail($id);
        $suppliers = Supplier::with('sales')->where('is_active', true)->orderBy('name')->get();

        return view('parts.show', compact('part', 'suppliers'));
    }

    public function edit(Part $part)
    {
        $categories = PartCategory::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('parts.edit', compact('part', 'categories', 'suppliers'));
    }

    public function update(Request $request, Part $part)
    {
        $validated = $request->validate([
            'part_number' => 'required|string|unique:parts,part_number,' . $part->id,
            'barcode' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'brand' => 'required|string',
            'category' => 'nullable|string',
            'part_category_id' => 'nullable|exists:part_categories,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'location' => 'nullable|string',
            'supplier' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        if (!empty($validated['part_category_id'])) {
            $cat = PartCategory::find($validated['part_category_id']);
            if ($cat && empty($validated['category'])) {
                $validated['category'] = $cat->name;
            }
        }

        if (!empty($validated['supplier_id'])) {
            $sup = Supplier::find($validated['supplier_id']);
            if ($sup && empty($validated['supplier'])) {
                $validated['supplier'] = $sup->name;
            }
        }

        $part->update($validated);

        return redirect()->route('parts.show', $part->id)
            ->with('success', 'Informasi sparepart berhasil diperbarui.');
    }

    public function adjustStock(Request $request, Part $part)
    {
        $request->validate([
            'type' => 'required|in:IN,OUT,ADJUSTMENT',
            'quantity' => 'required|numeric|min:0',
            'notes' => 'required|string',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'cost_calculation_method' => 'nullable|in:AVERAGE,LATEST,KEEP',
            'batch_reference' => 'nullable|string',
            'supplier' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_sales_id' => 'nullable|exists:supplier_sales,id',
        ]);

        $before = $part->stock;
        $qty = (int) $request->quantity;
        $oldCost = (float) $part->cost_price;
        $newBatchCost = $request->filled('cost_price') ? (float) $request->cost_price : $oldCost;
        $method = $request->input('cost_calculation_method', 'AVERAGE');

        $updatePartData = [];
        $costLogMsg = "";

        // Supplier resolution
        $supplierId = $request->supplier_id ?? $part->supplier_id;
        $supplierSalesId = $request->supplier_sales_id;
        $supplierName = $request->supplier;
        if ($supplierId && empty($supplierName)) {
            $sup = Supplier::find($supplierId);
            $supplierName = $sup?->name;
        }
        if (empty($supplierName)) {
            $supplierName = $part->supplier;
        }

        if ($request->type === 'IN') {
            $after = $before + $qty;
            $updatePartData['stock'] = $after;

            // Jika harga beli baru berbeda dan stok ada
            if ($request->filled('cost_price')) {
                if ($method === 'AVERAGE' && $after > 0) {
                    // Weighted Moving Average Cost
                    // ((Stok Lama * HPP Lama) + (Qty Masuk * Harga Masuk)) / Total Stok
                    $calculatedCost = round((($before * $oldCost) + ($qty * $newBatchCost)) / $after, 2);
                    $updatePartData['cost_price'] = $calculatedCost;
                    $costLogMsg = " [HPP Moving Average: Rp " . number_format($oldCost, 0, ',', '.') . " -> Rp " . number_format($calculatedCost, 0, ',', '.') . " | Beli Batch: Rp " . number_format($newBatchCost, 0, ',', '.') . "]";
                } elseif ($method === 'LATEST') {
                    $updatePartData['cost_price'] = $newBatchCost;
                    $costLogMsg = " [HPP disesuaikan ke harga beli terbaru: Rp " . number_format($newBatchCost, 0, ',', '.') . "]";
                }
            }

            // Update harga jual jika diisi
            if ($request->filled('selling_price') && (float) $request->selling_price > 0) {
                $updatePartData['selling_price'] = (float) $request->selling_price;
            }

        } elseif ($request->type === 'OUT') {
            $after = max(0, $before - $qty);
            $updatePartData['stock'] = $after;
        } else {
            // ADJUSTMENT / Stock Opname Fisik
            $after = $qty; // Qty adalah hasil hitungan fisik riil
            $difference = $after - $before;
            $updatePartData['stock'] = $after;

            if ($request->filled('cost_price') && (float) $request->cost_price > 0) {
                $updatePartData['cost_price'] = (float) $request->cost_price;
            }
            if ($request->filled('selling_price') && (float) $request->selling_price > 0) {
                $updatePartData['selling_price'] = (float) $request->selling_price;
            }

            $costLogMsg = " [Opname Fisik: Selisih " . ($difference >= 0 ? "+{$difference}" : "{$difference}") . " {$part->unit}]";
        }

        if ($supplierId && !$part->supplier_id) {
            $updatePartData['supplier_id'] = $supplierId;
        }
        if ($supplierName && !$part->supplier) {
            $updatePartData['supplier'] = $supplierName;
        }

        $part->update($updatePartData);

        StockMovement::create([
            'part_id' => $part->id,
            'type' => $request->type,
            'quantity' => $qty,
            'cost_price' => $newBatchCost,
            'selling_price' => $request->filled('selling_price') ? (float) $request->selling_price : $part->selling_price,
            'batch_reference' => $request->batch_reference,
            'supplier' => $supplierName,
            'supplier_id' => $supplierId,
            'supplier_sales_id' => $supplierSalesId,
            'before_stock' => $before,
            'after_stock' => $after,
            'notes' => $request->notes . $costLogMsg,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', "Mutasi & Opname stok {$part->name} berhasil dicatat! Stok saat ini: {$after} {$part->unit}.");
    }
}
