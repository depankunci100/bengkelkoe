@extends('layouts.app')

@section('title', 'Faktur Pengembalian Barang ke Supplier')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Faktur Pengembalian Barang ke Supplier (Retur)</h4>
        <p class="text-muted small mb-0">Kelola barang rusak/cacat per batch, lacak kontak sales penanggung jawab, dan terbitkan nota retur resmi.</p>
    </div>
    <div>
        <a href="{{ route('supplier-returns.create') }}" class="btn btn-danger d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> Buat Faktur Retur Baru
        </a>
    </div>
</div>
@endsection

@section('content')

<!-- Filter & Pencarian -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('supplier-returns.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari No. Retur, sparepart, supplier, sales, no batch..." value="{{ $search }}">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="status" class="form-select">
                    <option value="ALL">-- Semua Status Retur --</option>
                    <option value="PENDING" {{ $status === 'PENDING' ? 'selected' : '' }}>🟡 Diajukan ke Sales</option>
                    <option value="ACCEPTED" {{ $status === 'ACCEPTED' ? 'selected' : '' }}>🔵 Dikonfirmasi Sales</option>
                    <option value="COMPLETED" {{ $status === 'COMPLETED' ? 'selected' : '' }}>🟢 Selesai (Tukar/Refund)</option>
                    <option value="REJECTED" {{ $status === 'REJECTED' ? 'selected' : '' }}>🔴 Ditolak Supplier</option>
                </select>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            </div>

            @if($search || $status !== 'ALL')
                <div class="col-auto">
                    <a href="{{ route('supplier-returns.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            @endif
        </form>
    </div>
</div>

<x-card>
    @if($returns->isEmpty())
        <x-empty-state 
            title="Belum Ada Faktur Pengembalian Barang" 
            description="Jika terdapat sparepart yang cacat atau rusak pada batch tertentu, terbitkan faktur pengembalian untuk menghubungi sales supplier." 
            icon="arrow-return-left" />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>No. Faktur Retur</th>
                        <th>Tanggal</th>
                        <th>Sparepart / Part No</th>
                        <th>Supplier & Sales Penanggung Jawab</th>
                        <th>Batch / Surat Jalan</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Total Nilai</th>
                        <th>Penyelesaian</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($returns as $ret)
                        <tr>
                            <td>
                                <a href="{{ route('supplier-returns.show', $ret->id) }}" class="fw-bold font-monospace text-decoration-none">
                                    {{ $ret->return_number }}
                                </a>
                            </td>
                            <td class="small text-muted">{{ $ret->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('parts.show', $ret->part_id) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                    {{ $ret->part->name }}
                                </a>
                                <small class="text-muted font-monospace">{{ $ret->part->part_number }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small">{{ $ret->supplier->name }}</div>
                                @if($ret->supplierSales)
                                    <div class="d-flex align-items-center gap-1 mt-1">
                                        <span class="badge bg-info-subtle text-info border font-monospace" style="font-size: 0.72rem;">
                                            <i class="bi bi-person-fill"></i> {{ $ret->supplierSales->name }}
                                        </span>
                                        @if($ret->supplierSales->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $ret->supplierSales->phone) }}?text=Halo%20{{ urlencode($ret->supplierSales->name) }}%2C%20kami%20dari%20bengkel%20ingin%20konfirmasi%20retur%20barang%20rusak%20{{ urlencode($ret->return_number) }}" 
                                               target="_blank" 
                                               class="btn btn-xs btn-outline-success p-0 px-1" 
                                               title="Chat WhatsApp Sales">
                                                <i class="bi bi-whatsapp"></i>
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <small class="text-muted fst-italic" style="font-size: 0.72rem;">PIC Umum</small>
                                @endif
                            </td>
                            <td class="small font-monospace">
                                {{ $ret->batch_reference ?? '-' }}
                            </td>
                            <td class="text-center fw-bold text-danger">
                                {{ (float)$ret->quantity }} {{ $ret->part->unit }}
                            </td>
                            <td class="text-end fw-bold text-dark small">
                                Rp {{ number_format($ret->total_amount, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border small">
                                    {{ $ret->settlement_type === 'REPLACEMENT' ? 'Tukar Unit' : 'Refund' }}
                                </span>
                            </td>
                            <td>
                                @php $badge = $ret->status_badge; @endphp
                                <span class="badge {{ $badge['class'] }}">
                                    <i class="bi {{ $badge['icon'] }} me-1"></i>{{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('supplier-returns.show', $ret->id) }}" class="btn btn-light border text-primary" title="Lihat Faktur">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('supplier-returns.print', $ret->id) }}" target="_blank" class="btn btn-light border text-dark" title="Cetak Surat Retur">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $returns->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
</x-card>

@endsection
