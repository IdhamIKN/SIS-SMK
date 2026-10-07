{{--
    Partial: event/_card.blade.php
    Dipakai oleh event/index.blade.php

    Variabel yang dibutuhkan:
      $event      — instance Event
      $isSiswa    — bool
      $siswaId    — int|null
      $canViewAll — bool (user bisa lihat semua event)
--}}
@php
    $isActive   = $event->isActive();
    $isUpcoming = $event->tanggal_mulai->isFuture() && !$isActive;
    $isEnded    = !$isActive && !$isUpcoming;
    $isMyEvent  = !$isSiswa && $event->created_by === auth()->id();
@endphp

<div class="card">
    <div class="c-head">
        <div class="c-icon" style="background:var(--event-fade, #fef3c7);">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div style="flex:1; min-width:0;">
            <h3>{{ Str::limit($event->nama_event, 38) }}</h3>
        </div>

        {{-- Badge status --}}
        @if ($isActive)
            <span class="hbadge badge-status status-active">
                <i class="fas fa-circle" style="font-size:.5rem;"></i> Aktif
            </span>
        @elseif ($isUpcoming)
            <span class="hbadge badge-status status-upcoming">
                <i class="fas fa-clock"></i> Segera
            </span>
        @else
            <span class="hbadge badge-status status-ended">
                <i class="fas fa-check"></i> Selesai
            </span>
        @endif
    </div>

    <div class="c-body" style="padding:12px 16px 14px;">

        {{--
            Label pembuat event — hanya tampil jika user bisa lihat semua
            dan event ini BUKAN miliknya sendiri, supaya tidak redundan.
        --}}
        @if ($canViewAll && !$isMyEvent && $event->creator)
            <div style="
                display: inline-flex;
                align-items: center;
                gap: 5px;
                font-size: .68rem;
                color: #7c3aed;
                background: #ede9fe;
                border: 1px solid #ddd6fe;
                border-radius: 20px;
                padding: 2px 9px;
                margin-bottom: 8px;
                font-weight: 600;
            ">
                <i class="fas fa-user-edit"></i>
                {{ $event->creator->name ?? 'Unknown' }}
            </div>
        @elseif ($canViewAll && $isMyEvent)
            <div style="
                display: inline-flex;
                align-items: center;
                gap: 5px;
                font-size: .68rem;
                color: #6d28d9;
                background: #ede9fe;
                border: 1px solid #ddd6fe;
                border-radius: 20px;
                padding: 2px 9px;
                margin-bottom: 8px;
                font-weight: 600;
            ">
                <i class="fas fa-user-check"></i> Event Saya
            </div>
        @endif

        <p style="color:var(--text-muted);font-size:.84rem;line-height:1.55;margin:0 0 8px;">
            {{ $event->deskripsi ? Str::limit($event->deskripsi, 100) : 'Tidak ada deskripsi' }}
        </p>

        <div class="event-meta">
            <span>
                <i class="fas fa-clock"></i>
                {{ $event->tanggal_mulai->format('H:i') }} – {{ $event->tanggal_selesai->format('H:i') }}
            </span>
            @if (!$event->tanggal_mulai->isToday())
                <span>
                    <i class="fas fa-calendar"></i>
                    {{ $event->tanggal_mulai->translatedFormat('d M Y') }}
                </span>
            @endif
            @if ($event->lokasi)
                <span>
                    <i class="fas fa-map-marker-alt"></i>
                    {{ Str::limit($event->lokasi, 20) }}
                </span>
            @endif
            @if ($event->category)
                <span>
                    <i class="fas fa-tag"></i>
                    {{ $event->category->nama_kategori }}
                </span>
            @endif
            @if ($event->recurrenceRule)
                <span>
                    <i class="fas fa-sync-alt"></i>
                    {{ $event->recurrenceRule->label() }}
                </span>
            @endif
            @if ($event->absen_event_count > 0)
                <span>
                    <i class="fas fa-users"></i>
                    {{ $event->absen_event_count }} scan
                </span>
            @endif
        </div>

        @if ($event->ada_absen_masuk || $event->ada_absen_pulang)
            <div class="absen-tags">
                @if ($event->ada_absen_masuk)
                    <span class="tag tag-masuk">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </span>
                @endif
                @if ($event->ada_absen_pulang)
                    <span class="tag tag-pulang">
                        <i class="fas fa-sign-out-alt"></i> Pulang
                    </span>
                @endif
                @if ($event->recurrenceRule && !$event->recurrence_parent_id)
                    <span class="tag tag-repeat">
                        <i class="fas fa-sync-alt"></i> Berulang
                    </span>
                @elseif ($event->recurrence_parent_id)
                    <span class="tag" style="background:#e0f2fe;color:#0369a1;">
                        <i class="fas fa-link"></i> Kemunculan ke-{{ $event->recurrence_sequence }}
                    </span>
                @endif
            </div>
        @endif

        {{-- Jika ini child event, tampilkan link ke master --}}
        @if (!$isSiswa && $event->recurrence_parent_id)
            <div style="margin-bottom:8px;">
                <a href="{{ route('event.show', $event->recurrence_parent_id) }}"
                   style="display:inline-flex;align-items:center;gap:5px;font-size:.7rem;font-weight:600;
                          color:#0ea5e9;background:#e0f2fe;border:1px solid #bae6fd;
                          padding:3px 10px;border-radius:20px;text-decoration:none;">
                    <i class="fas fa-sync-alt"></i>
                    Lihat Event Master
                </a>
            </div>
        @endif

        <div class="action-group">

            {{-- Tombol Detail selalu tampil --}}
            <a href="{{ route('event.show', $event) }}" class="action-btn btn-view">
                <i class="fas fa-eye"></i> Detail
            </a>

            {{-- Tombol Scan Siswa --}}
            @if ($isSiswa && $isActive && $siswaId)
                @php
                    // Pola unified: 1 record per siswa per event
                    $rekoEvent   = $event->absenEvent()->where('siswa_id', $siswaId)->first();
                    $sudahMasuk  = $rekoEvent && $rekoEvent->waktu_masuk !== null;
                    $sudahPulang = $rekoEvent && $rekoEvent->waktu_pulang !== null;

                    if (!$sudahMasuk && $event->ada_absen_masuk) {
                        $nextJenis = 'masuk';
                        $showScan  = true;
                    } elseif ($sudahMasuk && !$sudahPulang && $event->ada_absen_pulang) {
                        $nextJenis = 'pulang';
                        $showScan  = true;
                    } else {
                        $nextJenis = null;
                        $showScan  = false;
                    }
                @endphp

                @if ($showScan)
                    <a href="{{ route('event.scan', ['event' => $event, 'jenis' => $nextJenis]) }}"
                       class="action-btn btn-scan">
                        <i class="fas fa-qrcode"></i>
                        {{ $nextJenis === 'masuk' ? 'Scan Masuk' : 'Scan Pulang' }}
                    </a>
                @else
                    <span class="action-btn btn-done">
                        <i class="fas fa-check-circle"></i> Absen Lengkap
                    </span>
                @endif
            @endif

            {{-- Rekap untuk siswa di event selesai --}}
            @if ($isSiswa && $isEnded)
                <a href="{{ route('event.rekap', $event) }}" class="action-btn btn-view">
                    <i class="fas fa-table"></i> Rekap Saya
                </a>
            @endif

            {{--
                Tombol Edit:
                - Tampil hanya jika user adalah pembuat event ($isMyEvent), ATAU
                - User punya canViewAll (admin/kepsek/dll) — mereka boleh edit semua
            --}}
            @if (!$isSiswa && ($isMyEvent || $canViewAll))
                <a href="{{ route('event.edit', $event) }}" class="action-btn btn-edit">
                    <i class="fas fa-pen"></i>
                </a>
            @endif

        </div>
    </div>
</div>