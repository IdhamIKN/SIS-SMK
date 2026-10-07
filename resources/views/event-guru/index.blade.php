@extends('layouts.app')
@section('title', 'Event Guru')
@push('styles')
    @include('components.event-styles')
    <style>
        .ev-wrap {
            padding: 0 12px;
            max-width: 1280px;
            margin: 0 auto;
            box-sizing: border-box
        }

        @media(min-width:768px) {
            .ev-wrap {
                padding: 0 20px
            }
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 14px
        }

        @media(min-width:768px) {
            .stat-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
                margin-bottom: 18px
            }
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 8px;
            text-align: center
        }

        @media(min-width:768px) {
            .stat-card {
                border-radius: 12px;
                padding: 16px
            }
        }

        .stat-value {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 3px
        }

        @media(min-width:768px) {
            .stat-value {
                font-size: 1.8rem
            }
        }

        .stat-label {
            font-size: .6rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em
        }

        /* filter */
        .filter-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 16px;
            overflow: hidden
        }

        .filter-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            cursor: pointer;
            user-select: none;
            background: #f1f5f9;
            border: none;
            width: 100%;
            font-family: inherit;
            font-size: .875rem;
            font-weight: 700;
            color: #0f172a;
            gap: 8px
        }

        .filter-toggle .ft-left {
            display: flex;
            align-items: center;
            gap: 8px
        }

        .filter-toggle .ft-chevron {
            transition: transform .2s;
            color: #64748b;
            font-size: .8rem
        }

        .filter-toggle.open .ft-chevron {
            transform: rotate(180deg)
        }

        @media(min-width:768px) {
            .filter-toggle {
                display: none
            }
        }

        .filter-body {
            padding: 12px 16px 16px;
            display: none
        }

        .filter-body.open {
            display: block
        }

        @media(min-width:768px) {
            .filter-body {
                display: block !important;
                padding: 16px
            }
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            align-items: end
        }

        @media(max-width:479px) {
            .filter-grid {
                grid-template-columns: 1fr
            }
        }

        @media(min-width:768px) {
            .filter-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 12px
            }
        }

        .form-label-f {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 5px
        }

        .form-input-f {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            background: #fff;
            box-sizing: border-box;
            -webkit-appearance: none
        }

        .form-input-f:focus {
            outline: 2px solid #4338ca;
            outline-offset: -1px
        }

        /* tab */
        .tab-filter {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 14px
        }

        .tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            border: 1px solid #dee2e6;
            background: #fff;
            color: #64748b;
            text-decoration: none;
            transition: all .15s;
            white-space: nowrap
        }

        .tab-btn:hover {
            background: #f1f5f9;
            color: #334155
        }

        .tab-btn.active {
            background: #4338ca;
            color: #fff;
            border-color: #4338ca
        }

        .tab-btn .badge-count {
            background: rgba(255, 255, 255, .25);
            border-radius: 10px;
            padding: 1px 6px;
            font-size: .68rem
        }

        .tab-btn:not(.active) .badge-count {
            background: #e2e8f0;
            color: #475569
        }

        /* table */
        .ev-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch
        }

        @media(max-width:767px) {
            .ev-table-wrap {
                display: none
            }
        }

        .ev-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .78rem;
            background: #fff
        }

        .ev-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0
        }

        .ev-table th {
            padding: 11px 10px;
            text-align: left;
            font-size: .68rem;
            font-weight: 700;
            color: #64748b;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: .04em
        }

        .ev-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle
        }

        .ev-table tbody tr:last-child td {
            border-bottom: none
        }

        .ev-table tbody tr:hover td {
            background: #fafbfc
        }

        /* card list mobile */
        .ev-card-list {
            display: none
        }

        @media(max-width:767px) {
            .ev-card-list {
                display: flex;
                flex-direction: column;
                gap: 0
            }
        }

        .evi {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            background: #fff
        }

        .evi:last-child {
            border-bottom: none
        }

        .evi-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 4px
        }

        .evi-name {
            font-weight: 700;
            font-size: .88rem;
            color: #0f172a;
            flex: 1;
            min-width: 0
        }

        .evi-mid {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 6px
        }

        .evi-chip {
            font-size: .7rem;
            color: #64748b;
            background: #f1f5f9;
            border-radius: 5px;
            padding: 2px 7px;
            font-weight: 600
        }

        .evi-bot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px
        }

        /* badges */
        .badge-ev-active {
            background: #dcfce7;
            color: #15803d;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700
        }

        .badge-ev-upcoming {
            background: #fef9c3;
            color: #a16207;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700
        }

        .badge-ev-ended {
            background: #f1f5f9;
            color: #64748b;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700
        }

        .tag-masuk {
            background: #dcfce7;
            color: #15803d;
            padding: 2px 7px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700
        }

        .tag-pulang {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 2px 7px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700
        }

        /* action */
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 7px;
            font-size: .72rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            white-space: nowrap;
            transition: opacity .15s
        }

        .btn-view {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe
        }

        .btn-scan {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #86efac
        }

        .btn-edit {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a
        }

        /* pagination */
        .ev-pagination {
            padding: 12px 14px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 5px
        }

        .pg-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 700;
            text-decoration: none;
            font-family: inherit;
            border: 1px solid #e2e8f0;
            background: #f1f5f9;
            color: #475569;
            cursor: pointer;
            white-space: nowrap
        }

        .pg-btn:hover {
            background: #e2e8f0
        }

        .pg-btn.active {
            background: #4338ca;
            color: #fff;
            border-color: transparent;
            pointer-events: none
        }

        .pg-btn.disabled {
            opacity: .4;
            cursor: not-allowed;
            pointer-events: none
        }

        @media(max-width:479px) {
            .pg-num {
                display: none
            }

            .pg-num.active {
                display: inline-flex
            }
        }

        /* empty */
        .ev-empty {
            text-align: center;
            padding: 36px 20px;
            color: #64748b
        }

        .ev-empty i {
            font-size: 2.5rem;
            opacity: .3;
            display: block;
            margin-bottom: 10px
        }

        .ev-empty strong {
            display: block;
            color: #0f172a;
            margin-bottom: 4px;
            font-size: .9rem
        }

        /* fab */
        .fab-add-guru {
            position: fixed;
            bottom: calc(var(--footer-h, 0px) + 16px);
            right: 16px;
            z-index: 998;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 18px;
            background: #4338ca;
            color: #fff;
            border-radius: 30px;
            font-size: .84rem;
            font-weight: 700;
            box-shadow: 0 6px 20px rgba(67, 56, 202, .4);
            text-decoration: none
        }

        .ev-group-label {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #64748b;
            padding: 10px 4px 4px
        }
    </style>
