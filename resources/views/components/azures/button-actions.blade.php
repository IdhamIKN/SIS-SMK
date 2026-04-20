{{--
    Komponen: Button Actions Azures
    Penggunaan: @include('components.azures.button-actions', ['primary' => [...], 'secondary' => [...]])

    Props:
    - primary: Array button utama [{'text' => '...', 'href' => '...', 'icon' => '...', 'color' => '...'}]
    - secondary: Array button sekunder (optional)
    - layout: 'row' atau 'column' (default: row)
--}}

<div class="mt-{{ $layout ?? '4' }} {{ $layout === 'column' ? '' : 'd-flex gap-2' }}">
    @if(isset($primary) && is_array($primary))
        @foreach($primary as $btn)
            @if(isset($btn['href']))
                <a href="{{ $btn['href'] ?? '#' }}"
                   class="btn {{ $btn['size'] ?? 'btn-m' }} btn-full {{ $btn['color'] ?? 'bg-highlight' }} rounded-s text-uppercase font-900 {{ $layout === 'column' ? 'mb-2' : '' }}">
                    @if(isset($btn['icon']))
                        <i class="{{ $btn['icon'] }} me-2"></i>
                    @endif
                    {{ $btn['text'] ?? 'Button' }}
                </a>
            @else
                <button type="{{ $btn['type'] ?? 'button' }}"
                        class="btn {{ $btn['size'] ?? 'btn-m' }} btn-full {{ $btn['color'] ?? 'bg-highlight' }} rounded-s text-uppercase font-900 {{ $layout === 'column' ? 'mb-2' : '' }}"
                        @if(isset($btn['onclick'])) onclick="{{ $btn['onclick'] }}" @endif
                        @if(isset($btn['disabled']) && $btn['disabled']) disabled @endif>
                    @if(isset($btn['icon']))
                        <i class="{{ $btn['icon'] }} me-2"></i>
                    @endif
                    {{ $btn['text'] ?? 'Button' }}
                </button>
            @endif
        @endforeach
    @endif

    @if(isset($secondary) && is_array($secondary))
        <div class="{{ $layout === 'column' ? 'mt-3' : 'ms-auto' }}">
            @foreach($secondary as $btn)
                @if(isset($btn['href']))
                    <a href="{{ $btn['href'] ?? '#' }}"
                       class="btn {{ $btn['size'] ?? 'btn-s' }} {{ $btn['style'] ?? 'btn-border border-gray color-gray' }} rounded-s text-uppercase font-900 {{ $layout === 'column' ? 'mb-2' : 'me-2' }}">
                        @if(isset($btn['icon']))
                            <i class="{{ $btn['icon'] }} me-2"></i>
                        @endif
                        {{ $btn['text'] ?? 'Button' }}
                    </a>
                @else
                    <button type="{{ $btn['type'] ?? 'button' }}"
                            class="btn {{ $btn['size'] ?? 'btn-s' }} {{ $btn['style'] ?? 'btn-border border-gray color-gray' }} rounded-s text-uppercase font-900 {{ $layout === 'column' ? 'mb-2' : 'me-2' }}"
                            @if(isset($btn['onclick'])) onclick="{{ $btn['onclick'] }}" @endif>
                        @if(isset($btn['icon']))
                            <i class="{{ $btn['icon'] }} me-2"></i>
                        @endif
                        {{ $btn['text'] ?? 'Button' }}
                    </button>
                @endif
            @endforeach
        </div>
    @endif
</div>