<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Panggilan Orang Tua - SMK Negeri 5 Madiun</title>
    <style>
        /* === RESET & BASE === */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #000;
            background: #e8e8e8;
        }

        /* === PAGE WRAPPER === */
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm 20mm 15mm 25mm;
            background: #fff;
            margin: 0 auto 20px;
            position: relative;
            page-break-after: always;
        }

        /* === KOP SURAT === */
        .kop {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 4px;
        }

        .kop-logo {
            width: 70px;
            height: 70px;
            flex-shrink: 0;
        }

        .kop-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .kop-logo-placeholder {
            width: 70px;
            height: 70px;
            border: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7pt;
            text-align: center;
            color: #555;
            flex-shrink: 0;
            border-radius: 50%;
        }

        .kop-text {
            flex: 1;
            text-align: center;
        }

        .kop-text .prov {
            font-size: 11pt;
            font-weight: normal;
            letter-spacing: .3px;
        }

        .kop-text .dinas {
            font-size: 11pt;
            font-weight: normal;
        }

        .kop-text .sekolah {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 2px;
        }

        .kop-text .alamat {
            font-size: 9pt;
            margin-top: 2px;
        }

        .kop-text .kota {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 2px;
        }

        .kop-divider {
            border: none;
            border-top: 3px double #000;
            margin: 6px 0 10px;
        }

        /* === SURAT HEADER META === */
        .surat-meta {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .surat-meta-left table {
            border-collapse: collapse;
        }

        .surat-meta-left td {
            padding: 1px 0;
            vertical-align: top;
        }

        .surat-meta-left td:nth-child(2) {
            padding: 0 6px;
        }

        .surat-meta-right {
            text-align: left;
            min-width: 200px;
        }

        .surat-meta-right .alamat-tujuan {
            padding-left: 12px;
        }

        /* === JUDUL REKAP === */
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
            margin: 10px 0;
        }

        .undangan-detail table {
            border-collapse: collapse;
        }

        .undangan-detail td {
            padding: 2px 0;
            vertical-align: top;
        }

        .undangan-detail td:nth-child(1) {
            width: 90px;
        }

        .undangan-detail td:nth-child(2) {
            width: 14px;
            text-align: center;
        }

        /* === PENUTUP === */
        .penutup {
            text-align: justify;
            line-height: 1.6;
            margin-top: 8px;
        }

        /* === TANDA TANGAN === */
        .ttd-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .ttd-block {
            text-align: center;
            min-width: 220px;
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
            min-width: 180px;
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
        }

        .tabel-poin th {
            text-align: center;
            font-weight: bold;
        }

        .tabel-poin td:nth-child(1),
        .tabel-poin td:nth-child(4) {
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

        /* === PRINT === */
        @media print {
            body {
                background: #fff;
            }

            .page {
                margin: 0;
                padding: 15mm 20mm 15mm 25mm;
                box-shadow: none;
                page-break-after: always;
            }

            .no-print {
                display: none !important;
            }
        }

        @page {
            size: A4;
            margin: 0;
        }

        /* === TOOLBAR (screen only) === */
        .toolbar {
            width: 210mm;
            margin: 16px auto 8px;
            display: flex;
            gap: 8px;
        }

        .toolbar button {
            padding: 8px 20px;
            font-size: 11pt;
            cursor: pointer;
            border: 1px solid #555;
            border-radius: 4px;
            background: #fff;
            font-family: Arial, sans-serif;
        }

        .toolbar button.btn-print {
            background: #1a56a8;
            color: #fff;
            border-color: #1a56a8;
        }

        .toolbar button.btn-back {
            background: #f1f5f9;
            color: #334155;
        }

        .toolbar button:hover {
            opacity: .85;
        }

        @media screen {
            .page {
                box-shadow: 0 2px 12px rgba(0, 0, 0, .18);
            }
        }
    </style>
</head>

<body>

    <!-- TOOLBAR -->
    <div class="toolbar no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Print</button>
        <button class="btn-back" onclick="history.back()">← Kembali</button>
    </div>
    {{-- Hanya tampil di layar, tidak ikut cetak --}}
    <div class="no-print" style="margin-bottom:12px;">
        <button onclick="bukaModalWaPrev()" 
            style="background:#22c55e;color:#fff;border:none;border-radius:8px;
                   padding:9px 18px;font-size:.85rem;cursor:pointer;display:inline-flex;
                   align-items:center;gap:6px;">
            <i class="fab fa-whatsapp"></i> Kirim WA ke Orang Tua
        </button>
    </div>
    
    {{-- Modal --}}
    <div id="modal-wa-prev" style="display:none;position:fixed;inset:0;z-index:9999;
         background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:14px;padding:24px;width:min(400px,90vw);">
            <h3 style="margin:0 0 14px;font-size:.95rem;">
                <i class="fab fa-whatsapp" style="color:#15803d;"></i> Kirim WhatsApp
            </h3>
            <form method="POST" action="{{ route('admin.surat-panggilan.kirim-wa', $surat) }}">
                @csrf
                <label style="font-size:.82rem;font-weight:600;display:block;margin-bottom:4px;">
                    Nomor HP Orang Tua
                </label>
                <input type="text" name="nomor_hp"
                    value="{{ $siswa?->no_hp_ortu ?? '' }}"
                    style="width:100%;border:1px solid #e2e8f0;border-radius:8px;
                           padding:9px 12px;font-size:.85rem;box-sizing:border-box;"
                    placeholder="08xxx / 628xxx">
                <div style="font-size:.72rem;color:#94a3b8;margin-top:5px;">
                    Link PDF akan disertakan di pesan, berlaku 7 hari.
                </div>
                <div style="display:flex;gap:8px;margin-top:14px;justify-content:flex-end;">
                    <button type="button" onclick="document.getElementById('modal-wa-prev').style.display='none'"
                        style="padding:8px 14px;border-radius:8px;border:1px solid #e2e8f0;
                               background:#f1f5f9;color:#475569;cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit"
                        style="padding:8px 16px;border-radius:8px;border:none;
                               background:#22c55e;color:#fff;cursor:pointer;font-weight:600;">
                        <i class="fab fa-whatsapp"></i> Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
    function bukaModalWaPrev() {
        document.getElementById('modal-wa-prev').style.display = 'flex';
    }
    </script>

    <!-- ================================================================ -->
    <!-- HALAMAN 1 : SURAT PANGGILAN                                      -->
    <!-- ================================================================ -->
    <div class="page" id="halaman-surat">

        <!-- KOP SURAT -->
        <div class="kop">
            @if ($logoJatim)
                <div class="kop-logo"><img src="{{ $logoJatim }}" alt="Logo Jawa Timur"></div>
            @else
                <div class="kop-logo-placeholder">LOGO<br>JATIM</div>
            @endif

            <div class="kop-text">
                <div class="prov">PEMERINTAH PROVINSI JAWA TIMUR</div>
                <div class="dinas">DINAS PENDIDIKAN</div>
                <div class="sekolah">{{ strtoupper($namaSekolah) }}</div>
                <div class="alamat">
                    {{ $alamatSekolah }}&nbsp;&nbsp;Telp. {{ $telpSekolah }}<br>
                    E-mail : {{ $emailSekolah }}
                </div>
                <div class="kota">{{ strtoupper($kabupaten) }}</div>
            </div>

            @if ($logoSmk)
                <div class="kop-logo"><img src="{{ $logoSmk }}" alt="Logo {{ $namaSekolah }}"></div>
            @else
                <div class="kop-logo-placeholder">LOGO<br>SMK</div>
            @endif
        </div>
        <hr class="kop-divider">

        <!-- HEADER META SURAT -->
        <div class="surat-meta">
            <div class="surat-meta-left">
                <table>
                    <tr>
                        <td>No.</td>
                        <td>:</td>
                        <td>{{ $surat->nomor_surat ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td>Lampiran</td>
                        <td>:</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>Hal</td>
                        <td>:</td>
                        <td>Panggilan Orang Tua/Wali
                            {{ ['I', 'II', 'III', 'IV', 'V'][$surat->panggilan_ke - 1] ?? $surat->panggilan_ke }}</td>
                    </tr>
                    <tr>
                        <td>Sifat</td>
                        <td>:</td>
                        <td>Penting</td>
                    </tr>
                </table>
            </div>

            <div class="surat-meta-right">
                <div>Kepada</div>
                <div class="alamat-tujuan">
                    Yth. Bapak / Ibu Orang tua / Wali Murid<br>
                    Dari <strong>{{ strtoupper($siswa->nama_lengkap) }}</strong><br>
                    Kelas <strong>{{ $siswa->kelas?->nama_kelas ?? '-' }}</strong><br>
                    Di<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;Tempat
                </div>
            </div>
        </div>

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
        <div class="undangan-detail">
            <table>
                <tr>
                    <td>Hari</td>
                    <td>:</td>
                    <td>{{ $surat->hari }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td>{{ $surat->tanggal_acara?->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td>{{ $surat->waktu }}</td>
                </tr>
                <tr>
                    <td>Tempat</td>
                    <td>:</td>
                    <td>{{ $surat->lokasi }}</td>
                </tr>
                @if ($surat->menemui)
                    <tr>
                        <td>Menemui</td>
                        <td>:</td>
                        <td>{{ $surat->menemui }}</td>
                    </tr>
                @endif
                @if ($surat->keperluan)
                    <tr>
                        <td>Keperluan</td>
                        <td>:</td>
                        <td>{{ $surat->keperluan }}</td>
                    </tr>
                @endif
            </table>
        </div>

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
        <div class="ttd-wrapper">
            <div class="ttd-block">
                <div class="ttd-kota-tanggal">
                    Madiun, {{ $surat->tanggal_surat?->translatedFormat('d F Y') }}
                </div>
                <div class="ttd-jabatan">
                    @php
                        $jabatan = $surat->gtk?->jabatan ?? '';
                        // Jika jabatan Kepala Sekolah, tidak perlu "an."
                        $isKepsek = str_contains(strtolower($jabatan), 'kepala sekolah')
                                 || str_contains(strtolower($jabatan), 'kepala');
                    @endphp
                    @if ($isKepsek)
                        {{ $jabatan }}
                    @else
                        
                    @endif
                </div>
                @if (!$isKepsek && $jabatan)
                    <div class="ttd-subjabatan">{{ $jabatan }} <br>SMKN 5 Madiun</div>
                @else
                    <div class="ttd-subjabatan">&nbsp;</div>
                @endif
                <div class="ttd-nama">{{ $surat->gtk?->nama_lengkap ?? '-' }}</div>
                <div class="ttd-nip">
                    NIP. {{ $surat->gtk?->nip ?: '-' }}
                </div>
            </div>
        </div>

    </div>
    <!-- end halaman-surat -->


    <!-- ================================================================ -->
    <!-- HALAMAN 2 : REKAP POIN PUNISHMENT & REWARD                       -->
    <!-- ================================================================ -->
    <div class="page" id="halaman-rekap">

        <!-- KOP SURAT (identik) -->
        <div class="kop">
            @if ($logoJatim)
                <div class="kop-logo"><img src="{{ $logoJatim }}" alt="Logo Jawa Timur"></div>
            @else
                <div class="kop-logo-placeholder">LOGO<br>JATIM</div>
            @endif

            <div class="kop-text">
                <div class="prov">PEMERINTAH PROVINSI JAWA TIMUR</div>
                <div class="dinas">DINAS PENDIDIKAN</div>
                <div class="sekolah">{{ strtoupper($namaSekolah) }}</div>
                <div class="alamat">
                    {{ $alamatSekolah }}&nbsp;&nbsp;Telp. {{ $telpSekolah }}<br>
                    E-mail : {{ $emailSekolah }}
                </div>
                <div class="kota">{{ strtoupper($kabupaten) }}</div>
            </div>

            @if ($logoSmk)
                <div class="kop-logo"><img src="{{ $logoSmk }}" alt="Logo {{ $namaSekolah }}"></div>
            @else
                <div class="kop-logo-placeholder">LOGO<br>SMK</div>
            @endif
        </div>
        <hr class="kop-divider">

        <!-- JUDUL REKAP -->
        <div class="judul-rekap">REKAP POIN PUNISHMENT DAN REWARD SISWA</div>
        <div class="subjudul-rekap">TAHUN AJARAN {{ $tahunAjaran }}</div>

        <!-- ALAMAT TUJUAN REKAP -->
        <div class="surat-meta-right" style="margin-bottom:14px;">
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
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $item->tanggal?->format('d/m/Y') }}</td>
                        <td>{{ $item->subPasal?->pasal ?? ($item->ket ?? '-') }}</td>
                        <td>{{ $item->poinp }}</td>
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
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $item->tanggal?->format('d/m/Y') }}</td>
                        <td>{{ $item->subPasal?->pasal ?? ($item->ket ?? '-') }}</td>
                        <td class="poin-positif">{{ $item->poinr }}</td>
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
