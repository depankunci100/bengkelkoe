@props([
    'text' => 'Memuat data...',
    'variant' => 'primary',
])

<div class="d-flex align-items-center justify-content-center p-4 gap-3 text-muted">
    <div class="spinner-border spinner-border-sm text-{{ $variant }}" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <span>{{ $text }}</span>
</div>
