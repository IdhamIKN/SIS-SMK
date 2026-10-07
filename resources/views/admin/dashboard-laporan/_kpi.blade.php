<style>
    .dl-kpi-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 20px;
    }

    @media(min-width:500px) {
        .dl-kpi-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media(min-width:768px) {
        .dl-kpi-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media(min-width:1024px) {
        .dl-kpi-grid {
            grid-template-columns: repeat(5, 1fr);
        }
    }

    @media(min-width:1280px) {
        .dl-kpi-grid {
            grid-template-columns: repeat(6, 1fr);
        }
    }

    .dl-kpi {
        background: var(--dl-card);
        border: 1px solid var(--dl-border);
        border-radius: var(--dl-radius);
        padding: 14px 12px;
        box-shadow: var(--dl-shadow);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 6px;
        transition: transform .18s, box-shadow .18s;
    }

    .dl-kpi:hover {
        transform: translateY(-2px);
        box-shadow: var(--dl-shadow-lg);
    }

    .dl-kpi-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        flex-shrink: 0;
    }

    .dl-kpi-val {
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1;
    }

    .dl-kpi-lbl {
        font-size: .6rem;
        font-weight: 700;
        color: var(--dl-light);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .dl-section-head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--dl-border);
    }

    .dl-section-head h3 {
        margin: 0;
        font-size: .85rem;
        font-weight: 800;
        color: var(--dl-text);
    }

    .dl-section-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        flex-shrink: 0;
    }

    .dl-section {
        background: var(--dl-card);
        border: 1px solid var(--dl-border);
        border-radius: var(--dl-radius);
        padding: 16px;
        margin-bottom: 16px;
        box-shadow: var(--dl-shadow);
    }

    .dl-chart-wrap {
        position: relative;
        width: 100%;
    }

    .dl-chart-wrap .apexcharts-canvas {
        width: 100% !important;
    }

    /* Responsive chart grid */
    .dl-chart-row {
        display: grid;
        gap: 14px;
        grid-template-columns: 1fr;
    }

    /* min-width:0 on grid children — tanpa ini, konten lebar (mis. canvas
   ApexCharts atau tabel) bisa memaksa track grid lebih lebar dari
   viewport dan memicu horizontal scroll pada halaman di mobile. */
    .dl-chart-row>div {
        min-width: 0;
    }

    @media(min-width:768px) {
        .dl-chart-row.cols-2 {
            grid-template-columns: 1fr 1fr;
        }

        .dl-chart-row.cols-3 {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .dl-chart-row.cols-2-1 {
            grid-template-columns: 2fr 1fr;
        }

        .dl-chart-row.cols-1-2 {
            grid-template-columns: 1fr 2fr;
        }
    }

    /* Tabel yang lebar (Top Siswa, dll) — scroll horizontal di layar sempit
   alih-alih kolomnya menyempit sampai teksnya bertabrakan. */
    .dl-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .dl-table-wrap table {
        min-width: 480px;
    }
</style>

{{-- ── KPI Section Label ── --}}
<p style="font-size:.68rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin:0 0 8px;">
    <i class="fas fa-tachometer-alt" style="margin-right:4px;"></i> KPI Ringkasan Periode
</p>

<div class="dl-kpi-grid">
    @php
        $kpis = [
            [
                'icon' => 'fa-user-check',
                'bg' => '#dcfce7',
                'color' => '#15803d',
                'val' => $kpi['hadir'],
                'lbl' => 'Hadir Masuk',
            ],
            [
                'icon' => 'fa-clock',
                'bg' => '#fef3c7',
                'color' => '#92400e',
                'val' => $kpi['terlambat'],
                'lbl' => 'Terlambat',
            ],
            [
                'icon' => 'fa-user-times',
                'bg' => '#fee2e2',
                'color' => '#b91c1c',
                'val' => $kpi['alfa'],
                'lbl' => 'Alfa',
            ],
            [
                'icon' => 'fa-file-medical',
                'bg' => '#ede9fe',
                'color' => '#6d28d9',
                'val' => $kpi['izin'],
                'lbl' => 'Sakit/Izin',
            ],
            [
                'icon' => 'fa-users',
                'bg' => '#dbeafe',
                'color' => '#1d4ed8',
                'val' => $kpi['totalSiswa'],
                'lbl' => 'Total Siswa',
            ],
            [
                'icon' => 'fa-exclamation-circle',
                'bg' => '#fee2e2',
                'color' => '#b91c1c',
                'val' => $kpi['totalPelanggaran'],
                'lbl' => 'Pelanggaran',
            ],
            [
                'icon' => 'fa-award',
                'bg' => '#dcfce7',
                'color' => '#15803d',
                'val' => $kpi['totalPenghargaan'],
                'lbl' => 'Penghargaan',
            ],
            [
                'icon' => 'fa-comments',
                'bg' => '#ccfbf1',
                'color' => '#0f766e',
                'val' => $kpi['waTerkirim'],
                'lbl' => 'WA Terkirim',
            ],
            [
                'icon' => 'fa-exclamation-triangle',
                'bg' => '#fef3c7',
                'color' => '#92400e',
                'val' => $kpi['waGagal'],
                'lbl' => 'WA Gagal',
            ],
            [
                'icon' => 'fa-envelope-open',
                'bg' => '#ede9fe',
                'color' => '#6d28d9',
                'val' => $kpi['suratPanggilan'],
                'lbl' => 'Surat Panggilan',
            ],
            [
                'icon' => 'fa-hourglass-half',
                'bg' => '#fef9c3',
                'color' => '#a16207',
                'val' => $kpi['izinPending'],
                'lbl' => 'Izin Pending',
            ],
        ];
    @endphp
    @foreach ($kpis as $k)
        <div class="dl-kpi">
            <div class="dl-kpi-icon" style="background:{{ $k['bg'] }};color:{{ $k['color'] }};">
                <i class="fas {{ $k['icon'] }}"></i>
            </div>
            <div class="dl-kpi-val" style="color:{{ $k['color'] }};">{{ number_format($k['val']) }}</div>
            <div class="dl-kpi-lbl">{{ $k['lbl'] }}</div>
        </div>
    @endforeach
</div>
