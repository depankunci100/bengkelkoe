<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\WorkshopSetting;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'ALL');
        $search = $request->query('search');

        $query = Invoice::with(['customer', 'workOrder.vehicle', 'cashier'])->latest();

        if ($status !== 'ALL') {
            $query->where('payment_status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('workOrder.vehicle', function ($v) use ($search) {
                        $v->where('plate_number', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->paginate(10)->withQueryString();

        return view('invoices.index', compact('invoices', 'status', 'search'));
    }

    public function show($id)
    {
        $invoice = Invoice::with([
            'customer',
            'workOrder.vehicle',
            'workOrder.items.service',
            'workOrder.items.part',
            'payments.cashier',
            'cashier'
        ])->findOrFail($id);

        return view('invoices.show', compact('invoice'));
    }

    public function print($id)
    {
        $invoice = Invoice::with([
            'customer',
            'workOrder.vehicle',
            'workOrder.items.service',
            'workOrder.items.part',
            'payments.cashier',
            'cashier'
        ])->findOrFail($id);

        $settings = [
            'name' => WorkshopSetting::get('workshop_name', 'SIM BENGKEL AUTO SERVICE'),
            'address' => WorkshopSetting::get('workshop_address', 'Jl. Raya Otomotif No. 88, Surabaya'),
            'phone' => WorkshopSetting::get('workshop_phone', '031-8976543'),
            'email' => WorkshopSetting::get('workshop_email', 'info@simbengkel.com'),
            'footer' => WorkshopSetting::get('invoice_footer', 'Terima kasih atas kunjungan Anda. Garansi servis 14 hari atau 1.000 KM.'),
        ];

        return view('invoices.print', compact('invoice', 'settings'));
    }
}
