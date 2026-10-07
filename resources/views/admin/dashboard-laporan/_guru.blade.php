{{-- ═══════════════════════════════════════════
     SEKSI 6 — LAPORAN KEHADIRAN GURU (KBM)
═══════════════════════════════════════════ --}}
<div class="dl-section">
    <div class="dl-section-head">
        <div class="dl-section-icon" style="background:#fef3c7;color:#92400e;">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div>
            <h3>Kehadiran Guru (Laporan KBM)</h3>
            <p style="margin:0;font-size:.68rem;color:#94a3b8;">Status laporan harian dari siswa petugas</p>
        </div>
    </div>

    {{-- Row 1: Tren stacked + Distribusi donut --}}
    <div class="dl-chart-row cols-2-1" style="margin-bottom:14px;">
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-chart-bar"></i> Tren Laporan Status Guru per Hari
            </p>
            <div class="dl-chart-wrap" id="chart-tren-guru" style="min-height:230px;"></div>
        </div>
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-chart-pie"></i> Distribusi Status
            </p>
            <div class="dl-chart-wrap" id="chart-donut-guru" style="min-height:230px;"></div>

            {{-- Legend warna status — dari config/status_guru.php --}}
            <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px;">
                @foreach (config('status_guru.order') as $key)
                    @php $cfg = config("status_guru.statuses.$key"); @endphp
                    @if ($cfg)
                        <span
                            style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;background:{{ $cfg['bg'] }};color:{{ $cfg['text'] }};font-size:.6rem;font-weight:700;">
                            <span
                                style="width:6px;height:6px;border-radius:50%;background:{{ $cfg['color'] }};flex-shrink:0;"></span>
                            {{ $cfg['label'] }}
                        </span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Row 2: Top guru bermasalah --}}
    {{-- <div>
        <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
            <i class="fas fa-user-slash"></i> Top 10 Guru Paling Sering Tidak Hadir / Terlambat
        </p>
        @if (count($topGuruBermasalah) > 0)
            <div class="dl-chart-wrap" id="chart-top-guru" style="min-height:220px;"></div>
        @else
            <div style="padding:20px;text-align:center;color:#94a3b8;font-size:.78rem;">
                <i class="fas fa-check-circle"
                    style="display:block;font-size:1.4rem;margin-bottom:6px;color:#22c55e;opacity:.7;"></i>
                Tidak ada laporan bermasalah dalam periode ini
            </div>
        @endif
    </div> --}}

    {{-- Row 3: Rekap lengkap semua guru × semua status --}}
    @php
        $statusOrder = config('status_guru.order', ['hijau','kuning','merah','abu','biru','pink','orange','putih']);
        $statusCfg   = config('status_guru.statuses', []);
    @endphp
    <div style="margin-top:18px;">
        <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
            <i class="fas fa-table"></i> Rekap Kehadiran Semua Guru — Semua Status
        </p>

        @if (count($rekapStatusGuru) > 0)
            {{-- Toolbar: search + per-page --}}
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
                <input
                    type="text"
                    id="rekap-guru-search"
                    placeholder="Cari nama guru…"
                    oninput="rekapGuru.onSearch(this.value)"
                    style="flex:1;min-width:180px;max-width:280px;padding:5px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:.75rem;color:#374151;outline:none;">
                <div style="display:flex;align-items:center;gap:5px;margin-left:auto;">
                    <span style="font-size:.7rem;color:#94a3b8;">Tampilkan</span>
                    <select id="rekap-guru-perpage"
                        onchange="rekapGuru.onPerPage(this.value)"
                        style="padding:4px 6px;border:1px solid #e2e8f0;border-radius:6px;font-size:.72rem;color:#374151;background:#fff;cursor:pointer;outline:none;">
                        <option value="10">10</option>
                        <option value="15" selected>15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="0">Semua</option>
                    </select>
                    <span style="font-size:.7rem;color:#94a3b8;">baris</span>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table id="rekap-guru-table" style="width:100%;border-collapse:collapse;font-size:.72rem;">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                            <th style="padding:7px 10px;text-align:left;font-weight:700;color:#475569;white-space:nowrap;">#</th>
                            <th style="padding:7px 10px;text-align:left;font-weight:700;color:#475569;white-space:nowrap;">Nama Guru</th>
                            <th style="padding:7px 10px;text-align:center;font-weight:700;color:#475569;white-space:nowrap;">Total</th>
                            @foreach ($statusOrder as $stKey)
                                @if (isset($statusCfg[$stKey]))
                                    <th style="padding:7px 6px;text-align:center;font-weight:700;color:{{ $statusCfg[$stKey]['text'] }};background:{{ $statusCfg[$stKey]['bg'] }};white-space:nowrap;min-width:70px;">
                                        <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:{{ $statusCfg[$stKey]['color'] }};margin-right:3px;vertical-align:middle;"></span>
                                        {{ $statusCfg[$stKey]['label'] }}
                                    </th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="rekap-guru-tbody">
                        @foreach ($rekapStatusGuru as $i => $guru)
                            @php
                                $hijauCount  = $guru['statuses']['hijau'] ?? 0;
                                $pct         = $guru['total'] > 0 ? round($hijauCount / $guru['total'] * 100) : 0;
                                $problematic = ($guru['statuses']['merah']  ?? 0)
                                             + ($guru['statuses']['kuning'] ?? 0)
                                             + ($guru['statuses']['orange'] ?? 0);
                                $rowBg       = $problematic > 5 ? '#fff5f5' : ($pct >= 80 ? '#f0fdf4' : '#ffffff');
                            @endphp
                            <tr class="rekap-guru-row"
                                data-nama="{{ strtolower($guru['nama']) }}"
                                data-orig-bg="{{ $rowBg }}"
                                style="border-bottom:1px solid #f1f5f9;background:{{ $rowBg }};">
                                <td class="rg-no" style="padding:6px 10px;color:#94a3b8;font-size:.65rem;">{{ $i + 1 }}</td>
                                <td style="padding:6px 10px;font-weight:600;color:#1e293b;">
                                    {{ $guru['nama'] }}
                                    @if ($pct >= 80)
                                        <span style="margin-left:4px;font-size:.6rem;background:#dcfce7;color:#15803d;padding:1px 5px;border-radius:10px;font-weight:700;">Rajin</span>
                                    @elseif ($problematic > 5)
                                        <span style="margin-left:4px;font-size:.6rem;background:#fee2e2;color:#b91c1c;padding:1px 5px;border-radius:10px;font-weight:700;">Perhatian</span>
                                    @endif
                                </td>
                                <td style="padding:6px 10px;text-align:center;font-weight:700;color:#374151;">
                                    {{ $guru['total'] }}
                                </td>
                                @foreach ($statusOrder as $stKey)
                                    @if (isset($statusCfg[$stKey]))
                                        @php $val = $guru['statuses'][$stKey] ?? 0; @endphp
                                        <td style="padding:6px 6px;text-align:center;">
                                            @if ($val > 0)
                                                <span style="display:inline-block;min-width:26px;padding:2px 6px;border-radius:10px;background:{{ $statusCfg[$stKey]['bg'] }};color:{{ $statusCfg[$stKey]['text'] }};font-weight:700;">
                                                    {{ $val }}
                                                </span>
                                            @else
                                                <span style="color:#cbd5e1;">—</span>
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer: info + pagination --}}
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-top:10px;">
                <p id="rekap-guru-info" style="margin:0;font-size:.63rem;color:#94a3b8;"></p>
                <div id="rekap-guru-pages" style="display:flex;gap:4px;flex-wrap:wrap;"></div>
            </div>

            <p style="margin-top:6px;font-size:.63rem;color:#94a3b8;">
                Baris <span style="background:#f0fdf4;color:#15803d;padding:0 4px;border-radius:3px;font-weight:700;">hijau muda</span> = ≥80% hadir tepat waktu.
                Baris <span style="background:#fff5f5;color:#b91c1c;padding:0 4px;border-radius:3px;font-weight:700;">merah muda</span> = &gt;5 kali bermasalah.
            </p>
        @else
            <div style="padding:20px;text-align:center;color:#94a3b8;font-size:.78rem;">
                <i class="fas fa-inbox" style="display:block;font-size:1.4rem;margin-bottom:6px;opacity:.4;"></i>
                Belum ada data laporan guru dalam periode ini
            </div>
        @endif
    </div>
