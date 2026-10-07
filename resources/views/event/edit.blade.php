@extends('layouts.app')

@section('title', 'Edit - ' . $event->nama_event)

@push('styles')
    @include('components.event-styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css" />
    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

@php use Illuminate\Support\Facades\Storage; @endphp
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

        .weekday-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
        }

        .weekday-option {
            position: relative;
        }

        .weekday-option input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .weekday-option span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 10px;
            background: #f8fafc;
            font-size: .78rem;
            font-weight: 700;
            color: var(--text-muted, #64748b);
            transition: all .18s;
        }

        .weekday-option input:checked + span {
            border-color: #0ea5e9;
            background: #e0f2fe;
            color: #0369a1;
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
            bottom: var(--footer-h, 60px);
            left: 0;
            right: 0;
            padding: 10px 16px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            z-index: 99;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
        }

        /* Pastikan footer navigasi selalu di atas action bar */
        #footer-bar {
            z-index: 100 !important;
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

        /* ── Auto Point Toggle Switch ── */
        .auto-point-toggle {
            position: relative;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .auto-point-toggle input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-track {
            width: 52px;
            height: 28px;
            background: #e2e8f0;
            border-radius: 14px;
            transition: background .25s;
            display: flex;
            align-items: center;
            padding: 0 3px;
        }

        .toggle-thumb {
            width: 22px;
            height: 22px;
            background: #fff;
            border-radius: 50%;
            transition: transform .25s;
            box-shadow: 0 1px 4px rgba(0,0,0,.2);
        }

        .auto-point-toggle input:checked + .toggle-track {
            background: #dc2626;
        }

        .auto-point-toggle input:checked + .toggle-track .toggle-thumb {
            transform: translateX(24px);
        }

        /* ── Pasal Autocomplete Dropdown ── */
        .pasal-dropdown-item {
            padding: 10px 14px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            transition: background .15s;
        }

        .pasal-dropdown-item:last-child {
            border-bottom: none;
        }

        .pasal-dropdown-item:hover,
        .pasal-dropdown-item[data-active] {
            background: #fef2f2;
        }

        .pasal-dropdown-item .pasal-item-label {
            font-size: .82rem;
            font-weight: 600;
            color: #0f172a;
            display: block;
        }

        .pasal-dropdown-item .pasal-item-poin {
            font-size: .72rem;
            color: #64748b;
            margin-top: 2px;
            display: block;
        }
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



        {{-- Notifikasi jika ini event master yang punya turunan --}}
        @if (!$event->recurrence_parent_id && $event->recurrenceRule)
            @php $childCount = $event->recurringChildren()->count(); @endphp
            @if ($childCount > 0)
                <div style="background:#e0f2fe;border:1px solid #bae6fd;border-radius:12px;
                            padding:12px 16px;margin-bottom:14px;display:flex;align-items:flex-start;gap:10px;">
                    <i class="fas fa-sync-alt" style="color:#0369a1;margin-top:2px;flex-shrink:0;"></i>
                    <div>
                        <div style="font-size:.82rem;font-weight:700;color:#0369a1;">Event Master · Seri Berulang</div>
                        <div style="font-size:.78rem;color:#0c4a6e;margin-top:2px;">
                            Event ini memiliki <strong>{{ $childCount }} kemunculan turunan</strong>.
                            Perubahan yang kamu simpan akan otomatis diterapkan ke semua turunan
                            (nama, deskripsi, lokasi, peserta, dll),
                            kecuali tanggal masing-masing kemunculan.
                        </div>
                    </div>
                </div>
            @endif
        @endif

        {{-- Notifikasi jika ini child event --}}
        @if ($event->recurrence_parent_id && $event->recurringParent)
            <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:12px;
                        padding:12px 16px;margin-bottom:14px;display:flex;align-items:flex-start;gap:10px;">
                <i class="fas fa-exclamation-triangle" style="color:#a16207;margin-top:2px;flex-shrink:0;"></i>
                <div>
                    <div style="font-size:.82rem;font-weight:700;color:#a16207;">
                        Kemunculan ke-{{ $event->recurrence_sequence }} dari Seri Berulang
                    </div>
                    <div style="font-size:.78rem;color:#713f12;margin-top:2px;">
                        Kamu sedang mengedit satu kemunculan saja. Perubahan tidak akan memengaruhi kemunculan lain.
                        Untuk mengubah semua sekaligus, gunakan
                        <a href="{{ route('event.edit', $event->recurringParent) }}"
                           style="color:#0ea5e9;font-weight:700;">Edit Event Master</a>.
                    </div>
                </div>
            </div>
        @endif

        <form id="editForm" method="POST" action="{{ route('event.update', $event) }}" enctype="multipart/form-data">
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
                        <label class="form-label">Kategori Event</label>
                        <input type="text" name="event_category_name"
                            class="form-input @error('event_category_name') is-error @enderror"
                            value="{{ old('event_category_name', $event->category?->nama_kategori) }}"
                            list="eventCategoryList" placeholder="Pilih atau ketik kategori baru">
                        <datalist id="eventCategoryList">
                            @foreach ($categories as $category)
                                <option value="{{ $category->nama_kategori }}"></option>
                            @endforeach
                        </datalist>
                        <div class="form-hint">Kategori baru akan tersimpan otomatis saat event diperbarui.</div>
                        @error('event_category_name')
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

            {{-- Pengulangan Event --}}
            @php
                $rule = $event->recurrenceRule;
                $recurrenceType = old('recurrence_type', $rule?->frequency ?? 'none');
                $selectedDays = collect(old('recurrence_days', $rule?->days_of_week ?? []))->map(fn ($day) => (string) $day)->all();
                $dayLabels = [
                    1 => 'Sen',
                    2 => 'Sel',
                    3 => 'Rab',
                    4 => 'Kam',
                    5 => 'Jum',
                    6 => 'Sab',
                    7 => 'Min',
                ];
            @endphp
            @if ($event->recurrence_parent_id)
                <div class="card">
                    <div class="c-head">
                        <div class="c-icon" style="background:#e0f2fe; color:#0369a1;"><i class="fas fa-repeat"></i></div>
                        <h3>Pengulangan Event</h3>
                        <span class="hbadge" style="background:#e0f2fe; color:#0369a1;">Occurrence</span>
                    </div>
                    <div class="c-body" style="padding:16px;">
                        <p style="color:var(--text-muted);font-size:.82rem;line-height:1.55;margin:0;">
                            Event ini dibuat dari seri berulang
                            @if ($event->recurringParent)
                                <strong>{{ $event->recurringParent->nama_event }}</strong>.
                            @endif
                        </p>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="c-head">
                        <div class="c-icon" style="background:#e0f2fe; color:#0369a1;"><i class="fas fa-repeat"></i></div>
                        <h3>Pengulangan Event</h3>
                        <span class="hbadge" style="background:#f1f5f9; color:#64748b;">Opsional</span>
                    </div>
                    <div class="c-body" style="padding:16px;">
                        <div class="grid-2" style="margin-bottom:14px;">
                            <div>
                                <label class="form-label">Pola Pengulangan</label>
                                <select name="recurrence_type" id="recurrenceType"
                                    class="form-input @error('recurrence_type') is-error @enderror"
                                    onchange="toggleRecurrenceFields()">
                                    <option value="none" {{ $recurrenceType === 'none' ? 'selected' : '' }}>Tidak berulang</option>
                                    <option value="daily" {{ $recurrenceType === 'daily' ? 'selected' : '' }}>Harian</option>
                                    <option value="weekly" {{ $recurrenceType === 'weekly' ? 'selected' : '' }}>Mingguan</option>
                                    <option value="monthly" {{ $recurrenceType === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="yearly" {{ $recurrenceType === 'yearly' ? 'selected' : '' }}>Tahunan</option>
                                    <option value="custom" {{ $recurrenceType === 'custom' ? 'selected' : '' }}>Custom interval</option>
                                </select>
                                @error('recurrence_type')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="recurrenceIntervalGroup">
                                <label class="form-label">Interval</label>
                                <input type="number" name="recurrence_interval" id="recurrenceInterval"
                                    class="form-input @error('recurrence_interval') is-error @enderror"
                                    value="{{ old('recurrence_interval', $rule?->interval ?? 1) }}" min="1" max="365">
                                <div class="form-hint" id="recurrenceIntervalHint">Setiap 1 periode</div>
                                @error('recurrence_interval')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div id="weeklyDaysGroup" style="margin-bottom:14px;">
                            <label class="form-label">Hari Mingguan</label>
                            <div class="weekday-grid">
                                @foreach ($dayLabels as $dayValue => $dayLabel)
                                    <label class="weekday-option">
                                        <input type="checkbox" name="recurrence_days[]" value="{{ $dayValue }}"
                                            {{ in_array((string) $dayValue, $selectedDays, true) ? 'checked' : '' }}>
                                        <span>{{ $dayLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('recurrence_days')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="recurrenceLimitGroup" class="grid-2">
                            <div>
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" name="recurrence_until"
                                    class="form-input @error('recurrence_until') is-error @enderror"
                                    value="{{ old('recurrence_until', $rule?->repeat_until?->format('Y-m-d')) }}">
                                @error('recurrence_until')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="form-label">Jumlah Kemunculan</label>
                                <input type="number" name="recurrence_count"
                                    class="form-input @error('recurrence_count') is-error @enderror"
                                    value="{{ old('recurrence_count', $rule?->occurrence_count) }}" min="2" max="366">
                                <div class="form-hint">Termasuk event pertama.</div>
                                @error('recurrence_count')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            @endif

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

            {{-- ⑤ Auto Point Pelanggaran --}}
            {{-- Data JSON pasal dikirim ke JS --}}
            <script id="pasal-event-data" type="application/json">
                {!! json_encode($pasalPelanggaran->map(fn($p) => [
                    'id'      => $p->idpasal,
                    'label'   => '[' . $p->idpasal . '] ' . $p->pasal,
                    'isi'     => $p->pasal,
                    'skormin' => (int) $p->skormin,
                    'skormax' => (int) $p->skormax,
                ])) !!}
            </script>

            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#fee2e2; color:#dc2626;"><i class="fas fa-exclamation-triangle"></i></div>
                    <h3>Auto Point Pelanggaran</h3>
                    <span class="hbadge" style="background:#f1f5f9; color:#64748b;">Opsional</span>
                </div>
                <div class="c-body" style="padding:16px;">
                    {{-- Toggle switch --}}
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                        <div>
                            <div style="font-size:.88rem; font-weight:700; color:var(--text-main);">Aktifkan Auto Point Pelanggaran</div>
                            <div class="form-hint" style="margin-top:2px;">Siswa yang tidak scan masuk otomatis diberi poin setelah event selesai</div>
                        </div>
                        <label class="auto-point-toggle" for="auto_point_toggle">
                            <input
                                type="checkbox"
                                id="auto_point_toggle"
                                name="auto_point_pelanggaran"
                                value="1"
                                {{ old('auto_point_pelanggaran', $event->auto_point_pelanggaran) ? 'checked' : '' }}
                                onchange="toggleAutoPoint(this)"
                            >
                            <span class="toggle-track">
                                <span class="toggle-thumb"></span>
                            </span>
                        </label>
                    </div>

                    {{-- Peringatan jika sudah diproses --}}
                    @if($event->auto_point_processed_at)
                        <div style="padding:10px 12px; background:#fef3c7; border:1px solid #fde68a; border-radius:8px; margin-bottom:12px; font-size:.8rem; color:#92400e;">
                            <i class="fas fa-check-circle"></i>
                            Auto poin sudah diproses pada {{ $event->auto_point_processed_at->translatedFormat('d F Y H:i') }}.
                            Mengubah pasal tidak akan mengulang proses ini.
                        </div>
                    @endif

                    {{-- Field Pasal (muncul saat toggle ON) --}}
                    <div id="pasalPelanggaranGroup" style="display:{{ old('auto_point_pelanggaran', $event->auto_point_pelanggaran) ? 'block' : 'none' }};">
                        <div class="c-divider"></div>
                        <label class="form-label">Pasal Pelanggaran <span class="req">*</span></label>

                        {{-- Hidden input untuk submit --}}
                        <input type="hidden" name="pasal_pelanggaran_id" id="pasal_event_hidden"
                            value="{{ old('pasal_pelanggaran_id', $event->pasal_pelanggaran_id) }}">

                        {{-- Input pencarian --}}
                        <input type="text" id="pasal_event_search"
                            class="form-input @error('pasal_pelanggaran_id') is-error @enderror"
                            placeholder="Ketik kode atau nama pasal pelanggaran..."
                            autocomplete="off" value="">

                        @error('pasal_pelanggaran_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror

                        {{-- Badge terpilih --}}
                        <div id="pasal_event_badge" style="display:none; align-items:center; gap:8px; margin-top:8px;
                             font-size:.82rem; color:#dc2626; background:#fef2f2; padding:8px 12px;
                             border-radius:8px; border:1px solid #fecaca;">
                            <i class="fas fa-tag" style="flex-shrink:0;"></i>
                            <span id="pasal_event_badge_text" style="flex:1; line-height:1.4;"></span>
                            <button type="button" id="pasal_event_clear"
                                style="background:none;border:none;cursor:pointer;color:#ef4444;font-size:1rem;padding:0 4px;flex-shrink:0;">
                                ✕
                            </button>
                        </div>

                        {{-- Container poin — ditampilkan JS jika pasal punya range --}}
                        <div id="poin_pelanggaran_container">
                            @if(old('poin_pelanggaran_event', $event->poin_pelanggaran_event))
                                <input type="hidden" id="poin_pelanggaran_event_input" name="poin_pelanggaran_event"
                                    value="{{ old('poin_pelanggaran_event', $event->poin_pelanggaran_event) }}">
                            @endif
                        </div>

                        @error('poin_pelanggaran_event')
                            <div class="form-error">{{ $message }}</div>
                        @enderror

                        <div class="form-hint" style="margin-top:6px;">Poin pelanggaran diambil langsung dari data pasal yang dipilih.</div>
                    </div>
                </div>
            </div>
            {{-- Dropdown pasal pelanggaran — di luar card agar tidak ter-clip overflow:hidden --}}
            <div id="pasal_event_dropdown" style="
                display:none; position:fixed; background:#fff;
                border:1px solid #e2e8f0; border-radius:10px;
                max-height:260px; overflow-y:auto; z-index:9999;
                box-shadow:0 8px 30px rgba(0,0,0,.15);">
            </div>

            {{-- ⑥ Auto Penghargaan --}}
            {{-- Data JSON pasal penghargaan dikirim ke JS --}}
            <script id="pasal-penghargaan-event-data" type="application/json">
                {!! json_encode($pasalPenghargaan->map(fn($p) => [
                    'id'      => $p->idpasal,
                    'label'   => '[' . $p->idpasal . '] ' . $p->pasal,
                    'isi'     => $p->pasal,
                    'skormin' => (int) $p->skormin,
                    'skormax' => (int) $p->skormax,
                ])) !!}
            </script>

            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#dcfce7; color:#16a34a;"><i class="fas fa-star"></i></div>
                    <h3>Auto Penghargaan</h3>
                    <span class="hbadge" style="background:#f1f5f9; color:#64748b;">Opsional</span>
                </div>
                <div class="c-body" style="padding:16px;">

                    {{-- Toggle switch --}}
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                        <div>
                            <div style="font-size:.88rem; font-weight:700; color:var(--text-main);">Aktifkan Auto Penghargaan</div>
                            <div class="form-hint" style="margin-top:2px;">Siswa yang berhasil scan masuk otomatis diberi poin penghargaan setelah event selesai</div>
                        </div>
                        <label class="auto-point-toggle" for="auto_penghargaan_toggle">
                            <input
                                type="checkbox"
                                id="auto_penghargaan_toggle"
                                name="auto_penghargaan"
                                value="1"
                                {{ old('auto_penghargaan', $event->auto_penghargaan) ? 'checked' : '' }}
                                onchange="toggleAutoPenghargaan(this)"
                            >
                            <span class="toggle-track">
                                <span class="toggle-thumb"></span>
                            </span>
                        </label>
                    </div>

                    {{-- Peringatan jika sudah diproses --}}
                    @if($event->auto_penghargaan_processed_at)
                        <div style="padding:10px 12px; background:#dcfce7; border:1px solid #bbf7d0; border-radius:8px; margin-bottom:12px; font-size:.8rem; color:#166534;">
                            <i class="fas fa-check-circle"></i>
                            Auto penghargaan sudah diproses pada {{ $event->auto_penghargaan_processed_at->translatedFormat('d F Y H:i') }}.
                            Mengubah pasal tidak akan mengulang proses ini.
                        </div>
                    @endif

                    {{-- Field Pasal --}}
                    <div id="pasalPenghargaanGroup" style="display:{{ old('auto_penghargaan', $event->auto_penghargaan) ? 'block' : 'none' }};">
                        <div class="c-divider"></div>
                        <label class="form-label">Pasal Penghargaan <span class="req">*</span></label>

                        {{-- Hidden input untuk submit --}}
                        <input type="hidden" name="pasal_penghargaan_id" id="pasal_penghargaan_hidden"
                            value="{{ old('pasal_penghargaan_id', $event->pasal_penghargaan_id) }}">

                        {{-- Input pencarian --}}
                        <input type="text" id="pasal_penghargaan_search"
                            class="form-input @error('pasal_penghargaan_id') is-error @enderror"
                            placeholder="Ketik kode atau nama pasal penghargaan..."
                            autocomplete="off" value="">

                        @error('pasal_penghargaan_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror

                        {{-- Badge terpilih --}}
                        <div id="pasal_penghargaan_badge" style="display:none; align-items:center; gap:8px; margin-top:8px;
                             font-size:.82rem; color:#16a34a; background:#f0fdf4; padding:8px 12px;
                             border-radius:8px; border:1px solid #bbf7d0;">
                            <i class="fas fa-tag" style="flex-shrink:0;"></i>
                            <span id="pasal_penghargaan_badge_text" style="flex:1; line-height:1.4;"></span>
                            <button type="button" id="pasal_penghargaan_clear"
                                style="background:none;border:none;cursor:pointer;color:#16a34a;font-size:1rem;padding:0 4px;flex-shrink:0;">
                                ✕
                            </button>
                        </div>

                        {{-- Container poin — ditampilkan JS jika pasal punya range --}}
                        <div id="poin_penghargaan_container">
                            @if(old('poin_penghargaan_event', $event->poin_penghargaan_event))
                                <input type="hidden" id="poin_penghargaan_event_input" name="poin_penghargaan_event"
                                    value="{{ old('poin_penghargaan_event', $event->poin_penghargaan_event) }}">
                            @endif
                        </div>

                        @error('poin_penghargaan_event')
                            <div class="form-error">{{ $message }}</div>
                        @enderror

                        <div class="form-hint" style="margin-top:6px;">Poin penghargaan diambil langsung dari data pasal yang dipilih.</div>
                    </div>
                </div>
            </div>
            {{-- Dropdown pasal penghargaan — di luar card agar tidak ter-clip overflow:hidden --}}
            <div id="pasal_penghargaan_dropdown" style="
                display:none; position:fixed; background:#fff;
                border:1px solid #e2e8f0; border-radius:10px;
                max-height:260px; overflow-y:auto; z-index:9999;
                box-shadow:0 8px 30px rgba(0,0,0,.15);">
            </div>

            {{-- ⑦ Ekstrakurikuler --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#fce7f3; color:#be185d;"><i class="fas fa-futbol"></i></div>
                    <h3>Ekstrakurikuler</h3>
                    <span class="hbadge" style="background:#f1f5f9; color:#64748b;">Opsional</span>
                </div>
                <div class="c-body" style="padding:16px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                        <div>
                            <div style="font-size:.88rem; font-weight:700; color:var(--text-main);">Apakah ini Ekstrakurikuler?</div>
                            <div class="form-hint" style="margin-top:2px;">Aktifkan untuk mengisi data pelatih dan pembina</div>
                        </div>
                        <label class="auto-point-toggle" for="is_ekstrakurikuler_toggle">
                            <input type="checkbox" id="is_ekstrakurikuler_toggle" name="is_ekstrakurikuler" value="1"
                                {{ old('is_ekstrakurikuler', $event->is_ekstrakurikuler) ? 'checked' : '' }}
                                onchange="toggleEkstrakurikuler(this)">
                            <span class="toggle-track" style="background: var(--toggle-bg, #cbd5e1);">
                                <span class="toggle-thumb"></span>
                            </span>
                        </label>
                    </div>

                    <div id="ekstraGroup" style="display:{{ old('is_ekstrakurikuler', $event->is_ekstrakurikuler) ? 'block' : 'none' }};">
                        <div class="c-divider"></div>
                        <div style="margin-bottom:12px;">
                            <label class="form-label">Pelatih 1 <span style="color:#94a3b8;font-weight:400;">(opsional)</span></label>
                            <input type="text" name="pelatih_1" class="form-input @error('pelatih_1') is-error @enderror"
                                value="{{ old('pelatih_1', $event->pelatih_1) }}" placeholder="Nama pelatih pertama">
                            @error('pelatih_1')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div style="margin-bottom:12px;">
                            <label class="form-label">Pelatih 2 <span style="color:#94a3b8;font-weight:400;">(opsional)</span></label>
                            <input type="text" name="pelatih_2" class="form-input @error('pelatih_2') is-error @enderror"
                                value="{{ old('pelatih_2', $event->pelatih_2) }}" placeholder="Nama pelatih kedua">
                            @error('pelatih_2')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div style="margin-bottom:12px;">
                            <label class="form-label">Pelatih 3 <span style="color:#94a3b8;font-weight:400;">(opsional)</span></label>
                            <input type="text" name="pelatih_3" class="form-input @error('pelatih_3') is-error @enderror"
                                value="{{ old('pelatih_3', $event->pelatih_3) }}" placeholder="Nama pelatih ketiga">
                            @error('pelatih_3')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="c-divider"></div>
                        <div style="margin-bottom:12px;">
                            <label class="form-label">Nama Pembina <span class="req">*</span></label>
                            <input type="text" name="pembina_nama" class="form-input @error('pembina_nama') is-error @enderror"
                                value="{{ old('pembina_nama', $event->pembina_nama) }}" placeholder="Nama lengkap pembina">
                            @error('pembina_nama')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">NIP Pembina <span style="color:#94a3b8;font-weight:400;">(opsional)</span></label>
                            <input type="text" name="pembina_nip" class="form-input @error('pembina_nip') is-error @enderror"
                                value="{{ old('pembina_nip', $event->pembina_nip) }}" placeholder="NIP pembina (jika ada)">
                            @error('pembina_nip')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ⑧ Upload Foto Kegiatan --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#e0f2fe; color:#0369a1;"><i class="fas fa-camera"></i></div>
                    <h3>Foto Kegiatan</h3>
                    <span class="hbadge" style="background:#f1f5f9; color:#64748b;">Maks 10 foto</span>
                </div>
                <div class="c-body" style="padding:16px;">
                    @php $existingPhotos = $event->photos; @endphp

                    {{-- Foto yang sudah tersimpan --}}
                    @if ($existingPhotos->isNotEmpty())
                        <div style="margin-bottom:14px;">
                            <div style="font-size:.78rem; font-weight:600; color:#475569; margin-bottom:8px;">
                                {{ $existingPhotos->count() }} foto tersimpan — centang untuk dihapus:
                            </div>
                            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px;">
                                @foreach ($existingPhotos as $photo)
                                    <div style="position:relative; border-radius:8px; overflow:hidden; aspect-ratio:1; background:#f1f5f9; border:2px solid transparent;" id="photo-wrap-{{ $photo->id }}">
                                        <img src="{{ Storage::url($photo->path) }}" style="width:100%;height:100%;object-fit:cover;" alt="Foto kegiatan">
                                        <label style="position:absolute;top:4px;right:4px;cursor:pointer;">
                                            <input type="checkbox" name="hapus_foto[]" value="{{ $photo->id }}"
                                                style="width:18px;height:18px;accent-color:#ef4444;cursor:pointer;"
                                                onchange="markPhotoDelete(this, {{ $photo->id }})">
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="c-divider"></div>
                    @endif

                    @if ($existingPhotos->count() < 10)
                        <p style="color:var(--text-muted);font-size:.82rem;line-height:1.55;margin:0 0 12px;">
                            Tambah foto baru (sisa slot: <strong>{{ 10 - $existingPhotos->count() }}</strong> foto).
                        </p>
                        <div id="fotoDropZone" onclick="document.getElementById('fotoInput').click()"
                            style="border:2px dashed #bae6fd; border-radius:12px; padding:24px 16px; text-align:center; cursor:pointer; background:#f0f9ff; transition:all .2s;">
                            <i class="fas fa-cloud-upload-alt" style="font-size:1.8rem; color:#0ea5e9; margin-bottom:8px; display:block;"></i>
                            <div style="font-size:.85rem; font-weight:600; color:#0369a1;">Klik untuk pilih foto</div>
                            <div style="font-size:.74rem; color:#64748b; margin-top:4px;">atau drag & drop di sini</div>
                        </div>
                        <input type="file" id="fotoInput" name="foto_kegiatan[]" multiple accept="image/jpeg,image/jpg,image/png,image/webp"
                            style="display:none;" onchange="handleFotoChange(this)">
                    @else
                        <div style="padding:10px 12px; background:#fef3c7; border:1px solid #fde68a; border-radius:8px; font-size:.8rem; color:#92400e;">
                            <i class="fas fa-exclamation-circle"></i> Sudah mencapai batas 10 foto. Hapus foto lama untuk menambah yang baru.
                        </div>
                    @endif

                    @error('foto_kegiatan')<div class="form-error" style="margin-top:6px;">{{ $message }}</div>@enderror
                    @error('foto_kegiatan.*')<div class="form-error" style="margin-top:6px;">{{ $message }}</div>@enderror

                    <div id="fotoPreviewGrid" style="display:none; margin-top:12px;">
                        <div style="font-size:.78rem; font-weight:600; color:#475569; margin-bottom:8px;">
                            <span id="fotoCount">0</span> foto baru dipilih
                        </div>
                        <div id="fotoPreviewList" style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px;"></div>
                    </div>
                </div>
            </div>

            {{-- ⑨ Peserta --}}
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

        function toggleAutoPoint(checkbox) {
            const group = document.getElementById('pasalPelanggaranGroup');
            if (checkbox.checked) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
                const hiddenEl = document.getElementById('pasal_event_hidden');
                const searchEl = document.getElementById('pasal_event_search');
                const badgeEl  = document.getElementById('pasal_event_badge');
                const dropEl   = document.getElementById('pasal_event_dropdown');
                if (hiddenEl) hiddenEl.value = '';
                if (searchEl) searchEl.value = '';
                if (badgeEl)  badgeEl.style.display = 'none';
                if (dropEl)   dropEl.style.display  = 'none';
            }
        }

        function toggleAutoPenghargaan(checkbox) {
            const group = document.getElementById('pasalPenghargaanGroup');
            if (checkbox.checked) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
                const hiddenEl = document.getElementById('pasal_penghargaan_hidden');
                const searchEl = document.getElementById('pasal_penghargaan_search');
                const badgeEl  = document.getElementById('pasal_penghargaan_badge');
                const dropEl   = document.getElementById('pasal_penghargaan_dropdown');
                if (hiddenEl) hiddenEl.value = '';
                if (searchEl) searchEl.value = '';
                if (badgeEl)  badgeEl.style.display = 'none';
                if (dropEl)   dropEl.style.display  = 'none';
            }
        } {
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
            const countEl = document.getElementById(countId);
            if (countEl) countEl.textContent = count + ' ' + label + ' dipilih';
        }

        function toggleRecurrenceFields() {
            const type = document.getElementById('recurrenceType')?.value || 'none';
            const intervalGroup = document.getElementById('recurrenceIntervalGroup');
            const weeklyDaysGroup = document.getElementById('weeklyDaysGroup');
            const limitGroup = document.getElementById('recurrenceLimitGroup');
            const hint = document.getElementById('recurrenceIntervalHint');

            if (intervalGroup) intervalGroup.style.display = type === 'none' ? 'none' : 'block';
            if (weeklyDaysGroup) weeklyDaysGroup.style.display = type === 'weekly' ? 'block' : 'none';
            if (limitGroup) limitGroup.style.display = type === 'none' ? 'none' : 'grid';

            if (!hint) return;

            const labels = {
                daily: 'hari',
                weekly: 'minggu',
                monthly: 'bulan',
                yearly: 'tahun',
                custom: 'hari',
            };
            hint.textContent = type === 'none' ? '' : 'Setiap N ' + (labels[type] || 'periode') + '.';
        }

        function confirmDelete() {
            if (confirm('Yakin ingin menghapus event ini? Data absensi juga akan terhapus.')) {
                document.getElementById('deleteForm').submit();
            }
        }

        // ═══════════════════════════════════════════════════════
        // EKSTRAKURIKULER — TOGGLE
        // ═══════════════════════════════════════════════════════
        function toggleEkstrakurikuler(checkbox) {
            document.getElementById('ekstraGroup').style.display = checkbox.checked ? 'block' : 'none';
        }

        // ═══════════════════════════════════════════════════════
        // FOTO KEGIATAN — PREVIEW (edit page)
        // ═══════════════════════════════════════════════════════
        let selectedFiles = [];

        function handleFotoChange(input) {
            var newFiles = Array.from(input.files);
            selectedFiles = selectedFiles.concat(newFiles).slice(0, 10);
            renderFotoPreview();
            rebuildFileInput();
        }

        function rebuildFileInput() {
            var dt = new DataTransfer();
            selectedFiles.forEach(function(f) { dt.items.add(f); });
            var inp = document.getElementById('fotoInput');
            if (inp) inp.files = dt.files;
        }

        function removeFoto(index) {
            selectedFiles.splice(index, 1);
            renderFotoPreview();
            rebuildFileInput();
        }

        function renderFotoPreview() {
            var grid  = document.getElementById('fotoPreviewGrid');
            var list  = document.getElementById('fotoPreviewList');
            var count = document.getElementById('fotoCount');
            if (!list) return;
            list.innerHTML = '';
            if (selectedFiles.length === 0) { if (grid) grid.style.display = 'none'; return; }
            if (grid) grid.style.display = 'block';
            if (count) count.textContent = selectedFiles.length;
            selectedFiles.forEach(function(file, idx) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var div = document.createElement('div');
                    div.style.cssText = 'position:relative;border-radius:8px;overflow:hidden;aspect-ratio:1;background:#f1f5f9;';
                    div.innerHTML =
                        '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;" alt="">' +
                        '<button type="button" onclick="removeFoto(' + idx + ')" ' +
                        'style="position:absolute;top:4px;right:4px;background:rgba(0,0,0,.6);color:#fff;border:none;border-radius:50%;' +
                        'width:22px;height:22px;font-size:.75rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">' +
                        '&times;</button>' +
                        '<div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.5);color:#fff;' +
                        'font-size:.6rem;padding:2px 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' +
                        file.name + '</div>';
                    list.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }

        function markPhotoDelete(checkbox, photoId) {
            var wrap = document.getElementById('photo-wrap-' + photoId);
            if (wrap) wrap.style.borderColor = checkbox.checked ? '#ef4444' : 'transparent';
        }

        (function() {
            var zone = document.getElementById('fotoDropZone');
            if (!zone) return;
            zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.style.borderColor='#0ea5e9'; zone.style.background='#e0f2fe'; });
            zone.addEventListener('dragleave', function() { zone.style.borderColor='#bae6fd'; zone.style.background='#f0f9ff'; });
            zone.addEventListener('drop', function(e) {
                e.preventDefault(); zone.style.borderColor='#bae6fd'; zone.style.background='#f0f9ff';
                var files = Array.from(e.dataTransfer.files).filter(function(f) { return f.type.startsWith('image/'); });
                selectedFiles = selectedFiles.concat(files).slice(0, 10);
                renderFotoPreview(); rebuildFileInput();
            });
        })();

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
            toggleRecurrenceFields();
            updateCount('kelasList', 'kelasCount');
            updateCount('siswaList', 'siswaCount');
            updateDisplay();

            // Init pasal autocomplete
            (function () {
                var PASAL_DATA = [];

                var searchEl  = document.getElementById('pasal_event_search');
                var hiddenEl  = document.getElementById('pasal_event_hidden');
                var dropEl    = document.getElementById('pasal_event_dropdown');
                var badgeEl   = document.getElementById('pasal_event_badge');
                var badgeText = document.getElementById('pasal_event_badge_text');
                var clearBtn  = document.getElementById('pasal_event_clear');

                if (!searchEl || !dropEl) return;

                function positionDropdown() {
                    var rect = searchEl.getBoundingClientRect();
                    dropEl.style.top   = rect.bottom + 'px';
                    dropEl.style.left  = rect.left + 'px';
                    dropEl.style.width = rect.width + 'px';
                }

                function filterPasal(q) {
                    if (!q || !q.trim()) return PASAL_DATA;
                    var lower = q.toLowerCase();
                    return PASAL_DATA.filter(function (p) {
                        return p.label.toLowerCase().indexOf(lower) !== -1 ||
                               p.isi.toLowerCase().indexOf(lower) !== -1;
                    });
                }

                function renderDropdown(items) {
                    dropEl.innerHTML = '';
                    if (items.length === 0) {
                        var empty = document.createElement('div');
                        empty.style.cssText = 'padding:12px 14px;color:#94a3b8;font-size:.82rem;';
                        empty.textContent = 'Pasal tidak ditemukan';
                        dropEl.appendChild(empty);
                    } else {
                        items.forEach(function (item) {
                            var div = document.createElement('div');
                            div.style.cssText = 'padding:10px 14px;cursor:pointer;border-bottom:1px solid #f1f5f9;';

                            var lbl = document.createElement('span');
                            lbl.style.cssText = 'display:block;font-size:.83rem;font-weight:600;color:#0f172a;line-height:1.4;';
                            lbl.textContent = item.label;

                            var poin = document.createElement('span');
                            poin.style.cssText = 'display:block;font-size:.73rem;color:#64748b;margin-top:2px;';
                            poin.textContent = item.skormin + '–' + item.skormax + ' poin';

                            div.appendChild(lbl);
                            div.appendChild(poin);

                            div.addEventListener('mouseover', function () { div.style.background = '#fef2f2'; });
                            div.addEventListener('mouseout',  function () { div.style.background = ''; });
                            div.addEventListener('mousedown', function (e) {
                                e.preventDefault();
                                selectPasalEvent(item);
                            });

                            dropEl.appendChild(div);
                        });
                    }
                    positionDropdown();
                    dropEl.style.display = 'block';
                }

                function selectPasalEvent(item) {
                    hiddenEl.value    = item.id;
                    searchEl.value    = item.label;
                    dropEl.style.display = 'none';
                    badgeText.textContent = item.label + ' — ' + item.skormin + '–' + item.skormax + ' poin';
                    badgeEl.style.display = 'flex';
                    renderPoinPelanggaranInput(item);
                }

                function renderPoinPelanggaranInput(item) {
                    var container = document.getElementById('poin_pelanggaran_container');
                    if (!container) return;
                    if (item.skormin === item.skormax) {
                        container.innerHTML = '<div class="form-hint" style="margin-top:6px; color:#64748b;">' +
                            '<i class="fas fa-info-circle"></i> Poin otomatis: <strong>' + item.skormax + '</strong></div>';
                        var existing = document.getElementById('poin_pelanggaran_event_input');
                        if (existing) existing.value = '';
                    } else {
                        var oldVal = (document.getElementById('poin_pelanggaran_event_input') || {}).value || item.skormax;
                        container.innerHTML =
                            '<label class="form-label" style="margin-top:10px;">Poin Pelanggaran <span class="req">*</span></label>' +
                            '<input type="number" id="poin_pelanggaran_event_input" name="poin_pelanggaran_event"' +
                            '    class="form-input" min="' + item.skormin + '" max="' + item.skormax + '"' +
                            '    value="' + oldVal + '" placeholder="' + item.skormin + ' – ' + item.skormax + '">' +
                            '<div class="form-hint" style="margin-top:4px;">Masukkan poin antara ' + item.skormin + ' dan ' + item.skormax + '</div>';
                    }
                }

                function clearPasalEvent() {
                    hiddenEl.value = '';
                    searchEl.value = '';
                    badgeEl.style.display = 'none';
                    dropEl.style.display  = 'none';
                    var container = document.getElementById('poin_pelanggaran_container');
                    if (container) container.innerHTML = '';
                    searchEl.focus();
                }

                if (clearBtn) clearBtn.addEventListener('click', clearPasalEvent);

                searchEl.addEventListener('input', function () {
                    if (hiddenEl.value) {
                        hiddenEl.value = '';
                        badgeEl.style.display = 'none';
                    }
                    renderDropdown(filterPasal(searchEl.value));
                });

                searchEl.addEventListener('focus', function () {
                    renderDropdown(filterPasal(searchEl.value));
                });

                searchEl.addEventListener('blur', function () {
                    setTimeout(function () { dropEl.style.display = 'none'; }, 200);
                });

                window.addEventListener('scroll', function () {
                    if (dropEl.style.display !== 'none') positionDropdown();
                }, true);
                window.addEventListener('resize', function () {
                    if (dropEl.style.display !== 'none') positionDropdown();
                });

                searchEl.addEventListener('keydown', function (e) {
                    var items  = dropEl.querySelectorAll('div[style*="cursor:pointer"]');
                    var active = dropEl.querySelector('[data-active]');
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (!active && items.length) {
                            items[0].setAttribute('data-active', '1');
                            items[0].style.background = '#fef2f2';
                        } else if (active && active.nextElementSibling) {
                            active.removeAttribute('data-active'); active.style.background = '';
                            active.nextElementSibling.setAttribute('data-active', '1');
                            active.nextElementSibling.style.background = '#fef2f2';
                            active.nextElementSibling.scrollIntoView({ block: 'nearest' });
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (active && active.previousElementSibling) {
                            active.removeAttribute('data-active'); active.style.background = '';
                            active.previousElementSibling.setAttribute('data-active', '1');
                            active.previousElementSibling.style.background = '#fef2f2';
                            active.previousElementSibling.scrollIntoView({ block: 'nearest' });
                        }
                    } else if (e.key === 'Enter' && active) {
                        e.preventDefault();
                        active.dispatchEvent(new MouseEvent('mousedown'));
                    } else if (e.key === 'Escape') {
                        dropEl.style.display = 'none';
                    }
                });

                document.addEventListener('click', function (e) {
                    if (!searchEl.contains(e.target) && !dropEl.contains(e.target)) {
                        dropEl.style.display = 'none';
                    }
                });

                // Load data & restore nilai tersimpan
                var dataEl = document.getElementById('pasal-event-data');
                if (dataEl) PASAL_DATA = JSON.parse(dataEl.textContent || '[]');

                var oldId = hiddenEl.value;
                if (oldId) {
                    var found = PASAL_DATA.find(function (p) { return String(p.id) === String(oldId); });
                    if (found) selectPasalEvent(found);
                }
            })();

            // ═══════════════════════════════════════════════════════
            // AUTO PENGHARGAAN — PASAL AUTOCOMPLETE (fixed-position dropdown)
            // ═══════════════════════════════════════════════════════
            (function () {
                var PASAL_DATA = [];

                var searchEl  = document.getElementById('pasal_penghargaan_search');
                var hiddenEl  = document.getElementById('pasal_penghargaan_hidden');
                var dropEl    = document.getElementById('pasal_penghargaan_dropdown');
                var badgeEl   = document.getElementById('pasal_penghargaan_badge');
                var badgeText = document.getElementById('pasal_penghargaan_badge_text');
                var clearBtn  = document.getElementById('pasal_penghargaan_clear');

                if (!searchEl || !dropEl) return;

                function positionDropdown() {
                    var rect = searchEl.getBoundingClientRect();
                    dropEl.style.top   = rect.bottom + 'px';
                    dropEl.style.left  = rect.left + 'px';
                    dropEl.style.width = rect.width + 'px';
                }

                function filterPasal(q) {
                    if (!q || !q.trim()) return PASAL_DATA;
                    var lower = q.toLowerCase();
                    return PASAL_DATA.filter(function (p) {
                        return p.label.toLowerCase().indexOf(lower) !== -1 ||
                               p.isi.toLowerCase().indexOf(lower) !== -1;
                    });
                }

                function renderDropdown(items) {
                    dropEl.innerHTML = '';
                    if (items.length === 0) {
                        var empty = document.createElement('div');
                        empty.style.cssText = 'padding:12px 14px;color:#94a3b8;font-size:.82rem;';
                        empty.textContent = 'Pasal tidak ditemukan';
                        dropEl.appendChild(empty);
                    } else {
                        items.forEach(function (item) {
                            var div = document.createElement('div');
                            div.style.cssText = 'padding:10px 14px;cursor:pointer;border-bottom:1px solid #f1f5f9;';
                            var lbl = document.createElement('span');
                            lbl.style.cssText = 'display:block;font-size:.83rem;font-weight:600;color:#0f172a;line-height:1.4;';
                            lbl.textContent = item.label;
                            var poin = document.createElement('span');
                            poin.style.cssText = 'display:block;font-size:.73rem;color:#64748b;margin-top:2px;';
                            poin.textContent = item.skormin + '–' + item.skormax + ' poin';
                            div.appendChild(lbl);
                            div.appendChild(poin);
                            div.addEventListener('mouseover', function () { div.style.background = '#f0fdf4'; });
                            div.addEventListener('mouseout',  function () { div.style.background = ''; });
                            div.addEventListener('mousedown', function (e) {
                                e.preventDefault();
                                selectPasal(item);
                            });
                            dropEl.appendChild(div);
                        });
                    }
                    positionDropdown();
                    dropEl.style.display = 'block';
                }

                function selectPasal(item) {
                    hiddenEl.value = item.id;
                    searchEl.value = item.label;
                    dropEl.style.display = 'none';
                    badgeText.textContent = item.label + ' — ' + item.skormin + '–' + item.skormax + ' poin';
                    badgeEl.style.display = 'flex';
                    renderPoinPenghargaanInput(item);
                }

                function renderPoinPenghargaanInput(item) {
                    var container = document.getElementById('poin_penghargaan_container');
                    if (!container) return;
                    if (item.skormin === item.skormax) {
                        container.innerHTML = '<div class="form-hint" style="margin-top:6px; color:#64748b;">' +
                            '<i class="fas fa-info-circle"></i> Poin otomatis: <strong>' + item.skormax + '</strong></div>';
                        var existing = document.getElementById('poin_penghargaan_event_input');
                        if (existing) existing.value = '';
                    } else {
                        var oldVal = (document.getElementById('poin_penghargaan_event_input') || {}).value || item.skormax;
                        container.innerHTML =
                            '<label class="form-label" style="margin-top:10px;">Poin Penghargaan <span class="req">*</span></label>' +
                            '<input type="number" id="poin_penghargaan_event_input" name="poin_penghargaan_event"' +
                            '    class="form-input" min="' + item.skormin + '" max="' + item.skormax + '"' +
                            '    value="' + oldVal + '" placeholder="' + item.skormin + ' – ' + item.skormax + '">' +
                            '<div class="form-hint" style="margin-top:4px;">Masukkan poin antara ' + item.skormin + ' dan ' + item.skormax + '</div>';
                    }
                }

                function clearPasal() {
                    hiddenEl.value = '';
                    searchEl.value = '';
                    badgeEl.style.display = 'none';
                    dropEl.style.display  = 'none';
                    var container = document.getElementById('poin_penghargaan_container');
                    if (container) container.innerHTML = '';
                    searchEl.focus();
                }

                if (clearBtn) clearBtn.addEventListener('click', clearPasal);

                searchEl.addEventListener('input', function () {
                    if (hiddenEl.value) { hiddenEl.value = ''; badgeEl.style.display = 'none'; }
                    renderDropdown(filterPasal(searchEl.value));
                });
                searchEl.addEventListener('focus', function () { renderDropdown(filterPasal(searchEl.value)); });
                searchEl.addEventListener('blur',  function () { setTimeout(function () { dropEl.style.display = 'none'; }, 200); });

                window.addEventListener('scroll', function () { if (dropEl.style.display !== 'none') positionDropdown(); }, true);
                window.addEventListener('resize', function () { if (dropEl.style.display !== 'none') positionDropdown(); });

                searchEl.addEventListener('keydown', function (e) {
                    var items  = dropEl.querySelectorAll('div[style*="cursor:pointer"]');
                    var active = dropEl.querySelector('[data-active]');
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (!active && items.length) { items[0].setAttribute('data-active', '1'); items[0].style.background = '#f0fdf4'; }
                        else if (active && active.nextElementSibling) {
                            active.removeAttribute('data-active'); active.style.background = '';
                            active.nextElementSibling.setAttribute('data-active', '1'); active.nextElementSibling.style.background = '#f0fdf4';
                            active.nextElementSibling.scrollIntoView({ block: 'nearest' });
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (active && active.previousElementSibling) {
                            active.removeAttribute('data-active'); active.style.background = '';
                            active.previousElementSibling.setAttribute('data-active', '1'); active.previousElementSibling.style.background = '#f0fdf4';
                            active.previousElementSibling.scrollIntoView({ block: 'nearest' });
                        }
                    } else if (e.key === 'Enter' && active) {
                        e.preventDefault();
                        active.dispatchEvent(new MouseEvent('mousedown'));
                    } else if (e.key === 'Escape') {
                        dropEl.style.display = 'none';
                    }
                });

                document.addEventListener('click', function (e) {
                    if (!searchEl.contains(e.target) && !dropEl.contains(e.target)) dropEl.style.display = 'none';
                });

                // Load data & restore nilai tersimpan
                var dataElP = document.getElementById('pasal-penghargaan-event-data');
                if (dataElP) PASAL_DATA = JSON.parse(dataElP.textContent || '[]');

                var oldIdP = hiddenEl.value;
                if (oldIdP) {
                    var foundP = PASAL_DATA.find(function (p) { return String(p.id) === String(oldIdP); });
                    if (foundP) selectPasal(foundP);
                }
            })();
        });
    </script>
@endpush
