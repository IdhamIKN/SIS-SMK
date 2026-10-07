@extends('layouts.app')
@section('title', 'Dashboard PKL')

@push('styles')
    @include('components.event-styles')
    <style>
        .pkl-wrap {
            padding: 0 12px;
            max-width: 700px;
            margin: 0 auto;
        }

        .hero-card {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #fff;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .hero-card h2 {
            font-size: 1.1rem;
            font-weight: 800;
            margin: 0 0 4px;
        }

        .hero-card p {
            font-size: .82rem;
            opacity: .9;
            margin: 0;
        }

        .hero-detail {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }

        .hero-chip {
            background: rgba(255, 255, 255, .2);
            border-radius: 20px;
            padding: 5px 12px;
            font-size: .75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-row {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .stat-card {
            flex: 1;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
            text-align: center;
            min-width: 80px;
        }

        .stat-val {
            font-size: 1.5rem;
            font-weight: 800;
        }

        .stat-lbl {
            font-size: .65rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .jurnal-list {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .jurnal-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .jurnal-item:last-child {
            border-bottom: none;
        }

        .jurnal-date {
            font-size: .72rem;
            font-weight: 700;
            color: #64748b;
            white-space: nowrap;
            min-width: 50px;
        }

        .jurnal-kegiatan {
            font-size: .82rem;
            color: #0f172a;
            flex: 1;
        }

        .jurnal-status {
            font-size: .65rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            flex-shrink: 0;
        }

        .js-diajukan {
            background: #fef3c7;
            color: #92400e;
        }

        .js-disetujui {
            background: #dcfce7;
            color: #15803d;
        }

        .js-revisi {
            background: #fee2e2;
            color: #dc2626;
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
            padding: 12px 16px;
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

        .ab-btn-amber {
            background: #f59e0b;
            color: #fff;
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
    </style>
@endpush

@section('content')
    <div class="pkl-wrap" style="padding-top:var(--header-h,56px);padding-bottom:120px;">

        {{-- Hero --}}
        <div class="hero-card" style="margin-top:12px;">
            <div style="display:flex;align-items:flex-start;gap:12px;">
                <div
                    style="width:48px;height:48px;background:rgba(255,255,255,.25);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.2rem;">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div>
                    <h2>{{ $penugasan->lokasiPkl?->nama_tempat ?? 'PKL Aktif' }}</h2>
                    <p>{{ $penugasan->lokasiPkl?->jenis_usaha ?? '' }}
                        {{ $penugasan->lokasiPkl?->kabupaten ? '• ' . $penugasan->lokasiPkl->kabupaten : '' }}</p>
                </div>
            </div>
            <div class="hero-detail">
                <span class="hero-chip"><i class="fas fa-calendar-alt"></i>{{ $penugasan->tanggal_mulai->format('d M Y') }} –
                    {{ $penugasan->tanggal_selesai->format('d M Y') }}</span>
                @if ($penugasan->gtk)
                    <span class="hero-chip"><i
                            class="fas fa-chalkboard-teacher"></i>{{ $penugasan->gtk->nama_lengkap }}</span>
                @endif
                @if ($penugasan->lokasiPkl?->nama_pj)
                    <span class="hero-chip"><i class="fas fa-user-tie"></i>{{ $penugasan->lokasiPkl->nama_pj }}</span>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        <div class="stat-row">
            <div class="stat-card">
                <div class="stat-val" style="color:#6366f1;">{{ $stats['total_jurnal'] }}</div>
                <div class="stat-lbl">Jurnal</div>
            </div>
            <div class="stat-card">
                <div class="stat-val" style="color:#16a34a;">{{ $stats['disetujui'] }}</div>
                <div class="stat-lbl">Disetujui</div>
            </div>
            <div class="stat-card">
                <div class="stat-val" style="color:#dc2626;">{{ $stats['revisi'] }}</div>
                <div class="stat-lbl">Perlu Revisi</div>
            </div>
            <div class="stat-card">
                <div class="stat-val" style="color:#f59e0b;">{{ $penugasan->durasiHari() }}</div>
                <div class="stat-lbl">Hari PKL</div>
            </div>
        </div>

        {{-- Status Absensi PKL Hari Ini --}}
        @php
            $sudahMasukPkl = $absenHariIni && !empty($absenHariIni->jam_masuk);
            $sudahPulangPkl = $absenHariIni && !empty($absenHariIni->jam_pulang);
            $statusMasukPkl = $absenHariIni?->status_masuk ?? null;
        @endphp
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:14px 16px;margin-bottom:14px;">
            <h4
                style="font-size:.8rem;font-weight:800;color:#0f172a;margin:0 0 10px;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-fingerprint" style="color:#f59e0b;"></i> Absensi PKL Hari Ini
            </h4>
            <div style="display:flex;gap:8px;">
                <div
                    style="flex:1;background:{{ $sudahMasukPkl ? '#f0fdf4' : '#fffbeb' }};border:1px solid {{ $sudahMasukPkl ? '#86efac' : '#fcd34d' }};border-radius:10px;padding:10px;text-align:center;">
                    <div style="font-size:1.2rem;">{{ $sudahMasukPkl ? '✅' : '⏳' }}</div>
                    <div
                        style="font-size:.7rem;font-weight:700;color:{{ $sudahMasukPkl ? '#15803d' : '#92400e' }};margin-top:4px;">
                        @if ($sudahMasukPkl)
                            {{ date('H:i', strtotime($absenHariIni->jam_masuk)) }}
                            @if ($statusMasukPkl === 'terlambat')
                                <span
                                    style="display:block;font-size:.6rem;background:#fee2e2;color:#dc2626;border-radius:10px;padding:1px 6px;margin-top:2px;">Terlambat</span>
                            @endif
                        @else
                            Belum Masuk
                        @endif
                    </div>
                    <div style="font-size:.6rem;color:#64748b;margin-top:2px;">Masuk</div>
                </div>
                <div
                    style="flex:1;background:{{ $sudahPulangPkl ? '#eff6ff' : '#f8fafc' }};border:1px solid {{ $sudahPulangPkl ? '#93c5fd' : '#e2e8f0' }};border-radius:10px;padding:10px;text-align:center;">
                    <div style="font-size:1.2rem;">{{ $sudahPulangPkl ? '🏠' : '⏳' }}</div>
                    <div
                        style="font-size:.7rem;font-weight:700;color:{{ $sudahPulangPkl ? '#1d4ed8' : '#64748b' }};margin-top:4px;">
                        {{ $sudahPulangPkl ? date('H:i', strtotime($absenHariIni->jam_pulang)) : 'Belum Pulang' }}
                    </div>
                    <div style="font-size:.6rem;color:#64748b;margin-top:2px;">Pulang</div>
                </div>
            </div>
            @if (!$sudahMasukPkl)
                <a href="{{ route('siswa.pkl.absen.index') }}"
                    style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:10px;padding:9px;background:#f59e0b;color:#fff;border-radius:10px;font-size:.8rem;font-weight:700;text-decoration:none;">
                    <i class="fas fa-sign-in-alt"></i> Absen Masuk PKL Sekarang
                </a>
            @elseif(!$sudahPulangPkl)
                <a href="{{ route('siswa.pkl.absen.index') }}"
                    style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:10px;padding:9px;background:#0ea5e9;color:#fff;border-radius:10px;font-size:.8rem;font-weight:700;text-decoration:none;">
                    <i class="fas fa-sign-out-alt"></i> Absen Pulang PKL Sekarang
                </a>
            @endif
        </div>

        {{-- Jurnal hari ini --}}
        @if ($jurnalHariIni)
            <div
                style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:12px 14px;border-radius:10px;margin-bottom:14px;font-size:.83rem;">
                <i class="fas fa-check-circle"></i> Jurnal hari ini sudah diisi.
                <a href="{{ route('siswa.pkl.jurnal.edit', $jurnalHariIni) }}"
                    style="color:#15803d;font-weight:700;margin-left:6px;text-decoration:none;">Edit</a>
            </div>
        @else
            <div
                style="background:#fffbeb;border:1px solid #fcd34d;color:#92400e;padding:12px 14px;border-radius:10px;margin-bottom:14px;font-size:.83rem;">
                <i class="fas fa-exclamation-circle"></i> Belum mengisi jurnal hari ini.
                <a href="{{ route('siswa.pkl.jurnal.create') }}"
                    style="color:#b45309;font-weight:700;margin-left:6px;text-decoration:none;">Isi Sekarang</a>
            </div>
        @endif

        {{-- Info lokasi --}}
        @if ($penugasan->lokasiPkl?->no_hp_pj)
            <div class="card" style="border-radius:12px;padding:14px 16px;margin-bottom:14px;">
                <h4 style="font-size:.8rem;font-weight:700;color:#0f172a;margin:0 0 8px;"><i class="fas fa-phone"
                        style="color:#f59e0b;margin-right:6px;"></i>Kontak Tempat PKL</h4>
                <div style="font-size:.82rem;color:#374151;">
                    {{ $penugasan->lokasiPkl->nama_pj }}{{ $penugasan->lokasiPkl->jabatan_pj ? ' (' . $penugasan->lokasiPkl->jabatan_pj . ')' : '' }}
                </div>
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $penugasan->lokasiPkl->no_hp_pj) }}"
                    style="font-size:.8rem;color:#0ea5e9;text-decoration:none;">
                    <i class="fab fa-whatsapp"></i> {{ $penugasan->lokasiPkl->no_hp_pj }}
                </a>
            </div>
        @endif

        {{-- Jurnal bulan ini --}}
        <div class="jurnal-list">
            <div style="display:flex;align-items:center;gap:8px;padding:12px 16px;border-bottom:1px solid #f1f5f9;">
                <i class="fas fa-book" style="color:#f59e0b;"></i>
                <h3 style="font-size:.9rem;font-weight:800;color:#0f172a;margin:0;flex:1;">Jurnal Bulan Ini</h3>
                <a href="{{ route('siswa.pkl.jurnal.index') }}"
                    style="font-size:.75rem;color:#0ea5e9;text-decoration:none;font-weight:700;">Semua <i
                        class="fas fa-arrow-right" style="font-size:.65rem;"></i></a>
            </div>

            @forelse($jurnalBulanIni->take(7) as $j)
                <div class="jurnal-item">
                    <div class="jurnal-date">{{ $j->tanggal->format('d M') }}</div>
                    <div class="jurnal-kegiatan">{{ \Illuminate\Support\Str::limit($j->kegiatan, 80) }}</div>
                    <span class="jurnal-status js-{{ $j->status_verifikasi }}">{{ $j->status_verifikasi_label }}</span>
                </div>
            @empty
                <div style="text-align:center;padding:24px;color:#94a3b8;font-size:.82rem;">
                    <i class="fas fa-book" style="font-size:1.8rem;opacity:.2;display:block;margin-bottom:8px;"></i>
                    Belum ada jurnal bulan ini.
                </div>
            @endforelse
        </div>
    </div>

    <div class="action-bar">
        <a href="{{ route('dashboard') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
        <a href="{{ route('siswa.pkl.absen.index') }}" class="ab-btn" style="background:#f59e0b;color:#fff;">
            <i class="fas fa-fingerprint"></i> Absen PKL
        </a>
        <a href="{{ route('siswa.pkl.jurnal.index') }}" class="ab-btn" style="background:#6366f1;color:#fff;">
            <i class="fas fa-book"></i> Jurnal
        </a>
        <a href="{{ route('siswa.pkl.jurnal.create') }}" class="ab-btn ab-btn-amber">
            <i class="fas fa-plus"></i> Isi Jurnal
        </a>
    </div>
@endsection
