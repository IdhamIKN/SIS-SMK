@extends('layouts.app')

@section('title', 'Edit Penghargaan')

@push('styles')
    @include('components.event-styles')
    @include('admin.tatib._styles')
@endpush

@section('content')
    <div class="event-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>{{ now()->translatedFormat('l, d F Y') }}</div>
            <h2><i class="fas fa-edit"></i> Edit Penghargaan</h2>
            <p>{{ $penghargaan->siswa?->nama_lengkap ?? $penghargaan->nama }}</p>
        </div>

        @if ($penghargaan->acc === 'YA')
            <div class="alert a-warn"><i class="fas fa-info-circle"></i>
                Penghargaan ini sudah disetujui pada {{ $penghargaan->tglacc?->format('d/m/Y H:i') }} oleh
                {{ $penghargaan->nmacc }}.
                Perubahan akan memperbarui data di laporan poin.
            </div>
        @else
            <div class="alert a-info"><i class="fas fa-info-circle"></i>
                Penghargaan masih menunggu persetujuan. Data dapat diubah sebelum disetujui.
            </div>
        @endif

        @include('admin.tatib._form', [
            'jenis' => 'penghargaan',
            'record' => $penghargaan,
            'action' => route('admin.penghargaan.update', $penghargaan),
            'method' => 'PUT',
            'backRoute' => route('admin.penghargaan.index', ['tahun_ajaran' => $tahunAjaran]),
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
