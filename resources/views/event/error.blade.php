@extends('layouts.app')

@section('title', 'Tidak Dapat Absen')

@push('styles')
    @include('components.event-styles')
    <style>
        .error-icon-wrap {
            width: 80px; height: 80px; border-radius: 50%;
            background: #fee2e2; color: #dc2626;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem; margin: 0 auto 16px;
        }
        .error-title {
            font-size: 1.15rem; font-weight: 700; color: var(--text-main);
            margin-bottom: 6px; text-align: center;
        }
        .error-text {
            color: var(--text-muted); font-size: .85rem;
            line-height: 1.6; text-align: center; margin-bottom: 20px;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap" style="padding-bottom: calc(var(--footer-h) + 20px);">

        {{-- Page Strip --}}
        <div class="page-strip page-strip-error">
            <div class="live-badge">
                <span class="live-dot" style="background: #fca5a5;"></span>
                Error
            </div>
            <h2>
                <i class="fas fa-exclamation-triangle"></i>
                Tidak Dapat Melakukan Absen
            </h2>
            <p>Maaf, ada masalah dengan absensi Anda</p>
        </div>

        {{-- Error Card --}}
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:#fee2e2; color:#dc2626;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>Akses Ditolak</h3>
                <span class="hbadge" style="background:#fee2e2; color:#dc2626;">Error</span>
            </div>
            <div class="c-body" style="padding: 28px 20px; text-align:center;">
                <div class="error-icon-wrap">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="error-title">Absen Gagal</div>
                <div class="error-text">
                    {{ $message ?? 'Terjadi kesalahan yang tidak diketahui.' }}
                </div>
                <a href="{{ url()->previous() }}" class="action-btn btn-view" style="margin:0 auto;">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

    </div>
@endsection

