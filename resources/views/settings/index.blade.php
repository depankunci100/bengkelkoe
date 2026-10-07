@extends('layouts.app')

@section('title', 'Pengaturan Bengkel & Sistem')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Pengaturan Bengkel</h4>
        <p class="text-muted small mb-0">Konfigurasi identitas bengkel, kontak WhatsApp, tarif pajak, dan warna tema aplikasi.</p>
    </div>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <form action="{{ route('settings.update') }}" method="POST">
            @csrf

            <x-card title="Identitas & Branding Bengkel" icon="sliders" class="mb-4">
                <div class="row">
                    <div class="col-md-8">
                        <x-form.input name="workshop_name" label="Nama Bengkel" :value="$settings['workshop_name'] ?? 'SIM BENGKEL AUTO SERVICE'" required icon="buildings" />
                    </div>
                    <div class="col-md-4">
                        <x-form.input name="primary_color" type="color" label="Warna Tema Utama" :value="$settings['primary_color'] ?? '#0d6efd'" required help="Warna primer Bootstrap tombol & navbar." />
                    </div>
                </div>

                <x-form.input name="workshop_tagline" label="Tagline / Slogan Bengkel" :value="$settings['workshop_tagline'] ?? 'Bengkel Mobil Profesional & Terpercaya'" />

                <x-form.textarea name="workshop_address" label="Alamat Fisik Bengkel" :value="$settings['workshop_address'] ?? 'Jl. Raya Otomotif No. 88, Surabaya'" rows="2" required />

                <div class="row">
                    <div class="col-md-4">
                        <x-form.input name="workshop_phone" label="No. Telepon Hotline" :value="$settings['workshop_phone'] ?? '031-8976543'" required icon="telephone" />
                    </div>
                    <div class="col-md-4">
                        <x-form.input name="whatsapp_number" label="No. WhatsApp Gateway" :value="$settings['whatsapp_number'] ?? '081234567890'" required icon="whatsapp" />
                    </div>
                    <div class="col-md-4">
                        <x-form.input name="workshop_email" type="email" label="Email Bengkel" :value="$settings['workshop_email'] ?? 'admin@simbengkel.com'" required icon="envelope" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <x-form.input name="tax_rate" type="number" label="Tarif Pajak PPN (%)" :value="$settings['tax_rate'] ?? '11'" required help="Contoh: 11 untuk PPN 11%." />
                    </div>
                </div>

                <x-form.textarea name="invoice_footer" label="Catatan / Syarat Garansi di Footer Faktur" :value="$settings['invoice_footer'] ?? 'Terima kasih atas kunjungan Anda. Garansi servis pengerjaan 14 hari atau 1.000 KM.'" rows="2" />

                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-save"></i> Simpan Konfigurasi
                    </button>
                </div>
            </x-card>
        </form>
    </div>
</div>
@endsection