@endpush

@section('content')
    @php
        $isAdmin = auth()
            ->user()
            ->hasAnyRole(['superadmin', 'admin_tatib', 'bk', 'TU']);
        $isGuru = auth()->user()->hasRole('gtk');
        $myGtkId = $isGuru ? auth()->user()->gtk->id ?? null : null;
        $now = now();
        $aktif = $events->filter(fn($e) => $e->tanggal_mulai <= $now && $e->tanggal_selesai >= $now);
        $upcoming = $events->filter(fn($e) => $e->tanggal_mulai > $now);
        $riwayat = $events->filter(fn($e) => $e->tanggal_selesai < $now);
        $hasFilter = request()->filled('search') || request()->filled('tanggal');
    @endphp

    <div class="ev-wrap" style="padding-top:var(--header-h,56px);padding-bottom:calc(var(--footer-h,0px) + 88px)">

        {{-- Page Strip --}}
        <div class="page-strip" style="background:linear-gradient(135deg,#1e40af 0%,#4338ca 100%)">
            <div class="live-badge"><span class="live-dot"></span>{{ now()->translatedFormat('l, d F Y') }}</div>
            <h2><i class="fas fa-chalkboard-teacher"></i> Event Guru</h2>
            <p>Kegiatan &amp; absensi barcode khusus guru</p>
        </div>

        @if (session('success'))
            <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert a-err"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        {{-- Stats --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value" style="color:#4338ca">{{ $events->total() }}</div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#16a34a">{{ $totalActive }}</div>
                <div class="stat-label">Aktif</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#a16207">{{ $upcoming->count() }}</div>
                <div class="stat-label">Akan Datang</div>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('event-guru.index') }}" class="filter-section" id="evFilterForm">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <button type="button" class="filter-toggle {{ $hasFilter ? 'open' : '' }}" id="evFilterToggle"
                onclick="toggleEvFilter()">
                <span class="ft-left"><i class="fas fa-filter"></i> Filter
                    @if ($hasFilter)
                        <span
                            style="background:#fef3c7;color:#b45309;font-size:.65rem;padding:2px 8px;border-radius:20px;font-weight:700">Aktif</span>
                    @endif
                </span>
                <i class="fas fa-chevron-down ft-chevron"></i>
            </button>
            <div class="filter-body {{ $hasFilter ? 'open' : '' }}" id="evFilterBody">
                <div class="filter-grid">
                    <div>
                        <label class="form-label-f">Nama Event</label>
                        <input type="text" name="search" class="form-input-f" placeholder="Cari nama event guru..."
                            value="{{ request('search') }}">
                    </div>
                    <div>
                        <label class="form-label-f">Tanggal</label>
                        <input type="date" name="tanggal" class="form-input-f" value="{{ request('tanggal') }}">
                    </div>
                    <div style="display:flex;gap:8px;align-items:flex-end;padding-top:4px">
                        <button type="submit" class="action-btn btn-view"
                            style="flex:1;justify-content:center;padding:10px"><i class="fas fa-filter"></i>
                            Terapkan</button>
                        @if ($hasFilter)
                            <a href="{{ route('event-guru.index', ['tab' => $tab]) }}" class="action-btn"
                                style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:10px 14px"><i
                                    class="fas fa-times"></i></a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- Tab Filter --}}
        @php $baseParams = array_filter(['search'=>request('search'),'tanggal'=>request('tanggal')]); @endphp
        <div class="tab-filter">
            <a href="{{ route('event-guru.index', array_merge($baseParams, ['tab' => 'semua'])) }}"
                class="tab-btn {{ $tab === 'semua' ? 'active' : '' }}">
                <i class="fas fa-list"></i> Semua <span class="badge-count">{{ $events->total() }}</span>
            </a>
            <a href="{{ route('event-guru.index', array_merge($baseParams, ['tab' => 'aktif'])) }}"
                class="tab-btn {{ $tab === 'aktif' ? 'active' : '' }}">
                <i class="fas fa-circle" style="font-size:.5rem"></i> Aktif <span
                    class="badge-count">{{ $totalActive }}</span>
            </a>
            <a href="{{ route('event-guru.index', array_merge($baseParams, ['tab' => 'upcoming'])) }}"
                class="tab-btn {{ $tab === 'upcoming' ? 'active' : '' }}">
                <i class="fas fa-clock"></i> Akan Datang
            </a>
            <a href="{{ route('event-guru.index', array_merge($baseParams, ['tab' => 'riwayat'])) }}"
                class="tab-btn {{ $tab === 'riwayat' ? 'active' : '' }}">
                <i class="fas fa-history"></i> Riwayat
            </a>
        </div>

        {{-- Data Card --}}
        <div class="card" style="border-radius:12px;overflow:hidden">
            <div class="c-head">
                <div class="c-icon" style="background:#e0e7ff;color:#4338ca;flex-shrink:0"><i
                        class="fas fa-chalkboard-teacher"></i></div>
                <h3>Daftar Event Guru</h3>
                <span class="hbadge">{{ $events->total() }} event</span>
            </div>

            @if ($events->count() > 0)
                {{-- TABLE desktop --}}
                <div class="ev-table-wrap">
                    <table class="ev-table">
                        <thead>
                            <tr>
                                <th>Nama Event</th>
                                <th>Waktu</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th>Scan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rows = $tab==='semua' ? $aktif->merge($upcoming)->merge($riwayat) : $events->getCollection(); @endphp
                            @forelse($rows as $ev)
                                @php
                                    $evActive = $ev->tanggal_mulai <= $now && $ev->tanggal_selesai >= $now;
                                    $evUpcoming = $ev->tanggal_mulai > $now;
                                    $myAbsen = $myGtkId ? $ev->absenEventGuru->where('gtk_id', $myGtkId) : collect();
                                    $myMasuk = $myAbsen->where('jenis', 'masuk')->isNotEmpty();
                                    $myPulang = $myAbsen->where('jenis', 'pulang')->isNotEmpty();
                                    $doneGuru = ($myMasuk && !$ev->ada_absen_pulang) || $myPulang;
                                    $nextJ = $myMasuk && $ev->ada_absen_pulang ? 'pulang' : 'masuk';
                                    $showSc = $isGuru && $evActive && !$doneGuru;
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight:700;font-size:.83rem">{{ Str::limit($ev->nama_event, 36) }}
                                        </div>
                                        @if ($ev->lokasi)
                                            <div style="font-size:.68rem;color:#64748b;margin-top:2px"><i
                                                    class="fas fa-map-marker-alt" style="font-size:.6rem"></i>
                                                {{ Str::limit($ev->lokasi, 24) }}</div>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap;font-size:.78rem">
                                        <div style="font-weight:600">{{ $ev->tanggal_mulai->format('d M Y') }}</div>
                                        <div style="color:#64748b">
                                            {{ $ev->tanggal_mulai->format('H:i') }}–{{ $ev->tanggal_selesai->format('H:i') }}
                                        </div>
                                    </td>
                                    <td style="white-space:nowrap;font-size:.75rem">
                                        @if ($ev->ada_absen_masuk)
                                            <span class="tag-masuk"><i class="fas fa-sign-in-alt"
                                                    style="font-size:.6rem"></i> Masuk</span>
                                        @endif
                                        @if ($ev->ada_absen_pulang)
                                            <span class="tag-pulang" style="display:inline-block;margin-top:2px"><i
                                                    class="fas fa-sign-out-alt" style="font-size:.6rem"></i> Pulang</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($evActive)
                                            <span class="badge-ev-active"><i class="fas fa-circle"
                                                    style="font-size:.4rem"></i> Aktif</span>
                                        @elseif($evUpcoming)
                                            <span class="badge-ev-upcoming">Segera</span>
                                        @else<span class="badge-ev-ended">Selesai</span>
                                        @endif
                                    </td>
                                    <td style="font-size:.75rem">
                                        @if ($isGuru)
                                            @if ($myMasuk)
                                                <span style="color:#16a34a"><i
                                                    class="fas fa-check-circle"></i></span>@else<span
                                                    style="color:#94a3b8"><i class="fas fa-times-circle"></i></span>
                                            @endif
                                            @if ($ev->ada_absen_pulang)
                                                / @if ($myPulang)
                                                    <span style="color:#0ea5e9"><i
                                                        class="fas fa-check-circle"></i></span>@else<span
                                                        style="color:#94a3b8"><i class="fas fa-times-circle"></i></span>
                                                @endif
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:5px;flex-wrap:wrap">
                                            <a href="{{ route('event-guru.show', $ev) }}" class="action-btn btn-view"><i
                                                    class="fas fa-eye"></i></a>
                                            @if ($showSc)
                                                <a href="{{ route('event-guru.scan', ['eventGuru' => $ev, 'jenis' => $nextJ]) }}"
                                                    class="action-btn btn-scan"><i class="fas fa-qrcode"></i></a>
                                            @endif
                                            @if ($isAdmin)
                                                <a href="{{ route('event-guru.edit', $ev) }}"
                                                    class="action-btn btn-edit"><i class="fas fa-pen"></i></a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- CARD LIST mobile --}}
                <div class="ev-card-list">
                    @if ($tab === 'semua')
                        @foreach ([['label' => 'Sedang Berlangsung', 'color' => '#16a34a', 'items' => $aktif], ['label' => 'Akan Datang', 'color' => '#a16207', 'items' => $upcoming], ['label' => 'Riwayat', 'color' => '#64748b', 'items' => $riwayat]] as $grp)
                            @if ($grp['items']->count() > 0)
                                <div class="ev-group-label" style="padding-left:14px"><i class="fas fa-circle"
                                        style="color:{{ $grp['color'] }};font-size:.4rem;vertical-align:middle"></i>
                                    {{ $grp['label'] }}</div>
                                @foreach ($grp['items'] as $ev)
                                    @include(
                                        'event-guru._card-row',
                                        compact('ev', 'isAdmin', 'isGuru', 'myGtkId'))
                                @endforeach
                            @endif
                        @endforeach
                    @else
                        @foreach ($events as $ev)
                            @include('event-guru._card-row', compact('ev', 'isAdmin', 'isGuru', 'myGtkId'))
                        @endforeach
                    @endif
                </div>

                {{-- Pagination --}}
                @if ($events->hasPages())
                    @php
                        $cur = $events->currentPage();
                        $last = $events->lastPage();
                        $from = max(1, $cur - 2);
                        $to = min($last, $cur + 2);
                    @endphp
                    <div class="ev-pagination">
                        @if ($events->onFirstPage())
                            <span class="pg-btn disabled"><i class="fas fa-chevron-left"></i> Prev</span>
                        @else
                            <a href="{{ $events->previousPageUrl() }}" class="pg-btn"><i
                                    class="fas fa-chevron-left"></i> Prev</a>
                        @endif

                        @if ($from > 1)
                            <a href="{{ $events->url(1) }}" class="pg-btn pg-num">1</a>
                            @if ($from > 2)
                                <span class="pg-btn disabled pg-num">…</span>
                            @endif
                        @endif

                        @for ($p = $from; $p <= $to; $p++)
                            <a href="{{ $events->url($p) }}"
                                class="pg-btn pg-num {{ $p === $cur ? 'active' : '' }}">{{ $p }}</a>
                        @endfor

                        @if ($to < $last)
                            @if ($to < $last - 1)
                                <span class="pg-btn disabled pg-num">…</span>
                            @endif
                            <a href="{{ $events->url($last) }}" class="pg-btn pg-num">{{ $last }}</a>
                        @endif

                        @if ($events->hasMorePages())
                            <a href="{{ $events->nextPageUrl() }}" class="pg-btn">Next <i
                                    class="fas fa-chevron-right"></i></a>
                        @else
                            <span class="pg-btn disabled">Next <i class="fas fa-chevron-right"></i></span>
                        @endif
                    </div>
                @endif
            @else
                <div class="ev-empty"><i class="fas fa-calendar-plus"></i><strong>Tidak ada event guru</strong>
                    @if ($tab === 'aktif')
                        Tidak ada event yang sedang berlangsung.
                    @elseif($tab === 'upcoming')
                        Tidak ada event yang akan datang.
                    @elseif($tab === 'riwayat')
                        Tidak ada riwayat event.
                    @else
                        Belum ada event guru yang dibuat.
                    @endif
                    @if ($isAdmin)
                        <br><a href="{{ route('event-guru.create') }}" class="action-btn btn-view"
                            style="margin-top:10px"><i class="fas fa-plus"></i> Tambah Event Guru</a>
                    @endif
                </div>
            @endif
        </div>

    </div>

    @if ($isAdmin)
        <a href="{{ route('event-guru.create') }}" class="fab-add-guru"><i class="fas fa-plus"></i> Tambah Event
            Guru</a>
    @endif
@endsection

@push('scripts')
    <script>
        function toggleEvFilter() {
            document.getElementById('evFilterToggle').classList.toggle('open');
            document.getElementById('evFilterBody').classList.toggle('open');
        }
        document.addEventListener('DOMContentLoaded', function() {
            var h = document.querySelector('.header-auto-show');
            if (h) h.classList.add('header-active');
        });
    </script>
@endpush
