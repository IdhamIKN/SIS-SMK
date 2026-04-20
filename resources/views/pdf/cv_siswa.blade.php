<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV - {{ $siswa->nama_lengkap }}</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20mm;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0;
            font-size: 11px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            font-size: 14px;
            border-bottom: 1px solid #666;
            padding-bottom: 3px;
            margin-bottom: 10px;
            text-transform: uppercase;
            color: #333;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 3px 0;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 120px;
        }
        .photo {
            float: right;
            width: 60px;
            height: 80px;
            border: 1px solid #333;
            margin-left: 15px;
            margin-bottom: 10px;
        }
        .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th, .table td {
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
        }
        .table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .signature {
            margin-top: 40px;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            width: 150px;
            display: inline-block;
            margin-top: 50px;
        }
        @page {
            margin: 15mm;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        @if($siswa->foto)
            <div class="photo">
                <img src="{{ public_path('storage/' . $siswa->foto) }}" alt="Foto">
            </div>
        @endif
        <h1>CURRICULUM VITAE</h1>
        <p>SMK NEGERI 5 MADIUN</p>
        <p>Jl. ... Madiun - Jawa Timur</p>
    </div>

    <!-- Data Pribadi -->
    <div class="section">
        <h2>Data Pribadi</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell info-label">Nama Lengkap</div>
                <div class="info-cell">: {{ $siswa->nama_lengkap }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">NIS</div>
                <div class="info-cell">: {{ $siswa->nis ?: '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">NISN</div>
                <div class="info-cell">: {{ $siswa->nisn }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Jenis Kelamin</div>
                <div class="info-cell">: {{ $siswa->jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Tempat, Tanggal Lahir</div>
                <div class="info-cell">: {{ $siswa->tempat_lahir ?: '-' }}, {{ $siswa->tanggal_lahir?->format('d F Y') ?: '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Kelas</div>
                <div class="info-cell">: {{ $siswa->kelas?->nama_kelas ?: '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Angkatan</div>
                <div class="info-cell">: {{ $siswa->angkatan ?: '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Alamat</div>
                <div class="info-cell">: {{ $siswa->alamat ?: '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Kontak Orang Tua -->
    <div class="section">
        <h2>Kontak Orang Tua / Wali</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell info-label">Nama Ortu 1</div>
                <div class="info-cell">: {{ $siswa->nama_ortu1 ?: '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">No HP Ortu 1</div>
                <div class="info-cell">: {{ $siswa->no_hp_ortu1 ?: '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Nama Ortu 2</div>
                <div class="info-cell">: {{ $siswa->nama_ortu2 ?: '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">No HP Ortu 2</div>
                <div class="info-cell">: {{ $siswa->no_hp_ortu2 ?: '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Nama Wali</div>
                <div class="info-cell">: {{ $siswa->nama_wali ?: '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Riwayat Absensi -->
    <div class="section">
        <h2>Riwayat Absensi Tahun {{ date('Y') }}</h2>
        @php
            $absenStats = [
                'hadir' => $siswa->absenSiswa->where('status', 'hadir')->count(),
                'sakit' => $siswa->absenSiswa->where('status', 'sakit')->count(),
                'izin' => $siswa->absenSiswa->where('status', 'izin')->count(),
                'alfa' => $siswa->absenSiswa->where('status', 'alfa')->count(),
            ];
        @endphp

        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell info-label">Hadir</div>
                <div class="info-cell">: {{ $absenStats['hadir'] }} hari</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Sakit</div>
                <div class="info-cell">: {{ $absenStats['sakit'] }} hari</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Izin</div>
                <div class="info-cell">: {{ $absenStats['izin'] }} hari</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Alfa</div>
                <div class="info-cell">: {{ $absenStats['alfa'] }} hari</div>
            </div>
        </div>

        <table class="table">
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
                @forelse($siswa->absenSiswa->sortByDesc('tanggal')->take(20) as $absen)
                <tr>
                    <td>{{ $absen->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $absen->jenis === 'masuk' ? 'Masuk' : 'Pulang' }}</td>
                    <td>{{ ucfirst($absen->status) }}</td>
                    <td>{{ $absen->waktu_absen?->format('H:i') ?: '-' }}</td>
                    <td>{{ $absen->catatan ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Belum ada data absensi</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Tanda Tangan -->
    <div class="signature">
        <p>Madiun, {{ date('d F Y') }}</p>
        <br><br><br>
        <div class="signature-line"></div>
        <p>{{ $siswa->nama_lengkap }}</p>
    </div>
</body>
</html>