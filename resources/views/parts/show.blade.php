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
                <small class="text-muted">
                    Supplier: 
                    @if($part->supplierRelation)
                        <a href="{{ route('suppliers.show', $part->supplierRelation->id) }}" class="text-decoration-none fw-semibold">
                            {{ $part->supplierRelation->name }}
                        </a>
                    @else
                        {{ $part->supplier ?? '-' }}
                    @endif
                </small>
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
                                <th>Harga Beli Batch</th>
                                <th>No. Ref / Supplier</th>
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
                                    <td class="small">
                                        @if($mov->cost_price)
                                            <span class="fw-semibold text-dark">Rp {{ number_format($mov->cost_price, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if($mov->batch_reference)
                                            <span class="font-monospace fw-semibold">{{ $mov->batch_reference }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                        @if($mov->supplier)
                                            <div class="text-muted small"><i class="bi bi-truck me-1"></i>{{ $mov->supplier }}</div>
                                        @endif
                                    </td>
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
<x-modal id="adjustStockModal" title="Penyesuaian Stok (Stock Opname) & Barang Masuk">
    <form action="{{ route('parts.adjust-stock', $part->id) }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Jenis Penyesuaian / Mutasi</label>
            <select name="type" id="mutationType" class="form-select" required>
                <option value="IN" selected>Barang Masuk / Pembelian Baru (Tambah Stok)</option>
                <option value="OUT">Barang Rusak / Kadaluarsa / Hilang (Kurangi Stok)</option>
                <option value="ADJUSTMENT">Koreksi Fisik Nyata (Setel Hasil Stock Opname)</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="quantity" id="quantityLabel" class="form-label fw-semibold">Jumlah Kuantitas Masuk ({{ $part->unit }})</label>
            <input type="number" name="quantity" id="quantity" class="form-control" placeholder="Contoh: 10" min="0" required>
            <div class="form-text text-muted" id="currentStockHelp">
                Stok fisik tercatat di sistem: <strong class="text-dark">{{ $part->stock }} {{ $part->unit }}</strong> &bull; HPP aktif: <strong class="text-dark">Rp {{ number_format($part->cost_price, 0, ',', '.') }}</strong>
            </div>
        </div>

        <!-- SEKSI KHUSUS: BARANG MASUK / HARGA BEDA & BATCH OPNAME -->
        <div id="batchPricingSection" class="card bg-light border p-3 mb-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-calculator text-primary fs-5"></i>
                <h6 class="fw-bold mb-0 text-dark">Kalkulasi HPP & Batch Pembelian Baru</h6>
            </div>
            <p class="small text-muted mb-3">
                Jika barang masuk memiliki harga beli berbeda dari supplier, sistem akan mengkalkulasi HPP (Harga Pokok Penjualan) baru secara otomatis.
            </p>

            <div class="row g-2 mb-2">
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold">Harga Beli Batch Masuk (Rp)</label>
                    <input type="number" name="cost_price" class="form-control form-control-sm" placeholder="Contoh: {{ (int) $part->cost_price }}" min="0">
                    <div class="form-text small text-muted">HPP saat ini: Rp {{ number_format($part->cost_price, 0, ',', '.') }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold">Metode Kalkulasi HPP</label>
                    <select name="cost_calculation_method" class="form-select form-select-sm">
                        <option value="AVERAGE" selected>Moving Average (Rata-rata Tertimbang)</option>
                        <option value="LATEST">Harga Terbaru (Ganti HPP)</option>
                        <option value="KEEP">Pertahankan HPP Lama</option>
                    </select>
                    <div class="form-text small text-muted">Rekomendasi: Moving Average</div>
                </div>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold">Harga Jual Baru (Rp)</label>
                    <input type="number" name="selling_price" class="form-control form-control-sm" placeholder="Rp {{ (int) $part->selling_price }}" min="0">
                    <div class="form-text small text-muted">Opsional jika ada penyesuaian</div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold">No. Faktur / Surat Jalan</label>
                    <input type="text" name="batch_reference" class="form-control form-control-sm" placeholder="Contoh: INV-2026-091">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold">Supplier / Pemasok</label>
                    <select name="supplier_id" class="form-select form-select-sm">
                        <option value="">-- Pilih Supplier --</option>
                        @if(isset($suppliers))
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" {{ ($part->supplier_id == $s->id || $part->supplier == $s->name) ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>

        <x-form.textarea name="notes" label="Alasan Penyesuaian / Catatan Faktur" placeholder="Contoh: Faktur PO-2026-118 barang masuk restock / Opname fisik akhir bulan..." rows="2" required />

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary fw-semibold">
                <i class="bi bi-check2-circle me-1"></i> Simpan Mutasi Stok
            </button>
        </div>
    </form>
</x-modal>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mutationType = document.getElementById('mutationType');
        const quantityLabel = document.getElementById('quantityLabel');
        const batchPricingSection = document.getElementById('batchPricingSection');
        const currentStockHelp = document.getElementById('currentStockHelp');

        if (mutationType) {
            mutationType.addEventListener('change', function () {
                const val = this.value;
                if (val === 'IN') {
                    quantityLabel.textContent = 'Jumlah Kuantitas Masuk ({{ $part->unit }})';
                    batchPricingSection.classList.remove('d-none');
                    currentStockHelp.innerHTML = 'Stok saat ini: <strong>{{ $part->stock }} {{ $part->unit }}</strong> (akan bertambah)';
                } else if (val === 'OUT') {
                    quantityLabel.textContent = 'Jumlah Kuantitas Keluar ({{ $part->unit }})';
                    batchPricingSection.classList.add('d-none');
                    currentStockHelp.innerHTML = 'Stok saat ini: <strong>{{ $part->stock }} {{ $part->unit }}</strong> (akan berkurang)';
                } else if (val === 'ADJUSTMENT') {
                    quantityLabel.textContent = 'Hasil Hitungan Stok Fisik Riil ({{ $part->unit }})';
                    batchPricingSection.classList.remove('d-none');
                    currentStockHelp.innerHTML = 'Stok di sistem saat ini: <strong>{{ $part->stock }} {{ $part->unit }}</strong> (akan disesuaikan ke angka riil)';
                }
            });
        }
    });
</script>

@endsection
