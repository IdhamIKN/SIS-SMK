@extends('layouts.app')

@section('title', 'Panel Realtime Kehadiran')

@push('styles')
<style>
/* ══════════════════════════════════════════
   SCOPE: .rtp — semua selector pakai prefix
   ══════════════════════════════════════════ */

/* ── Root wrap ── */
.rtp {
    display: flex;
    flex-direction: column;
    /* Tinggi tersisa setelah bottom-nav */
    height: calc(100dvh - var(--footer-h, 60px));
    background: #0f172a;
    overflow: hidden;
}

/* ══════════════════════════════════════════
   HEADER BAR (compact, collapsible)
   ══════════════════════════════════════════ */
.rtp .rtp-topbar {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px;
    background: #1e293b;
    border-bottom: 1px solid rgba(255,255,255,.07);
    overflow: hidden;
    transition: max-height .3s ease, padding .3s ease, opacity .3s ease;
    max-height: 60px;
}
.rtp .rtp-topbar.collapsed {
    max-height: 0;
    padding-top: 0;
    padding-bottom: 0;
    opacity: 0;
    pointer-events: none;
}

/* live badge */
.rtp .rtp-livebadge {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(14,165,233,.15); border: 1px solid rgba(14,165,233,.3);
    padding: 3px 9px; border-radius: 20px;
    font-size: .65rem; font-weight: 700; color: #7dd3fc;
    white-space: nowrap; flex-shrink: 0;
}
.rtp .rtp-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: #7dd3fc; animation: rtpPulse 1.2s infinite;
}
@keyframes rtpPulse { 0%,100%{opacity:1} 50%{opacity:.2} }

/* title */
.rtp .rtp-title {
    font-size: .9rem; font-weight: 800; color: #fff; white-space: nowrap;
    flex-shrink: 0; display: flex; align-items: center; gap: 6px;
}

