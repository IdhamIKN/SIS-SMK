@extends('layouts.app')
@section('title', $eventGuru->nama_event)
@push('styles')
    @include('components.event-styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .ev-wrap {
            padding: 0 12px;
            max-width: 1280px;
            margin: 0 auto;
            box-sizing: border-box
        }

        @media(min-width:768px) {
            .ev-wrap {
                padding: 0 20px
            }
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 14px
        }

        @media(min-width:480px) {
            .stat-grid {
                grid-template-columns: repeat(4, 1fr)
            }
        }

        @media(min-width:768px) {
            .stat-grid {
                gap: 12px;
                margin-bottom: 18px
            }
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 8px;
            text-align: center
        }

        @media(min-width:768px) {
            .stat-card {
                padding: 16px;
                border-radius: 12px
            }
        }

        .stat-value {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 3px
        }

        @media(min-width:768px) {
            .stat-value {
                font-size: 1.8rem
            }
        }

        .stat-label {
            font-size: .6rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 8px 0;
            font-size: .84rem;
            border-bottom: 1px solid #f1f5f9
        }

        .detail-row:last-child {
            border-bottom: none
        }

        .dr-label {
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 7px;
            flex-shrink: 0;
            font-size: .82rem
        }

        .dr-value {
            font-weight: 600;
            color: #0f172a;
            text-align: right;
            max-width: 60%;
            word-break: break-word
        }

        .ev-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch
        }

        @media(max-width:767px) {
            .ev-table-wrap {
                display: none
            }
        }

        .ev-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .78rem;
            background: #fff
        }

        .ev-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0
        }

        .ev-table th {
            padding: 11px 10px;
            text-align: left;
            font-size: .68rem;
            font-weight: 700;
            color: #64748b;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: .04em
        }

        .ev-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle
        }

        .ev-table tbody tr:last-child td {
            border-bottom: none
        }

        .ev-table tbody tr:hover td {
            background: #fafbfc
        }

        .ev-card-list {
            display: none
        }

        @media(max-width:767px) {
            .ev-card-list {
                display: flex;
                flex-direction: column;
                gap: 0
            }
        }

        .evi {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            background: #fff
        }

        .evi:last-child {
            border-bottom: none
        }

        .evi-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 4px
        }

        .evi-name {
            font-weight: 700;
            font-size: .85rem;
            color: #0f172a
        }

        .evi-mid {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 4px
        }

        .evi-chip {
            font-size: .7rem;
            color: #64748b;
            background: #f1f5f9;
            border-radius: 5px;
            padding: 2px 7px;
            font-weight: 600
        }

        .badge-masuk-g {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700
        }

        .badge-pulang-g {
            background: #ffedd5;
            color: #c2410c;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700
        }

        .badge-ev-active {
            background: #dcfce7;
            color: #15803d;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 700
        }

        .badge-ev-ended {
            background: #f1f5f9;
            color: #64748b;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 700
        }

        .barcode-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            font-family: monospace;
            font-size: .78rem;
            color: #64748b;
            word-break: break-all;
            margin-top: 12px;
            text-align: center
        }

        #qrcode-wrap {
            display: inline-block;
            background: #fff;
            padding: 16px;
            border-radius: 12px;
            border: 1px solid #e2e8f0
        }

        #qrcode-wrap img,
        #qrcode-wrap canvas {
            display: block
        }

        .rotate-timer {
            font-size: .75rem;
            color: #64748b;
            margin-top: 8px
        }

        .rotate-timer .timer-val {
            font-weight: 700;
            color: #4338ca
        }

        .sse-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .68rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px
        }

        .sse-status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%
        }

        .sse-connected {
            background: #dcfce7;
            color: #15803d
        }

        .sse-connected .sse-status-dot {
            background: #16a34a;
            animation: ssePulse 1.5s infinite
        }

        .sse-connecting {
            background: #fef9c3;
            color: #a16207
        }

        .sse-connecting .sse-status-dot {
            background: #eab308
        }

        .sse-disconnected {
            background: #fee2e2;
            color: #b91c1c
        }

        .sse-disconnected .sse-status-dot {
            background: #ef4444
        }

        @keyframes ssePulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .3
            }
        }

        #fullscreen-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #0a0f1e;
            z-index: 10000;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 22px;
            overflow: hidden
        }

        #fullscreen-overlay::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(67, 56, 202, .04) 1px, transparent 1px), linear-gradient(90deg, rgba(67, 56, 202, .04) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none
        }

        #fullscreen-overlay::after {
            content: '';
            position: absolute;
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, rgba(67, 56, 202, .12) 0%, transparent 70%);
            pointer-events: none
        }

        .fs-header {
            text-align: center;
            position: relative;
            z-index: 1
        }

        .fs-date {
            color: #64748b;
            font-size: .82rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            margin-bottom: 6px
        }

        .fs-title {
            color: #f8fafc;
            font-size: clamp(1.3rem, 3vw, 2rem);
            font-weight: 800;
            letter-spacing: -.02em;
            line-height: 1.2
        }

        .fs-lokasi {
            color: #475569;
            font-size: .82rem;
            margin-top: 5px
        }

        .fs-qr-box {
            background: #fff;
            padding: 22px;
            border-radius: 20px;
            box-shadow: 0 0 80px rgba(67, 56, 202, .2), 0 0 0 1px rgba(255, 255, 255, .05);
            position: relative;
            z-index: 1
        }

        .fs-qr-box::before,
        .fs-qr-box::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            border-color: #4338ca;
            border-style: solid
        }

        .fs-qr-box::before {
            top: -4px;
            left: -4px;
            border-width: 3px 0 0 3px;
            border-radius: 4px 0 0 0
        }

        .fs-qr-box::after {
            bottom: -4px;
            right: -4px;
            border-width: 0 3px 3px 0;
            border-radius: 0 0 4px 0
        }

        .fs-timer-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #0f172a;
            border: 1px solid #1e293b;
            padding: 12px 28px;
            border-radius: 14px;
            position: relative;
            z-index: 1
        }

        .fs-timer-label {
            color: #64748b;
            font-size: .82rem
        }

        .fs-timer-val {
            color: #f8fafc;
            font-size: 2rem;
            font-weight: 900;
            min-width: 2.4ch;
            text-align: center;
            line-height: 1
        }

        .fs-timer-bar-wrap {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #1e293b;
            border-radius: 0 0 14px 14px;
            overflow: hidden
        }

        .fs-timer-bar {
            height: 100%;
            background: linear-gradient(90deg, #4338ca, #6366f1);
            transition: width 1s linear
        }

        .fs-barcode-text {
            color: #334155;
            font-family: 'Courier New', monospace;
            font-size: .72rem;
            background: #0f172a;
            border: 1px solid #1e293b;
            padding: 8px 20px;
            border-radius: 8px;
            letter-spacing: .04em;
            position: relative;
            z-index: 1
        }

        .fs-badges {
            display: flex;
            gap: 10px;
            position: relative;
            z-index: 1
        }

        .fs-badge {
            padding: 6px 18px;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .02em
        }

        .fs-badge-masuk {
            background: rgba(22, 163, 74, .15);
            color: #86efac;
            border: 1px solid rgba(22, 163, 74, .25)
        }

        .fs-badge-pulang {
            background: rgba(14, 165, 233, .15);
            color: #7dd3fc;
            border: 1px solid rgba(14, 165, 233, .25)
        }

        .fs-sse-dot {
            position: fixed;
            top: 18px;
            left: 18px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #16a34a;
            z-index: 10001;
            animation: ssePulse 1.5s infinite
        }

        .fs-sse-dot.disconnected {
            background: #ef4444;
            animation: none
        }

        #exitFullscreenBtn {
            position: fixed;
            top: 18px;
            right: 18px;
            background: #0f172a;
            color: #64748b;
            border: 1px solid #1e293b;
            padding: 9px 16px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: .82rem;
            display: flex;
            align-items: center;
            gap: 7px;
            z-index: 10001;
            font-family: inherit
        }

        #exitFullscreenBtn:hover {
            background: #1e293b;
            color: #94a3b8
        }

        .fs-esc-hint {
            position: fixed;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            color: #1e293b;
            font-size: .72rem;
            z-index: 10001;
            white-space: nowrap
        }

        .fs-esc-hint kbd {
            background: #0f172a;
            border: 1px solid #1e293b;
            padding: 2px 7px;
            border-radius: 4px;
            color: #334155;
            font-family: inherit
        }

        @keyframes qrPulse {
            0% {
                box-shadow: 0 0 80px rgba(67, 56, 202, .2), 0 0 0 1px rgba(255, 255, 255, .05)
            }

            50% {
                box-shadow: 0 0 120px rgba(67, 56, 202, .5), 0 0 0 1px rgba(67, 56, 202, .2)
            }

            100% {
                box-shadow: 0 0 80px rgba(67, 56, 202, .2), 0 0 0 1px rgba(255, 255, 255, .05)
            }
        }

        .qr-pulse {
            animation: qrPulse .6s ease-out
        }

        /* action bar */
        .action-bar {
            position: fixed;
            bottom: var(--footer-h, 0);
            left: 0;
            right: 0;
            padding: 10px 12px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 6px;
            z-index: 999;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
            flex-wrap: wrap
        }

        @media(min-width:768px) {
            .action-bar {
                padding: 10px 24px 12px;
                gap: 10px;
                justify-content: flex-end;
                flex-wrap: nowrap
            }
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 11px 12px;
            border-radius: 12px;
            font-size: .78rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            line-height: 1;
            white-space: nowrap;
            min-width: 60px
        }

        @media(min-width:768px) {
            .ab-btn {
                flex: unset;
                min-width: 100px;
                font-size: .84rem;
                padding: 12px 16px
            }
        }

        .ab-btn:active {
            transform: scale(.97)
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            flex: 0 0 auto;
            padding: 11px 14px
        }

        .ab-btn-primary {
            background: #4338ca;
            color: #fff;
            box-shadow: 0 3px 10px rgba(67, 56, 202, .3)
        }

        .ab-btn-scan {
            background: #16a34a;
            color: #fff;
            box-shadow: 0 3px 10px rgba(22, 163, 74, .3)
        }

        .ab-btn-done {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #86efac;
            cursor: default
        }
    </style>
