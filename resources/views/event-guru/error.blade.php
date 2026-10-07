@extends('layouts.app')
@section('title', 'Error Event Guru')

@push('styles')
    @include('components.event-styles')
@endpush

@section('content')
    <div class="event-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">
        <div class="page-strip" style="background:linear-gradient(135deg,#1e40af 0%,#4338ca 100%);">
            <h2><i class="fas fa-exclamation-triangle"></i> Tidak Dapat Melanjutkan</h2>
        </div>
        <div class="alert a-warn"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
        <a href="{{ route('event-guru.index') }}" class="btn-sub">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Event
        </a>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const header = document.querySelector('.header-auto-show');
    if (header) { header.classList.add('header-active'); }
});
</script>
@endpush
