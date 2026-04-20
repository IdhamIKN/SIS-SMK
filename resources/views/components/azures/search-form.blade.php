{{--
    Komponen: Search Form Azures
    Penggunaan: @include('components.azures.search-form', ['action' => '...', 'placeholder' => '...'])

    Props:
    - action: URL form action (required)
    - placeholder: Placeholder text (default: 'Cari...')
    - fields: Array additional fields [['name' => '...', 'type' => '...', 'label' => '...', 'options' => [...]]]
    - showButton: Tampilkan tombol cari (default: true)
    - method: HTTP method (default: GET)
--}}

@if($method ?? 'GET' === 'GET')
    <form method="GET" action="{{ $action ?? '#' }}" class="mb-3">
@else
    <form method="POST" action="{{ $action ?? '#' }}" enctype="multipart/form-data">
        @csrf
@endif

    {{-- Main Search Input --}}
    <div class="input-style has-borders has-icon validate-field mb-3">
        <i class="fa fa-search"></i>
        <input type="text" name="search" value="{{ request('search') }}"
               class="form-control" placeholder="{{ $placeholder ?? 'Cari...' }}">
        <label class="color-highlight">Pencarian</label>
    </div>

    {{-- Additional Fields --}}
    @if(isset($fields) && is_array($fields))
        <div class="row mb-3">
            @foreach($fields as $field)
                <div class="col-{{ $field['col'] ?? 6 }} mb-3">
                    @if($field['type'] === 'select')
                        <div class="input-style has-borders no-icon">
                            <label class="color-highlight">{{ $field['label'] ?? 'Pilih' }}</label>
                            <select name="{{ $field['name'] }}" class="form-control">
                                <option value="">{{ $field['placeholder'] ?? 'Pilih...' }}</option>
                                @if(isset($field['options']) && is_array($field['options']))
                                    @foreach($field['options'] as $key => $value)
                                        <option value="{{ $key }}" {{ request($field['name']) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @elseif($field['type'] === 'checkbox')
                        <div class="fac fac-checkbox {{ $field['color'] ?? 'fac-blue' }}">
                            <span></span>
                            <input type="hidden" name="{{ $field['name'] }}" value="0">
                            <input id="{{ $field['name'] }}" type="checkbox" name="{{ $field['name'] }}"
                                   value="1" {{ request($field['name']) ? 'checked' : '' }}>
                            <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                        </div>
                    @else
                        <div class="input-style has-borders no-icon validate-field">
                            <input type="{{ $field['type'] ?? 'text' }}" name="{{ $field['name'] }}"
                                   value="{{ request($field['name']) }}"
                                   class="form-control" placeholder="{{ $field['placeholder'] ?? '' }}">
                            <label class="color-highlight">{{ $field['label'] }}</label>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Submit Button --}}
    @if($showButton ?? true)
        <button type="submit" class="btn btn-m btn-full bg-highlight rounded-s text-uppercase font-900">
            <i class="fas fa-search me-2"></i>{{ $buttonText ?? 'Cari' }}
        </button>
    @endif

</form>