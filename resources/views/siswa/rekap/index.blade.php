@extends('layouts.app')

@section('title', 'Rekap Kehadiran')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ═══════════════════════════════════════════════════════
               REKAP KEHADIRAN — RESPONSIVE STYLES
               Breakpoints:
                 xs  : < 480px
                 sm  : 480–767px
                 md  : 768–1023px
                 lg  : 1024px+
            ═══════════════════════════════════════════════════════ */

        /* ── Wrapper ──────────────────────────────────────────── */
        .rekap-wrap {
            padding: 0 12px;
            max-width: 1280px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        @media (min-width: 768px) {
            .rekap-wrap {
                padding: 0 20px;
            }
        }

        @media (min-width: 1024px) {
            .rekap-wrap {
                padding: 0 28px;
            }
        }

        /* ── Stat grid ────────────────────────────────────────── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }

        @media (min-width: 480px) {
            .stat-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 768px) {
            .stat-grid {
                grid-template-columns: repeat(6, 1fr);
                gap: 12px;
                margin-bottom: 20px;
            }
        }

        .stat-card {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 10px;
            padding: 10px 8px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        @media (min-width: 768px) {
            .stat-card {
                border-radius: 12px;
                padding: 16px;
            }
        }

        .stat-value {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }

        @media (min-width: 768px) {
            .stat-value {
                font-size: 1.8rem;
                margin-bottom: 6px;
            }
        }

        .stat-label {
            font-size: .6rem;
            color: var(--text-muted, #64748b);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        @media (min-width: 768px) {
            .stat-label {
                font-size: .7rem;
            }
        }

        /* ── Filter section ───────────────────────────────────── */
        .filter-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 16px;
            overflow: hidden;
        }

        .filter-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            cursor: pointer;
            user-select: none;
            background: #f1f5f9;
            border: none;
            width: 100%;
            font-family: inherit;
            font-size: .875rem;
            font-weight: 700;
            color: #0f172a;
            gap: 8px;
        }

        .filter-toggle .ft-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-toggle .ft-chevron {
            transition: transform .2s;
            color: #64748b;
            font-size: .8rem;
        }

        .filter-toggle.open .ft-chevron {
            transform: rotate(180deg);
        }

        @media (min-width: 768px) {
            .filter-toggle {
                display: none;
            }
        }

        .filter-body {
            padding: 12px 16px 16px;
            display: none;
        }

        .filter-body.open {
            display: block;
        }

        @media (min-width: 768px) {
            .filter-body {
                display: block !important;
                padding: 16px;
            }
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            align-items: end;
        }

        @media (max-width: 479px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (min-width: 768px) {
            .filter-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 12px;
            }
        }

        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: var(--text-main, #0f172a);
            margin-bottom: 5px;
        }

        .form-input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 8px;
            font-size: .875rem;
            font-family: inherit;
            color: var(--text-main, #0f172a);
            background: #fff;
            box-sizing: border-box;
            -webkit-appearance: none;
            appearance: none;
        }

        .form-input:focus {
            outline: 2px solid #0ea5e9;
            outline-offset: -1px;
        }

        /* ── Card header ──────────────────────────────────────── */
        .c-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            flex-wrap: wrap;
        }

        @media (min-width: 768px) {
            .c-head {
                padding: 14px 18px;
            }
        }

        .c-head h3 {
            font-size: .9rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            flex: 1;
        }

        @media (min-width: 768px) {
            .c-head h3 {
                font-size: 1rem;
            }
        }

        .hbadge {
            font-size: .7rem;
            font-weight: 700;
            background: #f1f5f9;
            color: #475569;
            padding: 3px 10px;
            border-radius: 20px;
        }

        /* ── Tabel desktop ────────────────────────────────────── */
        .rekap-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 767px) {
            .rekap-table-wrap {
                display: none;
            }
        }

        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .78rem;
            background: #fff;
        }

        .rekap-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid var(--border, #e2e8f0);
        }

        .rekap-table th {
            padding: 11px 10px;
            text-align: left;
            font-size: .68rem;
            font-weight: 700;
            color: var(--text-muted, #64748b);
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .rekap-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .rekap-table tbody tr:last-child td {
            border-bottom: none;
        }

        .rekap-table tbody tr:hover td {
            background: #fafbfc;
        }

        /* Kolom aksi: wrap tombol tanpa display:flex pada td */
        .aksi-wrap {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        /* ── Card list mobile ─────────────────────────────────── */
        .rekap-card-list {
            display: none;
        }

        @media (max-width: 767px) {
            .rekap-card-list {
                display: flex;
                flex-direction: column;
                gap: 0;
            }
        }

        .rekap-card-item {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            background: #fff;
        }

        .rekap-card-item:last-child {
            border-bottom: none;
        }

        .rekap-card-item:active {
            background: #f8fafc;
        }

        .rci-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 5px;
        }

        .rci-nama {
            font-weight: 700;
            font-size: .88rem;
            color: #0f172a;
            flex: 1;
            min-width: 0;
        }

        .rci-tgl {
            font-size: .72rem;
            color: #64748b;
            font-weight: 600;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .rci-mid {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 6px;
        }

        .rci-kelas {
            font-size: .7rem;
            color: #64748b;
            background: #f1f5f9;
            border-radius: 5px;
            padding: 2px 7px;
            font-weight: 600;
        }

        .rci-status-row {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .75rem;
            margin-bottom: 3px;
        }

        .rci-bot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 4px;
        }

        .rci-ket {
            font-size: .72rem;
            color: #64748b;
            flex: 1;
            min-width: 0;
        }

        .rci-ket strong {
            color: #c2410c;
        }

        .rci-actions {
            display: flex;
            gap: 6px;
            flex-shrink: 0;
        }

        .rci-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 10px;
            border-radius: 7px;
            font-size: .7rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: inherit;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .rci-btn-edit {
            background: #f0fdf4;
            color: #15803d;
        }

        .rci-btn-lokasi {
            background: #f0f9ff;
            color: #0369a1;
        }

        /* ── Badges ───────────────────────────────────────────── */
        .badge-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: .63rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-hadir {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-izin {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-sakit {
            background: #fef3c7;
            color: #b45309;
        }

        .badge-alfa {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-terlambat {
            background: #fff7ed;
            color: #c2410c;
        }

        .badge-event {
            background: #ede9fe;
            color: #7c3aed;
        }

        .badge-belum {
            background: #f1f5f9;
            color: #94a3b8;
        }

        /* ── Status cell (masuk + pulang 2 baris) ─────────────── */
        .status-cell {
            line-height: 1.8;
        }

        .status-row {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .75rem;
        }

        .status-dot-masuk {
            color: #16a34a;
        }

        .status-dot-pulang {
            color: #0ea5e9;
        }

        .status-dash {
            color: #94a3b8;
            font-size: .72rem;
        }

        /* ── Tombol aksi ──────────────────────────────────────── */
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 7px;
            font-size: .72rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            transition: opacity .15s;
            white-space: nowrap;
        }

        .action-btn:active {
            opacity: .75;
        }

        .btn-view {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .btn-edit {
            background: #f0fdf4;
            color: #15803d;
        }

        /* ── Action bar (fixed bottom) ────────────────────────── */
        .action-bar {
            position: fixed;
            bottom: var(--footer-h, 0);
            left: 0;
            right: 0;
            padding: 10px 12px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 8px;
            z-index: 999;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
        }

        @media (min-width: 768px) {
            .action-bar {
                padding: 10px 24px 12px;
                gap: 12px;
                justify-content: flex-end;
            }
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: .82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            line-height: 1;
            white-space: nowrap;
        }

        @media (min-width: 768px) {
            .ab-btn {
                flex: unset;
                min-width: 130px;
                font-size: .875rem;
                padding: 12px 20px;
            }
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

        .ab-btn-teal {
            background: #0d9488;
            color: #fff;
            border-color: #0d9488;
        }

        /* ── Pagination ───────────────────────────────────────── */
        .rekap-pagination {
            padding: 12px 14px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 5px;
        }

        .pg-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 700;
            text-decoration: none;
            font-family: inherit;
            border: 1px solid #e2e8f0;
            background: #f1f5f9;
            color: #475569;
            cursor: pointer;
            transition: background .15s;
            white-space: nowrap;
        }

        .pg-btn:hover {
            background: #e2e8f0;
        }

        .pg-btn.active {
            background: var(--event-primary, #f59e0b);
            color: #fff;
            border-color: transparent;
            pointer-events: none;
        }

        .pg-btn.disabled {
            opacity: .4;
            cursor: not-allowed;
            pointer-events: none;
        }

        @media (max-width: 479px) {
            .pg-num {
                display: none;
            }

            .pg-num.active {
                display: inline-flex;
            }
        }

        /* ── Empty state ──────────────────────────────────────── */
        .rekap-empty {
            text-align: center;
            padding: 36px 20px;
            color: var(--text-muted, #64748b);
        }

        .rekap-empty i {
            font-size: 2.5rem;
            opacity: .3;
            display: block;
            margin-bottom: 10px;
        }

        .rekap-empty strong {
            display: block;
            color: var(--text-main, #0f172a);
            margin-bottom: 4px;
            font-size: .9rem;
        }

        .rekap-empty a {
            color: #0ea5e9;
            font-size: .82rem;
            display: inline-block;
            margin-top: 8px;
        }

        /* ── Highlight nama ───────────────────────────────────── */
        .nama-highlight {
            background: #fef9c3;
            border-radius: 3px;
            padding: 0 2px;
        }

        /* ═══════════════════════════════════════════════════════
               MODAL — responsive bottom-sheet di mobile
            ═══════════════════════════════════════════════════════ */
        .rekap-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .6);
            z-index: 9998;
            align-items: flex-end;
            justify-content: center;
            padding: 0;
        }

        @media (min-width: 480px) {
            .rekap-overlay {
                align-items: center;
                padding: 16px;
            }
        }

        .rekap-overlay.open {
            display: flex;
        }

        .rekap-modal {
            background: #fff;
            width: 100%;
            max-height: 92vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 -4px 40px rgba(0, 0, 0, .2);
            overflow: hidden;
            border-radius: 20px 20px 0 0;
        }

        @media (min-width: 480px) {
            .rekap-modal {
                border-radius: 16px;
                max-height: 90vh;
                box-shadow: 0 20px 60px rgba(0, 0, 0, .3);
            }
        }

        .rekap-modal-sm {
            max-width: 480px;
        }

        .rekap-modal-lg {
            max-width: 720px;
        }

        /* Drag handle di mobile */
        .rekap-modal::before {
            content: '';
            display: block;
            width: 36px;
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin: 10px auto 0;
            flex-shrink: 0;
        }

        @media (min-width: 480px) {
            .rekap-modal::before {
                display: none;
            }
        }

        .rekap-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid #eef2f7;
            flex-shrink: 0;
        }

        @media (min-width: 480px) {
            .rekap-modal-head {
                padding: 14px 18px;
            }
        }

        .rekap-modal-head h4 {
            font-size: .9rem;
            font-weight: 800;
            margin: 0;
            color: #0f172a;
        }

        @media (min-width: 480px) {
            .rekap-modal-head h4 {
                font-size: .95rem;
            }
        }

        .rekap-modal-close {
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            font-size: 1rem;
            padding: 6px 10px;
            border-radius: 8px;
            line-height: 1;
            min-width: 36px;
            min-height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .rekap-modal-close:hover {
            background: #f1f5f9;
            color: #475569;
        }

        .rekap-modal-body {
            padding: 14px 16px;
            overflow-y: auto;
            flex: 1;
            -webkit-overflow-scrolling: touch;
        }

        @media (min-width: 480px) {
            .rekap-modal-body {
                padding: 16px 18px;
            }
        }

        .rekap-modal-foot {
            padding: 10px 16px;
            border-top: 1px solid #eef2f7;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            flex-shrink: 0;
        }

        @media (min-width: 480px) {
            .rekap-modal-foot {
                padding: 12px 18px;
            }
        }

        /* ── Modal tabs (Masuk / Pulang) ──────────────────────── */
        .rekap-tabs {
            display: flex;
            gap: 6px;
            margin-bottom: 14px;
        }

        .rekap-tab {
            flex: 1;
            padding: 9px 12px;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 700;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            cursor: pointer;
            text-align: center;
        }

        @media (min-width: 480px) {
            .rekap-tab {
                flex: unset;
                padding: 7px 16px;
            }
        }

        .rekap-tab.active {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        .rekap-tab-pane {
            display: none;
        }

        .rekap-tab-pane.active {
            display: block;
        }

        /* Sub-tab (Peta / Foto) */
        .rekap-tab-content-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }

        .rc-tab {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: .75rem;
            font-weight: 700;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            cursor: pointer;
        }

        .rc-tab.active-masuk {
            background: #dcfce7;
            color: #15803d;
            border-color: #bbf7d0;
        }

        .rc-tab.active-pulang {
            background: #dbeafe;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        /* Info rows di dalam modal lokasi */
        .rekap-info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: .82rem;
        }

        .rekap-info-row:last-child {
            border-bottom: none;
        }

        .rekap-info-label {
            width: 100px;
            flex-shrink: 0;
            font-weight: 600;
            color: #64748b;
        }

        @media (min-width: 480px) {
            .rekap-info-label {
                width: 110px;
            }
        }

        .rekap-info-value {
            color: #0f172a;
        }

        .rekap-map-area {
            height: 240px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            margin-bottom: 12px;
        }

        @media (min-width: 480px) {
            .rekap-map-area {
                height: 280px;
            }
        }

        .rekap-foto-area {
            text-align: center;
            padding: 8px 0;
        }

        .rekap-foto-area img {
            max-width: 100%;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .1);
        }

        .rekap-no-data {
            text-align: center;
            padding: 24px;
            color: #94a3b8;
            font-size: .84rem;
        }

        .rekap-no-data i {
            display: block;
            font-size: 1.8rem;
            margin-bottom: 8px;
            opacity: .3;
        }

        /* ── Form dalam modal edit ────────────────────────────── */
        .rekap-form-row {
            margin-bottom: 14px;
        }

        .rekap-form-row label {
            display: block;
            font-size: .78rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .rekap-form-row input,
        .rekap-form-row select,
        .rekap-form-row textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            font-size: 1rem;
            /* 1rem agar iOS tidak auto-zoom */
            font-family: inherit;
            background: #f8fafc;
            color: #0f172a;
            outline: none;
            box-sizing: border-box;
            -webkit-appearance: none;
            appearance: none;
        }

        @media (min-width: 768px) {

            .rekap-form-row input,
            .rekap-form-row select,
            .rekap-form-row textarea {
                font-size: .875rem;
            }
        }

        .rekap-form-row input:focus,
        .rekap-form-row select:focus,
        .rekap-form-row textarea:focus {
            border-color: #0ea5e9;
            background: #fff;
        }

        .rekap-form-row input:disabled {
            opacity: .6;
            cursor: default;
        }
    </style>
@endpush

@section('content')

    @if ($errorMsg)
        <div
            style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;
                    padding:12px 16px;border-radius:8px;margin:8px 12px 0;">
            <i class="fas fa-exclamation-triangle"></i> {{ $errorMsg }}
        </div>
    @endif

    <div class="event-wrap rekap-wrap"
        style="padding-top:var(--header-h,56px); padding-bottom:calc(var(--footer-h,0px) + 88px);">

        {{-- ── Page Strip ──────────────────────────────────────── --}}
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>Rekap Kehadiran</div>
            <h2><i class="fas fa-chart-line"></i> Rekap Absensi Harian</h2>
            @if ($hasFilter)
                <p>
                    Periode {{ \Carbon\Carbon::parse($tanggalMulai)->translatedFormat('d M Y') }} –
                    {{ \Carbon\Carbon::parse($tanggalSelesai)->translatedFormat('d M Y') }}
                    @if (!empty($namaSiswa))
                        · Siswa: <strong>{{ $namaSiswa }}</strong>
                    @endif
                </p>
            @else
                <p>Hari ini — {{ now()->translatedFormat('l, d F Y') }}</p>
            @endif
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- ── Stats ───────────────────────────────────────────── --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value" style="color:#0ea5e9;">{{ $stats['total_absen_siswa'] }}</div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#16a34a;">{{ $stats['hadir'] }}</div>
                <div class="stat-label">Hadir</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#ea580c;">{{ $stats['terlambat'] ?? 0 }}</div>
                <div class="stat-label">Terlambat</div>
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

        {{-- ── Filter ──────────────────────────────────────────── --}}
        <form method="GET" class="filter-section" id="filterForm">

            <button type="button" class="filter-toggle {{ $hasFilter ? 'open' : '' }}" id="filterToggle"
                aria-expanded="{{ $hasFilter ? 'true' : 'false' }}" aria-controls="filterBody">
                <span class="ft-left">
                    <i class="fas fa-filter"></i>
                    Filter
                    @if ($hasFilter)
                        <span
                            style="background:#dbeafe;color:#1d4ed8;font-size:.65rem;
                                     padding:2px 8px;border-radius:20px;font-weight:700;">Aktif</span>
                    @endif
                </span>
                <i class="fas fa-chevron-down ft-chevron"></i>
            </button>

            <div class="filter-body {{ $hasFilter ? 'open' : '' }}" id="filterBody">
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
                            <label class="form-label">Nama Siswa</label>
                            <div style="position:relative;">
                                <input type="text" name="nama_siswa" class="form-input" placeholder="Cari nama siswa..."
                                    value="{{ $namaSiswa ?? '' }}" style="padding-left:32px;" autocomplete="off">
                                <i class="fas fa-search"
                                    style="position:absolute;left:10px;top:50%;transform:translateY(-50%);
                                          color:#94a3b8;font-size:.8rem;pointer-events:none;"></i>
                            </div>
                        </div>
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
                                <option value="terlambat" {{ $status == 'terlambat' ? 'selected' : '' }}>Terlambat
                                </option>
                                <option value="izin" {{ $status == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ $status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="alfa" {{ $status == 'alfa' ? 'selected' : '' }}>Alfa</option>
                            </select>
                        </div>
                    @endif
                    <div style="display:flex;gap:8px;align-items:flex-end;padding-top:4px;">
                        <button type="submit" class="action-btn btn-view"
                            style="flex:1;justify-content:center;padding:10px;">
                            <i class="fas fa-filter"></i> Terapkan
                        </button>
                        @if ($hasFilter)
                            <a href="{{ route('absen.rekap') }}" class="action-btn"
                                style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:10px 14px;">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- ── Card data ────────────────────────────────────────── --}}
        <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:0;">
            <div class="c-head">
                <div class="c-icon" style="background:#fef3c7;flex-shrink:0;">
                    <i class="fas fa-list"></i>
                </div>
                <h3>Rekap Detail Kehadiran</h3>
                @if ($paginator)
                    <span class="hbadge">{{ $paginator->total() }} data</span>
                @endif
            </div>

            {{-- ══ TABEL (tablet / desktop) ══ --}}
            <div class="rekap-table-wrap">
                <table class="rekap-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Ket</th>
                            @if (!$isSiswa)
                                <th>Lokasi</th>
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rekapPage as $rekap)
                            @php
                                $sm = $rekap['status_masuk'] ?? 'alfa';
                                $sp = $rekap['status_pulang'];
                                $jamM = $rekap['jam_masuk'] ? $rekap['jam_masuk']->format('H:i') : null;
                                $jamP = $rekap['jam_pulang'] ? $rekap['jam_pulang']->format('H:i') : null;
                                $mnt = $rekap['menit_terlambat'] ?? 0;

                                // Status EFEKTIF untuk tampilan & modal:
                                // jika ada menit_terlambat > 0, efektifnya terlambat
                                $smEfektif = $mnt > 0 && $jamM ? 'terlambat' : $sm;

                                $namaLengkap = $rekap['siswa']->nama_lengkap ?? '-';
                                $namaTampil = Str::limit($namaLengkap, 18);
                            @endphp
                            <tr>
                                <td style="font-weight:600;white-space:nowrap;">
                                    {{ \Carbon\Carbon::parse($rekap['tanggal'])->format('d/m/Y') }}
                                </td>
                                <td style="font-weight:600;">
                                    @if (!empty($namaSiswa) && str_contains(strtolower($namaLengkap), strtolower($namaSiswa)))
                                        {!! str_ireplace(e($namaSiswa), '<mark class="nama-highlight">' . e($namaSiswa) . '</mark>', e($namaTampil)) !!}
                                    @else
                                        {{ $namaTampil }}
                                    @endif
                                </td>
                                <td style="font-size:.75rem;">{{ $rekap['kelas']->nama_kelas ?? '-' }}</td>
                                <td class="status-cell">
                                    {{-- Baris Masuk --}}
                                    <div class="status-row">
                                        <span class="status-dot-masuk">
                                            <i class="fas fa-sign-in-alt" style="font-size:.65rem;"></i>
                                        </span>
                                        @if ($smEfektif === 'terlambat')
                                            <span class="badge-status badge-terlambat">{{ $jamM }}</span>
                                        @elseif ($smEfektif === 'hadir')
                                            <span class="badge-status badge-hadir">{{ $jamM }}</span>
                                        @elseif ($smEfektif === 'sakit')
                                            <span class="badge-status badge-sakit">Sakit</span>
                                        @elseif ($smEfektif === 'izin')
                                            <span class="badge-status badge-izin">Izin</span>
                                        @else
                                            <span class="badge-status badge-alfa">Alfa</span>
                                        @endif
                                    </div>
                                    {{-- Baris Pulang --}}
                                    <div class="status-row" style="margin-top:3px;">
                                        <span class="status-dot-pulang">
                                            <i class="fas fa-sign-out-alt" style="font-size:.65rem;"></i>
                                        </span>
                                        @if ($jamP)
                                            <span class="badge-status badge-hadir">{{ $jamP }}</span>
                                        @else
                                            <span class="status-dash">Belum pulang</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="font-size:.75rem;">
                                    @if ($mnt > 0)
                                        <span style="color:#c2410c;font-weight:700;">
                                            <i class="fas fa-clock" style="font-size:.65rem;"></i>
                                            {{ $mnt }} mnt terlambat
                                        </span><br>
                                    @endif
                                    @if ($rekap['catatan'])
                                        <small style="color:#64748b;">{{ $rekap['catatan'] }}</small>
                                    @endif
                                </td>
                                @if (!$isSiswa)
                                    <td>
                                        <button type="button" class="action-btn btn-view"
                                            onclick="bukaModalLokasi({{ $rekap['id'] }})">
                                            <i class="fas fa-map-marked-alt"></i> Lihat
                                        </button>
                                    </td>
                                    <td>
                                        <div class="aksi-wrap">
                                            @if($rekap['siswa']?->id)
                                            <a href="{{ route('siswa.show', $rekap['siswa']->id) }}"
                                               class="action-btn" style="background:#ede9fe;color:#7c3aed;border:1px solid #ddd6fe;" title="Profil Siswa">
                                                <i class="fas fa-user"></i>
                                            </a>
                                            @endif
                                            @can('absen.manual.update')
                                                <a href="{{ $rekap['edit_url'] ?? route('admin.absen-manual.edit', $rekap['id']) }}"
                                                    class="action-btn btn-edit">
                                                    <i class="fas fa-pen"></i> Edit
                                                </a>
                                            @else
                                                <button type="button" class="action-btn btn-edit"
                                                    onclick="bukaModalEdit({{ $rekap['id'] }})">
                                                    <i class="fas fa-pen"></i> Edit
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSiswa ? 5 : 7 }}">
                                    <div class="rekap-empty">
                                        <i class="fas fa-inbox"></i>
                                        <strong>Tidak ada data absen</strong>
                                        @if (!empty($namaSiswa))
                                            Tidak ditemukan untuk siswa "<strong>{{ $namaSiswa }}</strong>".
                                        @elseif (!$hasFilter)
                                            Belum ada data hari ini.
                                            <a
                                                href="{{ route('absen.rekap', ['tanggal_mulai' => now()->subDays(7)->format('Y-m-d'), 'tanggal_selesai' => now()->format('Y-m-d')]) }}">
                                                <i class="fas fa-search"></i> Lihat 7 hari terakhir
                                            </a>
                                        @else
                                            Tidak ada data untuk filter yang dipilih.
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ══ CARD LIST (mobile) ══ --}}
            <div class="rekap-card-list">
                @forelse ($rekapPage as $rekap)
                    @php
                        $sm = $rekap['status_masuk'] ?? 'alfa';
                        $jamM = $rekap['jam_masuk'] ? $rekap['jam_masuk']->format('H:i') : null;
                        $jamP = $rekap['jam_pulang'] ? $rekap['jam_pulang']->format('H:i') : null;
                        $mnt = $rekap['menit_terlambat'] ?? 0;
                        $smEfektif = $mnt > 0 && $jamM ? 'terlambat' : $sm;
                        $namaLengkap = $rekap['siswa']->nama_lengkap ?? '-';
                    @endphp
                    <div class="rekap-card-item">
                        <div class="rci-top">
                            <span class="rci-nama">{{ $namaLengkap }}</span>
                            <span class="rci-tgl">
                                {{ \Carbon\Carbon::parse($rekap['tanggal'])->format('d/m/Y') }}
                            </span>
                        </div>
                        <div class="rci-mid">
                            @if ($rekap['kelas'])
                                <span class="rci-kelas">{{ $rekap['kelas']->nama_kelas }}</span>
                            @endif
                        </div>
                        {{-- Status masuk + pulang --}}
                        <div class="rci-status-row">
                            <span class="status-dot-masuk">
                                <i class="fas fa-sign-in-alt" style="font-size:.65rem;"></i>
                            </span>
                            @if ($smEfektif === 'terlambat')
                                <span class="badge-status badge-terlambat">{{ $jamM }}</span>
                                <span style="font-size:.68rem;color:#c2410c;font-weight:700;">
                                    ({{ $mnt }} mnt)
                                </span>
                            @elseif ($smEfektif === 'hadir')
                                <span class="badge-status badge-hadir">{{ $jamM }}</span>
                            @elseif ($smEfektif === 'sakit')
                                <span class="badge-status badge-sakit">Sakit</span>
                            @elseif ($smEfektif === 'izin')
                                <span class="badge-status badge-izin">Izin</span>
                            @else
                                <span class="badge-status badge-alfa">Alfa</span>
                            @endif
                            &nbsp;
                            <span class="status-dot-pulang" style="margin-left:4px;">
                                <i class="fas fa-sign-out-alt" style="font-size:.65rem;"></i>
                            </span>
                            @if ($jamP)
                                <span class="badge-status badge-hadir">{{ $jamP }}</span>
                            @else
                                <span class="status-dash">Belum pulang</span>
                            @endif
                        </div>
                        @if ($rekap['catatan'])
                            <div class="rci-ket" style="margin-top:4px;">{{ $rekap['catatan'] }}</div>
                        @endif
                        @if (!$isSiswa)
                            <div class="rci-actions" style="margin-top:8px;">
                                <button type="button" class="rci-btn rci-btn-lokasi"
                                    onclick="bukaModalLokasi({{ $rekap['id'] }})">
                                    <i class="fas fa-map-marked-alt"></i> Lokasi
                                </button>
                                @if($rekap['siswa']?->id)
                                <a href="{{ route('siswa.show', $rekap['siswa']->id) }}"
                                   class="rci-btn" style="background:#ede9fe;color:#7c3aed;text-decoration:none;">
                                    <i class="fas fa-user"></i> Profil
                                </a>
                                @endif
                                @can('absen.manual.update')
                                    <a href="{{ $rekap['edit_url'] ?? route('admin.absen-manual.edit', $rekap['id']) }}"
                                        class="rci-btn rci-btn-edit" style="text-decoration:none;">
                                        <i class="fas fa-pen"></i> Edit
                                    </a>
                                @else
                                    <button type="button" class="rci-btn rci-btn-edit"
                                        onclick="bukaModalEdit({{ $rekap['id'] }})">
                                        <i class="fas fa-pen"></i> Edit
                                    </button>
                                @endcan
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rekap-empty">
                        <i class="fas fa-inbox"></i>
                        <strong>Tidak ada data absen</strong>
                        @if (!empty($namaSiswa))
                            Tidak ditemukan untuk siswa "<strong>{{ $namaSiswa }}</strong>".
                        @elseif (!$hasFilter)
                            Belum ada data hari ini.
                            <a
                                href="{{ route('absen.rekap', ['tanggal_mulai' => now()->subDays(7)->format('Y-m-d'), 'tanggal_selesai' => now()->format('Y-m-d')]) }}">
                                <i class="fas fa-search"></i> Lihat 7 hari terakhir
                            </a>
                        @else
                            Tidak ada data untuk filter yang dipilih.
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- ── Pagination ───────────────────────────────────── --}}
            @if ($paginator && $paginator->hasPages())
                <div class="rekap-pagination">
                    @if ($paginator->onFirstPage())
                        <span class="pg-btn disabled"><i class="fas fa-angle-left"></i></span>
                    @else
                        <a href="{{ route('absen.rekap', array_merge(request()->except('page'), ['page' => $paginator->currentPage() - 1])) }}"
                            class="pg-btn"><i class="fas fa-angle-left"></i></a>
                    @endif

                    @php
                        $pgStart = max(1, $paginator->currentPage() - 2);
                        $pgEnd = min($paginator->lastPage(), $paginator->currentPage() + 2);
                    @endphp

                    @if ($pgStart > 1)
                        <a href="{{ route('absen.rekap', array_merge(request()->except('page'), ['page' => 1])) }}"
                            class="pg-btn pg-num">1</a>
                        @if ($pgStart > 2)
                            <span class="pg-btn pg-num" style="pointer-events:none;">…</span>
                        @endif
                    @endif

                    @for ($i = $pgStart; $i <= $pgEnd; $i++)
                        @if ($paginator->currentPage() === $i)
                            <span class="pg-btn pg-num active">{{ $i }}</span>
                        @else
                            <a href="{{ route('absen.rekap', array_merge(request()->except('page'), ['page' => $i])) }}"
                                class="pg-btn pg-num">{{ $i }}</a>
                        @endif
                    @endfor

                    @if ($pgEnd < $paginator->lastPage())
                        @if ($pgEnd < $paginator->lastPage() - 1)
                            <span class="pg-btn pg-num" style="pointer-events:none;">…</span>
                        @endif
                        <a href="{{ route('absen.rekap', array_merge(request()->except('page'), ['page' => $paginator->lastPage()])) }}"
                            class="pg-btn pg-num">{{ $paginator->lastPage() }}</a>
                    @endif

                    @if ($paginator->hasMorePages())
                        <a href="{{ route('absen.rekap', array_merge(request()->except('page'), ['page' => $paginator->currentPage() + 1])) }}"
                            class="pg-btn"><i class="fas fa-angle-right"></i></a>
                    @else
                        <span class="pg-btn disabled"><i class="fas fa-angle-right"></i></span>
                    @endif
                </div>
                <div style="text-align:center;font-size:.72rem;color:#94a3b8;padding:4px 0 10px;">
                    Halaman {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                    &nbsp;·&nbsp; {{ $paginator->total() }} data
                </div>
            @elseif ($paginator && $paginator->total() > 0)
                <div
                    style="padding:10px 16px;text-align:center;font-size:.75rem;color:#94a3b8;border-top:1px solid #f1f5f9;">
                    {{ $paginator->count() }} dari {{ $paginator->total() }} data
                </div>
            @endif

            {{-- JSON data untuk modal --}}
            @if (!$isSiswa)
                @php
                    $lokasiData = [];
                    $editData = [];
                    foreach ($rekapPage as $r) {
                        $smR = $r['status_masuk'] ?? 'alfa';
                        $mntR = $r['menit_terlambat'] ?? 0;
                        $jamMR = $r['jam_masuk'] ? $r['jam_masuk']->format('H:i') : null;
                        // Status efektif: jika ada menit terlambat → override ke 'terlambat'
                        $smEfektifR = $mntR > 0 && $jamMR ? 'terlambat' : $smR;

                        $lokasiData[$r['id']] = [
                            'nama' => $r['siswa']?->nama_lengkap ?? '-',
                            'jam_masuk' => $jamMR,
                            'status_masuk' => $smEfektifR,
                            'lat_masuk' => $r['latitude_masuk'] ?? null,
                            'lng_masuk' => $r['longitude_masuk'] ?? null,
                            'foto_masuk' =>
                                isset($r['foto_masuk']) && $r['foto_masuk']
                                    ? '/storage/' . ltrim($r['foto_masuk'], '/')
                                    : null,
                            'lokasi_masuk' => $r['lokasi_masuk'] ?? null,
                            'jam_pulang' => $r['jam_pulang'] ? $r['jam_pulang']->format('H:i') : null,
                            'status_pulang' => $r['status_pulang'] ?? null,
                            'lat_pulang' => $r['latitude_pulang'] ?? null,
                            'lng_pulang' => $r['longitude_pulang'] ?? null,
                            'foto_pulang' =>
                                isset($r['foto_pulang']) && $r['foto_pulang']
                                    ? '/storage/' . ltrim($r['foto_pulang'], '/')
                                    : null,
                            'lokasi_pulang' => $r['lokasi_pulang'] ?? null,
                        ];

                        $editData[$r['id']] = [
                            'nama' => $r['siswa']?->nama_lengkap ?? '-',
                            'jenis' => 'masuk',
                            // ← Kirim status EFEKTIF supaya select di modal langsung tepat
                            'status' => $smEfektifR,
                            'waktu' => $jamMR
                                ? \Carbon\Carbon::parse($r['tanggal'])->format('Y-m-d') . 'T' . $jamMR
                                : '',
                            'catatan' => $r['catatan'] ?? '',
                            'action_url' => route('absen.manual.update', $r['id']),
                            'edit_url' => $r['edit_url'] ?? null,
                        ];
                    }
                @endphp
                <script>
                    window._lokasiData = @json($lokasiData);
                    window._editData = @json($editData);
                    window._sekolahRadius = {{ config('sekolah.radius_m', 100) }};
                </script>
            @endif
        </div>{{-- end card --}}

        {{-- ── Action Bar ──────────────────────────────────────── --}}
        @if (!$isSiswa)
            <div class="action-bar">
                <a href="{{ route('dashboard') }}" class="ab-btn ab-btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                @can('absen.manual.view')
                    <a href="{{ route('admin.absen-manual.index', ['tanggal' => $tanggalMulai]) }}"
                        class="ab-btn ab-btn-teal" style="flex:unset;padding:12px 14px;">
                        <i class="fas fa-clipboard-list"></i>
                    </a>
                @endcan
                <a href="{{ route('absen.rekap.export', request()->query()) }}" class="ab-btn ab-btn-primary">
                    <i class="fas fa-file-excel"></i> Export
                </a>
            </div>
        @endif

    </div>{{-- end event-wrap --}}

    {{-- ═══════════════════════════════════════════
         MODAL: Lokasi & Foto (Tab Masuk / Pulang)
    ═══════════════════════════════════════════ --}}
    @if (!$isSiswa)
        <div class="rekap-overlay" id="overlayLokasi">
            <div class="rekap-modal rekap-modal-lg">
                <div class="rekap-modal-head">
                    <div>
                        <h4 id="lokasiModalTitle">
                            <i class="fas fa-map-marked-alt" style="color:#0ea5e9;margin-right:6px;"></i>
                            Lokasi &amp; Foto Selfie
                        </h4>
                        <div style="font-size:.75rem;color:#64748b;margin-top:2px;" id="lokasiModalSubtitle"></div>
                    </div>
                    <button class="rekap-modal-close" onclick="tutupModal('overlayLokasi')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="rekap-modal-body">
                    <div class="rekap-tabs">
                        <button class="rekap-tab active" id="btnTabMasuk" onclick="switchAbsenTab('masuk')">
                            <i class="fas fa-sign-in-alt" style="margin-right:4px;color:#16a34a;"></i>Masuk
                        </button>
                        <button class="rekap-tab" id="btnTabPulang" onclick="switchAbsenTab('pulang')">
                            <i class="fas fa-sign-out-alt" style="margin-right:4px;color:#0ea5e9;"></i>Pulang
                        </button>
                    </div>

                    {{-- Tab Masuk --}}
                    <div id="tabAbsenMasuk" class="rekap-tab-pane active">
                        <div id="infoMasuk" style="margin-bottom:12px;"></div>
                        <div class="rekap-tab-content-tabs">
                            <button class="rc-tab active-masuk" id="btnMasukPeta" onclick="switchSubTab('masuk','peta')">
                                <i class="fas fa-map" style="margin-right:4px;"></i>Peta
                            </button>
                            <button class="rc-tab" id="btnMasukFoto" onclick="switchSubTab('masuk','foto')">
                                <i class="fas fa-camera" style="margin-right:4px;"></i>Foto
                            </button>
                        </div>
                        <div id="masukPetaPane">
                            <div id="mapMasukWrap" class="rekap-map-area"></div>
                            <div id="mapMasukEmpty" class="rekap-no-data" style="display:none;">
                                <i class="fas fa-map-marker-slash"></i>
                                Data lokasi masuk tidak tersedia.
                            </div>
                        </div>
                        <div id="masukFotoPane" style="display:none;">
                            <div id="fotoMasukWrap" class="rekap-foto-area"></div>
                            <div id="fotoMasukEmpty" class="rekap-no-data" style="display:none;">
                                <i class="fas fa-image"></i>
                                Tidak ada foto selfie masuk.
                            </div>
                        </div>
                    </div>

                    {{-- Tab Pulang --}}
                    <div id="tabAbsenPulang" class="rekap-tab-pane">
                        <div id="infoPulang" style="margin-bottom:12px;"></div>
                        <div class="rekap-tab-content-tabs">
                            <button class="rc-tab active-pulang" id="btnPulangPeta"
                                onclick="switchSubTab('pulang','peta')">
                                <i class="fas fa-map" style="margin-right:4px;"></i>Peta
                            </button>
                            <button class="rc-tab" id="btnPulangFoto" onclick="switchSubTab('pulang','foto')">
                                <i class="fas fa-camera" style="margin-right:4px;"></i>Foto
                            </button>
                        </div>
                        <div id="pulangPetaPane">
                            <div id="mapPulangWrap" class="rekap-map-area"></div>
                            <div id="mapPulangEmpty" class="rekap-no-data" style="display:none;">
                                <i class="fas fa-map-marker-slash"></i>
                                Data lokasi pulang tidak tersedia.
                            </div>
                        </div>
                        <div id="pulangFotoPane" style="display:none;">
                            <div id="fotoPulangWrap" class="rekap-foto-area"></div>
                            <div id="fotoPulangEmpty" class="rekap-no-data" style="display:none;">
                                <i class="fas fa-image"></i>
                                Siswa belum melakukan absen pulang.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rekap-modal-foot">
                    <button class="action-btn btn-view" style="padding:10px 20px;" onclick="tutupModal('overlayLokasi')">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════
             MODAL: Edit Absen
        ═══════════════════════════════════════════ --}}
        <div class="rekap-overlay" id="overlayEdit">
            <div class="rekap-modal rekap-modal-sm">
                <div class="rekap-modal-head">
                    <h4 id="editModalTitle">
                        <i class="fas fa-pen" style="color:#10b981;margin-right:6px;"></i>Edit Absen
                    </h4>
                    <button class="rekap-modal-close" onclick="tutupModal('overlayEdit')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="editAbsenForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="rekap-modal-body">
                        <div class="rekap-form-row">
                            <label>Siswa</label>
                            <input type="text" id="editNama" disabled>
                        </div>
                        <div class="rekap-form-row">
                            <label>Status <span style="color:#ef4444;">*</span></label>
                            {{-- Semua opsi lengkap termasuk terlambat --}}
                            <select name="status" id="editStatus" required>
                                <option value="hadir">Hadir</option>
                                <option value="terlambat">Terlambat</option>
                                <option value="sakit">Sakit</option>
                                <option value="izin">Izin</option>
                                <option value="alfa">Alfa</option>
                            </select>
                        </div>
                        <div class="rekap-form-row">
                            <label>Waktu Absen Masuk</label>
                            <input type="datetime-local" name="waktu_absen" id="editWaktu">
                        </div>
                        <div class="rekap-form-row">
                            <label>Catatan</label>
                            <textarea name="catatan" id="editCatatan" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="rekap-modal-foot">
                        <button type="button" class="action-btn"
                            style="background:#f1f5f9;color:#475569;padding:10px 16px;"
                            onclick="tutupModal('overlayEdit')">
                            Batal
                        </button>
                        <button type="submit" class="action-btn btn-edit" style="padding:10px 20px;">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ── Header active ──────────────────────────────────── */
            var header = document.querySelector('.header-auto-show');
            if (header) header.classList.add('header-active');

            /* ── Filter toggle (mobile) ─────────────────────────── */
            var filterToggle = document.getElementById('filterToggle');
            var filterBody = document.getElementById('filterBody');
            if (filterToggle && filterBody) {
                filterToggle.addEventListener('click', function() {
                    var isOpen = filterBody.classList.toggle('open');
                    filterToggle.classList.toggle('open', isOpen);
                    filterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            /* ── Modal helpers ──────────────────────────────────── */
            window.tutupModal = function(id) {
                var el = document.getElementById(id);
                if (el) el.classList.remove('open');
                document.body.style.overflow = '';
                if (id === 'overlayLokasi') {
                    if (window._mapMasuk) {
                        window._mapMasuk.remove();
                        window._mapMasuk = null;
                    }
                    if (window._mapPulang) {
                        window._mapPulang.remove();
                        window._mapPulang = null;
                    }
                }
            };

            function bukaOverlay(id) {
                var el = document.getElementById(id);
                if (el) el.classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            ['overlayLokasi', 'overlayEdit'].forEach(function(id) {
                var el = document.getElementById(id);
                if (!el) return;
                el.addEventListener('click', function(e) {
                    if (e.target === el) window.tutupModal(id);
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    ['overlayLokasi', 'overlayEdit'].forEach(function(id) {
                        var el = document.getElementById(id);
                        if (el && el.classList.contains('open')) window.tutupModal(id);
                    });
                }
            });

            /* ── Leaflet loader ─────────────────────────────────── */
            function ensureLeaflet(cb) {
                if (window.L && window.L.map) {
                    cb();
                    return;
                }
                var s = document.createElement('script');
                s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                s.onload = cb;
                document.body.appendChild(s);
            }

            function buatPeta(containerId, lat, lng, radius) {
                var wrap = document.getElementById(containerId);
                if (!wrap || !lat || !lng) return null;
                var map = L.map(wrap).setView([+lat, +lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);
                L.marker([+lat, +lng]).addTo(map);
                if (radius > 0) {
                    L.circle([+lat, +lng], {
                        radius: radius,
                        color: '#0ea5e9',
                        weight: 2,
                        fillColor: '#0ea5e9',
                        fillOpacity: .15
                    }).addTo(map);
                }
                return map;
            }

            /* ── Tab Masuk / Pulang ─────────────────────────────── */
            window._currentAbsenData = null;

            window.switchAbsenTab = function(tab) {
                var tabM = document.getElementById('tabAbsenMasuk');
                var tabP = document.getElementById('tabAbsenPulang');
                var btnM = document.getElementById('btnTabMasuk');
                var btnP = document.getElementById('btnTabPulang');
                if (tab === 'masuk') {
                    tabM.classList.add('active');
                    tabP.classList.remove('active');
                    btnM.classList.add('active');
                    btnP.classList.remove('active');
                    inisialisasiPetaMasuk();
                } else {
                    tabP.classList.add('active');
                    tabM.classList.remove('active');
                    btnP.classList.add('active');
                    btnM.classList.remove('active');
                    inisialisasiPetaPulang();
                }
            };

            /* ── Sub-tab Peta / Foto ────────────────────────────── */
            window.switchSubTab = function(jenis, subTab) {
                var petaPane = document.getElementById(jenis + 'PetaPane');
                var fotoPane = document.getElementById(jenis + 'FotoPane');
                var btnPeta = document.getElementById('btn' + cap(jenis) + 'Peta');
                var btnFoto = document.getElementById('btn' + cap(jenis) + 'Foto');
                var activeClass = jenis === 'masuk' ? 'active-masuk' : 'active-pulang';

                if (subTab === 'peta') {
                    petaPane.style.display = 'block';
                    fotoPane.style.display = 'none';
                    btnPeta.classList.add(activeClass);
                    btnFoto.classList.remove('active-masuk', 'active-pulang');
                    if (jenis === 'masuk') inisialisasiPetaMasuk();
                    else inisialisasiPetaPulang();
                } else {
                    fotoPane.style.display = 'block';
                    petaPane.style.display = 'none';
                    btnFoto.classList.add(activeClass);
                    btnPeta.classList.remove('active-masuk', 'active-pulang');
                }
            };

            function cap(s) {
                return s.charAt(0).toUpperCase() + s.slice(1);
            }

            function inisialisasiPetaMasuk() {
                var d = window._currentAbsenData;
                if (!d) return;
                var wrap = document.getElementById('mapMasukWrap');
                var empty = document.getElementById('mapMasukEmpty');
                if (!wrap || wrap.dataset.inited === '1') return;
                if (!d.lat_masuk || !d.lng_masuk) {
                    wrap.style.display = 'none';
                    if (empty) empty.style.display = 'block';
                    return;
                }
                wrap.style.display = 'block';
                if (empty) empty.style.display = 'none';
                ensureLeaflet(function() {
                    window._mapMasuk = buatPeta('mapMasukWrap', d.lat_masuk, d.lng_masuk, window
                        ._sekolahRadius || 0);
                    wrap.dataset.inited = '1';
                });
            }

            function inisialisasiPetaPulang() {
                var d = window._currentAbsenData;
                if (!d) return;
                var wrap = document.getElementById('mapPulangWrap');
                var empty = document.getElementById('mapPulangEmpty');
                if (!wrap || wrap.dataset.inited === '1') return;
                if (!d.lat_pulang || !d.lng_pulang) {
                    wrap.style.display = 'none';
                    if (empty) empty.style.display = 'block';
                    return;
                }
                wrap.style.display = 'block';
                if (empty) empty.style.display = 'none';
                ensureLeaflet(function() {
                    window._mapPulang = buatPeta('mapPulangWrap', d.lat_pulang, d.lng_pulang, window
                        ._sekolahRadius || 0);
                    wrap.dataset.inited = '1';
                });
            }

            /* ── Render info rows ───────────────────────────────── */
            var statusLabel = {
                hadir: 'Hadir',
                terlambat: 'Terlambat',
                sakit: 'Sakit',
                izin: 'Izin',
                alfa: 'Alfa'
            };

            function renderInfo(containerId, rows) {
                var el = document.getElementById(containerId);
                if (!el) return;
                el.innerHTML = rows.map(function(r) {
                    return '<div class="rekap-info-row">' +
                        '<span class="rekap-info-label">' + r[0] + '</span>' +
                        '<span class="rekap-info-value">' + r[1] + '</span>' +
                        '</div>';
                }).join('');
            }

            /* ── Buka Modal Lokasi ──────────────────────────────── */
            window.bukaModalLokasi = function(id) {
                var d = (window._lokasiData || {})[id];
                if (!d) return;
                window._currentAbsenData = d;

                // Reset peta
                ['mapMasukWrap', 'mapPulangWrap'].forEach(function(wid) {
                    var w = document.getElementById(wid);
                    if (w) {
                        delete w.dataset.inited;
                        w.style.display = 'block';
                    }
                });
                if (window._mapMasuk) {
                    window._mapMasuk.remove();
                    window._mapMasuk = null;
                }
                if (window._mapPulang) {
                    window._mapPulang.remove();
                    window._mapPulang = null;
                }

                document.getElementById('lokasiModalTitle').innerHTML =
                    '<i class="fas fa-map-marked-alt" style="color:#0ea5e9;margin-right:6px;"></i>Lokasi &amp; Foto Selfie';
                document.getElementById('lokasiModalSubtitle').textContent = d.nama || '';

                // Info masuk
                renderInfo('infoMasuk', [
                    ['Jam Masuk', d.jam_masuk || '-'],
                    ['Status', statusLabel[d.status_masuk] || (d.status_masuk || '-')],
                    ['Koordinat', (d.lat_masuk && d.lng_masuk) ? d.lat_masuk + ', ' + d.lng_masuk :
                        'Tidak tersedia'
                    ],
                    ['Alamat', d.lokasi_masuk || 'Tidak tersedia'],
                ]);

                // Foto masuk
                var fMW = document.getElementById('fotoMasukWrap');
                var fME = document.getElementById('fotoMasukEmpty');
                if (d.foto_masuk) {
                    fMW.innerHTML = '<img src="' + d.foto_masuk + '" alt="Foto Masuk">';
                    fMW.style.display = 'block';
                    if (fME) fME.style.display = 'none';
                } else {
                    fMW.innerHTML = '';
                    fMW.style.display = 'none';
                    if (fME) fME.style.display = 'block';
                }

                // Info pulang
                renderInfo('infoPulang', [
                    ['Jam Pulang', d.jam_pulang || '-'],
                    ['Status', d.status_pulang ? (statusLabel[d.status_pulang] || d.status_pulang) :
                        'Belum pulang'
                    ],
                    ['Koordinat', (d.lat_pulang && d.lng_pulang) ? d.lat_pulang + ', ' + d.lng_pulang :
                        'Belum tersedia'
                    ],
                    ['Alamat', d.lokasi_pulang || 'Belum tersedia'],
                ]);

                // Foto pulang
                var fPW = document.getElementById('fotoPulangWrap');
                var fPE = document.getElementById('fotoPulangEmpty');
                if (d.foto_pulang) {
                    fPW.innerHTML = '<img src="' + d.foto_pulang + '" alt="Foto Pulang">';
                    fPW.style.display = 'block';
                    if (fPE) fPE.style.display = 'none';
                } else {
                    fPW.innerHTML = '';
                    fPW.style.display = 'none';
                    if (fPE) fPE.style.display = 'block';
                }

                // Reset ke tab masuk + sub-tab peta
                document.getElementById('tabAbsenMasuk').classList.add('active');
                document.getElementById('tabAbsenPulang').classList.remove('active');
                document.getElementById('btnTabMasuk').classList.add('active');
                document.getElementById('btnTabPulang').classList.remove('active');
                document.getElementById('masukPetaPane').style.display = 'block';
                document.getElementById('masukFotoPane').style.display = 'none';
                document.getElementById('btnMasukPeta').classList.add('active-masuk');
                document.getElementById('btnMasukFoto').classList.remove('active-masuk', 'active-pulang');

                bukaOverlay('overlayLokasi');
                setTimeout(inisialisasiPetaMasuk, 80);
            };

            /* ── Buka Modal Edit ────────────────────────────────── */
            window.bukaModalEdit = function(id) {
                var d = (window._editData || {})[id];
                if (!d) return;

                document.getElementById('editModalTitle').innerHTML =
                    '<i class="fas fa-pen" style="color:#10b981;margin-right:6px;"></i>Edit Absen Masuk';
                document.getElementById('editNama').value = d.nama || '';
                document.getElementById('editWaktu').value = d.waktu || '';
                document.getElementById('editCatatan').value = d.catatan || '';

                // ← Set status dari nilai efektif yang sudah dihitung di PHP
                var selectStatus = document.getElementById('editStatus');
                selectStatus.value = d.status || 'hadir';
                // Fallback jika value tidak cocok dengan opsi yang ada
                if (selectStatus.value !== d.status) {
                    selectStatus.value = 'hadir';
                }

                var form = document.getElementById('editAbsenForm');
                form.action = d.action_url;

                var mi = form.querySelector('input[name="_method"]');
                if (!mi) {
                    mi = document.createElement('input');
                    mi.type = 'hidden';
                    mi.name = '_method';
                    form.appendChild(mi);
                }
                mi.value = 'PATCH';

                bukaOverlay('overlayEdit');
            };
        });
    </script>
@endpush
