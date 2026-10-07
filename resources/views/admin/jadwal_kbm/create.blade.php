@extends('layouts.app')

@section('title', 'Tambah Jadwal KBM')

@push('styles')
    <style>
        .jkc {
            font-family: inherit;
        }


        /* Strip */
        .jkc .jkc-strip {
            padding: 20px 20px 28px;
            background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #10b981 100%);
            position: relative;
            overflow: hidden;
        }

        .jkc .jkc-strip::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .jkc .jkc-strip::after {
            content: '';
            position: absolute;
            bottom: -24px;
            left: -20px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, .04);
            border-radius: 50%;
        }

        .jkc .jkc-live {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 600;
            color: rgba(255, 255, 255, .9);
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .jkc .jkc-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #6ee7b7;
            display: inline-block;
            animation: jkc-pulse 2s infinite;
        }

        @keyframes jkc-pulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .4
            }
        }

        .jkc .jkc-strip h2 {
            font-size: 1.3rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 4px;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .jkc .jkc-strip p {
            font-size: .8rem;
            color: rgba(255, 255, 255, .65);
            margin: 0;
            position: relative;
            z-index: 1;
        }

        /* Alerts */
        .jkc .jkc-err {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: .84rem;
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
            margin: 12px 16px 0;
        }

        .jkc .jkc-err ul {
            margin: 4px 0 0 16px;
            font-size: .8rem;
        }

        /* Cards */
        .jkc .jkc-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            margin: 12px 16px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
            overflow: hidden;
        }

        .jkc .jkc-chead {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px 11px;
            border-bottom: 1px solid #f8fafc;
        }

        .jkc .jkc-cico {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
        }

        .jkc .jkc-chead h3 {
            margin: 0;
            font-size: .9rem;
            font-weight: 700;
        }

        .jkc .jkc-cbody {
            padding: 16px;
        }

        /* Fields */
        .jkc .jkc-fg {
            margin-bottom: 14px;
        }

        .jkc .jkc-lbl {
            display: block;
            font-size: .8rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .jkc .jkc-lbl .req {
            color: #ef4444;
            margin-left: 2px;
        }

        .jkc .jkc-inp {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            box-sizing: border-box;
            -webkit-appearance: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        .jkc .jkc-inp:focus {
            border-color: #10b981;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, .1);
        }

        .jkc .jkc-inp.iserr {
            border-color: #ef4444;
        }

        .jkc .jkc-sel {
            width: 100%;
            padding: 10px 28px 10px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            box-sizing: border-box;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            transition: border-color .2s, box-shadow .2s;
        }

        .jkc .jkc-sel:focus {
            border-color: #10b981;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, .1);
        }

        .jkc .jkc-sel.iserr {
            border-color: #ef4444;
        }

        .jkc .jkc-ferr {
            font-size: .72rem;
            color: #dc2626;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .jkc .jkc-hint {
            font-size: .7rem;
            color: #94a3b8;
            margin-top: 4px;
            line-height: 1.5;
        }

        /* Grid helpers */
        .jkc .jkc-g2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        /* Hari radio cards */
        .jkc .jkc-hari-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 4px;
        }

        .jkc .jkc-hcard {
            position: relative;
            cursor: pointer;
        }

        .jkc .jkc-hcard input[type="radio"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
            margin: 0;
        }

        .jkc .jkc-hbox {
            position: relative;
            z-index: 1;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 6px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            font-size: .78rem;
            font-weight: 700;
            color: #475569;
            transition: all .18s;
            text-align: center;
        }

        .jkc .jkc-hcard input:checked~.jkc-hbox {
            border-color: #10b981;
            background: #ecfdf5;
            color: #065f46;
        }

        /* Semester radio */
        .jkc .jkc-sem-row {
            display: flex;
            gap: 8px;
        }

        .jkc .jkc-semcard {
            flex: 1;
            position: relative;
            cursor: pointer;
        }

        .jkc .jkc-semcard input[type="radio"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
            margin: 0;
        }

        .jkc .jkc-sembox {
            position: relative;
            z-index: 1;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 8px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            font-size: .82rem;
            font-weight: 700;
            color: #475569;
            transition: all .18s;
        }

        .jkc .jkc-semcard input:checked~.jkc-sembox {
            border-color: #10b981;
            background: #ecfdf5;
            color: #065f46;
        }

        /* Divider */
        .jkc .jkc-div {
            height: 1px;
            background: #f1f5f9;
            margin: 4px 0 16px;
        }

        /* Action bar */
        .jkc .jkc-bar {
            position: fixed;
            bottom: var(--footer-h, 60px);
            left: 0;
            right: 0;
            padding: 10px 16px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            z-index: 100;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
        }

        .jkc .jkc-ab {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 14px;
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

        .jkc .jkc-ab:active {
            transform: scale(.97);
        }

        .jkc .jkc-ab.back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            flex: 0 0 auto;
            padding: 12px 18px;
        }

        .jkc .jkc-ab.back:hover {
            background: #e2e8f0;
        }

        .jkc .jkc-ab.save {
            background: linear-gradient(135deg, #065f46, #10b981);
            color: #fff;
            box-shadow: 0 3px 12px rgba(16, 185, 129, .3);
        }

        .jkc .jkc-ab.save:hover {
            filter: brightness(1.08);
        }

        /* Mapel info box */
        .jkc .jkc-mapel-info {
            background: #f0fdf4;
            border: 1px solid #a7f3d0;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: .78rem;
            color: #065f46;
            margin-top: 8px;
            display: none;
            align-items: center;
            gap: 8px;
        }

        .jkc .jkc-mapel-info.show {
            display: flex;
        }

        /* ── Select2 override ── */
        .jkc .select2-container {
            width: 100% !important;
        }

        .jkc .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            padding: 0 12px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            transition: border-color .2s, box-shadow .2s;
        }

        .jkc .select2-container--default .select2-selection--single:focus,
        .jkc .select2-container--default.select2-container--focus .select2-selection--single,
        .jkc .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #10b981;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, .1);
            outline: none;
        }

        .jkc .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #0f172a;
            line-height: normal;
            padding: 0;
        }

        .jkc .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8;
        }

        .jkc .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 8px;
        }

        .jkc .select2-container--default .select2-selection--single .select2-selection__clear {
            margin-right: 8px;
            font-size: 1rem;
            color: #94a3b8;
        }

        .select2-dropdown {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
            font-size: .875rem;
            font-family: inherit;
            overflow: hidden;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: .875rem;
            font-family: inherit;
            outline: none;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, .1);
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: #ecfdf5;
            color: #065f46;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background: #d1fae5;
            color: #065f46;
        }

        .select2-results__option {
            padding: 8px 12px;
        }
    </style>
