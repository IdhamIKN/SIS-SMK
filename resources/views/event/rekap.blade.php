@extends('layouts.app')
@section('title', 'Rekap - ' . $event->nama_event)
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
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 14px
        }

        @media(min-width:480px) {
            .stat-grid {
                grid-template-columns: repeat(4, 1fr)
            }
        }

        @media(min-width:768px) {
            .stat-grid {
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
            color: #0f172a
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

        .badge-masuk {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700
        }

        .badge-pulang {
            background: #ffedd5;
            color: #c2410c;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700
        }

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
            white-space: nowrap
        }

        .btn-scan {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #86efac
        }

        .action-bar {
            position: fixed;
            bottom: var(--footer-h, 0);
            left: 0;
            right: 0;
            padding: 10px 12px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 8px;
            z-index: 999;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06)
        }

        @media(min-width:768px) {
            .action-bar {
                padding: 10px 24px 12px;
                gap: 12px;
                justify-content: flex-end
            }
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: .82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            line-height: 1;
            white-space: nowrap
        }

        @media(min-width:768px) {
            .ab-btn {
                flex: unset;
                min-width: 120px;
                font-size: .875rem;
                padding: 12px 18px
            }
        }

        .ab-btn:active {
            transform: scale(.97)
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0
        }

        .ab-btn-primary {
            background: #f59e0b;
            color: #fff;
            box-shadow: 0 3px 12px rgba(245, 158, 11, .3)
        }

        .ab-btn-green {
            background: #16a34a;
            color: #fff;
            box-shadow: 0 3px 12px rgba(22, 163, 74, .3)
        }

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
    </style>
@endpush

