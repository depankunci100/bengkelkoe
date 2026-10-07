@extends('layouts.app')

@section('title', 'Tambah Suku Cadang')

@section('header')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('parts.index') }}" class="btn btn-sm btn-light border">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0 text-dark">Tambah Suku Cadang Baru</h4>
        <small class="text-muted">Daftarkan item sparepart, harga beli, harga jual, dan stok awal.</small>
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
                        <x-form.input name="part_number" label="Nomor Part (SKU/Kode)" placeholder="Contoh: PRT-OIL-03" required icon="barcode" />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="brand" label="Merk / Brand" placeholder="Denso / Toyota Genuine / Shell..." required />
                    </div>
                </div>

                <x-form.input name="name" label="Nama Sparepart Lengkap" placeholder="Contoh: Filter Oli Avanza / Xenia" required />

                <div class="row">
                    <div class="col-md-6">
                        <x-form.select name="category" label="Kategori" required>
                            <option value="Oli & Cairan">Oli & Cairan</option>
                            <option value="Filter">Filter (Oli, Udara, AC, Bensin)</option>
                            <option value="Pengereman">Pengereman (Kampas, Minyak, Disc)</option>
                            <option value="Mesin">Mesin & Transmisi (Busi, Belt, Packing)</option>
                            <option value="Kelistrikan">Kelistrikan (Aki, Bohlam, Relay)</option>
                            <option value="Kaki-kaki">Kaki-kaki & Suspensi (Shock, Tierod)</option>
                            <option value="Lain-lain">Lain-lain</option>
                        </x-form.select>
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="unit" label="Satuan Unit" placeholder="Pcs / Galon / Liter / Set / Botol" value="Pcs" required />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="cost_price" type="number" label="Harga Beli (Modal)" placeholder="Contoh: 35000" required icon="cash" />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="selling_price" type="number" label="Harga Jual ke Pelanggan" placeholder="Contoh: 50000" required icon="cash-coin" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="stock" type="number" label="Jumlah Stok Awal" value="0" min="0" required />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="min_stock" type="number" label="Batas Minimum Stok (Alert)" value="5" min="1" required help="Sistem akan memberi peringatan jika stok di bawah batas ini." />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="location" label="Lokasi Rak / Gudang (Opsional)" placeholder="Contoh: Rak B-2" icon="geo-alt" />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="supplier" label="Nama Supplier / Pemasok (Opsional)" placeholder="PT Sumber Jaya Otomotif" icon="truck" />
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
