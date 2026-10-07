@extends('layouts.app')

@section('title', 'Tambah Penghargaan')

@push('styles')
    @include('components.event-styles')
    @include('admin.tatib._styles')
@endpush

@section('content')
    <div class="event-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>{{ now()->translatedFormat('l, d F Y') }}</div>
            <h2><i class="fas fa-award"></i> Tambah Penghargaan</h2>
            <p>Tahun ajaran {{ $tahunAjaran }}</p>
        </div>

        @include('admin.tatib._form', [
            'jenis' => 'penghargaan',
            'action' => route('admin.penghargaan.store'),
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
