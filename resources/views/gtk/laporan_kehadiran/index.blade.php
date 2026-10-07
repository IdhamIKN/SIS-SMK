@extends('layouts.app')

@section('title', 'Laporan Kehadiran Guru')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ── Wrapper ── */
        .lkg-wrap {
            padding: 0 12px;
            max-width: 1280px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        @media(min-width:768px) {
            .lkg-wrap {
                padding: 0 20px;
            }
        }

        @media(min-width:1024px) {
            .lkg-wrap {
                padding: 0 28px;
            }
        }

        /* ── Stat grid ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }

        @media(min-width:480px) {
            .stat-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media(min-width:768px) {
            .stat-grid {
                grid-template-columns: repeat(7, 1fr);
                gap: 12px;
                margin-bottom: 20px;
            }
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 8px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        @media(min-width:768px) {
            .stat-card {
                border-radius: 12px;
                padding: 16px;
            }
        }

        .stat-value {
            font-size: 1.3rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 3px;
        }

        @media(min-width:768px) {
            .stat-value {
                font-size: 1.6rem;
                margin-bottom: 5px;
            }
        }

        .stat-label {
            font-size: .58rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        @media(min-width:768px) {
            .stat-label {
                font-size: .65rem;
            }
        }

        /* ── Filter section ── */
        .filter-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 16px;
            overflow: hidden;
        }

        .filter-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            cursor: pointer;
            user-select: none;
            background: #f1f5f9;
            border: none;
            width: 100%;
            font-family: inherit;
            font-size: .875rem;
            font-weight: 700;
            color: #0f172a;
            gap: 8px;
        }

        .filter-toggle .ft-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-toggle .ft-chevron {
            transition: transform .2s;
            color: #64748b;
            font-size: .8rem;
        }

        .filter-toggle.open .ft-chevron {
            transform: rotate(180deg);
        }

        @media(min-width:768px) {
            .filter-toggle {
                display: none;
            }
        }

        .filter-body {
            padding: 12px 16px 16px;
            display: none;
        }

        .filter-body.open {
            display: block;
        }

        @media(min-width:768px) {
            .filter-body {
                display: block !important;
                padding: 16px;
            }
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            align-items: end;
        }

        @media(max-width:479px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
        }

        @media(min-width:768px) {
            .filter-grid {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 12px;
            }
        }

        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .form-input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            background: #fff;
            box-sizing: border-box;
            -webkit-appearance: none;
            appearance: none;
        }

        .form-input:focus {
            outline: 2px solid #f59e0b;
            outline-offset: -1px;
        }

        /* ── Card header ── */
        .c-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            flex-wrap: wrap;
        }

        @media(min-width:768px) {
            .c-head {
                padding: 14px 18px;
            }
        }

        .c-head h3 {
            font-size: .9rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            flex: 1;
        }

        @media(min-width:768px) {
            .c-head h3 {
                font-size: 1rem;
            }
        }

        .hbadge {
            font-size: .7rem;
            font-weight: 700;
            background: #f1f5f9;
            color: #475569;
            padding: 3px 10px;
            border-radius: 20px;
        }

        /* ── Status badges ── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-hijau {
            background: #dcfce7;
            color: #15803d;
        }

        .status-kuning {
            background: #fef9c3;
            color: #a16207;
        }

        .status-merah {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-abu {
            background: #f1f5f9;
            color: #475569;
        }

        .status-biru {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-pink {
            background: #fce7f3;
            color: #be185d;
        }

        .status-orange {
            background: #ffedd5;
            color: #c2410c;
        }

        .status-putih {
            background: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        /* ── Table desktop ≥768px ── */
        .lkg-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media(max-width:767px) {
            .lkg-table-wrap {
                display: none;
            }
        }

        .lkg-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .78rem;
            background: #fff;
        }

        .lkg-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .lkg-table th {
            padding: 11px 10px;
            text-align: left;
            font-size: .68rem;
            font-weight: 700;
            color: #64748b;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .lkg-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .lkg-table tbody tr:last-child td {
            border-bottom: none;
        }

        .lkg-table tbody tr:hover td {
            background: #fafbfc;
        }

        /* ── Card list mobile <768px ── */
        .lkg-card-list {
            display: none;
        }

        @media(max-width:767px) {
            .lkg-card-list {
                display: flex;
                flex-direction: column;
                gap: 0;
            }
        }

        .lkg-card-item {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            background: #fff;
        }

        .lkg-card-item:last-child {
            border-bottom: none;
        }

        .lci-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 5px;
        }

        .lci-tgl {
            font-weight: 700;
            font-size: .85rem;
            color: #0f172a;
        }

        .lci-jam {
            font-size: .72rem;
            color: #64748b;
            white-space: nowrap;
        }

        .lci-mid {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 6px;
        }

        .lci-chip {
            font-size: .7rem;
            color: #64748b;
            background: #f1f5f9;
            border-radius: 5px;
            padding: 2px 7px;
            font-weight: 600;
        }

        .lci-bot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .lci-pelapor {
            font-size: .7rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Action buttons ── */
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 7px;
            font-size: .72rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            transition: opacity .15s;
            white-space: nowrap;
        }

        .action-btn:active {
            opacity: .75;
        }

        .btn-view {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .btn-edit {
            background: #f0fdf4;
            color: #15803d;
        }

        .btn-delete {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        /* ── Pagination ── */
        .rekap-pagination {
            padding: 12px 14px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 5px;
        }

        .pg-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 700;
            text-decoration: none;
            font-family: inherit;
            border: 1px solid #e2e8f0;
            background: #f1f5f9;
            color: #475569;
            cursor: pointer;
            transition: background .15s;
            white-space: nowrap;
        }

        .pg-btn:hover {
            background: #e2e8f0;
        }

        .pg-btn.active {
            background: #f59e0b;
            color: #fff;
            border-color: transparent;
            pointer-events: none;
        }

        .pg-btn.disabled {
            opacity: .4;
            cursor: not-allowed;
            pointer-events: none;
        }

        @media(max-width:479px) {
            .pg-num {
                display: none;
            }

            .pg-num.active {
                display: inline-flex;
            }
        }

        /* ── Empty state ── */
        .rekap-empty {
            text-align: center;
            padding: 36px 20px;
            color: #64748b;
        }

        .rekap-empty i {
            font-size: 2.5rem;
            opacity: .3;
            display: block;
            margin-bottom: 10px;
        }

        .rekap-empty strong {
            display: block;
            color: #0f172a;
            margin-bottom: 4px;
            font-size: .9rem;
        }

        /* ── Action bar (fixed bottom) ── */
        .action-bar {
            position: fixed;
            bottom: var(--footer-h, 0);
            left: 0;
            right: 0;
            padding: 10px 12px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 8px;
            z-index: 999;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
            align-items: center;
        }

        @media(min-width:768px) {
            .action-bar {
                padding: 10px 24px 12px;
                gap: 12px;
                justify-content: flex-end;
            }
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 11px 10px;
            border-radius: 10px;
            font-size: .78rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            line-height: 1;
            white-space: nowrap;
            min-width: 0;
            overflow: hidden;
        }

        /* Sembunyikan teks label pada layar sangat kecil, tampilkan ikon saja */
        @media(max-width:359px) {
            .ab-btn .ab-label {
                display: none;
            }

            .ab-btn {
                padding: 12px;
                flex: unset;
                width: 44px;
                border-radius: 10px;
            }
        }

        @media(min-width:360px) and (max-width:767px) {
            .ab-btn {
                font-size: .74rem;
                padding: 11px 8px;
                gap: 5px;
            }
        }

        @media(min-width:768px) {
            .ab-btn {
                flex: unset;
                min-width: 130px;
                font-size: .875rem;
                padding: 12px 20px;
                gap: 8px;
            }
        }

        .ab-btn:active {
            transform: scale(.97);
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .ab-btn-primary {
            background: #f59e0b;
            color: #fff;
            box-shadow: 0 3px 12px rgba(245, 158, 11, .3);
        }

        .ab-btn-rekap {
            background: #0d9488;
            color: #fff;
        }
    </style>
    <style>
        /* ── Select2 override untuk modal export kehadiran guru ── */
        #lkgExportModal .select2-container {
            width: 100% !important;
        }

        #lkgExportModal .select2-container--default .select2-selection--single {
            height: 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 4px 8px;
            font-size: .83rem;
            display: flex;
            align-items: center;
        }

        #lkgExportModal .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px;
            color: #0f172a;
            font-size: .83rem;
            padding-left: 4px;
        }

        #lkgExportModal .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }

        #lkgExportModal .select2-container--default.select2-container--focus .select2-selection--single,
        #lkgExportModal .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #0F766E;
            outline: none;
        }

        #lkgExportModal .select2-dropdown {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
            font-size: .83rem;
        }

        #lkgExportModal .select2-search--dropdown {
            padding: 8px;
        }

        #lkgExportModal .select2-search--dropdown .select2-search__field {
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            padding: 7px 10px;
            font-size: .83rem;
        }

        #lkgExportModal .select2-search--dropdown .select2-search__field:focus {
            border-color: #0F766E;
            outline: none;
        }

        #lkgExportModal .select2-results__option {
            padding: 9px 12px;
            font-size: .82rem;
        }

        #lkgExportModal .select2-results__option--highlighted {
            background: #ccfbf1 !important;
            color: #0f5a50 !important;
        }
    </style>
