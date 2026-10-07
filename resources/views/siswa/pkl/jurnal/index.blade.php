@extends('layouts.app')
@section('title', 'Jurnal PKL Saya')

@push('styles')
    @include('components.event-styles')
    <style>
        .pkl-wrap { padding:0 12px; max-width:640px; margin:0 auto; }
        .jurnal-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; margin-bottom:10px; }
        .jurnal-head { display:flex; align-items:flex-start; justify-content:space-between; padding:12px 16px 8px; gap:8px; }
        .jurnal-date { font-size:.75rem; font-weight:700; color:#64748b; white-space:nowrap; }
        .jurnal-status { font-size:.65rem; font-weight:700; padding:2px 8px; border-radius:20px; flex-shrink:0; }
        .js-diajukan { background:#fef3c7; color:#92400e; }
        .js-disetujui { background:#dcfce7; color:#15803d; }
        .js-revisi { background:#fee2e2; color:#dc2626; }
        .jurnal-kegiatan { font-size:.83rem; color:#0f172a; padding:0 16px 8px; line-height:1.5; }
        .jurnal-footer { padding:8px 16px 12px; display:flex; align-items:center; gap:6px; border-top:1px solid #f8fafc; flex-wrap:wrap; }
        .jurnal-meta { font-size:.7rem; color:#94a3b8; }
        .jurnal-catatan { background:#fef2f2; color:#dc2626; font-size:.75rem; padding:8px 16px; border-top:1px solid #fee2e2; }
        .action-bar { position:fixed; bottom:var(--footer-h,0); left:0; right:0; padding:10px 12px 12px; background:rgba(255,255,255,.96); backdrop-filter:blur(10px); border-top:1px solid #e2e8f0; display:flex; gap:8px; z-index:999; }
        @media(min-width:768px){ .action-bar { padding:10px 24px 12px; justify-content:flex-end; } }
        .ab-btn { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:12px 16px; border-radius:12px; font-size:.82rem; font-weight:700; border:none; cursor:pointer; text-decoration:none; font-family:inherit; }
        @media(min-width:768px){ .ab-btn { flex:unset; min-width:120px; } }
        .ab-btn-amber { background:#f59e0b; color:#fff; }
        .ab-btn-back { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
    </style>
@endpush

@section('content')
<div class="pkl-wrap" style="padding-top:var(--header-h,56px);padding-bottom:120px;">

    <div class="page-strip page-strip-event" style="margin-top:12px;">
        <div class="live-badge"><span class="live-dot"></span>PKL</div>
        <h2><i class="fas fa-book"></i> Jurnal PKL Saya</h2>
        <p>Semua catatan kegiatan harianmu di {{ $penugasan->lokasiPkl?->nama_tempat }}.</p>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @forelse($jurnalList as $j)
        <div class="jurnal-card">
            <div class="jurnal-head">
                <div>
                    <div style="font-weight:700;font-size:.88rem;color:#0f172a;">{{ $j->tanggal->translatedFormat('l, d F Y') }}</div>
                    <div class="jurnal-date">
                        @if($j->jam_datang)<i class="fas fa-sign-in-alt"></i> {{ \Carbon\Carbon::parse($j->jam_datang)->format('H:i') }}@endif
                        @if($j->jam_pulang) – {{ \Carbon\Carbon::parse($j->jam_pulang)->format('H:i') }}@endif
                    </div>
                </div>
                <span class="jurnal-status js-{{ $j->status_verifikasi }}">{{ $j->status_verifikasi_label }}</span>
            </div>
            <div class="jurnal-kegiatan">{{ \Illuminate\Support\Str::limit($j->kegiatan, 150) }}</div>
            @if($j->catatan_pembimbing)
                <div class="jurnal-catatan"><i class="fas fa-comment-alt"></i> <strong>Catatan pembimbing:</strong> {{ $j->catatan_pembimbing }}</div>
            @endif
            <div class="jurnal-footer">
                @if($j->foto)
                    <span class="jurnal-meta"><i class="fas fa-image"></i> Ada foto</span>
                @endif
                @if($j->hasil)
                    <span class="jurnal-meta"><i class="fas fa-check"></i> Ada hasil</span>
                @endif
                @if($j->status_verifikasi !== 'disetujui')
                    <a href="{{ route('siswa.pkl.jurnal.edit', $j) }}" class="action-btn btn-edit" style="font-size:.7rem;padding:4px 10px;text-decoration:none;margin-left:auto;">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="card" style="border-radius:12px;">
            <div style="text-align:center;padding:48px 20px;color:#94a3b8;">
                <i class="fas fa-book" style="font-size:2.5rem;opacity:.2;display:block;margin-bottom:12px;"></i>
                <strong style="display:block;color:#0f172a;margin-bottom:4px;">Belum ada jurnal</strong>
                Mulai isi jurnal harianmu sekarang!
            </div>
        </div>
    @endforelse

    {{-- Pagination --}}
    @if($jurnalList->hasPages())
        <div style="display:flex;justify-content:center;gap:6px;margin-top:8px;flex-wrap:wrap;">
            @if($jurnalList->onFirstPage())
                <span class="pg-btn disabled"><i class="fas fa-angle-left"></i></span>
            @else
                <a href="{{ $jurnalList->previousPageUrl() }}" class="pg-btn"><i class="fas fa-angle-left"></i></a>
            @endif
            <span class="pg-btn active">{{ $jurnalList->currentPage() }}/{{ $jurnalList->lastPage() }}</span>
            @if($jurnalList->hasMorePages())
                <a href="{{ $jurnalList->nextPageUrl() }}" class="pg-btn"><i class="fas fa-angle-right"></i></a>
            @else
                <span class="pg-btn disabled"><i class="fas fa-angle-right"></i></span>
            @endif
        </div>
    @endif
</div>

<div class="action-bar">
    <a href="{{ route('siswa.pkl.dashboard') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
    <a href="{{ route('siswa.pkl.jurnal.create') }}" class="ab-btn ab-btn-amber"><i class="fas fa-plus"></i> Jurnal Baru</a>
</div>
@endsection
