@extends('layouts.app')

@section('title', 'Daftar Event')

@push('styles')
    @include('components.event-styles')
    <style>
        .event-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: .75rem;
            color: var(--text-muted);
            margin: 6px 0 10px;
        }
        .event-meta span { display: inline-flex; align-items: center; gap: 4px; }

        .absen-tags {
            display: inline-flex;
            gap: 6px;
            font-size: .7rem;
            margin-bottom: 10px;
        }
        .tag { padding: 2px 8px; border-radius: 20px; font-weight: 600; }
        .tag-masuk  { background: #dcfce7; color: #15803d; }
        .tag-pulang { background: #dbeafe; color: #1d4ed8; }

        .action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .action-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 13px; border-radius: 8px;
            font-size: .78rem; font-weight: 600;
            border: none; cursor: pointer;
            text-decoration: none; font-family: inherit;
            transition: all .18s; white-space: nowrap;
        }
        .action-btn:active { transform: scale(.96); }
        .btn-view  { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .btn-view:hover  { background: #dbeafe; }
        .btn-scan  { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }
        .btn-scan:hover  { background: #dcfce7; }
        .btn-edit  { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .btn-edit:hover  { background: #fef3c7; }

        .status-active { background: #dcfce7; color: #15803d; }
        .status-ended  { background: #f1f5f9; color: #64748b; }
    </style>
@endpush

@section('content')
    @php
        $isSiswa = auth()->user()->hasRole('siswa');
        $siswaId = $isSiswa ? auth()->user()->siswa->id ?? null : null;
    @endphp
<div class="event-wrap" style="padding-bottom: calc(var(--footer-h) + 80px);">

    {{-- Page Strip --}}
    <div class="page-strip page-strip-event">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
        <h2>
            <i class="fas fa-calendar-alt"></i>
            Event &amp; Absensi
        </h2>
        <p>Kelola acara dan absensi barcode</p>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert a-ok">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="status-bar">
        <div class="s-chip">
            <div class="ci ci-e"><i class="fas fa-calendar-check"></i></div>
            <div>
                <div class="c-lbl">Total Event</div>
                <div class="c-val">{{ $events->total() }}</div>
            </div>
        </div>
        <div class="s-chip">
            <div class="ci ci-g"><i class="fas fa-play-circle"></i></div>
            <div>
                <div class="c-lbl">Sedang Aktif</div>
                <div class="c-val">{{ $events->filter(fn($e) => $e->isActive())->count() }}</div>
            </div>
        </div>
    </div>

    {{-- Event List --}}
    @if ($events->count() > 0)
        @foreach ($events as $event)
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:var(--event-fade, #fef3c7);">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <h3>{{ Str::limit($event->nama_event, 40) }}</h3>
                    </div>
                    <span class="hbadge badge-status {{ $event->isActive() ? 'status-active' : 'status-ended' }}">
                        {{ $event->isActive() ? 'Aktif' : 'Selesai' }}
                    </span>
                </div>

                <div class="c-body" style="padding:12px 16px 14px;">
                    <p style="color:var(--text-muted);font-size:.84rem;line-height:1.55;margin:0 0 8px;">
                        {{ $event->deskripsi ? Str::limit($event->deskripsi, 100) : 'Tidak ada deskripsi' }}
                    </p>

                    <div class="event-meta">
                        <span><i class="fas fa-clock"></i> {{ $event->tanggal_mulai->format('d M, H:i') }}</span>
                        @if ($event->lokasi)
                            <span><i class="fas fa-map-marker-alt"></i> {{ Str::limit($event->lokasi, 20) }}</span>
                        @endif
                        @if ($event->absen_event_count > 0)
                            <span><i class="fas fa-users"></i> {{ $event->absen_event_count }} scan</span>
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
                        <a href="{{ route('event.show', $event) }}" class="action-btn btn-view">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                        @if ($isSiswa && $event->isActive() && $siswaId)
                            @php
                                $sudahMasuk = $event->absenEvent()->where('siswa_id', $siswaId)->where('jenis', 'masuk')->exists();
                                $jenis = $sudahMasuk && $event->ada_absen_pulang ? 'pulang' : 'masuk';
                                $label = $jenis === 'masuk' ? 'Scan Masuk' : 'Scan Pulang';
                            @endphp
                            @if (($jenis === 'masuk' && $event->ada_absen_masuk) || ($jenis === 'pulang' && $event->ada_absen_pulang))
                                <a href="{{ route('event.scan', ['event' => $event, 'jenis' => $jenis]) }}" class="action-btn btn-scan">
                                    <i class="fas fa-qrcode"></i> {{ $label }}
                                </a>
                            @endif
                        @endif
                        @if (!$isSiswa)
                            <a href="{{ route('event.edit', $event) }}" class="action-btn btn-edit">
                                <i class="fas fa-pen"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Pagination --}}
        @if ($events->hasPages())
            <div class="pagination-chips">
                @if (!$events->onFirstPage())
                    <a href="{{ $events->previousPageUrl() }}" class="page-chip">← Sebelumnya</a>
                @endif
                <span class="page-chip active">{{ $events->currentPage() }} / {{ $events->lastPage() }}</span>
                @if ($events->hasMorePages())
                    <a href="{{ $events->nextPageUrl() }}" class="page-chip">Berikutnya →</a>
                @endif
            </div>
        @endif

    @else
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-calendar-plus"></i></div>
            <h3 class="empty-title">Belum ada event</h3>
            <p class="empty-text">Buat event pertama untuk memulai absensi barcode.</p>
            @if (!$isSiswa)
                <a href="{{ route('event.create') }}" class="btn-sub">
                    <i class="fas fa-plus"></i> Tambah Event Pertama
                </a>
            @endif
        </div>
    @endif

</div>

{{-- FAB: Tambah Event --}}
@if (!$isSiswa)
    <a href="{{ route('event.create') }}" class="fab-add">
        <i class="fas fa-plus"></i> Tambah Event
    </a>
@endif
@endsection