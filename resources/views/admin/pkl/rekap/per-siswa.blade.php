@extends('layouts.app')
@section('title', 'Rekap PKL per Siswa')

@push('styles')
    @include('components.event-styles')
    <style>
        .pkl-wrap {
            padding: 0 12px;
            max-width: 1200px;
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

        /* Ganti .rekap-table dan kolom-kolom lama dengan ini */
        .rekap-table {
            width: 100%;
            border-collapse: collapse;
        }

        .rekap-table th {
            background: #f8fafc;
            padding: 9px 12px;
            text-align: left;
            font-size: .69rem;
            font-weight: 500;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .rekap-table th.center {
            text-align: center;
        }

        .rekap-table td {
            padding: 11px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .rekap-table tr:last-child td {
            border-bottom: none;
        }

        .rekap-table tr:hover td {
            background: #fafbfc;
        }

        /* Siswa */
        .siswa-nama {
            font-size: .84rem;
            font-weight: 600;
            color: #0f172a;
        }

        .siswa-nis {
            font-size: .7rem;
            color: #64748b;
            margin-top: 1px;
        }

        .siswa-kelas {
            font-size: .7rem;
            color: #94a3b8;
        }

        /* Periode */
        .periode-tgl {
            font-size: .7rem;
            color: #64748b;
            white-space: nowrap;
        }

        /* Badge status */
        .badge-pkl {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .62rem;
            font-weight: 600;
            margin-top: 4px;
        }

        .badge-aktif {
            background: #dcfce7;
            color: #166534;
        }

        .badge-selesai {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-batal {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Angka statistik */
        .stat-center {
            text-align: center;
        }

        .stat-num {
            font-size: .88rem;
            font-weight: 600;
        }

        .stat-sub {
            font-size: .6rem;
            color: #94a3b8;
            margin-top: 1px;
        }

        .col-hadir {
            color: #16a34a;
        }

        .col-telat {
            color: #f59e0b;
        }

        .col-alfa {
            color: #dc2626;
        }

        /* Progress bar */
        .pct-bar {
            height: 5px;
            border-radius: 3px;
            background: #e2e8f0;
            overflow: hidden;
            margin-top: 4px;
        }

        .pct-fill {
            height: 100%;
            border-radius: 3px;
        }

        .badge-pkl {
            background: #fef3c7;
            color: #92400e;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700;
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
            <h2><i class="fas fa-user-graduate"></i> Rekap PKL per Siswa</h2>
            <p>Data kehadiran dan jurnal masing-masing siswa PKL.</p>
        </div>

        {{-- Filter --}}
        <form method="GET" class="filter-bar">
            <div>
                <label class="form-label">Lokasi PKL</label>
                <select name="lokasi_pkl_id" class="form-control">
                    <option value="">Semua Lokasi</option>
                    @foreach ($lokasiOptions as $l)
                        <option value="{{ $l->id }}" {{ $lokasiId == $l->id ? 'selected' : '' }}>
                            {{ $l->nama_tempat }}</option>
                    @endforeach
                </select>
            </div>
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
                <label class="form-label">Dari</label>
                <input type="date" name="tanggal_mulai" class="form-control" value="{{ $tanggalMulai }}">
            </div>
            <div>
                <label class="form-label">Sampai</label>
                <input type="date" name="tanggal_selesai" class="form-control" value="{{ $tanggalSelesai }}">
            </div>
            <div>
                <label class="form-label">Cari Siswa</label>
                <input type="text" name="search" class="form-control" placeholder="Nama / NIS..."
                    value="{{ $search }}">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">Semua</option>
                    <option value="aktif" {{ ($status ?? '') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="selesai" {{ ($status ?? '') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="batal" {{ ($status ?? '') === 'batal' ? 'selected' : '' }}>Batal</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end;">
                <button type="submit" class="action-btn btn-view" style="padding:8px 14px;">
                    <i class="fas fa-filter"></i>
                </button>
                @if (
                    $lokasiId ||
                        $academicYearId ||
                        $search ||
                        ($status ?? '') ||
                        $tanggalMulai !== now()->startOfMonth()->toDateString())
                    <a href="{{ route('admin.pkl.rekap.per-siswa') }}" class="action-btn"
                        style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:8px 12px;text-decoration:none;">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>

        {{-- Info range --}}
        <div
            style="background:#fef3c7;border:1px solid #fcd34d;border-radius:10px;padding:8px 14px;margin-bottom:12px;font-size:.76rem;color:#92400e;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <i class="fas fa-info-circle"></i>
            Range: <strong>{{ \Carbon\Carbon::parse($tanggalMulai)->translatedFormat('d M Y') }}</strong> –
            <strong>{{ \Carbon\Carbon::parse($tanggalSelesai)->translatedFormat('d M Y') }}</strong>
            &nbsp;|&nbsp; Est. hari kerja: <strong>{{ $hariKerja }} hari</strong>
            &nbsp;|&nbsp; Data absen = kehadiran via fitur absen PKL.
        </div>

        <div class="card" style="border-radius:12px;overflow:hidden;">
            <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid #f1f5f9;">
                <div class="c-icon" style="background:#fef3c7;color:#b45309;flex-shrink:0;"><i
                        class="fas fa-user-graduate"></i></div>
                <h3 style="font-size:.9rem;font-weight:800;color:#0f172a;margin:0;flex:1;">Data Siswa PKL</h3>
                <span
                    style="background:#f1f5f9;color:#475569;font-size:.7rem;padding:3px 10px;border-radius:20px;font-weight:700;">{{ $penugasanList->total() }}
                    siswa</span>
            </div>

            <div style="overflow-x:auto;">
                <table class="rekap-table">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Lokasi PKL</th>
                            <th>Pembimbing</th>
                            <th>Periode</th>
                            <th>Hadir</th>
                            <th>Terlambat</th>
                            <th>Alfa</th>
                            <th>% Hadir</th>
                            <th>Jurnal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penugasanList as $p)
                            @php
                                $siswaAbsen = $absenMap->get($p->siswa_id, collect());
                                $hadir = $siswaAbsen->whereIn('status_masuk', ['hadir', 'pkl'])->sum('total');
                                $terlambat = $siswaAbsen->where('status_masuk', 'terlambat')->sum('total');
                                $totalHadir = $hadir + $terlambat;
                                // Estimasi alfa: hari kerja dalam range penugasan vs aktual hadir
                                $mulaiEff = max($p->tanggal_mulai->toDateString(), $tanggalMulai);
                                $selesaiEff = min($p->tanggal_selesai->toDateString(), $tanggalSelesai);
                                $hariPenugasan =
                                    $mulaiEff <= $selesaiEff
                                        ? \Carbon\Carbon::parse($mulaiEff)->diffInWeekdays(
                                                \Carbon\Carbon::parse($selesaiEff),
                                            ) + 1
                                        : 0;
                                $alfa = max(0, $hariPenugasan - $totalHadir);
                                $total = $totalHadir + $alfa;
                                $pct = $total > 0 ? round(($totalHadir / $total) * 100) : 0;
                                $jmlJurnal = $jurnalMap->get($p->siswa_id, 0);
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:700;font-size:.85rem;">{{ $p->siswa?->nama_lengkap ?? '-' }}
                                    </div>
                                    <div style="font-size:.7rem;color:#64748b;">{{ $p->siswa?->nis }}</div>
                                    <div style="font-size:.7rem;color:#94a3b8;">{{ $p->siswa?->kelas?->nama_kelas }}</div>
                                </td>
                                <td style="font-size:.78rem;">{{ $p->lokasiPkl?->nama_tempat ?? '-' }}</td>
                                <td style="font-size:.75rem;">{{ $p->gtk?->nama_lengkap ?? '-' }}</td>
                                <td style="font-size:.72rem;white-space:nowrap;">
                                    {{ $p->tanggal_mulai->format('d/m/Y') }}<br>
                                    {{ $p->tanggal_selesai->format('d/m/Y') }}<br>
                                    <span class="badge-pkl">{{ $p->status_label }}</span>
                                </td>
                                <td style="text-align:center;color:#16a34a;font-weight:700;">{{ $hadir }}</td>
                                <td style="text-align:center;color:#f59e0b;font-weight:700;">{{ $terlambat }}</td>
                                <td style="text-align:center;color:#dc2626;font-weight:700;">{{ $alfa }}<span
                                        style="font-size:.6rem;color:#94a3b8;display:block;">/{{ $hariPenugasan }}hr</span>
                                </td>
                                <td style="min-width:80px;">
                                    <div
                                        style="font-size:.82rem;font-weight:700;color:{{ $pct >= 80 ? '#16a34a' : ($pct >= 60 ? '#f59e0b' : '#dc2626') }};">
                                        {{ $pct }}%
                                    </div>
                                    <div class="pct-bar">
                                        <div class="pct-fill"
                                            style="width:{{ $pct }}%;background:{{ $pct >= 80 ? '#16a34a' : ($pct >= 60 ? '#f59e0b' : '#dc2626') }};">
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align:center;font-size:.82rem;">
                                    <span
                                        style="background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:20px;font-weight:700;font-size:.68rem;">{{ $jmlJurnal }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.pkl.rekap.detail-siswa', $p) }}" class="action-btn btn-view"
                                        style="font-size:.7rem;padding:5px 9px;text-decoration:none;">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align:center;padding:32px;color:#94a3b8;">
                                    <i class="fas fa-inbox"
                                        style="font-size:2rem;opacity:.2;display:block;margin-bottom:8px;"></i>
                                    Tidak ada data penugasan PKL.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($penugasanList->hasPages())
                <div
                    style="padding:10px 14px;border-top:1px solid #f1f5f9;display:flex;justify-content:center;flex-wrap:wrap;gap:5px;">
                    @if ($penugasanList->onFirstPage())
                        <span class="pg-btn disabled"><i class="fas fa-angle-left"></i></span>
                    @else
                        <a href="{{ $penugasanList->previousPageUrl() }}" class="pg-btn"><i
                                class="fas fa-angle-left"></i></a>
                    @endif
                    <span
                        class="pg-btn active">{{ $penugasanList->currentPage() }}/{{ $penugasanList->lastPage() }}</span>
                    @if ($penugasanList->hasMorePages())
                        <a href="{{ $penugasanList->nextPageUrl() }}" class="pg-btn"><i class="fas fa-angle-right"></i></a>
                    @else
                        <span class="pg-btn disabled"><i class="fas fa-angle-right"></i></span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="action-bar">
        <a href="{{ route('admin.pkl.lokasi.index') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
        <a href="{{ route('admin.pkl.rekap.per-lokasi') }}" class="ab-btn" style="background:#6366f1;color:#fff;"><i
                class="fas fa-map-marked-alt"></i> Per Lokasi</a>
    </div>
@endsection
