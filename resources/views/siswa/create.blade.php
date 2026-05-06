@extends('layouts.app')

@section('title', 'Tambah Siswa Baru')

@push('styles')
    @include('components.izin-styles')
    <style>
        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 700;
            color: var(--text-main, #0f172a);
            margin-bottom: 5px;
        }

        .form-label .req {
            color: #ef4444;
            margin-left: 2px;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid var(--border, #e2e8f0);
            border-radius: 10px;
            font-size: .875rem;
            font-family: inherit;
            color: var(--text-main, #0f172a);
            background: #f8fafc;
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
            box-sizing: border-box;
            -webkit-appearance: none;
        }

        .form-input:focus {
            border-color: #7c3aed;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .1);
        }

        .form-input.is-error {
            border-color: #ef4444;
            background: #fff;
        }

        textarea.form-input {
            resize: vertical;
            min-height: 80px;
        }

        .form-error {
            font-size: .72rem;
            color: #dc2626;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .form-hint {
            font-size: .7rem;
            color: #94a3b8;
            margin-top: 4px;
        }

        /* grid helpers */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .grid-1 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        /* field group spacing */
        .fgroup {
            margin-bottom: 14px;
        }

        /* radio gender */
        .radio-group {
            display: flex;
            gap: 8px;
        }

        .radio-card {
            flex: 1;
            position: relative;
            cursor: pointer;
        }

        .radio-card input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
            margin: 0;
        }

        .radio-box {
            position: relative;
            z-index: 1;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 8px;
            border: 1.5px solid var(--border, #e2e8f0);
            border-radius: 10px;
            background: #f8fafc;
            font-size: .82rem;
            font-weight: 700;
            color: var(--text-main);
            transition: all .18s;
        }

        .radio-card input:checked~.radio-box {
            border-color: #7c3aed;
            background: #ede9fe;
            color: #7c3aed;
        }

        .radio-card.fem input:checked~.radio-box {
            border-color: #ec4899;
            background: #fdf2f8;
            color: #ec4899;
        }

        /* status toggle */
        .toggle-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toggle-switch {
            position: relative;
            width: 46px;
            height: 26px;
            flex-shrink: 0;
            cursor: pointer;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            inset: 0;
            border-radius: 13px;
            background: #e2e8f0;
            transition: background .2s;
        }

        .toggle-slider::before {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fff;
            top: 3px;
            left: 3px;
            transition: transform .2s;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .15);
        }

        .toggle-switch input:checked~.toggle-slider {
            background: #16a34a;
        }

        .toggle-switch input:checked~.toggle-slider::before {
            transform: translateX(20px);
        }

        .toggle-label {
            font-size: .85rem;
            color: var(--text-main);
            font-weight: 600;
        }

        /* foto upload */
        .foto-upload {
            border: 2px dashed var(--border, #e2e8f0);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            background: #f8fafc;
            transition: border-color .2s, background .2s;
            position: relative;
        }

        .foto-upload:hover {
            border-color: #7c3aed;
            background: #faf5ff;
        }

        .foto-upload input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .foto-upload .fu-icon {
            font-size: 1.8rem;
            color: #c4b5fd;
            margin-bottom: 6px;
        }

        .foto-upload .fu-text {
            font-size: .78rem;
            color: #7c3aed;
            font-weight: 700;
        }

        .foto-upload .fu-hint {
            font-size: .68rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        #fotoPreview {
            display: none;
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            margin: 0 auto 8px;
        }

        /* section divider */
        .section-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0 14px;
        }

        .section-head .sh-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
        }

        .section-head h4 {
            margin: 0;
            font-size: .9rem;
            font-weight: 800;
            color: var(--text-main);
        }

        .section-line {
            flex: 1;
            height: 1px;
            background: var(--border, #e2e8f0);
        }

        /* action bar */
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
            background: #7c3aed;
            color: #fff;
            box-shadow: 0 3px 12px rgba(124, 58, 237, .3);
        }

        .ab-btn-primary:hover {
            filter: brightness(1.08);
        }

        /* select arrow */
        select.form-input {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 32px;
            appearance: none;
            -webkit-appearance: none;
        }
    </style>
