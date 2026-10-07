@extends('layouts.app')

@section('title', 'Dashboard Laporan Aktivitas')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* ═══════════════════════════════════════════════════════════
               DASHBOARD LAPORAN — RESPONSIVE ACTIVITY DASHBOARD
               Mendukung: Mobile (320px+), Tablet (768px+), Desktop (1024px+)
                ═══════════════════════════════════════════════════════════ */

        /* ── Variables ── */
        :root {
            --dl-bg: #f1f5f9;
            --dl-card: #ffffff;
            --dl-border: #e2e8f0;
            --dl-text: #0f172a;
            --dl-muted: #64748b;
            --dl-light: #94a3b8;
            --dl-radius: 14px;
            --dl-radius-sm: 10px;
            --dl-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            --dl-shadow-lg: 0 8px 30px rgba(0, 0, 0, .10);
            --c-blue: #3b82f6;
            --c-green: #22c55e;
            --c-red: #ef4444;
            --c-orange: #f97316;
            --c-purple: #8b5cf6;
            --c-teal: #14b8a6;
            --c-yellow: #eab308;
            --c-indigo: #6366f1;
            --c-pink: #ec4899;
        }

        /* ── Page wrapper ── */
        .dl-page {
            background: var(--dl-bg);
            min-height: 100vh;
            padding-top: calc(var(--header-h, 56px) + 8px);
            padding-bottom: calc(var(--footer-h, 56px) + 24px);
        }

        /* ── Page strip ── */
        .dl-strip {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
            padding: 20px 16px 60px;
            position: relative;
            overflow: hidden;
        }

        .dl-strip::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 160px;
            height: 160px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .dl-strip::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: -20px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, .04);
            border-radius: 50%;
        }

        .dl-strip h1 {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 800;
            margin: 0 0 4px;
            position: relative;
            z-index: 1;
        }

        .dl-strip p {
            color: rgba(255, 255, 255, .65);
            font-size: .75rem;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .dl-strip-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 20px;
            padding: 3px 10px;
            font-size: .68rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        /* ── Main content area ── */
        .dl-wrap {
            padding: 0 12px 24px;
            margin-top: -44px;
            position: relative;
            z-index: 5;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ── Filter card ── */
        .dl-filter {
            background: var(--dl-card);
            border: 1px solid var(--dl-border);
            border-radius: var(--dl-radius);
            padding: 14px 16px;
            margin-bottom: 16px;
            box-shadow: var(--dl-shadow);
        }

        .dl-filter-title {
            font-size: .72rem;
            font-weight: 800;
            color: var(--dl-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .dl-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 8px;
            align-items: end;
        }

        @media(max-width:380px) {
            .dl-filter-grid {
                grid-template-columns: repeat(auto-fill, minmax(105px, 1fr));
            }
        }

        .dl-filter-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .dl-filter-field label {
            font-size: .65rem;
            font-weight: 700;
            color: var(--dl-muted);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .dl-fld {
            border: 1.5px solid var(--dl-border);
            border-radius: var(--dl-radius-sm);
            padding: 8px 10px;
            font-size: .8rem;
            color: var(--dl-text);
            background: #f8fafc;
            font-family: inherit;
            outline: none;
            transition: border-color .18s, box-shadow .18s;
            width: 100%;
        }

        .dl-fld:focus {
            border-color: var(--c-indigo);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
            background: #fff;
        }

        .dl-periode-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .dl-btn-periode {
            padding: 7px 12px;
            border-radius: 20px;
            font-size: .73rem;
            font-weight: 700;
            border: 1.5px solid var(--dl-border);
            background: #f1f5f9;
            color: var(--dl-muted);
            cursor: pointer;
            font-family: inherit;
            transition: all .18s;
            white-space: nowrap;
        }

        .dl-btn-periode.active,
        .dl-btn-periode:hover {
            background: var(--c-indigo);
            color: #fff;
            border-color: var(--c-indigo);
        }

        .dl-custom-range {
            display: none;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--dl-border);
        }

        .dl-custom-range.show {
            display: grid;
        }

        .dl-filter-actions {
            display: flex;
            gap: 6px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .dl-btn-apply {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: var(--dl-radius-sm);
            font-size: .8rem;
            font-weight: 700;
            border: none;
            background: linear-gradient(135deg, #4338ca, #7c3aed);
            color: #fff;
            cursor: pointer;
            font-family: inherit;
            box-shadow: 0 2px 8px rgba(99, 102, 241, .3);
            transition: opacity .18s;
        }

        .dl-btn-apply:hover {
            opacity: .9;
        }

        .dl-btn-reset {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: var(--dl-radius-sm);
            font-size: .8rem;
            font-weight: 700;
            border: 1.5px solid var(--dl-border);
            background: #f1f5f9;
            color: var(--dl-muted);
            text-decoration: none;
            font-family: inherit;
            cursor: pointer;
            transition: all .18s;
        }

        .dl-btn-reset:hover {
            background: var(--dl-border);
        }
    </style>
@endpush

@section('content')
    <div class="dl-page">

        {{-- ── Strip header ── --}}
        <div class="dl-strip">
            <div class="dl-strip-badge">
                <i class="fas fa-chart-mixed"></i>
                Dashboard Aktivitas
            </div>
            <h1><i class="fas fa-chart-line" style="opacity:.7;margin-right:6px;"></i>Laporan Aktivitas Sistem</h1>
            <p>
                Periode:
                <strong style="color:#fff;">
                    {{ $dateFrom->translatedFormat('d M Y') }} — {{ $dateTo->translatedFormat('d M Y') }}
                </strong>
            </p>
        </div>

        <div class="dl-wrap">

            {{-- ═══════════════════════════════════════════════
             FILTER PANEL
        ═══════════════════════════════════════════════ --}}
            @include('admin.dashboard-laporan._filter')

            {{-- loading overlay --}}
            <div id="dl-loading"
                style="display:none;position:fixed;inset:0;background:rgba(255,255,255,.55);z-index:9999;align-items:center;justify-content:center;">
                <div
                    style="background:#fff;border-radius:12px;padding:20px 28px;box-shadow:0 8px 32px rgba(0,0,0,.15);display:flex;align-items:center;gap:12px;font-size:.9rem;font-weight:700;color:#312e81;">
                    <div class="spinner-border spinner-border-sm" style="color:#6366f1;"></div>
                    Memuat data...
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════
             SEKSI 1 — KPI CARDS
        ═══════════════════════════════════════════════ --}}
            @include('admin.dashboard-laporan._kpi')

            {{-- ═══════════════════════════════════════════════
             SEKSI 2 — KEHADIRAN SISWA
        ═══════════════════════════════════════════════ --}}
            @include('admin.dashboard-laporan._kehadiran')

            {{-- ═══════════════════════════════════════════════
             SEKSI 3 — PENGAJUAN IZIN
        ═══════════════════════════════════════════════ --}}
            @include('admin.dashboard-laporan._izin')

            {{-- ═══════════════════════════════════════════════
             SEKSI 4 — TATIB (PELANGGARAN & PENGHARGAAN)
        ═══════════════════════════════════════════════ --}}
            @include('admin.dashboard-laporan._tatib')

            {{-- ═══════════════════════════════════════════════
             SEKSI 5 — EVENT SISWA & GURU
        ═══════════════════════════════════════════════ --}}
            @include('admin.dashboard-laporan._event')

            {{-- ═══════════════════════════════════════════════
             SEKSI 6 — LAPORAN KEHADIRAN GURU (KBM)
        ═══════════════════════════════════════════════ --}}
            @include('admin.dashboard-laporan._guru')

            {{-- ═══════════════════════════════════════════════
             SEKSI 7 — WHATSAPP & NOTIFIKASI
        ═══════════════════════════════════════════════ --}}
            @include('admin.dashboard-laporan._whatsapp')

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js"></script>
    @include('admin.dashboard-laporan._scripts')
@endpush