/* controls inline */
.rtp .rtp-controls {
    display: flex; align-items: center; gap: 7px; flex: 1; justify-content: flex-end;
}
.rtp .rtp-inp {
    padding: 5px 10px; border-radius: 8px;
    border: 1.5px solid rgba(255,255,255,.12); background: rgba(255,255,255,.06);
    font-size: .75rem; color: #e2e8f0; font-family: inherit;
    outline: none; cursor: pointer;
    appearance: none; -webkit-appearance: none;
}
.rtp .rtp-inp:focus { border-color: #38bdf8; background: rgba(56,189,248,.08); }
.rtp .rtp-inp option { background: #1e293b; color: #e2e8f0; }
.rtp .rtp-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 11px; border-radius: 8px; border: none; cursor: pointer;
    font-size: .75rem; font-weight: 700; font-family: inherit;
    transition: all .15s; white-space: nowrap;
}
.rtp .rtp-btn:active { transform: scale(.93); }
.rtp .rtp-btn.pri { background: #0ea5e9; color: #fff; }
.rtp .rtp-btn.pri:hover { background: #0284c7; }
.rtp .rtp-btn.pri:disabled { opacity: .5; cursor: not-allowed; }
.rtp .rtp-btn.sec { background: rgba(255,255,255,.08); color: #94a3b8; border: 1px solid rgba(255,255,255,.1); }
.rtp .rtp-btn.sec:hover { background: rgba(255,255,255,.14); color: #e2e8f0; }

/* jam now chip */
.rtp .rtp-jamchip {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 9px; border-radius: 8px;
    background: rgba(14,165,233,.12); border: 1px solid rgba(14,165,233,.2);
    color: #7dd3fc; font-size: .72rem; font-weight: 700; white-space: nowrap;
}

/* ══════════════════════════════════════════
   SUMMARY BAR (satu baris ringkas)
   ══════════════════════════════════════════ */
.rtp .rtp-sumbar {
    flex-shrink: 0;
    display: flex; align-items: center; gap: 6px;
    padding: 5px 14px;
    background: #1e293b;
    border-bottom: 1px solid rgba(255,255,255,.07);
    overflow-x: auto;
}
.rtp .rtp-sumbar::-webkit-scrollbar { display: none; }
.rtp .rtp-sitem {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 9px; border-radius: 20px;
    font-size: .68rem; font-weight: 700; white-space: nowrap;
    background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08);
    color: #94a3b8;
}
.rtp .rtp-sitem .sdot { width: 7px; height: 7px; border-radius: 50%; }
.rtp .rtp-sitem.s-total { color: #e2e8f0; background: rgba(255,255,255,.1); }

/* ══════════════════════════════════════════
   GRID CONTAINER — mengisi sisa tinggi
   ══════════════════════════════════════════ */
.rtp .rtp-gridwrap {
    flex: 1;
    overflow: hidden;
    padding: 8px;
}

.rtp .rtp-grid {
    width: 100%;
    height: 100%;
    display: grid;
    /* kolom & baris dihitung JS */
    gap: 6px;
    align-content: stretch;
}

/* ══════════════════════════════════════════
   CARD — full color background per status
   ══════════════════════════════════════════ */

/* Wrapper per card */
.rtp .rtp-card-wrap {
    border-radius: 11px;
    overflow: hidden;
}

/* Warna background gradient per status */
.rtp .st-hijau  .rtp-card { background: linear-gradient(145deg, #22c55e 0%, #16a34a 100%); }
.rtp .st-kuning .rtp-card { background: linear-gradient(145deg, #f59e0b 0%, #d97706 100%); }
.rtp .st-merah  .rtp-card { background: linear-gradient(145deg, #ef4444 0%, #dc2626 100%); }
.rtp .st-abu    .rtp-card { background: linear-gradient(145deg, #64748b 0%, #475569 100%); }
.rtp .st-biru   .rtp-card { background: linear-gradient(145deg, #3b82f6 0%, #2563eb 100%); }
.rtp .st-pink   .rtp-card { background: linear-gradient(145deg, #ec4899 0%, #db2777 100%); }
.rtp .st-orange .rtp-card { background: linear-gradient(145deg, #f97316 0%, #ea580c 100%); }
.rtp .st-putih  .rtp-card { background: linear-gradient(145deg, #f1f5f9 0%, #e2e8f0 100%); }

.rtp .rtp-card {
    border-radius: 11px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 0;
    transition: transform .15s, box-shadow .15s, filter .15s;
    position: relative;
    isolation: isolate;
}
/* Highlight shine di pojok kiri atas */
.rtp .rtp-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 50%;
    background: linear-gradient(180deg, rgba(255,255,255,.12) 0%, transparent 100%);
    border-radius: 11px 11px 0 0;
    pointer-events: none; z-index: 0;
}
.rtp .st-putih .rtp-card::before { background: linear-gradient(180deg, rgba(255,255,255,.6) 0%, transparent 100%); }

.rtp .rtp-card:hover {
    transform: translateY(-2px) scale(1.015);
    box-shadow: 0 8px 28px rgba(0,0,0,.4);
    filter: brightness(1.08);
}

/* Semua konten di atas pseudo overlay */
.rtp .rtp-ctop,
.rtp .rtp-cbody { position: relative; z-index: 1; }

/* Strip atas tipis — warna lebih terang */
.rtp .rtp-ctop {
    height: 3px;
    flex-shrink: 0;
    background: rgba(255,255,255,.35);
}
.rtp .st-putih .rtp-ctop { background: rgba(0,0,0,.07); }

/* Card body */
.rtp .rtp-cbody {
    flex: 1;
    min-height: 0;
    padding: 7px 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 3px;
    overflow: hidden;
}

/* ── Row 1: nama kelas + badge ── */
.rtp .rtp-row1 {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 6px;
}
.rtp .rtp-kelas {
    font-size: clamp(.75rem, 1.4vw, 1.05rem);
    font-weight: 800;
    color: #fff;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-shadow: 0 1px 4px rgba(0,0,0,.2);
}
.rtp .st-putih .rtp-kelas { color: #0f172a; text-shadow: none; }

/* Badge — kaca putih semi-transparan */
.rtp .rtp-badge {
    flex-shrink: 0;
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 7px; border-radius: 6px;
    font-size: clamp(.48rem, .85vw, .63rem);
    font-weight: 800; text-transform: uppercase; letter-spacing: .04em;
    white-space: nowrap;
    background: rgba(255,255,255,.25);
    color: #fff;
    border: 1px solid rgba(255,255,255,.4);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
.rtp .rtp-badge .bdt {
    width: 5px; height: 5px; border-radius: 50%;
    background: rgba(255,255,255,.9);
}
.rtp .st-putih .rtp-badge {
    background: rgba(0,0,0,.08); color: #334155;
    border-color: rgba(0,0,0,.12);
}
.rtp .st-putih .rtp-badge .bdt { background: #64748b; }
/* Orange badge kedip */
.rtp .st-orange .rtp-badge { animation: rtpBlink .9s infinite; }
@keyframes rtpBlink { 0%,100%{opacity:1} 50%{opacity:.45} }

/* ── Row 2: guru ── */
.rtp .rtp-gtk {
    display: flex; align-items: center; gap: 5px;
    font-size: clamp(.6rem, 1.1vw, .78rem);
    font-weight: 600;
    color: rgba(255,255,255,.92);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.rtp .rtp-gtk i { color: rgba(255,255,255,.5); font-size: .7em; flex-shrink: 0; }
.rtp .st-putih .rtp-gtk       { color: #334155; }
.rtp .st-putih .rtp-gtk i     { color: #94a3b8; }

/* ── Row 3: mapel ── */
.rtp .rtp-mapel {
    display: flex; align-items: center; gap: 5px;
    font-size: clamp(.58rem, 1vw, .76rem);
    font-weight: 700;
    color: rgba(255,255,255,.98);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.rtp .rtp-mapel i { color: rgba(255,255,255,.55); font-size: .7em; flex-shrink: 0; }
.rtp .st-putih .rtp-mapel     { color: #0369a1; }
.rtp .st-putih .rtp-mapel i   { color: #38bdf8; }

/* ── Row 4: meta ── */
.rtp .rtp-meta {
    display: flex; align-items: center; gap: 8px; flex-wrap: nowrap;
    font-size: clamp(.5rem, .85vw, .65rem);
    color: rgba(255,255,255,.65);
    font-weight: 600; overflow: hidden;
}
.rtp .rtp-meta span { display: inline-flex; align-items: center; gap: 3px; white-space: nowrap; }
.rtp .rtp-meta i { font-size: .75em; }
.rtp .st-putih .rtp-meta { color: #94a3b8; }

/* ── Orange urgent pulse ── */
.rtp .rtp-card-wrap.st-orange {
    animation: rtpUrgent 2s ease-in-out infinite;
}
@keyframes rtpUrgent {
    0%,100% { box-shadow: 0 2px 8px rgba(0,0,0,.2); }
    50%      { box-shadow: 0 0 0 3px rgba(249,115,22,.6), 0 4px 20px rgba(249,115,22,.4); }
}

/* ── Skeleton ── */
.rtp .rtp-skel {
    background: #1e293b; border-radius: 11px;
    position: relative; overflow: hidden;
}
.rtp .rtp-skel::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.05) 50%, transparent 100%);
    background-size: 200% 100%; animation: rtpShimmer 1.4s infinite;
}
@keyframes rtpShimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }

/* ── Flash on WS update ── */
@keyframes rtpFlash {
    0%   { outline: 3px solid rgba(255,255,255,.9); outline-offset: 0; }
    100% { outline: 3px solid transparent; outline-offset: 5px; }
}
.rtp .rtp-card.updated { animation: rtpFlash .9s ease-out; }

/* ── Empty state ── */
.rtp .rtp-empty {
    grid-column: 1 / -1;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    height: 100%;
    color: #475569; font-size: .85rem; gap: 10px;
}
.rtp .rtp-empty i { font-size: 2.5rem; opacity: .3; }
</style>
@endpush

@section('content')
<div class="rtp" id="rtpRoot">

    {{-- ══ TOP BAR ══ --}}
    <div class="rtp-topbar" id="rtpTopbar">
        {{-- Live badge + clock --}}
        <div class="rtp-livebadge">
            <span class="rtp-dot"></span>
            <span id="rtpClock">--:--:--</span>
        </div>
        <div class="rtp-title">
            <i class="fas fa-tv"></i> Panel Kehadiran
        </div>

        {{-- Controls --}}
        <div class="rtp-controls">
            <input type="date" id="rtpTanggal" class="rtp-inp" value="{{ now()->toDateString() }}">

            <select id="rtpJamKe" class="rtp-inp">
                <option value="">Otomatis</option>
                @forelse(($jamPelajaran ?? collect()) as $jam)
                    @php
                        $jLabel = is_numeric($jam->nama_jam) ? 'Jam '.$jam->nama_jam : $jam->nama_jam;
                        $jTime  = ($jam->time_in && $jam->time_out)
                            ? $jam->time_in->format('H:i').'-'.$jam->time_out->format('H:i') : null;
                    @endphp
                    <option value="{{ $jam->id_jam }}">{{ $jLabel }}{{ $jTime ? ' ('.$jTime.')' : '' }}</option>
                @empty
                    @for ($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}">Jam {{ $i }}</option>
                    @endfor
                @endforelse
            </select>

            <div class="rtp-jamchip">
                <i class="fas fa-clock"></i>
                <span id="rtpJamNow">—</span>
            </div>

            <button id="rtpRefreshBtn" class="rtp-btn pri">
                <i class="fas fa-sync-alt" id="rtpRefreshIco"></i> Refresh
            </button>

            <button id="rtpFsBtn" class="rtp-btn sec" title="Layar Penuh">
                <i class="fas fa-expand" id="rtpFsIco"></i>
            </button>

            <button id="rtpToggleBar" class="rtp-btn sec" title="Sembunyikan toolbar">
                <i class="fas fa-chevron-up" id="rtpToggleIco"></i>
            </button>
        </div>
    </div>

    {{-- ══ SUMMARY BAR ══ --}}
    <div class="rtp-sumbar" id="rtpSumbar">
        <div class="rtp-sitem s-total">
            <i class="fas fa-school"></i>
            <span id="sTotal">0</span> Kelas
        </div>
        <div class="rtp-sitem" style="color:#4ade80;">
            <span class="sdot" style="background:#16a34a;"></span>
            <span id="sHijau">0</span> Tepat
        </div>
        <div class="rtp-sitem" style="color:#fbbf24;">
            <span class="sdot" style="background:#d97706;"></span>
            <span id="sKuning">0</span> Terlambat
        </div>
        <div class="rtp-sitem" style="color:#f87171;">
            <span class="sdot" style="background:#dc2626;"></span>
            <span id="sMerah">0</span> Tidak Hadir
        </div>
        <div class="rtp-sitem" style="color:#94a3b8;">
            <span class="sdot" style="background:#64748b;"></span>
            <span id="sAbu">0</span> Ada Tugas
        </div>
        <div class="rtp-sitem" style="color:#60a5fa;">
            <span class="sdot" style="background:#2563eb;"></span>
            <span id="sBiru">0</span> Pergi+Tugas
        </div>
        <div class="rtp-sitem" style="color:#f472b6;">
            <span class="sdot" style="background:#db2777;"></span>
            <span id="sPink">0</span> Pergi No Tugas
        </div>
        <div class="rtp-sitem" style="color:#fb923c;">
            <span class="sdot" style="background:#ea580c; animation:rtpBlink .9s infinite;"></span>
            <span id="sOrange">0</span> Tanpa Laporan
        </div>
    </div>

    {{-- ══ GRID ══ --}}
    <div class="rtp-gridwrap" id="rtpGridWrap">
        <div class="rtp-grid" id="rtpGrid">
            @for ($s = 0; $s < 12; $s++)
                <div class="rtp-skel"></div>
            @endfor
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ══════════════════════════════════
       State
    ══════════════════════════════════ */
    let lastData = [];

    /* ══════════════════════════════════
       DOM refs
    ══════════════════════════════════ */
    const grid        = document.getElementById('rtpGrid');
    const gridWrap    = document.getElementById('rtpGridWrap');
    const tanggalInp  = document.getElementById('rtpTanggal');
    const jamKeSel    = document.getElementById('rtpJamKe');
    const refBtn      = document.getElementById('rtpRefreshBtn');
    const refIco      = document.getElementById('rtpRefreshIco');
    const jamNowEl    = document.getElementById('rtpJamNow');

    /* ══════════════════════════════════
       Clock
    ══════════════════════════════════ */
    function tick() {
        document.getElementById('rtpClock').textContent =
            new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
    }
    setInterval(tick, 1000); tick();

    /* ══════════════════════════════════
       Toolbar toggle
    ══════════════════════════════════ */
    const topbar   = document.getElementById('rtpTopbar');
    const toggleIco = document.getElementById('rtpToggleIco');
    let barVisible = true;

    document.getElementById('rtpToggleBar').addEventListener('click', () => {
        barVisible = !barVisible;
        topbar.classList.toggle('collapsed', !barVisible);
        toggleIco.className = barVisible ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
        // Recalculate grid after transition
        setTimeout(() => autoSizeGrid(lastData.length || 1), 320);
    });

    /* ══════════════════════════════════
       Fullscreen
    ══════════════════════════════════ */
    const rootEl = document.getElementById('rtpRoot');
    const fsBtn  = document.getElementById('rtpFsBtn');
    const fsIco  = document.getElementById('rtpFsIco');

    fsBtn.addEventListener('click', () => {
        const isFull = !!(document.fullscreenElement || document.webkitFullscreenElement);
        if (!isFull) (rootEl.requestFullscreen || rootEl.webkitRequestFullscreen).call(rootEl);
        else         (document.exitFullscreen  || document.webkitExitFullscreen).call(document);
    });
    function onFsChange() {
        const isFull = !!(document.fullscreenElement || document.webkitFullscreenElement);
        fsIco.className = isFull ? 'fas fa-compress' : 'fas fa-expand';
        fsBtn.title     = isFull ? 'Keluar Layar Penuh' : 'Layar Penuh';
        setTimeout(() => autoSizeGrid(lastData.length || 1), 100);
    }
    document.addEventListener('fullscreenchange',       onFsChange);
    document.addEventListener('webkitfullscreenchange', onFsChange);

    /* ══════════════════════════════════
       Auto-size grid
       Hitung kolom optimal agar semua
       card muat dalam 1 layar
    ══════════════════════════════════ */
    function autoSizeGrid(n) {
        if (!n) return;

        const W = gridWrap.clientWidth  - 16; // padding 8px kiri+kanan
        const H = gridWrap.clientHeight - 16; // padding 8px atas+bawah
        const GAP = 6;

        // Rasio target per card: lebar:tinggi ≈ 1.9 (card compact)
        const TARGET = 1.9;

        let bestCols = 1;
        let bestDelta = Infinity;

        for (let c = 1; c <= n; c++) {
            const r       = Math.ceil(n / c);
            const cellW   = (W - GAP * (c - 1)) / c;
            const cellH   = (H - GAP * (r - 1)) / r;
            if (cellH <= 0) continue;
            const delta = Math.abs(cellW / cellH - TARGET);
            if (delta < bestDelta) {
                bestDelta = delta;
                bestCols  = c;
            }
        }

        const rows = Math.ceil(n / bestCols);
        grid.style.gridTemplateColumns = `repeat(${bestCols}, 1fr)`;
        grid.style.gridTemplateRows    = `repeat(${rows}, 1fr)`;
    }

    window.addEventListener('resize', () => {
        if (lastData.length) autoSizeGrid(lastData.length);
    });

    /* ══════════════════════════════════
       Status config
    ══════════════════════════════════ */
    const SLBL = {
        hijau : 'Tepat Waktu',
        kuning: 'Terlambat',
        merah : 'Tidak Hadir',
        abu   : 'Ada Tugas',
        biru  : 'Pergi + Tugas',
        pink  : 'Pergi No Tugas',
        orange: 'Tanpa Laporan',
        putih : 'Belum Laporan',
    };

    /* ══════════════════════════════════
       Build card HTML — sesimpel screenshot
    ══════════════════════════════════ */
    function buildCard(item) {
        const lbl      = SLBL[item.status] || item.status;
        const jamLbl   = item.jam_label || (item.jam_ke ? `Jam Ke-${item.jam_ke}` : '—');
        const waktuRange = (item.jadwal_jam_mulai && item.jadwal_jam_selesai)
            ? `${item.jadwal_jam_mulai}–${item.jadwal_jam_selesai}` : '';
        const waktuLaporan = item.waktu_laporan || '';

        return `
        <div class="rtp-card-wrap st-${item.status}" id="rtp-kls-${item.kelas_id}">
            <div class="rtp-card">
                <div class="rtp-ctop"></div>
                <div class="rtp-cbody">
                    {{-- Row 1: Nama kelas + badge --}}
                    <div class="rtp-row1">
                        <div class="rtp-kelas">${item.kelas_nama}</div>
                        <div class="rtp-badge"><span class="bdt"></span>${lbl}</div>
                    </div>
                    {{-- Row 2: Guru --}}
                    <div class="rtp-gtk">
                        <i class="fas fa-chalkboard-teacher"></i>
                        ${item.gtk_nama || '—'}
                    </div>
                    {{-- Row 3: Mapel --}}
                    <div class="rtp-mapel">
                        <i class="fas fa-book-open"></i>
                        ${item.mata_pelajaran || 'Belum ada jadwal'}
                    </div>
                    {{-- Row 4: Meta --}}
                    <div class="rtp-meta">
                        <span><i class="fas fa-list-ol"></i>${jamLbl}</span>
                        ${waktuRange   ? `<span><i class="fas fa-hourglass-half"></i>${waktuRange}</span>` : ''}
                        ${waktuLaporan ? `<span><i class="fas fa-clock"></i>${waktuLaporan}</span>` : ''}
                    </div>
                </div>
            </div>
        </div>`;
    }

    /* ══════════════════════════════════
       Summary bar
    ══════════════════════════════════ */
    function updateSummary(data) {
        const c = {};
        data.forEach(i => c[i.status] = (c[i.status]||0)+1);
        document.getElementById('sTotal').textContent  = data.length;
        document.getElementById('sHijau').textContent  = c.hijau  || 0;
        document.getElementById('sKuning').textContent = c.kuning || 0;
        document.getElementById('sMerah').textContent  = c.merah  || 0;
        document.getElementById('sAbu').textContent    = c.abu    || 0;
        document.getElementById('sBiru').textContent   = c.biru   || 0;
        document.getElementById('sPink').textContent   = c.pink   || 0;
        document.getElementById('sOrange').textContent = c.orange || 0;
    }

    /* ══════════════════════════════════
       Render cards
    ══════════════════════════════════ */
    function renderCards(data) {
        grid.innerHTML = '';
        if (!data.length) {
            grid.style.gridTemplateColumns = '1fr';
            grid.style.gridTemplateRows    = '1fr';
            grid.innerHTML = `
            <div class="rtp-empty">
                <i class="fas fa-inbox"></i>
                Tidak ada data untuk filter ini.
            </div>`;
            return;
        }
        data.forEach(item => grid.insertAdjacentHTML('beforeend', buildCard(item)));
        autoSizeGrid(data.length);
    }

    /* ══════════════════════════════════
       Fetch
    ══════════════════════════════════ */
    function buildUrl() {
        const p = new URLSearchParams({ tanggal: tanggalInp.value });
        if (jamKeSel.value) p.set('jam_ke', jamKeSel.value);
        return `/panel/api/status?${p.toString()}`;
    }

    function loadStatus() {
        refBtn.disabled = true;
        refIco.classList.add('fa-spin');

        // Skeleton placeholder (jumlah = lastData.length atau 12)
        const skelCount = lastData.length || 12;
        grid.innerHTML = Array(skelCount).fill('<div class="rtp-skel"></div>').join('');
        if (skelCount) autoSizeGrid(skelCount);

        fetch(buildUrl())
            .then(r => r.json())
            .then(data => {
                lastData = data.status || [];
                renderCards(lastData);
                updateSummary(lastData);
                // Jam now
                if (data.jam_ke) {
                    const mode  = jamKeSel.value ? 'Manual' : 'Otomatis';
                    const label = data.jam_label || `Jam Ke-${data.jam_ke}`;
                    jamNowEl.textContent = `${mode}: ${label}`;
                } else {
                    jamNowEl.textContent = '—';
                }
            })
            .catch(() => {
                grid.style.gridTemplateColumns = '1fr';
                grid.style.gridTemplateRows    = '1fr';
                grid.innerHTML = `
                <div class="rtp-empty" style="color:#f87171;">
                    <i class="fas fa-wifi"></i>
                    Gagal memuat data. Periksa koneksi.
                </div>`;
            })
            .finally(() => {
                refBtn.disabled = false;
                refIco.classList.remove('fa-spin');
            });
    }

    /* ══════════════════════════════════
       Events
    ══════════════════════════════════ */
    tanggalInp.addEventListener('change', loadStatus);
    jamKeSel.addEventListener('change',   loadStatus);
    refBtn.addEventListener('click',      loadStatus);

    // Auto-refresh tiap 60 detik
    setInterval(loadStatus, 60_000);

    // Init
    loadStatus();

    /* ══════════════════════════════════
       WebSocket (Reverb) — update card tanpa reload
    ══════════════════════════════════ */
    @if(config('broadcasting.default') === 'reverb')
    window.Echo.channel('panel.realtime')
        .listen('.laporan.updated', (data) => {
            const idx = lastData.findIndex(i => i.kelas_id === data.kelas_id);
            if (idx >= 0) lastData[idx] = { ...lastData[idx], ...data };
            else          lastData.push(data);

            const cardData = idx >= 0 ? lastData[idx] : data;
            const old      = document.getElementById(`rtp-kls-${data.kelas_id}`);

            if (old) {
                const tmp = document.createElement('div');
                tmp.innerHTML = buildCard(cardData);
                const neo = tmp.firstElementChild;
                old.replaceWith(neo);
                // Flash
                const inner = neo.querySelector('.rtp-card');
                if (inner) {
                    inner.classList.add('updated');
                    setTimeout(() => inner.classList.remove('updated'), 900);
                }
            } else {
                // Kelas baru muncul → render ulang
                renderCards(lastData);
            }

            updateSummary(lastData);
        });
    @endif
});
</script>
@endpush