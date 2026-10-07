@extends('layouts.app')

@section('title', 'Konfigurasi Sekolah')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        /* ─────────────────────────────────────────────────────────────────────────
            SCFG — Konfigurasi Sekolah
            Semua selector diawali .scfg-pg agar tidak bocor ke layout global
        ──────────────────────────────────────────────────────────────────────────*/

        /* ── Reset CSS Variables ──────────────────────────────────────────────── */
        :root {
            --scfg-primary: #0ea5e9;
            --scfg-primary-dark: #0369a1;
            --scfg-danger: #dc2626;
            --scfg-success: #16a34a;
            --scfg-warning: #f97316;
            --scfg-surface: #ffffff;
            --scfg-bg: #f1f5f9;
            --scfg-border: #e2e8f0;
            --scfg-text: #0f172a;
            --scfg-muted: #64748b;
            --scfg-subtle: #94a3b8;
            --scfg-radius-card: 14px;
            --scfg-radius-input: 9px;
            --scfg-shadow-card: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 8px rgba(0, 0, 0, .04);
            --scfg-gap: 14px;
        }

        /* ── Page Wrapper ─────────────────────────────────────────────────────── */
        .scfg-pg {
            font-family: inherit;
            min-height: 100vh;
            background: var(--scfg-bg);
            padding-bottom: calc(var(--footer-h, 60px) + 76px);
        }

        /* ── Container ────────────────────────────────────────────────────────── */
        .scfg-container {
            max-width: 720px;
            margin: 0 auto;
            padding: 0 16px;
        }

        /* ── Hero Strip ───────────────────────────────────────────────────────── */
        .scfg-strip {
            padding: calc(var(--header-h, 56px) + 20px) 20px 24px;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0ea5e9 100%);
            position: relative;
            overflow: hidden;
            margin-bottom: 18px;
        }

        .scfg-strip::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -30px;
            width: 160px;
            height: 160px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
            pointer-events: none;
        }

        .scfg-strip::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -20px;
            width: 110px;
            height: 110px;
            background: rgba(255, 255, 255, .04);
            border-radius: 50%;
            pointer-events: none;
        }

        .scfg-live {
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

        .scfg-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #7dd3fc;
            animation: scfg-pulse 2.2s ease-in-out infinite;
        }

        @keyframes scfg-pulse {

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

        .scfg-strip h2 {
            font-size: 1.25rem;
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

        .scfg-strip h2 i {
            font-size: 1.1rem;
            opacity: .85;
        }

        .scfg-strip p {
            font-size: .8rem;
            color: rgba(255, 255, 255, .6);
            margin: 0;
            position: relative;
            z-index: 1;
        }

        /* ── Session Alerts ───────────────────────────────────────────────────── */
        .scfg-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: var(--scfg-radius-input);
            font-size: .83rem;
            margin-bottom: 14px;
        }

        .scfg-alert-ok {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .scfg-alert-err {
            background: #fee2e2;
            color: var(--scfg-danger);
            border: 1px solid #fecaca;
        }

        .scfg-alert-err ul {
            margin: 5px 0 0 16px;
            padding: 0;
            font-size: .79rem;
        }

        /* ── Card ─────────────────────────────────────────────────────────────── */
        .scfg-card {
            background: var(--scfg-surface);
            border: 1px solid var(--scfg-border);
            border-radius: var(--scfg-radius-card);
            margin-bottom: 14px;
            box-shadow: var(--scfg-shadow-card);
            overflow: visible;
            /* allow dropdown to overflow */
        }

        /* Card header */
        .scfg-chead {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px 12px;
            border-bottom: 1px solid #f8fafc;
            position: relative;
        }

        .scfg-cico {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            flex-shrink: 0;
        }

        .scfg-chead h3 {
            margin: 0;
            font-size: .88rem;
            font-weight: 700;
            color: var(--scfg-text);
            flex: 1;
            min-width: 0;
        }

        .scfg-badge-opt {
            font-size: .63rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            background: #f1f5f9;
            color: var(--scfg-muted);
            flex-shrink: 0;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        /* Card body */
        .scfg-cbody {
            padding: 16px;
        }

        /* ── Form Fields ──────────────────────────────────────────────────────── */
        .scfg-fg {
            margin-bottom: 13px;
        }

        .scfg-fg:last-child {
            margin-bottom: 0;
        }

        .scfg-lbl {
            display: block;
            font-size: .78rem;
            font-weight: 700;
            color: var(--scfg-text);
            margin-bottom: 5px;
        }

        .scfg-lbl .req {
            color: #ef4444;
            font-weight: 800;
            margin-left: 2px;
        }

        .scfg-lbl .opt-note {
            font-size: .7rem;
            font-weight: 400;
            color: var(--scfg-muted);
            margin-left: 5px;
        }

        /* Universal input style */
        .scfg-inp {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid var(--scfg-border);
            border-radius: var(--scfg-radius-input);
            font-size: .875rem;
            font-family: inherit;
            color: var(--scfg-text);
            background: #f8fafc;
            outline: none;
            box-sizing: border-box;
            -webkit-appearance: none;
            transition: border-color .18s, box-shadow .18s, background .18s;
            line-height: 1.5;
        }

        .scfg-inp:focus {
            border-color: var(--scfg-primary);
            background: var(--scfg-surface);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, .12);
        }

        .scfg-inp[readonly] {
            background: #f1f5f9;
            color: var(--scfg-muted);
            cursor: default;
        }

        .scfg-inp.iserr {
            border-color: #ef4444;
        }

        .scfg-inp.iserr:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, .12);
        }

        textarea.scfg-inp {
            resize: vertical;
            min-height: 76px;
        }

        /* Field error */
        .scfg-ferr {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .71rem;
            color: var(--scfg-danger);
            margin-top: 5px;
            font-weight: 600;
        }

        /* Field hint */
        .scfg-hint {
            font-size: .69rem;
            color: var(--scfg-subtle);
            margin-top: 4px;
            line-height: 1.5;
        }

        /* ── 2-Column Grid ────────────────────────────────────────────────────── */
        .scfg-g2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--scfg-gap);
        }

        /* ── Hari Checkbox Grid ───────────────────────────────────────────────── */
        .scfg-hari-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 8px;
        }

        .scfg-hcard {
            position: relative;
            cursor: pointer;
        }

        .scfg-hcard input[type="checkbox"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
            margin: 0;
        }

        .scfg-hbox {
            position: relative;
            z-index: 1;
            pointer-events: none;
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 9px 11px;
            border: 1.5px solid var(--scfg-border);
            border-radius: 9px;
            background: #f8fafc;
            font-size: .8rem;
            font-weight: 600;
            color: #475569;
            transition: border-color .15s, background .15s, color .15s;
            white-space: nowrap;
        }

        .scfg-hbox .shd {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--scfg-border);
            flex-shrink: 0;
            transition: background .15s;
        }

        .scfg-hcard input:checked~.scfg-hbox {
            border-color: var(--scfg-primary);
            background: #e0f2fe;
            color: var(--scfg-primary-dark);
        }

        .scfg-hcard input:checked~.scfg-hbox .shd {
            background: var(--scfg-primary);
        }

        .scfg-hcard:focus-within .scfg-hbox {
            outline: 2px solid var(--scfg-primary);
            outline-offset: 2px;
        }

        /* ── Map ──────────────────────────────────────────────────────────────── */
        .scfg-mwrap {
            border-radius: var(--scfg-radius-input);
            overflow: hidden;
            border: 1.5px solid var(--scfg-border);
            margin-bottom: 14px;
        }

        #scfgMap {
            width: 100%;
            height: 250px;
            z-index: 1;
        }

        .scfg-mstatus {
            padding: 8px 12px;
            background: #f8fafc;
            border-top: 1px solid var(--scfg-border);
            font-size: .73rem;
            color: var(--scfg-muted);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* ── Clear button ─────────────────────────────────────────────────────── */
        .scfg-clrbtn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 13px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 600;
            background: #fef2f2;
            color: var(--scfg-danger);
            border: 1.5px solid #fecaca;
            cursor: pointer;
            font-family: inherit;
            transition: background .15s;
        }

        .scfg-clrbtn:hover {
            background: #fee2e2;
        }

        /* ── WA Notification Item ─────────────────────────────────────────────── */
        .scfg-wa-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .scfg-wa-item:last-child {
            border-bottom: none;
        }

        .scfg-wa-ico {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .88rem;
        }

        .scfg-wa-info {
            flex: 1;
            min-width: 0;
        }

        .scfg-wa-title {
            font-size: .84rem;
            font-weight: 700;
            color: var(--scfg-text);
            margin-bottom: 2px;
        }

        .scfg-wa-desc {
            font-size: .74rem;
            color: var(--scfg-muted);
            line-height: 1.55;
        }

        .scfg-wa-dep {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 4px;
            font-size: .68rem;
            color: var(--scfg-primary-dark);
            background: #e0f2fe;
            padding: 2px 7px;
            border-radius: 20px;
            font-weight: 600;
        }

        /* Toggle WA — flexible shrink */
        .scfg-wa-item .scfg-toggle {
            flex-shrink: 0;
            margin-left: auto;
        }

        /* ── Laporan Guru (special WA item) ──────────────────────────────────── */
        .scfg-wa-laporan-guru {
            border-bottom: none;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 0;
            padding-bottom: 0;
        }

        .scfg-wa-laporan-top {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding-bottom: 10px;
            border-bottom: 1px dashed var(--scfg-border);
            margin-bottom: 12px;
        }

        .scfg-nomor-penerima-wrap {
            width: 100%;
        }

        /* ── Toggle Switch ────────────────────────────────────────────────────── */
        .scfg-toggle {
            position: relative;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .scfg-toggle input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .scfg-toggle-track {
            width: 48px;
            height: 26px;
            background: #cbd5e1;
            border-radius: 13px;
            transition: background .22s;
            display: flex;
            align-items: center;
            padding: 0 3px;
            box-sizing: border-box;
        }

        .scfg-toggle-thumb {
            width: 20px;
            height: 20px;
            background: var(--scfg-surface);
            border-radius: 50%;
            transition: transform .22s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
            flex-shrink: 0;
        }

        .scfg-toggle input:checked+.scfg-toggle-track {
            background: var(--scfg-success);
        }

        .scfg-toggle input:checked+.scfg-toggle-track .scfg-toggle-thumb {
            transform: translateX(22px);
        }

        /* Red variant */
        .scfg-toggle-red input:checked+.scfg-toggle-track {
            background: var(--scfg-danger);
        }

        /* Orange variant */
        .scfg-toggle-orange input:checked+.scfg-toggle-track {
            background: var(--scfg-warning);
        }

        .scfg-toggle:focus-within .scfg-toggle-track {
            outline: 2px solid var(--scfg-primary);
            outline-offset: 2px;
        }

        /* ── Inline Toggle Block ──────────────────────────────────────────────── */
        /* Replaces repetitive inline styles for toggle rows */
        .scfg-toggle-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 13px 14px;
            border: 1.5px solid var(--scfg-border);
            border-radius: 11px;
            background: #f8fafc;
            margin-bottom: 13px;
        }

        .scfg-toggle-row-info {
            flex: 1;
            min-width: 0;
        }

        .scfg-toggle-row-label {
            font-size: .86rem;
            font-weight: 700;
            color: var(--scfg-text);
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .scfg-toggle-row-desc {
            font-size: .74rem;
            color: var(--scfg-muted);
            margin-top: 3px;
            line-height: 1.5;
        }

        .scfg-toggle-row-danger {
            border-color: #fecaca;
            background: #fff5f5;
        }

        /* ── Auto Alfa detail section ─────────────────────────────────────────── */
        .scfg-alfa-detail {
            padding-top: 4px;
        }

        /* ── Pasal search badge ───────────────────────────────────────────────── */
        .scfg-pasal-badge {
            display: none;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            font-size: .81rem;
            color: var(--scfg-danger);
            background: #fef2f2;
            padding: 9px 12px;
            border-radius: 8px;
            border: 1px solid #fecaca;
        }

        .scfg-pasal-badge-text {
            flex: 1;
            line-height: 1.4;
        }

        .scfg-pasal-badge-clear {
            background: none;
            border: none;
            cursor: pointer;
            color: #ef4444;
            font-size: 1rem;
            padding: 0 4px;
            flex-shrink: 0;
            line-height: 1;
        }

        /* ── Pasal dropdown (fixed positioned) ───────────────────────────────── */
        .scfg-pasal-dropdown {
            display: none;
            position: fixed;
            background: var(--scfg-surface);
            border: 1.5px solid var(--scfg-border);
            border-radius: 10px;
            max-height: 260px;
            overflow-y: auto;
            z-index: 9999;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .14);
        }

        .pasal-dropdown-item {
            padding: 10px 14px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            transition: background .12s;
        }

        .pasal-dropdown-item:last-child {
            border-bottom: none;
        }

        .pasal-dropdown-item:hover,
        .pasal-dropdown-item[data-active] {
            background: #fef2f2;
        }

        .pasal-dropdown-item .pasal-item-label {
            font-size: .82rem;
            font-weight: 600;
            color: var(--scfg-text);
            display: block;
        }

        .pasal-dropdown-item .pasal-item-poin {
            font-size: .71rem;
            color: var(--scfg-muted);
            margin-top: 2px;
            display: block;
        }

        /* ── Info box variants ────────────────────────────────────────────────── */
        .scfg-infobox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 13px;
            border-radius: 9px;
            font-size: .78rem;
            line-height: 1.6;
        }

        .scfg-infobox-warn {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #c2410c;
        }

        .scfg-infobox-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        .scfg-infobox-yellow {
            background: #fefce8;
            border: 1px solid #fde68a;
            color: #854d0e;
        }

        .scfg-infobox i {
            margin-top: 1px;
            flex-shrink: 0;
        }

        /* ── Mode Libur Active Badge ──────────────────────────────────────────── */
        .scfg-libur-active-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            background: #ffedd5;
            color: #c2410c;
            font-size: .66rem;
            font-weight: 700;
            border: 1px solid #fed7aa;
            margin-left: auto;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        /* ── Action Bar ───────────────────────────────────────────────────────── */
        .scfg-bar {
            position: fixed;
            bottom: var(--footer-h, 60px);
            left: 0;
            right: 0;
            padding: 10px 16px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid var(--scfg-border);
            display: flex;
            gap: 10px;
            z-index: 100;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .07);
        }

        .scfg-sbtn {
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
            background: linear-gradient(135deg, var(--scfg-primary-dark), var(--scfg-primary));
            color: #fff;
            box-shadow: 0 3px 12px rgba(14, 165, 233, .3);
            transition: filter .18s, transform .12s;
        }

        .scfg-sbtn:hover {
            filter: brightness(1.07);
        }

        .scfg-sbtn:active {
            transform: scale(.98);
        }

        .scfg-sbtn:disabled {
            opacity: .65;
            cursor: not-allowed;
            transform: none;
        }

        /* ── Help Button ──────────────────────────────────────────────────────── */
        .scfg-help-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #fef9c3;
            border: 1.5px solid #fde047;
            color: #ca8a04;
            font-size: .78rem;
            cursor: pointer;
            flex-shrink: 0;
            margin-left: auto;
            transition: background .15s, transform .12s;
            outline: none;
            padding: 0;
        }

        .scfg-help-btn:hover {
            background: #fef08a;
            transform: scale(1.1);
        }

        .scfg-help-btn:focus {
            outline: 2px solid #fde047;
            outline-offset: 2px;
        }

        /* ── Help Modal ───────────────────────────────────────────────────────── */
        .scfg-help-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 9998;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .scfg-help-overlay.active {
            display: flex;
        }

        .scfg-help-modal {
            background: var(--scfg-surface);
            border-radius: 16px;
            max-width: 480px;
            width: 100%;
            max-height: 88vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
            animation: scfg-modal-in .2s ease;
            overflow: hidden;
        }

        @keyframes scfg-modal-in {
            from {
                opacity: 0;
                transform: scale(.94) translateY(8px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .scfg-help-mhead {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 18px;
            border-bottom: 1px solid #f1f5f9;
            flex-shrink: 0;
        }

        .scfg-help-mhead-ico {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: #fef9c3;
            border: 1.5px solid #fde047;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
            color: #ca8a04;
            flex-shrink: 0;
        }

        .scfg-help-mhead h4 {
            margin: 0;
            font-size: .9rem;
            font-weight: 800;
            color: var(--scfg-text);
            flex: 1;
        }

        .scfg-help-close {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--scfg-subtle);
            font-size: 1.1rem;
            padding: 4px;
            line-height: 1;
            border-radius: 6px;
            transition: color .15s;
        }

        .scfg-help-close:hover {
            color: #334155;
        }

        .scfg-help-mbody {
            padding: 16px 18px;
            overflow-y: auto;
            flex: 1;
            font-size: .83rem;
            color: #334155;
            line-height: 1.65;
        }

        .scfg-help-mbody h5 {
            margin: 0 0 6px;
            font-size: .77rem;
            font-weight: 700;
            color: var(--scfg-text);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .scfg-help-mbody p {
            margin: 0 0 10px;
        }

        .scfg-help-mbody ul {
            margin: 0 0 10px;
            padding-left: 18px;
        }

        .scfg-help-mbody ul li {
            margin-bottom: 4px;
        }

        .scfg-help-example {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 9px;
            padding: 10px 13px;
            margin-top: 10px;
        }

        .scfg-help-example .ex-label {
            font-size: .7rem;
            font-weight: 700;
            color: var(--scfg-primary-dark);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 6px;
            display: block;
        }

        .scfg-help-example table {
            width: 100%;
            border-collapse: collapse;
            font-size: .79rem;
        }

        .scfg-help-example table td {
            padding: 3px 6px;
            vertical-align: top;
        }

        .scfg-help-example table td:first-child {
            font-weight: 600;
            color: var(--scfg-primary-dark);
            white-space: nowrap;
            width: 44%;
        }

        .scfg-help-mfoot {
            padding: 12px 18px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            flex-shrink: 0;
        }

        .scfg-help-okbtn {
            padding: 9px 22px;
            border-radius: 9px;
            border: none;
            background: linear-gradient(135deg, var(--scfg-primary-dark), var(--scfg-primary));
            color: #fff;
            font-size: .83rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: filter .15s;
        }

        .scfg-help-okbtn:hover {
            filter: brightness(1.08);
        }

        /* ── Footer-bar override ──────────────────────────────────────────────── */
        #footer-bar {
            z-index: 999 !important;
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            display: flex !important;
            opacity: 1 !important;
            visibility: visible !important;
            transform: none !important;
            min-height: 60px !important;
        }

        /* ═══════════════════════════════════════════════════════════════════════
       RESPONSIVE
        ═══════════════════════════════════════════════════════════════════════ */

        /* Tablet (≤ 640px): single-column grids */
        @media (max-width: 640px) {
            .scfg-container {
                padding: 0 12px;
            }

            .scfg-strip {
                padding-left: 16px;
                padding-right: 16px;
                padding-bottom: 20px;
                margin-bottom: 14px;
            }

            .scfg-strip h2 {
                font-size: 1.1rem;
            }

            .scfg-g2 {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .scfg-hari-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .scfg-cbody {
                padding: 14px;
            }

            .scfg-card {
                margin-bottom: 12px;
            }

            #scfgMap {
                height: 210px;
            }

            .scfg-mstatus {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }

            .scfg-wa-item {
                gap: 10px;
            }

            .scfg-wa-ico {
                width: 30px;
                height: 30px;
                font-size: .82rem;
            }

            .scfg-wa-title {
                font-size: .82rem;
            }

            .scfg-wa-desc {
                font-size: .72rem;
            }

            .scfg-bar {
                padding: 10px 14px 14px;
            }

            .scfg-sbtn {
                font-size: .86rem;
                padding: 13px 14px;
            }

            .scfg-toggle-row {
                padding: 11px 12px;
            }
        }

        /* Mobile (≤ 420px): narrower */
        @media (max-width: 420px) {
            .scfg-hari-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .scfg-strip h2 {
                font-size: 1rem;
                flex-wrap: wrap;
            }

            #scfgMap {
                height: 185px;
            }

            .scfg-help-modal {
                border-radius: 12px;
            }
        }

        /* Very small (≤ 340px) */
        @media (max-width: 340px) {
            .scfg-hari-grid {
                grid-template-columns: 1fr 1fr;
            }

            .scfg-g2 {
                grid-template-columns: 1fr;
            }
        }

        /* ── Accessibility ────────────────────────────────────────────────────── */
        @media (prefers-contrast: high) {
            .scfg-card {
                border-width: 2px;
            }

            .scfg-inp {
                border-width: 2px;
            }

            .scfg-hbox {
                border-width: 2px;
            }

            .scfg-toggle-row {
                border-width: 2px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .scfg-dot,
            .scfg-inp,
            .scfg-hbox,
            .scfg-sbtn,
            .scfg-clrbtn,
            .scfg-help-modal,
            .scfg-toggle-track,
            .scfg-toggle-thumb {
                animation: none !important;
                transition: none !important;
            }
        }

        /* ── Auto Rules ──────────────────────────────────────────────── */
        .rule-item {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 8px;
            transition: border-color .15s;
        }
        .rule-item:hover { border-color: #cbd5e1; }
        .rule-inactive { opacity: .55; }
        .rule-item-header {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .rule-item-info { flex: 1; min-width: 0; }
        .rule-item-name {
            display: block;
            font-weight: 700;
            font-size: .83rem;
            color: #0f172a;
            margin-bottom: 3px;
        }
        .rule-item-meta {
            display: block;
            font-size: .73rem;
            color: #64748b;
            line-height: 1.5;
        }
        .rule-item-ket {
            font-size: .72rem;
            color: #94a3b8;
            margin-top: 5px;
            font-style: italic;
        }
        .rule-item-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        .rule-btn-edit, .rule-btn-del {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            border: none;
            cursor: pointer;
            font-size: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s;
        }
        .rule-btn-edit { background: #eff6ff; color: #3b82f6; }
        .rule-btn-edit:hover { background: #dbeafe; }
        .rule-btn-edit-green { background: #f0fdf4; color: #16a34a; }
        .rule-btn-edit-green:hover { background: #dcfce7; }
        .rule-btn-del { background: #fef2f2; color: #ef4444; }
        .rule-btn-del:hover { background: #fee2e2; }
        .rule-toggle { transform: scale(.85); }
        .rule-empty-hint {
            font-size: .8rem;
            color: #94a3b8;
            padding: 12px;
            border: 1px dashed #e2e8f0;
            border-radius: 8px;
            text-align: center;
        }
        .rule-add-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s;
        }
        .rule-add-btn:hover { background: #fee2e2; }
        .rule-add-btn-green {
            background: #f0fdf4;
            color: #16a34a;
            border-color: #bbf7d0;
        }
        .rule-add-btn-green:hover { background: #dcfce7; }

        /* ── Modal Rule ──────────────────────────────────────────────── */
        .rule-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        .rule-modal-overlay.open { display: flex; }
        .rule-modal {
            background: #fff;
            border-radius: 14px;
            width: min(560px, 96vw);
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
            padding: 24px;
        }
        .rule-modal-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .rule-modal-title .ico {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
        }
        .rule-modal-body .scfg-fg { margin-bottom: 14px; }
        .rule-modal-footer {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
        }
        .rule-modal-btn {
            padding: 8px 18px;
            border-radius: 8px;
            border: none;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
        }
        .rule-modal-btn-cancel {
            background: #f1f5f9;
            color: #64748b;
        }
        .rule-modal-btn-cancel:hover { background: #e2e8f0; }
        .rule-modal-btn-save {
            background: #dc2626;
            color: #fff;
        }
        .rule-modal-btn-save:hover { background: #b91c1c; }
        .rule-modal-btn-save-green {
            background: #16a34a;
            color: #fff;
        }
        .rule-modal-btn-save-green:hover { background: #15803d; }
        .rule-kondisi-wrap {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            margin-top: 4px;
        }
        .rule-kondisi-wrap .scfg-lbl { color: #475569; }
        .check-row-sm {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .8rem;
            color: #374151;
            margin-bottom: 6px;
            cursor: pointer;
        }
        .check-row-sm input { cursor: pointer; }
        .scfg-toggle-green .scfg-toggle-track { background: #cbd5e1; }
        .scfg-toggle-green input:checked ~ .scfg-toggle-track { background: #16a34a; }
    </style>
@endpush

@section('content')
    <div class="scfg-pg">

        {{-- ── Hero Strip ────────────────────────────────────────────────── --}}
        <div class="scfg-strip">
            <div class="scfg-live"><span class="scfg-dot"></span>Pengaturan Sistem</div>
            <h2><i class="fas fa-cog" aria-hidden="true"></i> Konfigurasi Sekolah</h2>
            <p>Atur jam, hari efektif, dan lokasi presensi sekolah</p>
        </div>

        <div class="scfg-container">

            @php
                $fmt = fn($v) => $v ? date('H:i', strtotime($v)) : '';
            @endphp

            <form id="scfgForm" action="{{ route('admin.school-config.update') }}" method="POST" novalidate>
                @csrf @method('PUT')

                {{-- ══════════════════════════════════════════════════════════
             1. IDENTITAS SISTEM
        ══════════════════════════════════════════════════════════ --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#dbeafe;color:#1d4ed8;"><i class="fas fa-school"
                                aria-hidden="true"></i></div>
                        <h3>Identitas Sistem</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('identitas_sistem')"
                            aria-label="Panduan Identitas Sistem"><i class="fas fa-lightbulb"></i></button>
                    </div>
                    <div class="scfg-cbody">
                        <div class="scfg-fg">
                            <label class="scfg-lbl" for="system_name">Nama Sistem <span class="req">*</span></label>
                            <input type="text" id="system_name" name="system_name" required
                                class="scfg-inp @error('system_name') iserr @enderror"
                                value="{{ old('system_name', $sekolah->system_name ?? 'SIS SMKN 5 Madiun') }}"
                                placeholder="Nama sistem aplikasi" maxlength="100">
                            @error('system_name')
                                <div class="scfg-ferr" role="alert"><i class="fas fa-exclamation-circle"
                                        aria-hidden="true"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
             2. IDENTITAS SEKOLAH
        ══════════════════════════════════════════════════════════ --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-building"
                                aria-hidden="true"></i></div>
                        <h3>Identitas Sekolah</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('identitas_sekolah')"
                            aria-label="Panduan Identitas Sekolah"><i class="fas fa-lightbulb"></i></button>
                    </div>
                    <div class="scfg-cbody">
                        <div class="scfg-fg">
                            <label class="scfg-lbl" for="sekolah">Nama Sekolah <span class="req">*</span></label>
                            <input type="text" id="sekolah" name="sekolah" required
                                class="scfg-inp @error('sekolah') iserr @enderror"
                                value="{{ old('sekolah', $sekolah->sekolah) }}" placeholder="Nama lengkap sekolah"
                                maxlength="255">
                            @error('sekolah')
                                <div class="scfg-ferr" role="alert"><i class="fas fa-exclamation-circle"
                                        aria-hidden="true"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="scfg-fg">
                            <label class="scfg-lbl" for="alsekolah">Alamat Sekolah</label>
                            <textarea id="alsekolah" name="alsekolah" rows="3" class="scfg-inp @error('alsekolah') iserr @enderror"
                                placeholder="Alamat lengkap sekolah">{{ old('alsekolah', $sekolah->alsekolah) }}</textarea>
                            @error('alsekolah')
                                <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                        aria-hidden="true"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="scfg-g2">
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="telp">Telepon</label>
                                <input type="text" id="telp" name="telp"
                                    class="scfg-inp @error('telp') iserr @enderror"
                                    value="{{ old('telp', $sekolah->telp) }}" placeholder="0351-464466" maxlength="20">
                                @error('telp')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="email">Email</label>
                                <input type="email" id="email" name="email"
                                    class="scfg-inp @error('email') iserr @enderror"
                                    value="{{ old('email', $sekolah->email) }}" placeholder="info@sekolah.sch.id"
                                    maxlength="255">
                                @error('email')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="scfg-g2">
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="kab">Kabupaten / Kota</label>
                                <input type="text" id="kab" name="kab"
                                    class="scfg-inp @error('kab') iserr @enderror"
                                    value="{{ old('kab', $sekolah->kab) }}" placeholder="Kota Madiun" maxlength="100">
                                @error('kab')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="alias">Alias / Kode</label>
                                <input type="text" id="alias" name="alias"
                                    class="scfg-inp @error('alias') iserr @enderror"
                                    value="{{ old('alias', $sekolah->alias) }}" placeholder="SMKN5MDN" maxlength="50">
                                @error('alias')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
             3. KEPALA SEKOLAH
        ══════════════════════════════════════════════════════════ --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#fed7d7;color:#c53030;"><i class="fas fa-user-tie"
                                aria-hidden="true"></i></div>
                        <h3>Kepala Sekolah</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('kepala_sekolah')"
                            aria-label="Panduan Kepala Sekolah"><i class="fas fa-lightbulb"></i></button>
                    </div>
                    <div class="scfg-cbody">
                        <div class="scfg-g2">
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="nama_ks">Nama Kepala Sekolah</label>
                                <input type="text" id="nama_ks" name="nama_ks"
                                    class="scfg-inp @error('nama_ks') iserr @enderror"
                                    value="{{ old('nama_ks', $sekolah->nama_ks) }}"
                                    placeholder="Dr. H. Ahmad Yani, M.Pd." maxlength="255">
                                @error('nama_ks')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="nip_ks">NIP Kepala Sekolah</label>
                                <input type="text" id="nip_ks" name="nip_ks"
                                    class="scfg-inp @error('nip_ks') iserr @enderror"
                                    value="{{ old('nip_ks', $sekolah->nip_ks) }}" placeholder="198001012010011001"
                                    maxlength="50">
                                @error('nip_ks')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
             4. WAKIL KEPALA SEKOLAH
        ══════════════════════════════════════════════════════════ --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#feebc8;color:#d69e2e;"><i class="fas fa-user-graduate"
                                aria-hidden="true"></i></div>
                        <h3>Wakil Kepala Sekolah</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('waka')"
                            aria-label="Panduan Waka"><i class="fas fa-lightbulb"></i></button>
                    </div>
                    <div class="scfg-cbody">
                        <div class="scfg-g2">
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="nama_waka">Nama Waka</label>
                                <input type="text" id="nama_waka" name="nama_waka"
                                    class="scfg-inp @error('nama_waka') iserr @enderror"
                                    value="{{ old('nama_waka', $sekolah->nama_waka) }}"
                                    placeholder="Drs. Siti Aminah, M.Pd." maxlength="255">
                                @error('nama_waka')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="nip_waka">NIP Waka</label>
                                <input type="text" id="nip_waka" name="nip_waka"
                                    class="scfg-inp @error('nip_waka') iserr @enderror"
                                    value="{{ old('nip_waka', $sekolah->nip_waka) }}" placeholder="198501022010012002"
                                    maxlength="50">
                                @error('nip_waka')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
             5. KETUA
        ══════════════════════════════════════════════════════════ --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#c4f1f9;color:#0e7490;"><i class="fas fa-user-cog"
                                aria-hidden="true"></i></div>
                        <h3>Ketua</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('ketua')"
                            aria-label="Panduan Ketua"><i class="fas fa-lightbulb"></i></button>
                    </div>
                    <div class="scfg-cbody">
                        <div class="scfg-g2">
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="nama_ketua">Nama Ketua</label>
                                <input type="text" id="nama_ketua" name="nama_ketua"
                                    class="scfg-inp @error('nama_ketua') iserr @enderror"
                                    value="{{ old('nama_ketua', $sekolah->nama_ketua) }}"
                                    placeholder="Ir. Budi Santoso, MT." maxlength="255">
                                @error('nama_ketua')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="nip_ketua">NIP Ketua</label>
                                <input type="text" id="nip_ketua" name="nip_ketua"
                                    class="scfg-inp @error('nip_ketua') iserr @enderror"
                                    value="{{ old('nip_ketua', $sekolah->nip_ketua) }}" placeholder="197801032008011003"
                                    maxlength="50">
                                @error('nip_ketua')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
             6. WEBSITE & MEDIA
        ══════════════════════════════════════════════════════════ --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#e0e7ff;color:#3730a3;"><i class="fas fa-globe"
                                aria-hidden="true"></i></div>
                        <h3>Website &amp; Media</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('website_media')"
                            aria-label="Panduan Website & Media"><i class="fas fa-lightbulb"></i></button>
                    </div>
                    <div class="scfg-cbody">
                        <div class="scfg-g2">
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="site_url">URL Website</label>
                                <input type="url" id="site_url" name="site_url"
                                    class="scfg-inp @error('site_url') iserr @enderror"
                                    value="{{ old('site_url', $sekolah->site_url) }}"
                                    placeholder="https://smkn5madiun.sch.id" maxlength="255">
                                @error('site_url')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="site_logo">URL Logo</label>
                                <input type="url" id="site_logo" name="site_logo"
                                    class="scfg-inp @error('site_logo') iserr @enderror"
                                    value="{{ old('site_logo', $sekolah->site_logo) }}" placeholder="https://…/logo.png"
                                    maxlength="255">
                                @error('site_logo')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="scfg-fg">
                            <label class="scfg-lbl" for="wasekolah">WhatsApp Sekolah</label>
                            <input type="text" id="wasekolah" name="wasekolah"
                                class="scfg-inp @error('wasekolah') iserr @enderror"
                                value="{{ old('wasekolah', $sekolah->wasekolah) }}" placeholder="6281234567890"
                                maxlength="20">
                            <span class="scfg-hint">Format internasional tanpa tanda +, awali dengan 62</span>
                            @error('wasekolah')
                                <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                        aria-hidden="true"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
             7. JADWAL ABSENSI SISWA
        ══════════════════════════════════════════════════════════ --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#fef3c7;color:#b45309;"><i class="fas fa-clock"
                                aria-hidden="true"></i></div>
                        <h3>Jadwal Absensi Siswa</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('jadwal_absensi')"
                            aria-label="Panduan Jadwal Absensi"><i class="fas fa-lightbulb"></i></button>
                    </div>
                    <div class="scfg-cbody">

                        {{-- Jam Normal --}}
                        <div class="scfg-fg">
                            <label class="scfg-lbl">Jadwal Normal</label>
                            <div class="scfg-g2">
                                <div>
                                    <label class="scfg-lbl" for="jam_masuk"
                                        style="font-weight:500;color:#64748b;font-size:.74rem;">Jam Masuk</label>
                                    <input type="time" id="jam_masuk" name="jam_masuk"
                                        class="scfg-inp @error('jam_masuk') iserr @enderror"
                                        value="{{ old('jam_masuk', $fmt($sekolah->jam_masuk)) }}">
                                    @error('jam_masuk')
                                        <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                                aria-hidden="true"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="scfg-lbl" for="jam_pulang"
                                        style="font-weight:500;color:#64748b;font-size:.74rem;">Jam Pulang</label>
                                    <input type="time" id="jam_pulang" name="jam_pulang"
                                        class="scfg-inp @error('jam_pulang') iserr @enderror"
                                        value="{{ old('jam_pulang', $fmt($sekolah->jam_pulang)) }}">
                                    @error('jam_pulang')
                                        <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                                aria-hidden="true"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Hari Khusus --}}
                        @php
                            $hariKhususRaw = old('hari_khusus', $sekolah->hari_khusus ?? null);
                            $hariKhususSelected = is_string($hariKhususRaw)
                                ? (json_decode($hariKhususRaw, true) ?:
                                [])
                                : (is_array($hariKhususRaw)
                                    ? $hariKhususRaw
                                    : ['Jumat']);
                            $hariKhususList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                        @endphp
                        <div class="scfg-fg">
                            <label class="scfg-lbl">Hari Khusus</label>
                            <div class="scfg-hari-grid" role="group" aria-label="Pilih hari khusus absensi">
                                @foreach ($hariKhususList as $h)
                                    <label class="scfg-hcard">
                                        <input type="checkbox" name="hari_khusus[]" value="{{ $h }}"
                                            {{ in_array($h, $hariKhususSelected) ? 'checked' : '' }}
                                            aria-label="{{ $h }}">
                                        <div class="scfg-hbox"><span class="shd"
                                                aria-hidden="true"></span>{{ $h }}</div>
                                    </label>
                                @endforeach
                            </div>
                            @error('hari_khusus')
                                <div class="scfg-ferr" style="margin-top:8px;"><i class="fas fa-exclamation-circle"
                                        aria-hidden="true"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Jam Khusus --}}
                        <div class="scfg-fg">
                            <label class="scfg-lbl">Jadwal Khusus <span class="opt-note">(hari yang dicentang di
                                    atas)</span></label>
                            <div class="scfg-g2">
                                <div>
                                    <label class="scfg-lbl" for="jam_masuk_khusus"
                                        style="font-weight:500;color:#64748b;font-size:.74rem;">Jam Masuk Khusus</label>
                                    <input type="time" id="jam_masuk_khusus" name="jam_masuk_khusus"
                                        class="scfg-inp @error('jam_masuk_khusus') iserr @enderror"
                                        value="{{ old('jam_masuk_khusus', $fmt($sekolah->jam_masuk_khusus ?? null)) }}">
                                    @error('jam_masuk_khusus')
                                        <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                                aria-hidden="true"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="scfg-lbl" for="jam_pulang_khusus"
                                        style="font-weight:500;color:#64748b;font-size:.74rem;">Jam Pulang Khusus</label>
                                    <input type="time" id="jam_pulang_khusus" name="jam_pulang_khusus"
                                        class="scfg-inp @error('jam_pulang_khusus') iserr @enderror"
                                        value="{{ old('jam_pulang_khusus', $fmt($sekolah->jam_pulang_khusus ?? null)) }}">
                                    @error('jam_pulang_khusus')
                                        <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                                aria-hidden="true"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
             8. KONFIGURASI KETERLAMBATAN
        ══════════════════════════════════════════════════════════ --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#fff7ed;color:#c2410c;"><i class="fas fa-hourglass-half"
                                aria-hidden="true"></i></div>
                        <h3>Konfigurasi Keterlambatan</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('keterlambatan')"
                            aria-label="Panduan Keterlambatan"><i class="fas fa-lightbulb"></i></button>
                    </div>
                    <div class="scfg-cbody">
                        <div class="scfg-g2">
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="jam_mulai_absensi">Jam Mulai Absensi</label>
                                <input type="time" id="jam_mulai_absensi" name="jam_mulai_absensi"
                                    class="scfg-inp @error('jam_mulai_absensi') iserr @enderror"
                                    value="{{ old('jam_mulai_absensi', $fmt($sekolah->jam_mulai_absensi ?? null)) }}">
                                <span class="scfg-hint">Scan sebelum jam ini ditolak</span>
                                @error('jam_mulai_absensi')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="batas_tepat_waktu">Batas Tepat Waktu</label>
                                <input type="time" id="batas_tepat_waktu" name="batas_tepat_waktu"
                                    class="scfg-inp @error('batas_tepat_waktu') iserr @enderror"
                                    value="{{ old('batas_tepat_waktu', $fmt($sekolah->batas_tepat_waktu ?? null)) }}">
                                <span class="scfg-hint">Scan setelah jam ini = Terlambat</span>
                                @error('batas_tepat_waktu')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
             9. NOTIFIKASI WHATSAPP
        ══════════════════════════════════════════════════════════ --}}

                {{-- Data JSON pasal untuk JS --}}
                <script id="pasal-alfa-data" type="application/json">
            {!! json_encode($pasalPelanggaran->map(fn($p) => [
                'id'    => $p->idpasal,
                'label' => '[' . $p->idpasal . '] ' . $p->pasal,
                'isi'   => $p->pasal,
                'poin'  => (int) ($p->poin_default ?? $p->skormin ?? 0),
            ])) !!}
        </script>

                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#dcfce7;color:#15803d;"><i class="fab fa-whatsapp"
                                aria-hidden="true"></i></div>
                        <h3>Notifikasi WhatsApp</h3>
                        <span class="scfg-badge-opt">Opsional</span>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('notif_wa')"
                            aria-label="Panduan Notifikasi WA"><i class="fas fa-lightbulb"></i></button>
                    </div>
                    <div class="scfg-cbody" style="padding-bottom:4px;">

                        {{-- Presensi Masuk --}}
                        <div class="scfg-wa-item">
                            <div class="scfg-wa-ico" style="background:#dcfce7;color:#15803d;"><i
                                    class="fas fa-sign-in-alt"></i></div>
                            <div class="scfg-wa-info">
                                <div class="scfg-wa-title">Presensi Masuk</div>
                                <div class="scfg-wa-desc">Kirim WA ke orang tua saat siswa absen masuk (Hadir / Terlambat)
                                </div>
                            </div>
                            <label class="scfg-toggle" for="wa_notif_masuk_enabled">
                                <input type="checkbox" id="wa_notif_masuk_enabled" name="wa_notif_masuk_enabled"
                                    value="1"
                                    {{ old('wa_notif_masuk_enabled', $sekolah->wa_notif_masuk_enabled ?? false) ? 'checked' : '' }}>
                                <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                            </label>
                        </div>

                        {{-- Presensi Pulang --}}
                        <div class="scfg-wa-item">
                            <div class="scfg-wa-ico" style="background:#fef3c7;color:#b45309;"><i
                                    class="fas fa-sign-out-alt"></i></div>
                            <div class="scfg-wa-info">
                                <div class="scfg-wa-title">Presensi Pulang</div>
                                <div class="scfg-wa-desc">Kirim WA ke orang tua saat siswa absen pulang</div>
                            </div>
                            <label class="scfg-toggle" for="wa_notif_pulang_enabled">
                                <input type="checkbox" id="wa_notif_pulang_enabled" name="wa_notif_pulang_enabled"
                                    value="1"
                                    {{ old('wa_notif_pulang_enabled', $sekolah->wa_notif_pulang_enabled ?? false) ? 'checked' : '' }}>
                                <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                            </label>
                        </div>

                        {{-- Auto Alfa --}}
                        <div class="scfg-wa-item">
                            <div class="scfg-wa-ico" style="background:#fee2e2;color:#dc2626;"><i
                                    class="fas fa-user-times"></i></div>
                            <div class="scfg-wa-info">
                                <div class="scfg-wa-title">Auto Alfa</div>
                                <div class="scfg-wa-desc">
                                    Kirim WA ke orang tua saat siswa otomatis dicatat Alfa
                                    <span class="scfg-wa-dep"><i class="fas fa-link" aria-hidden="true"></i> Aktif jika
                                        fitur Auto Alfa dinyalakan</span>
                                </div>
                            </div>
                            <label class="scfg-toggle" for="wa_notif_alfa_enabled">
                                <input type="checkbox" id="wa_notif_alfa_enabled" name="wa_notif_alfa_enabled"
                                    value="1"
                                    {{ old('wa_notif_alfa_enabled', $sekolah->wa_notif_alfa_enabled ?? false) ? 'checked' : '' }}>
                                <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                            </label>
                        </div>

                        {{-- Absen Event --}}
                        <div class="scfg-wa-item">
                            <div class="scfg-wa-ico" style="background:#ede9fe;color:#7c3aed;"><i
                                    class="fas fa-calendar-check"></i></div>
                            <div class="scfg-wa-info">
                                <div class="scfg-wa-title">Absen Kegiatan / Event</div>
                                <div class="scfg-wa-desc">Kirim WA ke orang tua saat siswa scan absen event sekolah</div>
                            </div>
                            <label class="scfg-toggle" for="wa_notif_event_enabled">
                                <input type="checkbox" id="wa_notif_event_enabled" name="wa_notif_event_enabled"
                                    value="1"
                                    {{ old('wa_notif_event_enabled', $sekolah->wa_notif_event_enabled ?? false) ? 'checked' : '' }}>
                                <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                            </label>
                        </div>

                        {{-- Peringatan Poin Tatib --}}
                        <div class="scfg-wa-item">
                            <div class="scfg-wa-ico" style="background:#fff7ed;color:#c2410c;"><i
                                    class="fas fa-exclamation-triangle"></i></div>
                            <div class="scfg-wa-info">
                                <div class="scfg-wa-title">Peringatan Poin Pelanggaran</div>
                                <div class="scfg-wa-desc">Kirim WA ke orang tua saat poin pelanggaran mencapai ambang batas
                                </div>
                            </div>
                            <label class="scfg-toggle" for="wa_notif_tatib_enabled">
                                <input type="checkbox" id="wa_notif_tatib_enabled" name="wa_notif_tatib_enabled"
                                    value="1"
                                    {{ old('wa_notif_tatib_enabled', $sekolah->wa_notif_tatib_enabled ?? false) ? 'checked' : '' }}>
                                <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                            </label>
                        </div>

                        {{-- Laporan Kehadiran Guru --}}
                        <div class="scfg-wa-item scfg-wa-laporan-guru" style="padding-bottom:12px;">
                            <div class="scfg-wa-laporan-top">
                                <div class="scfg-wa-ico" style="background:#e0f2fe;color:#0369a1;flex-shrink:0;"><i
                                        class="fas fa-chalkboard-teacher"></i></div>
                                <div class="scfg-wa-info">
                                    <div class="scfg-wa-title">Laporan Kehadiran Guru</div>
                                    <div class="scfg-wa-desc">Kirim WA ke penerima yang ditentukan saat laporan kehadiran
                                        guru dikirim</div>
                                </div>
                                <label class="scfg-toggle" for="wa_notif_laporan_guru_enabled" style="flex-shrink:0;">
                                    <input type="checkbox" id="wa_notif_laporan_guru_enabled"
                                        name="wa_notif_laporan_guru_enabled" value="1"
                                        onchange="toggleLaporanGuruNomor(this)"
                                        {{ old('wa_notif_laporan_guru_enabled', $sekolah->wa_notif_laporan_guru_enabled ?? false) ? 'checked' : '' }}>
                                    <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                                </label>
                            </div>
                            {{-- Nomor penerima (expand saat toggle ON) --}}
                            <div id="laporanGuruNomorWrap" class="scfg-nomor-penerima-wrap"
                                style="display:{{ old('wa_notif_laporan_guru_enabled', $sekolah->wa_notif_laporan_guru_enabled ?? false) ? 'block' : 'none' }};">
                                @php
                                    $nomorLG = $sekolah->wa_notif_laporan_guru_nomor ?? [];
                                    $nomorLGText = is_array($nomorLG) ? implode("\n", $nomorLG) : $nomorLG;
                                @endphp
                                <label class="scfg-lbl" for="wa_notif_laporan_guru_nomor">
                                    Nomor Penerima <span class="opt-note">(satu nomor per baris)</span>
                                </label>
                                <textarea id="wa_notif_laporan_guru_nomor" name="wa_notif_laporan_guru_nomor" rows="3"
                                    class="scfg-inp @error('wa_notif_laporan_guru_nomor') iserr @enderror"
                                    placeholder="6281234567890&#10;6289876543210" style="font-family:monospace;font-size:.82rem;">{{ old('wa_notif_laporan_guru_nomor', $nomorLGText) }}</textarea>
                                @error('wa_notif_laporan_guru_nomor')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
             10. AUTO ALFA SISWA
        ══════════════════════════════════════════════════════════ --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-robot"
                                aria-hidden="true"></i></div>
                        <h3>Auto Alfa Siswa</h3>
                        <span class="scfg-badge-opt">Opsional</span>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('auto_alfa')"
                            aria-label="Panduan Auto Alfa"><i class="fas fa-lightbulb"></i></button>
                    </div>
                    <div class="scfg-cbody">

                        {{-- Toggle Aktifkan --}}
                        <div class="scfg-toggle-row scfg-toggle-row-danger">
                            <div class="scfg-toggle-row-info">
                                <div class="scfg-toggle-row-label">
                                    <i class="fas fa-power-off" style="color:#dc2626;" aria-hidden="true"></i>
                                    Aktifkan Auto Alfa
                                </div>
                            </div>
                            <label class="scfg-toggle scfg-toggle-red" for="auto_alfa_enabled">
                                <input type="checkbox" id="auto_alfa_enabled" name="auto_alfa_enabled" value="1"
                                    {{ old('auto_alfa_enabled', $sekolah->auto_alfa_enabled ?? false) ? 'checked' : '' }}
                                    onchange="toggleAutoAlfaSection()">
                                <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                            </label>
                        </div>

                        {{-- Detail (muncul saat aktif) --}}
                        <div id="autoAlfaDetail" class="scfg-alfa-detail"
                            style="display:{{ old('auto_alfa_enabled', $sekolah->auto_alfa_enabled ?? false) ? 'block' : 'none' }};">

                            <div class="scfg-g2" style="margin-bottom:13px;">
                                <div class="scfg-fg">
                                    <label class="scfg-lbl" for="batas_absen_masuk">
                                        Batas Akhir Absen Masuk
                                        <span class="opt-note">(opsional)</span>
                                    </label>
                                    <input type="time" id="batas_absen_masuk" name="batas_absen_masuk"
                                        class="scfg-inp @error('batas_absen_masuk') iserr @enderror"
                                        value="{{ old('batas_absen_masuk', $fmt($sekolah->batas_absen_masuk ?? null)) }}">
                                    <span class="scfg-hint">Kosong = ikuti jam eksekusi</span>
                                    @error('batas_absen_masuk')
                                        <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                                aria-hidden="true"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="scfg-fg">
                                    <label class="scfg-lbl" for="jam_eksekusi_auto_alfa">
                                        Jam Eksekusi Auto Alfa <span class="req">*</span>
                                    </label>
                                    <input type="time" id="jam_eksekusi_auto_alfa" name="jam_eksekusi_auto_alfa"
                                        class="scfg-inp @error('jam_eksekusi_auto_alfa') iserr @enderror"
                                        value="{{ old('jam_eksekusi_auto_alfa', $fmt($sekolah->jam_eksekusi_auto_alfa ?? null)) }}">
                                    <span class="scfg-hint">Sebaiknya ≥ Batas Akhir Absen</span>
                                    @error('jam_eksekusi_auto_alfa')
                                        <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                                aria-hidden="true"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Toggle Auto Point --}}
                            <div class="scfg-toggle-row scfg-toggle-row-danger">
                                <div class="scfg-toggle-row-info">
                                    <div class="scfg-toggle-row-label">
                                        <i class="fas fa-gavel" style="color:#dc2626;" aria-hidden="true"></i>
                                        Auto Point Pelanggaran Alfa
                                    </div>
                                    <div class="scfg-toggle-row-desc">Siswa yang di-alfa-kan langsung mendapat poin
                                        pelanggaran</div>
                                </div>
                                <label class="scfg-toggle scfg-toggle-red" for="auto_point_alfa_enabled">
                                    <input type="checkbox" id="auto_point_alfa_enabled" name="auto_point_alfa_enabled"
                                        value="1"
                                        {{ old('auto_point_alfa_enabled', $sekolah->auto_point_alfa_enabled ?? false) ? 'checked' : '' }}
                                        onchange="toggleAutoPointAlfa()">
                                    <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                                </label>
                            </div>

                            {{-- Pilih Pasal --}}
                            <div id="pasalAlfaGroup"
                                style="display:{{ old('auto_point_alfa_enabled', $sekolah->auto_point_alfa_enabled ?? false) ? 'block' : 'none' }};">
                                <div class="scfg-fg" style="margin-bottom:0;">
                                    <label class="scfg-lbl">Pasal Pelanggaran untuk Auto Alfa <span
                                            class="req">*</span></label>
                                    <input type="hidden" name="pasal_alfa_id" id="pasal_alfa_hidden"
                                        value="{{ old('pasal_alfa_id', $sekolah->pasal_alfa_id ?? '') }}">
                                    <input type="text" id="pasal_alfa_search"
                                        class="scfg-inp @error('pasal_alfa_id') iserr @enderror"
                                        placeholder="Ketik kode atau nama pasal…" autocomplete="off">
                                    @error('pasal_alfa_id')
                                        <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                                aria-hidden="true"></i>{{ $message }}</div>
                                    @enderror
                                    <div id="pasal_alfa_badge" class="scfg-pasal-badge">
                                        <i class="fas fa-tag" aria-hidden="true" style="flex-shrink:0;"></i>
                                        <span id="pasal_alfa_badge_text" class="scfg-pasal-badge-text"></span>
                                        <button type="button" id="pasal_alfa_clear" class="scfg-pasal-badge-clear"
                                            aria-label="Hapus pilihan pasal">✕</button>
                                    </div>
                                </div>
                            </div>

                        </div>{{-- /#autoAlfaDetail --}}
                    </div>
                </div>

                {{-- Dropdown pasal (di luar card supaya tidak terkena overflow:hidden) --}}
                <div id="pasal_alfa_dropdown" class="scfg-pasal-dropdown"></div>

                {{-- ══════════════════════════════════════════════════════════
             10b. AUTO POIN TERLAMBAT
        ══════════════════════════════════════════════════════════ --}}

                {{-- Data JSON pasal pelanggaran untuk JS --}}
                <script id="pasal-terlambat-data" type="application/json">
                    {!! json_encode($pasalPelanggaran->map(fn($p) => [
                        'id'    => $p->idpasal,
                        'label' => '[' . $p->idpasal . '] ' . $p->pasal,
                        'isi'   => $p->pasal,
                        'poin'  => (int) ($p->poin_default ?? $p->skormin ?? 0),
                    ])) !!}
                </script>

                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#fff7ed;color:#f97316;">
                            <i class="fas fa-clock" aria-hidden="true"></i>
                        </div>
                        <h3>Auto Poin Terlambat</h3>
                        <span class="scfg-badge-opt">Opsional</span>
                    </div>
                    <div class="scfg-cbody">
                        <div class="scfg-toggle-row" style="border-bottom:1px solid #f1f5f9;padding-bottom:12px;margin-bottom:12px;">
                            <div class="scfg-toggle-row-info">
                                <div class="scfg-toggle-row-label">
                                    <i class="fas fa-power-off" style="color:#f97316;" aria-hidden="true"></i>
                                    Aktifkan Auto Poin Terlambat
                                </div>
                                <div class="scfg-toggle-row-desc">Siswa yang datang terlambat otomatis mendapat poin pelanggaran</div>
                            </div>
                            <label class="scfg-toggle" for="auto_poin_terlambat_enabled">
                                <input type="checkbox" id="auto_poin_terlambat_enabled"
                                    name="auto_poin_terlambat_enabled" value="1"
                                    {{ old('auto_poin_terlambat_enabled', $sekolah->auto_poin_terlambat_enabled ?? false) ? 'checked' : '' }}
                                    onchange="toggleAutoPoinTerlambat()">
                                <span class="scfg-toggle-track" style="{{ old('auto_poin_terlambat_enabled', $sekolah->auto_poin_terlambat_enabled ?? false) ? 'background:#f97316;' : '' }}">
                                    <span class="scfg-toggle-thumb"></span>
                                </span>
                            </label>
                        </div>

                        <div id="pasalTerlambatGroup"
                            style="display:{{ old('auto_poin_terlambat_enabled', $sekolah->auto_poin_terlambat_enabled ?? false) ? 'block' : 'none' }};">
                            <div class="scfg-fg" style="margin-bottom:0;">
                                <label class="scfg-lbl">Pasal Pelanggaran untuk Terlambat <span class="req">*</span></label>
                                <input type="hidden" name="pasal_terlambat_id" id="pasal_terlambat_hidden"
                                    value="{{ old('pasal_terlambat_id', $sekolah->pasal_terlambat_id ?? '') }}">
                                <input type="text" id="pasal_terlambat_search"
                                    class="scfg-inp @error('pasal_terlambat_id') iserr @enderror"
                                    placeholder="Ketik kode atau nama pasal… (contoh: B001)" autocomplete="off">
                                @error('pasal_terlambat_id')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle" aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                                <div id="pasal_terlambat_badge" class="scfg-pasal-badge" style="display:none;">
                                    <i class="fas fa-tag" aria-hidden="true" style="flex-shrink:0;"></i>
                                    <span id="pasal_terlambat_badge_text" class="scfg-pasal-badge-text"></span>
                                    <button type="button" id="pasal_terlambat_clear" class="scfg-pasal-badge-clear"
                                        aria-label="Hapus pilihan pasal">✕</button>
                                </div>
                                <span class="scfg-hint">Rekomendasi: B001 Datang terlambat (poin 10)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="pasal_terlambat_dropdown" class="scfg-pasal-dropdown"></div>

                {{-- ══════════════════════════════════════════════════════════
             10c. AUTO POIN HADIR
        ══════════════════════════════════════════════════════════ --}}

                {{-- Data JSON pasal penghargaan untuk JS --}}
                <script id="pasal-hadir-data" type="application/json">
                    {!! json_encode($pasalPenghargaan->map(fn($p) => [
                        'id'    => $p->idpasal,
                        'label' => '[' . $p->idpasal . '] ' . $p->pasal,
                        'isi'   => $p->pasal,
                        'poin'  => (int) ($p->poin_default ?? $p->skormin ?? 0),
                    ])) !!}
                </script>

                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#dcfce7;color:#16a34a;">
                            <i class="fas fa-user-check" aria-hidden="true"></i>
                        </div>
                        <h3>Auto Poin Hadir Tepat Waktu</h3>
                        <span class="scfg-badge-opt">Opsional</span>
                    </div>
                    <div class="scfg-cbody">
                        <div class="scfg-toggle-row" style="border-bottom:1px solid #f1f5f9;padding-bottom:12px;margin-bottom:12px;">
                            <div class="scfg-toggle-row-info">
                                <div class="scfg-toggle-row-label">
                                    <i class="fas fa-power-off" style="color:#16a34a;" aria-hidden="true"></i>
                                    Aktifkan Auto Poin Hadir
                                </div>
                                <div class="scfg-toggle-row-desc">Siswa yang hadir tepat waktu otomatis mendapat poin penghargaan</div>
                            </div>
                            <label class="scfg-toggle scfg-toggle-green" for="auto_poin_hadir_enabled">
                                <input type="checkbox" id="auto_poin_hadir_enabled"
                                    name="auto_poin_hadir_enabled" value="1"
                                    {{ old('auto_poin_hadir_enabled', $sekolah->auto_poin_hadir_enabled ?? false) ? 'checked' : '' }}
                                    onchange="toggleAutoPoinHadir()">
                                <span class="scfg-toggle-track" style="{{ old('auto_poin_hadir_enabled', $sekolah->auto_poin_hadir_enabled ?? false) ? 'background:#16a34a;' : '' }}">
                                    <span class="scfg-toggle-thumb"></span>
                                </span>
                            </label>
                        </div>

                        <div id="pasalHadirGroup"
                            style="display:{{ old('auto_poin_hadir_enabled', $sekolah->auto_poin_hadir_enabled ?? false) ? 'block' : 'none' }};">
                            <div class="scfg-fg" style="margin-bottom:0;">
                                <label class="scfg-lbl">Pasal Penghargaan untuk Hadir Tepat Waktu <span class="req">*</span></label>
                                <input type="hidden" name="pasal_hadir_id" id="pasal_hadir_hidden"
                                    value="{{ old('pasal_hadir_id', $sekolah->pasal_hadir_id ?? '') }}">
                                <input type="text" id="pasal_hadir_search"
                                    class="scfg-inp @error('pasal_hadir_id') iserr @enderror"
                                    placeholder="Ketik kode atau nama pasal… (contoh: K001)" autocomplete="off">
                                @error('pasal_hadir_id')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle" aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                                <div id="pasal_hadir_badge" class="scfg-pasal-badge" style="display:none;">
                                    <i class="fas fa-tag" aria-hidden="true" style="flex-shrink:0;"></i>
                                    <span id="pasal_hadir_badge_text" class="scfg-pasal-badge-text"></span>
                                    <button type="button" id="pasal_hadir_clear" class="scfg-pasal-badge-clear"
                                        aria-label="Hapus pilihan pasal">✕</button>
                                </div>
                                <span class="scfg-hint">Rekomendasi: K001 Hadir tepat waktu (poin 3)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="pasal_hadir_dropdown" class="scfg-pasal-dropdown"></div>

                {{-- ══════════════════════════════════════════════════════════
             11. AUTO RULES PELANGGARAN
        ══════════════════════════════════════════════════════════ --}}

                {{-- Data pasal pelanggaran untuk JS --}}
                <script id="pasal-pel-rule-data" type="application/json">
                    {!! json_encode($pasalPelanggaran->map(fn($p) => [
                        'id'    => $p->idpasal,
                        'label' => '[' . $p->idpasal . '] ' . $p->pasal,
                        'isi'   => $p->pasal,
                        'poin'  => (int) ($p->skormax ?: $p->skormin ?: 0),
                        'min'   => (int) $p->skormin,
                        'max'   => (int) $p->skormax,
                    ])) !!}
                </script>

                <div class="scfg-card" id="cardAutoPelanggaranRules">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-gavel" aria-hidden="true"></i></div>
                        <h3>Auto Rules Pelanggaran</h3>
                        <span class="scfg-badge-opt">Opsional</span>
                    </div>
                    <div class="scfg-cbody">
                        <p style="font-size:.82rem;color:#64748b;margin-bottom:14px;">
                            Konfigurasi aturan pemberian poin pelanggaran secara otomatis berdasarkan kondisi tertentu (misal: alfa berulang, terlambat berulang, dll).
                        </p>

                        {{-- List rules yang sudah ada --}}
                        <div id="pelanggaranRulesList">
                            @forelse($autoPelanggaranRules as $rule)
                                <div class="rule-item {{ $rule->aktif ? '' : 'rule-inactive' }}" data-id="{{ $rule->id }}" id="pel-rule-{{ $rule->id }}">
                                    <div class="rule-item-header">
                                        <div class="rule-item-info">
                                            <span class="rule-item-name">{{ $rule->nama_rule }}</span>
                                            <span class="rule-item-meta">
                                                {{ $rule->trigger_label }}
                                                @if($rule->threshold_hari) · ≥{{ $rule->threshold_hari }} hari @endif
                                                @if($rule->periode_bulan) · {{ $rule->periode_bulan }} bln @endif
                                                · {{ $rule->pasal ? '[' . $rule->pasal->idpasal . '] ' . \Illuminate\Support\Str::limit($rule->pasal->pasal, 40) : '—' }}
                                                · <strong>{{ $rule->poin_efektif }} poin</strong>
                                            </span>
                                        </div>
                                        <div class="rule-item-actions">
                                            <label class="scfg-toggle scfg-toggle-red rule-toggle" title="{{ $rule->aktif ? 'Nonaktifkan' : 'Aktifkan' }}" onclick="togglePelanggaranRule({{ $rule->id }}, this)">
                                                <input type="checkbox" {{ $rule->aktif ? 'checked' : '' }} onclick="event.preventDefault()">
                                                <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                                            </label>
                                            <button type="button" class="rule-btn-edit" onclick="editPelanggaranRule({{ $rule->id }})" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                                            <button type="button" class="rule-btn-del" onclick="deletePelanggaranRule({{ $rule->id }})" title="Hapus"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                    @if($rule->keterangan)
                                        <div class="rule-item-ket">{{ $rule->keterangan }}</div>
                                    @endif
                                </div>
                            @empty
                                <div id="pelEmptyHint" class="rule-empty-hint"><i class="fas fa-info-circle"></i> Belum ada rule. Klik tombol di bawah untuk menambah.</div>
                            @endforelse
                        </div>

                        <button type="button" class="rule-add-btn" onclick="openPelanggaranRuleModal()" style="margin-top:14px;">
                            <i class="fas fa-plus"></i> Tambah Rule Pelanggaran
                        </button>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
             12. AUTO RULES PENGHARGAAN
        ══════════════════════════════════════════════════════════ --}}

                {{-- Data pasal penghargaan untuk JS --}}
                <script id="pasal-pen-rule-data" type="application/json">
                    {!! json_encode($pasalPenghargaan->map(fn($p) => [
                        'id'    => $p->idpasal,
                        'label' => '[' . $p->idpasal . '] ' . $p->pasal,
                        'isi'   => $p->pasal,
                        'poin'  => (int) ($p->skormax ?: $p->skormin ?: 0),
                        'min'   => (int) $p->skormin,
                        'max'   => (int) $p->skormax,
                    ])) !!}
                </script>

                <div class="scfg-card" id="cardAutoPenghargaanRules">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-star" aria-hidden="true"></i></div>
                        <h3>Auto Rules Penghargaan</h3>
                        <span class="scfg-badge-opt">Opsional</span>
                    </div>
                    <div class="scfg-cbody">
                        <p style="font-size:.82rem;color:#64748b;margin-bottom:14px;">
                            Konfigurasi aturan pemberian poin penghargaan secara otomatis berdasarkan kondisi kehadiran (misal: hadir full 1 bulan, dll).
                        </p>

                        {{-- List rules yang sudah ada --}}
                        <div id="penghargaanRulesList">
                            @forelse($autoPenghargaanRules as $rule)
                                <div class="rule-item {{ $rule->aktif ? '' : 'rule-inactive' }}" data-id="{{ $rule->id }}" id="pen-rule-{{ $rule->id }}">
                                    <div class="rule-item-header">
                                        <div class="rule-item-info">
                                            <span class="rule-item-name">{{ $rule->nama_rule }}</span>
                                            <span class="rule-item-meta">
                                                {{ $rule->trigger_label }}
                                                · {{ $rule->periode_bulan }} bln
                                                @if($rule->izin_dihitung_hadir) · izin=hadir @endif
                                                @if($rule->sakit_dihitung_hadir) · sakit=hadir @endif
                                                @if($rule->terlambat_dihitung_hadir) · terlambat=hadir @endif
                                                · {{ $rule->pasal ? '[' . $rule->pasal->idpasal . '] ' . \Illuminate\Support\Str::limit($rule->pasal->pasal, 40) : '—' }}
                                                · <strong>{{ $rule->poin_efektif }} poin</strong>
                                            </span>
                                        </div>
                                        <div class="rule-item-actions">
                                            <label class="scfg-toggle scfg-toggle-green rule-toggle" title="{{ $rule->aktif ? 'Nonaktifkan' : 'Aktifkan' }}" onclick="togglePenghargaanRule({{ $rule->id }}, this)">
                                                <input type="checkbox" {{ $rule->aktif ? 'checked' : '' }} onclick="event.preventDefault()">
                                                <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                                            </label>
                                            <button type="button" class="rule-btn-edit rule-btn-edit-green" onclick="editPenghargaanRule({{ $rule->id }})" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                                            <button type="button" class="rule-btn-del" onclick="deletePenghargaanRule({{ $rule->id }})" title="Hapus"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                    @if($rule->keterangan)
                                        <div class="rule-item-ket">{{ $rule->keterangan }}</div>
                                    @endif
                                </div>
                            @empty
                                <div id="penEmptyHint" class="rule-empty-hint"><i class="fas fa-info-circle"></i> Belum ada rule. Klik tombol di bawah untuk menambah.</div>
                            @endforelse
                        </div>

                        <button type="button" class="rule-add-btn rule-add-btn-green" onclick="openPenghargaanRuleModal()" style="margin-top:14px;">
                            <i class="fas fa-plus"></i> Tambah Rule Penghargaan
                        </button>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
             13. MODE LIBUR PANJANG
        ══════════════════════════════════════════════════════════ --}}
                @php
                    $liburMode = old('libur_mode', $sekolah->libur_mode ?? false);
                    $liburDari = old('libur_dari', optional($sekolah->libur_dari)->format('Y-m-d') ?? '');
                    $liburSampai = old('libur_sampai', optional($sekolah->libur_sampai)->format('Y-m-d') ?? '');
                    $sedangLibur = $sekolah?->sedangLibur() ?? false;
                @endphp
                <div class="scfg-card"
                    style="border-color:{{ $sedangLibur ? '#f97316' : 'var(--scfg-border)' }};border-width:{{ $sedangLibur ? '2px' : '1px' }};">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#ffedd5;color:#c2410c;"><i class="fas fa-umbrella-beach"
                                aria-hidden="true"></i></div>
                        <h3>Mode Libur Panjang</h3>
                        @if ($sedangLibur)
                            <span class="scfg-libur-active-badge"><i class="fas fa-circle" style="font-size:.4rem;"
                                    aria-hidden="true"></i> Aktif Sekarang</span>
                        @endif
                    </div>
                    <div class="scfg-cbody">

                        {{-- Info box --}}
                        <div class="scfg-infobox scfg-infobox-warn" style="margin-bottom:14px;">
                            <i class="fas fa-info-circle" aria-hidden="true"></i>
                            <div>
                                <strong>Apa ini?</strong> Saat Mode Libur Panjang aktif dan hari ini berada dalam rentang
                                tanggal yang ditentukan, <strong>semua proses otomatis</strong> akan berhenti: Auto Alfa,
                                Auto-fill absen, Auto Point Pelanggaran Event, serta semua notifikasi WhatsApp.
                            </div>
                        </div>

                        {{-- Toggle --}}
                        <div class="scfg-toggle-row" style="margin-bottom:14px;">
                            <div class="scfg-toggle-row-info">
                                <div class="scfg-toggle-row-label">
                                    <i class="fas fa-power-off" style="color:#c2410c;" aria-hidden="true"></i>
                                    Aktifkan Mode Libur Panjang
                                </div>
                            </div>
                            <label class="scfg-toggle scfg-toggle-orange" for="libur_mode">
                                <input type="checkbox" id="libur_mode" name="libur_mode" value="1"
                                    {{ $liburMode ? 'checked' : '' }} onchange="toggleLiburSection()">
                                <span class="scfg-toggle-track" style="{{ $liburMode ? 'background:#f97316;' : '' }}">
                                    <span class="scfg-toggle-thumb"></span>
                                </span>
                            </label>
                        </div>

                        {{-- Rentang Tanggal --}}
                        <div class="scfg-g2" style="margin-bottom:10px;">
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="libur_dari">
                                    <i class="fas fa-calendar-day" style="color:#f97316;margin-right:3px;"
                                        aria-hidden="true"></i>
                                    Mulai Libur <span class="req">*</span>
                                </label>
                                <input type="date" id="libur_dari" name="libur_dari"
                                    class="scfg-inp @error('libur_dari') iserr @enderror" value="{{ $liburDari }}">
                                @error('libur_dari')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="libur_sampai">
                                    <i class="fas fa-calendar-check" style="color:#f97316;margin-right:3px;"
                                        aria-hidden="true"></i>
                                    Sampai Libur <span class="req">*</span>
                                </label>
                                <input type="date" id="libur_sampai" name="libur_sampai"
                                    class="scfg-inp @error('libur_sampai') iserr @enderror" value="{{ $liburSampai }}">
                                @error('libur_sampai')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @if ($sedangLibur)
                            <div class="scfg-infobox scfg-infobox-warn">
                                <i class="fas fa-pause-circle" aria-hidden="true"></i>
                                <span>Sistem sedang libur. Semua proses otomatis <strong>tidak berjalan</strong> hingga
                                    {{ \Carbon\Carbon::parse($sekolah->libur_sampai)->translatedFormat('d F Y') }}.</span>
                            </div>
                        @elseif ($liburMode && $liburDari && $liburSampai)
                            <div class="scfg-infobox scfg-infobox-success">
                                <i class="fas fa-check-circle" aria-hidden="true"></i>
                                <span>Mode libur terjadwal:
                                    <strong>{{ \Carbon\Carbon::parse($liburDari)->translatedFormat('d F Y') }}</strong> –
                                    <strong>{{ \Carbon\Carbon::parse($liburSampai)->translatedFormat('d F Y') }}</strong></span>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
             12. HARI EFEKTIF SEKOLAH
        ══════════════════════════════════════════════════════════ --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-calendar-week"
                                aria-hidden="true"></i></div>
                        <h3>Hari Efektif Sekolah</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('hari_efektif')"
                            aria-label="Panduan Hari Efektif"><i class="fas fa-lightbulb"></i></button>
                    </div>
                    <div class="scfg-cbody">
                        @php
                            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                            $selectedHari = old(
                                'hari_efektif',
                                is_string($sekolah->hari_efektif)
                                    ? json_decode($sekolah->hari_efektif, true)
                                    : $sekolah->hari_efektif ?? [],
                            );
                        @endphp
                        <div class="scfg-hari-grid" role="group" aria-label="Pilih hari efektif sekolah">
                            @foreach ($hariList as $h)
                                <label class="scfg-hcard">
                                    <input type="checkbox" name="hari_efektif[]" value="{{ $h }}"
                                        {{ in_array($h, $selectedHari) ? 'checked' : '' }}
                                        aria-label="{{ $h }}">
                                    <div class="scfg-hbox"><span class="shd"
                                            aria-hidden="true"></span>{{ $h }}</div>
                                </label>
                            @endforeach
                        </div>
                        @error('hari_efektif')
                            <div class="scfg-ferr" style="margin-top:8px;"><i class="fas fa-exclamation-circle"
                                    aria-hidden="true"></i>{{ $message }}</div>
                        @enderror

                        <div class="scfg-infobox scfg-infobox-yellow" style="margin-top:14px;">
                            <i class="fas fa-robot" aria-hidden="true"></i>
                            <span>
                                Absen otomatis berjalan pukul <strong>08:00</strong> setiap hari efektif.
                                Saat ini aktif pada:
                                <strong>{{ count($selectedHari) > 0 ? implode(', ', $selectedHari) : '— (tidak ada hari aktif)' }}</strong>.
                            </span>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
             13. LOKASI PRESENSI
        ══════════════════════════════════════════════════════════ --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-map-marker-alt"
                                aria-hidden="true"></i></div>
                        <h3>Lokasi Presensi</h3>
                        <span class="scfg-badge-opt">Opsional</span>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('lokasi_presensi')"
                            aria-label="Panduan Lokasi Presensi"><i class="fas fa-lightbulb"></i></button>
                    </div>
                    <div class="scfg-cbody">
                        <div class="scfg-mwrap">
                            <div id="scfgMap" role="application" aria-label="Peta lokasi sekolah"></div>
                            <div class="scfg-mstatus">
                                <span><i class="fas fa-mouse-pointer" aria-hidden="true"></i> Klik peta atau cari nama
                                    sekolah</span>
                                <span id="scfgCoord" aria-live="polite">Lat: -, Lng: -</span>
                            </div>
                        </div>

                        <div class="scfg-g2" style="margin-bottom:13px;">
                            <div class="scfg-fg">
                                <label class="scfg-lbl">Latitude</label>
                                <input type="text" id="scfgLat" name="latitude"
                                    class="scfg-inp @error('latitude') iserr @enderror"
                                    value="{{ old('latitude', $sekolah->latitude ?? '') }}" placeholder="-7.6291"
                                    readonly>
                                @error('latitude')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="scfg-fg">
                                <label class="scfg-lbl">Longitude</label>
                                <input type="text" id="scfgLng" name="longitude"
                                    class="scfg-inp @error('longitude') iserr @enderror"
                                    value="{{ old('longitude', $sekolah->longitude ?? '') }}" placeholder="111.5230"
                                    readonly>
                                @error('longitude')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="scfg-fg">
                            <label class="scfg-lbl" for="scfgRadius">Radius Absensi (meter)</label>
                            <input type="number" id="scfgRadius" name="radius_meter" min="10" max="50000"
                                step="1" class="scfg-inp @error('radius_meter') iserr @enderror"
                                value="{{ old('radius_meter', $sekolah->radius_meter ?? config('sekolah.radius_m', 100)) }}"
                                placeholder="100">
                            <span class="scfg-hint">Rekomendasi 50–200 meter. Terlalu kecil (&lt; 30 m) bisa mengganggu
                                akurasi GPS.</span>
                            @error('radius_meter')
                                <div class="scfg-ferr"><i class="fas fa-exclamation-circle"
                                        aria-hidden="true"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="button" class="scfg-clrbtn" onclick="scfgClear()">
                            <i class="fas fa-trash-alt" aria-hidden="true"></i> Hapus Pin
                        </button>
                    </div>
                </div>

            </form>{{-- /#scfgForm --}}
        </div>{{-- /.scfg-container --}}

        {{-- ── Action Bar ──────────────────────────────────────────────── --}}
        <div class="scfg-bar">
            <button type="submit" form="scfgForm" class="scfg-sbtn" id="scfgSubmitBtn">
                <i class="fas fa-save" aria-hidden="true"></i> Simpan Konfigurasi
            </button>
        </div>

        {{-- ── Help Modal ───────────────────────────────────────────────── --}}
        <div class="scfg-help-overlay" id="scfgHelpOverlay" role="dialog" aria-modal="true"
            aria-labelledby="scfgHelpTitle">
            <div class="scfg-help-modal">
                <div class="scfg-help-mhead">
                    <div class="scfg-help-mhead-ico"><i class="fas fa-lightbulb" aria-hidden="true"></i></div>
                    <h4 id="scfgHelpTitle">Panduan Pengisian</h4>
                    <button type="button" class="scfg-help-close" onclick="scfgHelpClose()" aria-label="Tutup panduan">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="scfg-help-mbody" id="scfgHelpBody"></div>
                <div class="scfg-help-mfoot">
                    <button type="button" class="scfg-help-okbtn" onclick="scfgHelpClose()">Mengerti</button>
                </div>
            </div>
        </div>

    </div>{{-- /.scfg-pg --}}

    {{-- ══════════════════════════════════════════════════════════
         MODAL: Rule Pelanggaran
    ══════════════════════════════════════════════════════════ --}}
    <div id="pelanggaranRuleModal" class="rule-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="pelModalTitle">
        <div class="rule-modal">
            <div class="rule-modal-title">
                <span class="ico" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-gavel"></i></span>
                <span id="pelModalTitle">Tambah Rule Pelanggaran</span>
            </div>
            <div class="rule-modal-body">
                <input type="hidden" id="pelRuleId">

                <div class="scfg-fg">
                    <label class="scfg-lbl" for="pelNamaRule">Nama Rule <span class="req">*</span></label>
                    <input type="text" id="pelNamaRule" class="scfg-inp" maxlength="100"
                        placeholder="Contoh: Alfa Harian, Alfa ≥3x/Bulan...">
                </div>

                <div class="scfg-g2">
                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="pelTriggerType">Jenis Trigger <span class="req">*</span></label>
                        <select id="pelTriggerType" class="scfg-inp" onchange="onPelTriggerChange()">
                            <option value="alfa_harian">Alfa Harian — setiap hari ada alfa</option>
                            <option value="alfa_bulanan">Alfa Bulanan — melebihi N alfa/bulan</option>
                            <option value="terlambat_berulang">Terlambat Berulang — ≥ N kali/periode</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="pelPeriodeBulan">Periode (bulan)</label>
                        <input type="number" id="pelPeriodeBulan" class="scfg-inp" min="0" max="12" placeholder="0 = harian">
                        <span class="scfg-hint">0 = harian, 1 = bulanan, dll</span>
                    </div>
                </div>

                <div id="pelThresholdWrap" class="scfg-fg">
                    <label class="scfg-lbl" for="pelThresholdHari">Batas Jumlah (threshold) <span class="req">*</span></label>
                    <input type="number" id="pelThresholdHari" class="scfg-inp" min="1" max="366"
                        placeholder="Contoh: 3 (alfa ≥3x)">
                    <span class="scfg-hint">Jumlah kejadian yang memicu rule ini</span>
                </div>

                <div class="scfg-fg">
                    <label class="scfg-lbl">Pasal Pelanggaran <span class="req">*</span></label>
                    <input type="hidden" id="pelPasalId">
                    <input type="text" id="pelPasalSearch" class="scfg-inp"
                        placeholder="Ketik kode atau nama pasal…" autocomplete="off">
                    <div id="pelPasalBadge" class="scfg-pasal-badge">
                        <i class="fas fa-tag" style="flex-shrink:0;"></i>
                        <span id="pelPasalBadgeText" class="scfg-pasal-badge-text"></span>
                        <button type="button" id="pelPasalClear" class="scfg-pasal-badge-clear">✕</button>
                    </div>
                </div>

                <div id="pelPoinWrap" class="scfg-fg" style="display:none;">
                    <label class="scfg-lbl" for="pelPoinOverride">Poin Override <span id="pelPoinRangeHint" style="font-weight:400;color:#64748b;"></span></label>
                    <input type="number" id="pelPoinOverride" class="scfg-inp" min="1" max="9999">
                    <span class="scfg-hint">Kosongkan untuk pakai poin default pasal</span>
                </div>

                <div class="scfg-fg">
                    <label class="scfg-lbl" for="pelKeterangan">Keterangan</label>
                    <textarea id="pelKeterangan" class="scfg-inp" rows="2" maxlength="500"
                        placeholder="Catatan tambahan tentang rule ini (opsional)"></textarea>
                </div>

                <label class="check-row-sm">
                    <input type="checkbox" id="pelAktif" checked>
                    <span>Rule aktif (langsung dijalankan saat command dieksekusi)</span>
                </label>
            </div>
            <div class="rule-modal-footer">
                <button type="button" class="rule-modal-btn rule-modal-btn-cancel" onclick="closePelanggaranModal()">Batal</button>
                <button type="button" class="rule-modal-btn rule-modal-btn-save" onclick="savePelanggaranRule()">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         MODAL: Rule Penghargaan
    ══════════════════════════════════════════════════════════ --}}
    <div id="penghargaanRuleModal" class="rule-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="penModalTitle">
        <div class="rule-modal">
            <div class="rule-modal-title">
                <span class="ico" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-star"></i></span>
                <span id="penModalTitle">Tambah Rule Penghargaan</span>
            </div>
            <div class="rule-modal-body">
                <input type="hidden" id="penRuleId">

                <div class="scfg-fg">
                    <label class="scfg-lbl" for="penNamaRule">Nama Rule <span class="req">*</span></label>
                    <input type="text" id="penNamaRule" class="scfg-inp" maxlength="100"
                        placeholder="Contoh: Hadir Full 1 Bulan, Streak 20 Hari...">
                </div>

                <div class="scfg-g2">
                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="penTriggerType">Jenis Trigger <span class="req">*</span></label>
                        <select id="penTriggerType" class="scfg-inp">
                            <option value="full_hadir_bulanan">Full Hadir Bulanan — tidak ada alfa sebulan</option>
                            <option value="full_hadir_mingguan">Full Hadir Mingguan — tidak ada alfa seminggu</option>
                            <option value="streak_hadir">Streak Hadir — berturut-turut ≥ N hari</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="penPeriodeBulan">Periode (bulan) <span class="req">*</span></label>
                        <input type="number" id="penPeriodeBulan" class="scfg-inp" min="1" max="12" value="1">
                        <span class="scfg-hint">1 = bulanan, 3 = per triwulan, dll</span>
                    </div>
                </div>

                <div class="rule-kondisi-wrap">
                    <label class="scfg-lbl" style="margin-bottom:8px;">Status yang Dianggap Hadir</label>
                    <label class="check-row-sm">
                        <input type="checkbox" id="penIzinHadir" checked>
                        <span>Izin dianggap hadir (tidak membatalkan penghargaan)</span>
                    </label>
                    <label class="check-row-sm">
                        <input type="checkbox" id="penSakitHadir" checked>
                        <span>Sakit dianggap hadir (tidak membatalkan penghargaan)</span>
                    </label>
                    <label class="check-row-sm" style="margin-bottom:0;">
                        <input type="checkbox" id="penTerlambatHadir">
                        <span>Terlambat dianggap hadir (tidak membatalkan penghargaan)</span>
                    </label>
                </div>

                <div class="scfg-fg" style="margin-top:14px;">
                    <label class="scfg-lbl">Pasal Penghargaan <span class="req">*</span></label>
                    <input type="hidden" id="penPasalId">
                    <input type="text" id="penPasalSearch" class="scfg-inp"
                        placeholder="Ketik kode atau nama pasal…" autocomplete="off">
                    <div id="penPasalBadge" class="scfg-pasal-badge" style="border-color:#bbf7d0;background:#f0fdf4;color:#16a34a;">
                        <i class="fas fa-tag" style="flex-shrink:0;"></i>
                        <span id="penPasalBadgeText" class="scfg-pasal-badge-text"></span>
                        <button type="button" id="penPasalClear" class="scfg-pasal-badge-clear" style="color:#16a34a;">✕</button>
                    </div>
                </div>

                <div id="penPoinWrap" class="scfg-fg" style="display:none;">
                    <label class="scfg-lbl" for="penPoinOverride">Poin Override <span id="penPoinRangeHint" style="font-weight:400;color:#64748b;"></span></label>
                    <input type="number" id="penPoinOverride" class="scfg-inp" min="1" max="9999">
                    <span class="scfg-hint">Kosongkan untuk pakai poin default pasal</span>
                </div>

                <div class="scfg-fg">
                    <label class="scfg-lbl" for="penKeterangan">Keterangan</label>
                    <textarea id="penKeterangan" class="scfg-inp" rows="2" maxlength="500"
                        placeholder="Catatan tambahan (opsional)"></textarea>
                </div>

                <label class="check-row-sm">
                    <input type="checkbox" id="penAktif" checked>
                    <span>Rule aktif (langsung dijalankan saat command dieksekusi)</span>
                </label>
            </div>
            <div class="rule-modal-footer">
                <button type="button" class="rule-modal-btn rule-modal-btn-cancel" onclick="closePenghargaanModal()">Batal</button>
                <button type="button" class="rule-modal-btn rule-modal-btn-save-green" onclick="savePenghargaanRule()">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>

    {{-- Dropdown pasal rule — di luar modal agar tidak ter-clip overflow:hidden --}}
    <div id="pelPasalDropdown" style="display:none;position:fixed;background:#fff;border:1px solid #e2e8f0;border-radius:10px;max-height:260px;overflow-y:auto;z-index:11000;box-shadow:0 8px 30px rgba(0,0,0,.15);"></div>
    <div id="penPasalDropdown" style="display:none;position:fixed;background:#fff;border:1px solid #e2e8f0;border-radius:10px;max-height:260px;overflow-y:auto;z-index:11000;box-shadow:0 8px 30px rgba(0,0,0,.15);"></div>

@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Ensure footer-bar remains visible when scrolling on school config page
        document.addEventListener('DOMContentLoaded', function() {
            const footerBar = document.getElementById('footer-bar');
            if (footerBar) {
                // Set CSS variable for footer height
                const footerHeight = footerBar.offsetHeight || 60;
                document.documentElement.style.setProperty('--footer-h', footerHeight + 'px');

                // Force footer to stay visible
                footerBar.style.display = 'flex';
                footerBar.style.opacity = '1';
                footerBar.style.visibility = 'visible';
                footerBar.style.transform = 'none';
                footerBar.style.zIndex = '999';

                // Monitor scroll events and keep footer visible
                window.addEventListener('scroll', function() {
                    footerBar.style.display = 'flex';
                    footerBar.style.opacity = '1';
                    footerBar.style.visibility = 'visible';
                    footerBar.style.transform = 'none';
                });

                // Also monitor window resize
                window.addEventListener('resize', function() {
                    const newFooterHeight = footerBar.offsetHeight || 60;
                    document.documentElement.style.setProperty('--footer-h', newFooterHeight + 'px');
                });
            }
        });
    </script>
    <script>
        (function() {
            var map, mkr, cir;

            function setVal(id, v) {
                var el = document.getElementById(id);
                el.removeAttribute('readonly');
                el.value = v;
                el.setAttribute('readonly', true);
            }

            function updateCoord() {
                var lat = document.getElementById('scfgLat').value;
                var lng = document.getElementById('scfgLng').value;
                document.getElementById('scfgCoord').textContent = lat ? 'Lat: ' + lat + ', Lng: ' + lng :
                    'Lat: -, Lng: -';
            }

            function getRadius() {
                var raw = document.getElementById('scfgRadius') ? document.getElementById('scfgRadius').value : '';
                var radius = parseInt(raw, 10);
                return Number.isFinite(radius) && radius >= 10 ? radius : 100;
            }

            function setMkr(lat, lng) {
                if (mkr) map.removeLayer(mkr);
                if (cir) map.removeLayer(cir);
                mkr = L.marker([lat, lng]).addTo(map);
                cir = L.circle([lat, lng], {
                    radius: getRadius(),
                    color: '#0ea5e9',
                    fillColor: '#0ea5e9',
                    fillOpacity: .15,
                    weight: 2
                }).addTo(map);
                setVal('scfgLat', lat.toFixed(6));
                setVal('scfgLng', lng.toFixed(6));
                updateCoord();
                map.fitBounds(cir.getBounds(), {
                    padding: [20, 20]
                });
            }
            window.scfgClear = function() {
                if (mkr) {
                    map.removeLayer(mkr);
                    mkr = null;
                }
                if (cir) {
                    map.removeLayer(cir);
                    cir = null;
                }
                setVal('scfgLat', '');
                setVal('scfgLng', '');
                updateCoord();
            };

            document.addEventListener('DOMContentLoaded', function() {
                var sLat = document.getElementById('scfgLat').value;
                var sLng = document.getElementById('scfgLng').value;
                var lat = sLat ? parseFloat(sLat) : -7.6291;
                var lng = sLng ? parseFloat(sLng) : 111.5230;

                map = L.map('scfgMap').setView([lat, lng], sLat ? 15 : 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(map);
                L.Control.geocoder({
                    defaultMarkGeocode: false,
                    placeholder: 'Cari lokasi...',
                    errorMessage: 'Tidak ditemukan',
                    suggestTimeout: 250,
                    queryMinLength: 3
                }).on('markgeocode', function(e) {
                    map.setView(e.geocode.center, 16);
                    setMkr(e.geocode.center.lat, e.geocode.center.lng);
                }).addTo(map);
                map.on('click', function(e) {
                    setMkr(e.latlng.lat, e.latlng.lng);
                });
                if (sLat && sLng) setMkr(lat, lng);
                var radiusInput = document.getElementById('scfgRadius');
                if (radiusInput) {
                    radiusInput.addEventListener('input', function() {
                        if (cir) {
                            cir.setRadius(getRadius());
                            map.fitBounds(cir.getBounds(), {
                                padding: [20, 20]
                            });
                        }
                    });
                }
                updateCoord();
            });
        })();

        // Form validation and submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('scfgForm');
            const submitBtn = document.getElementById('scfgSubmitBtn');
            const originalBtnText = submitBtn.innerHTML;

            // Form validation
            form.addEventListener('submit', function(e) {
                // Remove existing error messages
                document.querySelectorAll('.scfg-ferr').forEach(el => el.remove());
                document.querySelectorAll('.iserr').forEach(el => el.classList.remove('iserr'));

                // Basic validation
                let hasErrors = false;

                // Validate required fields
                const requiredFields = ['sekolah', 'system_name'];
                requiredFields.forEach(fieldName => {
                    const field = document.getElementById(fieldName);
                    if (field && !field.value.trim()) {
                        showFieldError(field, 'Field ini wajib diisi');
                        hasErrors = true;
                    }
                });

                // Validate email format
                const emailField = document.getElementById('email');
                if (emailField && emailField.value.trim()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(emailField.value.trim())) {
                        showFieldError(emailField, 'Format email tidak valid');
                        hasErrors = true;
                    }
                }

                // Validate URL format
                const urlFields = ['site_url', 'site_logo'];
                urlFields.forEach(fieldName => {
                    const field = document.getElementById(fieldName);
                    if (field && field.value.trim()) {
                        try {
                            new URL(field.value.trim());
                        } catch {
                            showFieldError(field, 'Format URL tidak valid');
                            hasErrors = true;
                        }
                    }
                });

                const radiusField = document.getElementById('scfgRadius');
                if (radiusField && radiusField.value.trim()) {
                    const radius = parseInt(radiusField.value, 10);
                    if (!Number.isFinite(radius) || radius < 10 || radius > 50000) {
                        showFieldError(radiusField, 'Radius harus antara 10 sampai 50000 meter');
                        hasErrors = true;
                    }
                }

                if (hasErrors) {
                    e.preventDefault();
                    // Scroll to first error
                    const firstError = document.querySelector('.iserr');
                    if (firstError) {
                        firstError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstError.focus();
                    }
                    return false;
                }

                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                submitBtn.disabled = true;
                submitBtn.classList.add('scfg-loading');
            });

            function showFieldError(field, message) {
                field.classList.add('iserr');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'scfg-ferr';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i>' + message;
                field.parentNode.appendChild(errorDiv);
            }

            // Reset form on page load if there are errors
            if (document.querySelector('.iserr')) {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                submitBtn.classList.remove('scfg-loading');
            }
        });

        // SweetAlert2 for messages
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#16a34a',
                confirmButtonText: 'OK',
                timer: 4000,
                timerProgressBar: true,
            });
        @endif

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: '<ul style="text-align:left;padding-left:16px;">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>',
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Tutup',
            });
        @endif

        // ═══════════════════════════════════════════════════════
        // AUTO ALFA — Toggle & Pasal Dropdown
        // ═══════════════════════════════════════════════════════
        (function() {
            var PASAL_DATA = [];

            function toggleAutoAlfaSection() {
                var cb = document.getElementById('auto_alfa_enabled');
                var detail = document.getElementById('autoAlfaDetail');
                if (detail) detail.style.display = cb && cb.checked ? 'block' : 'none';
            }

            function toggleAutoPointAlfa() {
                var cb = document.getElementById('auto_point_alfa_enabled');
                var group = document.getElementById('pasalAlfaGroup');
                if (group) group.style.display = cb && cb.checked ? 'block' : 'none';

                // Bila dimatikan, reset pilihan pasal
                if (cb && !cb.checked) {
                    clearPasalAlfa();
                }
            }

            window.toggleAutoAlfaSection = toggleAutoAlfaSection;
            window.toggleAutoPointAlfa = toggleAutoPointAlfa;

            window.toggleLiburSection = function() {
                // Tidak perlu show/hide — tanggal selalu tampil.
                // Hanya highlight track toggle saat aktif.
                var cb = document.getElementById('libur_mode');
                var track = cb ? cb.closest('label').querySelector('.scfg-toggle-track') : null;
                if (track) {
                    track.style.background = (cb && cb.checked) ? '#f97316' : '';
                }
            };

            window.toggleLaporanGuruNomor = function(cb) {
                var wrap = document.getElementById('laporanGuruNomorWrap');
                if (wrap) wrap.style.display = cb.checked ? 'block' : 'none';
            };

            // ── Pasal Autocomplete ──────────────────────────────────────────────
            var searchInput, hiddenInput, badge, badgeText, clearBtn, dropdown;
            var selectedId = '';

            function initPasalAlfa() {
                searchInput = document.getElementById('pasal_alfa_search');
                hiddenInput = document.getElementById('pasal_alfa_hidden');
                badge = document.getElementById('pasal_alfa_badge');
                badgeText = document.getElementById('pasal_alfa_badge_text');
                clearBtn = document.getElementById('pasal_alfa_clear');
                dropdown = document.getElementById('pasal_alfa_dropdown');

                if (!searchInput) return;

                // Load data pasal dari JSON inline
                var dataEl = document.getElementById('pasal-alfa-data');
                if (dataEl) {
                    try {
                        PASAL_DATA = JSON.parse(dataEl.textContent || '[]');
                    } catch (e) {
                        PASAL_DATA = [];
                    }
                }

                // Restore nilai yang sudah tersimpan (old() atau dari DB)
                var savedId = hiddenInput ? hiddenInput.value : '';
                if (savedId) {
                    var found = PASAL_DATA.find(function(p) {
                        return p.id == savedId;
                    });
                    if (found) {
                        selectedId = found.id;
                        searchInput.value = found.label;
                        showBadge(found);
                    }
                }

                // Input event: tampilkan dropdown saat mengetik
                searchInput.addEventListener('input', function() {
                    var q = this.value.trim().toLowerCase();
                    // Tampilkan semua data jika field kosong, atau filter sesuai query
                    var filtered = q ?
                        PASAL_DATA.filter(function(p) {
                            return p.label.toLowerCase().includes(q) || p.isi.toLowerCase().includes(q);
                        }) :
                        PASAL_DATA;
                    renderDropdown(filtered);
                });

                // Focus: selalu tampilkan dropdown (semua data jika kosong, filter jika ada teks)
                searchInput.addEventListener('focus', function() {
                    var q = this.value.trim().toLowerCase();
                    var filtered = q ?
                        PASAL_DATA.filter(function(p) {
                            return p.label.toLowerCase().includes(q) || p.isi.toLowerCase().includes(q);
                        }) :
                        PASAL_DATA;
                    renderDropdown(filtered);
                });

                // Clear button
                if (clearBtn) {
                    clearBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        clearPasalAlfa();
                    });
                }

                // Tutup dropdown saat klik di luar
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                        closeDropdown();
                    }
                });
            }

            function renderDropdown(items) {
                if (!dropdown) return;

                // Batasi tampilan maks 50 item agar dropdown tidak terlalu panjang
                var displayItems = items.slice(0, 50);

                dropdown.innerHTML = '';
                if (!displayItems.length) {
                    dropdown.innerHTML =
                        '<div style="padding:12px 14px;color:#94a3b8;font-size:.82rem;">Pasal tidak ditemukan</div>';
                } else {
                    displayItems.forEach(function(p) {
                        var item = document.createElement('div');
                        item.className = 'pasal-dropdown-item';
                        item.innerHTML =
                            '<span class="pasal-item-label">' + escHtml(p.label) + '</span>' +
                            '<span class="pasal-item-poin">Poin: ' + p.poin + '</span>';
                        item.addEventListener('mousedown', function(e) {
                            e.preventDefault();
                            selectPasal(p);
                        });
                        dropdown.appendChild(item);
                    });
                    // Tampilkan hint jika ada item yang dipotong
                    if (items.length > displayItems.length) {
                        var hint = document.createElement('div');
                        hint.style.cssText =
                            'padding:8px 14px;font-size:.7rem;color:#94a3b8;border-top:1px solid #f1f5f9;text-align:center;';
                        hint.textContent = 'Ketik untuk memfilter — ' + items.length + ' pasal tersedia';
                        dropdown.appendChild(hint);
                    }
                }

                // Posisikan dropdown di bawah input (position:fixed → koordinat viewport, tanpa scrollY)
                var rect = searchInput.getBoundingClientRect();
                dropdown.style.top = (rect.bottom + 4) + 'px';
                dropdown.style.left = rect.left + 'px';
                dropdown.style.width = rect.width + 'px';
                dropdown.style.display = 'block';
            }

            function selectPasal(p) {
                selectedId = p.id;
                if (hiddenInput) hiddenInput.value = p.id;
                if (searchInput) searchInput.value = p.label;
                showBadge(p);
                closeDropdown();
            }

            function showBadge(p) {
                if (badge && badgeText) {
                    badgeText.textContent = p.label + ' — Poin: ' + p.poin;
                    badge.style.display = 'flex';
                }
            }

            function clearPasalAlfa() {
                selectedId = '';
                if (hiddenInput) hiddenInput.value = '';
                if (searchInput) searchInput.value = '';
                if (badge) badge.style.display = 'none';
                closeDropdown();
            }

            function closeDropdown() {
                if (dropdown) dropdown.style.display = 'none';
            }

            function escHtml(str) {
                return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,
                    '&quot;');
            }

            document.addEventListener('DOMContentLoaded', function() {
                initPasalAlfa();
            });
        })();

        document.addEventListener('DOMContentLoaded', () => {
            const header = document.querySelector('.header-auto-show');
            if (header) {
                header.classList.add('header-active');
            }
        });
    </script>

    <script>
        // ═══════════════════════════════════════════════════════
        // AUTO POIN TERLAMBAT — Toggle & Pasal Dropdown
        // ═══════════════════════════════════════════════════════
        (function() {
            var PASAL_DATA = [];

            window.toggleAutoPoinTerlambat = function() {
                var cb    = document.getElementById('auto_poin_terlambat_enabled');
                var group = document.getElementById('pasalTerlambatGroup');
                var track = cb ? cb.closest('label').querySelector('.scfg-toggle-track') : null;
                if (group) group.style.display = cb && cb.checked ? 'block' : 'none';
                if (track) track.style.background = (cb && cb.checked) ? '#f97316' : '';
                if (cb && !cb.checked) clearPasalTerlambat();
            };

            var searchEl, hiddenEl, badgeEl, badgeText, clearBtn, dropEl;

            function initPasalTerlambat() {
                searchEl  = document.getElementById('pasal_terlambat_search');
                hiddenEl  = document.getElementById('pasal_terlambat_hidden');
                badgeEl   = document.getElementById('pasal_terlambat_badge');
                badgeText = document.getElementById('pasal_terlambat_badge_text');
                clearBtn  = document.getElementById('pasal_terlambat_clear');
                dropEl    = document.getElementById('pasal_terlambat_dropdown');
                if (!searchEl) return;

                var dataEl = document.getElementById('pasal-terlambat-data');
                if (dataEl) { try { PASAL_DATA = JSON.parse(dataEl.textContent || '[]'); } catch(e) { PASAL_DATA = []; } }

                var savedId = hiddenEl ? hiddenEl.value : '';
                if (savedId) {
                    var found = PASAL_DATA.find(function(p) { return p.id == savedId; });
                    if (found) { searchEl.value = found.label; showBadgeTerlambat(found); }
                }

                searchEl.addEventListener('input', function() {
                    var q = this.value.trim().toLowerCase();
                    renderDropdownTerlambat(q ? PASAL_DATA.filter(function(p) { return p.label.toLowerCase().includes(q) || p.isi.toLowerCase().includes(q); }) : PASAL_DATA);
                });
                searchEl.addEventListener('focus', function() {
                    var q = this.value.trim().toLowerCase();
                    renderDropdownTerlambat(q ? PASAL_DATA.filter(function(p) { return p.label.toLowerCase().includes(q) || p.isi.toLowerCase().includes(q); }) : PASAL_DATA);
                });
                if (clearBtn) clearBtn.addEventListener('click', function(e) { e.preventDefault(); clearPasalTerlambat(); });
                document.addEventListener('click', function(e) {
                    if (searchEl && !searchEl.contains(e.target) && dropEl && !dropEl.contains(e.target)) {
                        if (dropEl) dropEl.style.display = 'none';
                    }
                });
            }

            function renderDropdownTerlambat(items) {
                if (!dropEl) return;
                var display = items.slice(0, 50);
                dropEl.innerHTML = '';
                if (!display.length) {
                    dropEl.innerHTML = '<div style="padding:12px 14px;color:#94a3b8;font-size:.82rem;">Pasal tidak ditemukan</div>';
                } else {
                    display.forEach(function(p) {
                        var item = document.createElement('div');
                        item.className = 'pasal-dropdown-item';
                        item.innerHTML = '<span class="pasal-item-label">' + escH(p.label) + '</span><span class="pasal-item-poin">Poin: ' + p.poin + '</span>';
                        item.addEventListener('mousedown', function(e) { e.preventDefault(); selectPasalTerlambat(p); });
                        dropEl.appendChild(item);
                    });
                    if (items.length > display.length) {
                        var hint = document.createElement('div');
                        hint.style.cssText = 'padding:8px 14px;font-size:.7rem;color:#94a3b8;border-top:1px solid #f1f5f9;text-align:center;';
                        hint.textContent = 'Ketik untuk memfilter — ' + items.length + ' pasal tersedia';
                        dropEl.appendChild(hint);
                    }
                }
                var rect = searchEl.getBoundingClientRect();
                dropEl.style.top = (rect.bottom + 4) + 'px';
                dropEl.style.left = rect.left + 'px';
                dropEl.style.width = rect.width + 'px';
                dropEl.style.display = 'block';
            }

            function selectPasalTerlambat(p) {
                if (hiddenEl) hiddenEl.value = p.id;
                if (searchEl) searchEl.value = p.label;
                showBadgeTerlambat(p);
                if (dropEl) dropEl.style.display = 'none';
            }

            function showBadgeTerlambat(p) {
                if (badgeEl && badgeText) {
                    badgeText.textContent = p.label + ' — Poin: ' + p.poin;
                    badgeEl.style.display = 'flex';
                }
            }

            function clearPasalTerlambat() {
                if (hiddenEl) hiddenEl.value = '';
                if (searchEl) searchEl.value = '';
                if (badgeEl) badgeEl.style.display = 'none';
                if (dropEl) dropEl.style.display = 'none';
            }

            function escH(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

            document.addEventListener('DOMContentLoaded', function() { initPasalTerlambat(); });
        })();

        // ═══════════════════════════════════════════════════════
        // AUTO POIN HADIR — Toggle & Pasal Dropdown
        // ═══════════════════════════════════════════════════════
        (function() {
            var PASAL_DATA = [];

            window.toggleAutoPoinHadir = function() {
                var cb    = document.getElementById('auto_poin_hadir_enabled');
                var group = document.getElementById('pasalHadirGroup');
                var track = cb ? cb.closest('label').querySelector('.scfg-toggle-track') : null;
                if (group) group.style.display = cb && cb.checked ? 'block' : 'none';
                if (track) track.style.background = (cb && cb.checked) ? '#16a34a' : '';
                if (cb && !cb.checked) clearPasalHadir();
            };

            var searchEl, hiddenEl, badgeEl, badgeText, clearBtn, dropEl;

            function initPasalHadir() {
                searchEl  = document.getElementById('pasal_hadir_search');
                hiddenEl  = document.getElementById('pasal_hadir_hidden');
                badgeEl   = document.getElementById('pasal_hadir_badge');
                badgeText = document.getElementById('pasal_hadir_badge_text');
                clearBtn  = document.getElementById('pasal_hadir_clear');
                dropEl    = document.getElementById('pasal_hadir_dropdown');
                if (!searchEl) return;

                var dataEl = document.getElementById('pasal-hadir-data');
                if (dataEl) { try { PASAL_DATA = JSON.parse(dataEl.textContent || '[]'); } catch(e) { PASAL_DATA = []; } }

                var savedId = hiddenEl ? hiddenEl.value : '';
                if (savedId) {
                    var found = PASAL_DATA.find(function(p) { return p.id == savedId; });
                    if (found) { searchEl.value = found.label; showBadgeHadir(found); }
                }

                searchEl.addEventListener('input', function() {
                    var q = this.value.trim().toLowerCase();
                    renderDropdownHadir(q ? PASAL_DATA.filter(function(p) { return p.label.toLowerCase().includes(q) || p.isi.toLowerCase().includes(q); }) : PASAL_DATA);
                });
                searchEl.addEventListener('focus', function() {
                    var q = this.value.trim().toLowerCase();
                    renderDropdownHadir(q ? PASAL_DATA.filter(function(p) { return p.label.toLowerCase().includes(q) || p.isi.toLowerCase().includes(q); }) : PASAL_DATA);
                });
                if (clearBtn) clearBtn.addEventListener('click', function(e) { e.preventDefault(); clearPasalHadir(); });
                document.addEventListener('click', function(e) {
                    if (searchEl && !searchEl.contains(e.target) && dropEl && !dropEl.contains(e.target)) {
                        if (dropEl) dropEl.style.display = 'none';
                    }
                });
            }

            function renderDropdownHadir(items) {
                if (!dropEl) return;
                var display = items.slice(0, 50);
                dropEl.innerHTML = '';
                if (!display.length) {
                    dropEl.innerHTML = '<div style="padding:12px 14px;color:#94a3b8;font-size:.82rem;">Pasal tidak ditemukan</div>';
                } else {
                    display.forEach(function(p) {
                        var item = document.createElement('div');
                        item.className = 'pasal-dropdown-item';
                        item.innerHTML = '<span class="pasal-item-label">' + escH(p.label) + '</span><span class="pasal-item-poin">Poin: ' + p.poin + '</span>';
                        item.addEventListener('mousedown', function(e) { e.preventDefault(); selectPasalHadir(p); });
                        dropEl.appendChild(item);
                    });
                    if (items.length > display.length) {
                        var hint = document.createElement('div');
                        hint.style.cssText = 'padding:8px 14px;font-size:.7rem;color:#94a3b8;border-top:1px solid #f1f5f9;text-align:center;';
                        hint.textContent = 'Ketik untuk memfilter — ' + items.length + ' pasal tersedia';
                        dropEl.appendChild(hint);
                    }
                }
                var rect = searchEl.getBoundingClientRect();
                dropEl.style.top = (rect.bottom + 4) + 'px';
                dropEl.style.left = rect.left + 'px';
                dropEl.style.width = rect.width + 'px';
                dropEl.style.display = 'block';
            }

            function selectPasalHadir(p) {
                if (hiddenEl) hiddenEl.value = p.id;
                if (searchEl) searchEl.value = p.label;
                showBadgeHadir(p);
                if (dropEl) dropEl.style.display = 'none';
            }

            function showBadgeHadir(p) {
                if (badgeEl && badgeText) {
                    badgeText.textContent = p.label + ' — Poin: ' + p.poin;
                    badgeEl.style.display = 'flex';
                }
            }

            function clearPasalHadir() {
                if (hiddenEl) hiddenEl.value = '';
                if (searchEl) searchEl.value = '';
                if (badgeEl) badgeEl.style.display = 'none';
                if (dropEl) dropEl.style.display = 'none';
            }

            function escH(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

            document.addEventListener('DOMContentLoaded', function() { initPasalHadir(); });
        })();
    </script>

    <script>
        // ===== SCFG HELP MODAL =====
        (function() {
            var HELP_DATA = {
                identitas_sistem: {
                    title: 'Identitas Sistem',
                    content: `
                <h5>Apa ini?</h5>
                <p>Nama sistem yang ditampilkan di header aplikasi, halaman login, dan dokumen cetak. Sesuaikan dengan nama resmi sistem informasi sekolah Anda.</p>
                <h5>Tips Pengisian</h5>
                <ul>
                    <li>Gunakan nama singkat yang mudah dikenali.</li>
                    <li>Maksimal 100 karakter.</li>
                    <li>Hindari karakter khusus agar tampil rapi di semua perangkat.</li>
                </ul>
                <div class="scfg-help-example">
                    <span class="ex-label">✏️ Contoh</span>
                    <table>
                        <tr><td>Nama Sistem</td><td>SIS SMKN 5 Madiun</td></tr>
                        <tr><td>Nama Sistem</td><td>SIAP — SMAN 1 Surabaya</td></tr>
                    </table>
                </div>`
                },
                identitas_sekolah: {
                    title: 'Identitas Sekolah',
                    content: `
                <h5>Apa ini?</h5>
                <p>Data resmi sekolah yang digunakan pada kop surat, laporan, dan dokumen cetak lainnya.</p>
                <h5>Panduan Tiap Field</h5>
                <ul>
                    <li><strong>Nama Sekolah</strong> — nama lengkap resmi sesuai SK pendirian.</li>
                    <li><strong>Alamat</strong> — alamat lengkap termasuk jalan, kelurahan, dan kota.</li>
                    <li><strong>Telepon</strong> — nomor telepon kantor sekolah.</li>
                    <li><strong>Email</strong> — email resmi sekolah.</li>
                    <li><strong>Kabupaten</strong> — kabupaten/kota lokasi sekolah.</li>
                    <li><strong>Alias</strong> — singkatan atau kode pendek sekolah.</li>
                </ul>
                <div class="scfg-help-example">
                    <span class="ex-label">✏️ Contoh</span>
                    <table>
                        <tr><td>Nama Sekolah</td><td>SMK Negeri 5 Madiun</td></tr>
                        <tr><td>Alamat</td><td>Jl. Tentara Pelajar No.19, Madiun</td></tr>
                        <tr><td>Telepon</td><td>0351-464466</td></tr>
                        <tr><td>Email</td><td>info@smkn5madiun.sch.id</td></tr>
                        <tr><td>Kabupaten</td><td>Kota Madiun</td></tr>
                        <tr><td>Alias</td><td>SMKN5MDN</td></tr>
                    </table>
                </div>`
                },
                kepala_sekolah: {
                    title: 'Kepala Sekolah',
                    content: `
                <h5>Apa ini?</h5>
                <p>Data kepala sekolah yang aktif menjabat. Digunakan pada tanda tangan dokumen dan laporan resmi.</p>
                <h5>Panduan Tiap Field</h5>
                <ul>
                    <li><strong>Nama</strong> — nama lengkap beserta gelar akademik.</li>
                    <li><strong>NIP</strong> — Nomor Induk Pegawai (18 digit untuk PNS). Boleh dikosongkan untuk non-PNS.</li>
                </ul>
                <div class="scfg-help-example">
                    <span class="ex-label">✏️ Contoh</span>
                    <table>
                        <tr><td>Nama</td><td>Dr. H. Ahmad Yani, M.Pd.</td></tr>
                        <tr><td>NIP</td><td>196801011990031005</td></tr>
                    </table>
                </div>`
                },
                waka: {
                    title: 'Wakil Kepala Sekolah',
                    content: `
                <h5>Apa ini?</h5>
                <p>Data wakil kepala sekolah yang aktif. Digunakan pada dokumen tertentu yang membutuhkan tanda tangan Waka.</p>
                <h5>Panduan Tiap Field</h5>
                <ul>
                    <li><strong>Nama</strong> — nama lengkap beserta gelar akademik.</li>
                    <li><strong>NIP</strong> — Nomor Induk Pegawai. Boleh dikosongkan.</li>
                </ul>
                <div class="scfg-help-example">
                    <span class="ex-label">✏️ Contoh</span>
                    <table>
                        <tr><td>Nama</td><td>Drs. Siti Aminah, M.Pd.</td></tr>
                        <tr><td>NIP</td><td>197205152000122001</td></tr>
                    </table>
                </div>`
                },
                ketua: {
                    title: 'Ketua',
                    content: `
                <h5>Apa ini?</h5>
                <p>Data ketua program/jurusan atau pejabat struktural lain yang namanya perlu muncul di dokumen tertentu.</p>
                <h5>Panduan Tiap Field</h5>
                <ul>
                    <li><strong>Nama</strong> — nama lengkap beserta gelar.</li>
                    <li><strong>NIP</strong> — Nomor Induk Pegawai. Boleh dikosongkan.</li>
                </ul>
                <div class="scfg-help-example">
                    <span class="ex-label">✏️ Contoh</span>
                    <table>
                        <tr><td>Nama</td><td>Ir. Budi Santoso, M.T.</td></tr>
                        <tr><td>NIP</td><td>197801032008011003</td></tr>
                    </table>
                </div>`
                },
                website_media: {
                    title: 'Website & Media',
                    content: `
                <h5>Apa ini?</h5>
                <p>Informasi digital sekolah yang digunakan pada halaman login, footer, dan tautan notifikasi.</p>
                <h5>Panduan Tiap Field</h5>
                <ul>
                    <li><strong>URL Website</strong> — alamat situs resmi sekolah, diawali <code>https://</code>.</li>
                    <li><strong>URL Logo</strong> — link langsung ke file gambar logo sekolah (JPG/PNG).</li>
                    <li><strong>WhatsApp Sekolah</strong> — nomor WA resmi sekolah dalam format internasional <strong>tanpa tanda +</strong>. Diawali kode negara 62.</li>
                </ul>
                <div class="scfg-help-example">
                    <span class="ex-label">✏️ Contoh</span>
                    <table>
                        <tr><td>URL Website</td><td>https://smkn5madiun.sch.id</td></tr>
                        <tr><td>URL Logo</td><td>https://smkn5madiun.sch.id/logo.png</td></tr>
                        <tr><td>WhatsApp</td><td>6281234567890</td></tr>
                    </table>
                </div>
                <p style="margin-top:8px;font-size:.78rem;color:#64748b;">⚠️ Pastikan URL logo dapat diakses publik agar tampil di semua perangkat.</p>`
                },
                jadwal_absensi: {
                    title: 'Jadwal Absensi Siswa',
                    content: `
                <h5>Apa ini?</h5>
                <p>Mengatur jam masuk dan pulang standar, serta jadwal khusus untuk hari tertentu (misal Jumat).</p>
                <h5>Jadwal Normal vs Jadwal Khusus</h5>
                <ul>
                    <li><strong>Jadwal Normal</strong> — berlaku di semua hari efektif <em>kecuali</em> hari yang dipilih sebagai Hari Khusus.</li>
                    <li><strong>Hari Khusus</strong> — centang hari yang memiliki jadwal berbeda dari hari biasa.</li>
                    <li><strong>Jadwal Khusus</strong> — jam masuk/pulang yang berlaku di hari yang dicentang. Kosongkan jika sama dengan jadwal normal.</li>
                </ul>
                <div class="scfg-help-example">
                    <span class="ex-label">✏️ Contoh (Sekolah 5 Hari, Jumat Lebih Awal)</span>
                    <table>
                        <tr><td>Jam Masuk Normal</td><td>07:00</td></tr>
                        <tr><td>Jam Pulang Normal</td><td>15:30</td></tr>
                        <tr><td>Hari Khusus</td><td>Jumat ✓</td></tr>
                        <tr><td>Jam Masuk Khusus</td><td>07:00 (sama, bisa dikosongkan)</td></tr>
                        <tr><td>Jam Pulang Khusus</td><td>11:30</td></tr>
                    </table>
                </div>`
                },
                keterlambatan: {
                    title: 'Konfigurasi Keterlambatan',
                    content: `
                <h5>Apa ini?</h5>
                <p>Menentukan rentang waktu absensi dan batas waktu agar siswa dinyatakan tepat waktu atau terlambat.</p>
                <h5>Panduan Tiap Field</h5>
                <ul>
                    <li><strong>Jam Mulai Absensi</strong> — waktu paling awal siswa boleh scan absen masuk. Absen sebelum jam ini diabaikan sistem.</li>
                    <li><strong>Batas Tepat Waktu</strong> — batas akhir siswa dianggap tepat waktu. Absen setelah jam ini otomatis berstatus <strong>Terlambat</strong> beserta jumlah menitnya. Siswa <em>masih bisa</em> absen setelah jam ini selama belum melewati Batas Akhir Absen (di card Auto Alfa).</li>
                </ul>
                <div class="scfg-help-example">
                    <span class="ex-label">✏️ Contoh</span>
                    <table>
                        <tr><td>Jam Mulai Absensi</td><td>06:00</td></tr>
                        <tr><td>Batas Tepat Waktu</td><td>07:15</td></tr>
                    </table>
                    <p style="margin-top:8px;font-size:.78rem;color:#334155;">
                        Scan jam <strong>06:45</strong> → Hadir (Tepat Waktu)<br>
                        Scan jam <strong>07:15</strong> → Hadir (Tepat Waktu)<br>
                        Scan jam <strong>07:20</strong> → Terlambat 5 menit<br>
                        Scan jam <strong>05:50</strong> → Ditolak (terlalu awal)
                    </p>
                </div>
                <p style="font-size:.78rem;color:#64748b;margin-top:8px;">
                    ℹ️ Batas jam akhir siswa boleh absen diatur di card <strong>Auto Alfa Siswa</strong> (field Batas Akhir Absen Masuk).
                </p>`
                },
                notif_wa: {
                    title: 'Notifikasi WhatsApp',
                    content: `
                <h5>Apa ini?</h5>
                <p>Daftar semua jenis notifikasi WhatsApp otomatis yang bisa dikirim sistem ke orang tua/wali siswa. Aktifkan atau nonaktifkan masing-masing sesuai kebutuhan sekolah.</p>
                <h5>Syarat Agar Notifikasi Berfungsi</h5>
                <ul>
                    <li>Nomor WhatsApp orang tua sudah diisi di data masing-masing siswa.</li>
                    <li>Gateway WhatsApp (Fonnte, WA Gateway, dll.) sudah dikonfigurasi di file <code>.env</code>.</li>
                    <li>Saldo atau koneksi gateway aktif.</li>
                </ul>
                <h5>6 Jenis Notifikasi yang Tersedia</h5>
                <ul>
                    <li><strong>Presensi Masuk</strong> — dikirim saat siswa scan absen masuk (Hadir / Terlambat).</li>
                    <li><strong>Presensi Pulang</strong> — dikirim saat siswa scan absen pulang.</li>
                    <li><strong>Auto Alfa</strong> — dikirim saat siswa otomatis dicatat Alfa oleh sistem. Hanya aktif jika fitur Auto Alfa juga dinyalakan.</li>
                    <li><strong>Absen Event</strong> — dikirim saat siswa scan absen pada kegiatan/event sekolah.</li>
                    <li><strong>Peringatan Poin Tatib</strong> — dikirim ke orang tua saat poin pelanggaran siswa mencapai ambang batas yang ditentukan.</li>
                    <li><strong>Laporan Kehadiran Guru</strong> — dikirim ke nomor admin/BK/pejabat yang dikonfigurasi, saat laporan kehadiran guru dikirim oleh siswa atau GTK. Isi nomor penerima di bawah toggle (bisa lebih dari 1, satu nomor per baris).</li>
                </ul>
                <p style="font-size:.78rem;color:#64748b;margin-top:8px;">
                    ℹ️ Toggle bisa diaktifkan/nonaktifkan kapan saja tanpa mempengaruhi proses absensi itu sendiri.
                </p>`
                },
                auto_alfa: {
                    title: 'Auto Alfa Siswa',
                    content: `
                <h5>Apa ini?</h5>
                <p>Fitur yang secara otomatis mencatat status <strong>Alfa</strong> untuk siswa yang tidak hadir dan tidak ada keterangan izin, setelah jam eksekusi yang ditentukan.</p>

                <h5>Dua Field yang Saling Berkaitan</h5>
                <p>Card ini memiliki dua pengaturan waktu dengan fungsi berbeda:</p>
                <ul>
                    <li>
                        <strong>Batas Akhir Absen Masuk</strong> —
                        Jam terakhir siswa masih diizinkan melakukan absen masuk.
                        Siswa yang absen setelah <em>Batas Tepat Waktu</em> (di card Keterlambatan) tetap bisa absen dengan status <strong>Terlambat</strong>, sampai mencapai jam ini.
                        Setelah jam ini, absen langsung <strong>ditolak</strong>.
                    </li>
                    <li>
                        <strong>Jam Eksekusi Auto Alfa</strong> —
                        Jam saat sistem otomatis mencatat Alfa untuk semua siswa yang belum absen.
                        Sebaiknya diisi <strong>sama dengan atau setelah</strong> Batas Akhir Absen agar siswa tidak didahului alfa sebelum sempat absen.
                    </li>
                </ul>

                <h5>4 Skenario Kombinasi</h5>
                <div class="scfg-help-example">
                    <span class="ex-label">✅ Skenario 1 — Keduanya Diisi (Direkomendasikan)</span>
                    <table>
                        <tr><td>Batas Akhir Absen</td><td><strong>09:00</strong></td></tr>
                        <tr><td>Jam Eksekusi Alfa</td><td><strong>09:30</strong></td></tr>
                    </table>
                    <p style="margin-top:6px;font-size:.78rem;color:#334155;">
                        → Siswa boleh absen (Terlambat) s.d. 09:00. Setelah 09:00 absen ditolak. Jam 09:30 sistem mencatat Alfa.
                        <br><em>Ini pola paling aman dan eksplisit.</em>
                    </p>
                </div>
                <div class="scfg-help-example">
                    <span class="ex-label">⚡ Skenario 2 — Hanya Jam Eksekusi Diisi</span>
                    <table>
                        <tr><td>Batas Akhir Absen</td><td><em>(kosong)</em></td></tr>
                        <tr><td>Jam Eksekusi Alfa</td><td><strong>09:00</strong></td></tr>
                    </table>
                    <p style="margin-top:6px;font-size:.78rem;color:#334155;">
                        → Batas akhir absen <strong>otomatis menggunakan Jam Eksekusi</strong> (09:00).
                        Siswa boleh absen s.d. 09:00, lalu jam 09:00 juga langsung dicatat Alfa.
                        <br><em>Praktis, tapi absen dan eksekusi alfa di jam yang sama.</em>
                    </p>
                </div>
                <div class="scfg-help-example">
                    <span class="ex-label">⚠️ Skenario 3 — Hanya Batas Akhir Absen Diisi</span>
                    <table>
                        <tr><td>Batas Akhir Absen</td><td><strong>09:00</strong></td></tr>
                        <tr><td>Jam Eksekusi Alfa</td><td><em>(kosong)</em></td></tr>
                    </table>
                    <p style="margin-top:6px;font-size:.78rem;color:#334155;">
                        → Siswa tidak bisa absen setelah 09:00. Namun Auto Alfa <strong>tidak berjalan</strong> karena Jam Eksekusi kosong.
                        <br><em>Gunakan hanya jika tidak ingin auto-alfa tapi tetap ingin batasi jam absen.</em>
                    </p>
                </div>
                <div class="scfg-help-example">
                    <span class="ex-label">❌ Skenario 4 — Keduanya Kosong</span>
                    <table>
                        <tr><td>Batas Akhir Absen</td><td><em>(kosong)</em></td></tr>
                        <tr><td>Jam Eksekusi Alfa</td><td><em>(kosong)</em></td></tr>
                    </table>
                    <p style="margin-top:6px;font-size:.78rem;color:#9a3412;">
                        → Batas akhir absen jatuh ke nilai <code>limit_masuk</code> dari pengaturan jam pelajaran (<code>tblsetjam</code>).
                        Jika nilai tersebut sama dengan Batas Tepat Waktu, siswa terlambat akan langsung ditolak. <strong>Hindari skenario ini.</strong>
                    </p>
                </div>

                <h5>Sub-fitur Lainnya</h5>
                <ul>
                    <li><strong>Auto Point Pelanggaran Alfa</strong> — siswa yang di-alfa-kan langsung mendapat poin pelanggaran dari pasal yang dipilih.</li>
                    <li><strong>Notif WA Alfa</strong> — toggle notifikasi WA saat Auto Alfa ada di kartu <em>Notifikasi WhatsApp</em>.</li>
                </ul>
                <p style="font-size:.78rem;color:#dc2626;margin-top:10px;">⚠️ Pastikan Jam Eksekusi <strong>setelah</strong> Batas Akhir Absen agar siswa terlambat sempat absen sebelum dicatat Alfa.</p>`
                },
                hari_efektif: {
                    title: 'Hari Efektif Sekolah',
                    content: `
                <h5>Apa ini?</h5>
                <p>Menentukan hari-hari mana saja yang dianggap sebagai hari masuk sekolah. Sistem absensi dan auto alfa hanya akan berjalan pada hari-hari yang dicentang.</p>
                <h5>Tips Pengisian</h5>
                <ul>
                    <li>Centang semua hari masuk sekolah, biasanya Senin s.d. Jumat.</li>
                    <li>Jika sekolah masuk Sabtu, centang Sabtu juga.</li>
                    <li>Hari yang tidak dicentang (misal Sabtu & Minggu) akan dilewati oleh semua proses otomatis.</li>
                </ul>
                <div class="scfg-help-example">
                    <span class="ex-label">✏️ Contoh (5 Hari Kerja)</span>
                    <table>
                        <tr><td>Hari Efektif</td><td>Senin, Selasa, Rabu, Kamis, Jumat ✓</td></tr>
                        <tr><td>Sabtu & Minggu</td><td>Tidak dicentang (libur)</td></tr>
                    </table>
                </div>`
                },
                lokasi_presensi: {
                    title: 'Lokasi Presensi',
                    content: `
                <h5>Apa ini?</h5>
                <p>Membatasi area tempat siswa boleh melakukan presensi. Jika diaktifkan, siswa yang berada di luar radius tidak bisa absen melalui aplikasi mobile.</p>
                <h5>Cara Mengatur Lokasi</h5>
                <ul>
                    <li><strong>Klik peta</strong> — klik langsung pada peta di titik lokasi sekolah untuk menetapkan koordinat.</li>
                    <li><strong>Cari lokasi</strong> — gunakan kotak pencarian di pojok kanan peta untuk mencari nama sekolah atau alamat.</li>
                    <li><strong>Hapus Pin</strong> — klik tombol Hapus Pin untuk menghapus lokasi (fitur lokasi menjadi tidak aktif).</li>
                </ul>
                <h5>Radius Absensi</h5>
                <ul>
                    <li>Isi dalam satuan <strong>meter</strong>.</li>
                    <li>Rekomendasikan <strong>50–200 meter</strong> untuk area sekolah biasa.</li>
                    <li>Nilai terlalu kecil (&lt; 30 m) bisa menyulitkan siswa karena akurasi GPS bervariasi.</li>
                </ul>
                <div class="scfg-help-example">
                    <span class="ex-label">✏️ Contoh</span>
                    <table>
                        <tr><td>Latitude</td><td>-7.629100</td></tr>
                        <tr><td>Longitude</td><td>111.523000</td></tr>
                        <tr><td>Radius</td><td>100 meter</td></tr>
                    </table>
                </div>
                <p style="font-size:.78rem;color:#64748b;margin-top:8px;">ℹ️ Kosongkan lokasi jika tidak ingin membatasi area presensi siswa.</p>`
                },
            };

            window.scfgHelp = function(key) {
                var data = HELP_DATA[key];
                if (!data) return;
                document.getElementById('scfgHelpTitle').textContent = data.title;
                document.getElementById('scfgHelpBody').innerHTML = data.content;
                var overlay = document.getElementById('scfgHelpOverlay');
                overlay.classList.add('active');
                // Trap focus in modal
                var closeBtn = overlay.querySelector('.scfg-help-close');
                if (closeBtn) setTimeout(function() {
                    closeBtn.focus();
                }, 50);
                // Close on overlay click
                overlay._scfgClose = function(e) {
                    if (e.target === overlay) scfgHelpClose();
                };
                overlay.addEventListener('click', overlay._scfgClose);
                // Close on Escape
                document._scfgEsc = function(e) {
                    if (e.key === 'Escape') scfgHelpClose();
                };
                document.addEventListener('keydown', document._scfgEsc);
            };

            window.scfgHelpClose = function() {
                var overlay = document.getElementById('scfgHelpOverlay');
                overlay.classList.remove('active');
                if (overlay._scfgClose) {
                    overlay.removeEventListener('click', overlay._scfgClose);
                    overlay._scfgClose = null;
                }
                if (document._scfgEsc) {
                    document.removeEventListener('keydown', document._scfgEsc);
                    document._scfgEsc = null;
                }
            };
        })();

        // ═══════════════════════════════════════════════════════
        // AUTO RULES — SHARED UTILITIES
        // ═══════════════════════════════════════════════════════
        (function () {
            function esc(s) {
                return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            }
            function showToast(msg, type) {
                if (window.Swal) {
                    Swal.fire({ icon: type||'success', title: msg, toast:true, position:'top-end',
                        showConfirmButton:false, timer:2500, timerProgressBar:true });
                } else { alert(msg); }
            }
            function csrfToken() {
                var m = document.querySelector('meta[name="csrf-token"]');
                return m ? m.content : '';
            }
            function apiRequest(method, url, data) {
                return fetch(url, {
                    method: method,
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrfToken(), 'Accept':'application/json' },
                    body: data ? JSON.stringify(data) : undefined,
                }).then(function(r) {
                    if (!r.ok) return r.json().then(function(e) { throw e; });
                    return r.json();
                });
            }

            // ── Pasal Autocomplete builder ──────────────────────────────────
            function buildPasalAC(opts) {
                var DATA=opts.data||[], searchEl=document.getElementById(opts.searchId),
                    hiddenEl=document.getElementById(opts.hiddenId), dropEl=document.getElementById(opts.dropdownId),
                    badgeEl=document.getElementById(opts.badgeId), badgeText=document.getElementById(opts.badgeTextId),
                    clearBtn=document.getElementById(opts.clearId);
                if (!searchEl||!dropEl) return null;
                function pos() {
                    var r=searchEl.getBoundingClientRect();
                    // Gunakan position:fixed agar tidak ter-clip oleh overflow modal
                    dropEl.style.position='fixed';
                    dropEl.style.top=r.bottom+'px';
                    dropEl.style.left=r.left+'px';
                    dropEl.style.width=r.width+'px';
                }
                function filter(q) {
                    if (!q) return DATA;
                    var lq=q.toLowerCase();
                    return DATA.filter(function(p){ return p.label.toLowerCase().includes(lq)||p.isi.toLowerCase().includes(lq); });
                }
                function render(items) {
                    dropEl.innerHTML='';
                    if (!items.length) {
                        dropEl.innerHTML='<div style="padding:10px 14px;color:#94a3b8;font-size:.8rem;">Tidak ditemukan</div>';
                    } else {
                        items.slice(0,50).forEach(function(p) {
                            var d=document.createElement('div');
                            d.className='pasal-dropdown-item';
                            d.innerHTML='<span class="pasal-item-label">'+esc(p.label)+'</span>'+
                                '<span class="pasal-item-poin">Poin: '+p.poin+(p.min!==p.max?' ('+p.min+'–'+p.max+')':'')+' </span>';
                            d.addEventListener('mousedown',function(e){ e.preventDefault(); selectItem(p); });
                            dropEl.appendChild(d);
                        });
                        if (items.length>50) {
                            var h=document.createElement('div');
                            h.style.cssText='padding:6px 14px;font-size:.7rem;color:#94a3b8;text-align:center;';
                            h.textContent=items.length+' pasal — ketik untuk filter';
                            dropEl.appendChild(h);
                        }
                    }
                    pos(); dropEl.style.display='block';
                }
                function selectItem(p) {
                    hiddenEl.value=p.id; searchEl.value=p.label;
                    badgeText.textContent=p.label+' — Poin: '+p.poin+(p.min!==p.max?' ('+p.min+'–'+p.max+')':'');
                    badgeEl.style.display='flex'; dropEl.style.display='none';
                    if (opts.onSelect) opts.onSelect(p);
                }
                function clearItem() {
                    hiddenEl.value=''; searchEl.value='';
                    badgeEl.style.display='none'; dropEl.style.display='none';
                    if (opts.onSelect) opts.onSelect(null);
                }
                searchEl.addEventListener('input', function(){ render(filter(this.value)); });
                searchEl.addEventListener('focus', function(){ render(filter(this.value)); });
                searchEl.addEventListener('blur',  function(){ setTimeout(function(){ dropEl.style.display='none'; },200); });
                if (clearBtn) clearBtn.addEventListener('click', clearItem);
                window.addEventListener('scroll', function(){ if(dropEl.style.display!=='none') pos(); },true);
                window.addEventListener('resize', function(){ if(dropEl.style.display!=='none') pos(); });
                return { restore: function(id){ var f=DATA.find(function(p){ return String(p.id)===String(id); }); if(f) selectItem(f); }, clear: clearItem };
            }

            // ════════════════════════════════════════════════════
            // AUTO RULES PELANGGARAN
            // ════════════════════════════════════════════════════
            var PEL_DATA=[]; var pelAC=null;
            try { var _pd=document.getElementById('pasal-pel-rule-data'); if(_pd) PEL_DATA=JSON.parse(_pd.textContent||'[]'); } catch(e){}

            window.onPelTriggerChange=function() {
                var type=document.getElementById('pelTriggerType').value;
                var wrap=document.getElementById('pelThresholdWrap');
                if(wrap) wrap.style.display=(type==='alfa_harian')?'none':'block';
            };

            function onPelPasalSelect(p) {
                var wrap=document.getElementById('pelPoinWrap'), hint=document.getElementById('pelPoinRangeHint'),
                    input=document.getElementById('pelPoinOverride');
                if(!wrap) return;
                if(!p){ wrap.style.display='none'; return; }
                if(p.min!==p.max){ hint.textContent='('+p.min+'–'+p.max+')'; input.min=p.min; input.max=p.max;
                    if(!input.value) input.value=p.poin; wrap.style.display='block'; }
                else { hint.textContent='(fixed: '+p.poin+')'; wrap.style.display='none'; input.value=''; }
            }

            window.openPelanggaranRuleModal=function(data) {
                document.getElementById('pelModalTitle').textContent=data?'Edit Rule Pelanggaran':'Tambah Rule Pelanggaran';
                document.getElementById('pelRuleId').value=data?data.id:'';
                document.getElementById('pelNamaRule').value=data?data.nama_rule:'';
                document.getElementById('pelTriggerType').value=data?data.trigger_type:'alfa_harian';
                document.getElementById('pelPeriodeBulan').value=data?(data.periode_bulan||''):'';
                document.getElementById('pelThresholdHari').value=data?(data.threshold_hari||''):'';
                document.getElementById('pelPoinOverride').value=data?(data.poin_override||''):'';
                document.getElementById('pelKeterangan').value=data?(data.keterangan||''):'';
                document.getElementById('pelAktif').checked=data?data.aktif:true;
                if(pelAC) pelAC.clear();
                if(!pelAC) pelAC=buildPasalAC({ data:PEL_DATA, searchId:'pelPasalSearch', hiddenId:'pelPasalId',
                    dropdownId:'pelPasalDropdown', badgeId:'pelPasalBadge', badgeTextId:'pelPasalBadgeText',
                    clearId:'pelPasalClear', onSelect:onPelPasalSelect });
                if(data&&data.pasal_id) pelAC.restore(data.pasal_id);
                window.onPelTriggerChange();
                document.getElementById('pelanggaranRuleModal').classList.add('open');
            };

            window.closePelanggaranModal=function(){ document.getElementById('pelanggaranRuleModal').classList.remove('open'); };

            window.savePelanggaranRule=function() {
                var id=document.getElementById('pelRuleId').value;
                var nama=document.getElementById('pelNamaRule').value.trim();
                var pasalId=document.getElementById('pelPasalId').value;
                if(!nama){ showToast('Nama rule wajib diisi.','warning'); return; }
                if(!pasalId){ showToast('Pasal wajib dipilih.','warning'); return; }
                var payload={ nama_rule:nama, trigger_type:document.getElementById('pelTriggerType').value,
                    periode_bulan:document.getElementById('pelPeriodeBulan').value||null,
                    threshold_hari:document.getElementById('pelThresholdHari').value||null,
                    pasal_id:pasalId, poin_override:document.getElementById('pelPoinOverride').value||null,
                    keterangan:document.getElementById('pelKeterangan').value.trim()||null,
                    aktif:document.getElementById('pelAktif').checked };
                var url=id?'/admin/auto-rules/pelanggaran/'+id:'/admin/auto-rules/pelanggaran';
                apiRequest(id?'PUT':'POST',url,payload)
                    .then(function(r){ window.closePelanggaranModal(); renderPelanggaranRule(r,id?'update':'add'); showToast(id?'Rule diperbarui.':'Rule ditambahkan.','success'); })
                    .catch(function(e){ var m=e.message||(e.errors?Object.values(e.errors).flat().join('\n'):'Kesalahan.'); showToast(m,'error'); });
            };

            window.editPelanggaranRule=function(id) {
                apiRequest('GET','/admin/auto-rules/pelanggaran').then(function(rules){
                    var r=rules.find(function(x){ return x.id==id; }); if(r) window.openPelanggaranRuleModal(r);
                });
            };

            window.deletePelanggaranRule=function(id) {
                if(!window.Swal){ if(!confirm('Hapus rule ini?')) return; _doPelDel(id); return; }
                Swal.fire({ icon:'warning',title:'Hapus rule ini?',text:'Tidak dapat dibatalkan.',
                    showCancelButton:true,confirmButtonColor:'#dc2626',confirmButtonText:'Hapus',cancelButtonText:'Batal' })
                    .then(function(r){ if(r.isConfirmed) _doPelDel(id); });
            };

            function _doPelDel(id) {
                apiRequest('DELETE','/admin/auto-rules/pelanggaran/'+id)
                    .then(function(){ var el=document.getElementById('pel-rule-'+id); if(el) el.remove(); _checkPelEmpty(); showToast('Rule dihapus.','success'); })
                    .catch(function(){ showToast('Gagal menghapus.','error'); });
            }

            window.togglePelanggaranRule=function(id,lbl) {
                apiRequest('PATCH','/admin/auto-rules/pelanggaran/'+id+'/toggle').then(function(r){
                    var el=document.getElementById('pel-rule-'+id);
                    if(el){ if(r.aktif) el.classList.remove('rule-inactive'); else el.classList.add('rule-inactive'); }
                    var cb=lbl?lbl.querySelector('input[type="checkbox"]'):null; if(cb) cb.checked=r.aktif;
                });
            };

            function renderPelanggaranRule(rule,mode) {
                var list=document.getElementById('pelanggaranRulesList');
                var hint=document.getElementById('pelEmptyHint'); if(hint) hint.remove();
                var html='<div class="rule-item '+(rule.aktif?'':'rule-inactive')+'" data-id="'+rule.id+'" id="pel-rule-'+rule.id+'">'
                    +'<div class="rule-item-header"><div class="rule-item-info">'
                    +'<span class="rule-item-name">'+esc(rule.nama_rule)+'</span>'
                    +'<span class="rule-item-meta">'+esc(rule.trigger_label||rule.trigger_type)
                    +(rule.threshold_hari?' · ≥'+rule.threshold_hari+' hari':'')
                    +(rule.periode_bulan?' · '+rule.periode_bulan+' bln':'')
                    +' · '+esc(rule.pasal_label||rule.pasal_id)+' · <strong>'+rule.poin_efektif+' poin</strong></span></div>'
                    +'<div class="rule-item-actions">'
                    +'<label class="scfg-toggle scfg-toggle-red rule-toggle" onclick="togglePelanggaranRule('+rule.id+',this)">'
                    +'<input type="checkbox" '+(rule.aktif?'checked':'')+' onclick="event.preventDefault()">'
                    +'<span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span></label>'
                    +'<button type="button" class="rule-btn-edit" onclick="editPelanggaranRule('+rule.id+')"><i class="fas fa-pencil-alt"></i></button>'
                    +'<button type="button" class="rule-btn-del" onclick="deletePelanggaranRule('+rule.id+')"><i class="fas fa-trash"></i></button>'
                    +'</div></div>'+(rule.keterangan?'<div class="rule-item-ket">'+esc(rule.keterangan)+'</div>':'')+'</div>';
                if(mode==='add') list.insertAdjacentHTML('beforeend',html);
                else { var ex=document.getElementById('pel-rule-'+rule.id); if(ex) ex.outerHTML=html; }
            }
            function _checkPelEmpty() {
                var list=document.getElementById('pelanggaranRulesList');
                if(list&&!list.querySelector('.rule-item')) list.innerHTML='<div id="pelEmptyHint" class="rule-empty-hint"><i class="fas fa-info-circle"></i> Belum ada rule.</div>';
            }

            // ════════════════════════════════════════════════════
            // AUTO RULES PENGHARGAAN
            // ════════════════════════════════════════════════════
            var PEN_DATA=[]; var penAC=null;
            try { var _nd=document.getElementById('pasal-pen-rule-data'); if(_nd) PEN_DATA=JSON.parse(_nd.textContent||'[]'); } catch(e){}

            function onPenPasalSelect(p) {
                var wrap=document.getElementById('penPoinWrap'), hint=document.getElementById('penPoinRangeHint'),
                    input=document.getElementById('penPoinOverride');
                if(!wrap) return;
                if(!p){ wrap.style.display='none'; return; }
                if(p.min!==p.max){ hint.textContent='('+p.min+'–'+p.max+')'; input.min=p.min; input.max=p.max;
                    if(!input.value) input.value=p.poin; wrap.style.display='block'; }
                else { hint.textContent='(fixed: '+p.poin+')'; wrap.style.display='none'; input.value=''; }
            }

            window.openPenghargaanRuleModal=function(data) {
                document.getElementById('penModalTitle').textContent=data?'Edit Rule Penghargaan':'Tambah Rule Penghargaan';
                document.getElementById('penRuleId').value=data?data.id:'';
                document.getElementById('penNamaRule').value=data?data.nama_rule:'';
                document.getElementById('penTriggerType').value=data?data.trigger_type:'full_hadir_bulanan';
                document.getElementById('penPeriodeBulan').value=data?(data.periode_bulan||1):1;
                document.getElementById('penIzinHadir').checked=data?data.izin_dihitung_hadir:true;
                document.getElementById('penSakitHadir').checked=data?data.sakit_dihitung_hadir:true;
                document.getElementById('penTerlambatHadir').checked=data?data.terlambat_dihitung_hadir:false;
                document.getElementById('penPoinOverride').value=data?(data.poin_override||''):'';
                document.getElementById('penKeterangan').value=data?(data.keterangan||''):'';
                document.getElementById('penAktif').checked=data?data.aktif:true;
                if(penAC) penAC.clear();
                if(!penAC) penAC=buildPasalAC({ data:PEN_DATA, searchId:'penPasalSearch', hiddenId:'penPasalId',
                    dropdownId:'penPasalDropdown', badgeId:'penPasalBadge', badgeTextId:'penPasalBadgeText',
                    clearId:'penPasalClear', onSelect:onPenPasalSelect });
                if(data&&data.pasal_id) penAC.restore(data.pasal_id);
                document.getElementById('penghargaanRuleModal').classList.add('open');
            };

            window.closePenghargaanModal=function(){ document.getElementById('penghargaanRuleModal').classList.remove('open'); };

            window.savePenghargaanRule=function() {
                var id=document.getElementById('penRuleId').value;
                var nama=document.getElementById('penNamaRule').value.trim();
                var pasalId=document.getElementById('penPasalId').value;
                if(!nama){ showToast('Nama rule wajib diisi.','warning'); return; }
                if(!pasalId){ showToast('Pasal wajib dipilih.','warning'); return; }
                var payload={ nama_rule:nama, trigger_type:document.getElementById('penTriggerType').value,
                    periode_bulan:document.getElementById('penPeriodeBulan').value||1,
                    izin_dihitung_hadir:document.getElementById('penIzinHadir').checked,
                    sakit_dihitung_hadir:document.getElementById('penSakitHadir').checked,
                    terlambat_dihitung_hadir:document.getElementById('penTerlambatHadir').checked,
                    pasal_id:pasalId, poin_override:document.getElementById('penPoinOverride').value||null,
                    keterangan:document.getElementById('penKeterangan').value.trim()||null,
                    aktif:document.getElementById('penAktif').checked };
                var url=id?'/admin/auto-rules/penghargaan/'+id:'/admin/auto-rules/penghargaan';
                apiRequest(id?'PUT':'POST',url,payload)
                    .then(function(r){ window.closePenghargaanModal(); renderPenghargaanRule(r,id?'update':'add'); showToast(id?'Rule diperbarui.':'Rule ditambahkan.','success'); })
                    .catch(function(e){ var m=e.message||(e.errors?Object.values(e.errors).flat().join('\n'):'Kesalahan.'); showToast(m,'error'); });
            };

            window.editPenghargaanRule=function(id) {
                apiRequest('GET','/admin/auto-rules/penghargaan').then(function(rules){
                    var r=rules.find(function(x){ return x.id==id; }); if(r) window.openPenghargaanRuleModal(r);
                });
            };

            window.deletePenghargaanRule=function(id) {
                if(!window.Swal){ if(!confirm('Hapus rule ini?')) return; _doPenDel(id); return; }
                Swal.fire({ icon:'warning',title:'Hapus rule ini?',text:'Tidak dapat dibatalkan.',
                    showCancelButton:true,confirmButtonColor:'#dc2626',confirmButtonText:'Hapus',cancelButtonText:'Batal' })
                    .then(function(r){ if(r.isConfirmed) _doPenDel(id); });
            };

            function _doPenDel(id) {
                apiRequest('DELETE','/admin/auto-rules/penghargaan/'+id)
                    .then(function(){ var el=document.getElementById('pen-rule-'+id); if(el) el.remove(); _checkPenEmpty(); showToast('Rule dihapus.','success'); })
                    .catch(function(){ showToast('Gagal menghapus.','error'); });
            }

            window.togglePenghargaanRule=function(id,lbl) {
                apiRequest('PATCH','/admin/auto-rules/penghargaan/'+id+'/toggle').then(function(r){
                    var el=document.getElementById('pen-rule-'+id);
                    if(el){ if(r.aktif) el.classList.remove('rule-inactive'); else el.classList.add('rule-inactive'); }
                    var cb=lbl?lbl.querySelector('input[type="checkbox"]'):null; if(cb) cb.checked=r.aktif;
                });
            };

            function renderPenghargaanRule(rule,mode) {
                var list=document.getElementById('penghargaanRulesList');
                var hint=document.getElementById('penEmptyHint'); if(hint) hint.remove();
                var kondisi=[]; if(rule.izin_dihitung_hadir) kondisi.push('izin=hadir');
                if(rule.sakit_dihitung_hadir) kondisi.push('sakit=hadir');
                if(rule.terlambat_dihitung_hadir) kondisi.push('terlambat=hadir');
                var html='<div class="rule-item '+(rule.aktif?'':'rule-inactive')+'" data-id="'+rule.id+'" id="pen-rule-'+rule.id+'">'
                    +'<div class="rule-item-header"><div class="rule-item-info">'
                    +'<span class="rule-item-name">'+esc(rule.nama_rule)+'</span>'
                    +'<span class="rule-item-meta">'+esc(rule.trigger_label||rule.trigger_type)+' · '+rule.periode_bulan+' bln'
                    +(kondisi.length?' · '+kondisi.join(', '):'')
                    +' · '+esc(rule.pasal_label||rule.pasal_id)+' · <strong>'+rule.poin_efektif+' poin</strong></span></div>'
                    +'<div class="rule-item-actions">'
                    +'<label class="scfg-toggle scfg-toggle-green rule-toggle" onclick="togglePenghargaanRule('+rule.id+',this)">'
                    +'<input type="checkbox" '+(rule.aktif?'checked':'')+' onclick="event.preventDefault()">'
                    +'<span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span></label>'
                    +'<button type="button" class="rule-btn-edit rule-btn-edit-green" onclick="editPenghargaanRule('+rule.id+')"><i class="fas fa-pencil-alt"></i></button>'
                    +'<button type="button" class="rule-btn-del" onclick="deletePenghargaanRule('+rule.id+')"><i class="fas fa-trash"></i></button>'
                    +'</div></div>'+(rule.keterangan?'<div class="rule-item-ket">'+esc(rule.keterangan)+'</div>':'')+'</div>';
                if(mode==='add') list.insertAdjacentHTML('beforeend',html);
                else { var ex=document.getElementById('pen-rule-'+rule.id); if(ex) ex.outerHTML=html; }
            }
            function _checkPenEmpty() {
                var list=document.getElementById('penghargaanRulesList');
                if(list&&!list.querySelector('.rule-item')) list.innerHTML='<div id="penEmptyHint" class="rule-empty-hint"><i class="fas fa-info-circle"></i> Belum ada rule.</div>';
            }

            // Tutup modal saat klik overlay atau Escape
            ['pelanggaranRuleModal','penghargaanRuleModal'].forEach(function(id) {
                var el=document.getElementById(id);
                if(el) el.addEventListener('click',function(e){ if(e.target===el) el.classList.remove('open'); });
            });
            document.addEventListener('keydown',function(e) {
                if(e.key==='Escape') {
                    ['pelanggaranRuleModal','penghargaanRuleModal'].forEach(function(id){
                        var el=document.getElementById(id); if(el) el.classList.remove('open');
                    });
                }
            });
        })();
    </script>
@endpush
