{{-- ═══════════════════════════════════════════
     SEKSI 4 — TATIB
═══════════════════════════════════════════ --}}
<div class="dl-section">
    <div class="dl-section-head">
        <div class="dl-section-icon" style="background:#fee2e2;color:#b91c1c;">
            <i class="fas fa-gavel"></i>
        </div>
        <div>
            <h3>Tata Tertib — Pelanggaran & Penghargaan</h3>
            <p style="margin:0;font-size:.68rem;color:#94a3b8;">Poin, tren, top pasal, top siswa, dan surat panggilan</p>
        </div>
    </div>

    {{-- Row 1: Tren Pelanggaran vs Penghargaan --}}
    <div class="dl-chart-row cols-2" style="margin-bottom:14px;">
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-chart-line"></i> Tren Pelanggaran
            </p>
            <div class="dl-chart-wrap" id="chart-tren-pelanggaran" style="min-height:200px;"></div>
        </div>
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-chart-line"></i> Tren Penghargaan
            </p>
            <div class="dl-chart-wrap" id="chart-tren-penghargaan" style="min-height:200px;"></div>
        </div>
    </div>

    {{-- Row 2: Top 10 Pasal — Pelanggaran & Penghargaan --}}
    <div class="dl-chart-row cols-2" style="margin-bottom:14px;">
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-list-ol"></i> Top 10 Pasal Pelanggaran
            </p>
            @if (count($topPelanggaran) > 0)
                <div class="dl-chart-wrap" id="chart-top-pasal" style="min-height:260px;"></div>
            @else
                <div style="padding:28px;text-align:center;color:#94a3b8;font-size:.78rem;">
                    <i class="fas fa-circle-check"
                        style="display:block;font-size:1.5rem;margin-bottom:8px;opacity:.4;"></i>
                    Tidak ada pelanggaran dalam periode ini
                </div>
            @endif
        </div>
        <div>
            <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                <i class="fas fa-list-ol"></i> Top 10 Pasal Penghargaan
            </p>
            @if (count($topPenghargaan) > 0)
                <div class="dl-chart-wrap" id="chart-top-pasal-penghargaan" style="min-height:260px;"></div>
            @else
                <div style="padding:28px;text-align:center;color:#94a3b8;font-size:.78rem;">
                    <i class="fas fa-award" style="display:block;font-size:1.5rem;margin-bottom:8px;opacity:.4;"></i>
                    Tidak ada penghargaan dalam periode ini
                </div>
            @endif
        </div>
    </div>

    {{-- Row 3: Top 10 Siswa — Pelanggaran & Penghargaan --}}
    @php
        $tatibSiswaTables = [
            [
                'title' => 'Top 10 Siswa Poin Pelanggaran Tertinggi',
                'icon' => 'fa-user-slash',
                'bg' => '#fee2e2',
                'color' => '#b91c1c',
                'data' => $topSiswa,
            ],
            [
                'title' => 'Top 10 Siswa Poin Penghargaan Tertinggi',
                'icon' => 'fa-user-graduate',
                'bg' => '#dcfce7',
                'color' => '#15803d',
                'data' => $topSiswaPenghargaan,
            ],
        ];
    @endphp
    <div class="dl-chart-row cols-2" style="margin-bottom:14px;">
        @foreach ($tatibSiswaTables as $tbl)
            <div>
                <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
                    <i class="fas {{ $tbl['icon'] }}"></i> {{ $tbl['title'] }}
                </p>
                <div class="dl-table-wrap">
                    <table style="width:100%;border-collapse:collapse;font-size:.75rem;">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th
                                    style="padding:7px 8px;text-align:left;font-size:.63rem;font-weight:700;color:#64748b;text-transform:uppercase;">
                                    #</th>
                                <th
                                    style="padding:7px 8px;text-align:left;font-size:.63rem;font-weight:700;color:#64748b;text-transform:uppercase;">
                                    Nama</th>
                                <th
                                    style="padding:7px 8px;text-align:left;font-size:.63rem;font-weight:700;color:#64748b;text-transform:uppercase;">
                                    Kelas</th>
                                <th
                                    style="padding:7px 8px;text-align:right;font-size:.63rem;font-weight:700;color:#64748b;text-transform:uppercase;">
                                    Poin</th>
                                <th
                                    style="padding:7px 8px;text-align:right;font-size:.63rem;font-weight:700;color:#64748b;text-transform:uppercase;">
                                    Kasus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tbl['data'] as $i => $s)
                                @php $s = (object) $s; @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;"
                                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                                    <td style="padding:7px 8px;color:#94a3b8;">{{ $i + 1 }}</td>
                                    <td style="padding:7px 8px;font-weight:600;color:#0f172a;">
                                        {{ \Illuminate\Support\Str::limit($s->nama_lengkap, 18) }}
                                    </td>
                                    <td style="padding:7px 8px;color:#64748b;">{{ $s->nama_kelas ?? '-' }}</td>
                                    <td style="padding:7px 8px;text-align:right;">
                                        <span
                                            style="display:inline-block;padding:2px 8px;border-radius:20px;background:{{ $tbl['bg'] }};color:{{ $tbl['color'] }};font-weight:700;font-size:.7rem;">
                                            {{ number_format($s->total_poin) }}
                                        </span>
                                    </td>
                                    <td style="padding:7px 8px;text-align:right;color:#64748b;">{{ $s->jumlah }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        style="padding:20px;text-align:center;color:#94a3b8;font-size:.78rem;">Tidak ada
                                        data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Surat Panggilan --}}
    <div>
        <p style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0 0 8px;">
            <i class="fas fa-envelope"></i> Surat Panggilan Orang Tua
        </p>
        <div class="dl-chart-wrap" id="chart-surat-panggilan" style="min-height:160px;"></div>
    </div>
