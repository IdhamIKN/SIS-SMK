{{--
    Komponen: Icon Grid Item Azures
    Penggunaan: @include('components.azures.icon-grid-item', ['href' => '...', 'icon' => '...', 'label' => '...'])

    Props:
    - href: URL tujuan (required)
    - icon: Feather icon name (required)
    - label: Label text (required)
    - color: Background color (default: highlight)
--}}

<div class="col-4 text-center mb-3">
    <a href="{{ $href ?? '#' }}">
        <div class="icon icon-l rounded-m bg-{{ $color ?? 'highlight' }}-fade-dark shadow-m mx-auto mb-1">
            <i data-feather="{{ $icon ?? 'circle' }}" data-feather-line="1" data-feather-size="24" data-feather-color="{{ $color ?? 'highlight' }}-dark"></i>
        </div>
        <span class="font-10">{{ $label ?? 'Menu' }}</span>
    </a>
</div>