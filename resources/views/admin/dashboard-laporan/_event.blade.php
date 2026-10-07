{{-- ═══════════════════════════════════════════
     SEKSI 5 — EVENT SISWA & GURU
═══════════════════════════════════════════ --}}
<div class="dl-section">
    <div class="dl-section-head">
        <div class="dl-section-icon" style="background:#ccfbf1;color:#0f766e;">
            <i class="fas fa-calendar-star"></i>
        </div>
        <div>
            <h3>Event & Absensi Event</h3>
            <p style="margin:0;font-size:.68rem;color:#94a3b8;">Partisipasi scan masuk/pulang dan kategori event</p>
        </div>
    </div>

    {{-- Row: Event Siswa + Kategori --}}
    <div class="dl-chart-row cols-2-1" style="margin-bottom:14px;">
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-chart-bar"></i> Partisipasi Event Siswa (Scan)
            </p>
            @if (count($partisipasiEvent) > 0)
                <div class="dl-chart-wrap" id="chart-event-siswa" style="min-height:220px;"></div>
            @else
                <div style="padding:28px;text-align:center;color:#94a3b8;font-size:.78rem;">
                    <i class="fas fa-calendar-xmark"
                        style="display:block;font-size:1.5rem;margin-bottom:8px;opacity:.4;"></i>
                    Tidak ada event dalam periode ini
                </div>
            @endif
        </div>
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-chart-pie"></i> Kategori Event
            </p>
            @if (count($kategoriEvent) > 0)
                <div class="dl-chart-wrap" id="chart-kategori-event" style="min-height:220px;"></div>
            @else
                <div style="padding:28px;text-align:center;color:#94a3b8;font-size:.78rem;">
                    <i class="fas fa-tag" style="display:block;font-size:1.5rem;margin-bottom:8px;opacity:.4;"></i>
                    Tidak ada data kategori
                </div>
            @endif
        </div>
    </div>

    {{-- Row: Event Guru --}}
    <div>
        <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
            <i class="fas fa-chalkboard-teacher"></i> Partisipasi Event Guru (GTK)
        </p>
        @if (count($partisipasiEventGuru) > 0)
            <div class="dl-chart-wrap" id="chart-event-guru" style="min-height:200px;"></div>
        @else
            <div style="padding:20px;text-align:center;color:#94a3b8;font-size:.78rem;">
                <i class="fas fa-user-slash" style="display:block;font-size:1.3rem;margin-bottom:6px;opacity:.4;"></i>
                Tidak ada event guru dalam periode ini
            </div>
        @endif
    </div>
</div>

@php
    $evSiswaNames = collect($partisipasiEvent)->pluck('nama')->toArray();
    $evSiswaMasuk = collect($partisipasiEvent)->pluck('scan_masuk')->toArray();
    $evSiswaPulang = collect($partisipasiEvent)->pluck('scan_pulang')->toArray();

    $evKategoriLabels = array_keys($kategoriEvent);
    $evKategoriVals = array_values($kategoriEvent);

    $evGuruNames = collect($partisipasiEventGuru)->pluck('nama')->toArray();
    $evGuruMasuk = collect($partisipasiEventGuru)->pluck('scan_masuk')->toArray();
    $evGuruPulang = collect($partisipasiEventGuru)->pluck('scan_pulang')->toArray();
@endphp

<script>
    window.__dl_event = {
        siswaNames: @json($evSiswaNames),
        siswaMasuk: @json($evSiswaMasuk),
        siswaPulang: @json($evSiswaPulang),
        kategoriLabels: @json($evKategoriLabels),
        kategoriVals: @json($evKategoriVals),
        guruNames: @json($evGuruNames),
        guruMasuk: @json($evGuruMasuk),
        guruPulang: @json($evGuruPulang),
    };
</script>
