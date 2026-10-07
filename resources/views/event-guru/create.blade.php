@extends('layouts.app')

@section('title', 'Buat Event Guru Baru')

@push('styles')
    @include('components.event-styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css">
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
            pointer-events: none;
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

        .absen-toggle-card input:checked~.absen-toggle-label {
            border-color: #16a34a;
            background: #f0fdf4;
        }

        .absen-toggle-card input:checked~.absen-toggle-label .absen-toggle-status {
            background: #16a34a;
            color: #fff;
        }

        .absen-toggle-card.pulang input:checked~.absen-toggle-label {
            border-color: #0ea5e9;
            background: #e0f2fe;
        }

        .absen-toggle-card.pulang input:checked~.absen-toggle-label .absen-toggle-status {
            background: #0ea5e9;
            color: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">

        <div class="page-strip" style="background:linear-gradient(135deg,#1e40af 0%,#4338ca 100%);">
            <div class="live-badge"><span class="live-dot"></span> {{ now()->translatedFormat('l, d F Y') }}</div>
            <h2><i class="fas fa-chalkboard-teacher"></i> Tambah Event Guru</h2>
            <p>Kegiatan & absensi barcode khusus guru</p>
        </div>

        @if (session('success'))
            <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        <form id="createForm" method="POST" action="{{ route('event-guru.store') }}">
            @csrf

            {{-- Informasi Event --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#e0e7ff;"><i class="fas fa-info-circle"
                            style="color:#4338ca;"></i></div>
                    <h3>Informasi Event</h3>
                </div>
                <div class="c-body" style="padding:16px;">
                    <div style="margin-bottom:14px;">
                        <label class="form-label">Nama Event <span class="req">*</span></label>
                        <input type="text" name="nama_event" class="form-input @error('nama_event') is-error @enderror"
                            value="{{ old('nama_event') }}" placeholder="Contoh: Upacara 17 Agustus" required>
                        @error('nama_event')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="form-label">Kategori Event</label>
                        <input type="text" name="event_category_name"
                            class="form-input @error('event_category_name') is-error @enderror"
                            value="{{ old('event_category_name') }}" list="categoryList"
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
                        <textarea name="deskripsi" rows="3" class="form-input @error('deskripsi') is-error @enderror"
                            placeholder="Keterangan singkat...">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Lokasi / Alamat</label>
                        <input type="text" name="lokasi" id="lokasiInput"
                            class="form-input @error('lokasi') is-error @enderror" value="{{ old('lokasi') }}"
                            placeholder="Contoh: Aula SMKN 5 Madiun">
                        @error('lokasi')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Pin Lokasi --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#dcfce7;color:#15803d;"><i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Pin Lokasi di Peta</h3>
                    <span class="hbadge">Opsional</span>
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
                                class="form-input @error('lat') is-error @enderror" value="{{ old('lat') }}"
                                placeholder="-7.6291" readonly>
                            @error('lat')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Longitude</label>
                            <input type="text" name="lng" id="lngInput"
                                class="form-input @error('lng') is-error @enderror" value="{{ old('lng') }}"
                                placeholder="111.5230" readonly>
                            @error('lng')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Radius (meter)</label>
                            <input type="number" name="radius_meter" id="radiusInput"
                                class="form-input @error('radius_meter') is-error @enderror"
                                value="{{ old('radius_meter', 100) }}" min="10" max="5000">
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
                                value="{{ old('tanggal_mulai') }}" required>
                            @error('tanggal_mulai')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Tanggal Selesai <span class="req">*</span></label>
                            <input type="datetime-local" name="tanggal_selesai"
                                class="form-input @error('tanggal_selesai') is-error @enderror"
                                value="{{ old('tanggal_selesai') }}" required>
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
                    <div class="c-icon" style="background:#e0e7ff;"><i class="fas fa-qrcode" style="color:#4338ca;"></i>
                    </div>
                    <h3>Pengaturan Absen</h3>
                </div>
                <div class="c-body" style="padding:16px;">
                    <div class="absen-toggle-grid">
                        <div class="absen-toggle-card">
                            <input type="checkbox" id="chk_masuk" name="ada_absen_masuk" value="1"
                                @if (old('ada_absen_masuk', '1') === '1') checked @endif>
                            <label for="chk_masuk" class="absen-toggle-label">
                                <div class="absen-toggle-icon"><i class="fas fa-sign-in-alt" style="color:#16a34a;"></i>
                                </div>
                                <div class="absen-toggle-text">Absen Masuk</div>
                                <div class="absen-toggle-status">Aktif</div>
                            </label>
                        </div>
                        <div class="absen-toggle-card pulang">
                            <input type="checkbox" id="chk_pulang" name="ada_absen_pulang" value="1"
                                @if (old('ada_absen_pulang') === '1') checked @endif>
                            <label for="chk_pulang" class="absen-toggle-label">
                                <div class="absen-toggle-icon"><i class="fas fa-sign-out-alt" style="color:#0ea5e9;"></i>
                                </div>
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
                            value="{{ old('barcode_rotate_detik', 30) }}" min="0" max="3600">
                        <div class="form-hint">0 = barcode statis, &gt; 0 = berubah otomatis setiap N detik</div>
                        @error('barcode_rotate_detik')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

        </form>
    </div>

    <div class="action-bar">
        <a href="{{ route('event-guru.index') }}" class="ab-btn ab-btn-back"><i class="fas fa-times"></i> Batal</a>
        <button type="submit" form="createForm" class="ab-btn ab-btn-primary"
            style="background:#4338ca;box-shadow:0 3px 12px rgba(67,56,202,.3);">
            <i class="fas fa-calendar-plus"></i> Buat Event
        </button>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.js"></script>
    <script>
        let map, marker, circle;

        function setInputValue(id, val) {
            const el = document.getElementById(id);
            el.removeAttribute('readonly');
            el.value = val;
            el.setAttribute('readonly', true);
        }

        function initMap() {
            const savedLat = document.getElementById('latInput').value;
            const savedLng = document.getElementById('lngInput').value;
            const lat = savedLat ? parseFloat(savedLat) : -7.6291;
            const lng = savedLng ? parseFloat(savedLng) : 111.5230;
            map = L.map('eventMap').setView([lat, lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);
            L.Control.geocoder({
                    defaultMarkGeocode: false,
                    placeholder: 'Cari lokasi...'
                })
                .on('markgeocode', e => {
                    map.setView(e.geocode.center, 16);
                    setMarker(e.geocode.center.lat, e.geocode.center.lng);
                })
                .addTo(map);
            map.on('click', e => setMarker(e.latlng.lat, e.latlng.lng));
            if (savedLat && savedLng) setMarker(lat, lng);
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
                fillOpacity: .15,
                weight: 2
            }).addTo(map);
            setInputValue('latInput', lat.toFixed(6));
            setInputValue('lngInput', lng.toFixed(6));
            document.getElementById('latLngDisplay').textContent = 'Lat: ' + lat.toFixed(6) + ', Lng: ' + lng.toFixed(6);
            map.fitBounds(circle.getBounds(), {
                padding: [20, 20]
            });
        }

        function clearLocation() {
            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }
            if (circle) {
                map.removeLayer(circle);
                circle = null;
            }
            setInputValue('latInput', '');
            setInputValue('lngInput', '');
            document.getElementById('latLngDisplay').textContent = 'Lat: -, Lng: -';
        }
        document.getElementById('radiusInput').addEventListener('change', () => {
            const lat = document.getElementById('latInput').value;
            const lng = document.getElementById('lngInput').value;
            if (lat && lng) setMarker(parseFloat(lat), parseFloat(lng));
        });
        document.addEventListener('DOMContentLoaded', initMap);
    </script>
    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: '<ul style="text-align:left;padding-left:16px;">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>',
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Tutup'
            });
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header-auto-show');
            if (header) {
                header.classList.add('header-active');
            }
        });
    </script>
@endpush
