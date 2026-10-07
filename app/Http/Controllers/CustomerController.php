<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $customers = Customer::withCount(['vehicles', 'workOrders'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        // Jika opsi tambah kendaraan langsung diisi
        if ($request->filled('plate_number')) {
            $request->validate([
                'plate_number' => 'required|string|unique:vehicles,plate_number',
                'brand' => 'required|string',
                'model' => 'required|string',
                'year' => 'nullable|integer',
                'transmission' => 'nullable|string',
            ]);

            Vehicle::create([
                'customer_id' => $customer->id,
                'plate_number' => strtoupper(trim($request->plate_number)),
                'brand' => $request->brand,
                'model' => $request->model,
                'year' => $request->year,
                'transmission' => $request->transmission ?? 'Automatic',
                'color' => $request->color,
                'odometer' => $request->odometer ?? 0,
            ]);
        }

        return redirect()->route('customers.show', $customer->id)
            ->with('success', 'Customer ' . $customer->name . ' berhasil ditambahkan!');
    }

    public function show($id)
    {
        $customer = Customer::with([
            'vehicles',
            'workOrders' => function ($q) {
                $q->with(['vehicle', 'technician'])->latest();
            },
            'invoices' => function ($q) {
                $q->with(['workOrder', 'payments'])->latest();
            }
        ])->findOrFail($id);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer->id)
            ->with('success', 'Data customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $name = $customer->name;
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', "Customer {$name} dan seluruh data terkait berhasil dihapus.");
    }
}
