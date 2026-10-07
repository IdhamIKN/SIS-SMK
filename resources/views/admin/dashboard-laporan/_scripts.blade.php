<script>
    /* ═══════════════════════════════════════════════════════════
   DASHBOARD LAPORAN — ApexCharts Initialization
   Semua chart diinisialisasi setelah DOM ready
═══════════════════════════════════════════════════════════ */

    /* ── Helpers ── */
    const DL = {

        /* Palet warna default */
        palette: ['#6366f1', '#22c55e', '#ef4444', '#f59e0b', '#3b82f6', '#8b5cf6', '#14b8a6', '#ec4899', '#f97316',
            '#64748b'
        ],

        /* Konfigurasi toolbar download standar */
        _toolbarCfg: {
            show: true,
            tools: {
                download: true,
                selection: false,
                zoom: false,
                zoomin: false,
                zoomout: false,
                pan: false,
                reset: false,
                customIcons: []
            },
            export: {
                csv: {
                    filename: 'dashboard-laporan'
                },
                svg: {
                    filename: 'dashboard-laporan'
                },
                png: {
                    filename: 'dashboard-laporan'
                }
            }
        },

        /* Deep merge dua object satu level (cukup untuk chart + toolbar) */
        _deepMerge(base, extra) {
            const result = Object.assign({}, base);
            for (const key in extra) {
                if (
                    extra[key] !== null &&
                    typeof extra[key] === 'object' &&
                    !Array.isArray(extra[key]) &&
                    base[key] !== null &&
                    typeof base[key] === 'object' &&
                    !Array.isArray(base[key])
                ) {
                    result[key] = Object.assign({}, base[key], extra[key]);
                } else {
                    result[key] = extra[key];
                }
            }
            return result;
        },

        /* Opsi dasar chart — toolbar selalu di-inject ke property chart */
        baseOpts(extra = {}) {
            const base = {
                chart: {
                    fontFamily: 'Poppins, sans-serif',
                    toolbar: this._toolbarCfg,
                },
                dataLabels: {
                    enabled: false
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4
                },
                tooltip: {
                    theme: 'light'
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
            };
            const result = this._deepMerge(base, extra);
            // Pastikan toolbar dari base selalu ada (tidak tertimpa extra.chart yang parsial)
            if (!result.chart.toolbar || typeof result.chart.toolbar !== 'object') {
                result.chart.toolbar = this._toolbarCfg;
            } else {
                // Merge toolbar agar tools.download selalu ada
                result.chart.toolbar = Object.assign({}, this._toolbarCfg, result.chart.toolbar);
                delete result.chart.toolbar.autoSelected;
            }
            return result;
        },

        /* Inject toolbar ke options apa pun (untuk chart tanpa baseOpts) */
        withToolbar(opts) {
            const merged = Object.assign({}, opts);
            const existingChart = opts.chart || {};
            merged.chart = Object.assign({}, existingChart, {
                toolbar: Object.assign({}, this._toolbarCfg, existingChart.toolbar || {}),
            });
            // Hapus autoSelected yang tidak valid untuk pie/donut
            if (merged.chart.toolbar) delete merged.chart.toolbar.autoSelected;
            return merged;
        },

        /* Format tanggal pendek */
        shortDate(str) {
            if (!str) return str;
            const d = new Date(str);
            return d.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short'
            });
        },

        /* Pastikan elemen ada di DOM */
        el(id) {
            return document.querySelector('#' + id);
        },

        /* Render chart — simpan instance ke window._DL_CHARTS untuk PDF print */
        render(id, opts) {
            const el = this.el(id);
            if (!el) return null;
            try {
                const chart = new ApexCharts(el, opts);
                chart.render();
                window._DL_CHARTS = window._DL_CHARTS || [];
                window._DL_CHARTS.push({
                    id,
                    chart,
                    section: el.closest('.dl-section')?.querySelector('h3')?.textContent?.trim() || '',
                    label: el.previousElementSibling?.textContent?.trim() || ''
                });
                return chart;
            } catch (e) {
                console.error('[DL.render] Error chart #' + id + ':', e);
                return null;
            }
        },

        /* Color untuk status kehadiran */
        statusColor: {
            hadir: '#22c55e',
            terlambat: '#f59e0b',
            alfa: '#ef4444',
            sakit: '#8b5cf6',
            izin: '#3b82f6'
        },
    };

    document.addEventListener('DOMContentLoaded', function() {

        /* ════════════════════════════════════════════════
           FILTER — Periode switcher & custom date
        ════════════════════════════════════════════════ */
        const periodeInput = document.getElementById('input-periode');
        const customRange = document.getElementById('custom-range');

        document.querySelectorAll('.dl-btn-periode').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.dl-btn-periode').forEach(b => b.classList.remove(
                    'active'));
                this.classList.add('active');
                periodeInput.value = this.dataset.periode;
                if (this.dataset.periode === 'custom') {
                    customRange.classList.add('show');
                } else {
                    customRange.classList.remove('show');
                    document.getElementById('dl-form').submit();
                }
            });
        });

        /* Flatpickr untuk date range custom */
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#date_from', {
                locale: 'id',
                dateFormat: 'Y-m-d',
                onChange: function(selected) {
                    pickerTo.set('minDate', selected[0]);
                }
            });
            const pickerTo = flatpickr('#date_to', {
                locale: 'id',
                dateFormat: 'Y-m-d',
            });
        }

        /* ════════════════════════════════════════════════
           SEKSI 2 — KEHADIRAN SISWA
        ════════════════════════════════════════════════ */
        const kh = window.__dl_kehadiran || {};

        /* 2a. Tren Kehadiran — Stacked Area */
        DL.render('chart-tren-kehadiran', Object.assign(DL.baseOpts(), {
            chart: {
                type: 'area',
                height: 240,
                stacked: false,
                fontFamily: 'Poppins, sans-serif',
                toolbar: {
                    show: true,
                    tools: {
                        download: true
                    }
                }
            },
            series: [{
                    name: 'Hadir',
                    data: kh.trenHadir || []
                },
                {
                    name: 'Terlambat',
                    data: kh.trenTerlambat || []
                },
                {
                    name: 'Alfa',
                    data: kh.trenAlfa || []
                },
                {
                    name: 'Sakit',
                    data: kh.trenSakit || []
                },
                {
                    name: 'Izin',
                    data: kh.trenIzin || []
                },
            ],
            colors: ['#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#3b82f6'],
            xaxis: {
                categories: (kh.trenLabels || []).map(DL.shortDate),
                labels: {
                    rotate: -30,
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '11px'
                    }
                }
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    opacityFrom: .35,
                    opacityTo: .05
                }
            },
            legend: {
                position: 'top',
                fontSize: '11px'
            },
            dataLabels: {
                enabled: false
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4
            },
            tooltip: {
                theme: 'light',
                shared: true
            },
            noData: {
                text: 'Belum ada data kehadiran',
                style: {
                    color: '#94a3b8'
                }
            },
        }));

        /* 2b. Donut Distribusi Status */
        (function() {
            const labels = (kh.statusLabels || []).map(l => {
                const map = {
                    hadir: 'Hadir',
                    terlambat: 'Terlambat',
                    alfa: 'Alfa',
                    sakit: 'Sakit',
                    izin: 'Izin'
                };
                return map[l] || l;
            });
            const colors = (kh.statusLabels || []).map(l => DL.statusColor[l] || '#94a3b8');
            DL.render('chart-donut-status', DL.withToolbar({
                chart: {
                    type: 'donut',
                    height: 240,
                    fontFamily: 'Poppins, sans-serif'
                },
                series: kh.statusVals || [],
                labels: labels,
                colors: colors,
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '11px',
                                    fontWeight: 700
                                }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '11px'
                },
                dataLabels: {
                    enabled: true,
                    formatter: (val) => val.toFixed(1) + '%',
                    style: {
                        fontSize: '10px'
                    }
                },
                tooltip: {
                    theme: 'light'
                },
                noData: {
                    text: 'Belum ada data',
                    style: {
                        color: '#94a3b8'
                    }
                },
            }));
        })();

        /* 2c. Heatmap Kelas */
        if ((kh.heatmapSeries || []).length > 0) {
            DL.render('chart-heatmap', {
                chart: {
                    type: 'heatmap',
                    height: 220,
                    fontFamily: 'Poppins, sans-serif',
                    toolbar: {
                        show: true,
                        tools: {
                            download: true
                        }
                    }
                },
                series: kh.heatmapSeries,
                xaxis: {
                    type: 'category',
                    labels: {
                        rotate: -45,
                        style: {
                            fontSize: '9px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                plotOptions: {
                    heatmap: {
                        shadeIntensity: 0.5,
                        radius: 2,
                        colorScale: {
                            ranges: [{
                                    from: 0,
                                    to: 40,
                                    color: '#fca5a5',
                                    name: '0–40%'
                                },
                                {
                                    from: 41,
                                    to: 70,
                                    color: '#fde68a',
                                    name: '41–70%'
                                },
                                {
                                    from: 71,
                                    to: 90,
                                    color: '#86efac',
                                    name: '71–90%'
                                },
                                {
                                    from: 91,
                                    to: 100,
                                    color: '#16a34a',
                                    name: '91–100%'
                                },
                            ]
                        },
                    }
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: val => val + '%'
                    }
                },
                noData: {
                    text: 'Belum ada data heatmap',
                    style: {
                        color: '#94a3b8'
                    }
                },
            });
        }

        /* 2d. Distribusi Jam Masuk — Bar */
        DL.render('chart-jam-masuk', Object.assign(DL.baseOpts(), {
            chart: {
                type: 'bar',
                height: 220,
                fontFamily: 'Poppins, sans-serif'
            },
            series: [{
                name: 'Siswa',
                data: kh.jamVals || []
            }],
            colors: ['#6366f1'],
            xaxis: {
                categories: kh.jamLabels || [],
                labels: {
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '11px'
                    }
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '55%'
                }
            },
            dataLabels: {
                enabled: false
            },
            noData: {
                text: 'Belum ada data',
                style: {
                    color: '#94a3b8'
                }
            },
        }));

        /* 2e. Ranking Kelas — Horizontal Bar */
        DL.render('chart-ranking-kelas', Object.assign(DL.baseOpts(), {
            chart: {
                type: 'bar',
                height: Math.max(220, (kh.rankLabels || []).length * 30 + 60),
                fontFamily: 'Poppins, sans-serif'
            },
            series: [{
                name: '% Kehadiran',
                data: kh.rankVals || []
            }],
            colors: ['#22c55e'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '60%',
                    distributed: true,
                    dataLabels: {
                        position: 'right'
                    },
                }
            },
            dataLabels: {
                enabled: true,
                formatter: val => val + '%',
                style: {
                    fontSize: '11px',
                    fontWeight: 700
                }
            },
            xaxis: {
                categories: kh.rankLabels || [],
                min: 0,
                max: 100,
                labels: {
                    formatter: v => v + '%',
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            legend: {
                show: false
            },
            colors: (kh.rankVals || []).map(v => v >= 80 ? '#22c55e' : v >= 60 ? '#f59e0b' :
                '#ef4444'),
            noData: {
                text: 'Belum ada data ranking',
                style: {
                    color: '#94a3b8'
                }
            },
        }));

        /* ════════════════════════════════════════════════
           SEKSI 3 — IZIN
        ════════════════════════════════════════════════ */
        const iz = window.__dl_izin || {};

        /* 3a. Tren Izin — Grouped Bar */
        DL.render('chart-tren-izin', Object.assign(DL.baseOpts(), {
            chart: {
                type: 'bar',
                height: 220,
                stacked: false,
                fontFamily: 'Poppins, sans-serif'
            },
            series: [{
                    name: 'Izin Sakit',
                    data: iz.trenSakit || []
                },
                {
                    name: 'Pulang Cepat',
                    data: iz.trenPulang || []
                },
                {
                    name: 'Terlambat',
                    data: iz.trenTerlambat || []
                },
                {
                    name: 'Lainnya',
                    data: iz.trenLainnya || []
                },
            ],
            colors: ['#8b5cf6', '#3b82f6', '#f59e0b', '#14b8a6'],
            xaxis: {
                categories: (iz.trenLabels || []).map(DL.shortDate),
                labels: {
                    rotate: -30,
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 3,
                    columnWidth: '65%'
                }
            },
            legend: {
                position: 'top',
                fontSize: '11px'
            },
            noData: {
                text: 'Belum ada data izin',
                style: {
                    color: '#94a3b8'
                }
            },
        }));

        /* 3b. Status Izin — Donut */
        DL.render('chart-status-izin', DL.withToolbar({
            chart: {
                type: 'donut',
                height: 220,
                fontFamily: 'Poppins, sans-serif'
            },
            series: iz.statusVals || [],
            labels: (iz.statusLabels || []).map(l => ({
                diajukan: 'Diajukan',
                disetujui: 'Disetujui',
                ditolak: 'Ditolak'
            })[l] || l),
            colors: ['#f59e0b', '#22c55e', '#ef4444'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '11px'
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom',
                fontSize: '11px'
            },
            dataLabels: {
                enabled: true,
                formatter: v => v.toFixed(1) + '%',
                style: {
                    fontSize: '10px'
                }
            },
            tooltip: {
                theme: 'light'
            },
            noData: {
                text: 'Belum ada data',
                style: {
                    color: '#94a3b8'
                }
            },
        }));

        /* 3c. Jenis Izin — Horizontal Bar */
        DL.render('chart-jenis-izin', Object.assign(DL.baseOpts(), {
            chart: {
                type: 'bar',
                height: 180,
                fontFamily: 'Poppins, sans-serif'
            },
            series: [{
                name: 'Jumlah',
                data: iz.jenisVals || []
            }],
            colors: ['#8b5cf6'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '55%'
                }
            },
            xaxis: {
                categories: iz.jenisLabels || [],
                labels: {
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '10px',
                    fontWeight: 700
                }
            },
            legend: {
                show: false
            },
            noData: {
                text: 'Belum ada data',
                style: {
                    color: '#94a3b8'
                }
            },
        }));

        /* ════════════════════════════════════════════════
           SEKSI 4 — TATIB
        ════════════════════════════════════════════════ */
        const tt = window.__dl_tatib || {};

        /* 4a. Tren Pelanggaran — Area */
        DL.render('chart-tren-pelanggaran', Object.assign(DL.baseOpts(), {
            chart: {
                type: 'area',
                height: 200,
                fontFamily: 'Poppins, sans-serif'
            },
            series: [{
                name: 'Pelanggaran',
                data: tt.pelanggaranVals || []
            }],
            colors: ['#ef4444'],
            xaxis: {
                categories: (tt.pelanggaranLabels || []).map(DL.shortDate),
                labels: {
                    rotate: -30,
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    opacityFrom: .4,
                    opacityTo: .05
                }
            },
            dataLabels: {
                enabled: false
            },
            noData: {
                text: 'Tidak ada pelanggaran',
                style: {
                    color: '#94a3b8'
                }
            },
        }));

        /* 4b. Tren Penghargaan — Area */
        DL.render('chart-tren-penghargaan', Object.assign(DL.baseOpts(), {
            chart: {
                type: 'area',
                height: 200,
                fontFamily: 'Poppins, sans-serif'
            },
            series: [{
                name: 'Penghargaan',
                data: tt.penghargaanVals || []
            }],
            colors: ['#22c55e'],
            xaxis: {
                categories: (tt.penghargaanLabels || []).map(DL.shortDate),
                labels: {
                    rotate: -30,
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    opacityFrom: .4,
                    opacityTo: .05
                }
            },
            dataLabels: {
                enabled: false
            },
            noData: {
                text: 'Tidak ada penghargaan',
                style: {
                    color: '#94a3b8'
                }
            },
        }));

        /* 4c. Top Pasal — Horizontal Bar */
        DL.render('chart-top-pasal', Object.assign(DL.baseOpts(), {
            chart: {
                type: 'bar',
                height: Math.max(200, (tt.topPasalNames || []).length * 28 + 60),
                fontFamily: 'Poppins, sans-serif'
            },
            series: [{
                name: 'Kasus',
                data: tt.topPasalVals || []
            }],
            colors: ['#ef4444'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '60%',
                    dataLabels: {
                        position: 'right'
                    }
                }
            },
            xaxis: {
                categories: tt.topPasalNames || [],
                labels: {
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '9px'
                    },
                    maxWidth: 160
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '10px',
                    fontWeight: 700
                }
            },
            legend: {
                show: false
            },
            noData: {
                text: 'Tidak ada data pelanggaran',
                style: {
                    color: '#94a3b8'
                }
            },
        }));

        /* 4c-2. Top Pasal Penghargaan — Horizontal Bar */
        if ((tt.topPasalPenghargaanNames || []).length > 0) {
            DL.render('chart-top-pasal-penghargaan', Object.assign(DL.baseOpts(), {
                chart: {
                    type: 'bar',
                    height: Math.max(200, (tt.topPasalPenghargaanNames || []).length * 28 + 60),
                    fontFamily: 'Poppins, sans-serif'
                },
                series: [{
                    name: 'Kasus',
                    data: tt.topPasalPenghargaanVals || []
                }],
                colors: ['#22c55e'],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                        barHeight: '60%',
                        dataLabels: {
                            position: 'right'
                        }
                    }
                },
                xaxis: {
                    categories: tt.topPasalPenghargaanNames || [],
                    labels: {
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '9px'
                        },
                        maxWidth: 160
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '10px',
                        fontWeight: 700
                    }
                },
                legend: {
                    show: false
                },
                noData: {
                    text: 'Tidak ada data penghargaan',
                    style: {
                        color: '#94a3b8'
                    }
                },
            }));
        }

        /* 4d. Surat Panggilan — Grouped Bar */
        if ((tt.spLabels || []).length > 0) {
            DL.render('chart-surat-panggilan', Object.assign(DL.baseOpts(), {
                chart: {
                    type: 'bar',
                    height: 160,
                    fontFamily: 'Poppins, sans-serif'
                },
                series: [{
                        name: 'Total Surat',
                        data: tt.spTotal || []
                    },
                    {
                        name: 'WA Terkirim',
                        data: tt.spWa || []
                    },
                ],
                colors: ['#6366f1', '#22c55e'],
                xaxis: {
                    categories: tt.spLabels || [],
                    labels: {
                        style: {
                            fontSize: '11px'
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '55%'
                    }
                },
                legend: {
                    position: 'top',
                    fontSize: '11px'
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '10px',
                        fontWeight: 700
                    }
                },
            }));
        }

        /* ════════════════════════════════════════════════
           SEKSI 5 — EVENT
        ════════════════════════════════════════════════ */
        const ev = window.__dl_event || {};

        /* 5a. Partisipasi Event Siswa — Grouped Bar */
        if ((ev.siswaNames || []).length > 0) {
            DL.render('chart-event-siswa', Object.assign(DL.baseOpts(), {
                chart: {
                    type: 'bar',
                    height: 220,
                    fontFamily: 'Poppins, sans-serif'
                },
                series: [{
                        name: 'Scan Masuk',
                        data: ev.siswaMasuk || []
                    },
                    {
                        name: 'Scan Pulang',
                        data: ev.siswaPulang || []
                    },
                ],
                colors: ['#22c55e', '#3b82f6'],
                xaxis: {
                    categories: ev.siswaNames || [],
                    labels: {
                        rotate: -30,
                        style: {
                            fontSize: '9px'
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '65%'
                    }
                },
                legend: {
                    position: 'top',
                    fontSize: '11px'
                },
                dataLabels: {
                    enabled: false
                },
            }));
        }

        /* 5b. Kategori Event — Donut */
        if ((ev.kategoriLabels || []).length > 0) {
            DL.render('chart-kategori-event', DL.withToolbar({
                chart: {
                    type: 'donut',
                    height: 220,
                    fontFamily: 'Poppins, sans-serif'
                },
                series: ev.kategoriVals || [],
                labels: ev.kategoriLabels || [],
                colors: DL.palette,
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '11px'
                                }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '10px'
                },
                dataLabels: {
                    enabled: true,
                    formatter: v => v.toFixed(1) + '%',
                    style: {
                        fontSize: '10px'
                    }
                },
                tooltip: {
                    theme: 'light'
                },
            }));
        }

        /* 5c. Partisipasi Event Guru */
        if ((ev.guruNames || []).length > 0) {
            DL.render('chart-event-guru', Object.assign(DL.baseOpts(), {
                chart: {
                    type: 'bar',
                    height: 200,
                    fontFamily: 'Poppins, sans-serif'
                },
                series: [{
                        name: 'Scan Masuk',
                        data: ev.guruMasuk || []
                    },
                    {
                        name: 'Scan Pulang',
                        data: ev.guruPulang || []
                    },
                ],
                colors: ['#f97316', '#6366f1'],
                xaxis: {
                    categories: ev.guruNames || [],
                    labels: {
                        rotate: -30,
                        style: {
                            fontSize: '9px'
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '65%'
                    }
                },
                legend: {
                    position: 'top',
                    fontSize: '11px'
                },
                dataLabels: {
                    enabled: false
                },
            }));
        }

        /* ════════════════════════════════════════════════
           SEKSI 6 — GURU
        ════════════════════════════════════════════════ */
        const gr = window.__dl_guru || {};

        /* 6a. Tren Laporan Guru — Stacked Bar */
        if ((gr.trenDates || []).length > 0) {
            const guruColors = Object.values(gr.statusColorMap || {});
            DL.render('chart-tren-guru', Object.assign(DL.baseOpts(), {
                chart: {
                    type: 'bar',
                    height: 230,
                    stacked: true,
                    fontFamily: 'Poppins, sans-serif'
                },
                series: gr.trenSeries || [],
                colors: guruColors,
                xaxis: {
                    categories: (gr.trenDates || []).map(DL.shortDate),
                    labels: {
                        rotate: -30,
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        columnWidth: '75%',
                        borderRadius: 2
                    }
                },
                legend: {
                    position: 'top',
                    fontSize: '10px'
                },
                dataLabels: {
                    enabled: false
                },
                noData: {
                    text: 'Belum ada laporan KBM',
                    style: {
                        color: '#94a3b8'
                    }
                },
            }));
        }

        /* 6b. Donut Status Guru */
        (function() {
            const colorMap = gr.statusColorMap || {};
            const colors = (gr.donutLabels || []).map(l => colorMap[l] || '#94a3b8');
            const labelMap = {
                hijau: 'Hadir Tepat Waktu',
                kuning: 'Hadir Terlambat',
                merah: 'Tidak Hadir',
                abu: 'Tidak Hadir + Ada Tugas',
                biru: 'Pergi + Ada Tugas',
                pink: 'Pergi + No Tugas',
                orange: 'Tanpa Laporan',
                putih: 'Belum Lapor',
            };
            DL.render('chart-donut-guru', DL.withToolbar({
                chart: {
                    type: 'donut',
                    height: 230,
                    fontFamily: 'Poppins, sans-serif'
                },
                series: gr.donutVals || [],
                labels: (gr.donutLabels || []).map(l => labelMap[l] || l),
                colors: colors,
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '11px'
                                }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '10px'
                },
                dataLabels: {
                    enabled: true,
                    formatter: v => v.toFixed(1) + '%',
                    style: {
                        fontSize: '10px'
                    }
                },
                tooltip: {
                    theme: 'light'
                },
                noData: {
                    text: 'Belum ada data',
                    style: {
                        color: '#94a3b8'
                    }
                },
            }));
        })();

        /* 6c. Top Guru Bermasalah */
        if ((gr.topGuruNames || []).length > 0) {
            DL.render('chart-top-guru', Object.assign(DL.baseOpts(), {
                chart: {
                    type: 'bar',
                    height: 220,
                    fontFamily: 'Poppins, sans-serif'
                },
                series: [{
                    name: 'Jumlah Sesi',
                    data: gr.topGuruVals || []
                }],
                colors: ['#ef4444'],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                        barHeight: '60%',
                        dataLabels: {
                            position: 'right'
                        }
                    }
                },
                xaxis: {
                    categories: gr.topGuruNames || [],
                    labels: {
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '10px'
                        },
                        maxWidth: 140
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '10px',
                        fontWeight: 700
                    }
                },
                legend: {
                    show: false
                },
            }));
        }

        /* ════════════════════════════════════════════════
           SEKSI 7 — WHATSAPP
        ════════════════════════════════════════════════ */
        const wa = window.__dl_wa || {};

        /* 7a. Tren WA — Stacked Bar */
        if ((wa.trenDates || []).length > 0 && (wa.trenSeries || []).length > 0) {
            DL.render('chart-tren-wa', Object.assign(DL.baseOpts(), {
                chart: {
                    type: 'bar',
                    height: 220,
                    stacked: true,
                    fontFamily: 'Poppins, sans-serif'
                },
                series: wa.trenSeries,
                colors: DL.palette,
                xaxis: {
                    categories: (wa.trenDates || []).map(DL.shortDate),
                    labels: {
                        rotate: -30,
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        columnWidth: '75%',
                        borderRadius: 2
                    }
                },
                legend: {
                    position: 'top',
                    fontSize: '10px'
                },
                dataLabels: {
                    enabled: false
                },
                noData: {
                    text: 'Belum ada pesan WA',
                    style: {
                        color: '#94a3b8'
                    }
                },
            }));
        }

        /* 7b. Status WA — Donut */
        DL.render('chart-status-wa', DL.withToolbar({
            chart: {
                type: 'donut',
                height: 220,
                fontFamily: 'Poppins, sans-serif'
            },
            series: wa.statusVals || [],
            labels: (wa.statusLabels || []).map(l => ({
                terkirim: 'Terkirim',
                gagal: 'Gagal',
                pending: 'Pending'
            })[l] || l),
            colors: ['#22c55e', '#ef4444', '#f59e0b'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '11px'
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom',
                fontSize: '11px'
            },
            dataLabels: {
                enabled: true,
                formatter: v => v.toFixed(1) + '%',
                style: {
                    fontSize: '10px'
                }
            },
            tooltip: {
                theme: 'light'
            },
            noData: {
                text: 'Belum ada data WA',
                style: {
                    color: '#94a3b8'
                }
            },
        }));

        /* 7c. Jenis WA — Horizontal Bar */
        if ((wa.jenisLabels || []).length > 0) {
            DL.render('chart-jenis-wa', Object.assign(DL.baseOpts(), {
                chart: {
                    type: 'bar',
                    height: 180,
                    fontFamily: 'Poppins, sans-serif'
                },
                series: [{
                    name: 'Jumlah',
                    data: wa.jenisVals || []
                }],
                colors: ['#14b8a6'],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                        barHeight: '55%',
                        dataLabels: {
                            position: 'right'
                        }
                    }
                },
                xaxis: {
                    categories: wa.jenisLabels || [],
                    labels: {
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '10px',
                        fontWeight: 700
                    }
                },
                legend: {
                    show: false
                },
                noData: {
                    text: 'Belum ada data',
                    style: {
                        color: '#94a3b8'
                    }
                },
            }));
        }

    }); // end DOMContentLoaded

    /* ════════════════════════════════════════════════════════
       PRINT / CETAK SEMUA CHART KE PDF
       Menggunakan ApexCharts dataURI() → window.print()
    ════════════════════════════════════════════════════════ */
    window.dlPrintAllCharts = async function() {
        const btn = document.getElementById('dl-print-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyiapkan...';
        }

        try {
            // Gunakan registry global
            const entries = (window._DL_CHARTS || []).filter(e => e && e.chart);
            if (!entries.length) {
                alert(
                    'Belum ada chart yang tersedia. Tunggu hingga semua grafik selesai dimuat, lalu coba lagi.');
                return;
            }

            // Konversi semua chart ke PNG data URI
            const images = [];
            for (const entry of entries) {
                try {
                    const result = await entry.chart.dataURI({
                        scale: 2
                    });
                    if (result && result.imgURI) {
                        images.push({
                            src: result.imgURI,
                            section: entry.section || '',
                            label: entry.label || '',
                            id: entry.id,
                        });
                    }
                } catch (err) {
                    console.warn('[dlPrint] Skip chart:', entry.id, err);
                }
            }

            if (!images.length) {
                alert('Tidak ada chart yang berhasil diekspor. Pastikan halaman sudah selesai dimuat.');
                return;
            }

            // Buka popup print
            const printWin = window.open('', '_blank', 'width=1000,height=750');
            if (!printWin) {
                alert('Popup diblokir browser. Izinkan popup untuk halaman ini lalu coba lagi.');
                return;
            }

            // Info filter dari halaman
            const periodeEl = document.querySelector('.dl-btn-periode.active');
            const periode = periodeEl?.textContent?.trim() || '';
            const periodeBadge = document.querySelector(
                '.dl-filter .dl-periode-info, .dl-filter [style*="eef2ff"]');
            const periodeStr = periodeBadge?.textContent?.trim() ||
                document.getElementById('date_from')?.value + ' s.d. ' + document.getElementById('date_to')
                ?.value ||
                '';
            const kelasEl = document.querySelector('[name="kelas_id"] option:checked');
            const kelas = kelasEl?.textContent?.trim() || 'Semua Kelas';
            const printDate = new Date().toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });

            // Susun HTML: kelompokkan per section dalam grid 2 kolom
            let prevSection = '';
            let htmlBody = '';
            for (const img of images) {
                if (img.section !== prevSection) {
                    if (prevSection !== '') htmlBody += '</div>'; // tutup grid sebelumnya
                    if (img.section) {
                        htmlBody += `<h2 class="sec-title">${img.section}</h2>`;
                    }
                    htmlBody += '<div class="chart-grid">';
                    prevSection = img.section;
                }
                htmlBody += `
                    <div class="chart-item">
                        ${img.label ? `<p class="chart-label">${img.label}</p>` : ''}
                        <img src="${img.src}" alt="${img.label || img.id}">
                    </div>`;
            }
            htmlBody += '</div>'; // tutup grid terakhir

            printWin.document.write(`<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Laporan — Cetak Grafik</title>
<style>
@page { size: A4 portrait; margin: 12mm 10mm 14mm; }
*{ margin:0;padding:0;box-sizing:border-box; }
body{ font-family:Arial,sans-serif; font-size:9pt; color:#1e293b; background:#fff; }
.kop{ text-align:center; border-bottom:3px solid #1e293b; padding-bottom:8px; margin-bottom:14px; }
.kop h1{ font-size:13pt; font-weight:800; }
.kop p{ font-size:8pt; color:#64748b; margin-top:3px; }
.sec-title{
  font-size:10pt;font-weight:800;color:#1e40af;
  border-left:4px solid #1e40af;padding-left:8px;
  margin:14px 0 8px;
}
.chart-grid{ display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px; }
.chart-item{
  border:1px solid #e2e8f0; border-radius:6px; padding:8px;
  break-inside:avoid; page-break-inside:avoid;
}
.chart-label{
  font-size:7pt;font-weight:700;color:#64748b;
  text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;
}
.chart-item img{ width:100%; height:auto; display:block; }
.footer{
  margin-top:16px; border-top:1px solid #e2e8f0; padding-top:5px;
  font-size:7pt; color:#94a3b8; display:flex; justify-content:space-between;
}
.print-bar{
  position:fixed; bottom:0; left:0; right:0;
  background:#1e293b; color:#f1f5f9; padding:10px 20px;
  display:flex; align-items:center; justify-content:space-between;
  font-family:Arial,sans-serif; font-size:9pt;
}
.pb-btn{
  display:inline-flex; align-items:center; gap:6px;
  padding:8px 18px; border-radius:7px; font-size:9pt; font-weight:700;
  cursor:pointer; border:none; font-family:inherit;
}
.pb-print{ background:#6366f1; color:#fff; }
.pb-close{ background:#334155; color:#cbd5e1; }
@media print{
  .print-bar{ display:none!important; }
  body{ padding-bottom:0; }
}
@media screen{ body{ padding-bottom:56px; } }
</style>
</head>
<body>
<div class="kop">
  <h1>Dashboard Laporan Aktivitas</h1>
  <p>Periode: <strong>${periode || 'Custom'}</strong>${periodeStr ? ' &bull; ' + periodeStr : ''} &bull; Kelas: <strong>${kelas}</strong> &bull; Dicetak: ${printDate}</p>
</div>
${htmlBody}
<div class="footer">
  <span>Dashboard Laporan &mdash; ${printDate}</span>
  <span>Total grafik: ${images.length}</span>
</div>
<div class="print-bar">
  <span>Dashboard Laporan &mdash; ${images.length} grafik</span>
  <div style="display:flex;gap:8px;">
    <button class="pb-btn pb-print" onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
    <button class="pb-btn pb-close" onclick="window.close()">&#10005; Tutup</button>
  </div>
</div>
</body>
</html>`);
            printWin.document.close();

        } catch (err) {
            console.error('[dlPrint] Error:', err);
            alert('Terjadi kesalahan saat menyiapkan cetak. Lihat console untuk detail.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-print"></i> Cetak Semua Chart';
            }
        }
    };
</script>
