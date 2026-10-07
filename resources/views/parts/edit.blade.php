@extends('layouts.app')

@section('title', 'Edit Suku Cadang - ' . $part->name)

@section('header')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('parts.show', $part->id) }}" class="btn btn-sm btn-light border">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0 text-dark">Edit Data Suku Cadang</h4>
        <small class="text-muted">Perbarui tarif harga jual, brand, satuan, atau batas minimum stok.</small>
    </div>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <form action="{{ route('parts.update', $part->id) }}" method="POST">
            @csrf
            @method('PUT')

            <x-card title="Form Ubah Sparepart" icon="pencil-square">
                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="part_number" label="Nomor Part" :value="$part->part_number" required icon="barcode" />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="brand" label="Merk / Brand" :value="$part->brand" required />
                    </div>
                </div>

                <x-form.input name="name" label="Nama Sparepart Lengkap" :value="$part->name" required />

                <div class="row">
                    <div class="col-md-6">
                        <x-form.select name="category" label="Kategori" :selected="$part->category" required>
                            <option value="Oli & Cairan">Oli & Cairan</option>
                            <option value="Filter">Filter</option>
                            <option value="Pengereman">Pengereman</option>
                            <option value="Mesin">Mesin & Transmisi</option>
                            <option value="Kelistrikan">Kelistrikan</option>
                            <option value="Kaki-kaki">Kaki-kaki & Suspensi</option>
                            <option value="Lain-lain">Lain-lain</option>
                        </x-form.select>
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="unit" label="Satuan Unit" :value="$part->unit" required />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="cost_price" type="number" label="Harga Beli (Modal)" :value="$part->cost_price" required icon="cash" />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="selling_price" type="number" label="Harga Jual" :value="$part->selling_price" required icon="cash-coin" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="min_stock" type="number" label="Batas Minimum Stok" :value="$part->min_stock" required />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="location" label="Lokasi Rak" :value="$part->location" />
                    </div>
                </div>

                <x-form.input name="supplier" label="Supplier" :value="$part->supplier" />

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('parts.show', $part->id) }}" class="btn btn-light border px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">Perbarui Sparepart</button>
                </div>
            </x-card>
        </form>
    </div>
</div>
@endsection
