@props([
    'title' => 'Tidak Ada Data',
    'description' => 'Belum ada data yang tersedia untuk ditampilkan.',
    'icon' => 'inbox',
    'action' => null,
])

<div class="text-center py-5">
    <div class="display-4 text-muted mb-3 opacity-50">
        <i class="bi bi-{{ $icon }}"></i>
    </div>
    <h5 class="fw-bold text-dark mb-2">{{ $title }}</h5>
    <p class="text-muted mb-3 mx-auto" style="max-width: 420px;">{{ $description }}</p>
    @if($action)
        <div>{{ $action }}</div>
    @endif
</div>
