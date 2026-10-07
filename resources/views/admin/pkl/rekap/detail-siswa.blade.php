@extends('layouts.app')
@section('title', 'Detail PKL — ' . $penugasanPkl->siswa?->nama_lengkap)

@push('styles')
    @include('components.event-styles')
    <style>
        .pkl-wrap {
            padding: 0 12px;
            max-width: 900px;
            margin: 0 auto;
        }

        @media(min-width:768px) {
            .pkl-wrap {
                padding: 0 24px;
            }
        }

        .stat-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .stat-box {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            text-align: center;
            flex: 1;
            min-width: 70px;
        }

        .stat-val {
            font-size: 1.4rem;
            font-weight: 800;
        }

        .stat-lbl {
            font-size: .65rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }

        .absen-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .8rem;
        }

        .absen-table th {
            background: #f8fafc;
            padding: 8px 10px;
            text-align: left;
            font-size: .68rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
        }

        .absen-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .absen-table tr:last-child td {
            border-bottom: none;
        }

        .badge-hadir {
            background: #dcfce7;
            color: #15803d;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700;
        }

        .badge-terlambat {
            background: #fff7ed;
            color: #c2410c;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700;
        }

        .badge-alfa {
            background: #fee2e2;
            color: #dc2626;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700;
        }

        .badge-izin {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700;
        }

        .badge-pkl {
            background: #fef3c7;
            color: #92400e;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700;
        }

        .badge-sakit {
            background: #fef3c7;
            color: #b45309;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700;
        }

        .filter-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
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

        .verif-form {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }

        .verif-form textarea {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: .75rem;
            font-family: inherit;
            min-width: 180px;
            resize: vertical;
        }
    </style>
@endpush

