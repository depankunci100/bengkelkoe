@props([
    'status' => 'DRAFT'
])

@php
$config = match(strtoupper((string) $status)) {
    'DRAFT' => ['class' => 'bg-secondary', 'label' => 'Draft', 'icon' => 'file-earmark'],
    'INSPECTION' => ['class' => 'bg-info text-dark', 'label' => 'Inspeksi', 'icon' => 'search'],
    'WAITING_APPROVAL' => ['class' => 'bg-warning text-dark', 'label' => 'Menunggu Approval', 'icon' => 'clock-history'],
    'APPROVED' => ['class' => 'bg-primary', 'label' => 'Disetujui', 'icon' => 'check-circle'],
    'IN_PROGRESS' => ['class' => 'bg-primary', 'label' => 'Dikerjakan', 'icon' => 'wrench-adjustable'],
    'PAUSED' => ['class' => 'bg-secondary', 'label' => 'Ditunda', 'icon' => 'pause-circle'],
    'QC' => ['class' => 'bg-info text-dark', 'label' => 'Quality Control', 'icon' => 'clipboard-check'],
    'READY_FOR_PICKUP' => ['class' => 'bg-success', 'label' => 'Siap Diambil', 'icon' => 'check2-all'],
    'COMPLETED' => ['class' => 'bg-success', 'label' => 'Selesai', 'icon' => 'flag-fill'],
    'REJECTED' => ['class' => 'bg-danger', 'label' => 'Ditolak', 'icon' => 'x-circle'],
    'CANCELLED' => ['class' => 'bg-dark', 'label' => 'Dibatalkan', 'icon' => 'slash-circle'],
    'PAID' => ['class' => 'bg-success', 'label' => 'Lunas', 'icon' => 'check-circle-fill'],
    'PARTIAL' => ['class' => 'bg-warning text-dark', 'label' => 'DP / Sebagian', 'icon' => 'pie-chart'],
    'UNPAID' => ['class' => 'bg-danger', 'label' => 'Belum Lunas', 'icon' => 'exclamation-circle'],
    'GOOD' => ['class' => 'bg-success', 'label' => 'Baik (Good)', 'icon' => 'check'],
    'WARNING' => ['class' => 'bg-warning text-dark', 'label' => 'Perhatian (Warning)', 'icon' => 'exclamation-triangle'],
    'BAD' => ['class' => 'bg-danger', 'label' => 'Rusak (Bad)', 'icon' => 'x-lg'],
    'NEED_REPLACEMENT' => ['class' => 'bg-danger', 'label' => 'Perlu Diganti', 'icon' => 'arrow-repeat'],
    default => ['class' => 'bg-secondary', 'label' => $status, 'icon' => 'circle'],
};
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . $config['class'] . ' px-2 py-1 fw-semibold d-inline-flex align-items-center gap-1']) }}>
    <i class="bi bi-{{ $config['icon'] }}"></i>
    {{ $config['label'] }}
</span>
