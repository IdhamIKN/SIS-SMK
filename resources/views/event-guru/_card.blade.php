@php
    $isActive = $event->isActive();
    $isUpcoming = $event->tanggal_mulai->isFuture();

    if ($isActive) {
        $statusClass = 'status-active';
        $statusLabel = 'Aktif';
    } elseif ($isUpcoming) {
        $statusClass = 'status-upcoming';
        $statusLabel = 'Segera';
    } else {
        $statusClass = 'status-ended';
        $statusLabel = 'Selesai';
    }
@endphp

<div class="card">
    <div class="c-head">
        <div class="c-icon" style="background:#e0e7ff;">
            <i class="fas fa-calendar-day" style="color:#4338ca;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <h3>{{ Str::limit($event->nama_event, 38) }}</h3>
        </div>
        <span class="hbadge badge-status {{ $statusClass }}">
            {{ $statusLabel }}
        </span>
    </div>

    <div class="c-body" style="padding:12px 16px 14px;">
        <p style="color:var(--text-muted);font-size:.84rem;line-height:1.55;margin:0 0 8px;">
            {{ $event->deskripsi ? Str::limit($event->deskripsi, 90) : 'Tidak ada deskripsi' }}
        </p>

        <div class="event-meta">
            <span>
                <i class="fas fa-clock"></i>
                @if ($event->tanggal_mulai->isSameDay($event->tanggal_selesai))
                    {{-- 1 hari: tampilkan tanggal sekali + jam mulai–selesai --}}
                    {{ $event->tanggal_mulai->format('d M Y') }},
                    {{ $event->tanggal_mulai->format('H:i') }} – {{ $event->tanggal_selesai->format('H:i') }}
                @else
                    {{-- Beda hari: tampilkan rentang tanggal penuh --}}
                    {{ $event->tanggal_mulai->format('d M Y, H:i') }}
                    – {{ $event->tanggal_selesai->format('d M Y, H:i') }}
                @endif
            </span>
            @if ($event->lokasi)
                <span><i class="fas fa-map-marker-alt"></i> {{ Str::limit($event->lokasi, 20) }}</span>
            @endif
            @if ($event->absen_event_guru_count > 0)
                <span><i class="fas fa-user-check"></i> {{ $event->absen_event_guru_count }} scan</span>
            @endif
        </div>

        @if ($event->ada_absen_masuk || $event->ada_absen_pulang)
            <div class="absen-tags">
                @if ($event->ada_absen_masuk)
                    <span class="tag tag-masuk"><i class="fas fa-sign-in-alt"></i> Masuk</span>
                @endif
                @if ($event->ada_absen_pulang)
                    <span class="tag tag-pulang"><i class="fas fa-sign-out-alt"></i> Pulang</span>
                @endif
            </div>
        @endif

        <div class="action-group">
            <a href="{{ route('event-guru.show', $event) }}" class="action-btn btn-view">
                <i class="fas fa-eye"></i> Detail
            </a>

            @if ($isGuru && $myGtkId && $isActive)
                @php
                    $myAbsen = $event->absenEventGuru()->where('gtk_id', $myGtkId)->get();
                    $myMasuk = $myAbsen->where('jenis', 'masuk')->isNotEmpty();
                    $myPulang = $myAbsen->where('jenis', 'pulang')->isNotEmpty();
                    $jenisNext = $myMasuk && $event->ada_absen_pulang ? 'pulang' : 'masuk';
                    $done = ($myMasuk && !$event->ada_absen_pulang) || $myPulang;
                @endphp
                @if (!$done)
                    <a href="{{ route('event-guru.scan', ['eventGuru' => $event, 'jenis' => $jenisNext]) }}"
                        class="action-btn btn-scan">
                        <i class="fas fa-qrcode"></i>
                        {{ $jenisNext === 'masuk' ? 'Scan Masuk' : 'Scan Pulang' }}
                    </a>
                @else
                    <span class="action-btn"
                        style="background:#dcfce7;color:#15803d;border:1px solid #86efac;cursor:default;">
                        <i class="fas fa-check"></i> Sudah Absen
                    </span>
                @endif
            @endif

            @if ($isAdmin)
                <a href="{{ route('event-guru.edit', $event) }}" class="action-btn btn-edit">
                    <i class="fas fa-pen"></i>
                </a>
            @endif
        </div>
    </div>
</div>
