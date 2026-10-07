<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Jurnal Absensi {{ $event->nama_event }}</title>
    <style>
        @page {
            size: A4;
            margin: 12mm 10mm;
        }

        * { box-sizing: border-box; }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        /* ── KOP ── */
        table.kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        table.kop-table td { vertical-align: middle; }
        .kop-logo { width: 70px; text-align: center; }
        .kop-logo img { width: 30mm; height: 30mm; object-fit: contain; }
        .kop-logo-placeholder {
            width: 30mm; height: 30mm;
            border: 2px solid #000;
            display: inline-flex;
            align-items: center; justify-content: center;
            font-size: 7pt; text-align: center; color: #555;
            border-radius: 50%;
        }
        .kop-text { text-align: center; padding: 0 10px; }
        .kop-text .prov  { font-size: 11pt; font-weight: normal; letter-spacing: .3px; }
        .kop-text .dinas { font-size: 11pt; font-weight: normal; }
        .kop-text .sekolah { font-size: 14pt; font-weight: bold; margin-top: 2px; }
        .kop-text .alamat  { font-size: 9pt; margin-top: 2px; }
        .kop-text .kota    { font-size: 12pt; font-weight: bold; margin-top: 2px; }

        .kop-divider { border: none; border-top: 3px double #000; margin: 6px 0 10px; }

        /* ── Judul ── */
        .judul { text-align: center; margin: 10px 0 12px; }
        .judul h3 { margin: 0; font-size: 12pt; text-decoration: underline; text-transform: uppercase; }
        .judul p  { margin: 3px 0 0; font-size: 9.5pt; }

        /* ── Info kegiatan ── */
        .info-kegiatan { width: 100%; margin-bottom: 10px; font-size: 9.5pt; }
        .info-kegiatan td { padding: 1px 0; vertical-align: top; }
        .info-kegiatan td.label { width: 126px; }
        .info-kegiatan td.titik { width: 10px; }

        /* ── Tabel data ── */
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8.5pt;
        }
        table.data th, table.data td {
            border: 1px solid #000;
            padding: 3px 4px;
        }
        table.data th { background: #e9e9e9; text-align: center; font-weight: bold; }
        table.data td.center { text-align: center; }
        table.data td.nama   { text-align: left; }
        .status-hadir { font-weight: bold; }
        .status-sakit, .status-izin { font-style: italic; }
        .status-alpa  { font-weight: bold; }

        /* ── Rekap ── */
        .rekap-wrap { text-align: right; margin-bottom: 12px; }
        table.rekap {
            border-collapse: collapse;
            font-size: 8.5pt;
            width: 260px;
            margin-left: auto;
        }
        table.rekap th, table.rekap td {
            border: 1px solid #000;
            padding: 3px 6px;
        }
        table.rekap th { background: #e9e9e9; text-align: left; }
        table.rekap td { text-align: right; width: 78px; }
        table.rekap tr.total td,
        table.rekap tr.total th { font-weight: bold; background: #f3f3f3; }

        /* ── Berita acara ── */
        .berita-acara { margin-bottom: 14px; font-size: 9.5pt; }
        .berita-acara h4 {
            text-align: center;
            text-decoration: underline;
            margin: 0 0 6px;
            font-size: 10.5pt;
        }
        .berita-acara p {
            text-align: justify;
            margin: 0 0 6px;
            text-indent: 0.9cm;
        }

        /* ── TTD ── */
        .ttd-tempat-tanggal { text-align: right; margin-bottom: 4px; font-size: 9.5pt; }
        .ttd-wrap { display: table; width: 100%; margin-top: 6px; font-size: 9.5pt; }
        .ttd-box { display: table-cell; width: 33.33%; text-align: center; vertical-align: top; }
        .ttd-box-half { display: table-cell; width: 50%; text-align: center; vertical-align: top; }
        .ttd-box .spasi, .ttd-box-half .spasi { height: 54px; }
        .ttd-box .nama,  .ttd-box-half .nama  { font-weight: bold; text-decoration: underline; margin: 0; }
        .ttd-box .nip,   .ttd-box-half .nip   { margin: 2px 0 0; font-size: 8.8pt; }
        .ttd-box .jabatan, .ttd-box-half .jabatan { margin: 0 0 2px; font-size: 9pt; }

        /* ── Halaman foto ── */
        .page-break { page-break-before: always; }
        .foto-halaman { margin-top: 4px; }
        .foto-halaman h4 {
            text-align: center;
            text-decoration: underline;
            font-size: 11pt;
            margin-bottom: 12px;
        }
        table.foto-grid {
            width: 100%;
            border-collapse: collapse;
        }
        table.foto-grid td {
            width: 33.33%;
            padding: 4px;
            vertical-align: top;
            text-align: center;
        }
        table.foto-grid img {
            width: 100%;
            max-height: 60mm;
            object-fit: cover;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .foto-caption {
            font-size: 7.5pt;
            color: #555;
            margin-top: 2px;
        }

        /* ── No-print ── */
        .no-print { text-align: center; margin: 16px 0; font-family: Arial, sans-serif; }
        .no-print button, .no-print a {
            display: inline-block;
            padding: 8px 20px; margin: 0 4px;
            font-size: 11pt; cursor: pointer;
            font-family: Arial, sans-serif;
            color: #111827; background: #f8fafc;
            border: 1px solid #cbd5e1; border-radius: 4px;
            text-decoration: none;
        }

        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>

<body>
    @php
        $sekolah           = $sekolah ?? sekolah_data();
        $namaSekolah       = $sekolah['nama']      ?? config('sekolah.nama',    'SMK NEGERI 5 MADIUN');
        $alamatSekolah     = $sekolah['alamat']    ?? config('sekolah.alamat',  'Jl. ... Madiun');
        $telpSekolah       = $sekolah['telp']      ?? config('sekolah.telepon', '-');
        $emailSekolah      = $sekolah['email']     ?? config('sekolah.email',   '-');
        $kabupaten         = $sekolah['kabupaten'] ?? 'Madiun';
        $namaKepalaSekolah = $sekolah['nama_ks']   ?? '-';
        $nipKepalaSekolah  = $sekolah['nip_ks']    ?? '-';
        $jenisAbsen = collect([
            $event->ada_absen_masuk  ? 'Masuk'  : null,
            $event->ada_absen_pulang ? 'Pulang' : null,
        ])->filter()->join(' dan ') ?: '-';
        $persentaseHadir = number_format($rekap['persentase_hadir'] ?? 0, 1, ',', '.');
        $isEkstra = $event->is_ekstrakurikuler ?? false;
        $fotoKegiatan = $fotoKegiatan ?? [];
    @endphp

    @if (!($isPdf ?? false))
        <div class="no-print">
            <button onclick="window.print()">Cetak Jurnal</button>
            <a href="{{ route('event.jurnal', $event) }}">Download PDF</a>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- KOP SURAT --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if ($logoJatim ?? null)
                    <img src="{{ $logoJatim }}" alt="Logo Jawa Timur">
                @else
                    <div class="kop-logo-placeholder">LOGO<br>JATIM</div>
                @endif
            </td>
            <td class="kop-text">
                <div class="prov">PEMERINTAH PROVINSI JAWA TIMUR</div>
                <div class="dinas">DINAS PENDIDIKAN</div>
                <div class="sekolah">{{ strtoupper($namaSekolah) }}</div>
                <div class="alamat">
                    {{ $alamatSekolah }}&nbsp;&nbsp;Telp. {{ $telpSekolah }}<br>
                    E-mail : {{ $emailSekolah }}
                </div>
                <div class="kota">{{ strtoupper($kabupaten) }}</div>
            </td>
            <td class="kop-logo">
                @if ($logoSmk ?? null)
                    <img src="{{ $logoSmk }}" alt="Logo {{ $namaSekolah }}">
                @else
                    <div class="kop-logo-placeholder">LOGO<br>SMK</div>
                @endif
            </td>
        </tr>
    </table>
    <hr class="kop-divider">

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- JUDUL --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="judul">
        @if ($isEkstra)
            <h3>Jurnal Absensi Kegiatan Ekstrakurikuler</h3>
        @else
            <h3>Jurnal Absensi Kegiatan</h3>
        @endif
        <p>Nomor: {{ $nomorDokumen ?? '-' }}</p>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- INFO KEGIATAN --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <table class="info-kegiatan">
        <tr>
            <td class="label">Nama Kegiatan</td>
            <td class="titik">:</td>
            <td>{{ $event->nama_event }}</td>
        </tr>
        @if ($isEkstra)
        <tr>
            <td class="label">Jenis</td>
            <td class="titik">:</td>
            <td>Ekstrakurikuler</td>
        </tr>
        @endif
        <tr>
            <td class="label">Hari / Tanggal</td>
            <td class="titik">:</td>
            <td>{{ $event->tanggal_mulai->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Waktu</td>
            <td class="titik">:</td>
            <td>{{ $event->tanggal_mulai->format('H:i') }} WIB &ndash; {{ $event->tanggal_selesai->format('H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="label">Tempat</td>
            <td class="titik">:</td>
            <td>{{ $event->lokasi ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Peserta</td>
            <td class="titik">:</td>
            <td>{{ $labelPeserta ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tipe Absen</td>
            <td class="titik">:</td>
            <td>{{ $jenisAbsen }}</td>
        </tr>
        @if ($isEkstra)
            @if ($event->pelatih_1 || $event->pelatih_2 || $event->pelatih_3)
            <tr>
                <td class="label">Pelatih</td>
                <td class="titik">:</td>
                <td>
                    @php $pelatihList = array_filter([$event->pelatih_1, $event->pelatih_2, $event->pelatih_3]); @endphp
                    {{ implode(', ', $pelatihList) }}
                </td>
            </tr>
            @endif
            @if ($event->pembina_nama)
            <tr>
                <td class="label">Pembina</td>
                <td class="titik">:</td>
                <td>
                    {{ $event->pembina_nama }}
                    @if ($event->pembina_nip)
                        (NIP. {{ $event->pembina_nip }})
                    @endif
                </td>
            </tr>
            @endif
        @else
        <tr>
            <td class="label">Penanggung Jawab</td>
            <td class="titik">:</td>
            <td>{{ $penanggungJawab['nama'] ?? '-' }}</td>
        </tr>
        @endif
    </table>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- TABEL ABSENSI --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width:24px;">No</th>
                <th style="width:68px;">NIS</th>
                <th>Nama Siswa</th>
                <th style="width:58px;">Kelas</th>
                <th style="width:50px;">Status</th>
                <th style="width:50px;">Jam</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $i => $row)
                @php $statusClass = 'status-' . strtolower($row['status']); @endphp
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td class="center">{{ $row['nis'] }}</td>
                    <td class="nama">{{ $row['nama'] }}</td>
                    <td class="center">{{ $row['kelas'] }}</td>
                    <td class="center {{ $statusClass }}">{{ $row['status'] }}</td>
                    <td class="center">{{ $row['jam_hadir'] }}</td>
                    <td>{{ $row['keterangan'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">Belum ada data peserta untuk event ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- REKAP --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="rekap-wrap">
        <table class="rekap">
            <tr><th>Status</th><td>Jumlah</td></tr>
            <tr><th>Hadir</th><td>{{ $rekap['hadir'] ?? 0 }} siswa</td></tr>
            <tr><th>Sakit</th><td>{{ $rekap['sakit'] ?? 0 }} siswa</td></tr>
            <tr><th>Izin</th><td>{{ $rekap['izin'] ?? 0 }} siswa</td></tr>
            <tr><th>Alpa</th><td>{{ $rekap['alpa'] ?? 0 }} siswa</td></tr>
            <tr class="total"><th>Total Siswa</th><td>{{ $rekap['total'] ?? 0 }} siswa</td></tr>
            <tr class="total"><th>Persentase Hadir</th><td>{{ $persentaseHadir }}%</td></tr>
        </table>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- BERITA ACARA (berbeda untuk ekstra dan non-ekstra) --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="berita-acara">
        @if ($isEkstra)
            {{-- Berita Acara Ekstrakurikuler --}}
            <h4>Berita Acara Kehadiran Kegiatan Ekstrakurikuler</h4>
            <p>
                Pada hari {{ $event->tanggal_mulai->translatedFormat('l') }} tanggal
                {{ $event->tanggal_mulai->translatedFormat('d F Y') }}, bertempat di
                {{ $event->lokasi ?: $namaSekolah }}, telah dilaksanakan kegiatan
                Ekstrakurikuler <strong>{{ $event->nama_event }}</strong> yang diikuti oleh
                {{ $rekap['total'] ?? 0 }} siswa. Kegiatan berlangsung mulai pukul
                {{ $event->tanggal_mulai->format('H:i') }} WIB sampai dengan pukul
                {{ $event->tanggal_selesai->format('H:i') }} WIB.
            </p>
            <p>
                Dari jumlah peserta tersebut, tercatat {{ $rekap['hadir'] ?? 0 }} siswa hadir
                dan {{ $rekap['alpa'] ?? 0 }} siswa tidak hadir. Persentase kehadiran mencapai
                {{ $persentaseHadir }}%. Daftar lengkap kehadiran peserta tercantum pada tabel
                jurnal absensi di atas.
            </p>
            @if ($event->deskripsi)
                <p>Keterangan kegiatan: {{ $event->deskripsi }}</p>
            @endif
            <p>
                Berita acara ini dibuat dengan sebenar-benarnya untuk dipergunakan sebagai
                bahan pelaporan dan arsip pelaksanaan kegiatan ekstrakurikuler di {{ $namaSekolah }}.
            </p>
        @else
            {{-- Berita Acara Event Biasa --}}
            <h4>Berita Acara Pelaksanaan Kegiatan</h4>
            <p>
                Pada hari {{ $event->tanggal_mulai->translatedFormat('l') }} tanggal
                {{ $event->tanggal_mulai->translatedFormat('d F Y') }}, bertempat di
                {{ $event->lokasi ?: $namaSekolah }}, telah dilaksanakan kegiatan
                {{ $event->nama_event }} dengan jumlah peserta sebanyak {{ $rekap['total'] ?? 0 }}
                siswa. Rincian kehadiran peserta tercantum pada tabel jurnal absensi di atas.
            </p>
            @if ($event->deskripsi)
                <p>Deskripsi kegiatan: {{ $event->deskripsi }}</p>
            @endif
            <p>
                Kegiatan berlangsung sesuai jadwal yang telah ditetapkan. Jurnal absensi ini dibuat
                dengan sebenar-benarnya untuk dipergunakan sebagai bahan pelaporan dan arsip sekolah.
            </p>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- TANDA TANGAN (berbeda untuk ekstra dan non-ekstra) --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="ttd-tempat-tanggal">{{ $kabupaten }}, {{ $tanggalCetak->translatedFormat('d F Y') }}</div>

    @if ($isEkstra)
        {{-- TTD Ekstrakurikuler: Pelatih + Pembina + Kepala Sekolah --}}
        @php
            $pelatihList = array_values(array_filter([
                $event->pelatih_1,
                $event->pelatih_2,
                $event->pelatih_3,
            ]));
            $jumlahPelatih = count($pelatihList);
        @endphp

        {{-- Baris 1: pelatih (maks 3) --}}
        @if ($jumlahPelatih > 0)
        <div class="ttd-wrap">
            @foreach ($pelatihList as $idx => $pelatih)
            <div class="ttd-box">
                <p class="jabatan">Pelatih {{ $idx + 1 }}</p>
                <div class="spasi"></div>
                <p class="nama">{{ $pelatih }}</p>
                <p class="nip">&nbsp;</p>
            </div>
            @endforeach
            {{-- Isi kolom kosong jika pelatih kurang dari 3 --}}
            @for ($i = $jumlahPelatih; $i < 3; $i++)
            <div class="ttd-box"></div>
            @endfor
        </div>
        <br>
        @endif

        {{-- Baris 2: Pembina + Kepala Sekolah --}}
        <div class="ttd-wrap">
            <div class="ttd-box-half">
                <p class="jabatan">Pembina Ekstrakurikuler</p>
                <div class="spasi"></div>
                <p class="nama">{{ $event->pembina_nama ?: '-' }}</p>
                @if ($event->pembina_nip)
                    <p class="nip">NIP. {{ $event->pembina_nip }}</p>
                @else
                    <p class="nip">&nbsp;</p>
                @endif
            </div>
            <div class="ttd-box-half">
                <p class="jabatan">Mengetahui,<br>Kepala Sekolah<br>{{ $namaSekolah }}</p>
                <div class="spasi"></div>
                <p class="nama">{{ $namaKepalaSekolah ?: '-' }}</p>
                <p class="nip">NIP. {{ $nipKepalaSekolah ?: '-' }}</p>
            </div>
        </div>

    @else
        {{-- TTD Event Biasa: Penanggung Jawab + Kepala Sekolah --}}
        <div class="ttd-wrap">
            <div class="ttd-box-half">
                <p class="jabatan">Penanggung Jawab Kegiatan</p>
                <div class="spasi"></div>
                <p class="nama">{{ $penanggungJawab['nama'] ?? '-' }}</p>
                <p class="nip">NIP. {{ $penanggungJawab['nip'] ?? '-' }}</p>
            </div>
            <div class="ttd-box-half">
                <p class="jabatan">Mengetahui,<br>Kepala Sekolah<br>{{ $namaSekolah }}</p>
                <div class="spasi"></div>
                <p class="nama">{{ $namaKepalaSekolah ?: '-' }}</p>
                <p class="nip">NIP. {{ $nipKepalaSekolah ?: '-' }}</p>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- HALAMAN FOTO (jika ada foto) --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @if (!empty($fotoKegiatan))
        <div class="page-break">
            <hr class="kop-divider" style="margin-top:0;">
            <div class="foto-halaman">
                <h4>Dokumentasi Foto Kegiatan {{ $event->nama_event }}</h4>
                @php
                    $chunks = array_chunk($fotoKegiatan, 3);
                @endphp
                @foreach ($chunks as $row)
                    <table class="foto-grid">
                        <tr>
                            @foreach ($row as $idx => $foto)
                                <td>
                                    <img src="{{ $foto }}" alt="Foto kegiatan">
                                    <div class="foto-caption">Foto {{ ($loop->parent->index * 3) + $loop->index + 1 }}</div>
                                </td>
                            @endforeach
                            {{-- Isi sel kosong jika kolom kurang dari 3 --}}
                            @for ($e = count($row); $e < 3; $e++)
                                <td></td>
                            @endfor
                        </tr>
                    </table>
                    @if (!$loop->last)
                        <br>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

</body>
</html>
