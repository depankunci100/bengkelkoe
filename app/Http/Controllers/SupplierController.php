<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Supplier::withCount('parts')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        $suppliers = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('is_active', true)->count(),
            'total_parts_supplied' => Part::whereNotNull('supplier_id')->count(),
        ];

        return view('suppliers.index', compact('suppliers', 'search', 'status', 'stats'));
    }

    public function create()
    {
        $nextNumber = Supplier::count() + 1;
        $nextCode = 'SUP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('suppliers.create', compact('nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:suppliers,code',
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['code'])) {
            $nextNumber = Supplier::count() + 1;
            $validated['code'] = 'SUP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        $supplier = Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier {$supplier->name} ({$supplier->code}) berhasil ditambahkan!");
    }

    public function show($id)
    {
        $supplier = Supplier::with([
            'parts' => function ($q) {
                $q->latest();
            },
            'stockMovements' => function ($q) {
                $q->with(['part', 'user'])->latest()->limit(20);
            }
        ])->withCount('parts')->findOrFail($id);

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:suppliers,code,' . $supplier->id,
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $supplier->update($validated);

        return redirect()->route('suppliers.show', $supplier->id)
            ->with('success', "Informasi supplier {$supplier->name} berhasil diperbarui.");
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->parts()->count() > 0) {
            // Jika ada suku cadang tertaut, nonaktifkan saja untuk menjaga integritas data
            $supplier->update(['is_active' => false]);
            return redirect()->route('suppliers.index')
                ->with('warning', "Supplier {$supplier->name} memiliki suku cadang tertaut, status diubah menjadi Non-Aktif.");
        }

        $name = $supplier->name;
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier {$name} berhasil dihapus.");
    }
}
