{{--
    Komponen: Progress Bar Azures
    Penggunaan: @include('components.azures.progress', ['value' => 75, 'color' => 'green'])

    Props:
    - value: Nilai progress 0-100 (required)
    - color: Warna progress (default: highlight)
    - height: Tinggi progress bar (default: 8px)
    - showLabel: Tampilkan persentase (default: false)
    - label: Custom label (optional)
--}}

<div class="progress {{ $height ? 'progress-' . $height : '' }}" style="height: {{ $height ?? '8px' }};">
    <div class="progress-bar bg-{{ $color ?? 'highlight' }}-dark"
         style="width: {{ max(0, min(100, $value ?? 0)) }}%"
         role="progressbar"
         aria-valuenow="{{ $value ?? 0 }}"
         aria-valuemin="0"
         aria-valuemax="100">
    </div>
</div>

@if($showLabel ?? false)
    <div class="text-center mt-2">
        <small class="color-{{ $color ?? 'highlight' }}-dark font-12">
            {{ $label ?? ($value ?? 0) . '%' }}
        </small>
    </div>
@endif

<style>
.progress {
    background-color: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar {
    background-color: #007bff;
    transition: width 0.3s ease;
    border-radius: 4px;
}

.progress-4 {
    height: 4px;
}

.progress-6 {
    height: 6px;
}

.progress-10 {
    height: 10px;
}

.progress-12 {
    height: 12px;
}
</style>