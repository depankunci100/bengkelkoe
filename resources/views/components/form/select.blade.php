@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => '-- Pilih Opsi --',
    'required' => false,
    'disabled' => false,
    'help' => null,
])

<div class="mb-3">
    @if($label)
        <label for="{{ $name }}" class="form-label fw-semibold">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif
    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'form-select ' . ($errors->has($name) ? ' is-invalid' : '')]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @if(is_array($options))
            @foreach($options as $val => $text)
                <option value="{{ $val }}" {{ (string) old($name, $selected) === (string) $val ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    @if($help && !$errors->has($name))
        <div class="form-text text-muted">{{ $help }}</div>
    @endif
</div>
