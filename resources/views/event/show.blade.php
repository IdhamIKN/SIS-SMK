@extends('layouts.app')
@section('title', $event->nama_event)
@php use Illuminate\Support\Facades\Storage; @endphp
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

        /* detail rows */
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
            word-break: break-word;
            max-width: 60%
        }

        /* table */
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

        /* card list mobile */
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

        /* badges */
        .badge-masuk {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700
        }

        .badge-pulang {
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

        .badge-attend {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 800;
            white-space: nowrap
        }

        .badge-attend-ok {
            background: #dcfce7;
            color: #15803d
        }

        .badge-attend-no {
            background: #fee2e2;
            color: #b91c1c
        }

        .point-toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
            padding: 10px 14px;
            border-bottom: 1px solid #eef2f7;
            background: #f8fafc
        }

        .point-select-all {
            width: 16px;
            height: 16px;
            accent-color: #dc2626
        }

        .point-select-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: .76rem;
            font-weight: 700;
            color: #475569
        }

        .point-delete-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: none;
            border-radius: 8px;
            background: #dc2626;
            color: #fff;
            font-size: .76rem;
            font-weight: 800;
            padding: 8px 11px;
            cursor: pointer
        }

        .point-delete-btn:disabled {
            background: #cbd5e1;
            color: #64748b;
            cursor: not-allowed
        }

        /* bulk toolbar */
        .bulk-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
            padding: 10px 14px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc
        }

        .bulk-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            border-radius: 8px;
            font-size: .76rem;
            font-weight: 800;
            padding: 8px 13px;
            cursor: pointer;
            transition: all .15s
        }

        .bulk-btn:disabled {
            background: #e2e8f0 !important;
            color: #94a3b8 !important;
            cursor: not-allowed
        }

        .bulk-btn-red {
            background: #dc2626;
            color: #fff
        }

        .bulk-btn-red:not(:disabled):hover {
            background: #b91c1c
        }

        .bulk-btn-indigo {
            background: #7c3aed;
            color: #fff
        }

        .bulk-btn-indigo:not(:disabled):hover {
            background: #6d28d9
        }

        .point-stack {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 190px
        }

        .point-item {
            display: flex;
            align-items: flex-start;
            gap: 7px;
            padding: 7px 8px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #fff
        }

        .point-item-red {
            border-color: #fecdd3;
            background: #fff1f2
        }

        .point-item-green {
            border-color: #bbf7d0;
            background: #ecfdf5
        }

        .point-item-empty {
            color: #94a3b8;
            font-size: .72rem
        }

        .point-check {
            width: 15px;
            height: 15px;
            accent-color: #dc2626;
            flex: 0 0 auto;
            margin-top: 2px
        }

        .point-title {
            font-size: .74rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25
        }

        .point-meta {
            font-size: .68rem;
            color: #64748b;
            margin-top: 2px;
            line-height: 1.3
        }

        .point-red {
            color: #be123c
        }

        .point-green {
            color: #047857
        }

        .ev-table td.point-cell {
            vertical-align: top
        }

        /* barcode */
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
            color: #f59e0b
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

        /* foto grid */
        .foto-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px
        }

        @media(min-width:640px) {
            .foto-grid {
                grid-template-columns: repeat(4, 1fr)
            }
        }

        @media(min-width:768px) {
            .foto-grid {
                grid-template-columns: repeat(5, 1fr)
            }
        }

        .foto-item {
            border-radius: 8px;
            overflow: hidden;
            aspect-ratio: 1;
            background: #f1f5f9;
            display: block
        }

        .foto-item img {
            width: 100%;
            height: 100%;
            object-fit: cover
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
                min-width: 110px;
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
            background: #f59e0b;
            color: #fff;
            box-shadow: 0 3px 10px rgba(245, 158, 11, .3)
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

        /* fullscreen overlay */
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
            background-image: linear-gradient(rgba(99, 102, 241, .04) 1px, transparent 1px), linear-gradient(90deg, rgba(99, 102, 241, .04) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none
        }

        #fullscreen-overlay::after {
            content: '';
            position: absolute;
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, rgba(99, 102, 241, .12) 0%, transparent 70%);
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
            box-shadow: 0 0 80px rgba(99, 102, 241, .2), 0 0 0 1px rgba(255, 255, 255, .05);
            position: relative;
            z-index: 1
        }

        .fs-qr-box::before,
        .fs-qr-box::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            border-color: #6366f1;
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
            font-variant-numeric: tabular-nums;
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
            background: linear-gradient(90deg, #6366f1, #0ea5e9);
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
            transition: all .18s;
            font-family: inherit
        }

        #exitFullscreenBtn:hover {
            background: #1e293b;
            color: #94a3b8;
            border-color: #334155
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
                box-shadow: 0 0 80px rgba(99, 102, 241, .2), 0 0 0 1px rgba(255, 255, 255, .05)
            }

            50% {
                box-shadow: 0 0 120px rgba(99, 102, 241, .5), 0 0 0 1px rgba(99, 102, 241, .2)
            }

            100% {
                box-shadow: 0 0 80px rgba(99, 102, 241, .2), 0 0 0 1px rgba(255, 255, 255, .05)
            }
        }

        .qr-pulse {
            animation: qrPulse .6s ease-out
        }
    </style>
@endpush

