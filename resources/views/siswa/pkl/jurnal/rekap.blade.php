@extends('layouts.app')
@section('title', 'Rekap Jurnal PKL Saya')

@push('styles')
    @include('components.event-styles')
    <style>
        .pkl-wrap { padding:0 12px; max-width:700px; margin:0 auto; }

        /* Card penugasan ringkasan */
        .penugasan-summary { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:16px; margin-bottom:12px; }
        .penugasan-summary-head { display:flex; align-items:flex-start; gap:12px; margin-bottom:10px; }
        .pen-icon { width:42px; height:42px; border-radius:10px; background:#fef3c7; color:#b45309; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:.9rem; }
        .pen-name { font-weight:800; color:#0f172a; font-size:.9rem; }
        .pen-sub  { font-size:.73rem; color:#64748b; margin-top:2px; }

        .mini-stats { display:flex; gap:8px; flex-wrap:wrap; }
        .mini-stat  { background:#f8fafc; border-radius:8px; padding:7px 12px; text-align:center; flex:1; min-width:60px; }
        .mini-val   { font-size:1.15rem; font-weight:800; }
        .mini-lbl   { font-size:.6rem; color:#64748b; font-weight:600; text-transform:uppercase; }

        /* Jurnal list */
        .jurnal-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; margin-bottom:10px; }
        .jurnal-head { display:flex; align-items:flex-start; justify-content:space-between; padding:12px 16px 8px; gap:8px; }
        .jurnal-kegiatan { font-size:.83rem; color:#0f172a; padding:0 16px 8px; line-height:1.5; }
        .jurnal-footer { padding:8px 16px 12px; display:flex; align-items:center; gap:6px; border-top:1px solid #f8fafc; flex-wrap:wrap; }
        .jurnal-catatan { background:#fef2f2; color:#dc2626; font-size:.75rem; padding:8px 16px; border-top:1px solid #fee2e2; }
        .jurnal-status { font-size:.65rem; font-weight:700; padding:2px 8px; border-radius:20px; flex-shrink:0; }
        .js-diajukan  { background:#fef3c7; color:#92400e; }
        .js-disetujui { background:#dcfce7; color:#15803d; }
        .js-revisi    { background:#fee2e2; color:#dc2626; }

        .badge-selesai { background:#dbeafe; color:#1d4ed8; padding:2px 8px; border-radius:20px; font-size:.65rem; font-weight:700; }
        .badge-aktif   { background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:20px; font-size:.65rem; font-weight:700; }

        .section-label { font-size:.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; margin:16px 0 8px; }

        .action-bar { position:fixed; bottom:var(--footer-h,0); left:0; right:0; padding:10px 12px 12px; background:rgba(255,255,255,.96); backdrop-filter:blur(10px); border-top:1px solid #e2e8f0; display:flex; gap:8px; z-index:999; }
        @media(min-width:768px){ .action-bar { padding:10px 24px 12px; justify-content:flex-end; } }
        .ab-btn { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:12px 16px; border-radius:12px; font-size:.82rem; font-weight:700; border:none; cursor:pointer; text-decoration:none; font-family:inherit; }
        @media(min-width:768px){ .ab-btn { flex:unset; min-width:120px; } }
        .ab-btn-amber { background:#f59e0b; color:#fff; }
        .ab-btn-back  { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }

        .info-banner { background:linear-gradient(135deg,#f0fdf4,#dcfce7); border:1px solid #86efac; color:#166534; border-radius:12px; padding:14px 16px; margin-bottom:14px; font-size:.83rem; }
        .info-banner-selesai { background:linear-gradient(135deg,#eff6ff,#dbeafe); border:1px solid #93c5fd; color:#1d4ed8; }
    </style>
@endpush

@section('content')
<div class="pkl-wrap" style="padding-top:var(--header-h,56px);padding-bottom:120px;">

    <div class="page-strip page-strip-event" style="margin-top:12px;">
        <div class="live-badge"><span class="live-dot"></span>PKL</div>
        <h2><i class="fas fa-history"></i> Rekap Jurnal PKL Saya</h2>
        <p>Riwayat semua kegiatan selama Praktik Kerja Lapangan.</p>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Summary per penugasan --}}
    @foreach($statPerPenugasan as $stat)
        @php $p = $stat['penugasan']; @endphp
        <div class="penugasan-summary">
            <div class="penugasan-summary-head">
                <div class="pen-icon"><i class="fas fa-building"></i></div>
                <div style="flex:1;">
                    <div class="pen-name">{{ $p->lokasiPkl?->nama_tempat ?? 'Lokasi PKL' }}</div>
                    <div class="pen-sub">
                        {{ $p->tanggal_mulai->format('d M Y') }} – {{ $p->tanggal_selesai->format('d M Y') }}
                        @if($p->gtk) · Pembimbing: {{ $p->gtk->nama_lengkap }} @endif
                    </div>
                    <div style="margin-top:5px;">
                        <span class="badge-{{ $p->status }}">{{ $p->status === 'aktif' ? 'Sedang Berjalan' : 'Selesai' }}</span>
                    </div>
                </div>
            </div>
            <div class="mini-stats">
                <div class="mini-stat">
                    <div class="mini-val" style="color:#6366f1;">{{ $stat['total_jurnal'] }}</div>
                    <div class="mini-lbl">Total Jurnal</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-val" style="color:#16a34a;">{{ $stat['disetujui'] }}</div>
                    <div class="mini-lbl">Disetujui</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-val" style="color:#f59e0b;">{{ $stat['menunggu'] }}</div>
                    <div class="mini-lbl">Menunggu</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-val" style="color:#dc2626;">{{ $stat['revisi'] }}</div>
                    <div class="mini-lbl">Revisi</div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Info banner berdasarkan status --}}
    @php
        $adaAktif   = $penugasanList->where('status','aktif')->count() > 0;
        $adaSelesai = $penugasanList->where('status','selesai')->count() > 0;
    @endphp

    @if($adaAktif)
        <div class="info-banner">
            <i class="fas fa-info-circle"></i>
            Kamu masih dalam periode PKL aktif. Kamu bisa tetap mengisi jurnal harian.
            <a href="{{ route('siswa.pkl.dashboard') }}" style="font-weight:700;color:#15803d;margin-left:6px;text-decoration:none;">
                Ke Dashboard PKL →
            </a>
        </div>
    @elseif($adaSelesai)
        <div class="info-banner info-banner-selesai">
            <i class="fas fa-graduation-cap"></i>
            PKL kamu sudah selesai. Halaman ini hanya untuk melihat riwayat jurnal — tidak bisa diedit lagi.
        </div>
    @endif

    {{-- Daftar jurnal semua penugasan --}}
    <div class="section-label">Semua Jurnal ({{ $semuaJurnal->total() }} entri)</div>

    @forelse($semuaJurnal as $j)
        <div class="jurnal-card">
            <div class="jurnal-head">
                <div>
                    <div style="font-weight:700;font-size:.88rem;color:#0f172a;">
                        {{ $j->tanggal->translatedFormat('l, d F Y') }}
                    </div>
                    <div style="font-size:.72rem;color:#64748b;">
                        @if($j->jam_datang)<i class="fas fa-sign-in-alt"></i> {{ \Carbon\Carbon::parse($j->jam_datang)->format('H:i') }}@endif
                        @if($j->jam_pulang) – {{ \Carbon\Carbon::parse($j->jam_pulang)->format('H:i') }}@endif
                        · {{ $j->lokasiPkl?->nama_tempat ?? '-' }}
                    </div>
                </div>
                <span class="jurnal-status js-{{ $j->status_verifikasi }}">{{ $j->status_verifikasi_label }}</span>
            </div>

            <div class="jurnal-kegiatan">{{ \Illuminate\Support\Str::limit($j->kegiatan, 200) }}</div>

            @if($j->catatan_pembimbing)
                <div class="jurnal-catatan">
                    <i class="fas fa-comment-alt"></i>
                    <strong>Catatan pembimbing:</strong> {{ $j->catatan_pembimbing }}
                </div>
            @endif

            <div class="jurnal-footer">
                @if($j->foto)
                    <span style="font-size:.7rem;color:#64748b;"><i class="fas fa-image"></i> Foto tersedia</span>
                @endif
                @if($j->hasil)
                    <span style="font-size:.7rem;color:#64748b;"><i class="fas fa-check-circle" style="color:#16a34a;"></i> Ada hasil</span>
                @endif
                @if($j->kendala)
                    <span style="font-size:.7rem;color:#64748b;"><i class="fas fa-exclamation-circle" style="color:#f59e0b;"></i> Ada kendala</span>
                @endif

                {{-- Siswa PKL aktif masih bisa edit jurnal yang belum disetujui --}}
                @if($adaAktif && $j->penugasan?->status === 'aktif' && $j->status_verifikasi !== 'disetujui')
                    <a href="{{ route('siswa.pkl.jurnal.edit', $j) }}" class="action-btn btn-edit" style="font-size:.7rem;padding:4px 10px;text-decoration:none;margin-left:auto;">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="card" style="border-radius:12px;">
            <div style="text-align:center;padding:40px 20px;color:#94a3b8;">
                <i class="fas fa-book" style="font-size:2.5rem;opacity:.2;display:block;margin-bottom:12px;"></i>
                <strong style="display:block;color:#0f172a;margin-bottom:4px;">Belum ada jurnal</strong>
                Belum ada catatan jurnal PKL yang diisi.
            </div>
        </div>
    @endforelse

    {{-- Pagination --}}
    @if($semuaJurnal->hasPages())
        <div style="display:flex;justify-content:center;gap:6px;margin-top:8px;flex-wrap:wrap;">
            @if($semuaJurnal->onFirstPage())
                <span class="pg-btn disabled"><i class="fas fa-angle-left"></i></span>
            @else
                <a href="{{ $semuaJurnal->previousPageUrl() }}" class="pg-btn"><i class="fas fa-angle-left"></i></a>
            @endif
            <span class="pg-btn active">{{ $semuaJurnal->currentPage() }}/{{ $semuaJurnal->lastPage() }}</span>
            @if($semuaJurnal->hasMorePages())
                <a href="{{ $semuaJurnal->nextPageUrl() }}" class="pg-btn"><i class="fas fa-angle-right"></i></a>
            @else
                <span class="pg-btn disabled"><i class="fas fa-angle-right"></i></span>
            @endif
        </div>
    @endif
</div>

<div class="action-bar">
    <a href="{{ route('dashboard') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
    @if($adaAktif)
        <a href="{{ route('siswa.pkl.dashboard') }}" class="ab-btn ab-btn-amber">
            <i class="fas fa-tachometer-alt"></i> Dashboard PKL
        </a>
    @endif
</div>
@endsection
