<?php

namespace App\Http\Controllers;

use App\Models\WorkshopSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = WorkshopSetting::pluck('value', 'key')->toArray();

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'workshop_name' => 'required|string|max:255',
            'workshop_tagline' => 'nullable|string|max:255',
            'workshop_address' => 'required|string',
            'workshop_phone' => 'required|string',
            'workshop_email' => 'required|email',
            'whatsapp_number' => 'required|string',
            'primary_color' => 'required|string',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'invoice_footer' => 'nullable|string',
        ]);

        foreach ($validated as $key => $val) {
            WorkshopSetting::set($key, (string) $val);
        }

        return back()->with('success', 'Pengaturan bengkel dan tema warna berhasil disimpan!');
    }
}
