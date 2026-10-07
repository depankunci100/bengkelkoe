@extends('layouts.app')

@section('title', 'Dashboard Approval Pelanggan')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Persetujuan Estimasi (Approval)</h4>
        <p class="text-muted small mb-0">Monitor status pengiriman dan respon persetujuan biaya dari WhatsApp pelanggan.</p>
    </div>
</div>
@endsection

@section('content')

<!-- TABS APPROVAL (Section 15) -->
<ul class="nav nav-pills mb-4 bg-white p-2 rounded shadow-sm border" role="tablist">
    @foreach(['Pending' => ['label' => 'Pending (Menunggu)', 'icon' => 'clock-history', 'color' => 'warning'], 'Approved' => ['label' => 'Approved (Disetujui)', 'icon' => 'check-circle', 'color' => 'success'], 'Rejected' => ['label' => 'Rejected (Ditolak)', 'icon' => 'x-circle', 'color' => 'danger'], 'Expired' => ['label' => 'Expired (Kedaluwarsa)', 'icon' => 'hourglass-bottom', 'color' => 'secondary']] as $tKey => $tMeta)
        <li class="nav-item">
            <a href="{{ route('approvals.index', ['tab' => $tKey]) }}" class="nav-link {{ $tab === $tKey ? 'active' : '' }} d-inline-flex align-items-center gap-2">
                <i class="bi bi-{{ $tMeta['icon'] }}"></i>
                <span>{{ $tMeta['label'] }}</span>
                <span class="badge {{ $tab === $tKey ? 'bg-white text-dark' : 'bg-' . $tMeta['color'] . ' text-white' }} rounded-pill ms-1">
                    {{ $counts[$tKey] ?? 0 }}
                </span>
            </a>
        </li>
    @endforeach
</ul>

<x-card>
    @if($workOrders->isEmpty())
        <x-empty-state title="Tidak Ada Data Pada Tab Ini" description="Belum ada work order dengan status approval {{ $tab }}." icon="check2-all" />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>No. WO</th>
                        <th>Customer</th>
                        <th>Kendaraan & Plat</th>
                        <th>Total Estimasi</th>
                        <th>Status</th>
                        <th>Dikirim Pada</th>
                        <th>Batas Expired</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($workOrders as $wo)
                        <tr>
                            <td>
                                <a href="{{ route('approvals.show', $wo->id) }}" class="fw-bold text-decoration-none">
                                    {{ $wo->wo_number }}
                                </a>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark d-block">{{ $wo->customer->name }}</span>
                                <small class="text-muted"><i class="bi bi-whatsapp text-success"></i> {{ $wo->customer->phone }}</small>
                            </td>
                            <td>
                                <span class="badge bg-dark fs-7 px-2 py-1">{{ $wo->vehicle->plate_number }}</span>
                                <div class="small text-muted">{{ $wo->vehicle->brand }} {{ $wo->vehicle->model }}</div>
                            </td>
                            <td class="fw-bold text-dark">
                                Rp {{ number_format($wo->grand_total, 0, ',', '.') }}
                            </td>
                            <td>
                                <x-status-badge :status="$wo->status" />
                            </td>
                            <td class="small text-muted">
                                {{ $wo->approval_sent_at ? $wo->approval_sent_at->translatedFormat('d M, H:i') : '-' }}
                            </td>
                            <td class="small">
                                @if($wo->approval_expires_at)
                                    @if($wo->approval_expires_at->isPast())
                                        <span class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle"></i> Expired</span>
                                    @else
                                        <span class="text-muted">{{ $wo->approval_expires_at->diffForHumans() }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('approvals.show', $wo->id) }}" class="btn btn-primary d-inline-flex align-items-center gap-1" title="Lihat Rincian">
                                        <i class="bi bi-eye"></i> Lihat
                                    </a>
                                    @if($wo->status === 'WAITING_APPROVAL')
                                        <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-outline-success d-inline-flex align-items-center gap-1" title="Kirim Ulang WA">
                                            <i class="bi bi-whatsapp"></i> Kirim Ulang
                                        </a>
                                    @endif
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
