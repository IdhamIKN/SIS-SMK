@extends('layouts.app')

@section('title', 'Edit - ' . $eventGuru->nama_event)

@push('styles')
    @include('components.event-styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .form-label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .form-label .req {
            color: #ef4444;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 10px;
            font-size: .875rem;
            font-family: inherit;
            color: var(--text-main);
            background: #fff;
            transition: border-color .2s;
            outline: none;
            box-sizing: border-box;
        }

        .form-input:focus {
            border-color: #4338ca;
            box-shadow: 0 0 0 3px rgba(67, 56, 202, .12);
        }

        .form-input.is-error {
            border-color: #ef4444;
        }

        textarea.form-input {
            resize: vertical;
            min-height: 80px;
        }

        .form-hint {
            font-size: .72rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .form-error {
            font-size: .75rem;
            color: #dc2626;
            margin-top: 4px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
        }

        .c-divider {
            height: 1px;
            background: var(--border, #e2e8f0);
            margin: 4px 0 16px;
        }

        .map-wrap {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
            margin-bottom: 14px;
        }

        #eventMap {
            width: 100%;
            height: 260px;
            z-index: 1;
        }

        .map-status {
            padding: 8px 12px;
            background: #f8fafc;
            border-top: 1px solid var(--border);
            font-size: .75rem;
            color: var(--text-muted);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .absen-toggle-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }

        .absen-toggle-card {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
        }

        .absen-toggle-card input[type="checkbox"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
            margin: 0;
        }

        .absen-toggle-label {
            position: relative;
            z-index: 1;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 16px 10px;
            border: 2px solid var(--border);
            border-radius: 12px;
            background: #f8fafc;
            text-align: center;
            transition: all .18s;
        }

        .absen-toggle-icon {
            font-size: 1.4rem;
        }

        .absen-toggle-text {
            font-size: .8rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .absen-toggle-status {
            font-size: .68rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
            background: #e2e8f0;
            color: #64748b;
            transition: all .18s;
        }

        .absen-toggle-card input:checked ~ .absen-toggle-label {
            border-color: #16a34a;
            background: #f0fdf4;
        }

        .absen-toggle-card input:checked ~ .absen-toggle-label .absen-toggle-status {
            background: #16a34a;
            color: #fff;
        }

        .absen-toggle-card.pulang input:checked ~ .absen-toggle-label {
            border-color: #0ea5e9;
            background: #e0f2fe;
        }

        .absen-toggle-card.pulang input:checked ~ .absen-toggle-label .absen-toggle-status {
            background: #0ea5e9;
            color: #fff;
        }

        /* Action Bar */
        .action-bar {
            position: fixed;
            bottom: var(--footer-h, 60px);
            left: 0;
            right: 0;
            z-index: 200;
            padding: 10px 16px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: .875rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            line-height: 1;
        }

        .ab-btn:active {
            transform: scale(.97);
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .ab-btn-primary {
            background: #4338ca;
            color: #fff;
            box-shadow: 0 3px 12px rgba(67, 56, 202, .3);
        }

        .ab-btn-delete {
            background: #ef4444;
            color: #fff;
            box-shadow: 0 3px 12px rgba(239, 68, 68, .25);
            flex: 0 0 auto;
            padding: 12px 18px;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h, 60px) + 88px);">

        <div class="page-strip" style="background:linear-gradient(135deg,#1e40af 0%,#4338ca 100%);">
            <div class="live-badge"><span class="live-dot"></span> Edit Event Guru</div>
            <h2><i class="fas fa-pen-to-square"></i> {{ Str::limit($eventGuru->nama_event, 30) }}</h2>
            <p>Perbarui informasi acara guru</p>
        </div>

        <form id="editForm" method="POST" action="{{ route('event-guru.update', $eventGuru) }}">
            @csrf
            @method('PUT')

            {{-- Informasi Event --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#e0e7ff;"><i class="fas fa-info-circle" style="color:#4338ca;"></i></div>
                    <h3>Informasi Event</h3>
                </div>
                <div class="c-body" style="padding:16px;">
                    <div style="margin-bottom:14px;">
                        <label class="form-label">Nama Event <span class="req">*</span></label>
                        <input type="text" name="nama_event"
                            class="form-input @error('nama_event') is-error @enderror"
                            value="{{ old('nama_event', $eventGuru->nama_event) }}" required>
                        @error('nama_event')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="form-label">Kategori Event</label>
                        <input type="text" name="event_category_name"
                            class="form-input @error('event_category_name') is-error @enderror"
                            value="{{ old('event_category_name') }}"
                            list="categoryList"
                            placeholder="Pilih atau ketik kategori baru">
                        <datalist id="categoryList">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->nama_kategori }}">
                            @endforeach
                        </datalist>
                        <div class="form-hint">Ketik kategori baru atau pilih yang sudah ada.</div>
                        @error('event_category_name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" rows="3"
                            class="form-input @error('deskripsi') is-error @enderror">{{ old('deskripsi', $eventGuru->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Lokasi / Alamat</label>
                        <input type="text" name="lokasi" id="lokasiInput"
                            class="form-input @error('lokasi') is-error @enderror"
                            value="{{ old('lokasi', $eventGuru->lokasi) }}">
                        @error('lokasi')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Pin Lokasi --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#dcfce7;color:#15803d;"><i class="fas fa-map-marker-alt"></i></div>
                    <h3>Pin Lokasi di Peta</h3>
                    <span class="hbadge" style="background:#f1f5f9;color:#64748b;">Opsional</span>
                </div>
                <div class="c-body" style="padding:16px;">
                    <p style="color:var(--text-muted);font-size:.82rem;line-height:1.55;margin:0 0 12px;">
                        Jika diisi, guru harus berada dalam radius yang ditentukan. Kosongkan jika bebas dari mana saja.
                    </p>
                    <div class="map-wrap">
                        <div id="eventMap"></div>
                        <div class="map-status">
                            <span><i class="fas fa-mouse-pointer"></i> Klik peta atau gunakan pencarian</span>
                            <span id="latLngDisplay">Lat: -, Lng: -</span>
                        </div>
                    </div>
                    <div class="grid-3" style="margin-bottom:10px;">
                        <div>
                            <label class="form-label">Latitude</label>
                            <input type="text" name="lat" id="latInput"
                                class="form-input @error('lat') is-error @enderror"
                                value="{{ old('lat', $eventGuru->lat) }}" placeholder="-7.6291" readonly>
                            @error('lat')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Longitude</label>
                            <input type="text" name="lng" id="lngInput"
                                class="form-input @error('lng') is-error @enderror"
                                value="{{ old('lng', $eventGuru->lng) }}" placeholder="111.5230" readonly>
                            @error('lng')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Radius (meter)</label>
                            <input type="number" name="radius_meter" id="radiusInput"
                                class="form-input @error('radius_meter') is-error @enderror"
                                value="{{ old('radius_meter', $eventGuru->radius_meter ?? 100) }}" min="10" max="5000">
                            @error('radius_meter')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <button type="button" class="action-btn btn-view" onclick="clearLocation()">
                        <i class="fas fa-trash-alt"></i> Hapus Pin
                    </button>
                </div>
            </div>

            {{-- Waktu --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#fef3c7;"><i class="fas fa-clock"></i></div>
                    <h3>Waktu Pelaksanaan</h3>
                </div>
                <div class="c-body" style="padding:16px;">
                    <div class="grid-2">
                        <div>
                            <label class="form-label">Tanggal Mulai <span class="req">*</span></label>
                            <input type="datetime-local" name="tanggal_mulai"
                                class="form-input @error('tanggal_mulai') is-error @enderror"
                                value="{{ old('tanggal_mulai', $eventGuru->tanggal_mulai->format('Y-m-d\TH:i')) }}" required>
                            @error('tanggal_mulai')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Tanggal Selesai <span class="req">*</span></label>
                            <input type="datetime-local" name="tanggal_selesai"
                                class="form-input @error('tanggal_selesai') is-error @enderror"
                                value="{{ old('tanggal_selesai', $eventGuru->tanggal_selesai->format('Y-m-d\TH:i')) }}" required>
                            @error('tanggal_selesai')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pengaturan Absen --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#e0e7ff;"><i class="fas fa-qrcode" style="color:#4338ca;"></i></div>
                    <h3>Pengaturan Absen</h3>
                </div>
                <div class="c-body" style="padding:16px;">
                    <div class="absen-toggle-grid">
                        <div class="absen-toggle-card">
                            <input type="checkbox" id="chk_masuk" name="ada_absen_masuk" value="1"
                                {{ old('ada_absen_masuk', $eventGuru->ada_absen_masuk) ? 'checked' : '' }}>
                            <label for="chk_masuk" class="absen-toggle-label">
                                <div class="absen-toggle-icon"><i class="fas fa-sign-in-alt" style="color:#16a34a;"></i></div>
                                <div class="absen-toggle-text">Absen Masuk</div>
                                <div class="absen-toggle-status">Aktif</div>
                            </label>
                        </div>
                        <div class="absen-toggle-card pulang">
                            <input type="checkbox" id="chk_pulang" name="ada_absen_pulang" value="1"
                                {{ old('ada_absen_pulang', $eventGuru->ada_absen_pulang) ? 'checked' : '' }}>
                            <label for="chk_pulang" class="absen-toggle-label">
                                <div class="absen-toggle-icon"><i class="fas fa-sign-out-alt" style="color:#0ea5e9;"></i></div>
                                <div class="absen-toggle-text">Absen Pulang</div>
                                <div class="absen-toggle-status">Aktif</div>
                            </label>
                        </div>
                    </div>
                    <div class="c-divider"></div>
                    <div>
                        <label class="form-label">Interval Rotasi Barcode (detik)</label>
                        <input type="number" name="barcode_rotate_detik"
                            class="form-input @error('barcode_rotate_detik') is-error @enderror"
                            value="{{ old('barcode_rotate_detik', $eventGuru->barcode_rotate_detik) }}" min="0" max="3600">
                        <div class="form-hint">0 = barcode statis, &gt; 0 = berubah otomatis setiap N detik</div>
                        @error('barcode_rotate_detik')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

        </form>

        {{-- Form delete terpisah --}}
        <form id="deleteForm" method="POST" action="{{ route('event-guru.destroy', $eventGuru) }}">
            @csrf
            @method('DELETE')
        </form>

    </div>

    {{-- Action Bar --}}
    <div class="action-bar">
        <a href="{{ route('event-guru.show', $eventGuru) }}" class="ab-btn ab-btn-back">
            <i class="fas fa-times"></i> Batal
        </a>
        <button type="button" class="ab-btn ab-btn-delete" onclick="confirmDelete()">
            <i class="fas fa-trash-alt"></i>
        </button>
        <button type="submit" form="editForm" class="ab-btn ab-btn-primary">
            <i class="fas fa-save"></i> Simpan
        </button>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let map, marker, circle;
        const savedLat = {{ $eventGuru->lat ? $eventGuru->lat : 'null' }};
        const savedLng = {{ $eventGuru->lng ? $eventGuru->lng : 'null' }};

        function setInputValue(id, val) {
            const el = document.getElementById(id);
            el.removeAttribute('readonly');
            el.value = val;
            el.setAttribute('readonly', true);
        }

        function initMap() {
            const lat = savedLat || -7.6291;
            const lng = savedLng || 111.5230;
            map = L.map('eventMap').setView([lat, lng], savedLat ? 15 : 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            L.Control.geocoder({
                    defaultMarkGeocode: false,
                    placeholder: 'Cari lokasi...',
                    errorMessage: 'Tidak ditemukan',
                    suggestTimeout: 250,
                    queryMinLength: 3
                })
                .on('markgeocode', function(e) {
                    map.setView(e.geocode.center, 16);
                    setMarker(e.geocode.center.lat, e.geocode.center.lng);
                })
                .addTo(map);
            map.on('click', function(e) {
                setMarker(e.latlng.lat, e.latlng.lng);
            });
            if (savedLat && savedLng) setMarker(savedLat, savedLng);
        }

        function setMarker(lat, lng) {
            const radius = parseInt(document.getElementById('radiusInput').value) || 100;
            if (marker) map.removeLayer(marker);
            if (circle) map.removeLayer(circle);
            marker = L.marker([lat, lng]).addTo(map);
            circle = L.circle([lat, lng], {
                radius,
                color: '#4338ca',
                fillColor: '#4338ca',
                fillOpacity: 0.15,
                weight: 2
            }).addTo(map);
            setInputValue('latInput', lat.toFixed(6));
            setInputValue('lngInput', lng.toFixed(6));
            updateDisplay();
            map.fitBounds(circle.getBounds(), { padding: [20, 20] });
        }

        function updateDisplay() {
            const lat = document.getElementById('latInput').value;
            const lng = document.getElementById('lngInput').value;
            document.getElementById('latLngDisplay').textContent =
                lat ? 'Lat: ' + lat + ', Lng: ' + lng : 'Lat: -, Lng: -';
        }

        function clearLocation() {
            if (marker) { map.removeLayer(marker); marker = null; }
            if (circle) { map.removeLayer(circle); circle = null; }
            setInputValue('latInput', '');
            setInputValue('lngInput', '');
            updateDisplay();
        }

        function confirmDelete() {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Event?',
                text: 'Data absensi guru pada event ini juga akan terhapus. Tindakan ini tidak dapat dibatalkan.',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm').submit();
                }
            });
        }

        document.getElementById('radiusInput').addEventListener('change', function() {
            const lat = document.getElementById('latInput').value;
            const lng = document.getElementById('lngInput').value;
            if (lat && lng) setMarker(parseFloat(lat), parseFloat(lng));
        });

        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            updateDisplay();
            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: '<ul style="text-align:left;padding-left:16px;">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Tutup'
                });
            @endif
            const header = document.querySelector('.header-auto-show');
            if (header) header.classList.add('header-active');
        });
    </script>
@endpush
