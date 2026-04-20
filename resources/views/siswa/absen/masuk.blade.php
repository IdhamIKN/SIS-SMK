@extends('layouts.app')

@section('title', 'Absen Masuk')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

    :root {
        --green-primary: #16a34a;
        --blue-accent:   #0ea5e9;
        --surface:       #f1f5f9;
        --card:          #ffffff;
        --border:        #e2e8f0;
        --text-main:     #0f172a;
        --text-muted:    #64748b;
    }

    /* ── WRAPPER ── */
    .absen-wrap {
        max-width: 520px;
        margin: 0 auto;
        padding: 14px 14px 100px; /* extra bawah untuk bottom-nav layout */
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* ── PAGE STRIP (judul dalam konten, bukan navbar) ── */
    .page-strip {
        background: linear-gradient(135deg, #16a34a 0%, #0ea5e9 100%);
        border-radius: 16px;
        padding: 16px 18px;
        margin-bottom: 14px;
        position: relative;
        overflow: hidden;
    }
    .page-strip::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
    }
    .live-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.2);
        border: 1px solid rgba(255,255,255,.3);
        border-radius: 20px; padding: 3px 10px;
        font-size: .7rem; color: #fff; font-weight: 600; margin-bottom: 8px;
    }
    .live-dot {
        width: 6px; height: 6px; background: #4ade80;
        border-radius: 50%; animation: blink 1.5s ease infinite;
    }
    @keyframes blink {
        0%,100% { opacity:1; transform:scale(1); }
        50%      { opacity:.5; transform:scale(1.3); }
    }
    .page-strip h2 {
        font-size: 1.15rem; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 8px; position: relative;
    }
    .page-strip p { font-size: .77rem; color: rgba(255,255,255,.8); margin-top: 3px; position: relative; }

    /* ── STEPS ── */
    .steps { display: flex; margin-bottom: 14px; }
    .step  { flex: 1; text-align: center; position: relative; }
    .step::after {
        content: ''; position: absolute; top: 13px; left: 50%;
        width: 100%; height: 2px; background: var(--border);
    }
    .step:last-child::after { display: none; }
    .step.done::after { background: var(--green-primary); }
    .step-dot {
        width: 26px; height: 26px; border-radius: 50%;
        background: var(--border); color: var(--text-muted);
        font-size: .68rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 4px; position: relative; z-index: 1; transition: all .3s;
    }
    .step.active .step-dot { background: var(--blue-accent); color: #fff; }
    .step.done   .step-dot { background: var(--green-primary); color: #fff; }
    .step-lbl { font-size: .6rem; color: var(--text-muted); font-weight: 500; }
    .step.active .step-lbl, .step.done .step-lbl { color: var(--text-main); }

    /* ── STATUS CHIPS ── */
    .status-bar { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
    .s-chip {
        background: var(--card); border: 1px solid var(--border);
        border-radius: 12px; padding: 10px 12px;
        display: flex; align-items: center; gap: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
    }
    .ci {
        width: 34px; height: 34px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0; background: #f1f5f9;
    }
    .ci-g { background: #dcfce7; }
    .ci-b { background: #dbeafe; }
    .c-lbl { font-size: .65rem; color: var(--text-muted); }
    .c-val { font-size: .8rem; font-weight: 600; margin-top: 1px; }

    /* ── CARD ── */
    .card {
        background: var(--card); border-radius: 14px;
        border: 1px solid var(--border); margin-bottom: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 14px rgba(0,0,0,.03);
    }
    .c-head {
        padding: 12px 16px; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: 10px;
    }
    .c-icon {
        width: 30px; height: 30px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; font-size: .9rem;
    }
    .c-head h3 { font-size: .87rem; font-weight: 600; color: var(--text-main); }
    .c-body { padding: 16px; }
    .hbadge {
        margin-left: auto; font-size: .68rem; font-weight: 600;
        padding: 3px 10px; border-radius: 20px;
        background: #f1f5f9; color: #64748b;
    }

    /* ── ALERTS ── */
    .alert {
        border-radius: 10px; padding: 11px 14px; font-size: .82rem;
        display: flex; align-items: flex-start; gap: 8px; margin-bottom: 12px;
    }
    .alert ul { list-style: none; }
    .a-ok   { background: #f0fdf4; border: 1px solid #86efac; color: #15803d; }
    .a-err  { background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626; }
    .a-warn { background: #fffbeb; border: 1px solid #fcd34d; color: #b45309; }

    /* ── MAP ── */
    #mapBox { height: 200px; position: relative; }
    #map    { height: 100%; width: 100%; }
    .map-bar {
        position: absolute; bottom: 8px; left: 8px; right: 8px;
        background: rgba(255,255,255,.93); backdrop-filter: blur(6px);
        border-radius: 10px; padding: 7px 12px; z-index: 999;
        font-size: .74rem; display: flex; align-items: center; gap: 8px;
        border: 1px solid rgba(255,255,255,.8); box-shadow: 0 2px 8px rgba(0,0,0,.1);
    }
    .dbadge {
        margin-left: auto; font-weight: 700; font-size: .7rem;
        padding: 3px 10px; border-radius: 20px;
    }
    .db-ok   { background: #dcfce7; color: #15803d; }
    .db-far  { background: #fee2e2; color: #dc2626; }
    .db-wait { background: #f1f5f9; color: #64748b; }
    .coord-row { padding: 8px 14px; font-size: .72rem; color: var(--text-muted); display: flex; }

    /* ── SELFIE ── */
    .cam-section { display: flex; flex-direction: column; align-items: center; gap: 12px; }
    .sw { position: relative; width: 170px; height: 170px; }
    .sc {
        width: 170px; height: 170px; border-radius: 50%;
        overflow: hidden; background: #f1f5f9;
        border: 3px solid var(--border);
        display: flex; align-items: center; justify-content: center;
        transition: border-color .3s, box-shadow .3s;
    }
    .sc.on  { border-color: var(--blue-accent); }
    .sc.got { border-color: var(--green-primary); box-shadow: 0 0 0 4px #dcfce7; }
    #liveVid {
        width: 100%; height: 100%; object-fit: cover;
        display: none; transform: scaleX(-1);
    }
    #capImg { width: 100%; height: 100%; object-fit: cover; display: none; }
    .ph { text-align: center; color: var(--text-muted); }
    .ph .pi { font-size: 2.2rem; margin-bottom: 4px; }
    .ph p   { font-size: .72rem; line-height: 1.4; }

    /* ring & badges */
    .cring { position: absolute; inset: -3px; border-radius: 50%; display: none; pointer-events: none; }
    .cring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
    .cring circle {
        fill: none; stroke: var(--blue-accent); stroke-width: 3;
        stroke-dasharray: 534; stroke-dashoffset: 534;
        stroke-linecap: round; transition: stroke-dashoffset 1s linear;
    }
    .cnum, .cbadge {
        position: absolute; bottom: 0; right: 0;
        width: 34px; height: 34px; border-radius: 50%;
        display: none; align-items: center; justify-content: center;
        font-weight: 700; font-size: .95rem;
        border: 2px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,.2);
    }
    .cnum   { background: var(--blue-accent); color: #fff; }
    .cbadge { background: var(--green-primary); color: #fff; font-size: 1rem; }

    /* ── BUTTONS ── */
    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 18px; border-radius: 9px; font-size: .82rem;
        font-weight: 600; border: none; cursor: pointer;
        transition: all .2s; font-family: inherit;
    }
    .btn:active { transform: scale(.97); }
    .btn-b { background: var(--blue-accent); color: #fff; }
    .btn-b:hover { background: #0284c7; }
    .btn-o { background: transparent; color: var(--text-muted); border: 1px solid var(--border); }
    .btn-o:hover { background: #f8fafc; }
    .cam-btns { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }
    .cam-hint { font-size: .71rem; color: var(--text-muted); text-align: center; }

    /* ── SUBMIT ── */
    .btn-sub {
        width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 14px; border-radius: 12px; border: none; cursor: pointer;
        background: var(--green-primary); color: #fff;
        font-size: .95rem; font-weight: 700; font-family: inherit;
        box-shadow: 0 4px 14px rgba(22,163,74,.35); transition: all .2s;
    }
    .btn-sub:hover:not(:disabled) { background: #15803d; }
    .btn-sub:disabled { background: #94a3b8; box-shadow: none; cursor: not-allowed; }
    .btn-sub:active:not(:disabled) { transform: scale(.98); }
</style>
@endpush

@section('content')
<div class="absen-wrap">

    {{-- PAGE STRIP --}}
    <div class="page-strip">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
        <h2>
            <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0
                       3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946
                       3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138
                       3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806
                       3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438
                       3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
            Absen Masuk
        </h2>
        <p>Konfirmasi kehadiran Anda hari ini</p>
    </div>

    {{-- ALERTS --}}
    @if (session('success'))
        <div class="alert a-ok"><span>✅</span><div>{{ session('success') }}</div></div>
    @endif
    @if ($errors->any())
        <div class="alert a-err">
            <span>❌</span>
            <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- STEPS --}}
    <div class="steps">
        <div class="step done" id="step1">
            <div class="step-dot">✓</div><div class="step-lbl">Login</div>
        </div>
        <div class="step active" id="step2">
            <div class="step-dot">2</div><div class="step-lbl">Lokasi</div>
        </div>
        <div class="step" id="step3">
            <div class="step-dot">3</div><div class="step-lbl">Selfie</div>
        </div>
        <div class="step" id="step4">
            <div class="step-dot">4</div><div class="step-lbl">Kirim</div>
        </div>
    </div>

    {{-- STATUS CHIPS --}}
    <div class="status-bar">
        <div class="s-chip">
            <div class="ci" id="icM">⏳</div>
            <div><div class="c-lbl">Status Masuk</div><div class="c-val" id="valM">Memuat…</div></div>
        </div>
        <div class="s-chip">
            <div class="ci" id="icP">⏳</div>
            <div><div class="c-lbl">Status Pulang</div><div class="c-val" id="valP">Memuat…</div></div>
        </div>
    </div>

    {{-- CARD PETA --}}
    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#dbeafe;">📍</div>
            <h3>Lokasi Anda</h3>
            <span class="hbadge" id="gpsBadge">Mendeteksi…</span>
        </div>
        <div id="mapBox"><div id="map"></div>
            <div class="map-bar">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span id="mapTxt" style="color:var(--text-muted);">Mendapatkan GPS…</span>
                <span class="dbadge db-wait" id="distBadge">— m</span>
            </div>
        </div>
        <div class="coord-row">
            <span id="coordTxt">🌐 —</span>
            <span id="accTxt" style="margin-left:auto;">🎯 Akurasi: —</span>
        </div>
    </div>

    {{-- CARD SELFIE --}}
    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#fef9c3;">🤳</div>
            <h3>Foto Selfie</h3>
            <span class="hbadge" id="selBadge">Belum</span>
        </div>
        <div class="c-body">
            <div class="cam-section">
                <div class="sw">
                    <div class="sc" id="sc">
                        <video id="liveVid" autoplay playsinline muted></video>
                        <img id="capImg" alt="Selfie">
                        <div class="ph" id="ph">
                            <div class="pi">🤳</div>
                            <p>Tekan tombol kamera<br>untuk mulai</p>
                        </div>
                    </div>
                    <div class="cring" id="cring">
                        <svg viewBox="0 0 176 176"><circle id="arc" cx="88" cy="88" r="85"/></svg>
                    </div>
                    <div class="cnum" id="cnum"></div>
                    <div class="cbadge" id="cbadge">✓</div>
                </div>

                <div class="cam-btns">
                    <button type="button" id="btnCam" class="btn btn-b">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14
                                   M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Buka Kamera
                    </button>
                    <button type="button" id="btnRetake" class="btn btn-o" style="display:none;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9
                                   m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Ulangi
                    </button>
                    <button type="button" id="btnSnap" class="btn btn-b" style="display:none;">
                        📸 Ambil Sekarang
                    </button>
                </div>
                <p class="cam-hint">Foto otomatis diambil 5 detik setelah kamera terbuka.<br>
                    Pastikan wajah terlihat jelas &amp; pencahayaan cukup.</p>
            </div>
        </div>
    </div>

    {{-- FORM SUBMIT --}}
    <form id="absenForm" method="POST" action="{{ route('absen.masuk.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="latitude"  id="hidLat">
        <input type="hidden" name="longitude" id="hidLng">
        <canvas id="cv" style="display:none;"></canvas>

        <div id="warnJarak" class="alert a-warn" style="display:none;">
            ⚠️ Lokasi terlalu jauh dari sekolah. Radius maksimal <strong>{{ config('sekolah.radius_m') }}m</strong>.
        </div>

        <button type="submit" id="btnSub" disabled class="btn-sub">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span id="subLbl">Lengkapi lokasi &amp; selfie dahulu</span>
        </button>
    </form>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const SCH_LAT  = {{ config('sekolah.latitude') }};
    const SCH_LNG  = {{ config('sekolah.longitude') }};
    const RADIUS   = {{ config('sekolah.radius_m') }};

    let gpsOk = false, blob = null, stream = null, ticking = false;

    /* ─── MAP ─────────────────────────────── */
    const map = L.map('map', { zoomControl: false }).setView([SCH_LAT, SCH_LNG], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 19
    }).addTo(map);
    L.control.zoom({ position: 'topright' }).addTo(map);

    const mkSchool = L.divIcon({
        html: `<div style="background:#0ea5e9;width:34px;height:34px;border-radius:50%;
                border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);
                display:flex;align-items:center;justify-content:center;font-size:15px;">🏫</div>`,
        iconSize:[34,34], iconAnchor:[17,17], className:''
    });
    L.marker([SCH_LAT, SCH_LNG], { icon: mkSchool }).addTo(map)
        .bindPopup(`<b>Lokasi Sekolah</b><br>Radius ${RADIUS}m`);
    L.circle([SCH_LAT, SCH_LNG], {
        radius: RADIUS, color:'#0ea5e9', fillColor:'#0ea5e9',
        fillOpacity:.08, weight:2, dashArray:'5,5'
    }).addTo(map);

    const mkUser = L.divIcon({
        html: `<div style="background:#16a34a;width:30px;height:30px;border-radius:50%;
                border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);
                display:flex;align-items:center;justify-content:center;font-size:13px;">📍</div>`,
        iconSize:[30,30], iconAnchor:[15,15], className:''
    });
    let uMarker = null;

    /* ─── GPS ─────────────────────────────── */
    function haversine(a,b,c,d){
        const R=6371000,dA=(c-a)*Math.PI/180,dB=(d-b)*Math.PI/180;
        const x=Math.sin(dA/2)**2+Math.cos(a*Math.PI/180)*Math.cos(c*Math.PI/180)*Math.sin(dB/2)**2;
        return R*2*Math.atan2(Math.sqrt(x),Math.sqrt(1-x));
    }

    navigator.geolocation
        ? navigator.geolocation.getCurrentPosition(gpsOK, gpsFail,
            { enableHighAccuracy:true, timeout:12000, maximumAge:30000 })
        : gpsFail();

    function gpsOK(p) {
        const lat=p.coords.latitude, lng=p.coords.longitude,
              acc=Math.round(p.coords.accuracy),
              d=Math.round(haversine(lat,lng,SCH_LAT,SCH_LNG));

        document.getElementById('hidLat').value = lat;
        document.getElementById('hidLng').value = lng;

        if (uMarker) map.removeLayer(uMarker);
        uMarker = L.marker([lat,lng],{icon:mkUser}).addTo(map)
            .bindPopup(`<b>Posisi Anda</b><br>${lat.toFixed(5)}, ${lng.toFixed(5)}`);
        map.fitBounds([[lat,lng],[SCH_LAT,SCH_LNG]],{padding:[30,30]});

        document.getElementById('coordTxt').textContent = `🌐 ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        document.getElementById('accTxt').textContent   = `🎯 Akurasi: ±${acc}m`;

        const gb=document.getElementById('gpsBadge'),
              db=document.getElementById('distBadge'),
              mt=document.getElementById('mapTxt');

        if (d <= RADIUS) {
            gpsOk = true;
            setStyle(gb,'#dcfce7','#15803d'); gb.textContent = '✓ Dalam Radius';
            db.className='dbadge db-ok'; db.textContent = d+'m';
            mt.textContent='Anda berada dalam radius sekolah'; mt.style.color='#15803d';
            step(2,'done'); step(3,'active');
        } else {
            setStyle(gb,'#fee2e2','#dc2626'); gb.textContent = '✗ Luar Radius';
            db.className='dbadge db-far'; db.textContent = d+'m';
            mt.textContent=`Terlalu jauh (${d}m dari sekolah)`; mt.style.color='#dc2626';
            document.getElementById('warnJarak').style.display='flex';
        }
        checkReady();
    }

    function gpsFail() {
        const gb=document.getElementById('gpsBadge');
        setStyle(gb,'#fee2e2','#dc2626'); gb.textContent='✗ GPS Gagal';
        document.getElementById('mapTxt').textContent='Izin lokasi ditolak';
    }

    /* ─── CAMERA ──────────────────────────── */
    const vid=document.getElementById('liveVid'),
          img=document.getElementById('capImg'),
          ph=document.getElementById('ph'),
          sc=document.getElementById('sc'),
          cring=document.getElementById('cring'),
          cnum=document.getElementById('cnum'),
          cbadge=document.getElementById('cbadge'),
          arc=document.getElementById('arc'),
          sb=document.getElementById('selBadge'),
          cv=document.getElementById('cv'),
          cx=cv.getContext('2d');

    document.getElementById('btnCam').addEventListener('click', openCam);
    document.getElementById('btnRetake').addEventListener('click', openCam);
    document.getElementById('btnSnap').addEventListener('click', ()=>snap(true));

    async function openCam() {
        kill(); blob=null;
        img.style.display='none'; cbadge.style.display='none';
        ph.style.display='block'; sc.className='sc';
        setStyle(sb,'#fef9c3','#b45309'); sb.textContent='Bersiap…';

        try {
            stream = await navigator.mediaDevices.getUserMedia(
                { video:{facingMode:'user',width:{ideal:640},height:{ideal:640}} });
            vid.srcObject=stream; vid.style.display='block'; ph.style.display='none';
            sc.classList.add('on');
            show('btnCam',false); show('btnRetake',true); show('btnSnap',true);
            tick(5);
        } catch(e) { alert('Kamera tidak bisa dibuka: '+e.message); }
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
        cx.translate(480,0); cx.scale(-1,1);
        cx.drawImage(vid,0,0,480,480);
        cx.setTransform(1,0,0,1,0,0);
        cv.toBlob(b=>{
            blob=b;
            img.src=URL.createObjectURL(b); img.style.display='block'; vid.style.display='none';
            cring.style.display='none'; cnum.style.display='none'; cbadge.style.display='flex';
            sc.classList.remove('on'); sc.classList.add('got');
            show('btnSnap',false);
            setStyle(sb,'#dcfce7','#15803d'); sb.textContent='✓ Siap';
            step(3,'done'); step(4,'active');
            kill(); checkReady();
        },'image/jpeg',.85);
    }

    function kill() { if(stream){stream.getTracks().forEach(t=>t.stop()); stream=null;} }

    /* ─── SUBMIT ──────────────────────────── */
    document.getElementById('absenForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        if(!blob){ alert('Ambil foto selfie dulu!'); return; }
        const btn=document.getElementById('btnSub'), lbl=document.getElementById('subLbl');
        btn.disabled=true; lbl.textContent='Mengirim…';
        const fd=new FormData(this);
        fd.append('foto_selfie',blob,'selfie.jpg');
        try {
            const r=await fetch(this.action,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'},body:fd});
            window.location.href=r.url||window.location.href;
        } catch { btn.disabled=false; lbl.textContent='Absen Sekarang'; alert('Gagal, coba lagi.'); }
    });

    /* ─── STATUS ──────────────────────────── */
    fetch('{{ route('absen.status-hari-ini') }}')
        .then(r=>r.json())
        .then(d=>{
            if(d.sudahMasuk){
                document.getElementById('valM').textContent='Sudah Absen';
                document.getElementById('valM').style.color='#15803d';
                document.getElementById('icM').textContent='✅';
                document.getElementById('icM').className='ci ci-g';
            } else { document.getElementById('valM').textContent='Belum Absen'; }
            if(d.sudahPulang){
                document.getElementById('valP').textContent='Sudah Pulang';
                document.getElementById('valP').style.color='#1d4ed8';
                document.getElementById('icP').textContent='🏠';
                document.getElementById('icP').className='ci ci-b';
            } else { document.getElementById('valP').textContent='Belum Pulang'; }
        }).catch(()=>{});

    /* ─── UTIL ────────────────────────────── */
    function checkReady(){
        const b=document.getElementById('btnSub'), l=document.getElementById('subLbl');
        if(gpsOk&&blob){ b.disabled=false; l.textContent='Absen Sekarang'; step(4,'active'); }
        else if(!gpsOk){ l.textContent='Menunggu GPS…'; }
        else            { l.textContent='Ambil selfie dahulu'; }
    }
    function step(n,s){
        const e=document.getElementById('step'+n); if(!e) return;
        e.className='step '+s;
        if(s==='done') e.querySelector('.step-dot').textContent='✓';
    }
    function setStyle(el,bg,col){
        el.style.cssText=`background:${bg};color:${col};margin-left:auto;font-size:.68rem;font-weight:600;padding:3px 10px;border-radius:20px;`;
    }
    function show(id,v){ document.getElementById(id).style.display=v?'inline-flex':'none'; }
});
</script>
@endpush