<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $stockFilter = $request->query('stock_status'); // 'low', 'empty'

        $query = Part::latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($stockFilter === 'low') {
            $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
        } elseif ($stockFilter === 'empty') {
            $query->where('stock', '<=', 0);
        }

        $parts = $query->paginate(10)->withQueryString();
        $categories = Part::select('category')->distinct()->pluck('category');

        return view('parts.index', compact('parts', 'search', 'category', 'stockFilter', 'categories'));
    }

    public function create()
    {
        return view('parts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_number' => 'required|string|unique:parts,part_number',
            'name' => 'required|string|max:255',
            'brand' => 'required|string',
            'category' => 'required|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'location' => 'nullable|string',
            'supplier' => 'nullable|string',
        ]);

        $part = Part::create($validated);

        // Catat initial stock movement
        if ($part->stock > 0) {
            StockMovement::create([
                'part_id' => $part->id,
                'type' => 'IN',
                'quantity' => $part->stock,
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
        $part = Part::with(['movements.user', 'movements.workOrder'])->findOrFail($id);

        return view('parts.show', compact('part'));
    }

    public function edit(Part $part)
    {
        return view('parts.edit', compact('part'));
    }

    public function update(Request $request, Part $part)
    {
        $validated = $request->validate([
            'part_number' => 'required|string|unique:parts,part_number,' . $part->id,
            'name' => 'required|string|max:255',
            'brand' => 'required|string',
            'category' => 'required|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'location' => 'nullable|string',
            'supplier' => 'nullable|string',
        ]);

        $part->update($validated);

        return redirect()->route('parts.show', $part->id)
            ->with('success', 'Informasi sparepart berhasil diperbarui.');
    }

    public function adjustStock(Request $request, Part $part)
    {
        $request->validate([
            'type' => 'required|in:IN,OUT,ADJUSTMENT',
            'quantity' => 'required|numeric|min:1',
            'notes' => 'required|string',
        ]);

        $before = $part->stock;
        $qty = (int) $request->quantity;

        if ($request->type === 'IN') {
            $after = $before + $qty;
        } elseif ($request->type === 'OUT') {
            $after = max(0, $before - $qty);
        } else {
            // Penyesuaian fisik langsung
            $after = $qty;
        }

        $part->update(['stock' => $after]);

        StockMovement::create([
            'part_id' => $part->id,
            'type' => $request->type,
            'quantity' => $qty,
            'before_stock' => $before,
            'after_stock' => $after,
            'notes' => $request->notes,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', "Penyesuaian stok berhasil! Stok sekarang: {$after} {$part->unit}.");
    }
}
