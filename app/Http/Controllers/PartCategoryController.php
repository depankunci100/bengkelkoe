<?php

namespace App\Http\Controllers;

use App\Models\PartCategory;
use Illuminate\Http\Request;

class PartCategoryController extends Controller
{
    public function index()
    {
        $categories = PartCategory::withCount('parts')->orderBy('name')->get();

        return view('part-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:part_categories,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['icon'] = $validated['icon'] ?? 'bi-gear';
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        $category = PartCategory::create($validated);

        return redirect()->route('part-categories.index')
            ->with('success', "Kategori suku cadang '{$category->name}' berhasil ditambahkan!");
    }

    public function update(Request $request, PartCategory $partCategory)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:part_categories,code,' . $partCategory->id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['icon'] = $validated['icon'] ?? 'bi-gear';
        $validated['is_active'] = $request->has('is_active');

        $partCategory->update($validated);

        return redirect()->route('part-categories.index')
            ->with('success', "Kategori suku cadang '{$partCategory->name}' berhasil diperbarui.");
    }

    public function destroy(PartCategory $partCategory)
    {
        if ($partCategory->parts()->count() > 0) {
            return redirect()->route('part-categories.index')
                ->with('error', "Kategori '{$partCategory->name}' tidak dapat dihapus karena masih memiliki {$partCategory->parts()->count()} item sparepart terdaftar.");
        }

        $name = $partCategory->name;
        $partCategory->delete();

        return redirect()->route('part-categories.index')
            ->with('success', "Kategori '{$name}' berhasil dihapus.");
    }
}
