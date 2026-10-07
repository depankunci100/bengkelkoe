<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $technicianId = $request->query('technician_id');
        $status = $request->query('status');

        $query = WorkOrder::with(['customer', 'vehicle', 'technician', 'invoice'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($technicianId) {
            $query->where('technician_id', $technicianId);
        }

        if ($status && $status !== 'ALL') {
            $query->where('status', $status);
        }

        $workOrders = $query->latest()->get();

        // Metrik Ringkasan Laporan
        $totalWo = $workOrders->count();
        $completedWo = $workOrders->where('status', 'COMPLETED')->count();
        $totalOmset = $workOrders->sum('grand_total');
        $totalJasa = $workOrders->sum('total_services');
        $totalPart = $workOrders->sum('total_parts');

        $technicians = User::where('role', 'technician')->get();

        return view('reports.index', compact(
            'workOrders',
            'startDate',
            'endDate',
            'technicianId',
            'status',
            'totalWo',
            'completedWo',
            'totalOmset',
            'totalJasa',
            'totalPart',
            'technicians'
        ));
    }
}
