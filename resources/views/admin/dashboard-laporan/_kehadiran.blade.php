{{-- ═══════════════════════════════════════════
     SEKSI 2 — KEHADIRAN SISWA
═══════════════════════════════════════════ --}}
<div class="dl-section">
    <div class="dl-section-head">
        <div class="dl-section-icon" style="background:#dbeafe;color:#1d4ed8;">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div>
            <h3>Kehadiran Siswa</h3>
            <p style="margin:0;font-size:.68rem;color:#94a3b8;">Absensi masuk & status harian</p>
        </div>
    </div>

    {{-- Row 1: Tren Area + Donut Status --}}
    <div class="dl-chart-row cols-2-1" style="margin-bottom:14px;">
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-chart-area"></i> Tren Kehadiran
            </p>
            <div class="dl-chart-wrap" id="chart-tren-kehadiran" style="min-height:240px;"></div>
        </div>
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-chart-pie"></i> Distribusi Status
            </p>
            <div class="dl-chart-wrap" id="chart-donut-status" style="min-height:240px;"></div>
        </div>
    </div>

    {{-- Row 2: Heatmap + Jam Masuk --}}
    <div class="dl-chart-row cols-2-1" style="margin-bottom:14px;">
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-th"></i> Heatmap Kehadiran per Kelas
            </p>
            <div class="dl-chart-wrap" id="chart-heatmap" style="min-height:220px;overflow-x:auto;"></div>
        </div>
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-clock"></i> Distribusi Jam Masuk
            </p>
            <div class="dl-chart-wrap" id="chart-jam-masuk" style="min-height:220px;"></div>
        </div>
    </div>

    {{-- Row 3: Ranking Kelas --}}
    <div>
        <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
            <i class="fas fa-ranking-star"></i> Ranking Kehadiran per Kelas
        </p>
        <div class="dl-chart-wrap" id="chart-ranking-kelas" style="min-height:260px;"></div>
    </div>
</div>

@php
    // Siapkan data tren kehadiran untuk JS
    $trenLabels = collect($trenKehadiran)->pluck('tgl')->toArray();
    $trenHadir = collect($trenKehadiran)->pluck('hadir')->toArray();
    $trenTerlambat = collect($trenKehadiran)->pluck('terlambat')->toArray();
    $trenAlfa = collect($trenKehadiran)->pluck('alfa')->toArray();
    $trenSakit = collect($trenKehadiran)->pluck('sakit')->toArray();
    $trenIzinArr = collect($trenKehadiran)->pluck('izin')->toArray();
    $trenPkl = collect($trenKehadiran)->pluck('pkl')->toArray();

    // Distribusi status
    $statusLabels = array_keys($distribusiStatus);
    $statusVals = array_values($distribusiStatus);
    $statusColors = [
        'hadir' => '#22c55e',
        'terlambat' => '#f59e0b',
        'alfa' => '#ef4444',
        'sakit' => '#8b5cf6',
        'izin' => '#3b82f6',
        'pkl' => '#14b8a6',
    ];

    // Jam masuk
    $jamLabels = [];
    $jamVals = [];
    for ($h = 5; $h <= 14; $h++) {
        $jamLabels[] = sprintf('%02d:00', $h);
        $jamVals[] = $distribusiJam[$h] ?? 0;
    }

    // Ranking kelas
    $rankLabels = collect($rankingKelas)->pluck('kelas')->toArray();
    $rankVals = collect($rankingKelas)->pluck('pct')->toArray();

    // Heatmap — ubah ke format ApexCharts heatmap series
    $heatmapByKelas = [];
    foreach ($heatmapKelas as $row) {
        $heatmapByKelas[$row['kelas']][] = ['x' => $row['tanggal'], 'y' => $row['pct']];
    }
    $heatmapSeries = [];
    foreach ($heatmapByKelas as $kelasName => $data) {
        $heatmapSeries[] = ['name' => $kelasName, 'data' => $data];
    }
@endphp

<script>
    window.__dl_kehadiran = {
        trenLabels: @json($trenLabels),
        trenHadir: @json($trenHadir),
        trenTerlambat: @json($trenTerlambat),
        trenAlfa: @json($trenAlfa),
        trenSakit: @json($trenSakit),
        trenIzin: @json($trenIzinArr),
        trenPkl: @json($trenPkl),
        statusLabels: @json($statusLabels),
        statusVals: @json($statusVals),
        statusColors: @json($statusColors),
        jamLabels: @json($jamLabels),
        jamVals: @json($jamVals),
        rankLabels: @json($rankLabels),
        rankVals: @json($rankVals),
        heatmapSeries: @json($heatmapSeries),
    };
</script>
