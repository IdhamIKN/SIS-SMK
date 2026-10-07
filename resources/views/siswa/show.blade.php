@extends('layouts.app')

@section('title', 'Detail - ' . $siswa->nama_lengkap)

@push('styles')
    @include('components.izin-styles')
    <style>
        .profile-hero {
            position: relative;
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 55%, #0ea5e9 100%);
            padding: 24px 20px 64px;
            overflow: hidden;
        }

        .profile-hero::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 160px;
            height: 160px;
            background: rgba(255, 255, 255, .07);
            border-radius: 50%;
        }

        .profile-hero::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -20px;
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, .05);
            border-radius: 50%;
        }

        .hero-nav {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            max-width: 848px;
            margin-inline: auto;
        }

        .hero-back {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .18);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            font-size: .9rem;
            border: 1px solid rgba(255, 255, 255, .25);
            backdrop-filter: blur(6px);
        }

        .hero-actions {
            display: flex;
            gap: 8px;
        }

        .hero-action-btn {
            padding: 7px 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .18);
            color: #fff;
            font-size: .75rem;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, .25);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            backdrop-filter: blur(6px);
            transition: background .18s;
        }

        .hero-action-btn:hover {
            background: rgba(255, 255, 255, .28);
        }

        .profile-card-wrap {
            padding: 0 16px;
        }

        @media(min-width:768px) {
            .profile-card-wrap {
                max-width: 880px;
                margin-inline: auto;
                padding: 0 24px;
            }
        }

        .profile-card {
            position: relative;
            z-index: 3;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 6px 28px rgba(0, 0, 0, .12);
            margin-top: -44px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .profile-avatar {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            border: 3px solid #fff;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .15);
            flex-shrink: 0;
            overflow: hidden;
            background: #ede9fe;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7c3aed;
            font-size: 1.6rem;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info {
            flex: 1;
            min-width: 160px;
        }

        .profile-name {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .profile-nisn {
            font-size: .72rem;
            color: #64748b;
            margin: 0 0 6px;
        }

        .profile-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .ptag {
            font-size: .65rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .ptag-purple {
            background: #ede9fe;
            color: #7c3aed;
        }

        .ptag-green {
            background: #dcfce7;
            color: #15803d;
        }

        .ptag-red {
            background: #fee2e2;
            color: #dc2626;
        }

        .ptag-blue {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .absen-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }

        @media(max-width:359.98px) {
            .absen-stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .absen-stat {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 8px;
            text-align: center;
        }

        .absen-stat .as-val {
            font-size: 1.3rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 3px;
        }

        .absen-stat .as-lbl {
            font-size: .6rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 9px 0;
            font-size: .84rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .dr-label {
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 7px;
            flex-shrink: 0;
            min-width: 110px;
            font-size: .78rem;
        }

        .dr-label i {
            width: 14px;
            text-align: center;
        }

        .dr-value {
            flex: 1;
            min-width: 0;
            font-weight: 600;
            color: #0f172a;
            text-align: right;
            font-size: .82rem;
            word-break: break-word;
        }

        .absen-table-wrap {
            overflow-x: auto;
        }

        .absen-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .78rem;
        }

        .absen-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .absen-table th {
            padding: 8px 10px;
            text-align: left;
            font-size: .68rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }

        .absen-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .absen-table tbody tr:last-child td {
            border-bottom: none;
        }

        .absen-table tbody tr:hover td {
            background: #fafbfc;
        }

        @media(max-width:575.98px) {
            .absen-table thead {
                display: none;
            }

            .absen-table,
            .absen-table tbody,
            .absen-table tr,
            .absen-table td {
                display: block;
                width: 100%;
            }

            .absen-table tbody tr {
                background: #fff;
                border: 1px solid #f1f5f9;
                border-radius: 10px;
                padding: 4px 12px;
                margin-bottom: 10px;
            }

            .absen-table tbody tr:last-child {
                margin-bottom: 0;
            }

            .absen-table td {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 7px 0;
                border-bottom: 1px dashed #f1f5f9;
                text-align: right;
            }

            .absen-table td:last-child {
                border-bottom: none;
            }

            .absen-table td::before {
                content: attr(data-label);
                font-size: .68rem;
                font-weight: 700;
                color: #94a3b8;
                text-transform: uppercase;
                letter-spacing: .04em;
                flex-shrink: 0;
                text-align: left;
            }

            .absen-table td.empty-cell {
                display: block;
                text-align: center;
            }

            .absen-table td.empty-cell::before {
                content: none;
            }
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700;
        }

        .s-hadir {
            background: #dcfce7;
            color: #15803d;
        }

        .s-sakit {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .s-izin {
            background: #fef9c3;
            color: #a16207;
        }

        .s-alfa {
            background: #fee2e2;
            color: #dc2626;
        }

        .dash-wrap {
            padding: 16px 16px calc(var(--footer-h) + 80px);
        }

        @media(min-width:768px) {
            .dash-wrap {
                max-width: 880px;
                margin-inline: auto;
                padding-left: 24px;
                padding-right: 24px;
            }
        }

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
            gap: 8px;
            z-index: 999;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
        }

        @media(min-width:768px) {
            .action-bar {
                max-width: 928px;
                margin-inline: auto;
                left: 0;
                right: 0;
            }
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 12px;
            border-radius: 12px;
            font-size: .82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ab-btn:active {
            transform: scale(.97);
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            flex: 0 0 auto;
            padding: 12px 14px;
        }

        .ab-btn-edit {
            background: #0d6efd;
            color: #fff;
            border: 1px solid #0b5ed7;
        }

        .ab-btn-edit:hover {
            background: #0b5ed7;
        }

        .ab-btn-pdf {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .ab-btn-pdf:hover {
            background: #bbf7d0;
        }

        .ab-btn-reset-pw {
            background: #f59e0b;
            color: #fff;
            border: 1px solid #d97706;
            flex: 0 0 auto;
            padding: 12px 14px;
        }

        .ab-btn-reset-pw:hover {
            background: #d97706;
        }

        .ab-btn-delete {
            background: #dc3545;
            color: #fff;
            border: 1px solid #bb2d3b;
            flex: 0 0 auto;
            padding: 12px 14px;
        }

        .ab-btn-delete:hover {
            background: #bb2d3b;
        }

        @media(max-width:380px) {

            .ab-btn-edit,
            .ab-btn-pdf {
                flex: 0 0 auto;
                padding: 12px 16px;
            }

            .ab-btn-edit .ab-label,
            .ab-btn-pdf .ab-label {
                display: none;
            }
        }

        .empty-table {
            text-align: center;
            padding: 24px 16px;
            color: #94a3b8;
            font-size: .82rem;
        }

        .empty-table i {
            display: block;
            font-size: 1.8rem;
            margin-bottom: 6px;
            opacity: .4;
        }

        /* ── Collapsible card ── */
        .card-collapsible .c-head {
            cursor: pointer;
            user-select: none;
        }

        .card-collapsible .c-head:hover {
            background: #f8fafc;
        }

        .card-chevron {
            margin-left: auto;
            color: #94a3b8;
            font-size: .75rem;
            transition: transform .2s;
            flex-shrink: 0;
        }

        .card-collapsible.is-open .card-chevron {
            transform: rotate(180deg);
        }

        .card-body-collapse {
            display: none;
        }

        .card-collapsible.is-open .card-body-collapse {
            display: block;
        }

        /* ── URL-based tab bar ── */
        .section-tab-bar {
            display: flex;
            gap: 6px;
            margin: 10px 0 12px;
        }

        .section-tab {
            flex: 1;
            padding: 7px 0;
            border-radius: 8px;
            font-size: .72rem;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            transition: background .15s;
        }

        .section-tab.active-red {
            background: #dc2626;
            color: #fff;
            border-color: #dc2626;
        }

        .section-tab.active-amber {
            background: #a16207;
            color: #fff;
            border-color: #a16207;
        }

        .section-tab.active-blue {
            background: #1d4ed8;
            color: #fff;
            border-color: #1d4ed8;
        }

        .tab-count {
            font-size: .63rem;
            opacity: .8;
        }
    </style>
@endpush

@section('content')

    {{-- ── Profile Hero ── --}}
    <div class="profile-hero">
        <div class="hero-nav">
            <a href="{{ route('siswa.index') }}" class="hero-back"><i class="fas fa-arrow-left"></i></a>
            <div class="hero-actions">
                <a href="{{ route('siswa.edit', $siswa) }}" class="hero-action-btn"><i class="fas fa-pen"></i> Edit</a>
                <a href="{{ route('siswa.export-cv', $siswa) }}" target="_blank" class="hero-action-btn"><i
                        class="fas fa-file-pdf"></i> CV</a>
            </div>
        </div>
    </div>

    {{-- ── Profile Card ── --}}
    <div class="profile-card-wrap">
        <div class="profile-card">
            <div class="profile-avatar">
                @if ($siswa->foto)
                    <img src="{{ Storage::url($siswa->foto) }}" alt="{{ $siswa->nama_lengkap }}">
                @else
                    <i class="fas fa-user-graduate"></i>
                @endif
            </div>
            <div class="profile-info">
                <h3 class="profile-name">{{ $siswa->nama_lengkap }}</h3>
                <p class="profile-nisn">NISN: {{ $siswa->nisn }}{{ $siswa->nis ? ' · NIS: ' . $siswa->nis : '' }}</p>
                <div class="profile-tags">
                    <span class="ptag ptag-purple"><i class="fas fa-door-open"></i>
                        {{ $siswa->kelas?->nama_kelas ?? '-' }}</span>
                    <span class="ptag ptag-blue"><i
                            class="fas fa-{{ $siswa->jenis_kelamin === 'L' ? 'mars' : 'venus' }}"></i>
                        {{ $siswa->jenis_kelamin === 'L' ? 'L' : 'P' }}</span>
                    <span class="ptag {{ $siswa->status_aktif ? 'ptag-green' : 'ptag-red' }}"><i class="fas fa-circle"
                            style="font-size:.5rem;"></i> {{ $siswa->status_aktif ? 'Aktif' : 'Non Aktif' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="dash-wrap">

        {{-- ── Statistik Absensi ── --}}
        @php
            $absenStats = [
                'hadir' => $siswa->absenSiswa->where('status', 'hadir')->count(),
                'sakit' => $siswa->absenSiswa->where('status', 'sakit')->count(),
                'izin' => $siswa->absenSiswa->where('status', 'izin')->count(),
                'alfa' => $siswa->absenSiswa->where('status', 'alfa')->count(),
            ];
        @endphp
        <div class="absen-stat-grid" style="margin-top:14px;">
            <div class="absen-stat">
                <div class="as-val" style="color:#15803d;">{{ $absenStats['hadir'] }}</div>
                <div class="as-lbl">Hadir</div>
            </div>
            <div class="absen-stat">
                <div class="as-val" style="color:#1d4ed8;">{{ $absenStats['sakit'] }}</div>
                <div class="as-lbl">Sakit</div>
            </div>
            <div class="absen-stat">
                <div class="as-val" style="color:#a16207;">{{ $absenStats['izin'] }}</div>
                <div class="as-lbl">Izin</div>
            </div>
            <div class="absen-stat">
                <div class="as-val" style="color:#dc2626;">{{ $absenStats['alfa'] }}</div>
                <div class="as-lbl">Alfa</div>
            </div>
        </div>

        {{-- ════ Data Pribadi ════ --}}
        <div class="card card-collapsible" id="card-pribadi">
            <div class="c-head" onclick="toggleCard('card-pribadi')">
                <div class="c-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-id-card"></i></div>
                <h3>Data Pribadi</h3>
                <i class="fas fa-chevron-down card-chevron"></i>
            </div>
            <div class="card-body-collapse">
                <div class="c-body" style="padding:12px 18px;">
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-hashtag"></i> NIS</span><span
                            class="dr-value">{{ $siswa->nis ?: '-' }}</span></div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-id-badge"></i> NISN</span><span
                            class="dr-value">{{ $siswa->nisn }}</span></div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-user"></i> Nama</span><span
                            class="dr-value">{{ $siswa->nama_lengkap }}</span></div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-venus-mars"></i> Kelamin</span><span
                            class="dr-value">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-door-open"></i> Kelas</span><span
                            class="dr-value">{{ $siswa->kelas?->nama_kelas ?? '-' }}</span></div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-graduation-cap"></i>
                            Angkatan</span><span class="dr-value">{{ $siswa->angkatan ?: '-' }}</span></div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-map-pin"></i> Tempat Lahir</span><span
                            class="dr-value">{{ $siswa->tempat_lahir ?: '-' }}</span></div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-birthday-cake"></i> Tgl
                            Lahir</span><span
                            class="dr-value">{{ $siswa->tanggal_lahir?->translatedFormat('d F Y') ?: '-' }}</span></div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-map-marker-alt"></i> Alamat</span><span
                            class="dr-value">{{ $siswa->alamat ?: '-' }}</span></div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-home"></i> Desa</span><span
                            class="dr-value">{{ $siswa->desa ?: '-' }}</span></div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-building"></i> Kelurahan</span><span
                            class="dr-value">{{ $siswa->kelurahan ?: '-' }}</span></div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-city"></i> Kecamatan</span><span
                            class="dr-value">{{ $siswa->kecamatan ?: '-' }}</span></div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-map"></i> Kabupaten</span><span
                            class="dr-value">{{ $siswa->kabupaten ?: '-' }}</span></div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-envelope"></i> Kode Pos</span><span
                            class="dr-value">{{ $siswa->kode_pos ?: '-' }}</span></div>
                </div>
            </div>
        </div>

        {{-- ════ Kontak & Orang Tua ════ --}}
        <div class="card card-collapsible" id="card-kontak">
            <div class="c-head" onclick="toggleCard('card-kontak')">
                <div class="c-icon" style="background:#dcfce7;color:#15803d;"><i class="fas fa-phone-alt"></i></div>
                <h3>Kontak &amp; Orang Tua</h3>
                <i class="fas fa-chevron-down card-chevron"></i>
            </div>
            <div class="card-body-collapse">
                <div class="c-body" style="padding:12px 18px;">
                    <div class="detail-row">
                        <span class="dr-label"><i class="fas fa-mobile-alt"></i> HP Siswa</span>
                        <span class="dr-value">
                            @if ($siswa->no_hp_siswa)
                                <a href="tel:{{ $siswa->no_hp_siswa }}"
                                    style="color:#7c3aed;font-weight:700;">{{ $siswa->no_hp_siswa }}</a>
                            @else
                                —
                            @endif
                        </span>
                    </div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-user-friends"></i> Ortu 1</span><span
                            class="dr-value">{{ $siswa->nama_ortu1 ?: '-' }}</span></div>
                    <div class="detail-row">
                        <span class="dr-label"><i class="fas fa-phone"></i> HP Ortu 1</span>
                        <span class="dr-value">
                            @if ($siswa->no_hp_ortu1)
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $siswa->no_hp_ortu1) }}"
                                    target="_blank" style="color:#15803d;font-weight:700;"><i
                                        class="fab fa-whatsapp"></i> {{ $siswa->no_hp_ortu1 }}</a>
                            @else
                                —
                            @endif
                        </span>
                    </div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-user-friends"></i> Ortu 2</span><span
                            class="dr-value">{{ $siswa->nama_ortu2 ?: '-' }}</span></div>
                    <div class="detail-row">
                        <span class="dr-label"><i class="fas fa-phone"></i> HP Ortu 2</span>
                        <span class="dr-value">
                            @if ($siswa->no_hp_ortu2)
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $siswa->no_hp_ortu2) }}"
                                    target="_blank" style="color:#15803d;font-weight:700;"><i
                                        class="fab fa-whatsapp"></i> {{ $siswa->no_hp_ortu2 }}</a>
                            @else
                                —
                            @endif
                        </span>
                    </div>
                    <div class="detail-row"><span class="dr-label"><i class="fas fa-user-shield"></i> Wali</span><span
                            class="dr-value">{{ $siswa->nama_wali ?: '-' }}</span></div>
                </div>
            </div>
        </div>

        {{-- ════ Riwayat Absensi ════ --}}
        <div class="card card-collapsible" id="card-absensi">
            <div class="c-head" onclick="toggleCard('card-absensi')">
                <div class="c-icon" style="background:#fef3c7;color:#b45309;"><i class="fas fa-clipboard-list"></i></div>
                <h3>Riwayat Absensi</h3>
                <span class="hbadge" id="absen-total-badge">{{ $absenPagination->total() }} total</span>
                <i class="fas fa-chevron-down card-chevron"></i>
            </div>
            <div class="card-body-collapse">
                {{-- Tab bar --}}
                <div style="padding:10px 14px 0;">
                    <div style="display:flex;gap:6px;margin-bottom:10px;">
                        <button type="button" onclick="switchAbsenTab('harian')" id="tab-btn-harian"
                            style="flex:1;padding:7px 0;border-radius:8px;font-size:.75rem;font-weight:700;border:1.5px solid #b45309;background:#b45309;color:#fff;cursor:pointer;transition:.15s;">
                            <i class="fas fa-calendar-day"></i> Absen Harian
                        </button>
                        <button type="button" onclick="switchAbsenTab('event')" id="tab-btn-event"
                            style="flex:1;padding:7px 0;border-radius:8px;font-size:.75rem;font-weight:700;border:1.5px solid #e2e8f0;background:#f8fafc;color:#64748b;cursor:pointer;transition:.15s;">
                            <i class="fas fa-calendar-check"></i> Absen Event
                        </button>
                    </div>
                </div>

                {{-- Tab: Absen Harian --}}
                <div id="tab-harian">
                    <div class="absen-table-wrap" id="absen-harian-wrap">
                        <table class="absen-table" id="tbl-absen-harian">
                            <thead>
                                <tr>
                                    <th style="width:32px;"><input type="checkbox" id="ck-absen-all" style="accent-color:#b45309;width:14px;height:14px;"></th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Jam</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="absen-harian-body">
                                <tr><td colspan="6" style="text-align:center;padding:20px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="absen-harian-pagination" style="padding:8px 14px;display:flex;justify-content:center;gap:5px;flex-wrap:wrap;"></div>
                </div>

                {{-- Tab: Absen Event --}}
                <div id="tab-event" style="display:none;">
                    <div class="absen-table-wrap">
                        <table class="absen-table" id="tbl-absen-event">
                            <thead>
                                <tr>
                                    <th style="width:32px;"><input type="checkbox" id="ck-event-all" style="accent-color:#7c3aed;width:14px;height:14px;"></th>
                                    <th>Nama Event</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="absen-event-body">
                                <tr><td colspan="5" style="text-align:center;padding:20px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="absen-event-pagination" style="padding:8px 14px;display:flex;justify-content:center;gap:5px;flex-wrap:wrap;"></div>
                </div>
            </div>
        </div>

        {{-- ════ Riwayat Pelanggaran ════ --}}
        @php $pelTab = request('pel_tab','tahunini'); @endphp
        <div class="card card-collapsible" id="card-pel" style="margin-top:14px;">
            <div class="c-head" onclick="toggleCard('card-pel')">
                <div class="c-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-exclamation-triangle"></i></div>
                <h3>Riwayat Pelanggaran</h3>
                <span class="hbadge" style="background:#fee2e2;color:#b91c1c;">{{ $pelanggaranRawTahunIni->total() }} TA ini</span>
                <i class="fas fa-chevron-down card-chevron"></i>
            </div>
            <div class="card-body-collapse">
                <div style="padding:10px 14px 0;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;">
                        <div class="absen-stat">
                            <div class="as-val" style="color:#dc2626;">{{ $totalPoinPelanggaranTahunIni }}</div>
                            <div class="as-lbl">Poin TA {{ $tahunAjaranAktif }}</div>
                        </div>
                        <div class="absen-stat">
                            <div class="as-val" style="color:#9f1239;">{{ $totalPoinPelanggaranSemua }}</div>
                            <div class="as-lbl">Poin Semua TA</div>
                        </div>
                    </div>
                    {{-- Tab bar --}}
                    <div class="section-tab-bar">
                        <a href="{{ request()->fullUrlWithQuery(['pel_tab'=>'tahunini','pel_page'=>1]) }}#card-pel"
                            class="section-tab {{ $pelTab!=='semua' ? 'active-red':'' }}">
                            TA {{ $tahunAjaranAktif }} <span class="tab-count">({{ $pelanggaranRawTahunIni->total() }})</span></a>
                        <a href="{{ request()->fullUrlWithQuery(['pel_tab'=>'semua','pel_all_page'=>1]) }}#card-pel"
                            class="section-tab {{ $pelTab==='semua' ? 'active-red':'' }}">
                            Semua Tahun <span class="tab-count">({{ $pelanggaranRawSemua->total() }})</span></a>
                    </div>
                </div>

                @php
                    $pelList = $pelTab !== 'semua' ? $pelanggaranRawTahunIni : $pelanggaranRawSemua;
                    $pelPageParam = $pelTab !== 'semua' ? 'pel_page' : 'pel_all_page';
                @endphp

                {{-- Bulk toolbar --}}
                @canany(['pelanggaran.delete'])
                <div id="pel-bulk-toolbar" style="display:none;padding:8px 14px;background:#fff7ed;border-bottom:1px solid #fed7aa;display:none;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span style="font-size:.78rem;font-weight:700;color:#c2410c;"><span id="pel-sel-count">0</span> dipilih</span>
                    <button type="button" onclick="pelBulkDelete()" id="pel-bulk-del-btn"
                        style="background:#dc2626;color:#fff;border:none;border-radius:7px;padding:6px 12px;font-size:.74rem;font-weight:700;cursor:pointer;">
                        <i class="fas fa-trash"></i> Hapus Terpilih
                    </button>
                    <button type="button" onclick="pelClearAll()"
                        style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;border-radius:7px;padding:6px 10px;font-size:.74rem;cursor:pointer;">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
                @endcanany

                <div class="absen-table-wrap">
                    <table class="absen-table" id="tbl-pelanggaran">
                        <thead>
                            <tr>
                                @canany(['pelanggaran.delete'])
                                <th style="width:32px;"><input type="checkbox" id="ck-pel-all" onchange="pelToggleAll(this)" style="accent-color:#dc2626;width:14px;height:14px;"></th>
                                @endcanany
                                <th>Tanggal</th>
                                @if($pelTab==='semua')<th>TA</th>@endif
                                <th>Pasal / Pelanggaran</th>
                                <th>Poin</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pelList as $p)
                            <tr id="pel-row-{{ $p->idpel }}">
                                @canany(['pelanggaran.delete'])
                                <td><input type="checkbox" class="pel-check" value="{{ $p->idpel }}" onchange="pelOnCheck()" style="accent-color:#dc2626;width:14px;height:14px;"></td>
                                @endcanany
                                <td style="white-space:nowrap;font-size:.78rem;">{{ $p->tgl?->translatedFormat('d F Y') ?? '-' }}</td>
                                @if($pelTab==='semua')
                                <td style="font-size:.72rem;color:#7c3aed;font-weight:600;">{{ $p->tahun_ajaran }}</td>
                                @endif
                                <td style="max-width:180px;">
                                    @if($p->idpasal)<small style="color:#7c3aed;font-weight:700;">{{ $p->idpasal }}</small><br>@endif
                                    <span style="font-size:.78rem;">{{ $p->subPasal?->pasal ?? $p->isi ?? '-' }}</span>
                                </td>
                                <td><span class="status-badge s-alfa">{{ $p->poin }}</span></td>
                                <td style="font-size:.75rem;color:#64748b;max-width:140px;">{{ $p->pelapor ?? '-' }}</td>
                                <td>
                                    @canany(['pelanggaran.delete'])
                                    <button type="button" onclick="pelDelete({{ $p->idpel }}, this)"
                                        style="background:#fee2e2;color:#dc2626;border:1px solid #fecaca;border-radius:6px;padding:4px 8px;font-size:.72rem;cursor:pointer;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcanany
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="empty-cell">
                                <div class="empty-table"><i class="fas fa-check-circle"></i>Tidak ada pelanggaran</div>
                            </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($pelList->hasPages())
                <div style="padding:8px 14px 4px;">
                    {{ $pelList->appends(request()->except($pelPageParam))->links('pagination::azures') }}
                </div>
                @endif
            </div>
        </div>

        {{-- ════ Riwayat Penghargaan ════ --}}
        @php $penTab = request('pen_tab','tahunini'); @endphp
        <div class="card card-collapsible" id="card-pen" style="margin-top:14px;">
            <div class="c-head" onclick="toggleCard('card-pen')">
                <div class="c-icon" style="background:#fef9c3;color:#a16207;"><i class="fas fa-award"></i></div>
                <h3>Riwayat Penghargaan</h3>
                <span class="hbadge" style="background:#fef9c3;color:#a16207;">{{ $penghargaanRawTahunIni->total() }} TA ini</span>
                <i class="fas fa-chevron-down card-chevron"></i>
            </div>
            <div class="card-body-collapse">
                <div style="padding:10px 14px 0;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;">
                        <div class="absen-stat">
                            <div class="as-val" style="color:#a16207;">{{ $totalPoinPenghargaanTahunIni }}</div>
                            <div class="as-lbl">Poin TA {{ $tahunAjaranAktif }}</div>
                        </div>
                        <div class="absen-stat">
                            <div class="as-val" style="color:#854d0e;">{{ $totalPoinPenghargaanSemua }}</div>
                            <div class="as-lbl">Poin Semua TA</div>
                        </div>
                    </div>
                    <div class="section-tab-bar">
                        <a href="{{ request()->fullUrlWithQuery(['pen_tab'=>'tahunini','pen_page'=>1]) }}#card-pen"
                            class="section-tab {{ $penTab!=='semua' ? 'active-amber':'' }}">
                            TA {{ $tahunAjaranAktif }} <span class="tab-count">({{ $penghargaanRawTahunIni->total() }})</span></a>
                        <a href="{{ request()->fullUrlWithQuery(['pen_tab'=>'semua','pen_all_page'=>1]) }}#card-pen"
                            class="section-tab {{ $penTab==='semua' ? 'active-amber':'' }}">
                            Semua Tahun <span class="tab-count">({{ $penghargaanRawSemua->total() }})</span></a>
                    </div>
                </div>

                @php
                    $penList     = $penTab !== 'semua' ? $penghargaanRawTahunIni : $penghargaanRawSemua;
                    $penPageParam = $penTab !== 'semua' ? 'pen_page' : 'pen_all_page';
                    $pendingCount = $penList->getCollection()->where('acc','!=','YA')->filter(fn($x)=>$x->acc!=='YA')->count();
                @endphp

                {{-- Bulk toolbar --}}
                @canany(['penghargaan.delete','penghargaan.approve'])
                <div id="pen-bulk-toolbar" style="display:none;padding:8px 14px;background:#f0fdf4;border-bottom:1px solid #86efac;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span style="font-size:.78rem;font-weight:700;color:#15803d;"><span id="pen-sel-count">0</span> dipilih</span>
                    @can('penghargaan.approve')
                    <button type="button" onclick="penBulkApprove()" id="pen-bulk-acc-btn"
                        style="background:#16a34a;color:#fff;border:none;border-radius:7px;padding:6px 12px;font-size:.74rem;font-weight:700;cursor:pointer;">
                        <i class="fas fa-check-double"></i> ACC Terpilih
                    </button>
                    @endcan
                    @can('penghargaan.delete')
                    <button type="button" onclick="penBulkDelete()" id="pen-bulk-del-btn"
                        style="background:#dc2626;color:#fff;border:none;border-radius:7px;padding:6px 12px;font-size:.74rem;font-weight:700;cursor:pointer;">
                        <i class="fas fa-trash"></i> Hapus Terpilih
                    </button>
                    @endcan
                    <button type="button" onclick="penClearAll()"
                        style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;border-radius:7px;padding:6px 10px;font-size:.74rem;cursor:pointer;">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
                @endcanany

                <div class="absen-table-wrap">
                    <table class="absen-table" id="tbl-penghargaan">
                        <thead>
                            <tr>
                                @canany(['penghargaan.delete','penghargaan.approve'])
                                <th style="width:32px;"><input type="checkbox" id="ck-pen-all" onchange="penToggleAll(this)" style="accent-color:#16a34a;width:14px;height:14px;"></th>
                                @endcanany
                                <th>Tanggal</th>
                                @if($penTab==='semua')<th>TA</th>@endif
                                <th>Pasal / Penghargaan</th>
                                <th>Poin</th>
                                <th>Status ACC</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penList as $p)
                            @php $isAcc = $p->acc === 'YA'; @endphp
                            <tr id="pen-row-{{ $p->idpen }}" style="{{ !$isAcc ? 'background:#fffbeb;' : '' }}">
                                @canany(['penghargaan.delete','penghargaan.approve'])
                                <td><input type="checkbox" class="pen-check" value="{{ $p->idpen }}" data-acc="{{ $isAcc?'1':'0' }}" onchange="penOnCheck()" style="accent-color:#16a34a;width:14px;height:14px;"></td>
                                @endcanany
                                <td style="white-space:nowrap;font-size:.78rem;">{{ $p->tgl?->translatedFormat('d F Y') ?? '-' }}</td>
                                @if($penTab==='semua')
                                <td style="font-size:.72rem;color:#7c3aed;font-weight:600;">{{ $p->tahun_ajaran }}</td>
                                @endif
                                <td style="max-width:180px;">
                                    @if($p->idpasal)<small style="color:#15803d;font-weight:700;">{{ $p->idpasal }}</small><br>@endif
                                    <span style="font-size:.78rem;">{{ $p->subPasal?->pasal ?? $p->isi ?? '-' }}</span>
                                </td>
                                <td><span class="status-badge s-hadir">{{ $p->poin }}</span></td>
                                <td>
                                    @if($isAcc)
                                        <span class="status-badge s-hadir"><i class="fas fa-check"></i> ACC</span>
                                    @else
                                        <span class="status-badge" style="background:#fef3c7;color:#d97706;border:1px solid #fde68a;"><i class="fas fa-clock"></i> Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex;gap:4px;">
                                        @can('penghargaan.approve')
                                        @if(!$isAcc)
                                        <button type="button" onclick="penApprove({{ $p->idpen }}, this)"
                                            style="background:#dcfce7;color:#15803d;border:1px solid #86efac;border-radius:6px;padding:4px 7px;font-size:.7rem;cursor:pointer;" title="ACC">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        @endif
                                        @endcan
                                        @can('penghargaan.delete')
                                        <button type="button" onclick="penDelete({{ $p->idpen }}, this)"
                                            style="background:#fee2e2;color:#dc2626;border:1px solid #fecaca;border-radius:6px;padding:4px 7px;font-size:.7rem;cursor:pointer;" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="empty-cell">
                                <div class="empty-table"><i class="fas fa-star"></i>Belum ada penghargaan</div>
                            </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($penList->hasPages())
                <div style="padding:8px 14px 4px;">
                    {{ $penList->appends(request()->except($penPageParam))->links('pagination::azures') }}
                </div>
                @endif
            </div>
        </div>

        {{-- ════ Surat Panggilan ════ --}}
        @php $suratTab = request('surat_tab','tahunini'); @endphp
        <div class="card card-collapsible" id="card-surat" style="margin-top:14px;">
            <div class="c-head" onclick="toggleCard('card-surat')">
                <div class="c-icon" style="background:#dbeafe;color:#1d4ed8;"><i class="fas fa-envelope-open-text"></i>
                </div>
                <h3>Surat Panggilan</h3>
                <span class="hbadge" style="background:#dbeafe;color:#1d4ed8;">{{ $suratPanggilanTahunIni->total() }} TA
                    ini</span>
                <i class="fas fa-chevron-down card-chevron"></i>
            </div>
            <div class="card-body-collapse">
                <div style="padding:12px 16px 4px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
                        <div class="absen-stat">
                            <div class="as-val" style="color:#1d4ed8;">{{ $suratPanggilanTahunIni->total() }}</div>
                            <div class="as-lbl">Surat TA {{ $tahunAjaranAktif }}</div>
                        </div>
                        <div class="absen-stat">
                            <div class="as-val" style="color:#1e3a8a;">{{ $suratPanggilanSemua->total() }}</div>
                            <div class="as-lbl">Total Semua TA</div>
                        </div>
                    </div>
                    <div class="section-tab-bar">
                        <a href="{{ request()->fullUrlWithQuery(['surat_tab' => 'tahunini', 'surat_page' => 1]) }}#card-surat"
                            class="section-tab {{ $suratTab !== 'semua' ? 'active-blue' : '' }}">TA {{ $tahunAjaranAktif }}
                            <span class="tab-count">({{ $suratPanggilanTahunIni->total() }})</span></a>
                        <a href="{{ request()->fullUrlWithQuery(['surat_tab' => 'semua', 'surat_all_page' => 1]) }}#card-surat"
                            class="section-tab {{ $suratTab === 'semua' ? 'active-blue' : '' }}">Semua Tahun <span
                                class="tab-count">({{ $suratPanggilanSemua->total() }})</span></a>
                    </div>
                </div>
                @if ($suratTab !== 'semua')
                    <div class="absen-table-wrap">
                        <table class="absen-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No. Surat</th>
                                    <th>Panggilan ke-</th>
                                    <th>Keperluan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($suratPanggilanTahunIni as $s)
                                    <tr>
                                        <td data-label="Tanggal" style="white-space:nowrap;">
                                            {{ $s->tanggal_surat?->format('d/m/Y') ?: '-' }}</td>
                                        <td data-label="No. Surat" style="font-size:.75rem;color:#475569;">
                                            {{ $s->nomor_surat ?: '-' }}</td>
                                        <td data-label="Panggilan ke-" style="text-align:center;"><span
                                                class="status-badge s-sakit">{{ $s->panggilan_ke ?? '-' }}</span></td>
                                        <td data-label="Keperluan"
                                            style="max-width:180px;word-break:break-word;font-size:.78rem;">
                                            {{ $s->keperluan ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="empty-cell">
                                            <div class="empty-table"><i class="fas fa-envelope"></i>Tidak ada surat
                                                panggilan TA {{ $tahunAjaranAktif }}</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($suratPanggilanTahunIni->hasPages())
                        <div style="padding:10px 16px 6px;">
                            {{ $suratPanggilanTahunIni->appends(request()->except('surat_page'))->links('pagination::azures') }}
                        </div>
                    @endif
                @else
                    <div class="absen-table-wrap">
                        <table class="absen-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>TA</th>
                                    <th>No. Surat</th>
                                    <th>Ke-</th>
                                    <th>Keperluan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($suratPanggilanSemua as $s)
                                    <tr>
                                        <td data-label="Tanggal" style="white-space:nowrap;">
                                            {{ $s->tanggal_surat?->format('d/m/Y') ?: '-' }}</td>
                                        <td data-label="TA" style="font-size:.72rem;color:#7c3aed;font-weight:600;">
                                            {{ $s->tahun_ajaran }}</td>
                                        <td data-label="No. Surat" style="font-size:.72rem;color:#475569;">
                                            {{ $s->nomor_surat ?: '-' }}</td>
                                        <td data-label="Ke-" style="text-align:center;"><span
                                                class="status-badge s-sakit">{{ $s->panggilan_ke ?? '-' }}</span></td>
                                        <td data-label="Keperluan"
                                            style="max-width:160px;word-break:break-word;font-size:.75rem;">
                                            {{ $s->keperluan ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="empty-cell">
                                            <div class="empty-table"><i class="fas fa-envelope"></i>Belum ada riwayat
                                                surat panggilan</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($suratPanggilanSemua->hasPages())
                        <div style="padding:10px 16px 6px;">
                            {{ $suratPanggilanSemua->appends(request()->except('surat_all_page'))->links('pagination::azures') }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

    </div>{{-- /dash-wrap --}}

    {{-- ── Action Bar ── --}}
    <div class="action-bar">
        <a href="{{ route('siswa.index') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
        <a href="{{ route('siswa.edit', $siswa) }}" class="ab-btn ab-btn-edit"><i class="fas fa-pen"></i> <span
                class="ab-label">Edit</span></a>
        <a href="{{ route('siswa.export-cv', $siswa) }}" target="_blank" class="ab-btn ab-btn-pdf"><i
                class="fas fa-file-pdf"></i> <span class="ab-label">Export CV</span></a>
        <button type="button" class="ab-btn ab-btn-reset-pw" onclick="confirmResetPassword()"><i
                class="fas fa-key"></i></button>
        <button type="button" class="ab-btn ab-btn-delete"
            onclick="confirmDelete('{{ route('siswa.destroy', $siswa) }}')"><i class="fas fa-trash-alt"></i></button>
    </div>

    <form id="deleteForm" method="POST" action="{{ route('siswa.destroy', $siswa) }}" style="display:none;">@csrf
        @method('DELETE')</form>
    <form id="resetPwForm" method="POST" action="{{ route('siswa.reset-password', $siswa) }}" style="display:none;">
        @csrf</form>

    {{-- ══ Modal Edit Absen Harian ══ --}}
    <div id="overlay-edit-absen" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:9000;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:16px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden;">
            <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;">
                <h4 style="font-size:.9rem;font-weight:800;margin:0;color:#0f172a;"><i class="fas fa-pen" style="color:#b45309;margin-right:6px;"></i>Edit Absen Harian</h4>
                <button type="button" onclick="closeAbsenModal()" style="background:#f1f5f9;border:none;border-radius:8px;padding:6px 10px;cursor:pointer;color:#64748b;"><i class="fas fa-times"></i></button>
            </div>
            <form id="form-edit-absen" onsubmit="submitEditAbsen(event)">
                <div style="padding:16px 18px;">
                    <div style="margin-bottom:12px;">
                        <label style="font-size:.76rem;font-weight:700;color:#475569;display:block;margin-bottom:5px;">Tanggal</label>
                        <input type="text" id="edit-absen-tanggal" readonly style="width:100%;padding:8px 10px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;font-size:.82rem;box-sizing:border-box;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-size:.76rem;font-weight:700;color:#475569;display:block;margin-bottom:5px;">Status <span style="color:#ef4444;">*</span></label>
                        <select id="edit-absen-status" name="status_masuk" style="width:100%;padding:8px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:.82rem;background:#fff;box-sizing:border-box;">
                            <option value="hadir">Hadir</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="sakit">Sakit</option>
                            <option value="izin">Izin</option>
                            <option value="alfa">Alfa</option>
                        </select>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
                        <div>
                            <label style="font-size:.76rem;font-weight:700;color:#475569;display:block;margin-bottom:5px;">Jam Masuk</label>
                            <input type="time" id="edit-absen-jam-masuk" name="jam_masuk" style="width:100%;padding:8px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:.82rem;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:.76rem;font-weight:700;color:#475569;display:block;margin-bottom:5px;">Jam Pulang</label>
                            <input type="time" id="edit-absen-jam-pulang" name="jam_pulang" style="width:100%;padding:8px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:.82rem;box-sizing:border-box;">
                        </div>
                    </div>
                    <div>
                        <label style="font-size:.76rem;font-weight:700;color:#475569;display:block;margin-bottom:5px;">Catatan</label>
                        <textarea id="edit-absen-catatan" name="catatan" rows="2" style="width:100%;padding:8px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:.82rem;box-sizing:border-box;resize:vertical;"></textarea>
                    </div>
                </div>
                <div style="padding:12px 18px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" onclick="closeAbsenModal()" style="background:#f1f5f9;border:none;border-radius:8px;padding:9px 16px;font-size:.8rem;font-weight:700;color:#475569;cursor:pointer;">Batal</button>
                    <button type="submit" id="edit-absen-submit" style="background:#b45309;border:none;border-radius:8px;padding:9px 18px;font-size:.8rem;font-weight:800;color:#fff;cursor:pointer;">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ Modal Edit Absen Event ══ --}}
    <div id="overlay-edit-event" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:9000;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:16px;width:100%;max-width:380px;box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden;">
            <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;">
                <h4 style="font-size:.9rem;font-weight:800;margin:0;color:#0f172a;"><i class="fas fa-calendar-check" style="color:#7c3aed;margin-right:6px;"></i>Edit Absen Event</h4>
                <button type="button" onclick="closeEventModal()" style="background:#f1f5f9;border:none;border-radius:8px;padding:6px 10px;cursor:pointer;color:#64748b;"><i class="fas fa-times"></i></button>
            </div>
            <form id="form-edit-event" onsubmit="submitEditEvent(event)">
                <div style="padding:16px 18px;">
                    <div style="margin-bottom:12px;">
                        <label style="font-size:.76rem;font-weight:700;color:#475569;display:block;margin-bottom:5px;">Event</label>
                        <input type="text" id="edit-event-nama" readonly style="width:100%;padding:8px 10px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;font-size:.82rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:.76rem;font-weight:700;color:#475569;display:block;margin-bottom:8px;">Status Kehadiran</label>
                        <div style="display:flex;gap:8px;">
                            <label style="flex:1;display:flex;align-items:center;gap:7px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;cursor:pointer;font-size:.8rem;font-weight:700;">
                                <input type="radio" name="status" value="hadir" id="ev-hadir" style="accent-color:#16a34a;">
                                <span style="color:#15803d;"><i class="fas fa-check-circle"></i> Hadir</span>
                            </label>
                            <label style="flex:1;display:flex;align-items:center;gap:7px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;cursor:pointer;font-size:.8rem;font-weight:700;">
                                <input type="radio" name="status" value="tidak_hadir" id="ev-tidak-hadir" style="accent-color:#dc2626;">
                                <span style="color:#b91c1c;"><i class="fas fa-times-circle"></i> Tidak Hadir</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div style="padding:12px 18px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" onclick="closeEventModal()" style="background:#f1f5f9;border:none;border-radius:8px;padding:9px 16px;font-size:.8rem;font-weight:700;color:#475569;cursor:pointer;">Batal</button>
                    <button type="submit" style="background:#7c3aed;border:none;border-radius:8px;padding:9px 18px;font-size:.8rem;font-weight:800;color:#fff;cursor:pointer;">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    /* ── Config ─────────────────────────────────────── */
    var SISWA_ID       = {{ $siswa->id }};
    var CSRF           = document.querySelector('meta[name="csrf-token"]').content;
    var URL_ABSEN_HARIAN = '{{ route("siswa.absen-harian", $siswa) }}';
    var URL_ABSEN_EVENT  = '{{ route("siswa.absen-event",  $siswa) }}';
    var URL_PEL_DELETE_BASE  = '{{ url("siswa/{$siswa->id}/pelanggaran") }}';
    var URL_PEL_BULK_DELETE  = '{{ route("siswa.pelanggaran.bulk-delete", $siswa) }}';
    var URL_PEN_DELETE_BASE  = '{{ url("siswa/{$siswa->id}/penghargaan") }}';
    var URL_PEN_BULK_DELETE  = '{{ route("siswa.penghargaan.bulk-delete", $siswa) }}';
    var URL_PEN_BULK_APPROVE = '{{ route("siswa.penghargaan.bulk-approve", $siswa) }}';
    var URL_PEN_APPROVE_BASE = '{{ url("siswa/{$siswa->id}/penghargaan") }}';

    /* ── Card toggle ────────────────────────────────── */
    function toggleCard(id) {
        var card = document.getElementById(id);
        card.classList.toggle('is-open');
        sessionStorage.setItem('card_' + id, card.classList.contains('is-open') ? '1' : '0');
        // Lazy-load absen harian saat card pertama kali dibuka
        if (id === 'card-absensi' && card.classList.contains('is-open') && !window._absenHarianLoaded) {
            loadAbsenHarian(1);
            window._absenHarianLoaded = true;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.card-collapsible').forEach(function(card) {
            if (sessionStorage.getItem('card_' + card.id) === '1') card.classList.add('is-open');
        });
        var p = new URLSearchParams(window.location.search);
        if (p.has('pel_page') || p.has('pel_all_page') || p.has('pel_tab')) document.getElementById('card-pel')?.classList.add('is-open');
        if (p.has('pen_page') || p.has('pen_all_page') || p.has('pen_tab')) document.getElementById('card-pen')?.classList.add('is-open');
        if (p.has('surat_page') || p.has('surat_all_page') || p.has('surat_tab')) document.getElementById('card-surat')?.classList.add('is-open');
        if (p.has('absen_page')) document.getElementById('card-absensi')?.classList.add('is-open');
        var header = document.querySelector('.header-auto-show');
        if (header) header.classList.add('header-active');

        // Auto-load absen harian jika card sudah open saat DOM ready
        if (document.getElementById('card-absensi')?.classList.contains('is-open')) {
            loadAbsenHarian(1);
            window._absenHarianLoaded = true;
        }
    });

    /* ══════════════════════════════════════════════
       TAB ABSEN
    ══════════════════════════════════════════════ */
    var _activeAbsenTab = 'harian';
    function switchAbsenTab(tab) {
        _activeAbsenTab = tab;
        document.getElementById('tab-harian').style.display = tab === 'harian' ? '' : 'none';
        document.getElementById('tab-event').style.display  = tab === 'event'  ? '' : 'none';
        document.getElementById('tab-btn-harian').style.background    = tab === 'harian' ? '#b45309' : '#f8fafc';
        document.getElementById('tab-btn-harian').style.color         = tab === 'harian' ? '#fff' : '#64748b';
        document.getElementById('tab-btn-harian').style.borderColor   = tab === 'harian' ? '#b45309' : '#e2e8f0';
        document.getElementById('tab-btn-event').style.background     = tab === 'event'  ? '#7c3aed' : '#f8fafc';
        document.getElementById('tab-btn-event').style.color          = tab === 'event'  ? '#fff' : '#64748b';
        document.getElementById('tab-btn-event').style.borderColor    = tab === 'event'  ? '#7c3aed' : '#e2e8f0';
        if (tab === 'harian' && !window._absenHarianLoaded) { loadAbsenHarian(1); window._absenHarianLoaded = true; }
        if (tab === 'event'  && !window._absenEventLoaded)  { loadAbsenEvent(1);  window._absenEventLoaded  = true; }
    }

    /* ── Absen Harian AJAX ──────────────────────── */
    var _absenHarianPage = 1;
    function loadAbsenHarian(page) {
        _absenHarianPage = page;
        fetch(URL_ABSEN_HARIAN + '?absen_page=' + page, {headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}})
        .then(function(r){return r.json();})
        .then(function(d){renderAbsenHarian(d);})
        .catch(function(){ document.getElementById('absen-harian-body').innerHTML = '<tr><td colspan="6" style="text-align:center;color:#dc2626;padding:16px;">Gagal memuat data.</td></tr>'; });
    }

    function statusBadgeHtml(s) {
        var map = {hadir:'s-hadir',terlambat:'s-hadir',sakit:'s-sakit',izin:'s-izin',alfa:'s-alfa'};
        var cls = map[s] || 's-alfa';
        return '<span class="status-badge ' + cls + '">' + (s.charAt(0).toUpperCase()+s.slice(1)) + '</span>';
    }

    function renderAbsenHarian(d) {
        var body = document.getElementById('absen-harian-body');
        if (!d.data || d.data.length === 0) {
            body.innerHTML = '<tr><td colspan="6" class="empty-cell"><div class="empty-table"><i class="fas fa-clipboard"></i>Belum ada data absensi</div></td></tr>';
        } else {
            body.innerHTML = d.data.map(function(row) {
                var jam = (row.jam_masuk||'-') + (row.jam_pulang ? ' / '+row.jam_pulang : '');
                return '<tr id="ah-row-'+row.id+'">' +
                    '<td><input type="checkbox" class="ck-ah" value="'+row.id+'" style="accent-color:#b45309;width:14px;height:14px;"></td>' +
                    '<td style="white-space:nowrap;font-size:.78rem;">'+escHtml(row.tanggal)+'</td>' +
                    '<td>'+statusBadgeHtml(row.status)+'</td>' +
                    '<td style="font-size:.75rem;color:#64748b;">'+jam+'</td>' +
                    '<td style="font-size:.75rem;color:#64748b;max-width:120px;">'+escHtml(row.catatan||'-')+'</td>' +
                    '<td><button type="button" onclick="openAbsenModal('+JSON.stringify(row)+')" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;border-radius:6px;padding:4px 8px;font-size:.72rem;cursor:pointer;"><i class="fas fa-pen"></i></button></td>' +
                    '</tr>';
            }).join('');
        }
        renderPagination('absen-harian-pagination', d.current_page, d.last_page, loadAbsenHarian);
        document.getElementById('absen-total-badge').textContent = d.total + ' total';
    }

    /* ── Absen Event AJAX ───────────────────────── */
    var _absenEventPage = 1;
    function loadAbsenEvent(page) {
        _absenEventPage = page;
        fetch(URL_ABSEN_EVENT + '?page=' + page, {headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}})
        .then(function(r){return r.json();})
        .then(function(d){renderAbsenEvent(d);})
        .catch(function(){ document.getElementById('absen-event-body').innerHTML = '<tr><td colspan="5" style="text-align:center;color:#dc2626;padding:16px;">Gagal memuat data.</td></tr>'; });
    }

    function renderAbsenEvent(d) {
        var body = document.getElementById('absen-event-body');
        if (!d.data || d.data.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="empty-cell"><div class="empty-table"><i class="fas fa-calendar-times"></i>Belum ada data absen event</div></td></tr>';
        } else {
            body.innerHTML = d.data.map(function(row) {
                var badge = row.hadir
                    ? '<span class="status-badge s-hadir"><i class="fas fa-check-circle"></i> Hadir</span>'
                    : '<span class="status-badge s-alfa"><i class="fas fa-times-circle"></i> Tidak Hadir</span>';
                return '<tr id="ae-row-'+row.id+'">' +
                    '<td><input type="checkbox" class="ck-ae" value="'+row.id+'" style="accent-color:#7c3aed;width:14px;height:14px;"></td>' +
                    '<td style="font-size:.78rem;font-weight:600;">'+escHtml(row.nama_event)+'</td>' +
                    '<td style="white-space:nowrap;font-size:.78rem;">'+escHtml(row.tanggal)+'</td>' +
                    '<td>'+badge+'</td>' +
                    '<td><button type="button" onclick="openEventModal('+JSON.stringify(row)+')" style="background:#ede9fe;color:#7c3aed;border:1px solid #ddd6fe;border-radius:6px;padding:4px 8px;font-size:.72rem;cursor:pointer;"><i class="fas fa-pen"></i></button></td>' +
                    '</tr>';
            }).join('');
        }
        renderPagination('absen-event-pagination', d.current_page, d.last_page, loadAbsenEvent);
    }

    /* ── Pagination renderer ────────────────────── */
    function renderPagination(containerId, current, last, loadFn) {
        var el = document.getElementById(containerId);
        if (!el) return;
        if (last <= 1) { el.innerHTML = ''; return; }
        var btnBase = 'display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 8px;border-radius:7px;font-size:.78rem;font-weight:600;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;cursor:pointer;margin:2px;transition:background .12s;';
        var activeAdd = 'background:#b45309!important;border-color:#b45309!important;color:#fff!important;';
        var html = '';
        // Prev
        if (current > 1) {
            html += '<button type="button" style="'+btnBase+'" onclick="(window._paginateFns[\''+containerId+'\'])('+( current-1)+')">&lsaquo;</button>';
        } else {
            html += '<button type="button" disabled style="'+btnBase+'opacity:.4;">&lsaquo;</button>';
        }
        for (var i = Math.max(1,current-2); i <= Math.min(last,current+2); i++) {
            html += '<button type="button" style="'+btnBase+(i===current?activeAdd:'')+'" onclick="(window._paginateFns[\''+containerId+'\'])('+i+')">'+i+'</button>';
        }
        // Next
        if (current < last) {
            html += '<button type="button" style="'+btnBase+'" onclick="(window._paginateFns[\''+containerId+'\'])('+(current+1)+')">&rsaquo;</button>';
        } else {
            html += '<button type="button" disabled style="'+btnBase+'opacity:.4;">&rsaquo;</button>';
        }
        el.innerHTML = html;
    }

    // Register pagination callbacks by container id
    window._paginateFns = {
        'absen-harian-pagination': function(p){ loadAbsenHarian(p); },
        'absen-event-pagination':  function(p){ loadAbsenEvent(p); },
    };

    /* ── Modal Absen Harian ─────────────────────── */
    var _currentAbsenRow = null;
    function openAbsenModal(row) {
        _currentAbsenRow = row;
        document.getElementById('edit-absen-tanggal').value   = row.tanggal;
        document.getElementById('edit-absen-status').value    = row.status;
        document.getElementById('edit-absen-jam-masuk').value = row.jam_masuk || '';
        document.getElementById('edit-absen-jam-pulang').value= row.jam_pulang || '';
        document.getElementById('edit-absen-catatan').value   = row.catatan || '';
        var ov = document.getElementById('overlay-edit-absen');
        ov.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeAbsenModal() {
        document.getElementById('overlay-edit-absen').style.display = 'none';
        document.body.style.overflow = '';
        _currentAbsenRow = null;
    }

    function submitEditAbsen(e) {
        e.preventDefault();
        if (!_currentAbsenRow) return;
        var btn = document.getElementById('edit-absen-submit');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        var data = {
            status_masuk: document.getElementById('edit-absen-status').value,
            jam_masuk:    document.getElementById('edit-absen-jam-masuk').value || null,
            jam_pulang:   document.getElementById('edit-absen-jam-pulang').value || null,
            catatan:      document.getElementById('edit-absen-catatan').value || null,
            _method:      'PATCH',
        };
        fetch(_currentAbsenRow.edit_url, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json'},
            body: JSON.stringify(data),
        })
        .then(function(r){ return r.json(); })
        .then(function(d) {
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Simpan';
            if (d.success) {
                closeAbsenModal();
                // Refresh row in table
                var row = d.row;
                var tr = document.getElementById('ah-row-' + row.id);
                if (tr) {
                    var jam = (row.jam_masuk||'-') + (row.jam_pulang ? ' / '+row.jam_pulang : '');
                    tr.cells[1].innerHTML = escHtml(row.tanggal);
                    tr.cells[2].innerHTML = statusBadgeHtml(row.status);
                    tr.cells[3].innerHTML = '<span style="font-size:.75rem;color:#64748b;">'+jam+'</span>';
                    tr.cells[4].innerHTML = '<span style="font-size:.75rem;color:#64748b;">'+escHtml(row.catatan||'-')+'</span>';
                    tr.cells[5].innerHTML = '<button type="button" onclick="openAbsenModal('+JSON.stringify(row)+')" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;border-radius:6px;padding:4px 8px;font-size:.72rem;cursor:pointer;"><i class="fas fa-pen"></i></button>';
                }
                Swal.fire({icon:'success',title:'Tersimpan',text:'Absensi berhasil diperbarui.',timer:2000,timerProgressBar:true,showConfirmButton:false});
            } else {
                Swal.fire({icon:'error',title:'Gagal',text:d.message||'Terjadi kesalahan.',confirmButtonColor:'#dc2626'});
            }
        })
        .catch(function() {
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Simpan';
            Swal.fire({icon:'error',title:'Error',text:'Koneksi gagal.',confirmButtonColor:'#dc2626'});
        });
    }

    document.getElementById('overlay-edit-absen').addEventListener('click', function(e){ if(e.target===this) closeAbsenModal(); });

    /* ── Modal Absen Event ──────────────────────── */
    var _currentEventRow = null;
    function openEventModal(row) {
        _currentEventRow = row;
        document.getElementById('edit-event-nama').value = row.nama_event;
        document.getElementById('ev-hadir').checked       = row.hadir;
        document.getElementById('ev-tidak-hadir').checked = !row.hadir;
        var ov = document.getElementById('overlay-edit-event');
        ov.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeEventModal() {
        document.getElementById('overlay-edit-event').style.display = 'none';
        document.body.style.overflow = '';
        _currentEventRow = null;
    }

    function submitEditEvent(e) {
        e.preventDefault();
        if (!_currentEventRow) return;
        var status = document.querySelector('[name="status"]:checked')?.value;
        if (!status) return;
        fetch(_currentEventRow.edit_url, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json'},
            body: JSON.stringify({status: status, _method: 'PATCH'}),
        })
        .then(function(r){ return r.json(); })
        .then(function(d) {
            if (d.success) {
                closeEventModal();
                var row = d.row;
                var tr = document.getElementById('ae-row-' + row.id);
                if (tr) {
                    var badge = row.hadir
                        ? '<span class="status-badge s-hadir"><i class="fas fa-check-circle"></i> Hadir</span>'
                        : '<span class="status-badge s-alfa"><i class="fas fa-times-circle"></i> Tidak Hadir</span>';
                    tr.cells[3].innerHTML = badge;
                    tr.cells[4].innerHTML = '<button type="button" onclick="openEventModal('+JSON.stringify(row)+')" style="background:#ede9fe;color:#7c3aed;border:1px solid #ddd6fe;border-radius:6px;padding:4px 8px;font-size:.72rem;cursor:pointer;"><i class="fas fa-pen"></i></button>';
                }
                Swal.fire({icon:'success',title:'Tersimpan',timer:2000,timerProgressBar:true,showConfirmButton:false});
            } else {
                Swal.fire({icon:'error',title:'Gagal',text:d.message||'Terjadi kesalahan.',confirmButtonColor:'#dc2626'});
            }
        });
    }

    document.getElementById('overlay-edit-event').addEventListener('click', function(e){ if(e.target===this) closeEventModal(); });

    /* ══════════════════════════════════════════════
       PELANGGARAN — Delete & Bulk
    ══════════════════════════════════════════════ */
    function pelDelete(id, btn) {
        Swal.fire({icon:'warning',title:'Hapus Pelanggaran?',text:'Data dan transaksinya akan dihapus.',showCancelButton:true,confirmButtonColor:'#dc2626',cancelButtonColor:'#6b7280',confirmButtonText:'Ya, Hapus',cancelButtonText:'Batal'})
        .then(function(r) {
            if (!r.isConfirmed) return;
            fetch(URL_PEL_DELETE_BASE+'/'+id, {
                method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json','Content-Type':'application/json'},
                body: JSON.stringify({_method:'DELETE'})
            })
            .then(function(r){return r.json();})
            .then(function(d) {
                if (d.success) {
                    var row = document.getElementById('pel-row-'+id);
                    if (row) row.remove();
                    Swal.fire({icon:'success',title:'Dihapus',timer:1500,showConfirmButton:false});
                } else {
                    Swal.fire({icon:'error',title:'Gagal',text:d.error||'Error',confirmButtonColor:'#dc2626'});
                }
            });
        });
    }

    var _pelSelected = {};
    function pelOnCheck() {
        _pelSelected = {};
        document.querySelectorAll('.pel-check:checked').forEach(function(cb){ _pelSelected[cb.value]=true; });
        var count = Object.keys(_pelSelected).length;
        var toolbar = document.getElementById('pel-bulk-toolbar');
        if (toolbar) toolbar.style.display = count>0 ? 'flex' : 'none';
        var el = document.getElementById('pel-sel-count'); if(el) el.textContent = count;
        var all = document.getElementById('ck-pel-all');
        if (all) {
            var total = document.querySelectorAll('.pel-check').length;
            all.checked = count===total && total>0;
            all.indeterminate = count>0 && count<total;
        }
    }

    function pelToggleAll(cb) {
        document.querySelectorAll('.pel-check').forEach(function(c){ c.checked=cb.checked; });
        pelOnCheck();
    }

    function pelClearAll() {
        document.querySelectorAll('.pel-check').forEach(function(c){ c.checked=false; });
        _pelSelected = {};
        var toolbar = document.getElementById('pel-bulk-toolbar');
        if (toolbar) toolbar.style.display='none';
        var all = document.getElementById('ck-pel-all'); if(all){ all.checked=false; all.indeterminate=false; }
    }

    function pelBulkDelete() {
        var ids = Object.keys(_pelSelected);
        if (!ids.length) return;
        Swal.fire({icon:'warning',title:'Hapus '+ids.length+' Pelanggaran?',text:'Semua data dan transaksinya akan dihapus.',showCancelButton:true,confirmButtonColor:'#dc2626',cancelButtonColor:'#6b7280',confirmButtonText:'Ya, Hapus Semua',cancelButtonText:'Batal'})
        .then(function(r) {
            if (!r.isConfirmed) return;
            fetch(URL_PEL_BULK_DELETE, {
                method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json','Content-Type':'application/json'},
                body: JSON.stringify({ids:ids})
            })
            .then(function(r){return r.json();})
            .then(function(d) {
                if (d.success) {
                    ids.forEach(function(id){ var row=document.getElementById('pel-row-'+id); if(row)row.remove(); });
                    pelClearAll();
                    Swal.fire({icon:'success',title:d.deleted+' data dihapus',timer:2000,showConfirmButton:false});
                } else {
                    Swal.fire({icon:'error',title:'Gagal',text:d.error||'Error',confirmButtonColor:'#dc2626'});
                }
            });
        });
    }

    /* ══════════════════════════════════════════════
       PENGHARGAAN — Delete, Approve & Bulk
    ══════════════════════════════════════════════ */
    function penApprove(id, btn) {
        Swal.fire({icon:'question',title:'ACC Penghargaan?',text:'Penghargaan akan disetujui dan masuk ke transaksi poin.',showCancelButton:true,confirmButtonColor:'#16a34a',cancelButtonColor:'#6b7280',confirmButtonText:'Ya, ACC',cancelButtonText:'Batal'})
        .then(function(r) {
            if (!r.isConfirmed) return;
            fetch(URL_PEN_APPROVE_BASE+'/'+id+'/approve', {
                method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json','Content-Type':'application/json'},
                body: JSON.stringify({})
            })
            .then(function(r){return r.json();})
            .then(function(d) {
                if (d.success) {
                    var row = document.getElementById('pen-row-'+id);
                    if (row) {
                        row.style.background = '';
                        // Update status cell
                        var statusCell = row.querySelector('td:nth-last-child(2)');
                        if(statusCell) statusCell.innerHTML = '<span class="status-badge s-hadir"><i class="fas fa-check"></i> ACC</span>';
                        // Remove approve button
                        if(btn) btn.remove();
                    }
                    Swal.fire({icon:'success',title:'Berhasil di-ACC',timer:1800,showConfirmButton:false});
                } else {
                    Swal.fire({icon:'error',title:'Gagal',text:d.error||'Error',confirmButtonColor:'#dc2626'});
                }
            });
        });
    }

    function penDelete(id, btn) {
        Swal.fire({icon:'warning',title:'Hapus Penghargaan?',text:'Data dan transaksinya akan dihapus.',showCancelButton:true,confirmButtonColor:'#dc2626',cancelButtonColor:'#6b7280',confirmButtonText:'Ya, Hapus',cancelButtonText:'Batal'})
        .then(function(r) {
            if (!r.isConfirmed) return;
            fetch(URL_PEN_DELETE_BASE+'/'+id, {
                method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json','Content-Type':'application/json'},
                body: JSON.stringify({_method:'DELETE'})
            })
            .then(function(r){return r.json();})
            .then(function(d) {
                if (d.success) {
                    var row = document.getElementById('pen-row-'+id);
                    if (row) row.remove();
                    Swal.fire({icon:'success',title:'Dihapus',timer:1500,showConfirmButton:false});
                } else {
                    Swal.fire({icon:'error',title:'Gagal',text:d.error||'Error',confirmButtonColor:'#dc2626'});
                }
            });
        });
    }

    var _penSelected = {};
    function penOnCheck() {
        _penSelected = {};
        document.querySelectorAll('.pen-check:checked').forEach(function(cb){ _penSelected[cb.value]=cb.dataset.acc; });
        var count = Object.keys(_penSelected).length;
        var toolbar = document.getElementById('pen-bulk-toolbar');
        if (toolbar) toolbar.style.display = count>0 ? 'flex' : 'none';
        var el = document.getElementById('pen-sel-count'); if(el) el.textContent = count;
        var all = document.getElementById('ck-pen-all');
        if (all) {
            var total = document.querySelectorAll('.pen-check').length;
            all.checked = count===total && total>0;
            all.indeterminate = count>0 && count<total;
        }
    }

    function penToggleAll(cb) {
        document.querySelectorAll('.pen-check').forEach(function(c){ c.checked=cb.checked; });
        penOnCheck();
    }

    function penClearAll() {
        document.querySelectorAll('.pen-check').forEach(function(c){ c.checked=false; });
        _penSelected = {};
        var toolbar = document.getElementById('pen-bulk-toolbar');
        if(toolbar) toolbar.style.display='none';
        var all = document.getElementById('ck-pen-all'); if(all){ all.checked=false; all.indeterminate=false; }
    }

    function penBulkApprove() {
        var ids = Object.keys(_penSelected).filter(function(id){ return _penSelected[id]!=='1'; });
        if (!ids.length) { Swal.fire({icon:'info',title:'Tidak ada yang bisa di-ACC',text:'Semua yang dipilih sudah di-ACC.',confirmButtonColor:'#16a34a'}); return; }
        Swal.fire({icon:'question',title:'ACC '+ids.length+' Penghargaan?',showCancelButton:true,confirmButtonColor:'#16a34a',cancelButtonColor:'#6b7280',confirmButtonText:'Ya, ACC',cancelButtonText:'Batal'})
        .then(function(r) {
            if (!r.isConfirmed) return;
            fetch(URL_PEN_BULK_APPROVE, {
                method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json','Content-Type':'application/json'},
                body: JSON.stringify({ids:ids})
            })
            .then(function(r){return r.json();})
            .then(function(d) {
                if (d.success) {
                    ids.forEach(function(id) {
                        var row = document.getElementById('pen-row-'+id);
                        if (row) {
                            row.style.background='';
                            var sc = row.querySelector('td:nth-last-child(2)');
                            if(sc) sc.innerHTML='<span class="status-badge s-hadir"><i class="fas fa-check"></i> ACC</span>';
                            var accBtn = row.querySelector('button[onclick*="penApprove"]');
                            if(accBtn) accBtn.remove();
                        }
                    });
                    penClearAll();
                    Swal.fire({icon:'success',title:d.approved+' berhasil di-ACC',timer:2000,showConfirmButton:false});
                } else {
                    Swal.fire({icon:'error',title:'Gagal',text:d.error||'Error',confirmButtonColor:'#dc2626'});
                }
            });
        });
    }

    function penBulkDelete() {
        var ids = Object.keys(_penSelected);
        if (!ids.length) return;
        Swal.fire({icon:'warning',title:'Hapus '+ids.length+' Penghargaan?',text:'Semua data dan transaksinya akan dihapus.',showCancelButton:true,confirmButtonColor:'#dc2626',cancelButtonColor:'#6b7280',confirmButtonText:'Ya, Hapus Semua',cancelButtonText:'Batal'})
        .then(function(r) {
            if (!r.isConfirmed) return;
            fetch(URL_PEN_BULK_DELETE, {
                method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json','Content-Type':'application/json'},
                body: JSON.stringify({ids:ids})
            })
            .then(function(r){return r.json();})
            .then(function(d) {
                if (d.success) {
                    ids.forEach(function(id){ var row=document.getElementById('pen-row-'+id); if(row)row.remove(); });
                    penClearAll();
                    Swal.fire({icon:'success',title:d.deleted+' data dihapus',timer:2000,showConfirmButton:false});
                } else {
                    Swal.fire({icon:'error',title:'Gagal',text:d.error||'Error',confirmButtonColor:'#dc2626'});
                }
            });
        });
    }

    /* ── Helpers ────────────────────────────────── */
    function escHtml(str) {
        return (str||'').toString().replace(/[&<>"']/g,function(m){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]);});
    }

    function confirmDelete(url) {
        if (typeof Swal === 'undefined') { if (!confirm('Yakin menghapus siswa {{ $siswa->nama_lengkap }}?')) return; document.getElementById('deleteForm').submit(); return; }
        Swal.fire({title:'Hapus Siswa?',html:'Yakin menghapus <strong>{{ $siswa->nama_lengkap }}</strong>?<br><small style="color:#64748b;">Data absensi dan izin juga akan terhapus.</small>',icon:'warning',showCancelButton:true,reverseButtons:true,buttonsStyling:false,customClass:{confirmButton:'ab-btn ab-btn-delete',cancelButton:'ab-btn ab-btn-back'},confirmButtonText:'<i class="fas fa-trash-alt"></i> Hapus',cancelButtonText:'<i class="fas fa-times"></i> Batal'}).then(function(r){if(r.isConfirmed)document.getElementById('deleteForm').submit();});
    }

    function confirmResetPassword() {
        if (typeof Swal === 'undefined') { if (!confirm('Reset password {{ $siswa->nama_lengkap }} ke 12345678?')) return; document.getElementById('resetPwForm').submit(); return; }
        Swal.fire({title:'Reset Password?',html:'Password <strong>{{ $siswa->nama_lengkap }}</strong> akan direset ke <code>12345678</code>.<br><small style="color:#64748b;">Siswa harus ganti password setelah login.</small>',icon:'warning',showCancelButton:true,reverseButtons:true,buttonsStyling:false,customClass:{confirmButton:'ab-btn ab-btn-reset-pw',cancelButton:'ab-btn ab-btn-back'},confirmButtonText:'<i class="fas fa-key"></i> Reset',cancelButtonText:'<i class="fas fa-times"></i> Batal'}).then(function(r){if(r.isConfirmed)document.getElementById('resetPwForm').submit();});
    }

    document.addEventListener('keydown', function(e) {
        if (e.key==='Escape') { closeAbsenModal(); closeEventModal(); }
    });
    </script>
@endpush
