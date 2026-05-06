@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <style>
        /* ── Hero / Greeting Strip ── */
        .dash-hero {
            position: relative;
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 55%, #0ea5e9 100%);
            padding: 28px 20px 56px;
            overflow: hidden;
        }

        .dash-hero::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, .07);
            border-radius: 50%;
        }

        .dash-hero::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -30px;
            width: 130px;
            height: 130px;
            background: rgba(255, 255, 255, .05);
            border-radius: 50%;
        }

        .hero-top {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .hero-greeting {
            flex: 1;
            min-width: 0;
        }

        .hero-date {
            font-size: .72rem;
            font-weight: 600;
            color: rgba(255, 255, 255, .7);
            letter-spacing: .04em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .hero-name {
            font-size: 1.2rem;
            font-weight: 800;
            color: #fff;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
        }

        .hero-role {
            font-size: .72rem;
            color: rgba(255, 255, 255, .65);
            margin-top: 2px;
        }

        .hero-avatar {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            border: 2px solid rgba(255, 255, 255, .35);
            overflow: hidden;
            flex-shrink: 0;
            background: rgba(255, 255, 255, .15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
        }

        .hero-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ── Stat cards (overlap hero) ── */
        .dash-stats {
            position: relative;
            z-index: 3;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: -36px 16px 0;
        }

        .dash-stats.cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 14px 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .10);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, .8);
        }

        .stat-card .sc-icon {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
            margin: 0 auto 8px;
        }

        .stat-card .sc-val {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 3px;
        }

        .stat-card .sc-lbl {
            font-size: .62rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* ── Wrapper utama ── */
        .dash-wrap {
            padding: 16px 16px calc(var(--footer-h) + 80px);
        }

        /* ── Section title ── */
        .section-title {
            font-size: .72rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 20px 0 10px;
        }

        /* ── Quick action grid ── */
        .qa-grid {
            display: grid;
            gap: 10px;
        }

        .qa-grid.cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .qa-grid.cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        .qa-grid.cols-5 {
            grid-template-columns: repeat(5, 1fr);
        }

        .qa-item {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 16px;
            padding: 16px 8px 12px;
            text-align: center;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
            transition: transform .18s, box-shadow .18s;
        }

        .qa-item:active {
            transform: scale(.95);
        }

        .qa-item:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, .10);
        }

        .qa-icon {
            width: 44px;
            height: 44px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .qa-label {
            font-size: .68rem;
            font-weight: 700;
            color: var(--text-main, #0f172a);
            line-height: 1.2;
        }

        /* Color tokens */
        .c-green {
            background: #dcfce7;
            color: #15803d;
        }

        .c-blue {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .c-red {
            background: #fee2e2;
            color: #dc2626;
        }

        .c-orange {
            background: #ffedd5;
            color: #c2410c;
        }

        .c-purple {
            background: #ede9fe;
            color: #7c3aed;
        }

        .c-teal {
            background: #ccfbf1;
            color: #0f766e;
        }

        .c-yellow {
            background: #fef9c3;
            color: #a16207;
        }

        .c-indigo {
            background: #e0e7ff;
            color: #4338ca;
        }

        /* ── Aktivitas terbaru ── */
        .activity-card {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .activity-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .activity-head h4 {
            margin: 0;
            font-size: .88rem;
            font-weight: 700;
        }

        .activity-head .see-all {
            font-size: .72rem;
            color: #7c3aed;
            font-weight: 600;
            text-decoration: none;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 16px;
            border-bottom: 1px solid #f8fafc;
            transition: background .15s;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            background: #fafbfc;
        }

        .act-icon {
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
        }

        .act-body {
            flex: 1;
            min-width: 0;
        }

        .act-title {
            font-size: .82rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 2px;
        }

        .act-sub {
            font-size: .72rem;
            color: #64748b;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .act-time {
            font-size: .65rem;
            color: #94a3b8;
            flex-shrink: 0;
            padding-top: 2px;
        }

        .empty-activity {
            padding: 28px 20px;
            text-align: center;
            color: #94a3b8;
            font-size: .82rem;
        }

        .empty-activity i {
            font-size: 2rem;
            display: block;
            margin-bottom: 8px;
            opacity: .4;
        }

        /* ── Info banner (event aktif, dll) ── */
        .info-banner {
            display: flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #ede9fe, #dbeafe);
            border: 1px solid #c4b5fd;
            border-radius: 14px;
            padding: 14px;
            text-decoration: none;
            margin-bottom: 0;
            transition: opacity .18s;
        }

        .info-banner:active {
            opacity: .85;
        }

        .ib-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #7c3aed;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .ib-body {
            flex: 1;
            min-width: 0;
        }

        .ib-title {
            font-size: .82rem;
            font-weight: 700;
            color: #4c1d95;
            margin: 0 0 2px;
        }

        .ib-sub {
            font-size: .72rem;
            color: #6d28d9;
            margin: 0;
        }

        .ib-arrow {
            color: #7c3aed;
            font-size: .85rem;
            flex-shrink: 0;
        }
    </style>
@endpush

@section('content')

    {{-- ── Hero Greeting ── --}}
    <div class="dash-hero">
        <div class="hero-top">
            <div class="hero-greeting">
                <div class="hero-date">{{ now()->translatedFormat('l, d F Y') }}</div>
                @php
                    $hour = now()->hour;
                    $greet =
                        $hour < 11
                            ? 'Selamat Pagi'
                            : ($hour < 15
                                ? 'Selamat Siang'
                                : ($hour < 18
                                    ? 'Selamat Sore'
                                    : 'Selamat Malam'));
                    $user = auth()->user();
                @endphp
                <h2 class="hero-name">{{ $greet }}, {{ Str::words($user->name, 2, '') }} 👋</h2>
                <div class="hero-role">
                    @hasrole('siswa')
                        Siswa
                        @elsehasrole('guru') Guru / GTK
                    @else
                        Administrator
                    @endhasrole
                    &nbsp;·&nbsp; {{ config('sekolah.nama', 'SMKN 5 Madiun') }}
                </div>
            </div>
            <div class="hero-avatar">
                @if ($user->foto)
                    <img src="{{ Storage::url($user->foto) }}" alt="{{ $user->name }}">
                @else
                    <i class="fas fa-user"></i>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Stat Cards (overlap hero) ── --}}
    @hasrole('siswa')
        <div class="dash-stats" style="grid-template-columns: repeat(2, 1fr);">
            <div class="stat-card">
                <div class="sc-icon c-green"><i class="fas fa-calendar-check"></i></div>
                <div class="sc-val" style="color:#15803d;">{{ $statHadir ?? 0 }}</div>
                <div class="sc-lbl">Hadir Bulan Ini</div>
            </div>
            <div class="stat-card">
                <div class="sc-icon c-red"><i class="fas fa-times-circle"></i></div>
                <div class="sc-val" style="color:#dc2626;">{{ $statAlfa ?? 0 }}</div>
                <div class="sc-lbl">Alfa Bulan Ini</div>
            </div>
            <div class="stat-card">
                <div class="sc-icon c-yellow"><i class="fas fa-file-alt"></i></div>
                <div class="sc-val" style="color:#a16207;">{{ $statIzin ?? 0 }}</div>
                <div class="sc-lbl">Izin / Sakit</div>
            </div>
            <div class="stat-card">
                <div class="sc-icon c-blue"><i class="fas fa-calendar-alt"></i></div>
                <div class="sc-val" style="color:#1d4ed8;">{{ $statEvent ?? 0 }}</div>
                <div class="sc-lbl">Event Aktif</div>
            </div>
        </div>
    @else
        <div class="dash-stats" style="grid-template-columns: repeat(2, 1fr);">
            <div class="stat-card">
                <div class="sc-icon c-green"><i class="fas fa-user-check"></i></div>
                <div class="sc-val" style="color:#15803d;">{{ $statHadir ?? 0 }}</div>
                <div class="sc-lbl">Hadir Hari Ini</div>
            </div>
            <div class="stat-card">
                <div class="sc-icon c-red"><i class="fas fa-user-times"></i></div>
                <div class="sc-val" style="color:#dc2626;">{{ $statAlfa ?? 0 }}</div>
                <div class="sc-lbl">Alfa Bulan Ini</div>
            </div>
            <div class="stat-card">
                <div class="sc-icon c-purple"><i class="fas fa-users"></i></div>
                <div class="sc-val" style="color:#7c3aed;">{{ $statSiswa ?? 0 }}</div>
                <div class="sc-lbl">Total Siswa</div>
            </div>
            <div class="stat-card">
                <div class="sc-icon c-orange"><i class="fas fa-chalkboard-teacher"></i></div>
                <div class="sc-val" style="color:#c2410c;">{{ $statGtk ?? 0 }}</div>
                <div class="sc-lbl">Total GTK</div>
            </div>
        </div>
    @endhasrole

    {{-- ── Main Content ── --}}
    <div class="dash-wrap">

        {{-- Banner Event Aktif (jika ada) --}}
        @if (!empty($eventAktif))
            <p class="section-title">Event Berlangsung</p>
            <a href="{{ route('event.show', $eventAktif) }}" class="info-banner">
                <div class="ib-icon"><i class="fas fa-calendar-star"></i></div>
                <div class="ib-body">
                    <div class="ib-title">{{ Str::limit($eventAktif->nama_event, 35) }}</div>
                    <div class="ib-sub">
                        <i class="fas fa-clock" style="font-size:.65rem;"></i>
                        Sampai {{ $eventAktif->tanggal_selesai->format('H:i') }}
                        &nbsp;·&nbsp; {{ $eventAktif->tanggal_selesai->format('d M Y') }}
                    </div>
                </div>
                <i class="fas fa-chevron-right ib-arrow"></i>
            </a>
        @endif

        {{-- ── Quick Actions ── --}}
        <p class="section-title">Akses Cepat</p>

        @hasrole('siswa')
            <div class="qa-grid cols-3">
                <a href="{{ route('absen.index') }}" class="qa-item">
                    <div class="qa-icon c-green"><i class="fas fa-clipboard-check"></i></div>
                    <div class="qa-label">Absensi</div>
                </a>
                <a href="{{ route('event.index') }}" class="qa-item">
                    <div class="qa-icon c-red"><i class="fas fa-calendar-alt"></i></div>
                    <div class="qa-label">Event</div>
                </a>
                <a href="{{ route('siswa.izin.index') }}" class="qa-item">
                    <div class="qa-icon c-teal"><i class="fas fa-file-medical"></i></div>
                    <div class="qa-label">Izin</div>
                </a>
            </div>
        @else
            <div class="qa-grid cols-5">
                <a href="{{ route('event.index') }}" class="qa-item">
                    <div class="qa-icon c-red"><i class="fas fa-calendar-alt"></i></div>
                    <div class="qa-label">Event</div>
                </a>
                <a href="{{ route('siswa.index') }}" class="qa-item">
                    <div class="qa-icon c-blue"><i class="fas fa-users"></i></div>
                    <div class="qa-label">Siswa</div>
                </a>
                <a href="#" class="qa-item">
                    <div class="qa-icon c-orange"><i class="fas fa-chart-bar"></i></div>
                    <div class="qa-label">Laporan</div>
                </a>
                <a href="{{ route('gtk.index') }}" class="qa-item">
                    <div class="qa-icon c-teal"><i class="fas fa-user-tie"></i></div>
                    <div class="qa-label">GTK</div>
                </a>
                <a href="{{ route('kelas.index') }}" class="qa-item">
                    <div class="qa-icon c-purple"><i class="fas fa-school"></i></div>
                    <div class="qa-label">Kelas</div>
                </a>
            </div>
        @endhasrole

        {{-- ── Aktivitas Terbaru ── --}}
        <p class="section-title">Aktivitas Terbaru</p>

        <div class="activity-card">
            <div class="activity-head">
                <h4><i class="fas fa-clock" style="color:#7c3aed;margin-right:6px;font-size:.85rem;"></i> Aktivitas
                    Terbaru</h4>
                <a href="#" class="see-all">Lihat semua →</a>
            </div>

            @php
                $aktivitas = $aktivitas ?? [
                    [
                        'icon' => 'fas fa-user-plus',
                        'title' => 'Siswa baru terdaftar',
                        'subtitle' => 'Ahmad Siswa berhasil mendaftar',
                        'time' => '2 jam lalu',
                        'color' => 'c-green',
                    ],
                    [
                        'icon' => 'fas fa-clipboard-check',
                        'title' => 'Absen berhasil',
                        'subtitle' => '23 siswa sudah absen hari ini',
                        'time' => '1 jam lalu',
                        'color' => 'c-blue',
                    ],
                    [
                        'icon' => 'fas fa-user-edit',
                        'title' => 'Data GTK diperbarui',
                        'subtitle' => 'Data guru Budi Santoso telah diperbarui',
                        'time' => '3 jam lalu',
                        'color' => 'c-orange',
                    ],
                ];
            @endphp

            @forelse ($aktivitas as $act)
                <div class="activity-item">
                    <div class="act-icon {{ $act['color'] ?? 'c-blue' }}">
                        <i class="{{ $act['icon'] ?? 'fas fa-bell' }}"></i>
                    </div>
                    <div class="act-body">
                        <p class="act-title">{{ $act['title'] }}</p>
                        <p class="act-sub">{{ $act['subtitle'] }}</p>
                    </div>
                    <div class="act-time">{{ $act['time'] }}</div>
                </div>
            @empty
                <div class="empty-activity">
                    <i class="fas fa-inbox"></i>
                    Belum ada aktivitas hari ini
                </div>
            @endforelse
        </div>

    </div>
@endsection
