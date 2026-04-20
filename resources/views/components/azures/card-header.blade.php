{{--
    Komponen: Card Header Azures
    Penggunaan: @include('components.azures.card-header', ['image' => '...', 'height' => '...'])

    Props:
    - image: URL background image (optional)
    - height: Tinggi card dalam px (default: 150)
    - overlay: Opacity overlay (default: 95)
    - title: Teks title yang muncul di overlay (optional)
    - subtitle: Teks subtitle (optional)
--}}

<div class="card header-card shape-rounded" data-card-height="{{ $height ?? 150 }}">
    @if(isset($image) && $image)
        <div class="card-overlay bg-highlight opacity-{{ $overlay ?? 95 }}"></div>
        <div class="card-overlay dark-mode-tint"></div>
        <div class="card-bg preload-img" data-src="{{ $image }}"></div>

        @if(isset($title) && $title)
            <div class="card-bottom">
                <h4 class="color-white mb-1">{{ $title }}</h4>
                @if(isset($subtitle) && $subtitle)
                    <p class="color-white opacity-70 font-12">{{ $subtitle }}</p>
                @endif
            </div>
        @endif
    @else
        <div class="card-overlay bg-highlight opacity-{{ $overlay ?? 95 }}"></div>
        <div class="card-overlay dark-mode-tint"></div>
        <div class="card-bg bg-highlight"></div>

        @if(isset($title) && $title)
            <div class="card-bottom">
                <h4 class="color-white mb-1">{{ $title }}</h4>
                @if(isset($subtitle) && $subtitle)
                    <p class="color-white opacity-70 font-12">{{ $subtitle }}</p>
                @endif
            </div>
        @endif
    @endif
</div>