@endpush

@section('content')
    <div class="jkc" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">

        {{-- Strip --}}
        <div class="jkc-strip">
            <div class="jkc-live"><span class="jkc-dot"></span> Tambah Jadwal</div>
            <h2><i class="fas fa-calendar-plus"></i> Tambah Jadwal KBM</h2>
            <p>Isi data jadwal kegiatan belajar mengajar</p>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="jkc-err">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Silakan perbaiki:</strong>
                    <ul>
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="jkcForm" method="POST" action="{{ route('admin.jadwal-kbm.store') }}">
            @csrf

            {{-- ① Kelas & Guru --}}
            <div class="jkc-card">
                <div class="jkc-chead">
                    <div class="jkc-cico" style="background:#dcfce7;color:#15803d;">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3>Kelas &amp; Guru</h3>
                </div>
                <div class="jkc-cbody">

                    <div class="jkc-fg">
                        <label class="jkc-lbl" for="kelas_id">Kelas <span class="req">*</span></label>
                        <select id="kelas_id" name="kelas_id" class="jkc-sel @error('kelas_id') iserr @enderror" required>
                            <option value="">— Pilih Kelas —</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}{{ $k->jurusan ? ' — ' . $k->jurusan->nama_jurusan : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id')
                            <div class="jkc-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="jkc-fg">
                        <label class="jkc-lbl" for="gtk_id">Guru <span class="req">*</span></label>

                        {{-- Select2 Static — data dari $gtkList yang sudah di-pass controller --}}
                        <select id="gtk_id" name="gtk_id" class="jkc-sel @error('gtk_id') iserr @enderror" required>
                            <option value="">— Pilih Guru —</option>
                            @foreach ($gtkList as $g)
                                <option value="{{ $g->id }}" {{ old('gtk_id') == $g->id ? 'selected' : '' }}>
                                    {{ $g->nama_lengkap }}{{ $g->kd_guru ? ' (' . $g->kd_guru . ')' : '' }}
                                </option>
                            @endforeach
                        </select>

                        @error('gtk_id')
                            <div class="jkc-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                        <div class="jkc-hint">Ketik nama guru untuk mencari</div>
                    </div>

                </div>
            </div>

            {{-- ② Hari & Waktu --}}
            <div class="jkc-card">
                <div class="jkc-chead">
                    <div class="jkc-cico" style="background:#fef3c7;color:#b45309;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Hari &amp; Waktu</h3>
                </div>
                <div class="jkc-cbody">

                    {{-- Hari --}}
                    <div class="jkc-fg">
                        <label class="jkc-lbl">Hari <span class="req">*</span></label>
                        <div class="jkc-hari-grid">
                            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                                <label class="jkc-hcard">
                                    <input type="radio" name="hari" value="{{ $h }}"
                                        {{ old('hari') === $h ? 'checked' : '' }} required>
                                    <div class="jkc-hbox">{{ $h }}</div>
                                </label>
                            @endforeach
                        </div>
                        @error('hari')
                            <div class="jkc-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="jkc-div"></div>

                    {{-- Info kelompok jam (auto-show saat hari dipilih) --}}
                    <div id="jkcKelompokInfo" class="jkc-hint" style="margin-bottom:10px;display:none;">
                        <i class="fas fa-info-circle" style="color:#10b981;"></i>
                        <span id="jkcKelompokInfoTxt"></span>
                    </div>

                    {{-- Jam Pelajaran --}}
                    <div class="jkc-g2">
                        {{-- Mulai --}}
                        <div class="jkc-fg">
                            <label class="jkc-lbl" for="jam_pelajaran_mulai">Mulai Jam Ke <span
                                    class="req">*</span></label>
                            <select id="jam_pelajaran_mulai" name="jam_ke" class="jkc-sel @error('jam_ke') iserr @enderror"
                                required>
                                @include('admin.jadwal_kbm._jam_options', [
                                    'jamPelajaranGrouped' => $jamPelajaranGrouped,
                                    'selected' => old('jam_ke'),
                                    'placeholder' => '— Pilih Jam Mulai —',
                                ])
                            </select>
                            @error('jam_ke')
                                <div class="jkc-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Selesai --}}
                        <div class="jkc-fg">
                            <label class="jkc-lbl" for="jam_pelajaran_selesai">Sampai Jam Ke <span
                                    class="req">*</span></label>
                            <select id="jam_pelajaran_selesai" name="jam_ke_selesai"
                                class="jkc-sel @error('jam_ke_selesai') iserr @enderror" required>
                                @include('admin.jadwal_kbm._jam_options', [
                                    'jamPelajaranGrouped' => $jamPelajaranGrouped,
                                    'selected' => old('jam_ke_selesai', old('jam_ke')),
                                    'placeholder' => '— Pilih Jam Selesai —',
                                ])
                            </select>
                            @error('jam_ke_selesai')
                                <div class="jkc-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="jkc-hint" style="margin-top:-8px;margin-bottom:14px;">
                        Pilih hari terlebih dahulu — dropdown jam akan otomatis menyesuaikan (Reguler / Jumat).
                    </div>

                    {{-- Jam Real (readonly, auto-fill) --}}
                    <div class="jkc-g2">
                        <div class="jkc-fg">
                            <label class="jkc-lbl" for="jam_mulai">Jam Mulai <span class="req">*</span></label>
                            <input type="time" id="jam_mulai" name="jam_mulai"
                                class="jkc-inp @error('jam_mulai') iserr @enderror" value="{{ old('jam_mulai') }}"
                                required readonly>
                            @error('jam_mulai')
                                <div class="jkc-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                            <div class="jkc-hint">Otomatis terisi saat memilih jam pelajaran</div>
                        </div>

                        <div class="jkc-fg">
                            <label class="jkc-lbl" for="jam_selesai">Jam Selesai <span class="req">*</span></label>
                            <input type="time" id="jam_selesai" name="jam_selesai"
                                class="jkc-inp @error('jam_selesai') iserr @enderror" value="{{ old('jam_selesai') }}"
                                required readonly>
                            @error('jam_selesai')
                                <div class="jkc-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                            <div class="jkc-hint">Otomatis terisi saat memilih jam pelajaran</div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ③ Mata Pelajaran --}}
            <div class="jkc-card">
                <div class="jkc-chead">
                    <div class="jkc-cico" style="background:#dbeafe;color:#1d4ed8;">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3>Mata Pelajaran</h3>
                </div>
                <div class="jkc-cbody">

                    <div class="jkc-fg">
                        <label class="jkc-lbl" for="mata_pelajaran_id">Mata Pelajaran <span
                                class="req">*</span></label>
                        <select id="mata_pelajaran_id" name="mata_pelajaran_id"
                            class="jkc-sel @error('mata_pelajaran_id') iserr @enderror" onchange="jkcAutoMapel(this)"
                            required>
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach ($mataPelajaran as $mp)
                                <option value="{{ $mp->id }}" data-nama="{{ $mp->nama_mapel }}"
                                    {{ old('mata_pelajaran_id') == $mp->id ? 'selected' : '' }}>
                                    {{ $mp->kode_mapel }} — {{ $mp->nama_mapel }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" id="mata_pelajaran" name="mata_pelajaran"
                            value="{{ old('mata_pelajaran') }}">
                        @error('mata_pelajaran_id')
                            <div class="jkc-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                        @error('mata_pelajaran')
                            <div class="jkc-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                        <div class="jkc-hint">Terisi otomatis dari kompetensi guru. Pilihan ini tetap bisa diganti manual.
                        </div>
                    </div>

                    <div class="jkc-mapel-info" id="jkcMapelInfo">
                        <i class="fas fa-check-circle"></i>
                        <span id="jkcMapelInfoTxt"></span>
                    </div>

                </div>
            </div>

            {{-- ④ Tahun Ajaran & Semester --}}
            <div class="jkc-card">
                <div class="jkc-chead">
                    <div class="jkc-cico" style="background:#ede9fe;color:#7c3aed;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Tahun Ajaran &amp; Semester</h3>
                </div>
                <div class="jkc-cbody">

                    <div class="jkc-fg">
                        <label class="jkc-lbl" for="tahun_ajaran">Tahun Ajaran</label>
                        <input type="text" id="tahun_ajaran" name="tahun_ajaran"
                            class="jkc-inp @error('tahun_ajaran') iserr @enderror"
                            value="{{ old('tahun_ajaran', now()->year . '/' . (now()->year + 1)) }}"
                            placeholder="2025/2026" maxlength="10">
                        @error('tahun_ajaran')
                            <div class="jkc-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="jkc-fg">
                        <label class="jkc-lbl">Semester <span class="req">*</span></label>
                        <div class="jkc-sem-row">
                            <label class="jkc-semcard">
                                <input type="radio" name="semester" value="1"
                                    {{ old('semester', '1') == '1' ? 'checked' : '' }} required>
                                <div class="jkc-sembox"><i class="fas fa-1"></i> Semester 1</div>
                            </label>
                            <label class="jkc-semcard">
                                <input type="radio" name="semester" value="2"
                                    {{ old('semester') == '2' ? 'checked' : '' }}>
                                <div class="jkc-sembox"><i class="fas fa-2"></i> Semester 2</div>
                            </label>
                        </div>
                        @error('semester')
                            <div class="jkc-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

        </form>

        {{-- Action Bar --}}
        <div class="jkc-bar">
            <a href="{{ route('admin.jadwal-kbm.index') }}" class="jkc-ab back">
                <i class="fas fa-times"></i>
            </a>
            <button type="submit" form="jkcForm" class="jkc-ab save">
                <i class="fas fa-save"></i> Simpan Jadwal
            </button>
        </div>

    </div>
@endsection

@push('scripts')
    {{-- SweetAlert: validasi gagal --}}
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi gagal',
                    html: '<ul style="text-align:left;margin:0;padding-left:18px;">{{ implode('', array_map(fn($e) => '<li>' . e($e) . '</li>', $errors->all())) }}</ul>',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json(session('success')),
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: @json(session('error')),
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    <script>
        // ═══════════════════════════════════════════════════════════
        //  DATA dari Blade
        // ═══════════════════════════════════════════════════════════
        const jkcAllMataPelajaran = @json($mataPelajaranOptions);
        let jkcPreferredMapelId = @json((string) old('mata_pelajaran_id'));

        // ═══════════════════════════════════════════════════════════
        //  Select2 — Guru (static, search lokal)
        // ═══════════════════════════════════════════════════════════
        $(document).ready(function() {
            const $gtk = $('#gtk_id');

            if (!$gtk.length || typeof $gtk.select2 !== 'function') return;

            $gtk.select2({
                width: '100%',
                placeholder: '— Pilih Guru —',
                allowClear: true,
                language: {
                    noResults: function() { return 'Guru tidak ditemukan'; },
                    searching: function() { return 'Mencari...'; }
                }
            });

            // ── Event Select2 ──
            $gtk.on('select2:select', function() {
                loadMataPelajaranByGuru(this);
            });

            $gtk.on('select2:unselect', function() {
                resetMataPelajaranDropdown();
            });

            // ── Restore old value setelah validasi gagal ──
            @if (old('gtk_id'))
                loadMataPelajaranByGuru($gtk[0]);
            @endif
        });

        // ═══════════════════════════════════════════════════════════
        //  Helper: escape HTML
        // ═══════════════════════════════════════════════════════════
        function jkcEscapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function(ch) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                })[ch];
            });
        }

        // ═══════════════════════════════════════════════════════════
        //  Render ulang dropdown Mata Pelajaran
        // ═══════════════════════════════════════════════════════════
        function jkcRenderMapelOptions(items, selectedId, autoSelectFirst) {
            var mapelSelect = document.getElementById('mata_pelajaran_id');
            var list = Array.isArray(items) ? items : [];
            var ids = list.map(function(mp) {
                return String(mp.id);
            });
            var selected = ids.includes(String(selectedId || '')) ? String(selectedId) : '';

            if (!selected && autoSelectFirst && list.length) {
                selected = String(list[0].id);
            }

            var options = '<option value="">— Pilih Mata Pelajaran —</option>';
            list.forEach(function(mp) {
                var label = (mp.kode_mapel ? mp.kode_mapel + ' — ' : '') + mp.nama_mapel;
                options += '<option value="' + jkcEscapeHtml(mp.id) + '" data-nama="' + jkcEscapeHtml(mp
                    .nama_mapel) + '">' +
                    jkcEscapeHtml(label) + '</option>';
            });

            mapelSelect.innerHTML = options;
            mapelSelect.value = selected;
            jkcAutoMapel(mapelSelect);
        }

        // ═══════════════════════════════════════════════════════════
        //  Auto-fill hidden input mata_pelajaran (nama) & info box
        // ═══════════════════════════════════════════════════════════
        function jkcAutoMapel(sel) {
            var opt = sel.options[sel.selectedIndex];
            var nama = opt ? (opt.getAttribute('data-nama') || '') : '';
            var info = document.getElementById('jkcMapelInfo');
            var txt = document.getElementById('jkcMapelInfoTxt');
            var hidden = document.getElementById('mata_pelajaran');

            hidden.value = nama;

            if (nama) {
                txt.textContent = 'Mata pelajaran dipilih: ' + nama;
                info.classList.add('show');
            } else {
                info.classList.remove('show');
            }
        }

        // ═══════════════════════════════════════════════════════════
        //  Load Mata Pelajaran berdasarkan Guru yang dipilih
        // ═══════════════════════════════════════════════════════════
        function loadMataPelajaranByGuru(sel) {
            var gtkId = sel.value;
            if (!gtkId) {
                resetMataPelajaranDropdown();
                return;
            }

            var mapelSelect = document.getElementById('mata_pelajaran_id');
            var currentMapelId = mapelSelect.value || jkcPreferredMapelId;

            mapelSelect.disabled = true;
            mapelSelect.innerHTML = '<option value="">Loading...</option>';

            fetch('{{ route('admin.jadwal-kbm.api.mata-pelajaran-by-guru') }}?gtk_id=' + encodeURIComponent(gtkId), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(data) {
                    if (data.error) {
                        resetMataPelajaranDropdown();
                        return;
                    }

                    jkcRenderMapelOptions(data.mata_pelajaran, currentMapelId, true);
                    mapelSelect.disabled = false;
                    jkcPreferredMapelId = '';

                    // Tampilkan kompetensi guru di info box
                    var info = document.getElementById('jkcMapelInfo');
                    var txt = document.getElementById('jkcMapelInfoTxt');
                    var selectedNama = document.getElementById('mata_pelajaran').value;

                    if (data.kompetensi_guru) {
                        txt.textContent = selectedNama ?
                            'Auto dari guru: ' + selectedNama + ' | Kompetensi: ' + data.kompetensi_guru :
                            'Kompetensi guru: ' + data.kompetensi_guru;
                        info.classList.add('show');
                    }
                })
                .catch(function() {
                    resetMataPelajaranDropdown();
                });
        }

        // ═══════════════════════════════════════════════════════════
        //  Reset Mata Pelajaran ke daftar penuh
        // ═══════════════════════════════════════════════════════════
        function resetMataPelajaranDropdown() {
            jkcRenderMapelOptions(jkcAllMataPelajaran, '', false);
            document.getElementById('mata_pelajaran_id').disabled = false;
            document.getElementById('jkcMapelInfo').classList.remove('show');
        }

        // ═══════════════════════════════════════════════════════════
        //  Filter Jam berdasarkan Hari yang dipilih
        // ═══════════════════════════════════════════════════════════

        // Semua option jam disimpan saat DOM ready (clone dari DOM)
        var jkcAllJamOptions = null;

        function jkcGetKelompokByHari(hari) {
            if (hari === 'Jumat') return 'jumat';
            return 'reguler'; // tampilkan reguler + reguler_1112 untuk non-Jumat
        }

        function jkcFilterJamByHari(hari) {
            if (!jkcAllJamOptions) return;

            var kelompok = jkcGetKelompokByHari(hari);
            var selects  = [
                document.getElementById('jam_pelajaran_mulai'),
                document.getElementById('jam_pelajaran_selesai'),
            ];
            var info    = document.getElementById('jkcKelompokInfo');
            var infoTxt = document.getElementById('jkcKelompokInfoTxt');

            selects.forEach(function(sel, idx) {
                var current = sel.value;
                // Rebuild dari snapshot
                sel.innerHTML = '';

                var placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = idx === 0 ? '— Pilih Jam Mulai —' : '— Pilih Jam Selesai —';
                sel.appendChild(placeholder);

                jkcAllJamOptions.forEach(function(optgroup) {
                    var klp = optgroup.dataset.kelompok;
                    // Jumat: hanya tampilkan jumat
                    // Reguler: tampilkan reguler & reguler_1112
                    var show = (kelompok === 'jumat')
                        ? (klp === 'jumat')
                        : (klp === 'reguler' || klp === 'reguler_1112');

                    if (show) {
                        sel.appendChild(optgroup.cloneNode(true));
                    }
                });

                // Coba restore pilihan sebelumnya
                if (current) {
                    sel.value = current;
                    // Jika option tidak ada (beda kelompok), reset
                    if (!sel.value) {
                        document.getElementById('jam_mulai').value  = '';
                        document.getElementById('jam_selesai').value = '';
                    }
                }
            });

            // Tampilkan info kelompok
            if (hari && info && infoTxt) {
                var msg = (hari === 'Jumat')
                    ? '🕌 Menampilkan jadwal Jumat (jam lebih pendek + ekskul)'
                    : '📅 Menampilkan jadwal Reguler – Kelas 10 dan Kelas 11-12 (Senin–Kamis)';
                infoTxt.textContent = msg;
                info.style.display = 'flex';
            } else if (info) {
                info.style.display = 'none';
            }

            jkcSyncJamRange();
        }

        // ═══════════════════════════════════════════════════════════
        //  Sinkronisasi Jam Pelajaran → Jam Real
        // ═══════════════════════════════════════════════════════════
        function jkcSyncJamRange() {
            var mulaiSel = document.getElementById('jam_pelajaran_mulai');
            var selesaiSel = document.getElementById('jam_pelajaran_selesai');
            var mulaiOpt = mulaiSel.options[mulaiSel.selectedIndex];
            var mulaiTime = mulaiOpt ? (mulaiOpt.getAttribute('data-time-in') || '') : '';

            // Auto-set selesai = mulai jika belum dipilih
            if (mulaiSel.value && !selesaiSel.value) {
                selesaiSel.value = mulaiSel.value;
            }

            // Disable pilihan selesai yang lebih awal dari mulai
            Array.from(selesaiSel.options).forEach(function(opt) {
                if (!opt.value || !mulaiTime) {
                    opt.disabled = false;
                    return;
                }
                opt.disabled = (opt.getAttribute('data-time-out') || '') <= mulaiTime;
            });

            // Jika pilihan selesai saat ini jadi disabled, reset ke mulai
            if (selesaiSel.selectedOptions[0] && selesaiSel.selectedOptions[0].disabled) {
                selesaiSel.value = mulaiSel.value;
            }

            // Isi input jam real
            var selesaiOpt = selesaiSel.options[selesaiSel.selectedIndex];
            document.getElementById('jam_mulai').value = mulaiOpt ? (mulaiOpt.getAttribute('data-mulai') || '') : '';
            document.getElementById('jam_selesai').value = selesaiOpt ? (selesaiOpt.getAttribute('data-selesai') || '') :
            '';
        }

        // ═══════════════════════════════════════════════════════════
        //  Init saat DOM siap
        // ═══════════════════════════════════════════════════════════
        document.addEventListener('DOMContentLoaded', function() {
            // Snapshot semua optgroup jam dari DOM (sebelum filter)
            var refSel = document.getElementById('jam_pelajaran_mulai');
            jkcAllJamOptions = Array.from(refSel.querySelectorAll('optgroup')).map(function(og) {
                var clone = og.cloneNode(true);
                // Simpan kelompok di dataset optgroup untuk lookup
                clone.dataset.kelompok = og.label.includes('Jumat') ? 'jumat'
                    : og.label.includes('11') ? 'reguler_1112' : 'reguler';
                return clone;
            });

            // Jam range
            document.getElementById('jam_pelajaran_mulai').addEventListener('change', jkcSyncJamRange);
            document.getElementById('jam_pelajaran_selesai').addEventListener('change', jkcSyncJamRange);

            // Filter jam saat hari berubah
            document.querySelectorAll('input[name="hari"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    jkcFilterJamByHari(this.value);
                });
            });

            // Terapkan filter sesuai hari old/default
            var checkedHari = document.querySelector('input[name="hari"]:checked');
            if (checkedHari) {
                jkcFilterJamByHari(checkedHari.value);
            }

            // Mata pelajaran: restore old value jika ada
            if (jkcPreferredMapelId) {
                jkcRenderMapelOptions(jkcAllMataPelajaran, jkcPreferredMapelId, false);
            } else {
                var mapelSelect = document.getElementById('mata_pelajaran_id');
                if (mapelSelect && mapelSelect.value) jkcAutoMapel(mapelSelect);
            }

            // Sync jam jika ada old value
            jkcSyncJamRange();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header-auto-show');
            if (header) {
                header.classList.add('header-active');
            }
        });
    </script>
@endpush
