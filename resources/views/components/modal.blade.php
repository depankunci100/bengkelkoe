@props([
    'id',
    'title',
    'size' => '', // sm, lg, xl
    'scrollable' => true,
    'footer' => null,
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog {{ $size ? 'modal-' . $size : '' }} {{ $scrollable ? 'modal-dialog-scrollable' : '' }}">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @if($footer)
                <div class="modal-footer bg-light">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
