@extends('layouts.app')

@section('title', 'Detail Suku Cadang - ' . $part->name)

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('parts.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark">{{ $part->name }}</h4>
                <span class="badge bg-dark font-monospace">{{ $part->part_number }}</span>
            </div>
            <small class="text-muted">Brand: {{ $part->brand }} &bull; Kategori: {{ $part->category }}</small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-warning text-dark fw-bold d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
            <i class="bi bi-arrow-left-right"></i> Penyesuaian Stok (Stock Opname)
        </button>
        <a href="{{ route('parts.edit', $part->id) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-pencil"></i> Edit Item
        </a>
    </div>
</div>
@endsection

@section('content')

<!-- KPI KARTU STOK -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-bold">Stok Saat Ini</span>
                <h3 class="fw-bold text-dark my-1">{{ $part->stock }} <small class="fs-6 text-muted">{{ $part->unit }}</small></h3>
                @if($part->stock <= 0)
                    <span class="badge bg-danger">Habis</span>
                @elseif($part->stock <= $part->min_stock)
                    <span class="badge bg-warning text-dark">Stok Rendah (Batas: {{ $part->min_stock }})</span>
                @else
                    <span class="badge bg-success">Stok Aman</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-bold">Harga Beli (Modal)</span>
                <h4 class="fw-bold text-dark my-1">Rp {{ number_format($part->cost_price, 0, ',', '.') }}</h4>
                <small class="text-muted">Per {{ $part->unit }}</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-bold">Harga Jual Pelanggan</span>
                <h4 class="fw-bold text-primary my-1">Rp {{ number_format($part->selling_price, 0, ',', '.') }}</h4>
                <small class="text-success fw-semibold">Margin: Rp {{ number_format($part->selling_price - $part->cost_price, 0, ',', '.') }}</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-bold">Lokasi Gudang / Rak</span>
                <h4 class="fw-bold text-dark my-1">{{ $part->location ?? '-' }}</h4>
                <small class="text-muted">Supplier: {{ $part->supplier ?? '-' }}</small>
            </div>
        </div>
    </div>
</div>

<!-- TABS (Section 21) -->
<ul class="nav nav-tabs mb-4 border-bottom" id="partTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" id="movement-tab" data-bs-toggle="tab" data-bs-target="#movement-pane" type="button">
            <i class="bi bi-clock-history me-1"></i> Riwayat Mutasi Stok ({{ $part->movements->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-pane" type="button">
            <i class="bi bi-info-circle me-1"></i> Informasi Spesifikasi
        </button>
    </li>
</ul>

<div class="tab-content" id="partTabsContent">
    <!-- TAB 1: MUTASI STOK -->
    <div class="tab-pane fade show active" id="movement-pane">
        <x-card>
            @if($part->movements->isEmpty())
                <x-empty-state title="Belum Ada Riwayat Mutasi" description="Belum ada transaksi keluar masuk stok untuk item suku cadang ini." icon="boxes" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>Tanggal & Waktu</th>
                                <th>Tipe Mutasi</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-center">Stok Sebelum</th>
                                <th class="text-center">Stok Sesudah</th>
                                <th>Keterangan / No. WO</th>
                                <th>Operator</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($part->movements as $mov)
                                <tr>
                                    <td class="small">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($mov->type === 'IN')
                                            <span class="badge bg-success"><i class="bi bi-arrow-down-left"></i> Masuk (IN)</span>
                                        @elseif($mov->type === 'OUT')
                                            <span class="badge bg-danger"><i class="bi bi-arrow-up-right"></i> Keluar (OUT)</span>
                                        @else
                                            <span class="badge bg-warning text-dark"><i class="bi bi-arrow-left-right"></i> Penyesuaian</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">{{ (float) $mov->quantity }}</td>
                                    <td class="text-center text-muted">{{ (float) $mov->before_stock }}</td>
                                    <td class="text-center fw-bold text-dark">{{ (float) $mov->after_stock }}</td>
                                    <td class="small text-dark">
                                        {{ $mov->notes }}
                                        @if($mov->workOrder)
                                            <a href="{{ route('work-orders.show', $mov->work_order_id) }}" class="ms-1 text-decoration-none">
                                                ({{ $mov->workOrder->wo_number }})
                                            </a>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $mov->user->name ?? 'Sistem' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>

    <!-- TAB 2: INFORMASI -->
    <div class="tab-pane fade" id="info-pane">
        <div class="row">
            <div class="col-12 col-md-6">
                <x-card title="Detail Spesifikasi" icon="sliders">
                    <table class="table table-borderless mb-0">
                        <tr><td class="text-muted small" style="width: 150px;">Part Number</td><td class="fw-bold font-monospace">{{ $part->part_number }}</td></tr>
                        <tr><td class="text-muted small">Nama Barang</td><td class="fw-bold">{{ $part->name }}</td></tr>
                        <tr><td class="text-muted small">Brand / Merk</td><td>{{ $part->brand }}</td></tr>
                        <tr><td class="text-muted small">Kategori</td><td><span class="badge bg-light text-dark border">{{ $part->category }}</span></td></tr>
                        <tr><td class="text-muted small">Satuan Unit</td><td>{{ $part->unit }}</td></tr>
                        <tr><td class="text-muted small">Batas Minimum Stok</td><td>{{ $part->min_stock }} {{ $part->unit }}</td></tr>
                        <tr><td class="text-muted small">Supplier Pemasok</td><td>{{ $part->supplier ?? '-' }}</td></tr>
                    </table>
                </x-card>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PENYESUAIAN STOK (STOCK OPNAME) -->
<x-modal id="adjustStockModal" title="Penyesuaian Stok (Stock Opname)">
    <form action="{{ route('parts.adjust-stock', $part->id) }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Jenis Penyesuaian</label>
            <select name="type" class="form-select" required>
                <option value="IN">Barang Masuk / Pembelian Baru (Tambah Stok)</option>
                <option value="OUT">Barang Rusak / Kadaluarsa / Hilang (Kurangi Stok)</option>
                <option value="ADJUSTMENT">Koreksi Fisik Nyata (Setel Stok Baru)</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label fw-semibold">Jumlah Kuantitas ({{ $part->unit }})</label>
            <input type="number" name="quantity" id="quantity" class="form-control" placeholder="Contoh: 10" min="1" required>
            <div class="form-text text-muted">Stok fisik sistem saat ini: <strong>{{ $part->stock }} {{ $part->unit }}</strong></div>
        </div>

        <x-form.textarea name="notes" label="Alasan Penyesuaian" placeholder="Contoh: Pembelian faktur PO-1234 / Hasil Stock Opname bulanan..." rows="3" required />

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Mutasi Stok</button>
        </div>
    </form>
</x-modal>

@endsection
