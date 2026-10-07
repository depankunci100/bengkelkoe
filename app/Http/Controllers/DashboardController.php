<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Payment;
use App\Models\Service;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. KPI Cards
        $woToday = WorkOrder::whereDate('created_at', $today)->count();
        $woTotal = WorkOrder::count();
        $revenueToday = Payment::whereDate('paid_at', $today)->sum('amount');
        $revenueMonth = Payment::where('paid_at', '>=', $startOfMonth)->sum('amount');
        $waitingApproval = WorkOrder::where('status', 'WAITING_APPROVAL')->count();
        $readyPickup = WorkOrder::where('status', 'READY_FOR_PICKUP')->count();
        $inProgress = WorkOrder::where('status', 'IN_PROGRESS')->count();

        // 2. Trend 7 Hari Pendapatan
        $days = [];
        $revenueDaily = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayLabel = $date->translatedFormat('D, d M');
            $days[] = $dayLabel;
            $revenueDaily[] = (float) Payment::whereDate('paid_at', $date->toDateString())->sum('amount');
        }

        // 3. Distribusi Status Work Order
        $statuses = ['DRAFT', 'INSPECTION', 'WAITING_APPROVAL', 'APPROVED', 'IN_PROGRESS', 'QC', 'READY_FOR_PICKUP', 'COMPLETED'];
        $statusCounts = [];
        foreach ($statuses as $st) {
            $statusCounts[$st] = WorkOrder::where('status', $st)->count();
        }

        // 4. Jasa Terlaris
        $topServices = WorkOrderItem::where('type', 'SERVICE')
            ->select('item_name', DB::raw('count(*) as total_used'), DB::raw('sum(subtotal) as total_revenue'))
            ->groupBy('item_name')
            ->orderByDesc('total_used')
            ->limit(5)
            ->get();

        // 5. Sparepart Terlaris / Stok Kritis
        $lowStockParts = Part::whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        // 6. Recent Work Orders
        $recentWorkOrders = WorkOrder::with(['customer', 'vehicle', 'technician'])
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard.index', compact(
            'woToday',
            'woTotal',
            'revenueToday',
            'revenueMonth',
            'waitingApproval',
            'readyPickup',
            'inProgress',
            'days',
            'revenueDaily',
            'statuses',
            'statusCounts',
            'topServices',
            'lowStockParts',
            'recentWorkOrders'
        ));
    }
}
