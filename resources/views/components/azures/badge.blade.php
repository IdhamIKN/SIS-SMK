{{--
    Komponen: Badge Azures
    Penggunaan: @include('components.azures.badge', ['text' => '...', 'color' => '...'])

    Props:
    - text: Teks badge (required)
    - color: Warna badge (default: blue-dark)
    - size: Ukuran badge (default: normal) - small, normal, large
    - rounded: Bentuk rounded (default: true)
--}}

<span class="badge {{ $size === 'small' ? 'font-10' : ($size === 'large' ? 'font-14 px-3 py-2' : 'font-12') }} {{ $rounded ?? true ? 'rounded-s' : '' }} color-white bg-{{ $color ?? 'blue-dark' }}">
    {{ $text ?? 'Badge' }}
</span>