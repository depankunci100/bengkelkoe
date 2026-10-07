@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => '',
    'icon' => null,
    'href' => null,
])

@php
$classes = 'btn btn-' . $variant . ($size ? ' btn-' . $size : '') . ' d-inline-flex align-items-center gap-2';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="bi bi-{{ $icon }}"></i>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="bi bi-{{ $icon }}"></i>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