@section('content')
    @php
        $isSiswa = auth()->user()->hasRole('siswa');
        $siswaId = $isSiswa ? auth()->user()->siswa->id ?? null : null;

        $masukQ = $event->absenEvent()->whereNotNull('waktu_masuk');
        $pulangQ = $event->absenEvent()->whereNotNull('waktu_pulang');
        $totalQ = $event->absenEvent();
        $unikQ = $event->absenEvent();

        if ($isSiswa && $siswaId) {
            $masukQ->where('siswa_id', $siswaId);
            $pulangQ->where('siswa_id', $siswaId);
            $totalQ->where('siswa_id', $siswaId);
            $unikQ->where('siswa_id', $siswaId);
        }

        $masukCount = $masukQ->count();
        $pulangCount = $pulangQ->count();
        $totalScan = $totalQ->count();
        $uniqueSiswa = $unikQ->distinct('siswa_id')->count();

        $absenQuery = $event->absenEvent()->with('siswa.kelas')->orderBy('created_at');
        if ($isSiswa && $siswaId) {
            $absenQuery->where('siswa_id', $siswaId);
        }
        $absenList = $absenQuery->get();
    @endphp

    <div class="ev-wrap" style="padding-top:var(--header-h,56px);padding-bottom:calc(var(--footer-h,0px) + 88px)">

        {{-- Page Strip --}}
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>Rekap Absensi</div>
            <h2><i class="fas fa-table"></i> {{ Str::limit($event->nama_event, 30) }}</h2>
            <p>{{ $event->tanggal_mulai->format('d M Y') }} &bull;
                {{ $event->tanggal_mulai->format('H:i') }}&ndash;{{ $event->tanggal_selesai->format('H:i') }}</p>
        </div>

        {{-- Stats --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value" style="color:#0ea5e9">{{ $totalScan }}</div>
                <div class="stat-label">Total Scan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#16a34a">{{ $masukCount }}</div>
                <div class="stat-label">Masuk</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#f59e0b">{{ $pulangCount }}</div>
                <div class="stat-label">Pulang</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#8b5cf6">{{ $uniqueSiswa }}</div>
                <div class="stat-label">{{ $isSiswa ? 'Status' : 'Siswa Unik' }}</div>
            </div>
        </div>

        {{-- Export (non-siswa only) --}}
        @if (!$isSiswa)
            <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:12px">
                <div class="c-head">
                    <div class="c-icon" style="background:#dcfce7;color:#15803d;flex-shrink:0">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <h3>Export &amp; Cetak</h3>
                </div>
                <div class="c-body" style="padding:12px 16px;display:flex;gap:8px;flex-wrap:wrap">
                    <a href="{{ route('event.export', $event) }}" class="action-btn btn-scan">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                    <a href="{{ route('event.jurnal', $event) }}" target="_blank" rel="noopener"
                        class="action-btn btn-scan">
                        <i class="fas fa-print"></i> Cetak Jurnal PDF
                    </a>
                    <a href="{{ route('event.jurnal', ['event' => $event, 'preview' => 1]) }}" target="_blank"
                        rel="noopener" class="action-btn" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe">
                        <i class="fas fa-eye"></i> Preview Jurnal
                    </a>
                </div>
            </div>
        @endif

        {{-- Data Card --}}
        <div class="card" style="border-radius:12px;overflow:hidden">
            <div class="c-head">
                <div class="c-icon" style="background:#fef3c7;flex-shrink:0">
                    <i class="fas fa-list"></i>
                </div>
                <h3>{{ $isSiswa ? 'Absen Saya' : 'Daftar Absen Detail' }}</h3>
                <span class="hbadge">{{ $absenList->count() }} data</span>
            </div>

            @if ($absenList->count() > 0)

                {{-- TABLE desktop --}}
                <div class="ev-table-wrap">
                    <table class="ev-table">
                        <thead>
                            <tr>
                                <th style="width:28px">No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Absen Masuk</th>
                                <th>Absen Pulang</th>
                                <th>WA Ortu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($absenList as $i => $absen)
                                @php
                                    $wM = $absen->waktu_masuk ?? $absen->waktu_scan;
                                    $wP = $absen->waktu_pulang;
                                @endphp
                                <tr>
                                    <td style="color:#94a3b8;font-size:.72rem">{{ $i + 1 }}</td>
                                    <td style="font-size:.75rem">{{ $absen->siswa->nis ?? '-' }}</td>
                                    <td style="font-weight:600">{{ Str::limit($absen->siswa->nama_lengkap ?? '-', 22) }}
                                    </td>
                                    <td style="font-size:.75rem">{{ $absen->siswa->kelas->nama_kelas ?? '-' }}</td>
                                    <td>
                                        @if ($wM)
                                            <span class="badge-masuk">
                                                <i class="fas fa-sign-in-alt" style="font-size:.6rem"></i>
                                                {{ $wM->format('H:i') }}
                                            </span>
                                        @else
                                            <span style="color:#94a3b8;font-size:.75rem">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($wP)
                                            <span class="badge-pulang">
                                                <i class="fas fa-sign-out-alt" style="font-size:.6rem"></i>
                                                {{ $wP->format('H:i') }}
                                            </span>
                                        @else
                                            <span style="color:#94a3b8;font-size:.75rem">Belum pulang</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($absen->wa_terkirim_ortu)
                                            <span style="color:#16a34a;font-size:.75rem;font-weight:600">
                                                <i class="fas fa-check-circle"></i> Ya
                                            </span>
                                        @else
                                            <span style="color:#94a3b8;font-size:.75rem">
                                                <i class="fas fa-minus-circle"></i> Belum
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- CARD LIST mobile --}}
                <div class="ev-card-list">
                    @foreach ($absenList as $absen)
                        @php
                            $wM = $absen->waktu_masuk ?? $absen->waktu_scan;
                            $wP = $absen->waktu_pulang;
                        @endphp
                        <div class="evi">
                            <div class="evi-top">
                                <span class="evi-name">{{ $absen->siswa->nama_lengkap ?? '-' }}</span>
                                <span style="font-size:.7rem;color:#64748b">{{ $absen->siswa->nis ?? '-' }}</span>
                            </div>
                            <div class="evi-mid">
                                @if ($absen->siswa->kelas)
                                    <span class="evi-chip">{{ $absen->siswa->kelas->nama_kelas }}</span>
                                @endif
                                @if ($wM)
                                    <span class="badge-masuk">
                                        <i class="fas fa-sign-in-alt" style="font-size:.6rem"></i>
                                        {{ $wM->format('H:i') }}
                                    </span>
                                @endif
                                @if ($wP)
                                    <span class="badge-pulang">
                                        <i class="fas fa-sign-out-alt" style="font-size:.6rem"></i>
                                        {{ $wP->format('H:i') }}
                                    </span>
                                @endif
                            </div>
                            <div class="evi-bot">
                                <span style="font-size:.7rem;color:#64748b">
                                    @if ($absen->wa_terkirim_ortu)
                                        <i class="fas fa-check-circle" style="color:#16a34a"></i> WA terkirim
                                    @else
                                        <i class="fas fa-minus-circle" style="color:#94a3b8"></i> WA belum terkirim
                                    @endif
                                </span>
                                @if (!$wM && !$wP)
                                    <span style="font-size:.7rem;color:#94a3b8">Belum scan</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="ev-empty">
                    <i class="fas fa-inbox"></i>
                    <strong>Belum ada data absen</strong>
                    {{ $isSiswa ? 'Anda belum melakukan absen untuk event ini.' : 'Data absensi akan muncul setelah siswa scan barcode.' }}
                </div>
            @endif

        </div>{{-- .card --}}

    </div>{{-- .ev-wrap --}}

    {{-- Action Bar --}}
    <div class="action-bar">
        <a href="{{ route('event.show', $event) }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        @if (!$isSiswa)
            <a href="{{ route('event.export', $event) }}" class="ab-btn ab-btn-green">
                <i class="fas fa-file-excel"></i> Export
            </a>
            <a href="{{ route('event.jurnal', $event) }}" target="_blank" rel="noopener" class="ab-btn ab-btn-primary">
                <i class="fas fa-print"></i> Jurnal
            </a>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var h = document.querySelector('.header-auto-show');
            if (h) h.classList.add('header-active');
        });
    </script>
@endpush
