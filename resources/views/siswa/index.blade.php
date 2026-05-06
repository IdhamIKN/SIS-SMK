@extends('layouts.app')

@section('title', 'Daftar Siswa')

@push('styles')
    @include('components.izin-styles')
    <style>
        /* ── Stat grid 3 kolom ── */
        .stat-3-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 14px;
        }
        .stat-mini {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 14px;
            padding: 14px 10px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .stat-mini .s-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
            margin: 0 auto 6px;
        }
        .stat-mini .s-val { font-size: 1.4rem; font-weight: 800; line-height: 1; }
        .stat-mini .s-lbl { font-size: .65rem; font-weight: 600; color: var(--text-muted, #64748b); text-transform: uppercase; letter-spacing: .04em; margin-top: 3px; }

        /* ── Search card ── */
        .search-card {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .search-input-wrap {
            position: relative; margin-bottom: 10px;
        }
        .search-input-wrap .s-icon-left {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted, #64748b); font-size: .85rem;
        }
        .search-input-wrap input {
            width: 100%; padding: 10px 12px 10px 34px;
            border: 1px solid var(--border, #e2e8f0); border-radius: 10px;
            font-size: .875rem; font-family: inherit;
            color: var(--text-main, #0f172a); background: #f8fafc;
            outline: none; box-sizing: border-box; transition: border-color .2s, box-shadow .2s;
        }
        .search-input-wrap input:focus {
            border-color: #7c3aed; background: #fff;
            box-shadow: 0 0 0 3px rgba(124,58,237,.1);
        }
        .filter-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px; }
        .filter-select {
            width: 100%; padding: 9px 10px;
            border: 1px solid var(--border, #e2e8f0); border-radius: 10px;
            font-size: .82rem; font-family: inherit;
            color: var(--text-main, #0f172a); background: #f8fafc;
            outline: none; box-sizing: border-box;
            appearance: none; -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center;
            padding-right: 28px;
        }
        .btn-search {
            width: 100%; padding: 11px 16px;
            background: #7c3aed; color: #fff;
            border: none; border-radius: 10px;
            font-size: .875rem; font-weight: 700; font-family: inherit;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 7px;
            margin-bottom: 10px; transition: filter .18s;
        }
        .btn-search:hover { filter: brightness(1.08); }

        .export-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .btn-export {
            padding: 9px 10px; border-radius: 10px; font-size: .78rem; font-weight: 700;
            font-family: inherit; border: none; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            text-decoration: none; transition: filter .18s;
        }
        .btn-export:active { transform: scale(.97); }
        .btn-dl  { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }
        .btn-dl:hover { background: #dcfce7; }
        .btn-ul  { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .btn-ul:hover { background: #dbeafe; }



        /* ── Siswa card ── */
        .siswa-avatar {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: #ede9fe;
            display: flex; align-items: center; justify-content: center;
            color: #7c3aed; font-size: 1.2rem;
            flex-shrink: 0; overflow: hidden;
        }
        .siswa-avatar img { width: 100%; height: 100%; object-fit: cover; }

        .izin-meta { display: flex; flex-wrap: wrap; gap: 8px; font-size: .75rem; color: var(--text-muted); margin-bottom: 10px; }
        .izin-meta span { display: inline-flex; align-items: center; gap: 4px; }

        .action-group { display: flex; gap: 8px; flex-wrap: wrap; }
        .action-btn {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 7px 12px; border-radius: 8px;
            font-size: .78rem; font-weight: 600;
            border: none; cursor: pointer;
            text-decoration: none; font-family: inherit;
            transition: all .18s; white-space: nowrap;
        }
        .action-btn:active { transform: scale(.96); }
        .btn-view   { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .btn-view:hover   { background: #dbeafe; }
        .btn-edit   { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .btn-edit:hover   { background: #fef3c7; }
        .btn-delete { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .btn-delete:hover { background: #fee2e2; }

        .status-approved { background: #dcfce7; color: #15803d; }
        .status-rejected { background: #fee2e2; color: #dc2626; }

        /* ── FAB ── */
        .fab-add {
            position: fixed; bottom: calc(var(--footer-h) + 16px); right: 16px;
            background: #7c3aed; color: #fff;
            padding: 13px 20px; border-radius: 50px;
            font-size: .875rem; font-weight: 700;
            display: inline-flex; align-items: center; gap: 7px;
            text-decoration: none; box-shadow: 0 4px 20px rgba(124,58,237,.35);
            z-index: 900; transition: all .2s;
        }
        .fab-add:hover { filter: brightness(1.08); transform: translateY(-1px); }
        .fab-add:active { transform: scale(.97); }
    </style>
@endpush

@section('content')
<div class="izin-wrap" style="padding-bottom: calc(var(--footer-h) + 88px);">

    {{-- ── Page Strip ── --}}
    <div class="page-strip page-strip-izin">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
        <h2>
            <i class="fas fa-users"></i>
            Daftar Siswa
        </h2>
        <p>Kelola data siswa SMKN 5 Madiun</p>
    </div>

    {{-- ── Alerts ── --}}
    @if (session('success'))
        <div class="alert a-ok">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert a-err">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
        </div>
    @endif

    {{-- ── Stat Grid 3 kolom ── --}}
    <div class="stat-3-grid">
        <div class="stat-mini">
            <div class="s-icon" style="background:#ede9fe; color:#7c3aed;">
                <i class="fas fa-users"></i>
            </div>
            <div class="s-val" style="color:#7c3aed;">{{ $siswas->total() ?? $siswas->count() }}</div>
            <div class="s-lbl">Total</div>
        </div>
        <div class="stat-mini">
            <div class="s-icon" style="background:#dcfce7; color:#15803d;">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="s-val" style="color:#15803d;">{{ $aktifCount ?? 0 }}</div>
            <div class="s-lbl">Aktif</div>
        </div>
        <div class="stat-mini">
            <div class="s-icon" style="background:#fee2e2; color:#dc2626;">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="s-val" style="color:#dc2626;">{{ $nonAktifCount ?? 0 }}</div>
            <div class="s-lbl">Non Aktif</div>
        </div>
    </div>

    {{-- ── Search & Filter Card ── --}}
    <div class="search-card">
        <form method="GET" action="{{ route('siswa.index') }}">

            {{-- Input pencarian --}}
            <div class="search-input-wrap">
                <i class="fas fa-search s-icon-left"></i>
                <input type="text" name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama / NIS / NISN...">
            </div>

            {{-- Filter kelas & status --}}
            <div class="filter-row">
                <select name="kelas_id" class="filter-select">
                    <option value="">Semua Kelas</option>
                    @foreach ($kelas as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>
                <select name="status_aktif" class="filter-select">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status_aktif') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status_aktif') === '0' ? 'selected' : '' }}>Non Aktif</option>
                </select>
            </div>

            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i> Cari Siswa
            </button>
        </form>

        {{-- Export / Import --}}
        <div class="export-row">
            <a href="{{ route('siswa.export') }}" class="btn-export btn-dl">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ route('siswa.import.form') }}" class="btn-export btn-ul">
                <i class="fas fa-file-upload"></i> Import Excel
            </a>
        </div>


    </div>

    {{-- ── Daftar Siswa ── --}}
    @if ($siswas->count() > 0)
        @foreach ($siswas as $siswa)
            <div class="card izin-item">
                <div class="c-head">
                    <div class="siswa-avatar">
                        @if ($siswa->foto)
                            <img src="{{ Storage::url($siswa->foto) }}" alt="{{ $siswa->nama_lengkap }}">
                        @else
                            <i class="fas fa-user-graduate"></i>
                        @endif
                    </div>
                    <div style="flex:1; min-width:0;">
                        <h3 style="margin:0 0 2px; font-size:.9rem; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ $siswa->nama_lengkap }}
                        </h3>
                        <p style="margin:0; font-size:.72rem; color:var(--text-muted);">
                            {{ $siswa->nis }}{{ $siswa->nisn ? ' / ' . $siswa->nisn : '' }}
                        </p>
                    </div>
                    <span class="hbadge badge-status {{ $siswa->status_aktif ? 'status-approved' : 'status-rejected' }}">
                        {{ $siswa->status_aktif ? 'Aktif' : 'Non Aktif' }}
                    </span>
                </div>

                <div class="c-body" style="padding:10px 16px 14px;">
                    <div class="izin-meta">
                        <span><i class="fas fa-door-open"></i> {{ $siswa->kelas?->nama_kelas ?? '-' }}</span>
                        <span>
                            <i class="fas fa-{{ $siswa->jenis_kelamin === 'L' ? 'mars' : 'venus' }}"></i>
                            {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </span>
                    </div>

                    <div class="action-group">
                        <a href="{{ route('siswa.show', $siswa) }}" class="action-btn btn-view">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                        <a href="{{ route('siswa.edit', $siswa) }}" class="action-btn btn-edit">
                            <i class="fas fa-pen"></i> Edit
                        </a>
                        <button type="button" class="action-btn btn-delete"
                                onclick="confirmDelete('{{ route('siswa.destroy', $siswa) }}')">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Pagination --}}
        @if ($siswas->hasPages())
            <div class="pagination-chips">
                @if (!$siswas->onFirstPage())
                    <a href="{{ $siswas->previousPageUrl() }}" class="page-chip">← Sebelumnya</a>
                @endif
                <span class="page-chip active">{{ $siswas->currentPage() }} / {{ $siswas->lastPage() }}</span>
                @if ($siswas->hasMorePages())
                    <a href="{{ $siswas->nextPageUrl() }}" class="page-chip">Berikutnya →</a>
                @endif
            </div>
        @endif

    @else
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-users"></i></div>
            <h3 class="empty-title">Belum ada data siswa</h3>
            <p class="empty-text">Mulai tambahkan data siswa atau import dari file Excel.</p>
            <a href="{{ route('siswa.create') }}" class="btn-sub">
                <i class="fas fa-plus"></i> Tambah Siswa Pertama
            </a>
        </div>
    @endif

</div>

{{-- FAB Tambah Siswa --}}
<a href="{{ route('siswa.create') }}" class="fab-add">
    <i class="fas fa-plus"></i> Tambah Siswa
</a>
@endsection

@push('scripts')

@endpush