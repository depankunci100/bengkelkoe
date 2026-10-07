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
            <small class="text-muted">Kontak PIC Kantor: {{ $supplier->contact_person ?? '-' }} &bull; {{ $supplier->phone ?? '-' }}</small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#createSalesModal">
            <i class="bi bi-person-plus-fill"></i> Tambah Sales
        </button>
        @if($supplier->phone)
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $supplier->phone);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                }
            @endphp
            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="btn btn-success d-inline-flex align-items-center gap-1">
                <i class="bi bi-whatsapp"></i> Hubungi WA Kantor
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
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Informasi Kantor</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted small" style="width: 110px;">Kode</td>
                        <td class="fw-bold font-monospace">{{ $supplier->code }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">PIC Utama</td>
                        <td class="fw-semibold text-dark">{{ $supplier->contact_person ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Telepon</td>
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

    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-geo-alt text-danger me-2"></i>Alamat & Syarat Pembayaran</h6>
                <div class="mb-2">
                    <small class="text-muted d-block fw-semibold">Alamat Kantor / Gudang:</small>
                    <span class="small text-dark">{{ $supplier->address ?? '-' }}</span>
                </div>
                <div>
                    <small class="text-muted d-block fw-semibold">Catatan / Rekening:</small>
                    <span class="small text-dark">{{ $supplier->notes ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="row g-2 h-100">
            <div class="col-6">
                <div class="card shadow-sm border-0 h-100 bg-primary bg-opacity-10 border-primary border-opacity-25 text-center p-3">
                    <span class="text-muted small text-uppercase fw-bold">Tim Sales</span>
                    <h3 class="fw-bold text-primary my-1">{{ $supplier->sales_count }} <small class="fs-6 text-muted">Orang</small></h3>
                    <small class="text-muted">Sales Representatif</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card shadow-sm border-0 h-100 bg-success bg-opacity-10 border-success border-opacity-25 text-center p-3">
                    <span class="text-muted small text-uppercase fw-bold">Pengiriman Masuk</span>
                    <h3 class="fw-bold text-success my-1">{{ $supplier->deliveries_count ?? $supplier->stockMovements->count() }} <small class="fs-6 text-muted">Batch</small></h3>
                    <small class="text-muted">Total Pengiriman Sales</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABS DAFTAR SUKU CADANG, SALES, HISTORY PENGIRIMAN, DAN RETUR -->
<ul class="nav nav-tabs mb-4 border-bottom" id="supplierTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales-pane" type="button">
            <i class="bi bi-people-fill me-1"></i> Tim Sales Representatif ({{ $supplier->sales->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="deliveries-tab" data-bs-toggle="tab" data-bs-target="#deliveries-pane" type="button">
            <i class="bi bi-truck me-1"></i> History Pengiriman Setiap Sales ke Bengkel ({{ $supplier->stockMovements->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="parts-tab" data-bs-toggle="tab" data-bs-target="#parts-pane" type="button">
            <i class="bi bi-box-seam me-1"></i> Suku Cadang Disuplai ({{ $supplier->parts->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="returns-tab" data-bs-toggle="tab" data-bs-target="#returns-pane" type="button">
            <i class="bi bi-arrow-return-left me-1"></i> Retur Barang & Klaim Sales ({{ $supplier->returns->count() }})
        </button>
    </li>
</ul>

<div class="tab-content" id="supplierTabsContent">
    <!-- TAB 1: TIM SALES -->
    <div class="tab-pane fade show active" id="sales-pane">
        <x-card>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Daftar Sales Representatif Supplier</h6>
                    <small class="text-muted">1 Supplier dapat memiliki beberapa sales untuk mencatat kontak PIC yang membawa pengiriman barang ke bengkel.</small>
                </div>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createSalesModal">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Sales Baru
                </button>
            </div>

            @if($supplier->sales->isEmpty())
                <x-empty-state title="Belum Ada Sales Terdaftar" description="Tambahkan kontak sales dari supplier ini untuk mulai mencatat penerimaan barang." icon="people" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>Nama Sales</th>
                                <th>Wilayah / Area</th>
                                <th>No. Handphone / WhatsApp</th>
                                <th>Email</th>
                                <th class="text-center">Pengiriman ke Bengkel</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supplier->sales as $sale)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark d-block">{{ $sale->name }}</span>
                                        @if($sale->notes)
                                            <small class="text-muted">{{ $sale->notes }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $sale->area ?: 'Umum / Semua Area' }}</span>
                                    </td>
                                    <td>
                                        @if($sale->phone)
                                            @php
                                                $cleanSalesPhone = preg_replace('/[^0-9]/', '', $sale->phone);
                                                if (str_starts_with($cleanSalesPhone, '0')) {
                                                    $cleanSalesPhone = '62' . substr($cleanSalesPhone, 1);
                                                }
                                            @endphp
                                            <a href="https://wa.me/{{ $cleanSalesPhone }}" target="_blank" class="btn btn-sm btn-outline-success py-0 px-2 d-inline-flex align-items-center gap-1 text-decoration-none">
                                                <i class="bi bi-whatsapp"></i> {{ $sale->phone }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $sale->email ?: '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary px-2 py-1 fs-6">{{ $sale->deliveries_count ?? $sale->stockMovements()->where('type', 'IN')->count() }} Pengiriman</span>
                                    </td>
                                    <td>
                                        @if($sale->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Non-Aktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#editSalesModal{{ $sale->id }}" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('suppliers.sales.destroy', [$supplier->id, $sale->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sales ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-light border text-danger" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- MODAL EDIT SALES -->
                                <div class="modal fade" id="editSalesModal{{ $sale->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('suppliers.sales.update', [$supplier->id, $sale->id]) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Sales: {{ $sale->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Nama Sales <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control" value="{{ $sale->name }}" required>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold">No. WhatsApp / HP</label>
                                                            <input type="text" name="phone" class="form-control" value="{{ $sale->phone }}">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold">Wilayah / Area</label>
                                                            <input type="text" name="area" class="form-control" value="{{ $sale->area }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Email Sales</label>
                                                        <input type="email" name="email" class="form-control" value="{{ $sale->email }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Catatan</label>
                                                        <textarea name="notes" class="form-control" rows="2">{{ $sale->notes }}</textarea>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="is_active" id="activeSale{{ $sale->id }}" value="1" {{ $sale->is_active ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="activeSale{{ $sale->id }}">Status Aktif</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>

    <!-- TAB 2: HISTORY PENGIRIMAN SETIAP SALES KE BENGKEL -->
    <div class="tab-pane fade" id="deliveries-pane" x-data="{ selectedSalesFilter: 'ALL' }">
        <x-card>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
                <div>
                    <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-truck text-primary"></i> History Pengiriman Setiap Sales ke Bengkel
                    </h5>
                    <p class="text-muted small mb-0">Catatan faktur surat jalan dan penerimaan barang masuk (Stock IN / Opname) yang dibawa oleh masing-masing sales representatif.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('scanner.index', ['mode' => 'stock_in']) }}" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-upc-scan"></i> Scan / Input Barang Masuk
                    </a>
                </div>
            </div>

            <!-- RINGKASAN PENGIRIMAN PER SALES PIC -->
            @if($supplier->sales->isNotEmpty())
                <div class="mb-4">
                    <h6 class="fw-bold text-secondary mb-2 small text-uppercase">Ringkasan Pengiriman per Sales PIC:</h6>
                    <div class="row g-2">
                        <!-- Card Semua Sales -->
                        <div class="col-6 col-md-3">
                            <div class="card p-2 border text-center transition-all" 
                                 :class="selectedSalesFilter === 'ALL' ? 'border-primary bg-primary bg-opacity-10 shadow-sm' : 'bg-light'"
                                 @click="selectedSalesFilter = 'ALL'" style="cursor: pointer;">
                                <div class="fw-bold text-dark small">Semua Sales</div>
                                <div class="h5 fw-bold text-primary mb-0">{{ $supplier->stockMovements->count() }} <small class="fs-6 text-muted">Batch</small></div>
                                <small class="text-muted">Total Seluruh Pengiriman</small>
                            </div>
                        </div>
                        @foreach($supplier->sales as $sls)
                            @php
                                $slsDeliveries = $supplier->stockMovements->where('supplier_sales_id', $sls->id);
                                $totalUnits = $slsDeliveries->sum('quantity');
                            @endphp
                            <div class="col-6 col-md-3">
                                <div class="card p-2 border text-center transition-all" 
                                     :class="selectedSalesFilter === '{{ $sls->id }}' ? 'border-primary bg-primary bg-opacity-10 shadow-sm' : 'bg-light'"
                                     @click="selectedSalesFilter = '{{ $sls->id }}'" style="cursor: pointer;">
                                    <div class="fw-bold text-dark small text-truncate" title="{{ $sls->name }}">{{ $sls->name }}</div>
                                    <div class="h5 fw-bold text-success mb-0">{{ $slsDeliveries->count() }} <small class="fs-6 text-muted">Batch</small></div>
                                    <small class="text-muted">{{ (float)$totalUnits }} Unit Suku Cadang</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- TABEL DAFTAR PENGIRIMAN SALES KE BENGKEL -->
            @if($supplier->stockMovements->isEmpty())
                <x-empty-state title="Belum Ada Riwayat Pengiriman" description="Belum ada transaksi surat jalan pengiriman barang masuk yang tercatat dari supplier atau sales ini." icon="truck" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>Tanggal & Waktu</th>
                                <th>Sales Penanggung Jawab</th>
                                <th>No. Surat Jalan / Batch</th>
                                <th>Suku Cadang</th>
                                <th class="text-center">Jumlah Masuk</th>
                                <th class="text-end">HPP Satuan</th>
                                <th class="text-end">Total Nilai</th>
                                <th>Penerima Gudang</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supplier->stockMovements as $mov)
                                <tr x-show="selectedSalesFilter === 'ALL' || selectedSalesFilter === '{{ $mov->supplier_sales_id }}'">
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $mov->created_at->format('d/m/Y') }}</div>
                                        <small class="text-muted font-monospace">{{ $mov->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        @if($mov->supplierSales)
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 fw-semibold">
                                                    <i class="bi bi-person-fill me-1"></i>{{ $mov->supplierSales->name }}
                                                </span>
                                            </div>
                                            @if($mov->supplierSales->phone)
                                                @php
                                                    $cleanSalesPhone = preg_replace('/[^0-9]/', '', $mov->supplierSales->phone);
                                                    if (str_starts_with($cleanSalesPhone, '0')) {
                                                        $cleanSalesPhone = '62' . substr($cleanSalesPhone, 1);
                                                    }
                                                @endphp
                                                <a href="https://wa.me/{{ $cleanSalesPhone }}" target="_blank" class="small text-success text-decoration-none mt-1 d-inline-block">
                                                    <i class="bi bi-whatsapp me-1"></i>{{ $mov->supplierSales->phone }}
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted small">Tanpa Sales Spesifik</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace fs-7">
                                            {{ $mov->batch_reference ?: 'PO-LOKAL' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($mov->part)
                                            <a href="{{ route('parts.show', $mov->part->id) }}" class="fw-bold text-dark text-decoration-none d-block">
                                                {{ $mov->part->name }}
                                            </a>
                                            <small class="text-muted font-monospace">{{ $mov->part->part_number }} &bull; {{ $mov->part->brand }}</small>
                                        @else
                                            <span class="text-muted">Part ID #{{ $mov->part_id }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold text-success fs-6">
                                        +{{ (float) $mov->quantity }} <small class="text-muted">{{ $mov->part->unit ?? 'Pcs' }}</small>
                                    </td>
                                    <td class="text-end small">
                                        @if($mov->cost_price)
                                            Rp {{ number_format($mov->cost_price, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        @php
                                            $totalCost = $mov->total_cost ?: (($mov->cost_price ?? 0) * $mov->quantity);
                                        @endphp
                                        Rp {{ number_format($totalCost, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <small class="text-muted"><i class="bi bi-person me-1"></i>{{ $mov->user->name ?? 'Gudang' }}</small>
                                        @if($mov->notes)
                                            <small class="text-muted d-block text-truncate" style="max-width: 150px;" title="{{ $mov->notes }}">{{ $mov->notes }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            @if($mov->part)
                                                <a href="{{ route('supplier-returns.create', ['movement_id' => $mov->id, 'part_id' => $mov->part->id]) }}" 
                                                   class="btn btn-outline-danger btn-sm" title="Retur Barang Rusak dari Batch Ini">
                                                    <i class="bi bi-arrow-return-left me-1"></i> Retur
                                                </a>
                                                <a href="{{ route('parts.show', $mov->part->id) }}" class="btn btn-light border btn-sm" title="Lihat Detail Part">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>

    <!-- TAB 3: SUKU CADANG DISUPLAI -->
    <div class="tab-pane fade" id="parts-pane">
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

    <!-- TAB 4: RETUR BARANG & KLAIM SALES -->
    <div class="tab-pane fade" id="returns-pane">
        <x-card>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-arrow-return-left text-danger me-1"></i> Riwayat Pengembalian Barang Rusak ke Supplier Ini</h6>
                <a href="{{ route('supplier-returns.create') }}" class="btn btn-sm btn-danger">
                    <i class="bi bi-plus-lg me-1"></i> Buat Faktur Retur Baru
                </a>
            </div>

            @if($supplier->returns->isEmpty())
                <x-empty-state title="Belum Ada Retur Barang" description="Tidak ada catatan pengembalian barang rusak atau cacat ke supplier ini." icon="check2-circle" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>No. Faktur Retur</th>
                                <th>Tanggal</th>
                                <th>Sparepart</th>
                                <th>Sales Penanggung Jawab</th>
                                <th>No. Batch / SJ</th>
                                <th class="text-center">Qty Rusak</th>
                                <th class="text-end">Nilai Retur</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supplier->returns as $ret)
                                <tr>
                                    <td>
                                        <a href="{{ route('supplier-returns.show', $ret->id) }}" class="fw-bold font-monospace text-decoration-none">
                                            {{ $ret->return_number }}
                                        </a>
                                    </td>
                                    <td class="small text-muted">{{ $ret->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="fw-bold text-dark d-block">{{ $ret->part->name }}</span>
                                        <small class="text-muted font-monospace">{{ $ret->part->part_number }}</small>
                                    </td>
                                    <td>
                                        @if($ret->supplierSales)
                                            <span class="badge bg-info-subtle text-info border font-monospace">
                                                <i class="bi bi-person-fill"></i> {{ $ret->supplierSales->name }}
                                            </span>
                                            @if($ret->supplierSales->phone)
                                                <small class="text-muted d-block mt-1">{{ $ret->supplierSales->phone }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="small font-monospace">{{ $ret->batch_reference ?? '-' }}</td>
                                    <td class="text-center fw-bold text-danger">{{ (float)$ret->quantity }} {{ $ret->part->unit }}</td>
                                    <td class="text-end fw-bold text-dark small">Rp {{ number_format($ret->total_amount, 0, ',', '.') }}</td>
                                    <td>
                                        @php $b = $ret->status_badge; @endphp
                                        <span class="badge {{ $b['class'] }}">{{ $b['label'] }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('supplier-returns.show', $ret->id) }}" class="btn btn-sm btn-light border text-primary" title="Lihat">
                                            <i class="bi bi-eye"></i>
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
</div>

<!-- MODAL TAMBAH SALES BARU -->
<div class="modal fade" id="createSalesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('suppliers.sales.store', $supplier->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Sales Baru: {{ $supplier->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap Sales <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Denny Setiawan" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">No. WhatsApp / HP</label>
                            <input type="text" name="phone" class="form-control" placeholder="081234567890">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Wilayah / Divisi Area</label>
                            <input type="text" name="area" class="form-control" placeholder="Contoh: Surabaya Barat / Fast Moving">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Sales (Opsional)</label>
                        <input type="email" name="email" class="form-control" placeholder="sales@domain.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan Tambahan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan jam kunjungan / jadwal rutin toko..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Sales</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
