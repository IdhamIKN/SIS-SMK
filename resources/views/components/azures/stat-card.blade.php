{{--
    Komponen: Stat Card Azures
    Penggunaan: @include('components.azures.stat-card', ['icon' => '...', 'title' => '...', 'value' => '...', 'subtitle' => '...'])

    Props:
    - icon: FontAwesome icon class (required)
    - title: Judul statistik (required)
    - value: Nilai statistik (required)
    - subtitle: Sub judul (optional)
    - color: Warna tema (default: highlight)
    - showProgress: Tampilkan progress bar (default: false)
    - progressValue: Nilai progress 0-100 (default: 0)
--}}

<div class="card card-style me-0 ms-0 mb-3">
    <div class="content">
        <div class="d-flex">
            <div class="align-self-center">
                <i class="{{ $icon ?? 'fas fa-chart-bar' }} fa-2x color-{{ $color ?? 'highlight' }}-dark mb-2"></i>
            </div>
            <div class="align-self-center ms-auto text-end">
                <h3 class="font-700 mb-0">{{ $value ?? 0 }}</h3>
                <p class="font-11 color-{{ $color ?? 'highlight' }}-dark mb-1">{{ $title ?? 'Statistik' }}</p>
                @if(isset($subtitle) && $subtitle)
                    <p class="font-10 opacity-70 mb-0">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        @if(isset($showProgress) && $showProgress)
            @include('components.azures.progress', [
                'value' => $progressValue ?? 0,
                'color' => $color ?? 'highlight',
                'height' => '4px'
            ])
        @endif
    </div>
</div>