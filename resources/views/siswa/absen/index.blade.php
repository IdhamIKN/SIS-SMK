@extends('layouts.app')

@section('title', 'Absen Siswa')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        :root {
            --green-primary: #16a34a;
            --blue-accent: #0ea5e9;
            --surface: #f1f5f9;
            --card: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        .absen-wrap {
            max-width: 520px;
            margin: 0 auto;
            padding: 14px 14px 100px;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Dynamic page strip based on jenis */
        .page-strip-masuk {
            background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
        }

        .page-strip-pulang {
            background: linear-gradient(135deg, #0ea5e9 0%, #1d4ed8 100%);
        }

        .page-strip {
            border-radius: 16px;
            padding: 16px 18px;
            margin-bottom: 14px;
            position: relative;
            overflow: hidden;
        }

        .page-strip::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
        }

        .live-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .2);
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 20px;
            padding: 3px 10px;
            font-size: .7rem;
            color: #fff;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .live-dot {
            width: 6px;
            height: 6px;
            background: #4ade80;
            border-radius: 50%;
            animation: blink 1.5s ease infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .5;
                transform: scale(1.3);
            }
        }

        .page-strip h2 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }

        .page-strip p {
            font-size: .77rem;
            color: rgba(255, 255, 255, .8);
            margin-top: 3px;
            position: relative;
        }

        /* Steps, status chips, cards */
        .steps {
            display: flex;
            margin-bottom: 14px;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step::after {
            content: '';
            position: absolute;
            top: 13px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: var(--border);
        }

        .step:last-child::after {
            display: none;
        }

        .step.done::after {
            background: var(--green-primary);
        }

        .step-dot {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--border);
            color: var(--text-muted);
            font-size: .68rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 4px;
            position: relative;
            z-index: 1;
            transition: all .3s;
        }

        .step.active .step-dot {
            background: var(--blue-accent);
            color: #fff;
        }

        .step.done .step-dot {
            background: var(--green-primary);
            color: #fff;
        }

        .step-lbl {
            font-size: .6rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .step.active .step-lbl,
        .step.done .step-lbl {
            color: var(--text-main);
        }

        .status-bar {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 14px;
        }

        .s-chip {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
        }

        .ci {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
            background: #f1f5f9;
        }

        .ci-g {
            background: #dcfce7;
        }

        .ci-b {
            background: #dbeafe;
        }

        .c-lbl {
            font-size: .65rem;
            color: var(--text-muted);
        }

        .c-val {
            font-size: .8rem;
            font-weight: 600;
            margin-top: 1px;
        }

        .card {
            background: var(--card);
            border-radius: 14px;
            border: 1px solid var(--border);
            margin-bottom: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05), 0 4px 14px rgba(0, 0, 0, .03);
        }

        .c-head {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .c-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
        }

        .c-head h3 {
            font-size: .87rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .hbadge {
            margin-left: auto;
            font-size: .68rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #64748b;
        }

        /* Map styles - FIXED */
        #map {
            height: 200px;
            width: 100%;
            position: relative;
            background-color: #f1f5f9;
            border-radius: 12px 12px 0 0;
            z-index: 1;
        }

        .leaflet-container {
            background: #f1f5f9 !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .map-wrapper {
            position: relative;
            height: 200px;
            width: 100%;
            margin-bottom: 12px;
            border-radius: 12px;
            overflow: hidden;
        }

        .map-bar {
            position: absolute;
            bottom: 8px;
            left: 8px;
            right: 8px;
            background: rgba(255, 255, 255, .93);
            backdrop-filter: blur(6px);
            border-radius: 10px;
            padding: 7px 12px;
            z-index: 999;
            font-size: .74rem;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, .8);
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        }

        .db-ok {
            background: #dcfce7;
            color: #15803d;
        }

        .db-far {
            background: #fee2e2;
            color: #dc2626;
        }

        .db-wait {
            background: #f1f5f9;
            color: #64748b;
        }

        .dbadge {
            font-size: .68rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 12px;
        }

        /* Selfie camera styles */
        .c-body {
            padding: 12px;
        }

        .cam-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .sw {
            position: relative;
            width: 170px;
            height: 170px;
        }

        .sc {
            width: 170px;
            height: 170px;
            border-radius: 50%;
            overflow: hidden;
            background: #f1f5f9;
            border: 3px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: border-color .3s, box-shadow .3s;
        }

        .sc.on {
            border-color: var(--blue-accent);
            box-shadow: 0 0 0 2px rgba(14, 165, 233, .2);
        }

        .sc.got {
            border-color: var(--green-primary);
            box-shadow: 0 0 0 2px rgba(22, 163, 74, .2);
        }

        .ph {
            text-align: center;
            color: var(--text-muted);
        }

        .pi {
            font-size: 2.2rem;
            margin-bottom: 4px;
        }

        .cring {
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            display: none;
            pointer-events: none;
        }

        .cnum {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .95rem;
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .2);
            background: var(--blue-accent);
            color: #fff;
        }

        .cbadge {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .2);
            background: var(--green-primary);
            color: #fff;
        }

        .cam-btns {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            padding: 9px 18px;
            border-radius: 9px;
            font-size: .82rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all .2s;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .btn-b {
            background: var(--blue-accent);
            color: #fff;
        }

        .btn-b:hover:not(:disabled) {
            background: #0284c7;
        }

        .btn-o {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
        }

        .btn-o:hover:not(:disabled) {
            border-color: var(--text-main);
        }

        .cam-hint {
            font-size: .71rem;
            color: var(--text-muted);
            text-align: center;
        }

        .btn-sub {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            background: var(--green-primary);
            color: #fff;
            font-size: .95rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(22, 163, 74, .35);
            transition: all .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-sub:hover:not(:disabled) {
            background: #15803d;
        }

        .btn-sub:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        .time-window {
            background: rgba(255, 255, 255, .9);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            font-size: .78rem;
            border-left: 4px solid var(--green-primary);
            margin: 12px 0;
        }

        .map-info {
            padding: 8px 14px;
            font-size: .72rem;
            color: var(--text-muted);
            display: flex;
            gap: 12px;
            background: #f8fafc;
            border-top: 1px solid var(--border);
        }

        .map-info span {
            flex: 1;
        }

        /* Loading indicator */
        .camera-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: var(--text-muted);
            z-index: 10;
        }

        .spinner {
            border: 3px solid #f1f5f9;
            border-top: 3px solid var(--blue-accent);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 8px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* SweetAlert2 custom font */
        .swal2-popup {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
    </style>
@endpush

@section('content')
    <div class="absen-wrap">
        {{-- Dynamic Page Strip --}}
        <div class="page-strip {{ $status['sudahMasuk'] ? 'page-strip-pulang' : 'page-strip-masuk' }}">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ now()->translatedFormat('l, d F Y, H:i') }}
            </div>
            <h2>
                @if (!$status['sudahMasuk'])
                    <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Absen Masuk
                @else
                    <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Absen Pulang
                @endif
            </h2>
            <p>
                @if (!$status['sudahMasuk'])
                    Konfirmasi kehadiran pagi ini
                @else
                    Konfirmasi pulang dengan selamat
                @endif
            </p>
        </div>

        {{-- Steps --}}
        <div class="steps">
            <div class="step done" id="step1">
                <div class="step-dot">✓</div>
                <div class="step-lbl">Login</div>
            </div>
            <div class="step active" id="step2">
                <div class="step-dot">2</div>
                <div class="step-lbl">Lokasi</div>
            </div>
            <div class="step" id="step3">
                <div class="step-dot">3</div>
                <div class="step-lbl">Selfie</div>
            </div>
            <div class="step" id="step4">
                <div class="step-dot">4</div>
                <div class="step-lbl">Kirim</div>
            </div>
        </div>

        {{-- Status --}}
        <div class="status-bar">
            <div class="s-chip">
                <div class="ci {{ $status['sudahMasuk'] ? 'ci-g' : '' }}" id="icM">
                    {{ $status['sudahMasuk'] ? '✅' : '⏳' }}</div>
                <div>
                    <div class="c-lbl">Masuk</div>
                    <div class="c-val" id="valM">{{ $status['sudahMasuk'] ? 'Sudah' : 'Belum' }}</div>
                </div>
            </div>
            <div class="s-chip">
                <div class="ci {{ $status['sudahPulang'] ? 'ci-b' : '' }}" id="icP">
                    {{ $status['sudahPulang'] ? '🏠' : '⏳' }}</div>
                <div>
                    <div class="c-lbl">Pulang</div>
                    <div class="c-val" id="valP">{{ $status['sudahPulang'] ? 'Sudah' : 'Belum' }}</div>
                </div>
            </div>
        </div>

        {{-- Jenis Absen --}}
        @php $jenis = $status['sudahMasuk'] ? 'pulang' : 'masuk'; @endphp
        @php $shift = jam_shift_config()['pagi'] ?? config('sekolah.jam_shift.pagi'); @endphp
        @php $waktuValid = ($jenis=='masuk' ? date('H:i', strtotime($shift['masuk'])) . '-' . date('H:i', strtotime($shift['limit_masuk'])) : date('H:i', strtotime($shift['pulang'])) . '-' . date('H:i', strtotime($shift['limit_pulang']))); @endphp

        <div class="time-window">
            <strong>⏰ Waktu Absen {{ ucfirst($jenis) }}:</strong><br>
            <span id="timeWindow">
                @if ($jenis === 'pulang' && ($status['bolehPulangCepat'] ?? false))
                    Izin pulang cepat disetujui, boleh absen pulang sekarang
                @else
                    {{ $waktuValid }}
                @endif
            </span>
            <div id="timeStatus" style="font-size:.75rem;color:#64748b;margin-top:4px;"></div>
        </div>

        {{-- Selfie Card --}}
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:#fef9c3;">{{ $status['sudahMasuk'] ? '🏠' : '📸' }}</div>
                <h3>{{ $status['sudahMasuk'] ? 'Foto Pulang' : 'Foto Selfie Masuk' }}</h3>
                <span class="hbadge" id="selBadge">Mempersiapkan kamera...</span>
            </div>
            <div class="c-body">
                <div class="cam-section">
                    <div class="sw">
                        <div class="sc" id="sc">
                            <video id="liveVid" autoplay playsinline muted
                                style="width:100%;height:100%;object-fit:cover;display:none;transform:scaleX(-1);"></video>
                            <img id="capImg" alt="Selfie"
                                style="width:100%;height:100%;object-fit:cover;display:none;">
                            <div class="ph" id="ph">
                                <div class="spinner"></div>
                                <p style="font-size:.72rem;line-height:1.4;">Membuka kamera...</p>
                            </div>
                        </div>
                        <div class="cring" id="cring">
                            <svg viewBox="0 0 176 176" style="width:100%;height:100%;transform:rotate(-90deg);">
                                <circle id="arc" cx="88" cy="88" r="85" fill="none"
                                    stroke="var(--blue-accent)" stroke-width="3" stroke-dasharray="534"
                                    stroke-dashoffset="534" stroke-linecap="round" />
                            </svg>
                        </div>
                        <div class="cnum" id="cnum"></div>
                        <div class="cbadge" id="cbadge">✓</div>
                    </div>
                    <div class="cam-btns">
                        <button type="button" id="btnCam" class="btn btn-b" style="display:none;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Buka Kamera
                        </button>
                        <button type="button" id="btnRetake" class="btn btn-o" style="display:none;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Ulangi
                        </button>
                        <button type="button" id="btnSnap" class="btn btn-b" style="display:none;">📸 Ambil
                            Sekarang</button>
                    </div>
                    <p class="cam-hint">
                        Foto otomatis 5 detik setelah kamera terbuka.<br>
                        Pastikan wajah jelas & pencahayaan cukup.
                    </p>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <form id="absenForm" method="POST" action="{{ route('absen.store', $jenis) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="latitude" id="hidLat">
            <input type="hidden" name="longitude" id="hidLng">
            <canvas id="cv" style="display:none;"></canvas>

            <button type="submit" id="btnSub" disabled class="btn-sub">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                </svg>
                <span id="subLbl">{{ $status['sudahMasuk'] ? 'Konfirmasi Pulang' : 'Absen Masuk Sekarang' }}</span>
            </button>
        </form>
        <br>

        {{-- Map Card - FIXED --}}
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:#dbeafe;">📍</div>
                <h3>Lokasi Saat Ini</h3>
                <span class="hbadge" id="gpsBadge">Mendeteksi GPS...</span>
            </div>
            <div class="map-wrapper">
                <div id="map"></div>
                <div class="map-bar">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span id="mapTxt">Mendapatkan lokasi...</span>
                    <span class="dbadge db-wait" id="distBadge">— m</span>
                </div>
            </div>
            <div class="map-info">
                <span id="coordTxt">🌐 —</span>
                <span id="accTxt">🎯 Akurasi: — m</span>
            </div>
        </div>
        <br>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const SCH_LAT = {{ config('sekolah.latitude') }};
            const SCH_LNG = {{ config('sekolah.longitude') }};
            const RADIUS = {{ config('sekolah.radius_m') }};

            /* ─── TAMPILKAN FLASH MESSAGE VIA SWEETALERT ─────────── */
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

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: '<ul style="text-align:left;padding-left:16px;">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Tutup',
                });
            @endif

            @if ($jenis === 'pulang' && ($status['bolehPulangCepat'] ?? false) && !$status['sudahPulang'])
                Swal.fire({
                    icon: 'info',
                    title: 'Izin Pulang Cepat',
                    text: 'Izin pulang cepat Anda sudah disetujui. Absen pulang hari ini boleh dilakukan tanpa menunggu jam pulang normal.',
                    confirmButtonColor: '#0ea5e9',
                    confirmButtonText: 'Mengerti',
                });
            @endif

            @if ($status['sudahMasuk'] && $status['sudahPulang'])
                Swal.fire({
                    icon: 'success',
                    title: '🎉 Absen Lengkap!',
                    text: 'Absen hari ini sudah lengkap! Terima kasih.',
                    confirmButtonColor: '#16a34a',
                    confirmButtonText: 'OK',
                });
            @endif

            let gpsOk = false,
                blob = null,
                stream = null,
                ticking = false,
                map = null;

            /* ─── MAP INITIALIZATION ──────────────────────────────── */
            function initMap() {
                setTimeout(() => {
                    const mapElement = document.getElementById('map');
                    if (!mapElement) {
                        console.error('Map element not found');
                        return;
                    }

                    map = L.map('map', {
                        zoomControl: false,
                        dragging: true,
                        touchZoom: true,
                        scrollWheelZoom: false,
                        attributionControl: true
                    }).setView([SCH_LAT, SCH_LNG], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 19,
                        minZoom: 12
                    }).addTo(map);

                    L.control.zoom({ position: 'topright' }).addTo(map);

                    const mkSchool = L.divIcon({
                        html: `<div style="background:#0ea5e9;width:34px;height:34px;border-radius:50%;
                        border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);
                        display:flex;align-items:center;justify-content:center;font-size:18px;line-height:1;">🏫</div>`,
                        iconSize: [34, 34],
                        iconAnchor: [17, 17],
                        className: ''
                    });

                    L.marker([SCH_LAT, SCH_LNG], { icon: mkSchool }).addTo(map)
                        .bindPopup(`<b>Lokasi Sekolah</b><br>Radius ${RADIUS}m`);

                    L.circle([SCH_LAT, SCH_LNG], {
                        radius: RADIUS,
                        color: '#0ea5e9',
                        fillColor: '#0ea5e9',
                        fillOpacity: 0.08,
                        weight: 2,
                        dashArray: '5,5'
                    }).addTo(map);

                    const mkUser = L.divIcon({
                        html: `<div style="background:#16a34a;width:30px;height:30px;border-radius:50%;
                        border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);
                        display:flex;align-items:center;justify-content:center;font-size:14px;line-height:1;">📍</div>`,
                        iconSize: [30, 30],
                        iconAnchor: [15, 15],
                        className: ''
                    });

                    let uMarker = null;
                    map.invalidateSize();

                    function haversine(lat1, lng1, lat2, lng2) {
                        const R = 6371000;
                        const dLat = (lat2 - lat1) * Math.PI / 180;
                        const dLng = (lng2 - lng1) * Math.PI / 180;
                        const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 *
                            Math.PI / 180) * Math.sin(dLng / 2) ** 2;
                        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    }

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(gpsOK, gpsFail, {
                            enableHighAccuracy: true,
                            timeout: 12000,
                            maximumAge: 30000
                        });
                    } else {
                        gpsFail();
                    }

                    function gpsOK(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const acc = Math.round(position.coords.accuracy);
                        const distance = Math.round(haversine(lat, lng, SCH_LAT, SCH_LNG));

                        document.getElementById('hidLat').value = lat;
                        document.getElementById('hidLng').value = lng;

                        if (uMarker) map.removeLayer(uMarker);

                        uMarker = L.marker([lat, lng], { icon: mkUser }).addTo(map)
                            .bindPopup(`<b>Posisi Anda</b><br>${lat.toFixed(5)}, ${lng.toFixed(5)}`);

                        map.fitBounds([[lat, lng], [SCH_LAT, SCH_LNG]], { padding: [30, 30] });

                        document.getElementById('coordTxt').textContent =
                            `🌐 ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                        document.getElementById('accTxt').textContent = `🎯 Akurasi: ±${acc}m`;

                        const gb = document.getElementById('gpsBadge');
                        const db = document.getElementById('distBadge');
                        const mt = document.getElementById('mapTxt');

                        if (distance <= RADIUS) {
                            gpsOk = true;
                            gb.style.background = '#dcfce7';
                            gb.style.color = '#15803d';
                            gb.textContent = '✓ Dalam Radius';
                            db.className = 'dbadge db-ok';
                            db.textContent = distance + 'm';
                            mt.textContent = 'Anda berada dalam radius sekolah';
                            mt.style.color = '#15803d';
                            step(2, 'done');
                            step(3, 'active');
                        } else {
                            gpsOk = false;
                            gb.style.background = '#fee2e2';
                            gb.style.color = '#dc2626';
                            gb.textContent = '✗ Luar Radius';
                            db.className = 'dbadge db-far';
                            db.textContent = distance + 'm';
                            mt.textContent = `Terlalu jauh (${distance}m dari sekolah)`;
                            mt.style.color = '#dc2626';

                            /* ── SweetAlert: lokasi terlalu jauh ── */
                            Swal.fire({
                                icon: 'warning',
                                title: 'Lokasi Terlalu Jauh',
                                html: `Anda berada <b>${distance}m</b> dari sekolah.<br>Maksimum radius yang diizinkan adalah <b>${RADIUS}m</b>.`,
                                confirmButtonColor: '#f59e0b',
                                confirmButtonText: 'Mengerti',
                            });
                        }
                        checkReady();
                    }

                    function gpsFail() {
                        const gb = document.getElementById('gpsBadge');
                        gb.style.background = '#fee2e2';
                        gb.style.color = '#dc2626';
                        gb.textContent = '✗ GPS Gagal';
                        document.getElementById('mapTxt').textContent =
                            'Izin lokasi ditolak atau GPS tidak tersedia';
                        document.getElementById('mapTxt').style.color = '#dc2626';

                        /* ── SweetAlert: GPS gagal ── */
                        Swal.fire({
                            icon: 'error',
                            title: 'GPS Tidak Tersedia',
                            text: 'Izin lokasi ditolak atau GPS tidak tersedia. Aktifkan lokasi dan muat ulang halaman.',
                            confirmButtonColor: '#dc2626',
                            confirmButtonText: 'Tutup',
                        });
                    }

                }, 100);
            }

            /* ─── CAMERA ──────────────────────────── */
            const vid = document.getElementById('liveVid');
            const img = document.getElementById('capImg');
            const ph = document.getElementById('ph');
            const sc = document.getElementById('sc');
            const cring = document.getElementById('cring');
            const cnum = document.getElementById('cnum');
            const cbadge = document.getElementById('cbadge');
            const arc = document.getElementById('arc');
            const sb = document.getElementById('selBadge');
            const cv = document.getElementById('cv');
            const cx = cv.getContext('2d');

            document.getElementById('btnCam').addEventListener('click', openCam);
            document.getElementById('btnRetake').addEventListener('click', openCam);
            document.getElementById('btnSnap').addEventListener('click', () => snap(true));

            async function openCam() {
                kill();
                blob = null;
                img.style.display = 'none';
                cbadge.style.display = 'none';

                ph.innerHTML =
                    '<div class="spinner"></div><p style="font-size:.72rem;line-height:1.4;">Membuka kamera...</p>';
                ph.style.display = 'block';
                sc.className = 'sc';
                sb.style.background = '#fef9c3';
                sb.style.color = '#b45309';
                sb.textContent = 'Bersiap…';

                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user',
                            width: { ideal: 640 },
                            height: { ideal: 640 }
                        }
                    });
                    vid.srcObject = stream;
                    vid.style.display = 'block';
                    ph.style.display = 'none';
                    sc.classList.add('on');
                    show('btnCam', false);
                    show('btnRetake', true);
                    show('btnSnap', true);
                    tick(5);
                } catch (e) {
                    console.error('Camera error:', e);
                    ph.innerHTML =
                        '<p style="font-size:.72rem;line-height:1.4;color:#dc2626;">❌ Kamera tidak bisa dibuka</p>';
                    sb.style.background = '#fee2e2';
                    sb.style.color = '#dc2626';
                    sb.textContent = 'Kamera gagal';
                    show('btnCam', true);

                    /* ── SweetAlert: kamera gagal ── */
                    Swal.fire({
                        icon: 'error',
                        title: 'Kamera Tidak Bisa Dibuka',
                        html: `Pastikan izin kamera sudah diberikan di browser.<br><small style="color:#64748b;">${e.message}</small>`,
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: 'Tutup',
                    });
                }
            }

            function tick(seconds) {
                ticking = true;
                let left = seconds;
                const C = 534;
                cring.style.display = 'block';
                cnum.style.display = 'flex';
                cnum.textContent = left;
                arc.style.strokeDashoffset = C;

                const interval = setInterval(() => {
                    left--;
                    cnum.textContent = left;
                    arc.style.strokeDashoffset = C * (1 - (seconds - left) / seconds);
                    if (left <= 0) {
                        clearInterval(interval);
                        if (ticking) snap(false);
                    }
                }, 1000);
            }

            function snap(manual) {
                if (!stream) return;
                ticking = false;
                cv.width = cv.height = 480;
                cx.translate(480, 0);
                cx.scale(-1, 1);
                cx.drawImage(vid, 0, 0, 480, 480);
                cx.setTransform(1, 0, 0, 1, 0, 0);

                cv.toBlob(b => {
                    blob = b;
                    img.src = URL.createObjectURL(b);
                    img.style.display = 'block';
                    vid.style.display = 'none';
                    cring.style.display = 'none';
                    cnum.style.display = 'none';
                    cbadge.style.display = 'flex';
                    sc.classList.remove('on');
                    sc.classList.add('got');
                    show('btnSnap', false);
                    sb.style.background = '#dcfce7';
                    sb.style.color = '#15803d';
                    sb.textContent = '✓ Siap';
                    step(3, 'done');
                    step(4, 'active');
                    kill();
                    checkReady();
                }, 'image/jpeg', 0.85);
            }

            function kill() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
            }

            /* ─── SUBMIT ──────────────────────────── */
            document.getElementById('absenForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!blob) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Foto Belum Diambil',
                        text: 'Silakan ambil foto selfie terlebih dahulu.',
                        confirmButtonColor: '#f59e0b',
                        confirmButtonText: 'OK',
                    });
                    return;
                }

                const btn = document.getElementById('btnSub');
                const lbl = document.getElementById('subLbl');
                btn.disabled = true;
                lbl.textContent = 'Mengirim…';

                const fd = new FormData(this);
                fd.append('foto_selfie', blob, 'selfie.jpg');

                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: fd
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Absen Berhasil! 🎉',
                            text: data.message || 'Absen Anda telah tercatat.',
                            confirmButtonColor: '#16a34a',
                            confirmButtonText: 'OK',
                            timer: 3000,
                            timerProgressBar: true,
                        });
                        window.location.href = data.redirect || window.location.href;
                    } else {
                        let errorMsg = '';
                        if (data.errors && typeof data.errors === 'object') {
                            errorMsg = '<ul style="text-align:left;padding-left:16px;">' +
                                Object.values(data.errors).flat().map(e => `<li>${e}</li>`).join('') +
                                '</ul>';
                        } else {
                            errorMsg = data.error || data.message || 'Terjadi kesalahan, silakan coba lagi.';
                        }

                        console.error('Server error:', data);
                        Swal.fire({
                            icon: 'error',
                            title: 'Absen Gagal',
                            html: errorMsg,
                            confirmButtonColor: '#dc2626',
                            confirmButtonText: 'Tutup',
                        });
                        lbl.textContent = 'Coba Lagi';
                    }
                } catch (error) {
                    console.error('Network/Parse error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Gagal',
                        text: 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda dan coba lagi.',
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: 'Tutup',
                    });
                    lbl.textContent = 'Absen Sekarang';
                } finally {
                    btn.disabled = false;
                }
            });

            /* ─── STATUS ──────────────────────────── */
            fetch('{{ route('absen.status-hari-ini') }}')
                .then(r => r.json())
                .then(d => {
                    if (d.sudahMasuk) {
                        document.getElementById('valM').textContent = 'Sudah Absen';
                        document.getElementById('valM').style.color = '#15803d';
                        document.getElementById('icM').textContent = '✅';
                        document.getElementById('icM').className = 'ci ci-g';
                    } else {
                        document.getElementById('valM').textContent = 'Belum Absen';
                    }
                    if (d.sudahPulang) {
                        document.getElementById('valP').textContent = 'Sudah Pulang';
                        document.getElementById('valP').style.color = '#1d4ed8';
                        document.getElementById('icP').textContent = '🏠';
                        document.getElementById('icP').className = 'ci ci-b';
                    } else {
                        document.getElementById('valP').textContent = 'Belum Pulang';
                    }
                })
                .catch(err => console.error('Status fetch error:', err));

            /* ─── UTILITY FUNCTIONS ────────────────────────────── */
            function checkReady() {
                const btn = document.getElementById('btnSub');
                const lbl = document.getElementById('subLbl');

                if (gpsOk && blob) {
                    btn.disabled = false;
                    lbl.textContent = 'Absen Sekarang';
                    step(4, 'active');
                } else if (!gpsOk) {
                    lbl.textContent = 'Menunggu GPS…';
                } else {
                    lbl.textContent = 'Ambil selfie dahulu';
                }
            }

            function step(n, s) {
                const e = document.getElementById('step' + n);
                if (!e) return;
                e.className = 'step ' + s;
                if (s === 'done') {
                    e.querySelector('.step-dot').textContent = '✓';
                }
            }

            function show(id, v) {
                document.getElementById(id).style.display = v ? 'inline-flex' : 'none';
            }

            // Initialize map when page loaded
            initMap();

            // Auto open camera on page load
            setTimeout(() => {
                openCam();
            }, 500);

        });
    </script>
@endpush