@extends('layouts.app')

@section('title', 'Tambah Supplier')

@section('header')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-light border">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0 text-dark">Tambah Supplier Baru</h4>
        <small class="text-muted">Daftarkan vendor atau distributor pemasok suku cadang bengkel.</small>
    </div>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf

            <x-card title="Informasi Supplier" icon="truck">
                <div class="row">
                    <div class="col-md-4">
                        <x-form.input name="code" label="Kode Supplier" value="{{ old('code', $nextCode) }}" placeholder="SUP-001" required icon="upc" />
                    </div>
                    <div class="col-md-8">
                        <x-form.input name="name" label="Nama Perusahaan / Toko Supplier" placeholder="Contoh: PT Sumber Jaya Otomotif / Toko Onderdil Berkat" required icon="building" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="contact_person" label="Nama Kontak Person (PIC / Sales)" placeholder="Contoh: Pak Anton / Ibu Lina" icon="person" />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="phone" label="No. Handphone / WhatsApp" placeholder="Contoh: 081234567890" icon="telephone" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="email" type="email" label="Alamat Email (Opsional)" placeholder="supplier@domain.com" icon="envelope" />
                    </div>
                    <div class="col-md-6 d-flex align-items-center mt-3 mt-md-0">
                        <div class="form-check form-switch pt-md-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label fw-semibold" for="is_active">Status Supplier Aktif</label>
                            <div class="form-text small text-muted">Supplier aktif dapat dipilih pada pengadaan dan mutasi stok.</div>
                        </div>
                    </div>
                </div>

                <x-form.textarea name="address" label="Alamat Kantor / Gudang Supplier" placeholder="Jl. Raya Pasar Otomotif Blok A No. 10, Surabaya" rows="2" />

                <x-form.textarea name="notes" label="Catatan / Syarat Pembayaran (TOP / Rekening Bank)" placeholder="Contoh: Tempo 30 hari, Rekening BCA 1234567890 a.n PT Sumber Jaya..." rows="2" />

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-light border px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Supplier
                    </button>
                </div>
            </x-card>
        </form>
    </div>
</div>
@endsection
