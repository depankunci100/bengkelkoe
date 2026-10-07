<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category');

        $query = Service::latest();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        if ($category) {
            $query->where('category', $category);
        }

        $services = $query->paginate(10)->withQueryString();
        $categories = Service::select('category')->distinct()->pluck('category');

        return view('services.index', compact('services', 'search', 'category', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:services,code',
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'estimated_minutes' => 'required|integer|min:5',
            'description' => 'nullable|string',
        ]);

        $service = Service::create($validated);

        return back()->with('success', "Jasa {$service->name} ({$service->code}) berhasil ditambahkan!");
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:services,code,' . $service->id,
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'estimated_minutes' => 'required|integer|min:5',
            'description' => 'nullable|string',
        ]);

        $service->update($validated);

        return back()->with('success', "Tarif jasa {$service->name} berhasil diperbarui.");
    }

    public function destroy(Service $service)
    {
        $name = $service->name;
        $service->delete();

        return back()->with('success', "Jasa {$name} berhasil dihapus.");
    }
}
