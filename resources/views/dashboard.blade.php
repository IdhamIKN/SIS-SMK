@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <style>
        /* ── Hero / Greeting Strip ── */
        .dash-hero {
            position: relative;
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 55%, #0ea5e9 100%);
            padding: 28px 20px 64px; /* padding-bottom diperbesar agar stat card tidak hilang */
            overflow: visible; /* ubah ke visible agar stat card tidak terpotong */
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
            margin-bottom: 0;
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
            font-size: 1.15rem;
            font-weight: 800;
            color: #fff;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%; /* tidak dibatasi fix agar responsif */
        }

        .hero-role {
            font-size: .70rem;
            color: rgba(255, 255, 255, .65);
            margin-top: 3px;
        }

        .hero-avatar {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            border: 2px solid rgba(255, 255, 255, .35);
            overflow: hidden;
            flex-shrink: 0;
            margin-left: 12px;
            background: rgba(255, 255, 255, .15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.1rem;
        }

        .hero-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ── Stat cards ── */
        /* Bungkus khusus agar overlap terkontrol */
        .dash-stats-wrapper {
            position: relative;
            z-index: 10;
            margin-top: -44px; /* tarik ke atas tepat setengah tinggi card */
            padding: 0 16px;
        }

        .dash-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .dash-stats.cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 14px 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .12);
            text-align: center;
            border: 1px solid rgba(226, 232, 240, .8);
        }

        .stat-card .sc-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .88rem;
            margin: 0 auto 7px;
        }

        .stat-card .sc-val {
            font-size: 1.45rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 3px;
        }

        .stat-card .sc-lbl {
            font-size: .60rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* ── Wrapper utama ── */
        .dash-wrap {
            padding: 20px 16px calc(var(--footer-h, 56px) + 80px);
        }

        /* ── Section title ── */
        .section-title {
            font-size: .70rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 0 0 10px;
        }

        /* ── Quick action grid ── */
        .qa-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(154px, 1fr));
            gap: 10px;
        }

        .qa-grid.cols-3,
        .qa-grid.cols-4,
        .qa-grid.cols-5 {
            grid-template-columns: repeat(auto-fit, minmax(154px, 1fr));
        }

        .qa-item {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 8px;
            padding: 12px;
            text-align: left;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 72px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
            transition: transform .18s, box-shadow .18s;
        }

        .qa-item:active { transform: scale(.95); }
        .qa-item:hover  { box-shadow: 0 4px 16px rgba(0, 0, 0, .10); }

        .qa-icon {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex: 0 0 auto;
        }

        .qa-text {
            min-width: 0;
            flex: 1;
        }

        .qa-label {
            font-size: .75rem;
            font-weight: 800;
            color: var(--text-main, #0f172a);
            line-height: 1.25;
        }

        .qa-desc {
            color: #64748b;
            font-size: .64rem;
            font-weight: 600;
            line-height: 1.3;
            margin-top: 3px;
        }

        .home-menu-titlebar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .home-menu-titlebar .section-title {
            margin: 0;
        }

        .home-menu-titlebar span {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 4px 9px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #64748b;
            font-size: .66rem;
            font-weight: 800;
        }

        /* Home menu grouping */
        .home-menu-group {
            margin-bottom: 18px;
        }

        .home-menu-group:last-child {
            margin-bottom: 0;
        }

        .home-menu-group-head {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 9px;
        }

        .home-menu-group-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: .76rem;
        }

        .home-menu-group-head h3 {
            margin: 0;
            color: #334155;
            font-size: .77rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .045em;
        }

        .home-menu-group-head p {
            margin: 1px 0 0;
            color: #94a3b8;
            font-size: .66rem;
            line-height: 1.35;
        }

        /* Color tokens */
        .c-green  { background: #dcfce7; color: #15803d; }
        .c-blue   { background: #dbeafe; color: #1d4ed8; }
        .c-red    { background: #fee2e2; color: #dc2626; }
        .c-orange { background: #ffedd5; color: #c2410c; }
        .c-purple { background: #ede9fe; color: #7c3aed; }
        .c-teal   { background: #ccfbf1; color: #0f766e; }
        .c-yellow { background: #fef9c3; color: #a16207; }
        .c-indigo { background: #e0e7ff; color: #4338ca; }

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

        .activity-item:last-child { border-bottom: none; }
        .activity-item:hover { background: #fafbfc; }

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

        /* ── Info banner ── */
        .info-banner {
            display: flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #ede9fe, #dbeafe);
            border: 1px solid #c4b5fd;
            border-radius: 14px;
            padding: 14px;
            text-decoration: none;
            margin-bottom: 16px;
            transition: opacity .18s;
        }

        .info-banner:active { opacity: .85; }

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

        .ib-body { flex: 1; min-width: 0; }

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
                    $hour  = now()->hour;
                    $greet = $hour < 11 ? 'Selamat Pagi'
                           : ($hour < 15 ? 'Selamat Siang'
                           : ($hour < 18 ? 'Selamat Sore'
                           : 'Selamat Malam'));
                    $user  = auth()->user();
                @endphp
                <h2 class="hero-name">{{ $greet }}, {{ Str::words($user->name, 2, '') }}</h2>
                <div class="hero-role">
                    @hasrole('siswa')
                        Siswa
                    @elsehasrole('gtk')
                        Guru / GTK
                    @elsehasrole('waka')
                        Waka
                    @elsehasrole('kepala_sekolah')
                        Kepala Sekolah
                    @else
                        Administrator
                    @endhasrole
                    &nbsp;·&nbsp; {{ config('sekolah.nama', 'SMKN 5 Madiun') }}
                </div>
            </div>
            <div class="hero-avatar">
                @if ($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}">
                @else
                    <i class="fas fa-user"></i>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Stat Cards (wrapper terpisah, overlap hero dengan margin negatif terkontrol) ── --}}
    <div class="dash-stats-wrapper">
        @hasrole('siswa')
            <div class="dash-stats">
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
            <div class="dash-stats">
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
    </div>

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

        {{-- Banner Event Aktif + Tombol Absen untuk Guru --}}
        @hasrole('gtk')
            @if (!empty($eventAktifGuru))
                <p class="section-title">Event Berlangsung</p>
                <div class="info-banner" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border-color: #93c5fd; flex-direction: column; align-items: stretch; gap: 10px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="ib-icon" style="background:#2563eb;">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="ib-body">
                            <div class="ib-title" style="color:#1e3a8a;">{{ Str::limit($eventAktifGuru->nama_event, 32) }}</div>
                            <div class="ib-sub" style="color:#1d4ed8;">
                                <i class="fas fa-clock" style="font-size:.65rem;"></i>
                                Sampai {{ $eventAktifGuru->tanggal_selesai->format('H:i') }}
                                &nbsp;·&nbsp; {{ $eventAktifGuru->tanggal_selesai->format('d M Y') }}
                            </div>
                        </div>
                        <a href="{{ route('event-guru.show', $eventAktifGuru) }}" style="color:#2563eb; font-size:.85rem; flex-shrink:0;">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    @if (!empty($guruAbsenNext))
                        <a href="{{ route('event-guru.scan', ['eventGuru' => $eventAktifGuru, 'jenis' => $guruAbsenNext]) }}"
                           style="display:flex; align-items:center; justify-content:center; gap:8px;
                                  background:#2563eb; color:#fff; border-radius:10px;
                                  padding:10px 16px; font-size:.82rem; font-weight:700;
                                  text-decoration:none; transition:opacity .18s;"
                           onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                            <i class="fas fa-qrcode"></i>
                            Scan Absen {{ $guruAbsenNext === 'masuk' ? 'Masuk' : 'Pulang' }} Sekarang
                        </a>
                    @else
                        <div style="display:flex; align-items:center; justify-content:center; gap:8px;
                                    background:#dcfce7; color:#15803d; border-radius:10px;
                                    padding:10px 16px; font-size:.82rem; font-weight:700;">
                            <i class="fas fa-check-circle"></i> Absen sudah selesai
                        </div>
                    @endif
                </div>
            @endif
        @endhasrole

        {{-- ── Home Menu (role-based) ── --}}
        @include('components.azures.home-menu')

    </div>
@endsection
