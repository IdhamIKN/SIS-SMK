@extends('layouts.app')

@section('title', 'Edit Mata Pelajaran')

@push('styles')
    @include('components.event-styles')
    <style>
        .detail-wrap {
            padding-bottom: calc(var(--footer-h, 64px) + 80px);
        }

        .form-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .07);
            margin-bottom: 14px;
            overflow: hidden;
        }

        .form-card-head {
            padding: 14px 16px 12px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-card-head .fc-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
            flex-shrink: 0;
        }

        .form-card-head h3 {
            font-size: .92rem;
            font-weight: 700;
            color: var(--text-main, #1e293b);
            margin: 0;
        }

        .form-card-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .field label {
            font-size: .75rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .field label .req {
            color: #be123c;
            margin-left: 2px;
        }

        .field .hint {
            font-size: .72rem;
            color: var(--text-muted, #94a3b8);
            margin-top: 2px;
        }

        .f-input,
        .f-select,
        .f-textarea {
            padding: 10px 13px;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            font-size: .85rem;
            font-family: inherit;
            background: #f8fafc;
            color: var(--text-main, #1e293b);
            outline: none;
            transition: border-color .15s, background .15s;
            width: 100%;
            box-sizing: border-box;
        }

        .f-input:focus,
        .f-select:focus,
        .f-textarea:focus {
            border-color: #2563eb;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
        }

        .f-input.is-invalid,
        .f-select.is-invalid,
        .f-textarea.is-invalid {
            border-color: #be123c;
            box-shadow: 0 0 0 3px rgba(190, 18, 60, .08);
        }

        .f-textarea {
            resize: vertical;
            min-height: 88px;
        }

        .f-kode {
            font-family: 'Courier New', monospace;
            font-size: .9rem;
            font-weight: 700;
            letter-spacing: .05em;
        }

        .invalid-msg {
            font-size: .75rem;
            color: #be123c;
            font-weight: 600;
        }

        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 14px;
        }

        .toggle-info h4 {
            font-size: .85rem;
            font-weight: 700;
            color: var(--text-main, #1e293b);
            margin: 0 0 2px;
        }

        .toggle-info p {
            font-size: .72rem;
            color: var(--text-muted, #94a3b8);
            margin: 0;
        }

        .toggle-switch {
            position: relative;
            width: 44px;
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
            background: #cbd5e1;
            border-radius: 34px;
            cursor: pointer;
            transition: background .2s;
        }

        .toggle-slider:before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            left: 3px;
            top: 3px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
            transition: transform .2s;
        }

        .toggle-switch input:checked+.toggle-slider {
            background: #2563eb;
        }

        .toggle-switch input:checked+.toggle-slider:before {
            transform: translateX(20px);
        }

        .kategori-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .kategori-pill input[type="radio"] {
            display: none;
        }

        .kategori-pill label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: .8rem;
            font-weight: 600;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            cursor: pointer;
            transition: all .15s;
            text-transform: none;
            letter-spacing: 0;
        }

        .kategori-pill input[type="radio"]:checked+label.pill-umum {
            background: #ede9fe;
            color: #6d28d9;
            border-color: #c4b5fd;
        }

        .kategori-pill input[type="radio"]:checked+label.pill-jurusan {
            background: #dcfce7;
            color: #15803d;
            border-color: #86efac;
        }

        .kategori-pill input[type="radio"]:checked+label.pill-mulok {
            background: #fef3c7;
            color: #b45309;
            border-color: #fde68a;
        }

        /* Edit banner */
        .edit-banner {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fffbeb;
            border: 1.5px solid #fde68a;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 14px;
        }

        .edit-banner i {
            color: #b45309;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .edit-banner span {
            font-size: .82rem;
            color: #92400e;
            font-weight: 600;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 4px;
        }

        .fa-btn {
            flex: 1;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            text-decoration: none;
            transition: all .18s;
        }

        .fa-btn:active {
            transform: scale(.97);
        }

        .fa-btn-back {
            background: #f1f5f9;
            color: #475569;
            flex: 0 0 auto;
            padding: 12px 18px;
        }

        .fa-btn-back:hover {
            background: #e2e8f0;
        }

        .fa-btn-submit {
            background: #b45309;
            color: #fff;
        }

        .fa-btn-submit:hover {
            background: #92400e;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap detail-wrap"
        style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">

        {{-- Page Strip --}}
        <div class="page-strip page-strip-event">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <h2><i class="fas fa-pen"></i> Edit Mata Pelajaran</h2>
            <p>{{ $mataPelajaran->nama_mapel }}</p>
        </div>

        {{-- Edit banner --}}
        {{-- <div class="edit-banner">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Anda sedang mengedit data mata pelajaran. Perubahan akan langsung tersimpan.</span>
    </div> --}}

        <form method="POST" action="{{ route('admin.mata-pelajaran.update', $mataPelajaran) }}">
            @csrf
            @method('PUT')

            {{-- Identitas --}}
            <div class="form-card">
                <div class="form-card-head">
                    <div class="fc-icon" style="background:#ede9fe;">
                        <i class="fas fa-id-card" style="color:#6d28d9;"></i>
                    </div>
                    <h3>Identitas Mata Pelajaran</h3>
                </div>
                <div class="form-card-body">

                    <div class="field">
                        <label>Nama Mata Pelajaran <span class="req">*</span></label>
                        <input type="text" name="nama_mapel" id="nama_mapel"
                            class="f-input @error('nama_mapel') is-invalid @enderror"
                            value="{{ old('nama_mapel', $mataPelajaran->nama_mapel) }}" placeholder="cth. Bahasa Indonesia"
                            required>
                        @error('nama_mapel')
                            <span class="invalid-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label>Kode Mata Pelajaran <span class="req">*</span></label>
                        <input type="text" name="kode_mapel" id="kode_mapel"
                            class="f-input f-kode @error('kode_mapel') is-invalid @enderror"
                            value="{{ old('kode_mapel', $mataPelajaran->kode_mapel) }}" placeholder="cth. BI" maxlength="10"
                            required>
                        @error('kode_mapel')
                            <span class="invalid-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                        <span class="hint">Kode unik, maksimal 10 karakter</span>
                    </div>

                    <div class="field">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="f-textarea @error('deskripsi') is-invalid @enderror"
                            placeholder="Deskripsi singkat (opsional)...">{{ old('deskripsi', $mataPelajaran->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <span class="invalid-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- Pengaturan --}}
            <div class="form-card">
                <div class="form-card-head">
                    <div class="fc-icon" style="background:#fef3c7;">
                        <i class="fas fa-sliders-h" style="color:#b45309;"></i>
                    </div>
                    <h3>Pengaturan</h3>
                </div>
                <div class="form-card-body">

                    <div class="field">
                        <label>Kategori <span class="req">*</span></label>
                        <div class="kategori-pills">
                            <div class="kategori-pill">
                                <input type="radio" id="k-umum" name="kategori" value="umum"
                                    {{ old('kategori', $mataPelajaran->kategori) == 'umum' ? 'checked' : '' }} required>
                                <label for="k-umum" class="pill-umum">
                                    <i class="fas fa-globe"></i> Umum
                                </label>
                            </div>
                            <div class="kategori-pill">
                                <input type="radio" id="k-jurusan" name="kategori" value="jurusan"
                                    {{ old('kategori', $mataPelajaran->kategori) == 'jurusan' ? 'checked' : '' }}>
                                <label for="k-jurusan" class="pill-jurusan">
                                    <i class="fas fa-cogs"></i> Jurusan
                                </label>
                            </div>
                            <div class="kategori-pill">
                                <input type="radio" id="k-mulok" name="kategori" value="mulok"
                                    {{ old('kategori', $mataPelajaran->kategori) == 'mulok' ? 'checked' : '' }}>
                                <label for="k-mulok" class="pill-mulok">
                                    <i class="fas fa-map-marker-alt"></i> Muatan Lokal
                                </label>
                            </div>
                        </div>
                        @error('kategori')
                            <span class="invalid-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label>Status</label>
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <h4>Mata Pelajaran Aktif</h4>
                                <p>Mapel aktif dapat digunakan dalam penjadwalan KBM</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="status_aktif" name="status_aktif" value="1"
                                    {{ old('status_aktif', $mataPelajaran->status_aktif) ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Actions --}}
            <div class="form-actions">
                <a href="{{ route('admin.mata-pelajaran.show', $mataPelajaran) }}" class="fa-btn fa-btn-back">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <button type="submit" class="fa-btn fa-btn-submit">
                    <i class="fas fa-save"></i> Simpan Perubahan
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

        });
    </script>
@endpush
