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
            gap: 8px;
            padding: 6px 14px;
            background: #1e293b;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            overflow: visible;
            transition: max-height .3s ease, padding .3s ease, opacity .3s ease;
            max-height: 72px;
        }

        .rtp .rtp-topbar.collapsed {
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            opacity: 0;
            pointer-events: none;
            overflow: hidden;
        }

        /* live badge */
        .rtp .rtp-livebadge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(14, 165, 233, .15);
            border: 1px solid rgba(14, 165, 233, .3);
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700;
            color: #7dd3fc;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .rtp .rtp-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #7dd3fc;
            animation: rtpPulse 1.2s infinite;
        }

        @keyframes rtpPulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .2
            }
        }

        /* title */
        .rtp .rtp-title {
            font-size: .9rem;
            font-weight: 800;
            color: #fff;
            white-space: nowrap;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* controls inline */
        .rtp .rtp-controls {
            display: flex;
            align-items: center;
            gap: 7px;
            flex: 1;
            justify-content: flex-end;
        }

        .rtp .rtp-inp {
            padding: 5px 10px;
            border-radius: 8px;
            border: 1.5px solid rgba(255, 255, 255, .12);
            background: rgba(255, 255, 255, .06);
            font-size: .75rem;
            color: #e2e8f0;
            font-family: inherit;
            outline: none;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
        }

        .rtp .rtp-inp:focus {
            border-color: #38bdf8;
            background: rgba(56, 189, 248, .08);
        }

        .rtp .rtp-inp option {
            background: #1e293b;
            color: #e2e8f0;
        }

        .rtp .rtp-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 11px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: .75rem;
            font-weight: 700;
            font-family: inherit;
            transition: all .15s;
            white-space: nowrap;
        }

        .rtp .rtp-btn:active {
            transform: scale(.93);
        }

        .rtp .rtp-btn.pri {
            background: #0ea5e9;
            color: #fff;
        }

        .rtp .rtp-btn.pri:hover {
            background: #0284c7;
        }

        .rtp .rtp-btn.pri:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .rtp .rtp-btn.sec {
            background: rgba(255, 255, 255, .08);
            color: #94a3b8;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .rtp .rtp-btn.sec:hover {
            background: rgba(255, 255, 255, .14);
            color: #e2e8f0;
        }

        /* jam now chip */
        .rtp .rtp-jamchip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 9px;
            border-radius: 8px;
            background: rgba(14, 165, 233, .12);
            border: 1px solid rgba(14, 165, 233, .2);
            color: #7dd3fc;
            font-size: .72rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* ── Datetime chip (pengganti livebadge) ── */
        .rtp .rtp-datetimechip {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0px;
            padding: 4px 10px;
            border-radius: 10px;
            background: rgba(14, 165, 233, .12);
            border: 1px solid rgba(14, 165, 233, .25);
            flex-shrink: 0;
            line-height: 1.3;
        }

        .rtp .rtp-datetimechip .dtc-date {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .58rem;
            font-weight: 700;
            color: #7dd3fc;
            white-space: nowrap;
        }

        .rtp .rtp-datetimechip .dtc-date .rtp-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #38bdf8;
            animation: rtpPulse 1.2s infinite;
            flex-shrink: 0;
        }

        .rtp .rtp-datetimechip .dtc-time {
            font-size: .85rem;
            font-weight: 800;
            color: #e0f2fe;
            letter-spacing: .04em;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        /* ── Fullscreen button (enhanced) ── */
        .rtp .rtp-btn.fs-btn {
            background: rgba(99, 102, 241, .15);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, .3);
            gap: 5px;
        }

        .rtp .rtp-btn.fs-btn:hover {
            background: rgba(99, 102, 241, .28);
            color: #c7d2fe;
            border-color: rgba(99, 102, 241, .5);
        }

        .rtp .rtp-btn.fs-btn.is-fullscreen {
            background: rgba(239, 68, 68, .12);
            color: #fca5a5;
            border-color: rgba(239, 68, 68, .3);
        }

        .rtp .rtp-btn.fs-btn.is-fullscreen:hover {
            background: rgba(239, 68, 68, .22);
            color: #fecaca;
        }

        /* ── WS Status chip ── */
        .rtp .rtp-wsstatus {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 9px;
            border-radius: 8px;
            font-size: .68rem;
            font-weight: 700;
            white-space: nowrap;
            transition: background .4s, color .4s, border-color .4s;
        }

        .rtp .rtp-wsstatus.ws-connected {
            background: rgba(34, 197, 94, .15);
            border: 1px solid rgba(34, 197, 94, .3);
            color: #86efac;
        }

        .rtp .rtp-wsstatus.ws-connecting {
            background: rgba(234, 179, 8, .15);
            border: 1px solid rgba(234, 179, 8, .3);
            color: #fde047;
        }

        .rtp .rtp-wsstatus.ws-disconnected {
            background: rgba(239, 68, 68, .15);
            border: 1px solid rgba(239, 68, 68, .3);
            color: #fca5a5;
            animation: rtpPulse 1s infinite;
        }

        .rtp .rtp-wsdot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .ws-connected .rtp-wsdot {
            background: #22c55e;
        }

        .ws-connecting .rtp-wsdot {
            background: #eab308;
        }

        .ws-disconnected .rtp-wsdot {
            background: #ef4444;
        }

        /* ══════════════════════════════════════════
           SUMMARY BAR
           ══════════════════════════════════════════ */
        .rtp .rtp-sumbar {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            background: #1e293b;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            overflow-x: auto;
        }

        .rtp .rtp-sumbar::-webkit-scrollbar {
            display: none;
        }

        .rtp .rtp-sitem {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 700;
            white-space: nowrap;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .08);
            color: #94a3b8;
        }

        .rtp .rtp-sitem .sdot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }

        .rtp .rtp-sitem.s-total {
            color: #e2e8f0;
            background: rgba(255, 255, 255, .1);
        }

        /* ══════════════════════════════════════════
           GRID CONTAINER
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
            gap: 6px;
            align-content: stretch;
        }

        /* ══════════════════════════════════════════
           CARD
           ══════════════════════════════════════════ */
        .rtp .rtp-card-wrap {
            border-radius: 11px;
            overflow: hidden;
        }

        .rtp .st-hijau .rtp-card {
            background: linear-gradient(145deg, #22c55e 0%, #16a34a 100%);
        }

        .rtp .st-kuning .rtp-card {
            background: linear-gradient(145deg, #eab308 0%, #ca8a04 100%);
        }

        .rtp .st-merah .rtp-card {
            background: linear-gradient(145deg, #ef4444 0%, #dc2626 100%);
        }

        .rtp .st-abu .rtp-card {
            background: linear-gradient(145deg, #64748b 0%, #475569 100%);
        }

        .rtp .st-biru .rtp-card {
            background: linear-gradient(145deg, #3b82f6 0%, #2563eb 100%);
        }

        .rtp .st-pink .rtp-card {
            background: linear-gradient(145deg, #ec4899 0%, #db2777 100%);
        }

        .rtp .st-orange .rtp-card {
            background: linear-gradient(145deg, #f97316 0%, #ea580c 100%);
        }

        .rtp .st-putih .rtp-card {
            background: linear-gradient(145deg, #f1f5f9 0%, #e2e8f0 100%);
        }

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

        .rtp .rtp-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(180deg, rgba(255, 255, 255, .12) 0%, transparent 100%);
            border-radius: 11px 11px 0 0;
            pointer-events: none;
            z-index: 0;
        }

        .rtp .st-putih .rtp-card::before {
            background: linear-gradient(180deg, rgba(255, 255, 255, .6) 0%, transparent 100%);
        }

        .rtp .rtp-card:hover {
            transform: translateY(-2px) scale(1.015);
            box-shadow: 0 8px 28px rgba(0, 0, 0, .4);
            filter: brightness(1.08);
        }

        .rtp .rtp-ctop,
        .rtp .rtp-cbody {
            position: relative;
            z-index: 1;
        }

        .rtp .rtp-ctop {
            height: 3px;
            flex-shrink: 0;
            background: rgba(255, 255, 255, .35);
        }

        .rtp .st-putih .rtp-ctop {
            background: rgba(0, 0, 0, .07);
        }

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

        .rtp .rtp-card-wrap {
            transition: background 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .rtp .rtp-badge {
            transition: all 0.3s ease;
        }

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
            text-shadow: 0 1px 4px rgba(0, 0, 0, .2);
        }

        .rtp .st-putih .rtp-kelas {
            color: #0f172a;
            text-shadow: none;
        }

        .rtp .rtp-badge {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 7px;
            border-radius: 6px;
            font-size: clamp(.48rem, .85vw, .63rem);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
            background: rgba(255, 255, 255, .25);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .4);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        .rtp .rtp-badge .bdt {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .9);
        }

        .rtp .st-putih .rtp-badge {
            background: rgba(0, 0, 0, .08);
            color: #334155;
            border-color: rgba(0, 0, 0, .12);
        }

        .rtp .st-putih .rtp-badge .bdt {
            background: #64748b;
        }

        .rtp .st-orange .rtp-badge {
            animation: rtpBlink .9s infinite;
        }

        @keyframes rtpBlink {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .45
            }
        }

        .rtp .rtp-gtk {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: clamp(.6rem, 1.1vw, .78rem);
            font-weight: 600;
            color: rgba(255, 255, 255, .92);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rtp .rtp-gtk i {
            color: rgba(255, 255, 255, .5);
            font-size: .7em;
            flex-shrink: 0;
        }

        .rtp .st-putih .rtp-gtk {
            color: #334155;
        }

        .rtp .st-putih .rtp-gtk i {
            color: #94a3b8;
        }

        .rtp .rtp-mapel {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: clamp(.58rem, 1vw, .76rem);
            font-weight: 700;
            color: rgba(255, 255, 255, .98);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rtp .rtp-mapel i {
            color: rgba(255, 255, 255, .55);
            font-size: .7em;
            flex-shrink: 0;
        }

        .rtp .st-putih .rtp-mapel {
            color: #0369a1;
        }

        .rtp .st-putih .rtp-mapel i {
            color: #38bdf8;
        }

        .rtp .rtp-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
            font-size: clamp(.5rem, .85vw, .65rem);
            color: rgba(255, 255, 255, .65);
            font-weight: 600;
            overflow: hidden;
        }

        .rtp .rtp-meta span {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            white-space: nowrap;
        }

        .rtp .rtp-meta i {
            font-size: .75em;
        }

        .rtp .st-putih .rtp-meta {
            color: #94a3b8;
        }

        .rtp .rtp-card-wrap.st-orange {
            animation: rtpUrgent 2s ease-in-out infinite;
        }

        @keyframes rtpUrgent {

            0%,
            100% {
                box-shadow: 0 2px 8px rgba(0, 0, 0, .2);
            }

            50% {
                box-shadow: 0 0 0 3px rgba(249, 115, 22, .6), 0 4px 20px rgba(249, 115, 22, .4);
            }
        }

        /* ── Skeleton ── */
        .rtp .rtp-skel {
            background: #1e293b;
            border-radius: 11px;
            position: relative;
            overflow: hidden;
        }

        .rtp .rtp-skel::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, .05) 50%, transparent 100%);
            background-size: 200% 100%;
            animation: rtpShimmer 1.4s infinite;
        }

        @keyframes rtpShimmer {
            0% {
                background-position: -200% 0
            }

            100% {
                background-position: 200% 0
            }
        }

        /* ── Flash on WS update ── */
        @keyframes rtpFlash {
            0% {
                outline: 3px solid rgba(255, 255, 255, .9);
                outline-offset: 0;
            }

            100% {
                outline: 3px solid transparent;
                outline-offset: 5px;
            }
        }

        .rtp .rtp-card.updated {
            animation: rtpFlash .9s ease-out;
        }

        /* ── Empty state ── */
        .rtp .rtp-empty {
            grid-column: 1 / -1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #475569;
            font-size: .85rem;
            gap: 10px;
        }

        .rtp .rtp-empty i {
            font-size: 2.5rem;
            opacity: .3;
        }
    </style>
@endpush

@section('content')
    <div class="rtp" id="rtpRoot">

        {{-- ══ TOP BAR ══ --}}
        <div class="rtp-topbar" id="rtpTopbar">
            {{-- Datetime chip: tanggal + jam live --}}
            <div class="rtp-datetimechip">
                <div class="dtc-date">
                    <span class="rtp-dot"></span>
                    <span id="rtpDateLabel">— — —</span>
                </div>
                <div class="dtc-time" id="rtpClock">--:--:--</div>
            </div>

            <div class="rtp-title">
                <i class="fas fa-tv"></i> Panel Kehadiran
            </div>

            {{-- Controls --}}
            <div class="rtp-controls">
                <input type="date" id="rtpTanggal" class="rtp-inp" value="{{ now()->toDateString() }}">

                {{-- Chip jam aktif sekarang (info only, tidak lagi pakai dropdown) --}}
                <div class="rtp-jamchip">
                    <i class="fas fa-clock"></i>
                    <span id="rtpJamNow">⏱ Realtime</span>
                </div>

                {{-- Chip info hari --}}
                <div class="rtp-jamchip" id="rtpHariChip"
                    style="background:rgba(99,102,241,.15);border-color:rgba(99,102,241,.3);color:#a5b4fc;">
                    <i class="fas fa-calendar-day"></i>
                    <span id="rtpHariLabel">—</span>
                </div>

                {{-- WS STATUS CHIP --}}
                <div class="rtp-wsstatus ws-connecting" id="rtpWsStatus" title="Status koneksi WebSocket">
                    <span class="rtp-wsdot"></span>
                    <span id="rtpWsLabel">Menghubungkan…</span>
                </div>

                <button id="rtpRefreshBtn" class="rtp-btn pri">
                    <i class="fas fa-sync-alt" id="rtpRefreshIco"></i> Refresh
                </button>

                {{-- Tombol Fullscreen --}}
                <button id="rtpFsBtn" class="rtp-btn fs-btn" title="Layar Penuh">
                    <i class="fas fa-expand" id="rtpFsIco"></i>
                    <span id="rtpFsLabel">Fullscreen</span>
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
                <span class="sdot" style="background:#22c55e;"></span>
                <span id="sHijau">0</span> Hadir Tepat Waktu
            </div>
            <div class="rtp-sitem" style="color:#fde047;">
                <span class="sdot" style="background:#eab308;"></span>
                <span id="sKuning">0</span> Hadir Terlambat
            </div>
            <div class="rtp-sitem" style="color:#f87171;">
                <span class="sdot" style="background:#ef4444;"></span>
                <span id="sMerah">0</span> Tidak Hadir
            </div>
            <div class="rtp-sitem" style="color:#94a3b8;">
                <span class="sdot" style="background:#64748b;"></span>
                <span id="sAbu">0</span> Tidak Hadir+Tugas
            </div>
            <div class="rtp-sitem" style="color:#60a5fa;">
                <span class="sdot" style="background:#3b82f6;"></span>
                <span id="sBiru">0</span> Pergi+Ada Tugas
            </div>
            <div class="rtp-sitem" style="color:#f9a8d4;">
                <span class="sdot" style="background:#ec4899;"></span>
                <span id="sPink">0</span> Pergi+No Tugas
            </div>
            <div class="rtp-sitem" style="color:#fb923c;">
                <span class="sdot" style="background:#f97316; animation:rtpBlink .9s infinite;"></span>
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
        document.addEventListener('DOMContentLoaded', function() {

            /* ══════════════════════════════════════════════════════════════
               KONSTANTA KONFIGURASI
               Sesuaikan nilai-nilai ini dengan kebutuhan sistem Anda.
            ══════════════════════════════════════════════════════════════ */
            const CFG = {
                // Jika tidak ada event WS masuk selama N milidetik, anggap koneksi mati
                WS_IDLE_TIMEOUT_MS: 90_000, // 90 detik
                // Interval cek watchdog
                WS_WATCHDOG_INTERVAL: 30_000, // 30 detik
                // Delay sebelum reconnect setelah disconnect terdeteksi (exponential base)
                WS_RECONNECT_DELAY: 3_000, // 3 detik (base)
                // Delay maksimal reconnect (exponential backoff cap)
                WS_RECONNECT_DELAY_MAX: 60_000, // 60 detik
                // Interval polling HTTP fallback (safety net)
                POLL_INTERVAL_MS: 15_000, // 15 detik
                // Reconnect tidak pernah berhenti — set ke Infinity agar panel tetap hidup 24 jam
                MAX_RECONNECT_TRIES: Infinity,
                // Target rasio lebar:tinggi card (landscape = ~1.9)
                CARD_ASPECT_TARGET: 1.9,
            };

            /* ══════════════════════════════════════════════════════════════
               STATE
            ══════════════════════════════════════════════════════════════ */
            let lastData = [];
            let updateQueue = [];
            let updateScheduled = false;
            let resizeTimeout = null;
            let currentHari = '';

            // WebSocket health tracking
            let lastWsActivity = Date.now();
            let wsWatchdogTimer = null;
            let wsReconnectTimer = null;
            let reconnectTries = 0;
            let rtpChannel = null;
            let wsIsConnected = false;

            /* ══════════════════════════════════════════════════════════════
               DOM REFS
            ══════════════════════════════════════════════════════════════ */
            const grid = document.getElementById('rtpGrid');
            const gridWrap = document.getElementById('rtpGridWrap');
            const tanggalInp = document.getElementById('rtpTanggal');
            const refBtn = document.getElementById('rtpRefreshBtn');
            const refIco = document.getElementById('rtpRefreshIco');
            const jamNowEl = document.getElementById('rtpJamNow');
            const hariLblEl = document.getElementById('rtpHariLabel');
            const wsStatusEl = document.getElementById('rtpWsStatus');
            const wsLabelEl = document.getElementById('rtpWsLabel');

            /* ══════════════════════════════════════════════════════════════
               CLOCK + DATE
            ══════════════════════════════════════════════════════════════ */
            const HARI_ID = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const BULAN_ID = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const dateLblEl = document.getElementById('rtpDateLabel');

            function tick() {
                const now = new Date();

                // Jam menit detik
                document.getElementById('rtpClock').textContent =
                    now.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });

                // Hari, tanggal Bulan Tahun
                if (dateLblEl) {
                    const hari = HARI_ID[now.getDay()];
                    const tgl = String(now.getDate()).padStart(2, '0');
                    const bulan = BULAN_ID[now.getMonth()];
                    const tahun = now.getFullYear();
                    dateLblEl.textContent = `${hari}, ${tgl} ${bulan} ${tahun}`;
                }
            }
            setInterval(tick, 1000);
            tick();

            /* ══════════════════════════════════════════════════════════════
               WS STATUS CHIP HELPER
            ══════════════════════════════════════════════════════════════ */
            function setWsStatus(state, label) {
                wsStatusEl.className = `rtp-wsstatus ws-${state}`;
                wsLabelEl.textContent = label;
            }

            /* ══════════════════════════════════════════════════════════════
               TOOLBAR TOGGLE
            ══════════════════════════════════════════════════════════════ */
            const topbar = document.getElementById('rtpTopbar');
            const toggleIco = document.getElementById('rtpToggleIco');
            let barVisible = true;

            document.getElementById('rtpToggleBar').addEventListener('click', () => {
                barVisible = !barVisible;
                topbar.classList.toggle('collapsed', !barVisible);
                toggleIco.className = barVisible ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
                setTimeout(() => autoSizeGrid(lastData.length || 1), 320);
            });

            /* ══════════════════════════════════════════════════════════════
               FULLSCREEN
            ══════════════════════════════════════════════════════════════ */
            const rootEl = document.getElementById('rtpRoot');
            const fsBtn = document.getElementById('rtpFsBtn');
            const fsIco = document.getElementById('rtpFsIco');

            fsBtn.addEventListener('click', () => {
                const isFull = !!(document.fullscreenElement || document.webkitFullscreenElement);
                if (!isFull)(rootEl.requestFullscreen || rootEl.webkitRequestFullscreen).call(rootEl);
                else(document.exitFullscreen || document.webkitExitFullscreen).call(document);
            });

            function onFsChange() {
                const isFull = !!(document.fullscreenElement || document.webkitFullscreenElement);
                fsIco.className = isFull ? 'fas fa-compress' : 'fas fa-expand';
                fsBtn.title = isFull ? 'Keluar Layar Penuh' : 'Layar Penuh';
                fsBtn.classList.toggle('is-fullscreen', isFull);
                const fsLbl = document.getElementById('rtpFsLabel');
                if (fsLbl) fsLbl.textContent = isFull ? 'Keluar' : 'Fullscreen';
                setTimeout(() => autoSizeGrid(lastData.length || 1), 100);
            }
            document.addEventListener('fullscreenchange', onFsChange);
            document.addEventListener('webkitfullscreenchange', onFsChange);

            /* ══════════════════════════════════════════════════════════════
               AUTO-SIZE GRID
            ══════════════════════════════════════════════════════════════ */
            let currentCols = 1;

            function calculateOptimalCols(n) {
                if (!n) return 1;
                const W = gridWrap.clientWidth - 16;
                const H = gridWrap.clientHeight - 16;
                const GAP = 6;
                let bestCols = 1,
                    bestDelta = Infinity;
                for (let c = 1; c <= n; c++) {
                    const r = Math.ceil(n / c);
                    const cellW = (W - GAP * (c - 1)) / c;
                    const cellH = (H - GAP * (r - 1)) / r;
                    if (cellH <= 0) continue;
                    const delta = Math.abs(cellW / cellH - CFG.CARD_ASPECT_TARGET);
                    if (delta < bestDelta) {
                        bestDelta = delta;
                        bestCols = c;
                    }
                }
                return bestCols;
            }

            function autoSizeGrid(n) {
                if (!n) return;
                const optimalCols = calculateOptimalCols(n);
                if (optimalCols !== currentCols) {
                    currentCols = optimalCols;
                    const rows = Math.ceil(n / currentCols);
                    grid.style.gridTemplateColumns = `repeat(${currentCols}, 1fr)`;
                    grid.style.gridTemplateRows = `repeat(${rows}, 1fr)`;
                }
            }

            function scheduleResize() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    autoSizeGrid(lastData.length);
                    resizeTimeout = null;
                }, 300);
            }

            window.addEventListener('resize', () => {
                if (lastData.length) autoSizeGrid(lastData.length);
            });

            /* ══════════════════════════════════════════════════════════════
               STATUS LABELS
            ══════════════════════════════════════════════════════════════ */
            const SLBL = @json(collect(config('status_guru.statuses'))->mapWithKeys(fn($v, $k) => [$k => $v['label']]));

            /* ══════════════════════════════════════════════════════════════
               LOAD HARI — hanya ambil nama hari untuk chip info
               Dropdown jam sudah dihapus. Server pakai now() langsung.
            ══════════════════════════════════════════════════════════════ */
            function loadHari(tanggal, afterLoad) {
                fetch(`/panel/api/jam?tanggal=${tanggal}`)
                    .then(r => r.json())
                    .then(data => {
                        currentHari = data.hari || '';
                        if (hariLblEl) {
                            const hariIcon = currentHari === 'Jumat' ? '🕌' : '📅';
                            hariLblEl.textContent = `${hariIcon} ${currentHari}`;
                        }
                        if (typeof afterLoad === 'function') afterLoad();
                    })
                    .catch(() => {
                        if (typeof afterLoad === 'function') afterLoad();
                    });
            }

            /* ══════════════════════════════════════════════════════════════
               BUILD CARD HTML
            ══════════════════════════════════════════════════════════════ */
            function buildCard(item) {
                const lbl = SLBL[item.status] || item.status;
                const jamLbl = item.jam_label || (item.jam_ke ? `Jam ${item.jam_ke}` : '—');
                const waktuLaporan = item.waktu_laporan || '';

                return `<div class="rtp-card-wrap st-${item.status}" id="rtp-kls-${item.kelas_id}">
                    <div class="rtp-card">
                        <div class="rtp-ctop"></div>
                        <div class="rtp-cbody">
                            <div class="rtp-row1">
                                <div class="rtp-kelas">${item.kelas_nama}</div>
                                <div class="rtp-badge"><span class="bdt"></span>${lbl}</div>
                            </div>
                            <div class="rtp-gtk">
                                <i class="fas fa-chalkboard-teacher"></i>
                                ${item.gtk_nama || '—'}
                            </div>
                            <div class="rtp-mapel">
                                <i class="fas fa-book-open"></i>
                                ${item.mata_pelajaran || 'Belum ada jadwal'}
                            </div>
                            <div class="rtp-meta">
                                <span><i class="fas fa-hourglass-half"></i>${jamLbl}</span>
                                ${waktuLaporan ? `<span><i class="fas fa-clock"></i>${waktuLaporan}</span>` : ''}
                            </div>
                        </div>
                    </div>
                </div>`;
            }

            /* ══════════════════════════════════════════════════════════════
               SUMMARY BAR
            ══════════════════════════════════════════════════════════════ */
            function updateSummary(data) {
                const c = {};
                data.forEach(i => c[i.status] = (c[i.status] || 0) + 1);
                document.getElementById('sTotal').textContent = data.length;
                document.getElementById('sHijau').textContent = c.hijau || 0;
                document.getElementById('sKuning').textContent = c.kuning || 0;
                document.getElementById('sMerah').textContent = c.merah || 0;
                document.getElementById('sAbu').textContent = c.abu || 0;
                document.getElementById('sBiru').textContent = c.biru || 0;
                document.getElementById('sPink').textContent = c.pink || 0;
                document.getElementById('sOrange').textContent = c.orange || 0;
            }

            /* ══════════════════════════════════════════════════════════════
               RENDER CARDS
            ══════════════════════════════════════════════════════════════ */
            function renderCards(data) {
                grid.innerHTML = '';
                if (!data.length) {
                    grid.style.gridTemplateColumns = '1fr';
                    grid.style.gridTemplateRows = '1fr';
                    grid.innerHTML = `<div class="rtp-empty">
                        <i class="fas fa-inbox"></i>Tidak ada data untuk filter ini.</div>`;
                    return;
                }
                data.forEach(item => grid.insertAdjacentHTML('beforeend', buildCard(item)));
                autoSizeGrid(data.length);
            }

            /* ══════════════════════════════════════════════════════════════
               RECONCILE (diff update tanpa kedip)
            ══════════════════════════════════════════════════════════════ */
            function reconcileCards(newData) {
                const newMap = new Map(newData.map(d => [d.kelas_id, d]));
                const oldMap = new Map(lastData.map(d => [d.kelas_id, d]));
                let needResize = false;

                newData.forEach(item => {
                    const old = oldMap.get(item.kelas_id);
                    const cardWrap = document.getElementById(`rtp-kls-${item.kelas_id}`);

                    if (cardWrap) {
                        const changed = !old ||
                            old.status !== item.status ||
                            old.gtk_nama !== item.gtk_nama ||
                            old.mata_pelajaran !== item.mata_pelajaran ||
                            old.waktu_laporan !== item.waktu_laporan ||
                            old.jam_label !== item.jam_label;

                        if (changed) {
                            updateCardClass(cardWrap, old ? old.status : null, item.status);
                            updateCardContent(cardWrap, item);
                            flashCard(cardWrap);
                        }
                    } else {
                        const tmp = document.createElement('div');
                        tmp.innerHTML = buildCard(item);
                        grid.appendChild(tmp.firstElementChild);
                        needResize = true;
                    }
                });

                lastData.forEach(old => {
                    if (!newMap.has(old.kelas_id)) {
                        document.getElementById(`rtp-kls-${old.kelas_id}`)?.remove();
                        needResize = true;
                    }
                });

                lastData = newData;
                updateSummary(lastData);
                if (needResize) scheduleResize();
            }

            function flashCard(cardWrap) {
                const inner = cardWrap.querySelector('.rtp-card');
                if (!inner) return;
                inner.classList.remove('updated');
                void inner.offsetWidth;
                inner.classList.add('updated');
                setTimeout(() => inner.classList.remove('updated'), 900);
            }

            /* ══════════════════════════════════════════════════════════════
               FETCH STATUS
               Tidak ada lagi jam_ke — server selalu pakai now() sebagai
               refTime sehingga setiap kelas mendapat jadwal yang tepat
               berdasarkan waktu pelajaran masing-masing saat ini.
            ══════════════════════════════════════════════════════════════ */
            function buildUrl() {
                const p = new URLSearchParams({
                    tanggal: tanggalInp.value
                });
                return `/panel/api/status?${p.toString()}`;
            }

            function loadStatus(forceReload = false) {
                const isInitial = lastData.length === 0 || forceReload;

                refBtn.disabled = true;
                refIco.classList.add('fa-spin');

                if (isInitial) {
                    const skelCount = lastData.length || 12;
                    grid.innerHTML = Array(skelCount).fill('<div class="rtp-skel"></div>').join('');
                    if (skelCount) autoSizeGrid(skelCount);
                }

                fetch(buildUrl())
                    .then(r => r.json())
                    .then(data => {
                        const newData = data.status || [];

                        if (isInitial) {
                            lastData = newData;
                            renderCards(lastData);
                            updateSummary(lastData);
                        } else {
                            reconcileCards(newData);
                        }

                        // Catat waktu terakhir berhasil mendapat data
                        lastSuccessfulPoll = Date.now();

                        // Update chip jam: tampilkan waktu server saat data diambil
                        if (jamNowEl) {
                            jamNowEl.textContent = data.ref_time ?
                                `⏱ ${data.ref_time.slice(0,5)}` :
                                '⏱ Realtime';
                        }
                    })
                    .catch(() => {
                        if (isInitial) {
                            grid.style.gridTemplateColumns = '1fr';
                            grid.style.gridTemplateRows = '1fr';
                            grid.innerHTML = `<div class="rtp-empty" style="color:#f87171;">
                                <i class="fas fa-wifi"></i>Gagal memuat data. Periksa koneksi.</div>`;
                        }
                    })
                    .finally(() => {
                        refBtn.disabled = false;
                        refIco.classList.remove('fa-spin');
                    });
            }

            /* ══════════════════════════════════════════════════════════════
               CARD CONTENT UPDATE (dipakai WS + reconcile)
            ══════════════════════════════════════════════════════════════ */
            function updateCardContent(cardEl, newData) {
                const badge = cardEl.querySelector('.rtp-badge');
                const gtk = cardEl.querySelector('.rtp-gtk');
                const mapel = cardEl.querySelector('.rtp-mapel');
                const meta = cardEl.querySelector('.rtp-meta');

                if (badge) badge.innerHTML = `<span class="bdt"></span>${SLBL[newData.status] || newData.status}`;
                if (gtk) gtk.innerHTML = `<i class="fas fa-chalkboard-teacher"></i>${newData.gtk_nama || '—'}`;
                if (mapel) mapel.innerHTML =
                    `<i class="fas fa-book-open"></i>${newData.mata_pelajaran || 'Belum ada jadwal'}`;

                if (meta) {
                    const jamLbl = newData.jam_label || (newData.jam_ke ? `Jam ${newData.jam_ke}` : '—');
                    const waktuLaporan = newData.waktu_laporan || '';
                    meta.innerHTML = `<span><i class="fas fa-hourglass-half"></i>${jamLbl}</span>
                        ${waktuLaporan ? `<span><i class="fas fa-clock"></i>${waktuLaporan}</span>` : ''}`;
                }
            }

            function updateCardClass(cardWrap, oldStatus, newStatus) {
                if (oldStatus !== newStatus) {
                    cardWrap.classList.remove(`st-${oldStatus}`);
                    cardWrap.classList.add(`st-${newStatus}`);
                }
            }

            /* ══════════════════════════════════════════════════════════════
               PROCESS WS UPDATE QUEUE (batched via rAF)
            ══════════════════════════════════════════════════════════════ */
            function processUpdates() {
                updateQueue.forEach(({
                    kelas_id,
                    newData,
                    oldStatus
                }) => {
                    const cardWrap = document.getElementById(`rtp-kls-${kelas_id}`);
                    if (!cardWrap) return;
                    updateCardClass(cardWrap, oldStatus, newData.status);
                    updateCardContent(cardWrap, newData);
                    flashCard(cardWrap);
                });
                updateQueue = [];
                updateScheduled = false;
            }

            /* ══════════════════════════════════════════════════════════════
               WS WATCHDOG — deteksi koneksi diam terlalu lama
            ══════════════════════════════════════════════════════════════ */
            function resetWsWatchdog() {
                lastWsActivity = Date.now();
            }

            function startWsWatchdog() {
                clearInterval(wsWatchdogTimer);
                wsWatchdogTimer = setInterval(() => {
                    const idleMs = Date.now() - lastWsActivity;
                    if (idleMs > CFG.WS_IDLE_TIMEOUT_MS && wsIsConnected) {
                        console.warn(`[RTP] WS idle ${Math.round(idleMs / 1000)}s — paksa reconnect…`);
                        triggerReconnect('idle timeout');
                    }
                }, CFG.WS_WATCHDOG_INTERVAL);
            }

            /* ══════════════════════════════════════════════════════════════
               WS RECONNECT — putus dan sambung ulang channel
               Menggunakan exponential backoff: 3s, 6s, 12s, 24s, 48s, max 60s
               MAX_RECONNECT_TRIES = Infinity → panel tidak pernah menyerah
            ══════════════════════════════════════════════════════════════ */
            function triggerReconnect(reason) {
                if (wsReconnectTimer) return; // sudah ada reconnect pending

                reconnectTries++;
                wsIsConnected = false;

                // Exponential backoff dengan cap
                const delay = Math.min(
                    CFG.WS_RECONNECT_DELAY * Math.pow(2, reconnectTries - 1),
                    CFG.WS_RECONNECT_DELAY_MAX
                );

                setWsStatus('connecting', `Reconnect (${reconnectTries}) ${Math.round(delay/1000)}s…`);
                console.info(`[RTP] Reconnect #${reconnectTries} — alasan: ${reason}, delay: ${delay}ms`);

                // Tutup koneksi lama dulu
                try {
                    if (rtpChannel) {
                        window.Echo.leave('panel.realtime');
                        rtpChannel = null;
                    }
                    window.Echo.disconnect();
                } catch (e) {
                    /* abaikan error saat disconnect */ }

                wsReconnectTimer = setTimeout(() => {
                    wsReconnectTimer = null;
                    try {
                        window.Echo.connect();
                    } catch (e) {
                        console.error('[RTP] Echo.connect() gagal:', e);
                    }

                    // Paksa subscribe ulang setelah delay tambahan kecil,
                    // sebagai fallback jika event 'connected' tidak ter-fire
                    setTimeout(() => {
                        const state = window.Echo?.connector?.pusher?.connection?.state;
                        if (state === 'connected' && !rtpChannel) {
                            console.info('[RTP] Fallback subscribe setelah reconnect');
                            reconnectTries = 0;
                            subscribeChannel();
                        }
                    }, 2000);

                    // Ambil data terbaru via HTTP agar tidak ada gap selama WS terputus
                    loadStatus(false);
                }, delay);
            }

            /* ══════════════════════════════════════════════════════════════
               SUBSCRIBE CHANNEL — dipanggil setiap reconnect
            ══════════════════════════════════════════════════════════════ */
            function subscribeChannel() {
                // Pastikan channel lama sudah di-leave terlebih dahulu
                try {
                    if (rtpChannel) window.Echo.leave('panel.realtime');
                } catch (e) {
                    /* ignore */ }

                rtpChannel = window.Echo.channel('panel.realtime')
                    .listen('.laporan.updated', (data) => {
                        // Tandai ada aktivitas → reset watchdog
                        resetWsWatchdog();

                        const idx = lastData.findIndex(i => i.kelas_id === data.kelas_id);
                        const oldStatus = idx >= 0 ? lastData[idx].status : null;
                        const isNew = idx < 0;

                        if (!isNew) lastData[idx] = {
                            ...lastData[idx],
                            ...data
                        };
                        else lastData.push(data);

                        const latestData = lastData[isNew ? lastData.length - 1 : idx];
                        const cardWrap = document.getElementById(`rtp-kls-${data.kelas_id}`);

                        if (cardWrap) {
                            updateQueue.push({
                                kelas_id: data.kelas_id,
                                newData: latestData,
                                oldStatus
                            });
                        } else {
                            const tmp = document.createElement('div');
                            tmp.innerHTML = buildCard(latestData);
                            grid.appendChild(tmp.firstElementChild);
                            scheduleResize();
                        }

                        updateSummary(lastData);
                        if (!updateScheduled && updateQueue.length > 0) {
                            updateScheduled = true;
                            requestAnimationFrame(processUpdates);
                        }
                    })
                    .subscribed(() => {
                        // Channel berhasil di-subscribe (termasuk setelah reconnect)
                        resetWsWatchdog();
                        reconnectTries = 0; // reset counter karena sudah berhasil
                        wsIsConnected = true;
                        setWsStatus('connected', 'WS Terhubung');
                        console.info('[RTP] Channel subscribed ✓');
                        // Ambil data terbaru sekali lagi untuk menutup gap selama offline
                        loadStatus(false);
                    })
                    .error((err) => {
                        console.error('[RTP] Channel error:', err);
                        setWsStatus('disconnected', 'Channel Error');
                    });
            }

            /* ══════════════════════════════════════════════════════════════
               WEBSOCKET (REVERB) — INIT + EVENT BINDING
            ══════════════════════════════════════════════════════════════ */
            @if (config('broadcasting.default') === 'reverb')

                // ── Bind ke event koneksi Pusher/Reverb ──────────────────
                // 'connected': setiap kali (re)connect berhasil → re-subscribe
                window.Echo.connector.pusher.connection.bind('connected', () => {
                    console.info('[RTP] Echo: connected');
                    resetWsWatchdog();
                    wsIsConnected = true;
                    subscribeChannel(); // subscribe/re-subscribe channel
                });

                // 'connecting': sedang berusaha terhubung
                window.Echo.connector.pusher.connection.bind('connecting', () => {
                    console.info('[RTP] Echo: connecting…');
                    setWsStatus('connecting', 'Menghubungkan…');
                    wsIsConnected = false;
                });

                // 'disconnected': koneksi terputus (tanpa reconnect otomatis dari Echo)
                window.Echo.connector.pusher.connection.bind('disconnected', () => {
                    console.warn('[RTP] Echo: disconnected');
                    wsIsConnected = false;
                    setWsStatus('disconnected', 'WS Terputus');
                    triggerReconnect('pusher disconnected event');
                });

                // 'unavailable': koneksi gagal setelah beberapa retry
                window.Echo.connector.pusher.connection.bind('unavailable', () => {
                    console.warn('[RTP] Echo: unavailable');
                    wsIsConnected = false;
                    setWsStatus('disconnected', 'WS Tidak Tersedia');
                    triggerReconnect('pusher unavailable event');
                });

                // 'failed': browser tidak support WS sama sekali
                window.Echo.connector.pusher.connection.bind('failed', () => {
                    console.error('[RTP] Echo: failed — browser tidak support WS');
                    setWsStatus('disconnected', 'WS Tidak Didukung');
                });

                // ── Subscribe awal ────────────────────────────────────────
                // Jika Echo sudah terhubung saat halaman dimuat, langsung subscribe.
                // Jika belum, akan ditangani oleh event 'connected' di atas.
                const initState = window.Echo.connector.pusher.connection.state;
                if (initState === 'connected') {
                    subscribeChannel();
                } else {
                    setWsStatus('connecting', 'Menghubungkan…');
                }

                // ── Mulai watchdog ────────────────────────────────────────
                startWsWatchdog();
            @else
                // Reverb tidak aktif — tampilkan status yang sesuai
                setWsStatus('disconnected', 'Reverb Nonaktif');
            @endif

            /* ══════════════════════════════════════════════════════════════
               HTTP POLLING FALLBACK
               Safety net jika WS terputus dan reconnect gagal.
               Tetap berjalan selamanya — jika WS aktif, reconcileCards()
               hanya akan mengupdate data yang berubah tanpa kedip.
            ══════════════════════════════════════════════════════════════ */
            setInterval(() => loadStatus(false), CFG.POLL_INTERVAL_MS);

            /* ══════════════════════════════════════════════════════════════
               AUTO RELOAD — jaring pengaman terakhir
               Jika tab dibuka berjam-jam dan polling/WS somehow berhenti
               (browser deep throttle, memory pressure, dll), halaman akan
               reload otomatis setiap RELOAD_INTERVAL_MS.
               Default: 30 menit. Hanya reload saat tab VISIBLE agar tidak
               ganggu saat user sedang melihat panel.
            ══════════════════════════════════════════════════════════════ */
            const RELOAD_INTERVAL_MS = 30 * 60 * 1000; // 30 menit
            let lastSuccessfulPoll = Date.now();
            let reloadCheckTimer = null;

            // Catat setiap kali polling berhasil
            const _origLoadStatus = loadStatus;
            // Wrap loadStatus untuk catat waktu terakhir sukses
            // (sudah didefinisikan di atas, tambahkan tracking di .then)
            // → gunakan pendekatan interval terpisah yang cek lastSuccessfulPoll

            // Cek setiap menit: jika sudah > RELOAD_INTERVAL_MS tanpa data baru
            // DAN tab sedang visible → reload halaman
            reloadCheckTimer = setInterval(() => {
                const idleMs = Date.now() - lastSuccessfulPoll;
                if (idleMs >= RELOAD_INTERVAL_MS && document.visibilityState === 'visible') {
                    console.warn(
                        `[RTP] Auto-reload: tidak ada update selama ${Math.round(idleMs/60000)} menit`);
                    window.location.reload();
                }
            }, 60_000); // cek setiap 1 menit

            /* ══════════════════════════════════════════════════════════════
               WS HEARTBEAT — kirim ping ke Pusher/Reverb setiap 25 detik
               Mencegah koneksi idle diputus oleh NAT / proxy / load balancer
               yang memiliki idle timeout (biasanya 30–120 detik).
            ══════════════════════════════════════════════════════════════ */
            @if (config('broadcasting.default') === 'reverb')
                setInterval(() => {
                    try {
                        const pusher = window.Echo?.connector?.pusher;
                        if (pusher && pusher.connection.state === 'connected') {
                            pusher.connection.send_event('pusher:ping', {}, null);
                            resetWsWatchdog();
                        }
                    } catch (e) {
                        /* abaikan, heartbeat bersifat opsional */ }
                }, 25_000);
            @endif

            /* ══════════════════════════════════════════════════════════════
               PAGE VISIBILITY — refresh segera saat tab/monitor aktif kembali
               Mengatasi browser throttling saat tab tidak aktif lama.
            ══════════════════════════════════════════════════════════════ */
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    console.info('[RTP] Tab aktif kembali — refresh data dan reset watchdog');
                    resetWsWatchdog();
                    loadStatus(false);

                    // Jika WS tidak terhubung saat tab kembali aktif, reconnect
                    if (!wsIsConnected) {
                        triggerReconnect('tab visible kembali');
                    }
                }
            });

            /* ══════════════════════════════════════════════════════════════
               EVENTS — FILTER CHANGE
            ══════════════════════════════════════════════════════════════ */
            tanggalInp.addEventListener('change', () => {
                loadHari(tanggalInp.value, () => loadStatus(true));
            });

            refBtn.addEventListener('click', () => loadStatus(true));

            /* ══════════════════════════════════════════════════════════════
               INIT — load jam dulu, lalu status
            ══════════════════════════════════════════════════════════════ */
            loadHari(tanggalInp.value, () => loadStatus(true));
        });
    </script>
@endpush
