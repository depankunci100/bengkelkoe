@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'headerClass' => '',
    'bodyClass' => '',
    'actions' => null,
])

<div {{ $attributes->merge(['class' => 'card shadow-sm border-0']) }}>
    @if($title || $actions)
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center {{ $headerClass }}">
            <div>
                @if($title)
                    <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                        @if($icon)<i class="bi bi-{{ $icon }} text-primary"></i>@endif
                        {{ $title }}
                    </h6>
                @endif
                @if($subtitle)
                    <small class="text-muted">{{ $subtitle }}</small>
                @endif
            </div>
            @if($actions)
                <div>{{ $actions }}</div>
            @endif
        </div>
    @endif
    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>