</div>

@php
    $pelanggaranLabels = collect($trenPelanggaran)->pluck('tgl')->toArray();
    $pelanggaranVals = collect($trenPelanggaran)->pluck('total')->toArray();
    $penghargaanLabels = collect($trenPenghargaan)->pluck('tgl')->toArray();
    $penghargaanVals = collect($trenPenghargaan)->pluck('total')->toArray();

    $topPasalNames = collect($topPelanggaran)
        ->pluck('nama')
        ->map(fn($n) => \Illuminate\Support\Str::limit($n, 30))
        ->toArray();
    $topPasalVals = collect($topPelanggaran)->pluck('total')->toArray();

    $topPasalPenghargaanNames = collect($topPenghargaan)
        ->pluck('nama')
        ->map(fn($n) => \Illuminate\Support\Str::limit($n, 30))
        ->toArray();
    $topPasalPenghargaanVals = collect($topPenghargaan)->pluck('total')->toArray();

    $spLabels = collect($suratPanggilan)->map(fn($s) => 'Panggilan ke-' . ((object) $s)->panggilan_ke)->toArray();
    $spTotal = collect($suratPanggilan)->map(fn($s) => ((object) $s)->total)->toArray();
    $spWa = collect($suratPanggilan)->map(fn($s) => ((object) $s)->wa_terkirim)->toArray();
@endphp

<script>
    window.__dl_tatib = {
        pelanggaranLabels: @json($pelanggaranLabels),
        pelanggaranVals: @json($pelanggaranVals),
        penghargaanLabels: @json($penghargaanLabels),
        penghargaanVals: @json($penghargaanVals),
        topPasalNames: @json($topPasalNames),
        topPasalVals: @json($topPasalVals),
        topPasalPenghargaanNames: @json($topPasalPenghargaanNames),
        topPasalPenghargaanVals: @json($topPasalPenghargaanVals),
        spLabels: @json($spLabels),
        spTotal: @json($spTotal),
        spWa: @json($spWa),
    };
</script>
