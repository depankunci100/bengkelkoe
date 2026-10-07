<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Models\WoTimeline;
use App\Models\WhatsappLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    // ===============================================
    // ADMIN / OWNER APPROVAL DASHBOARD (Sections 15 & 16)
    // ===============================================
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'Pending'); // Pending, Approved, Rejected, Expired

        $query = WorkOrder::with(['customer', 'vehicle', 'technician'])->latest();

        match ($tab) {
            'Pending' => $query->where('status', 'WAITING_APPROVAL'),
            'Approved' => $query->whereIn('status', ['APPROVED', 'IN_PROGRESS', 'QC', 'READY_FOR_PICKUP', 'COMPLETED']),
            'Rejected' => $query->where('status', 'REJECTED'),
            'Expired' => $query->where('status', 'WAITING_APPROVAL')->where('approval_expires_at', '<', Carbon::now()),
            default => $query->where('status', 'WAITING_APPROVAL'),
        };

        $workOrders = $query->paginate(10)->withQueryString();

        $counts = [
            'Pending' => WorkOrder::where('status', 'WAITING_APPROVAL')->count(),
            'Approved' => WorkOrder::whereIn('status', ['APPROVED', 'IN_PROGRESS', 'QC', 'READY_FOR_PICKUP', 'COMPLETED'])->count(),
            'Rejected' => WorkOrder::where('status', 'REJECTED')->count(),
            'Expired' => WorkOrder::where('status', 'WAITING_APPROVAL')->where('approval_expires_at', '<', Carbon::now())->count(),
        ];

        return view('approvals.index', compact('workOrders', 'tab', 'counts'));
    }

    public function show($id)
    {
        $wo = WorkOrder::with([
            'customer',
            'vehicle',
            'technician',
            'items',
            'inspection.items',
            'timelines',
            'whatsappLogs',
        ])->findOrFail($id);

        return view('approvals.show', compact('wo'));
    }

    // ===============================================
    // CUSTOMER PUBLIC APPROVAL PAGE (Section 37)
    // Route: /approval/{token}
    // ===============================================
    public function publicView($token)
    {
        $wo = WorkOrder::with([
            'customer',
            'vehicle',
            'technician',
            'items' => function ($q) {
                $q->with(['service', 'part']);
            },
            'inspection.items',
        ])->where('approval_token', $token)->firstOrFail();

        $isExpired = $wo->approval_expires_at && $wo->approval_expires_at->isPast();

        return view('approvals.customer-portal', compact('wo', 'isExpired'));
    }

    public function submitCustomerDecision(Request $request, $token)
    {
        $wo = WorkOrder::where('approval_token', $token)->firstOrFail();

        $decision = $request->input('decision'); // 'APPROVE_ALL', 'REJECT_ALL', 'PARTIAL'
        $itemDecisions = $request->input('items', []); // [item_id => 'APPROVED'|'REJECTED']
        $customerNotes = $request->input('customer_notes');

        if ($decision === 'APPROVE_ALL') {
            $wo->items()->update(['approval_status' => 'APPROVED']);
            $wo->update([
                'status' => 'APPROVED',
                'approval_status' => 'APPROVED',
                'approved_at' => Carbon::now(),
            ]);

            WoTimeline::create([
                'work_order_id' => $wo->id,
                'title' => 'Pelanggan Menyetujui Seluruh Pekerjaan',
                'description' => 'Disetujui secara digital via Customer Approval Portal.',
                'status' => 'APPROVED',
            ]);

            $message = 'Terima kasih! Anda telah menyetujui seluruh estimasi pekerjaan perbaikan.';
        } elseif ($decision === 'REJECT_ALL') {
            $wo->items()->update(['approval_status' => 'REJECTED']);
            $wo->update([
                'status' => 'REJECTED',
                'approval_status' => 'REJECTED',
            ]);

            WoTimeline::create([
                'work_order_id' => $wo->id,
                'title' => 'Pelanggan Menolak Estimasi',
                'description' => 'Pelanggan memilih untuk tidak melanjutkan perbaikan. Catatan: ' . ($customerNotes ?? '-'),
                'status' => 'REJECTED',
            ]);

            $message = 'Tanggapan Anda telah dicatat. Perbaikan tidak akan dilanjutkan.';
        } else {
            // Partial Approval (Section 18)
            foreach ($itemDecisions as $itemId => $status) {
                $itemStatus = $status === 'APPROVED' ? 'APPROVED' : 'REJECTED';
                WorkOrderItem::where('id', $itemId)->where('work_order_id', $wo->id)
                    ->update(['approval_status' => $itemStatus]);
            }

            // Recalculate totals based on approved items only
            $approvedServices = $wo->items()->where('type', 'SERVICE')->where('approval_status', 'APPROVED')->sum('subtotal');
            $approvedParts = $wo->items()->where('type', 'PART')->where('approval_status', 'APPROVED')->sum('subtotal');
            $newSubtotal = $approvedServices + $approvedParts;
            $newTax = round($newSubtotal * 0.11, 2);
            $newGrand = $newSubtotal + $newTax;

            $wo->update([
                'status' => 'APPROVED',
                'approval_status' => 'PARTIALLY_APPROVED',
                'approved_at' => Carbon::now(),
                'total_services' => $approvedServices,
                'total_parts' => $approvedParts,
                'subtotal' => $newSubtotal,
                'tax' => $newTax,
                'grand_total' => $newGrand,
            ]);

            WoTimeline::create([
                'work_order_id' => $wo->id,
                'title' => 'Pelanggan Menyetujui Sebagian (Partial Approval)',
                'description' => 'Customer menyetujui sebagian item perbaikan. Total estimasi disesuaikan menjadi Rp ' . number_format($newGrand, 0, ',', '.'),
                'status' => 'APPROVED',
            ]);

            $message = 'Terima kasih! Persetujuan item perbaikan Anda telah berhasil dicatat.';
        }

        return redirect()->route('customer.approval', $token)->with('success', $message);
    }
}