@endpush

@section('content')
    @php
        $hasFilter = request()->hasAny(['kelas_id', 'gtk_id']) || request('tanggal') !== now()->toDateString();
    @endphp

    <div class="event-wrap lkg-wrap" style="padding-top:var(--header-h,56px);padding-bottom: 148px;">

        {{-- Page Strip --}}
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>Laporan Kehadiran Guru</div>
            <h2><i class="fas fa-clipboard-check"></i> Status Kehadiran Guru</h2>
            <p>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</p>
        </div>

        @if (session('success'))
            <div
                style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div
                style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
                <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value" style="color:#0ea5e9;">{{ $stats['total_laporan'] }}</div>
                <div class="stat-label">Total Laporan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#22c55e;">{{ $stats['hijau'] }}</div>
                <div class="stat-label">Tepat Waktu</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#eab308;">{{ $stats['kuning'] }}</div>
                <div class="stat-label">Terlambat</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#ef4444;">{{ $stats['merah'] }}</div>
                <div class="stat-label">Tidak Hadir</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#f97316;">{{ $stats['orange'] }}</div>
                <div class="stat-label">Tanpa Laporan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#94a3b8;">{{ $stats['putih'] }}</div>
                <div class="stat-label">Belum Lapor</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#64748b;">{{ $stats['kelas_belum_lapor'] }}</div>
                <div class="stat-label">Kelas Belum Lapor</div>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('kehadiran-guru.laporan') }}" class="filter-section" id="lkgFilterForm">
            <button type="button" class="filter-toggle {{ $hasFilter ? 'open' : '' }}" id="lkgFilterToggle"
                onclick="toggleLkgFilter()">
                <span class="ft-left">
                    <i class="fas fa-filter"></i> Filter
                    @if ($hasFilter)
                        <span
                            style="background:#fef3c7;color:#b45309;font-size:.65rem;padding:2px 8px;border-radius:20px;font-weight:700;">Aktif</span>
                    @endif
                </span>
                <i class="fas fa-chevron-down ft-chevron"></i>
            </button>

            <div class="filter-body {{ $hasFilter ? 'open' : '' }}" id="lkgFilterBody">
                <div class="filter-grid">
                    <div>
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-input" value="{{ $tanggal }}">
                    </div>
                    @if (!auth()->user()->hasRole('gtk'))
                        <div>
                            <label class="form-label">Kelas</label>
                            <select name="kelas_id" class="form-input">
                                <option value="">Semua Kelas</option>
                                @foreach ($kelas as $k)
                                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}{{ $k->jurusan ? ' - ' . $k->jurusan->nama_jurusan : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Guru</label>
                            <select name="gtk_id" class="form-input">
                                <option value="">Semua Guru</option>
                                @foreach ($gtkList as $g)
                                    <option value="{{ $g->id }}" {{ $gtkId == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div style="display:flex;gap:8px;align-items:flex-end;padding-top:4px;">
                        <button type="submit" class="action-btn btn-view"
                            style="flex:1;justify-content:center;padding:10px;">
                            <i class="fas fa-filter"></i> Terapkan
                        </button>
                        @if ($hasFilter)
                            <a href="{{ route('kehadiran-guru.laporan') }}" class="action-btn"
                                style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:10px 14px;text-decoration:none;">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- Data Card --}}
        <div class="card" style="border-radius:12px;overflow:hidden;">
            <div class="c-head">
                <div class="c-icon" style="background:#fef3c7;color:#b45309;flex-shrink:0;"><i class="fas fa-list"></i>
                </div>
                <h3>Daftar Laporan Kehadiran</h3>
                <span class="hbadge">{{ $laporanKehadiran->total() }} laporan</span>
            </div>

            @if ($laporanKehadiran->count() > 0)
                {{-- TABLE desktop ≥768px --}}
                <div class="lkg-table-wrap">
                    <table class="lkg-table">
                        <thead>
                            <tr>
                                <th>Tanggal / Jam</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru</th>
                                <th>Pelapor</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($laporanKehadiran as $laporan)
                                <tr>
                                    <td style="white-space:nowrap;">
                                        <div style="font-weight:700;font-size:.83rem;">
                                            {{ \Carbon\Carbon::parse($laporan->tanggal)->format('d/m/Y') }}</div>
                                        <div style="font-size:.7rem;color:#64748b;">
                                            Jam {{ $laporan->jam_ke }}
                                            @if ($laporan->jadwalKbm)
                                                ·
                                                {{ \Carbon\Carbon::parse($laporan->jadwalKbm->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($laporan->jadwalKbm->jam_selesai)->format('H:i') }}
                                            @endif
                                        </div>
                                        <div style="font-size:.68rem;color:#94a3b8;">Lapor
                                            {{ $laporan->waktu_laporan->format('H:i') }}</div>
                                    </td>
                                    <td style="font-weight:600;font-size:.82rem;">{{ $laporan->kelas?->nama_kelas ?? '-' }}
                                    </td>
                                    <td style="font-size:.78rem;color:#475569;">
                                        {{ $laporan->jadwalKbm?->mata_pelajaran ?? '-' }}</td>
                                    <td>
                                        <div style="font-weight:600;font-size:.82rem;">
                                            {{ $laporan->gtk?->nama_lengkap ?? '-' }}</div>
                                    </td>
                                    <td>
                                        @if ($laporan->dilaporkan_oleh_siswa_id)
                                            <div style="display:flex;align-items:center;gap:4px;font-size:.75rem;">
                                                <i class="fas fa-user-graduate" style="color:#0ea5e9;font-size:.65rem;"></i>
                                                {{ \Illuminate\Support\Str::limit($laporan->dilaporkanOlehSiswa?->nama_lengkap ?? '-', 20) }}
                                            </div>
                                        @elseif(str_starts_with($laporan->catatan ?? '', 'Auto-generated:'))
                                            <div
                                                style="display:flex;align-items:center;gap:4px;font-size:.75rem;color:#8b5cf6;">
                                                <i class="fas fa-robot" style="font-size:.65rem;"></i> Sistem
                                            </div>
                                        @else
                                            <div
                                                style="display:flex;align-items:center;gap:4px;font-size:.75rem;color:#64748b;">
                                                <i class="fas fa-user-tie" style="font-size:.65rem;"></i> Guru Sendiri
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $laporan->status }}">
                                            <span class="status-dot"
                                                style="background:{{ $laporan->status_color }};"></span>
                                            {{ $laporan->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:4px;">
                                            <a href="{{ route('kehadiran-guru.show', $laporan) }}"
                                                class="action-btn btn-view">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('kehadiran-guru.update')
                                                @if ($isPrivileged || $laporan->tanggal->isToday())
                                                    <a href="{{ route('kehadiran-guru.edit', $laporan) }}"
                                                        class="action-btn btn-edit">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                @endif
                                            @endcan
                                            @can('kehadiran-guru.delete')
                                                <form method="POST" action="{{ route('kehadiran-guru.destroy', $laporan) }}"
                                                    style="display:inline;"
                                                    onsubmit="return confirmDeleteLaporan(event, this)">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="action-btn btn-delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- CARD LIST mobile <768px --}}
                <div class="lkg-card-list">
                    @foreach ($laporanKehadiran as $laporan)
                        <div class="lkg-card-item">
                            <div class="lci-top">
                                <div>
                                    <div class="lci-tgl">{{ \Carbon\Carbon::parse($laporan->tanggal)->format('d/m/Y') }}
                                        &bull; Jam {{ $laporan->jam_ke }}</div>
                                    @if ($laporan->jadwalKbm)
                                        <div style="font-size:.7rem;color:#64748b;">
                                            {{ \Carbon\Carbon::parse($laporan->jadwalKbm->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($laporan->jadwalKbm->jam_selesai)->format('H:i') }}
                                            · Lapor {{ $laporan->waktu_laporan->format('H:i') }}
                                        </div>
                                    @endif
                                </div>
                                <span class="status-badge status-{{ $laporan->status }}">
                                    <span class="status-dot" style="background:{{ $laporan->status_color }};"></span>
                                    {{ $laporan->status_label }}
                                </span>
                            </div>
                            <div class="lci-mid">
                                <span class="lci-chip"><i class="fas fa-door-open"></i>
                                    {{ $laporan->kelas?->nama_kelas ?? '-' }}</span>
                                @if ($laporan->jadwalKbm?->mata_pelajaran)
                                    <span class="lci-chip">{{ $laporan->jadwalKbm->mata_pelajaran }}</span>
                                @endif
                                <span class="lci-chip"><i class="fas fa-user-tie"></i>
                                    {{ \Illuminate\Support\Str::limit($laporan->gtk?->nama_lengkap ?? '-', 18) }}</span>
                            </div>
                            <div class="lci-bot">
                                <div class="lci-pelapor">
                                    @if ($laporan->dilaporkan_oleh_siswa_id)
                                        <i class="fas fa-user-graduate" style="color:#0ea5e9;"></i>
                                        {{ \Illuminate\Support\Str::limit($laporan->dilaporkanOlehSiswa?->nama_lengkap ?? '-', 18) }}
                                    @elseif(str_starts_with($laporan->catatan ?? '', 'Auto-generated:'))
                                        <i class="fas fa-robot" style="color:#8b5cf6;"></i> Sistem
                                    @else
                                        <i class="fas fa-user-tie"></i> Guru Sendiri
                                    @endif
                                </div>
                                <div style="display:flex;gap:5px;">
                                    <a href="{{ route('kehadiran-guru.show', $laporan) }}" class="action-btn btn-view">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    @can('kehadiran-guru.update')
                                        @if ($isPrivileged || $laporan->tanggal->isToday())
                                            <a href="{{ route('kehadiran-guru.edit', $laporan) }}"
                                                class="action-btn btn-edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        @endif
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if ($laporanKehadiran->hasPages())
                    @php
                        $cur = $laporanKehadiran->currentPage();
                        $last = $laporanKehadiran->lastPage();
                        $from = max(1, $cur - 2);
                        $to = min($last, $cur + 2);
                    @endphp
                    <div class="rekap-pagination">
                        @if ($laporanKehadiran->onFirstPage())
                            <span class="pg-btn disabled"><i class="fas fa-chevron-left"></i> Prev</span>
                        @else
                            <a href="{{ $laporanKehadiran->previousPageUrl() }}" class="pg-btn"><i
                                    class="fas fa-chevron-left"></i> Prev</a>
                        @endif
                        @if ($from > 1)
                            <a href="{{ $laporanKehadiran->url(1) }}" class="pg-btn pg-num">1</a>
                            @if ($from > 2)
                                <span class="pg-btn disabled pg-num">…</span>
                            @endif
                        @endif
                        @for ($p = $from; $p <= $to; $p++)
                            <a href="{{ $laporanKehadiran->url($p) }}"
                                class="pg-btn pg-num {{ $p === $cur ? 'active' : '' }}">{{ $p }}</a>
                        @endfor
                        @if ($to < $last)
                            @if ($to < $last - 1)
                                <span class="pg-btn disabled pg-num">…</span>
                            @endif
                            <a href="{{ $laporanKehadiran->url($last) }}" class="pg-btn pg-num">{{ $last }}</a>
                        @endif
                        @if ($laporanKehadiran->hasMorePages())
                            <a href="{{ $laporanKehadiran->nextPageUrl() }}" class="pg-btn">Next <i
                                    class="fas fa-chevron-right"></i></a>
                        @else
                            <span class="pg-btn disabled">Next <i class="fas fa-chevron-right"></i></span>
                        @endif
                    </div>
                @endif
            @else
                <div class="rekap-empty">
                    <i class="fas fa-clipboard-check"></i>
                    <strong>Belum ada laporan</strong>
                    Tidak ada laporan kehadiran untuk tanggal dan filter yang dipilih.
                </div>
            @endif
        </div>

    </div>{{-- /lkg-wrap --}}

    {{-- Action Bar --}}
    <div class="action-bar">
        <a href="{{ route('dashboard') }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i>
            <span class="ab-label">Kembali</span>
        </a>
        @if (!auth()->user()->hasRole('siswa'))
            <a href="{{ route('kehadiran-guru.grafik') }}" class="ab-btn"
                style="background:#6366f1;color:#fff;box-shadow:0 3px 10px rgba(99,102,241,.3);">
                <i class="fas fa-chart-bar"></i>
                <span class="ab-label">Grafik</span>
            </a>
            <button type="button" class="ab-btn ab-btn-rekap" onclick="openLkgExportModal()">
                <i class="fas fa-file-export"></i>
                <span class="ab-label">Export</span>
            </button>
        @endif
        @can('kehadiran-guru.create')
            <a href="{{ route('kehadiran-guru.create') }}" class="ab-btn ab-btn-primary">
                <i class="fas fa-plus"></i>
                <span class="ab-label">Buat Laporan</span>
            </a>
        @endcan
    </div>

    {{-- ═══════════════════════════════════════════
         MODAL EXPORT / CETAK KEHADIRAN GURU
    ═══════════════════════════════════════════ --}}
    @if (!auth()->user()->hasRole('siswa'))
        <div id="lkgExportModal"
            style="display:none;position:fixed;inset:0;z-index:9998;
         background:rgba(15,23,42,.55);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);
         align-items:center;justify-content:center;padding:16px;">
            <div
                style="background:#fff;border-radius:16px;width:100%;max-width:460px;
                    box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden;">

                {{-- Header --}}
                <div
                    style="background:linear-gradient(135deg,#0F766E,#0d9488);padding:16px 20px;
                        display:flex;align-items:center;justify-content:space-between;">
                    <div style="color:#fff;">
                        <div style="font-weight:800;font-size:.95rem;"><i class="fas fa-file-export"></i> Cetak / Export
                            Kehadiran Guru</div>
                        <div style="font-size:.73rem;opacity:.85;margin-top:2px;">Pilih rentang tanggal dan filter yang
                            diinginkan</div>
                    </div>
                    <button onclick="closeLkgExportModal()"
                        style="background:rgba(255,255,255,.2);border:none;
                        color:#fff;width:30px;height:30px;border-radius:8px;cursor:pointer;font-size:.9rem;
                        display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div style="padding:18px 20px 20px;">
                    {{-- Range Tanggal --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                        <div>
                            <label style="display:block;font-size:.78rem;font-weight:700;color:#0f172a;margin-bottom:5px;">
                                <i class="fas fa-calendar-alt" style="color:#0F766E;"></i> Dari Tanggal
                            </label>
                            <input type="date" id="lkgExpMulai" class="form-input"
                                value="{{ now()->startOfMonth()->toDateString() }}" style="font-size:.83rem;">
                        </div>
                        <div>
                            <label style="display:block;font-size:.78rem;font-weight:700;color:#0f172a;margin-bottom:5px;">
                                <i class="fas fa-calendar-alt" style="color:#0F766E;"></i> Sampai Tanggal
                            </label>
                            <input type="date" id="lkgExpSelesai" class="form-input"
                                value="{{ now()->toDateString() }}" style="font-size:.83rem;">
                        </div>
                    </div>

                    @if (!auth()->user()->hasRole('gtk'))
                        {{-- Filter Kelas --}}
                        <div style="margin-bottom:10px;">
                            <label style="display:block;font-size:.78rem;font-weight:700;color:#0f172a;margin-bottom:5px;">
                                <i class="fas fa-door-open" style="color:#0F766E;"></i> Kelas
                            </label>
                            <select id="lkgExpKelas" class="form-input lkg-exp-select" data-placeholder="Semua Kelas"
                                style="font-size:.83rem;">
                                <option value=""></option>
                                @foreach ($kelas as $k)
                                    <option value="{{ $k->id }}">
                                        {{ $k->nama_kelas }}{{ $k->jurusan ? ' - ' . $k->jurusan->nama_jurusan : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Guru --}}
                        <div style="margin-bottom:18px;">
                            <label style="display:block;font-size:.78rem;font-weight:700;color:#0f172a;margin-bottom:5px;">
                                <i class="fas fa-chalkboard-teacher" style="color:#0F766E;"></i> Guru
                            </label>
                            <select id="lkgExpGtk" class="form-input lkg-exp-select" data-placeholder="Semua Guru"
                                style="font-size:.83rem;">
                                <option value=""></option>
                                @foreach ($gtkList as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div style="margin-bottom:18px;"></div>
                    @endif

                    {{-- Tombol Export --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <button onclick="doLkgExport('pdf')"
                            style="display:flex;align-items:center;justify-content:center;gap:8px;
                               padding:12px;border-radius:10px;font-size:.83rem;font-weight:700;
                               background:#dc2626;color:#fff;border:none;cursor:pointer;">
                            <i class="fas fa-file-pdf"></i> Cetak PDF
                        </button>
                        <button onclick="doLkgExport('excel')"
                            style="display:flex;align-items:center;justify-content:center;gap:8px;
                               padding:12px;border-radius:10px;font-size:.83rem;font-weight:700;
                               background:#15803d;color:#fff;border:none;cursor:pointer;">
                            <i class="fas fa-file-excel"></i> Download Excel
                        </button>
                    </div>
                    <p style="font-size:.7rem;color:#94a3b8;text-align:center;margin-top:10px;margin-bottom:0;">
                        PDF akan dibuka di tab baru untuk dicetak atau disimpan.
                    </p>
                </div>
            </div>
        </div>
    @endif



    @push('scripts')
        <script>
            function toggleLkgFilter() {
                document.getElementById('lkgFilterToggle').classList.toggle('open');
                document.getElementById('lkgFilterBody').classList.toggle('open');
            }

            function confirmDeleteLaporan(e, form) {
                e.preventDefault();
                Swal.fire({
                    title: 'Hapus Laporan?',
                    text: 'Data laporan ini akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="fas fa-trash"></i> Hapus',
                    cancelButtonText: 'Batal',
                }).then(r => {
                    if (r.isConfirmed) form.submit();
                });
                return false;
            }

            // ── Export Modal ─────────────────────────────────────────────────
            let lkgExpSelect2Inited = false;

            function openLkgExportModal() {
                const modal = document.getElementById('lkgExportModal');
                if (!modal) return;
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';

                // Init Select2 sekali saja
                if (!lkgExpSelect2Inited && typeof $ !== 'undefined' && $.fn.select2) {
                    $('.lkg-exp-select').select2({
                        dropdownParent: $('#lkgExportModal'),
                        allowClear: true,
                        language: {
                            noResults: () => 'Tidak ditemukan'
                        },
                        placeholder: function() {
                            return $(this).data('placeholder') || 'Pilih...';
                        },
                    });
                    lkgExpSelect2Inited = true;
                }
            }

            function closeLkgExportModal() {
                const modal = document.getElementById('lkgExportModal');
                if (!modal) return;
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }

            document.getElementById('lkgExportModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeLkgExportModal();
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeLkgExportModal();
            });

            function doLkgExport(type) {
                const mulai = document.getElementById('lkgExpMulai')?.value;
                const selesai = document.getElementById('lkgExpSelesai')?.value;
                // Ambil nilai via jQuery/Select2 jika tersedia, fallback ke DOM biasa
                const kelasVal = (typeof $ !== 'undefined') ? $('#lkgExpKelas').val() : document.getElementById('lkgExpKelas')
                    ?.value;
                const gtkVal = (typeof $ !== 'undefined') ? $('#lkgExpGtk').val() : document.getElementById('lkgExpGtk')?.value;

                if (!mulai || !selesai) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal wajib diisi',
                        text: 'Pilih rentang tanggal terlebih dahulu.',
                        confirmButtonColor: '#0F766E'
                    });
                    return;
                }
                if (mulai > selesai) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rentang tidak valid',
                        text: 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.',
                        confirmButtonColor: '#0F766E'
                    });
                    return;
                }

                const params = new URLSearchParams({
                    tanggal_mulai: mulai,
                    tanggal_selesai: selesai
                });
                if (kelasVal) params.append('kelas_id', kelasVal);
                if (gtkVal) params.append('gtk_id', gtkVal);

                const baseUrl = type === 'pdf' ?
                    '{{ route('kehadiran-guru.export.pdf') }}' :
                    '{{ route('kehadiran-guru.export.excel') }}';

                const url = baseUrl + '?' + params.toString();

                if (type === 'pdf') {
                    window.open(url, '_blank');
                } else {
                    window.location.href = url;
                }

                closeLkgExportModal();
            }
        </script>
    @endpush
