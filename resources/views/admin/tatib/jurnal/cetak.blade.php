<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Laporan Tatib</title>
    <style>
        /* ════════════════════════════════════════════
           PAGE SETUP — Landscape A4
        ════════════════════════════════════════════ */
        @page {
            size: A4 landscape;
            margin-top: 12mm;
            margin-right: 12mm;
            margin-bottom: 14mm;
            margin-left: 18mm;
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
            font-size: 8pt;
            color: #000;
            line-height: 1.35;
            background: #fff;
        }

        /* Landscape A4 cetak area = 297mm - 18mm - 12mm = 267mm */
        .wrap {
            width: 267mm;
            max-width: 267mm;
            margin: 0 auto;
        }

        /* ════════════════════════════════════════════
           KOP SURAT
        ════════════════════════════════════════════ */
        table.kop {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        td.kop-logo {
            width: 56px;
            text-align: center;
            vertical-align: middle;
            padding: 0;
        }

        td.kop-logo img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        td.kop-logo .ph {
            width: 50px;
            height: 50px;
            border: 1.5px solid #000;
            border-radius: 50%;
            display: inline-block;
            font-size: 5pt;
            color: #555;
            text-align: center;
            line-height: 50px;
        }

        td.kop-text {
            text-align: center;
            vertical-align: middle;
            padding: 0 6px;
        }

        .t-prov {
            font-size: 8.5pt;
            line-height: 1.4;
        }

        .t-dinas {
            font-size: 8.5pt;
            line-height: 1.4;
        }

        .t-sek {
            font-size: 13pt;
            font-weight: bold;
            line-height: 1.4;
            margin-top: 2px;
        }

        .t-adr {
            font-size: 7.5pt;
            line-height: 1.5;
            margin-top: 1px;
        }

        .t-kota {
            font-size: 10pt;
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

        /* ════════════════════════════════════════════
           JUDUL
        ════════════════════════════════════════════ */
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

        /* ════════════════════════════════════════════
           INFO LAPORAN
        ════════════════════════════════════════════ */
        table.info {
            width: 60%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 8px;
        }

        table.info td {
            padding: 1.5px 2px;
            vertical-align: top;
        }

        table.info td.lbl {
            width: 115px;
            font-weight: bold;
            white-space: nowrap;
        }

        table.info td.sep {
            width: 10px;
        }

        /* ════════════════════════════════════════════
           HEADER KELAS
        ════════════════════════════════════════════ */
        .kelas-header {
            font-weight: bold;
            font-size: 8.5pt;
            padding: 3px 6px;
            border: 1px solid #000;
            border-bottom: none;
            margin-top: 8px;
        }

        /* ════════════════════════════════════════════
           TABEL DATA — kolom widths
        ════════════════════════════════════════════ */
        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            table-layout: fixed;
            margin-bottom: 3px;
            word-wrap: break-word;
        }

        table.data th {
            border: 1px solid #000;
            padding: 3px 2px;
            text-align: center;
            font-weight: bold;
            font-size: 7.5pt;
            line-height: 1.3;
            background: #fff;
        }

        table.data td {
            border: 1px solid #666;
            padding: 2.5px 3px;
            vertical-align: middle;
            overflow-wrap: break-word;
            background: #fff;
        }

        table.data td.c {
            text-align: center;
        }

        table.data td.r {
            text-align: right;
        }

        col.c-no {
            width: 4%;
        }

        col.c-nama {
            width: 16%;
        }

        col.c-nis {
            width: 9%;
        }

        col.c-tgl {
            width: 9%;
        }

        col.c-urai {
            width: 36%;
        }

        col.c-poin {
            width: 6%;
        }

        col.c-akum {
            width: 10%;
        }

        col.c-sisa {
            width: 10%;
        }

        /* Mode ringkas (tanpa detail): lebar kolom dibagi ulang */
        col.c-no-r {
            width: 5%;
        }

        col.c-nama-r {
            width: 35%;
        }

        col.c-nis-r {
            width: 18%;
        }

        col.c-akum-r {
            width: 22%;
        }

        col.c-sisa-r {
            width: 20%;
        }

        /* ── CSS border-trick (untuk DomPDF / mode PDF) ──
           Kolom merged No, Nama, NISN, Akum, Sisa terlihat menyatu
           tanpa rowspan HTML sehingga aman dipotong antar halaman. */
        td.m-only {
            border-bottom: 2px solid #000;
        }

        td.m-first {
            border-bottom-color: transparent;
        }

        td.m-mid {
            border-top-color: transparent;
            border-bottom-color: transparent;
        }

        td.m-last {
            border-top-color: transparent;
            border-bottom: 2px solid #000;
        }

        tr.row-last td.det {
            border-bottom: 2px solid #000;
        }

        /* ── Rowspan mode (untuk browser HTML print) ──
           Sel rowspan mendapat border bawah tebal sebagai pemisah antar siswa. */
        td.rs-merged {
            border-bottom: 2px solid #000 !important;
            vertical-align: middle;
        }

        tr.rs-detail-last td {
            border-bottom: 2px solid #000;
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

        /* ════════════════════════════════════════════
           REKAP MINI PER KELAS
        ════════════════════════════════════════════ */
        table.rekap {
            width: 38%;
            margin: 2px 0 8px auto;
            border-collapse: collapse;
            font-size: 7.5pt;
        }

        table.rekap th {
            background: #fff;
            border: 1px solid #666;
            padding: 2px 6px;
            text-align: left;
            font-weight: bold;
        }

        table.rekap td {
            border: 1px solid #666;
            padding: 2px 6px;
            text-align: right;
            background: #fff;
        }

        table.rekap tr.tot th,
        table.rekap tr.tot td {
            font-weight: bold;
            border-color: #000;
        }

        /* ════════════════════════════════════════════
           CATATAN KAKI & TTD
        ════════════════════════════════════════════ */
        .footnote {
            font-size: 7pt;
            color: #333;
            margin-top: 10px;
            padding-top: 4px;
            border-top: 1px solid #999;
            line-height: 1.5;
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
            height: 42px;
        }

        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
        }

        .ttd-nip {
            font-size: 7.5pt;
            margin-top: 1px;
        }

        /* ════════════════════════════════════════════
           UTILITAS
        ════════════════════════════════════════════ */
        .page-break {
            page-break-after: always;
        }

        .empty-msg {
            text-align: center;
            margin-top: 24px;
            font-style: italic;
            font-size: 9pt;
            color: #555;
        }

        /* ════════════════════════════════════════════
           BROWSER PRINT — matikan header/footer bawaan
           dan atur margin cetak
        ════════════════════════════════════════════ */
        @media print {

            /* Matikan header/footer URL, judul, tanggal bawaan browser */
            @page {
                size: A4 landscape;
                margin-top: 12mm;
                margin-right: 12mm;
                margin-bottom: 14mm;
                margin-left: 18mm;
                /* Chrome/Edge: -webkit-margin-* tidak dipakai, pakai margin langsung */
            }

            html,
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-toolbar {
                display: none !important;
            }

            .fallback-banner {
                display: none !important;
            }

            /* Paksa page break antar kelas */
            .page-break {
                page-break-after: always;
                break-after: page;
            }

            /* Heading tabel ulang di tiap halaman */
            table.data thead {
                display: table-header-group;
            }
        }

        /* ════════════════════════════════════════════
           TOOLBAR LAYAR
        ════════════════════════════════════════════ */
        .fallback-banner {
            background: #fffbeb;
            border: 1px solid #f59e0b;
            border-radius: 4px;
            padding: 7px 12px;
            font-size: 8pt;
            color: #92400e;
            margin-bottom: 10px;
        }

        .print-toolbar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 20px;
            z-index: 9999;
            font-family: Arial, sans-serif;
            font-size: 9pt;
            box-shadow: 0 -2px 12px rgba(0, 0, 0, .35);
        }

        .print-toolbar .pt-info {
            flex: 1;
            color: #94a3b8;
            font-size: 8pt;
        }

        .print-toolbar .pt-info strong {
            color: #f1f5f9;
        }

        .print-toolbar .pt-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 7px;
            font-size: 9pt;
            font-weight: 700;
            cursor: pointer;
            border: none;
            font-family: inherit;
            transition: filter .15s;
        }

        .print-toolbar .pt-btn:hover {
            filter: brightness(1.12);
        }

        .print-toolbar .pt-btn-print {
            background: #7c3aed;
            color: #fff;
        }

        .print-toolbar .pt-btn-close {
            background: #334155;
            color: #cbd5e1;
        }

        @media screen {
            body.has-toolbar {
                padding-bottom: 56px;
            }
        }
    </style>
