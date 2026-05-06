@extends('layouts.app')

@section('title', 'Rekap - ' . $event->nama_event)

@push('styles')
    @include('components.event-styles')
    <style>
        .rekap-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            font-size: .78rem;
            color: var(--text-muted);
            margin-bottom: 14px;
        }

        .rekap-meta span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 3px 10px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 14px;
        }

        .stat-card {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 14px;
            padding: 14px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: .68rem;
            color: var(--text-muted, #64748b);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
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
            font-size: .7rem;
            font-weight: 700;
            color: var(--text-muted, #64748b);
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .event-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .event-table tbody tr:last-child td {
            border-bottom: none;
        }

        .event-table tbody tr:hover td {
            background: #fafbfc;
        }

        .badge-masuk {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-pulang {
            background: #ffedd5;
            color: #c2410c;
        }

        .jenis-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 13px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            white-space: nowrap;
        }

        .btn-scan {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .btn-scan:hover {
            background: #dcfce7;
        }

        .action-bar {
            position: fixed;
            bottom: var(--footer-h);
            left: 0;
            right: 0;
            padding: 10px 16px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            z-index: 999;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: .875rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            line-height: 1;
        }

        .ab-btn:active {
            transform: scale(.97);
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .ab-btn-back:hover {
            background: #e2e8f0;
        }

        .ab-btn-primary {
            background: var(--event-primary, #f59e0b);
            color: #fff;
            box-shadow: 0 3px 12px rgba(245, 158, 11, .3);
        }

        .ab-btn-primary:hover {
            filter: brightness(1.07);
        }
    </style>
@endpush

@section('content')
    @php
        $isSiswa = auth()->user()->hasRole('siswa');
        $siswaId = $isSiswa ? auth()->user()->siswa->id ?? null : null;

        $totalScanQ = $event->absenEvent();
        $masukQ = $event->absenEvent()->where('jenis', 'masuk');
        $pulangQ = $event->absenEvent()->where('jenis', 'pulang');
        $uniqueQ = $event->absenEvent();

        if ($isSiswa && $siswaId) {
            $totalScanQ->where('siswa_id', $siswaId);
            $masukQ->where('siswa_id', $siswaId);
            $pulangQ->where('siswa_id', $siswaId);
            $uniqueQ->where('siswa_id', $siswaId);
        }

        $totalScan = $totalScanQ->count();
        $masukCount = $masukQ->count();
        $pulangCount = $pulangQ->count();
        $uniqueSiswa = $uniqueQ->distinct('siswa_id')->count();

        $absenQuery = $event->absenEvent()->with('siswa.kelas')->orderBy('jenis')->orderBy('waktu_scan');
        if ($isSiswa && $siswaId) {
            $absenQuery->where('siswa_id', $siswaId);
        }
        $absenList = $absenQuery->get();
    @endphp

    <div class="event-wrap" style="padding-bottom: calc(var(--footer-h) + 88px);">

        {{-- Page Strip --}}
        <div class="page-strip page-strip-event">
            <div class="live-badge">
                <span class="live-dot"></span>
                Rekap Absensi
            </div>
            <h2>
                <i class="fas fa-table"></i>
                {{ Str::limit($event->nama_event, 30) }}
            </h2>
            <p>Daftar detail kehadiran siswa</p>
        </div>

        {{-- Meta --}}
        <div class="rekap-meta">
            <span><i class="fas fa-calendar-day"></i> {{ $event->tanggal_mulai->format('d M Y') }}</span>
            <span><i class="fas fa-clock"></i> {{ $event->tanggal_mulai->format('H:i') }} -
                {{ $event->tanggal_selesai->format('H:i') }}</span>
            <span><i class="fas fa-map-marker-alt"></i> {{ $event->lokasi ?? 'Tanpa lokasi' }}</span>
        </div>

        {{-- Stat Grid --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value" style="color:#0ea5e9;">{{ $totalScan }}</div>
                <div class="stat-label">Total Scan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#16a34a;">{{ $masukCount }}</div>
                <div class="stat-label">Absen Masuk</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#f59e0b;">{{ $pulangCount }}</div>
                <div class="stat-label">Absen Pulang</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#8b5cf6;">{{ $uniqueSiswa }}</div>
                <div class="stat-label">{{ $isSiswa ? 'Status Saya' : 'Siswa Unik' }}</div>
            </div>
        </div>

        {{-- Export Card --}}
        @if (!$isSiswa)
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#dcfce7; color:#15803d;"><i class="fas fa-file-export"></i></div>
                    <h3>Export Data</h3>
                </div>
                <div class="c-body" style="padding:14px 16px;">
                    <p style="color:var(--text-muted);font-size:.84rem;line-height:1.55;margin:0 0 12px;">
                        Unduh rekap absensi dalam format Excel untuk keperluan arsip atau laporan.
                    </p>
                    <a href="{{ route('event.export', $event) }}" class="action-btn btn-scan">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                </div>
            </div>
        @endif

        {{-- Table Card --}}
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:#fef3c7;"><i class="fas fa-list"></i></div>
                <h3>{{ $isSiswa ? 'Absen Saya' : 'Daftar Absen Detail' }}</h3>
                <span class="hbadge">{{ $absenList->count() }} data</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="event-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jenis</th>
                            <th>Waktu Scan</th>
                            <th>WA Ortu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($absenList as $i => $absen)
                            <tr>
                                <td style="color:var(--text-muted);font-size:.72rem;">{{ $i + 1 }}</td>
                                <td style="font-size:.75rem;">{{ $absen->siswa->nis ?? '-' }}</td>
                                <td style="font-weight:600;">{{ Str::limit($absen->siswa->nama_lengkap ?? '-', 20) }}</td>
                                <td style="font-size:.75rem;">{{ $absen->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>
                                    <span
                                        class="jenis-badge {{ $absen->jenis === 'masuk' ? 'badge-masuk' : 'badge-pulang' }}">
                                        {{ $absen->jenis === 'masuk' ? 'Masuk' : 'Pulang' }}
                                    </span>
                                </td>
                                <td style="font-size:.75rem; white-space:nowrap;">
                                    {{ $absen->waktu_scan->format('d M Y H:i') }}</td>
                                <td>
                                    @if ($absen->wa_terkirim_ortu)
                                        <span style="color:#16a34a; font-size:.75rem; font-weight:600;">
                                            <i class="fas fa-check-circle"></i> Ya
                                        </span>
                                    @else
                                        <span style="color:var(--text-muted); font-size:.75rem;">
                                            <i class="fas fa-minus-circle"></i> Belum
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"
                                    style="text-align:center; padding:28px 20px; color:var(--text-muted); font-size:.85rem;">
                                    <div style="font-size:2.5rem; margin-bottom:8px; color:var(--event-fade);"><i
                                            class="fas fa-inbox"></i></div>
                                    <strong style="color:var(--text-main); display:block; margin-bottom:4px;">Belum ada data
                                        absen</strong>
                                    {{ $isSiswa ? 'Anda belum melakukan absen untuk event ini.' : 'Data absensi akan muncul setelah siswa melakukan scan barcode.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Action Bar --}}
    <div class="action-bar">
        <a href="{{ route('event.show', $event) }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        @if (!$isSiswa)
            <a href="{{ route('event.export', $event) }}" class="ab-btn ab-btn-primary">
                <i class="fas fa-file-excel"></i> Export
            </a>
        @endif
    </div>
@endsection
