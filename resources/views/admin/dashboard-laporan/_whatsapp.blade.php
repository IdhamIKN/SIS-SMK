{{-- ═══════════════════════════════════════════
     SEKSI 7 — WHATSAPP & NOTIFIKASI
═══════════════════════════════════════════ --}}
<div class="dl-section">
    <div class="dl-section-head">
        <div class="dl-section-icon" style="background:#dcfce7;color:#15803d;">
            <i class="fab fa-whatsapp"></i>
        </div>
        <div>
            <h3>Notifikasi WhatsApp</h3>
            <p style="margin:0;font-size:.68rem;color:#94a3b8;">Volume pengiriman, status, dan distribusi jenis pesan</p>
        </div>
    </div>

    {{-- KPI WA mini --}}
    @php
        $waTotal = array_sum($statusWa);
        $waTerkirim = $statusWa['sukses'] ?? ($statusWa['terkirim'] ?? 0);
        $waGagal = ($statusWa['gagal'] ?? 0) + ($statusWa['failed'] ?? 0) + ($statusWa['error'] ?? 0);
        $waPending = $statusWa['pending'] ?? 0;
        $waSuccessRate = $waTotal > 0 ? round(($waTerkirim / $waTotal) * 100, 1) : 0;
    @endphp
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(100px,1fr));gap:8px;margin-bottom:14px;">
        @foreach ([['label' => 'Total Pesan', 'val' => $waTotal, 'bg' => '#f1f5f9', 'color' => '#334155', 'icon' => 'fa-comments'], ['label' => 'Terkirim', 'val' => $waTerkirim, 'bg' => '#dcfce7', 'color' => '#15803d', 'icon' => 'fa-check'], ['label' => 'Gagal', 'val' => $waGagal, 'bg' => '#fee2e2', 'color' => '#b91c1c', 'icon' => 'fa-times'], ['label' => 'Pending', 'val' => $waPending, 'bg' => '#fef9c3', 'color' => '#a16207', 'icon' => 'fa-clock'], ['label' => 'Success Rate', 'val' => $waSuccessRate . '%', 'bg' => '#dbeafe', 'color' => '#1d4ed8', 'icon' => 'fa-percent']] as $wk)
            <div style="background:{{ $wk['bg'] }};border-radius:10px;padding:10px 12px;text-align:center;">
                <div style="font-size:.8rem;color:{{ $wk['color'] }};margin-bottom:3px;"><i
                        class="fas {{ $wk['icon'] }}"></i></div>
                <div style="font-size:1.15rem;font-weight:800;color:{{ $wk['color'] }};line-height:1;">
                    {{ $wk['val'] }}</div>
                <div style="font-size:.6rem;color:#94a3b8;font-weight:700;text-transform:uppercase;margin-top:2px;">
                    {{ $wk['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Row: Tren WA + Status Donut --}}
    <div class="dl-chart-row cols-2-1" style="margin-bottom:14px;">
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-chart-bar"></i> Volume Pesan per Hari (per Jenis)
            </p>
            <div class="dl-chart-wrap" id="chart-tren-wa" style="min-height:220px;"></div>
        </div>
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-chart-pie"></i> Status Pengiriman
            </p>
            <div class="dl-chart-wrap" id="chart-status-wa" style="min-height:220px;"></div>
        </div>
    </div>

    {{-- Distribusi Jenis WA --}}
    <div>
        <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
            <i class="fas fa-tags"></i> Distribusi Jenis Notifikasi WA
        </p>
        <div class="dl-chart-wrap" id="chart-jenis-wa" style="min-height:180px;"></div>
    </div>
</div>

@php
    // Flatten tren WA menjadi series per jenis
    $waTrenDates = array_keys($trenWa);
    $waJenisAll = [];
    foreach ($trenWa as $d => $jenisMap) {
        $waJenisAll = array_merge($waJenisAll, array_keys($jenisMap));
    }
    $waJenisAll = array_unique($waJenisAll);

    $waTrenSeries = [];
    foreach ($waJenisAll as $jenis) {
        $vals = [];
        foreach ($waTrenDates as $d) {
            $vals[] = $trenWa[$d][$jenis] ?? 0;
        }
        $waTrenSeries[] = ['name' => $jenis, 'data' => $vals];
    }

    $waStatusLabels = array_keys($statusWa);
    $waStatusVals = array_values($statusWa);

    $waJenisLabels = array_keys($distribusiJenisWa);
    $waJenisVals = array_values($distribusiJenisWa);
@endphp

<script>
    window.__dl_wa = {
        trenDates: @json($waTrenDates),
        trenSeries: @json($waTrenSeries),
        statusLabels: @json($waStatusLabels),
        statusVals: @json($waStatusVals),
        jenisLabels: @json($waJenisLabels),
        jenisVals: @json($waJenisVals),
    };
</script>