</div>

<script>
(function () {
    'use strict';

    var rekapGuru = (function () {
        var allRows    = [];   // semua <tr> asli
        var filtered   = [];   // subset setelah search
        var perPage    = 15;
        var currentPage = 1;
        var searchVal  = '';

        // btn style helpers
        function btnBase() {
            return 'display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;' +
                   'padding:0 6px;border-radius:6px;font-size:.7rem;font-weight:700;cursor:pointer;' +
                   'border:1px solid #e2e8f0;background:#fff;color:#475569;transition:background .15s;';
        }
        function btnActive() {
            return 'display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;' +
                   'padding:0 6px;border-radius:6px;font-size:.7rem;font-weight:700;cursor:default;' +
                   'border:1px solid #6366f1;background:#6366f1;color:#fff;';
        }
        function btnDisabled() {
            return 'display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;' +
                   'padding:0 6px;border-radius:6px;font-size:.7rem;font-weight:700;cursor:not-allowed;' +
                   'border:1px solid #e2e8f0;background:#f8fafc;color:#cbd5e1;';
        }

        function init() {
            allRows = Array.from(document.querySelectorAll('#rekap-guru-tbody .rekap-guru-row'));
            filtered = allRows.slice();
            render();
        }

        function applyFilter() {
            filtered = allRows.filter(function (r) {
                return r.dataset.nama.includes(searchVal);
            });
            currentPage = 1;
            render();
        }

        function totalPages() {
            if (perPage === 0) return 1;
            return Math.max(1, Math.ceil(filtered.length / perPage));
        }

        function render() {
            var tp = totalPages();
            if (currentPage > tp) currentPage = tp;

            // hide / show rows
            var start = perPage === 0 ? 0 : (currentPage - 1) * perPage;
            var end   = perPage === 0 ? filtered.length : start + perPage;

            // first hide all
            allRows.forEach(function (r) { r.style.display = 'none'; });

            // show filtered slice & renumber
            var visibleNum = 1;
            filtered.forEach(function (r, idx) {
                if (idx >= start && idx < end) {
                    r.style.display = '';
                    var noCell = r.querySelector('.rg-no');
                    if (noCell) noCell.textContent = start + visibleNum;
                    visibleNum++;
                }
            });

            renderInfo(start, Math.min(end, filtered.length));
            renderPages(tp);
        }

        function renderInfo(start, end) {
            var el = document.getElementById('rekap-guru-info');
            if (!el) return;
            var total = allRows.length;
            if (filtered.length === total) {
                el.textContent = 'Menampilkan ' + (start + 1) + '–' + end + ' dari ' + total + ' guru.';
            } else {
                el.textContent = 'Menampilkan ' + (start + 1) + '–' + end +
                    ' dari ' + filtered.length + ' hasil (total ' + total + ' guru).';
            }
        }

        function renderPages(tp) {
            var el = document.getElementById('rekap-guru-pages');
            if (!el) return;
            if (tp <= 1 && perPage !== 0) { el.innerHTML = ''; return; }
            if (perPage === 0) { el.innerHTML = ''; return; }

            var html = '';

            // Prev
            if (currentPage > 1) {
                html += '<button style="' + btnBase() + '" onclick="rekapGuru.goTo(' + (currentPage - 1) + ')">' +
                        '<i class="fas fa-chevron-left" style="font-size:.6rem;"></i></button>';
            } else {
                html += '<span style="' + btnDisabled() + '"><i class="fas fa-chevron-left" style="font-size:.6rem;"></i></span>';
            }

            // page numbers — show up to 5 around current
            var pages = pageRange(currentPage, tp);
            var prev = null;
            pages.forEach(function (p) {
                if (prev !== null && p - prev > 1) {
                    html += '<span style="' + btnDisabled() + '">…</span>';
                }
                if (p === currentPage) {
                    html += '<span style="' + btnActive() + '">' + p + '</span>';
                } else {
                    html += '<button style="' + btnBase() + '" onclick="rekapGuru.goTo(' + p + ')">' + p + '</button>';
                }
                prev = p;
            });

            // Next
            if (currentPage < tp) {
                html += '<button style="' + btnBase() + '" onclick="rekapGuru.goTo(' + (currentPage + 1) + ')">' +
                        '<i class="fas fa-chevron-right" style="font-size:.6rem;"></i></button>';
            } else {
                html += '<span style="' + btnDisabled() + '"><i class="fas fa-chevron-right" style="font-size:.6rem;"></i></span>';
            }

            el.innerHTML = html;
        }

        function pageRange(cur, total) {
            var delta = 2, range = [], l;
            var left  = Math.max(1, cur - delta);
            var right = Math.min(total, cur + delta);
            if (cur - delta > 2)  { range.push(1); range.push('gap'); }
            else                  { left = 1; }
            if (cur + delta < total - 1) { /* right stays, add last after */ }
            else                         { right = total; }
            for (var i = left; i <= right; i++) range.push(i);
            if (right < total - 1) { range.push('gap'); range.push(total); }
            else if (right < total) range.push(total);
            // resolve gap → filtered by int
            return range.filter(function (x) { return x !== 'gap'; });
        }

        return {
            init: init,
            onSearch: function (val) {
                searchVal = val.toLowerCase().trim();
                applyFilter();
            },
            onPerPage: function (val) {
                perPage = parseInt(val, 10);
                currentPage = 1;
                render();
            },
            goTo: function (page) {
                currentPage = page;
                render();
                // scroll tabel ke atas
                var tbl = document.getElementById('rekap-guru-table');
                if (tbl) tbl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            },
        };
    })();

    // expose globally so inline onclick works
    window.rekapGuru = rekapGuru;

    // init after DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', rekapGuru.init);
    } else {
        rekapGuru.init();
    }
})();
</script>

