<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>CV - {{ $siswa->nama_lengkap }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #fff;
        }

        /* ── Sidebar kiri ── */
        .layout {
            display: table;
            width: 100%;
            min-height: 297mm;
        }

        .sidebar {
            display: table-cell;
            width: 68mm;
            background: #1e3a5f;
            vertical-align: top;
            padding: 0;
        }

        .main {
            display: table-cell;
            vertical-align: top;
            padding: 0;
            background: #fff;
        }

        /* ── Sidebar top: foto + nama ── */
        .sb-top {
            background: #162d4a;
            padding: 24px 16px 20px;
            text-align: center;
        }

        .sb-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, .35);
            overflow: hidden;
            margin: 0 auto 12px;
            background: #1d4ed8;
        }

        .sb-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sb-photo-placeholder {
            width: 100%;
            height: 100%;
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            font-size: 28px;
            color: rgba(255, 255, 255, .6);
        }

        .sb-name {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            line-height: 1.3;
            margin-bottom: 4px;
        }

        .sb-sub {
            font-size: 9px;
            color: rgba(255, 255, 255, .65);
            line-height: 1.5;
        }

        .sb-badge {
            display: inline-block;
            margin-top: 8px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 8.5px;
            font-weight: 700;
            background: rgba(255, 255, 255, .15);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .25);
        }

        /* ── Sidebar section ── */
        .sb-section {
            padding: 14px 16px;
        }

        .sb-section-title {
            font-size: 8px;
            font-weight: 700;
            color: rgba(255, 255, 255, .5);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, .12);
            padding-bottom: 5px;
        }

        .sb-row {
            margin-bottom: 9px;
        }

        .sb-row-label {
            font-size: 8px;
            font-weight: 700;
            color: rgba(255, 255, 255, .55);
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 2px;
        }

        .sb-row-val {
            font-size: 10px;
            color: #fff;
            font-weight: 600;
            line-height: 1.4;
        }

        /* Stat kotak absensi di sidebar */
        .absen-stat-table {
            width: 100%;
            border-collapse: collapse;
        }

        .absen-stat-table td {
            width: 50%;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }

        .stat-box {
            background: rgba(255, 255, 255, .1);
            border-radius: 6px;
            padding: 7px 4px;
        }

        .stat-num {
            font-size: 16px;
            font-weight: 800;
            color: #fff;
            line-height: 1;
        }

        .stat-lbl {
            font-size: 7.5px;
            color: rgba(255, 255, 255, .6);
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .stat-box.hadir {
            border-top: 3px solid #34d399;
        }

        .stat-box.sakit {
            border-top: 3px solid #60a5fa;
        }

        .stat-box.izin {
            border-top: 3px solid #fbbf24;
        }

        .stat-box.alfa {
            border-top: 3px solid #f87171;
        }

        /* ── Main content ── */
        .main-top {
            background: #f0f6ff;
            padding: 20px 22px 14px;
            border-bottom: 3px solid #1d4ed8;
        }

        .school-name {
            font-size: 16px;
            font-weight: 800;
            color: #1e3a5f;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .school-sub {
            font-size: 9px;
            color: #1d4ed8;
            margin-top: 2px;
            letter-spacing: .04em;
        }

        .doc-title {
            margin-top: 10px;
            font-size: 11px;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: .08em;
            text-transform: uppercase;
            background: #1d4ed8;
            color: #fff;
            display: inline-block;
            padding: 3px 12px;
            border-radius: 4px;
        }

        /* ── Section ── */
        .section {
            padding: 14px 22px;
            border-bottom: 1px solid #f0f4fb;
        }

        .section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 9px;
            font-weight: 800;
            color: #1d4ed8;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 10px;
            display: table;
            width: 100%;
        }

        .section-title::after {
            content: '';
            display: table-cell;
            border-bottom: 1.5px solid #dbeafe;
            padding-left: 8px;
            vertical-align: middle;
        }

        /* Info rows di main */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table tr td {
            padding: 4px 0;
            vertical-align: top;
        }

        .info-table .lbl {
            width: 110px;
            font-weight: 700;
            color: #64748b;
            font-size: 10px;
            white-space: nowrap;
        }

        .info-table .sep {
            width: 12px;
            color: #cbd5e1;
            font-size: 10px;
        }

        .info-table .val {
            font-size: 10.5px;
            color: #1e293b;
            font-weight: 600;
        }

        /* ── Tabel absensi ── */
        .absen-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .absen-table thead tr {
            background: #1e3a5f;
            color: #fff;
        }

        .absen-table th {
            padding: 6px 8px;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            text-align: left;
        }

        .absen-table tbody tr:nth-child(even) td {
            background: #f0f6ff;
        }

        .absen-table td {
            padding: 5px 8px;
            font-size: 9.5px;
            border-bottom: 1px solid #f0f4fb;
            color: #334155;
        }

        .badge {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 20px;
            font-size: 8px;
            font-weight: 700;
        }

        .b-hadir {
            background: #dcfce7;
            color: #15803d;
        }

        .b-sakit {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .b-izin {
            background: #fef9c3;
            color: #a16207;
        }

        .b-alfa {
            background: #fee2e2;
            color: #dc2626;
        }

        /* ── Tanda tangan ── */
        .ttd-section {
            padding: 16px 22px 20px;
        }

        .ttd-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ttd-table td {
            text-align: center;
            vertical-align: top;
            padding: 0 6px;
        }

        .ttd-city {
            font-size: 9.5px;
            color: #475569;
            margin-bottom: 38px;
        }

        .ttd-line {
            border-bottom: 1px solid #334155;
            margin: 0 10px 4px;
        }

        .ttd-name {
            font-size: 10px;
            font-weight: 700;
            color: #1e293b;
        }

        .ttd-role {
            font-size: 8px;
            color: #64748b;
        }

        /* ── Footer strip ── */
        .pdf-footer {
            background: #1e3a5f;
            padding: 6px 22px;
            font-size: 8px;
            color: rgba(255, 255, 255, .6);
            text-align: center;
        }
    </style>
</head>

<body>

    @php
        $absenStats = [
            'hadir' => $siswa->absenSiswa->where('status', 'hadir')->count(),
            'sakit' => $siswa->absenSiswa->where('status', 'sakit')->count(),
            'izin' => $siswa->absenSiswa->where('status', 'izin')->count(),
            'alfa' => $siswa->absenSiswa->where('status', 'alfa')->count(),
        ];
        $total = array_sum($absenStats) ?: 1;
        $hadirPct = round(($absenStats['hadir'] / $total) * 100);
    @endphp

    <div class="layout">

        {{-- ═══ SIDEBAR ═══ --}}
        <div class="sidebar">

            {{-- Foto & Nama --}}
            <div class="sb-top">
                <div class="sb-photo">
                    @if ($siswa->foto)
                        <img src="{{ public_path('storage/' . $siswa->foto) }}" alt="Foto">
                    @else
                        <table style="width:100%;height:100%;">
                            <tr>
                                <td class="sb-photo-placeholder">&#128100;</td>
                            </tr>
                        </table>
                    @endif
                </div>
                <div class="sb-name">{{ $siswa->nama_lengkap }}</div>
                <div class="sb-sub">
                    {{ $siswa->kelas?->nama_kelas ?? '-' }}<br>
                    Angkatan {{ $siswa->angkatan ?? date('Y') }}
                </div>
                <div class="sb-badge">{{ $siswa->status_aktif ? '● Siswa Aktif' : '○ Non Aktif' }}</div>
            </div>

            {{-- Identitas --}}
            <div class="sb-section">
                <div class="sb-section-title">Identitas</div>
                <div class="sb-row">
                    <div class="sb-row-label">NIS</div>
                    <div class="sb-row-val">{{ $siswa->nis ?: '-' }}</div>
                </div>
                <div class="sb-row">
                    <div class="sb-row-label">NISN</div>
                    <div class="sb-row-val">{{ $siswa->nisn }}</div>
                </div>
                <div class="sb-row">
                    <div class="sb-row-label">Jenis Kelamin</div>
                    <div class="sb-row-val">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan' }}</div>
                </div>
                <div class="sb-row">
                    <div class="sb-row-label">TTL</div>
                    <div class="sb-row-val">
                        {{ $siswa->tempat_lahir ?: '-' }},<br>
                        {{ $siswa->tanggal_lahir?->translatedFormat('d F Y') ?: '-' }}
                    </div>
                </div>
            </div>

            {{-- Kontak --}}
            <div class="sb-section">
                <div class="sb-section-title">Kontak</div>
                <div class="sb-row">
                    <div class="sb-row-label">HP Siswa</div>
                    <div class="sb-row-val">{{ $siswa->no_hp_siswa ?: '-' }}</div>
                </div>
                <div class="sb-row">
                    <div class="sb-row-label">HP Ortu 1</div>
                    <div class="sb-row-val">{{ $siswa->no_hp_ortu1 ?: '-' }}</div>
                </div>
                <div class="sb-row">
                    <div class="sb-row-label">HP Ortu 2</div>
                    <div class="sb-row-val">{{ $siswa->no_hp_ortu2 ?: '-' }}</div>
                </div>
            </div>

            {{-- Rekap Absensi --}}
            <div class="sb-section">
                <div class="sb-section-title">Rekap Absensi {{ date('Y') }}</div>
                <table class="absen-stat-table">
                    <tr>
                        <td>
                            <div class="stat-box hadir">
                                <div class="stat-num">{{ $absenStats['hadir'] }}</div>
                                <div class="stat-lbl">Hadir</div>
                            </div>
                        </td>
                        <td>
                            <div class="stat-box sakit">
                                <div class="stat-num">{{ $absenStats['sakit'] }}</div>
                                <div class="stat-lbl">Sakit</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="stat-box izin">
                                <div class="stat-num">{{ $absenStats['izin'] }}</div>
                                <div class="stat-lbl">Izin</div>
                            </div>
                        </td>
                        <td>
                            <div class="stat-box alfa">
                                <div class="stat-num">{{ $absenStats['alfa'] }}</div>
                                <div class="stat-lbl">Alfa</div>
                            </div>
                        </td>
                    </tr>
                </table>
                <div style="margin-top:10px; font-size:9px; color:rgba(255,255,255,.55); text-align:center;">
                    Tingkat kehadiran: <strong style="color:#34d399;">{{ $hadirPct }}%</strong>
                </div>
            </div>

        </div>{{-- /sidebar --}}

        {{-- ═══ MAIN ═══ --}}
        <div class="main">

            {{-- Header sekolah --}}
            <div class="main-top">
                <div class="school-name">SMK Negeri 5 Madiun</div>
                <div class="school-sub">
                    {{ config('sekolah.alamat', 'Jl. ... Madiun, Jawa Timur') }}
                    &nbsp;·&nbsp; {{ config('sekolah.telepon', '') }}
                </div>
                <div class="doc-title">Curriculum Vitae Siswa</div>
            </div>

            {{-- Data Pribadi --}}
            <div class="section">
                <div class="section-title">Data Pribadi</div>
                <table class="info-table">
                    <tr>
                        <td class="lbl">Nama Lengkap</td>
                        <td class="sep">:</td>
                        <td class="val"><strong>{{ $siswa->nama_lengkap }}</strong></td>
                    </tr>
                    <tr>
                        <td class="lbl">NIS / NISN</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $siswa->nis ?: '-' }} / {{ $siswa->nisn }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Jenis Kelamin</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Tempat, Tgl Lahir</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $siswa->tempat_lahir ?: '-' }},
                            {{ $siswa->tanggal_lahir?->translatedFormat('d F Y') ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Kelas / Angkatan</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $siswa->kelas?->nama_kelas ?? '-' }} / {{ $siswa->angkatan ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="lbl">Status</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $siswa->status_aktif ? 'Aktif' : 'Non Aktif' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Alamat</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $siswa->alamat ?: '-' }}</td>
                    </tr>
                </table>
            </div>

            {{-- Orang Tua / Wali --}}
            <div class="section">
                <div class="section-title">Orang Tua / Wali</div>
                <table class="info-table">
                    <tr>
                        <td class="lbl">Nama Orang Tua 1</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $siswa->nama_ortu1 ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">No. HP Ortu 1</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $siswa->no_hp_ortu1 ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Nama Orang Tua 2</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $siswa->nama_ortu2 ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">No. HP Ortu 2</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $siswa->no_hp_ortu2 ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Nama Wali</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $siswa->nama_wali ?: '-' }}</td>
                    </tr>
                </table>
            </div>

            {{-- Riwayat Absensi --}}
            <div class="section">
                <div class="section-title">Riwayat Absensi Tahun {{ date('Y') }}</div>
                <table class="absen-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($siswa->absenSiswa->sortByDesc('tanggal')->take(15) as $absen)
                            <tr>
                                <td>{{ $absen->tanggal->format('d/m/Y') }}</td>
                                <td>{{ $absen->jenis === 'masuk' ? 'Masuk' : 'Pulang' }}</td>
                                <td>
                                    @php
                                        $cls = match ($absen->status) {
                                            'hadir' => 'b-hadir',
                                            'sakit' => 'b-sakit',
                                            'izin' => 'b-izin',
                                            default => 'b-alfa',
                                        };
                                    @endphp
                                    <span class="badge {{ $cls }}">{{ ucfirst($absen->status) }}</span>
                                </td>
                                <td>{{ $absen->waktu_absen?->format('H:i') ?: '-' }}</td>
                                <td>{{ $absen->catatan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center; color:#94a3b8; padding:14px;">
                                    Belum ada data absensi
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Tanda Tangan --}}
            <div class="ttd-section">
                <table class="ttd-table">
                    <tr>
                        <td style="width:50%;">
                            <div class="ttd-city">Madiun, {{ now()->translatedFormat('d F Y') }}</div>
                            <div class="ttd-line"></div>
                            <div class="ttd-name">{{ $siswa->nama_lengkap }}</div>
                            <div class="ttd-role">Siswa</div>
                        </td>
                        <td style="width:50%;">
                            <div class="ttd-city">Mengetahui,</div>
                            <div class="ttd-line"></div>
                            <div class="ttd-name">................................</div>
                            <div class="ttd-role">Wali Kelas / Kepala Sekolah</div>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Footer strip --}}
            <div class="pdf-footer">
                Dokumen ini digenerate otomatis oleh Sistem Informasi {{ config('sekolah.nama', 'SMKN 5 Madiun') }}
                &nbsp;·&nbsp; {{ now()->format('d/m/Y H:i') }}
                &nbsp;·&nbsp; Dokumen ini sah tanpa tanda tangan basah
            </div>

        </div>{{-- /main --}}

    </div>{{-- /layout --}}

</body>

</html>
