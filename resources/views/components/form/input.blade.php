@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'icon' => null,
    'help' => null,
])

<div class="mb-3">
    @if($label)
        <label for="{{ $name }}" class="form-label fw-semibold">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif
    <div class="{{ $icon ? 'input-group' : '' }}">
        @if($icon)
            <span class="input-group-text bg-light text-muted border-end-0">
                <i class="bi bi-{{ $icon }}"></i>
            </span>
        @endif
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $attributes->merge(['class' => 'form-control ' . ($icon ? 'border-start-0' : '') . ($errors->has($name) ? ' is-invalid' : '')]) }}
        >
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    @if($help && !$errors->has($name))
        <div class="form-text text-muted">{{ $help }}</div>
    @endif
</div>
