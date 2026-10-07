@extends('layouts.app')

@section('title', 'Daftar Lembar Inspeksi')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Inspeksi Kendaraan</h4>
        <p class="text-muted small mb-0">Checklist digital pemeriksaan kondisi fisik, mesin, pengereman, kelistrikan, dan kaki-kaki.</p>
    </div>
</div>
@endsection

@section('content')
<x-card>
    @if($inspections->isEmpty())
        <x-empty-state title="Belum Ada Lembar Inspeksi" description="Inspeksi dibuat otomatis ketika Work Order didaftarkan." icon="clipboard2-check" />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>No. WO</th>
                        <th>Pelanggan</th>
                        <th>Kendaraan & Plat</th>
                        <th>Mekanik Pemeriksa</th>
                        <th>Jumlah Item Diperiksa</th>
                        <th>Status Inspeksi</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inspections as $ins)
                        <tr>
                            <td>
                                <a href="{{ route('work-orders.show', $ins->work_order_id) }}" class="fw-bold text-decoration-none">
                                    {{ $ins->workOrder->wo_number }}
                                </a>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $ins->workOrder->customer->name }}</span>
                            </td>
                            <td>
                                <span class="badge bg-dark fs-7 px-2 py-1">{{ $ins->workOrder->vehicle->plate_number }}</span>
                                <small class="text-muted d-block">{{ $ins->workOrder->vehicle->brand }} {{ $ins->workOrder->vehicle->model }}</small>
                            </td>
                            <td>
                                @if($ins->technician)
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        {{ $ins->technician->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">Belum ditentukan</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border">
                                    {{ $ins->items->count() }} Poin Pemeriksaan
                                </span>
                            </td>
                            <td>
                                @if($ins->status === 'COMPLETED')
                                    <span class="badge bg-success">Selesai Diinspeksi</span>
                                @else
                                    <span class="badge bg-warning text-dark">Sedang Berjalan</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('inspections.show', $ins->work_order_id) }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-pencil-square"></i> Buka Checklist
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($inspections->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $inspections->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
</x-card>
@endsection
