@extends('layouts.app')

@section('title', 'Daftar Siswa')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ── Wrapper ── */
        .sw-wrap { padding: 0 12px; max-width: 1280px; margin: 0 auto; box-sizing: border-box; }
        @media(min-width:768px)  { .sw-wrap { padding: 0 20px; } }
        @media(min-width:1024px) { .sw-wrap { padding: 0 28px; } }

        /* ── Stat grid ── */
        .stat-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 8px; margin-bottom: 14px; }
        @media(min-width:480px) { .stat-grid { grid-template-columns: repeat(4,1fr); } }
        @media(min-width:768px) { .stat-grid { gap: 12px; margin-bottom: 20px; } }
        .stat-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:12px 8px; text-align:center; box-shadow:0 1px 4px rgba(0,0,0,.04); }
        @media(min-width:768px) { .stat-card { border-radius:12px; padding:16px; } }
        .stat-icon { width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:.95rem; margin:0 auto 6px; }
        .stat-value { font-size:1.3rem; font-weight:800; line-height:1; margin-bottom:3px; }
        @media(min-width:768px) { .stat-value { font-size:1.7rem; margin-bottom:5px; } }
        .stat-label { font-size:.6rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
        @media(min-width:768px) { .stat-label { font-size:.7rem; } }

        /* ── Filter section ── */
        .filter-section { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; margin-bottom:16px; overflow:hidden; }
        .filter-toggle { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; cursor:pointer; user-select:none; background:#f1f5f9; border:none; width:100%; font-family:inherit; font-size:.875rem; font-weight:700; color:#0f172a; gap:8px; }
        .filter-toggle .ft-left { display:flex; align-items:center; gap:8px; }
        .filter-toggle .ft-chevron { transition:transform .2s; color:#64748b; font-size:.8rem; }
        .filter-toggle.open .ft-chevron { transform:rotate(180deg); }
        @media(min-width:768px) { .filter-toggle { display:none; } }
        .filter-body { padding:12px 16px 16px; display:none; }
        .filter-body.open { display:block; }
        @media(min-width:768px) { .filter-body { display:block !important; padding:16px; } }
        .filter-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; align-items:end; }
        @media(max-width:479px) { .filter-grid { grid-template-columns:1fr; } }
        @media(min-width:768px) { .filter-grid { grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; } }
        .form-label { display:block; font-size:.8rem; font-weight:600; color:#0f172a; margin-bottom:5px; }
        .form-input { width:100%; padding:9px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:.875rem; font-family:inherit; color:#0f172a; background:#fff; box-sizing:border-box; -webkit-appearance:none; appearance:none; }
        .form-input:focus { outline:2px solid #7c3aed; outline-offset:-1px; }

        /* ── Shortcut links in filter ── */
        .sw-shortcuts { display:flex; gap:6px; flex-wrap:wrap; margin-top:10px; padding-top:10px; border-top:1px solid #f1f5f9; }
        .sw-shortcut { display:inline-flex; align-items:center; gap:5px; padding:7px 12px; border-radius:8px; font-size:.75rem; font-weight:700; text-decoration:none; border:1px solid #e2e8f0; background:#f8fafc; color:#475569; white-space:nowrap; }

        /* ── Card header ── */
        .c-head { display:flex; align-items:center; gap:10px; padding:12px 14px; border-bottom:1px solid #f1f5f9; flex-wrap:wrap; }
        @media(min-width:768px) { .c-head { padding:14px 18px; } }
        .c-head h3 { font-size:.9rem; font-weight:800; color:#0f172a; margin:0; flex:1; }
        @media(min-width:768px) { .c-head h3 { font-size:1rem; } }
        .hbadge { font-size:.7rem; font-weight:700; background:#f1f5f9; color:#475569; padding:3px 10px; border-radius:20px; }

        /* ── Bulk action bar ── */
        .bulk-bar { display:flex; align-items:center; gap:8px; flex-wrap:wrap; padding:10px 14px; border-bottom:1px solid #f1f5f9; background:#fafafa; }
        .bulk-check-label { display:inline-flex; align-items:center; gap:6px; font-size:.8rem; font-weight:700; color:#475569; margin-right:auto; }
        .bulk-btn { display:inline-flex; align-items:center; gap:5px; padding:7px 12px; border-radius:8px; font-size:.75rem; font-weight:700; border:none; cursor:pointer; font-family:inherit; white-space:nowrap; }
        .bulk-btn.archive { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
        .bulk-btn.restore { background:#f0fdf4; color:#15803d; border:1px solid #86efac; }

        /* ── Table desktop (≥768px) ── */
        .sw-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
        @media(max-width:767px) { .sw-table-wrap { display:none; } }
        .sw-table { width:100%; border-collapse:collapse; font-size:.78rem; background:#fff; }
        .sw-table thead tr { background:#f8fafc; border-bottom:2px solid #e2e8f0; }
        .sw-table th { padding:11px 10px; text-align:left; font-size:.68rem; font-weight:700; color:#64748b; white-space:nowrap; text-transform:uppercase; letter-spacing:.04em; }
        .sw-table td { padding:10px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
        .sw-table tbody tr:last-child td { border-bottom:none; }
        .sw-table tbody tr:hover td { background:#fafbfc; }

        /* ── Card list mobile (<768px) ── */
        .sw-card-list { display:none; }
        @media(max-width:767px) { .sw-card-list { display:flex; flex-direction:column; gap:0; } }
        .sw-card-item { padding:12px 14px; border-bottom:1px solid #f1f5f9; background:#fff; }
        .sw-card-item:last-child { border-bottom:none; }
        .swci-top { display:flex; align-items:center; gap:10px; margin-bottom:6px; }
        .swci-avatar { width:40px; height:40px; border-radius:10px; background:#ede9fe; color:#7c3aed; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; overflow:hidden; }
        .swci-avatar img { width:100%; height:100%; object-fit:cover; }
        .swci-name { font-weight:700; font-size:.88rem; color:#0f172a; margin:0 0 2px; }
        .swci-id { font-size:.7rem; color:#64748b; }
        .swci-meta { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:8px; }
        .swci-meta span { font-size:.7rem; color:#64748b; display:inline-flex; align-items:center; gap:3px; }
        .swci-actions { display:flex; gap:6px; flex-wrap:wrap; }

        /* ── Action buttons ── */
        .action-btn { display:inline-flex; align-items:center; gap:5px; padding:5px 10px; border-radius:7px; font-size:.72rem; font-weight:700; border:none; cursor:pointer; font-family:inherit; text-decoration:none; transition:opacity .15s; white-space:nowrap; }
        .action-btn:active { opacity:.75; }
        .btn-view    { background:#eff6ff; color:#1d4ed8; }
        .btn-edit    { background:#fffbeb; color:#b45309; border:1px solid #fde68a; }
        .btn-archive { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
        .btn-restore { background:#f0fdf4; color:#15803d; border:1px solid #86efac; }

        /* ── Status badges ── */
        .badge-status { display:inline-block; padding:3px 8px; border-radius:20px; font-size:.63rem; font-weight:700; white-space:nowrap; }
        .badge-aktif    { background:#dcfce7; color:#15803d; }
        .badge-nonaktif { background:#fee2e2; color:#dc2626; }
        .badge-arsip    { background:#fef3c7; color:#b45309; }

        /* ── Pagination ── */
        .rekap-pagination { padding:12px 14px; border-top:1px solid #f1f5f9; display:flex; justify-content:center; flex-wrap:wrap; gap:5px; }
        .pg-btn { display:inline-flex; align-items:center; justify-content:center; gap:5px; padding:8px 12px; border-radius:8px; font-size:.78rem; font-weight:700; text-decoration:none; font-family:inherit; border:1px solid #e2e8f0; background:#f1f5f9; color:#475569; cursor:pointer; transition:background .15s; white-space:nowrap; }
        .pg-btn:hover { background:#e2e8f0; }
        .pg-btn.active { background:#7c3aed; color:#fff; border-color:transparent; pointer-events:none; }
        .pg-btn.disabled { opacity:.4; cursor:not-allowed; pointer-events:none; }
        @media(max-width:479px) { .pg-num { display:none; } .pg-num.active { display:inline-flex; } }

        /* ── Empty state ── */
        .rekap-empty { text-align:center; padding:36px 20px; color:#64748b; }
        .rekap-empty i { font-size:2.5rem; opacity:.3; display:block; margin-bottom:10px; }
        .rekap-empty strong { display:block; color:#0f172a; margin-bottom:4px; font-size:.9rem; }

        /* ── FAB ── */
        .fab-add { position:fixed; bottom:calc(var(--footer-h,60px)+16px); right:16px; background:#7c3aed; color:#fff; padding:13px 20px; border-radius:50px; font-size:.875rem; font-weight:800; display:inline-flex; align-items:center; gap:7px; text-decoration:none; box-shadow:0 4px 20px rgba(124,58,237,.35); z-index:900; }
    </style>
@endpush

@section('content')
@php
    $showArchived = ($statusData ?? request('status_data')) === 'arsip';
    $modeParams   = request()->except('page', 'status_data');
    $hasFilter    = request()->hasAny(['search','kelas_id','status_aktif']);
@endphp

<div class="event-wrap sw-wrap" style="padding-top:var(--header-h,56px);padding-bottom: 148px;">

    {{-- Page Strip --}}
    <div class="page-strip page-strip-event">
        <div class="live-badge"><span class="live-dot"></span>Manajemen Siswa</div>
        <h2><i class="fas fa-users"></i> Daftar Siswa</h2>
        <p>Kelola data siswa — {{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-users"></i></div>
            <div class="stat-value" style="color:#7c3aed;">{{ $siswas->total() }}</div>
            <div class="stat-label">{{ $showArchived ? 'Hasil Arsip' : 'Hasil Filter' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;"><i class="fas fa-user-check"></i></div>
            <div class="stat-value" style="color:#15803d;">{{ $aktifCount ?? 0 }}</div>
            <div class="stat-label">Aktif</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-user-times"></i></div>
            <div class="stat-value" style="color:#dc2626;">{{ $nonAktifCount ?? 0 }}</div>
            <div class="stat-label">Non Aktif</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#b45309;"><i class="fas fa-box-archive"></i></div>
            <div class="stat-value" style="color:#b45309;">{{ $arsipCount ?? 0 }}</div>
            <div class="stat-label">Arsip</div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('siswa.index') }}" class="filter-section" id="swFilterForm">
        <input type="hidden" name="status_data" value="{{ $showArchived ? 'arsip' : 'aktif' }}">

        <button type="button" class="filter-toggle {{ $hasFilter ? 'open' : '' }}" id="swFilterToggle"
            onclick="toggleSwFilter()">
            <span class="ft-left">
                <i class="fas fa-filter"></i> Filter
                @if($hasFilter)
                    <span style="background:#ede9fe;color:#7c3aed;font-size:.65rem;padding:2px 8px;border-radius:20px;font-weight:700;">Aktif</span>
                @endif
            </span>
            <i class="fas fa-chevron-down ft-chevron"></i>
        </button>

        <div class="filter-body {{ $hasFilter ? 'open' : '' }}" id="swFilterBody">
            <div class="filter-grid">
                <div>
                    <label class="form-label">Cari Siswa</label>
                    <div style="position:relative;">
                        <input type="text" name="search" class="form-input" placeholder="Nama / NIS / NISN..."
                            value="{{ request('search') }}" style="padding-left:32px;" autocomplete="off">
                        <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.8rem;pointer-events:none;"></i>
                    </div>
                </div>
                <div>
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-input">
                        <option value="">Semua Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!$showArchived)
                <div>
                    <label class="form-label">Status</label>
                    <select name="status_aktif" class="form-input">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('status_aktif') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('status_aktif') === '0' ? 'selected' : '' }}>Non Aktif</option>
                    </select>
                </div>
                @endif
                <div style="display:flex;gap:8px;align-items:flex-end;padding-top:4px;">
                    <button type="submit" class="action-btn btn-view" style="flex:1;justify-content:center;padding:10px;">
                        <i class="fas fa-filter"></i> Terapkan
                    </button>
                    @if($hasFilter)
                        <a href="{{ route('siswa.index', ['status_data' => $showArchived ? 'arsip' : 'aktif']) }}"
                            class="action-btn" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:10px 14px;text-decoration:none;">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Shortcuts --}}
            <div class="sw-shortcuts">
                <a href="{{ route('siswa.index', $modeParams + ['status_data' => 'aktif']) }}" class="sw-shortcut"
                    style="{{ !$showArchived ? 'background:#ede9fe;color:#7c3aed;border-color:#c4b5fd;' : '' }}">
                    <i class="fas fa-users"></i> Data Aktif
                </a>
                <a href="{{ route('siswa.index', $modeParams + ['status_data' => 'arsip']) }}" class="sw-shortcut"
                    style="{{ $showArchived ? 'background:#fef3c7;color:#b45309;border-color:#fde68a;' : '' }}">
                    <i class="fas fa-box-archive"></i> Arsip
                </a>
                <a href="{{ route('siswa.export') }}" class="sw-shortcut" style="background:#f0fdf4;color:#15803d;border-color:#86efac;">
                    <i class="fas fa-file-excel"></i> Export
                </a>
                <a href="{{ route('siswa.import.form') }}" class="sw-shortcut" style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;">
                    <i class="fas fa-file-upload"></i> Import
                </a>
                <a href="{{ route('siswa.update-hp.form') }}" class="sw-shortcut" style="background:#fdf4ff;color:#7c3aed;border-color:#e9d5ff;">
                    <i class="fas fa-mobile-alt"></i> Update HP
                </a>
            </div>
        </div>
    </form>

    {{-- Data Card --}}
    <form id="bulkSiswaForm" method="POST"
        action="{{ $showArchived ? route('siswa.bulk-restore') : route('siswa.bulk-archive') }}">
        @csrf
        <input type="hidden" name="mode" id="bulkMode" value="selected">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="kelas_id" value="{{ request('kelas_id') }}">
        <input type="hidden" name="status_aktif" value="{{ request('status_aktif') }}">

        <div class="card" style="border-radius:12px;overflow:hidden;">
            <div class="c-head">
                <div class="c-icon" style="background:#ede9fe;color:#7c3aed;flex-shrink:0;"><i class="fas fa-users"></i></div>
                <h3>{{ $showArchived ? 'Siswa Diarsipkan' : 'Data Siswa' }}</h3>
                <span class="hbadge">{{ $siswas->total() }} siswa</span>
            </div>

            @if($siswas->count() > 0)
                {{-- Bulk Bar --}}
                <div class="bulk-bar">
                    <label class="bulk-check-label">
                        <input type="checkbox" id="selectAllSiswa" style="accent-color:#7c3aed;">
                        Pilih semua di halaman ini
                    </label>
                    @if($showArchived)
                        <button type="button" class="bulk-btn restore" onclick="submitBulkSiswa('selected')">
                            <i class="fas fa-rotate-left"></i> Pulihkan Terpilih
                        </button>
                        <button type="button" class="bulk-btn restore" onclick="submitBulkSiswa('filtered')">
                            <i class="fas fa-filter"></i> Pulihkan Semua Filter
                        </button>
                    @else
                        <button type="button" class="bulk-btn archive" onclick="submitBulkSiswa('selected')">
                            <i class="fas fa-box-archive"></i> Arsip Terpilih
                        </button>
                        <button type="button" class="bulk-btn archive" onclick="submitBulkSiswa('filtered')">
                            <i class="fas fa-filter"></i> Arsip Semua Filter
                        </button>
                    @endif
                </div>

                {{-- TABLE desktop ≥768px --}}
                <div class="sw-table-wrap">
                    <table class="sw-table">
                        <thead>
                            <tr>
                                <th style="width:32px;"></th>
                                <th>Nama Siswa</th>
                                <th>NIS / NISN</th>
                                <th>Kelas</th>
                                <th>Jenis Kelamin</th>
                                <th>Status</th>
                                @if($showArchived)<th>Diarsipkan</th>@endif
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($siswas as $siswa)
                            <tr>
                                <td><input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}" class="siswa-check" style="accent-color:#7c3aed;"></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div style="width:34px;height:34px;border-radius:8px;background:#ede9fe;color:#7c3aed;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;overflow:hidden;">
                                            @if($siswa->foto)
                                                <img src="{{ Storage::url($siswa->foto) }}" alt="{{ $siswa->nama_lengkap }}" style="width:100%;height:100%;object-fit:cover;">
                                            @else
                                                <i class="fas fa-user-graduate"></i>
                                            @endif
                                        </div>
                                        <div style="font-weight:700;font-size:.85rem;">{{ $siswa->nama_lengkap }}</div>
                                    </div>
                                </td>
                                <td style="font-size:.75rem;color:#64748b;">{{ $siswa->nis }}{{ $siswa->nisn ? ' / '.$siswa->nisn : '' }}</td>
                                <td style="font-size:.78rem;">{{ $siswa->kelas?->nama_kelas ?? '-' }}</td>
                                <td style="font-size:.78rem;">
                                    <i class="fas fa-{{ $siswa->jenis_kelamin === 'L' ? 'mars' : 'venus' }}" style="color:{{ $siswa->jenis_kelamin === 'L' ? '#0ea5e9' : '#ec4899' }};margin-right:4px;"></i>
                                    {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </td>
                                <td>
                                    @if($showArchived)
                                        <span class="badge-status badge-arsip">Diarsipkan</span>
                                    @else
                                        <span class="badge-status {{ $siswa->status_aktif ? 'badge-aktif' : 'badge-nonaktif' }}">
                                            {{ $siswa->status_aktif ? 'Aktif' : 'Non Aktif' }}
                                        </span>
                                    @endif
                                </td>
                                @if($showArchived)
                                    <td style="font-size:.72rem;color:#64748b;white-space:nowrap;">{{ $siswa->deleted_at?->translatedFormat('d M Y') }}</td>
                                @endif
                                <td>
                                    <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                        @if($showArchived)
                                            <button type="button" class="action-btn btn-restore" onclick="confirmRestore('{{ route('siswa.restore', $siswa->id) }}')">
                                                <i class="fas fa-rotate-left"></i> Pulihkan
                                            </button>
                                        @else
                                            <a href="{{ route('siswa.show', $siswa) }}" class="action-btn btn-view"><i class="fas fa-eye"></i> Detail</a>
                                            <a href="{{ route('siswa.edit', $siswa) }}" class="action-btn btn-edit"><i class="fas fa-pen"></i> Edit</a>
                                            <button type="button" class="action-btn btn-archive" onclick="confirmArchive('{{ route('siswa.destroy', $siswa) }}')"><i class="fas fa-box-archive"></i></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- CARD LIST mobile <768px --}}
                <div class="sw-card-list">
                    @foreach($siswas as $siswa)
                        <div class="sw-card-item">
                            <div class="swci-top">
                                <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}" class="siswa-check" style="accent-color:#7c3aed;flex-shrink:0;">
                                <div class="swci-avatar">
                                    @if($siswa->foto)
                                        <img src="{{ Storage::url($siswa->foto) }}" alt="{{ $siswa->nama_lengkap }}">
                                    @else
                                        <i class="fas fa-user-graduate"></i>
                                    @endif
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div class="swci-name">{{ $siswa->nama_lengkap }}</div>
                                    <div class="swci-id">{{ $siswa->nis }}{{ $siswa->nisn ? ' / '.$siswa->nisn : '' }}</div>
                                </div>
                                @if($showArchived)
                                    <span class="badge-status badge-arsip">Arsip</span>
                                @else
                                    <span class="badge-status {{ $siswa->status_aktif ? 'badge-aktif' : 'badge-nonaktif' }}">
                                        {{ $siswa->status_aktif ? 'Aktif' : 'Non Aktif' }}
                                    </span>
                                @endif
                            </div>
                            <div class="swci-meta">
                                <span><i class="fas fa-door-open"></i> {{ $siswa->kelas?->nama_kelas ?? '-' }}</span>
                                <span>
                                    <i class="fas fa-{{ $siswa->jenis_kelamin === 'L' ? 'mars' : 'venus' }}" style="color:{{ $siswa->jenis_kelamin === 'L' ? '#0ea5e9' : '#ec4899' }}"></i>
                                    {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </span>
                                @if($showArchived && $siswa->deleted_at)
                                    <span><i class="fas fa-clock"></i> {{ $siswa->deleted_at->translatedFormat('d M Y') }}</span>
                                @endif
                            </div>
                            <div class="swci-actions">
                                @if($showArchived)
                                    <button type="button" class="action-btn btn-restore" onclick="confirmRestore('{{ route('siswa.restore', $siswa->id) }}')">
                                        <i class="fas fa-rotate-left"></i> Pulihkan
                                    </button>
                                @else
                                    <a href="{{ route('siswa.show', $siswa) }}" class="action-btn btn-view"><i class="fas fa-eye"></i> Detail</a>
                                    <a href="{{ route('siswa.edit', $siswa) }}" class="action-btn btn-edit"><i class="fas fa-pen"></i> Edit</a>
                                    <button type="button" class="action-btn btn-archive" onclick="confirmArchive('{{ route('siswa.destroy', $siswa) }}')">
                                        <i class="fas fa-box-archive"></i> Arsip
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($siswas->hasPages())
                    @php
                        $cur  = $siswas->currentPage();
                        $last = $siswas->lastPage();
                        $from = max(1, $cur - 2);
                        $to   = min($last, $cur + 2);
                    @endphp
                    <div class="rekap-pagination">
                        @if($siswas->onFirstPage())
                            <span class="pg-btn disabled"><i class="fas fa-chevron-left"></i> Prev</span>
                        @else
                            <a href="{{ $siswas->previousPageUrl() }}" class="pg-btn"><i class="fas fa-chevron-left"></i> Prev</a>
                        @endif

                        @if($from > 1)
                            <a href="{{ $siswas->url(1) }}" class="pg-btn pg-num">1</a>
                            @if($from > 2)<span class="pg-btn disabled pg-num">…</span>@endif
                        @endif
                        @for($p = $from; $p <= $to; $p++)
                            <a href="{{ $siswas->url($p) }}" class="pg-btn pg-num {{ $p === $cur ? 'active' : '' }}">{{ $p }}</a>
                        @endfor
                        @if($to < $last)
                            @if($to < $last - 1)<span class="pg-btn disabled pg-num">…</span>@endif
                            <a href="{{ $siswas->url($last) }}" class="pg-btn pg-num">{{ $last }}</a>
                        @endif

                        @if($siswas->hasMorePages())
                            <a href="{{ $siswas->nextPageUrl() }}" class="pg-btn">Next <i class="fas fa-chevron-right"></i></a>
                        @else
                            <span class="pg-btn disabled">Next <i class="fas fa-chevron-right"></i></span>
                        @endif
                    </div>
                @endif

            @else
                <div class="rekap-empty">
                    <i class="fas fa-users"></i>
                    <strong>{{ $showArchived ? 'Belum ada siswa diarsipkan' : 'Tidak ada data siswa' }}</strong>
                    {{ $showArchived ? 'Siswa yang diarsipkan akan muncul di sini.' : 'Coba ubah filter atau tambah siswa baru.' }}
                    @if(!$showArchived)
                        <br><a href="{{ route('siswa.create') }}" style="color:#7c3aed;font-size:.82rem;display:inline-block;margin-top:8px;">
                            <i class="fas fa-plus"></i> Tambah Siswa Pertama
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </form>

</div>{{-- /sw-wrap --}}

@if(!$showArchived)
    <a href="{{ route('siswa.create') }}" class="fab-add">
        <i class="fas fa-plus"></i> Tambah Siswa
    </a>
@endif
@endsection

@push('scripts')
<script>
    const isArchivedMode = @json($showArchived ?? false);

    function toggleSwFilter() {
        document.getElementById('swFilterToggle').classList.toggle('open');
        document.getElementById('swFilterBody').classList.toggle('open');
    }

    document.getElementById('selectAllSiswa')?.addEventListener('change', function () {
        document.querySelectorAll('.siswa-check').forEach(cb => cb.checked = this.checked);
    });

    function createMethodForm(url, method) {
        const form = document.createElement('form');
        form.method = 'POST'; form.action = url;
        const csrf = document.createElement('input');
        csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        if (method !== 'POST') {
            const m = document.createElement('input');
            m.type = 'hidden'; m.name = '_method'; m.value = method;
            form.appendChild(m);
        }
        document.body.appendChild(form);
        return form;
    }

    function confirmArchive(url) {
        Swal.fire({
            title: 'Arsipkan Siswa?',
            text: 'Siswa akan disembunyikan dari data aktif dan dapat dipulihkan.',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-box-archive"></i> Arsipkan', cancelButtonText: 'Batal',
        }).then(r => { if (r.isConfirmed) createMethodForm(url, 'DELETE').submit(); });
    }

    function confirmRestore(url) {
        Swal.fire({
            title: 'Pulihkan Siswa?', text: 'Siswa akan kembali muncul di data aktif.',
            icon: 'question', showCancelButton: true,
            confirmButtonColor: '#15803d', cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-rotate-left"></i> Pulihkan', cancelButtonText: 'Batal',
        }).then(r => { if (r.isConfirmed) createMethodForm(url, 'PATCH').submit(); });
    }

    function submitBulkSiswa(mode) {
        const selected = document.querySelectorAll('.siswa-check:checked').length;
        if (mode === 'selected' && selected === 0) {
            Swal.fire({ icon:'info', title:'Belum ada siswa dipilih', text:'Centang minimal satu siswa.' });
            return;
        }
        document.getElementById('bulkMode').value = mode;
        const action = isArchivedMode ? 'pulihkan' : 'arsipkan';
        const target = mode === 'filtered' ? 'semua siswa pada hasil filter ini' : selected + ' siswa terpilih';
        Swal.fire({
            title: (isArchivedMode ? 'Pulihkan' : 'Arsipkan') + ' Data?',
            text: 'Anda akan ' + action + ' ' + target + '.',
            icon: isArchivedMode ? 'question' : 'warning', showCancelButton: true,
            confirmButtonColor: isArchivedMode ? '#15803d' : '#dc2626', cancelButtonColor: '#6b7280',
            confirmButtonText: isArchivedMode ? 'Pulihkan' : 'Arsipkan', cancelButtonText: 'Batal',
        }).then(r => { if (r.isConfirmed) document.getElementById('bulkSiswaForm').submit(); });
    }

    @if(session('update_hp_success') !== null)
    document.addEventListener('DOMContentLoaded', function () {
        @php $hpSuccess = session('update_hp_success'); $hpFailed = session('update_hp_failed', []); @endphp
        let failedHtml = '';
        @if(!empty($hpFailed))
            failedHtml = '<ul style="text-align:left;font-size:.8rem;margin-top:8px;padding-left:16px;">';
            @foreach($hpFailed as $f)
                failedHtml += '<li><strong>{{ addslashes($f['nama']) }}</strong>: {{ addslashes($f['alasan']) }}</li>';
            @endforeach
            failedHtml += '</ul>';
        @endif
        Swal.fire({
            title: 'Update Selesai',
            html: `<p><strong>{{ $hpSuccess }}</strong> data berhasil diupdate.</p>@if(!empty($hpFailed))<p style="color:#dc2626;margin-top:8px;"><strong>{{ count($hpFailed) }}</strong> data gagal:</p>${failedHtml}@endif`,
            icon: '{{ empty($hpFailed) ? "success" : "warning" }}',
            confirmButtonColor: '#7c3aed', confirmButtonText: 'OK',
        });
    });
    @endif
</script>
@endpush