@php
    // Flatten tren laporan guru menjadi series per status
    $guruTrenDates = array_keys($trenLaporanGuru);
    $guruStatuses = ['hijau', 'kuning', 'merah', 'orange', 'biru', 'abu', 'pink', 'putih'];
    $guruTrenSeries = [];
    foreach ($guruStatuses as $st) {
        $vals = [];
        foreach ($guruTrenDates as $d) {
            $vals[] = $trenLaporanGuru[$d][$st] ?? 0;
        }
        $guruTrenSeries[] = ['name' => ucfirst($st), 'data' => $vals];
    }

    $guruDonutLabels = array_keys($distribusiStatusGuru);
    $guruDonutVals = array_values($distribusiStatusGuru);

    $topGuruNames = collect($topGuruBermasalah)->pluck('nama')->toArray();
    $topGuruVals = collect($topGuruBermasalah)->pluck('total')->toArray();
@endphp

<script>
    window.__dl_guru = {
        trenDates: @json($guruTrenDates),
        trenSeries: @json($guruTrenSeries),
        // Warna seragam — bersumber dari config/status_guru.php
        statusColorMap: @json(collect(config('status_guru.statuses'))->mapWithKeys(fn($v, $k) => [$k => $v['color']])),
        statusLabelMap: @json(collect(config('status_guru.statuses'))->mapWithKeys(fn($v, $k) => [$k => $v['label']])),
        donutLabels: @json($guruDonutLabels),
        donutVals: @json($guruDonutVals),
        topGuruNames: @json($topGuruNames),
        topGuruVals: @json($topGuruVals),
    };
</script>