</head>

<body class="{{ !empty($printFallback) ? 'has-toolbar' : '' }}">

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

        $totalEntri = $dataPerKelas->sum(fn($k) => collect($k['siswa'])->sum(fn($s) => count($s['rows'])));

        // Mode render:
        //   useRowspan = true  → browser HTML print: pakai rowspan asli
        //   useRowspan = false → DomPDF: pakai CSS border-trick
        $useRowspan = !empty($printFallback);

        // showDetail: tampilkan kolom Tanggal, Jenis/Pasal, Poin
        // Default false → mode ringkas (jauh lebih sedikit halaman)
        $showDetail = $showDetail ?? false;
    @endphp

    <div class="wrap">

        @if (!empty($printFallback))
            <div class="fallback-banner">
                &#9888; Data terlalu besar untuk PDF otomatis — ditampilkan sebagai halaman cetak.
                Gunakan tombol <strong>Cetak / Save PDF</strong> di bawah, atau tekan <strong>Ctrl+P</strong>.
                Saat dialog cetak muncul, pastikan <strong>Headers and footers</strong> dinonaktifkan.
            </div>
        @endif

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

        {{-- ══ INFO ══ --}}
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
            {{-- <tr>
                <td class="lbl">Total Data</td>
                <td class="sep">:</td>
                <td>{{ $totalEntri }} entri dari {{ $dataPerKelas->count() }} kelas</td>
            </tr>
            <tr>
                <td class="lbl">Mode Cetak</td>
                <td class="sep">:</td>
                <td>{{ $showDetail ? 'Lengkap (dengan detail transaksi)' : 'Ringkas (tanpa detail transaksi)' }}</td>
            </tr> --}}
        </table>

        {{-- ══ DATA PER KELAS ══ --}}
        @if ($dataPerKelas->isEmpty())
            <p class="empty-msg">Tidak ada data dalam periode dan filter yang dipilih.</p>
        @else
            @foreach ($dataPerKelas as $idx => $kelasData)
                @php
                    $siswaDiKelas = collect($kelasData['siswa']);
                    $itemsFlat = collect($kelasData['items']);
                    $namaKelas = $kelasData['nama_kelas'];
                    $totalPelKelas = $itemsFlat->where('jenis', 'pelanggaran')->sum('poin');
                    $totalRwKelas = $itemsFlat->where('jenis', 'penghargaan')->sum('poin');
                    $jumlahEntri = $siswaDiKelas->sum(fn($s) => count($s['rows']));
                    $noSiswa = 1;
                @endphp

                @if ($idx > 0)
                    <div class="page-break"></div>
                @endif

                <div class="kelas-header">
                    Kelas: {{ $namaKelas }} &mdash; {{ $jumlahEntri }} data
                </div>

                <table class="data" cellspacing="0" cellpadding="0">
                    <colgroup>
                        @if ($showDetail)
                            <col class="c-no">
                            <col class="c-nama">
                            <col class="c-nis">
                            <col class="c-tgl">
                            <col class="c-urai">
                            <col class="c-poin">
                            <col class="c-akum">
                            <col class="c-sisa">
                        @else
                            <col class="c-no-r">
                            <col class="c-nama-r">
                            <col class="c-nis-r">
                            <col class="c-akum-r">
                            <col class="c-sisa-r">
                        @endif
                    </colgroup>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>NISN</th>
                            @if ($showDetail)
                                <th>Tanggal</th>
                                <th>Jenis / Pasal</th>
                                <th>Poin</th>
                            @endif
                            <th>Akum.<br>Poin*</th>
                            <th>Sisa<br>Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($siswaDiKelas as $siswaData)
                            @php
                                $sId = $siswaData['siswa_id'];
                                $rows = collect($siswaData['rows']);
                                $total = $rows->count();
                                $sisaAkhir = $sisaPoinMap[$sId] ?? '-';
                                // Label sisa poin: negatif ditampilkan merah dengan tanda minus
                                $sisaLabel = is_numeric($sisaAkhir)
                                    ? ($sisaAkhir < 0
                                        ? '<span style="color:#8b0000;font-weight:bold;">' . $sisaAkhir . '</span>'
                                        : (string) $sisaAkhir)
                                    : $sisaAkhir;

                                $finalPel = $rows->where('jenis', 'pelanggaran')->sum('poin');
                                $finalPrg = $rows->where('jenis', 'penghargaan')->sum('poin');
                                $akumFinal =
                                    $jenis === 'semua'
                                        ? 'P:' . $finalPel . ' R:' . $finalPrg
                                        : ($jenis === 'pelanggaran'
                                            ? $finalPel
                                            : $finalPrg);
                            @endphp

                            @foreach ($rows as $rIdx => $row)
                                @php
                                    $isPel = ($row['jenis'] ?? '') === 'pelanggaran';
                                    $poin = (int) ($row['poin'] ?? 0);
                                    $tglFmt =
                                        isset($row['tgl']) && $row['tgl']
                                            ? \Carbon\Carbon::parse($row['tgl'])->format('d/m/Y')
                                            : '-';

                                    $isFirst = $rIdx === 0;
                                    $isLast = $rIdx === $total - 1;
                                    $isOnly = $total === 1;
                                @endphp

                                @if ($useRowspan)
                                    {{-- ══════════════════════════════════════
                                         MODE BROWSER: rowspan asli
                                         Hanya render sel merged di baris pertama
                                    ══════════════════════════════════════ --}}
                                    @if ($showDetail)
                                        {{-- Mode lengkap: satu baris per transaksi --}}
                                        <tr class="{{ $isLast ? 'rs-detail-last' : '' }}">
                                            @if ($isFirst)
                                                <td class="c rs-merged" rowspan="{{ $total }}"
                                                    style="font-weight:bold;">{{ $noSiswa }}</td>
                                                <td class="rs-merged" rowspan="{{ $total }}"
                                                    style="font-weight:bold;">{{ $siswaData['nama'] ?? '-' }}</td>
                                                <td class="c rs-merged" rowspan="{{ $total }}">
                                                    {{ $siswaData['nisn'] ?? '-' }}</td>
                                            @endif
                                            <td class="c det">{{ $tglFmt }}</td>
                                            <td class="det">
                                                @if ($isPel)
                                                <span class="lbl-pel">[Pel]</span>@else<span
                                                        class="lbl-prg">[Prg]</span>
                                                @endif
                                                @if (!empty($row['idpasal']))
                                                    {{ $row['idpasal'] }}
                                                @endif
                                                @if (!empty($row['ket']))
                                                    — {{ $row['ket'] }}
                                                @endif
                                            </td>
                                            <td class="c det">
                                                @if ($isPel)
                                                <span class="lbl-pel">{{ $poin }}</span>@else<span
                                                        class="lbl-prg">+{{ $poin }}</span>
                                                @endif
                                            </td>
                                            @if ($isFirst)
                                                <td class="c rs-merged" rowspan="{{ $total }}">
                                                    {{ $akumFinal }}</td>
                                                <td class="c rs-merged" rowspan="{{ $total }}">
                                                    {!! $sisaLabel !!}</td>
                                            @endif
                                        </tr>
                                    @else
                                        {{-- Mode ringkas: satu baris per siswa, skip baris non-pertama --}}
                                        @if ($isFirst || $isOnly)
                                            <tr style="border-bottom:2px solid #000;">
                                                <td class="c" style="font-weight:bold;">{{ $noSiswa }}</td>
                                                <td style="font-weight:bold;">{{ $siswaData['nama'] ?? '-' }}</td>
                                                <td class="c">{{ $siswaData['nisn'] ?? '-' }}</td>
                                                <td class="c">{{ $akumFinal }}</td>
                                                <td class="c">{!! $sisaLabel !!}</td>
                                            </tr>
                                        @endif
                                    @endif
                                @else
                                    {{-- ══════════════════════════════════════
                                         MODE DOMPDF: CSS border-trick
                                         Semua sel ada di setiap baris,
                                         border dihapus untuk efek "menyatu"
                                    ══════════════════════════════════════ --}}
                                    @php
                                        $mc = $isOnly
                                            ? 'm-only'
                                            : ($isFirst
                                                ? 'm-first'
                                                : ($isLast
                                                    ? 'm-last'
                                                    : 'm-mid'));
                                    @endphp
                                    @if ($showDetail)
                                        {{-- Mode lengkap: satu baris per transaksi --}}
                                        <tr class="{{ $isLast ? 'row-last' : '' }}">
                                            <td class="c {{ $mc }}" style="font-weight:bold;">
                                                @if ($isFirst || $isOnly)
                                                    {{ $noSiswa }}
                                                @endif
                                            </td>
                                            <td class="{{ $mc }}" style="font-weight:bold;">
                                                @if ($isFirst || $isOnly)
                                                    {{ $siswaData['nama'] ?? '-' }}
                                                @endif
                                            </td>
                                            <td class="c {{ $mc }}">
                                                @if ($isFirst || $isOnly)
                                                    {{ $siswaData['nisn'] ?? '-' }}
                                                @endif
                                            </td>
                                            <td class="c det">{{ $tglFmt }}</td>
                                            <td class="det">
                                                @if ($isPel)
                                                <span class="lbl-pel">[Pel]</span>@else<span
                                                        class="lbl-prg">[Prg]</span>
                                                @endif
                                                @if (!empty($row['idpasal']))
                                                    {{ $row['idpasal'] }}
                                                @endif
                                                @if (!empty($row['ket']))
                                                    — {{ $row['ket'] }}
                                                @endif
                                            </td>
                                            <td class="c det">
                                                @if ($isPel)
                                                <span class="lbl-pel">{{ $poin }}</span>@else<span
                                                        class="lbl-prg">+{{ $poin }}</span>
                                                @endif
                                            </td>
                                            <td class="c {{ $mc }}">
                                                @if ($isFirst || $isOnly)
                                                    {{ $akumFinal }}
                                                @endif
                                            </td>
                                            <td class="c {{ $mc }}">
                                                @if ($isLast || $isOnly)
                                                    {!! $sisaLabel !!}
                                                @endif
                                            </td>
                                        </tr>
                                    @else
                                        {{-- Mode ringkas: satu baris per siswa, skip baris non-pertama --}}
                                        @if ($isFirst || $isOnly)
                                            <tr style="border-bottom:2px solid #000;">
                                                <td class="c" style="font-weight:bold;">{{ $noSiswa }}</td>
                                                <td style="font-weight:bold;">{{ $siswaData['nama'] ?? '-' }}</td>
                                                <td class="c">{{ $siswaData['nisn'] ?? '-' }}</td>
                                                <td class="c">{{ $akumFinal }}</td>
                                                <td class="c">{!! $sisaLabel !!}</td>
                                            </tr>
                                        @endif
                                    @endif
                                @endif
                            @endforeach

                            @php $noSiswa++; @endphp
                        @endforeach
                    </tbody>
                </table>

                {{-- Rekap mini per kelas --}}
                <table class="rekap" cellspacing="0" cellpadding="0">
                    <tr>
                        <th colspan="2" style="text-align:center;">Rekap Kelas {{ $namaKelas }}</th>
                    </tr>
                    @if ($jenis !== 'semua')
                        <tr>
                            <th>Total Poin {{ ucfirst($jenis) }}</th>
                            <td>{{ $jenis === 'pelanggaran' ? $totalPelKelas : $totalRwKelas }}</td>
                        </tr>
                    @else
                        <tr>
                            <th>Total Poin Pelanggaran</th>
                            <td>{{ $totalPelKelas }}</td>
                        </tr>
                        <tr>
                            <th>Total Poin Penghargaan</th>
                            <td>{{ $totalRwKelas }}</td>
                        </tr>
                    @endif
                    <tr class="tot">
                        <th>Jumlah Entri</th>
                        <td>{{ $jumlahEntri }}</td>
                    </tr>
                    <tr class="tot">
                        <th>Jumlah Siswa</th>
                        <td>{{ $siswaDiKelas->count() }}</td>
                    </tr>
                </table>
            @endforeach
        @endif

        {{-- ══ CATATAN KAKI ══ --}}
        <div class="footnote">
            * Akum. Poin = akumulasi total poin dalam periode laporan ini per siswa.
            &nbsp;|&nbsp;
            Sisa Poin = sisa poin total tahun ajaran {{ $tahunAjaran }}.
            &nbsp;|&nbsp;
            <span class="lbl-pel">Pel</span> = Pelanggaran
            &nbsp;|&nbsp;
            <span class="lbl-prg">Prg</span> = Penghargaan
        </div>

        {{-- ══ TANDA TANGAN ══ --}}
        <div class="ttd-tempat">{{ $kab }}, {{ $tanggalCetak->translatedFormat('d F Y') }}</div>
        <table class="ttd" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <div><br></div>
                    <div>Guru BK / Wali Kelas,</div>
                    <div><br></div>

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

    {{-- ══ TOOLBAR PRINT — hanya mode HTML fallback ══ --}}
    @if (!empty($printFallback))
        <div class="print-toolbar" id="printToolbar">
            <div class="pt-info">
                <strong>Mode Cetak Browser</strong> &mdash;
                {{ number_format($totalEntri) }} entri &bull;
                {{ $dataPerKelas->count() }} kelas &bull; {{ $judulJenis }}
                &mdash; <span style="color:#fbbf24;">Nonaktifkan "Headers and footers" di dialog cetak</span>
            </div>
            <button class="pt-btn pt-btn-close"
                onclick="document.getElementById('printToolbar').style.display='none'">
                ✕ Tutup
            </button>
            <button class="pt-btn pt-btn-print" onclick="window.print()">
                &#128438; Cetak / Save PDF
            </button>
        </div>
        <script>
            // Instruksi: matikan header/footer di dialog cetak browser
            // Chrome/Edge: More settings → Headers and footers → OFF
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 900);
            });
        </script>
    @endif

</body>

</html>
