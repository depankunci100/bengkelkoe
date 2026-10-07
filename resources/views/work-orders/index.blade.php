@extends('layouts.app')

@section('title', 'Daftar Work Order')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Work Order (SPK Servis)</h4>
        <p class="text-muted small mb-0">Kelola alur pengerjaan perbaikan, diagnosa teknisi, persetujuan biaya, dan serah terima kendaraan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('work-orders.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Buat WO Baru</span>
        </a>
    </div>
</div>
@endsection

@section('content')

<!-- STATUS PILLS / FILTER TABS (Bootstrap 5) -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-2">
        <div class="d-flex flex-wrap gap-1">
            @php
                $tabFilters = [
                    'ALL' => ['label' => 'Semua', 'icon' => 'grid'],
                    'DRAFT' => ['label' => 'Draft', 'icon' => 'file-earmark'],
                    'INSPECTION' => ['label' => 'Inspeksi', 'icon' => 'search'],
                    'WAITING_APPROVAL' => ['label' => 'Wait Approval', 'icon' => 'clock-history'],
                    'IN_PROGRESS' => ['label' => 'Dikerjakan', 'icon' => 'wrench-adjustable'],
                    'QC' => ['label' => 'QC', 'icon' => 'clipboard-check'],
                    'READY_FOR_PICKUP' => ['label' => 'Siap Diambil', 'icon' => 'check2-all'],
                    'COMPLETED' => ['label' => 'Selesai', 'icon' => 'flag-fill'],
                ];
            @endphp

            @foreach($tabFilters as $key => $tab)
                <a href="{{ route('work-orders.index', array_merge(request()->query(), ['status' => $key])) }}"
                   class="btn btn-sm {{ $status === $key ? 'btn-primary text-white fw-bold' : 'btn-light text-muted' }} d-inline-flex align-items-center gap-1">
                    <i class="bi bi-{{ $tab['icon'] }}"></i>
                    <span>{{ $tab['label'] }}</span>
                    <span class="badge {{ $status === $key ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-dark' }} ms-1">
                        {{ $counts[$key] ?? 0 }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</div>

<!-- Search Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('work-orders.index') }}" method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari No. WO, plat nomor, nama pelanggan..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
            @if($search)
                <div class="col-auto">
                    <a href="{{ route('work-orders.index', ['status' => $status]) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

<!-- Work Orders Table -->
<x-card>
    @if($workOrders->isEmpty())
        <x-empty-state
            title="Tidak Ada Work Order"
            description="Tidak ada data pengerjaan yang cocok dengan kriteria filter status atau pencarian ini."
            icon="file-earmark-medical"
        >
            <x-slot:action>
                <a href="{{ route('work-orders.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Buat Work Order Baru
                </a>
            </x-slot:action>
        </x-empty-state>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>No. WO & Waktu</th>
                        <th>Pelanggan & Sales Mitra</th>
                        <th>Kendaraan</th>
                        <th>Keluhan Masuk</th>
                        <th>Teknisi</th>
                        <th>Status</th>
                        <th>Estimasi Biaya</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($workOrders as $wo)
                        <tr>
                            <td>
                                <a href="{{ route('work-orders.show', $wo->id) }}" class="fw-bold text-decoration-none d-block">
                                    {{ $wo->wo_number }}
                                </a>
                                <small class="text-muted">{{ $wo->created_at->translatedFormat('d M Y, H:i') }}</small>
                            </td>
                            <td>
                                <a href="{{ route('customers.show', $wo->customer_id) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                    {{ $wo->customer->name }}
                                </a>
                                <small class="text-muted d-block"><i class="bi bi-telephone"></i> {{ $wo->customer->phone }}</small>
                            </td>
                            <td>
                                <span class="badge bg-dark fs-7 px-2 py-1 fw-bold text-tracking">{{ $wo->vehicle->plate_number }}</span>
                                <div class="small text-muted">{{ $wo->vehicle->brand }} {{ $wo->vehicle->model }}</div>
                            </td>
                            <td class="small text-muted" style="max-width: 200px;">
                                <div class="text-truncate text-dark" title="{{ $wo->complaint }}">
                                    {{ $wo->complaint }}
                                </div>
                            </td>
                            <td>
                                @if($wo->technician)
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        <i class="bi bi-person-fill"></i> {{ $wo->technician->name }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border">Belum Ditugaskan</span>
                                @endif
                            </td>
                            <td>
                                <x-status-badge :status="$wo->status" />
                            </td>
                            <td class="fw-bold text-dark">
                                Rp {{ number_format($wo->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-primary d-inline-flex align-items-center gap-1" title="Buka Detail WO">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($workOrders->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $workOrders->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
</x-card>

@endsection
