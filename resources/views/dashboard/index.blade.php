@extends('layouts.app')

@section('title', 'Dashboard Bengkel')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Dashboard Utama</h4>
        <p class="text-muted small mb-0">Selamat datang, <strong>{{ auth()->user()->name }}</strong>. Berikut ringkasan operasional bengkel hari ini.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('work-orders.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Buat Work Order Baru</span>
        </a>
    </div>
</div>
@endsection

@section('content')

<!-- 1. KPI CARDS (Bootstrap 5 Cards) -->
<div class="row g-3 mb-4">
    <!-- Card 1: WO Hari Ini -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase">WO HARI INI</span>
                    <h3 class="fw-bold text-dark my-1">{{ $woToday }}</h3>
                    <div class="small text-success d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-up-right"></i>
                        <span>Total Aktif: {{ $inProgress }} proses</span>
                    </div>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-file-earmark-medical-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Pendapatan Bulan Ini -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase">PENDAPATAN BULAN INI</span>
                    <h3 class="fw-bold text-dark my-1">Rp {{ number_format($revenueMonth, 0, ',', '.') }}</h3>
                    <div class="small text-muted">
                        <span>Hari ini: Rp {{ number_format($revenueToday, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Menunggu Approval -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase">MENUNGGU APPROVAL</span>
                    <h3 class="fw-bold text-dark my-1">{{ $waitingApproval }}</h3>
                    <div class="small">
                        <a href="{{ route('approvals.index') }}" class="text-warning text-decoration-none fw-semibold">
                            Cek WhatsApp Link <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-whatsapp"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Siap Diambil -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase">SIAP DIAMBIL</span>
                    <h3 class="fw-bold text-dark my-1">{{ $readyPickup }}</h3>
                    <div class="small">
                        <a href="{{ route('work-orders.index', ['status' => 'READY_FOR_PICKUP']) }}" class="text-info text-decoration-none fw-semibold">
                            Tinjau Kasir / Serah Terima <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-car-front-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. GRAFIK (Chart.js) -->
<div class="row g-3 mb-4">
    <!-- Line Chart Pendapatan -->
    <div class="col-12 col-lg-8">
        <x-card title="Tren Pendapatan 7 Hari Terakhir" icon="graph-up">
            <div style="height: 280px; position: relative;">
                <canvas id="revenueChart"></canvas>
            </div>
        </x-card>
    </div>

    <!-- Doughnut Chart Distribusi WO -->
    <div class="col-12 col-lg-4">
        <x-card title="Distribusi Status Work Order" icon="pie-chart">
            <div style="height: 280px; position: relative;">
                <canvas id="statusChart"></canvas>
            </div>
        </x-card>
    </div>
</div>

<!-- 3. TABEL WORK ORDER TERBARU & WIDGET OPERASIONAL -->
<div class="row g-3">
    <!-- Work Order Terbaru -->
    <div class="col-12 col-lg-8">
        <x-card title="Work Order Berjalan Terbaru" icon="list-task">
            <x-slot:actions>
                <a href="{{ route('work-orders.index') }}" class="btn btn-sm btn-outline-primary">
                    Lihat Semua WO <i class="bi bi-arrow-right"></i>
                </a>
            </x-slot:actions>

            @if($recentWorkOrders->isEmpty())
                <x-empty-state title="Belum Ada Work Order" description="Mulai daftarkan kendaraan pelanggan dengan membuat Work Order baru." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>No. WO</th>
                                <th>Customer & Kendaraan</th>
                                <th>Teknisi</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentWorkOrders as $wo)
                                <tr>
                                    <td>
                                        <a href="{{ route('work-orders.show', $wo->id) }}" class="fw-bold text-decoration-none">
                                            {{ $wo->wo_number }}
                                        </a>
                                        <div class="small text-muted">{{ $wo->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $wo->customer->name }}</div>
                                        <div class="small text-muted">
                                            <span class="badge bg-light text-dark border">{{ $wo->vehicle->plate_number }}</span>
                                            {{ $wo->vehicle->brand }} {{ $wo->vehicle->model }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($wo->technician)
                                            <span class="badge bg-secondary-subtle text-secondary border">
                                                <i class="bi bi-person-fill"></i> {{ $wo->technician->name }}
                                            </span>
                                        @else
                                            <span class="text-muted small"><em>Belum ditugaskan</em></span>
                                        @endif
                                    </td>
                                    <td>
                                        <x-status-badge :status="$wo->status" />
                                    </td>
                                    <td class="fw-semibold">
                                        Rp {{ number_format($wo->grand_total, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-light border" title="Lihat Rincian">
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

    <!-- Sisi Kanan: Jasa Terlaris & Peringatan Stok Sparepart -->
    <div class="col-12 col-lg-4 d-flex flex-column gap-3">
        <!-- Jasa Terlaris -->
        <x-card title="Jasa Paling Sering Dikerjakan" icon="star-fill">
            @if($topServices->isEmpty())
                <p class="text-muted small text-center my-3">Belum ada data pengerjaan jasa.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($topServices as $svc)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <div>
                                <span class="fw-semibold text-dark small d-block">{{ $svc->item_name }}</span>
                                <small class="text-muted">{{ $svc->total_used }}x dikerjakan</small>
                            </div>
                            <span class="badge bg-primary-subtle text-primary fw-bold">
                                Rp {{ number_format($svc->total_revenue, 0, ',', '.') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>

        <!-- Peringatan Stok Kritis -->
        <x-card title="Peringatan Stok Sparepart" icon="exclamation-diamond-fill">
            <x-slot:actions>
                <a href="{{ route('parts.index') }}" class="small text-primary text-decoration-none fw-semibold">Kelola</a>
            </x-slot:actions>

            @if($lowStockParts->isEmpty())
                <div class="text-center py-3 text-success">
                    <i class="bi bi-check-circle fs-3 d-block mb-1"></i>
                    <small class="fw-semibold">Semua persediaan suku cadang aman!</small>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Sisa</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockParts as $part)
                                <tr>
                                    <td>
                                        <span class="small fw-semibold text-dark d-block text-truncate" style="max-width: 130px;">{{ $part->name }}</span>
                                        <small class="text-muted">{{ $part->part_number }}</small>
                                    </td>
                                    <td class="text-center fw-bold {{ $part->stock <= 0 ? 'text-danger' : 'text-warning' }}">
                                        {{ $part->stock }} {{ $part->unit }}
                                    </td>
                                    <td>
                                        @if($part->stock <= 0)
                                            <span class="badge bg-danger">Habis</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Rendah</span>
                                        @endif
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

@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. Chart Tren Pendapatan 7 Hari
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: @json($days),
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: @json($revenueDaily),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#2563eb',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + Number(context.raw).toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value >= 1000000 ? (value/1000000) + ' Jt' : (value/1000) + ' Rb');
                        }
                    }
                }
            }
        }
    });

    // 2. Chart Distribusi Status Work Order
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Draft', 'Inspeksi', 'Wait Approval', 'Disetujui', 'Dikerjakan', 'QC', 'Siap Diambil', 'Selesai'],
            datasets: [{
                data: [
                    {{ $statusCounts['DRAFT'] ?? 0 }},
                    {{ $statusCounts['INSPECTION'] ?? 0 }},
                    {{ $statusCounts['WAITING_APPROVAL'] ?? 0 }},
                    {{ $statusCounts['APPROVED'] ?? 0 }},
                    {{ $statusCounts['IN_PROGRESS'] ?? 0 }},
                    {{ $statusCounts['QC'] ?? 0 }},
                    {{ $statusCounts['READY_FOR_PICKUP'] ?? 0 }},
                    {{ $statusCounts['COMPLETED'] ?? 0 }}
                ],
                backgroundColor: [
                    '#64748b', // draft
                    '#06b6d4', // inspection
                    '#f59e0b', // wait approval
                    '#3b82f6', // approved
                    '#2563eb', // in progress
                    '#0284c7', // qc
                    '#10b981', // ready
                    '#059669'  // completed
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, font: { size: 10 } }
                }
            },
            cutout: '65%'
        }
    });
});
</script>
@endpush
