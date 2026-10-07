@extends('layouts.app')
@section('title', 'Rekap PKL per Lokasi')

@push('styles')
    @include('components.event-styles')
    <style>
        .pkl-wrap {
            padding: 0 12px;
            max-width: 1100px;
            margin: 0 auto;
        }

        @media(min-width:768px) {
            .pkl-wrap {
                padding: 0 24px;
            }
        }

        .filter-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
        }

        .filter-bar .form-label {
            font-size: .75rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 4px;
            display: block;
        }

        .filter-bar .form-control {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: .82rem;
            background: #fff;
            color: #0f172a;
            min-width: 150px;
        }

        .lokasi-rekap-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .lokasi-rekap-head {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .lokasi-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: #fef3c7;
            color: #b45309;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
            flex-shrink: 0;
        }

        .lokasi-rekap-name {
            font-weight: 800;
            font-size: .95rem;
            color: #0f172a;
        }

        .lokasi-rekap-sub {
            font-size: .75rem;
            color: #64748b;
            margin-top: 2px;
        }

        .stat-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .mini-stat {
            background: #f8fafc;
            border-radius: 8px;
            padding: 8px 14px;
            text-align: center;
            flex: 1;
            min-width: 70px;
        }

        .mini-val {
            font-size: 1.2rem;
            font-weight: 800;
        }

        .mini-lbl {
            font-size: .62rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }

        .pct-bar {
            height: 8px;
            border-radius: 4px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .pct-fill {
            height: 100%;
            border-radius: 4px;
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
        }

        @media(min-width:768px) {
            .action-bar {
                padding: 10px 24px 12px;
                justify-content: flex-end;
            }
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 11px 16px;
            border-radius: 12px;
            font-size: .82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
        }

        @media(min-width:768px) {
            .ab-btn {
                flex: unset;
                min-width: 120px;
            }
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
    </style>
@endpush

@section('content')
    <div class="pkl-wrap" style="padding-top:var(--header-h,56px);padding-bottom:148px;">

        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>PKL</div>
            <h2><i class="fas fa-map-marked-alt"></i> Rekap PKL per Lokasi</h2>
            <p>Ringkasan kehadiran siswa di setiap tempat PKL.</p>
        </div>

        {{-- Filter --}}
        <form method="GET" class="filter-bar">
            <div>
                <label class="form-label">Tahun Ajaran</label>
                <select name="academic_year_id" class="form-control">
                    <option value="">Semua</option>
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ $academicYearId == $ay->id ? 'selected' : '' }}>
                            {{ $ay->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="tanggal_mulai" class="form-control" value="{{ $tanggalMulai }}">
            </div>
            <div>
                <label class="form-label">Sampai</label>
                <input type="date" name="tanggal_selesai" class="form-control" value="{{ $tanggalSelesai }}">
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end;">
                <button type="submit" class="action-btn btn-view" style="padding:8px 14px;">
                    <i class="fas fa-filter"></i> Terapkan
                </button>
            </div>
        </form>

        {{-- Cards per lokasi --}}
        @forelse($lokasiStats as $stat)
            @php
                $lokasi = $stat['lokasi'];
                $pct = $stat['persen_hadir'];
            @endphp
            <div class="lokasi-rekap-card">
                <div class="lokasi-rekap-head">
                    <div class="lokasi-icon"><i class="fas fa-building"></i></div>
                    <div style="flex:1;">
                        <div class="lokasi-rekap-name">{{ $lokasi->nama_tempat }}</div>
                        <div class="lokasi-rekap-sub">{{ $lokasi->jenis_usaha ?? '' }}
                            {{ $lokasi->kabupaten ? '• ' . $lokasi->kabupaten : '' }}</div>
                    </div>
                    <a href="{{ route('admin.pkl.lokasi.show', $lokasi) }}" class="action-btn btn-view"
                        style="font-size:.72rem;padding:5px 10px;text-decoration:none;flex-shrink:0;">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>

                <div class="stat-row">
                    <div class="mini-stat">
                        <div class="mini-val" style="color:#6366f1;">{{ $stat['total_siswa'] }}</div>
                        <div class="mini-lbl">Siswa</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-val" style="color:#16a34a;">{{ $stat['hadir'] }}</div>
                        <div class="mini-lbl">Hadir</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-val" style="color:#f59e0b;">{{ $stat['terlambat'] }}</div>
                        <div class="mini-lbl">Terlambat</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-val" style="color:#dc2626;">{{ $stat['alfa'] }}</div>
                        <div class="mini-lbl">Alfa</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-val"
                            style="color:{{ $pct >= 80 ? '#16a34a' : ($pct >= 60 ? '#f59e0b' : '#dc2626') }};">
                            {{ $pct }}%</div>
                        <div class="mini-lbl">% Hadir</div>
                    </div>
                </div>

                <div class="pct-bar">
                    <div class="pct-fill"
                        style="width:{{ $pct }}%;background:{{ $pct >= 80 ? '#16a34a' : ($pct >= 60 ? '#f59e0b' : '#dc2626') }};">
                    </div>
                </div>

                <div style="margin-top:10px;display:flex;gap:6px;">
                    <a href="{{ route('admin.pkl.rekap.per-siswa', ['lokasi_pkl_id' => $lokasi->id, 'tanggal_mulai' => $tanggalMulai, 'tanggal_selesai' => $tanggalSelesai]) }}"
                        class="action-btn btn-view" style="font-size:.72rem;padding:5px 10px;text-decoration:none;">
                        <i class="fas fa-users"></i> Detail Siswa
                    </a>
                </div>
            </div>
        @empty
            <div class="card" style="border-radius:12px;">
                <div style="text-align:center;padding:48px 20px;color:#94a3b8;">
                    <i class="fas fa-map-marked-alt"
                        style="font-size:2.5rem;opacity:.2;display:block;margin-bottom:12px;"></i>
                    <strong style="display:block;color:#0f172a;margin-bottom:4px;">Belum ada data lokasi PKL</strong>
                    Tambahkan lokasi PKL terlebih dahulu.
                </div>
            </div>
        @endforelse
    </div>

    <div class="action-bar">
        <a href="{{ route('admin.pkl.lokasi.index') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
        <a href="{{ route('admin.pkl.rekap.per-siswa') }}" class="ab-btn" style="background:#6366f1;color:#fff;"><i
                class="fas fa-user-graduate"></i> Per Siswa</a>
    </div>
@endsection