@endpush

@section('content')
    <div class="izin-wrap" style="padding-bottom: calc(var(--footer-h) + 88px);">

        {{-- Page Strip --}}
        <div class="page-strip page-strip-izin">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ now()->translatedFormat('d F Y') }}
            </div>
            <h2><i class="fas fa-user-plus"></i> Tambah Siswa Baru</h2>
            <p>Isi data siswa dengan lengkap dan benar</p>
        </div>

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="alert a-err">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Silakan perbaiki:</strong>
                    <ul style="margin:4px 0 0 16px;font-size:.8rem;">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="createSiswaForm" method="POST" action="{{ route('siswa.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- ── ① Identitas ── --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#ede9fe; color:#7c3aed;"><i class="fas fa-id-card"></i></div>
                    <h3>Identitas Siswa</h3>
                </div>
                <div class="c-body" style="padding:16px;">

                    {{-- NISN --}}
                    <div class="fgroup">
                        <label class="form-label" for="nisn">NISN <span class="req">*</span></label>
                        <input type="text" id="nisn" name="nisn"
                            class="form-input @error('nisn') is-error @enderror" value="{{ old('nisn') }}"
                            placeholder="Nomor Induk Siswa Nasional" maxlength="20" required>
                        @error('nisn')
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NIS + Nama --}}
                    <div class="grid-2">
                        <div class="fgroup">
                            <label class="form-label" for="nis">NIS</label>
                            <input type="text" id="nis" name="nis"
                                class="form-input @error('nis') is-error @enderror" value="{{ old('nis') }}"
                                placeholder="Nomor Induk Sekolah" maxlength="20">
                            <div class="form-hint">Opsional</div>
                            @error('nis')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="fgroup">
                            <label class="form-label" for="angkatan">Angkatan</label>
                            <input type="number" id="angkatan" name="angkatan"
                                class="form-input @error('angkatan') is-error @enderror"
                                value="{{ old('angkatan', date('Y')) }}" placeholder="{{ date('Y') }}" min="2000"
                                max="{{ date('Y') + 1 }}">
                            <div class="form-hint">Tahun masuk</div>
                            @error('angkatan')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Nama Lengkap --}}
                    <div class="fgroup">
                        <label class="form-label" for="nama_lengkap">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap"
                            class="form-input @error('nama_lengkap') is-error @enderror" value="{{ old('nama_lengkap') }}"
                            placeholder="Nama lengkap sesuai akta" maxlength="255" required>
                        @error('nama_lengkap')
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Jenis Kelamin + Kelas --}}
                    <div class="grid-2">
                        <div class="fgroup">
                            <label class="form-label">Jenis Kelamin <span class="req">*</span></label>
                            <div class="radio-group">
                                <label class="radio-card">
                                    <input type="radio" name="jenis_kelamin" value="L"
                                        {{ old('jenis_kelamin') === 'L' ? 'checked' : '' }} required>
                                    <div class="radio-box"><i class="fas fa-mars"></i> L</div>
                                </label>
                                <label class="radio-card fem">
                                    <input type="radio" name="jenis_kelamin" value="P"
                                        {{ old('jenis_kelamin') === 'P' ? 'checked' : '' }}>
                                    <div class="radio-box"><i class="fas fa-venus"></i> P</div>
                                </label>
                            </div>
                            @error('jenis_kelamin')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="fgroup">
                            <label class="form-label" for="kelas_id">Kelas <span class="req">*</span></label>
                            <select id="kelas_id" name="kelas_id"
                                class="form-input @error('kelas_id') is-error @enderror" required>
                                <option value="">— Pilih Kelas —</option>
                                @foreach ($kelas as $k)
                                    <option value="{{ $k->id }}"
                                        {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Status Aktif --}}
                    <div class="fgroup">
                        <label class="form-label">Status</label>
                        <div class="toggle-wrap">
                            <label class="toggle-switch">
                                <input type="checkbox" name="status_aktif" value="1"
                                    {{ old('status_aktif', true) ? 'checked' : '' }} id="statusAktif">
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label" id="statusLabel">Siswa Aktif</span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── ② Kelahiran ── --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#fef3c7; color:#b45309;"><i class="fas fa-birthday-cake"></i>
                    </div>
                    <h3>Data Kelahiran</h3>
                </div>
                <div class="c-body" style="padding:16px;">
                    <div class="grid-2">
                        <div class="fgroup">
                            <label class="form-label" for="tempat_lahir">Tempat Lahir</label>
                            <input type="text" id="tempat_lahir" name="tempat_lahir"
                                class="form-input @error('tempat_lahir') is-error @enderror"
                                value="{{ old('tempat_lahir') }}" placeholder="Kota kelahiran" maxlength="100">
                            @error('tempat_lahir')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="fgroup">
                            <label class="form-label" for="tanggal_lahir">Tanggal Lahir</label>
                            <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                                class="form-input @error('tanggal_lahir') is-error @enderror"
                                value="{{ old('tanggal_lahir') }}" max="{{ now()->subDay()->format('Y-m-d') }}">
                            @error('tanggal_lahir')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── ③ Kontak ── --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#dcfce7; color:#15803d;"><i class="fas fa-phone-alt"></i></div>
                    <h3>Kontak &amp; Orang Tua</h3>
                </div>
                <div class="c-body" style="padding:16px;">

                    <div class="fgroup">
                        <label class="form-label" for="no_hp_siswa">No. HP Siswa</label>
                        <input type="tel" id="no_hp_siswa" name="no_hp_siswa"
                            class="form-input @error('no_hp_siswa') is-error @enderror" value="{{ old('no_hp_siswa') }}"
                            placeholder="081234567890" maxlength="20">
                        @error('no_hp_siswa')
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid-2">
                        <div class="fgroup">
                            <label class="form-label" for="nama_ortu1">Nama Orang Tua 1</label>
                            <input type="text" id="nama_ortu1" name="nama_ortu1"
                                class="form-input @error('nama_ortu1') is-error @enderror"
                                value="{{ old('nama_ortu1') }}" placeholder="Ayah / Ibu" maxlength="100">
                            @error('nama_ortu1')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="fgroup">
                            <label class="form-label" for="no_hp_ortu1">No. HP Ortu 1</label>
                            <input type="tel" id="no_hp_ortu1" name="no_hp_ortu1"
                                class="form-input @error('no_hp_ortu1') is-error @enderror"
                                value="{{ old('no_hp_ortu1') }}" placeholder="081234567890" maxlength="20">
                            @error('no_hp_ortu1')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="fgroup">
                            <label class="form-label" for="nama_ortu2">Nama Orang Tua 2</label>
                            <input type="text" id="nama_ortu2" name="nama_ortu2"
                                class="form-input @error('nama_ortu2') is-error @enderror"
                                value="{{ old('nama_ortu2') }}" placeholder="Ayah / Ibu" maxlength="100">
                            @error('nama_ortu2')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="fgroup">
                            <label class="form-label" for="no_hp_ortu2">No. HP Ortu 2</label>
                            <input type="tel" id="no_hp_ortu2" name="no_hp_ortu2"
                                class="form-input @error('no_hp_ortu2') is-error @enderror"
                                value="{{ old('no_hp_ortu2') }}" placeholder="081234567890" maxlength="20">
                            @error('no_hp_ortu2')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="fgroup">
                        <label class="form-label" for="nama_wali">Nama Wali</label>
                        <input type="text" id="nama_wali" name="nama_wali"
                            class="form-input @error('nama_wali') is-error @enderror" value="{{ old('nama_wali') }}"
                            placeholder="Nama wali (jika ada)" maxlength="100">
                        <div class="form-hint">Kosongkan jika orang tua kandung</div>
                        @error('nama_wali')
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- ── ④ Alamat ── --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#dbeafe; color:#1d4ed8;"><i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Alamat</h3>
                </div>
                <div class="c-body" style="padding:16px;">
                    <div class="fgroup">
                        <label class="form-label" for="alamat">Alamat Lengkap</label>
                        <textarea id="alamat" name="alamat" rows="3" class="form-input @error('alamat') is-error @enderror"
                            placeholder="Jalan, RT/RW..." maxlength="500">{{ old('alamat') }}</textarea>
                        @error('alamat')
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid-2">
                        <div class="fgroup">
                            <label class="form-label" for="desa">Desa</label>
                            <input type="text" id="desa" name="desa"
                                class="form-input @error('desa') is-error @enderror" value="{{ old('desa') }}"
                                placeholder="Nama Desa" maxlength="100">
                            @error('desa')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="fgroup">
                            <label class="form-label" for="kelurahan">Kelurahan</label>
                            <input type="text" id="kelurahan" name="kelurahan"
                                class="form-input @error('kelurahan') is-error @enderror" value="{{ old('kelurahan') }}"
                                placeholder="Nama Kelurahan" maxlength="100">
                            @error('kelurahan')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="fgroup">
                            <label class="form-label" for="kecamatan">Kecamatan</label>
                            <input type="text" id="kecamatan" name="kecamatan"
                                class="form-input @error('kecamatan') is-error @enderror" value="{{ old('kecamatan') }}"
                                placeholder="Nama Kecamatan" maxlength="100">
                            @error('kecamatan')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="fgroup">
                            <label class="form-label" for="kabupaten">Kabupaten</label>
                            <input type="text" id="kabupaten" name="kabupaten"
                                class="form-input @error('kabupaten') is-error @enderror" value="{{ old('kabupaten') }}"
                                placeholder="Nama Kabupaten" maxlength="100">
                            @error('kabupaten')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="fgroup">
                            <label class="form-label" for="kode_pos">Kode Pos</label>
                            <input type="text" id="kode_pos" name="kode_pos"
                                class="form-input @error('kode_pos') is-error @enderror" value="{{ old('kode_pos') }}"
                                placeholder="12345" maxlength="10">
                            @error('kode_pos')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── ⑤ Foto ── --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#ccfbf1; color:#0f766e;"><i class="fas fa-camera"></i></div>
                    <h3>Foto Siswa</h3>
                    <span class="hbadge" style="background:#f1f5f9; color:#64748b;">Opsional</span>
                </div>
                <div class="c-body" style="padding:16px;">
                    <label class="foto-upload" for="foto" id="fotoLabel">
                        <input type="file" id="foto" name="foto" accept="image/*"
                            onchange="previewFoto(this)">
                        <img id="fotoPreview" src="" alt="Preview">
                        <div class="fu-icon" id="fotoIcon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div class="fu-text" id="fotoText">Ketuk untuk pilih foto</div>
                        <div class="fu-hint">JPG / PNG · Maks 2 MB</div>
                    </label>
                    @error('foto')
                        <div class="form-error mt-2"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

        </form>
    </div>

    {{-- Action Bar --}}
    <div class="action-bar">
        <a href="{{ route('siswa.index') }}" class="ab-btn ab-btn-back">
            <i class="fas fa-times"></i> Batal
        </a>
        <button type="submit" form="createSiswaForm" class="ab-btn ab-btn-primary">
            <i class="fas fa-user-plus"></i> Simpan Siswa
        </button>
    </div>
@endsection

@push('scripts')
    <script>
        // Preview foto
        function previewFoto(input) {
            const preview = document.getElementById('fotoPreview');
            const icon = document.getElementById('fotoIcon');
            const text = document.getElementById('fotoText');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    icon.style.display = 'none';
                    text.textContent = input.files[0].name;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Toggle label status aktif
        document.getElementById('statusAktif').addEventListener('change', function() {
            document.getElementById('statusLabel').textContent = this.checked ? 'Siswa Aktif' : 'Non Aktif';
        });
    </script>
@endpush
