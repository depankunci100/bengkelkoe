@extends('layouts.app')

@section('title', 'Tambah Suku Cadang')

@section('header')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('parts.index') }}" class="btn btn-sm btn-light border">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0 text-dark">Tambah Suku Cadang Baru</h4>
        <small class="text-muted">Daftarkan item sparepart (Mesin, Kaki-kaki, Transmisi, dll.), modal HPP, harga jual, dan supplier.</small>
    </div>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <form action="{{ route('parts.store') }}" method="POST">
            @csrf

            <x-card title="Informasi Sparepart" icon="box-seam">
                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="part_number" label="Nomor Part (SKU/Kode)" placeholder="Contoh: PRT-SUS-01 / PRT-TRN-01" required icon="barcode" />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="brand" label="Merk / Brand" placeholder="Kayaba / 555 / Aisin / Denso / Toyota..." required />
                    </div>
                </div>

                <x-form.input name="name" label="Nama Sparepart Lengkap" placeholder="Contoh: Shockbreaker Depan Avanza / Kampas Kopling Aisin" required />

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kategori Sparepart <span class="text-danger">*</span></label>
                            <select name="part_category_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted">Contoh: Kaki-Kaki, Mesin, Transmisi, Rem, dll.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="unit" label="Satuan Unit" placeholder="Pcs / Set / Galon / Liter / Botol" value="Pcs" required />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="cost_price" type="number" label="Harga Beli (Modal HPP)" placeholder="Contoh: 185000" required icon="cash" />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="selling_price" type="number" label="Harga Jual ke Pelanggan" placeholder="Contoh: 260000" required icon="cash-coin" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="stock" type="number" label="Jumlah Stok Awal" value="0" min="0" required />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="min_stock" type="number" label="Batas Minimum Stok (Alert)" value="3" min="1" required help="Sistem akan memberi peringatan jika stok di bawah batas ini." />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Supplier / Pemasok</label>
                            <select name="supplier_id" class="form-select">
                                <option value="">-- Pilih Supplier Master --</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }} ({{ $sup->code }})</option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted">
                                Tidak ada di daftar? <a href="{{ route('suppliers.create') }}" target="_blank" class="text-decoration-none">Tambah Supplier Baru</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="location" label="Lokasi Rak / Gudang" placeholder="Contoh: Rak Kaki-kaki B-1" icon="geo-alt" />
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('parts.index') }}" class="btn btn-light border px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Sparepart
                    </button>
                </div>
            </x-card>
        </form>
    </div>
</div>
@endsection
