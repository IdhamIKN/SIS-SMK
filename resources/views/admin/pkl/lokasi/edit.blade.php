@extends('layouts.app')
@section('title', 'Edit Lokasi PKL — ' . $lokasiPkl->nama_tempat)

@push('styles')
    @include('components.event-styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css">
    <style>
        .form-wrap {
            padding-top: var(--header-h, 56px);
            padding-bottom: calc(var(--footer-h, 60px) + 88px);
            max-width: 760px;
            margin: 0 auto;
            padding-left: 12px;
            padding-right: 12px;
        }

        .form-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 14px;
        }

        .form-card h3 {
            font-size: .9rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        @media(min-width:640px) {
            .form-grid-2 {
                grid-template-columns: 1fr 1fr;
            }

            .form-grid-3 {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }

        .form-group label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        .form-group label span {
            color: #ef4444;
            margin-left: 2px;
        }

        .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            background: #fff;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: 2px solid #f59e0b;
            outline-offset: -1px;
        }

        .is-invalid {
            border-color: #ef4444;
        }

        .invalid-feedback {
            font-size: .72rem;
            color: #ef4444;
            margin-top: 3px;
        }

        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .toggle-row:last-child {
            border-bottom: none;
        }

        .toggle-label {
            font-size: .85rem;
            font-weight: 600;
            color: #0f172a;
        }

        .toggle-sub {
            font-size: .72rem;
            color: #64748b;
            margin-top: 2px;
        }

        .toggle-switch {
            position: relative;
            width: 40px;
            height: 22px;
            flex-shrink: 0;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            inset: 0;
            background: #cbd5e1;
            border-radius: 22px;
            cursor: pointer;
            transition: .2s;
        }

        .toggle-slider:before {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            left: 3px;
            bottom: 3px;
            background: #fff;
            border-radius: 50%;
            transition: .2s;
        }

        .toggle-switch input:checked+.toggle-slider {
            background: #f59e0b;
        }

        .toggle-switch input:checked+.toggle-slider:before {
            transform: translateX(18px);
        }

        .hint {
            font-size: .72rem;
            color: #64748b;
            margin-top: 3px;
        }

        .foto-preview {
            max-width: 120px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 8px;
        }

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
            gap: 8px;
            z-index: 999;
        }

        @media(min-width:768px) {
            .action-bar {
                padding: 10px 24px 12px;
                justify-content: flex-end;
            }
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: .82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
        }

        @media(min-width:768px) {
            .ab-btn {
                flex: unset;
                min-width: 130px;
            }
        }

        .ab-btn-amber {
            background: #f59e0b;
            color: #fff;
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        /* ── Peta (identik dengan halaman tambah) ─────────────────────────── */
        #pklMap {
            width: 100%;
            height: 280px;
        }

        .map-wrap {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            margin-top: 10px;
        }

        .map-status {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 12px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: .72rem;
            color: #64748b;
        }

        .map-status span:last-child {
            font-weight: 700;
            color: #0f172a;
        }

        .leaflet-control-geocoder-form input {
            font-family: inherit;
            font-size: .85rem;
            padding: 6px 10px;
        }
    </style>
@endpush

