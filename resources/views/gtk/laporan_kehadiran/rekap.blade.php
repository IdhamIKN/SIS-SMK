@extends('layouts.app')

@section('title', 'Rekap Laporan Kehadiran Guru')

@push('styles')
    @include('components.event-styles')
    <style>
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

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .status-hijau  { background: #E8F5E8; color: #2E7D32; }
        .status-kuning { background: #FFFDE7; color: #F57C00; }
        .status-merah  { background: #FFEBEE; color: #C62828; }
        .status-abu    { background: #F5F5F9; color: #424242; }
        .status-biru   { background: #E3F2FD; color: #1565C0; }
        .status-pink   { background: #FCE4EC; color: #AD1457; }
        .status-orange { background: #FFF3E0; color: #E65100; animation: pulse 2s infinite; }
        .status-putih  { background: #FFFFFF; color: #616161; border: 1px solid #E0E0E0; }

        .filter-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
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
            font-size: 1.6rem;
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
            Rekap Laporan Kehadiran
        </div>
        <h2>
            <i class="fas fa-chart-bar"></i>
            Rekap Kehadiran Guru
        </h2>
        <p>Periode {{ \Carbon\Carbon::parse($tanggalMulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tanggalSelesai)->format('d M Y') }}</p>
    </div>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value" style="color:#0ea5e9;">{{ $stats['total_laporan'] }}</div>
            <div class="stat-label">Total Laporan</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#16a34a;">{{ $stats['hijau'] }}</div>
            <div class="stat-label">Hadir Tepat</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#f59e0b;">{{ $stats['kuning'] }}</div>
            <div class="stat-label">Terlambat</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#dc2626;">{{ $stats['merah'] }}</div>
            <div class="stat-label">Tidak Hadir</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#8b5cf6;">{{ $stats['abu'] }}</div>
            <div class="stat-label">Ada Tugas</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#ec4899;">{{ $stats['orange'] }}</div>
            <div class="stat-label">Belum Lapor</div>
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
            @if (!auth()->user()->hasRole('gtk'))
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
                <label class="form-label">Guru</label>
                <select name="gtk_id" class="form-input">
                    <option value="">Semua Guru</option>
                    @foreach ($gtkList as $g)
                        <option value="{{ $g->id }}" {{ $gtkId == $g->id ? 'selected' : '' }}>
                            {{ $g->nama_lengkap }}
                        </option>
                    @endforeach
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
            <h3>Detail Rekap Kehadiran</h3>
            <span class="hbadge">{{ $rekapData->count() }} laporan</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="rekap-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Kelas</th>
                        <th>Guru</th>
                        <th>Mata Pelajaran</th>
                        <th>Status</th>
                        <th>Pelapor</th>
                        <th>Waktu Lapor</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekapData as $laporan)
                        <tr>
                            <td style="font-weight:600;">
                                {{ \Carbon\Carbon::parse($laporan->tanggal)->format('d/m/Y') }}
                            </td>
                            <td style="font-size:.75rem; white-space:nowrap;">
                                Jam {{ $laporan->jam_ke }}
                            </td>
                            <td style="font-weight:600;">{{ $laporan->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ $laporan->gtk->nama_lengkap ?? '-' }}</td>
                            <td style="font-size:.75rem;">
                                {{ $laporan->jadwalKbm->mata_pelajaran ?? '-' }}
                            </td>
                            <td>
                                <span class="status-badge status-{{ $laporan->status }}">
                                    <div style="width:8px; height:8px; border-radius:50%; background:{{ $laporan->status_color }}"></div>
                                    {{ $laporan->status_label }}
                                </span>
                            </td>
                            <td>
                                @if ($laporan->dilaporkan_oleh_siswa_id)
                                    <i class="fas fa-user-graduate"></i> Siswa
                                @else
                                    <i class="fas fa-user-tie"></i> Guru
                                @endif
                            </td>
                            <td style="font-size:.75rem; white-space:nowrap;">
                                {{ $laporan->waktu_laporan->format('d/m H:i') }}
                            </td>
                            <td style="font-size:.75rem;">
                                {{ $laporan->catatan ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:28px 20px; color:var(--text-muted); font-size:.85rem;">
                                <div style="font-size:2.5rem; margin-bottom:8px; color:var(--event-fade);"><i class="fas fa-inbox"></i></div>
                                <strong style="color:var(--text-main); display:block; margin-bottom:4px;">Belum ada data</strong>
                                Tidak ada laporan kehadiran untuk periode yang dipilih.
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
    <a href="{{ route('kehadiran-guru.laporan') }}" class="ab-btn ab-btn-back">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>
@endsection