@endpush

@section('content')
    @php
        $isAdmin = auth()
            ->user()
            ->hasAnyRole(['superadmin', 'admin_tatib', 'bk']);
        $myGtk = auth()->user()->gtk ?? null;
        $myGtkId = $myGtk?->id;
        $isGuru = $myGtkId !== null;
        $myAbsen = $myGtkId ? $eventGuru->absenEventGuru->where('gtk_id', $myGtkId) : collect();
        $myMasuk = $myAbsen->where('jenis', 'masuk')->isNotEmpty();
        $myPulang = $myAbsen->where('jenis', 'pulang')->isNotEmpty();
        $doneGuru = ($myMasuk && !$eventGuru->ada_absen_pulang) || $myPulang;
        $nextJenis = $myMasuk && $eventGuru->ada_absen_pulang ? 'pulang' : 'masuk';
        $showScan = $isGuru && $eventGuru->isActive() && !$doneGuru;

        $masukCount = $eventGuru->absenEventGuru->where('jenis', 'masuk')->unique('gtk_id')->count();
        $pulangCount = $eventGuru->absenEventGuru->where('jenis', 'pulang')->unique('gtk_id')->count();
        $totalScan = $eventGuru->absenEventGuru->count();
        $totalGuru = \App\Models\GTK::where('status_aktif', true)->count();

        $absenTerbaru = $eventGuru->absenEventGuru()->with('gtk')->latest('waktu_scan')->take(10)->get();
    @endphp

    <div class="ev-wrap" style="padding-top:var(--header-h,56px);padding-bottom:calc(var(--footer-h,0px) + 88px)">

        {{-- Page Strip --}}
        <div class="page-strip {{ $eventGuru->isActive() ? '' : 'page-strip-orange' }}"
            style="{{ $eventGuru->isActive() ? 'background:linear-gradient(135deg,#1e40af 0%,#4338ca 100%)' : '' }}">
            <div class="live-badge"><span
                    class="live-dot"></span>{{ $eventGuru->tanggal_mulai->translatedFormat('l, d F Y') }}</div>
            <h2><i class="fas fa-chalkboard-teacher"></i> {{ Str::limit($eventGuru->nama_event, 35) }}</h2>
            <p>{{ $eventGuru->isActive() ? 'Event sedang berlangsung' : 'Event telah selesai' }}</p>
        </div>

        @if (session('success'))
            <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        {{-- Stats --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value" style="color:#0ea5e9">{{ $masukCount }}</div>
                <div class="stat-label">Masuk</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#f59e0b">{{ $pulangCount }}</div>
                <div class="stat-label">Pulang</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#16a34a">{{ $totalScan }}</div>
                <div class="stat-label">Total Scan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#4338ca">{{ $totalGuru }}</div>
                <div class="stat-label">Total Guru</div>
            </div>
        </div>

        {{-- Status Absen Saya --}}
        @if ($isGuru && $myGtkId)
            <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:12px">
                <div class="c-head">
                    <div class="c-icon" style="background:#e0e7ff;flex-shrink:0"><i class="fas fa-user-check"
                            style="color:#4338ca"></i></div>
                    <h3>Status Absen Saya</h3>
                    <span class="hbadge"
                        style="background:{{ $doneGuru ? '#dcfce7' : '#fef9c3' }};color:{{ $doneGuru ? '#15803d' : '#b45309' }}">{{ $doneGuru ? 'Selesai' : 'Belum Lengkap' }}</span>
                </div>
                <div class="c-body" style="padding:12px 16px">
                    <div style="display:flex;gap:10px;flex-wrap:wrap">
                        @if ($eventGuru->ada_absen_masuk)
                            <div class="s-chip">
                                <div class="ci {{ $myMasuk ? 'ci-g' : '' }}"><i
                                        class="fas fa-{{ $myMasuk ? 'check' : 'times' }}"></i></div>
                                <div>
                                    <div class="c-lbl">Masuk</div>
                                    <div class="c-val">
                                        {{ $myMasuk ? $myAbsen->firstWhere('jenis', 'masuk')->waktu_scan->format('H:i') : 'Belum scan' }}
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($eventGuru->ada_absen_pulang)
                            <div class="s-chip">
                                <div class="ci {{ $myPulang ? 'ci-g' : '' }}"><i
                                        class="fas fa-{{ $myPulang ? 'check' : 'times' }}"></i></div>
                                <div>
                                    <div class="c-lbl">Pulang</div>
                                    <div class="c-val">
                                        {{ $myPulang ? $myAbsen->firstWhere('jenis', 'pulang')->waktu_scan->format('H:i') : 'Belum scan' }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Detail Event --}}
        <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:12px">
            <div class="c-head">
                <div class="c-icon" style="background:#e0e7ff;flex-shrink:0"><i class="fas fa-info-circle"
                        style="color:#4338ca"></i></div>
                <h3>Detail Event</h3>
                <span
                    class="hbadge {{ $eventGuru->isActive() ? 'badge-ev-active' : 'badge-ev-ended' }}">{{ $eventGuru->isActive() ? 'Aktif' : 'Selesai' }}</span>
            </div>
            <div class="c-body" style="padding:12px 18px">
                <div class="detail-row"><span class="dr-label"><i class="fas fa-align-left" style="width:14px"></i>
                        Deskripsi</span><span class="dr-value">{{ $eventGuru->deskripsi ?? '-' }}</span></div>
                <div class="detail-row"><span class="dr-label"><i class="fas fa-clock" style="width:14px"></i>
                        Waktu</span><span class="dr-value">{{ $eventGuru->tanggal_mulai->format('d M H:i') }} –
                        {{ $eventGuru->tanggal_selesai->format('d M H:i') }}</span></div>
                <div class="detail-row"><span class="dr-label"><i class="fas fa-map-marker-alt" style="width:14px"></i>
                        Lokasi</span><span class="dr-value">{{ $eventGuru->lokasi ?? '-' }}</span></div>
                <div class="detail-row"><span class="dr-label"><i class="fas fa-sync-alt" style="width:14px"></i>
                        Barcode</span><span
                        class="dr-value">{{ $eventGuru->barcode_rotate_detik > 0 ? 'Rotate setiap ' . $eventGuru->barcode_rotate_detik . ' detik' : 'Statis' }}</span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-clipboard-check" style="width:14px"></i> Tipe Absen</span>
                    <span class="dr-value" style="display:flex;gap:6px;justify-content:flex-end">
                        @if ($eventGuru->ada_absen_masuk)
                            <span style="color:#16a34a"><i class="fas fa-check"></i> Masuk</span>
                        @endif
                        @if ($eventGuru->ada_absen_pulang)
                            <span style="color:#0ea5e9"><i class="fas fa-check"></i> Pulang</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- QR Code (admin saat aktif) --}}
        @if ($eventGuru->isActive() && $isAdmin)
            <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:12px">
                <div class="c-head">
                    <div class="c-icon" style="background:#dcfce7;flex-shrink:0"><i class="fas fa-qrcode"></i></div>
                    <h3>QR Code Scan</h3>
                    @if ($eventGuru->barcode_rotate_detik > 0)
                        <span class="sse-status sse-connecting" id="sseStatusBadge">
                            <span class="sse-status-dot"></span><span id="sseStatusText">Menghubungkan...</span>
                        </span>
                    @endif
                </div>
                <div class="c-body" style="padding:14px 16px;text-align:center">
                    <div id="qrcode-wrap"></div>
                    @if ($eventGuru->barcode_rotate_detik > 0)
                        <div class="rotate-timer">Berganti dalam <span class="timer-val"
                                id="rotateTimer">{{ $eventGuru->barcode_rotate_detik }}</span> detik</div>
                    @endif
                    <div class="barcode-box" id="barcodeText">{{ substr($eventGuru->barcode_value, 0, 32) }}...</div>
                </div>
            </div>
        @endif

        {{-- Absen Terbaru --}}
        @if ($absenTerbaru->isNotEmpty())
            <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:12px">
                <div class="c-head">
                    <div class="c-icon" style="background:#fef3c7;flex-shrink:0"><i class="fas fa-list"></i></div>
                    <h3>Absen Terbaru</h3>
                    <span class="hbadge">{{ $absenTerbaru->count() }} data</span>
                </div>
                {{-- TABLE desktop --}}
                <div class="ev-table-wrap">
                    <table class="ev-table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Guru</th>
                                <th>Jenis</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($absenTerbaru as $absen)
                                <tr>
                                    <td style="color:#94a3b8;font-size:.72rem">{{ $absen->gtk->kd_guru ?? '-' }}</td>
                                    <td style="font-weight:600">{{ Str::limit($absen->gtk->nama_lengkap ?? '-', 22) }}</td>
                                    <td><span
                                            class="{{ $absen->jenis === 'masuk' ? 'badge-masuk-g' : 'badge-pulang-g' }}">{{ $absen->jenis === 'masuk' ? 'Masuk' : 'Pulang' }}</span>
                                    </td>
                                    <td style="font-size:.78rem;white-space:nowrap">
                                        {{ $absen->waktu_scan->format('H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- CARD LIST mobile --}}
                <div class="ev-card-list">
                    @foreach ($absenTerbaru as $absen)
                        <div class="evi">
                            <div class="evi-top">
                                <span class="evi-name">{{ Str::limit($absen->gtk->nama_lengkap ?? '-', 26) }}</span>
                                <span
                                    class="{{ $absen->jenis === 'masuk' ? 'badge-masuk-g' : 'badge-pulang-g' }}">{{ $absen->jenis === 'masuk' ? 'Masuk' : 'Pulang' }}</span>
                            </div>
                            <div class="evi-mid">
                                @if ($absen->gtk->kd_guru)
                                    <span class="evi-chip">{{ $absen->gtk->kd_guru }}</span>
                                @endif
                                <span class="evi-chip"><i class="fas fa-clock"></i>
                                    {{ $absen->waktu_scan->format('H:i') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>{{-- ev-wrap --}}

    {{-- Fullscreen Overlay --}}
    @if ($eventGuru->isActive() && $isAdmin)
        <div id="fullscreen-overlay">
            <div class="fs-sse-dot" id="fsSseDot"></div>
            <button id="exitFullscreenBtn"><i class="fas fa-compress"></i> Keluar Full Screen</button>
            <div class="fs-header">
                <div class="fs-date"><i class="fas fa-calendar-day"
                        style="margin-right:5px"></i>{{ $eventGuru->tanggal_mulai->translatedFormat('l, d F Y') }}</div>
                <div class="fs-title">{{ $eventGuru->nama_event }}</div>
                @if ($eventGuru->lokasi)
                    <div class="fs-lokasi"><i class="fas fa-map-marker-alt"
                            style="margin-right:4px"></i>{{ $eventGuru->lokasi }}</div>
                @endif
            </div>
            <div class="fs-qr-box" id="fs-qr-box">
                <div id="fullscreen-qr"></div>
            </div>
            @if ($eventGuru->barcode_rotate_detik > 0)
                <div class="fs-timer-wrap">
                    <i class="fas fa-sync-alt" style="color:#334155;font-size:.9rem"></i>
                    <span class="fs-timer-label">Berganti dalam</span>
                    <span class="fs-timer-val" id="fsTimerVal">{{ $eventGuru->barcode_rotate_detik }}</span>
                    <span class="fs-timer-label">detik</span>
                    <div class="fs-timer-bar-wrap">
                        <div class="fs-timer-bar" id="fsTimerBar" style="width:100%"></div>
                    </div>
                </div>
            @endif
            <div class="fs-barcode-text" id="fsBarcodeText">
                {{ substr($eventGuru->barcode_value, 0, 48) }}{{ strlen($eventGuru->barcode_value) > 48 ? '…' : '' }}</div>
            <div class="fs-badges">
                @if ($eventGuru->ada_absen_masuk)
                    <span class="fs-badge fs-badge-masuk"><i class="fas fa-sign-in-alt" style="margin-right:5px"></i>Scan
                        Masuk</span>
                @endif
                @if ($eventGuru->ada_absen_pulang)
                    <span class="fs-badge fs-badge-pulang"><i class="fas fa-sign-out-alt"
                            style="margin-right:5px"></i>Scan Pulang</span>
                @endif
            </div>
            <div class="fs-esc-hint">Tekan <kbd>Esc</kbd> untuk keluar</div>
        </div>
    @endif

    {{-- Action Bar --}}
    <div class="action-bar">
        <a href="{{ route('event-guru.index') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
        @if ($isAdmin)
            <a href="{{ route('event-guru.edit', $eventGuru) }}" class="ab-btn ab-btn-primary"
                style="background:#b45309;box-shadow:0 3px 10px rgba(180,83,9,.3)"><i class="fas fa-pen"></i> Edit</a>
            <a href="{{ route('event-guru.rekap', $eventGuru) }}" class="ab-btn ab-btn-primary"><i
                    class="fas fa-table"></i> Rekap</a>
            <a href="{{ route('event-guru.export', $eventGuru) }}" class="ab-btn ab-btn-primary"
                style="background:#15803d;box-shadow:0 3px 10px rgba(21,128,61,.3)"><i class="fas fa-file-excel"></i></a>
            @if ($eventGuru->isActive())
                <button id="fullscreenBtn" class="ab-btn ab-btn-primary"><i class="fas fa-expand"></i></button>
            @endif
        @endif
        @if ($showScan)
            <a href="{{ route('event-guru.scan', ['eventGuru' => $eventGuru, 'jenis' => $nextJenis]) }}"
                class="ab-btn ab-btn-scan"><i class="fas fa-qrcode"></i>
                {{ $nextJenis === 'masuk' ? 'Scan Masuk' : 'Scan Pulang' }}</a>
        @elseif($isGuru && $doneGuru)
            <span class="ab-btn ab-btn-done"><i class="fas fa-check-circle"></i> Absen Lengkap</span>
        @endif
        @if ($isGuru)
            <a href="{{ route('event-guru.rekap', $eventGuru) }}" class="ab-btn"
                style="background:#f0fdf4;color:#15803d;border:1px solid #86efac"><i class="fas fa-table"></i></a>
        @endif
    </div>
@endsection

@if ($eventGuru->isActive() && $isAdmin)
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#4338ca',
                    timer: 4000,
                    timerProgressBar: true
                });
            @endif
            (function() {
                const ROTATE_DETIK = {{ $eventGuru->barcode_rotate_detik }};
                const UPDATE_URL = "{{ route('event-guru.updateBarcode', $eventGuru) }}";
                const POLL_URL = "{{ route('event-guru.barcode', $eventGuru) }}";
                const CSRF = "{{ csrf_token() }}";
                const IS_ROTATING = ROTATE_DETIK > 0;
                const UPDATED_AT_MS =
                    {{ $eventGuru->barcode_updated_at ? $eventGuru->barcode_updated_at->valueOf() : 'Date.now()' }};
                let currentBarcode = @json($eventGuru->barcode_value);
                let countdown = IS_ROTATING ? Math.max(0, ROTATE_DETIK - Math.floor((Date.now() - UPDATED_AT_MS) / 1000)) :
                    0;
                let fsOpen = false,
                    isRotating = false,
                    mainInterval = null,
                    pollInterval = null;
                const $el = id => document.getElementById(id);
                const iW = $el('qrcode-wrap'),
                    iTEl = $el('rotateTimer'),
                    iTxt = $el('barcodeText');
                const ssB = $el('sseStatusBadge'),
                    ssT = $el('sseStatusText');
                const overlay = $el('fullscreen-overlay'),
                    fsQrW = $el('fullscreen-qr'),
                    fsQrB = $el('fs-qr-box');
                const fsTV = $el('fsTimerVal'),
                    fsTB = $el('fsTimerBar'),
                    fsBT = $el('fsBarcodeText');
                const fsDot = $el('fsSseDot'),
                    fsBtn = $el('fullscreenBtn'),
                    exitBtn = $el('exitFullscreenBtn');

                function mkQR(c, v, s) {
                    c.innerHTML = '';
                    new QRCode(c, {
                        text: v,
                        width: s,
                        height: s,
                        colorDark: '#0f172a',
                        colorLight: '#fff',
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }

                function gI(v) {
                    if (iW) mkQR(iW, v, 220);
                    if (iTxt) iTxt.textContent = v.substring(0, 32) + (v.length > 32 ? '…' : '');
                }

                function gF(v) {
                    if (!fsQrW) return;
                    mkQR(fsQrW, v, 360);
                    if (fsQrB) {
                        fsQrB.classList.remove('qr-pulse');
                        void fsQrB.offsetWidth;
                        fsQrB.classList.add('qr-pulse');
                    }
                    if (fsBT) fsBT.textContent = v.substring(0, 48) + (v.length > 48 ? '…' : '');
                }

                function setSt(s) {
                    if (ssB) {
                        ssB.className = 'sse-status sse-' + s;
                        const L = {
                            connected: 'Live',
                            connecting: 'Menghubungkan...',
                            disconnected: 'Terputus'
                        };
                        if (ssT) ssT.textContent = L[s] || s;
                    }
                    if (fsDot) fsDot.className = 'fs-sse-dot' + (s === 'connected' ? '' : ' disconnected');
                }

                function applyNew(v, ms) {
                    if (!v || v === currentBarcode) return;
                    currentBarcode = v;
                    if (IS_ROTATING && ms) countdown = Math.max(0, ROTATE_DETIK - Math.floor((Date.now() - ms) / 1000));
                    gI(currentBarcode);
                    if (fsOpen) gF(currentBarcode);
                }

                function reqRot() {
                    if (isRotating) return;
                    isRotating = true;
                    fetch(UPDATE_URL, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'Content-Type': 'application/json'
                        }
                    }).then(r => r.json()).then(d => {
                        setSt('connected');
                        applyNew(d.barcode_value, d.updated_at_ms);
                    }).catch(() => setSt('disconnected')).finally(() => {
                        isRotating = false;
                    });
                }

                function poll() {
                    fetch(POLL_URL, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF
                        }
                    }).then(r => r.json()).then(d => {
                        setSt('connected');
                        if (d.barcode_value && d.barcode_value !== currentBarcode) {
                            const ms = d.barcode_updated_at ? new Date(d.barcode_updated_at).getTime() : null;
                            applyNew(d.barcode_value, ms);
                        }
                    }).catch(() => setSt('disconnected'));
                }

                function updateFsBar() {
                    if (!fsTB || !IS_ROTATING) return;
                    const p = Math.min(100, (countdown / ROTATE_DETIK) * 100);
                    fsTB.style.width = p + '%';
                    fsTB.style.background = p > 50 ? 'linear-gradient(90deg,#4338ca,#6366f1)' : p > 25 ?
                        'linear-gradient(90deg,#f59e0b,#4338ca)' : 'linear-gradient(90deg,#ef4444,#f59e0b)';
                }

                function startInt() {
                    mainInterval = setInterval(function() {
                        if (!IS_ROTATING) return;
                        if (iTEl) iTEl.textContent = Math.max(0, countdown);
                        if (fsOpen && fsTV) fsTV.textContent = Math.max(0, countdown);
                        updateFsBar();
                        if (countdown <= 0) {
                            reqRot();
                            countdown = ROTATE_DETIK;
                        } else countdown--;
                    }, 1000);
                }

                function startPoll() {
                    setSt('connecting');
                    poll();
                    const ms = IS_ROTATING ? Math.max(3000, ROTATE_DETIK * 1000) : 10000;
                    pollInterval = setInterval(poll, ms);
                }

                function openFs() {
                    if (!overlay) return;
                    overlay.style.display = 'flex';
                    fsOpen = true;
                    gF(currentBarcode);
                    if (fsTV) fsTV.textContent = Math.max(0, countdown);
                    updateFsBar();
                }

                function closeFs() {
                    if (!overlay) return;
                    overlay.style.display = 'none';
                    fsOpen = false;
                }
                if (fsBtn) fsBtn.addEventListener('click', e => {
                    e.preventDefault();
                    openFs();
                });
                if (exitBtn) exitBtn.addEventListener('click', closeFs);
                document.addEventListener('keydown', e => {
                    if (e.key === 'Escape' && fsOpen) closeFs();
                });
                window.addEventListener('beforeunload', () => {
                    clearInterval(mainInterval);
                    clearInterval(pollInterval);
                });
                gI(currentBarcode);
                startInt();
                startPoll();
                document.addEventListener('DOMContentLoaded', function() {
                    var h = document.querySelector('.header-auto-show');
                    if (h) h.classList.add('header-active');
                });
            })();
        </script>
    @endpush
@else
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var h = document.querySelector('.header-auto-show');
                if (h) h.classList.add('header-active');
            });
        </script>
    @endpush
@endif
