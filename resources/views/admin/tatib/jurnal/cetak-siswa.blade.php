<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Jurnal Siswa — {{ $namaSiswa }}</title>
    <style>
        /* ════════ PAGE SETUP — Portrait A4 ════════ */
        @page {
            size: A4 portrait;
            margin-top: 14mm;
            margin-right: 14mm;
            margin-bottom: 16mm;
            margin-left: 20mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 8.5pt;
            color: #000;
            line-height: 1.35;
            background: #fff;
        }

        /* Portrait A4 cetak area = 210mm - 20mm - 14mm = 176mm */
        .wrap {
            width: 176mm;
            max-width: 176mm;
            margin: 0 auto;
        }

        /* ════════ KOP ════════ */
        table.kop {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        td.kop-logo {
            width: 52px;
            text-align: center;
            vertical-align: middle;
        }

        td.kop-logo img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        td.kop-logo .ph {
            width: 48px;
            height: 48px;
            border: 1.5px solid #000;
            border-radius: 50%;
            display: inline-block;
            font-size: 5pt;
            color: #555;
            text-align: center;
            line-height: 48px;
        }

        td.kop-text {
            text-align: center;
            vertical-align: middle;
            padding: 0 6px;
        }

        .t-prov {
            font-size: 8pt;
            line-height: 1.4;
        }

        .t-dinas {
            font-size: 8pt;
            line-height: 1.4;
        }

        .t-sek {
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.4;
            margin-top: 2px;
        }

        .t-adr {
            font-size: 7pt;
            line-height: 1.5;
            margin-top: 1px;
        }

        .t-kota {
            font-size: 9pt;
            font-weight: bold;
            line-height: 1.4;
            margin-top: 2px;
        }

        .kop-line {
            border-top: 1px solid #000;
            border-bottom: 3px solid #000;
            height: 3px;
            margin: 4px 0 6px;
        }

        /* ════════ JUDUL ════════ */
        .judul-wrap {
            text-align: center;
            margin: 4px 0 5px;
        }

        .judul-utama {
            font-size: 11pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .judul-sub {
            font-size: 8pt;
            margin-top: 2px;
        }

        /* ════════ IDENTITAS SISWA ════════ */
        table.identitas {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 8px;
            border: 1px solid #000;
        }

        table.identitas td {
            padding: 3px 6px;
            vertical-align: top;
        }

        table.identitas td.lbl {
            font-weight: bold;
            width: 100px;
            white-space: nowrap;
        }

        table.identitas td.sep {
            width: 10px;
        }

        table.identitas td.val {}

        table.identitas tr:not(:last-child) td {
            border-bottom: 1px solid #ccc;
        }

        table.identitas .row-head td {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid #000;
            font-size: 8.5pt;
        }

        /* ════════ INFO CETAK ════════ */
        table.info {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 8px;
        }

        table.info td {
            padding: 1.5px 2px;
            vertical-align: top;
        }

        table.info td.lbl {
            width: 110px;
            font-weight: bold;
            white-space: nowrap;
        }

        table.info td.sep {
            width: 10px;
        }

        /* ════════ TABEL TRANSAKSI ════════ */
        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            table-layout: fixed;
            word-wrap: break-word;
            margin-bottom: 4px;
        }

        table.data th {
            border: 1px solid #000;
            padding: 3px 3px;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            line-height: 1.3;
            background: #fff;
        }

        table.data td {
            border: 1px solid #666;
            padding: 2.5px 3px;
            vertical-align: top;
            overflow-wrap: break-word;
        }

        table.data td.c {
            text-align: center;
            vertical-align: middle;
        }

        table.data tr:last-child td {
            border-bottom: 2px solid #000;
        }

        /* Kolom detail */
        col.c-no {
            width: 6%;
        }

        col.c-tgl {
            width: 16%;
        }

        col.c-jen {
            width: 12%;
        }

        col.c-pas {
            width: 28%;
        }

        col.c-poin {
            width: 10%;
        }

        col.c-ket {
            width: 28%;
        }

        /* Kolom ringkas */
        col.c-no-r {
            width: 7%;
        }

        col.c-tgl-r {
            width: 20%;
        }

        col.c-jen-r {
            width: 15%;
        }

        col.c-pas-r {
            width: 58%;
        }

        /* Label jenis */
        .lbl-pel {
            color: #8b0000;
            font-weight: bold;
        }

        .lbl-prg {
            color: #145214;
            font-weight: bold;
        }

        /* ════════ RINGKASAN ════════ */
        .ringkasan-wrap {
            width: 60%;
            margin: 6px 0 6px auto;
        }

        table.ringkasan {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }

        table.ringkasan th {
            background: #fff;
            border: 1px solid #000;
            padding: 3px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 8.5pt;
        }

        table.ringkasan td.lbl {
            border: 1px solid #666;
            padding: 3px 8px;
            font-weight: normal;
        }

        table.ringkasan td.val {
            border: 1px solid #666;
            padding: 3px 8px;
            text-align: right;
            font-weight: bold;
            width: 35%;
        }

        table.ringkasan tr.tot td {
            border-color: #000;
            background: #f5f5f5;
        }

        table.ringkasan tr.sisa td {
            border-color: #000;
            font-weight: bold;
            font-size: 9pt;
        }

        /* ════════ CATATAN KAKI & TTD ════════ */
        .footnote {
            font-size: 7pt;
            color: #333;
            margin-top: 12px;
            padding-top: 4px;
            border-top: 1px solid #999;
            line-height: 1.6;
        }

        .ttd-tempat {
            text-align: right;
            margin: 14px 0 2px;
            font-size: 8.5pt;
        }

        table.ttd {
            width: 100%;
            margin-top: 4px;
            font-size: 8.5pt;
            border-collapse: collapse;
        }

        table.ttd td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }

        .ttd-spasi {
            height: 40px;
        }

        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
        }

        .ttd-nip {
            font-size: 7.5pt;
            margin-top: 1px;
        }

        /* ════════ PRINT ════════ */
        @media print {
            @page {
                size: A4 portrait;
                margin-top: 14mm;
                margin-right: 14mm;
                margin-bottom: 16mm;
                margin-left: 20mm;
            }

            html,
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table.data thead {
                display: table-header-group;
            }
        }
    </style>
