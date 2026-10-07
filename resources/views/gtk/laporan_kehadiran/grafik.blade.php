@extends('layouts.app')

@section('title', 'Grafik Kehadiran Guru')

@push('styles')
    @include('components.event-styles')
    <style>
        .gkg-wrap {
            padding: 0 12px;
            max-width: 1280px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        @media(min-width:768px) {
            .gkg-wrap {
                padding: 0 20px;
            }
        }

        @media(min-width:1024px) {
            .gkg-wrap {
                padding: 0 28px;
            }
        }

        /* ── Filter card ── */
        .gkg-filter {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .gkg-filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        @media(min-width:640px) {
            .gkg-filter-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media(min-width:900px) {
            .gkg-filter-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        .form-label {
            display: block;
            font-size: .75rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .form-input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: .83rem;
            font-family: inherit;
            color: #0f172a;
            background: #fff;
            box-sizing: border-box;
            -webkit-appearance: none;
            appearance: none;
        }

        .form-input:focus {
            outline: 2px solid #6366f1;
            outline-offset: -1px;
        }

        /* ── KPI grid ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }

        @media(min-width:480px) {
            .kpi-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        @media(min-width:768px) {
            .kpi-grid {
                grid-template-columns: repeat(9, 1fr);
                gap: 10px;
            }
        }

        .kpi-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 6px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .kpi-val {
            font-size: 1.25rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 3px;
        }

        .kpi-lbl {
            font-size: .55rem;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            line-height: 1.3;
        }

        @media(min-width:768px) {
            .kpi-val {
                font-size: 1.5rem;
            }

            .kpi-lbl {
                font-size: .62rem;
            }
        }

        /* ── Chart row ── */
        .chart-row-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            margin-bottom: 16px;
        }

        @media(min-width:768px) {
            .chart-row-2 {
                grid-template-columns: 1fr 1fr;
            }
        }

        .chart-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
        }

        /* judul & subtitle chart */
        .chart-card-title {
            font-size: .68rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin: 0 0 3px;
        }

        /* BARU: subtitle kecil di bawah judul — menjelaskan cara penghitungan */
        .chart-card-sub {
            font-size: .62rem;
            color: #cbd5e1;
            font-weight: 500;
            margin: 0 0 10px;
        }

        /* ── Mini stacked bar untuk distribusi status per guru ── */
        .status-mini-bar {
            display: flex;
            height: 6px;
            border-radius: 4px;
            overflow: hidden;
            min-width: 80px;
            gap: 1px;
        }

        /* ── Leaderboard ── */
        .leaderboard-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .leaderboard-row:last-child {
            border-bottom: none;
        }

        .leaderboard-name {
            font-size: .75rem;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .leaderboard-bar-track {
            margin-top: 3px;
            height: 5px;
            background: #f1f5f9;
            border-radius: 3px;
            overflow: hidden;
        }

        .leaderboard-bar-fill {
            height: 100%;
            border-radius: 3px;
            transition: width .4s;
        }

        .leaderboard-pct {
            font-size: .82rem;
            font-weight: 800;
        }

        .leaderboard-detail {
            font-size: .6rem;
            color: #94a3b8;
        }

        /* ── Tabel rekap ── */
        .rekap-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 80px;
        }

        .rekap-card-head {
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            background: #f8fafc;
        }

        .rekap-card-head h3 {
            font-size: .85rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .rekap-search {
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: .78rem;
            color: #374151;
            font-family: inherit;
            outline: none;
            min-width: 160px;
        }

        .rekap-search:focus {
            border-color: #6366f1;
        }

        .rekap-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .72rem;
        }

        .rekap-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .rekap-table th {
            padding: 9px 8px;
            text-align: left;
            font-size: .63rem;
            font-weight: 700;
            color: #64748b;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .rekap-table td {
            padding: 8px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .rekap-table tbody tr:last-child td {
            border-bottom: none;
        }

        .rekap-table tbody tr:hover td {
            background: rgba(99, 102, 241, .03);
        }

        .rekap-footer {
            padding: 10px 16px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
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
            justify-content: flex-end;
        }

        @media(min-width:768px) {
            .action-bar {
                padding: 10px 24px 12px;
                gap: 12px;
            }
        }

        .ab-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 11px 18px;
            border-radius: 10px;
            font-size: .82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: opacity .18s;
            white-space: nowrap;
            line-height: 1;
        }

        .ab-btn:active {
            opacity: .75;
        }

        .ab-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .ab-apply {
            background: #6366f1;
            color: #fff;
            box-shadow: 0 3px 12px rgba(99, 102, 241, .3);
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap gkg-wrap" style="padding-top:var(--header-h,56px);padding-bottom:80px;">

        {{-- Page strip --}}
        <div class="page-strip page-strip-event" style="margin-bottom:16px;">
            <div class="live-badge"><span class="live-dot"></span>Grafik Kehadiran Guru</div>
            <h2><i class="fas fa-chart-bar"></i> Grafik &amp; Rekap Kehadiran Guru</h2>
            <p>Analisis kehadiran guru berdasarkan rentang tanggal</p>
        </div>

        {{-- ── Filter ──────────────────────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('kehadiran-guru.grafik') }}" class="gkg-filter">
            <p style="font-size:.72rem;font-weight:700;color:#475569;margin:0 0 12px;">
                <i class="fas fa-filter" style="color:#6366f1;"></i> Filter Rentang Tanggal
            </p>
            <div class="gkg-filter-grid">
                <div>
                    <label class="form-label"><i class="fas fa-calendar-alt" style="color:#6366f1;"></i> Dari
                        Tanggal</label>
                    <input type="date" name="tanggal_mulai" class="form-input" value="{{ $tanggalMulai }}">
                </div>
                <div>
                    <label class="form-label"><i class="fas fa-calendar-alt" style="color:#6366f1;"></i> Sampai
                        Tanggal</label>
                    <input type="date" name="tanggal_selesai" class="form-input" value="{{ $tanggalSelesai }}">
                </div>
                @if (!auth()->user()->hasRole('gtk'))
                    <div>
                        <label class="form-label"><i class="fas fa-door-open" style="color:#6366f1;"></i> Kelas</label>
                        <select name="kelas_id" class="form-input">
                            <option value="">Semua Kelas</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label"><i class="fas fa-chalkboard-teacher" style="color:#6366f1;"></i>
                            Guru</label>
                        <select name="gtk_id" class="form-input">
                            <option value="">Semua Guru</option>
                            @foreach ($gtkList as $g)
                                <option value="{{ $g->id }}" {{ $gtkId == $g->id ? 'selected' : '' }}>
                                    {{ $g->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div style="display:flex;align-items:flex-end;padding-top:4px;">
                    <button type="submit" class="ab-btn ab-apply" style="width:100%;justify-content:center;">
                        <i class="fas fa-search"></i> Tampilkan
                    </button>
                </div>
            </div>
            <p style="font-size:.68rem;color:#94a3b8;margin:10px 0 0;">
                Periode: <strong style="color:#6366f1;">
                    {{ \Carbon\Carbon::parse($tanggalMulai)->translatedFormat('d M Y') }}
                    — {{ \Carbon\Carbon::parse($tanggalSelesai)->translatedFormat('d M Y') }}
                </strong>
            </p>
        </form>

        {{-- ── KPI strip ──────────────────────────────────────────────────────────── --}}
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-val" style="color:#6366f1;">{{ $total }}</div>
                <div class="kpi-lbl">Total Laporan</div>
            </div>
            @foreach ($allStatuses as $st)
                @php $cfg = $statusCfg[$st] ?? []; @endphp
                <div class="kpi-card"
                    style="background:{{ $cfg['bg'] ?? '#f8fafc' }};border-color:{{ $cfg['color'] ?? '#e2e8f0' }}44;">
                    <div class="kpi-val" style="color:{{ $cfg['color'] ?? '#64748b' }};">{{ $distribusi[$st] ?? 0 }}</div>
                    <div class="kpi-lbl" style="color:{{ $cfg['text'] ?? '#64748b' }};">{{ $cfg['label'] ?? $st }}</div>
                </div>
            @endforeach
        </div>

        {{-- ── Chart: Tren per hari (full width) ─────────────────────────────────── --}}
        <div class="chart-card" style="margin-bottom:16px;">
            <p class="chart-card-title"><i class="fas fa-chart-bar"></i> Tren Laporan Status Guru per Hari</p>
            @if (count($trenDates) > 0)
                <div id="chartTren" style="min-height:260px;"></div>
            @else
                <div style="text-align:center;padding:40px;color:#94a3b8;font-size:.82rem;">
                    <i class="fas fa-inbox" style="display:block;font-size:2rem;margin-bottom:10px;opacity:.35;"></i>
                    Tidak ada data tren untuk periode ini
                </div>
            @endif
        </div>

        {{-- ── Distribusi + Perlu Perhatian ──────────────────────────────────────── --}}
        {{--
        PERUBAHAN:
        • Label "Top 10 Guru Paling Sering Bermasalah" → "Perlu Perhatian — Top 10 Guru"
        • Tambah chart-card-sub yang menjelaskan status apa saja yang dihitung
        • Tooltip chart juga diperbarui agar lebih deskriptif
    --}}
        {{-- ── Chart: Distribusi Status — satu baris penuh ──────────── --}}
        <div style="margin-bottom:16px;">
            <div class="chart-card">
                <p class="chart-card-title"><i class="fas fa-chart-pie"></i> Distribusi Status</p>
                @php $hasDistribusi = array_sum($distribusi) > 0; @endphp
                @if ($hasDistribusi)
                    <div id="chartDonut" style="min-height:260px;"></div>
                @else
                    <div style="text-align:center;padding:40px;color:#94a3b8;font-size:.82rem;">
                        <i class="fas fa-inbox" style="display:block;font-size:2rem;margin-bottom:10px;opacity:.35;"></i>
                        Tidak ada data
                    </div>
                @endif
                <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach ($allStatuses as $st)
                        @php $cfg = $statusCfg[$st] ?? []; @endphp
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;
                                     background:{{ $cfg['bg'] ?? '#f8fafc' }};color:{{ $cfg['text'] ?? '#64748b' }};
                                     font-size:.6rem;font-weight:700;">
                            <span style="width:6px;height:6px;border-radius:50%;background:{{ $cfg['color'] ?? '#94a3b8' }};flex-shrink:0;"></span>
                            {{ $cfg['label'] ?? $st }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Perlu Perhatian + Kehadiran Tertinggi — satu baris ───── --}}
        <div class="chart-row-2" style="margin-bottom:16px;">

            {{-- Perlu Perhatian — Top 10 Guru (format leaderboard) --}}
            <div class="chart-card" style="display:flex;flex-direction:column;">
                <p class="chart-card-title">
                    <i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i> Perlu Perhatian — Top 10 Guru
                </p>
                <p class="chart-card-sub">
                    Jumlah laporan berstatus
                    <span style="color:#ef4444;font-weight:700;">Tidak Hadir</span>,
                    <span style="color:#eab308;font-weight:700;">Terlambat</span>, dan
                    <span style="color:#f97316;font-weight:700;">Tanpa Laporan</span>
                    dalam periode ini.
                </p>
                @if (count($topBermasalah) > 0)
                    @foreach ($topBermasalah as $i => $guru)
                        @php
                            $maxProb = $topBermasalah[0]['problematic'] ?: 1;
                            $barW    = max(4, round(($guru['problematic'] / $maxProb) * 100));
                            $bgRow   = $i === 0 ? '#fff5f5' : ($i % 2 === 0 ? '#fff' : '#fafafa');
                        @endphp
                        <div class="leaderboard-row" style="background:{{ $bgRow }};">
                            <span style="font-size:{{ $i < 3 ? '1.1rem' : '.75rem' }};min-width:22px;text-align:center;line-height:1;">
                                {{ ['🔴','🟠','🟡'][$i] ?? ($i + 1 . '.') }}
                            </span>
                            <div style="flex:1;min-width:0;">
                                <div class="leaderboard-name">{{ $guru['nama'] }}</div>
                                <div class="leaderboard-bar-track">
                                    <div class="leaderboard-bar-fill"
                                        style="width:{{ $barW }}%;background:{{ $i === 0 ? '#ef4444' : '#f97316' }};"></div>
                                </div>
                            </div>
                            <div style="text-align:right;flex-shrink:0;">
                                <div class="leaderboard-pct" style="color:{{ $i === 0 ? '#b91c1c' : '#c2410c' }};">
                                    {{ $guru['problematic'] }}x
                                </div>
                                <div class="leaderboard-detail">laporan</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="text-align:center;padding:40px;color:#22c55e;font-size:.82rem;
                                flex:1;display:flex;align-items:center;justify-content:center;flex-direction:column;">
                        <i class="fas fa-check-circle" style="font-size:2rem;margin-bottom:10px;opacity:.7;"></i>
                        Semua guru hadir tepat waktu 🎉
                    </div>
                @endif
            </div>

            {{-- Kehadiran Tertinggi — Top 10 Guru --}}
            <div class="chart-card" style="display:flex;flex-direction:column;">
                <p class="chart-card-title">
                    <i class="fas fa-trophy" style="color:#f59e0b;"></i> Kehadiran Tertinggi
                </p>
                <p class="chart-card-sub">
                    % hadir tepat waktu — hanya guru dengan minimal 5 laporan yang ditampilkan agar persentase representatif.
                </p>
                @if (count($topTerbaik) > 0)
                    @foreach ($topTerbaik as $i => $guru)
                        @php
                            $medals = ['🥇', '🥈', '🥉'];
                            $icon   = $medals[$i] ?? ($i + 1 . '.');
                            $pct    = $guru['hijau_pct'];
                            $barW   = max(4, $pct);
                            $bgRow  = $i === 0 ? '#fffbeb' : ($i === 1 ? '#f8fafc' : '#fff');
                        @endphp
                        <div class="leaderboard-row" style="background:{{ $bgRow }};">
                            <span style="font-size:{{ $i < 3 ? '1.1rem' : '.75rem' }};min-width:22px;text-align:center;line-height:1;">
                                {{ $icon }}
                            </span>
                            <div style="flex:1;min-width:0;">
                                <div class="leaderboard-name">{{ $guru['nama'] }}</div>
                                <div class="leaderboard-bar-track">
                                    <div class="leaderboard-bar-fill"
                                        style="width:{{ $barW }}%;background:{{ $i === 0 ? '#f59e0b' : '#22c55e' }};"></div>
                                </div>
                            </div>
                            <div style="text-align:right;flex-shrink:0;">
                                <div class="leaderboard-pct" style="color:{{ $i === 0 ? '#d97706' : '#15803d' }};">
                                    {{ $pct }}%
                                </div>
                                <div class="leaderboard-detail">{{ $guru['hijau_count'] }}/{{ $guru['total'] }}</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="text-align:center;padding:40px;color:#94a3b8;font-size:.82rem;
                                flex:1;display:flex;align-items:center;justify-content:center;flex-direction:column;">
                        <i class="fas fa-inbox" style="font-size:2rem;margin-bottom:10px;opacity:.35;"></i>
                        Belum ada data cukup (min. 5 laporan per guru)
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Tabel rekap semua guru ──────────────────────────────────────────────── --}}
        <div class="rekap-card">
            <div class="rekap-card-head">
                <h3><i class="fas fa-table" style="color:#6366f1;"></i> Rekap Kehadiran Semua Guru</h3>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:.7rem;color:#94a3b8;">{{ count($rekapGuru) }} guru</span>
                    <input type="text" id="rekapSearch" class="rekap-search" placeholder="Cari nama guru…"
                        oninput="rekapTbl.search(this.value)">
                </div>
            </div>

            @if (count($rekapGuru) > 0)
                <div class="rekap-table-wrap">
                    <table class="rekap-table">
                        <thead>
                            <tr>
                                <th style="padding-left:14px;">#</th>
                                <th>Nama Guru</th>
                                <th style="text-align:center;">Total</th>
                                @foreach ($allStatuses as $st)
                                    @php $cfg = $statusCfg[$st] ?? []; @endphp
                                    <th
                                        style="text-align:center;background:{{ $cfg['bg'] ?? '#f8fafc' }};color:{{ $cfg['text'] ?? '#64748b' }};min-width:72px;">
                                        <span
                                            style="display:inline-block;width:7px;height:7px;border-radius:50%;
                                                 background:{{ $cfg['color'] ?? '#94a3b8' }};margin-right:3px;vertical-align:middle;"></span>
                                        {{ $cfg['label'] ?? $st }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="rekapTbody">
                            @foreach ($rekapGuru as $i => $guru)
                                @php
                                    $hijauPct =
                                        $guru['total'] > 0
                                            ? round((($guru['statuses']['hijau'] ?? 0) / $guru['total']) * 100)
                                            : 0;
                                    $problematic =
                                        ($guru['statuses']['merah'] ?? 0) +
                                        ($guru['statuses']['kuning'] ?? 0) +
                                        ($guru['statuses']['orange'] ?? 0);
                                    $rowBg = $problematic > 5 ? '#fff5f5' : ($hijauPct >= 80 ? '#f0fdf4' : '#fff');
                                @endphp
                                <tr class="rekap-row" data-nama="{{ strtolower($guru['nama']) }}"
                                    data-orig-bg="{{ $rowBg }}" style="background:{{ $rowBg }};">
                                    <td class="rg-no" style="padding-left:14px;color:#94a3b8;font-size:.65rem;">
                                        {{ $i + 1 }}</td>
                                    <td style="font-weight:600;color:#0f172a;">
                                        {{ $guru['nama'] }}
                                        @if ($hijauPct >= 80)
                                            <span
                                                style="margin-left:4px;font-size:.6rem;background:#dcfce7;color:#15803d;padding:1px 5px;border-radius:10px;font-weight:700;">Rajin</span>
                                        @elseif($problematic > 5)
                                            <span
                                                style="margin-left:4px;font-size:.6rem;background:#fee2e2;color:#b91c1c;padding:1px 5px;border-radius:10px;font-weight:700;">Perhatian</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;font-weight:700;color:#374151;">{{ $guru['total'] }}</td>
                                    @foreach ($allStatuses as $st)
                                        @php
                                            $cfg = $statusCfg[$st] ?? [];
                                            $val = $guru['statuses'][$st] ?? 0;
                                        @endphp
                                        <td style="text-align:center;">
                                            @if ($val > 0)
                                                <span
                                                    style="display:inline-block;min-width:26px;padding:2px 7px;border-radius:10px;
                                                         background:{{ $cfg['bg'] ?? '#f8fafc' }};color:{{ $cfg['text'] ?? '#64748b' }};font-weight:700;">
                                                    {{ $val }}
                                                </span>
                                            @else
                                                <span style="color:#cbd5e1;">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="rekap-footer">
                    <span id="rekapInfo" style="font-size:.65rem;color:#94a3b8;"></span>
                    <div id="rekapPages" style="display:flex;gap:4px;flex-wrap:wrap;"></div>
                </div>

                {{-- PERUBAHAN: keterangan baris merah diperjelas dengan menyebut status-nya --}}
                <p style="padding:0 16px 12px;margin:0;font-size:.65rem;color:#94a3b8;">
                    Baris <span
                        style="background:#f0fdf4;color:#15803d;padding:0 4px;border-radius:3px;font-weight:700;">hijau
                        muda</span>
                    = ≥80% hadir tepat waktu.
                    Baris <span
                        style="background:#fff5f5;color:#b91c1c;padding:0 4px;border-radius:3px;font-weight:700;">merah
                        muda</span>
                    = &gt;5 laporan <strong>Tidak Hadir / Terlambat / Tidak Tepat Waktu</strong>.
                </p>
            @else
                <div style="padding:40px;text-align:center;color:#94a3b8;font-size:.82rem;">
                    <i class="fas fa-inbox" style="display:block;font-size:2rem;margin-bottom:10px;opacity:.35;"></i>
                    Belum ada data laporan guru dalam periode ini
                </div>
            @endif
        </div>

    </div>{{-- /gkg-wrap --}}

    {{-- Action bar --}}
    <div class="action-bar">
        <a href="{{ route('kehadiran-guru.laporan') }}" class="ab-btn ab-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <button type="button" class="ab-btn" id="btnExportPdf" onclick="exportGrafikPdf()"
            style="background:#dc2626;color:#fff;box-shadow:0 3px 10px rgba(220,38,38,.3);">
            <i class="fas fa-file-pdf"></i> Export PDF
        </button>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js"></script>
    <script>
        (function() {
            'use strict';

            /* ─── Data dari server ────────────────────────────────────── */
            var statusCfg = @json($statusCfg);
            var statusOrder = @json($allStatuses);
            var trenDates = @json($trenDates);
            var trenSeries = @json($trenSeries);
            var distribusi = @json($distribusi);
            var topBermasalah = @json($topBermasalah);
            var rekapGuru = @json($rekapGuru);

            function color(st) {
                return (statusCfg[st] || {}).color || '#94a3b8';
            }

            function label(st) {
                return (statusCfg[st] || {}).label || st;
            }

            /* instance chart (dipakai export PDF) */
            var chartTrenInst  = null;
            var chartDonutInst = null;
            /* chartBarInst dihapus — diganti leaderboard HTML */

            /* ─── Chart: Tren stacked bar ────────────────────────────── */
            var elTren = document.getElementById('chartTren');
            if (elTren && trenDates.length) {
                chartTrenInst = new ApexCharts(elTren, {
                    chart: {
                        type: 'bar',
                        height: 260,
                        stacked: true,
                        toolbar: {
                            show: false
                        },
                        fontFamily: 'inherit'
                    },
                    series: trenSeries.map(function(s) {
                        return {
                            name: label(s.name),
                            data: s.data,
                            color: color(s.name)
                        };
                    }),
                    xaxis: {
                        categories: trenDates,
                        labels: {
                            rotate: -30,
                            style: {
                                fontSize: '10px'
                            },
                            formatter: function(v) {
                                if (!v) return '';
                                var d = new Date(v);
                                return isNaN(d) ? v : d.getDate() + '/' + (d.getMonth() + 1);
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: '10px'
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        fontSize: '11px'
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function(v) {
                                return v + ' laporan';
                            }
                        }
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '65%'
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    grid: {
                        strokeDashArray: 3
                    },
                });
                chartTrenInst.render();
            }

            /* ─── Chart: Donut distribusi ────────────────────────────── */
            var elDonut = document.getElementById('chartDonut');
            if (elDonut) {
                var donutLabels = [],
                    donutVals = [],
                    donutColors = [];
                statusOrder.forEach(function(st) {
                    var v = distribusi[st] || 0;
                    if (v > 0) {
                        donutLabels.push(label(st));
                        donutVals.push(v);
                        donutColors.push(color(st));
                    }
                });
                if (donutVals.length) {
                    chartDonutInst = new ApexCharts(elDonut, {
                        chart: {
                            type: 'donut',
                            height: 260,
                            fontFamily: 'inherit',
                            toolbar: {
                                show: false
                            }
                        },
                        series: donutVals,
                        labels: donutLabels,
                        colors: donutColors,
                        legend: {
                            position: 'bottom',
                            fontSize: '11px'
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function(pct) {
                                return Math.round(pct) + '%';
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: function(v) {
                                    return v + ' laporan';
                                }
                            }
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '60%'
                                }
                            }
                        },
                    });
                    chartDonutInst.render();
                }
            }

            /* ─── Tabel pagination & search ─────────────────────────── */
            var allRows = Array.from(document.querySelectorAll('#rekapTbody .rekap-row'));
            var filtered = allRows.slice();
            var perPage = 20;
            var curPage = 1;
            var searchVal = '';

            window.rekapTbl = {
                search: function(val) {
                    searchVal = val.toLowerCase().trim();
                    filtered = searchVal ? allRows.filter(function(r) {
                        return r.dataset.nama.includes(searchVal);
                    }) : allRows.slice();
                    curPage = 1;
                    rekapTbl.render();
                },
                goTo: function(p) {
                    curPage = p;
                    rekapTbl.render();
                    var card = document.querySelector('.rekap-card');
                    if (card) card.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                },
                render: function() {
                    var total = filtered.length;
                    var tp = Math.max(1, Math.ceil(total / perPage));
                    if (curPage > tp) curPage = tp;
                    var start = (curPage - 1) * perPage;
                    var end = Math.min(start + perPage, total);

                    allRows.forEach(function(r) {
                        r.style.display = 'none';
                    });
                    var num = 1;
                    filtered.forEach(function(r, i) {
                        if (i >= start && i < end) {
                            r.style.display = '';
                            var noCell = r.querySelector('.rg-no');
                            if (noCell) noCell.textContent = start + num;
                            num++;
                        }
                    });

                    var info = document.getElementById('rekapInfo');
                    if (info) {
                        info.textContent = total ?
                            'Menampilkan ' + (start + 1) + '–' + end + ' dari ' + total + (searchVal ?
                                ' hasil' : ' guru') + '.' :
                            'Tidak ada hasil.';
                    }

                    var pagesEl = document.getElementById('rekapPages');
                    if (!pagesEl) return;
                    if (tp <= 1) {
                        pagesEl.innerHTML = '';
                        return;
                    }

                    var b =
                        'display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;padding:0 7px;border-radius:6px;font-size:.7rem;font-weight:700;cursor:pointer;border:1px solid #e2e8f0;font-family:inherit;';
                    var bn = b + 'background:#fff;color:#475569;';
                    var ba = b + 'background:#6366f1;color:#fff;border-color:#6366f1;cursor:default;';
                    var bd = b + 'background:#f8fafc;color:#cbd5e1;cursor:not-allowed;';

                    var html = curPage > 1 ?
                        '<button style="' + bn + '" onclick="rekapTbl.goTo(' + (curPage - 1) +
                        ')"><i class="fas fa-chevron-left" style="font-size:.6rem;"></i></button>' :
                        '<span style="' + bd +
                        '"><i class="fas fa-chevron-left" style="font-size:.6rem;"></i></span>';

                    var from = Math.max(1, curPage - 2),
                        to = Math.min(tp, curPage + 2);
                    if (from > 1) {
                        html += '<button style="' + bn + '" onclick="rekapTbl.goTo(1)">1</button>';
                        if (from > 2) html += '<span style="' + bd + '">…</span>';
                    }
                    for (var p = from; p <= to; p++) {
                        html += p === curPage ?
                            '<span style="' + ba + '">' + p + '</span>' :
                            '<button style="' + bn + '" onclick="rekapTbl.goTo(' + p + ')">' + p + '</button>';
                    }
                    if (to < tp) {
                        if (to < tp - 1) html += '<span style="' + bd + '">…</span>';
                        html += '<button style="' + bn + '" onclick="rekapTbl.goTo(' + tp + ')">' + tp +
                            '</button>';
                    }
                    html += curPage < tp ?
                        '<button style="' + bn + '" onclick="rekapTbl.goTo(' + (curPage + 1) +
                        ')"><i class="fas fa-chevron-right" style="font-size:.6rem;"></i></button>' :
                        '<span style="' + bd +
                        '"><i class="fas fa-chevron-right" style="font-size:.6rem;"></i></span>';

                    pagesEl.innerHTML = html;
                },
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', rekapTbl.render);
            } else {
                rekapTbl.render();
            }

            /* ─── Export PDF ─────────────────────────────────────────── */
            window.exportGrafikPdf = async function() {
                var btn = document.getElementById('btnExportPdf');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyiapkan…';
                }

                try {
                    /* ── 1. Capture chart images ── */
                    var imgTren  = chartTrenInst  ? (await chartTrenInst.dataURI()).imgURI  : null;
                    var imgDonut = chartDonutInst ? (await chartDonutInst.dataURI()).imgURI : null;

                    /* ── 2. Capture leaderboard "Perlu Perhatian" ── */
                    var leaderboardBermasalahHtml = '';
                    var allLeaderboardRows = Array.from(document.querySelectorAll('.leaderboard-row'));
                    /* Dua card: card pertama = Perlu Perhatian, card kedua = Kehadiran Tertinggi */
                    var allCards = Array.from(document.querySelectorAll('.chart-row-2 .chart-card'));
                    var cardBermasalah = allCards[0] || null;
                    var cardTerbaik    = allCards[1] || null;

                    function buildLeaderboardHtml(cardEl) {
                        var out = '';
                        if (!cardEl) return out;
                        cardEl.querySelectorAll('.leaderboard-row').forEach(function(row, idx) {
                            var icon    = row.querySelector('span:first-child');
                            var nama    = row.querySelector('.leaderboard-name');
                            var pct     = row.querySelector('.leaderboard-pct');
                            var det     = row.querySelector('.leaderboard-detail');
                            var barFill = row.querySelector('.leaderboard-bar-fill');
                            if (!nama) return;
                            var barColor = barFill ? barFill.style.background : '#22c55e';
                            var barWidth = barFill ? barFill.style.width       : '0%';
                            var pctColor = pct     ? pct.style.color           : '#15803d';
                            out += '<div style="display:flex;align-items:center;gap:8px;padding:5px 8px;'
                                 + 'border-bottom:1px solid #e8e8e8;">'
                                 + '<span style="font-size:' + (idx < 3 ? '12px' : '9px') + ';min-width:20px;text-align:center;">'
                                 + (icon ? icon.innerText.trim() : (idx + 1) + '.') + '</span>'
                                 + '<div style="flex:1;min-width:0;">'
                                 + '<div style="font-size:7.5px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'
                                 + nama.innerText.trim() + '</div>'
                                 + '<div style="margin-top:2px;height:4px;background:#f1f5f9;border-radius:2px;overflow:hidden;">'
                                 + '<div style="height:100%;width:' + barWidth + ';background:' + barColor + ';border-radius:2px;"></div>'
                                 + '</div></div>'
                                 + '<div style="text-align:right;flex-shrink:0;">'
                                 + '<div style="font-size:9px;font-weight:800;color:' + pctColor + ';">'
                                 + (pct ? pct.innerText.trim() : '') + '</div>'
                                 + '<div style="font-size:6px;color:#888;">' + (det ? det.innerText.trim() : '') + '</div>'
                                 + '</div></div>';
                        });
                        return out;
                    }

                    var leaderBermasalahHtml = buildLeaderboardHtml(cardBermasalah);
                    var leaderTerbaikHtml    = buildLeaderboardHtml(cardTerbaik);

                    /* ── 3. Tabel rekap — SEMUA baris (abaikan pagination) ── */
                    var allTableRows = Array.from(document.querySelectorAll('#rekapTbody .rekap-row'));
                    allTableRows.forEach(function(r) { r.style.display = ''; });

                    var rawThead = document.querySelector('.rekap-table thead');
                    var theadClean = '<tr>';
                    if (rawThead) {
                        rawThead.querySelectorAll('th').forEach(function(th) {
                            theadClean += '<th>' + th.innerText.trim() + '</th>';
                        });
                    }
                    theadClean += '</tr>';

                    var tbodyClean = '';
                    allTableRows.forEach(function(tr) {
                        tbodyClean += '<tr>';
                        tr.querySelectorAll('td').forEach(function(td) {
                            tbodyClean += '<td>' + (td.innerText.trim().replace(/\s+/g, ' ') || '—') + '</td>';
                        });
                        tbodyClean += '</tr>';
                    });

                    rekapTbl.render(); /* kembalikan pagination */

                    /* ── 4. KPI strip ── */
                    var kpiHtmlClean = '';
                    document.querySelectorAll('.kpi-grid .kpi-card').forEach(function(card) {
                        var val = card.querySelector('.kpi-val');
                        var lbl = card.querySelector('.kpi-lbl');
                        kpiHtmlClean += '<div class="kpi-card">'
                            + '<div class="kpi-val">' + (val ? val.innerText : '') + '</div>'
                            + '<div class="kpi-lbl">' + (lbl ? lbl.innerText : '') + '</div>'
                            + '</div>';
                    });

                    /* ── 5. Periode ── */
                    var periodeText = (document.querySelector('.gkg-filter p:last-child') || {}).innerText || '';

                    /* ── 6. Bangun HTML dokumen ── */
                    var html = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">'
                        + '<title>Grafik Kehadiran Guru</title><style>'
                        + '@page{size:A4 landscape;margin:10mm 12mm;}'
                        + '*{box-sizing:border-box;}'
                        + 'body{font-family:Arial,sans-serif;font-size:10px;color:#000;margin:0;padding:0;}'
                        + 'h1{font-size:14px;font-weight:800;margin:0 0 2px;}'
                        + 'h2{font-size:9px;font-weight:600;margin:0;color:#444;}'
                        + '.header{display:flex;align-items:center;justify-content:space-between;'
                        +         'border-bottom:2px solid #000;padding-bottom:7px;margin-bottom:8px;}'
                        + '.badge{border:2px solid #000;font-size:8px;font-weight:700;padding:2px 8px;'
                        +        'border-radius:4px;letter-spacing:.05em;}'
                        + '.periode{font-size:8px;font-weight:700;margin-bottom:8px;color:#333;}'
                        + '.kpi-grid{display:grid;grid-template-columns:repeat(9,1fr);gap:4px;margin-bottom:8px;}'
                        + '.kpi-card{border:1px solid #ccc;border-radius:3px;padding:4px 3px;text-align:center;}'
                        + '.kpi-val{font-size:12px;font-weight:800;line-height:1;margin-bottom:2px;}'
                        + '.kpi-lbl{font-size:5.5px;font-weight:700;text-transform:uppercase;color:#555;line-height:1.2;}'
                        + '.section-title{font-size:8px;font-weight:700;color:#333;text-transform:uppercase;'
                        +                'letter-spacing:.04em;margin:0 0 2px;}'
                        + '.section-sub{font-size:6.5px;color:#777;margin:0 0 5px;}'
                        + '.chart-card{border:1px solid #ccc;border-radius:4px;padding:7px;margin-bottom:8px;}'
                        /* donut satu baris penuh */
                        + '.donut-card{border:1px solid #ccc;border-radius:4px;padding:7px;margin-bottom:8px;}'
                        + '.donut-card img{width:100%;max-height:260px;height:auto;display:block;}'
                        /* dua kolom leaderboard */
                        + '.two-col{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;}'
                        /* tabel */
                        + '.tbl-card{border:1px solid #aaa;border-radius:4px;overflow:hidden;}'
                        + '.tbl-head{padding:6px 10px;border-bottom:1px solid #aaa;'
                        +           'font-size:9px;font-weight:800;background:#e8e8e8;}'
                        + 'table{width:100%;border-collapse:collapse;font-size:7px;}'
                        + 'thead tr{background:#d8d8d8;border-bottom:2px solid #999;}'
                        + 'th{padding:4px 3px;text-align:left;font-size:6.5px;font-weight:700;'
                        +    'white-space:nowrap;text-transform:uppercase;border-right:1px solid #bbb;}'
                        + 'th:last-child{border-right:none;}'
                        + 'td{padding:3px;border-bottom:1px solid #ddd;border-right:1px solid #e8e8e8;vertical-align:middle;}'
                        + 'td:last-child{border-right:none;}'
                        + 'tbody tr:nth-child(even){background:#f4f4f4;}'
                        + 'tbody tr:nth-child(odd){background:#fff;}'
                        + '.footer{margin-top:6px;border-top:1px solid #ccc;padding-top:5px;'
                        +          'font-size:7px;color:#555;display:flex;justify-content:space-between;}'
                        + '@media print{'
                        + '  .chart-card,.two-col,.donut-card{page-break-inside:avoid;}'
                        + '  .tbl-card{page-break-before:always;}'
                        + '  thead{display:table-header-group;}'
                        + '}'
                        + '</style></head><body>';

                    /* Header */
                    html += '<div class="header"><div>'
                          + '<h1>Grafik &amp; Rekap Kehadiran Guru</h1>'
                          + '<h2>Laporan Kehadiran Berdasarkan Status KBM</h2>'
                          + '</div><span class="badge">SMK</span></div>';
                    html += '<p class="periode">' + periodeText + '</p>';

                    /* KPI */
                    html += '<div class="kpi-grid">' + kpiHtmlClean + '</div>';

                    /* Chart tren — satu baris penuh */
                    if (imgTren) {
                        html += '<div class="chart-card">'
                              + '<p class="section-title">Tren Laporan Status Guru per Hari</p>'
                              + '<img src="' + imgTren + '" style="width:100%;height:auto;display:block;"></div>';
                    }

                    /* Donut distribusi — satu baris penuh */
                    if (imgDonut) {
                        html += '<div class="donut-card">'
                              + '<p class="section-title">Distribusi Status Kehadiran</p>'
                              + '<img src="' + imgDonut + '"></div>';
                    }

                    /* Perlu Perhatian + Kehadiran Tertinggi — dua kolom */
                    if (leaderBermasalahHtml || leaderTerbaikHtml) {
                        html += '<div class="two-col">';
                        html += '<div class="chart-card">'
                              + '<p class="section-title">&#x26A0; Perlu Perhatian — Top 10 Guru</p>'
                              + '<p class="section-sub">Tidak Hadir + Terlambat + Tanpa Laporan</p>'
                              + (leaderBermasalahHtml || '<p style="color:#888;font-size:7px;padding:8px;">Tidak ada data</p>')
                              + '</div>';
                        html += '<div class="chart-card">'
                              + '<p class="section-title">&#x1F3C6; Kehadiran Tertinggi — Top 10 Guru</p>'
                              + '<p class="section-sub">% hadir tepat waktu &mdash; min. 5 laporan</p>'
                              + (leaderTerbaikHtml || '<p style="color:#888;font-size:7px;padding:8px;">Belum ada data cukup</p>')
                              + '</div>';
                        html += '</div>';
                    }

                    /* Tabel rekap — halaman baru, semua baris, hitam-putih */
                    html += '<div class="tbl-card">'
                          + '<div class="tbl-head">Rekap Kehadiran Semua Guru &mdash; '
                          + allTableRows.length + ' guru</div>'
                          + '<table><thead>' + theadClean + '</thead>'
                          + '<tbody>' + tbodyClean + '</tbody></table></div>';

                    /* Footer */
                    html += '<div class="footer">'
                          + '<span>Dicetak: ' + new Date().toLocaleDateString('id-ID', {
                                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                            }) + '</span>'
                          + '<span>' + periodeText + '</span>'
                          + '</div>';

                    html += '</body></html>';

                    /* Buka print window */
                    var w = window.open('', '_blank', 'width=1100,height=750');
                    if (!w) {
                        alert('Popup diblokir browser. Izinkan popup untuk domain ini lalu coba lagi.');
                        return;
                    }
                    w.document.write(html);
                    w.document.close();
                    w.onload = function() {
                        setTimeout(function() { w.print(); }, 600);
                    };

                } catch (err) {
                    alert('Gagal menyiapkan PDF: ' + err.message);
                } finally {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-file-pdf"></i> Export PDF';
                    }
                }
            };

        })();
    </script>
@endpush
