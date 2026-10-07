@extends('layouts.app')

@section('title', 'Manajemen Sparepart & Stok')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Gudang & Suku Cadang (Inventory)</h4>
        <p class="text-muted small mb-0">Kelola master sparepart (Mesin, Kaki-kaki, Transmisi, dll.), HPP, harga jual, dan vendor supplier.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('scanner.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-upc-scan"></i> Scanner Gudang
        </a>
        <a href="{{ route('part-categories.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-tags"></i> Kategori
        </a>
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-truck"></i> Supplier
        </a>
        <a href="{{ route('parts.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Tambah Sparepart</span>
        </a>
    </div>
</div>
@endsection

@section('content')

<!-- QUICK CATEGORY PILLS -->
<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="{{ route('parts.index') }}" class="btn btn-sm {{ empty($category) ? 'btn-dark' : 'btn-light border' }} fw-semibold rounded-pill">
        Semua Kategori
    </a>
    @foreach($categories as $catItem)
        <a href="{{ route('parts.index', ['category' => $catItem->name]) }}" class="btn btn-sm {{ $category === $catItem->name ? 'btn-primary' : 'btn-light border text-dark' }} fw-semibold rounded-pill d-inline-flex align-items-center gap-1">
            <i class="bi {{ $catItem->icon ?: 'bi-tag' }}"></i>
            {{ $catItem->name }}
        </a>
    @endforeach
</div>

<!-- Search & Filter Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('parts.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari Part Number, nama, atau brand..." value="{{ $search }}">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="category" class="form-select">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}" {{ $category === $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="supplier_id" class="form-select">
                    <option value="">-- Semua Supplier --</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ ($supplierId == $sup->id) ? 'selected' : '' }}>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="stock_status" class="form-select">
                    <option value="">-- Status Stok --</option>
                    <option value="low" {{ $stockFilter === 'low' ? 'selected' : '' }}>⚠️ Stok Rendah</option>
                    <option value="empty" {{ $stockFilter === 'empty' ? 'selected' : '' }}>🛑 Stok Habis</option>
                </select>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>

            @if($search || $category || $supplierId || $stockFilter)
                <div class="col-auto">
                    <a href="{{ route('parts.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            @endif
        </form>
    </div>
</div>

<x-card>
    @if($parts->isEmpty())
        <x-empty-state title="Tidak Ada Sparepart" description="Belum ada suku cadang yang sesuai dengan kriteria filter Anda." icon="box-seam" />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>Part Number</th>
                        <th>Nama Suku Cadang</th>
                        <th>Kategori & Brand</th>
                        <th>Supplier / Pemasok</th>
                        <th class="text-end">Harga Beli</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-center">Stok Fisik</th>
                        <th>Status</th>
                        <th>Lokasi Rak</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parts as $part)
                        <tr>
                            <td>
                                <a href="{{ route('parts.show', $part->id) }}" class="fw-bold font-monospace text-decoration-none">
                                    {{ $part->part_number }}
                                </a>
                                @if($part->barcode && $part->barcode !== $part->part_number)
                                    <small class="text-muted d-block font-monospace"><i class="bi bi-upc"></i> {{ $part->barcode }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-dark d-block">{{ $part->name }}</span>
                                <small class="text-muted">{{ $part->brand }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $part->category }}</span>
                            </td>
                            <td>
                                @if($part->supplierRelation)
                                    <a href="{{ route('suppliers.show', $part->supplierRelation->id) }}" class="text-decoration-none small text-dark fw-semibold">
                                        <i class="bi bi-truck text-muted me-1"></i>{{ $part->supplierRelation->name }}
                                    </a>
                                @elseif($part->supplier)
                                    <span class="small text-muted"><i class="bi bi-truck me-1"></i>{{ $part->supplier }}</span>
                                @else
                                    <span class="small text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end small text-muted">
                                Rp {{ number_format($part->cost_price, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-semibold text-primary">
                                Rp {{ number_format($part->selling_price, 0, ',', '.') }}
                            </td>
                            <td class="text-center fw-bold">
                                {{ $part->stock }} <small class="text-muted">{{ $part->unit }}</small>
                            </td>
                            <td>
                                @if($part->stock <= 0)
                                    <span class="badge bg-danger">Habis</span>
                                @elseif($part->stock <= $part->min_stock)
                                    <span class="badge bg-warning text-dark">Rendah</span>
                                @else
                                    <span class="badge bg-success">Aman</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-muted border">
                                    <i class="bi bi-geo-alt"></i> {{ $part->location ?? '-' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('parts.show', $part->id) }}" class="btn btn-light border text-primary" title="Detail & Mutasi">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('parts.barcode-print', $part->id) }}" target="_blank" class="btn btn-light border text-secondary" title="Cetak Label Barcode">
                                        <i class="bi bi-upc"></i>
                                    </a>
                                    <a href="{{ route('parts.edit', $part->id) }}" class="btn btn-light border text-dark" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($parts->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $parts->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
</x-card>

@endsection
