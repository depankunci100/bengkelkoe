<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierSales;
use Illuminate\Http\Request;

class SupplierSalesController extends Controller
{
    public function store(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'area' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        $sales = $supplier->sales()->create($validated);

        return back()->with('success', "Sales {$sales->name} berhasil ditambahkan ke supplier {$supplier->name}!");
    }

    public function update(Request $request, Supplier $supplier, SupplierSales $sale)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'area' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $sale->update($validated);

        return back()->with('success', "Informasi sales {$sale->name} berhasil diperbarui.");
    }

    public function destroy(Supplier $supplier, SupplierSales $sale)
    {
        if ($sale->workOrders()->count() > 0) {
            $sale->update(['is_active' => false]);
            return back()->with('warning', "Sales {$sale->name} memiliki riwayat Work Order, status dinonaktifkan.");
        }

        $name = $sale->name;
        $sale->delete();

        return back()->with('success', "Sales {$name} berhasil dihapus.");
    }

    public function getSalesBySupplier(Supplier $supplier)
    {
        $sales = $supplier->sales()->where('is_active', true)->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $sales,
        ]);
    }
}
