<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkOrderApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $query = WorkOrder::with(['customer', 'vehicle', 'technician'])->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $workOrders = $query->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Daftar work order berhasil dimuat',
            'data' => $workOrders,
        ]);
    }

    public function show($id): JsonResponse
    {
        $wo = WorkOrder::with([
            'customer',
            'vehicle',
            'technician',
            'items',
            'inspection.items',
            'timelines',
        ])->find($id);

        if (!$wo) {
            return response()->json([
                'success' => false,
                'message' => 'Work order tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $wo,
        ]);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate(['status' => 'required|string']);

        $wo = WorkOrder::find($id);
        if (!$wo) {
            return response()->json(['success' => false, 'message' => 'Work order tidak ditemukan'], 404);
        }

        $wo->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status work order berhasil diperbarui',
            'data' => $wo,
        ]);
    }
}
