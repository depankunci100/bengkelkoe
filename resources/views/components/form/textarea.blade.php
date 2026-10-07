@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => '',
    'rows' => 3,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'help' => null,
])

<div class="mb-3">
    @if($label)
        <label for="{{ $name }}" class="form-label fw-semibold">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $readonly ? 'readonly' : '' }}
        {{ $attributes->merge(['class' => 'form-control ' . ($errors->has($name) ? ' is-invalid' : '')]) }}
    >{{ old($name, $value ?? $slot) }}</textarea>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    @if($help && !$errors->has($name))
        <div class="form-text text-muted">{{ $help }}</div>
    @endif
</div>
