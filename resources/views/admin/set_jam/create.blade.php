@extends('layouts.app')

@section('title', 'Tambah Jam Pelajaran')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ── Form Card ── */
        .form-wrap {
            padding-bottom: calc(var(--footer-h, 0px) + 40px);
        }

        /* ── Section Card ── */
        .form-section {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
            border: 1px solid #f1f5f9;
            margin-bottom: 14px;
            overflow: hidden;
        }

        .form-section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px;
            border-bottom: 1px solid #f1f5f9;
            background: #fafbfc;
        }

        .form-section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            flex-shrink: 0;
        }

        .form-section-title {
            font-size: .82rem;
            font-weight: 700;
            color: #334155;
            letter-spacing: .2px;
        }

        .form-section-body {
            padding: 16px;
        }

        /* ── Field Group ── */
        .field-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .field-row:last-child {
            margin-bottom: 0;
        }

        .field-col {
            flex: 1;
            min-width: 140px;
        }

        .field-col.full {
            flex: 100%;
        }

        /* ── Label ── */
        .f-label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #64748b;
            margin-bottom: 6px;
        }

        .f-label .req {
            color: #f43f5e;
            font-size: .85rem;
            line-height: 1;
        }

        /* ── Inputs ── */
        .f-input,
        .f-select {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: .84rem;
            color: #1e293b;
            background: #f8fafc;
            font-family: inherit;
            transition: border-color .15s, box-shadow .15s, background .15s;
            box-sizing: border-box;
        }

        .f-input:focus,
        .f-select:focus {
            outline: none;
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px #eff6ff;
        }

        .f-input.is-error,
        .f-select.is-error {
            border-color: #f43f5e;
            background: #fff1f2;
        }

        .f-input.is-error:focus,
        .f-select.is-error:focus {
            box-shadow: 0 0 0 3px #ffe4e6;
        }

        /* time input icon fix */
        input[type="time"].f-input {
            cursor: pointer;
        }

        /* select chevron */
        .f-select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 11px center;
            padding-right: 32px;
            cursor: pointer;
        }

        /* number input spinner hide */
        input[type=number].f-input::-webkit-inner-spin-button,
        input[type=number].f-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
        }

        input[type=number].f-input {
            -moz-appearance: textfield;
        }

        /* ── Error msg ── */
        .f-error {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: .7rem;
            color: #f43f5e;
            margin-top: 4px;
        }

        /* ── Helper text ── */
        .f-hint {
            font-size: .68rem;
            color: #94a3b8;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        /* ── Toggle Switch ── */
        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
        }

        .toggle-info {
            flex: 1;
        }

        .toggle-title {
            font-size: .84rem;
            font-weight: 600;
            color: #1e293b;
        }

        .toggle-desc {
            font-size: .7rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 42px;
            height: 24px;
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
            background: #e2e8f0;
            border-radius: 24px;
            cursor: pointer;
            transition: background .2s;
        }

        .toggle-slider::before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            left: 3px;
            top: 3px;
            background: #fff;
            border-radius: 50%;
            transition: transform .2s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
        }

        .toggle-switch input:checked+.toggle-slider {
            background: #22c55e;
        }

        .toggle-switch input:checked+.toggle-slider::before {
            transform: translateX(18px);
        }

        /* ── Shift Preview ── */
        .shift-preview {
            display: none;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 600;
            margin-top: 8px;
            transition: all .2s;
        }

        .shift-preview.visible {
            display: flex;
        }

        .sp-pagi {
            background: #fef9c3;
            color: #a16207;
        }

        .sp-siang {
            background: #ffedd5;
            color: #c2410c;
        }

        .sp-sore {
            background: #ede9fe;
            color: #6d28d9;
        }

        .sp-malam {
            background: #1e293b;
            color: #94a3b8;
        }

        /* ── Time Arrow ── */
        .time-arrow {
            display: flex;
            align-items: flex-end;
            padding-bottom: 10px;
            color: #cbd5e1;
            font-size: .8rem;
            flex-shrink: 0;
        }

        /* ── Action Buttons ── */
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 6px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: .84rem;
            font-weight: 600;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            text-decoration: none;
            font-family: inherit;
            cursor: pointer;
            transition: all .15s;
        }

        .btn-back:hover {
            background: #f1f5f9;
            color: #334155;
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 22px;
            border-radius: 10px;
            font-size: .84rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: inherit;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            box-shadow: 0 2px 8px rgba(37, 99, 235, .3);
            transition: all .18s;
        }

        .btn-save:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            box-shadow: 0 4px 12px rgba(37, 99, 235, .4);
        }

        .btn-save:active {
            transform: scale(.97);
        }

        .btn-save:disabled {
            opacity: .65;
            cursor: not-allowed;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap form-wrap"
        style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">

        {{-- ── Page Strip ── --}}
        <div class="page-strip page-strip-event">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <h2>
                <i class="fas fa-plus-circle"></i>
                Tambah Jam Pelajaran
            </h2>
            <p>Isi detail jam masuk &amp; pulang untuk shift baru</p>
        </div>

        <form method="POST" action="{{ route('admin.set-jam.store') }}" id="formJam" novalidate>
            @csrf

            {{-- ── 1. Info Dasar ── --}}
            <div class="form-section">
                <div class="form-section-header">
                    <div class="form-section-icon" style="background:#eff6ff; color:#1d4ed8;">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <span class="form-section-title">Informasi Dasar</span>
                </div>
                <div class="form-section-body">

                    <div class="field-row">
                        {{-- ID Jam --}}
                        <div class="field-col">
                            <label for="id_jam" class="f-label">
                                <i class="fas fa-hashtag"></i> ID Jam <span class="req">*</span>
                            </label>
                            <input type="number" id="id_jam" name="id_jam" value="{{ old('id_jam') }}" min="1"
                                class="f-input {{ $errors->has('id_jam') ? 'is-error' : '' }}" placeholder="1" required>
                            @error('id_jam')
                                <div class="f-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                            <div class="f-hint"><i class="fas fa-info-circle"></i> Nomor urut jam (1, 2, 3, dst)</div>
                        </div>

                        {{-- Shift --}}
                        <div class="field-col">
                            <label for="shif" class="f-label">
                                <i class="fas fa-layer-group"></i> Shift <span class="req">*</span>
                            </label>
                            <select id="shif" name="shif"
                                class="f-select {{ $errors->has('shif') ? 'is-error' : '' }}" required>
                                <option value="">Pilih Shift</option>
                                @foreach (['Pagi', 'Siang', 'Sore', 'Malam'] as $s)
                                    <option value="{{ $s }}" {{ old('shif') == $s ? 'selected' : '' }}>
                                        {{ $s }}</option>
                                @endforeach
                            </select>
                            @error('shif')
                                <div class="f-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                            {{-- Shift Preview --}}
                            <div id="shiftPreview" class="shift-preview"></div>
                        </div>
                    </div>

                    {{-- Nama Jam --}}
                    <div class="field-row">
                        <div class="field-col full">
                            <label for="nama_jam" class="f-label">
                                <i class="fas fa-tag"></i> Nama Jam <span class="req">*</span>
                            </label>
                            <input type="text" id="nama_jam" name="nama_jam" value="{{ old('nama_jam') }}"
                                class="f-input {{ $errors->has('nama_jam') ? 'is-error' : '' }}"
                                placeholder="cth: Jam Ke-3, Istirahat 1, Ekskul 1 (Jumat) …" required>
                            @error('nama_jam')
                                <div class="f-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Kelompok Jam --}}
                    <div class="field-row">
                        <div class="field-col full">
                            <label for="kelompok_jam" class="f-label">
                                <i class="fas fa-sitemap"></i> Kelompok Jadwal <span class="req">*</span>
                            </label>
                            <select id="kelompok_jam" name="kelompok_jam"
                                class="f-select {{ $errors->has('kelompok_jam') ? 'is-error' : '' }}" required>
                                <option value="">Pilih Kelompok</option>
                                <option value="reguler"
                                    {{ old('kelompok_jam') == 'reguler' ? 'selected' : '' }}>
                                    Reguler – Kelas 10 (Senin–Kamis)
                                </option>
                                <option value="reguler_1112"
                                    {{ old('kelompok_jam') == 'reguler_1112' ? 'selected' : '' }}>
                                    Reguler – Kelas 11 &amp; 12 (Senin–Kamis)
                                </option>
                                <option value="jumat"
                                    {{ old('kelompok_jam') == 'jumat' ? 'selected' : '' }}>
                                    Jumat – Semua Kelas
                                </option>
                            </select>
                            @error('kelompok_jam')
                                <div class="f-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                            <div class="f-hint"><i class="fas fa-info-circle"></i>
                                Menentukan di hari apa jam ini muncul saat input jadwal KBM
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 2. Waktu Masuk ── --}}
            <div class="form-section">
                <div class="form-section-header">
                    <div class="form-section-icon" style="background:#f0fdf4; color:#15803d;">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <span class="form-section-title">Waktu Masuk</span>
                </div>
                <div class="form-section-body">
                    <div class="field-row">
                        <div class="field-col">
                            <label for="time_in" class="f-label">
                                <i class="fas fa-clock"></i> Jam Mulai <span class="req">*</span>
                            </label>
                            <input type="time" id="time_in" name="time_in" value="{{ old('time_in') }}"
                                class="f-input {{ $errors->has('time_in') ? 'is-error' : '' }}" required>
                            @error('time_in')
                                <div class="f-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field-col">
                            <label for="limit_in" class="f-label">
                                <i class="fas fa-hourglass-half"></i> Batas Keterlambatan
                            </label>
                            <input type="time" id="limit_in" name="limit_in" value="{{ old('limit_in') }}"
                                class="f-input {{ $errors->has('limit_in') ? 'is-error' : '' }}">
                            @error('limit_in')
                                <div class="f-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                            <div class="f-hint"><i class="fas fa-info-circle"></i> Opsional · toleransi terlambat</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 3. Waktu Pulang ── --}}
            <div class="form-section">
                <div class="form-section-header">
                    <div class="form-section-icon" style="background:#eff6ff; color:#1d4ed8;">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span class="form-section-title">Waktu Pulang</span>
                </div>
                <div class="form-section-body">
                    <div class="field-row">
                        <div class="field-col">
                            <label for="time_out" class="f-label">
                                <i class="fas fa-clock"></i> Jam Selesai <span class="req">*</span>
                            </label>
                            <input type="time" id="time_out" name="time_out" value="{{ old('time_out') }}"
                                class="f-input {{ $errors->has('time_out') ? 'is-error' : '' }}" required>
                            @error('time_out')
                                <div class="f-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field-col">
                            <label for="limit_out" class="f-label">
                                <i class="fas fa-hourglass-half"></i> Batas Pulang Awal
                            </label>
                            <input type="time" id="limit_out" name="limit_out" value="{{ old('limit_out') }}"
                                class="f-input {{ $errors->has('limit_out') ? 'is-error' : '' }}">
                            @error('limit_out')
                                <div class="f-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                            <div class="f-hint"><i class="fas fa-info-circle"></i> Opsional · toleransi pulang awal</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 4. Status ── --}}
            <div class="form-section">
                <div class="form-section-header">
                    <div class="form-section-icon" style="background:#f0fdf4; color:#15803d;">
                        <i class="fas fa-toggle-on"></i>
                    </div>
                    <span class="form-section-title">Status Jam</span>
                </div>
                <div class="form-section-body">
                    <div class="toggle-row">
                        <div class="toggle-info">
                            <div class="toggle-title">Aktifkan jam pelajaran ini</div>
                            <div class="toggle-desc">Jam yang aktif dapat digunakan dalam jadwal KBM</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="statusjam" name="statusjam" value="1"
                                {{ old('statusjam', true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- ── Actions ── --}}
            <div class="form-actions">
                <a href="{{ route('admin.set-jam.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn-save" id="btnSave">
                    <i class="fas fa-save"></i> Simpan Jam
                </button>
            </div>

        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const header = document.querySelector('.header-auto-show');
            if (header) {
                header.classList.add('header-active');
            }

            /* ── Shift Preview ── */
            const shifSelect = document.getElementById('shif');
            const preview = document.getElementById('shiftPreview');
            const shiftIcons = {
                Pagi: 'fa-sun',
                Siang: 'fa-cloud-sun',
                Sore: 'fa-cloud-moon',
                Malam: 'fa-moon'
            };
            const shiftClass = {
                Pagi: 'sp-pagi',
                Siang: 'sp-siang',
                Sore: 'sp-sore',
                Malam: 'sp-malam'
            };

            function updateShiftPreview() {
                const val = shifSelect.value;
                preview.className = 'shift-preview';
                if (!val) return;
                preview.classList.add('visible', shiftClass[val]);
                preview.innerHTML = `<i class="fas ${shiftIcons[val]}"></i> Shift ${val}`;
            }

            shifSelect.addEventListener('change', updateShiftPreview);
            updateShiftPreview(); // run on page load (old value)

            /* ── Submit guard ── */
            document.getElementById('formJam').addEventListener('submit', function() {
                const btn = document.getElementById('btnSave');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan…';
            });

            /* ── Session Alerts ── */
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            @endif

            /* ── Validation Error Alert ── */
            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Periksa Input',
                    html: `<ul style="text-align:left;padding-left:1.2rem;margin:0;font-size:.875rem;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>`,
                    confirmButtonColor: '#1d4ed8',
                    confirmButtonText: 'Oke, Saya Periksa',
                });
            @endif
        });
    </script>
@endpush
