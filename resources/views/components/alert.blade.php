@props([
    'variant' => 'info',
    'dismissible' => true,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'alert alert-' . $variant . ($dismissible ? ' alert-dismissible fade show' : '') . ' shadow-sm d-flex align-items-center gap-2']) }} role="alert">
    @if($icon)
        <i class="bi bi-{{ $icon }} fs-5"></i>
    @endif
    <div class="flex-grow-1">
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