@section('content')
    <div class="pkl-wrap" style="padding-top:var(--header-h,56px);padding-bottom:130px;">

        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>PKL</div>
            <h2><i class="fas fa-user-graduate"></i> {{ $penugasanPkl->siswa?->nama_lengkap }}</h2>
            <p>{{ $penugasanPkl->lokasiPkl?->nama_tempat }} · {{ $penugasanPkl->tanggal_mulai->format('d M Y') }} –
                {{ $penugasanPkl->tanggal_selesai->format('d M Y') }}</p>
        </div>

        @if (session('success'))
            <div
                style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:12px;font-size:.83rem;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Info siswa --}}
        <div class="card"
            style="border-radius:12px;padding:14px 16px;margin-bottom:14px;display:flex;gap:10px;align-items:flex-start;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <div style="font-size:.72rem;color:#94a3b8;font-weight:600;text-transform:uppercase;margin-bottom:4px;">
                    Siswa</div>
                <div style="font-weight:700;color:#0f172a;">{{ $penugasanPkl->siswa?->nama_lengkap }}</div>
                <div style="font-size:.78rem;color:#64748b;">{{ $penugasanPkl->siswa?->nis }} ·
                    {{ $penugasanPkl->siswa?->kelas?->nama_kelas }}</div>
            </div>
            <div style="flex:1;min-width:200px;">
                <div style="font-size:.72rem;color:#94a3b8;font-weight:600;text-transform:uppercase;margin-bottom:4px;">
                    Pembimbing Sekolah</div>
                <div style="font-weight:600;color:#0f172a;font-size:.85rem;">{{ $penugasanPkl->gtk?->nama_lengkap ?? '—' }}
                </div>
            </div>
            <div style="flex:1;min-width:200px;">
                <div style="font-size:.72rem;color:#94a3b8;font-weight:600;text-transform:uppercase;margin-bottom:4px;">
                    Penanggung Jawab DU/DI</div>
                <div style="font-weight:600;color:#0f172a;font-size:.85rem;">{{ $penugasanPkl->lokasiPkl?->nama_pj ?? '—' }}
                </div>
                @if ($penugasanPkl->lokasiPkl?->no_hp_pj)
                    <div style="font-size:.75rem;color:#64748b;">{{ $penugasanPkl->lokasiPkl->no_hp_pj }}</div>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        <div class="stat-row">
            <div class="stat-box">
                <div class="stat-val" style="color:#16a34a;">{{ $stats['hadir'] }}</div>
                <div class="stat-lbl">Hadir</div>
            </div>
            <div class="stat-box">
                <div class="stat-val" style="color:#f59e0b;">{{ $stats['terlambat'] }}</div>
                <div class="stat-lbl">Terlambat</div>
            </div>
            <div class="stat-box">
                <div class="stat-val" style="color:#dc2626;">{{ $stats['alfa'] }}</div>
                <div class="stat-lbl">Alfa</div>
            </div>
            <div class="stat-box">
                <div class="stat-val" style="color:#0ea5e9;">{{ $stats['izin'] }}</div>
                <div class="stat-lbl">Izin/Sakit</div>
            </div>
            <div class="stat-box">
                <div class="stat-val" style="color:#6366f1;">{{ $stats['total_jurnal'] }}</div>
                <div class="stat-lbl">Jurnal</div>
            </div>
        </div>

        {{-- Filter tanggal --}}
        <form method="GET" class="filter-bar">
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Dari</label>
                <input type="date" name="tanggal_mulai"
                    style="padding:7px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:.82rem;"
                    value="{{ $tanggalMulai }}">
            </div>
            <div>
                <label
                    style="font-size:.75rem;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Sampai</label>
                <input type="date" name="tanggal_selesai"
                    style="padding:7px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:.82rem;"
                    value="{{ $tanggalSelesai }}">
            </div>
            <div style="display:flex;align-items:flex-end;">
                <button type="submit" class="action-btn btn-view" style="padding:7px 14px;font-size:.8rem;"><i
                        class="fas fa-filter"></i> Filter</button>
            </div>
        </form>

        {{-- Tabel absensi + jurnal --}}
        <div class="card" style="border-radius:12px;overflow:hidden;">
            <div style="padding:11px 14px;border-bottom:1px solid #f1f5f9;font-size:.85rem;font-weight:800;color:#0f172a;">
                <i class="fas fa-calendar-check" style="color:#f59e0b;margin-right:6px;"></i> Rekap Harian
            </div>
            <div style="overflow-x:auto;">
                <table class="absen-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Status Absen</th>
                            <th>Jam Masuk</th>
                            <th>Jurnal</th>
                            @can('pkl.update')
                                <th>Verifikasi Jurnal</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absenList as $absen)
                            @php
                                $tgl = $absen->tanggal->toDateString();
                                $jurnal = $jurnalList->get($tgl);
                                $status = $absen->status_masuk ?? ($absen->status ?? 'alfa');
                            @endphp
                            <tr>
                                <td style="font-weight:600;white-space:nowrap;">
                                    {{ $absen->tanggal->translatedFormat('D, d M Y') }}</td>
                                <td>
                                    <span class="badge-{{ in_array($status, ['hadir', 'pkl']) ? 'hadir' : $status }}">
                                        @php
                                            echo match ($status) {
                                                'hadir', 'pkl' => 'Hadir',
                                                'terlambat' => 'Terlambat',
                                                'alfa' => 'Alfa',
                                                'izin' => 'Izin',
                                                'sakit' => 'Sakit',
                                                default => ucfirst($status),
                                            };
                                        @endphp
                                    </span>
                                </td>
                                <td style="font-size:.78rem;">
                                    {{ $absen->jam_masuk ? \Carbon\Carbon::parse($absen->jam_masuk)->format('H:i') : '—' }}
                                </td>
                                <td>
                                    @if ($jurnal)
                                        <div style="font-size:.78rem;max-width:220px;">
                                            {{ \Illuminate\Support\Str::limit($jurnal->kegiatan, 60) }}</div>
                                        <span
                                            style="font-size:.65rem;font-weight:700;padding:1px 7px;border-radius:20px;
                                        background:{{ $jurnal->status_verifikasi === 'disetujui' ? '#dcfce7' : ($jurnal->status_verifikasi === 'revisi' ? '#fee2e2' : '#fef3c7') }};
                                        color:{{ $jurnal->status_verifikasi === 'disetujui' ? '#15803d' : ($jurnal->status_verifikasi === 'revisi' ? '#dc2626' : '#92400e') }};">
                                            {{ $jurnal->status_verifikasi_label }}
                                        </span>
                                    @else
                                        <span style="font-size:.75rem;color:#94a3b8;">—</span>
                                    @endif
                                </td>
                                @can('pkl.update')
                                    <td>
                                        @if ($jurnal && $jurnal->status_verifikasi !== 'disetujui')
                                            <form method="POST"
                                                action="{{ route('admin.pkl.rekap.jurnal.verifikasi', $jurnal) }}"
                                                class="verif-form">
                                                @csrf @method('PATCH')
                                                <textarea name="catatan_pembimbing" placeholder="Catatan (opsional)" rows="1">{{ $jurnal->catatan_pembimbing }}</textarea>
                                                <button type="submit" name="status_verifikasi" value="disetujui"
                                                    class="action-btn btn-view"
                                                    style="font-size:.7rem;padding:4px 8px;background:#dcfce7;color:#15803d;border:none;">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="submit" name="status_verifikasi" value="revisi"
                                                    class="action-btn"
                                                    style="font-size:.7rem;padding:4px 8px;background:#fee2e2;color:#dc2626;border:none;border-radius:6px;cursor:pointer;">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;">Belum ada data
                                    absensi untuk periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="action-bar">
        <a href="{{ route('admin.pkl.rekap.per-siswa') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
        <a href="{{ route('admin.pkl.lokasi.show', $penugasanPkl->lokasi_pkl_id) }}" class="ab-btn"
            style="background:#f59e0b;color:#fff;">
            <i class="fas fa-building"></i> Lihat Lokasi
        </a>
    </div>
@endsection
