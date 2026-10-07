@extends('layouts.app')

@section('title', 'Rekap Absen Guru - ' . $event->nama_event)

@push('styles')
@include('components.event-styles')
<style>
    .stat-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
        margin-bottom: 14px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 14px;
        padding: 14px;
        text-align: center;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: .65rem;
        color: var(--text-muted, #64748b);
        font-weight: 600;
        text-transform: uppercase;
    }

    .event-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .78rem;
    }

    .event-table thead tr {
        background: #f8fafc;
        border-bottom: 2px solid var(--border, #e2e8f0);
    }

    .event-table th {
        padding: 9px 12px;
        text-align: left;
        font-size: .68rem;
        font-weight: 700;
        color: var(--text-muted, #64748b);
        text-transform: uppercase;
    }

    .event-table td {
        padding: 8px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .jenis-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: .65rem;
        font-weight: 700;
    }

    .badge-masuk  { background: #dbeafe; color: #1d4ed8; }
    .badge-pulang { background: #ffedd5; color: #c2410c; }
    .badge-hadir  { background: #dcfce7; color: #15803d; }
    .badge-belum  { background: #f1f5f9; color: #64748b; }

    .action-bar {
        position: fixed;
        bottom: var(--footer-h);
        left: 0;
        right: 0;
        padding: 10px 16px 12px;
        background: rgba(255,255,255,.96);
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 8px;
        z-index: 999;
    }

    .ab-btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 12px 10px;
        border-radius: 12px;
        font-size: .82rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
        line-height: 1;
    }

    .ab-btn-back { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; flex: 0 0 auto; padding: 12px 14px; }
    .ab-btn-primary { background: #2563eb; color: #fff; }
</style>
@endpush

@section('content')
@php
    $isGuru = auth()->user()->hasRole('gtk');
    $myGtkId = $isGuru ? (auth()->user()->gtk->id ?? null) : null;

    $totalGuru   = $guruList->count();
    $hadirMasuk  = $event->absenEventGuru->where('jenis', 'masuk')->unique('gtk_id')->count();
    $hadirPulang = $event->absenEventGuru->where('jenis', 'pulang')->unique('gtk_id')->count();
@endphp

<div class="event-wrap" style="padding-bottom: calc(var(--footer-h) + 80px);">

    {{-- HEADER --}}
    <div class="page-strip" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ $event->tanggal_mulai->translatedFormat('l, d F Y') }}
        </div>
        <h2>
            <i class="fas fa-chalkboard-teacher"></i>
            Rekap Absen Guru
        </h2>
        <p>{{ Str::limit($event->nama_event, 40) }}</p>
    </div>

    {{-- STATS --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-value" style="color:#2563eb;">{{ $hadirMasuk }}</div>
            <div class="stat-label">Scan Masuk</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#f59e0b;">{{ $hadirPulang }}</div>
            <div class="stat-label">Scan Pulang</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#64748b;">{{ $totalGuru }}</div>
            <div class="stat-label">Total Guru</div>
        </div>
    </div>

    {{-- STATUS SAYA (untuk guru) --}}
    @if ($isGuru && $myGtkId)
        @php
            $myAbsen  = $absenByGuru->get($myGtkId, collect());
            $myMasuk  = $myAbsen->firstWhere('jenis', 'masuk');
            $myPulang = $myAbsen->firstWhere('jenis', 'pulang');
        @endphp
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:#eff6ff;"><i class="fas fa-user-check" style="color:#2563eb;"></i></div>
                <h3>Status Absen Saya</h3>
            </div>
            <div class="c-body" style="padding:12px 16px;">
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    @if ($event->ada_absen_masuk)
                        <div class="s-chip">
                            <div class="ci {{ $myMasuk ? 'ci-g' : '' }}">
                                <i class="fas fa-{{ $myMasuk ? 'check' : 'times' }}"></i>
                            </div>
                            <div>
                                <div class="c-lbl">Masuk</div>
                                <div class="c-val">
                                    {{ $myMasuk ? $myMasuk->waktu_scan->format('H:i') : 'Belum scan' }}
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($event->ada_absen_pulang)
                        <div class="s-chip">
                            <div class="ci {{ $myPulang ? 'ci-g' : '' }}">
                                <i class="fas fa-{{ $myPulang ? 'check' : 'times' }}"></i>
                            </div>
                            <div>
                                <div class="c-lbl">Pulang</div>
                                <div class="c-val">
                                    {{ $myPulang ? $myPulang->waktu_scan->format('H:i') : 'Belum scan' }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- TABEL REKAP --}}
    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#eff6ff;"><i class="fas fa-list-alt" style="color:#2563eb;"></i></div>
            <h3>Daftar Kehadiran Guru</h3>
            <span class="hbadge">{{ $event->absenEventGuru->count() }} scan</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="event-table">
                <thead>
                    <tr>
                        <th>Nama Guru</th>
                        <th>Masuk</th>
                        <th>Pulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($event->absenEventGuru->groupBy('gtk_id') as $gtkId => $absenGuru)
                        @php
                            $guru   = $absenGuru->first()->gtk;
                            $masuk  = $absenGuru->firstWhere('jenis', 'masuk');
                            $pulang = $absenGuru->firstWhere('jenis', 'pulang');
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $guru->nama_lengkap ?? '-' }}</div>
                                <div style="font-size:.7rem; color:var(--text-muted);">{{ $guru->kd_guru ?? '' }}</div>
                            </td>
                            <td>
                                @if ($masuk)
                                    <span class="jenis-badge badge-masuk">{{ $masuk->waktu_scan->format('H:i') }}</span>
                                @else
                                    <span style="color:#94a3b8; font-size:.75rem;">–</span>
                                @endif
                            </td>
                            <td>
                                @if ($pulang)
                                    <span class="jenis-badge badge-pulang">{{ $pulang->waktu_scan->format('H:i') }}</span>
                                @else
                                    <span style="color:#94a3b8; font-size:.75rem;">–</span>
                                @endif
                            </td>
                            <td>
                                <span class="jenis-badge badge-hadir">Hadir</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center; color:var(--text-muted); padding:20px;">
                                Belum ada guru yang melakukan scan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ACTION BAR --}}
<div class="action-bar">
    <a href="{{ route('event.show', $event) }}" class="ab-btn ab-btn-back">
        <i class="fas fa-arrow-left"></i>
    </a>
    @if ($event->isActive())
        @if ($isGuru)
            @php
                $myAbsenAll   = $absenByGuru->get($myGtkId, collect());
                $sudahMasuk   = $myAbsenAll->where('jenis', 'masuk')->isNotEmpty();
                $jenisNext    = $sudahMasuk && $event->ada_absen_pulang ? 'pulang' : 'masuk';
                $labelNext    = $jenisNext === 'masuk' ? 'Scan Masuk' : 'Scan Pulang';
                $sudahPulang  = $myAbsenAll->where('jenis', 'pulang')->isNotEmpty();
                $selesaiAbsen = ($sudahMasuk && !$event->ada_absen_pulang) || $sudahPulang;
            @endphp
            @if (!$selesaiAbsen)
                <a href="{{ route('event-guru.scan', ['eventGuru' => $event, 'jenis' => $jenisNext]) }}" class="ab-btn ab-btn-primary">
                    <i class="fas fa-qrcode"></i> {{ $labelNext }}
                </a>
            @endif
        @else
            <a href="{{ route('event-guru.scan', ['eventGuru' => $event, 'jenis' => 'masuk']) }}" class="ab-btn ab-btn-primary">
                <i class="fas fa-qrcode"></i> Scan Absen
            </a>
        @endif
    @endif
</div>
@endsection
