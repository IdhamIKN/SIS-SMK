@extends('layouts.app')

@section('title', 'Edit Pelanggaran')

@push('styles')
    @include('components.event-styles')
    @include('admin.tatib._styles')
@endpush

@section('content')
<div class="event-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">
    <div class="page-strip page-strip-event">
        <div class="live-badge"><span class="live-dot"></span>{{ now()->translatedFormat('l, d F Y') }}</div>
        <h2><i class="fas fa-edit"></i> Edit Pelanggaran</h2>
        <p>{{ $pelanggaran->siswa?->nama_lengkap ?? $pelanggaran->nama }}</p>
    </div>

    @include('admin.tatib._form', [
        'jenis' => 'pelanggaran',
        'record' => $pelanggaran,
        'action' => route('admin.pelanggaran.update', $pelanggaran),
        'method' => 'PUT',
        'backRoute' => route('admin.pelanggaran.index', ['tahun_ajaran' => $tahunAjaran]),
    ])
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header-auto-show');
            if (header) {
                header.classList.add('header-active');
            }
        });
    </script>
@endpush