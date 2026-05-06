@extends('layouts.app')

@section('title', 'Edit - ' . $event->nama_event)

@push('styles')
    @include('components.event-styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css" />
    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .form-label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: var(--text-main, #0f172a);
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
            color: var(--text-main, #0f172a);
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
            box-sizing: border-box;
        }

        .form-input:focus {
            border-color: var(--event-primary, #f59e0b);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .12);
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
            color: var(--text-muted, #64748b);
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

        /* ── Absen toggle cards ── */
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

        /* Checkbox = full-area tap target, transparan di atas label */
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

        /* Label = visual only, di bawah checkbox */
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
            border: 2px solid var(--border, #e2e8f0);
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

        /* Masuk checked */
        .absen-toggle-card input:checked~.absen-toggle-label {
            border-color: #16a34a;
            background: #f0fdf4;
        }

        .absen-toggle-card input:checked~.absen-toggle-label .absen-toggle-status {
            background: #16a34a;
            color: #fff;
        }

        /* Pulang checked */
        .absen-toggle-card.pulang input:checked~.absen-toggle-label {
            border-color: #0ea5e9;
            background: #e0f2fe;
        }

        .absen-toggle-card.pulang input:checked~.absen-toggle-label .absen-toggle-status {
            background: #0ea5e9;
            color: #fff;
        }

        /* ── Mode radio peserta ── */
        .mode-radio-wrap {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
        }

        .mode-radio {
            flex: 1;
            position: relative;
        }

        .mode-radio input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
            margin: 0;
        }

        .mode-radio .mode-box {
            position: relative;
            z-index: 1;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 14px 10px;
            border: 2px solid var(--border, #e2e8f0);
            border-radius: 12px;
            background: #f8fafc;
            transition: all .18s;
            text-align: center;
        }

        .mode-radio input:checked~.mode-box {
            border-color: #0ea5e9;
            background: #e0f2fe;
        }

        .mode-radio .mode-icon {
            font-size: 1.4rem;
        }

        .mode-radio .mode-lbl {
            font-size: .78rem;
            font-weight: 700;
            color: var(--text-main);
        }

        /* ── Checkbox baris berlaku semua ── */
        .check-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            cursor: pointer;
        }

        .check-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #0ea5e9;
            cursor: pointer;
            flex-shrink: 0;
        }

        .check-row span {
            font-size: .85rem;
            color: var(--text-main);
        }

        /* ── Multi select ── */
        .multi-select-wrap {
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 10px;
            background: #fff;
            overflow: hidden;
        }

        .multi-select-search {
            width: 100%;
            padding: 10px 12px;
            border: none;
            border-bottom: 1px solid var(--border, #e2e8f0);
            font-size: .85rem;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
        }

        .multi-select-search:focus {
            background: #f8fafc;
        }

        .multi-select-list {
            max-height: 220px;
            overflow-y: auto;
            padding: 4px;
        }

        .multi-select-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: .82rem;
            transition: background .15s;
        }

        .multi-select-item:hover {
            background: #f1f5f9;
        }

        .multi-select-item.selected {
            background: #e0f2fe;
        }

        .multi-select-item input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: #0ea5e9;
            cursor: pointer;
            flex-shrink: 0;
        }

        .multi-select-item .item-meta {
            font-size: .7rem;
            color: var(--text-muted);
            margin-left: 4px;
        }

        .multi-select-count {
            font-size: .72rem;
            color: var(--text-muted);
            padding: 6px 12px;
            border-top: 1px solid var(--border, #e2e8f0);
            background: #f8fafc;
        }

        /* ── Map ── */
        .map-wrap {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border, #e2e8f0);
            margin-bottom: 14px;
        }

        #eventMap {
            width: 100%;
            height: 280px;
            z-index: 1;
        }

        .map-status {
            padding: 8px 12px;
            background: #f8fafc;
            border-top: 1px solid var(--border, #e2e8f0);
            font-size: .75rem;
            color: var(--text-muted);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* ── Action bar ── */
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
            gap: 10px;
            z-index: 999;
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

        .ab-btn-back:hover {
            background: #e2e8f0;
        }

        .ab-btn-primary {
            background: var(--event-primary, #f59e0b);
            color: #fff;
            box-shadow: 0 3px 12px rgba(245, 158, 11, .3);
        }

        .ab-btn-primary:hover {
            filter: brightness(1.07);
        }

        .ab-btn-delete {
            background: #ef4444;
            color: #fff;
            box-shadow: 0 3px 12px rgba(239, 68, 68, .25);
            flex: 0 0 auto;
            padding: 12px 18px;
        }

        .ab-btn-delete:hover {
            background: #dc2626;
        }

        .leaflet-control-geocoder-form input {
            font-family: inherit;
            font-size: .85rem;
            padding: 6px 10px;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap" style="padding-bottom: calc(var(--footer-h) + 88px);">

        {{-- Page Strip --}}
        <div class="page-strip page-strip-orange">
            <div class="live-badge">
                <span class="live-dot" style="background:#fde68a;"></span>
                Edit Event
            </div>
            <h2><i class="fas fa-pen-to-square"></i> {{ Str::limit($event->nama_event, 30) }}</h2>
            <p>Perbarui informasi acara</p>
        </div>



        <form id="editForm" method="POST" action="{{ route('event.update', $event) }}">
            @csrf
            @method('PUT')

            {{-- ① Informasi Event --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:var(--event-fade,#fef3c7);"><i class="fas fa-info-circle"></i>
                    </div>
                    <h3>Informasi Event</h3>
                </div>
                <div class="c-body" style="padding:16px;">
                    <div style="margin-bottom:14px;">
                        <label class="form-label">Nama Event <span class="req">*</span></label>
                        <input type="text" name="nama_event" class="form-input @error('nama_event') is-error @enderror"
                            value="{{ old('nama_event', $event->nama_event) }}" required>
                        @error('nama_event')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" class="form-input @error('deskripsi') is-error @enderror">{{ old('deskripsi', $event->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Lokasi / Alamat</label>
                        <input type="text" name="lokasi" id="lokasiInput"
                            class="form-input @error('lokasi') is-error @enderror"
                            value="{{ old('lokasi', $event->lokasi) }}">
                        @error('lokasi')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ② Pin Lokasi --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#dcfce7; color:#15803d;"><i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Pin Lokasi di Peta</h3>
                    <span class="hbadge" style="background:#f1f5f9; color:#64748b;">Opsional</span>
                </div>
                <div class="c-body" style="padding:16px;">
                    <p style="color:var(--text-muted);font-size:.82rem;line-height:1.55;margin:0 0 12px;">
                        Jika lokasi ditentukan, siswa harus berada dalam radius untuk bisa absen. Kosongkan jika bebas.
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
                                class="form-input @error('lat') is-error @enderror" value="{{ old('lat', $event->lat) }}"
                                placeholder="-7.6291" readonly>
                            @error('lat')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Longitude</label>
                            <input type="text" name="lng" id="lngInput"
                                class="form-input @error('lng') is-error @enderror" value="{{ old('lng', $event->lng) }}"
                                placeholder="111.5230" readonly>
                            @error('lng')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Radius (meter)</label>
                            <input type="number" name="radius_meter" id="radiusInput"
                                class="form-input @error('radius_meter') is-error @enderror"
                                value="{{ old('radius_meter', $event->radius_meter ?? 100) }}" min="10"
                                max="5000">
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

            {{-- ③ Waktu --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#fef3c7;"><i class="fas fa-clock"></i></div>
                    <h3>Waktu Pelaksanaan</h3>
                </div>
                <div class="c-body" style="padding:16px;">
                    <div class="grid-2">
                        <div>
                            <label class="form-label">Mulai <span class="req">*</span></label>
                            <input type="datetime-local" name="tanggal_mulai"
                                class="form-input @error('tanggal_mulai') is-error @enderror"
                                value="{{ old('tanggal_mulai', $event->tanggal_mulai->format('Y-m-d\TH:i')) }}" required>
                            @error('tanggal_mulai')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Selesai <span class="req">*</span></label>
                            <input type="datetime-local" name="tanggal_selesai"
                                class="form-input @error('tanggal_selesai') is-error @enderror"
                                value="{{ old('tanggal_selesai', $event->tanggal_selesai->format('Y-m-d\TH:i')) }}"
                                required>
                            @error('tanggal_selesai')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ④ Pengaturan Absen --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#dcfce7;"><i class="fas fa-qrcode"></i></div>
                    <h3>Pengaturan Absen</h3>
                </div>
                <div class="c-body" style="padding:16px;">

                    <div class="absen-toggle-grid">
                        {{-- Absen Masuk --}}
                        <div class="absen-toggle-card">
                            <input type="checkbox" id="chk_masuk" name="ada_absen_masuk" value="1"
                                {{ old('ada_absen_masuk', $event->ada_absen_masuk) ? 'checked' : '' }}>
                            <label for="chk_masuk" class="absen-toggle-label">
                                <div class="absen-toggle-icon">
                                    <i class="fas fa-sign-in-alt" style="color:#16a34a;"></i>
                                </div>
                                <div class="absen-toggle-text">Absen Masuk</div>
                                <div class="absen-toggle-status">Aktif</div>
                            </label>
                        </div>
                        {{-- Absen Pulang --}}
                        <div class="absen-toggle-card pulang">
                            <input type="checkbox" id="chk_pulang" name="ada_absen_pulang" value="1"
                                {{ old('ada_absen_pulang', $event->ada_absen_pulang) ? 'checked' : '' }}>
                            <label for="chk_pulang" class="absen-toggle-label">
                                <div class="absen-toggle-icon">
                                    <i class="fas fa-sign-out-alt" style="color:#0ea5e9;"></i>
                                </div>
                                <div class="absen-toggle-text">Absen Pulang</div>
                                <div class="absen-toggle-status">Aktif</div>
                            </label>
                        </div>
                    </div>

                    <div class="c-divider"></div>

                    <div>
                        <label class="form-label">Rotasi Barcode (detik)</label>
                        <input type="number" name="barcode_rotate_detik"
                            class="form-input @error('barcode_rotate_detik') is-error @enderror"
                            value="{{ old('barcode_rotate_detik', $event->barcode_rotate_detik) }}" min="0"
                            max="3600">
                        <div class="form-hint">0 = statis, &gt; 0 = berubah otomatis</div>
                        @error('barcode_rotate_detik')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ⑤ Peserta --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#ede9fe; color:#7c3aed;"><i class="fas fa-users"></i></div>
                    <h3>Peserta Event</h3>
                </div>
                <div class="c-body" style="padding:16px;">
                    <label class="form-label">Pilih Mode Peserta <span class="req">*</span></label>
                    <div class="mode-radio-wrap">
                        <div class="mode-radio">
                            <input type="radio" name="mode_peserta" value="kelas"
                                {{ old('mode_peserta', $event->mode_peserta ?? 'kelas') === 'kelas' ? 'checked' : '' }}
                                onchange="toggleMode()">
                            <div class="mode-box">
                                <div class="mode-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                <div class="mode-lbl">Per Kelas</div>
                            </div>
                        </div>
                        <div class="mode-radio">
                            <input type="radio" name="mode_peserta" value="siswa"
                                {{ old('mode_peserta', $event->mode_peserta ?? 'kelas') === 'siswa' ? 'checked' : '' }}
                                onchange="toggleMode()">
                            <div class="mode-box">
                                <div class="mode-icon"><i class="fas fa-user-graduate"></i></div>
                                <div class="mode-lbl">Per Siswa</div>
                            </div>
                        </div>
                    </div>

                    <div class="c-divider"></div>

                    <label class="check-row">
                        <input type="checkbox" name="berlaku_untuk_semua" id="berlakuSemua" value="1"
                            {{ old('berlaku_untuk_semua', $event->berlaku_untuk_semua) ? 'checked' : '' }}
                            onchange="togglePeserta()">
                        <span><strong>Berlaku untuk semua</strong></span>
                    </label>
                    @php
                        $isSemua = old('berlaku_untuk_semua', $event->berlaku_untuk_semua);
                        $mode = old('mode_peserta', $event->mode_peserta ?? 'kelas');

                        $initialKelas = $event->kelas
                            ->map(
                                fn($k) => [
                                    'id' => $k->id,
                                    'text' => $k->nama_kelas,
                                    'meta' => $k->jurusan->nama_jurusan ?? '',
                                ],
                            )
                            ->toArray();

                        $initialSiswa = $event->siswa
                            ->map(
                                fn($s) => [
                                    'id' => $s->id,
                                    'text' => $s->nama_lengkap,
                                    'meta' => $s->kelas->nama_kelas ?? '',
                                ],
                            )
                            ->toArray();

                        if (old('kelas_id')) {
                            $initialKelas = collect(old('kelas_id'))
                                ->map(
                                    fn($id) => [
                                        'id' => $id,
                                        'text' => \App\Models\Kelas::find($id)?->nama_kelas ?? $id,
                                        'meta' => '',
                                    ],
                                )
                                ->values()
                                ->toArray();
                        }
                        if (old('siswa_id')) {
                            $initialSiswa = collect(old('siswa_id'))
                                ->map(
                                    fn($id) => [
                                        'id' => $id,
                                        'text' => \App\Models\Siswa::find($id)?->nama_lengkap ?? $id,
                                        'meta' => '',
                                    ],
                                )
                                ->values()
                                ->toArray();
                        }
                    @endphp

                    <div id="kelasGroup"
                        style="display:{{ $isSemua ? 'none' : ($mode === 'kelas' ? 'block' : 'none') }};">
                        @include('components.ajax-multi-select', [
                            'selectId' => 'kelas',
                            'inputName' => 'kelas_id[]',
                            'placeholder' => 'Ketik nama kelas...',
                            'searchUrl' => route('event.search.kelas'),
                            'label' => 'kelas',
                            'initialData' => $initialKelas,
                        ])
                        @error('kelas_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="siswaGroup"
                        style="display:{{ $isSemua ? 'none' : ($mode === 'siswa' ? 'block' : 'none') }};">
                        @include('components.ajax-multi-select', [
                            'selectId' => 'siswa',
                            'inputName' => 'siswa_id[]',
                            'placeholder' => 'Ketik nama atau NIS siswa...',
                            'searchUrl' => route('event.search.siswa'),
                            'label' => 'siswa',
                            'initialData' => $initialSiswa,
                        ])
                        @error('siswa_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

        </form>

        <form id="deleteForm" method="POST" action="{{ route('event.destroy', $event) }}">
            @csrf @method('DELETE')
        </form>

    </div>

    {{-- Action Bar --}}
    <div class="action-bar">
        <a href="{{ route('event.show', $event) }}" class="ab-btn ab-btn-back">
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
        const savedLat = {{ $event->lat ? $event->lat : 'null' }};
        const savedLng = {{ $event->lng ? $event->lng : 'null' }};

        function setInputValue(id, val) {
            const el = document.getElementById(id);
            el.removeAttribute('readonly');
            el.value = val;
            el.setAttribute('readonly', true);
        }

        function initMap() {
            const lat = savedLat || -7.6291,
                lng = savedLng || 111.5230;
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
            }).on('markgeocode', function(e) {
                map.setView(e.geocode.center, 16);
                setMarker(e.geocode.center.lat, e.geocode.center.lng);
            }).addTo(map);
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
                color: '#0ea5e9',
                fillColor: '#0ea5e9',
                fillOpacity: 0.15,
                weight: 2
            }).addTo(map);
            setInputValue('latInput', lat.toFixed(6));
            setInputValue('lngInput', lng.toFixed(6));
            updateDisplay();
            map.fitBounds(circle.getBounds(), {
                padding: [20, 20]
            });
        }

        function updateDisplay() {
            const lat = document.getElementById('latInput').value;
            const lng = document.getElementById('lngInput').value;
            document.getElementById('latLngDisplay').textContent = lat ? 'Lat: ' + lat + ', Lng: ' + lng : 'Lat: -, Lng: -';
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
            updateDisplay();
        }

        document.getElementById('radiusInput').addEventListener('change', function() {
            const lat = document.getElementById('latInput').value;
            const lng = document.getElementById('lngInput').value;
            if (lat && lng) setMarker(parseFloat(lat), parseFloat(lng));
        });

        function toggleMode() {
            const mode = document.querySelector('input[name="mode_peserta"]:checked').value;
            if (!document.getElementById('berlakuSemua').checked) {
                document.getElementById('kelasGroup').style.display = mode === 'kelas' ? 'block' : 'none';
                document.getElementById('siswaGroup').style.display = mode === 'siswa' ? 'block' : 'none';
            }
        }

        function togglePeserta() {
            const isSemua = document.getElementById('berlakuSemua').checked;
            if (isSemua) {
                document.getElementById('kelasGroup').style.display = 'none';
                document.getElementById('siswaGroup').style.display = 'none';
            } else {
                toggleMode();
            }
        }

        function filterList(input, listId) {
            const q = input.value.toLowerCase();
            document.querySelectorAll('#' + listId + ' .multi-select-item').forEach(function(item) {
                item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        }

        function updateCount(listId, countId) {
            const count = document.querySelectorAll('#' + listId + ' input:checked').length;
            const label = listId === 'kelasList' ? 'kelas' : 'siswa';
            document.getElementById(countId).textContent = count + ' ' + label + ' dipilih';
        }

        function confirmDelete() {
            if (confirm('Yakin ingin menghapus event ini? Data absensi juga akan terhapus.')) {
                document.getElementById('deleteForm').submit();
            }
        }

        // Show validation errors via SweetAlert
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: '<ul style="text-align:left;padding-left:16px;">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>',
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Tutup',
            });
        @endif

        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            updateCount('kelasList', 'kelasCount');
            updateCount('siswaList', 'siswaCount');
            updateDisplay();
        });
    </script>
@endpush
