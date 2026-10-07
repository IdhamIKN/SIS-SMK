@extends('layouts.app')

@section('title', 'Detail Rekap Poin')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ══════════════════════════════════════════════════════════
           REKAP POIN SHOW — Responsive (mengikuti pola /tatib/pelanggaran)
           Breakpoints: xs <480 | sm 480-767 | md 768+ | lg 1024+
           ══════════════════════════════════════════════════════════ */

        /* ── Wrapper ── */
        .rp-wrap {
            padding: 0 12px;
            max-width: 1280px;
            margin: 0 auto;
            box-sizing: border-box;
        }
        @media (min-width: 768px)  { .rp-wrap { padding: 0 20px; } }
        @media (min-width: 1024px) { .rp-wrap { padding: 0 28px; } }

        /* ── Stat grid — 2-col xs, 4-col sm+ ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }
        @media (min-width: 480px) { .stat-grid { grid-template-columns: repeat(4, 1fr); } }
        @media (min-width: 768px) { .stat-grid { gap: 12px; margin-bottom: 20px; } }
        .stat-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 10px 8px; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        @media (min-width: 768px) { .stat-card { border-radius: 12px; padding: 16px; } }
        .stat-value { font-size: 1.4rem; font-weight: 800; line-height: 1; margin-bottom: 4px; }
        @media (min-width: 768px) { .stat-value { font-size: 1.8rem; margin-bottom: 6px; } }
        .stat-label { font-size: .6rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        @media (min-width: 768px) { .stat-label { font-size: .7rem; } }

        /* ── Action bar (fixed bottom) ── */
        .action-bar {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            padding: 10px 12px;
            padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px));
            background: rgba(255,255,255,.96); backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px); border-top: 1px solid #e2e8f0;
            display: flex; gap: 8px; z-index: 999;
            box-shadow: 0 -4px 20px rgba(0,0,0,.06);
        }
        @media (min-width: 768px) { .action-bar { padding: 10px 24px 12px; gap: 12px; justify-content: flex-end; } }
        .ab-btn {
            flex: 1; display: inline-flex; align-items: center; justify-content: center;
            gap: 7px; padding: 11px 14px; border-radius: 12px; font-size: .82rem;
            font-weight: 700; border: none; cursor: pointer; text-decoration: none;
            font-family: inherit; transition: all .18s; line-height: 1; white-space: nowrap;
        }
        @media (min-width: 480px) { .ab-btn { font-size: .875rem; } }
        @media (min-width: 768px) { .ab-btn { flex: unset; min-width: 130px; padding: 11px 20px; } }
        .ab-btn:active { transform: scale(.97); }
        .ab-btn-back   { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .ab-btn-back:hover { background: #e2e8f0; }
        .ab-btn-primary { background: #f59e0b; color: #fff; box-shadow: 0 3px 12px rgba(245,158,11,.3); }
        .ab-btn-danger  { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }

        /* ── Tags / badges ── */
        .tag {
            padding: 2px 9px; border-radius: 20px; font-weight: 700; font-size: .7rem;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .tag-pelanggaran { background: #fee2e2; color: #b91c1c; }
        .tag-penghargaan { background: #dcfce7; color: #15803d; }
        .tag-blue        { background: #dbeafe; color: #1d4ed8; }
        .tag-amber       { background: #fffbeb; color: #b45309; }
        .tag-muted       { background: #f1f5f9; color: #64748b; }
        .tag-red-outline { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        /* ── Chart grid — 1-col xs, 2-col md+ ── */
        .chart-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }
        @media (min-width: 640px) { .chart-grid { grid-template-columns: 1fr 2fr; } }

        /* ── Doughnut card inner layout ── */
        .doughnut-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 16px 16px;
        }
        .doughnut-canvas-wrap {
            position: relative;
            width: 160px; height: 160px;
        }
        @media (min-width: 640px) {
            .doughnut-canvas-wrap { width: 180px; height: 180px; }
        }
        .chart-center-label {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            text-align: center; pointer-events: none;
        }
        .chart-center-label .cl-val { font-size: 1.3rem; font-weight: 800; line-height: 1; color: #0f172a; }
        .chart-center-label .cl-lbl { font-size: .62rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: .05em; margin-top: 2px; }
        .doughnut-legend {
            display: flex; gap: 10px; flex-wrap: wrap;
            justify-content: center; font-size: .73rem; margin-top: 10px;
        }

        /* ── Chart tren height ── */
        .chart-wrap-tall { position: relative; height: 180px; }
        @media (min-width: 640px) { .chart-wrap-tall { height: 200px; } }

        /* ── Threshold items ── */
        .threshold-item {
            display: flex; gap: 10px; align-items: flex-start;
            padding: 10px 0; border-bottom: 1px solid #f1f5f9;
        }
        .threshold-item:last-child { border-bottom: none; padding-bottom: 0; }
        .threshold-poin { flex-shrink: 0; padding-top: 1px; }
        .threshold-body { flex: 1; min-width: 0; }
        .threshold-actions-text { font-size: .82rem; color: #0f172a; margin-bottom: 5px; line-height: 1.45; }
        .threshold-footer { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .threshold-date { font-size: .7rem; color: #64748b; }

        /* ── Histori table (desktop ≥640px) + card list (mobile) ── */
        .rekap-table-wrap { display: none; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        @media (min-width: 640px) { .rekap-table-wrap { display: block; } }
        .rekap-table { width: 100%; border-collapse: collapse; font-size: .78rem; background: #fff; }
        .rekap-table thead tr { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
        .rekap-table th {
            padding: 10px; text-align: left; font-size: .68rem; font-weight: 700;
            color: #64748b; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap;
        }
        .rekap-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; vertical-align: top; color: #0f172a; line-height: 1.45; }
        .rekap-table tbody tr:last-child td { border-bottom: none; }
        .rekap-table tbody tr:hover td { background: #fafbfc; }
        .td-date { white-space: nowrap; font-size: .75rem; color: #64748b; }
        .td-poin { font-weight: 700; text-align: right; white-space: nowrap; }
        .td-uraian strong { display: block; font-size: .75rem; color: #64748b; margin-bottom: 2px; }
        .td-pemberi { font-size: .75rem; color: #64748b; }

        /* ── Mobile card list for histori (<640px) ── */
        .rekap-card-list { display: flex; flex-direction: column; gap: 0; }
        @media (min-width: 640px) { .rekap-card-list { display: none; } }
        .rci {
            padding: 12px 14px; border-bottom: 1px solid #f1f5f9;
            border-left: 4px solid #e2e8f0; background: #fff;
        }
        .rci.rci-pel { border-left-color: #ef4444; }
        .rci.rci-pen { border-left-color: #22c55e; }
        .rci:last-child { border-bottom: none; }
        .rci-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 4px; }
        .rci-uraian { font-size: .85rem; color: #0f172a; flex: 1; line-height: 1.4; }
        .rci-poin { font-size: .88rem; font-weight: 800; white-space: nowrap; flex-shrink: 0; }
        .rci-meta { font-size: .72rem; color: #64748b; display: flex; flex-wrap: wrap; gap: 4px 10px; margin-top: 4px; }

        /* ── Empty inline ── */
        .empty-inline { padding: 24px 0; text-align: center; color: #64748b; font-size: .84rem; }
        .empty-inline i { display: block; font-size: 1.4rem; opacity: .3; margin-bottom: 6px; }

        /* ── Pagination ── */
        .rp-pagination {
            padding: 12px 14px; border-top: 1px solid #f1f5f9;
            display: flex; justify-content: center; flex-wrap: wrap; gap: 5px;
        }
        .pg-btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 34px; height: 34px; padding: 0 10px; border-radius: 8px;
            font-size: .8rem; font-weight: 600; text-decoration: none; font-family: inherit;
            border: 1.5px solid #e2e8f0; background: #fff; color: #64748b;
            cursor: pointer; transition: background .15s; white-space: nowrap; box-sizing: border-box;
        }
        .pg-btn:hover:not(.active):not(.disabled) { background: #fef3c7; border-color: #fde68a; color: #b45309; }
        .pg-btn.active { background: #f59e0b; color: #fff; border-color: transparent; pointer-events: none; font-weight: 700; }
        .pg-btn.disabled { opacity: .4; cursor: not-allowed; pointer-events: none; }
        @media (max-width: 479px) { .pg-num { display: none; } .pg-num.active { display: inline-flex; } }
    </style>
@endpush

@section('content')
    <div class="event-wrap rp-wrap" style="padding-top:var(--header-h,56px); padding-bottom:calc(var(--footer-h,0px) + 80px);">

        {{-- ── Page Strip ── --}}
        <div class="page-strip page-strip-event" style="--event-primary:#f59e0b;">
            <div class="live-badge"><span class="live-dot" style="background:#f59e0b;"></span>{{ $tahunAjaran }}</div>
            <h2><i class="fas fa-user-graduate"></i> {{ $siswa->nama_lengkap }}</h2>
            <p>{{ $siswa->nis ?? '-' }} &middot; {{ $siswa->kelas?->nama_kelas ?? '-' }}</p>
        </div>

        {{-- ── Stats ── --}}
        @php
            $sisaColor = $sisaPoin <= 25 ? '#b91c1c' : ($sisaPoin < $poinAwal ? '#b45309' : '#15803d');
            $sisaBg    = $sisaPoin <= 25 ? '#fee2e2' : ($sisaPoin < $poinAwal ? '#fffbeb' : '#dcfce7');
        @endphp
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value" style="color:#b91c1c;">{{ $totalPelanggaran }}</div>
                <div class="stat-label">Pelanggaran</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#15803d;">{{ $totalPenghargaan }}</div>
                <div class="stat-label">Penghargaan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#1d4ed8;">{{ $poinAwal }}</div>
                <div class="stat-label">Poin Awal</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:{{ $sisaColor }};">{{ $sisaPoin }}</div>
                <div class="stat-label">Sisa Poin</div>
            </div>
        </div>

        {{-- ── Charts ── --}}
        @php
            $trenBulan  = $histori->groupBy(fn($t) => $t->tanggal?->format('Y-m'));
            $trenLabels = $trenBulan->keys()
                ->map(fn($ym) => \Carbon\Carbon::parse($ym . '-01')->translatedFormat('M Y'))
                ->values()->toArray();
            $trenPel = $trenBulan->map(fn($g) => (int) $g->sum('poinp'))->values()->toArray();
            $trenPen = $trenBulan->map(fn($g) => (int) $g->sum('poinr'))->values()->toArray();
            $donutTotal  = max(0, $poinAwal + $totalPenghargaan - $totalPelanggaran);
        @endphp

        <div class="chart-grid">
            {{-- Doughnut --}}
            <div class="card" style="margin-bottom:0;">
                <div class="c-head">
                    <div class="c-icon" style="background:#eff6ff;"><i class="fas fa-circle-half-stroke" style="color:#1d4ed8;"></i></div>
                    <h3>Komposisi Poin</h3>
                </div>
                <div class="doughnut-inner">
                    <div class="doughnut-canvas-wrap">
                        <canvas id="chart_doughnut_poin"></canvas>
                        <div class="chart-center-label">
                            <div class="cl-val" style="color:{{ $sisaColor }};">{{ $donutTotal }}</div>
                            <div class="cl-lbl">sisa poin</div>
                        </div>
                    </div>
                    <div class="doughnut-legend">
                        <span style="display:flex;align-items:center;gap:5px;">
                            <span style="width:10px;height:10px;border-radius:2px;background:#86efac;display:inline-block;flex-shrink:0;"></span>
                            Penghargaan <strong>+{{ $totalPenghargaan }}</strong>
                        </span>
                        <span style="display:flex;align-items:center;gap:5px;">
                            <span style="width:10px;height:10px;border-radius:2px;background:#fca5a5;display:inline-block;flex-shrink:0;"></span>
                            Pelanggaran <strong>-{{ $totalPelanggaran }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Tren per Bulan --}}
            <div class="card" style="margin-bottom:0;">
                <div class="c-head">
                    <div class="c-icon" style="background:#fefce8;"><i class="fas fa-chart-column" style="color:#ca8a04;"></i></div>
                    <h3>Tren Poin per Bulan</h3>
                </div>
                <div class="c-body" style="padding:8px 12px 16px;">
                    @if ($histori->isEmpty())
                        <div class="empty-inline" style="padding:30px 0;">
                            <i class="fas fa-chart-bar"></i>
                            Belum ada transaksi untuk ditampilkan.
                        </div>
                    @else
                        <div class="chart-wrap-tall"><canvas id="chart_tren_bulan"></canvas></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Ambang Peringatan ── --}}
        <div class="card" style="margin-bottom:12px;">
            <div class="c-head">
                <div class="c-icon" style="background:#fff7ed;"><i class="fas fa-flag" style="color:#c2410c;"></i></div>
                <h3>Ambang Peringatan</h3>
            </div>
            <div class="c-body" style="padding:10px 16px 14px;">
                @forelse ($thresholds as $threshold)
                    @php
                        $notification = $threshold['notification'];
                        $status = $notification?->status;
                        $tercapai = $threshold['tercapai'];
                        $statusLabel = match($status) {
                            'sent'    => 'Terkirim',
                            'pending' => 'Antri',
                            'failed'  => 'Gagal',
                            'skipped' => 'Nomor kosong',
                            default   => 'Belum dikirim',
                        };
                        $statusClass = match($status) {
                            'sent'    => 'tag-penghargaan',
                            'pending' => 'tag-amber',
                            'failed'  => 'tag-pelanggaran',
                            default   => 'tag-muted',
                        };
                    @endphp
                    <div class="threshold-item">
                        <div class="threshold-poin">
                            <span class="tag {{ $tercapai ? 'tag-red-outline' : 'tag-muted' }}">
                                <i class="fas fa-flag"></i> {{ $threshold['poin'] }} poin
                            </span>
                        </div>
                        <div class="threshold-body">
                            <div class="threshold-actions-text">
                                @if ($threshold['tindakan'] ?? null)
                                    <strong>{{ $threshold['tindakan'] }}</strong>
                                @endif
                                @if ($threshold['sanksi'] ?? null)
                                    &middot; {{ $threshold['sanksi'] }}
                                @endif
                                @if (!($threshold['tindakan'] ?? null) && !($threshold['sanksi'] ?? null))
                                    <span style="color:#64748b;">—</span>
                                @endif
                            </div>
                            <div class="threshold-footer">
                                <span class="tag tag-blue">Sisa {{ $threshold['sisa_poin'] }} poin</span>
                                <span class="tag {{ $statusClass }}">{{ $statusLabel }}</span>
                                @if ($notification?->sent_at)
                                    <span class="threshold-date">
                                        <i class="fas fa-clock"></i>
                                        {{ $notification->sent_at->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-inline"><i class="fas fa-flag"></i> Ambang poin belum tersedia.</div>
                @endforelse
            </div>
        </div>

        {{-- ── Histori Transaksi ── --}}
        <div class="card" id="histori" style="margin-bottom:16px;">
            <div class="c-head">
                <div class="c-icon" style="background:#eff6ff;"><i class="fas fa-history" style="color:#1d4ed8;"></i></div>
                <h3>Histori Transaksi</h3>
                @if ($historiPaginated->total())
                    <span class="hbadge">{{ $historiPaginated->total() }} entri</span>
                @endif
            </div>
            <div class="c-body" style="padding:0 0 4px;">
                @if ($historiPaginated->total())

                    {{-- ══ TABEL — Desktop (≥640px) ══ --}}
                    <div class="rekap-table-wrap">
                        <table class="rekap-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Uraian</th>
                                    <th style="text-align:right;">Poin</th>
                                    <th>Pemberi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($historiPaginated as $item)
                                    @php $isReward = $item->poinr > 0; @endphp
                                    <tr>
                                        <td class="td-date">
                                            {{ $item->tanggal?->format('d/m/Y') }}<br>
                                            <span style="font-size:.7rem;">{{ $item->tanggal?->format('H:i') }}</span>
                                        </td>
                                        <td>
                                            <span class="tag {{ $isReward ? 'tag-penghargaan' : 'tag-pelanggaran' }}">
                                                {{ $isReward ? 'Penghargaan' : 'Pelanggaran' }}
                                            </span>
                                        </td>
                                        <td class="td-uraian">
                                            @if ($item->idpasal)
                                                <strong>{{ $item->idpasal }}</strong>
                                            @endif
                                            {{ $item->ket ?: $item->subPasal?->pasal ?: '-' }}
                                        </td>
                                        <td class="td-poin" style="color:{{ $isReward ? '#15803d' : '#b91c1c' }};">
                                            {{ $isReward ? '+' : '-' }}{{ $isReward ? $item->poinr : $item->poinp }}
                                        </td>
                                        <td class="td-pemberi">{{ $item->creator?->name ?? ($item->pelapor ?? '-') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- ══ CARD LIST — Mobile (<640px) ══ --}}
                    <div class="rekap-card-list">
                        @foreach ($historiPaginated as $item)
                            @php $isReward = $item->poinr > 0; $poinVal = $isReward ? $item->poinr : $item->poinp; @endphp
                            <div class="rci {{ $isReward ? 'rci-pen' : 'rci-pel' }}">
                                <div class="rci-top">
                                    <div class="rci-uraian">
                                        @if ($item->idpasal)
                                            <span class="tag tag-muted" style="margin-bottom:3px;display:inline-flex;">{{ $item->idpasal }}</span><br>
                                        @endif
                                        {{ $item->ket ?: $item->subPasal?->pasal ?: '—' }}
                                    </div>
                                    <div class="rci-poin" style="color:{{ $isReward ? '#15803d' : '#b91c1c' }};">
                                        {{ $isReward ? '+' : '-' }}{{ $poinVal }}
                                    </div>
                                </div>
                                <div class="rci-meta">
                                    <span>
                                        <span class="tag {{ $isReward ? 'tag-penghargaan' : 'tag-pelanggaran' }}" style="font-size:.65rem;">
                                            {{ $isReward ? 'Penghargaan' : 'Pelanggaran' }}
                                        </span>
                                    </span>
                                    <span><i class="fas fa-calendar-alt"></i> {{ $item->tanggal?->format('d/m/Y H:i') }}</span>
                                    <span><i class="fas fa-user-check"></i> {{ $item->creator?->name ?? ($item->pelapor ?? '-') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- ── Pagination ── --}}
                    @if ($historiPaginated->hasPages())
                        <div class="rp-pagination">
                            {{-- Prev --}}
                            @if ($historiPaginated->onFirstPage())
                                <span class="pg-btn disabled"><i class="fas fa-angle-left"></i></span>
                            @else
                                <a href="{{ $historiPaginated->previousPageUrl() }}#histori" class="pg-btn"><i class="fas fa-angle-left"></i></a>
                            @endif

                            @php
                                $pgStart = max(1, $historiPaginated->currentPage() - 2);
                                $pgEnd   = min($historiPaginated->lastPage(), $historiPaginated->currentPage() + 2);
                            @endphp

                            @if ($pgStart > 1)
                                <a href="{{ $historiPaginated->url(1) }}#histori" class="pg-btn pg-num">1</a>
                                @if ($pgStart > 2)<span class="pg-btn pg-num" style="pointer-events:none;">…</span>@endif
                            @endif

                            @for ($pg = $pgStart; $pg <= $pgEnd; $pg++)
                                @if ($historiPaginated->currentPage() === $pg)
                                    <span class="pg-btn pg-num active">{{ $pg }}</span>
                                @else
                                    <a href="{{ $historiPaginated->url($pg) }}#histori" class="pg-btn pg-num">{{ $pg }}</a>
                                @endif
                            @endfor

                            @if ($pgEnd < $historiPaginated->lastPage())
                                @if ($pgEnd < $historiPaginated->lastPage() - 1)
                                    <span class="pg-btn pg-num" style="pointer-events:none;">…</span>
                                @endif
                                <a href="{{ $historiPaginated->url($historiPaginated->lastPage()) }}#histori" class="pg-btn pg-num">{{ $historiPaginated->lastPage() }}</a>
                            @endif

                            {{-- Next --}}
                            @if ($historiPaginated->hasMorePages())
                                <a href="{{ $historiPaginated->nextPageUrl() }}#histori" class="pg-btn"><i class="fas fa-angle-right"></i></a>
                            @else
                                <span class="pg-btn disabled"><i class="fas fa-angle-right"></i></span>
                            @endif
                        </div>
                        <div style="text-align:center;font-size:.72rem;color:#94a3b8;padding:4px 0 10px;">
                            Halaman {{ $historiPaginated->currentPage() }} / {{ $historiPaginated->lastPage() }}
                            &nbsp;·&nbsp; {{ $historiPaginated->total() }} entri
                        </div>
                    @endif

                @else
                    <div class="empty-inline">
                        <i class="fas fa-inbox"></i>
                        Belum ada transaksi poin tahun ajaran ini.
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── Fixed Action Bar ── --}}
    <div class="action-bar">
        <a href="{{ route('admin.rekap-poin.index', ['tahun_ajaran' => $tahunAjaran]) }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        @if (auth()->user()?->hasAnyRole(['superadmin', 'admin_tatib', 'bk']))
            <a href="{{ route('admin.surat-panggilan.create', ['siswa_id' => $siswa->id, 'tahun_ajaran' => $tahunAjaran]) }}"
                class="ab-btn ab-btn-danger">
                <i class="fas fa-envelope-open-text"></i> Surat Panggilan
            </a>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header-auto-show');
            if (header) header.classList.add('header-active');
        });

        (function() {
            const isMobile = window.innerWidth < 640;
            const font = { family: 'inherit', size: isMobile ? 10 : 11 };

            /* ── Doughnut: Komposisi Poin ─────────────────────────────────── */
            const donutCtx = document.getElementById('chart_doughnut_poin');
            if (donutCtx) {
                const poinAwal         = {{ $poinAwal }};
                const totalPelanggaran = {{ $totalPelanggaran }};
                const totalPenghargaan = {{ $totalPenghargaan }};

                const labels   = ['Modal Awal'];
                const datasets = [poinAwal];
                const bgColors = ['#93c5fd'];

                if (totalPenghargaan > 0) {
                    labels.push('Penghargaan');
                    datasets.push(totalPenghargaan);
                    bgColors.push('#86efac');
                }
                if (totalPelanggaran > 0) {
                    labels.push('Pelanggaran');
                    datasets.push(totalPelanggaran);
                    bgColors.push('#fca5a5');
                }

                new Chart(donutCtx, {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [{
                            data: datasets,
                            backgroundColor: bgColors,
                            borderColor: '#fff',
                            borderWidth: 3,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + ' poin' } }
                        }
                    }
                });
            }

            /* ── Bar Chart: Tren per Bulan ────────────────────────────────── */
            const trenCtx = document.getElementById('chart_tren_bulan');
            if (trenCtx) {
                new Chart(trenCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($trenLabels),
                        datasets: [
                            {
                                label: 'Pelanggaran',
                                data: @json($trenPel),
                                backgroundColor: 'rgba(239,68,68,.75)',
                                borderColor: '#ef4444',
                                borderWidth: 1, borderRadius: 4
                            },
                            {
                                label: 'Penghargaan',
                                data: @json($trenPen),
                                backgroundColor: 'rgba(34,197,94,.75)',
                                borderColor: '#22c55e',
                                borderWidth: 1, borderRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true, position: 'top',
                                labels: { font, boxWidth: 12, padding: 10 }
                            },
                            tooltip: { callbacks: { label: ctx => ' ' + ctx.dataset.label + ': ' + ctx.parsed.y + ' poin' } }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font,
                                    maxRotation: isMobile ? 45 : 0,
                                    minRotation: isMobile ? 45 : 0
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, font },
                                grid: { color: '#f1f5f9' }
                            }
                        }
                    }
                });
            }
        })();
    </script>
@endpush
