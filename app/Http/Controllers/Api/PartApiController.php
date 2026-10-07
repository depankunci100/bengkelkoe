<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Part;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $query = Part::where('is_active', true)->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%");
            });
        }

        $parts = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $parts,
        ]);
    }
}
