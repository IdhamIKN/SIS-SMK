@extends('layouts.app')

@section('title', 'Rekap Laporan Kehadiran Guru')

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
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }

        @media(min-width:480px) {
            .stat-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media(min-width:640px) {
            .stat-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        @media(min-width:768px) {
            .stat-grid {
                grid-template-columns: repeat(9, 1fr);
                gap: 10px;
                margin-bottom: 20px;
            }
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 6px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        @media(min-width:768px) {
            .stat-card {
                border-radius: 12px;
                padding: 14px 8px;
            }
        }

        .stat-value {
            font-size: 1.2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 3px;
        }

        @media(min-width:768px) {
            .stat-value {
                font-size: 1.5rem;
                margin-bottom: 5px;
            }
        }

        .stat-label {
            font-size: .55rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        @media(min-width:768px) {
            .stat-label {
                font-size: .62rem;
            }
        }

        /* ── Filter ── */
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

        /* ── Card head ── */
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

        .lci {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            background: #fff;
        }

        .lci:last-child {
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
            font-size: .7rem;
            color: #64748b;
        }

        /* ── Action button ── */
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
            white-space: nowrap;
        }

        .btn-view {
            background: #eff6ff;
            color: #1d4ed8;
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

        /* ── Action bar ── */
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
            gap: 7px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: .82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            line-height: 1;
            white-space: nowrap;
        }

        @media(min-width:768px) {
            .ab-btn {
                flex: unset;
                min-width: 130px;
                font-size: .875rem;
                padding: 12px 20px;
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
    </style>
@endpush

@section('content')
    @php
        $hasFilter =
            request()->hasAny(['kelas_id', 'gtk_id']) ||
            request('tanggal_mulai') !== now()->subDays(30)->format('Y-m-d') ||
            request('tanggal_selesai') !== now()->format('Y-m-d');
    @endphp

    <div class="event-wrap lkg-wrap" style="padding-top:var(--header-h,56px);padding-bottom: 148px;">

        {{-- Page Strip --}}
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>Rekap Laporan Kehadiran</div>
            <h2><i class="fas fa-chart-bar"></i> Rekap Kehadiran Guru</h2>
            <p>
                Periode {{ \Carbon\Carbon::parse($tanggalMulai)->translatedFormat('d M Y') }}
                – {{ \Carbon\Carbon::parse($tanggalSelesai)->translatedFormat('d M Y') }}
            </p>
        </div>

        {{-- Stats ── 9 kartu ── --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value" style="color:#0ea5e9;">{{ $stats['total_laporan'] }}</div>
                <div class="stat-label">Total</div>
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
                <div class="stat-value" style="color:#64748b;">{{ $stats['abu'] }}</div>
                <div class="stat-label">TH+Tugas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#3b82f6;">{{ $stats['biru'] }}</div>
                <div class="stat-label">Pergi+Tugas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#ec4899;">{{ $stats['pink'] }}</div>
                <div class="stat-label">Pergi No Tugas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#f97316;">{{ $stats['orange'] }}</div>
                <div class="stat-label">Tanpa Laporan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#94a3b8;">{{ $stats['putih'] }}</div>
                <div class="stat-label">Belum Lapor</div>
            </div>
        </div>

        {{-- Filter ── --}}
        <form method="GET" action="{{ route('kehadiran-guru.rekap') }}" class="filter-section" id="rekapFilterForm">
            <button type="button" class="filter-toggle {{ $hasFilter ? 'open' : '' }}" id="rekapFilterToggle"
                onclick="toggleRekapFilter()">
                <span class="ft-left">
                    <i class="fas fa-filter"></i> Filter
                    @if ($hasFilter)
                        <span
                            style="background:#fef3c7;color:#b45309;font-size:.65rem;padding:2px 8px;border-radius:20px;font-weight:700;">Aktif</span>
                    @endif
                </span>
                <i class="fas fa-chevron-down ft-chevron"></i>
            </button>

            <div class="filter-body {{ $hasFilter ? 'open' : '' }}" id="rekapFilterBody">
                <div class="filter-grid">
                    <div>
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-input" value="{{ $tanggalMulai }}">
                    </div>
                    <div>
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-input" value="{{ $tanggalSelesai }}">
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
                            <a href="{{ route('kehadiran-guru.rekap') }}" class="action-btn"
                                style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:10px 14px;text-decoration:none;">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- Data Card ── --}}
        <div class="card" style="border-radius:12px;overflow:hidden;">
            <div class="c-head">
                <div class="c-icon" style="background:#fef3c7;color:#b45309;flex-shrink:0;"><i class="fas fa-list"></i>
                </div>
                <h3>Detail Rekap Kehadiran</h3>
                <span class="hbadge">{{ $rekapData->total() }} laporan</span>
            </div>

            @if ($rekapData->count() > 0)

                {{-- TABLE desktop ≥768px ── --}}
                <div class="lkg-table-wrap">
                    <table class="lkg-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Kelas</th>
                                <th>Guru</th>
                                <th>Mata Pelajaran</th>
                                <th>Status</th>
                                <th>Pelapor</th>
                                <th>Waktu Lapor</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rekapData as $laporan)
                                <tr>
                                    <td style="font-weight:700;white-space:nowrap;">
                                        {{ \Carbon\Carbon::parse($laporan->tanggal)->format('d/m/Y') }}
                                    </td>
                                    <td style="font-size:.75rem;white-space:nowrap;">Jam {{ $laporan->jam_ke }}</td>
                                    <td style="font-weight:600;">{{ $laporan->kelas?->nama_kelas ?? '-' }}</td>
                                    <td style="font-size:.82rem;">{{ $laporan->gtk?->nama_lengkap ?? '-' }}</td>
                                    <td style="font-size:.75rem;">{{ $laporan->jadwalKbm?->mata_pelajaran ?? '-' }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $laporan->status }}">
                                            <span class="status-dot"
                                                style="background:{{ $laporan->status_color }};"></span>
                                            {{ $laporan->status_label }}
                                        </span>
                                    </td>
                                    <td style="font-size:.75rem;">
                                        @if ($laporan->dilaporkan_oleh_siswa_id)
                                            <span style="display:inline-flex;align-items:center;gap:3px;">
                                                <i class="fas fa-user-graduate"
                                                    style="color:#0ea5e9;font-size:.65rem;"></i>
                                                {{ \Illuminate\Support\Str::limit($laporan->dilaporkanOlehSiswa?->nama_lengkap ?? 'Siswa', 14) }}
                                            </span>
                                        @elseif(str_starts_with($laporan->catatan ?? '', 'Auto-generated:'))
                                            <span style="color:#8b5cf6;display:inline-flex;align-items:center;gap:3px;">
                                                <i class="fas fa-robot" style="font-size:.65rem;"></i> Sistem
                                            </span>
                                        @else
                                            <span style="color:#64748b;display:inline-flex;align-items:center;gap:3px;">
                                                <i class="fas fa-user-tie" style="font-size:.65rem;"></i> Guru
                                            </span>
                                        @endif
                                    </td>
                                    <td style="font-size:.72rem;white-space:nowrap;color:#64748b;">
                                        {{ $laporan->waktu_laporan->format('d/m H:i') }}
                                    </td>
                                    <td style="font-size:.75rem;max-width:160px;">
                                        {{ $laporan->catatan ? \Illuminate\Support\Str::limit($laporan->catatan, 40) : '—' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('kehadiran-guru.show', $laporan) }}"
                                            class="action-btn btn-view">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- CARD LIST mobile <768px ── --}}
                <div class="lkg-card-list">
                    @foreach ($rekapData as $laporan)
                        <div class="lci">
                            <div class="lci-top">
                                <div>
                                    <div class="lci-tgl">
                                        {{ \Carbon\Carbon::parse($laporan->tanggal)->format('d/m/Y') }}
                                        &bull; Jam {{ $laporan->jam_ke }}
                                    </div>
                                    <div style="font-size:.7rem;color:#64748b;margin-top:2px;">
                                        Lapor {{ $laporan->waktu_laporan->format('H:i') }}
                                    </div>
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
                                    {{ \Illuminate\Support\Str::limit($laporan->gtk?->nama_lengkap ?? '-', 16) }}</span>
                            </div>
                            <div class="lci-bot">
                                <span>
                                    @if ($laporan->dilaporkan_oleh_siswa_id)
                                        <i class="fas fa-user-graduate" style="color:#0ea5e9;"></i>
                                        {{ \Illuminate\Support\Str::limit($laporan->dilaporkanOlehSiswa?->nama_lengkap ?? 'Siswa', 16) }}
                                    @elseif(str_starts_with($laporan->catatan ?? '', 'Auto-generated:'))
                                        <i class="fas fa-robot" style="color:#8b5cf6;"></i> Sistem
                                    @else
                                        <i class="fas fa-user-tie"></i> Guru Sendiri
                                    @endif
                                </span>
                                <a href="{{ route('kehadiran-guru.show', $laporan) }}" class="action-btn btn-view">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </div>
                            @if ($laporan->catatan)
                                <div
                                    style="margin-top:5px;font-size:.7rem;color:#64748b;font-style:italic;padding-top:5px;border-top:1px solid #f1f5f9;">
                                    {{ \Illuminate\Support\Str::limit($laporan->catatan, 80) }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Pagination ── --}}
                @if ($rekapData->hasPages())
                    @php
                        $cur = $rekapData->currentPage();
                        $last = $rekapData->lastPage();
                        $from = max(1, $cur - 2);
                        $to = min($last, $cur + 2);
                    @endphp
                    <div class="rekap-pagination">
                        @if ($rekapData->onFirstPage())
                            <span class="pg-btn disabled"><i class="fas fa-chevron-left"></i> Prev</span>
                        @else
                            <a href="{{ $rekapData->previousPageUrl() }}" class="pg-btn"><i
                                    class="fas fa-chevron-left"></i> Prev</a>
                        @endif

                        @if ($from > 1)
                            <a href="{{ $rekapData->url(1) }}" class="pg-btn pg-num">1</a>
                            @if ($from > 2)
                                <span class="pg-btn disabled pg-num">…</span>
                            @endif
                        @endif
                        @for ($p = $from; $p <= $to; $p++)
                            <a href="{{ $rekapData->url($p) }}"
                                class="pg-btn pg-num {{ $p === $cur ? 'active' : '' }}">{{ $p }}</a>
                        @endfor
                        @if ($to < $last)
                            @if ($to < $last - 1)
                                <span class="pg-btn disabled pg-num">…</span>
                            @endif
                            <a href="{{ $rekapData->url($last) }}" class="pg-btn pg-num">{{ $last }}</a>
                        @endif

                        @if ($rekapData->hasMorePages())
                            <a href="{{ $rekapData->nextPageUrl() }}" class="pg-btn">Next <i
                                    class="fas fa-chevron-right"></i></a>
                        @else
                            <span class="pg-btn disabled">Next <i class="fas fa-chevron-right"></i></span>
                        @endif
                    </div>
                @endif
            @else
                <div class="rekap-empty">
                    <i class="fas fa-inbox"></i>
                    <strong>Belum ada data</strong>
                    Tidak ada laporan kehadiran untuk periode dan filter yang dipilih.
                </div>
            @endif
        </div>

    </div>{{-- /lkg-wrap --}}

    {{-- Action Bar ── --}}
    <div class="action-bar">
        <a href="{{ route('kehadiran-guru.laporan') }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        @can('kehadiran-guru.create')
            <a href="{{ route('kehadiran-guru.create') }}" class="ab-btn ab-btn-primary">
                <i class="fas fa-plus"></i> Buat Laporan
            </a>
        @endcan
    </div>
@endsection

@push('scripts')
    <script>
        function toggleRekapFilter() {
            document.getElementById('rekapFilterToggle').classList.toggle('open');
            document.getElementById('rekapFilterBody').classList.toggle('open');
        }
    </script>
@endpush
