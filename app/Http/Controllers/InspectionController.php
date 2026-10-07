<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\InspectionItem;
use App\Models\WorkOrder;
use App\Models\WoTimeline;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'ALL');

        $query = Inspection::with(['workOrder.customer', 'workOrder.vehicle', 'technician'])->latest();

        if ($status !== 'ALL') {
            $query->where('status', $status);
        }

        $inspections = $query->paginate(10)->withQueryString();

        return view('inspections.index', compact('inspections', 'status'));
    }

    public function show($workOrderId)
    {
        $wo = WorkOrder::with(['customer', 'vehicle', 'technician'])->findOrFail($workOrderId);

        $inspection = Inspection::with('items')->firstOrCreate(
            ['work_order_id' => $wo->id],
            [
                'vehicle_id' => $wo->vehicle_id,
                'technician_id' => $wo->technician_id,
                'status' => 'IN_PROGRESS',
            ]
        );

        // Group items by category (ENGINE, BRAKE, ELECTRICAL, SUSPENSION, BODY_INTERIOR)
        $groupedItems = $inspection->items->groupBy('category');

        return view('inspections.show', compact('wo', 'inspection', 'groupedItems'));
    }

    public function updateItem(Request $request, InspectionItem $item)
    {
        $validated = $request->validate([
            'condition' => 'required|in:GOOD,WARNING,BAD,NEED_REPLACEMENT',
            'notes' => 'nullable|string',
        ]);

        $item->update($validated);

        return back()->with('success', "Item {$item->item_name} berhasil diperbarui.");
    }

    public function addItem(Request $request, Inspection $inspection)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'item_name' => 'required|string',
            'condition' => 'required|in:GOOD,WARNING,BAD,NEED_REPLACEMENT',
            'notes' => 'nullable|string',
        ]);

        $item = InspectionItem::create([
            'inspection_id' => $inspection->id,
            'category' => $validated['category'],
            'item_name' => $validated['item_name'],
            'condition' => $validated['condition'],
            'notes' => $validated['notes'],
        ]);

        return back()->with('success', "Item inspeksi '{$item->item_name}' berhasil ditambahkan.");
    }

    public function complete(Request $request, Inspection $inspection)
    {
        $validated = $request->validate([
            'overall_summary' => 'required|string',
        ]);

        $inspection->update([
            'overall_summary' => $validated['overall_summary'],
            'status' => 'COMPLETED',
        ]);

        // Catat timeline WO
        WoTimeline::create([
            'work_order_id' => $inspection->work_order_id,
            'user_id' => auth()->id(),
            'title' => 'Inspeksi Fisik Selesai',
            'description' => 'Hasil diagnosa teknisi: ' . $validated['overall_summary'],
            'status' => 'INSPECTION',
        ]);

        return redirect()->route('work-orders.show', $inspection->work_order_id)
            ->with('success', 'Pemeriksaan inspeksi kendaraan berhasil diselesaikan!');
    }
}
