@php
    $now2 = now();
    $evActive = $ev->tanggal_mulai <= $now2 && $ev->tanggal_selesai >= $now2;
    $evUpcoming = $ev->tanggal_mulai > $now2;
    $myAbsen = $myGtkId ? $ev->absenEventGuru->where('gtk_id', $myGtkId) : collect();
    $myMasuk = $myAbsen->where('jenis', 'masuk')->isNotEmpty();
    $myPulang = $myAbsen->where('jenis', 'pulang')->isNotEmpty();
    $doneGuru = ($myMasuk && !$ev->ada_absen_pulang) || $myPulang;
    $nextJ = $myMasuk && $ev->ada_absen_pulang ? 'pulang' : 'masuk';
    $showSc = $isGuru && $evActive && !$doneGuru;
@endphp
<div class="evi">
    <div class="evi-top">
        <span class="evi-name">{{ Str::limit($ev->nama_event, 32) }}</span>
        @if ($evActive)
            <span class="badge-ev-active"><i class="fas fa-circle" style="font-size:.4rem"></i> Aktif</span>
        @elseif($evUpcoming)
            <span class="badge-ev-upcoming">Segera</span>
        @else
            <span class="badge-ev-ended">Selesai</span>
        @endif
    </div>

    <div class="evi-mid">
        <span class="evi-chip"><i class="fas fa-calendar"></i> {{ $ev->tanggal_mulai->format('d M Y') }}</span>
        <span class="evi-chip"><i class="fas fa-clock"></i>
            {{ $ev->tanggal_mulai->format('H:i') }}–{{ $ev->tanggal_selesai->format('H:i') }}</span>
        @if ($ev->lokasi)
            <span class="evi-chip"><i class="fas fa-map-marker-alt"></i> {{ Str::limit($ev->lokasi, 18) }}</span>
        @endif
        @if ($ev->ada_absen_masuk)
            <span class="tag-masuk"><i class="fas fa-sign-in-alt" style="font-size:.6rem"></i> Masuk</span>
        @endif
        @if ($ev->ada_absen_pulang)
            <span class="tag-pulang"><i class="fas fa-sign-out-alt" style="font-size:.6rem"></i> Pulang</span>
        @endif
    </div>

    <div class="evi-bot">
        @if ($isGuru)
            <span style="font-size:.7rem;color:#64748b">
                @if ($myMasuk)
                    <i class="fas fa-check-circle" style="color:#16a34a"></i> Masuk
                @else
                    <i class="fas fa-times-circle" style="color:#94a3b8"></i> Belum
                @endif
                @if ($ev->ada_absen_pulang)
                    ·
                    @if ($myPulang)
                        <i class="fas fa-check-circle" style="color:#0ea5e9"></i> Pulang
                    @else
                        <i class="fas fa-times-circle" style="color:#94a3b8"></i> Belum
                    @endif
                @endif
            </span>
        @else
            <span></span>
        @endif

        <div style="display:flex;gap:5px">
            <a href="{{ route('event-guru.show', $ev) }}" class="action-btn btn-view">
                <i class="fas fa-eye"></i> Detail
            </a>
            @if ($showSc)
                <a href="{{ route('event-guru.scan', ['eventGuru' => $ev, 'jenis' => $nextJ]) }}"
                    class="action-btn btn-scan">
                    <i class="fas fa-qrcode"></i> Scan
                </a>
            @endif
            @if ($isAdmin)
                <a href="{{ route('event-guru.edit', $ev) }}" class="action-btn btn-edit">
                    <i class="fas fa-pen"></i>
                </a>
            @endif
        </div>
    </div>
</div>
