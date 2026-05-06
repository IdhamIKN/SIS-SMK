@extends('layouts.app')

@section('title', 'Edit GTK')

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
        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
        }

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

        /* permissions grid */
        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .perm-card {
            border: 1.5px solid var(--border, #e2e8f0);
            border-radius: 10px;
            padding: 12px;
            background: #f8fafc;
        }

        .perm-title {
            font-size: .8rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
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
            <h2><i class="fas fa-user-edit"></i> Edit GTK</h2>
            <p>{{ $gtk->nama_lengkap }} <span class="text-muted">• {{ $gtk->kd_guru }}</span></p>
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

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert a-ok">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <form id="editGtkForm" method="POST" action="{{ route('gtk.update', $gtk) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- ── ① Identitas GTK ── --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#ede9fe; color:#7c3aed;"><i class="fas fa-id-card"></i></div>
                    <h3>Identitas GTK</h3>
                </div>
                <div class="c-body" style="padding:16px;">

                    {{-- Kode Guru --}}
                    <div class="fgroup">
                        <label class="form-label" for="kd_guru">Kode Guru <span class="req">*</span></label>
                        <input type="text" id="kd_guru" name="kd_guru"
                            class="form-input @error('kd_guru') is-error @enderror" value="{{ old('kd_guru', $gtk->kd_guru) }}"
                            placeholder="Kode unik guru" maxlength="10" required>
                        @error('kd_guru')
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NIP + NIK + NUPTK --}}
                    <div class="grid-3">
                        <div class="fgroup">
                            <label class="form-label" for="nip">NIP</label>
                            <input type="text" id="nip" name="nip"
                                class="form-input @error('nip') is-error @enderror" value="{{ old('nip', $gtk->nip) }}"
                                placeholder="Nomor Induk Pegawai" maxlength="20">
                            <div class="form-hint">Opsional</div>
                            @error('nip')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="fgroup">
                            <label class="form-label" for="nik">NIK</label>
                            <input type="text" id="nik" name="nik"
                                class="form-input @error('nik') is-error @enderror" value="{{ old('nik', $gtk->nik) }}"
                                placeholder="Nomor Induk Kependudukan" maxlength="20">
                            <div class="form-hint">Opsional, untuk login</div>
                            @error('nik')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="fgroup">
                            <label class="form-label" for="nuptk">NUPTK</label>
                            <input type="text" id="nuptk" name="nuptk"
                                class="form-input @error('nuptk') is-error @enderror" value="{{ old('nuptk', $gtk->nuptk) }}"
                                placeholder="Nomor Unik Pendidik" maxlength="20">
                            <div class="form-hint">Opsional</div>
                            @error('nuptk')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Nama Lengkap --}}
                    <div class="fgroup">
                        <label class="form-label" for="nama_lengkap">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap"
                            class="form-input @error('nama_lengkap') is-error @enderror" value="{{ old('nama_lengkap', $gtk->nama_lengkap) }}"
                            placeholder="Nama lengkap GTK" maxlength="255" required>
                        @error('nama_lengkap')
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div class="fgroup">
                        <label class="form-label">Jenis Kelamin <span class="req">*</span></label>
                        <div class="radio-group">
                            <label class="radio-card">
                                <input type="radio" name="jenis_kelamin" value="L"
                                    {{ old('jenis_kelamin', $gtk->jenis_kelamin) === 'L' ? 'checked' : '' }} required>
                                <div class="radio-box"><i class="fas fa-mars"></i> L</div>
                            </label>
                            <label class="radio-card fem">
                                <input type="radio" name="jenis_kelamin" value="P"
                                    {{ old('jenis_kelamin', $gtk->jenis_kelamin) === 'P' ? 'checked' : '' }}>
                                <div class="radio-box"><i class="fas fa-venus"></i> P</div>
                            </label>
                        </div>
                        @error('jenis_kelamin')
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status Aktif --}}
                    <div class="fgroup">
                        <label class="form-label">Status</label>
                        <div class="toggle-wrap">
                            <label class="toggle-switch">
                                <input type="checkbox" name="status_aktif" value="1"
                                    {{ old('status_aktif', $gtk->status_aktif) ? 'checked' : '' }} id="statusAktif">
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label" id="statusLabel">{{ old('status_aktif', $gtk->status_aktif) ? 'GTK Aktif' : 'Non Aktif' }}</span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── ② Kontak ── --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#dcfce7; color:#15803d;"><i class="fas fa-phone-alt"></i></div>
                    <h3>Kontak</h3>
                </div>
                <div class="c-body" style="padding:16px;">
                    <div class="fgroup">
                        <label class="form-label" for="no_hp">No. HP</label>
                        <input type="tel" id="no_hp" name="no_hp"
                            class="form-input @error('no_hp') is-error @enderror" value="{{ old('no_hp', $gtk->no_hp) }}"
                            placeholder="081234567890" maxlength="20">
                        @error('no_hp')
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ── ③ Mata Pelajaran & Jabatan ── --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#fef3c7; color:#b45309;"><i class="fas fa-book"></i></div>
                    <h3>Mata Pelajaran & Jabatan</h3>
                </div>
                <div class="c-body" style="padding:16px;">
                    <div class="grid-2">
                        <div class="fgroup">
                            <label class="form-label" for="mata_pelajaran">Mata Pelajaran</label>
                            <input type="text" id="mata_pelajaran" name="mata_pelajaran"
                                class="form-input @error('mata_pelajaran') is-error @enderror"
                                value="{{ old('mata_pelajaran', $gtk->mata_pelajaran) }}" placeholder="Contoh: Matematika, Bahasa Indonesia" maxlength="255">
                            <div class="form-hint">Opsional</div>
                            @error('mata_pelajaran')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="fgroup">
                            <label class="form-label" for="jabatan">Jabatan <span class="req">*</span></label>
                            <input type="text" id="jabatan" name="jabatan"
                                class="form-input @error('jabatan') is-error @enderror" value="{{ old('jabatan', $gtk->jabatan) }}"
                                placeholder="Contoh: Guru Kelas, Kepala Sekolah" maxlength="255" required>
                            @error('jabatan')
                                <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── ④ Akses & Permissions ── --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#dbeafe; color:#1d4ed8;"><i class="fas fa-shield-alt"></i></div>
                    <h3>Akses & Permissions</h3>
                </div>
                <div class="c-body" style="padding:16px;">

                    {{-- View Siswa --}}
                    <div class="fgroup">
                        <label class="form-label">Akses Data Siswa <span class="req">*</span></label>
                        <div class="radio-group">
                            <label class="radio-card">
                                <input type="radio" name="view_siswa" value="limit"
                                    {{ old('view_siswa', $gtk->view_siswa) === 'limit' ? 'checked' : '' }} required>
                                <div class="radio-box"><i class="fas fa-eye-slash"></i> Terbatas</div>
                            </label>
                            <label class="radio-card">
                                <input type="radio" name="view_siswa" value="full"
                                    {{ old('view_siswa', $gtk->view_siswa) === 'full' ? 'checked' : '' }}>
                                <div class="radio-box"><i class="fas fa-eye"></i> Lengkap</div>
                            </label>
                        </div>
                        @error('view_siswa')
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Permissions --}}
                    <div class="section-head">
                        <div class="sh-icon" style="background:#e0e7ff; color:#3730a3;"><i class="fas fa-key"></i></div>
                        <h4>Hak Akses</h4>
                        <div class="section-line"></div>
                    </div>

                    <div class="permissions-grid">
                        <div class="perm-card">
                            <div class="perm-title">Absensi</div>
                            <div class="toggle-wrap">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="acc_absen" value="1"
                                        {{ old('acc_absen', $gtk->acc_absen) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Akses Absen</span>
                            </div>
                        </div>

                        <div class="perm-card">
                            <div class="perm-title">Kurikulum</div>
                            <div class="toggle-wrap">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="acc_kurikulum" value="1"
                                        {{ old('acc_kurikulum', $gtk->acc_kurikulum) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Akses Kurikulum</span>
                            </div>
                        </div>

                        <div class="perm-card">
                            <div class="perm-title">Jurnal</div>
                            <div class="toggle-wrap">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="acc_jurnal" value="1"
                                        {{ old('acc_jurnal', $gtk->acc_jurnal) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Akses Jurnal</span>
                            </div>
                        </div>

                        <div class="perm-card">
                            <div class="perm-title">BK</div>
                            <div class="toggle-wrap">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="acc_bk" value="1"
                                        {{ old('acc_bk', $gtk->acc_bk) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Akses BK</span>
                            </div>
                        </div>

                        <div class="perm-card">
                            <div class="perm-title">Guru Piket</div>
                            <div class="toggle-wrap">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="guru_piket" value="1"
                                        {{ old('guru_piket', $gtk->guru_piket) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Guru Piket</span>
                            </div>
                        </div>

                        <div class="perm-card">
                            <div class="perm-title">Profil</div>
                            <div class="toggle-wrap">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="acc_profil" value="1"
                                        {{ old('acc_profil', $gtk->acc_profil) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Akses Profil</span>
                            </div>
                        </div>

                        <div class="perm-card">
                            <div class="perm-title">Group Access</div>
                            <div class="toggle-wrap">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="group_acc" value="1"
                                        {{ old('group_acc', $gtk->group_acc) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Group Access</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── ⑤ Foto ── --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#ccfbf1; color:#0f766e;"><i class="fas fa-camera"></i></div>
                    <h3>Foto GTK</h3>
                    <span class="hbadge" style="background:#f1f5f9; color:#64748b;">Opsional</span>
                </div>
                <div class="c-body" style="padding:16px;">
                    <label class="foto-upload" for="foto" id="fotoLabel">
                        @if($gtk->foto)
                            <img id="fotoPreview" src="{{ Storage::url($gtk->foto) }}" alt="Current photo" style="display: block;">
                        @else
                            <img id="fotoPreview" src="" alt="Preview">
                        @endif
                        <div class="fu-icon" id="fotoIcon" style="{{ $gtk->foto ? 'display: none;' : '' }}"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div class="fu-text" id="fotoText">{{ $gtk->foto ? 'Ubah foto' : 'Ketuk untuk pilih foto' }}</div>
                        <div class="fu-hint">JPG / PNG · Maks 2 MB</div>
                        <input type="file" id="foto" name="foto" accept="image/*"
                            onchange="previewFoto(this)">
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
        <a href="{{ route('gtk.show', $gtk) }}" class="ab-btn ab-btn-back">
            <i class="fas fa-times"></i> Batal
        </a>
        <button type="submit" form="editGtkForm" class="ab-btn ab-btn-primary">
            <i class="fas fa-save"></i> Simpan Perubahan
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
            document.getElementById('statusLabel').textContent = this.checked ? 'GTK Aktif' : 'Non Aktif';
        });
    </script>
@endpush