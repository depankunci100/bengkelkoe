@extends('layouts.app')

@section('title', 'Edit Customer - ' . $customer->name)

@section('header')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-light border">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0 text-dark">Edit Data Customer</h4>
        <small class="text-muted">Perbarui informasi kontak dan catatan pelanggan.</small>
    </div>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <form action="{{ route('customers.update', $customer->id) }}" method="POST">
            @csrf
            @method('PUT')

            <x-card title="Form Perubahan Data" icon="person-gear">
                <x-form.input name="name" label="Nama Lengkap Pelanggan" :value="$customer->name" required icon="person" />
                <x-form.input name="phone" label="Nomor Handphone / WhatsApp" :value="$customer->phone" required icon="whatsapp" />
                <x-form.input name="email" type="email" label="Alamat Email (Opsional)" :value="$customer->email" icon="envelope" />
                <x-form.textarea name="address" label="Alamat Lengkap" :value="$customer->address" rows="3" />
                <x-form.textarea name="notes" label="Catatan Khusus" :value="$customer->notes" rows="2" />

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteCustomerModal">
                        <i class="bi bi-trash"></i> Hapus Customer
                    </button>
                    <div class="d-flex gap-2">
                        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Perbarui
                        </button>
                    </div>
                </div>
            </x-card>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<x-modal id="deleteCustomerModal" title="Konfirmasi Hapus Customer">
    <p class="text-danger mb-3">
        <i class="bi bi-exclamation-triangle-fill fs-4 me-2 align-middle"></i>
        Apakah Anda yakin ingin menghapus data customer <strong>{{ $customer->name }}</strong>?
    </p>
    <p class="small text-muted mb-4">
        Tindakan ini akan menghapus data kendaraan, riwayat inspeksi, work order, dan transaksi terkait.
    </p>
    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger">Ya, Hapus Data</button>
        </div>
    </form>
</x-modal>
@endsection