</head>

<body>
    @php
        $sd = $sekolah ?? sekolah_data();
        $nmSek = $sd['nama'] ?? config('sekolah.nama', 'SMK NEGERI 5 MADIUN');
        $alamat = $sd['alamat'] ?? config('sekolah.alamat', '-');
        $telp = $sd['telp'] ?? config('sekolah.telepon', '-');
        $email = $sd['email'] ?? config('sekolah.email', '-');
        $kab = $sd['kabupaten'] ?? 'Madiun';
        $namaKs = $sd['nama_ks'] ?? '-';
        $nipKs = $sd['nip_ks'] ?? '-';

        $judulJenis = match ($jenis) {
            'pelanggaran' => 'PELANGGARAN',
            'penghargaan' => 'PENGHARGAAN',
            default => 'PELANGGARAN DAN PENGHARGAAN',
        };

        // Ambil data siswa dari struktur dataPerKelas (per-siswa selalu 1 kelas)
        $kelasData = $dataPerKelas->first();
        $namaKelas = $kelasData['nama_kelas'] ?? '-';
        $siswaData = collect($kelasData['siswa'] ?? [])->first() ?? [];
        $rows = collect($siswaData['rows'] ?? []);
        $sisaAkhir = $sisaPoinMap[$siswaId] ?? '-';

        $totalPel = $rows->where('jenis', 'pelanggaran')->sum('poin');
        $totalPrg = $rows->where('jenis', 'penghargaan')->sum('poin');
        $totalEntri = $rows->count();

        $akumLabel = match ($jenis) {
            'pelanggaran' => $totalPel,
            'penghargaan' => $totalPrg,
            default => 'P:' . $totalPel . '  R:' . $totalPrg,
        };

        $sisaLabel = is_numeric($sisaAkhir)
            ? ($sisaAkhir < 0
                ? '<span style="color:#8b0000;font-weight:bold;">' . $sisaAkhir . '</span>'
                : (string) $sisaAkhir)
            : $sisaAkhir;

        $showDetail = $showDetail ?? false;
    @endphp

    <div class="wrap">

        {{-- ══ KOP ══ --}}
        <table class="kop" cellspacing="0" cellpadding="0">
            <tr>
                <td class="kop-logo">
                    @if (!empty($logoJatim))
                        <img src="{{ $logoJatim }}" alt="Logo Jawa Timur">
                    @else
                        <div class="ph">LOGO<br>JATIM</div>
                    @endif
                </td>
                <td class="kop-text">
                    <div class="t-prov">PEMERINTAH PROVINSI JAWA TIMUR</div>
                    <div class="t-dinas">DINAS PENDIDIKAN</div>
                    <div class="t-sek">{{ strtoupper($nmSek) }}</div>
                    <div class="t-adr">{{ $alamat }}&nbsp;&nbsp;Telp. {{ $telp }}</div>
                    <div class="t-adr">E-mail : {{ $email }}</div>
                    <div class="t-kota">{{ strtoupper($kab) }}</div>
                </td>
                <td class="kop-logo">
                    @if (!empty($logoSmk))
                        <img src="{{ $logoSmk }}" alt="Logo {{ $nmSek }}">
                    @else
                        <div class="ph">LOGO<br>SMK</div>
                    @endif
                </td>
            </tr>
        </table>
        <div class="kop-line"></div>

        {{-- ══ JUDUL ══ --}}
        <div class="judul-wrap">
            <div class="judul-utama">Jurnal Laporan {{ $judulJenis }} Siswa</div>
            <div class="judul-sub">Tahun Ajaran {{ $tahunAjaran }}</div>
        </div>

        {{-- ══ IDENTITAS SISWA ══ --}}
        <table class="identitas" cellspacing="0" cellpadding="0">
            <tr class="row-head">
                <td colspan="3">Identitas Siswa</td>
            </tr>
            <tr>
                <td class="lbl">Nama Siswa</td>
                <td class="sep">:</td>
                <td class="val"><strong>{{ $namaSiswa }}</strong></td>
            </tr>
            <tr>
                <td class="lbl">NIS / NISN</td>
                <td class="sep">:</td>
                <td class="val">{{ $nisSiswa }} / {{ $nisnSiswa }}</td>
            </tr>
            <tr>
                <td class="lbl">Kelas</td>
                <td class="sep">:</td>
                <td class="val">{{ $namaKelas }}</td>
            </tr>
        </table>

        {{-- ══ INFO CETAK ══ --}}
        <table class="info" cellspacing="0" cellpadding="0">
            <tr>
                <td class="lbl">Periode</td>
                <td class="sep">:</td>
                <td>{{ $dariTanggal->translatedFormat('d F Y') }} s.d. {{ $sampaiTanggal->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td class="lbl">Jenis Laporan</td>
                <td class="sep">:</td>
                <td>{{ $judulJenis }}</td>
            </tr>
            <tr>
                <td class="lbl">Dicetak oleh</td>
                <td class="sep">:</td>
                <td>{{ $cetakUser }}</td>
            </tr>
            <tr>
                <td class="lbl">Tanggal Cetak</td>
                <td class="sep">:</td>
                <td>{{ $tanggalCetak->translatedFormat('l, d F Y') }}</td>
            </tr>
        </table>

        {{-- ══ TABEL TRANSAKSI ══ --}}
        @if ($rows->isEmpty())
            <p style="text-align:center;font-style:italic;margin-top:20px;color:#555;">
                Tidak ada data transaksi dalam periode dan filter yang dipilih.
            </p>
        @else
            <table class="data" cellspacing="0" cellpadding="0">
                @if ($showDetail)
                    <colgroup>
                        <col class="c-no">
                        <col class="c-tgl">
                        <col class="c-jen">
                        <col class="c-pas">
                        <col class="c-poin">
                        <col class="c-ket">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Pasal / Aturan</th>
                            <th>Poin</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                @else
                    <colgroup>
                        <col class="c-no-r">
                        <col class="c-tgl-r">
                        <col class="c-jen-r">
                        <col class="c-pas-r">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Pasal / Aturan</th>
                        </tr>
                    </thead>
                @endif
                <tbody>
                    @foreach ($rows as $i => $row)
                        @php
                            $isPel = ($row['jenis'] ?? '') === 'pelanggaran';
                            $poin = (int) ($row['poin'] ?? 0);
                            $tglFmt =
                                isset($row['tgl']) && $row['tgl']
                                    ? \Carbon\Carbon::parse($row['tgl'])->format('d/m/Y')
                                    : '-';
                        @endphp
                        <tr>
                            <td class="c">{{ $i + 1 }}</td>
                            <td class="c">{{ $tglFmt }}</td>
                            <td class="c">
                                @if ($isPel)
                                    <span class="lbl-pel">Pelanggaran</span>
                                @else
                                    <span class="lbl-prg">Penghargaan</span>
                                @endif
                            </td>
                            <td>{{ $row['idpasal'] ?? '-' }}</td>
                            @if ($showDetail)
                                <td class="c">
                                    @if ($isPel)
                                        <span class="lbl-pel">{{ $poin }}</span>
                                    @else
                                        <span class="lbl-prg">+{{ $poin }}</span>
                                    @endif
                                </td>
                                <td>{{ $row['ket'] ?? '-' }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- ══ RINGKASAN ══ --}}
            <div class="ringkasan-wrap">
                <table class="ringkasan" cellspacing="0" cellpadding="0">
                    <tr>
                        <th colspan="2">Ringkasan Poin — {{ $namaKelas }}</th>
                    </tr>
                    @if ($jenis === 'semua' || $jenis === 'pelanggaran')
                        <tr>
                            <td class="lbl">Total Poin Pelanggaran (periode)</td>
                            <td class="val"><span class="lbl-pel">{{ $totalPel }}</span></td>
                        </tr>
                    @endif
                    @if ($jenis === 'semua' || $jenis === 'penghargaan')
                        <tr>
                            <td class="lbl">Total Poin Penghargaan (periode)</td>
                            <td class="val"><span class="lbl-prg">{{ $totalPrg }}</span></td>
                        </tr>
                    @endif
                    <tr class="tot">
                        <td class="lbl">Jumlah Transaksi</td>
                        <td class="val">{{ $totalEntri }}</td>
                    </tr>
                    <tr class="tot">
                        <td class="lbl">Akumulasi Poin Periode*</td>
                        <td class="val">{{ $akumLabel }}</td>
                    </tr>
                    <tr class="sisa">
                        <td class="lbl">Sisa Poin Tahun Ajaran {{ $tahunAjaran }}</td>
                        <td class="val">{!! $sisaLabel !!}</td>
                    </tr>
                </table>
            </div>
        @endif

        {{-- ══ CATATAN KAKI ══ --}}
        <div class="footnote">
            * Akum. Poin = akumulasi total poin dalam periode laporan ini per siswa.
            &nbsp;|&nbsp;
            Sisa Poin = sisa poin total tahun ajaran {{ $tahunAjaran }}
            (Poin awal {{ \App\Services\TatibPoinService::POIN_AWAL_EDARAN }} + Penghargaan &minus; Pelanggaran).
            &nbsp;|&nbsp;
            <span class="lbl-pel">Pelanggaran</span> dikurangi dari poin,
            <span class="lbl-prg">Penghargaan</span> menambah poin.
        </div>

        {{-- ══ TANDA TANGAN ══ --}}
        <div class="ttd-tempat">{{ $kab }}, {{ $tanggalCetak->translatedFormat('d F Y') }}</div>
        <table class="ttd" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <div>Guru BK / Wali Kelas,</div>
                    <div class="ttd-spasi"></div>
                    <div class="ttd-nama">{{ $cetakUser }}</div>
                    <div class="ttd-nip">NIP. &mdash;</div>
                </td>
                <td>
                    <div>Mengetahui,</div>
                    <div>Kepala Sekolah,</div>
                    <div>{{ $nmSek }},</div>
                    <div class="ttd-spasi"></div>
                    <div class="ttd-nama">{{ $namaKs ?: '-' }}</div>
                    <div class="ttd-nip">NIP. {{ $nipKs ?: '-' }}</div>
                </td>
            </tr>
        </table>

    </div>{{-- /.wrap --}}
</body>

</html>
