@php
    $evActive   = $ev->tanggal_mulai <= now() && $ev->tanggal_selesai >= now();
    $evUpcoming = $ev->tanggal_mulai > now();
    $evEnded    = !$evActive && !$evUpcoming;
    $isMyEv     = !$isSiswa && $ev->created_by === auth()->id();
@endphp
<div class="evi">
    <div class="evi-top">
        <span class="evi-name">{{ Str::limit($ev->nama_event, 34) }}</span>
        @if($evActive)<span class="badge-ev-active"><i class="fas fa-circle" style="font-size:.4rem"></i> Aktif</span>
        @elseif($evUpcoming)<span class="badge-ev-upcoming"><i class="fas fa-clock" style="font-size:.6rem"></i> Segera</span>
        @else<span class="badge-ev-ended">Selesai</span>
        @endif
    </div>
    <div class="evi-mid">
        <span class="evi-chip"><i class="fas fa-calendar"></i> {{ $ev->tanggal_mulai->format('d M Y') }}</span>
        <span class="evi-chip"><i class="fas fa-clock"></i> {{ $ev->tanggal_mulai->format('H:i') }}–{{ $ev->tanggal_selesai->format('H:i') }}</span>
        @if($ev->lokasi)<span class="evi-chip"><i class="fas fa-map-marker-alt"></i> {{ Str::limit($ev->lokasi, 18) }}</span>@endif
        @if($ev->category)<span class="evi-chip"><i class="fas fa-tag"></i> {{ $ev->category->nama_kategori }}</span>@endif
        @if($ev->is_ekstrakurikuler)<span class="tag-ekstra"><i class="fas fa-futbol" style="font-size:.6rem"></i> Ekstra</span>@endif
        @if($ev->ada_absen_masuk)<span class="tag-masuk"><i class="fas fa-sign-in-alt" style="font-size:.6rem"></i> Masuk</span>@endif
        @if($ev->ada_absen_pulang)<span class="tag-pulang"><i class="fas fa-sign-out-alt" style="font-size:.6rem"></i> Pulang</span>@endif
    </div>
    <div class="evi-bot">
        <span style="font-size:.7rem;color:#64748b">
            @if($ev->berlaku_untuk_semua) <i class="fas fa-users"></i> Semua kelas
            @elseif($ev->mode_peserta==='kelas') <i class="fas fa-door-open"></i> {{ $ev->kelas->count() }} kelas
            @else <i class="fas fa-user-graduate"></i> {{ $ev->siswa->count() }} siswa
            @endif
        </span>
        <div style="display:flex;gap:5px">
            <a href="{{ route('event.show', $ev) }}" class="action-btn btn-view"><i class="fas fa-eye"></i> Detail</a>
            @if($isSiswa && $evActive && $siswaId)
                @php
                    $reko = $ev->absenEvent()->where('siswa_id',$siswaId)->first();
                    $sMasuk  = $reko && $reko->waktu_masuk !== null;
                    $sPulang = $reko && $reko->waktu_pulang !== null;
                    if(!$sMasuk && $ev->ada_absen_masuk){$nextJ='masuk';$showSc=true;}
                    elseif($sMasuk && !$sPulang && $ev->ada_absen_pulang){$nextJ='pulang';$showSc=true;}
                    else{$nextJ=null;$showSc=false;}
                @endphp
                @if($showSc)
                    <a href="{{ route('event.scan', ['event'=>$ev,'jenis'=>$nextJ]) }}" class="action-btn btn-scan"><i class="fas fa-qrcode"></i> Scan</a>
                @else
                    <span class="action-btn btn-done"><i class="fas fa-check-circle"></i></span>
                @endif
            @endif
            @if(!$isSiswa && ($isMyEv || $canViewAll))
                <a href="{{ route('event.edit', $ev) }}" class="action-btn btn-edit"><i class="fas fa-pen"></i></a>
            @endif
        </div>
    </div>
</div>
