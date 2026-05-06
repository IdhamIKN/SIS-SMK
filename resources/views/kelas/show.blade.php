@extends('layouts.app')

@section('title', 'Detail Kelas - ' . $kela->nama_kelas)

@push('styles')
    @include('components.izin-styles')
    <style>
        /* ── Profile hero ── */
        .profile-hero {
            position: relative;
            background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 60%, #a78bfa 100%);
            padding: 24px 20px 64px; overflow: hidden;
        }
        .profile-hero::before {
            content: ''; position: absolute; top: -50px; right: -50px;
            width: 160px; height: 160px; background: rgba(255,255,255,.07); border-radius: 50%;
        }
        .profile-hero::after {
            content: ''; position: absolute; bottom: -30px; left: -20px;
            width: 120px; height: 120px; background: rgba(255,255,255,.05); border-radius: 50%;
        }
        .hero-nav {
            position: relative; z-index: 2;
            display: flex; align-items: center; justify-content: space-between;
        }
        .hero-back {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(255,255,255,.18); backdrop-filter: blur(6px);
            display: flex; align-items: center; justify-content: center;
            color: #fff; text-decoration: none; font-size: .9rem;
            border: 1px solid rgba(255,255,255,.25);
        }
        .hero-actions { display: flex; gap: 8px; }
        .hero-action-btn {
            padding: 7px 14px; border-radius: 10px;
            background: rgba(255,255,255,.18); backdrop-filter: blur(6px);
            color: #fff; font-size: .75rem; font-weight: 700;
            text-decoration: none; border: 1px solid rgba(255,255,255,.25);
            display: inline-flex; align-items: center; gap: 5px; transition: background .18s;
        }
        .hero-action-btn:hover { background: rgba(255,255,255,.28); }

        /* ── Profile card overlap ── */
        .profile-card {
            position: relative; z-index: 3;
            background: #fff; border-radius: 20px;
            box-shadow: 0 6px 28px rgba(0,0,0,.12);
            margin: -44px 16px 0; padding: 20px;
            display: flex; align-items: center; gap: 16px;
        }
        .profile-avatar {
            width: 72px; height: 72px; border-radius: 16px;
            border: 3px solid #fff; box-shadow: 0 2px 12px rgba(0,0,0,.15);
            flex-shrink: 0; overflow: hidden;
            background: #ede9fe; display: flex; align-items: center;
            justify-content: center; color: #7c3aed; font-size: 1.6rem;
        }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-info { flex: 1; min-width: 0; }
        .profile-name { font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0 0 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .profile-nisn  { font-size: .72rem; color: #64748b; margin: 0 0 6px; }
        .profile-tags  { display: flex; flex-wrap: wrap; gap: 5px; }
        .ptag { font-size: .65rem; font-weight: 700; padding: 2px 8px; border-radius: 20px; display: inline-flex; align-items: center; gap: 3px; }
        .ptag-purple { background: #ede9fe; color: #7c3aed; }
        .ptag-blue   { background: #dbeafe; color: #1d4ed8; }
        .ptag-green  { background: #dcfce7; color: #15803d; }
        .ptag-red    { background: #fee2e2; color: #dc2626; }

        /* ── Stat absen 4 kolom ── */
        .absen-stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 14px; }
        .absen-stat { background: #fff; border: 1px solid var(--border, #e2e8f0); border-radius: 12px; padding: 12px 8px; text-align: center; }
        .absen-stat .as-val { font-size: 1.3rem; font-weight: 800; line-height: 1; margin-bottom: 3px; }
        .absen-stat .as-lbl { font-size: .6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .03em; }

        /* ── Card ── */
        .card { background: #fff; border: 1px solid var(--border, #e2e8f0); border-radius: 16px; margin: 0 16px 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); overflow: hidden; }
        .c-head { display: flex; align-items: center; gap: 10px; padding: 14px 16px 10px; border-bottom: 1px solid #f8fafc; }
        .c-head h3 { margin: 0; font-size: .9rem; font-weight: 700; flex: 1; }
        .c-icon { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0; }
        .hbadge { font-size: .68rem; font-weight: 700; padding: 3px 9px; border-radius: 20px; background: #f1f5f9; color: #64748b; }

        /* ── Detail rows ── */
        .detail-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 9px 18px; font-size: .84rem; border-bottom: 1px solid #f1f5f9; }
        .detail-row:last-child { border-bottom: none; }
        .dr-label { color: #64748b; display: flex; align-items: center; gap: 7px; flex-shrink: 0; font-size: .78rem; min-width: 110px; }
        .dr-label i { width: 14px; text-align: center; }
        .dr-value { font-weight: 600; color: #0f172a; text-align: right; font-size: .82rem; word-break: break-word; max-width: 180px; }

        /* ── Siswa list ── */
        .siswa-list { padding: 0 16px; }
        .siswa-item {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
            margin-bottom: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.04); overflow: hidden;
        }
        .siswa-head {
            display: flex; align-items: center; gap: 12px; padding: 14px 16px 10px;
        }
        .siswa-ava {
            width: 48px; height: 48px; border-radius: 12px;
            background: #ede9fe; color: #7c3aed;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; flex-shrink: 0; overflow: hidden;
        }
        .siswa-ava img { width: 100%; height: 100%; object-fit: cover; }
        .siswa-name {
            margin: 0 0 2px; font-size: .9rem; font-weight: 700;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .siswa-sub { margin: 0; font-size: .72rem; color: #64748b; }
        .siswa-badge {
            font-size: .68rem; font-weight: 700; padding: 3px 9px;
            border-radius: 20px; flex-shrink: 0;
        }
        .siswa-badge.ok  { background: #dcfce7; color: #15803d; }
        .siswa-badge.off { background: #fee2e2; color: #dc2626; }
        .siswa-meta {
            display: flex; flex-wrap: wrap; gap: 8px;
            font-size: .75rem; color: #64748b; padding: 0 16px;
        }
        .siswa-meta span { display: inline-flex; align-items: center; gap: 4px; }
        .siswa-actions { display: flex; gap: 8px; padding: 10px 16px 14px; }
        .siswa-btn {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 7px 12px; border-radius: 8px; font-size: .78rem; font-weight: 600;
            border: none; cursor: pointer; text-decoration: none; font-family: inherit;
            transition: all .18s; white-space: nowrap;
        }
        .siswa-btn:active { transform: scale(.96); }
        .siswa-btn.view   { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }

        /* ── Pagination ── */
        .sw-pager {
            display: flex; align-items: center; justify-content: center;
            gap: 8px; padding: 12px 16px 0;
        }
        .sw-page-chip {
            padding: 7px 14px; border-radius: 20px; font-size: .78rem; font-weight: 700;
            background: #fff; border: 1px solid #e2e8f0; color: #475569; text-decoration: none;
        }
        .sw-page-chip.active { background: #7c3aed; color: #fff; border-color: #7c3aed; }

        /* ── Alert ── */
        .alert { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border-radius: 12px; font-size: .84rem; margin: 12px 16px 0; }
        .a-ok  { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .a-err { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

        /* ── Action bar ── */
        .action-bar {
            position: fixed; bottom: var(--footer-h); left: 0; right: 0;
            padding: 10px 16px 12px;
            background: rgba(255,255,255,.96); backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0; display: flex; gap: 8px;
            z-index: 999; box-shadow: 0 -4px 20px rgba(0,0,0,.06);
        }
        .ab-btn { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 12px 12px; border-radius: 12px; font-size: .82rem; font-weight: 700; border: none; cursor: pointer; text-decoration: none; font-family: inherit; transition: all .18s; line-height: 1; white-space: nowrap; }
        .ab-btn:active { transform: scale(.97); }
        .ab-btn-back   { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; flex: 0 0 auto; padding: 12px 14px; }
        .ab-btn-edit   { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .ab-btn-edit:hover { background: #fef3c7; }
        .ab-btn-primary { background: #7c3aed; color: #fff; box-shadow: 0 3px 12px rgba(124,58,237,.3); }
        .ab-btn-primary:hover { filter: brightness(1.08); }
        .ab-btn-delete { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; flex: 0 0 auto; padding: 12px 14px; }
        .ab-btn-delete:hover { background: #fecaca; }

        .dash-wrap { padding: 16px 0 calc(var(--footer-h) + 80px); }
        .empty-state { text-align: center; padding: 40px 20px; }
        .empty-state-ico { font-size: 3rem; color: #c4b5fd; margin-bottom: 12px; }
        .empty-state h3  { font-size: 1rem; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
        .empty-state p   { font-size: .84rem; color: #64748b; margin-bottom: 16px; }
    </style>
@endpush

@section('content')

{{-- ── Profile Hero ── --}}
<div class="profile-hero">
    <div class="hero-nav">
        <a href="{{ route('kelas.index') }}" class="hero-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="hero-actions">
            <a href="{{ route('kelas.edit', $kela) }}" class="hero-action-btn">
                <i class="fas fa-pen"></i> Edit
            </a>
        </div>
    </div>
</div>

{{-- ── Profile Card (overlap) ── --}}
<div class="profile-card">
    <div class="profile-avatar">
        <i class="fas fa-school"></i>
    </div>
    <div class="profile-info">
        <h3 class="profile-name">{{ $kela->nama_kelas }}</h3>
        <p class="profile-nisn">Tingkat {{ $kela->tingkat }}</p>
        <div class="profile-tags">
            <span class="ptag ptag-purple"><i class="fas fa-graduation-cap"></i> {{ $kela->jurusan?->nama_jurusan ?? '-' }}</span>
            <span class="ptag ptag-blue"><i class="fas fa-users"></i> {{ $kela->siswa_count }} Siswa</span>
            @if($kela->waliKelas)
                <span class="ptag ptag-green"><i class="fas fa-user-tie"></i> {{ $kela->waliKelas->nama_lengkap }}</span>
            @endif
        </div>
    </div>
</div>

<div class="dash-wrap">

    @if (session('success'))
        <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    {{-- ── Stat Siswa ── --}}
    @php
        $aktifCount = $kela->siswa()->where('status_aktif', true)->count();
        $nonAktifCount = $kela->siswa_count - $aktifCount;
    @endphp
    <div class="absen-stat-grid" style="margin: 14px 16px;">
        <div class="absen-stat">
            <div class="as-val" style="color:#15803d;">{{ $aktifCount }}</div>
            <div class="as-lbl">Aktif</div>
        </div>
        <div class="absen-stat">
            <div class="as-val" style="color:#dc2626;">{{ $nonAktifCount }}</div>
            <div class="as-lbl">Non Aktif</div>
        </div>
        <div class="absen-stat">
            <div class="as-val" style="color:#7c3aed;">{{ $kela->siswa_count }}</div>
            <div class="as-lbl">Total</div>
        </div>
        <div class="absen-stat">
            <div class="as-val" style="color:#b45309;">{{ $kela->tingkat }}</div>
            <div class="as-lbl">Tingkat</div>
        </div>
    </div>

    {{-- ── Data Kelas ── --}}
    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#ede9fe; color:#7c3aed;"><i class="fas fa-school"></i></div>
            <h3>Informasi Kelas</h3>
        </div>
        <div class="detail-row"><span class="dr-label"><i class="fas fa-tag"></i> Nama Kelas</span><span class="dr-value">{{ $kela->nama_kelas }}</span></div>
        <div class="detail-row"><span class="dr-label"><i class="fas fa-layer-group"></i> Tingkat</span><span class="dr-value">{{ $kela->tingkat }}</span></div>
        <div class="detail-row"><span class="dr-label"><i class="fas fa-graduation-cap"></i> Jurusan</span><span class="dr-value">{{ $kela->jurusan?->nama_jurusan ?? '-' }}</span></div>
        <div class="detail-row"><span class="dr-label"><i class="fas fa-user-tie"></i> Wali Kelas</span><span class="dr-value">{{ $kela->waliKelas?->nama_lengkap ?? '-' }}</span></div>
        <div class="detail-row"><span class="dr-label"><i class="fas fa-user-shield"></i> BK</span><span class="dr-value">{{ $kela->bk?->nama_lengkap ?? '-' }}</span></div>
        <div class="detail-row"><span class="dr-label"><i class="fas fa-users"></i> Jumlah Siswa</span><span class="dr-value">{{ $kela->siswa_count }}</span></div>
    </div>

    {{-- ── Daftar Siswa ── --}}
    @if ($siswa->count() > 0)
        <div class="siswa-list">
            @foreach ($siswa as $s)
                <div class="siswa-item">
                    <div class="siswa-head">
                        <div class="siswa-ava">
                            @if ($s->foto)
                                <img src="{{ Storage::url($s->foto) }}" alt="{{ $s->nama_lengkap }}">
                            @else
                                <i class="fas fa-user-graduate"></i>
                            @endif
                        </div>
                        <div style="flex:1; min-width:0;">
                            <h3 class="siswa-name">{{ $s->nama_lengkap }}</h3>
                            <p class="siswa-sub">{{ $s->nis ?? '-' }}{{ $s->nisn ? ' / ' . $s->nisn : '' }}</p>
                        </div>
                        <span class="siswa-badge {{ $s->status_aktif ? 'ok' : 'off' }}">
                            {{ $s->status_aktif ? 'Aktif' : 'Non Aktif' }}
                        </span>
                    </div>
                    <div class="siswa-meta">
                        <span><i class="fas fa-{{ $s->jenis_kelamin === 'L' ? 'mars' : 'venus' }}"></i> {{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                        <span><i class="fas fa-calendar"></i> {{ $s->absen_siswa_count }} Absensi</span>
                    </div>
                    <div class="siswa-actions">
                        <a href="{{ route('siswa.show', $s) }}" class="siswa-btn view"><i class="fas fa-eye"></i> Detail</a>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($siswa->hasPages())
            <div class="sw-pager">
                @if (!$siswa->onFirstPage())
                    <a href="{{ $siswa->previousPageUrl() }}" class="sw-page-chip">← Sebelumnya</a>
                @endif
                <span class="sw-page-chip active">{{ $siswa->currentPage() }} / {{ $siswa->lastPage() }}</span>
                @if ($siswa->hasMorePages())
                    <a href="{{ $siswa->nextPageUrl() }}" class="sw-page-chip">Berikutnya →</a>
                @endif
            </div>
        @endif

    @else
        <div class="empty-state">
            <div class="empty-state-ico"><i class="fas fa-users"></i></div>
            <h3>Belum ada siswa</h3>
            <p>Kelas ini belum memiliki siswa terdaftar.</p>
        </div>
    @endif

</div>

{{-- ── Action Bar ── --}}
<div class="action-bar">
    <a href="{{ route('kelas.index') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
    <a href="{{ route('kelas.edit', $kela) }}" class="ab-btn ab-btn-edit"><i class="fas fa-pen"></i> Edit</a>
    @if($kela->tingkat !== 'XII')
        <a href="{{ route('kelas.promote', $kela) }}" class="ab-btn ab-btn-primary"><i class="fas fa-arrow-up"></i> Promosi</a>
    @endif
    <button type="button" class="ab-btn ab-btn-delete" onclick="confirmDelete()"><i class="fas fa-trash-alt"></i></button>
</div>

<form id="deleteForm" method="POST" action="{{ route('kelas.destroy', $kela) }}" style="display:none;">
    @csrf @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function confirmDelete() {
    if (typeof Swal === 'undefined') {
        if (!confirm('Yakin menghapus kelas {{ addslashes($kela->nama_kelas) }}?')) return;
        document.getElementById('deleteForm').submit(); return;
    }
    Swal.fire({
        title: 'Hapus Kelas?',
        html: 'Yakin menghapus <strong>{{ addslashes($kela->nama_kelas) }}</strong>?<br><small style="color:#64748b;">Data tidak dapat dikembalikan.</small>',
        icon: 'warning', showCancelButton: true, reverseButtons: true, buttonsStyling: false,
        customClass: { confirmButton: 'ab-btn ab-btn-delete', cancelButton: 'ab-btn ab-btn-back' },
        confirmButtonText: '<i class="fas fa-trash-alt"></i> Hapus',
        cancelButtonText:  '<i class="fas fa-times"></i> Batal',
    }).then(r => { if (r.isConfirmed) document.getElementById('deleteForm').submit(); });
}
</script>
@endpush