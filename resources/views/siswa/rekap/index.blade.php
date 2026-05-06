@extends('layouts.app')

@section('title', 'Rekap Kehadiran')

@push('styles')
    @include('components.event-styles')
    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-label {
            font-size: .7rem;
            color: var(--text-muted, #64748b);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .filter-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            align-items: end;
        }

        .form-label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: var(--text-main, #0f172a);
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 8px;
            font-size: .875rem;
            font-family: inherit;
            color: var(--text-main, #0f172a);
            background: #fff;
        }

        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .78rem;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .rekap-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid var(--border, #e2e8f0);
        }

        .rekap-table th {
            padding: 12px 8px;
            text-align: left;
            font-size: .7rem;
            font-weight: 700;
            color: var(--text-muted, #64748b);
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .rekap-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .rekap-table tbody tr:last-child td {
            border-bottom: none;
        }

        .rekap-table tbody tr:hover td {
            background: #fafbfc;
        }

        .badge-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700;
        }

        .badge-hadir { background: #dcfce7; color: #15803d; }
        .badge-izin { background: #dbeafe; color: #1d4ed8; }
        .badge-sakit { background: #fef3c7; color: #b45309; }
        .badge-alfa { background: #fee2e2; color: #dc2626; }
        .badge-event { background: #ede9fe; color: #7c3aed; }

        .tipe-indicator {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: .7rem;
            font-weight: 600;
        }

        .tipe-absen {
            color: #0ea5e9;
        }

        .tipe-event {
            color: #8b5cf6;
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
<div class="event-wrap" style="padding-bottom: calc(var(--footer-h) + 88px);">

    {{-- Page Strip --}}
    <div class="page-strip page-strip-event">
        <div class="live-badge">
            <span class="live-dot"></span>
            Rekap Kehadiran
        </div>
        <h2>
            <i class="fas fa-chart-line"></i>
            Rekap Absensi & Event
        </h2>
        <p>Data kehadiran siswa periode {{ \Carbon\Carbon::parse($tanggalMulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tanggalSelesai)->format('d M Y') }}</p>
    </div>

    {{-- Stats --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-value" style="color:#0ea5e9;">{{ $stats['total_absen_siswa'] }}</div>
            <div class="stat-label">Absen Sekolah</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#8b5cf6;">{{ $stats['total_absen_event'] }}</div>
            <div class="stat-label">Absen Event</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#16a34a;">{{ $stats['hadir'] }}</div>
            <div class="stat-label">Hadir</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#f59e0b;">{{ $stats['izin'] }}</div>
            <div class="stat-label">Izin</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#b45309;">{{ $stats['sakit'] }}</div>
            <div class="stat-label">Sakit</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#dc2626;">{{ $stats['alfa'] }}</div>
            <div class="stat-label">Alfa</div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="filter-section">
        <div class="filter-grid">
            <div>
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-input" value="{{ $tanggalMulai }}">
            </div>
            <div>
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-input" value="{{ $tanggalSelesai }}">
            </div>
            @if (!$isSiswa)
            <div>
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-input">
                    <option value="">Semua Kelas</option>
                    @foreach ($kelas as $k)
                        <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }} - {{ $k->jurusan->nama_jurusan ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="">Semua Status</option>
                    <option value="hadir" {{ $status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="izin" {{ $status == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ $status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="alfa" {{ $status == 'alfa' ? 'selected' : '' }}>Alfa</option>
                </select>
            </div>
            @endif
            <div>
                <button type="submit" class="action-btn btn-view" style="margin-top: 24px;">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#fef3c7;"><i class="fas fa-list"></i></div>
            <h3>Rekap Detail Kehadiran</h3>
            <span class="hbadge">{{ $rekapUnified->count() }} data</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="rekap-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        @if (!$isSiswa)
                        <th>Lokasi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekapUnified as $rekap)
                        <tr>
                            <td style="font-weight:600;">{{ \Carbon\Carbon::parse($rekap['tanggal'])->format('d/m/Y') }}</td>
                            <td style="font-size:.75rem; white-space:nowrap;">
                                {{ $rekap['waktu']->format('H:i') }}
                            </td>
                            <td style="color:var(--text-muted);font-size:.72rem;">{{ $rekap['siswa']->nis ?? '-' }}</td>
                            <td style="font-weight:600;">{{ Str::limit($rekap['siswa']->nama_lengkap ?? '-', 18) }}</td>
                            <td style="font-size:.75rem;">{{ $rekap['kelas']->nama_kelas ?? '-' }}</td>
                            <td>
                                <span class="tipe-indicator {{ $rekap['tipe'] === 'absen_event' ? 'tipe-event' : 'tipe-absen' }}">
                                    <i class="fas fa-{{ $rekap['tipe'] === 'absen_event' ? 'calendar-day' : 'school' }}"></i>
                                    {{ $rekap['jenis'] === 'masuk' ? 'Masuk' : 'Pulang' }}
                                </span>
                            </td>
                            <td>
                                @if ($rekap['tipe'] === 'absen_event')
                                    <span class="badge-status badge-event">Event</span>
                                @else
                                    <span class="badge-status badge-{{ $rekap['status'] }}">
                                        {{ ucfirst($rekap['status']) }}
                                    </span>
                                @endif
                            </td>
                            <td style="font-size:.75rem;">
                                {{ $rekap['keterangan'] }}
                                @if ($rekap['catatan'])
                                    <br><small style="color:#64748b;">{{ $rekap['catatan'] }}</small>
                                @endif
                            </td>
                            @if (!$isSiswa)
                            <td style="font-size:.75rem;">{{ $rekap['lokasi'] ?? '-' }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isSiswa ? 8 : 9 }}" style="text-align:center; padding:28px 20px; color:var(--text-muted); font-size:.85rem;">
                                <div style="font-size:2.5rem; margin-bottom:8px; color:var(--event-fade);"><i class="fas fa-inbox"></i></div>
                                <strong style="color:var(--text-main); display:block; margin-bottom:4px;">Tidak ada data absen</strong>
                                Belum ada data kehadiran untuk periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Action Bar --}}
@if (!$isSiswa)
<div class="action-bar">
    <a href="{{ route('dashboard') }}" class="ab-btn ab-btn-back">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
    <a href="{{ route('absen.rekap.export', request()->query()) }}" class="ab-btn ab-btn-primary">
        <i class="fas fa-file-excel"></i> Export Excel
    </a>
</div>
@endif
@endsection