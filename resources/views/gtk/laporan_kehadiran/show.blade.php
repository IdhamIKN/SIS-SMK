@extends('layouts.app')

@section('title', 'Detail Laporan Kehadiran')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ── Wrapper ── */
        .lkg-wrap { padding: 0 12px; max-width: 720px; margin: 0 auto; box-sizing: border-box; }
        @media(min-width:768px)  { .lkg-wrap { padding: 0 20px; } }
        @media(min-width:1024px) { .lkg-wrap { padding: 0 24px; } }

        /* ── Status hero ── */
        .status-hero {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 16px;
            margin-bottom: 16px;
            border-radius: 14px;
            gap: 10px;
        }
        .status-hero-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
        .status-hero-label { font-size: 1rem; font-weight: 800; }
        @media(min-width:480px) { .status-hero-label { font-size: 1.1rem; } }

        .status-hijau  { background: #dcfce7; color: #15803d; }
        .status-kuning { background: #fef9c3; color: #a16207; }
        .status-merah  { background: #fee2e2; color: #b91c1c; }
        .status-abu    { background: #f1f5f9; color: #475569; }
        .status-biru   { background: #dbeafe; color: #1d4ed8; }
        .status-pink   { background: #fce7f3; color: #be185d; }
        .status-orange { background: #ffedd5; color: #c2410c; }
        .status-putih  { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

        /* ── Info card ── */
        .info-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .info-card-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
            font-size: .85rem;
            font-weight: 700;
            color: #0f172a;
        }
        .info-card-head i { color: #f59e0b; }

        /* ── Info rows ── */
        .info-row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: .84rem;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label {
            flex-shrink: 0;
            width: 120px;
            font-size: .75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .03em;
            padding-top: 1px;
        }
        @media(min-width:480px) { .info-label { width: 140px; } }
        .info-value { flex: 1; color: #0f172a; line-height: 1.5; }

        /* ── Badges ── */
        .badge-inline {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 9px; border-radius: 20px;
            font-size: .72rem; font-weight: 700;
        }
        .badge-wa-ok  { background: #dcfce7; color: #15803d; }
        .badge-wa-no  { background: #f1f5f9; color: #94a3b8; }

        /* ── Action bar ── */
        .action-bar { position:fixed; bottom:var(--footer-h,0); left:0; right:0; padding:10px 12px 12px; background:rgba(255,255,255,.96); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); border-top:1px solid #e2e8f0; display:flex; gap:8px; z-index:999; box-shadow:0 -4px 20px rgba(0,0,0,.06); }
        @media(min-width:768px) { .action-bar { padding:10px 24px 12px; gap:12px; justify-content:flex-end; } }
        .ab-btn { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:12px 14px; border-radius:12px; font-size:.82rem; font-weight:700; border:none; cursor:pointer; text-decoration:none; font-family:inherit; transition:all .18s; line-height:1; white-space:nowrap; }
        @media(min-width:768px) { .ab-btn { flex:unset; min-width:130px; font-size:.875rem; padding:12px 20px; } }
        .ab-btn:active { transform: scale(.97); }
        .ab-btn-back { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
        .ab-btn-edit { background:#f59e0b; color:#fff; box-shadow:0 3px 12px rgba(245,158,11,.3); }
        .ab-btn-delete { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
    </style>
@endpush

@section('content')
<div class="event-wrap lkg-wrap" style="padding-top:var(--header-h,56px);padding-bottom: 148px;">

    {{-- Page Strip --}}
    <div class="page-strip page-strip-event">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ $laporanKehadiran->tanggal->translatedFormat('d F Y') }}
        </div>
        <h2><i class="fas fa-clipboard-check"></i> Detail Laporan Kehadiran</h2>
        <p>Jam {{ $laporanKehadiran->jam_ke }}
            @if($laporanKehadiran->jadwalKbm?->mata_pelajaran)
                — {{ $laporanKehadiran->jadwalKbm->mata_pelajaran }}
            @endif
        </p>
    </div>

    {{-- Status Hero --}}
    <div class="status-hero status-{{ $laporanKehadiran->status }}">
        <div class="status-hero-dot" style="background:{{ $laporanKehadiran->status_color }};"></div>
        <span class="status-hero-label">{{ $laporanKehadiran->status_label }}</span>
    </div>

    {{-- Info Guru & Jadwal --}}
    <div class="info-card">
        <div class="info-card-head">
            <i class="fas fa-user-tie"></i> Informasi Guru &amp; Jadwal
        </div>
        <div class="info-row">
            <span class="info-label">Nama Guru</span>
            <span class="info-value" style="font-weight:600;">{{ $laporanKehadiran->gtk?->nama_lengkap ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Kelas</span>
            <span class="info-value">{{ $laporanKehadiran->kelas?->nama_kelas ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Mata Pelajaran</span>
            <span class="info-value">{{ $laporanKehadiran->jadwalKbm?->mata_pelajaran ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Jam Ke</span>
            <span class="info-value">Jam {{ $laporanKehadiran->jam_ke }}</span>
        </div>
        @if($laporanKehadiran->jadwalKbm?->jam_mulai)
        <div class="info-row">
            <span class="info-label">Waktu KBM</span>
            <span class="info-value">
                {{ \Carbon\Carbon::parse($laporanKehadiran->jadwalKbm->jam_mulai)->format('H:i') }}
                –
                {{ \Carbon\Carbon::parse($laporanKehadiran->jadwalKbm->jam_selesai)->format('H:i') }}
                WIB
            </span>
        </div>
        @endif
    </div>

    {{-- Info Laporan --}}
    <div class="info-card">
        <div class="info-card-head">
            <i class="fas fa-file-alt"></i> Informasi Laporan
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal</span>
            <span class="info-value">{{ $laporanKehadiran->tanggal->translatedFormat('l, d F Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Waktu Lapor</span>
            <span class="info-value">{{ $laporanKehadiran->waktu_laporan->format('H:i') }} WIB</span>
        </div>
        <div class="info-row">
            <span class="info-label">Dilaporkan Oleh</span>
            <span class="info-value">
                @if($laporanKehadiran->dilaporkan_oleh_siswa_id)
                    <i class="fas fa-user-graduate" style="color:#0ea5e9;margin-right:4px;"></i>
                    {{ $laporanKehadiran->dilaporkanOlehSiswa?->nama_lengkap ?? 'Siswa' }}
                @elseif(str_starts_with($laporanKehadiran->catatan ?? '', 'Auto-generated:'))
                    <i class="fas fa-robot" style="color:#8b5cf6;margin-right:4px;"></i>
                    Sistem
                @else
                    <i class="fas fa-user-tie" style="color:#64748b;margin-right:4px;"></i>
                    Guru Sendiri
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Notif WA</span>
            <span class="info-value">
                @if($laporanKehadiran->wa_terkirim)
                    <span class="badge-inline badge-wa-ok"><i class="fas fa-check-circle"></i> Terkirim</span>
                @else
                    <span class="badge-inline badge-wa-no"><i class="fas fa-clock"></i> Belum Terkirim</span>
                @endif
            </span>
        </div>
        @if($laporanKehadiran->catatan)
        <div class="info-row">
            <span class="info-label">Catatan</span>
            <span class="info-value" style="font-style:italic;color:#475569;">{{ $laporanKehadiran->catatan }}</span>
        </div>
        @endif
    </div>

</div>

{{-- Action Bar --}}
<div class="action-bar">
    <a href="{{ route('kehadiran-guru.laporan') }}" class="ab-btn ab-btn-back">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
    @can('kehadiran-guru.delete')
    <form method="POST" action="{{ route('kehadiran-guru.destroy', $laporanKehadiran) }}"
        style="flex:1;display:contents;" onsubmit="return confirmDeleteLaporan(event, this)">
        @csrf @method('DELETE')
        <button type="submit" class="ab-btn ab-btn-delete">
            <i class="fas fa-trash"></i> Hapus
        </button>
    </form>
    @endcan
    @can('kehadiran-guru.update')
    @if ($isPrivileged || $laporanKehadiran->tanggal->isToday())
    <a href="{{ route('kehadiran-guru.edit', $laporanKehadiran) }}" class="ab-btn ab-btn-edit">
        <i class="fas fa-pen"></i> Edit Laporan
    </a>
    @endif
    @endcan
</div>
@endsection

@push('scripts')
<script>
    function confirmDeleteLaporan(e, form) {
        e.preventDefault();
        Swal.fire({
            title: 'Hapus Laporan?',
            text: 'Data laporan ini akan dihapus permanen dan tidak dapat dipulihkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-trash"></i> Hapus',
            cancelButtonText: 'Batal',
        }).then(r => { if (r.isConfirmed) form.submit(); });
        return false;
    }
</script>
@endpush