@section('content')
    @php
        $user = auth()->user();
        $isSiswa = $user->hasRole('siswa');
        $siswaId = $isSiswa ? $user->siswa->id ?? null : null;
        $canViewAll =
            !$isSiswa &&
            ($user->hasAnyRole(['superadmin', 'kepsek', 'waka', 'kurikulum', 'admin_tatib', 'bk']) ||
                $user->hasPermissionTo('view_all_events'));
        $isMyEvent = !$isSiswa && $event->created_by === $user->id;
        $canEdit = !$isSiswa && ($canViewAll || $isMyEvent);
        $canManageEventPoints =
            $canEdit &&
            ($eventAttendanceSummary['pelanggaran'] ?? 0) + ($eventAttendanceSummary['penghargaan'] ?? 0) > 0;

        $masukCount = $event
            ->absenEvent()
            ->whereNotNull('waktu_masuk')
            ->when($isSiswa && $siswaId, fn($q) => $q->where('siswa_id', $siswaId))
            ->count();
        $pulangCount = $event
            ->absenEvent()
            ->whereNotNull('waktu_pulang')
            ->when($isSiswa && $siswaId, fn($q) => $q->where('siswa_id', $siswaId))
            ->count();
        $hadirCount = $eventAttendanceSummary['hadir'] ?? 0;
        $tidakHadirCount = $eventAttendanceSummary['tidak_hadir'] ?? 0;

        // Status absen siswa
        $sudahMasuk = $sudahPulang = $showScan = false;
        $nextJenis = null;
        if ($isSiswa && $siswaId && $event->isActive()) {
            $reko = $event->absenEvent()->where('siswa_id', $siswaId)->first();
            $sudahMasuk = $reko && $reko->waktu_masuk !== null;
            $sudahPulang = $reko && $reko->waktu_pulang !== null;
            if (!$sudahMasuk && $event->ada_absen_masuk) {
                $nextJenis = 'masuk';
                $showScan = true;
            } elseif ($sudahMasuk && !$sudahPulang && $event->ada_absen_pulang) {
                $nextJenis = 'pulang';
                $showScan = true;
            }
        }
        $eventPhotos = $event->photos;
    @endphp

    <div class="ev-wrap" style="padding-top:var(--header-h,56px);padding-bottom:calc(var(--footer-h,0px) + 88px)">

        {{-- Page Strip --}}
        <div class="page-strip {{ $event->isActive() ? 'page-strip-event' : 'page-strip-orange' }}">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ $event->tanggal_mulai->translatedFormat('l, d F Y') }}
            </div>
            <h2><i class="fas fa-calendar-day"></i> {{ Str::limit($event->nama_event, 35) }}</h2>
            <p>{{ $event->isActive() ? 'Event sedang berlangsung' : 'Event telah selesai' }}
                @if ($event->is_ekstrakurikuler)
                    &bull; <i class="fas fa-futbol"></i> Ekstrakurikuler
                @endif
            </p>
        </div>

        @if (session('success'))
            <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert a-err"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        {{-- Stats --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value" style="color:#0ea5e9">{{ $masukCount }}</div>
                <div class="stat-label">Absen Masuk</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#f59e0b">{{ $pulangCount }}</div>
                <div class="stat-label">Absen Pulang</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#16a34a">{{ $hadirCount }}</div>
                <div class="stat-label">Hadir</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#dc2626">{{ $tidakHadirCount }}</div>
                <div class="stat-label">Tidak Hadir</div>
            </div>
        </div>

        {{-- Detail Event --}}
        <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:12px">
            <div class="c-head">
                <div class="c-icon" style="background:#fef3c7;flex-shrink:0"><i class="fas fa-info-circle"></i></div>
                <h3>Detail Event</h3>
                <span class="hbadge {{ $event->isActive() ? 'badge-ev-active' : 'badge-ev-ended' }}">
                    {{ $event->isActive() ? 'Aktif' : 'Selesai' }}
                </span>
            </div>
            <div class="c-body" style="padding:12px 18px">
                @if ($canViewAll && $event->creator)
                    <div class="detail-row">
                        <span class="dr-label"><i class="fas fa-user-edit" style="width:14px"></i> Dibuat oleh</span>
                        <span class="dr-value" style="color:#7c3aed">{{ $event->creator->name ?? '-' }}@if ($isMyEvent)
                                <span style="color:#94a3b8;font-weight:400">(Saya)</span>
                            @endif
                        </span>
                    </div>
                @endif
                @if ($event->recurrence_parent_id && $event->recurringParent)
                    <div class="detail-row">
                        <span class="dr-label"><i class="fas fa-sync-alt" style="width:14px"></i> Bagian dari seri</span>
                        <span class="dr-value"><a href="{{ route('event.show', $event->recurringParent) }}"
                                style="color:#0ea5e9;text-decoration:none">Lihat Master</a> <span
                                style="color:#94a3b8;font-size:.75rem;display:block">Ke-{{ $event->recurrence_sequence }}</span></span>
                    </div>
                @elseif($event->recurrenceRule && !$event->recurrence_parent_id)
                    <div class="detail-row">
                        <span class="dr-label"><i class="fas fa-sync-alt" style="width:14px"></i> Pengulangan</span>
                        <span class="dr-value" style="color:#0369a1">Master · {{ $event->recurringChildren->count() }}
                            kemunculan</span>
                    </div>
                @endif
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-align-left" style="width:14px"></i> Deskripsi</span>
                    <span class="dr-value">{{ $event->deskripsi ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-clock" style="width:14px"></i> Waktu</span>
                    <span class="dr-value">{{ $event->tanggal_mulai->format('d M H:i') }} –
                        {{ $event->tanggal_selesai->format('d M H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-map-marker-alt" style="width:14px"></i> Lokasi</span>
                    <span class="dr-value">{{ $event->lokasi ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-users" style="width:14px"></i> Peserta</span>
                    <span class="dr-value">
                        @if ($event->berlaku_untuk_semua)
                            Semua kelas
                        @elseif($event->mode_peserta === 'kelas')
                            {{ $event->kelas->count() }} kelas
                        @else
                            {{ $event->siswa->count() }} siswa
                        @endif
                    </span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-sync-alt" style="width:14px"></i> Barcode</span>
                    <span
                        class="dr-value">{{ $event->barcode_rotate_detik > 0 ? 'Rotate setiap ' . $event->barcode_rotate_detik . ' detik' : 'Statis' }}</span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-clipboard-check" style="width:14px"></i> Tipe Absen</span>
                    <span class="dr-value" style="display:flex;gap:6px;justify-content:flex-end">
                        @if ($event->ada_absen_masuk)
                            <span style="color:#16a34a"><i class="fas fa-check"></i> Masuk</span>
                        @endif
                        @if ($event->ada_absen_pulang)
                            <span style="color:#0ea5e9"><i class="fas fa-check"></i> Pulang</span>
                        @endif
                    </span>
                </div>
                {{-- Ekstrakurikuler info --}}
                @if ($event->is_ekstrakurikuler)
                    <div class="detail-row">
                        <span class="dr-label"><i class="fas fa-futbol" style="width:14px"></i> Jenis</span>
                        <span class="dr-value"><span
                                style="background:#fce7f3;color:#be185d;padding:2px 10px;border-radius:20px;font-size:.75rem;font-weight:700"><i
                                    class="fas fa-futbol"></i> Ekstrakurikuler</span></span>
                    </div>
                    @if ($event->pelatih_1 || $event->pelatih_2 || $event->pelatih_3)
                        <div class="detail-row">
                            <span class="dr-label"><i class="fas fa-user-tie" style="width:14px"></i> Pelatih</span>
                            <span class="dr-value">
                                @if ($event->pelatih_1)
                                    <div>{{ $event->pelatih_1 }}</div>
                                @endif
                                @if ($event->pelatih_2)
                                    <div>{{ $event->pelatih_2 }}</div>
                                @endif
                                @if ($event->pelatih_3)
                                    <div>{{ $event->pelatih_3 }}</div>
                                @endif
                            </span>
                        </div>
                    @endif
                    @if ($event->pembina_nama)
                        <div class="detail-row">
                            <span class="dr-label"><i class="fas fa-user-shield" style="width:14px"></i> Pembina</span>
                            <span class="dr-value">{{ $event->pembina_nama }}@if ($event->pembina_nip)
                                    <span style="color:#94a3b8;font-size:.75rem;display:block">NIP.
                                        {{ $event->pembina_nip }}</span>
                                @endif
                            </span>
                        </div>
                    @endif
                @endif
                {{-- Status siswa --}}
                @if ($isSiswa && $siswaId)
                    <div class="detail-row">
                        <span class="dr-label"><i class="fas fa-user-check" style="width:14px"></i> Status Saya</span>
                        <span class="dr-value" style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap">
                            @if ($event->ada_absen_masuk)
                                <span style="color:{{ $sudahMasuk ? '#16a34a' : '#94a3b8' }}"><i
                                        class="fas fa-{{ $sudahMasuk ? 'check-circle' : 'times-circle' }}"></i>
                                    Masuk</span>
                            @endif
                            @if ($event->ada_absen_pulang)
                                <span style="color:{{ $sudahPulang ? '#0ea5e9' : '#94a3b8' }}"><i
                                        class="fas fa-{{ $sudahPulang ? 'check-circle' : 'times-circle' }}"></i>
                                    Pulang</span>
                            @endif
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- QR Code (non-siswa, event aktif) --}}
        @if ($event->isActive() && !$isSiswa)
            <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:12px">
                <div class="c-head">
                    <div class="c-icon" style="background:#dcfce7;flex-shrink:0"><i class="fas fa-qrcode"></i></div>
                    <h3>QR Code Scan</h3>
                    @if ($event->barcode_rotate_detik > 0)
                        <span class="sse-status sse-connecting" id="sseStatusBadge">
                            <span class="sse-status-dot"></span><span id="sseStatusText">Menghubungkan...</span>
                        </span>
                    @endif
                </div>
                <div class="c-body" style="padding:14px 16px;text-align:center">
                    <div id="qrcode-wrap"></div>
                    @if ($event->barcode_rotate_detik > 0)
                        <div class="rotate-timer">Barcode berubah dalam <span class="timer-val"
                                id="rotateTimer">{{ $event->barcode_rotate_detik }}</span> detik</div>
                    @endif
                    <div class="barcode-box" id="barcodeText">{{ substr($event->barcode_value, 0, 32) }}...</div>
                </div>
            </div>
        @endif

        {{-- Absen Terbaru --}}
        @php
            $eventSelesai = now()->gte($event->tanggal_selesai);
            $canBulkAction = $canEdit && $eventSelesai;
            $totalRows = $eventAttendanceRows->count();
        @endphp
        <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:12px">
            <div class="c-head">
                <div class="c-icon" style="background:#fef3c7;flex-shrink:0"><i class="fas fa-list"></i></div>
                <h3>{{ $isSiswa ? 'Absen Saya' : 'Absen Terbaru' }}</h3>
                <span class="hbadge">{{ $eventAttendanceSummary['hadir'] ?? 0 }} hadir /
                    {{ $eventAttendanceSummary['tidak_hadir'] ?? 0 }} tidak hadir / {{ $totalRows }} total</span>
            </div>

            @if ($canEdit && !$eventSelesai)
                {{-- Time-gate info --}}
                <div
                    style="background:#fef9c3;border-bottom:1px solid #fde68a;padding:10px 16px;display:flex;align-items:center;gap:8px;font-size:.78rem;color:#92400e">
                    <i class="fas fa-clock"></i>
                    <span>Fitur hapus massal & edit status tersedia setelah event selesai
                        ({{ $event->tanggal_selesai->format('d M Y H:i') }})</span>
                </div>
            @endif

            @if ($canEdit)
                {{-- Toolbar aksi massal --}}
                <div class="bulk-toolbar" id="bulkToolbar" style="{{ $canBulkAction ? '' : 'display:none' }}">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <label
                            style="display:inline-flex;align-items:center;gap:6px;font-size:.76rem;font-weight:700;color:#475569;cursor:pointer">
                            <input type="checkbox" id="selectAllRows"
                                style="width:15px;height:15px;accent-color:#7c3aed">
                            Pilih semua
                        </label>
                        <span id="selectedCount" style="font-size:.72rem;color:#64748b;font-weight:600"></span>
                    </div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap">
                        {{-- Hapus poin terpilih --}}
                        @if (($eventAttendanceSummary['pelanggaran'] ?? 0) + ($eventAttendanceSummary['penghargaan'] ?? 0) > 0)
                            <button type="button" id="bulkDeletePointsBtn" class="bulk-btn bulk-btn-red" disabled>
                                <i class="fas fa-trash-alt"></i> Hapus Poin Terpilih
                            </button>
                        @endif
                        {{-- Edit massal status --}}
                        <button type="button" id="bulkEditStatusBtn" class="bulk-btn bulk-btn-indigo" disabled>
                            <i class="fas fa-edit"></i> Edit Status Terpilih
                        </button>
                    </div>
                </div>
            @endif

            {{-- TABLE desktop --}}
            <div class="ev-table-wrap">
                <table class="ev-table" id="attendanceTable">
                        <tr>
                            @if ($canBulkAction)
                                <th style="width:36px"></th>
                            @endif
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Masuk</th>
                            <th>Pulang</th>
                            <th style="min-width:200px">Pelanggaran Event</th>
                            <th style="min-width:200px">Penghargaan Event</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($eventAttendanceRows as $row)
                            @php
                                $siswa = $row['siswa'];
                                $wM = $row['waktu_masuk'];
                                $wP = $row['waktu_pulang'];
                                $pelanggaranList = $row['pelanggaran'];
                                $penghargaanList = $row['penghargaan'];
                                $rowHadir = $row['hadir'];
                            @endphp
                            <tr class="attendance-row" data-siswa-id="{{ $siswa->id }}"
                                data-hadir="{{ $rowHadir ? '1' : '0' }}" data-nama="{{ $siswa->nama_lengkap ?? '' }}"
                                data-pelanggaran-ids="{{ $pelanggaranList->pluck('idpel')->implode(',') }}"
                                data-penghargaan-ids="{{ $penghargaanList->pluck('idpen')->implode(',') }}">
                                @if ($canBulkAction)
                                    <td>
                                        <input type="checkbox" class="row-check"
                                            style="width:15px;height:15px;accent-color:#7c3aed">
                                    </td>
                                @endif
                                <td style="color:#94a3b8;font-size:.72rem">{{ $siswa->nis ?? '-' }}</td>
                                <td style="font-weight:600">{{ Str::limit($siswa->nama_lengkap ?? '-', 22) }}</td>
                                <td style="font-size:.75rem">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>
                                    <span class="badge-attend {{ $rowHadir ? 'badge-attend-ok' : 'badge-attend-no' }}">
                                        <i class="fas fa-{{ $rowHadir ? 'check-circle' : 'times-circle' }}"></i>
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td>
                                    @if ($wM)
                                        <span class="badge-masuk"><i class="fas fa-sign-in-alt"
                                            style="font-size:.6rem"></i> {{ $wM->format('H:i') }}</span>@else<span
                                            style="color:#94a3b8">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($wP)
                                        <span class="badge-pulang"><i class="fas fa-sign-out-alt"
                                            style="font-size:.6rem"></i> {{ $wP->format('H:i') }}</span>@else<span
                                            style="color:#94a3b8;font-size:.72rem">—</span>
                                    @endif
                                </td>
                                {{-- Pelanggaran Event --}}
                                <td class="point-cell">
                                    @if ($pelanggaranList->isNotEmpty())
                                        <div class="point-stack">
                                            @foreach ($pelanggaranList as $pel)
                                                <div class="point-item point-item-red">
                                                    @if ($canBulkAction)
                                                        <input type="checkbox" class="point-check pel-check"
                                                            data-id="{{ $pel->idpel }}" data-type="pelanggaran"
                                                            style="width:14px;height:14px;accent-color:#dc2626;flex-shrink:0;margin-top:2px">
                                                    @endif
                                                    <div>
                                                        <div class="point-title point-red">
                                                            {{ Str::limit($pel->subPasal->pasal ?? $pel->idpasal, 40) }}
                                                        </div>
                                                        <div class="point-meta">{{ $pel->poin }} poin &bull;
                                                            {{ $pel->tgl?->format('d M') }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="point-item-empty">—</span>
                                    @endif
                                </td>
                                {{-- Penghargaan Event --}}
                                <td class="point-cell">
                                    @if ($penghargaanList->isNotEmpty())
                                        <div class="point-stack">
                                            @foreach ($penghargaanList as $pen)
                                                <div class="point-item point-item-green">
                                                    @if ($canBulkAction)
                                                        <input type="checkbox" class="point-check pen-check"
                                                            data-id="{{ $pen->idpen }}" data-type="penghargaan"
                                                            style="width:14px;height:14px;accent-color:#16a34a;flex-shrink:0;margin-top:2px">
                                                    @endif
                                                    <div>
                                                        <div class="point-title point-green">
                                                            {{ Str::limit($pen->subPasal->pasal ?? $pen->idpasal, 40) }}
                                                        </div>
                                                        <div class="point-meta">{{ $pen->poin }} poin &bull;
                                                            {{ $pen->tgl?->format('d M') }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="point-item-empty">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if ($eventAttendanceRows->isEmpty())
                            <tr>
                                <td colspan="9" style="text-align:center;color:#94a3b8;padding:24px;font-size:.82rem">
                                    Belum ada data absensi.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- CARD LIST mobile --}}
            <div class="ev-card-list" id="cardList">
                @forelse($eventAttendanceRows as $row)
                    @php
                        $siswa = $row['siswa'];
                        $wM = $row['waktu_masuk'];
                        $wP = $row['waktu_pulang'];
                        $pelanggaranList = $row['pelanggaran'];
                        $penghargaanList = $row['penghargaan'];
                        $rowHadir = $row['hadir'];
                    @endphp
                    <div class="evi" data-siswa-id="{{ $siswa->id }}" data-hadir="{{ $rowHadir ? '1' : '0' }}"
                        data-pelanggaran-ids="{{ $pelanggaranList->pluck('idpel')->implode(',') }}"
                        data-penghargaan-ids="{{ $penghargaanList->pluck('idpen')->implode(',') }}">
                        <div class="evi-top">
                            <div>
                                @if ($canBulkAction)
                                    <input type="checkbox" class="row-check-mobile"
                                        style="width:15px;height:15px;accent-color:#7c3aed;margin-right:6px;vertical-align:middle">
                                @endif
                                <span class="evi-name">{{ Str::limit($siswa->nama_lengkap ?? '-', 22) }}</span>
                            </div>
                            <span class="badge-attend {{ $rowHadir ? 'badge-attend-ok' : 'badge-attend-no' }}"
                                style="font-size:.65rem">
                                <i class="fas fa-{{ $rowHadir ? 'check-circle' : 'times-circle' }}"></i>
                                {{ $row['status'] }}
                            </span>
                        </div>
                        <div class="evi-mid">
                            <span style="font-size:.7rem;color:#64748b">{{ $siswa->nis ?? '-' }}</span>
                            @if ($siswa->kelas)
                                <span class="evi-chip">{{ $siswa->kelas->nama_kelas }}</span>
                            @endif
                            @if ($wM)
                                <span class="badge-masuk"><i class="fas fa-sign-in-alt" style="font-size:.6rem"></i>
                                    {{ $wM->format('H:i') }}</span>
                            @endif
                            @if ($wP)
                                <span class="badge-pulang"><i class="fas fa-sign-out-alt" style="font-size:.6rem"></i>
                                    {{ $wP->format('H:i') }}</span>
                            @endif
                        </div>
                        @if ($pelanggaranList->isNotEmpty() || $penghargaanList->isNotEmpty())
                            <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:4px">
                                @foreach ($pelanggaranList as $pel)
                                    <span
                                        style="font-size:.67rem;background:#fff1f2;color:#be123c;border:1px solid #fecdd3;border-radius:5px;padding:2px 7px;font-weight:700">
                                        ⚠ {{ Str::limit($pel->subPasal->pasal ?? $pel->idpasal, 25) }}
                                        ({{ $pel->poin }}p)
                                    </span>
                                @endforeach
                                @foreach ($penghargaanList as $pen)
                                    <span
                                        style="font-size:.67rem;background:#ecfdf5;color:#047857;border:1px solid #bbf7d0;border-radius:5px;padding:2px 7px;font-weight:700">
                                        ★ {{ Str::limit($pen->subPasal->pasal ?? $pen->idpasal, 25) }}
                                        ({{ $pen->poin }}p)
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div style="padding:20px;text-align:center;color:#94a3b8;font-size:.82rem">Belum ada data absensi.
                    </div>
                @endforelse
            </div>

            {{-- Pagination Controls --}}
            <div id="absenPagination" style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-top:1px solid #f1f5f9;background:#fafbfc;flex-wrap:wrap;gap:8px">
                <div style="font-size:.76rem;color:#64748b;font-weight:600" id="paginationInfo"></div>
                <div style="display:flex;align-items:center;gap:6px">
                    <button id="prevPageBtn" onclick="absenChangePage(-1)"
                        style="padding:6px 14px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#475569;font-size:.76rem;font-weight:700;cursor:pointer;transition:all .15s"
                        onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                        ‹ Prev
                    </button>
                    <div id="pageNumbers" style="display:flex;gap:4px"></div>
                    <button id="nextPageBtn" onclick="absenChangePage(1)"
                        style="padding:6px 14px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#475569;font-size:.76rem;font-weight:700;cursor:pointer;transition:all .15s"
                        onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                        Next ›
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== MODAL: Edit Massal Status ===== --}}
        @if ($canEdit)
            <div id="editStatusModal"
                style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:16px">
                <div
                    style="background:#fff;border-radius:16px;width:100%;max-width:560px;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.25)">
                    {{-- Header --}}
                    <div
                        style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between">
                        <div>
                            <h4 style="font-size:.95rem;font-weight:800;color:#0f172a;margin:0">Edit Status Kehadiran</h4>
                            <p style="font-size:.73rem;color:#64748b;margin:3px 0 0">Pilih status baru dan pasal yang
                                berlaku</p>
                        </div>
                        <button type="button" id="closeEditModal"
                            style="background:#f1f5f9;border:none;border-radius:8px;padding:7px 11px;cursor:pointer;font-size:.9rem;color:#475569">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    {{-- Body --}}
                    <div style="padding:16px 20px;overflow-y:auto;flex:1">
                        {{-- Pilihan status baru --}}
                        <div style="margin-bottom:14px">
                            <label
                                style="font-size:.76rem;font-weight:700;color:#475569;display:block;margin-bottom:6px">Status
                                Baru</label>
                            <div style="display:flex;gap:8px">
                                <label
                                    style="flex:1;display:flex;align-items:center;gap:7px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;cursor:pointer;font-size:.8rem;font-weight:700">
                                    <input type="radio" name="newStatus" value="hadir" id="statusHadir"
                                        style="accent-color:#16a34a">
                                    <span style="color:#15803d"><i class="fas fa-check-circle"></i> Hadir</span>
                                </label>
                                <label
                                    style="flex:1;display:flex;align-items:center;gap:7px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;cursor:pointer;font-size:.8rem;font-weight:700">
                                    <input type="radio" name="newStatus" value="tidak_hadir" id="statusTidakHadir"
                                        style="accent-color:#dc2626">
                                    <span style="color:#b91c1c"><i class="fas fa-times-circle"></i> Tidak Hadir</span>
                                </label>
                            </div>
                        </div>
                        {{-- Pemilihan pasal --}}
                        <div id="pasalSection" style="margin-bottom:14px">
                            <label
                                style="font-size:.76rem;font-weight:700;color:#475569;display:block;margin-bottom:6px">Pasal
                                / Aturan</label>
                            <select id="pasalSelect"
                                style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:9px;font-size:.8rem;color:#0f172a;background:#fff;appearance:auto">
                                <option value="">— Pilih status terlebih dahulu —</option>
                            </select>
                        </div>
                        {{-- Info aksi otomatis --}}
                        <div id="autoActionInfo"
                            style="display:none;background:#f0f9ff;border:1px solid #bae6fd;border-radius:9px;padding:10px 12px;font-size:.74rem;color:#0369a1;margin-bottom:14px">
                            <i class="fas fa-info-circle"></i> <span id="autoActionText"></span>
                        </div>
                        {{-- Daftar siswa terpilih --}}
                        <div>
                            <label
                                style="font-size:.76rem;font-weight:700;color:#475569;display:block;margin-bottom:6px">Siswa
                                Terpilih (<span id="modalSiswaCount">0</span>)</label>
                            <div id="modalSiswaList"
                                style="max-height:180px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:9px;background:#f8fafc">
                            </div>
                        </div>
                    </div>
                    {{-- Footer --}}
                    <div
                        style="padding:14px 20px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end">
                        <button type="button" id="cancelEditModal"
                            style="background:#f1f5f9;border:none;border-radius:9px;padding:9px 16px;font-size:.8rem;font-weight:700;color:#475569;cursor:pointer">
                            Batal
                        </button>
                        <button type="button" id="confirmEditStatus"
                            style="background:#7c3aed;border:none;border-radius:9px;padding:9px 18px;font-size:.8rem;font-weight:800;color:#fff;cursor:pointer;display:inline-flex;align-items:center;gap:6px"
                            disabled>
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>

            {{-- Hidden form untuk bulk update status --}}
            <form id="bulkUpdateStatusForm" method="POST" action="{{ route('event.attendance.bulk-update', $event) }}"
                style="display:none">
                @csrf
                <div id="bulkStatusInputs"></div>
            </form>

            {{-- Hidden form untuk bulk delete points --}}
            <form id="bulkDeletePointsForm" method="POST"
                action="{{ route('event.tatib-points.bulk-delete', $event) }}" style="display:none">
                @csrf
                <div id="bulkDeleteInputs"></div>
            </form>
        @endif

        {{-- Foto Kegiatan --}}
        @if ($eventPhotos->isNotEmpty())
            <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:12px">
                <div class="c-head">
                    <div class="c-icon" style="background:#e0f2fe;color:#0369a1;flex-shrink:0"><i
                            class="fas fa-images"></i></div>
                    <h3>Foto Kegiatan</h3>
                    <span class="hbadge">{{ $eventPhotos->count() }} foto</span>
                </div>
                <div class="c-body" style="padding:12px 16px">
                    <div class="foto-grid">
                        @foreach ($eventPhotos as $photo)
                            <a href="{{ Storage::url($photo->path) }}" target="_blank" rel="noopener"
                                class="foto-item">
                                <img src="{{ Storage::url($photo->path) }}" alt="Foto kegiatan {{ $loop->iteration }}"
                                    loading="lazy">
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>{{-- ev-wrap --}}

    {{-- Fullscreen Overlay --}}
    @if ($event->isActive() && !$isSiswa)
        <div id="fullscreen-overlay">
            <div class="fs-sse-dot" id="fsSseDot"></div>
            <button id="exitFullscreenBtn"><i class="fas fa-compress"></i> Keluar Full Screen</button>
            <div class="fs-header">
                <div class="fs-date"><i class="fas fa-calendar-day"
                        style="margin-right:5px"></i>{{ $event->tanggal_mulai->translatedFormat('l, d F Y') }}</div>
                <div class="fs-title">{{ $event->nama_event }}</div>
                @if ($event->lokasi)
                    <div class="fs-lokasi"><i class="fas fa-map-marker-alt"
                            style="margin-right:4px"></i>{{ $event->lokasi }}</div>
                @endif
            </div>
            <div class="fs-qr-box" id="fs-qr-box">
                <div id="fullscreen-qr"></div>
            </div>
            @if ($event->barcode_rotate_detik > 0)
                <div class="fs-timer-wrap">
                    <i class="fas fa-sync-alt" style="color:#334155;font-size:.9rem"></i>
                    <span class="fs-timer-label">Berganti dalam</span>
                    <span class="fs-timer-val" id="fsTimerVal">{{ $event->barcode_rotate_detik }}</span>
                    <span class="fs-timer-label">detik</span>
                    <div class="fs-timer-bar-wrap">
                        <div class="fs-timer-bar" id="fsTimerBar" style="width:100%"></div>
                    </div>
                </div>
            @endif
            <div class="fs-barcode-text" id="fsBarcodeText">
                {{ substr($event->barcode_value, 0, 48) }}{{ strlen($event->barcode_value) > 48 ? '…' : '' }}</div>
            <div class="fs-badges">
                @if ($event->ada_absen_masuk)
                    <span class="fs-badge fs-badge-masuk"><i class="fas fa-sign-in-alt" style="margin-right:5px"></i>Scan
                        Masuk</span>
                @endif
                @if ($event->ada_absen_pulang)
                    <span class="fs-badge fs-badge-pulang"><i class="fas fa-sign-out-alt"
                            style="margin-right:5px"></i>Scan Pulang</span>
                @endif
            </div>
            <div class="fs-esc-hint">Tekan <kbd>Esc</kbd> untuk keluar</div>
        </div>
    @endif

    {{-- Action Bar --}}
    <div class="action-bar">
        <a href="{{ route('event.index') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
        @if (!$isSiswa)
            @if ($canEdit)
                <a href="{{ route('event.edit', $event) }}" class="ab-btn ab-btn-primary"><i class="fas fa-pen"></i>
                    Edit</a>
                @if ($event->recurrence_parent_id && $event->recurringParent)
                    <a href="{{ route('event.edit', $event->recurringParent) }}" class="ab-btn ab-btn-primary"
                        style="background:#0ea5e9;box-shadow:0 3px 10px rgba(14,165,233,.3)"><i
                            class="fas fa-sync-alt"></i> Master</a>
                @endif
            @endif
            <a href="{{ route('event.rekap', $event) }}" class="ab-btn ab-btn-primary"><i class="fas fa-table"></i>
                Rekap</a>
            <a href="{{ route('event.jurnal', $event) }}" target="_blank" rel="noopener"
                class="ab-btn ab-btn-primary"><i class="fas fa-print"></i> Jurnal</a>
            @if ($event->isActive())
                <button id="fullscreenBtn" class="ab-btn ab-btn-primary"><i class="fas fa-expand"></i></button>
            @endif
        @else
            @if ($event->isActive())
                @if ($showScan)
                    <a href="{{ route('event.scan', ['event' => $event, 'jenis' => $nextJenis]) }}"
                        class="ab-btn ab-btn-scan"><i class="fas fa-qrcode"></i>
                        {{ $nextJenis === 'masuk' ? 'Scan Masuk' : 'Scan Pulang' }}</a>
                @else
                    <span class="ab-btn ab-btn-done"><i class="fas fa-check-circle"></i> Absen Lengkap</span>
                @endif
            @endif
            <a href="{{ route('event.rekap', $event) }}" class="ab-btn ab-btn-primary"><i class="fas fa-table"></i>
                Rekap</a>
        @endif
    </div>
@endsection

@if ($event->isActive() && !$isSiswa)
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#16a34a',
                    timer: 4000,
                    timerProgressBar: true
                });
            @endif
            (function() {
                const ROTATE_DETIK = {{ $event->barcode_rotate_detik }};
                const UPDATE_URL = "{{ route('event.updateBarcode', $event) }}";
                const POLL_URL = "{{ route('event.barcode', $event) }}";
                const CSRF = "{{ csrf_token() }}";
                const IS_ROTATING = ROTATE_DETIK > 0;
                const UPDATED_AT_MS =
                    {{ $event->barcode_updated_at ? $event->barcode_updated_at->valueOf() : 'Date.now()' }};

                let currentBarcode = @json($event->barcode_value);
                let countdown = IS_ROTATING ? Math.max(0, ROTATE_DETIK - Math.floor((Date.now() - UPDATED_AT_MS) / 1000)) :
                    0;
                let fsOpen = false,
                    isRotating = false,
                    mainInterval = null,
                    pollInterval = null;

                const $el = id => document.getElementById(id);
                const inlineWrap = $el('qrcode-wrap'),
                    inlineTimerEl = $el('rotateTimer'),
                    inlineTextEl = $el('barcodeText');
                const sseBadge = $el('sseStatusBadge'),
                    sseText = $el('sseStatusText');
                const overlay = $el('fullscreen-overlay'),
                    fsQrWrap = $el('fullscreen-qr'),
                    fsQrBox = $el('fs-qr-box');
                const fsTimerVal = $el('fsTimerVal'),
                    fsTimerBar = $el('fsTimerBar'),
                    fsBarcodeText = $el('fsBarcodeText');
                const fsSseDot = $el('fsSseDot'),
                    fullscreenBtn = $el('fullscreenBtn'),
                    exitBtn = $el('exitFullscreenBtn');

                function makeQR(c, v, s) {
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

                function genInline(v) {
                    if (inlineWrap) makeQR(inlineWrap, v, 220);
                    if (inlineTextEl) inlineTextEl.textContent = v.substring(0, 32) + (v.length > 32 ? '…' : '');
                }

                function genFs(v) {
                    if (!fsQrWrap) return;
                    makeQR(fsQrWrap, v, 360);
                    if (fsQrBox) {
                        fsQrBox.classList.remove('qr-pulse');
                        void fsQrBox.offsetWidth;
                        fsQrBox.classList.add('qr-pulse');
                    }
                    if (fsBarcodeText) fsBarcodeText.textContent = v.substring(0, 48) + (v.length > 48 ? '…' : '');
                }

                function setStatus(s) {
                    if (sseBadge) {
                        sseBadge.className = 'sse-status sse-' + s;
                        const L = {
                            connected: 'Live',
                            connecting: 'Menghubungkan...',
                            disconnected: 'Terputus'
                        };
                        if (sseText) sseText.textContent = L[s] || s;
                    }
                    if (fsSseDot) fsSseDot.className = 'fs-sse-dot' + (s === 'connected' ? '' : ' disconnected');
                }

                function applyNew(v, ms) {
                    if (!v || v === currentBarcode) return;
                    currentBarcode = v;
                    if (IS_ROTATING && ms) countdown = Math.max(0, ROTATE_DETIK - Math.floor((Date.now() - ms) / 1000));
                    genInline(currentBarcode);
                    if (fsOpen) genFs(currentBarcode);
                }

                function requestRotate() {
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
                        setStatus('connected');
                        applyNew(d.barcode_value, d.updated_at_ms);
                    }).catch(() => setStatus('disconnected')).finally(() => {
                        isRotating = false;
                    });
                }

                function pollBarcode() {
                    fetch(POLL_URL, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF
                        }
                    }).then(r => r.json()).then(d => {
                        setStatus('connected');
                        if (d.barcode_value && d.barcode_value !== currentBarcode) {
                            const ms = d.barcode_updated_at ? new Date(d.barcode_updated_at).getTime() : null;
                            applyNew(d.barcode_value, ms);
                        }
                    }).catch(() => setStatus('disconnected'));
                }

                function updateFsBar() {
                    if (!fsTimerBar || !IS_ROTATING) return;
                    const p = Math.min(100, (countdown / ROTATE_DETIK) * 100);
                    fsTimerBar.style.width = p + '%';
                    fsTimerBar.style.background = p > 50 ? 'linear-gradient(90deg,#6366f1,#0ea5e9)' : p > 25 ?
                        'linear-gradient(90deg,#f59e0b,#6366f1)' : 'linear-gradient(90deg,#ef4444,#f59e0b)';
                }

                function startInterval() {
                    mainInterval = setInterval(function() {
                        if (!IS_ROTATING) return;
                        if (inlineTimerEl) inlineTimerEl.textContent = Math.max(0, countdown);
                        if (fsOpen && fsTimerVal) fsTimerVal.textContent = Math.max(0, countdown);
                        updateFsBar();
                        if (countdown <= 0) {
                            requestRotate();
                            countdown = ROTATE_DETIK;
                        } else countdown--;
                    }, 1000);
                }

                function startPoll() {
                    setStatus('connecting');
                    pollBarcode();
                    const ms = IS_ROTATING ? Math.max(3000, ROTATE_DETIK * 1000) : 10000;
                    pollInterval = setInterval(pollBarcode, ms);
                }

                function openFs() {
                    if (!overlay) return;
                    overlay.style.display = 'flex';
                    fsOpen = true;
                    genFs(currentBarcode);
                    if (fsTimerVal) fsTimerVal.textContent = Math.max(0, countdown);
                    updateFsBar();
                }

                function closeFs() {
                    if (!overlay) return;
                    overlay.style.display = 'none';
                    fsOpen = false;
                }

                if (fullscreenBtn) fullscreenBtn.addEventListener('click', e => {
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

                genInline(currentBarcode);
                startInterval();
                startPoll();
                document.addEventListener('DOMContentLoaded', function() {
                    var h = document.querySelector('.header-auto-show');
                    if (h) h.classList.add('header-active');
                });
            })();
        </script>
        @include('event._bulk_actions_script', [
            'canBulkAction' => $canBulkAction,
            'canEdit' => $canEdit,
            'eventSelesai' => $eventSelesai,
            'pasalPelanggaranOptions' => $pasalPelanggaranOptions,
            'pasalPenghargaanOptions' => $pasalPenghargaanOptions,
        ])
        @include('event._attendance_pagination')
    @endpush
@else
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var h = document.querySelector('.header-auto-show');
                if (h) h.classList.add('header-active');
            });
        </script>
        @include('event._bulk_actions_script', [
            'canBulkAction' => $canBulkAction,
            'canEdit' => $canEdit,
            'eventSelesai' => $eventSelesai,
            'pasalPelanggaranOptions' => $pasalPelanggaranOptions,
            'pasalPenghargaanOptions' => $pasalPenghargaanOptions,
        ])
        @include('event._attendance_pagination')
    @endpush
@endif
