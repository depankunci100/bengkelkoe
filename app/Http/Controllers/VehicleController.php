<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $vehicles = Vehicle::with(['customer', 'workOrders'])
            ->when($search, function ($query, $search) {
                $query->where('plate_number', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $customers = Customer::orderBy('name')->get();

        return view('vehicles.index', compact('vehicles', 'search', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'plate_number' => 'required|string|unique:vehicles,plate_number',
            'brand' => 'required|string',
            'model' => 'required|string',
            'year' => 'nullable|integer',
            'transmission' => 'nullable|string',
            'vin_number' => 'nullable|string',
            'color' => 'nullable|string',
            'odometer' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $validated['plate_number'] = strtoupper(trim($validated['plate_number']));
        $vehicle = Vehicle::create($validated);

        return back()->with('success', "Kendaraan {$vehicle->plate_number} ({$vehicle->brand} {$vehicle->model}) berhasil didaftarkan!");
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|unique:vehicles,plate_number,' . $vehicle->id,
            'brand' => 'required|string',
            'model' => 'required|string',
            'year' => 'nullable|integer',
            'transmission' => 'nullable|string',
            'color' => 'nullable|string',
            'odometer' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $validated['plate_number'] = strtoupper(trim($validated['plate_number']));
        $vehicle->update($validated);

        return back()->with('success', "Data kendaraan {$vehicle->plate_number} berhasil diperbarui.");
    }

    public function destroy(Vehicle $vehicle)
    {
        $plate = $vehicle->plate_number;
        $vehicle->delete();

        return back()->with('success', "Kendaraan {$plate} berhasil dihapus.");
    }
}
