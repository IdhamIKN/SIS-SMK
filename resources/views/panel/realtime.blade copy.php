    @extends('layouts.app')

    @section('title', 'Panel Realtime Kehadiran')

    @push('styles')
    <style>
    /* ═══════════════════════════════════════════════════════
    SCOPE: .rtp — tidak ada selector tanpa prefix ini
    agar sidebar / bottom-nav dari layouts.app tetap aman
    ═══════════════════════════════════════════════════════ */

    /* Wrap */
    .rtp {
        padding-bottom: calc(var(--footer-h, 60px) + 40px);
        background: #f8fafc;
    }

    /* ── Page strip ── */
    .rtp .rtp-strip {
        padding: 20px 20px 28px;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0ea5e9 100%);
        position: relative; overflow: hidden;
    }
    .rtp .rtp-strip::before {
        content: ''; position: absolute; top: -40px; right: -40px;
        width: 140px; height: 140px; background: rgba(255,255,255,.06); border-radius: 50%;
    }
    .rtp .rtp-strip::after {
        content: ''; position: absolute; bottom: -24px; left: -20px;
        width: 100px; height: 100px; background: rgba(255,255,255,.04); border-radius: 50%;
    }
    .rtp .rtp-live-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.18);
        padding: 3px 10px; border-radius: 20px;
        font-size: .7rem; font-weight: 600; color: rgba(255,255,255,.9);
        margin-bottom: 10px; position: relative; z-index: 1;
    }
    .rtp .rtp-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: #7dd3fc; animation: rtp-pulse 1.2s infinite;
    }
    @keyframes rtp-pulse { 0%,100%{opacity:1} 50%{opacity:.3} }
    .rtp .rtp-strip h2 {
        font-size: 1.3rem; font-weight: 800; color: #fff;
        margin: 0 0 4px; position: relative; z-index: 1;
        display: flex; align-items: center; gap: 8px;
    }
    .rtp .rtp-strip p {
        font-size: .8rem; color: rgba(255,255,255,.65);
        margin: 0; position: relative; z-index: 1;
    }

    /* ── Summary chips ── */
    .rtp .rtp-summary {
        display: none; flex-wrap: wrap; gap: 8px; padding: 12px 16px 0;
    }
    .rtp .rtp-summary.show { display: flex; }
    .rtp .rtp-schip {
        flex: 1; min-width: 70px;
        background: #fff; border-radius: 12px; padding: 10px 12px;
        border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,.05);
        display: flex; align-items: center; gap: 10px;
    }
    .rtp .rtp-schip-ico {
        width: 32px; height: 32px; border-radius: 9px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: .85rem;
    }
    .rtp .rtp-schip-val { font-size: 1.2rem; font-weight: 800; line-height: 1; color: #0f172a; }
    .rtp .rtp-schip-lbl { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; margin-top: 2px; }

    /* ── Controls bar ── */
    .rtp .rtp-ctrl {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
        padding: 12px 16px; margin: 12px 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
        display: flex; flex-wrap: wrap; align-items: flex-end; gap: 10px;
    }
    .rtp .rtp-cf { display: flex; flex-direction: column; gap: 3px; flex: 1; min-width: 100px; }
    .rtp .rtp-clbl {
        font-size: .65rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: #94a3b8;
    }
    .rtp .rtp-cinp {
        width: 100%; padding: 8px 10px;
        border: 1.5px solid #e2e8f0; border-radius: 9px;
        font-size: .82rem; color: #334155; background: #f8fafc;
        font-family: inherit; outline: none; cursor: pointer;
        transition: border-color .15s, box-shadow .15s;
        appearance: none; -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='11' height='11' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 9px center; padding-right: 26px;
    }
    .rtp .rtp-cinp[type="date"] { background-image: none; padding-right: 10px; }
    .rtp .rtp-cinp:focus { border-color: #0ea5e9; background: #fff; box-shadow: 0 0 0 3px rgba(14,165,233,.1); }
    .rtp .rtp-cbtns { display: flex; gap: 8px; align-self: flex-end; }
    .rtp .rtp-cbtn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 14px; border-radius: 9px;
        font-size: .78rem; font-weight: 700; font-family: inherit;
        border: none; cursor: pointer; transition: all .16s; white-space: nowrap;
    }
    .rtp .rtp-cbtn:active { transform: scale(.95); }
    .rtp .rtp-cbtn.pri { background: #0ea5e9; color: #fff; }
    .rtp .rtp-cbtn.pri:hover { background: #0284c7; }
    .rtp .rtp-cbtn.pri:disabled { opacity: .55; cursor: not-allowed; }
    .rtp .rtp-cbtn.sec { background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; padding: 8px 11px; }
    .rtp .rtp-cbtn.sec:hover { background: #e2e8f0; }
    .rtp .rtp-jamchip {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 7px 11px; border-radius: 9px;
        background: #e0f2fe; border: 1px solid #7dd3fc;
        color: #0369a1; font-size: .75rem; font-weight: 700;
        align-self: flex-end; white-space: nowrap;
    }

    /* ── Legend ── */
    .rtp .rtp-legend {
        display: flex; flex-wrap: wrap; gap: 8px;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        padding: 10px 16px; margin: 0 16px 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .rtp .rtp-li { display: inline-flex; align-items: center; gap: 5px; font-size: .68rem; font-weight: 600; color: #64748b; }
    .rtp .rtp-ldot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }

    /* ── Tabs ── */
    .rtp .rtp-tabs {
        display: flex; gap: 4px; background: #fff;
        border: 1px solid #e2e8f0; border-radius: 12px; padding: 4px;
        margin: 0 16px 12px; box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .rtp .rtp-tab {
        flex: 1; display: inline-flex; align-items: center; justify-content: center;
        gap: 6px; padding: 9px 12px; border-radius: 9px;
        font-size: .8rem; font-weight: 700; border: none; cursor: pointer;
        font-family: inherit; background: transparent; color: #94a3b8; transition: all .18s;
    }
    .rtp .rtp-tab:hover { background: #f8fafc; color: #334155; }
    .rtp .rtp-tab.active { background: #0ea5e9; color: #fff; box-shadow: 0 2px 8px rgba(14,165,233,.3); }
    .rtp .rtp-pane { display: none; padding: 0 16px; }
    .rtp .rtp-pane.active { display: block; }

    /* ── Class cards grid ── */
    .rtp .rtp-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px;
    }

    /* ── Class card ── */
    .rtp .rtp-card {
        background: #fff; border-radius: 14px; border: 1px solid #e2e8f0;
        box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden;
        transition: transform .2s, box-shadow .2s; position: relative;
    }
    .rtp .rtp-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.1); }
    .rtp .rtp-cstrip { height: 5px; }

    /* Status strip colors */
    .rtp .st-hijau  .rtp-cstrip { background: linear-gradient(90deg,#16a34a,#4ade80); }
    .rtp .st-kuning .rtp-cstrip { background: linear-gradient(90deg,#d97706,#fbbf24); }
    .rtp .st-merah  .rtp-cstrip { background: linear-gradient(90deg,#dc2626,#f87171); }
    .rtp .st-abu    .rtp-cstrip { background: linear-gradient(90deg,#475569,#94a3b8); }
    .rtp .st-biru   .rtp-cstrip { background: linear-gradient(90deg,#1d4ed8,#60a5fa); }
    .rtp .st-pink   .rtp-cstrip { background: linear-gradient(90deg,#be185d,#f472b6); }
    .rtp .st-orange .rtp-cstrip { background: linear-gradient(90deg,#c2410c,#fb923c); }
    .rtp .st-putih  .rtp-cstrip { background: linear-gradient(90deg,#cbd5e1,#e2e8f0); }
    .rtp .st-orange { animation: rtp-urgent 2s ease-in-out infinite; }
    @keyframes rtp-urgent {
        0%,100% { box-shadow: 0 1px 4px rgba(0,0,0,.06); }
        50%      { box-shadow: 0 0 0 3px rgba(194,65,12,.2), 0 4px 12px rgba(194,65,12,.15); }
    }

    /* Card head */
    .rtp .rtp-chead { padding: 11px 13px 7px; display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
    .rtp .rtp-cname { font-size: 1.05rem; font-weight: 800; color: #0f172a; line-height: 1.25; letter-spacing: -.3px; }
    .rtp .rtp-sbadge {
        flex-shrink: 0; display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 8px; border-radius: 6px; font-size: .6rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .04em; white-space: nowrap;
    }
    .rtp .rtp-sbadge .bdt { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
    .rtp .st-hijau  .rtp-sbadge { background: #dcfce7; color: #15803d; }
    .rtp .st-kuning .rtp-sbadge { background: #fef3c7; color: #b45309; }
    .rtp .st-merah  .rtp-sbadge { background: #fee2e2; color: #dc2626; }
    .rtp .st-abu    .rtp-sbadge { background: #f1f5f9; color: #475569; }
    .rtp .st-biru   .rtp-sbadge { background: #dbeafe; color: #1d4ed8; }
    .rtp .st-pink   .rtp-sbadge { background: #fce7f3; color: #be185d; }
    .rtp .st-orange .rtp-sbadge { background: #ffedd5; color: #c2410c; }
    .rtp .st-putih  .rtp-sbadge { background: #f8fafc; color: #94a3b8; }
    .rtp .st-orange .bdt { animation: rtp-blink .8s infinite; }
    @keyframes rtp-blink { 0%,100%{opacity:1} 50%{opacity:.2} }

    /* Card body */
    .rtp .rtp-cinfo { padding: 0 13px 8px; }
    .rtp .rtp-gtk { display: flex; align-items: center; gap: 5px; font-size: .8rem; font-weight: 700; color: #334155; margin-bottom: 3px; }
    .rtp .rtp-gtk i { color: #94a3b8; font-size: .65rem; }
    .rtp .rtp-mapel {
        display: flex; align-items: center; gap: 5px;
        font-size: .74rem; font-weight: 700; color: #0369a1;
        margin-bottom: 4px; line-height: 1.25;
    }
    .rtp .rtp-mapel i { color: #38bdf8; font-size: .65rem; }
    .rtp .rtp-cmeta { display: flex; gap: 10px; font-size: .68rem; color: #94a3b8; }
    .rtp .rtp-cmeta span { display: inline-flex; align-items: center; gap: 3px; }
    .rtp .rtp-cdiv { height: 1px; background: #f1f5f9; margin: 0 13px; }

    /* Stats grid */
    .rtp .rtp-cstats { padding: 8px 13px; display: grid; grid-template-columns: repeat(3,1fr); gap: 5px; }
    .rtp .rtp-si { background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px; padding: 6px 7px; display: flex; flex-direction: column; gap: 1px; }
    .rtp .rtp-sv { font-size: .9rem; font-weight: 800; line-height: 1; }
    .rtp .rtp-sl { font-size: .57rem; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; color: #94a3b8; }
    .rtp .sv-m { color: #15803d; } .rtp .sv-p { color: #1d4ed8; }
    .rtp .sv-b { color: #b45309; } .rtp .sv-i { color: #be185d; }
    .rtp .sv-pd{ color: #c2410c; } .rtp .sv-t { color: #0f172a; }

    /* Progress */
    .rtp .rtp-cprog { padding: 0 13px 12px; }
    .rtp .rtp-ptrack { height: 5px; border-radius: 99px; background: #f1f5f9; overflow: hidden; display: flex; }
    .rtp .rtp-pm { height: 100%; background: #22c55e; border-radius: 99px; transition: width .5s; }
    .rtp .rtp-pp { height: 100%; background: #3b82f6; border-radius: 99px; transition: width .5s; }
    .rtp .rtp-plbl { display: flex; justify-content: space-between; font-size: .6rem; color: #94a3b8; margin-top: 4px; }

    /* Skeleton */
    .rtp .rtp-skel { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; height: 210px; overflow: hidden; position: relative; }
    .rtp .rtp-skel::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(90deg, transparent, rgba(0,0,0,.04), transparent);
        background-size: 200% 100%; animation: rtp-shimmer 1.5s ease-in-out infinite;
    }
    @keyframes rtp-shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }

    /* Flash on WS update */
    @keyframes rtp-flash { 0%{outline:2px solid #0ea5e9;outline-offset:0} 100%{outline:2px solid transparent;outline-offset:4px} }
    .rtp .rtp-card.updated { animation: rtp-flash .7s ease-out; }

    /* ── Charts ── */
    .rtp .rtp-chart-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 600px) { .rtp .rtp-chart-grid { grid-template-columns: 1fr; } }
    .rtp .rtp-chart-card {
        background: #fff; border-radius: 14px; border: 1px solid #e2e8f0;
        box-shadow: 0 1px 4px rgba(0,0,0,.06); padding: 14px 16px; overflow: hidden;
    }
    .rtp .rtp-chart-card.full { grid-column: 1 / -1; }
    .rtp .rtp-chart-title { font-size: .82rem; font-weight: 800; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 7px; }
    .rtp .rtp-chart-title i { color: #0ea5e9; }
    .rtp .rtp-cwrap { position: relative; height: 220px; }
    .rtp .rtp-cwrap-tall { position: relative; height: 280px; }

    /* Fullscreen */
    .rtp:fullscreen,
    .rtp:-webkit-full-screen {
        width: 100vw; height: 100vh;
        overflow-y: auto; overflow-x: hidden;
        background: #f8fafc;
    }
    .rtp:fullscreen .rtp-grid,
    .rtp:-webkit-full-screen .rtp-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px,1fr));
    }
    </style>
    @endpush

    @section('content')
    <div class="rtp" id="rtpRoot">

        {{-- Page Strip --}}
        <div class="rtp-strip">
            <div class="rtp-live-badge">
                <span class="rtp-dot"></span>
                <span id="rtpClockTime">--:--:--</span>
                &nbsp;·&nbsp;
                <span id="rtpClockDate">--</span>
            </div>
            <h2><i class="fas fa-tv"></i> Panel Realtime Kehadiran</h2>
            <p>Pantau kehadiran guru per kelas secara langsung</p>
        </div>

        {{-- Summary chips --}}
        <div class="rtp-summary" id="rtpSummary">
            <div class="rtp-schip">
                <div class="rtp-schip-ico" style="background:#e0f2fe;color:#0369a1;"><i class="fas fa-school"></i></div>
                <div><div class="rtp-schip-val" id="sTotal">0</div><div class="rtp-schip-lbl">Kelas</div></div>
            </div>
            <div class="rtp-schip">
                <div class="rtp-schip-ico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-check-circle"></i></div>
                <div><div class="rtp-schip-val" id="sHijau">0</div><div class="rtp-schip-lbl">Tepat</div></div>
            </div>
            <div class="rtp-schip">
                <div class="rtp-schip-ico" style="background:#fef3c7;color:#b45309;"><i class="fas fa-clock"></i></div>
                <div><div class="rtp-schip-val" id="sKuning">0</div><div class="rtp-schip-lbl">Terlambat</div></div>
            </div>
            <div class="rtp-schip">
                <div class="rtp-schip-ico" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-times-circle"></i></div>
                <div><div class="rtp-schip-val" id="sMerah">0</div><div class="rtp-schip-lbl">Tidak Hadir</div></div>
            </div>
            <div class="rtp-schip">
                <div class="rtp-schip-ico" style="background:#ffedd5;color:#c2410c;"><i class="fas fa-exclamation-triangle"></i></div>
                <div><div class="rtp-schip-val" id="sOrange">0</div><div class="rtp-schip-lbl">Tanpa Laporan</div></div>
            </div>
        </div>

        {{-- Controls --}}
        <div class="rtp-ctrl">
            <div class="rtp-cf">
                <div class="rtp-clbl"><i class="fas fa-calendar"></i> Tanggal</div>
                <input type="date" id="rtpTanggal" class="rtp-cinp" value="{{ now()->toDateString() }}">
            </div>
            <div class="rtp-cf">
                <div class="rtp-clbl"><i class="fas fa-list-ol"></i> Jam Ke</div>
                <select id="rtpJamKe" class="rtp-cinp">
                    <option value="">Otomatis (Sekarang)</option>
                    @forelse(($jamPelajaran ?? collect()) as $jam)
                        @php
                            $jamText = is_numeric($jam->nama_jam) ? 'Jam ' . $jam->nama_jam : $jam->nama_jam;
                            $jamTime = ($jam->time_in && $jam->time_out)
                                ? $jam->time_in->format('H:i') . ' - ' . $jam->time_out->format('H:i')
                                : null;
                        @endphp
                        <option value="{{ $jam->id_jam }}">
                            {{ $jamText }}{{ $jamTime ? ' (' . $jamTime . ')' : '' }}
                        </option>
                    @empty
                        @for ($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}">Jam {{ $i }}</option>
                        @endfor
                    @endforelse
                </select>
            </div>
            <div class="rtp-cbtns">
                <button id="rtpRefreshBtn" class="rtp-cbtn pri">
                    <i class="fas fa-sync-alt" id="rtpRefreshIco"></i> Refresh
                </button>
                <button id="rtpFsBtn" class="rtp-cbtn sec" title="Layar Penuh">
                    <i class="fas fa-expand" id="rtpFsIco"></i>
                </button>
            </div>
            <div class="rtp-jamchip">
                <i class="fas fa-clock"></i>
                <span id="rtpJamNow">—</span>
            </div>
        </div>

        {{-- Legend --}}
        <div class="rtp-legend">
            @php $legs = [
                ['28A745','Tepat Waktu'],
                ['FFC107','Terlambat'],
                ['DC3545','Tidak Hadir'],
                ['6C757D','Ada Tugas'],
                ['17A2B8','Hadir & Memberikan Tugas'],
                ['007BFF','Meninggalkan Kelas – Ada Tugas'],
                ['343A40','Meninggalkan Kelas – Tanpa Tugas'],
                ['cbd5e1','Belum Laporan'],
            ]; @endphp
            @foreach ($legs as [$c, $l])
                <div class="rtp-li">
                    <div class="rtp-ldot" style="background:#{{ $c }};"></div>
                    {{ $l }}
                </div>
            @endforeach
        </div>

        {{-- Tabs --}}
        <div class="rtp-tabs">
            <button class="rtp-tab active" data-tab="rtpPaneKartu">
                <i class="fas fa-th-large"></i> Kartu Kelas
            </button>
            <button class="rtp-tab" data-tab="rtpPaneGrafik">
                <i class="fas fa-chart-bar"></i> Grafik Realtime
            </button>
        </div>

        {{-- Tab: Kartu --}}
        <div class="rtp-pane active" id="rtpPaneKartu">
            <div class="rtp-grid" id="rtpGrid">
                @for ($s = 0; $s < 8; $s++)<div class="rtp-skel"></div>@endfor
            </div>
        </div>

        {{-- Tab: Grafik --}}
        <div class="rtp-pane" id="rtpPaneGrafik">
            <div class="rtp-chart-grid">
                <div class="rtp-chart-card">
                    <div class="rtp-chart-title"><i class="fas fa-chart-pie"></i> Distribusi Status Kelas</div>
                    <div class="rtp-cwrap"><canvas id="rtpChartStatus"></canvas></div>
                </div>
                <div class="rtp-chart-card">
                    <div class="rtp-chart-title"><i class="fas fa-users"></i> Kehadiran Siswa per Kelas</div>
                    <div class="rtp-cwrap"><canvas id="rtpChartKehadiran"></canvas></div>
                </div>
                <div class="rtp-chart-card full">
                    <div class="rtp-chart-title"><i class="fas fa-chart-bar"></i> Rekap Masuk · Pulang · Belum per Kelas</div>
                    <div class="rtp-cwrap-tall"><canvas id="rtpChartRekap"></canvas></div>
                </div>
            </div>
        </div>

    </div>
    @endsection

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        /* ── State ── */
        let lastData = [];
        let charts   = {};

        /* ── DOM ── */
        const grid       = document.getElementById('rtpGrid');
        const tanggalInp = document.getElementById('rtpTanggal');
        const jamKeSel   = document.getElementById('rtpJamKe');
        const refBtn     = document.getElementById('rtpRefreshBtn');
        const refIco     = document.getElementById('rtpRefreshIco');
        const summary    = document.getElementById('rtpSummary');
        const jamNowEl   = document.getElementById('rtpJamNow');

        /* ── Clock ── */
        function tick() {
            const n = new Date();
            document.getElementById('rtpClockTime').textContent =
                n.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
            document.getElementById('rtpClockDate').textContent =
                n.toLocaleDateString('id-ID', { weekday:'short', day:'numeric', month:'short' });
        }
        setInterval(tick, 1000); tick();

        /* ── Fullscreen ── */
        const fsBtn  = document.getElementById('rtpFsBtn');
        const fsIco  = document.getElementById('rtpFsIco');
        const rootEl = document.getElementById('rtpRoot');
        fsBtn.addEventListener('click', () => {
            const full = document.fullscreenElement || document.webkitFullscreenElement;
            if (!full) (rootEl.requestFullscreen || rootEl.webkitRequestFullscreen).call(rootEl);
            else       (document.exitFullscreen   || document.webkitExitFullscreen).call(document);
        });
        function onFsChange() {
            const full = !!(document.fullscreenElement || document.webkitFullscreenElement);
            fsIco.className = full ? 'fas fa-compress' : 'fas fa-expand';
            fsBtn.title     = full ? 'Keluar Layar Penuh' : 'Layar Penuh';
        }
        document.addEventListener('fullscreenchange',       onFsChange);
        document.addEventListener('webkitfullscreenchange', onFsChange);

        /* ── Tabs ── */
        document.querySelectorAll('.rtp-tab').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.rtp-tab').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.rtp-pane').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(btn.dataset.tab).classList.add('active');
                if (btn.dataset.tab === 'rtpPaneGrafik' && lastData.length) updateCharts(lastData);
            });
        });

        /* ── Status config ── */
        const SLBL = {
            hijau:'Tepat Waktu', kuning:'Terlambat', merah:'Tidak Hadir',
            abu:'Ada Tugas', biru:'Pergi+Tugas', pink:'Pergi No Tugas',
            orange:'⚠ Tanpa Laporan', putih:'Belum Laporan',
        };
        const SCOL = {
            hijau:'#28A745',
            kuning:'#FFC107',
            biru:'#17A2B8',
            abu:'#6C757D',
            merah:'#DC3545',
            pink:'#007BFF',
            orange:'#343A40',
            putih:'#e2e8f0',
        };

        /* ── Build card ── */
        function buildCard(item) {
            const lbl    = SLBL[item.status] || item.status;
            const total  = item.total_siswa    || 0;
            const masuk  = item.sudah_masuk    || 0;
            const pulang = item.sudah_pulang   || 0;
            const belum  = item.belum_absen    || 0;
            const izin   = item.izin_disetujui || 0;
            const pend   = item.izin_pending   || 0;
            const pM     = total > 0 ? Math.round((masuk  / total) * 100) : 0;
            const pP     = total > 0 ? Math.round((pulang / total) * 100) : 0;
            const jamLbl = item.jam_label || `Jam Ke-${item.jam_ke}`;
            const jadwalWaktu = item.jadwal_jam_mulai && item.jadwal_jam_selesai
                ? `${item.jadwal_jam_mulai}-${item.jadwal_jam_selesai}`
                : '';
            return `
            <div class="rtp-card st-${item.status}" id="rtp-kls-${item.kelas_id}">
                <div class="rtp-cstrip"></div>
                <div class="rtp-chead">
                    <div class="rtp-cname">${item.kelas_nama}</div>
                    <div class="rtp-sbadge"><span class="bdt"></span>${lbl}</div>
                </div>
                <div class="rtp-cinfo">
                    <div class="rtp-gtk"><i class="fas fa-chalkboard-teacher"></i>${item.gtk_nama || '—'}</div>
                    <div class="rtp-mapel"><i class="fas fa-book-open"></i>${item.mata_pelajaran || 'Belum ada jadwal'}</div>
                    <div class="rtp-cmeta">
                        <span><i class="fas fa-book"></i>${jamLbl}</span>
                        ${jadwalWaktu ? `<span><i class="fas fa-hourglass-half"></i>${jadwalWaktu}</span>` : ''}
                        ${item.waktu_laporan ? `<span><i class="fas fa-clock"></i>${item.waktu_laporan}</span>` : ''}
                    </div>
                </div>
                <div class="rtp-cdiv"></div>
                <div class="rtp-cstats">
                    <div class="rtp-si"><div class="rtp-sv sv-m">${masuk}</div><div class="rtp-sl">Masuk</div></div>
                    <div class="rtp-si"><div class="rtp-sv sv-p">${pulang}</div><div class="rtp-sl">Pulang</div></div>
                    <div class="rtp-si"><div class="rtp-sv sv-b">${belum}</div><div class="rtp-sl">Belum</div></div>
                    <div class="rtp-si"><div class="rtp-sv sv-i">${izin}</div><div class="rtp-sl">Izin ✓</div></div>
                    <div class="rtp-si"><div class="rtp-sv sv-pd">${pend}</div><div class="rtp-sl">Pending</div></div>
                    <div class="rtp-si"><div class="rtp-sv sv-t">${total}</div><div class="rtp-sl">Total</div></div>
                </div>
                <div class="rtp-cprog">
                    <div class="rtp-ptrack">
                        <div class="rtp-pm" style="width:${pM}%;"></div>
                        <div class="rtp-pp" style="width:${Math.min(pP, 100 - pM)}%;"></div>
                    </div>
                    <div class="rtp-plbl"><span>${pM}% masuk</span><span>${total} siswa</span></div>
                </div>
            </div>`;
        }

        /* ── Summary ── */
        function updateSummary(data) {
            const c = {};
            data.forEach(i => c[i.status] = (c[i.status] || 0) + 1);
            document.getElementById('sTotal').textContent  = data.length;
            document.getElementById('sHijau').textContent  = c.hijau  || 0;
            document.getElementById('sKuning').textContent = c.kuning || 0;
            document.getElementById('sMerah').textContent  = c.merah  || 0;
            document.getElementById('sOrange').textContent = c.orange || 0;
            summary.classList.add('show');
        }

        /* ── Charts ── */
        Chart.defaults.font.family = 'inherit';
        Chart.defaults.color       = '#64748b';

        function initCharts() {
            charts.status = new Chart(document.getElementById('rtpChartStatus'), {
                type: 'doughnut',
                data: { labels: [], datasets: [{ data: [], backgroundColor: [], borderWidth: 2, borderColor: '#fff' }] },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '65%',
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, padding: 12, font: { size: 11, weight: '600' } } },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} kelas` } }
                    }
                }
            });
            charts.kehadiran = new Chart(document.getElementById('rtpChartKehadiran'), {
                type: 'bar',
                data: { labels: [], datasets: [
                    { label:'Masuk',  data:[], backgroundColor:'#22c55e', borderRadius:4 },
                    { label:'Pulang', data:[], backgroundColor:'#3b82f6', borderRadius:4 },
                    { label:'Belum',  data:[], backgroundColor:'#f59e0b', borderRadius:4 },
                ]},
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { labels: { boxWidth: 12, font: { size: 11, weight: '600' } } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } }
                    }
                }
            });
            charts.rekap = new Chart(document.getElementById('rtpChartRekap'), {
                type: 'bar',
                data: { labels: [], datasets: [
                    { label:'Masuk',   data:[], backgroundColor:'#22c55e', borderRadius:4 },
                    { label:'Pulang',  data:[], backgroundColor:'#3b82f6', borderRadius:4 },
                    { label:'Belum',   data:[], backgroundColor:'#fbbf24', borderRadius:4 },
                    { label:'Izin ✓', data:[], backgroundColor:'#ec4899', borderRadius:4 },
                    { label:'Pending', data:[], backgroundColor:'#f97316', borderRadius:4 },
                ]},
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { labels: { boxWidth: 12, font: { size: 11, weight: '600' } } } },
                    scales: {
                        x: { stacked: true, beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } },
                        y: { stacked: true, grid: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        function updateCharts(data) {
            if (!data.length) return;
            const statusCount = {};
            data.forEach(i => statusCount[i.status] = (statusCount[i.status] || 0) + 1);
            const sKeys = Object.keys(statusCount);
            charts.status.data.labels                          = sKeys.map(k => SLBL[k] || k);
            charts.status.data.datasets[0].data                = sKeys.map(k => statusCount[k]);
            charts.status.data.datasets[0].backgroundColor     = sKeys.map(k => SCOL[k] || '#ccc');
            charts.status.update('none');

            const lbls = data.map(i => i.kelas_nama);
            charts.kehadiran.data.labels = lbls;
            charts.kehadiran.data.datasets[0].data = data.map(i => i.sudah_masuk  || 0);
            charts.kehadiran.data.datasets[1].data = data.map(i => i.sudah_pulang || 0);
            charts.kehadiran.data.datasets[2].data = data.map(i => i.belum_absen  || 0);
            charts.kehadiran.update('none');

            charts.rekap.data.labels = lbls;
            charts.rekap.data.datasets[0].data = data.map(i => i.sudah_masuk    || 0);
            charts.rekap.data.datasets[1].data = data.map(i => i.sudah_pulang   || 0);
            charts.rekap.data.datasets[2].data = data.map(i => i.belum_absen    || 0);
            charts.rekap.data.datasets[3].data = data.map(i => i.izin_disetujui || 0);
            charts.rekap.data.datasets[4].data = data.map(i => i.izin_pending   || 0);
            charts.rekap.update('none');
        }

        /* ── Render cards ── */
        function renderCards(data) {
            grid.innerHTML = '';
            if (!data.length) {
                grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:50px;color:#94a3b8;font-size:.9rem;">
                    <i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.4;"></i>
                    Tidak ada data untuk filter ini.
                </div>`;
                return;
            }
            data.forEach(item => grid.insertAdjacentHTML('beforeend', buildCard(item)));
        }

        function isJamAuto() {
            return !jamKeSel.value;
        }

        function statusUrl() {
            const params = new URLSearchParams({ tanggal: tanggalInp.value });
            if (!isJamAuto()) params.set('jam_ke', jamKeSel.value);
            return `/panel/api/status?${params.toString()}`;
        }

        function updateJamNow(data) {
            if (!data.jam_ke) {
                jamNowEl.textContent = '—';
                return;
            }

            const mode = isJamAuto() ? 'Otomatis' : 'Manual';
            const label = data.jam_label || `Jam Ke-${data.jam_ke}`;
            jamNowEl.textContent = `${mode}: ${label}`;
        }

        /* ── Fetch ── */
        function loadStatus() {
            refBtn.disabled = true;
            refIco.classList.add('fa-spin');
            grid.innerHTML  = Array(8).fill('<div class="rtp-skel"></div>').join('');
            summary.classList.remove('show');

            fetch(statusUrl())
                .then(r => r.json())
                .then(data => {
                    lastData = data.status || [];
                    renderCards(lastData);
                    updateSummary(lastData);
                    updateJamNow(data);
                    if (document.getElementById('rtpPaneGrafik').classList.contains('active')) updateCharts(lastData);
                })
                .catch(() => {
                    grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:50px;color:#ef4444;font-size:.9rem;">
                        <i class="fas fa-wifi" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
                        Gagal memuat data. Periksa koneksi.
                    </div>`;
                })
                .finally(() => {
                    refBtn.disabled = false;
                    refIco.classList.remove('fa-spin');
                });
        }

        /* ── Init ── */
        function init() {
            initCharts();
            loadStatus();
        }

        tanggalInp.addEventListener('change', loadStatus);
        jamKeSel.addEventListener('change',   loadStatus);
        refBtn.addEventListener('click',      loadStatus);
        setInterval(loadStatus, 60000);
        init();

        /* ── WebSocket (Reverb) ── */
        @if(config('broadcasting.default') === 'reverb')
        window.Echo.channel('panel.realtime')
            .listen('.laporan.updated', (data) => {
                const idx = lastData.findIndex(i => i.kelas_id === data.kelas_id);
                if (idx >= 0) lastData[idx] = { ...lastData[idx], ...data };
                else lastData.push(data);
                const cardData = idx >= 0 ? lastData[idx] : data;
                const old = document.getElementById(`rtp-kls-${data.kelas_id}`);
                if (old) {
                    const tmp = document.createElement('div');
                    tmp.innerHTML = buildCard(cardData);
                    const neo = tmp.firstElementChild;
                    old.replaceWith(neo);
                    neo.classList.add('updated');
                    setTimeout(() => neo.classList.remove('updated'), 800);
                }
                updateSummary(lastData);
                if (document.getElementById('rtpPaneGrafik').classList.contains('active')) updateCharts(lastData);
            });
        @endif
    });
    </script>
    @endpush
