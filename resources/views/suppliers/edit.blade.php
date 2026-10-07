@extends('layouts.app')

@section('title', 'Edit Supplier - ' . $supplier->name)

@section('header')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-sm btn-light border">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0 text-dark">Edit Supplier: {{ $supplier->name }}</h4>
        <small class="text-muted">Perbarui informasi kontak, alamat, atau syarat pembayaran pemasok.</small>
    </div>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
            @csrf
            @method('PUT')

            <x-card title="Informasi Supplier" icon="truck">
                <div class="row">
                    <div class="col-md-4">
                        <x-form.input name="code" label="Kode Supplier" value="{{ old('code', $supplier->code) }}" required icon="upc" />
                    </div>
                    <div class="col-md-8">
                        <x-form.input name="name" label="Nama Perusahaan / Toko Supplier" value="{{ old('name', $supplier->name) }}" required icon="building" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="contact_person" label="Nama Kontak Person (PIC / Sales)" value="{{ old('contact_person', $supplier->contact_person) }}" icon="person" />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="phone" label="No. Handphone / WhatsApp" value="{{ old('phone', $supplier->phone) }}" icon="telephone" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="email" type="email" label="Alamat Email (Opsional)" value="{{ old('email', $supplier->email) }}" icon="envelope" />
                    </div>
                    <div class="col-md-6 d-flex align-items-center mt-3 mt-md-0">
                        <div class="form-check form-switch pt-md-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_active">Status Supplier Aktif</label>
                            <div class="form-text small text-muted">Centang untuk mengaktifkan supplier ini.</div>
                        </div>
                    </div>
                </div>

                <x-form.textarea name="address" label="Alamat Kantor / Gudang Supplier" value="{{ old('address', $supplier->address) }}" rows="2" />

                <x-form.textarea name="notes" label="Catatan / Syarat Pembayaran (TOP / Rekening Bank)" value="{{ old('notes', $supplier->notes) }}" rows="2" />

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-light border px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </x-card>
        </form>
    </div>
</div>
@endsection