@section('content')
    <div class="form-wrap">
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>PKL</div>
            <h2><i class="fas fa-pen"></i> Edit Lokasi PKL</h2>
            <p>{{ $lokasiPkl->nama_tempat }}</p>
        </div>

        @if (session('success'))
            <div
                style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.pkl.lokasi.update', $lokasiPkl) }}" enctype="multipart/form-data"
            id="lokasiForm">
            @csrf @method('PUT')

            {{-- Identitas --}}
            <div class="form-card">
                <h3><i class="fas fa-building" style="color:#f59e0b;"></i> Identitas Tempat</h3>
                <div class="form-grid form-grid-2" style="gap:12px;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Nama Tempat <span>*</span></label>
                        <input type="text" name="nama_tempat"
                            class="form-control @error('nama_tempat') is-invalid @enderror"
                            value="{{ old('nama_tempat', $lokasiPkl->nama_tempat) }}" required>
                        @error('nama_tempat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Jenis Usaha</label>
                        <input type="text" name="jenis_usaha" class="form-control"
                            value="{{ old('jenis_usaha', $lokasiPkl->jenis_usaha) }}">
                    </div>
                    <div class="form-group">
                        <label>Tahun Ajaran</label>
                        <select name="academic_year_id" class="form-control">
                            <option value="">— Pilih —</option>
                            @foreach ($academicYears as $ay)
                                <option value="{{ $ay->id }}"
                                    {{ old('academic_year_id', $lokasiPkl->academic_year_id) == $ay->id ? 'selected' : '' }}>
                                    {{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kapasitas</label>
                        <input type="number" name="kapasitas" class="form-control"
                            value="{{ old('kapasitas', $lokasiPkl->kapasitas) }}" min="1">
                    </div>
                    <div class="form-group">
                        <label>Foto Lokasi</label>
                        @if ($lokasiPkl->foto)
                            <img src="{{ Storage::url($lokasiPkl->foto) }}" class="foto-preview" alt="Foto">
                        @endif
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <div class="hint">Kosongkan jika tidak ingin mengubah foto.</div>
                    </div>
                </div>
            </div>

            {{-- ── ALAMAT (sekarang pakai dropdown wilayah dari API + peta interaktif,
                 sama seperti halaman tambah, dan otomatis ter-prefill dari data tersimpan) ── --}}
            <div class="form-card">
                <h3><i class="fas fa-map-marker-alt" style="color:#ef4444;"></i> Alamat Lengkap</h3>

                <div class="form-group">
                    <label>Alamat <span>*</span></label>
                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="2" required>{{ old('alamat', $lokasiPkl->alamat) }}</textarea>
                    @error('alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grid form-grid-2" style="gap:12px;margin-top:12px;">
                    <div class="form-group">
                        <label>Provinsi</label>
                        <select name="provinsi" id="selProvinsi" class="form-control">
                            <option value="">— Pilih Provinsi —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kabupaten / Kota</label>
                        <select name="kabupaten" id="selKabupaten" class="form-control" disabled>
                            <option value="">— Pilih Kab/Kota —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kecamatan</label>
                        <select name="kecamatan" id="selKecamatan" class="form-control" disabled>
                            <option value="">— Pilih Kecamatan —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kelurahan / Desa</label>
                        <select name="kelurahan" id="selKelurahan" class="form-control" disabled>
                            <option value="">— Pilih Kelurahan —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            Kode Pos
                            <span id="kodePosLoading"
                                style="display:none;font-size:.68rem;color:#64748b;font-weight:400;margin-left:6px;">
                                <i class="fas fa-spinner fa-spin"></i> mencari...
                            </span>
                        </label>
                        <input type="text" name="kode_pos" id="inpKodePos" class="form-control"
                            value="{{ old('kode_pos', $lokasiPkl->kode_pos) }}"
                            placeholder="Masukan Kode Pos sesuai wilayah" maxlength="10">
                    </div>
                </div>

                <div class="hint" id="wilayahRestoreHint" style="margin-top:6px;"></div>

                {{-- Koordinat GPS --}}
                <div class="form-grid form-grid-3" style="gap:12px;margin-top:12px;">
                    <div class="form-group">
                        <label>Latitude</label>
                        <input type="text" name="latitude" id="latInput" class="form-control"
                            value="{{ old('latitude', $lokasiPkl->latitude) }}" placeholder="-7.123456" readonly>
                    </div>
                    <div class="form-group">
                        <label>Longitude</label>
                        <input type="text" name="longitude" id="lngInput" class="form-control"
                            value="{{ old('longitude', $lokasiPkl->longitude) }}" placeholder="110.123456" readonly>
                    </div>
                    <div class="form-group">
                        <label>Radius Absen (meter)</label>
                        <input type="number" name="radius_meter" id="radiusInput" class="form-control"
                            value="{{ old('radius_meter', $lokasiPkl->radius_meter ?? 200) }}" min="50"
                            max="5000">
                    </div>
                </div>

                {{-- Peta Leaflet + Geocoder (identik dengan halaman tambah) --}}
                <div class="map-wrap" style="margin-top:12px;">
                    <div id="pklMap"></div>
                    <div class="map-status">
                        <span><i class="fas fa-mouse-pointer"></i> Klik peta atau gunakan pencarian untuk ubah
                            lokasi</span>
                        <span id="latLngDisplay">Lat: -, Lng: -</span>
                    </div>
                </div>

                <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;">
                    <button type="button" onclick="hapusPin()" class="action-btn"
                        style="background:#fee2e2;color:#dc2626;border:none;border-radius:8px;padding:8px 14px;font-size:.78rem;font-weight:700;cursor:pointer;">
                        <i class="fas fa-times"></i> Hapus Pin
                    </button>
                    <button type="button" onclick="dapatkanLokasi()" class="action-btn btn-view"
                        style="font-size:.78rem;padding:8px 14px;">
                        <i class="fas fa-crosshairs"></i> Lokasi Saya
                    </button>
                </div>
                <div class="hint" style="margin-top:5px;">
                    Gunakan kotak pencarian di peta, klik pada peta, atau tombol "Lokasi Saya" untuk mengubah titik lokasi.
                    Lingkaran biru = radius absen.
                </div>
            </div>

            {{-- PJ --}}
            <div class="form-card">
                <h3><i class="fas fa-user-tie" style="color:#6366f1;"></i> Penanggung Jawab</h3>
                <div class="form-grid form-grid-2" style="gap:12px;">
                    <div class="form-group"><label>Nama PJ</label><input type="text" name="nama_pj"
                            class="form-control" value="{{ old('nama_pj', $lokasiPkl->nama_pj) }}"></div>
                    <div class="form-group"><label>Jabatan</label><input type="text" name="jabatan_pj"
                            class="form-control" value="{{ old('jabatan_pj', $lokasiPkl->jabatan_pj) }}"></div>
                    <div class="form-group"><label>No. HP</label><input type="text" name="no_hp_pj"
                            class="form-control" value="{{ old('no_hp_pj', $lokasiPkl->no_hp_pj) }}"></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email_pj"
                            class="form-control" value="{{ old('email_pj', $lokasiPkl->email_pj) }}"></div>
                    <div class="form-group"><label>Telp. Kantor</label><input type="text" name="no_telp_kantor"
                            class="form-control" value="{{ old('no_telp_kantor', $lokasiPkl->no_telp_kantor) }}"></div>
                    <div class="form-group"><label>Website</label><input type="url" name="website"
                            class="form-control" value="{{ old('website', $lokasiPkl->website) }}"></div>
                </div>
            </div>

            {{-- Jam --}}
            <div class="form-card">
                <h3><i class="fas fa-clock" style="color:#0ea5e9;"></i> Jam Absen PKL</h3>
                <div class="form-grid form-grid-2" style="gap:12px;">
                    <div class="form-group"><label>Jam Masuk</label><input type="time" name="jam_masuk_pkl"
                            class="form-control"
                            value="{{ old('jam_masuk_pkl', $lokasiPkl->jam_masuk_pkl ? \Carbon\Carbon::parse($lokasiPkl->jam_masuk_pkl)->format('H:i') : '') }}">
                    </div>
                    <div class="form-group"><label>Batas Tepat Waktu</label><input type="time"
                            name="batas_terlambat_pkl" class="form-control"
                            value="{{ old('batas_terlambat_pkl', $lokasiPkl->batas_terlambat_pkl ? \Carbon\Carbon::parse($lokasiPkl->batas_terlambat_pkl)->format('H:i') : '') }}">
                    </div>
                    <div class="form-group"><label>Batas Akhir Absen Masuk</label><input type="time"
                            name="batas_absen_masuk_pkl" class="form-control"
                            value="{{ old('batas_absen_masuk_pkl', $lokasiPkl->batas_absen_masuk_pkl ? \Carbon\Carbon::parse($lokasiPkl->batas_absen_masuk_pkl)->format('H:i') : '') }}">
                    </div>
                    <div class="form-group"><label>Jam Pulang</label><input type="time" name="jam_pulang_pkl"
                            class="form-control"
                            value="{{ old('jam_pulang_pkl', $lokasiPkl->jam_pulang_pkl ? \Carbon\Carbon::parse($lokasiPkl->jam_pulang_pkl)->format('H:i') : '') }}">
                    </div>
                </div>
            </div>

            {{-- Auto Poin --}}
            {{-- JSON data pasal untuk JS autocomplete --}}
            <script id="pkl-pasal-pelanggaran-data" type="application/json">
            {!! json_encode($pasalPelanggaran->map(fn($p) => [
                'id'    => $p->idpasal,
                'label' => '[' . $p->idpasal . '] ' . $p->pasal,
                'isi'   => $p->pasal,
                'poin'  => $p->poin_default,
            ])) !!}
        </script>
            <script id="pkl-pasal-penghargaan-data" type="application/json">
            {!! json_encode($pasalPenghargaan->map(fn($p) => [
                'id'    => $p->idpasal,
                'label' => '[' . $p->idpasal . '] ' . $p->pasal,
                'isi'   => $p->pasal,
                'poin'  => $p->poin_default,
            ])) !!}
        </script>

            <div class="form-card">
                <h3><i class="fas fa-star" style="color:#eab308;"></i> Auto Poin PKL</h3>

                {{-- HADIR --}}
                @php $hadirEnabled = old('auto_poin_hadir_pkl', $lokasiPkl->auto_poin_hadir_pkl); @endphp
                <div class="toggle-row">
                    <div>
                        <div class="toggle-label"><i class="fas fa-check-circle"
                                style="color:#16a34a;margin-right:5px;"></i> Poin Hadir PKL</div>
                        <div class="toggle-sub">Penghargaan hadir tepat waktu</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="auto_poin_hadir_pkl" id="toggleHadir" value="1"
                            {{ $hadirEnabled ? 'checked' : '' }} onchange="togglePasal('hadir',this.checked)">
                        <span class="toggle-slider" style="{{ $hadirEnabled ? 'background:#16a34a;' : '' }}"></span>
                    </label>
                </div>
                <div id="pasalHadirDiv" style="{{ $hadirEnabled ? '' : 'display:none;' }}padding:10px 0 14px 0;">
                    <div class="form-group">
                        <label>Pasal Penghargaan Hadir</label>
                        <input type="hidden" name="pasal_hadir_pkl_id" id="pkl_pasal_hadir_hidden"
                            value="{{ old('pasal_hadir_pkl_id', $lokasiPkl->pasal_hadir_pkl_id) }}">
                        <input type="text" id="pkl_pasal_hadir_search" class="form-control"
                            placeholder="Ketik kode atau nama pasal penghargaan…" autocomplete="off">
                        <div id="pkl_pasal_hadir_badge"
                            style="display:none;align-items:center;gap:8px;margin-top:8px;font-size:.81rem;color:#16a34a;background:#f0fdf4;padding:9px 12px;border-radius:8px;border:1px solid #bbf7d0;">
                            <i class="fas fa-tag" style="flex-shrink:0;"></i>
                            <span id="pkl_pasal_hadir_badge_text" style="flex:1;"></span>
                            <button type="button" onclick="clearPklPasal('hadir')"
                                style="background:none;border:none;cursor:pointer;color:#16a34a;font-size:1rem;">✕</button>
                        </div>
                    </div>
                </div>
                <div id="pkl_pasal_hadir_dropdown"
                    style="display:none;position:fixed;background:#fff;border:1.5px solid #e2e8f0;border-radius:10px;max-height:260px;overflow-y:auto;z-index:9999;box-shadow:0 8px 30px rgba(0,0,0,.14);">
                </div>

                {{-- TERLAMBAT --}}
                @php $terlambatEnabled = old('auto_poin_terlambat_pkl', $lokasiPkl->auto_poin_terlambat_pkl); @endphp
                <div class="toggle-row">
                    <div>
                        <div class="toggle-label"><i class="fas fa-clock" style="color:#f59e0b;margin-right:5px;"></i>
                            Poin Terlambat PKL</div>
                        <div class="toggle-sub">Pelanggaran terlambat hadir</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="auto_poin_terlambat_pkl" id="toggleTerlambat" value="1"
                            {{ $terlambatEnabled ? 'checked' : '' }} onchange="togglePasal('terlambat',this.checked)">
                        <span class="toggle-slider" style="{{ $terlambatEnabled ? 'background:#f59e0b;' : '' }}"></span>
                    </label>
                </div>
                <div id="pasalTerlambatDiv" style="{{ $terlambatEnabled ? '' : 'display:none;' }}padding:10px 0 14px 0;">
                    <div class="form-group">
                        <label>Pasal Pelanggaran Terlambat</label>
                        <input type="hidden" name="pasal_terlambat_pkl_id" id="pkl_pasal_terlambat_hidden"
                            value="{{ old('pasal_terlambat_pkl_id', $lokasiPkl->pasal_terlambat_pkl_id) }}">
                        <input type="text" id="pkl_pasal_terlambat_search" class="form-control"
                            placeholder="Ketik kode atau nama pasal pelanggaran…" autocomplete="off">
                        <div id="pkl_pasal_terlambat_badge"
                            style="display:none;align-items:center;gap:8px;margin-top:8px;font-size:.81rem;color:#dc2626;background:#fef2f2;padding:9px 12px;border-radius:8px;border:1px solid #fecaca;">
                            <i class="fas fa-tag" style="flex-shrink:0;"></i>
                            <span id="pkl_pasal_terlambat_badge_text" style="flex:1;"></span>
                            <button type="button" onclick="clearPklPasal('terlambat')"
                                style="background:none;border:none;cursor:pointer;color:#ef4444;font-size:1rem;">✕</button>
                        </div>
                    </div>
                </div>
                <div id="pkl_pasal_terlambat_dropdown"
                    style="display:none;position:fixed;background:#fff;border:1.5px solid #e2e8f0;border-radius:10px;max-height:260px;overflow-y:auto;z-index:9999;box-shadow:0 8px 30px rgba(0,0,0,.14);">
                </div>

                {{-- ALFA --}}
                @php $alfaEnabled = old('auto_poin_alfa_pkl', $lokasiPkl->auto_poin_alfa_pkl); @endphp
                <div class="toggle-row">
                    <div>
                        <div class="toggle-label"><i class="fas fa-times-circle"
                                style="color:#dc2626;margin-right:5px;"></i> Poin Alfa PKL</div>
                        <div class="toggle-sub">Pelanggaran tidak hadir tanpa keterangan</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="auto_poin_alfa_pkl" id="toggleAlfa" value="1"
                            {{ $alfaEnabled ? 'checked' : '' }} onchange="togglePasal('alfa',this.checked)">
                        <span class="toggle-slider" style="{{ $alfaEnabled ? 'background:#dc2626;' : '' }}"></span>
                    </label>
                </div>
                <div id="pasalAlfaDiv" style="{{ $alfaEnabled ? '' : 'display:none;' }}padding:10px 0 14px 0;">
                    <div class="form-group">
                        <label>Pasal Pelanggaran Alfa PKL</label>
                        <input type="hidden" name="pasal_alfa_pkl_id" id="pkl_pasal_alfa_hidden"
                            value="{{ old('pasal_alfa_pkl_id', $lokasiPkl->pasal_alfa_pkl_id) }}">
                        <input type="text" id="pkl_pasal_alfa_search" class="form-control"
                            placeholder="Ketik kode atau nama pasal pelanggaran…" autocomplete="off">
                        <div id="pkl_pasal_alfa_badge"
                            style="display:none;align-items:center;gap:8px;margin-top:8px;font-size:.81rem;color:#dc2626;background:#fef2f2;padding:9px 12px;border-radius:8px;border:1px solid #fecaca;">
                            <i class="fas fa-tag" style="flex-shrink:0;"></i>
                            <span id="pkl_pasal_alfa_badge_text" style="flex:1;"></span>
                            <button type="button" onclick="clearPklPasal('alfa')"
                                style="background:none;border:none;cursor:pointer;color:#ef4444;font-size:1rem;">✕</button>
                        </div>
                    </div>
                </div>
                <div id="pkl_pasal_alfa_dropdown"
                    style="display:none;position:fixed;background:#fff;border:1.5px solid #e2e8f0;border-radius:10px;max-height:260px;overflow-y:auto;z-index:9999;box-shadow:0 8px 30px rgba(0,0,0,.14);">
                </div>
            </div>

            {{-- Catatan & Status --}}
            <div class="form-card">
                <h3><i class="fas fa-cog" style="color:#64748b;"></i> Catatan & Status</h3>
                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="catatan" class="form-control" rows="2">{{ old('catatan', $lokasiPkl->catatan) }}</textarea>
                </div>
                <div class="toggle-row" style="border:none;padding:8px 0 0;">
                    <div>
                        <div class="toggle-label">Status Aktif</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="status_aktif" value="1"
                            {{ old('status_aktif', $lokasiPkl->status_aktif) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </form>
    </div>

    <div class="action-bar">
        <a href="{{ route('admin.pkl.lokasi.show', $lokasiPkl) }}" class="ab-btn ab-btn-back"><i
                class="fas fa-arrow-left"></i></a>
        <button type="submit" form="lokasiForm" class="ab-btn ab-btn-amber"><i class="fas fa-save"></i> Simpan
            Perubahan</button>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.js"></script>
    <script>
        // ══════════════════════════════════════════════════════════════════════
        // MAP — identik dengan halaman tambah, tapi titik awal diambil dari
        // data lokasi yang sudah tersimpan (latInput/lngInput terisi dari server)
        // ══════════════════════════════════════════════════════════════════════
        let map, marker, circle;

        function setInputValue(id, val) {
            const el = document.getElementById(id);
            if (!el) return;
            el.removeAttribute('readonly');
            el.value = val;
            el.setAttribute('readonly', true);
        }

        function initMap() {
            const defaultLat = -7.6291,
                defaultLng = 111.5230;
            const savedLat = document.getElementById('latInput').value;
            const savedLng = document.getElementById('lngInput').value;
            const lat = savedLat ? parseFloat(savedLat) : defaultLat;
            const lng = savedLng ? parseFloat(savedLng) : defaultLng;

            map = L.map('pklMap').setView([lat, lng], savedLat && savedLng ? 16 : 13);

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

            if (savedLat && savedLng) setMarker(parseFloat(savedLat), parseFloat(savedLng));
        }

        function setMarker(lat, lng) {
            const radius = parseInt(document.getElementById('radiusInput').value) || 200;
            if (marker) map.removeLayer(marker);
            if (circle) map.removeLayer(circle);

            marker = L.marker([lat, lng], {
                draggable: true
            }).addTo(map);
            circle = L.circle([lat, lng], {
                radius,
                color: '#0ea5e9',
                fillColor: '#0ea5e9',
                fillOpacity: 0.15,
                weight: 2
            }).addTo(map);

            marker.on('dragend', function(e) {
                const p = e.target.getLatLng();
                setMarker(p.lat, p.lng);
            });

            setInputValue('latInput', lat.toFixed(6));
            setInputValue('lngInput', lng.toFixed(6));
            updateDisplay();
        }

        function updateDisplay() {
            const lat = document.getElementById('latInput').value;
            const lng = document.getElementById('lngInput').value;
            document.getElementById('latLngDisplay').textContent =
                lat ? 'Lat: ' + lat + ', Lng: ' + lng : 'Lat: -, Lng: -';
        }

        function hapusPin() {
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

        function dapatkanLokasi() {
            if (!navigator.geolocation) {
                alert('Geolokasi tidak didukung browser ini.');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    setMarker(pos.coords.latitude, pos.coords.longitude);
                    map.setView([pos.coords.latitude, pos.coords.longitude], 16);
                },
                function() {
                    alert('Gagal mendapatkan lokasi. Pastikan izin lokasi diaktifkan.');
                }
            );
        }

        document.getElementById('radiusInput').addEventListener('change', function() {
            const lat = document.getElementById('latInput').value;
            const lng = document.getElementById('lngInput').value;
            if (lat && lng) setMarker(parseFloat(lat), parseFloat(lng));
        });

        // ══════════════════════════════════════════════════════════════════════
        // WILAYAH — proxy Laravel (hindari CORS), sama seperti halaman tambah,
        // TAPI di sini loadWilayah() mengembalikan Promise supaya bisa di-await
        // untuk mem-prefill provinsi/kabupaten/kecamatan/kelurahan yang sudah tersimpan.
        // ══════════════════════════════════════════════════════════════════════
        var BASE_WILAYAH = '{{ url('admin/pkl/lokasi/api/wilayah') }}';

        function loadWilayah(tipe, id, selEl, placeholder) {
            selEl.innerHTML = '<option value="">Memuat...</option>';
            selEl.disabled = true;
            var url = id ? (BASE_WILAYAH + '/' + tipe + '/' + id) : (BASE_WILAYAH + '/' + tipe);

            return fetch(url)
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function(data) {
                    selEl.innerHTML = '<option value="">' + placeholder + '</option>';
                    (data || []).forEach(function(d) {
                        var opt = document.createElement('option');
                        opt.value = d.name;
                        opt.dataset.id = d.id;
                        opt.textContent = d.name;
                        selEl.appendChild(opt);
                    });
                    selEl.disabled = false;
                })
                .catch(function(err) {
                    selEl.innerHTML = '<option value="">Gagal memuat</option>';
                    selEl.disabled = false;
                });
        }

        function selectByName(selectEl, name) {
            if (!name) return false;
            var target = String(name).trim().toLowerCase();
            var found = false;
            Array.prototype.forEach.call(selectEl.options, function(opt) {
                if (opt.value.trim().toLowerCase() === target) {
                    selectEl.value = opt.value;
                    found = true;
                }
            });
            return found;
        }

        var selProv = document.getElementById('selProvinsi');
        var selKab = document.getElementById('selKabupaten');
        var selKec = document.getElementById('selKecamatan');
        var selKel = document.getElementById('selKelurahan');

        // Nilai wilayah yang sudah tersimpan di database untuk lokasi ini
        var savedWilayah = {
            provinsi: @json(old('provinsi', $lokasiPkl->provinsi)),
            kabupaten: @json(old('kabupaten', $lokasiPkl->kabupaten)),
            kecamatan: @json(old('kecamatan', $lokasiPkl->kecamatan)),
            kelurahan: @json(old('kelurahan', $lokasiPkl->kelurahan)),
        };

        // Muat provinsi, lalu jika ada data tersimpan, otomatis pilih & lanjutkan
        // kaskade kabupaten → kecamatan → kelurahan sesuai data lama.
        async function initWilayah() {
            var hint = document.getElementById('wilayahRestoreHint');

            await loadWilayah('provinces', null, selProv, '— Pilih Provinsi —');
            if (!savedWilayah.provinsi) return;

            if (!selectByName(selProv, savedWilayah.provinsi)) {
                if (hint) hint.textContent = 'Provinsi tersimpan ("' + savedWilayah.provinsi +
                    '") tidak ditemukan di data wilayah API — silakan pilih ulang.';
                return;
            }
            var provId = selProv.selectedOptions[0] && selProv.selectedOptions[0].dataset.id;
            if (!provId) return;

            await loadWilayah('regencies', provId, selKab, '— Pilih Kab/Kota —');
            if (!savedWilayah.kabupaten || !selectByName(selKab, savedWilayah.kabupaten)) return;
            var kabId = selKab.selectedOptions[0] && selKab.selectedOptions[0].dataset.id;
            if (!kabId) return;

            await loadWilayah('districts', kabId, selKec, '— Pilih Kecamatan —');
            if (!savedWilayah.kecamatan || !selectByName(selKec, savedWilayah.kecamatan)) return;
            var kecId = selKec.selectedOptions[0] && selKec.selectedOptions[0].dataset.id;
            if (!kecId) return;

            await loadWilayah('villages', kecId, selKel, '— Pilih Kelurahan —');
            if (savedWilayah.kelurahan) selectByName(selKel, savedWilayah.kelurahan);
        }

        selProv.addEventListener('change', function() {
            var id = this.options[this.selectedIndex]?.dataset?.id;
            selKec.innerHTML = '<option value="">— Pilih Kecamatan —</option>';
            selKec.disabled = true;
            selKel.innerHTML = '<option value="">— Pilih Kelurahan —</option>';
            selKel.disabled = true;
            if (id) loadWilayah('regencies', id, selKab, '— Pilih Kab/Kota —');
            else {
                selKab.innerHTML = '<option value="">— Pilih Kab/Kota —</option>';
                selKab.disabled = true;
            }
        });

        selKab.addEventListener('change', function() {
            var id = this.options[this.selectedIndex]?.dataset?.id;
            selKel.innerHTML = '<option value="">— Pilih Kelurahan —</option>';
            selKel.disabled = true;
            if (id) loadWilayah('districts', id, selKec, '— Pilih Kecamatan —');
            else {
                selKec.innerHTML = '<option value="">— Pilih Kecamatan —</option>';
                selKec.disabled = true;
            }
        });

        selKec.addEventListener('change', function() {
            var id = this.options[this.selectedIndex]?.dataset?.id;
            if (id) loadWilayah('villages', id, selKel, '— Pilih Kelurahan —');
            else {
                selKel.innerHTML = '<option value="">— Pilih Kelurahan —</option>';
                selKel.disabled = true;
            }
        });

        // ══════════════════════════════════════════════════════════════════════
        // KODE POS OTOMATIS — via Nominatim reverse-geocode saat pilih kelurahan
        // ══════════════════════════════════════════════════════════════════════
        selKel.addEventListener('change', function() {
            var kelurahan = this.value;
            if (!kelurahan) return;

            var kecamatan = selKec.value;
            var kabupaten = selKab.value;
            var provinsi = selProv.value;

            var query = [kelurahan, kecamatan, kabupaten, provinsi, 'Indonesia']
                .filter(Boolean).join(', ');

            var loading = document.getElementById('kodePosLoading');
            if (loading) loading.style.display = 'inline';

            fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query), {
                    headers: {
                        'Accept-Language': 'id'
                    }
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(data) {
                    if (loading) loading.style.display = 'none';
                    if (!data || !data.length) return;

                    var result = data[0];
                    return fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + result.lat +
                        '&lon=' + result.lon, {
                            headers: {
                                'Accept-Language': 'id'
                            }
                        });
                })
                .then(function(r) {
                    return r ? r.json() : null;
                })
                .then(function(detail) {
                    if (!detail) return;

                    var posCode = detail?.address?.postcode;
                    var inpKodePos = document.getElementById('inpKodePos');
                    if (posCode && inpKodePos && !inpKodePos.value) {
                        inpKodePos.value = posCode;
                    }
                })
                .catch(function() {
                    if (loading) loading.style.display = 'none';
                });
        });

        // ══════════════════════════════════════════════════════════════════════
        // TOGGLE PASAL
        // ══════════════════════════════════════════════════════════════════════
        window.togglePasal = function(jenis, show) {
            var el = document.getElementById('pasal' + jenis.charAt(0).toUpperCase() + jenis.slice(1) + 'Div');
            if (el) el.style.display = show ? 'block' : 'none';
        };

        // ══════════════════════════════════════════════════════════════════════
        // PASAL AUTOCOMPLETE
        // ══════════════════════════════════════════════════════════════════════
        (function() {
            var PELANGGARAN = [],
                PENGHARGAAN = [];

            function esc(s) {
                return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,
                    '&quot;');
            }

            function buildAC(opts) {
                var DATA = opts.data,
                    searchEl = document.getElementById(opts.searchId),
                    hiddenEl = document.getElementById(opts.hiddenId),
                    dropEl = document.getElementById(opts.dropdownId),
                    badgeEl = document.getElementById(opts.badgeId),
                    badgeText = document.getElementById(opts.badgeTextId);
                if (!searchEl || !dropEl) return;

                function pos() {
                    var r = searchEl.getBoundingClientRect();
                    dropEl.style.top = (r.bottom + 4) + 'px';
                    dropEl.style.left = r.left + 'px';
                    dropEl.style.width = r.width + 'px';
                }

                function render(items) {
                    dropEl.innerHTML = '';
                    items.slice(0, 50).forEach(function(p) {
                        var d = document.createElement('div');
                        d.style.cssText = 'padding:10px 14px;cursor:pointer;border-bottom:1px solid #f1f5f9;';
                        d.innerHTML =
                            '<span style="font-size:.82rem;font-weight:600;color:#0f172a;display:block;">' +
                            esc(p.label) + '</span><span style="font-size:.71rem;color:#64748b;">Poin: ' + p
                            .poin + '</span>';
                        d.addEventListener('mouseover', function() {
                            this.style.background = '#f8fafc';
                        });
                        d.addEventListener('mouseout', function() {
                            this.style.background = '';
                        });
                        d.addEventListener('mousedown', function(e) {
                            e.preventDefault();
                            selectItem(p);
                        });
                        dropEl.appendChild(d);
                    });
                    if (!items.length) dropEl.innerHTML =
                        '<div style="padding:12px 14px;color:#94a3b8;font-size:.82rem;">Tidak ditemukan</div>';
                    pos();
                    dropEl.style.display = 'block';
                }

                function selectItem(p) {
                    hiddenEl.value = p.id;
                    searchEl.value = p.label;
                    badgeText.textContent = p.label + ' — Poin: ' + p.poin;
                    badgeEl.style.display = 'flex';
                    dropEl.style.display = 'none';
                }
                var savedId = hiddenEl ? hiddenEl.value : '';
                if (savedId) {
                    var found = DATA.find(function(p) {
                        return String(p.id) === String(savedId);
                    });
                    if (found) selectItem(found);
                }
                searchEl.addEventListener('input', function() {
                    var q = this.value.toLowerCase();
                    render(q ? DATA.filter(function(p) {
                        return p.label.toLowerCase().includes(q) || p.isi.toLowerCase().includes(q);
                    }) : DATA);
                });
                searchEl.addEventListener('focus', function() {
                    var q = this.value.toLowerCase();
                    render(q ? DATA.filter(function(p) {
                        return p.label.toLowerCase().includes(q) || p.isi.toLowerCase().includes(q);
                    }) : DATA);
                });
                document.addEventListener('click', function(e) {
                    if (!searchEl.contains(e.target) && !dropEl.contains(e.target)) dropEl.style.display =
                        'none';
                });
                window.addEventListener('scroll', function() {
                    if (dropEl.style.display !== 'none') pos();
                }, true);
                window.addEventListener('resize', function() {
                    if (dropEl.style.display !== 'none') pos();
                });
            }
            window.clearPklPasal = function(jenis) {
                document.getElementById('pkl_pasal_' + jenis + '_hidden').value = '';
                document.getElementById('pkl_pasal_' + jenis + '_search').value = '';
                document.getElementById('pkl_pasal_' + jenis + '_badge').style.display = 'none';
            };
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    PELANGGARAN = JSON.parse(document.getElementById('pkl-pasal-pelanggaran-data')
                        ?.textContent || '[]');
                } catch (e) {}
                try {
                    PENGHARGAAN = JSON.parse(document.getElementById('pkl-pasal-penghargaan-data')
                        ?.textContent || '[]');
                } catch (e) {}
                buildAC({
                    data: PENGHARGAAN,
                    searchId: 'pkl_pasal_hadir_search',
                    hiddenId: 'pkl_pasal_hadir_hidden',
                    dropdownId: 'pkl_pasal_hadir_dropdown',
                    badgeId: 'pkl_pasal_hadir_badge',
                    badgeTextId: 'pkl_pasal_hadir_badge_text'
                });
                buildAC({
                    data: PELANGGARAN,
                    searchId: 'pkl_pasal_terlambat_search',
                    hiddenId: 'pkl_pasal_terlambat_hidden',
                    dropdownId: 'pkl_pasal_terlambat_dropdown',
                    badgeId: 'pkl_pasal_terlambat_badge',
                    badgeTextId: 'pkl_pasal_terlambat_badge_text'
                });
                buildAC({
                    data: PELANGGARAN,
                    searchId: 'pkl_pasal_alfa_search',
                    hiddenId: 'pkl_pasal_alfa_hidden',
                    dropdownId: 'pkl_pasal_alfa_dropdown',
                    badgeId: 'pkl_pasal_alfa_badge',
                    badgeTextId: 'pkl_pasal_alfa_badge_text'
                });
            });
        })();

        // ══════════════════════════════════════════════════════════════════════
        // INIT
        // ══════════════════════════════════════════════════════════════════════
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            initWilayah();
        });
    </script>
@endpush
