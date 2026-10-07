@extends('layouts.app')

@section('title', 'Detail Supplier - ' . $supplier->name)

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark">{{ $supplier->name }}</h4>
                <span class="badge bg-dark font-monospace">{{ $supplier->code }}</span>
                @if($supplier->is_active)
                    <span class="badge bg-success">Aktif</span>
                @else
                    <span class="badge bg-secondary">Non-Aktif</span>
                @endif
            </div>
            <small class="text-muted">Kontak PIC: {{ $supplier->contact_person ?? '-' }} &bull; {{ $supplier->phone ?? '-' }}</small>
        </div>
    </div>
    <div class="d-flex gap-2">
        @if($supplier->phone)
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $supplier->phone);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                }
            @endphp
            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="btn btn-success d-inline-flex align-items-center gap-1">
                <i class="bi bi-whatsapp"></i> Hubungi WhatsApp
            </a>
        @endif
        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-pencil"></i> Edit Supplier
        </a>
    </div>
</div>
@endsection

@section('content')

<!-- INFO KARTU SUPPLIER -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Informasi Kontak</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted small" style="width: 110px;">Kode</td>
                        <td class="fw-bold font-monospace">{{ $supplier->code }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">PIC / Sales</td>
                        <td class="fw-semibold text-dark">{{ $supplier->contact_person ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Telepon/WA</td>
                        <td>{{ $supplier->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Email</td>
                        <td>{{ $supplier->email ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-geo-alt text-danger me-2"></i>Alamat & Syarat Pembayaran</h6>
                <div class="mb-2">
                    <small class="text-muted d-block fw-semibold">Alamat Kantor / Gudang:</small>
                    <span class="small text-dark">{{ $supplier->address ?? '-' }}</span>
                </div>
                <div>
                    <small class="text-muted d-block fw-semibold">Catatan / Rekening Pembayaran:</small>
                    <span class="small text-dark">{{ $supplier->notes ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-3">
        <div class="card shadow-sm border-0 h-100 bg-primary bg-opacity-10 border-primary border-opacity-25">
            <div class="card-body p-3 d-flex flex-column justify-content-center text-center">
                <span class="text-muted small text-uppercase fw-bold">Suku Cadang Disuplai</span>
                <h2 class="fw-bold text-primary my-2">{{ $supplier->parts_count }} <small class="fs-6 text-muted">Item</small></h2>
                <a href="{{ route('parts.create') }}" class="btn btn-sm btn-primary mt-2">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Part Baru
                </a>
            </div>
        </div>
    </div>
</div>

<!-- TABS DAFTAR SUKU CADANG & MUTASI PENGADAAN -->
<ul class="nav nav-tabs mb-4 border-bottom" id="supplierTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" id="parts-tab" data-bs-toggle="tab" data-bs-target="#parts-pane" type="button">
            <i class="bi bi-box-seam me-1"></i> Daftar Suku Cadang ({{ $supplier->parts->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-pane" type="button">
            <i class="bi bi-clock-history me-1"></i> Riwayat Pengadaan / Mutasi Masuk ({{ $supplier->stockMovements->count() }})
        </button>
    </li>
</ul>

<div class="tab-content" id="supplierTabsContent">
    <!-- TAB 1: SUKU CADANG -->
    <div class="tab-pane fade show active" id="parts-pane">
        <x-card>
            @if($supplier->parts->isEmpty())
                <x-empty-state title="Belum Ada Suku Cadang" description="Belum ada sparepart yang ditautkan ke supplier ini." icon="box-seam" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>Part Number</th>
                                <th>Nama Suku Cadang</th>
                                <th>Kategori</th>
                                <th class="text-end">Harga Beli (Modal)</th>
                                <th class="text-end">Harga Jual</th>
                                <th class="text-center">Stok</th>
                                <th>Status Stok</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supplier->parts as $part)
                                <tr>
                                    <td>
                                        <a href="{{ route('parts.show', $part->id) }}" class="fw-bold font-monospace text-decoration-none">
                                            {{ $part->part_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark d-block">{{ $part->name }}</span>
                                        <small class="text-muted">{{ $part->brand }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $part->category }}</span>
                                    </td>
                                    <td class="text-end small">
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
                                    <td class="text-end">
                                        <a href="{{ route('parts.show', $part->id) }}" class="btn btn-sm btn-light border">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>

    <!-- TAB 2: RIWAYAT MUTASI MASUK -->
    <div class="tab-pane fade" id="history-pane">
        <x-card>
            @if($supplier->stockMovements->isEmpty())
                <x-empty-state title="Belum Ada Riwayat Mutasi" description="Belum ada transaksi barang masuk dari supplier ini." icon="clock-history" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>Tanggal</th>
                                <th>Suku Cadang</th>
                                <th class="text-center">Jumlah Masuk</th>
                                <th>Harga Beli Batch</th>
                                <th>No. Faktur / Ref</th>
                                <th>Catatan / Keterangan</th>
                                <th>Operator</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supplier->stockMovements as $mov)
                                <tr>
                                    <td class="small">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('parts.show', $mov->part_id) }}" class="fw-bold text-dark text-decoration-none">
                                            {{ $mov->part->name ?? 'Part #' . $mov->part_id }}
                                        </a>
                                        <div class="small font-monospace text-muted">{{ $mov->part->part_number ?? '-' }}</div>
                                    </td>
                                    <td class="text-center fw-bold text-success">
                                        +{{ (float) $mov->quantity }}
                                    </td>
                                    <td class="small fw-semibold text-dark">
                                        @if($mov->cost_price)
                                            Rp {{ number_format($mov->cost_price, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="small font-monospace">
                                        {{ $mov->batch_reference ?? '-' }}
                                    </td>
                                    <td class="small text-muted">{{ $mov->notes }}</td>
                                    <td class="small text-muted">{{ $mov->user->name ?? 'Sistem' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
</div>
@endsection
