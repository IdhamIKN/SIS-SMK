{{-- ═══════════════════════════════════════════
     SEKSI 3 — PENGAJUAN IZIN
═══════════════════════════════════════════ --}}
<div class="dl-section">
    <div class="dl-section-head">
        <div class="dl-section-icon" style="background:#ede9fe;color:#6d28d9;">
            <i class="fas fa-file-signature"></i>
        </div>
        <div>
            <h3>Pengajuan Izin Siswa</h3>
            <p style="margin:0;font-size:.68rem;color:#94a3b8;">Tren, status persetujuan, dan jenis izin</p>
        </div>
    </div>

    <div class="dl-chart-row cols-2" style="margin-bottom:14px;">
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-chart-bar"></i> Tren Pengajuan Izin
            </p>
            <div class="dl-chart-wrap" id="chart-tren-izin" style="min-height:220px;"></div>
        </div>
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-chart-donut"></i> Status Persetujuan
            </p>
            <div class="dl-chart-wrap" id="chart-status-izin" style="min-height:220px;"></div>
        </div>
    </div>

    <div>
        <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
            <i class="fas fa-list-check"></i> Distribusi Jenis Izin
        </p>
        <div class="dl-chart-wrap" id="chart-jenis-izin" style="min-height:180px;"></div>
    </div>
</div>

@php
    $izinTrenLabels = collect($trenIzin)->pluck('tgl')->toArray();
    $izinTrenTotal = collect($trenIzin)->pluck('total')->toArray();
    $izinTrenSakit = collect($trenIzin)->pluck('sakit')->toArray();
    $izinTrenPulang = collect($trenIzin)->pluck('pulang_cepat')->toArray();
    $izinTrenTerlambat = collect($trenIzin)->pluck('terlambat')->toArray();
    $izinTrenLainnya = collect($trenIzin)->pluck('lainnya')->toArray();

    $statusIzinLabels = array_keys($statusIzin);
    $statusIzinVals = array_values($statusIzin);

    // Map nilai enum ke label yang lebih ramah
    $jenisIzinMap = [
        'izin_sakit' => 'Izin Sakit',
        'izin_pulang_cepat' => 'Pulang Cepat',
        'izin_terlambat' => 'Terlambat',
        'izin_lainnya' => 'Lainnya',
    ];
    $jenisIzinLabels = array_map(fn($k) => $jenisIzinMap[$k] ?? $k, array_keys($distribusiJenisIzin));
    $jenisIzinVals = array_values($distribusiJenisIzin);
@endphp

<script>
    window.__dl_izin = {
        trenLabels: @json($izinTrenLabels),
        trenTotal: @json($izinTrenTotal),
        trenSakit: @json($izinTrenSakit),
        trenPulang: @json($izinTrenPulang),
        trenTerlambat: @json($izinTrenTerlambat),
        trenLainnya: @json($izinTrenLainnya),
        statusLabels: @json($statusIzinLabels),
        statusVals: @json($statusIzinVals),
        jenisLabels: @json($jenisIzinLabels),
        jenisVals: @json($jenisIzinVals),
    };
</script>
