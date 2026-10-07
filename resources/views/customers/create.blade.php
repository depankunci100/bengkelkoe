@extends('layouts.app')

@section('title', 'Tambah Customer')

@section('header')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-light border">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0 text-dark">Tambah Customer Baru</h4>
        <small class="text-muted">Daftarkan pelanggan baru dan unit kendaraan pertamanya.</small>
    </div>
</div>
@endsection

@section('content')
<form action="{{ route('customers.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        <!-- Kolom Data Pelanggan -->
        <div class="col-12 col-lg-6">
            <x-card title="Informasi Pelanggan" icon="person-lines-fill">
                <x-form.input name="name" label="Nama Lengkap Pelanggan" placeholder="Contoh: Budi Santoso" required icon="person" />
                <x-form.input name="phone" label="Nomor Handphone / WhatsApp" placeholder="Contoh: 081234567890" required icon="whatsapp" help="Nomor ini akan digunakan untuk pengiriman approval & invoice." />
                <x-form.input name="email" type="email" label="Alamat Email (Opsional)" placeholder="Contoh: budi@gmail.com" icon="envelope" />
                <x-form.textarea name="address" label="Alamat Lengkap" placeholder="Alamat domisili pelanggan..." rows="2" />
                <x-form.textarea name="notes" label="Catatan Khusus (Opsional)" placeholder="Misal: langganan korporat, minta nota rangkap 2, dll." rows="2" />
            </x-card>
        </div>

        <!-- Kolom Registrasi Kendaraan Pertama (Opsional/Direkomendasikan) -->
        <div class="col-12 col-lg-6">
            <x-card title="Registrasi Kendaraan (Opsional)" icon="car-front-fill" subtitle="Dapat ditambahkan sekarang atau nanti">
                <x-form.input name="plate_number" label="Nomor Polisi (Plat Nomor)" placeholder="Contoh: L 1234 AB" icon="card-text" />
                
                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="brand" label="Merk Mobil" placeholder="Toyota / Honda / Daihatsu..." />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="model" label="Tipe / Model" placeholder="Avanza 1.3 G / Brio / Xpander..." />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="year" type="number" label="Tahun Pembuatan" placeholder="Contoh: 2021" />
                    </div>
                    <div class="col-md-6">
                        <x-form.select name="transmission" label="Transmisi">
                            <option value="Automatic">Automatic (AT/CVT)</option>
                            <option value="Manual">Manual (MT)</option>
                        </x-form.select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="color" label="Warna Kendaraan" placeholder="Putih / Hitam / Silver..." />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="odometer" type="number" label="Odometer Terakhir (KM)" placeholder="Contoh: 45000" />
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-end gap-2">
        <a href="{{ route('customers.index') }}" class="btn btn-light border px-4">Batal</a>
        <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
            <i class="bi bi-save"></i> Simpan Data Customer
        </button>
    </div>
</form>
@endsection
