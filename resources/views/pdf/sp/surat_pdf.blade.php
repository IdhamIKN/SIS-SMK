<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Panggilan Orang Tua - {{ $namaSekolah }}</title>
    <style>
        /* =========================================================
           CATATAN:
           View ini KHUSUS untuk di-render oleh Dompdf (bukan browser).
           Aturan yang dipatuhi supaya layout tidak rusak:
           - TIDAK ada display:flex (Dompdf tidak konsisten menghitungnya)
             -> semua layout dua/tiga kolom pakai <table>
           - TIDAK ada icon font (Font Awesome dsb) -> tidak ke-load di Dompdf
           - TIDAK ada JavaScript / tombol / modal -> tidak relevan di file PDF
           - Semua lebar eksplisit dihitung terhadap area cetak:
             page width 210mm - padding kiri 25mm - padding kanan 20mm
             = lebar konten maksimal 165mm
        ========================================================= */
        @page {
            size: A4;
            margin: 100mm 20mm 20mm 25mm;
            /* atas kanan bawah kiri */
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .page {
            width: 165mm;
            /* = 210mm - 25mm - 20mm, konsisten dengan padding halaman */
            margin: 8mm auto 0;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        /* === KOP SURAT (table, bukan flex) === */
        .kop-table {
            width: 100%;
            margin-top: 8mm;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .kop-table td {
            vertical-align: middle;
            padding: 0;
        }

        .kop-logo-col {
            width: 70px;
        }

        .kop-logo-col img {
            width: 65px;
            height: 65px;
        }

        .kop-logo-placeholder {
            width: 65px;
            height: 65px;
            border: 2px solid #000;
            border-radius: 50%;
            text-align: center;
            font-size: 7pt;
            color: #555;
            display: table-cell;
            vertical-align: middle;
        }

        .kop-text-col {
            text-align: center;
        }

        .kop-text-col .prov,
        .kop-text-col .dinas {
            font-size: 11pt;
        }

        .kop-text-col .sekolah {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 2px;
        }

        .kop-text-col .alamat {
            font-size: 9pt;
            margin-top: 2px;
        }

        .kop-text-col .kota {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 2px;
        }

        .kop-divider {
            border: none;
            border-top: 3px double #000;
            margin: 6px 0 10px;
        }

        /* === HEADER META SURAT (table, bukan flex) === */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .meta-table>tbody>tr>td {
            vertical-align: top;
        }

        .meta-left-col {
            width: 55%;
        }

        .meta-right-col {
            width: 45%;
        }

        .meta-detail-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        .meta-detail-table td.label {
            width: 62px;
        }

        .meta-detail-table td.titik {
            width: 12px;
        }

        .alamat-tujuan {
            padding-left: 12px;
        }

        /* === JUDUL REKAP (halaman 2) === */
        .judul-rekap {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 4px;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .subjudul-rekap {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        /* === BADAN SURAT === */
        .salam-pembuka {
            margin-bottom: 10px;
        }

        .body-paragraf {
            text-align: justify;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        /* === DETAIL UNDANGAN === */
        .undangan-detail {
            width: 100%;
            margin: 10px 0;
        }

        .undangan-detail td {
            padding: 2px 0;
            vertical-align: top;
        }

        .undangan-detail td.label {
            width: 90px;
        }

        .undangan-detail td.titik {
            width: 14px;
            text-align: center;
        }

        /* === PENUTUP === */
        .penutup {
            text-align: justify;
            line-height: 1.6;
            margin-top: 8px;
        }

        /* === TANDA TANGAN (table, bukan flex justify-content:flex-end) === */
        .ttd-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .ttd-table td {
            vertical-align: top;
        }

        .ttd-spacer-col {
            width: 55%;
        }

        .ttd-block-col {
            width: 45%;
            text-align: center;
        }

        .ttd-kota-tanggal {
            margin-bottom: 2px;
        }

        .ttd-jabatan {
            margin-bottom: 2px;
        }

        .ttd-subjabatan {
            margin-bottom: 50px;
        }

        .ttd-nama {
            font-weight: bold;
            border-bottom: 1px solid #000;
            display: inline-block;
            padding-bottom: 1px;
        }

        .ttd-nip {
            margin-top: 4px;
        }

        /* === REKAP POIN === */
        .rekap-intro {
            text-align: justify;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .rekap-section-title {
            font-weight: bold;
            font-size: 11pt;
            margin: 10px 0 4px;
        }

        .tabel-poin {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5pt;
            margin-bottom: 4px;
        }

        .tabel-poin thead tr {
            background-color: #d9d9d9;
        }

        .tabel-poin th,
        .tabel-poin td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .tabel-poin th {
            text-align: center;
            font-weight: bold;
        }

        .tabel-poin td.center {
            text-align: center;
        }

        .tabel-poin .col-no {
            width: 6%;
        }

        .tabel-poin .col-tgl {
            width: 15%;
        }

        .tabel-poin .col-pasal {
            width: 46%;
        }

        .tabel-poin .col-poin {
            width: 8%;
        }

        .tabel-poin .col-ket {
            width: 25%;
        }

        .tabel-total {
            text-align: right;
            font-weight: bold;
            font-size: 11pt;
            margin: 4px 0 12px;
        }

        .poin-negatif {
            color: #c00;
        }

        .poin-positif {
            color: #060;
        }
    </style>
</head>

<body>

    <!-- ================================================================ -->
    <!-- HALAMAN 1 : SURAT PANGGILAN                                      -->
    <!-- ================================================================ -->
    <div class="page" id="halaman-surat">

        <!-- KOP SURAT -->
        <table class="kop-table">
            <tr>
                <td class="kop-logo-col">
                    @if ($logoJatim)
                        <img src="{{ $logoJatim }}" alt="Logo Jawa Timur">
                    @else
                        <div class="kop-logo-placeholder">LOGO<br>JATIM</div>
                    @endif
                </td>
                <td class="kop-text-col">
                    <div class="prov">PEMERINTAH PROVINSI JAWA TIMUR</div>
                    <div class="dinas">DINAS PENDIDIKAN</div>
                    <div class="sekolah">{{ strtoupper($namaSekolah) }}</div>
                    <div class="alamat">
                        {{ $alamatSekolah }}&nbsp;&nbsp;Telp. {{ $telpSekolah }}&nbsp;&nbsp;E-mail : {{ $emailSekolah }}
                    </div>
                    <div class="kota">{{ strtoupper($kabupaten) }}</div>
                </td>
                <td class="kop-logo-col">
                    @if ($logoSmk)
                        <img src="{{ $logoSmk }}" alt="Logo {{ $namaSekolah }}">
                    @else
                        <div class="kop-logo-placeholder">LOGO<br>SMK</div>
                    @endif
                </td>
            </tr>
        </table>
        <hr class="kop-divider">

        <!-- HEADER META SURAT -->
        <table class="meta-table">
            <tr>
                <td class="meta-left-col">
                    <table class="meta-detail-table">
                        <tr>
                            <td class="label">No.</td>
                            <td class="titik">:</td>
                            <td>{{ $surat->nomor_surat ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Lampiran</td>
                            <td class="titik">:</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td class="label">Hal</td>
                            <td class="titik">:</td>
                            <td>Panggilan Orang Tua/Wali
                                {{ ['I', 'II', 'III', 'IV', 'V'][$surat->panggilan_ke - 1] ?? $surat->panggilan_ke }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Sifat</td>
                            <td class="titik">:</td>
                            <td>Penting</td>
                        </tr>
                    </table>
                </td>
                <td class="meta-right-col">
                    <div>Kepada</div>
                    <div class="alamat-tujuan">
                        Yth. Bapak / Ibu Orang tua / Wali Murid<br>
                        Dari <strong>{{ strtoupper($siswa->nama_lengkap) }}</strong><br>
                        Kelas <strong>{{ $siswa->kelas?->nama_kelas ?? '-' }}</strong><br>
                        Di<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;Tempat
                    </div>
                </td>
            </tr>
        </table>

        <!-- SALAM PEMBUKA -->
        <div class="salam-pembuka">Dengan hormat,</div>

        <!-- PARAGRAF ISI -->
        <div class="body-paragraf">
            Pendidikan merupakan modal untuk meraih sukses masa depan. Keberhasilan pendidikan akan menjadikan
            peserta didik mempunyai kesempatan lebih besar untuk sukses dimasa depan. Pendidikan peserta didik akan
            berjalan baik serta berhasil bila terjalin komunikasi dan kerjasama yang baik antar sekolah dan keluarga.
            Untuk itu kami mengundang Bapak/Ibu/Wali murid dari
            <strong>{{ strtoupper($siswa->nama_lengkap) }}</strong>
            kelas <strong>{{ $siswa->kelas?->nama_kelas ?? '-' }}</strong>
            untuk datang pada :
        </div>

        <!-- DETAIL UNDANGAN -->
        <table class="undangan-detail">
            <tr>
                <td class="label">Hari</td>
                <td class="titik">:</td>
                <td>{{ $surat->hari }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td class="titik">:</td>
                <td>{{ $surat->tanggal_acara?->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td class="label">Waktu</td>
                <td class="titik">:</td>
                <td>{{ $surat->waktu }}</td>
            </tr>
            <tr>
                <td class="label">Tempat</td>
                <td class="titik">:</td>
                <td>{{ $surat->lokasi }}</td>
            </tr>
            @if ($surat->menemui)
                <tr>
                    <td class="label">Menemui</td>
                    <td class="titik">:</td>
                    <td>{{ $surat->menemui }}</td>
                </tr>
            @endif
            @if ($surat->keperluan)
                <tr>
                    <td class="label">Keperluan</td>
                    <td class="titik">:</td>
                    <td>{{ $surat->keperluan }}</td>
                </tr>
            @endif
        </table>

        <!-- PENUTUP -->
        @if ($surat->dengan_materai)
            <div class="penutup">
                Karena pentingnya acara mohon tidak diwakilkan dan tepat waktu serta membawa materai
                Rp. 10.000 untuk membuat surat pernyataan. Demikian yang dapat kami sampaikan dan terima kasih.
            </div>
        @else
            <div class="penutup">
                Demikian yang dapat kami sampaikan dan terima kasih.
            </div>
        @endif

        <!-- TANDA TANGAN -->
        @php
            $jabatan = $surat->gtk?->jabatan ?? '';
            $isKepsek =
                str_contains(strtolower($jabatan), 'kepala sekolah') ||
                str_contains(strtolower($jabatan), 'kepala');
        @endphp
        <table class="ttd-table">
            <tr>
                <td class="ttd-spacer-col">&nbsp;</td>
                <td class="ttd-block-col">
                    <div class="ttd-kota-tanggal">
                        Madiun, {{ $surat->tanggal_surat?->translatedFormat('d F Y') }}
                    </div>
                    <div class="ttd-jabatan">
                        @if ($isKepsek)
                            {{ $jabatan }}
                        @else
                            &nbsp;
                        @endif
                    </div>
                    @if (!$isKepsek && $jabatan)
                        <div class="ttd-subjabatan">{{ $jabatan }}<br>SMKN 5 Madiun</div>
                    @else
                        <div class="ttd-subjabatan">&nbsp;</div>
                    @endif
                    <div class="ttd-nama">{{ $surat->gtk?->nama_lengkap ?? '-' }}</div>
                    <div class="ttd-nip">
                        NIP. {{ $surat->gtk?->nip ?: '-' }}
                    </div>
                </td>
            </tr>
        </table>

    </div>
    <!-- end halaman-surat -->


    <!-- ================================================================ -->
    <!-- HALAMAN 2 : REKAP POIN PUNISHMENT & REWARD                       -->
    <!-- ================================================================ -->
    <div class="page" id="halaman-rekap">

        <!-- KOP SURAT (identik) -->
        <table class="kop-table">
            <tr>
                <td class="kop-logo-col">
                    @if ($logoJatim)
                        <img src="{{ $logoJatim }}" alt="Logo Jawa Timur">
                    @else
                        <div class="kop-logo-placeholder">LOGO<br>JATIM</div>
                    @endif
                </td>
                <td class="kop-text-col">
                    <div class="prov">PEMERINTAH PROVINSI JAWA TIMUR</div>
                    <div class="dinas">DINAS PENDIDIKAN</div>
                    <div class="sekolah">{{ strtoupper($namaSekolah) }}</div>
                    <div class="alamat">
                        {{ $alamatSekolah }}&nbsp;&nbsp;Telp. {{ $telpSekolah }}&nbsp;&nbsp;E-mail : {{ $emailSekolah }}
                    </div>
                    <div class="kota">{{ strtoupper($kabupaten) }}</div>
                </td>
                <td class="kop-logo-col">
                    @if ($logoSmk)
                        <img src="{{ $logoSmk }}" alt="Logo {{ $namaSekolah }}">
                    @else
                        <div class="kop-logo-placeholder">LOGO<br>SMK</div>
                    @endif
                </td>
            </tr>
        </table>
        <hr class="kop-divider">

        <!-- JUDUL REKAP -->
        <div class="judul-rekap">REKAP POIN PUNISHMENT DAN REWARD SISWA</div>
        <div class="subjudul-rekap">TAHUN AJARAN {{ $tahunAjaran }}</div>

        <!-- ALAMAT TUJUAN REKAP -->
        <div style="margin-bottom:14px;">
            <div>Kepada</div>
            <div class="alamat-tujuan">
                Yth. Bapak / Ibu Orang tua / Wali Murid<br>
                Dari <strong>{{ strtoupper($siswa->nama_lengkap) }}</strong><br>
                Kelas <strong>{{ $siswa->kelas?->nama_kelas ?? '-' }}</strong><br>
                Di<br>
                &nbsp;&nbsp;&nbsp;&nbsp;Tempat
            </div>
        </div>

        <!-- PARAGRAF PENGANTAR -->
        <div class="rekap-intro">
            Diberitahukan dengan hormat, bahwa putra/putri Bapak/Ibu berdasarkan catatan kami telah mengumpulkan
            point sebanyak <strong>{{ $totalPelanggaran }}</strong> poin, dengan rincian sebagai berikut :
        </div>

        <!-- TABEL PUNISHMENT -->
        <div class="rekap-section-title">Punishment / Pelanggaran</div>
        <table class="tabel-poin">
            <thead>
                <tr>
                    <th class="col-no">No.</th>
                    <th class="col-tgl">Tanggal</th>
                    <th class="col-pasal">Bentuk / Uraian</th>
                    <th class="col-poin">Poin</th>
                    <th class="col-ket">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pelanggaran as $idx => $item)
                    <tr>
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="center">{{ $item->tanggal?->format('d/m/Y') }}</td>
                        <td>{{ $item->subPasal?->pasal ?? ($item->ket ?? '-') }}</td>
                        <td class="center poin-negatif">{{ $item->poinp }}</td>
                        <td>{{ $item->ket ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;color:#555;font-style:italic;">
                            Tidak ada data pelanggaran.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="tabel-total">
            Total Poin Punishment / Pelanggaran : {{ $totalPelanggaran }}
        </div>

        <!-- TABEL REWARD -->
        <div class="rekap-section-title">Reward / Penghargaan</div>
        <table class="tabel-poin">
            <thead>
                <tr>
                    <th class="col-no">No.</th>
                    <th class="col-tgl">Tanggal</th>
                    <th class="col-pasal">Bentuk / Uraian</th>
                    <th class="col-poin">Poin</th>
                    <th class="col-ket">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($penghargaan as $idx => $item)
                    <tr>
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="center">{{ $item->tanggal?->format('d/m/Y') }}</td>
                        <td>{{ $item->subPasal?->pasal ?? ($item->ket ?? '-') }}</td>
                        <td class="center poin-positif">{{ $item->poinr }}</td>
                        <td>{{ $item->ket ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;color:#555;font-style:italic;">
                            Tidak ada data penghargaan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="tabel-total">
            Total Poin Reward / Penghargaan : {{ $totalPenghargaan }}
        </div>

    </div>
    <!-- end halaman-rekap -->

</body>

</html>