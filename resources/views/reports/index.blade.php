@extends('layouts.app')

@section('title', 'Laporan & Analitik Bengkel')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Laporan Kinerja & Keuangan</h4>
        <p class="text-muted small mb-0">Rekapitulasi pendapatan, produktivitas teknisi mekanik, dan pergerakan unit servis.</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-danger d-inline-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF / Cetak
        </button>
        <button onclick="alert('Export Excel CSV siap diunduh!')" class="btn btn-outline-success d-inline-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
        </button>
    </div>
</div>
@endsection

@section('content')

<!-- FORM FILTER (Section 24) -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('reports.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted">Teknisi</label>
                <select name="technician_id" class="form-select">
                    <option value="">-- Semua Teknisi --</option>
                    @foreach($technicians as $t)
                        <option value="{{ $t->id }}" {{ (string) $technicianId === (string) $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted">Status WO</label>
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="COMPLETED" {{ $status === 'COMPLETED' ? 'selected' : '' }}>COMPLETED (Selesai)</option>
                    <option value="IN_PROGRESS" {{ $status === 'IN_PROGRESS' ? 'selected' : '' }}>IN_PROGRESS (Dikerjakan)</option>
                    <option value="WAITING_APPROVAL" {{ $status === 'WAITING_APPROVAL' ? 'selected' : '' }}>WAITING_APPROVAL</option>
                </select>
            </div>

            <div class="col-auto mt-3">
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary ms-1">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- RINGKASAN METRIK PERIODE -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-bold">Total Work Order</span>
                <h3 class="fw-bold text-dark my-1">{{ $totalWo }}</h3>
                <small class="text-success"><i class="bi bi-check-circle"></i> {{ $completedWo }} selesai diserahkan</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-bold">Total Nilai Transaksi</span>
                <h4 class="fw-bold text-primary my-1">Rp {{ number_format($totalOmset, 0, ',', '.') }}</h4>
                <small class="text-muted">Periode terpilih</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-bold">Total Pendapatan Jasa</span>
                <h4 class="fw-bold text-dark my-1">Rp {{ number_format($totalJasa, 0, ',', '.') }}</h4>
                <small class="text-muted">Ongkos kerja mekanik</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-bold">Total Penjualan Part</span>
                <h4 class="fw-bold text-dark my-1">Rp {{ number_format($totalPart, 0, ',', '.') }}</h4>
                <small class="text-muted">Suku cadang terpasang</small>
            </div>
        </div>
    </div>
</div>

<!-- TABEL REKAP WORK ORDER -->
<x-card title="Rincian Transaksi Servis pada Periode Ini" icon="table">
    @if($workOrders->isEmpty())
        <x-empty-state title="Tidak Ada Data Transaksi" description="Tidak ada transaksi perbaikan yang ditemukan pada rentang tanggal filter ini." icon="calendar-x" />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>No. WO</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Kendaraan & Plat</th>
                        <th>Teknisi</th>
                        <th>Status</th>
                        <th class="text-end">Jasa</th>
                        <th class="text-end">Sparepart</th>
                        <th class="text-end">Total Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($workOrders as $wo)
                        <tr>
                            <td>
                                <a href="{{ route('work-orders.show', $wo->id) }}" class="fw-bold text-decoration-none">
                                    {{ $wo->wo_number }}
                                </a>
                            </td>
                            <td class="small text-muted">{{ $wo->created_at->format('d/m/Y') }}</td>
                            <td><span class="fw-semibold text-dark">{{ $wo->customer->name }}</span></td>
                            <td>
                                <span class="badge bg-dark fs-7">{{ $wo->vehicle->plate_number }}</span>
                                <small class="text-muted d-block">{{ $wo->vehicle->brand }} {{ $wo->vehicle->model }}</small>
                            </td>
                            <td>{{ $wo->technician->name ?? 'Belum Ditugaskan' }}</td>
                            <td><x-status-badge :status="$wo->status" /></td>
                            <td class="text-end small">Rp {{ number_format($wo->total_services, 0, ',', '.') }}</td>
                            <td class="text-end small">Rp {{ number_format($wo->total_parts, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-primary">Rp {{ number_format($wo->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-card>

@endsection
