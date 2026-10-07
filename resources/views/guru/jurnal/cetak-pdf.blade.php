<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekap Jurnal Mengajar</title>
    <style>
        @page {
            size: A4 landscape;
            margin-top: 12mm;
            margin-right: 10mm;
            margin-bottom: 14mm;
            margin-left: 15mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            width: 100%;
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 8pt;
            color: #000;
            line-height: 1.35;
            background: #fff;
        }

        .wrap {
            width: 272mm;
            max-width: 272mm;
            margin: 0 auto;
        }

        /* ── KOP ── */
        table.kop { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        td.kop-logo { width: 56px; text-align: center; vertical-align: middle; }
        td.kop-logo img { width: 50px; height: 50px; object-fit: contain; display: block; margin: 0 auto; }
        td.kop-logo .ph { width: 50px; height: 50px; border: 1.5px solid #000; border-radius: 50%;
            display: inline-block; font-size: 5pt; color: #555; text-align: center; line-height: 50px; }
        td.kop-text { text-align: center; vertical-align: middle; padding: 0 6px; }
        .t-prov   { font-size: 8.5pt; }
        .t-dinas  { font-size: 8.5pt; }
        .t-sek    { font-size: 13pt; font-weight: bold; margin-top: 2px; }
        .t-adr    { font-size: 7.5pt; margin-top: 1px; }
        .t-kota   { font-size: 10pt; font-weight: bold; margin-top: 2px; }
        .kop-line { border-top: 1px solid #000; border-bottom: 3px solid #000; height: 3px; margin: 4px 0 6px; }

        /* ── JUDUL ── */
        .judul-wrap  { text-align: center; margin: 4px 0 5px; }
        .judul-utama { font-size: 11pt; font-weight: bold; text-decoration: underline;
                       text-transform: uppercase; letter-spacing: 0.3px; }
        .judul-sub   { font-size: 8pt; margin-top: 2px; }

        /* ── INFO ── */
        table.info { width: 62%; border-collapse: collapse; font-size: 8pt; margin-bottom: 8px; }
        table.info td { padding: 1.5px 2px; vertical-align: top; }
        table.info td.lbl { width: 120px; font-weight: bold; white-space: nowrap; }
        table.info td.sep { width: 10px; }

        /* ── TABEL DATA ── */
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
            background: #1D4ED8;
            color: #fff;
        }
        table.data td {
            border: 1px solid #ccc;
            padding: 2.5px 3px;
            vertical-align: top;
        }
        table.data td.c  { text-align: center; }
        tr.even td { background: #f8fafc; }

        col.c-no    { width: 4%; }
        col.c-tgl   { width: 8%; }
        col.c-hari  { width: 7%; }
        col.c-kls   { width: 7%; }
        col.c-mapel { width: 15%; }
        col.c-jam   { width: 5%; }
        col.c-wmul  { width: 6%; }
        col.c-wsel  { width: 6%; }
        col.c-guru  { width: 14%; }
        col.c-kd    { width: 7%; }
        col.c-hdr   { width: 4%; }
        col.c-th    { width: 4%; }
        col.c-nsis  { width: 10%; }
        col.c-desk  { width: 13%; }

        /* ── FOOTER ── */
        .ttd-tempat { text-align: right; margin: 14px 0 2px; font-size: 8.5pt; }
        table.ttd   { width: 100%; margin-top: 4px; font-size: 8.5pt; border-collapse: collapse; }
        table.ttd td { width: 50%; text-align: center; vertical-align: top; padding: 0 10px; }
        .ttd-spasi  { height: 42px; }
        .ttd-nama   { font-weight: bold; text-decoration: underline; }
        .ttd-nip    { font-size: 7.5pt; margin-top: 1px; }

        .page-break { page-break-after: always; }
        .empty-msg  { text-align: center; margin-top: 24px; font-style: italic; color: #555; }

        /* ── TOOLBAR LAYAR ── */
        .print-toolbar {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: #1e293b; color: #f1f5f9;
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; padding: 10px 20px; z-index: 9999;
            font-family: Arial, sans-serif; font-size: 9pt;
            box-shadow: 0 -2px 12px rgba(0,0,0,.35);
        }
        .print-toolbar .pt-info { flex: 1; color: #94a3b8; font-size: 8pt; }
        .print-toolbar .pt-info strong { color: #f1f5f9; }
        .print-toolbar .pt-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 18px; border-radius: 7px; font-size: 9pt;
            font-weight: 700; cursor: pointer; border: none; font-family: inherit;
        }
        .print-toolbar .pt-btn-print { background: #1D4ED8; color: #fff; }
        .print-toolbar .pt-btn-close { background: #334155; color: #cbd5e1; }

        @media screen { body.has-toolbar { padding-bottom: 56px; } }

        @media print {
            @page { size: A4 landscape; margin-top:12mm; margin-right:10mm; margin-bottom:14mm; margin-left:15mm; }
            html, body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .print-toolbar { display: none !important; }
            table.data thead { display: table-header-group; }
        }
    </style>
</head>
<body class="{{ isset($printFallback) ? 'has-toolbar' : '' }}">
@php
    $sd       = sekolah_data();
    $nmSek    = $sd['nama']      ?? config('sekolah.nama',    'SMK NEGERI 5 MADIUN');
    $alamat   = $sd['alamat']    ?? config('sekolah.alamat',  '-');
    $telp     = $sd['telp']      ?? config('sekolah.telepon', '-');
    $email    = $sd['email']     ?? config('sekolah.email',   '-');
    $kab      = $sd['kabupaten'] ?? 'Madiun';
    $namaKs   = $sd['nama_ks']   ?? '-';
    $nipKs    = $sd['nip_ks']    ?? '-';

    $hari = [0=>'Minggu',1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu'];
@endphp

<div class="wrap">

    {{-- KOP --}}
    <table class="kop" cellspacing="0" cellpadding="0">
        <tr>
            <td class="kop-logo">
                @if(!empty($logoJatim))
                    <img src="{{ $logoJatim }}" alt="Logo Jatim">
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
                @if(!empty($logoSmk))
                    <img src="{{ $logoSmk }}" alt="Logo SMK">
                @else
                    <div class="ph">LOGO<br>SMK</div>
                @endif
            </td>
        </tr>
    </table>
    <div class="kop-line"></div>

    {{-- JUDUL --}}
    <div class="judul-wrap">
        <div class="judul-utama">Rekap Jurnal Mengajar Guru</div>
        <div class="judul-sub">
            @if($filterGuru)Guru: {{ $filterGuru }} &nbsp;|&nbsp;@endif
            @if($filterKelas)Kelas: {{ $filterKelas }} &nbsp;|&nbsp;@endif
            @if($filterMapel)Mapel: {{ $filterMapel }}@endif
        </div>
    </div>

    {{-- INFO --}}
    <table class="info" cellspacing="0" cellpadding="0">
        <tr>
            <td class="lbl">Periode</td>
            <td class="sep">:</td>
            <td>{{ \Carbon\Carbon::parse($tanggalMulai)->translatedFormat('d F Y') }}
                s.d. {{ \Carbon\Carbon::parse($tanggalSelesai)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">Total Jurnal</td>
            <td class="sep">:</td>
            <td>{{ $jurnals->count() }} entri</td>
        </tr>
        <tr>
            <td class="lbl">Dicetak oleh</td>
            <td class="sep">:</td>
            <td>{{ $cetakUser }}</td>
        </tr>
        <tr>
            <td class="lbl">Tanggal Cetak</td>
            <td class="sep">:</td>
            <td>{{ now()->translatedFormat('l, d F Y H:i') }}</td>
        </tr>
    </table>

    {{-- TABEL --}}
    @if($jurnals->isEmpty())
        <p class="empty-msg">Tidak ada data jurnal untuk filter dan periode yang dipilih.</p>
    @else
        <table class="data" cellspacing="0" cellpadding="0">
            <colgroup>
                <col class="c-no">
                <col class="c-tgl">
                <col class="c-hari">
                <col class="c-kls">
                <col class="c-mapel">
                <col class="c-jam">
                <col class="c-wmul">
                <col class="c-wsel">
                <col class="c-guru">
                <col class="c-kd">
                <col class="c-hdr">
                <col class="c-th">
                <col class="c-nsis">
                <col class="c-desk">
            </colgroup>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>Kelas</th>
                    <th>Mata Pelajaran</th>
                    <th>Jam<br>Ke</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Guru</th>
                    <th>Kode<br>Guru</th>
                    <th>H</th>
                    <th>TH</th>
                    <th>Siswa Tidak Hadir</th>
                    <th>Materi / Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jurnals as $i => $jurnal)
                @php
                    $jamMulai   = $jurnal->jam_mulai   ? \Carbon\Carbon::parse($jurnal->jam_mulai)->format('H:i')   : '-';
                    $jamSelesai = $jurnal->jam_selesai  ? \Carbon\Carbon::parse($jurnal->jam_selesai)->format('H:i')  : '-';
                    $tgl        = \Carbon\Carbon::parse($jurnal->tanggal);
                @endphp
                <tr class="{{ $i % 2 === 1 ? 'even' : '' }}">
                    <td class="c">{{ $i + 1 }}</td>
                    <td class="c">{{ $tgl->format('d/m/Y') }}</td>
                    <td class="c">{{ $hari[$tgl->dayOfWeek] ?? '-' }}</td>
                    <td>{{ $jurnal->kelas ?? '-' }}</td>
                    <td>{{ $jurnal->pelajaran ?? '-' }}</td>
                    <td class="c">{{ $jurnal->jamke ?? '-' }}</td>
                    <td class="c">{{ $jamMulai }}</td>
                    <td class="c">{{ $jamSelesai }}</td>
                    <td>{{ $jurnal->guru ?? '-' }}</td>
                    <td class="c">{{ $jurnal->kdguru ?? '-' }}</td>
                    <td class="c">{{ (int)$jurnal->siswahadir }}</td>
                    <td class="c">{{ (int)$jurnal->siswatdkhadir }}</td>
                    <td style="font-size:7pt;">{{ $jurnal->namasiswa ?? '-' }}</td>
                    <td style="font-size:7pt;">{{ strip_tags($jurnal->deskripsi ?? '-') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- TTD --}}
    <div class="ttd-tempat">
        {{ $kab }}, {{ now()->translatedFormat('d F Y') }}
    </div>
    <table class="ttd" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                Mengetahui,<br>Kepala Sekolah<br>
                <div class="ttd-spasi"></div>
                <div class="ttd-nama">{{ $namaKs }}</div>
                <div class="ttd-nip">NIP. {{ $nipKs }}</div>
            </td>
            <td>
                Koordinator Kurikulum<br><br>
                <div class="ttd-spasi"></div>
                <div class="ttd-nama">____________________</div>
                <div class="ttd-nip">NIP. ____________________</div>
            </td>
        </tr>
    </table>

</div>{{-- /wrap --}}

@if(isset($printFallback))
<div class="print-toolbar">
    <div class="pt-info">
        <strong>Rekap Jurnal Mengajar</strong> &nbsp;—&nbsp;
        {{ \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($tanggalSelesai)->format('d/m/Y') }}
        &nbsp;&middot;&nbsp; {{ $jurnals->count() }} jurnal
    </div>
    <button class="pt-btn pt-btn-print" onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
    <button class="pt-btn pt-btn-close" onclick="window.close()">&#10005; Tutup</button>
</div>
@endif

</body>
</html>
