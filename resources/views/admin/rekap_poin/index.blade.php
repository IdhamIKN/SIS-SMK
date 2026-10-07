@extends('layouts.app')

@section('title', 'Rekap Poin Siswa')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ══════════════════════════════════════════════════════════
           REKAP POIN — Responsive (mengikuti pola /tatib/pelanggaran)
           Breakpoints: xs <480 | sm 480-767 | md 768+ | lg 1024+
           ══════════════════════════════════════════════════════════ */

        /* ── Wrapper (sama persis dengan pvl-wrap di pelanggaran) ── */
        .rp-wrap {
            padding: 0 12px;
            max-width: 1280px;
            margin: 0 auto;
            box-sizing: border-box;
        }
        @media (min-width: 768px)  { .rp-wrap { padding: 0 20px; } }
        @media (min-width: 1024px) { .rp-wrap { padding: 0 28px; } }

        /* ── Filter collapsible ── */
        .filter-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 16px;
            overflow: hidden;
        }
        .filter-toggle {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 16px; cursor: pointer; user-select: none;
            background: #f1f5f9; border: none; width: 100%;
            font-family: inherit; font-size: .875rem; font-weight: 700;
            color: #0f172a; gap: 8px;
        }
        .filter-toggle .ft-left { display: flex; align-items: center; gap: 8px; }
        .filter-toggle .ft-chevron { transition: transform .2s; color: #64748b; font-size: .8rem; }
        .filter-toggle.open .ft-chevron { transform: rotate(180deg); }
        @media (min-width: 768px) { .filter-toggle { display: none; } }
        .filter-body { padding: 12px 16px 16px; display: none; }
        .filter-body.open { display: block; }
        @media (min-width: 768px) { .filter-body { display: block !important; padding: 16px; } }

        /* ── Filter grid: 1-col xs, 2-col sm, auto md+ ── */
        .filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            align-items: end;
        }
        @media (min-width: 480px) { .filter-grid { grid-template-columns: 1fr 1fr; } }
        @media (min-width: 768px) { .filter-grid { grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; } }

        .form-label {
            display: block; font-size: .8rem; font-weight: 600;
            color: #0f172a; margin-bottom: 5px;
        }
        .form-input {
            width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0;
            border-radius: 8px; font-size: .875rem; font-family: inherit;
            color: #0f172a; background: #fff; box-sizing: border-box;
            -webkit-appearance: none; appearance: none;
        }
        .form-input:focus { outline: 2px solid #f59e0b; outline-offset: -1px; }
        .form-input.has-value { border-color: #fde68a; background: #fffbeb; color: #92400e; font-weight: 700; }

        /* ── Shortcut chart link ── */
        .chart-shortcut {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            font-size: .82rem;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(245,158,11,.3);
            transition: opacity .18s;
            margin-bottom: 14px;
            width: 100%;
            box-sizing: border-box;
        }
        .chart-shortcut:hover { opacity: .88; }
        .chart-shortcut .cs-arrow { font-size: .7rem; opacity: .7; margin-left: auto; }
        @media (min-width: 480px) {
            .chart-shortcut { width: auto; display: inline-flex; }
        }

        /* ── Action bar (fixed bottom) ── */
        .action-bar {
            position: fixed;
            bottom: 0;
            left: 0; right: 0;
            padding: 10px 12px;
            padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px));
            background: rgba(255,255,255,.96);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 8px;
            z-index: 999;
            box-shadow: 0 -4px 20px rgba(0,0,0,.06);
        }
        @media (min-width: 768px) {
            .action-bar { padding: 10px 24px 12px; gap: 12px; justify-content: flex-end; }
        }
        .ab-btn {
            flex: 1;
            display: inline-flex; align-items: center; justify-content: center;
            gap: 7px; padding: 11px 14px; border-radius: 12px; font-size: .82rem;
            font-weight: 700; border: none; cursor: pointer; text-decoration: none;
            font-family: inherit; transition: all .18s; line-height: 1; white-space: nowrap;
        }
        @media (min-width: 480px) { .ab-btn { font-size: .875rem; } }
        @media (min-width: 768px) { .ab-btn { flex: unset; min-width: 130px; padding: 11px 20px; } }
        .ab-btn:active { transform: scale(.97); }
        .ab-btn-back { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .ab-btn-back:hover { background: #e2e8f0; }
        .ab-btn-primary { background: #f59e0b; color: #fff; box-shadow: 0 3px 12px rgba(245,158,11,.3); }

        /* ── Pagination ── */
        .rekap-pagination {
            padding: 12px 14px; border-top: 1px solid #f1f5f9;
            display: flex; justify-content: center; flex-wrap: wrap; gap: 5px;
        }
        .pg-btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 34px; height: 34px;
            gap: 5px; padding: 0 10px; border-radius: 8px; font-size: .78rem;
            font-weight: 700; text-decoration: none; font-family: inherit;
            border: 1.5px solid #e2e8f0; background: #fff; color: #64748b;
            cursor: pointer; transition: background .15s; white-space: nowrap;
            box-sizing: border-box;
        }
        .pg-btn:hover:not(.active):not(.disabled) { background: #fef3c7; border-color: #fde68a; color: #b45309; }
        .pg-btn.active { background: #f59e0b; color: #fff; border-color: transparent; pointer-events: none; font-weight: 700; }
        .pg-btn.disabled { opacity: .4; cursor: not-allowed; pointer-events: none; }
        @media (max-width: 479px) { .pg-num { display: none; } .pg-num.active { display: inline-flex; } }

        /* ── Stat grid — 2-col xs, 3-col sm+ ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }
        @media (min-width: 480px) { .stat-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (min-width: 768px) { .stat-grid { gap: 12px; margin-bottom: 20px; } }
        .stat-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 10px 8px; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        /* Pada xs (2 kolom), kartu ke-3 bentang penuh agar tidak tersisa sendiri */
        @media (max-width: 479px) { .stat-card:last-child:nth-child(odd) { grid-column: span 2; } }
        @media (min-width: 768px) { .stat-card { border-radius: 12px; padding: 16px; } }
        .stat-value { font-size: 1.4rem; font-weight: 800; line-height: 1; margin-bottom: 4px; }
        @media (min-width: 768px) { .stat-value { font-size: 1.8rem; margin-bottom: 6px; } }
        .stat-label { font-size: .6rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        @media (min-width: 768px) { .stat-label { font-size: .7rem; } }

        /* ── Kelas section ── */
        .kelas-section { margin-bottom: 16px; }
        .kelas-header {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; border-bottom: 1px solid #f1f5f9;
        }
        .kelas-icon {
            width: 32px; height: 32px; border-radius: 8px; background: #fef3c7;
            color: #b45309; display: flex; align-items: center; justify-content: center;
            font-size: .85rem; flex-shrink: 0;
        }
        .kelas-title { font-size: .88rem; font-weight: 800; color: #0f172a; flex: 1; margin: 0; }
        .kelas-count {
            font-size: .7rem; color: #475569; background: #f1f5f9;
            padding: 3px 10px; border-radius: 20px; font-weight: 600;
        }

        /* ── Poin table (desktop ≥768px) ── */
        .poin-table-wrap {
            display: none;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        @media (min-width: 768px) { .poin-table-wrap { display: block; } }
        .poin-table { width: 100%; border-collapse: collapse; font-size: .78rem; background: #fff; }
        .poin-table thead tr { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
        .poin-table th {
            padding: 10px 10px; text-align: left; font-size: .68rem; font-weight: 700;
            color: #64748b; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap;
        }
        .poin-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .poin-table tbody tr:last-child td { border-bottom: none; }
        .poin-table tbody tr:hover td { background: #fafbfc; }
        .col-no { width: 36px; color: #94a3b8; font-size: .72rem; text-align: center; }
        .col-act { width: 60px; text-align: right; }

        /* ── Badges / tags ── */
        .risk-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 8px; border-radius: 20px; font-size: .65rem; font-weight: 700;
        }
        .badge-risk-high { background: #fee2e2; color: #b91c1c; }
        .badge-risk-medium { background: #fffbeb; color: #b45309; }
        .badge-risk-low { background: #dbeafe; color: #1d4ed8; }
        .badge-clean { background: #dcfce7; color: #15803d; }
        .poin-tag {
            display: inline-flex; align-items: center; gap: 3px;
            padding: 2px 8px; border-radius: 20px; font-size: .68rem; font-weight: 700;
        }
        .tag-p { background: #fee2e2; color: #b91c1c; }
        .tag-r { background: #dcfce7; color: #15803d; }
        .tag-sisa { background: #dbeafe; color: #1d4ed8; }
        .tag-zero { color: #cbd5e1; font-size: .72rem; }
        .poin-stack { display: flex; flex-direction: column; gap: 3px; }

        /* ── Detail btn ── */
        .btn-detail {
            display: inline-flex; align-items: center; gap: 5px; padding: 6px 10px;
            border-radius: 8px; font-size: .72rem; font-weight: 700; background: #eff6ff;
            color: #1d4ed8; border: 1px solid #bfdbfe; text-decoration: none; white-space: nowrap;
        }
        .btn-detail:hover { background: #dbeafe; }

        /* ── Card mobile list (<768px) ── */
        .poin-card-list { display: flex; flex-direction: column; gap: 0; }
        @media (min-width: 768px) { .poin-card-list { display: none; } }

        .pci {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            border-left: 4px solid #f59e0b;
            background: #fff;
            transition: background .15s;
        }
        .pci:last-child { border-bottom: none; }
        .pci:active { background: #fffbeb; }
        .pci-top {
            display: flex; align-items: flex-start;
            justify-content: space-between; gap: 8px; margin-bottom: 5px;
        }
        .pci-nama { font-weight: 800; font-size: .88rem; color: #0f172a; flex: 1; line-height: 1.3; }
        .pci-nis { font-size: .72rem; color: #64748b; margin-bottom: 6px; }
        .pci-mid { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 7px; }
        .pci-bot { display: flex; align-items: center; justify-content: space-between; gap: 8px; }

        /* ── Empty state ── */
        .rekap-empty { text-align: center; padding: 36px 20px; color: #64748b; }
        .rekap-empty i { font-size: 2.5rem; opacity: .3; display: block; margin-bottom: 10px; }
        .rekap-empty strong { display: block; color: #0f172a; margin-bottom: 4px; font-size: .9rem; }

        /* ── Filter active badge ── */
        .filter-active-badge {
            display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px;
            border-radius: 20px; background: #fef3c7; color: #92400e; font-size: .7rem;
            font-weight: 700; border: 1px solid #fde68a; margin-bottom: 12px;
        }
    </style>
@endpush

@section('content')
    @php $currentStatus = request('status'); $hasFilter = request()->hasAny(['search','kelas_id','status','tahun_ajaran']); @endphp

    <div class="event-wrap rp-wrap" style="padding-top:var(--header-h,56px); padding-bottom:calc(var(--footer-h,0px) + 80px);">

        {{-- ── Page Strip ── --}}
        <div class="page-strip page-strip-event" style="--event-primary:#f59e0b;">
            <div class="live-badge"><span class="live-dot" style="background:#f59e0b;"></span>Tatib</div>
            <h2><i class="fas fa-chart-line"></i> Rekap Poin Siswa</h2>
            <p>Tahun Ajaran {{ $tahunAjaran }}</p>
        </div>

        {{-- ── Stats ── --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value" style="color:#0ea5e9;">{{ $stats['total_siswa'] }}</div>
                <div class="stat-label">Siswa</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#b91c1c;">{{ $stats['total_pelanggaran'] }}</div>
                <div class="stat-label">Pelanggaran</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#15803d;">{{ $stats['total_penghargaan'] }}</div>
                <div class="stat-label">Penghargaan</div>
            </div>
        </div>

        {{-- ── Shortcut Chart ── --}}
        @hasanyrole('superadmin|admin_tatib|bk|waka|kepala_sekolah')
            <div style="margin-bottom:14px;">
                <a href="{{ route('admin.tatib.chart', ['tahun_ajaran' => $tahunAjaran]) }}" class="chart-shortcut">
                    <i class="fas fa-chart-bar"></i>
                    <span>Dashboard Grafik &amp; Analitik Tata Tertib</span>
                    <i class="fas fa-arrow-right cs-arrow"></i>
                </a>
            </div>
        @endhasanyrole

        {{-- ── Filter (collapsible on mobile) ── --}}
        <form method="GET" action="{{ route('admin.rekap-poin.index') }}" id="filterForm" class="filter-section">
            <button type="button" class="filter-toggle {{ $hasFilter ? 'open' : '' }}"
                id="rekapPoinFilterToggle" onclick="toggleRekapPoinFilter()">
                <span class="ft-left">
                    <i class="fas fa-filter"></i> Filter
                    @if ($hasFilter)
                        <span style="background:#fef3c7;color:#b45309;font-size:.65rem;padding:2px 8px;border-radius:20px;font-weight:700;">Aktif</span>
                    @endif
                </span>
                <i class="fas fa-chevron-down ft-chevron {{ $hasFilter ? 'open' : '' }}"></i>
            </button>
            <div class="filter-body {{ $hasFilter ? 'open' : '' }}" id="rekapPoinFilterBody">
                <div class="filter-grid">
                    <div>
                        <label class="form-label">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="form-input" value="{{ $tahunAjaran }}" maxlength="9" placeholder="cth. 2025/2026">
                    </div>
                    <div>
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" class="form-input {{ request('kelas_id') ? 'has-value' : '' }}">
                            <option value="">Semua Kelas</option>
                            @foreach ($kelas as $item)
                                <option value="{{ $item->id }}" @selected((string) request('kelas_id') === (string) $item->id)>
                                    {{ $item->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Status Poin</label>
                        <select name="status" class="form-input {{ $currentStatus ? 'has-value' : '' }}">
                            <option value="">Semua Status</option>
                            <option value="aman" @selected($currentStatus === 'aman')>Aman ({{ $countAman }})</option>
                            <option value="pantau" @selected($currentStatus === 'pantau')>Pantau ({{ $countPantau }})</option>
                            @foreach ($distStatus as $dst)
                                <option value="{{ $dst['key'] }}" @selected($currentStatus === $dst['key'])>
                                    {{ $dst['label'] }} &ge;{{ $dst['poin'] }}p ({{ $dst['count'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Cari Siswa</label>
                        <input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Nama, NIS, atau NISN">
                    </div>
                    <div style="display:flex;gap:8px;align-items:flex-end;padding-top:4px;">
                        <button type="submit" class="ab-btn ab-btn-primary" style="flex:1;padding:10px;font-size:.82rem;">
                            <i class="fas fa-filter"></i> Terapkan
                        </button>
                        @if ($hasFilter)
                            <a href="{{ route('admin.rekap-poin.index') }}"
                                class="ab-btn ab-btn-back" style="flex:unset;padding:10px 14px;">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- ── Filter active indicator ── --}}
        @if ($isFiltered)
            <div>
                <span class="filter-active-badge">
                    <i class="fas fa-filter"></i> Filter aktif
                    @if ($statusLabel) &mdash; {{ $statusLabel }} @endif
                    @if (request('kelas_id')) &mdash; {{ $kelas->find(request('kelas_id'))?->nama_kelas }} @endif
                    @if (request('search')) &mdash; "{{ request('search') }}" @endif
                </span>
            </div>
        @endif

        {{-- ── Top Ranking (hanya tampil kalau tidak filter) ── --}}
        {{-- @if (!$isFiltered)
        <div class="top-section">
            <div class="top-card">
                <div class="top-card-header">
                    <div class="th-icon th-icon-red"><i class="fas fa-exclamation-circle"></i></div>
                    <h4>Top Pelanggaran</h4>
                </div>
                <ul class="top-list">
                    @forelse ($topPelanggaran as $i => $tp)
                        <li>
                            <span class="top-rank {{ $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : 'rank-n')) }}">{{ $i + 1 }}</span>
                            <span class="top-nama">{{ \Str::limit($tp->siswa?->nama_lengkap ?? '-', 18, '…') }}</span>
                            <span class="top-kelas">{{ $tp->siswa?->kelas?->nama_kelas ?? '-' }}</span>
                            <span class="top-poin-badge top-poin-red">{{ (int)$tp->total }}p</span>
                        </li>
                    @empty
                        <li><div class="top-empty">Belum ada data</div></li>
                    @endforelse
                </ul>
            </div>
            <div class="top-card">
                <div class="top-card-header">
                    <div class="th-icon th-icon-grn"><i class="fas fa-award"></i></div>
                    <h4>Top Penghargaan</h4>
                </div>
                <ul class="top-list">
                    @forelse ($topPenghargaan as $i => $tp)
                        <li>
                            <span class="top-rank {{ $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : 'rank-n')) }}">{{ $i + 1 }}</span>
                            <span class="top-nama">{{ \Str::limit($tp->siswa?->nama_lengkap ?? '-', 18, '…') }}</span>
                            <span class="top-kelas">{{ $tp->siswa?->kelas?->nama_kelas ?? '-' }}</span>
                            <span class="top-poin-badge top-poin-grn">{{ (int)$tp->total }}p</span>
                        </li>
                    @empty
                        <li><div class="top-empty">Belum ada data</div></li>
                    @endforelse
                </ul>
            </div>
        </div>
        @endif --}}

        {{-- ── Tables grouped by Kelas ── --}}
        @forelse ($kelasPaginated as $kelasItem)
            @php
                $siswasKelas = $siswasPerKelas->get($kelasItem->id, collect());
                $jumlahSiswa = $siswasKelas->count();
            @endphp
            @if (!$isFiltered || $jumlahSiswa > 0)
                <div class="card kelas-section">
                    <div class="c-head kelas-header">
                        <div class="c-icon kelas-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <h3 class="kelas-title">{{ $kelasItem->nama_kelas }}</h3>
                        <span class="hbadge kelas-count">{{ $jumlahSiswa }} siswa</span>
                    </div>

                    {{-- Desktop table --}}
                    <div class="poin-table-wrap">
                        <table class="poin-table">
                            <thead>
                                <tr>
                                    <th class="col-no">#</th>
                                    <th>Nama Siswa</th>
                                    <th>Pel. / Pgh.</th>
                                    <th>Total Poin</th>
                                    <th>Status</th>
                                    <th class="col-act"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($siswasKelas as $i => $siswa)
                                    @php
                                        $agg = $aggregates->get($siswa->id);
                                        $pelanggaran = (int)($agg?->total_pelanggaran ?? 0);
                                        $penghargaan = (int)($agg?->total_penghargaan ?? 0);
                                        $sisaPoin = max(0, $poinAwal + $penghargaan - $pelanggaran);
                                        $sortedT = collect($thresholds)->sortByDesc('poin');
                                        $rb = 'badge-clean'; $rl = 'Aman'; $ri = 'fa-circle-check';
                                        if ($pelanggaran > 0) { $rb = 'badge-risk-low'; $rl = 'Pantau'; $ri = 'fa-circle-info'; }
                                        foreach ($sortedT as $t) {
                                            if ($pelanggaran >= (int)$t['poin']) {
                                                $lbl = $t['tindakan'] ?? 'Panggilan '.$t['batas_ke'];
                                                $batasKe = (int)($t['batas_ke'] ?? 1);
                                                $rb = $batasKe >= 3 ? 'badge-risk-high' : 'badge-risk-medium';
                                                $ri = $batasKe >= 3 ? 'fa-circle-exclamation' : 'fa-triangle-exclamation';
                                                $rl = $lbl; break;
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td class="col-no">{{ $i + 1 }}</td>
                                        <td style="font-weight:600;color:#0f172a;">{{ \Str::limit($siswa->nama_lengkap, 24, '…') }}</td>
                                        <td>
                                            <div class="poin-stack">
                                                @if ($pelanggaran > 0)
                                                    <span class="poin-tag tag-p"><i class="fas fa-exclamation-circle"></i> {{ $pelanggaran }}</span>
                                                @else
                                                    <span class="tag-zero">— pel</span>
                                                @endif
                                                @if ($penghargaan > 0)
                                                    <span class="poin-tag tag-r"><i class="fas fa-award"></i> {{ $penghargaan }}</span>
                                                @else
                                                    <span class="tag-zero">— pgh</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td><span class="poin-tag tag-sisa"><i class="fas fa-battery-half"></i> {{ $sisaPoin }}</span></td>
                                        <td><span class="risk-badge {{ $rb }}"><i class="fas {{ $ri }}"></i> {{ $rl }}</span></td>
                                        <td class="col-act">
                                            <a href="{{ route('admin.rekap-poin.show', ['siswa' => $siswa, 'tahun_ajaran' => $tahunAjaran]) }}" class="btn-detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" style="text-align:center;padding:18px;color:#94a3b8;font-size:.8rem;">
                                        <i class="fas fa-users-slash" style="display:block;font-size:1.3rem;margin-bottom:6px;"></i>
                                        Belum ada siswa di kelas ini
                                    </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile card list --}}
                    <div class="poin-card-list">
                        @forelse ($siswasKelas as $i => $siswa)
                            @php
                                $agg = $aggregates->get($siswa->id);
                                $pelanggaran = (int)($agg?->total_pelanggaran ?? 0);
                                $penghargaan = (int)($agg?->total_penghargaan ?? 0);
                                $sisaPoin = max(0, $poinAwal + $penghargaan - $pelanggaran);
                                $sortedT = collect($thresholds)->sortByDesc('poin');
                                $rb = 'badge-clean'; $rl = 'Aman'; $ri = 'fa-circle-check';
                                if ($pelanggaran > 0) { $rb = 'badge-risk-low'; $rl = 'Pantau'; $ri = 'fa-circle-info'; }
                                foreach ($sortedT as $t) {
                                    if ($pelanggaran >= (int)$t['poin']) {
                                        $lbl = $t['tindakan'] ?? 'Panggilan '.$t['batas_ke'];
                                        $batasKe = (int)($t['batas_ke'] ?? 1);
                                        $rb = $batasKe >= 3 ? 'badge-risk-high' : 'badge-risk-medium';
                                        $ri = $batasKe >= 3 ? 'fa-circle-exclamation' : 'fa-triangle-exclamation';
                                        $rl = $lbl; break;
                                    }
                                }
                                $borderColor = match($rb) {
                                    'badge-risk-high'   => '#dc2626',
                                    'badge-risk-medium' => '#f59e0b',
                                    'badge-risk-low'    => '#3b82f6',
                                    default             => '#22c55e',
                                };
                            @endphp
                            <div class="pci" style="border-left-color:{{ $borderColor }};">
                                <div class="pci-top">
                                    <div style="flex:1;min-width:0;">
                                        <div class="pci-nama">{{ $siswa->nama_lengkap }}</div>
                                        <div class="pci-nis">
                                            <i class="fas fa-id-card"></i> {{ $siswa->nis ?? $siswa->noreg ?? '-' }}
                                        </div>
                                    </div>
                                    <a href="{{ route('admin.rekap-poin.show', ['siswa' => $siswa, 'tahun_ajaran' => $tahunAjaran]) }}" class="btn-detail">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </div>
                                <div class="pci-mid">
                                    @if ($pelanggaran > 0)
                                        <span class="poin-tag tag-p"><i class="fas fa-exclamation-circle"></i> {{ $pelanggaran }} pel</span>
                                    @else
                                        <span class="poin-tag" style="background:#f8fafc;color:#94a3b8;"><i class="fas fa-exclamation-circle"></i> 0 pel</span>
                                    @endif
                                    @if ($penghargaan > 0)
                                        <span class="poin-tag tag-r"><i class="fas fa-award"></i> {{ $penghargaan }} pgh</span>
                                    @endif
                                    <span class="poin-tag tag-sisa"><i class="fas fa-battery-half"></i> {{ $sisaPoin }} sisa</span>
                                </div>
                                <div class="pci-bot">
                                    <span class="risk-badge {{ $rb }}"><i class="fas {{ $ri }}"></i> {{ $rl }}</span>
                                </div>
                            </div>
                        @empty
                            <div style="text-align:center;padding:18px;color:#94a3b8;font-size:.8rem;">
                                <i class="fas fa-users-slash" style="display:block;font-size:1.3rem;margin-bottom:6px;"></i>
                                Belum ada siswa di kelas ini
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        @empty
            <div class="card">
                <div class="rekap-empty">
                    <i class="fas fa-chart-bar"></i>
                    <strong>{{ $isFiltered ? 'Siswa tidak ditemukan' : 'Belum ada data kelas' }}</strong>
                    @if ($isFiltered)
                        <span style="font-size:.82rem;">Tidak ada siswa yang cocok dengan filter yang dipilih.</span>
                        <a href="{{ route('admin.rekap-poin.index') }}"
                            style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:8px 16px;
                                   border-radius:10px;background:#fef3c7;color:#92400e;font-size:.8rem;font-weight:700;
                                   border:1px solid #fde68a;text-decoration:none;">
                            <i class="fas fa-times"></i> Hapus filter
                        </a>
                    @else
                        <span style="font-size:.82rem;">Tidak ada kelas yang terdaftar di sistem.</span>
                    @endif
                </div>
            </div>
        @endforelse

        {{-- ── Pagination ── --}}
        @if (!$isFiltered && $kelasPaginated->hasPages())
            <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:0;">
                <div class="rekap-pagination">
                    {{-- Prev --}}
                    @if ($kelasPaginated->onFirstPage())
                        <span class="pg-btn disabled"><i class="fas fa-angle-left"></i></span>
                    @else
                        <a href="{{ $kelasPaginated->previousPageUrl() }}" class="pg-btn"><i class="fas fa-angle-left"></i></a>
                    @endif

                    @php
                        $pgStart = max(1, $kelasPaginated->currentPage() - 2);
                        $pgEnd   = min($kelasPaginated->lastPage(), $kelasPaginated->currentPage() + 2);
                    @endphp

                    @if ($pgStart > 1)
                        <a href="{{ $kelasPaginated->url(1) }}" class="pg-btn pg-num">1</a>
                        @if ($pgStart > 2) <span class="pg-btn pg-num" style="pointer-events:none;">…</span> @endif
                    @endif

                    @for ($pg = $pgStart; $pg <= $pgEnd; $pg++)
                        @if ($kelasPaginated->currentPage() === $pg)
                            <span class="pg-btn pg-num active">{{ $pg }}</span>
                        @else
                            <a href="{{ $kelasPaginated->url($pg) }}" class="pg-btn pg-num">{{ $pg }}</a>
                        @endif
                    @endfor

                    @if ($pgEnd < $kelasPaginated->lastPage())
                        @if ($pgEnd < $kelasPaginated->lastPage() - 1)
                            <span class="pg-btn pg-num" style="pointer-events:none;">…</span>
                        @endif
                        <a href="{{ $kelasPaginated->url($kelasPaginated->lastPage()) }}" class="pg-btn pg-num">{{ $kelasPaginated->lastPage() }}</a>
                    @endif

                    {{-- Next --}}
                    @if ($kelasPaginated->hasMorePages())
                        <a href="{{ $kelasPaginated->nextPageUrl() }}" class="pg-btn"><i class="fas fa-angle-right"></i></a>
                    @else
                        <span class="pg-btn disabled"><i class="fas fa-angle-right"></i></span>
                    @endif
                </div>
                <div style="text-align:center;font-size:.72rem;color:#94a3b8;padding:4px 0 10px;">
                    Halaman {{ $kelasPaginated->currentPage() }} / {{ $kelasPaginated->lastPage() }}
                </div>
            </div>
        @endif

    </div>

    {{-- ── Fixed Action Bar ── --}}
    <div class="action-bar">
        <a href="{{ route('dashboard') }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
        <a href="{{ route('admin.tatib.jurnal.index', ['tahun_ajaran' => $tahunAjaran]) }}"
            class="ab-btn ab-btn-back" style="color:#92400e;border-color:#fde68a;background:#fef3c7;" title="Jurnal Tatib">
            <i class="fas fa-print"></i> Cetak
        </a>
        @hasanyrole('superadmin|admin_tatib|bk|waka|kepala_sekolah')
            <a href="{{ route('admin.tatib.chart', ['tahun_ajaran' => $tahunAjaran]) }}" class="ab-btn ab-btn-primary">
                <i class="fas fa-chart-bar"></i> Grafik
            </a>
        @endhasanyrole
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const header = document.querySelector('.header-auto-show');
            if (header) header.classList.add('header-active');
        });

        function toggleRekapPoinFilter() {
            const btn = document.getElementById('rekapPoinFilterToggle');
            const body = document.getElementById('rekapPoinFilterBody');
            const chevron = btn.querySelector('.ft-chevron');
            const isOpen = btn.classList.toggle('open');
            body.classList.toggle('open', isOpen);
            chevron.classList.toggle('open', isOpen);
        }
    </script>
@endpush
