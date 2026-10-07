@extends('layouts.app')

@section('title', 'Cetak Jurnal Laporan Tatib')

@push('styles')
    <style>
        /* ─────────────────────────────────────────────────────────────────────────
       TATIB-CETAK — Cetak Jurnal Laporan Tatib
       Semua selector diawali .tcjrn- agar tidak bocor ke layout global
    ──────────────────────────────────────────────────────────────────────────*/

        :root {
            --tcj-primary: #7c3aed;
            --tcj-primary-dark: #5b21b6;
            --tcj-surface: #ffffff;
            --tcj-bg: #f5f3ff;
            --tcj-border: #e2e8f0;
            --tcj-text: #0f172a;
            --tcj-muted: #64748b;
            --tcj-subtle: #94a3b8;
            --tcj-radius-card: 14px;
            --tcj-radius-input: 9px;
            --tcj-shadow-card: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 8px rgba(0, 0, 0, .04);
        }

        /* ── Page wrapper ─────────────────────────────────────────────────── */
        .tcjrn-pg {
            font-family: inherit;
            min-height: 100vh;
            background: var(--tcj-bg);
            padding-top: var(--header-h, 56px);
            padding-bottom: calc(80px + env(safe-area-inset-bottom, 0px));
        }

        /* ── Container ────────────────────────────────────────────────────── */
        .tcjrn-container {
            max-width: 560px;
            margin: 0 auto;
            padding: 0 16px;
        }

        /* ── Hero Strip ───────────────────────────────────────────────────── */
        .tcjrn-strip {
            padding: 20px 20px 24px;
            background: linear-gradient(135deg, #1e1b4b 0%, #3b1f8c 55%, #7c3aed 100%);
            position: relative;
            overflow: hidden;
            margin-bottom: 18px;
        }

        .tcjrn-strip::before {
            content: '';
            position: absolute;
            top: -44px;
            right: -28px;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
            pointer-events: none;
        }

        .tcjrn-strip::after {
            content: '';
            position: absolute;
            bottom: -28px;
            left: -16px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, .04);
            border-radius: 50%;
            pointer-events: none;
        }

        .tcjrn-live {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 700;
            color: rgba(255, 255, 255, .9);
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .tcjrn-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #c4b5fd;
            animation: tcjrn-pulse 2.2s ease-in-out infinite;
        }

        @keyframes tcjrn-pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .4;
                transform: scale(.8);
            }
        }

        .tcjrn-strip h2 {
            font-size: 1.2rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 5px;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 8px;
            line-height: 1.3;
        }

        .tcjrn-strip h2 i {
            font-size: 1rem;
            opacity: .85;
        }

        .tcjrn-strip p {
            font-size: .8rem;
            color: rgba(255, 255, 255, .6);
            margin: 0;
            position: relative;
            z-index: 1;
        }

        /* ── Error Alert ──────────────────────────────────────────────────── */
        .tcjrn-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: var(--tcj-radius-input);
            font-size: .83rem;
            margin-bottom: 14px;
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .tcjrn-alert ul {
            margin: 5px 0 0 16px;
            padding: 0;
            font-size: .79rem;
        }

        /* ── Card ─────────────────────────────────────────────────────────── */
        .tcjrn-card {
            background: var(--tcj-surface);
            border: 1px solid var(--tcj-border);
            border-radius: var(--tcj-radius-card);
            box-shadow: var(--tcj-shadow-card);
            overflow: hidden;
            margin-bottom: 14px;
        }

        /* Card header */
        .tcjrn-chead {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px 12px;
            border-bottom: 1px solid #f8fafc;
        }

        .tcjrn-cico {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: #ede9fe;
            color: var(--tcj-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            flex-shrink: 0;
        }

        .tcjrn-chead h3 {
            margin: 0;
            font-size: .88rem;
            font-weight: 700;
            color: var(--tcj-text);
            flex: 1;
        }

        /* Card body */
        .tcjrn-cbody {
            padding: 16px;
        }

        /* ── Form Grid — 2 kolom di ≥ 480px, 1 kolom di mobile ─────────── */
        .tcjrn-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        /* Full-width item dalam grid */
        .tcjrn-full {
            grid-column: 1 / -1;
        }

        /* ── Field ────────────────────────────────────────────────────────── */
        .tcjrn-fg {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .tcjrn-lbl {
            font-size: .78rem;
            font-weight: 700;
            color: var(--tcj-text);
        }

        .tcjrn-lbl .req {
            color: #ef4444;
            font-weight: 800;
            margin-left: 2px;
        }

        /* ── Input / Select / Textarea ────────────────────────────────────── */
        .tcjrn-inp,
        .tcjrn-sel {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid var(--tcj-border);
            border-radius: var(--tcj-radius-input);
            font-size: .875rem;
            font-family: inherit;
            color: var(--tcj-text);
            background: #f8fafc;
            outline: none;
            box-sizing: border-box;
            -webkit-appearance: none;
            transition: border-color .18s, box-shadow .18s, background .18s;
            line-height: 1.5;
        }

        .tcjrn-inp:focus,
        .tcjrn-sel:focus {
            border-color: var(--tcj-primary);
            background: var(--tcj-surface);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .12);
        }

        /* Select chevron */
        .tcjrn-sel {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
            cursor: pointer;
        }

        .tcjrn-inp.iserr,
        .tcjrn-sel.iserr {
            border-color: #ef4444;
        }

        /* Field error */
        .tcjrn-ferr {
            font-size: .71rem;
            color: #dc2626;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Divider ──────────────────────────────────────────────────────── */
        .tcjrn-divider {
            height: 1px;
            background: #f1f5f9;
            margin: 16px 0;
        }

        /* ── Rentang Tanggal — label connector ───────────────────────────── */
        .tcjrn-date-row {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: end;
            gap: 8px;
        }

        .tcjrn-date-sep {
            font-size: .75rem;
            font-weight: 700;
            color: var(--tcj-muted);
            padding-bottom: 10px;
            text-align: center;
            white-space: nowrap;
        }

        /* ── Jenis radio pills ────────────────────────────────────────────── */
        .tcjrn-pill-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .tcjrn-pill {
            position: relative;
            cursor: pointer;
        }

        .tcjrn-pill input[type="radio"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            z-index: 2;
            margin: 0;
        }

        .tcjrn-pill-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            padding: 10px 8px;
            border: 1.5px solid var(--tcj-border);
            border-radius: 10px;
            background: #f8fafc;
            font-size: .78rem;
            font-weight: 600;
            color: #475569;
            transition: border-color .15s, background .15s, color .15s;
            text-align: center;
            line-height: 1.3;
            pointer-events: none;
            position: relative;
            z-index: 1;
        }

        .tcjrn-pill-ico {
            font-size: .9rem;
        }

        .tcjrn-pill input:checked~.tcjrn-pill-box {
            border-color: var(--tcj-primary);
            background: #ede9fe;
            color: var(--tcj-primary-dark);
        }

        .tcjrn-pill:focus-within .tcjrn-pill-box {
            outline: 2px solid var(--tcj-primary);
            outline-offset: 2px;
        }

        /* ── Action Bar (fixed) ───────────────────────────────────────────── */
        .tcjrn-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 16px;
            padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px));
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid var(--tcj-border);
            display: flex;
            gap: 10px;
            z-index: 100;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .07);
        }

        .tcjrn-back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px 16px;
            border-radius: 11px;
            font-size: .88rem;
            font-weight: 700;
            border: 1.5px solid var(--tcj-border);
            background: var(--tcj-surface);
            color: var(--tcj-muted);
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            transition: background .15s, color .15s;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .tcjrn-back-btn:hover {
            background: #f1f5f9;
            color: var(--tcj-text);
        }

        .tcjrn-submit-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 16px;
            border-radius: 11px;
            font-size: .9rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: inherit;
            background: linear-gradient(135deg, var(--tcj-primary-dark), var(--tcj-primary));
            color: #fff;
            box-shadow: 0 3px 12px rgba(124, 58, 237, .3);
            transition: filter .18s, transform .12s;
        }

        .tcjrn-submit-btn:hover {
            filter: brightness(1.07);
        }

        .tcjrn-submit-btn:active {
            transform: scale(.98);
        }

        .tcjrn-submit-btn:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

        /* ═══════════════════════════════════════════════════════════════════
       RESPONSIVE
    ═══════════════════════════════════════════════════════════════════ */

        @media (max-width: 520px) {
            .tcjrn-container {
                padding: 0 12px;
            }

            .tcjrn-strip {
                padding: 16px 16px 20px;
                margin-bottom: 14px;
            }

            .tcjrn-strip h2 {
                font-size: 1.05rem;
            }

            .tcjrn-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .tcjrn-pill-group {
                grid-template-columns: repeat(3, 1fr);
            }

            .tcjrn-cbody {
                padding: 14px;
            }

            .tcjrn-bar {
                padding: 10px 14px 14px;
            }

            .tcjrn-back-btn {
                padding: 12px 13px;
                font-size: .82rem;
            }

            .tcjrn-submit-btn {
                font-size: .86rem;
            }
        }

        @media (max-width: 380px) {
            .tcjrn-pill-group {
                grid-template-columns: 1fr;
            }

            .tcjrn-pill-box {
                flex-direction: row;
                justify-content: center;
                gap: 8px;
            }

            .tcjrn-date-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .tcjrn-date-sep {
                display: none;
            }
        }

        /* ── Accessibility ────────────────────────────────────────────────── */
        @media (prefers-contrast: high) {
            .tcjrn-card {
                border-width: 2px;
            }

            .tcjrn-inp,
            .tcjrn-sel {
                border-width: 2px;
            }

            .tcjrn-pill-box {
                border-width: 2px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .tcjrn-dot,
            .tcjrn-inp,
            .tcjrn-sel,
            .tcjrn-pill-box,
            .tcjrn-submit-btn,
            .tcjrn-back-btn {
                animation: none !important;
                transition: none !important;
            }
        }

        /* ── Toggle switch ────────────────────────────────────────────────── */
        .tcjrn-toggle {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
            user-select: none;
            -webkit-user-select: none;
        }

        .tcjrn-toggle input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            pointer-events: none;
        }

        .tcjrn-toggle-track {
            position: relative;
            flex-shrink: 0;
            width: 40px;
            height: 22px;
            background: #cbd5e1;
            border-radius: 11px;
            transition: background .2s;
            margin-top: 1px;
        }

        .tcjrn-toggle-thumb {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 16px;
            height: 16px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0,0,0,.2);
            transition: transform .2s;
        }

        .tcjrn-toggle input:checked ~ .tcjrn-toggle-track {
            background: var(--tcj-primary);
        }

        .tcjrn-toggle input:checked ~ .tcjrn-toggle-track .tcjrn-toggle-thumb {
            transform: translateX(18px);
        }

        .tcjrn-toggle:focus-within .tcjrn-toggle-track {
            outline: 2px solid var(--tcj-primary);
            outline-offset: 2px;
        }

        .tcjrn-toggle-lbl {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: .83rem;
            font-weight: 700;
            color: var(--tcj-text);
            line-height: 1.3;
        }

        .tcjrn-toggle-sub {
            font-size: .72rem;
            font-weight: 400;
            color: var(--tcj-muted);
        }

        .tcjrn-toggle-hint {
            font-size: .71rem;
            color: var(--tcj-subtle);
            margin-top: 6px;
            display: flex;
            align-items: flex-start;
            gap: 5px;
            line-height: 1.4;
        }

        .tcjrn-toggle-hint i { margin-top: 1px; flex-shrink: 0; }

        /* ── Cetak Per Siswa ──────────────────────────────────────────────── */
        .tcjrn-divider-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 6px 0 14px;
            font-size: .72rem;
            font-weight: 700;
            color: var(--tcj-subtle);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .tcjrn-divider-label::before,
        .tcjrn-divider-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--tcj-border);
        }

        /* Search wrapper */
        .tcjrn-search-wrap {
            position: relative;
        }

        .tcjrn-search-wrap .tcjrn-search-ico {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--tcj-subtle);
            font-size: .78rem;
            pointer-events: none;
        }

        .tcjrn-search-wrap .tcjrn-inp {
            padding-left: 32px;
            padding-right: 32px;
        }

        .tcjrn-search-wrap .tcjrn-clear-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--tcj-subtle);
            font-size: .8rem;
            padding: 2px 4px;
            line-height: 1;
            display: none;
        }

        .tcjrn-search-wrap .tcjrn-clear-btn:hover { color: #ef4444; }

        /* Dropdown hasil pencarian */
        .tcjrn-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1.5px solid var(--tcj-border);
            border-radius: var(--tcj-radius-input);
            box-shadow: 0 4px 16px rgba(0,0,0,.10);
            z-index: 500;
            max-height: 220px;
            overflow-y: auto;
            display: none;
        }

        .tcjrn-dropdown.open { display: block; }

        .tcjrn-dd-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            transition: background .12s;
        }

        .tcjrn-dd-item:last-child { border-bottom: none; }
        .tcjrn-dd-item:hover,
        .tcjrn-dd-item.focused { background: #f5f3ff; }

        .tcjrn-dd-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #ede9fe;
            color: var(--tcj-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .tcjrn-dd-main {
            flex: 1;
            min-width: 0;
        }

        .tcjrn-dd-nama {
            font-size: .82rem;
            font-weight: 700;
            color: var(--tcj-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tcjrn-dd-sub {
            font-size: .7rem;
            color: var(--tcj-muted);
            margin-top: 1px;
        }

        .tcjrn-dd-empty {
            padding: 14px 12px;
            font-size: .8rem;
            color: var(--tcj-subtle);
            text-align: center;
        }

        .tcjrn-dd-loading {
            padding: 14px 12px;
            font-size: .8rem;
            color: var(--tcj-subtle);
            text-align: center;
        }

        /* Chip siswa terpilih */
        .tcjrn-chip {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #ede9fe;
            border: 1.5px solid #c4b5fd;
            border-radius: 9px;
            padding: 8px 12px;
            margin-top: 8px;
        }

        .tcjrn-chip-ico {
            color: var(--tcj-primary);
            font-size: .85rem;
            flex-shrink: 0;
        }

        .tcjrn-chip-info {
            flex: 1;
            min-width: 0;
        }

        .tcjrn-chip-nama {
            font-size: .83rem;
            font-weight: 700;
            color: var(--tcj-primary-dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tcjrn-chip-sub {
            font-size: .7rem;
            color: #7c3aed;
            margin-top: 1px;
        }

        .tcjrn-chip-remove {
            background: none;
            border: none;
            cursor: pointer;
            color: #7c3aed;
            font-size: .75rem;
            padding: 2px 4px;
            flex-shrink: 0;
            border-radius: 4px;
            transition: background .12s;
        }

        .tcjrn-chip-remove:hover { background: #ddd6fe; }

        /* Tombol cetak per siswa */
        .tcjrn-siswa-btn {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 11px 16px;
            border-radius: 11px;
            font-size: .88rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: inherit;
            background: linear-gradient(135deg, #065f46, #059669);
            color: #fff;
            box-shadow: 0 3px 10px rgba(5,150,105,.25);
            transition: filter .18s, transform .12s;
            margin-top: 10px;
        }

        .tcjrn-siswa-btn:hover  { filter: brightness(1.08); }
        .tcjrn-siswa-btn:active { transform: scale(.98); }
        .tcjrn-siswa-btn:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none;
        }
    </style>
@endpush

@section('content')
    <div class="tcjrn-pg">

        {{-- ── Hero Strip ────────────────────────────────────────────── --}}
        <div class="tcjrn-strip">
            <div class="tcjrn-live"><span class="tcjrn-dot"></span>{{ now()->translatedFormat('l, d F Y') }}</div>
            <h2><i class="fas fa-print" aria-hidden="true"></i> Cetak Jurnal Laporan Tatib</h2>
            <p>Cetak laporan pelanggaran / penghargaan per rentang tanggal</p>
        </div>

        <div class="tcjrn-container">

            {{-- Error alert --}}
            @if ($errors->any())
                <div class="tcjrn-alert" role="alert">
                    <i class="fas fa-exclamation-circle" aria-hidden="true" style="margin-top:2px;flex-shrink:0;"></i>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="tcjrnForm" method="GET" action="{{ route('admin.tatib.jurnal.cetak') }}" novalidate>

                {{-- ── Kartu: Rentang Tanggal ──────────────────────── --}}
                <div class="tcjrn-card">
                    <div class="tcjrn-chead">
                        <div class="tcjrn-cico"><i class="fas fa-calendar-alt" aria-hidden="true"></i></div>
                        <h3>Rentang Tanggal</h3>
                    </div>
                    <div class="tcjrn-cbody">
                        <div class="tcjrn-date-row">
                            <div class="tcjrn-fg">
                                <label class="tcjrn-lbl" for="dari_tanggal">Dari Tanggal <span
                                        class="req">*</span></label>
                                <input type="date" id="dari_tanggal" name="dari_tanggal"
                                    class="tcjrn-inp @error('dari_tanggal') iserr @enderror"
                                    value="{{ old('dari_tanggal', now()->startOfMonth()->toDateString()) }}" required>
                                @error('dari_tanggal')
                                    <span class="tcjrn-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="tcjrn-date-sep" aria-hidden="true">s.d.</div>

                            <div class="tcjrn-fg">
                                <label class="tcjrn-lbl" for="sampai_tanggal">Sampai Tanggal <span
                                        class="req">*</span></label>
                                <input type="date" id="sampai_tanggal" name="sampai_tanggal"
                                    class="tcjrn-inp @error('sampai_tanggal') iserr @enderror"
                                    value="{{ old('sampai_tanggal', now()->toDateString()) }}" required>
                                @error('sampai_tanggal')
                                    <span class="tcjrn-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Kartu: Parameter Laporan ─────────────────────── --}}
                <div class="tcjrn-card">
                    <div class="tcjrn-chead">
                        <div class="tcjrn-cico"><i class="fas fa-sliders-h" aria-hidden="true"></i></div>
                        <h3>Parameter Laporan</h3>
                    </div>
                    <div class="tcjrn-cbody">

                        {{-- Jenis Laporan — radio pills --}}
                        <div class="tcjrn-fg" style="margin-bottom:16px;">
                            <label class="tcjrn-lbl">Jenis Laporan <span class="req">*</span></label>
                            <div class="tcjrn-pill-group" role="group" aria-label="Pilih jenis laporan">

                                <label class="tcjrn-pill">
                                    <input type="radio" name="jenis" value="semua"
                                        {{ old('jenis', 'semua') === 'semua' ? 'checked' : '' }} aria-label="Semua">
                                    <div class="tcjrn-pill-box">
                                        <span class="tcjrn-pill-ico">📋</span>
                                        Semua
                                    </div>
                                </label>

                                <label class="tcjrn-pill">
                                    <input type="radio" name="jenis" value="pelanggaran"
                                        {{ old('jenis') === 'pelanggaran' ? 'checked' : '' }} aria-label="Pelanggaran">
                                    <div class="tcjrn-pill-box">
                                        <span class="tcjrn-pill-ico">⚠️</span>
                                        Pelanggaran
                                    </div>
                                </label>

                                <label class="tcjrn-pill">
                                    <input type="radio" name="jenis" value="penghargaan"
                                        {{ old('jenis') === 'penghargaan' ? 'checked' : '' }} aria-label="Penghargaan">
                                    <div class="tcjrn-pill-box">
                                        <span class="tcjrn-pill-ico">🏆</span>
                                        Penghargaan
                                    </div>
                                </label>

                            </div>
                            @error('jenis')
                                <span class="tcjrn-ferr"><i class="fas fa-exclamation-circle"
                                        aria-hidden="true"></i>{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="tcjrn-divider"></div>

                        {{-- Opsi Tampilan Detail --}}
                        <div class="tcjrn-fg" style="margin-bottom:14px;">
                            <label class="tcjrn-lbl">Opsi Tampilan</label>
                            <label class="tcjrn-toggle" for="show_detail">
                                <input type="checkbox" id="show_detail" name="show_detail" value="1"
                                    {{ old('show_detail') ? 'checked' : '' }}>
                                <span class="tcjrn-toggle-track">
                                    <span class="tcjrn-toggle-thumb"></span>
                                </span>
                                <span class="tcjrn-toggle-lbl">
                                    Tampilkan Detail
                                    <span class="tcjrn-toggle-sub">Kolom Tanggal, Jenis / Pasal, dan Poin</span>
                                </span>
                            </label>
                            <p class="tcjrn-toggle-hint">
                                <i class="fas fa-info-circle" aria-hidden="true"></i>
                                Default: detail disembunyikan agar hasil cetak lebih ringkas.
                                Centang untuk melihat rincian lengkap per transaksi.
                            </p>
                        </div>

                        <div class="tcjrn-divider"></div>

                        {{-- Kelas & Tahun Ajaran --}}
                        <div class="tcjrn-grid">
                            <div class="tcjrn-fg">
                                <label class="tcjrn-lbl" for="kelas_id">Kelas</label>
                                <select id="kelas_id" name="kelas_id"
                                    class="tcjrn-sel @error('kelas_id') iserr @enderror">
                                    <option value="">Semua Kelas</option>
                                    @foreach ($kelas as $item)
                                        <option value="{{ $item->id }}" @selected((string) old('kelas_id') === (string) $item->id)>
                                            {{ $item->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kelas_id')
                                    <span class="tcjrn-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="tcjrn-fg">
                                <label class="tcjrn-lbl" for="tahun_ajaran">Tahun Ajaran <span
                                        class="req">*</span></label>
                                <input type="text" id="tahun_ajaran" name="tahun_ajaran"
                                    class="tcjrn-inp @error('tahun_ajaran') iserr @enderror"
                                    value="{{ old('tahun_ajaran', $tahunAjaran) }}" placeholder="2024/2025"
                                    maxlength="9" required>
                                @error('tahun_ajaran')
                                    <span class="tcjrn-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>

            </form>
        </div>{{-- /.tcjrn-container --}}

        {{-- ══ CETAK PER SISWA ══════════════════════════════════════════ --}}
        <div class="tcjrn-container" style="margin-top:0;">

            <div class="tcjrn-divider-label">atau cetak per siswa</div>

            <div class="tcjrn-card">
                <div class="tcjrn-chead">
                    <div class="tcjrn-cico" style="background:#d1fae5;color:#059669;">
                        <i class="fas fa-user-graduate" aria-hidden="true"></i>
                    </div>
                    <h3>Cetak Per Siswa</h3>
                </div>
                <div class="tcjrn-cbody">

                    {{-- Pencarian siswa --}}
                    <div class="tcjrn-fg" style="margin-bottom:12px;">
                        <label class="tcjrn-lbl" for="siswa_search_input">
                            Cari Siswa <span class="req">*</span>
                        </label>
                        <div class="tcjrn-search-wrap" id="siswaSearchWrap">
                            <i class="fas fa-search tcjrn-search-ico" aria-hidden="true"></i>
                            <input type="text" id="siswa_search_input" autocomplete="off"
                                class="tcjrn-inp"
                                placeholder="Ketik nama, NIS, atau NISN…"
                                aria-label="Cari nama siswa"
                                aria-autocomplete="list"
                                aria-controls="siswaDropdown"
                                aria-expanded="false">
                            <button type="button" class="tcjrn-clear-btn" id="siswaClearBtn"
                                aria-label="Hapus pilihan siswa" title="Hapus">
                                <i class="fas fa-times" aria-hidden="true"></i>
                            </button>
                            <div class="tcjrn-dropdown" id="siswaDropdown" role="listbox"></div>
                        </div>

                        {{-- Chip siswa terpilih --}}
                        <div id="siswaChip" style="display:none;">
                            <div class="tcjrn-chip">
                                <span class="tcjrn-chip-ico"><i class="fas fa-user-circle" aria-hidden="true"></i></span>
                                <div class="tcjrn-chip-info">
                                    <div class="tcjrn-chip-nama" id="chipNama">—</div>
                                    <div class="tcjrn-chip-sub" id="chipSub">—</div>
                                </div>
                                <button type="button" class="tcjrn-chip-remove" id="siswaRemoveBtn"
                                    aria-label="Batalkan pilihan siswa">
                                    <i class="fas fa-times" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <input type="hidden" id="siswa_id_hidden" name="siswa_id_hidden" value="">
                    </div>

                    {{-- Opsi Tampilan Detail (khusus Cetak Per Siswa) --}}
                    <div class="tcjrn-fg" style="margin-bottom:12px;">
                        <label class="tcjrn-toggle" for="show_detail_siswa">
                            <input type="checkbox" id="show_detail_siswa" name="show_detail_siswa" value="1">
                            <span class="tcjrn-toggle-track">
                                <span class="tcjrn-toggle-thumb"></span>
                            </span>
                            <span class="tcjrn-toggle-lbl">
                                Tampilkan Detail Pelanggaran &amp; Penghargaan
                                <span class="tcjrn-toggle-sub">Kolom Tanggal, Jenis / Pasal, dan Poin per transaksi</span>
                            </span>
                        </label>
                    </div>

                    {{-- Tombol cetak siswa — submit ke form terpisah --}}
                    <button type="button" id="cetakSiswaBtn" class="tcjrn-siswa-btn" disabled>
                        <i class="fas fa-file-pdf" aria-hidden="true"></i>
                        Cetak Jurnal Siswa Ini
                    </button>

                    <p style="font-size:.7rem;color:var(--tcj-subtle);margin-top:8px;text-align:center;">
                        Menggunakan parameter Rentang Tanggal, Jenis, &amp; Tahun Ajaran dari form di atas
                    </p>
                </div>
            </div>

        </div>{{-- /.tcjrn-container siswa --}}

        {{-- Form tersembunyi untuk submit cetak-siswa --}}
        <form id="cetakSiswaForm" method="GET"
              action="{{ route('admin.tatib.jurnal.cetak-siswa') }}"
              style="display:none;">
            <input type="hidden" name="siswa_id"       id="fs_siswa_id">
            <input type="hidden" name="dari_tanggal"   id="fs_dari">
            <input type="hidden" name="sampai_tanggal" id="fs_sampai">
            <input type="hidden" name="jenis"          id="fs_jenis">
            <input type="hidden" name="tahun_ajaran"   id="fs_tahun">
            <input type="hidden" name="show_detail"    id="fs_show_detail">
        </form>

        {{-- ── Fixed Action Bar ─────────────────────────────────────── --}}
        <div class="tcjrn-bar">
            <a href="{{ url()->previous() }}" class="tcjrn-back-btn">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
            </a>
            <button type="submit" form="tcjrnForm" class="tcjrn-submit-btn" id="tcjrnSubmitBtn">
                <i class="fas fa-print" aria-hidden="true"></i> Cetak Jurnal
            </button>
        </div>

    </div>{{-- /.tcjrn-pg --}}
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            /* ── Header active state ────────────────────────────────── */
            var h = document.querySelector('.header-auto-show');
            if (h) h.classList.add('header-active');

            /* ── Footer height CSS variable ─────────────────────────── */
            var fb = document.getElementById('footer-bar');
            if (fb) {
                document.documentElement.style.setProperty('--footer-h', (fb.offsetHeight || 60) + 'px');
                window.addEventListener('resize', function() {
                    document.documentElement.style.setProperty('--footer-h', (fb.offsetHeight || 60) + 'px');
                }, { passive: true });
            }

            /* ── Form validation ────────────────────────────────────── */
            var form      = document.getElementById('tcjrnForm');
            var submitBtn = document.getElementById('tcjrnSubmitBtn');
            var origLabel = submitBtn.innerHTML;

            form.addEventListener('submit', function(e) {
                form.querySelectorAll('.tcjrn-ferr.js-err').forEach(function(el) { el.remove(); });
                form.querySelectorAll('.iserr').forEach(function(el) { el.classList.remove('iserr'); });

                var hasErr = false;
                function addErr(field, msg) {
                    field.classList.add('iserr');
                    var d = document.createElement('span');
                    d.className = 'tcjrn-ferr js-err';
                    d.setAttribute('role', 'alert');
                    d.innerHTML = '<i class="fas fa-exclamation-circle" aria-hidden="true"></i>' + msg;
                    field.parentNode.appendChild(d);
                    hasErr = true;
                }

                var dari   = document.getElementById('dari_tanggal');
                var sampai = document.getElementById('sampai_tanggal');
                var ta     = document.getElementById('tahun_ajaran');

                if (!dari.value) addErr(dari, 'Tanggal mulai wajib diisi');
                if (!sampai.value) {
                    addErr(sampai, 'Tanggal akhir wajib diisi');
                } else if (dari.value && sampai.value < dari.value) {
                    addErr(sampai, 'Tanggal akhir tidak boleh sebelum tanggal mulai');
                }
                if (!ta.value.trim()) {
                    addErr(ta, 'Tahun ajaran wajib diisi');
                } else if (!/^\d{4}\/\d{4}$/.test(ta.value.trim())) {
                    addErr(ta, 'Format: 2024/2025');
                }

                var jenisChecked = form.querySelector('input[name="jenis"]:checked');
                if (!jenisChecked) {
                    var pillGroup = form.querySelector('.tcjrn-pill-group');
                    var d2 = document.createElement('span');
                    d2.className = 'tcjrn-ferr js-err';
                    d2.setAttribute('role', 'alert');
                    d2.innerHTML = '<i class="fas fa-exclamation-circle" aria-hidden="true"></i>Pilih jenis laporan';
                    pillGroup.parentNode.appendChild(d2);
                    hasErr = true;
                }

                if (hasErr) {
                    e.preventDefault();
                    var first = form.querySelector('.iserr');
                    if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Memproses…';
                submitBtn.disabled  = true;
            });

            if (document.querySelector('.iserr')) {
                submitBtn.innerHTML = origLabel;
                submitBtn.disabled  = false;
            }

            /* ── Auto-format Tahun Ajaran ───────────────────────────── */
            var taInput = document.getElementById('tahun_ajaran');
            taInput.addEventListener('input', function() {
                var v = this.value.replace(/[^0-9\/]/g, '');
                if (v.length === 4 && !v.includes('/')) v = v + '/';
                if (v.length > 9) v = v.slice(0, 9);
                this.value = v;
            });

            /* ══ CETAK PER SISWA — live search autocomplete ════════════ */
            var API      = '{{ route('admin.tatib.jurnal.siswa-search') }}';
            var inp      = document.getElementById('siswa_search_input');
            var drop     = document.getElementById('siswaDropdown');
            var chip     = document.getElementById('siswaChip');
            var clearBtn = document.getElementById('siswaClearBtn');
            var removeBtn= document.getElementById('siswaRemoveBtn');
            var hiddenId = document.getElementById('siswa_id_hidden');
            var chipNama = document.getElementById('chipNama');
            var chipSub  = document.getElementById('chipSub');
            var cetakBtn = document.getElementById('cetakSiswaBtn');

            var timer    = null;
            var focusIdx = -1;
            var results  = [];

            function initials(name) {
                return name.trim().split(/\s+/).slice(0, 2)
                    .map(function(w) { return w[0].toUpperCase(); }).join('');
            }

            function escHtml(s) {
                return String(s)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function openDrop() {
                drop.classList.add('open');
                inp.setAttribute('aria-expanded', 'true');
            }

            function closeDrop() {
                drop.classList.remove('open');
                inp.setAttribute('aria-expanded', 'false');
                focusIdx = -1;
            }

            function setFocus(idx) {
                drop.querySelectorAll('.tcjrn-dd-item').forEach(function(el, i) {
                    el.classList.toggle('focused', i === idx);
                });
                focusIdx = idx;
            }

            function renderItems(list) {
                results  = list;
                focusIdx = -1;
                if (!list.length) {
                    drop.innerHTML = '<div class="tcjrn-dd-empty">Tidak ada siswa ditemukan</div>';
                    openDrop();
                    return;
                }
                drop.innerHTML = list.map(function(s, i) {
                    return '<div class="tcjrn-dd-item" role="option" tabindex="-1" data-idx="' + i + '">'
                        + '<div class="tcjrn-dd-avatar">' + initials(s.text) + '</div>'
                        + '<div class="tcjrn-dd-main">'
                        + '<div class="tcjrn-dd-nama">' + escHtml(s.text) + '</div>'
                        + '<div class="tcjrn-dd-sub">NISN: ' + escHtml(s.nisn) + ' &bull; ' + escHtml(s.kelas) + '</div>'
                        + '</div></div>';
                }).join('');

                drop.querySelectorAll('.tcjrn-dd-item').forEach(function(el) {
                    el.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        selectSiswa(results[parseInt(el.dataset.idx)]);
                    });
                    el.addEventListener('mouseover', function() {
                        setFocus(parseInt(el.dataset.idx));
                    });
                });
                openDrop();
            }

            function selectSiswa(s) {
                hiddenId.value         = s.id;
                chipNama.textContent   = s.text;
                chipSub.textContent    = 'NISN: ' + s.nisn + '  •  ' + s.kelas;
                chip.style.display     = 'block';
                inp.style.display      = 'none';
                clearBtn.style.display = 'none';
                cetakBtn.disabled      = false;
                closeDrop();
            }

            function clearSiswa() {
                hiddenId.value         = '';
                chip.style.display     = 'none';
                inp.style.display      = '';
                inp.value              = '';
                clearBtn.style.display = 'none';
                cetakBtn.disabled      = true;
                inp.focus();
            }

            /* Search input */
            inp.addEventListener('input', function() {
                var q = inp.value.trim();
                clearBtn.style.display = q ? '' : 'none';
                clearTimeout(timer);
                if (q.length < 1) { closeDrop(); return; }
                drop.innerHTML = '<div class="tcjrn-dd-loading"><i class="fas fa-spinner fa-spin"></i> Mencari…</div>';
                openDrop();
                timer = setTimeout(function() {
                    fetch(API + '?q=' + encodeURIComponent(q))
                        .then(function(r) { return r.json(); })
                        .then(function(d) { renderItems(d.results || []); })
                        .catch(function() { closeDrop(); });
                }, 280);
            });

            /* Keyboard navigation */
            inp.addEventListener('keydown', function(e) {
                var items = drop.querySelectorAll('.tcjrn-dd-item');
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    setFocus(Math.min(focusIdx + 1, items.length - 1));
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    setFocus(Math.max(focusIdx - 1, 0));
                } else if (e.key === 'Enter' && focusIdx >= 0) {
                    e.preventDefault();
                    if (results[focusIdx]) selectSiswa(results[focusIdx]);
                } else if (e.key === 'Escape') {
                    closeDrop();
                }
            });

            clearBtn.addEventListener('click',  function() { clearSiswa(); });
            removeBtn.addEventListener('click', function() { clearSiswa(); });

            document.addEventListener('mousedown', function(e) {
                if (!document.getElementById('siswaSearchWrap').contains(e.target)) {
                    closeDrop();
                }
            });

            /* Submit cetak siswa */
            cetakBtn.addEventListener('click', function() {
                var sid = hiddenId.value;
                if (!sid) return;

                var dari   = document.getElementById('dari_tanggal').value;
                var sampai = document.getElementById('sampai_tanggal').value;
                var jenis  = (form.querySelector('input[name="jenis"]:checked') || {}).value || 'semua';
                var tahun  = document.getElementById('tahun_ajaran').value.trim();

                if (!dari || !sampai) {
                    alert('Isi Rentang Tanggal terlebih dahulu.');
                    document.getElementById('dari_tanggal').focus();
                    return;
                }
                if (!tahun || !/^\d{4}\/\d{4}$/.test(tahun)) {
                    alert('Isi Tahun Ajaran dengan format yang benar (contoh: 2026/2027).');
                    document.getElementById('tahun_ajaran').focus();
                    return;
                }

                document.getElementById('fs_siswa_id').value = sid;
                document.getElementById('fs_dari').value     = dari;
                document.getElementById('fs_sampai').value   = sampai;
                document.getElementById('fs_jenis').value    = jenis;
                document.getElementById('fs_tahun').value    = tahun;
                document.getElementById('fs_show_detail').value =
                    document.getElementById('show_detail_siswa').checked ? '1' : '';

                cetakBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses…';
                cetakBtn.disabled  = true;

                document.getElementById('cetakSiswaForm').submit();

                setTimeout(function() {
                    cetakBtn.innerHTML = '<i class="fas fa-file-pdf"></i> Cetak Jurnal Siswa Ini';
                    cetakBtn.disabled  = false;
                }, 4000);
            });

        }); /* end DOMContentLoaded */
    </script>
@endpush
