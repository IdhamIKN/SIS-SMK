@extends('layouts.app')

@section('title', 'Edit Jurnal Mengajar')

@push('styles')
    @include('components.event-styles')
    @include('guru.jurnal.styles')
@endpush

@section('content')
    <div class="event-wrap jurnal-wrap"
        style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h, 0px) + 88px);">

        {{-- ── Page Strip ──────────────────────────────────────── --}}
        <div class="page-strip page-strip-event">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ optional($jurnal->tanggal)->translatedFormat('d F Y') }}
            </div>
            <h2><i class="fas fa-edit"></i> Edit Jurnal Mengajar</h2>
            <p>Perbarui materi, data kehadiran siswa, atau bukti pembelajaran.</p>
        </div>

        @include('guru.jurnal._form', [
            'action'      => route('guru.jurnal-mengajar.update', $jurnal),
            'method'      => 'PUT',
            'submitLabel' => 'Perbarui Jurnal',
        ])
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header-auto-show');
            if (header) header.classList.add('header-active');
        });
    </script>
@endpush
