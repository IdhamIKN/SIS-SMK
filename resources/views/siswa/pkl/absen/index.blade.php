@extends('layouts.app')
@section('title', 'Absen PKL')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        :root {
            --pkl-primary: #f59e0b;
            --pkl-dark: #b45309;
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

        .page-strip-pkl-masuk  { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); }
        .page-strip-pkl-pulang { background: linear-gradient(135deg, #0ea5e9 0%, #1d4ed8 100%); }

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
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.3);
            border-radius: 20px; padding: 3px 10px; font-size: .7rem; color: #fff;
            font-weight: 600; margin-bottom: 8px;
        }
        .live-dot {
            width: 6px; height: 6px; background: #4ade80; border-radius: 50%;
            animation: blink 1.5s ease infinite;
        }
        @keyframes blink { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.3)} }
        .page-strip h2 { font-size: 1.15rem; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px; position: relative; }
        .page-strip p  { font-size: .77rem; color: rgba(255,255,255,.8); margin-top: 3px; position: relative; }

        /* Lokasi PKL info card */
        .lokasi-card {
            background: #fffbeb; border: 1px solid #fcd34d; border-radius: 12px;
            padding: 12px 14px; margin-bottom: 14px;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .lokasi-icon {
            width: 38px; height: 38px; background: #fef3c7; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;
        }
        .lokasi-name { font-weight: 700; font-size: .88rem; color: #92400e; }
        .lokasi-sub  { font-size: .72rem; color: #b45309; margin-top: 2px; }
        .lokasi-jam  { font-size: .7rem; color: #78350f; margin-top: 4px; background: rgba(245,158,11,.15); border-radius: 6px; padding: 3px 8px; display: inline-block; }

        /* Steps */
        .steps { display: flex; margin-bottom: 14px; }
        .step { flex: 1; text-align: center; position: relative; }
        .step::after { content: ''; position: absolute; top: 13px; left: 50%; width: 100%; height: 2px; background: var(--border); }
        .step:last-child::after { display: none; }
        .step.done::after { background: var(--pkl-primary); }
        .step-dot {
            width: 26px; height: 26px; border-radius: 50%; background: var(--border);
            color: var(--text-muted); font-size: .68rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 4px; position: relative; z-index: 1; transition: all .3s;
        }
        .step.active .step-dot { background: var(--pkl-primary); color: #fff; }
        .step.done .step-dot   { background: var(--green-primary); color: #fff; }
        .step-lbl { font-size: .6rem; color: var(--text-muted); font-weight: 500; }
        .step.active .step-lbl, .step.done .step-lbl { color: var(--text-main); }

        /* Status bar */
        .status-bar { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
        .s-chip {
            background: var(--card); border: 1px solid var(--border); border-radius: 12px;
            padding: 10px 12px; display: flex; align-items: center; gap: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
        }
        .ci { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; background: #f1f5f9; }
        .ci-g { background: #dcfce7; }
        .ci-b { background: #dbeafe; }
        .c-lbl { font-size: .65rem; color: var(--text-muted); }
        .c-val { font-size: .8rem; font-weight: 600; margin-top: 1px; }

        /* Card */
        .card { background: var(--card); border-radius: 14px; border: 1px solid var(--border); margin-bottom: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
        .c-head { padding: 12px 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px; }
        .c-icon { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .9rem; }
        .c-head h3 { font-size: .87rem; font-weight: 600; color: var(--text-main); }
        .hbadge { margin-left: auto; font-size: .68rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; background: #f1f5f9; color: #64748b; }

        /* Map */
        .map-wrapper { position: relative; height: 200px; width: 100%; margin-bottom: 12px; border-radius: 12px; overflow: hidden; }
        #map { height: 200px; width: 100%; position: relative; background-color: #f1f5f9; border-radius: 12px 12px 0 0; z-index: 1; }
        .map-bar {
            position: absolute; bottom: 8px; left: 8px; right: 8px;
            background: rgba(255,255,255,.93); backdrop-filter: blur(6px);
            border-radius: 10px; padding: 7px 12px; z-index: 999; font-size: .74rem;
            display: flex; align-items: center; gap: 8px; border: 1px solid rgba(255,255,255,.8);
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
        }
        .dbadge { font-size: .68rem; font-weight: 600; padding: 3px 8px; border-radius: 12px; }
        .db-ok   { background: #dcfce7; color: #15803d; }
        .db-far  { background: #fee2e2; color: #dc2626; }
        .db-wait { background: #f1f5f9; color: #64748b; }
        .map-info { padding: 8px 14px; font-size: .72rem; color: var(--text-muted); display: flex; gap: 12px; background: #f8fafc; border-top: 1px solid var(--border); }
        .map-info span { flex: 1; }

        /* Camera */
        .c-body { padding: 12px; }
        .cam-section { display: flex; flex-direction: column; align-items: center; gap: 12px; }
        .sw { position: relative; width: 170px; height: 170px; }
        .sc { width: 170px; height: 170px; border-radius: 50%; overflow: hidden; background: #f1f5f9; border: 3px solid var(--border); display: flex; align-items: center; justify-content: center; transition: border-color .3s, box-shadow .3s; }
        .sc.on  { border-color: var(--blue-accent); box-shadow: 0 0 0 2px rgba(14,165,233,.2); }
        .sc.got { border-color: var(--green-primary); box-shadow: 0 0 0 2px rgba(22,163,74,.2); }
        .ph { text-align: center; color: var(--text-muted); }
        .cring  { position: absolute; inset: -3px; border-radius: 50%; display: none; pointer-events: none; }
        .cnum   { position: absolute; bottom: 0; right: 0; width: 34px; height: 34px; border-radius: 50%; display: none; align-items: center; justify-content: center; font-weight: 700; font-size: .95rem; border: 2px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,.2); background: var(--blue-accent); color: #fff; }
        .cbadge { position: absolute; bottom: 0; right: 0; width: 34px; height: 34px; border-radius: 50%; display: none; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; border: 2px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,.2); background: var(--green-primary); color: #fff; }
        .cam-btns { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }
        .btn { padding: 9px 18px; border-radius: 9px; font-size: .82rem; font-weight: 600; border: none; cursor: pointer; transition: all .2s; font-family: inherit; display: inline-flex; align-items: center; gap: 7px; }
        .btn-b { background: var(--blue-accent); color: #fff; }
        .btn-b:hover:not(:disabled) { background: #0284c7; }
        .btn-o { background: transparent; color: var(--text-muted); border: 1px solid var(--border); }
        .cam-hint { font-size: .71rem; color: var(--text-muted); text-align: center; }

        /* Submit button */
        .btn-sub {
            width: 100%; padding: 14px; border-radius: 12px; background: var(--pkl-primary);
            color: #fff; font-size: .95rem; font-weight: 700; border: none; cursor: pointer;
            box-shadow: 0 4px 14px rgba(245,158,11,.35); transition: all .2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-sub.pulang { background: var(--blue-accent); box-shadow: 0 4px 14px rgba(14,165,233,.35); }
        .btn-sub:hover:not(:disabled) { opacity: .9; }
        .btn-sub:disabled { background: #94a3b8; cursor: not-allowed; box-shadow: none; }

        /* Spinner */
        .spinner { border: 3px solid #f1f5f9; border-top: 3px solid var(--blue-accent); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 8px; }
        @keyframes spin { 0%{transform:rotate(0deg)} 100%{transform:rotate(360deg)} }

        /* Time window */
        .time-window {
            background: rgba(255,255,255,.9); backdrop-filter: blur(10px); border-radius: 12px;
            padding: 12px; text-align: center; font-size: .78rem;
            border-left: 4px solid var(--pkl-primary); margin: 12px 0;
        }

        /* Completed state */
        .absen-selesai {
            background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px;
            padding: 24px 20px; text-align: center; margin-bottom: 14px;
        }
        .selesai-icon { font-size: 3rem; margin-bottom: 8px; }
        .selesai-title { font-size: 1.1rem; font-weight: 800; color: #15803d; margin-bottom: 4px; }
        .selesai-sub   { font-size: .8rem; color: #166534; }

        .swal2-popup { font-family: 'Plus Jakarta Sans', sans-serif !important; }
    </style>
@endpush

@section('content')
<div class="absen-wrap">

    {{-- Header strip --}}
    @php
        $jenis    = $status['sudahMasuk'] ? 'pulang' : 'masuk';
        $selesai  = $status['sudahMasuk'] && $status['sudahPulang'];
        $stripCls = $status['sudahMasuk'] ? 'page-strip-pkl-pulang' : 'page-strip-pkl-masuk';
    @endphp

    <div class="page-strip {{ $stripCls }}">
        <div class="live-badge">
            <span class="live-dot"></span>
            PKL · {{ now()->translatedFormat('l, d F Y, H:i') }}
        </div>
        <h2>
            <i class="fas fa-{{ $selesai ? 'check-double' : ($status['sudahMasuk'] ? 'sign-out-alt' : 'sign-in-alt') }}"></i>
            @if($selesai) Absen PKL Selesai
            @elseif($status['sudahMasuk']) Absen Pulang PKL
            @else Absen Masuk PKL
            @endif
        </h2>
        <p>{{ $penugasan->lokasiPkl?->nama_tempat ?? 'Lokasi PKL' }}</p>
    </div>

    {{-- Info Lokasi PKL --}}
    <div class="lokasi-card">
        <div class="lokasi-icon">🏢</div>
        <div>
            <div class="lokasi-name">{{ $lokasi->nama_tempat }}</div>
            <div class="lokasi-sub">{{ $lokasi->alamat }}{{ $lokasi->kabupaten ? ', '.$lokasi->kabupaten : '' }}</div>
            @php
                $masukFmt   = $jadwal['masuk']   ? date('H:i', strtotime($jadwal['masuk'])) : '-';
                $pulangFmt  = $jadwal['pulang']  ? date('H:i', strtotime($jadwal['pulang'])) : '-';
                $terlambatFmt = $jadwal['batas_terlambat'] ? date('H:i', strtotime($jadwal['batas_terlambat'])) : '-';
            @endphp
            <span class="lokasi-jam">
                ⏰ Masuk: {{ $masukFmt }} &nbsp;|&nbsp; Terlambat: > {{ $terlambatFmt }} &nbsp;|&nbsp; Pulang: {{ $pulangFmt }}
            </span>
        </div>
    </div>

    {{-- Absen sudah selesai (masuk + pulang) --}}
    @if($selesai)
        <div class="absen-selesai">
            <div class="selesai-icon">🎉</div>
            <div class="selesai-title">Absen PKL Hari Ini Lengkap!</div>
            <div class="selesai-sub">
                Masuk: {{ $status['absen']->jam_masuk ? date('H:i', strtotime($status['absen']->jam_masuk)) : '-' }}
                &nbsp;·&nbsp;
                Pulang: {{ $status['absen']->jam_pulang ? date('H:i', strtotime($status['absen']->jam_pulang)) : '-' }}
            </div>
            <div style="margin-top:12px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                <a href="{{ route('siswa.pkl.dashboard') }}" class="btn btn-b" style="background:#16a34a;">
                    <i class="fas fa-home"></i> Dashboard PKL
                </a>
                <a href="{{ route('siswa.pkl.jurnal.create') }}" class="btn" style="background:#f59e0b;color:#fff;">
                    <i class="fas fa-book"></i> Isi Jurnal
                </a>
            </div>
        </div>
    @else
        {{-- Steps --}}
        <div class="steps">
            <div class="step done" id="step1"><div class="step-dot">✓</div><div class="step-lbl">Login</div></div>
            <div class="step active" id="step2"><div class="step-dot">2</div><div class="step-lbl">Lokasi</div></div>
            <div class="step" id="step3"><div class="step-dot">3</div><div class="step-lbl">Selfie</div></div>
            <div class="step" id="step4"><div class="step-dot">4</div><div class="step-lbl">Kirim</div></div>
        </div>

        {{-- Status masuk / pulang --}}
        <div class="status-bar">
            <div class="s-chip">
                <div class="ci {{ $status['sudahMasuk'] ? 'ci-g' : '' }}" id="icM">
                    {{ $status['sudahMasuk'] ? '✅' : '⏳' }}
                </div>
                <div>
                    <div class="c-lbl">Masuk PKL</div>
                    <div class="c-val" id="valM">
                        {{ $status['sudahMasuk'] ? ($status['absen']->jam_masuk ? date('H:i', strtotime($status['absen']->jam_masuk)) : 'Sudah') : 'Belum' }}
                    </div>
                </div>
            </div>
            <div class="s-chip">
                <div class="ci {{ $status['sudahPulang'] ? 'ci-b' : '' }}" id="icP">
                    {{ $status['sudahPulang'] ? '🏠' : '⏳' }}
                </div>
                <div>
                    <div class="c-lbl">Pulang PKL</div>
                    <div class="c-val" id="valP">
                        {{ $status['sudahPulang'] ? ($status['absen']->jam_pulang ? date('H:i', strtotime($status['absen']->jam_pulang)) : 'Sudah') : 'Belum' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Jadwal absen --}}
        <div class="time-window">
            <strong>⏰ Waktu Absen {{ ucfirst($jenis) }} PKL:</strong><br>
            @if($jenis === 'masuk')
                {{ $masukFmt }} – {{ $jadwal['batas_masuk'] ? date('H:i', strtotime($jadwal['batas_masuk'])) : '-' }}
                &nbsp;|&nbsp; Terlambat setelah {{ $terlambatFmt }}
            @else
                Mulai {{ $pulangFmt }}
            @endif
        </div>

        {{-- Selfie Card --}}
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:#fef9c3;">{{ $status['sudahMasuk'] ? '🏠' : '📸' }}</div>
                <h3>{{ $status['sudahMasuk'] ? 'Foto Pulang PKL' : 'Foto Selfie Masuk PKL' }}</h3>
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
                                    stroke="#f59e0b" stroke-width="3" stroke-dasharray="534"
                                    stroke-dashoffset="534" stroke-linecap="round" />
                            </svg>
                        </div>
                        <div class="cnum" id="cnum"></div>
                        <div class="cbadge" id="cbadge">✓</div>
                    </div>
                    <div class="cam-btns">
                        <button type="button" id="btnCam" class="btn btn-b" style="display:none;">
                            <i class="fas fa-video"></i> Buka Kamera
                        </button>
                        <button type="button" id="btnRetake" class="btn btn-o" style="display:none;">
                            <i class="fas fa-redo"></i> Ulangi
                        </button>
                        <button type="button" id="btnSnap" class="btn btn-b" style="display:none;">
                            📸 Ambil Sekarang
                        </button>
                    </div>
                    <p class="cam-hint">Foto otomatis 5 detik setelah kamera terbuka.<br>Pastikan wajah jelas.</p>
                </div>
            </div>
        </div>

        {{-- Form absen --}}
        <form id="absenForm" method="POST"
              action="{{ route('siswa.pkl.absen.store', $jenis) }}"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="latitude"  id="hidLat">
            <input type="hidden" name="longitude" id="hidLng">
            <canvas id="cv" style="display:none;"></canvas>

            <button type="submit" id="btnSub" disabled
                    class="btn-sub {{ $jenis === 'pulang' ? 'pulang' : '' }}">
                <i class="fas fa-{{ $jenis === 'pulang' ? 'sign-out-alt' : 'sign-in-alt' }}"></i>
                <span id="subLbl">{{ $jenis === 'pulang' ? 'Konfirmasi Pulang PKL' : 'Absen Masuk PKL Sekarang' }}</span>
            </button>
        </form>
        <br>

        {{-- Peta Lokasi PKL --}}
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:#fef3c7;">📍</div>
                <h3>Lokasi Saat Ini</h3>
                <span class="hbadge" id="gpsBadge">Mendeteksi GPS...</span>
            </div>
            <div class="map-wrapper">
                <div id="map"></div>
                <div class="map-bar">
                    <i class="fas fa-map-marker-alt" style="color:#f59e0b;font-size:.8rem;"></i>
                    <span id="mapTxt">Mendapatkan lokasi...</span>
                    <span class="dbadge db-wait" id="distBadge">— m</span>
                </div>
            </div>
            <div class="map-info">
                <span id="coordTxt">🌐 —</span>
                <span id="accTxt">🎯 Akurasi: — m</span>
            </div>
        </div>

        {{-- Navigasi --}}
        <div style="display:flex;gap:8px;margin-top:4px;">
            <a href="{{ route('siswa.pkl.dashboard') }}"
               style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:11px;border-radius:12px;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;font-size:.8rem;font-weight:700;text-decoration:none;">
                <i class="fas fa-home"></i> Dashboard PKL
            </a>
        </div>
    @endif

</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // Koordinat lokasi PKL
        const PKL_LAT    = @json((float)($lokasi->latitude  ?? 0));
        const PKL_LNG    = @json((float)($lokasi->longitude ?? 0));
        const PKL_RADIUS = @json((int)$radius);
        const PKL_NAMA   = @json($lokasi->nama_tempat);

        @if($selesai)
        // Sudah selesai, tidak perlu kamera/GPS
        return;
        @endif

        let gpsOk = false, blob = null, stream = null, ticking = false, map = null;

        /* ── FLASH MESSAGES ── */
        @if(session('success'))
        Swal.fire({ icon:'success', title:'Berhasil!', text:'{{ session("success") }}',
            confirmButtonColor:'#f59e0b', timer:4000, timerProgressBar:true });
        @endif
        @if($errors->any())
        Swal.fire({ icon:'error', title:'Validasi Gagal',
            html:'<ul style="text-align:left;padding-left:16px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>',
            confirmButtonColor:'#dc2626' });
        @endif
        @if($status['sudahMasuk'] && !$status['sudahPulang'])
        Swal.fire({ icon:'info', title:'Sudah Absen Masuk',
            text: 'Masuk: {{ $status["absen"]?->jam_masuk ? date("H:i", strtotime($status["absen"]->jam_masuk)) : "-" }}. Sekarang lakukan absen pulang PKL.',
            confirmButtonColor:'#f59e0b', confirmButtonText:'OK' });
        @endif

        /* ── MAP ── */
        function initMap() {
            setTimeout(() => {
                const el = document.getElementById('map');
                if (!el) return;

                map = L.map('map', { zoomControl: false, scrollWheelZoom: false })
                        .setView([PKL_LAT || -6.2, PKL_LNG || 106.8], 16);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap', maxZoom: 19
                }).addTo(map);

                L.control.zoom({ position: 'topright' }).addTo(map);

                // Marker lokasi PKL
                if (PKL_LAT && PKL_LNG) {
                    const mkPkl = L.divIcon({
                        html: `<div style="background:#f59e0b;width:36px;height:36px;border-radius:50%;
                               border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);
                               display:flex;align-items:center;justify-content:center;font-size:18px;">🏢</div>`,
                        iconSize: [36,36], iconAnchor: [18,18], className: ''
                    });
                    L.marker([PKL_LAT, PKL_LNG], { icon: mkPkl })
                     .addTo(map)
                     .bindPopup(`<b>${PKL_NAMA}</b><br>Radius ${PKL_RADIUS}m`);

                    L.circle([PKL_LAT, PKL_LNG], {
                        radius: PKL_RADIUS, color: '#f59e0b', fillColor: '#f59e0b',
                        fillOpacity: 0.08, weight: 2, dashArray: '5,5'
                    }).addTo(map);
                }

                // Marker user
                const mkUser = L.divIcon({
                    html: `<div style="background:#16a34a;width:30px;height:30px;border-radius:50%;
                           border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);
                           display:flex;align-items:center;justify-content:center;font-size:14px;">📍</div>`,
                    iconSize: [30,30], iconAnchor: [15,15], className: ''
                });

                let uMarker = null;
                map.invalidateSize();

                function haversine(lat1,lng1,lat2,lng2) {
                    const R=6371000, dLat=((lat2-lat1)*Math.PI/180), dLng=((lng2-lng1)*Math.PI/180);
                    const a=Math.sin(dLat/2)**2+Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
                    return R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));
                }

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(gpsOK, gpsFail, {
                        enableHighAccuracy: true, timeout: 12000, maximumAge: 30000
                    });
                } else { gpsFail(); }

                function gpsOK(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    const acc = Math.round(pos.coords.accuracy);
                    const dist = Math.round(haversine(lat, lng, PKL_LAT, PKL_LNG));

                    document.getElementById('hidLat').value = lat;
                    document.getElementById('hidLng').value = lng;

                    if (uMarker) map.removeLayer(uMarker);
                    uMarker = L.marker([lat,lng],{icon:mkUser}).addTo(map)
                        .bindPopup(`<b>Posisi Anda</b><br>${lat.toFixed(5)}, ${lng.toFixed(5)}`);

                    if (PKL_LAT && PKL_LNG) {
                        map.fitBounds([[lat,lng],[PKL_LAT,PKL_LNG]], { padding:[30,30] });
                    } else {
                        map.setView([lat,lng],16);
                    }

                    document.getElementById('coordTxt').textContent = `🌐 ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                    document.getElementById('accTxt').textContent   = `🎯 Akurasi: ±${acc}m`;

                    const gb = document.getElementById('gpsBadge');
                    const db = document.getElementById('distBadge');
                    const mt = document.getElementById('mapTxt');

                    if (dist <= PKL_RADIUS) {
                        gpsOk = true;
                        gb.style.background='#dcfce7'; gb.style.color='#15803d'; gb.textContent='✓ Dalam Radius';
                        db.className='dbadge db-ok'; db.textContent=dist+'m';
                        mt.textContent='Anda berada dalam radius tempat PKL'; mt.style.color='#15803d';
                        step(2,'done'); step(3,'active');
                    } else {
                        gpsOk = false;
                        gb.style.background='#fee2e2'; gb.style.color='#dc2626'; gb.textContent='✗ Luar Radius';
                        db.className='dbadge db-far'; db.textContent=dist+'m';
                        mt.textContent=`Terlalu jauh (${dist}m dari PKL)`; mt.style.color='#dc2626';
                        Swal.fire({ icon:'warning', title:'Lokasi Terlalu Jauh',
                            html:`Anda <b>${dist}m</b> dari <b>${PKL_NAMA}</b>.<br>Maksimal <b>${PKL_RADIUS}m</b>.`,
                            confirmButtonColor:'#f59e0b' });
                    }
                    checkReady();
                }

                function gpsFail() {
                    const gb = document.getElementById('gpsBadge');
                    gb.style.background='#fee2e2'; gb.style.color='#dc2626'; gb.textContent='✗ GPS Gagal';
                    document.getElementById('mapTxt').textContent = 'Izin lokasi ditolak atau GPS tidak tersedia';
                    document.getElementById('mapTxt').style.color = '#dc2626';
                    Swal.fire({ icon:'error', title:'GPS Tidak Tersedia',
                        text:'Aktifkan lokasi dan muat ulang halaman.', confirmButtonColor:'#dc2626' });
                }
            }, 100);
        }

        /* ── CAMERA ── */
        const vid=document.getElementById('liveVid'), img=document.getElementById('capImg'),
              ph=document.getElementById('ph'), sc=document.getElementById('sc'),
              cring=document.getElementById('cring'), cnum=document.getElementById('cnum'),
              cbadge=document.getElementById('cbadge'), arc=document.getElementById('arc'),
              sb=document.getElementById('selBadge'), cv=document.getElementById('cv'),
              cx=cv.getContext('2d');

        document.getElementById('btnCam').addEventListener('click', openCam);
        document.getElementById('btnRetake').addEventListener('click', openCam);
        document.getElementById('btnSnap').addEventListener('click', () => snap(true));

        async function openCam() {
            kill(); blob=null;
            img.style.display='none'; cbadge.style.display='none';
            ph.innerHTML='<div class="spinner"></div><p style="font-size:.72rem;">Membuka kamera...</p>';
            ph.style.display='block'; sc.className='sc';
            sb.style.background='#fef9c3'; sb.style.color='#b45309'; sb.textContent='Bersiap…';
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video:{ facingMode:'user', width:{ideal:640}, height:{ideal:640} }
                });
                vid.srcObject=stream; vid.style.display='block'; ph.style.display='none';
                sc.classList.add('on');
                show('btnCam',false); show('btnRetake',true); show('btnSnap',true);
                tick(5);
            } catch(e) {
                ph.innerHTML='<p style="font-size:.72rem;color:#dc2626;">❌ Kamera tidak bisa dibuka</p>';
                sb.style.background='#fee2e2'; sb.style.color='#dc2626'; sb.textContent='Kamera gagal';
                show('btnCam',true);
                Swal.fire({ icon:'error', title:'Kamera Gagal', text:e.message, confirmButtonColor:'#dc2626' });
            }
        }

        function tick(s) {
            ticking=true; let left=s; const C=534;
            cring.style.display='block'; cnum.style.display='flex'; cnum.textContent=left;
            arc.style.strokeDashoffset=C;
            const iv=setInterval(()=>{
                left--; cnum.textContent=left;
                arc.style.strokeDashoffset=C*(1-(s-left)/s);
                if(left<=0){ clearInterval(iv); if(ticking) snap(false); }
            },1000);
        }

        function snap(manual) {
            if(!stream) return; ticking=false;
            cv.width=cv.height=480;
            cx.translate(480,0); cx.scale(-1,1); cx.drawImage(vid,0,0,480,480); cx.setTransform(1,0,0,1,0,0);
            cv.toBlob(b=>{
                blob=b; img.src=URL.createObjectURL(b); img.style.display='block';
                vid.style.display='none'; cring.style.display='none'; cnum.style.display='none';
                cbadge.style.display='flex'; sc.classList.remove('on'); sc.classList.add('got');
                show('btnSnap',false);
                sb.style.background='#dcfce7'; sb.style.color='#15803d'; sb.textContent='✓ Siap';
                step(3,'done'); step(4,'active');
                kill(); checkReady();
            },'image/jpeg',0.85);
        }

        function kill() {
            if(stream){ stream.getTracks().forEach(t=>t.stop()); stream=null; }
        }

        /* ── SUBMIT ── */
        document.getElementById('absenForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            if(!blob) {
                Swal.fire({ icon:'warning', title:'Foto Belum Diambil',
                    text:'Ambil foto selfie terlebih dahulu.', confirmButtonColor:'#f59e0b' });
                return;
            }
            const btn=document.getElementById('btnSub'), lbl=document.getElementById('subLbl');
            btn.disabled=true; lbl.textContent='Mengirim…';
            const fd=new FormData(this);
            fd.append('foto_selfie', blob, 'selfie.jpg');
            try {
                const res = await fetch(this.action, {
                    method:'POST',
                    headers:{ 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' },
                    body: fd
                });
                const data = await res.json();
                if(res.ok && data.success) {
                    await Swal.fire({ icon:'success', title:'Absen PKL Berhasil! 🎉',
                        text: data.message, confirmButtonColor:'#f59e0b',
                        timer:3000, timerProgressBar:true });
                    window.location.href = data.redirect || window.location.href;
                } else {
                    let msg='';
                    if(data.errors && typeof data.errors==='object') {
                        msg='<ul style="text-align:left;padding-left:16px;">'+
                            Object.values(data.errors).flat().map(e=>`<li>${e}</li>`).join('')+'</ul>';
                    } else { msg=data.error||'Terjadi kesalahan.'; }
                    Swal.fire({ icon:'error', title:'Absen Gagal', html:msg, confirmButtonColor:'#dc2626' });
                    lbl.textContent='Coba Lagi';
                }
            } catch(err) {
                Swal.fire({ icon:'error', title:'Koneksi Gagal', text:'Periksa koneksi dan coba lagi.', confirmButtonColor:'#dc2626' });
                lbl.textContent='Absen Sekarang';
            } finally { btn.disabled=false; }
        });

        /* ── UTILITY ── */
        function checkReady() {
            const btn=document.getElementById('btnSub'), lbl=document.getElementById('subLbl');
            if(gpsOk && blob) { btn.disabled=false; lbl.textContent='{{ $jenis==="pulang" ? "Konfirmasi Pulang PKL" : "Absen Masuk PKL Sekarang" }}'; step(4,'active'); }
            else if(!gpsOk)   { lbl.textContent='Menunggu GPS…'; }
            else               { lbl.textContent='Ambil selfie dahulu'; }
        }

        function step(n,s) {
            const e=document.getElementById('step'+n); if(!e) return;
            e.className='step '+s;
            if(s==='done') e.querySelector('.step-dot').textContent='✓';
        }

        function show(id,v) { document.getElementById(id).style.display=v?'inline-flex':'none'; }

        initMap();
        setTimeout(() => openCam(), 500);
    });
    </script>
@endpush
