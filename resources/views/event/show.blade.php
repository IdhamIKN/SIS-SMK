@extends('layouts.app')

@section('title', $event->nama_event)

@push('styles')
@include('components.event-styles')
{{-- SweetAlert2 --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 14px;
        }

        .stat-card {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 14px;
            padding: 14px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: .68rem;
            color: var(--text-muted, #64748b);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 8px 0;
            font-size: .84rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .dr-label {
            color: var(--text-muted, #64748b);
            display: flex;
            align-items: center;
            gap: 7px;
            flex-shrink: 0;
        }

        .dr-value {
            font-weight: 600;
            color: var(--text-main, #0f172a);
            text-align: right;
        }

        .barcode-box {
            background: #f8fafc;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 10px;
            padding: 12px 16px;
            font-family: monospace;
            font-size: .78rem;
            color: var(--text-muted, #64748b);
            word-break: break-all;
            margin-top: 12px;
            text-align: center;
        }

        .event-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .78rem;
        }

        .event-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid var(--border, #e2e8f0);
        }

        .event-table th {
            padding: 9px 12px;
            text-align: left;
            font-size: .7rem;
            font-weight: 700;
            color: var(--text-muted, #64748b);
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .event-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .event-table tbody tr:last-child td {
            border-bottom: none;
        }

        .event-table tbody tr:hover td {
            background: #fafbfc;
        }

        .badge-masuk  { background: #dbeafe; color: #1d4ed8; }
        .badge-pulang { background: #ffedd5; color: #c2410c; }

        .jenis-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700;
        }

        .status-active { background: #dcfce7; color: #15803d; }
        .status-ended  { background: #f1f5f9; color: #64748b; }

        .action-bar {
            position: fixed;
            bottom: var(--footer-h);
            left: 0;
            right: 0;
            padding: 10px 16px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 8px;
            z-index: 999;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 10px;
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

        .ab-btn:active { transform: scale(.97); }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            flex: 0 0 auto;
            padding: 12px 14px;
        }

        .ab-btn-back:hover { background: #e2e8f0; }

        .ab-btn-primary {
            background: var(--event-primary, #f59e0b);
            color: #fff;
            box-shadow: 0 3px 10px rgba(245, 158, 11, .3);
        }

        .ab-btn-primary:hover { filter: brightness(1.07); }

        .ab-btn-scan {
            background: #16a34a;
            color: #fff;
            box-shadow: 0 3px 10px rgba(22, 163, 74, .3);
        }

        .ab-btn-scan:hover { background: #15803d; }

        #qrcode-wrap {
            display: inline-block;
            background: #fff;
            padding: 16px;
            border-radius: 12px;
            border: 1px solid var(--border, #e2e8f0);
        }

        #qrcode-wrap img,
        #qrcode-wrap canvas { display: block; }

        .rotate-timer {
            font-size: .75rem;
            color: var(--text-muted);
            margin-top: 8px;
        }

        .rotate-timer .timer-val {
            font-weight: 700;
            color: var(--event-primary, #0ea5e9);
        }

        /* ── Indikator status SSE ───────────────────────────── */
        .sse-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .68rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            margin-top: 8px;
        }

        .sse-status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .sse-connected    { background: #dcfce7; color: #15803d; }
        .sse-connected .sse-status-dot    { background: #16a34a; animation: ssePulse 1.5s infinite; }
        .sse-connecting   { background: #fef9c3; color: #a16207; }
        .sse-connecting .sse-status-dot   { background: #eab308; }
        .sse-disconnected { background: #fee2e2; color: #b91c1c; }
        .sse-disconnected .sse-status-dot { background: #ef4444; }

        @keyframes ssePulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: .3; }
        }

        /* ── Fullscreen Overlay ─────────────────────────────── */
        #fullscreen-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background: #0a0f1e;
            z-index: 10000;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 22px;
            overflow: hidden;
        }

        #fullscreen-overlay::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(99,102,241,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,.04) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        #fullscreen-overlay::after {
            content: '';
            position: absolute;
            width: 480px; height: 480px;
            background: radial-gradient(circle, rgba(99,102,241,.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .fs-header { text-align: center; position: relative; z-index: 1; }

        .fs-date {
            color: #64748b;
            font-size: .82rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .fs-title {
            color: #f8fafc;
            font-size: clamp(1.3rem, 3vw, 2rem);
            font-weight: 800;
            letter-spacing: -.02em;
            line-height: 1.2;
        }

        .fs-lokasi { color: #475569; font-size: .82rem; margin-top: 5px; }

        .fs-qr-box {
            background: #fff;
            padding: 22px;
            border-radius: 20px;
            box-shadow: 0 0 80px rgba(99,102,241,.2), 0 0 0 1px rgba(255,255,255,.05);
            position: relative;
            z-index: 1;
        }

        .fs-qr-box::before, .fs-qr-box::after {
            content: '';
            position: absolute;
            width: 16px; height: 16px;
            border-color: #6366f1;
            border-style: solid;
        }

        .fs-qr-box::before {
            top: -4px; left: -4px;
            border-width: 3px 0 0 3px;
            border-radius: 4px 0 0 0;
        }

        .fs-qr-box::after {
            bottom: -4px; right: -4px;
            border-width: 0 3px 3px 0;
            border-radius: 0 0 4px 0;
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
            z-index: 1;
        }

        .fs-timer-label { color: #64748b; font-size: .82rem; }

        .fs-timer-val {
            color: #f8fafc;
            font-size: 2rem;
            font-weight: 900;
            min-width: 2.4ch;
            text-align: center;
            font-variant-numeric: tabular-nums;
            line-height: 1;
        }

        .fs-timer-bar-wrap {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: #1e293b;
            border-radius: 0 0 14px 14px;
            overflow: hidden;
        }

        .fs-timer-bar {
            height: 100%;
            background: linear-gradient(90deg, #6366f1, #0ea5e9);
            transition: width 1s linear;
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
            z-index: 1;
        }

        .fs-badges { display: flex; gap: 10px; position: relative; z-index: 1; }

        .fs-badge {
            padding: 6px 18px;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .02em;
        }

        .fs-badge-masuk {
            background: rgba(22,163,74,.15);
            color: #86efac;
            border: 1px solid rgba(22,163,74,.25);
        }

        .fs-badge-pulang {
            background: rgba(14,165,233,.15);
            color: #7dd3fc;
            border: 1px solid rgba(14,165,233,.25);
        }

        /* SSE indicator di fullscreen */
        .fs-sse-dot {
            position: fixed;
            top: 18px; left: 18px;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #16a34a;
            z-index: 10001;
            animation: ssePulse 1.5s infinite;
        }

        .fs-sse-dot.disconnected { background: #ef4444; animation: none; }

        #exitFullscreenBtn {
            position: fixed;
            top: 18px; right: 18px;
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
            font-family: inherit;
        }

        #exitFullscreenBtn:hover {
            background: #1e293b;
            color: #94a3b8;
            border-color: #334155;
        }

        .fs-esc-hint {
            position: fixed;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            color: #1e293b;
            font-size: .72rem;
            z-index: 10001;
            white-space: nowrap;
        }

        .fs-esc-hint kbd {
            background: #0f172a;
            border: 1px solid #1e293b;
            padding: 2px 7px;
            border-radius: 4px;
            color: #334155;
            font-family: inherit;
        }

        @keyframes qrPulse {
            0%   { box-shadow: 0 0 80px rgba(99,102,241,.2), 0 0 0 1px rgba(255,255,255,.05); }
            50%  { box-shadow: 0 0 120px rgba(99,102,241,.5), 0 0 0 1px rgba(99,102,241,.2); }
            100% { box-shadow: 0 0 80px rgba(99,102,241,.2), 0 0 0 1px rgba(255,255,255,.05); }
        }

        .qr-pulse { animation: qrPulse .6s ease-out; }
    </style>
@endpush

@section('content')
    @php
        $isSiswa = auth()->user()->hasRole('siswa');
        $siswaId = $isSiswa ? auth()->user()->siswa->id ?? null : null;

        $absenMasukQ  = $event->absenEvent()->where('jenis', 'masuk');
        $absenPulangQ = $event->absenEvent()->where('jenis', 'pulang');
        $totalScanQ   = $event->absenEvent();
        $uniqueSiswaQ = $event->absenEvent();

        if ($isSiswa && $siswaId) {
            $absenMasukQ->where('siswa_id', $siswaId);
            $absenPulangQ->where('siswa_id', $siswaId);
            $totalScanQ->where('siswa_id', $siswaId);
            $uniqueSiswaQ->where('siswa_id', $siswaId);
        }

        $masukCount  = $absenMasukQ->count();
        $pulangCount = $absenPulangQ->count();
        $totalScan   = $totalScanQ->count();
        $uniqueSiswa = $uniqueSiswaQ->distinct('siswa_id')->count();

        $absenTerbaruQuery = $event->absenEvent()->with('siswa.kelas')->latest('waktu_scan');
        if ($isSiswa && $siswaId) {
            $absenTerbaruQuery->where('siswa_id', $siswaId);
        }
        $absenTerbaru = $absenTerbaruQuery->take(10)->get();
    @endphp

    <div class="event-wrap" style="padding-bottom: calc(var(--footer-h) + 88px);">

        {{-- Page Strip --}}
        <div class="page-strip {{ $event->isActive() ? 'page-strip-event' : 'page-strip-orange' }}">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ $event->tanggal_mulai->translatedFormat('l, d F Y') }}
            </div>
            <h2>
                <i class="fas fa-calendar-day"></i>
                {{ Str::limit($event->nama_event, 35) }}
            </h2>
            <p>{{ $event->isActive() ? 'Event sedang berlangsung' : 'Event telah selesai' }}</p>
        </div>



        {{-- Stat Grid --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value" style="color:#0ea5e9;">{{ $masukCount }}</div>
                <div class="stat-label">Absen Masuk</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#f59e0b;">{{ $pulangCount }}</div>
                <div class="stat-label">Absen Pulang</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#16a34a;">{{ $totalScan }}</div>
                <div class="stat-label">Total Scan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#8b5cf6;">{{ $uniqueSiswa }}</div>
                <div class="stat-label">{{ $isSiswa ? 'Status Saya' : 'Siswa Unik' }}</div>
            </div>
        </div>

        {{-- Detail Card --}}
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:var(--event-fade,#fef3c7);"><i class="fas fa-info-circle"></i></div>
                <h3>Detail Event</h3>
                <span class="hbadge badge-status {{ $event->isActive() ? 'status-active' : 'status-ended' }}">
                    {{ $event->isActive() ? 'Aktif' : 'Selesai' }}
                </span>
            </div>
            <div class="c-body" style="padding:12px 18px;">
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-align-left" style="width:14px;"></i> Deskripsi</span>
                    <span class="dr-value">{{ $event->deskripsi ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-clock" style="width:14px;"></i> Waktu</span>
                    <span class="dr-value">
                        {{ $event->tanggal_mulai->format('d M H:i') }} – {{ $event->tanggal_selesai->format('d M H:i') }}
                    </span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-map-marker-alt" style="width:14px;"></i> Lokasi</span>
                    <span class="dr-value">{{ $event->lokasi ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-users" style="width:14px;"></i> Berlaku</span>
                    <span class="dr-value">
                        {{ $event->berlaku_untuk_semua ? 'Semua kelas' : $event->kelas->count() . ' kelas' }}
                    </span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-sync-alt" style="width:14px;"></i> Barcode</span>
                    <span class="dr-value">
                        {{ $event->barcode_rotate_detik > 0 ? 'Rotate ' . $event->barcode_rotate_detik . 's' : 'Statis' }}
                    </span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-clipboard-check" style="width:14px;"></i> Tipe Absen</span>
                    <span class="dr-value" style="display:flex;gap:6px;justify-content:flex-end;">
                        @if ($event->ada_absen_masuk)
                            <span style="color:#16a34a;"><i class="fas fa-check"></i> Masuk</span>
                        @endif
                        @if ($event->ada_absen_pulang)
                            <span style="color:#0ea5e9;"><i class="fas fa-check"></i> Pulang</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Barcode / QR Code Card --}}
        @if ($event->isActive() && !$isSiswa)
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#dcfce7;"><i class="fas fa-qrcode"></i></div>
                    <h3>QR Code Scan</h3>
                    @if ($event->barcode_rotate_detik > 0)
                        <span class="sse-status sse-connecting" id="sseStatusBadge">
                            <span class="sse-status-dot"></span>
                            <span id="sseStatusText">Menghubungkan...</span>
                        </span>
                    @endif
                </div>
                <div class="c-body" style="padding:14px 16px; text-align:center;">
                    <div id="qrcode-wrap"></div>

                    @if ($event->barcode_rotate_detik > 0)
                        <div class="rotate-timer">
                            Barcode berubah dalam
                            <span class="timer-val" id="rotateTimer">{{ $event->barcode_rotate_detik }}</span> detik
                        </div>
                    @endif

                    <div class="barcode-box" id="barcodeText">
                        {{ substr($event->barcode_value, 0, 32) }}...
                    </div>
                </div>
            </div>
        @endif

        {{-- Recent Absen Card --}}
        @if ($absenTerbaru->isNotEmpty())
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#fef3c7;"><i class="fas fa-list"></i></div>
                    <h3>{{ $isSiswa ? 'Absen Saya' : 'Absen Terbaru' }}</h3>
                    <span class="hbadge">{{ $absenTerbaru->count() }} data</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="event-table">
                        <thead>
                            <tr>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Jenis</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($absenTerbaru as $absen)
                                <tr>
                                    <td style="color:var(--text-muted);font-size:.72rem;">
                                        {{ $absen->siswa->nis ?? '-' }}
                                    </td>
                                    <td style="font-weight:600;">
                                        {{ Str::limit($absen->siswa->nama_lengkap ?? '-', 14) }}
                                    </td>
                                    <td style="font-size:.75rem;">
                                        {{ $absen->siswa->kelas->nama_kelas ?? '-' }}
                                    </td>
                                    <td>
                                        <span class="jenis-badge {{ $absen->jenis === 'masuk' ? 'badge-masuk' : 'badge-pulang' }}">
                                            {{ $absen->jenis === 'masuk' ? 'Masuk' : 'Pulang' }}
                                        </span>
                                    </td>
                                    <td style="font-size:.75rem;white-space:nowrap;">
                                        {{ $absen->waktu_scan->format('H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Full Screen Overlay                                         --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if ($event->isActive() && !$isSiswa)
        <div id="fullscreen-overlay">

            {{-- Dot indikator SSE di fullscreen --}}
            <div class="fs-sse-dot" id="fsSseDot" title="Status koneksi real-time"></div>

            {{-- Tombol exit pojok kanan atas --}}
            <button id="exitFullscreenBtn">
                <i class="fas fa-compress"></i> Keluar Full Screen
            </button>

            {{-- Header info event --}}
            <div class="fs-header">
                <div class="fs-date">
                    <i class="fas fa-calendar-day" style="margin-right:5px;"></i>
                    {{ $event->tanggal_mulai->translatedFormat('l, d F Y') }}
                </div>
                <div class="fs-title">{{ $event->nama_event }}</div>
                @if ($event->lokasi)
                    <div class="fs-lokasi">
                        <i class="fas fa-map-marker-alt" style="margin-right:4px;"></i>{{ $event->lokasi }}
                    </div>
                @endif
            </div>

            {{-- QR Code box --}}
            <div class="fs-qr-box" id="fs-qr-box">
                <div id="fullscreen-qr"></div>
            </div>

            {{-- Timer (hanya jika rotate) --}}
            @if ($event->barcode_rotate_detik > 0)
                <div class="fs-timer-wrap">
                    <i class="fas fa-sync-alt" style="color:#334155; font-size:.9rem;"></i>
                    <span class="fs-timer-label">Berganti dalam</span>
                    <span class="fs-timer-val" id="fsTimerVal">{{ $event->barcode_rotate_detik }}</span>
                    <span class="fs-timer-label">detik</span>
                    <div class="fs-timer-bar-wrap">
                        <div class="fs-timer-bar" id="fsTimerBar" style="width:100%;"></div>
                    </div>
                </div>
            @endif

            {{-- Barcode text --}}
            <div class="fs-barcode-text" id="fsBarcodeText">
                {{ substr($event->barcode_value, 0, 48) }}{{ strlen($event->barcode_value) > 48 ? '…' : '' }}
            </div>

            {{-- Badge jenis absen --}}
            <div class="fs-badges">
                @if ($event->ada_absen_masuk)
                    <span class="fs-badge fs-badge-masuk">
                        <i class="fas fa-sign-in-alt" style="margin-right:5px;"></i>Scan Masuk
                    </span>
                @endif
                @if ($event->ada_absen_pulang)
                    <span class="fs-badge fs-badge-pulang">
                        <i class="fas fa-sign-out-alt" style="margin-right:5px;"></i>Scan Pulang
                    </span>
                @endif
            </div>

            {{-- Hint keyboard --}}
            <div class="fs-esc-hint">
                Tekan <kbd>Esc</kbd> untuk keluar
            </div>
        </div>
    @endif

    {{-- Action Bar --}}
    <div class="action-bar">
        <a href="{{ route('event.index') }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        @if (!$isSiswa)
            <a href="{{ route('event.edit', $event) }}" class="ab-btn ab-btn-primary">
                <i class="fas fa-pen"></i> Edit
            </a>
            <a href="{{ route('event.rekap', $event) }}" class="ab-btn ab-btn-primary">
                <i class="fas fa-table"></i> Rekap
            </a>
            @if ($event->isActive())
                <button id="fullscreenBtn" class="ab-btn ab-btn-primary">
                    <i class="fas fa-expand"></i> Full Screen
                </button>
            @endif
        @else
            <a href="{{ route('event.rekap', $event) }}" class="ab-btn ab-btn-primary">
                <i class="fas fa-table"></i> Rekap Saya
            </a>
        @endif
        @if ($isSiswa && $event->isActive() && $siswaId)
            @php
                $sudahMasuk = $masukCount > 0;
                $jenis      = $sudahMasuk && $event->ada_absen_pulang ? 'pulang' : 'masuk';
                $label      = $jenis === 'masuk' ? 'Scan Masuk' : 'Scan Pulang';
            @endphp
            @if (($jenis === 'masuk' && $event->ada_absen_masuk) || ($jenis === 'pulang' && $event->ada_absen_pulang))
                <a href="{{ route('event.scan', ['event' => $event, 'jenis' => $jenis]) }}" class="ab-btn ab-btn-scan">
                    <i class="fas fa-qrcode"></i> {{ $label }}
                </a>
            @endif
        @endif
    </div>
@endsection

{{-- ================================================================ --}}
{{-- Scripts — hanya untuk admin/guru saat event aktif                --}}
{{-- ================================================================ --}}
@if ($event->isActive() && !$isSiswa)
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
        // Show session messages via SweetAlert
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#16a34a',
                confirmButtonText: 'OK',
                timer: 4000,
                timerProgressBar: true,
            });
        @endif

        (function () {
            // ── Konfigurasi dari server ─────────────────────────────────────
            const ROTATE_DETIK   = {{ $event->barcode_rotate_detik }};
            const STREAM_URL     = "{{ route('event.barcodeStream', $event) }}";
            const UPDATE_URL     = "{{ route('event.updateBarcode', $event) }}";
            const CSRF           = "{{ csrf_token() }}";
            const IS_ROTATING    = ROTATE_DETIK > 0;

            // Waktu update terakhir dari server → untuk sync countdown awal
            const UPDATED_AT_MS  = {{ $event->barcode_updated_at ? $event->barcode_updated_at->valueOf() : 'Date.now()' }};

            let currentBarcode   = @json($event->barcode_value);

            // ── Elemen DOM ──────────────────────────────────────────────────
            const inlineWrap     = document.getElementById('qrcode-wrap');
            const inlineTimerEl  = document.getElementById('rotateTimer');
            const inlineTextEl   = document.getElementById('barcodeText');
            const sseStatusBadge = document.getElementById('sseStatusBadge');
            const sseStatusText  = document.getElementById('sseStatusText');

            const overlay        = document.getElementById('fullscreen-overlay');
            const fsQrWrap       = document.getElementById('fullscreen-qr');
            const fsQrBox        = document.getElementById('fs-qr-box');
            const fsTimerVal     = document.getElementById('fsTimerVal');
            const fsTimerBar     = document.getElementById('fsTimerBar');
            const fsBarcodeText  = document.getElementById('fsBarcodeText');
            const fsSseDot       = document.getElementById('fsSseDot');
            const fullscreenBtn  = document.getElementById('fullscreenBtn');
            const exitBtn        = document.getElementById('exitFullscreenBtn');

            // ── State ───────────────────────────────────────────────────────
            // Hitung sisa detik dari server (sinkron antar semua device)
            let countdown        = IS_ROTATING
                ? Math.max(0, ROTATE_DETIK - Math.floor((Date.now() - UPDATED_AT_MS) / 1000))
                : 0;
            let mainInterval     = null;
            let fsOpen           = false;
            let sseConnected     = false;

            // ════════════════════════════════════════════════════════════════
            // Generate QR Helper
            // ════════════════════════════════════════════════════════════════
            function makeQR(container, value, size) {
                container.innerHTML = '';
                new QRCode(container, {
                    text: value,
                    width: size,
                    height: size,
                    colorDark: "#0f172a",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            }

            function generateInlineQR(value) {
                if (inlineWrap) makeQR(inlineWrap, value, 220);
                if (inlineTextEl) inlineTextEl.textContent = value.substring(0, 32) + (value.length > 32 ? '…' : '');
            }

            function generateFullscreenQR(value) {
                if (!fsQrWrap) return;
                makeQR(fsQrWrap, value, 360);
                if (fsQrBox) {
                    fsQrBox.classList.remove('qr-pulse');
                    void fsQrBox.offsetWidth; // force reflow
                    fsQrBox.classList.add('qr-pulse');
                }
                if (fsBarcodeText) fsBarcodeText.textContent = value.substring(0, 48) + (value.length > 48 ? '…' : '');
            }

            // ════════════════════════════════════════════════════════════════
            // Terapkan barcode baru — dipanggil dari SSE atau fallback fetch
            // ════════════════════════════════════════════════════════════════
            function applyNewBarcode(value, updatedAtMs) {
                if (value === currentBarcode) return; // tidak ada perubahan

                currentBarcode = value;

                // Sync ulang countdown dari server
                if (IS_ROTATING && updatedAtMs) {
                    countdown = Math.max(0, ROTATE_DETIK - Math.floor((Date.now() - updatedAtMs) / 1000));
                }

                generateInlineQR(currentBarcode);
                if (fsOpen) generateFullscreenQR(currentBarcode);
            }

            // ════════════════════════════════════════════════════════════════
            // SSE — Server-Sent Events (sumber utama update barcode)
            // ════════════════════════════════════════════════════════════════
            let sseSource      = null;
            let sseRetryTimer  = null;
            let sseRetryCount  = 0;
            const SSE_MAX_RETRY = 5;

            function setSSEStatus(status) {
                sseConnected = (status === 'connected');
                if (!sseStatusBadge) return;

                sseStatusBadge.className = 'sse-status sse-' + status;
                const labels = {
                    connected:    'Live',
                    connecting:   'Menghubungkan...',
                    disconnected: 'Terputus – polling aktif',
                };
                if (sseStatusText) sseStatusText.textContent = labels[status] || status;

                if (fsSseDot) {
                    fsSseDot.className = 'fs-sse-dot' + (status === 'connected' ? '' : ' disconnected');
                }
            }

            function connectSSE() {
                if (sseSource) {
                    sseSource.close();
                    sseSource = null;
                }

                setSSEStatus('connecting');
                sseSource = new EventSource(STREAM_URL);

                // Event: barcode baru dari server
                sseSource.addEventListener('barcode', function (e) {
                    sseRetryCount = 0;
                    const data = JSON.parse(e.data);
                    applyNewBarcode(data.barcode_value, data.updated_at_ms);
                });

                // Event: heartbeat — tandanya koneksi masih hidup
                sseSource.addEventListener('heartbeat', function () {
                    setSSEStatus('connected');
                    sseRetryCount = 0;
                });

                sseSource.onopen = function () {
                    setSSEStatus('connected');
                    sseRetryCount = 0;
                };

                sseSource.onerror = function () {
                    setSSEStatus('disconnected');
                    sseSource.close();
                    sseSource = null;

                    if (sseRetryCount < SSE_MAX_RETRY) {
                        const delay = Math.min(3000 * Math.pow(2, sseRetryCount), 30000); // exponential backoff
                        sseRetryCount++;
                        sseRetryTimer = setTimeout(connectSSE, delay);
                    }
                    // Jika sudah max retry → fallback ke polling (mainInterval tetap berjalan)
                };
            }

            // ════════════════════════════════════════════════════════════════
            // Fallback fetch (dipanggil hanya jika SSE tidak tersambung)
            // ════════════════════════════════════════════════════════════════
            function fetchBarcodeUpdate() {
                fetch(UPDATE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                })
                .then(r => r.json())
                .then(data => {
                    applyNewBarcode(data.barcode_value, data.updated_at_ms);
                })
                .catch(err => console.warn('[Barcode] Fallback fetch error:', err));
            }

            // ════════════════════════════════════════════════════════════════
            // Progress bar fullscreen
            // ════════════════════════════════════════════════════════════════
            function updateFsTimerBar() {
                if (!fsTimerBar || !IS_ROTATING) return;
                const pct = Math.min(100, (countdown / ROTATE_DETIK) * 100);
                fsTimerBar.style.width = pct + '%';

                if (pct > 50) {
                    fsTimerBar.style.background = 'linear-gradient(90deg, #6366f1, #0ea5e9)';
                } else if (pct > 25) {
                    fsTimerBar.style.background = 'linear-gradient(90deg, #f59e0b, #6366f1)';
                } else {
                    fsTimerBar.style.background = 'linear-gradient(90deg, #ef4444, #f59e0b)';
                }
            }

            // ════════════════════════════════════════════════════════════════
            // Interval utama — tick tiap 1 detik (hanya untuk display timer)
            // Rotasi barcode dihandle server via SSE, bukan dari sini
            // ════════════════════════════════════════════════════════════════
            function startMainInterval() {
                mainInterval = setInterval(function () {
                    if (IS_ROTATING) {
                        // Update display countdown
                        if (inlineTimerEl) inlineTimerEl.textContent = Math.max(0, countdown);
                        if (fsOpen && fsTimerVal) fsTimerVal.textContent = Math.max(0, countdown);
                        updateFsTimerBar();

                        if (countdown <= 0) {
                            // SSE belum connected → pakai fallback fetch
                            if (!sseConnected) {
                                fetchBarcodeUpdate();
                            }
                            // Jika SSE connected: server akan push update via SSE
                            // applyNewBarcode() akan reset countdown otomatis
                            // Tapi agar timer tidak stuck di 0, set sementara:
                            if (countdown <= 0) countdown = ROTATE_DETIK;
                        } else {
                            countdown--;
                        }
                    }
                }, 1000);
            }

            // ════════════════════════════════════════════════════════════════
            // Buka / tutup fullscreen
            // ════════════════════════════════════════════════════════════════
            function openFullscreen() {
                if (!overlay) return;
                overlay.style.display = 'flex';
                fsOpen = true;
                generateFullscreenQR(currentBarcode);
                if (fsTimerVal) fsTimerVal.textContent = Math.max(0, countdown);
                updateFsTimerBar();
            }

            function closeFullscreen() {
                if (!overlay) return;
                overlay.style.display = 'none';
                fsOpen = false;
            }

            // ════════════════════════════════════════════════════════════════
            // Event Listeners
            // ════════════════════════════════════════════════════════════════
            if (fullscreenBtn) {
                fullscreenBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    openFullscreen();
                });
            }

            if (exitBtn) exitBtn.addEventListener('click', closeFullscreen);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && fsOpen) closeFullscreen();
            });

            // Bersihkan SSE saat user navigasi pergi
            window.addEventListener('beforeunload', function () {
                if (sseSource) sseSource.close();
                clearTimeout(sseRetryTimer);
                clearInterval(mainInterval);
            });

            // ════════════════════════════════════════════════════════════════
            // Init
            // ════════════════════════════════════════════════════════════════
            generateInlineQR(currentBarcode);
            startMainInterval();

            if (IS_ROTATING) {
                connectSSE(); // mulai SSE untuk barcode rotate
            }

        })();
        </script>
    @endpush
@endif