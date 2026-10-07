@extends('layouts.app')

@section('title', 'Tambah Jurnal Mengajar')

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
                {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
            </div>
            <h2><i class="fas fa-book-open"></i> Tambah Jurnal Mengajar</h2>
            <p>Pilih jadwal KBM, isi materi, siswa tidak hadir, dan unggah bukti pembelajaran.</p>
        </div>

        @include('guru.jurnal._form', [
            'action'      => route('guru.jurnal-mengajar.store'),
            'method'      => 'POST',
            'submitLabel' => 'Simpan Jurnal',
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
