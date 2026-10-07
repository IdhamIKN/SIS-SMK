@extends('layouts.app')

@section('title', 'Dashboard Analitik Tata Tertib')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ══════════════════════════════════════════════════════════
               CHART — Responsive (mengikuti pola /tatib/pelanggaran)
               Breakpoints: xs <480 | sm 480-767 | md 768+ | lg 1024+
               ══════════════════════════════════════════════════════════ */

        /* ── Wrapper ── */
        .rp-wrap {
            padding: 0 12px;
            max-width: 1280px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        @media (min-width: 768px) {
            .rp-wrap {
                padding: 0 20px;
            }
        }

        @media (min-width: 1024px) {
            .rp-wrap {
                padding: 0 28px;
            }
        }

        /* ── Chart grids ── */
        .ch-grid-1 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .ch-grid-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .ch-grid-3 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        @media (min-width: 600px) {
            .ch-grid-2 {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (min-width: 600px) {
            .ch-grid-3 {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (min-width: 960px) {
            .ch-grid-3 {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }

        /* ── Card chart ── */
        .cc {
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 12px 14px 16px;
            box-shadow: 0 1px 6px rgba(15, 23, 42, .04);
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .cc {
                padding: 14px 16px 16px;
            }
        }

        .cc-head {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .cc-ico {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .82rem;
            flex-shrink: 0;
        }

        @media (min-width: 768px) {
            .cc-ico {
                width: 32px;
                height: 32px;
            }
        }

        .cc-title {
            font-size: .8rem;
            font-weight: 800;
            color: #0f172a;
            flex: 1;
            min-width: 0;
        }

        @media (min-width: 768px) {
            .cc-title {
                font-size: .82rem;
            }
        }

        .cc-badge {
            font-size: .65rem;
            font-weight: 700;
            padding: 2px 9px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #64748b;
            white-space: nowrap;
        }

        /* ── Stat chips ── */
        .stat-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }

        @media (min-width: 768px) {
            .stat-row {
                grid-template-columns: repeat(4, 1fr);
                gap: 10px;
                margin-bottom: 16px;
            }
        }

        .stat-chip {
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        @media (min-width: 768px) {
            .stat-chip {
                padding: 12px 14px;
                gap: 10px;
            }
        }

        .stat-ico {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
        }

        @media (min-width: 768px) {
            .stat-ico {
                width: 36px;
                height: 36px;
                font-size: 1rem;
                border-radius: 10px;
            }
        }

        .stat-lbl {
            font-size: .6rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
            line-height: 1.2;
        }

        @media (min-width: 768px) {
            .stat-lbl {
                font-size: .68rem;
            }
        }

        .stat-val {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
            margin-top: 2px;
        }

        @media (min-width: 768px) {
            .stat-val {
                font-size: 1.25rem;
            }
        }

        /* ── Filter collapsible ── */
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

        @media (min-width: 768px) {
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

        @media (min-width: 768px) {
            .filter-body {
                display: block !important;
                padding: 14px 16px;
            }
        }

        /* Filter grid: 1-col xs, auto md+ */
        .filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            align-items: end;
        }

        @media (min-width: 768px) {
            .filter-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

        /* ── Action bar (fixed bottom) ── */
        .action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 12px;
            padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px));
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 8px;
            z-index: 999;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
        }

        @media (min-width: 768px) {
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
            padding: 11px 14px;
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

        @media (min-width: 480px) {
            .ab-btn {
                font-size: .875rem;
            }
        }

        @media (min-width: 768px) {
            .ab-btn {
                flex: unset;
                min-width: 130px;
                padding: 11px 20px;
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

        .ab-btn-back:hover {
            background: #e2e8f0;
        }

        .ab-btn-primary {
            background: #f59e0b;
            color: #fff;
            box-shadow: 0 3px 12px rgba(245, 158, 11, .3);
        }

        /* ── Top-list table ── */
        .top-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .top-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .78rem;
            min-width: 280px;
        }

        .top-table th {
            padding: 6px 8px;
            text-align: left;
            font-size: .65rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }

        .top-table td {
            padding: 7px 8px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .top-table tbody tr:last-child td {
            border-bottom: none;
        }

        .top-table tbody tr:hover td {
            background: #f8fafc;
        }

        /* Kolom nama di xs: truncate */
        .top-table .col-nama {
            max-width: 120px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            font-weight: 600;
            color: #0f172a;
        }

        @media (min-width: 480px) {
            .top-table .col-nama {
                max-width: 160px;
            }
        }

        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 6px;
            font-size: .7rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .rank-1 {
            background: #fef3c7;
            color: #92400e;
        }

        .rank-2 {
            background: #f1f5f9;
            color: #475569;
        }

        .rank-3 {
            background: #fef9c3;
            color: #78350f;
        }

        .rank-n {
            background: #f8fafc;
            color: #94a3b8;
        }

        /* ── Doughnut legend layout di mobile ── */
        .donut-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            height: 190px;
        }

        @media (max-width: 479px) {
            .donut-wrap {
                flex-direction: column;
                height: auto;
                gap: 12px;
                padding-bottom: 4px;
            }

            .donut-canvas-wrap {
                width: 130px;
                height: 130px;
                flex-shrink: 0;
            }
        }

        @media (min-width: 480px) {
            .donut-canvas-wrap {
                position: relative;
                width: 160px;
                height: 160px;
                flex-shrink: 0;
            }
        }

        .donut-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
        }

        .donut-legend {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: .78rem;
        }

        @media (max-width: 479px) {
            .donut-legend {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 8px 16px;
                justify-content: center;
            }
        }

        /* ── Empty ── */
        .ch-empty {
            text-align: center;
            padding: 28px 10px;
            color: #94a3b8;
            font-size: .82rem;
        }

        .ch-empty i {
            display: block;
            font-size: 1.6rem;
            margin-bottom: 8px;
            opacity: .3;
        }

        /* ── Section divider ── */
        .section-lbl {
            font-size: .68rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: .06em;
            margin: 14px 0 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-lbl::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #eef2f7;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap rp-wrap"
        style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h, 0px) + 80px);">

        {{-- ── Page Strip ── --}}
        <div class="page-strip page-strip-event" style="--event-primary:#f59e0b;">
            <div class="live-badge"><span class="live-dot" style="background:#f59e0b;"></span>Tatib</div>
            <h2><i class="fas fa-chart-bar"></i> Dashboard Analitik Tata Tertib</h2>
            <p>Grafik &amp; statistik pelanggaran dan penghargaan · {{ $tahunAjaran }}</p>
        </div>

        {{-- ── Filter (collapsible on mobile) ── --}}
        <form method="GET" action="{{ route('admin.tatib.chart') }}" class="filter-section" id="chartFilterForm">
            <button type="button" class="filter-toggle" id="chartFilterToggle" onclick="toggleChartFilter()">
                <span class="ft-left"><i class="fas fa-calendar-alt"></i> Tahun Ajaran:
                    <strong>{{ $tahunAjaran }}</strong></span>
                <i class="fas fa-chevron-down ft-chevron"></i>
            </button>
            <div class="filter-body" id="chartFilterBody">
                <div class="filter-grid">
                    <div>
                        <label class="form-label">Tahun Ajaran</label>
                        <select name="tahun_ajaran" class="form-input" onchange="this.form.submit()">
                            @foreach ($tahunList as $t)
                                <option value="{{ $t }}" @selected($t == $tahunAjaran)>{{ $t }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </form>

        {{-- ── STAT CHIPS ── --}}
        <div class="stat-row">
            <div class="stat-chip">
                <div class="stat-ico" style="background:#eff6ff;color:#1d4ed8;"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-lbl">Total Siswa</div>
                    <div class="stat-val">{{ $totalSiswa }}</div>
                </div>
            </div>
            <div class="stat-chip">
                <div class="stat-ico" style="background:#fee2e2;color:#b91c1c;"><i class="fas fa-exclamation-circle"></i>
                </div>
                <div>
                    <div class="stat-lbl">Poin Pelanggaran</div>
                    <div class="stat-val" style="color:#b91c1c;">{{ $totalPelanggaran }}</div>
                </div>
            </div>
            <div class="stat-chip">
                <div class="stat-ico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-award"></i></div>
                <div>
                    <div class="stat-lbl">Poin Penghargaan</div>
                    <div class="stat-val" style="color:#15803d;">{{ $totalPenghargaan }}</div>
                </div>
            </div>
            <div class="stat-chip">
                <div class="stat-ico" style="background:#fff7ed;color:#c2410c;"><i class="fas fa-flag"></i></div>
                <div>
                    <div class="stat-lbl">Siswa Bermasalah</div>
                    <div class="stat-val" style="color:#c2410c;">{{ array_sum($dist) - ($dist['Aman'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        {{-- ── ROW 1: Doughnut pelanggaran/penghargaan + Distribusi status ── --}}
        <div class="section-lbl"><i class="fas fa-chart-pie"></i> Distribusi &amp; Komposisi</div>
        <div class="ch-grid-2">

            {{-- Doughnut: pelanggaran vs penghargaan --}}
            <div class="cc">
                <div class="cc-head">
                    <div class="cc-ico" style="background:#eff6ff;color:#1d4ed8;"><i class="fas fa-chart-pie"></i></div>
                    <span class="cc-title">Pelanggaran vs Penghargaan</span>
                    <span class="cc-badge">{{ $tahunAjaran }}</span>
                </div>
                <div class="donut-wrap">
                    <div class="donut-canvas-wrap" style="position:relative;">
                        <canvas id="c_donut_pvsr"></canvas>
                        <div class="donut-center">
                            <div style="font-size:1.1rem;font-weight:800;color:#0f172a;line-height:1;">
                                {{ $totalPelanggaran + $totalPenghargaan }}</div>
                            <div
                                style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:#94a3b8;margin-top:2px;">
                                total</div>
                        </div>
                    </div>
                    <div class="donut-legend">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span
                                style="width:12px;height:12px;border-radius:3px;background:#ef4444;display:inline-block;flex-shrink:0;"></span>
                            <span style="color:#64748b;font-size:.78rem;">Pelanggaran</span>
                            <strong
                                style="color:#b91c1c;margin-left:4px;font-size:.78rem;">{{ $totalPelanggaran }}</strong>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span
                                style="width:12px;height:12px;border-radius:3px;background:#22c55e;display:inline-block;flex-shrink:0;"></span>
                            <span style="color:#64748b;font-size:.78rem;">Penghargaan</span>
                            <strong
                                style="color:#15803d;margin-left:4px;font-size:.78rem;">{{ $totalPenghargaan }}</strong>
                        </div>
                        @php $rasio = ($totalPelanggaran + $totalPenghargaan) > 0 ? round($totalPelanggaran / ($totalPelanggaran + $totalPenghargaan) * 100) : 0; @endphp
                        <div style="padding:5px 10px;background:#f8fafc;border-radius:8px;font-size:.72rem;color:#64748b;">
                            Rasio pelanggaran <strong style="color:#b91c1c;">{{ $rasio }}%</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Horizontal Bar: distribusi status --}}
            <div class="cc">
                <div class="cc-head">
                    <div class="cc-ico" style="background:#fef9c3;color:#ca8a04;"><i class="fas fa-layer-group"></i></div>
                    <span class="cc-title">Distribusi Status Siswa</span>
                    <span class="cc-badge">{{ array_sum($dist) }} siswa</span>
                </div>
                <div style="height:200px;"><canvas id="c_dist_status"></canvas></div>
            </div>
        </div>

        {{-- ── ROW 2: Tren bulanan ── --}}
        <div class="section-lbl"><i class="fas fa-chart-line"></i> Tren Waktu</div>
        <div class="ch-grid-1">
            <div class="cc">
                <div class="cc-head">
                    <div class="cc-ico" style="background:#f0fdf4;color:#16a34a;"><i class="fas fa-chart-line"></i></div>
                    <span class="cc-title">Tren Pelanggaran & Penghargaan per Bulan</span>
                    <span class="cc-badge">{{ $tren->count() }} bulan</span>
                </div>
                @if ($tren->isEmpty())
                    <div class="ch-empty"><i class="fas fa-chart-line"></i>Belum ada data transaksi tahun ajaran ini.
                    </div>
                @else
                    <div style="height:220px;"><canvas id="c_tren_bulan"></canvas></div>
                @endif
            </div>
        </div>

        {{-- ── ROW 3: Pelanggaran & penghargaan per kelas ── --}}
        <div class="section-lbl"><i class="fas fa-school"></i> Per Kelas</div>
        <div class="ch-grid-1">
            <div class="cc">
                <div class="cc-head">
                    <div class="cc-ico" style="background:#faf5ff;color:#7c3aed;"><i
                            class="fas fa-chalkboard-teacher"></i></div>
                    <span class="cc-title">Pelanggaran & Penghargaan per Kelas</span>
                    <span class="cc-badge">{{ $perKelas->count() }} kelas</span>
                </div>
                @if ($perKelas->isEmpty())
                    <div class="ch-empty"><i class="fas fa-school"></i>Belum ada data per kelas.</div>
                @else
                    <div style="height:{{ max(200, $perKelas->count() * 30) }}px;">
                        <canvas id="c_per_kelas"></canvas>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── ROW 4: Threshold pencapaian ── --}}
        <div class="section-lbl"><i class="fas fa-flag"></i> Ambang Peringatan</div>
        <div class="ch-grid-1">
            <div class="cc">
                <div class="cc-head">
                    <div class="cc-ico" style="background:#fff7ed;color:#c2410c;"><i class="fas fa-flag-checkered"></i>
                    </div>
                    <span class="cc-title">Jumlah Siswa yang Melampaui Setiap Ambang</span>
                </div>
                <div style="height:160px;"><canvas id="c_threshold"></canvas></div>
            </div>
        </div>

        {{-- ── ROW 5: Top siswa + Top pasal ── --}}
        <div class="section-lbl"><i class="fas fa-ranking-star"></i> Leaderboard & Jenis Pelanggaran</div>
        <div class="ch-grid-3">

            {{-- Top 10 Pelanggaran --}}
            <div class="cc">
                <div class="cc-head">
                    <div class="cc-ico" style="background:#fee2e2;color:#b91c1c;"><i
                            class="fas fa-exclamation-circle"></i></div>
                    <span class="cc-title">Top 10 Pelanggaran Tertinggi</span>
                </div>
                @if ($topPelanggaran->isEmpty())
                    <div class="ch-empty"><i class="fas fa-inbox"></i>Belum ada data.</div>
                @else
                    <div class="top-table-wrap">
                        <table class="top-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Siswa</th>
                                    <th>Kelas</th>
                                    <th style="text-align:right;">Poin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topPelanggaran as $i => $row)
                                    <tr>
                                        <td><span
                                                class="rank-badge {{ $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : 'rank-n')) }}">{{ $i + 1 }}</span>
                                        </td>
                                        <td class="col-nama">{{ $row->siswa?->nama_lengkap ?? '-' }}</td>
                                        <td style="color:#64748b;font-size:.73rem;white-space:nowrap;">
                                            {{ $row->siswa?->kelas?->nama_kelas ?? '-' }}</td>
                                        <td style="text-align:right;font-weight:800;color:#b91c1c;white-space:nowrap;">
                                            {{ (int) $row->total }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Top 10 Penghargaan --}}
            <div class="cc">
                <div class="cc-head">
                    <div class="cc-ico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-award"></i></div>
                    <span class="cc-title">Top 10 Penghargaan Tertinggi</span>
                </div>
                @if ($topPenghargaan->isEmpty())
                    <div class="ch-empty"><i class="fas fa-inbox"></i>Belum ada data.</div>
                @else
                    <div class="top-table-wrap">
                        <table class="top-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Siswa</th>
                                    <th>Kelas</th>
                                    <th style="text-align:right;">Poin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topPenghargaan as $i => $row)
                                    <tr>
                                        <td><span
                                                class="rank-badge {{ $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : 'rank-n')) }}">{{ $i + 1 }}</span>
                                        </td>
                                        <td class="col-nama">{{ $row->siswa?->nama_lengkap ?? '-' }}</td>
                                        <td style="color:#64748b;font-size:.73rem;white-space:nowrap;">
                                            {{ $row->siswa?->kelas?->nama_kelas ?? '-' }}</td>
                                        <td style="text-align:right;font-weight:800;color:#15803d;white-space:nowrap;">
                                            {{ (int) $row->total }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Top Pasal/Jenis Pelanggaran -- Pie --}}
            <div class="cc">
                <div class="cc-head">
                    <div class="cc-ico" style="background:#fef3c7;color:#b45309;"><i class="fas fa-gavel"></i></div>
                    <span class="cc-title">Top Jenis Pelanggaran</span>
                    <span class="cc-badge">{{ $topPasal->count() }} jenis</span>
                </div>
                @if ($topPasal->isEmpty())
                    <div class="ch-empty"><i class="fas fa-inbox"></i>Belum ada data.</div>
                @else
                    <div style="height:200px;"><canvas id="c_top_pasal"></canvas></div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── Fixed Action Bar ── --}}
    <div class="action-bar">
        <a href="{{ route('admin.rekap-poin.index', ['tahun_ajaran' => $tahunAjaran]) }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i> Rekap Poin
        </a>
        <a href="{{ route('dashboard') }}" class="ab-btn ab-btn-back" style="flex:unset;padding:11px 16px;">
            <i class="fas fa-home"></i>
        </a>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header-auto-show');
            if (header) header.classList.add('header-active');
        });

        function toggleChartFilter() {
            const btn = document.getElementById('chartFilterToggle');
            const body = document.getElementById('chartFilterBody');
            const chevron = btn.querySelector('.ft-chevron');
            const isOpen = btn.classList.toggle('open');
            body.classList.toggle('open', isOpen);
            chevron.classList.toggle('open', isOpen);
        }
    </script>
    <script>
        (function() {
            const isMobile = window.innerWidth < 600;
            const font = {
                family: 'inherit',
                size: isMobile ? 10 : 11
            };
            const gridColor = '#f1f5f9';

            /* ── 1. Doughnut: Pelanggaran vs Penghargaan ─────────────────── */
            (function() {
                const ctx = document.getElementById('c_donut_pvsr');
                if (!ctx) return;
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pelanggaran', 'Penghargaan'],
                        datasets: [{
                            data: [{{ $totalPelanggaran }}, {{ $totalPenghargaan }}],
                            backgroundColor: ['#fca5a5', '#86efac'],
                            borderColor: ['#ef4444', '#22c55e'],
                            borderWidth: 2,
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: c => ' ' + c.label + ': ' + c.parsed
                                }
                            }
                        }
                    }
                });
            })();

            /* ── 2. Horizontal Bar: Distribusi Status ────────────────────── */
            (function() {
                const ctx = document.getElementById('c_dist_status');
                if (!ctx) return;
                const labels = @json(array_keys($dist));
                const values = @json(array_values($dist));
                const palette = {
                    'Aman': '#86efac',
                    'Pantau': '#93c5fd',
                    'Panggilan 1': '#fcd34d',
                    'Panggilan 2': '#fb923c',
                    'Panggilan 3': '#f87171',
                    'Point 0': '#dc2626',
                };
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Siswa',
                            data: values,
                            backgroundColor: labels.map(l => palette[l] ?? '#cbd5e1'),
                            borderRadius: 5,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: c => ' ' + c.parsed.x + ' siswa'
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    font
                                },
                                grid: {
                                    color: gridColor
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font
                                }
                            }
                        }
                    }
                });
            })();

            /* ── 3. Line: Tren bulanan ───────────────────────────────────── */
            (function() {
                const ctx = document.getElementById('c_tren_bulan');
                if (!ctx) return;
                @php
                    $trenLabels = $tren->map(fn($r) => \Carbon\Carbon::parse($r->bulan . '-01')->translatedFormat('M Y'))->toArray();
                    $trenPel = $tren->pluck('pel')->map(fn($v) => (int) $v)->toArray();
                    $trenPen = $tren->pluck('pen')->map(fn($v) => (int) $v)->toArray();
                @endphp
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($trenLabels),
                        datasets: [{
                                label: 'Pelanggaran',
                                data: @json($trenPel),
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239,68,68,.1)',
                                tension: .35,
                                fill: true,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                borderWidth: 2,
                            },
                            {
                                label: 'Penghargaan',
                                data: @json($trenPen),
                                borderColor: '#22c55e',
                                backgroundColor: 'rgba(34,197,94,.08)',
                                tension: .35,
                                fill: true,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                borderWidth: 2,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    font,
                                    boxWidth: 14,
                                    padding: 12
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font,
                                    maxRotation: isMobile ? 45 : 0,
                                    minRotation: isMobile ? 45 : 0
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    font
                                },
                                grid: {
                                    color: gridColor
                                }
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            axis: 'x',
                            intersect: false
                        }
                    }
                });
            })();

            /* ── 4. Stacked Bar: Per kelas ───────────────────────────────── */
            (function() {
                const ctx = document.getElementById('c_per_kelas');
                if (!ctx) return;
                @php
                    $kelasLabels = $perKelas->pluck('nmkelas')->toArray();
                    $kelasPel = $perKelas->pluck('pel')->map(fn($v) => (int) $v)->toArray();
                    $kelasPen = $perKelas->pluck('pen')->map(fn($v) => (int) $v)->toArray();
                @endphp
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($kelasLabels),
                        datasets: [{
                                label: 'Pelanggaran',
                                data: @json($kelasPel),
                                backgroundColor: 'rgba(239,68,68,.75)',
                                borderColor: '#ef4444',
                                borderWidth: 1,
                                borderRadius: 4,
                                stack: 'kelas',
                            },
                            {
                                label: 'Penghargaan',
                                data: @json($kelasPen),
                                backgroundColor: 'rgba(34,197,94,.75)',
                                borderColor: '#22c55e',
                                borderWidth: 1,
                                borderRadius: 4,
                                stack: 'kelas',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    font,
                                    boxWidth: 14,
                                    padding: 12
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font,
                                    maxRotation: isMobile ? 60 : 0,
                                    minRotation: isMobile ? 45 : 0,
                                    callback: function(val, idx) {
                                        const lbl = this.getLabelForValue(val);
                                        return isMobile && lbl && lbl.length > 8 ? lbl.substring(0, 8) +
                                            '…' : lbl;
                                    }
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    font
                                },
                                grid: {
                                    color: gridColor
                                }
                            }
                        }
                    }
                });
            })();

            /* ── 5. Bar: Threshold siswa ─────────────────────────────────── */
            (function() {
                const ctx = document.getElementById('c_threshold');
                if (!ctx) return;
                @php
                    $tLabels = collect($thresholdCounts)->pluck('label')->toArray();
                    $tValues = collect($thresholdCounts)->pluck('jumlah')->toArray();
                    $tPoin = collect($thresholdCounts)->pluck('poin')->toArray();
                @endphp
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($tLabels),
                        datasets: [{
                            label: 'Jumlah Siswa',
                            data: @json($tValues),
                            backgroundColor: ['#fcd34d', '#fb923c', '#f87171', '#dc2626'],
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    title: (items) => {
                                        const poin = @json($tPoin);
                                        return items[0].label + ' (≥ ' + poin[items[0].dataIndex] +
                                            ' poin)';
                                    },
                                    label: c => ' ' + c.parsed.y + ' siswa'
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    font
                                },
                                grid: {
                                    color: gridColor
                                }
                            }
                        }
                    }
                });
            })();

            /* ── 6. Pie: Top jenis pelanggaran ───────────────────────────── */
            (function() {
                const ctx = document.getElementById('c_top_pasal');
                if (!ctx) return;
                @php
                    $pasalLabels = $topPasal->map(fn($r) => \Str::limit($r->isi, 28, '…'))->toArray();
                    $pasalValues = $topPasal->pluck('jumlah')->map(fn($v) => (int) $v)->toArray();
                @endphp
                const palette = ['#fca5a5', '#fdba74', '#fcd34d', '#a3e635', '#6ee7b7', '#93c5fd', '#c4b5fd',
                    '#f9a8d4'
                ];
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($pasalLabels),
                        datasets: [{
                            data: @json($pasalValues),
                            backgroundColor: palette,
                            borderWidth: 2,
                            borderColor: '#fff',
                            hoverOffset: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '50%',
                        plugins: {
                            legend: {
                                display: true,
                                position: isMobile ? 'bottom' : 'right',
                                labels: {
                                    font: {
                                        size: isMobile ? 9 : 10
                                    },
                                    boxWidth: 10,
                                    padding: isMobile ? 8 : 6
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: c => ' ' + c.label + ': ' + c.parsed + 'x'
                                }
                            }
                        }
                    }
                });
            })();

        })();
    </script>
@endpush
