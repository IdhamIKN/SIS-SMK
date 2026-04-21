@extends('layouts.app')

@section('title', 'Absen Siswa')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

    :root {
        --green: #16a34a;
        --blue: #0ea5e9;
        --border: #e2e8f0;
        --muted: #64748b;
        --main: #0f172a;
    }

    .absen-wrap {
        max-width: 520px;
        margin: 0 auto;
        padding: 14px 14px 100px;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* ── PAGE STRIP ── */
    .page-strip {
        border-radius: 16px;
        padding: 16px 18px;
        margin-bottom: 14px;
        position: relative;
        overflow: hidden;
    }
    .ps-masuk  { background: linear-gradient(135deg, #16a34a, #22c55e); }
    .ps-pulang { background: linear-gradient(135deg, #0ea5e9, #1d4ed8); }
    .page-strip::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4z'/%3E%3C/g%3E%3C/svg%3E");
    }
    .live-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.3);
        border-radius: 20px; padding: 3px 10px; font-size: .7rem;
        color: #fff; font-weight: 600; margin-bottom: 8px;
    }
    .live-dot {
        width: 6px; height: 6px; background: #4ade80;
        border-radius: 50%; animation: blink 1.5s ease infinite;
    }
    @keyframes blink { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.3)} }
    .page-strip h2 { font-size: 1.15rem; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px; position: relative; }
    .page-strip p  { font-size: .77rem; color: rgba(255,255,255,.8); margin-top: 3px; position: relative; }

    /* ── STEPS ── */
    .steps { display: flex; margin-bottom: 14px; }
    .step  { flex: 1; text-align: center; position: relative; }
    .step::after { content:''; position:absolute; top:13px; left:50%; width:100%; height:2px; background:var(--border); }
    .step:last-child::after { display: none; }
    .step.done::after { background: var(--green); }
    .step-dot {
        width: 26px; height: 26px; border-radius: 50%; background: var(--border);
        color: var(--muted); font-size: .68rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 4px; position: relative; z-index: 1; transition: all .3s;
    }
    .step.active .step-dot { background: var(--blue); color: #fff; }
    .step.done   .step-dot { background: var(--green); color: #fff; }
    .step-lbl { font-size: .6rem; color: var(--muted); font-weight: 500; }
    .step.active .step-lbl, .step.done .step-lbl { color: var(--main); }

    /* ── STATUS CHIPS ── */
    .status-bar { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
    .s-chip {
        background: #fff; border: 1px solid var(--border); border-radius: 12px;
        padding: 10px 12px; display: flex; align-items: center; gap: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
    }
    .ci { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; background: #f1f5f9; }
    .ci-g { background: #dcfce7; }
    .ci-b { background: #dbeafe; }
    .c-lbl { font-size: .65rem; color: var(--muted); }
    .c-val { font-size: .8rem; font-weight: 600; margin-top: 1px; }

    /* ── CARDS ── */
    .card {
        background: #fff; border-radius: 14px; border: 1px solid var(--border);
        margin-bottom: 12px; overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 14px rgba(0,0,0,.03);
    }
    .c-head {
        padding: 12px 16px; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: 10px;
    }
    .c-icon { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .9rem; }
    .c-head h3 { font-size: .87rem; font-weight: 600; color: var(--main); flex: 1; }
    .hbadge { font-size: .68rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; background: #f1f5f9; color: #64748b; white-space: nowrap; }

    /* ── ALERTS ── */
    .alert { border-radius: 10px; padding: 11px 14px; font-size: .82rem; display: flex; align-items: flex-start; gap: 8px; margin-bottom: 12px; }
    .a-ok   { background: #f0fdf4; border: 1px solid #86efac; color: #15803d; }
    .a-err  { background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626; }
    .a-warn { background: #fffbeb; border: 1px solid #fcd34d; color: #b45309; }

    /* ── MAP ── */
    /* KEY FIX: jangan overflow:hidden di wrapper - bikin leaflet salah ukur */
    .map-wrapper { position: relative; height: 210px; width: 100%; }
    #map { height: 210px; width: 100%; }

    .map-bar {
        position: absolute; bottom: 8px; left: 8px; right: 8px;
        background: rgba(255,255,255,.93); backdrop-filter: blur(6px);
        border-radius: 10px; padding: 7px 12px; z-index: 1000;
        font-size: .74rem; display: flex; align-items: center; gap: 8px;
        border: 1px solid rgba(255,255,255,.8);
        box-shadow: 0 2px 8px rgba(0,0,0,.1);
    }
    .dbadge { font-size: .68rem; font-weight: 600; padding: 3px 8px; border-radius: 12px; }
    .db-ok   { background: #dcfce7; color: #15803d; }
    .db-far  { background: #fee2e2; color: #dc2626; }
    .db-wait { background: #f1f5f9; color: #64748b; }
    .map-info {
        padding: 8px 14px; font-size: .72rem; color: var(--muted);
        display: flex; gap: 12px; background: #f8fafc;
        border-top: 1px solid var(--border);
    }

    /* ── GPS LOADING STATE ── */
    .gps-loading {
        display: flex; align-items: center; gap: 8px;
        font-size: .75rem; color: var(--muted);
    }
    .gps-spinner {
        width: 14px; height: 14px;
        border: 2px solid #e2e8f0; border-top-color: var(--blue);
        border-radius: 50%; animation: spin .8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── SELFIE ── */
    .c-body { padding: 12px; }
    .cam-section { display: flex; flex-direction: column; align-items: center; gap: 12px; }
    .sw { position: relative; width: 170px; height: 170px; }
    .sc {
        width: 170px; height: 170px; border-radius: 50%; overflow: hidden;
        background: #f1f5f9; border: 3px solid var(--border);
        display: flex; align-items: center; justify-content: center;
        transition: border-color .3s, box-shadow .3s;
    }
    .sc.on  { border-color: var(--blue); box-shadow: 0 0 0 2px rgba(14,165,233,.2); }
    .sc.got { border-color: var(--green); box-shadow: 0 0 0 2px rgba(22,163,74,.2); }
    .ph { text-align: center; color: var(--muted); }
    .cring { position: absolute; inset: -3px; border-radius: 50%; display: none; pointer-events: none; }
    .cnum  { position: absolute; bottom: 0; right: 0; width: 34px; height: 34px; border-radius: 50%; display: none; align-items: center; justify-content: center; font-weight: 700; font-size: .95rem; border: 2px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,.2); background: var(--blue); color: #fff; }
    .cbadge{ position: absolute; bottom: 0; right: 0; width: 34px; height: 34px; border-radius: 50%; display: none; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; border: 2px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,.2); background: var(--green); color: #fff; }
    .cam-btns { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }
    .btn { padding: 9px 18px; border-radius: 9px; font-size: .82rem; font-weight: 600; border: none; cursor: pointer; transition: all .2s; font-family: inherit; display: inline-flex; align-items: center; gap: 7px; }
    .btn-b { background: var(--blue); color: #fff; }
    .btn-b:hover { background: #0284c7; }
    .btn-o { background: transparent; color: var(--muted); border: 1px solid var(--border); }
    .cam-hint { font-size: .71rem; color: var(--muted); text-align: center; }

    .spinner { border: 3px solid #f1f5f9; border-top: 3px solid var(--blue); border-radius: 50%; width: 36px; height: 36px; animation: spin .9s linear infinite; margin: 0 auto 8px; }

    /* ── SUBMIT ── */
    .btn-sub {
        width: 100%; padding: 14px; border-radius: 12px;
        background: var(--green); color: #fff; font-size: .95rem;
        font-weight: 700; border: none; cursor: pointer;
        box-shadow: 0 4px 14px rgba(22,163,74,.35); transition: all .2s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        font-family: inherit;
    }
    .btn-sub:hover:not(:disabled) { background: #15803d; }
    .btn-sub:disabled { background: #94a3b8; box-shadow: none; cursor: not-allowed; }

    .time-window {
        background: rgba(255,255,255,.9); backdrop-filter: blur(10px);
        border-radius: 12px; padding: 12px; text-align: center;
        font-size: .78rem; border-left: 4px solid var(--green); margin: 12px 0;
    }
</style>
@endpush

@section('content')
@php
    $jenis     = $status['sudahMasuk'] ? 'pulang' : 'masuk';
    $shift     = config('sekolah.jam_shift.pagi');
    $waktuValid = $jenis === 'masuk'
        ? date('H:i', strtotime($shift['masuk'])) . ' – ' . date('H:i', strtotime($shift['limit_masuk']))
        : date('H:i', strtotime($shift['pulang'])) . ' – ' . date('H:i', strtotime($shift['limit_pulang']));
@endphp

<div class="absen-wrap">

    {{-- PAGE STRIP --}}
    <div class="page-strip {{ $jenis === 'masuk' ? 'ps-masuk' : 'ps-pulang' }}">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ now()->translatedFormat('l, d F Y, H:i') }}
        </div>
        <h2>
            @if ($jenis === 'masuk')
                <i class="fas fa-sign-in-alt"></i> Absen Masuk
            @else
                <i class="fas fa-sign-out-alt"></i> Absen Pulang
            @endif
        </h2>
        <p>{{ $jenis === 'masuk' ? 'Konfirmasi kehadiran pagi ini' : 'Konfirmasi pulang dengan selamat' }}</p>
    </div>

    {{-- ALERTS --}}
    @if (session('success'))
        <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert a-err">
            <i class="fas fa-times-circle"></i>
            <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- STEPS --}}
    <div class="steps">
        <div class="step done" id="step1"><div class="step-dot">✓</div><div class="step-lbl">Login</div></div>
        <div class="step active" id="step2"><div class="step-dot">2</div><div class="step-lbl">Lokasi</div></div>
        <div class="step" id="step3"><div class="step-dot">3</div><div class="step-lbl">Selfie</div></div>
        <div class="step" id="step4"><div class="step-dot">4</div><div class="step-lbl">Kirim</div></div>
    </div>

    {{-- STATUS CHIPS --}}
    <div class="status-bar">
        <div class="s-chip">
            <div class="ci {{ $status['sudahMasuk'] ? 'ci-g' : '' }}" id="icM">
                <i class="fas fa-{{ $status['sudahMasuk'] ? 'check' : 'clock' }}"></i>
            </div>
            <div>
                <div class="c-lbl">Masuk</div>
                <div class="c-val" id="valM">{{ $status['sudahMasuk'] ? 'Sudah' : 'Belum' }}</div>
            </div>
        </div>
        <div class="s-chip">
            <div class="ci {{ $status['sudahPulang'] ? 'ci-b' : '' }}" id="icP">
                <i class="fas fa-{{ $status['sudahPulang'] ? 'home' : 'clock' }}"></i>
            </div>
            <div>
                <div class="c-lbl">Pulang</div>
                <div class="c-val" id="valP">{{ $status['sudahPulang'] ? 'Sudah' : 'Belum' }}</div>
            </div>
        </div>
    </div>

    {{-- TIME WINDOW --}}
    <div class="time-window">
        <strong><i class="fas fa-clock"></i> Jam Absen {{ ucfirst($jenis) }}:</strong>
        {{ $waktuValid }}
    </div>

    {{-- CARD: PETA LOKASI --}}
    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#dbeafe;"><i class="fas fa-map-marker-alt"></i></div>
            <h3>Lokasi Saat Ini</h3>
            <span class="hbadge" id="gpsBadge">
                <span class="gps-loading"><span class="gps-spinner"></span> Mendeteksi GPS...</span>
            </span>
        </div>

        {{-- Map tanpa overflow:hidden agar Leaflet bisa ukur container dengan benar --}}
        <div class="map-wrapper">
            <div id="map"></div>
            <div class="map-bar">
                <i class="fas fa-location-dot" style="color:var(--muted);font-size:.8rem;"></i>
                <span id="mapTxt">Mendapatkan lokasi...</span>
                <span class="dbadge db-wait" id="distBadge" style="margin-left:auto;">— m</span>
            </div>
        </div>

        <div class="map-info">
            <span id="coordTxt"><i class="fas fa-globe"></i> —</span>
            <span id="accTxt" style="text-align:right;"><i class="fas fa-crosshairs"></i> Akurasi: —</span>
        </div>
    </div>

    {{-- CARD: SELFIE --}}
    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#fef9c3;"><i class="fas fa-camera"></i></div>
            <h3>{{ $jenis === 'masuk' ? 'Foto Selfie Masuk' : 'Foto Selfie Pulang' }}</h3>
            <span class="hbadge" id="selBadge">Belum</span>
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
                            <div style="font-size:2.2rem;margin-bottom:4px;">🤳</div>
                            <p style="font-size:.72rem;line-height:1.4;color:var(--muted);">Tekan tombol kamera<br>untuk mulai</p>
                        </div>
                    </div>
                    <div class="cring" id="cring">
                        <svg viewBox="0 0 176 176" style="width:100%;height:100%;transform:rotate(-90deg);">
                            <circle id="arc" cx="88" cy="88" r="85" fill="none"
                                stroke="var(--blue)" stroke-width="3"
                                stroke-dasharray="534" stroke-dashoffset="534" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="cnum" id="cnum"></div>
                    <div class="cbadge" id="cbadge"><i class="fas fa-check"></i></div>
                </div>

                <div class="cam-btns">
                    <button type="button" id="btnCam"    class="btn btn-b"><i class="fas fa-video"></i> Buka Kamera</button>
                    <button type="button" id="btnRetake" class="btn btn-o" style="display:none;"><i class="fas fa-rotate-right"></i> Ulangi</button>
                    <button type="button" id="btnSnap"   class="btn btn-b" style="display:none;"><i class="fas fa-camera"></i> Ambil Sekarang</button>
                </div>
                <p class="cam-hint">Foto otomatis 5 detik setelah kamera terbuka.<br>Pastikan wajah jelas &amp; pencahayaan cukup.</p>
            </div>
        </div>
    </div>

    {{-- FORM --}}
    <form id="absenForm" method="POST" action="{{ route('absen.store', $jenis) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="latitude"  id="hidLat">
        <input type="hidden" name="longitude" id="hidLng">
        <canvas id="cv" style="display:none;"></canvas>

        <div id="warnJarak" class="alert a-warn" style="display:none;">
            <i class="fas fa-triangle-exclamation"></i>
            Lokasi terlalu jauh dari sekolah (radius max {{ config('sekolah.radius_m') }}m)
        </div>

        <button type="submit" id="btnSub" disabled class="btn-sub">
            <i class="fas fa-circle-check"></i>
            <span id="subLbl">{{ $jenis === 'masuk' ? 'Absen Masuk Sekarang' : 'Konfirmasi Pulang' }}</span>
        </button>
    </form>

    @if ($status['sudahMasuk'] && $status['sudahPulang'])
        <div class="alert a-ok" style="margin-top:16px;">
            <i class="fas fa-star"></i>
            <div>Absen hari ini sudah lengkap! Terima kasih.</div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const SCH_LAT = {{ config('sekolah.latitude') }};
    const SCH_LNG = {{ config('sekolah.longitude') }};
    const RADIUS  = {{ config('sekolah.radius_m') }};
    const CSRF    = '{{ csrf_token() }}';

    let gpsOk = false, blob = null, stream = null, ticking = false;

    /* ═══════════════════════════════════════════════════════
       1. INISIALISASI MAP (dipisah dari GPS)
       ═══════════════════════════════════════════════════════ */
    const map = L.map('map', {
        zoomControl: false,
        scrollWheelZoom: false,
        attributionControl: true
    }).setView([SCH_LAT, SCH_LNG], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    L.control.zoom({ position: 'topright' }).addTo(map);

    // Marker sekolah
    const mkSchool = L.divIcon({
        html: `<div style="background:#0ea5e9;width:34px;height:34px;border-radius:50%;
                border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);
                display:flex;align-items:center;justify-content:center;font-size:16px;">🏫</div>`,
        iconSize:[34,34], iconAnchor:[17,17], className:''
    });
    L.marker([SCH_LAT, SCH_LNG], { icon: mkSchool })
        .addTo(map).bindPopup(`<b>Lokasi Sekolah</b><br>Radius ${RADIUS}m`);

    L.circle([SCH_LAT, SCH_LNG], {
        radius: RADIUS, color:'#0ea5e9', fillColor:'#0ea5e9',
        fillOpacity:.08, weight:2, dashArray:'5,5'
    }).addTo(map);

    // Paksa Leaflet hitung ulang ukuran setelah render
    map.invalidateSize();

    /* ═══════════════════════════════════════════════════════
       2. GPS — DIPISAH, dipanggil langsung (bukan di dalam setTimeout)
          Ini penyebab utama lokasi tidak terdeteksi sebelumnya:
          GPS dipanggil di dalam setTimeut bersarang + konflik L.control.locate
       ═══════════════════════════════════════════════════════ */
    const mkUser = L.divIcon({
        html: `<div style="background:#16a34a;width:30px;height:30px;border-radius:50%;
                border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);
                display:flex;align-items:center;justify-content:center;font-size:13px;">📍</div>`,
        iconSize:[30,30], iconAnchor:[15,15], className:''
    });
    let uMarker = null;

    function haversine(lat1, lng1, lat2, lng2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat/2)**2 +
                  Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLng/2)**2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    function onGpsSuccess(pos) {
        const lat  = pos.coords.latitude;
        const lng  = pos.coords.longitude;
        const acc  = Math.round(pos.coords.accuracy);
        const dist = Math.round(haversine(lat, lng, SCH_LAT, SCH_LNG));

        // Isi hidden input form
        document.getElementById('hidLat').value = lat;
        document.getElementById('hidLng').value = lng;

        // Tambah / update marker posisi user
        if (uMarker) map.removeLayer(uMarker);
        uMarker = L.marker([lat, lng], { icon: mkUser })
            .addTo(map)
            .bindPopup(`<b>Posisi Anda</b><br>${lat.toFixed(5)}, ${lng.toFixed(5)}`);

        // Fit bounds agar kedua titik terlihat
        map.fitBounds([[lat, lng], [SCH_LAT, SCH_LNG]], { padding: [32, 32] });
        // invalidateSize lagi setelah fitBounds untuk pastikan tile ter-render
        setTimeout(() => map.invalidateSize(), 300);

        // Update UI
        document.getElementById('coordTxt').textContent = `🌐 ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        document.getElementById('accTxt').textContent   = `🎯 Akurasi: ±${acc}m`;

        const gb   = document.getElementById('gpsBadge');
        const db   = document.getElementById('distBadge');
        const mt   = document.getElementById('mapTxt');

        if (dist <= RADIUS) {
            gpsOk = true;
            gb.innerHTML = '✓ Dalam Radius';
            gb.style.cssText = 'background:#dcfce7;color:#15803d;font-size:.68rem;font-weight:600;padding:3px 10px;border-radius:20px;';
            db.className = 'dbadge db-ok';
            db.textContent = dist + 'm';
            mt.textContent = 'Anda berada dalam radius sekolah ✓';
            mt.style.color = '#15803d';
            setStep(2, 'done');
            setStep(3, 'active');
        } else {
            gpsOk = false;
            gb.innerHTML = '✗ Luar Radius';
            gb.style.cssText = 'background:#fee2e2;color:#dc2626;font-size:.68rem;font-weight:600;padding:3px 10px;border-radius:20px;';
            db.className = 'dbadge db-far';
            db.textContent = dist + 'm';
            mt.textContent = `Terlalu jauh (${dist}m dari sekolah)`;
            mt.style.color = '#dc2626';
            document.getElementById('warnJarak').style.display = 'flex';
        }
        checkReady();
    }

    function onGpsError(err) {
        const gb = document.getElementById('gpsBadge');
        gb.innerHTML = '✗ GPS Gagal';
        gb.style.cssText = 'background:#fee2e2;color:#dc2626;font-size:.68rem;font-weight:600;padding:3px 10px;border-radius:20px;';
        const mt = document.getElementById('mapTxt');
        mt.textContent = 'Izin lokasi ditolak atau GPS tidak tersedia';
        mt.style.color = '#dc2626';
        console.warn('GPS error:', err.code, err.message);
    }

    // Panggil GPS langsung — TANPA bungkus setTimeout
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(onGpsSuccess, onGpsError, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0   // paksa fresh position, jangan pakai cache
        });
    } else {
        onGpsError({ code: 0, message: 'Geolocation not supported' });
    }

    /* ═══════════════════════════════════════════════════════
       3. KAMERA
       ═══════════════════════════════════════════════════════ */
    const vid    = document.getElementById('liveVid');
    const img    = document.getElementById('capImg');
    const ph     = document.getElementById('ph');
    const sc     = document.getElementById('sc');
    const cring  = document.getElementById('cring');
    const cnum   = document.getElementById('cnum');
    const cbadge = document.getElementById('cbadge');
    const arc    = document.getElementById('arc');
    const sb     = document.getElementById('selBadge');
    const cv     = document.getElementById('cv');
    const cx     = cv.getContext('2d');

    document.getElementById('btnCam').addEventListener('click', openCam);
    document.getElementById('btnRetake').addEventListener('click', openCam);
    document.getElementById('btnSnap').addEventListener('click', () => snap(true));

    async function openCam() {
        killStream();
        blob = null;
        img.style.display = 'none';
        cbadge.style.display = 'none';
        sc.className = 'sc';

        ph.innerHTML = '<div class="spinner"></div><p style="font-size:.72rem;line-height:1.4;">Membuka kamera...</p>';
        ph.style.display = 'block';
        sb.style.cssText = 'background:#fef9c3;color:#b45309;font-size:.68rem;font-weight:600;padding:3px 10px;border-radius:20px;';
        sb.textContent = 'Bersiap…';

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 640 } }
            });
            vid.srcObject = stream;
            vid.style.display = 'block';
            ph.style.display  = 'none';
            sc.classList.add('on');
            showBtn('btnCam',    false);
            showBtn('btnRetake', true);
            showBtn('btnSnap',   true);
            startTick(5);
        } catch (e) {
            ph.innerHTML = `<p style="font-size:.72rem;color:#dc2626;">❌ Kamera gagal dibuka<br><small>${e.message}</small></p>`;
            sb.textContent = 'Kamera gagal';
            sb.style.cssText = 'background:#fee2e2;color:#dc2626;font-size:.68rem;font-weight:600;padding:3px 10px;border-radius:20px;';
            showBtn('btnCam', true);
        }
    }

    function startTick(seconds) {
        ticking = true;
        let left = seconds;
        const C = 534;
        cring.style.display = 'block';
        cnum.style.display  = 'flex';
        cnum.textContent    = left;
        arc.style.strokeDashoffset = C;

        const iv = setInterval(() => {
            left--;
            cnum.textContent = left;
            arc.style.strokeDashoffset = C * (left / seconds);
            if (left <= 0) { clearInterval(iv); if (ticking) snap(false); }
        }, 1000);
    }

    function snap() {
        if (!stream) return;
        ticking = false;
        cv.width = cv.height = 480;
        cx.translate(480, 0); cx.scale(-1, 1);
        cx.drawImage(vid, 0, 0, 480, 480);
        cx.setTransform(1, 0, 0, 1, 0, 0);

        cv.toBlob(b => {
            blob = b;
            img.src = URL.createObjectURL(b);
            img.style.display  = 'block';
            vid.style.display  = 'none';
            cring.style.display = 'none';
            cnum.style.display  = 'none';
            cbadge.style.display = 'flex';
            sc.className = 'sc got';
            showBtn('btnSnap', false);
            sb.innerHTML = '✓ Siap';
            sb.style.cssText = 'background:#dcfce7;color:#15803d;font-size:.68rem;font-weight:600;padding:3px 10px;border-radius:20px;';
            setStep(3, 'done');
            setStep(4, 'active');
            killStream();
            checkReady();
        }, 'image/jpeg', 0.85);
    }

    function killStream() {
        if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    }

    /* ═══════════════════════════════════════════════════════
       4. SUBMIT
       ═══════════════════════════════════════════════════════ */
    document.getElementById('absenForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!blob) { alert('Foto selfie belum diambil!'); return; }

        const btn = document.getElementById('btnSub');
        const lbl = document.getElementById('subLbl');
        btn.disabled = true;
        lbl.textContent = 'Mengirim…';

        const fd = new FormData(this);
        fd.append('foto_selfie', blob, 'selfie.jpg');

        try {
            const resp = await fetch(this.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF },
                body: fd
            });
            window.location.href = resp.url || window.location.href;
        } catch (err) {
            btn.disabled = false;
            lbl.textContent = 'Absen Sekarang';
            alert('Gagal mengirim data, coba lagi.');
        }
    });

    /* ═══════════════════════════════════════════════════════
       5. STATUS HARI INI
       ═══════════════════════════════════════════════════════ */
    fetch('{{ route('absen.status-hari-ini') }}')
        .then(r => r.json())
        .then(d => {
            if (d.sudahMasuk) {
                document.getElementById('valM').textContent = 'Sudah Absen';
                document.getElementById('valM').style.color = '#15803d';
                const im = document.getElementById('icM');
                im.innerHTML = '<i class="fas fa-check"></i>'; im.className = 'ci ci-g';
            }
            if (d.sudahPulang) {
                document.getElementById('valP').textContent = 'Sudah Pulang';
                document.getElementById('valP').style.color = '#1d4ed8';
                const ip = document.getElementById('icP');
                ip.innerHTML = '<i class="fas fa-home"></i>'; ip.className = 'ci ci-b';
            }
        }).catch(() => {});

    /* ═══════════════════════════════════════════════════════
       HELPERS
       ═══════════════════════════════════════════════════════ */
    function checkReady() {
        const btn = document.getElementById('btnSub');
        const lbl = document.getElementById('subLbl');
        if (gpsOk && blob) {
            btn.disabled = false;
            lbl.textContent = '{{ $jenis === "masuk" ? "Absen Masuk Sekarang" : "Konfirmasi Pulang" }}';
        } else if (!gpsOk) {
            lbl.textContent = 'Menunggu GPS…';
        } else {
            lbl.textContent = 'Ambil selfie dahulu';
        }
    }

    function setStep(n, state) {
        const el = document.getElementById('step' + n);
        if (!el) return;
        el.className = 'step ' + state;
        if (state === 'done') el.querySelector('.step-dot').textContent = '✓';
    }

    function showBtn(id, visible) {
        document.getElementById(id).style.display = visible ? 'inline-flex' : 'none';
    }

    // Auto buka kamera setelah 600ms (beri jeda GPS request duluan)
    setTimeout(openCam, 600);
});
</script>
@endpush