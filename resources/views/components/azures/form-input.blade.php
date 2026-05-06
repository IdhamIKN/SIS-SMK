{{--
    Komponen: Form Input Azures
    Penggunaan: @include('components.azures.form-input', ['name' => '...', 'label' => '...', 'type' => '...'])

    Props:
    - name: Nama field (required)
    - label: Label text (required)
    - type: Tipe input (default: text)
    - value: Nilai default (optional)
    - placeholder: Placeholder text (optional)
    - required: Apakah required (default: false)
    - icon: FontAwesome icon class (optional)
    - error: Error message (optional)
    - help: Help text (optional)
    - options: Array untuk select/radio (optional)
--}}

@if (($type ?? 'text') === 'select')
    {{-- Select Input --}}
    <div class="input-style has-borders no-icon {{ $error ?? false ? 'has-error' : '' }} mb-4">
        <label class="color-highlight">{{ $label }}</label>
        <select name="{{ $name }}" id="{{ $name }}" class="form-control"
            {{ $required ?? false ? 'required' : '' }}>
            <option value="">{{ $placeholder ?? 'Pilih...' }}</option>
            @if (isset($options) && is_array($options))
                @foreach ($options as $optKey => $optValue)
                    <option value="{{ $optKey }}" {{ (old($name) ?? (isset($value) ? $value : '')) == $optKey ? 'selected' : '' }}>
                        {{ $optValue }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>
@elseif(($type ?? 'text') === 'radio')
    {{-- Radio Buttons --}}
    <div class="mb-4">
        <label class="color-highlight font-14 mb-2 d-block">{{ $label }}</label>
            @if (isset($options) && is_array($options))
                @foreach ($options as $key => $label)
                    <div class="fac fac-radio fac-{{ $color ?? 'blue' }} mb-2">
                        <span></span>
                        <input id="{{ $name }}_{{ $key }}" type="radio" name="{{ $name }}"
                            value="{{ $key }}" {{ (old($name) ?? (isset($value) ? $value : '')) == $key ? 'checked' : '' }}
                            {{ $required ?? false ? 'required' : '' }}>
                        <label for="{{ $name }}_{{ $key }}">{{ $label }}</label>
                    </div>
                @endforeach
            @endif
    </div>
@elseif(($type ?? 'text') === 'checkbox')
    {{-- Checkbox --}}
    <div class="fac fac-checkbox fac-{{ $color ?? 'green' }} mb-4">
        <span></span>
        <input type="hidden" name="{{ $name }}" value="0">
        <input id="{{ $name }}" type="checkbox" name="{{ $name }}" value="1"
            {{ old($name) ?? (isset($value) ? $value : false) ? 'checked' : '' }}>
        <label for="{{ $name }}">{{ $label }}</label>
    </div>
@elseif(($type ?? 'text') === 'textarea')
    {{-- Textarea --}}
    <div class="input-style has-borders no-icon mb-4">
        <label class="color-highlight">{{ $label }}</label>
        <textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows ?? 3 }}" class="form-control"
            placeholder="{{ $placeholder ?? '' }}" {{ $required ?? false ? 'required' : '' }}>{{ old($name) ?? (isset($value) ? $value : '') }}</textarea>
    </div>
@elseif(($type ?? 'text') === 'file')
    {{-- File Input --}}
    <div class="input-style has-borders {{ isset($icon) ? 'has-icon' : 'no-icon' }} validate-field mb-4">
        @if (isset($icon))
            <i class="{{ $icon }}"></i>
        @endif
        <input type="file" name="{{ $name }}" id="{{ $name }}" class="form-control"
            accept="{{ $accept ?? '*' }}" {{ $required ?? false ? 'required' : '' }}>
        <label class="color-highlight">{{ $label }}</label>
        <em>{{ $help ?? 'Format: ' . ($accept ?? 'semua file') }}</em>
    </div>
@else
    {{-- Text/Email/Password/etc Input --}}
    <div class="input-style has-borders {{ isset($icon) ? 'has-icon' : 'no-icon' }} validate-field mb-4">
        @if (isset($icon))
            <i class="{{ $icon }}"></i>
        @endif
        <input type="{{ $type ?? 'text' }}" name="{{ $name }}" id="{{ $name }}"
            class="form-control {{ $error ?? false ? 'is-invalid' : '' }}" value="{{ old($name) ?? (isset($value) ? $value : '') }}"
            placeholder="{{ $placeholder ?? '' }}" {{ $required ?? false ? 'required' : '' }}>
        <label class="color-highlight">{{ $label }}</label>
        @if ($error ?? false)
            <i class="fa fa-times disabled invalid color-red-dark"></i>
        @else
            <i class="fa fa-check disabled valid color-green-dark"></i>
        @endif
        @if ($required ?? false)
            <em>(wajib diisi)</em>
        @elseif(isset($help))
            <em>{{ $help }}</em>
        @endif
    </div>
@endif

{{-- Error Message --}}
@if ($error ?? false)
    <p class="mt-1 text-sm text-red-600 font-12">{{ $error }}</p>
@endif
