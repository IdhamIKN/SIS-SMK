<!DOCTYPE html>

<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>{{ $judulJenis }} - {{ $nmSek }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&amp;family=Libre+Franklin:wght@600;700&amp;family=Source+Serif+4:wght@400&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-container": "#eeeeee",
                        "surface": "#f9f9f9",
                        "on-background": "#1b1b1b",
                        "surface-bright": "#f9f9f9",
                        "background": "#f9f9f9",
                        "on-surface-variant": "#4c4546",
                        "on-primary-fixed-variant": "#474747",
                        "surface-dim": "#dadada",
                        "inverse-on-surface": "#f1f1f1",
                        "secondary": "#555f6d",
                        "error-container": "#ffdad6",
                        "outline": "#7e7576",
                        "secondary-fixed-dim": "#bdc7d8",
                        "on-primary": "#ffffff",
                        "on-tertiary-container": "#828486",
                        "on-error-container": "#93000a",
                        "inverse-primary": "#c6c6c6",
                        "primary": "#000000",
                        "surface-tint": "#5e5e5e",
                        "on-secondary": "#ffffff",
                        "tertiary-fixed": "#e1e2e4",
                        "on-tertiary": "#ffffff",
                        "tertiary-fixed-dim": "#c5c6c8",
                        "tertiary": "#000000",
                        "on-tertiary-fixed": "#191c1e",
                        "error": "#ba1a1a",
                        "on-primary-fixed": "#1b1b1b",
                        "surface-variant": "#e2e2e2",
                        "secondary-container": "#d6e0f1",
                        "outline-variant": "#cfc4c5",
                        "secondary-fixed": "#d9e3f4",
                        "on-error": "#ffffff",
                        "primary-fixed": "#e2e2e2",
                        "on-surface": "#1b1b1b",
                        "on-secondary-fixed-variant": "#3e4755",
                        "primary-container": "#1b1b1b",
                        "surface-container-low": "#f3f3f3",
                        "on-secondary-container": "#596372",
                        "on-primary-container": "#848484",
                        "tertiary-container": "#191c1e",
                        "on-secondary-fixed": "#121c28",
                        "surface-container-high": "#e8e8e8",
                        "primary-fixed-dim": "#c6c6c6",
                        "on-tertiary-fixed-variant": "#444749",
                        "surface-container-lowest": "#ffffff",
                        "inverse-surface": "#303030",
                        "surface-container-highest": "#e2e2e2"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "page-margin-top": "20mm",
                        "gutter-table": "4pt",
                        "page-margin-bottom": "20mm",
                        "stack-standard": "16pt",
                        "stack-tight": "8pt",
                        "page-margin-inline": "15mm",
                        "section-gap": "24pt"
                    },
                    "fontFamily": {
                        "section-header": [
                            "Libre Franklin"
                        ],
                        "table-data": [
                            "Inter"
                        ],
                        "caption-fine": [
                            "Inter"
                        ],
                        "report-title-mobile": [
                            "Libre Franklin"
                        ],
                        "body-main": [
                            "\"Source Serif 4\""
                        ],
                        "report-title": [
                            "Libre Franklin"
                        ],
                        "table-header": [
                            "Inter"
                        ]
                    },
                    "fontSize": {
                        "section-header": [
                            "14pt",
                            {
                                "lineHeight": "18pt",
                                "letterSpacing": "0.05em",
                                "fontWeight": "600"
                            }
                        ],
                        "table-data": [
                            "8pt",
                            {
                                "lineHeight": "10pt",
                                "fontWeight": "400"
                            }
                        ],
                        "caption-fine": [
                            "7pt",
                            {
                                "lineHeight": "9pt",
                                "fontWeight": "400"
                            }
                        ],
                        "report-title-mobile": [
                            "20pt",
                            {
                                "lineHeight": "26pt",
                                "fontWeight": "700"
                            }
                        ],
                        "body-main": [
                            "10pt",
                            {
                                "lineHeight": "14pt",
                                "fontWeight": "400"
                            }
                        ],
                        "report-title": [
                            "24pt",
                            {
                                "lineHeight": "32pt",
                                "fontWeight": "700"
                            }
                        ],
                        "table-header": [
                            "8pt",
                            {
                                "lineHeight": "10pt",
                                "fontWeight": "700"
                            }
                        ]
                    }
                },
            },
        }
    </script>
    <style>
        /* Print Styles for A4 */
        @media print {
            @page {
                size: A4 portrait;
                margin: 20mm 15mm;
            }

            body {
                background: none;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
            }

            /* Force exact colors for printing */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        /* A4 Canvas Simulation for Web View */
        .a4-canvas {
            width: 210mm;
            min-height: 297mm;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            padding: 20mm 15mm;
            position: relative;
        }

        @media (max-width: 768px) {
            .a4-canvas {
                width: 100%;
                margin: 0;
                padding: 10mm 5mm;
                box-shadow: none;
            }
        }

        .double-border {
            border-bottom: 3px double #000;
        }
    </style>
</head>

<body class="bg-surface-container-high text-on-background antialiased font-body-main">
    <!-- Screen-only Print Button -->
    <div class="no-print fixed top-4 right-4 z-50">
        <button class="bg-primary text-on-primary px-4 py-2 rounded shadow flex items-center gap-2 hover:bg-surface-tint transition-colors" onclick="window.print()">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">print</span>
            Cetak Dokumen
        </button>
    </div>
    <div class="a4-canvas bg-surface-container-lowest">
        <!-- Kop Surat (Letterhead) -->
        <header class="flex items-center justify-between mb-4 border-b-4 border-double border-primary pb-2 double-border">
            <div class="w-24 h-24 flex-shrink-0 flex items-center justify-center">
                <img class="max-w-full max-h-full object-contain mix-blend-multiply" data-alt="Official emblem of East Java Province, a formal crest design with a shield shape, containing a star, a monument, paddy, and cotton, rendered in a professional, formal academic style in monochrome." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBGqpWrwgqGI-hS0MZYbAnWzfEMfAtLURaWSzYedJc2wRmwDXiqQzTYZ2RAtJjl_0lk6B6EJUKzrUJyIiFFpHU42Eo0QK9rdrxKyLViu2DfGCGpZgUkJp5L29LwP4aXt4DZaD7ZtTlTyCGbbQaLXi_pn6e4RjMH2s5Sp6ZQo_mRCn4SHn7aW-ByQ_4YOSjml_zib2mV4bQXsAyvO6XDXpBKDiVVB_bnvBKROieqG3uc8YpltFuHxw8hZg" />
            </div>
            <div class="flex-grow text-center px-4">
                <h1 class="font-section-header text-section-header uppercase text-primary tracking-wide">Pemerintah Provinsi Jawa Timur</h1>
                <h2 class="font-report-title text-[12.5pt] leading-tight font-bold text-primary uppercase mt-1">Dinas Pendidikan</h2>
                <h3 class="font-report-title text-[14pt] leading-tight font-extrabold text-primary uppercase mt-1">{{ $nmSek }}</h3>
                <p class="font-caption-fine text-caption-fine text-on-surface-variant mt-2">
                    {{ $alamat }}<br />
                    Telp: {{ $telp }} | Email: {{ $email }}
                </p>
            </div>
            <div class="w-24 h-24 flex-shrink-0 flex items-center justify-center">
                <img class="max-w-full max-h-full object-contain mix-blend-multiply" data-alt="Official logo of SMK Negeri 5 Madiun, a formal academic emblem with an open book and gear motif, rendered in a strict professional, formal monochrome print-ready style." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBTwESf_FYD-QWaXos3cv6SHDem6aJ3wvsRBZl3tfiuIw-iClTm5EsRmP4hzJ0JYh_Pxd63sSSC_gS5Ct7PiOvVVuWtw3xmzKr5RYp-J_GSEXet8Bx9uFsx1Ev2fpGfPPaKhJYSAI30CQgLoRpC9Rcxz_VVfZSCGi_duO223Z_CjKzgek-VNoXTAg5ozebdnrCX4NA5lnmKHMOApf4XPcQbGE_N3eKnVJuAtMa7MKRaeugd08NfP8D4cw" />
            </div>
        </header>
        <!-- Document Title -->
        <div class="text-center mb-stack-standard">
            <h4 class="font-section-header text-[12pt] font-bold text-primary uppercase underline underline-offset-4 decoration-[1.5px]">
                {{ $judulJenis }}
            </h4>
            <p class="font-caption-fine text-[9pt] font-semibold text-on-surface mt-1">
                Tahun Ajaran {{ $tahunAjaran }}
            </p>
        </div>
        <!-- Report Info Table -->
        <div class="mb-stack-standard">
            <table class="w-full border-collapse border border-outline">
                <tbody class="font-table-data text-table-data">
                    <tr class="border-b border-outline">
                        <td class="px-2 py-1 bg-surface-container-low font-bold w-1/4 border-r border-outline">Periode</td>
                        <td class="px-2 py-1 w-1/4 border-r border-outline">{{ $dariTanggal }} - {{ $sampaiTanggal }}</td>
                        <td class="px-2 py-1 bg-surface-container-low font-bold w-1/4 border-r border-outline">Tanggal Cetak</td>
                        <td class="px-2 py-1 w-1/4">{{ $tanggalCetak }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1 bg-surface-container-low font-bold w-1/4 border-r border-outline">Jenis Laporan</td>
                        <td class="px-2 py-1 w-1/4 border-r border-outline">Rekapitulasi Kelas</td>
                        <td class="px-2 py-1 bg-surface-container-low font-bold w-1/4 border-r border-outline">Dicetak Oleh</td>
                        <td class="px-2 py-1 w-1/4">{{ $cetakUser }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Class Sections -->
        @forelse ($dataPerKelas as $kelasData)
        <section class="mb-section-gap">
            <h5 class="font-table-header text-[10pt] font-bold text-primary mb-stack-tight border-l-4 border-primary pl-2">
                Kelas: {{ $kelasData['nama_kelas'] ?? '' }}
            </h5>

            @foreach ($kelasData['items'] ?? [] as $index =&gt; $row)

            @endforeach
            <table class="w-full border-collapse border border-primary mb-stack-tight">
                <thead>
                    <tr class="bg-surface-container-low font-table-header text-table-header text-primary">
                        <th class="border border-outline px-1 py-1 text-center w-[3%]">No</th>
                        <th class="border border-outline px-1 py-1 text-center w-[8%]">Tanggal</th>
                        <th class="border border-outline px-2 py-1 text-left w-[20%]">Nama Siswa</th>
                        <th class="border border-outline px-1 py-1 text-center w-[10%]">NIS</th>
                        <th class="border border-outline px-1 py-1 text-center w-[5%]">Jenis</th>
                        <th class="border border-outline px-2 py-1 text-left">Uraian / Deskripsi</th>
                        <th class="border border-outline px-1 py-1 text-center w-[6%]">Poin</th>
                        <th class="border border-outline px-1 py-1 text-center w-[8%]">Akm Poin</th>
                        <th class="border border-outline px-1 py-1 text-center w-[8%]">Sisa Poin</th>
                    </tr>
                </thead>
                <tbody class="font-table-data text-table-data">
                    <tr class="{{ $index % 2 == 0 ? 'bg-surface-container-lowest' : 'bg-surface-container-low' }}">
                        <td class="border border-outline-variant px-1 py-1 text-center">{{ $index + 1 }}</td>
                        <td class="border border-outline-variant px-1 py-1 text-center">{{ $row['tanggal'] ?? '' }}</td>
                        <td class="border border-outline-variant px-2 py-1 font-semibold">{{ $row['nama'] ?? '' }}</td>
                        <td class="border border-outline-variant px-1 py-1 text-center">{{ $row['nis'] ?? '' }}</td>
                        <td class="border border-outline-variant px-1 py-1 text-center font-bold">{{ $row['jenis'] ?? '' }}</td>
                        <td class="border border-outline-variant px-2 py-1">{{ $row['isi'] ?? '' }}</td>
                        <td class="border border-outline-variant px-1 py-1 text-center">{{ $row['poin'] ?? '' }}</td>
                        <td class="border border-outline-variant px-1 py-1 text-center">{{ $row['akm_poin'] ?? '' }}</td>
                        <td class="border border-outline-variant px-1 py-1 text-center font-semibold {{ strtolower($row['jenis'] ?? '') == 'pel' || strtolower($row['jenis'] ?? '') == 'pelanggaran' ? 'text-error' : 'text-primary' }}">{{ $row['sisa_poin'] ?? '' }}</td>
                    </tr>
                </tbody>
            </table>
            <!-- Mini Recap -->
            <div class="flex justify-end mt-2">
                <table class="border-collapse border border-outline w-1/3">
                    <tbody class="font-caption-fine text-caption-fine">
                        <tr>
                            <td class="border border-outline px-2 py-1 bg-surface-container-low font-bold">Total Pelanggaran</td>
                            <td class="border border-outline px-2 py-1 text-center">{{ $kelasData['total_pelanggaran'] ?? 0 }} Kasus</td>
                        </tr>
                        <tr>
                            <td class="border border-outline px-2 py-1 bg-surface-container-low font-bold">Total Penghargaan</td>
                            <td class="border border-outline px-2 py-1 text-center">{{ $kelasData['total_penghargaan'] ?? 0 }} Kasus</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        @empty
        <div class="text-center py-4 text-on-surface-variant font-body-main">Tidak ada data untuk periode ini.</div>
        @endforelse
        <!-- Footnote Legend -->
        <div class="mt-section-gap pt-4 border-t border-outline-variant font-caption-fine text-caption-fine text-on-surface-variant flex gap-8">
            <div>
                <strong>Keterangan:</strong><br />
                Pel = Pelanggaran Tata Tertib (Mengurangi Poin)<br />
                Prg = Penghargaan/Prestasi (Menambah Poin)<br />
            </div>
            <div>
                Poin Awal Siswa = 100<br />
                Sisa Poin = Poin Awal - Akm Pelanggaran + Akm Penghargaan
            </div>
        </div>
        <!-- Signatures -->
        <div class="mt-[40pt]">
            <div class="text-right font-body-main text-body-main mb-stack-standard">
                {{ $kab }}, {{ $tanggalCetak }}
            </div>
            <div class="flex justify-between items-end font-body-main text-body-main">
                <div class="text-center w-1/3">
                    <p class="mb-[60pt]">{{ $cetakUser }}</p>
                    <p class="font-bold underline uppercase">Tanda Tangan</p>
                    <p class="text-[9pt] opacity-0">NIP</p>
                </div>
                <div class="text-center w-1/3">
                    <p class="mb-[60pt]">Kepala Sekolah</p>
                    <p class="font-bold underline uppercase">{{ $namaKs }}</p>
                    <p class="text-[9pt]">NIP. {{ $nipKs }}</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>