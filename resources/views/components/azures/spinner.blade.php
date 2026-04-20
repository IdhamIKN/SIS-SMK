{{--
    Komponen: Loading Spinner Azures
    Penggunaan: @include('components.azures.spinner', ['text' => 'Memuat...'])

    Props:
    - text: Teks loading (optional, default: 'Memuat...')
    - size: Ukuran spinner (optional, default: 'medium')
    - color: Warna spinner (optional, default: 'highlight')
--}}

<div class="d-flex align-items-center justify-content-center p-4">
    <div class="spinner-border color-{{ $color ?? 'highlight' }} {{ $size === 'small' ? 'spinner-border-sm' : '' }} me-3" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <span class="color-{{ $color ?? 'highlight' }} font-14">{{ $text ?? 'Memuat...' }}</span>
</div>

<style>
/* Additional spinner styles */
.spinner-border {
    animation: spinner-border .75s linear infinite;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

@keyframes spinner-border {
    to {
        transform: rotate(360deg);
    }
}
</style>