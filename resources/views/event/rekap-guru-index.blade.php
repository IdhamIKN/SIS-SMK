@extends('layouts.app')

@section('title', 'Rekap Absen Guru per Event')

@push('styles')
@include('components.event-styles')
<style>
    .event-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .82rem;
    }
    .event-table thead tr {
        background: #f8fafc;
        border-bottom: 2px solid var(--border, #e2e8f0);
    }
    .event-table th {
        padding: 10px 12px;
        text-align: left;
        font-size: .68rem;
        font-weight: 700;
        color: var(--text-muted, #64748b);
        text-transform: uppercase;
        white-space: nowrap;
    }
    .event-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .event-table tbody tr:last-child td { border-bottom: none; }
    .event-table tbody tr:hover td { background: #fafbfc; }

    .badge-aktif  { background: #dcfce7; color: #15803d; }
    .badge-selesai { background: #f1f5f9; color: #64748b; }
    .status-badge {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: .65rem;
        font-weight: 700;
    }
    .btn-detail {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 8px;
        font-size: .75rem;
        font-weight: 600;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s;
    }
    .btn-detail:hover { background: #dbeafe; }

    .action-bar {
        position: fixed;
        bottom: var(--footer-h);
        left: 0; right: 0;
        padding: 10px 16px 12px;
        background: rgba(255,255,255,.96);
        border-top: 1px solid #e2e8f0;
        display: flex; gap: 8px;
        z-index: 999;
    }
    .ab-btn {
        flex: 1;
        display: inline-flex;
        align-items: center; justify-content: center;
        gap: 7px;
        padding: 12px 10px;
        border-radius: 12px;
        font-size: .82rem; font-weight: 700;
        border: none; cursor: pointer;
        text-decoration: none; font-family: inherit;
        line-height: 1;
    }
    .ab-btn-back { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
</style>
@endpush

@section('content')
<div class="event-wrap" style="padding-bottom: calc(var(--footer-h) + 80px);">

    {{-- HEADER --}}
    <div class="page-strip" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
        <h2>
            <i class="fas fa-chalkboard-teacher"></i>
            Rekap Absen Guru
        </h2>
        <p>Kehadiran guru di setiap event</p>
    </div>

    {{-- STATS --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;">
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:14px; text-align:center;">
            <div style="font-size:1.5rem; font-weight:800; color:#2563eb; line-height:1; margin-bottom:4px;">
                {{ $events->total() }}
            </div>
            <div style="font-size:.65rem; color:#64748b; font-weight:600; text-transform:uppercase;">Total Event</div>
        </div>
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:14px; text-align:center;">
            <div style="font-size:1.5rem; font-weight:800; color:#16a34a; line-height:1; margin-bottom:4px;">
                {{ $events->where('absen_event_guru_count', '>', 0)->count() }}
            </div>
            <div style="font-size:.65rem; color:#64748b; font-weight:600; text-transform:uppercase;">Ada Absen Guru</div>
        </div>
    </div>

    {{-- TABEL EVENT --}}
    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#eff6ff;">
                <i class="fas fa-list-alt" style="color:#2563eb;"></i>
            </div>
            <h3>Daftar Event</h3>
            <span class="hbadge">{{ $events->total() }} event</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="event-table">
                <thead>
                    <tr>
                        <th>Nama Event</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Absen Guru</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr>
                            <td>
                                <div style="font-weight:600; max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $event->nama_event }}
                                </div>
                                @if ($event->category)
                                    <div style="font-size:.7rem; color:#94a3b8;">{{ $event->category->nama_kategori }}</div>
                                @endif
                            </td>
                            <td style="white-space:nowrap; font-size:.78rem;">
                                {{ $event->tanggal_mulai->format('d M Y') }}<br>
                                <span style="color:#94a3b8;">{{ $event->tanggal_mulai->format('H:i') }} – {{ $event->tanggal_selesai->format('H:i') }}</span>
                            </td>
                            <td>
                                <span class="status-badge {{ $event->isActive() ? 'badge-aktif' : 'badge-selesai' }}">
                                    {{ $event->isActive() ? 'Aktif' : 'Selesai' }}
                                </span>
                            </td>
                            <td style="text-align:center; font-weight:700; color:{{ $event->absen_event_guru_count > 0 ? '#2563eb' : '#94a3b8' }};">
                                {{ $event->absen_event_guru_count }}
                            </td>
                            <td>
                                <a href="{{ route('event-guru.rekap', $event) }}" class="btn-detail">
                                    <i class="fas fa-eye"></i> Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; color:#94a3b8; padding:24px;">
                                Belum ada event
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
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

</div>

<div class="action-bar">
    <a href="{{ route('event.index') }}" class="ab-btn ab-btn-back">
        <i class="fas fa-arrow-left"></i> Kembali ke Event
    </a>
</div>
@endsection
