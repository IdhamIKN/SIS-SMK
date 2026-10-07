@extends('layouts.app')

@section('title', 'Konfigurasi Sekolah')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css" />
    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Ensure footer-bar is visible on school config page */
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

        /* Set CSS variable as fallback */
        .scfg-pg {
            --footer-h: 60px;
        }
    </style>
    <style>
        /* scope: .scfg-pg — tidak ada selector tanpa prefix ini */
        .scfg-pg {
            font-family: inherit;
            min-height: 100vh;
            background-color: #f8fafc;
        }

        /* Responsive container */
        .scfg-pg .scfg-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 16px;
        }

        /* Strip */
        .scfg-pg .scfg-strip {
            padding: 20px 20px 28px;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0ea5e9 100%);
            position: relative;
            overflow: hidden;
            margin: 0 -16px 16px;
        }

        .scfg-pg .scfg-strip::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .scfg-pg .scfg-strip::after {
            content: '';
            position: absolute;
            bottom: -24px;
            left: -20px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, .04);
            border-radius: 50%;
        }

        .scfg-pg .scfg-live {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 600;
            color: rgba(255, 255, 255, .9);
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .scfg-pg .scfg-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #7dd3fc;
            display: inline-block;
            animation: scfg-pulse 2s infinite;
        }

        @keyframes scfg-pulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .4
            }
        }

        .scfg-pg .scfg-strip h2 {
            font-size: 1.3rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 4px;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .scfg-pg .scfg-strip p {
            font-size: .8rem;
            color: rgba(255, 255, 255, .65);
            margin: 0;
            position: relative;
            z-index: 1;
        }

        /* alerts */
        .scfg-pg .scfg-ok,
        .scfg-pg .scfg-err {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: .84rem;
            margin: 12px 0 0;
        }

        .scfg-pg .scfg-ok {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .scfg-pg .scfg-err {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .scfg-pg .scfg-err ul {
            margin: 4px 0 0 16px;
            font-size: .8rem;
        }

        /* card */
        .scfg-pg .scfg-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            margin-bottom: 16px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
            overflow: hidden;
        }

        .scfg-pg .scfg-chead {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px 11px;
            border-bottom: 1px solid #f8fafc;
        }

        .scfg-pg .scfg-cico {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
        }

        .scfg-pg .scfg-chead h3 {
            margin: 0;
            font-size: .9rem;
            font-weight: 700;
            flex: 1;
        }

        .scfg-pg .scfg-opt {
            font-size: .65rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #64748b;
            flex-shrink: 0;
        }

        .scfg-pg .scfg-cbody {
            padding: 16px;
        }

        /* fields */
        .scfg-pg .scfg-fg {
            margin-bottom: 14px;
        }

        .scfg-pg .scfg-lbl {
            display: block;
            font-size: .8rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .scfg-pg .scfg-lbl .text-red-500 {
            color: #ef4444;
            font-weight: 800;
        }

        .scfg-pg .scfg-inp {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            box-sizing: border-box;
            -webkit-appearance: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        .scfg-pg .scfg-inp:focus {
            border-color: #0ea5e9;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, .1);
        }

        .scfg-pg .scfg-inp[readonly] {
            background: #f1f5f9;
            color: #64748b;
            cursor: default;
        }

        .scfg-pg .scfg-inp.iserr {
            border-color: #ef4444;
        }

        .scfg-pg .scfg-ferr {
            font-size: .72rem;
            color: #dc2626;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .scfg-pg .scfg-hint {
            font-size: .7rem;
            color: #94a3b8;
            margin-top: 4px;
        }

        .scfg-pg .scfg-g2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        /* hari efektif */
        .scfg-pg .scfg-hari-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 8px;
        }

        .scfg-pg .scfg-hcard {
            position: relative;
            cursor: pointer;
        }

        .scfg-pg .scfg-hcard input[type="checkbox"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
            margin: 0;
        }

        .scfg-pg .scfg-hbox {
            position: relative;
            z-index: 1;
            pointer-events: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            font-size: .82rem;
            font-weight: 600;
            color: #475569;
            transition: all .18s;
        }

        .scfg-pg .scfg-hbox .shd {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #e2e8f0;
            flex-shrink: 0;
            transition: background .18s;
        }

        .scfg-pg .scfg-hcard input:checked~.scfg-hbox {
            border-color: #0ea5e9;
            background: #e0f2fe;
            color: #0369a1;
        }

        .scfg-pg .scfg-hcard input:checked~.scfg-hbox .shd {
            background: #0ea5e9;
        }

        /* map */
        .scfg-pg .scfg-mwrap {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            margin-bottom: 14px;
        }

        .scfg-pg #scfgMap {
            width: 100%;
            height: 260px;
            z-index: 1;
        }

        .scfg-pg .scfg-mstatus {
            padding: 8px 12px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: .75rem;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .scfg-pg .scfg-clrbtn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 12px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 600;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            cursor: pointer;
            font-family: inherit;
            transition: background .18s;
        }

        .scfg-pg .scfg-clrbtn:hover {
            background: #fee2e2;
        }

        /* action bar — z-index 100 agar tidak menutup sidebar */
        .scfg-pg .scfg-bar {
            position: fixed;
            bottom: var(--footer-h, 60px);
            left: 0;
            right: 0;
            padding: 10px 16px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            z-index: 100;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
        }

        .scfg-pg .scfg-sbtn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: .9rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: inherit;
            background: linear-gradient(135deg, #0369a1, #0ea5e9);
            color: #fff;
            box-shadow: 0 3px 12px rgba(14, 165, 233, .3);
            transition: filter .18s;
        }

        .scfg-pg .scfg-sbtn:hover {
            filter: brightness(1.08);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .scfg-pg .scfg-container {
                padding: 0 12px;
            }

            .scfg-pg .scfg-strip {
                margin: 0 -12px 16px;
                padding: 16px 16px 20px;
            }

            .scfg-pg .scfg-strip h2 {
                font-size: 1.1rem;
            }

            .scfg-pg .scfg-card {
                margin-bottom: 12px;
            }

            .scfg-pg .scfg-cbody {
                padding: 12px;
            }

            .scfg-pg .scfg-g2 {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .scfg-pg .scfg-hari-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .scfg-pg #scfgMap {
                height: 200px;
            }

            .scfg-pg .scfg-mstatus {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }

            .scfg-pg .scfg-bar {
                padding: 12px 16px 16px;
                gap: 8px;
            }

            .scfg-pg .scfg-sbtn {
                padding: 14px 16px;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 480px) {
            .scfg-pg .scfg-hari-grid {
                grid-template-columns: 1fr;
            }

            .scfg-pg .scfg-strip h2 {
                flex-direction: column;
                gap: 4px;
                align-items: flex-start;
            }

            .scfg-pg #scfgMap {
                height: 180px;
            }
        }

        /* ── WA Notification Items ── */
        .scfg-pg .scfg-wa-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .scfg-pg .scfg-wa-ico {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
        }

        .scfg-pg .scfg-wa-info {
            flex: 1;
            min-width: 0;
        }

        .scfg-pg .scfg-wa-title {
            font-size: .86rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .scfg-pg .scfg-wa-desc {
            font-size: .76rem;
            color: #64748b;
            line-height: 1.5;
        }

        .scfg-pg .scfg-wa-dep {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 3px;
            font-size: .7rem;
            color: #0369a1;
            background: #e0f2fe;
            padding: 2px 7px;
            border-radius: 20px;
            font-weight: 600;
        }

        /* ── Help Button (Lampu) ── */
        .scfg-pg .scfg-help-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #fef9c3;
            border: 1.5px solid #fde047;
            color: #ca8a04;
            font-size: .82rem;
            cursor: pointer;
            flex-shrink: 0;
            transition: background .18s, transform .15s;
            outline: none;
            padding: 0;
            margin-left: auto;
        }

        .scfg-pg .scfg-help-btn:hover {
            background: #fef08a;
            transform: scale(1.12);
        }

        .scfg-pg .scfg-help-btn:focus {
            outline: 2px solid #fde047;
            outline-offset: 2px;
        }

        /* ── Help Modal ── */
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
            background: #fff;
            border-radius: 18px;
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
                transform: scale(.93) translateY(8px);
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
            padding: 16px 18px;
            border-bottom: 1px solid #f1f5f9;
            flex-shrink: 0;
        }

        .scfg-help-mhead-ico {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #fef9c3;
            border: 1.5px solid #fde047;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #ca8a04;
            flex-shrink: 0;
        }

        .scfg-help-mhead h4 {
            margin: 0;
            font-size: .92rem;
            font-weight: 800;
            color: #0f172a;
            flex: 1;
        }

        .scfg-help-close {
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
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
            font-size: .84rem;
            color: #334155;
            line-height: 1.65;
        }

        .scfg-help-mbody h5 {
            margin: 0 0 6px;
            font-size: .8rem;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: .04em;
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
            border-radius: 10px;
            padding: 10px 13px;
            margin-top: 10px;
        }

        .scfg-help-example .ex-label {
            font-size: .72rem;
            font-weight: 700;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 6px;
            display: block;
        }

        .scfg-help-example table {
            width: 100%;
            border-collapse: collapse;
            font-size: .8rem;
        }

        .scfg-help-example table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .scfg-help-example table td:first-child {
            font-weight: 600;
            color: #0369a1;
            white-space: nowrap;
            width: 42%;
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
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #0369a1, #0ea5e9);
            color: #fff;
            font-size: .84rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: filter .15s;
        }

        .scfg-help-okbtn:hover {
            filter: brightness(1.08);
        }

        /* Loading state */
        .scfg-pg .scfg-loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .scfg-pg .scfg-loading .scfg-sbtn::after {
            content: '';
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 8px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* ── Toggle Switch (sama seperti event/create) ── */
        .scfg-pg .scfg-toggle {
            position: relative;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .scfg-pg .scfg-toggle input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .scfg-pg .scfg-toggle-track {
            width: 52px;
            height: 28px;
            background: #e2e8f0;
            border-radius: 14px;
            transition: background .25s;
            display: flex;
            align-items: center;
            padding: 0 3px;
        }

        .scfg-pg .scfg-toggle-thumb {
            width: 22px;
            height: 22px;
            background: #fff;
            border-radius: 50%;
            transition: transform .25s;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .2);
        }

        .scfg-pg .scfg-toggle input:checked+.scfg-toggle-track {
            background: #16a34a;
        }

        .scfg-pg .scfg-toggle input:checked+.scfg-toggle-track .scfg-toggle-thumb {
            transform: translateX(24px);
        }

        /* Red variant toggle */
        .scfg-pg .scfg-toggle.scfg-toggle-red input:checked+.scfg-toggle-track {
            background: #dc2626;
        }

        /* ── Pasal Autocomplete Dropdown (sama seperti event/create) ── */
        .scfg-pg .pasal-dropdown-item {
            padding: 10px 14px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            transition: background .15s;
        }

        .scfg-pg .pasal-dropdown-item:last-child {
            border-bottom: none;
        }

        .scfg-pg .pasal-dropdown-item:hover,
        .scfg-pg .pasal-dropdown-item[data-active] {
            background: #fef2f2;
        }

        .scfg-pg .pasal-dropdown-item .pasal-item-label {
            font-size: .82rem;
            font-weight: 600;
            color: #0f172a;
            display: block;
        }

        .scfg-pg .pasal-dropdown-item .pasal-item-poin {
            font-size: .72rem;
            color: #64748b;
            margin-top: 2px;
            display: block;
        }

        /* Focus styles for accessibility */
        .scfg-pg .scfg-inp:focus,
        .scfg-pg .scfg-hcard:focus-within .scfg-hbox,
        .scfg-pg .scfg-sbtn:focus,
        .scfg-pg .scfg-clrbtn:focus {
            outline: 2px solid #0ea5e9;
            outline-offset: 2px;
        }

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .scfg-pg .scfg-card {
                border-width: 2px;
            }

            .scfg-pg .scfg-inp {
                border-width: 2px;
            }

            .scfg-pg .scfg-hbox {
                border-width: 2px;
            }
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {

            .scfg-pg .scfg-inp,
            .scfg-pg .scfg-hbox,
            .scfg-pg .scfg-sbtn,
            .scfg-pg .scfg-clrbtn {
                transition: none;
            }

            .scfg-pg .scfg-dot {
                animation: none;
            }
        }
    </style>
@endpush

@section('content')
    <div class="scfg-pg" style="padding-bottom:calc(var(--footer-h,60px) + 88px);">

        {{-- Strip --}}
        <div class="scfg-strip" style="padding-top:calc(var(--header-h,56px) + 16px);">
            <div class="scfg-live"><span class="scfg-dot"></span>Pengaturan Sistem</div>
            <h2><i class="fas fa-cog"></i> Konfigurasi Sekolah</h2>
            <p>Atur jam, hari efektif, dan lokasi presensi sekolah</p>
        </div>

        <div class="scfg-container">
            @php
                $formatTimeValue = fn($value) => $value ? date('H:i', strtotime($value)) : '';
            @endphp
            <form id="scfgForm" action="{{ route('admin.school-config.update') }}" method="POST" novalidate>
                @csrf @method('PUT')

                {{-- Identitas Sistem --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#dbeafe;color:#1d4ed8;"><i class="fas fa-school"
                                aria-hidden="true"></i></div>
                        <h3>Identitas Sistem</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('identitas_sistem')"
                            title="Panduan pengisian" aria-label="Panduan pengisian Identitas Sistem">
                            <i class="fas fa-lightbulb"></i>
                        </button>
                    </div>
                    <div class="scfg-cbody">
                        <div class="scfg-fg">
                            <label class="scfg-lbl" for="system_name">Nama Sistem <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="system_name" name="system_name" required
                                class="scfg-inp @error('system_name') iserr @enderror"
                                value="{{ old('system_name', $sekolah->system_name ?? 'SIS SMKN 5 Madiun') }}"
                                placeholder="Nama sistem aplikasi" maxlength="100" aria-describedby="system_name_hint">
                            @error('system_name')
                                <div class="scfg-ferr" role="alert"><i class="fas fa-exclamation-circle"
                                        aria-hidden="true"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Identitas Sekolah --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-building"></i>
                        </div>
                        <h3>Identitas Sekolah</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('identitas_sekolah')"
                            title="Panduan pengisian" aria-label="Panduan pengisian Identitas Sekolah">
                            <i class="fas fa-lightbulb"></i>
                        </button>
                    </div>
                    <div class="scfg-cbody">
                        <div class="scfg-fg">
                            <label class="scfg-lbl" for="sekolah">Nama Sekolah <span class="text-red-500">*</span></label>
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
                                <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="scfg-g2">
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="telp">Telepon</label>
                                <input type="text" id="telp" name="telp"
                                    class="scfg-inp @error('telp') iserr @enderror"
                                    value="{{ old('telp', $sekolah->telp) }}" placeholder="021-12345678" maxlength="20">
                                @error('telp')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="email">Email</label>
                                <input type="email" id="email" name="email"
                                    class="scfg-inp @error('email') iserr @enderror"
                                    value="{{ old('email', $sekolah->email) }}" placeholder="info@sekolah.sch.id"
                                    maxlength="255">
                                @error('email')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="scfg-g2">
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="kab">Kabupaten</label>
                                <input type="text" id="kab" name="kab"
                                    class="scfg-inp @error('kab') iserr @enderror"
                                    value="{{ old('kab', $sekolah->kab) }}" placeholder="Kabupaten Madiun"
                                    maxlength="100">
                                @error('kab')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="alias">Alias</label>
                                <input type="text" id="alias" name="alias"
                                    class="scfg-inp @error('alias') iserr @enderror"
                                    value="{{ old('alias', $sekolah->alias) }}" placeholder="SMKN5MDN" maxlength="50">
                                @error('alias')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kepala Sekolah --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#fed7d7;color:#c53030;"><i class="fas fa-user-tie"></i>
                        </div>
                        <h3>Kepala Sekolah</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('kepala_sekolah')"
                            title="Panduan pengisian" aria-label="Panduan pengisian Kepala Sekolah">
                            <i class="fas fa-lightbulb"></i>
                        </button>
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
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="nip_ks">NIP Kepala Sekolah</label>
                                <input type="text" id="nip_ks" name="nip_ks"
                                    class="scfg-inp @error('nip_ks') iserr @enderror"
                                    value="{{ old('nip_ks', $sekolah->nip_ks) }}" placeholder="198001012010011001"
                                    maxlength="50">
                                @error('nip_ks')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Wakil Kepala Sekolah --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#feebc8;color:#d69e2e;"><i
                                class="fas fa-user-graduate"></i></div>
                        <h3>Wakil Kepala Sekolah</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('waka')"
                            title="Panduan pengisian" aria-label="Panduan pengisian Wakil Kepala Sekolah">
                            <i class="fas fa-lightbulb"></i>
                        </button>
                    </div>
                    <div class="scfg-cbody">
                        <div class="scfg-g2">
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="nama_waka">Nama Wakil Kepala Sekolah</label>
                                <input type="text" id="nama_waka" name="nama_waka"
                                    class="scfg-inp @error('nama_waka') iserr @enderror"
                                    value="{{ old('nama_waka', $sekolah->nama_waka) }}"
                                    placeholder="Drs. Siti Aminah, M.Pd." maxlength="255">
                                @error('nama_waka')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="nip_waka">NIP Wakil Kepala Sekolah</label>
                                <input type="text" id="nip_waka" name="nip_waka"
                                    class="scfg-inp @error('nip_waka') iserr @enderror"
                                    value="{{ old('nip_waka', $sekolah->nip_waka) }}" placeholder="198501022010012002"
                                    maxlength="50">
                                @error('nip_waka')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ketua --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#c4f1f9;color:#0e7490;"><i class="fas fa-user-cog"></i>
                        </div>
                        <h3>Ketua</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('ketua')"
                            title="Panduan pengisian" aria-label="Panduan pengisian Ketua">
                            <i class="fas fa-lightbulb"></i>
                        </button>
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
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="nip_ketua">NIP Ketua</label>
                                <input type="text" id="nip_ketua" name="nip_ketua"
                                    class="scfg-inp @error('nip_ketua') iserr @enderror"
                                    value="{{ old('nip_ketua', $sekolah->nip_ketua) }}" placeholder="197801032008011003"
                                    maxlength="50">
                                @error('nip_ketua')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Website & Media --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#e0e7ff;color:#3730a3;"><i class="fas fa-globe"></i>
                        </div>
                        <h3>Website & Media</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('website_media')"
                            title="Panduan pengisian" aria-label="Panduan pengisian Website &amp; Media">
                            <i class="fas fa-lightbulb"></i>
                        </button>
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
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="site_logo">URL Logo</label>
                                <input type="url" id="site_logo" name="site_logo"
                                    class="scfg-inp @error('site_logo') iserr @enderror"
                                    value="{{ old('site_logo', $sekolah->site_logo) }}"
                                    placeholder="https://smkn5madiun.sch.id/logo.png" maxlength="255">
                                @error('site_logo')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="scfg-fg">
                            <label class="scfg-lbl" for="wasekolah">WhatsApp Sekolah</label>
                            <input type="text" id="wasekolah" name="wasekolah"
                                class="scfg-inp @error('wasekolah') iserr @enderror"
                                value="{{ old('wasekolah', $sekolah->wasekolah) }}" placeholder="6281234567890"
                                maxlength="20">
                            @error('wasekolah')
                                <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Jam Sekolah --}}
                <div class="scfg-card">
                    <div class="scfg-chead">
                        <div class="scfg-cico" style="background:#fef3c7;color:#b45309;"><i class="fas fa-clock"></i>
                        </div>
                        <h3>Jadwal Absensi Siswa</h3>
                        <button type="button" class="scfg-help-btn" onclick="scfgHelp('jadwal_absensi')"
                            title="Panduan pengisian" aria-label="Panduan pengisian Jadwal Absensi Siswa">
                            <i class="fas fa-lightbulb"></i>
                        </button>
                    </div>
                    <div class="scfg-cbody">
                        <div class="scfg-g2">
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="jam_masuk">Jam Masuk Normal</label>
                                <input type="time" id="jam_masuk" name="jam_masuk"
                                    class="scfg-inp @error('jam_masuk') iserr @enderror"
                                    value="{{ old('jam_masuk', $formatTimeValue($sekolah->jam_masuk)) }}">
                                @error('jam_masuk')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="scfg-fg">
                                <label class="scfg-lbl" for="jam_pulang">Jam Pulang Normal</label>
                                <input type="time" id="jam_pulang" name="jam_pulang"
                                    class="scfg-inp @error('jam_pulang') iserr @enderror"
                                    value="{{ old('jam_pulang', $formatTimeValue($sekolah->jam_pulang)) }}">
                                @error('jam_pulang')
                                    <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @php
                            $hariKhususRaw = old('hari_khusus', $sekolah->hari_khusus ?? null);
                            if (is_string($hariKhususRaw)) {
                                $hariKhususSelected = json_decode($hariKhususRaw, true) ?: [];
                            } elseif (is_array($hariKhususRaw)) {
                                $hariKhususSelected = $hariKhususRaw;
                            } else {
                                $hariKhususSelected = ['Jumat'];
                            }
                            $hariKhususList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                        @endphp
                        <div class="scfg-fg">
                            <label class="scfg-lbl">Hari Khusus</label>
                            <div class="scfg-hari-grid" role="group" aria-label="Pilih hari khusus absensi">
                                @foreach ($hariKhususList as $h)
                                    <label class="scfg-hcard">
                                        <input type="checkbox" name="hari_khusus[]" value="{{ $h }}"
                                            {{ in_array($h, $hariKhususSelected) ? 'checked' : '' }}
                                            aria-label="Pilih {{ $h }} sebagai hari khusus">
                                        <div class="scfg-hbox"><span class="shd"
                                                aria-hidden="true"></span>{{ $h }}</div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @error('hari_khusus')
                            <div class="scfg-ferr" style="margin-top:8px;"><i
                                    class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="scfg-g2">
                        <div class="scfg-fg">
                            <label class="scfg-lbl" for="jam_masuk_khusus">Jam Masuk Khusus</label>
                            <input type="time" id="jam_masuk_khusus" name="jam_masuk_khusus"
                                class="scfg-inp @error('jam_masuk_khusus') iserr @enderror"
                                value="{{ old('jam_masuk_khusus', $formatTimeValue($sekolah->jam_masuk_khusus ?? null)) }}">
                            @error('jam_masuk_khusus')
                                <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="scfg-fg">
                            <label class="scfg-lbl" for="jam_pulang_khusus">Jam Pulang Khusus</label>
                            <input type="time" id="jam_pulang_khusus" name="jam_pulang_khusus"
                                class="scfg-inp @error('jam_pulang_khusus') iserr @enderror"
                                value="{{ old('jam_pulang_khusus', $formatTimeValue($sekolah->jam_pulang_khusus ?? null)) }}">
                            @error('jam_pulang_khusus')
                                <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
        </div>

        {{-- Konfigurasi Keterlambatan --}}
        <div class="scfg-card">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#fff7ed;color:#c2410c;"><i class="fas fa-hourglass-half"></i>
                </div>
                <h3>Konfigurasi Keterlambatan</h3>
                <button type="button" class="scfg-help-btn" onclick="scfgHelp('keterlambatan')"
                    title="Panduan pengisian" aria-label="Panduan pengisian Konfigurasi Keterlambatan">
                    <i class="fas fa-lightbulb"></i>
                </button>
            </div>
            <div class="scfg-cbody">
                <div class="scfg-g2">
                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="jam_mulai_absensi">Jam Mulai Absensi Masuk</label>
                        <input type="time" id="jam_mulai_absensi" name="jam_mulai_absensi"
                            class="scfg-inp @error('jam_mulai_absensi') iserr @enderror"
                            value="{{ old('jam_mulai_absensi', $formatTimeValue($sekolah->jam_mulai_absensi ?? null)) }}"
                            placeholder="06:00">
                        @error('jam_mulai_absensi')
                            <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="batas_tepat_waktu">Batas Tepat Waktu</label>
                        <input type="time" id="batas_tepat_waktu" name="batas_tepat_waktu"
                            class="scfg-inp @error('batas_tepat_waktu') iserr @enderror"
                            value="{{ old('batas_tepat_waktu', $formatTimeValue($sekolah->batas_tepat_waktu ?? null)) }}"
                            placeholder="07:00">
                        @error('batas_tepat_waktu')
                            <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Notifikasi WhatsApp & Auto Alfa --}}
        {{-- Data JSON pasal dikirim ke JS --}}
        <script id="pasal-alfa-data" type="application/json">
            {!! json_encode($pasalPelanggaran->map(fn($p) => [
                'id'    => $p->idpasal,
                'label' => '[' . $p->idpasal . '] ' . $p->pasal,
                'isi'   => $p->pasal,
                'poin'  => (int) ($p->poin_default ?? $p->skormin ?? 0),
            ])) !!}
        </script>

        {{-- Kartu Notifikasi WhatsApp --}}
        <div class="scfg-card">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#dcfce7;color:#15803d;"><i class="fab fa-whatsapp"></i></div>
                <h3>Notifikasi WhatsApp</h3>
                <span class="scfg-opt">Opsional</span>
                <button type="button" class="scfg-help-btn" onclick="scfgHelp('notif_wa')" title="Panduan pengisian"
                    aria-label="Panduan pengisian Notifikasi WhatsApp">
                    <i class="fas fa-lightbulb"></i>
                </button>
            </div>
            <div class="scfg-cbody">
                {{-- Notif Masuk --}}
                <div class="scfg-wa-item">
                    <div class="scfg-wa-ico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div class="scfg-wa-info">
                        <div class="scfg-wa-title">Presensi Masuk</div>
                        <div class="scfg-wa-desc">Kirim WA ke orang tua saat siswa scan absen masuk (Hadir atau Terlambat)
                        </div>
                    </div>
                    <label class="scfg-toggle" for="wa_notif_masuk_enabled">
                        <input type="checkbox" id="wa_notif_masuk_enabled" name="wa_notif_masuk_enabled" value="1"
                            {{ old('wa_notif_masuk_enabled', $sekolah->wa_notif_masuk_enabled ?? false) ? 'checked' : '' }}>
                        <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                    </label>
                </div>

                {{-- Notif Pulang --}}
                <div class="scfg-wa-item">
                    <div class="scfg-wa-ico" style="background:#fef3c7;color:#b45309;"><i
                            class="fas fa-sign-out-alt"></i></div>
                    <div class="scfg-wa-info">
                        <div class="scfg-wa-title">Presensi Pulang</div>
                        <div class="scfg-wa-desc">Kirim WA ke orang tua saat siswa scan absen pulang</div>
                    </div>
                    <label class="scfg-toggle" for="wa_notif_pulang_enabled">
                        <input type="checkbox" id="wa_notif_pulang_enabled" name="wa_notif_pulang_enabled"
                            value="1"
                            {{ old('wa_notif_pulang_enabled', $sekolah->wa_notif_pulang_enabled ?? false) ? 'checked' : '' }}>
                        <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                    </label>
                </div>

                {{-- Notif Auto Alfa --}}
                <div class="scfg-wa-item">
                    <div class="scfg-wa-ico" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-user-times"></i>
                    </div>
                    <div class="scfg-wa-info">
                        <div class="scfg-wa-title">Auto Alfa</div>
                        <div class="scfg-wa-desc">Kirim WA ke orang tua saat siswa otomatis dicatat Alfa oleh sistem
                            <span class="scfg-wa-dep"><i class="fas fa-link"></i> Aktif jika fitur Auto Alfa
                                dinyalakan</span>
                        </div>
                    </div>
                    <label class="scfg-toggle" for="wa_notif_alfa_enabled">
                        <input type="checkbox" id="wa_notif_alfa_enabled" name="wa_notif_alfa_enabled" value="1"
                            {{ old('wa_notif_alfa_enabled', $sekolah->wa_notif_alfa_enabled ?? false) ? 'checked' : '' }}>
                        <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                    </label>
                </div>

                {{-- Notif Absen Event --}}
                <div class="scfg-wa-item">
                    <div class="scfg-wa-ico" style="background:#ede9fe;color:#7c3aed;"><i
                            class="fas fa-calendar-check"></i></div>
                    <div class="scfg-wa-info">
                        <div class="scfg-wa-title">Absen Kegiatan / Event</div>
                        <div class="scfg-wa-desc">Kirim WA ke orang tua saat siswa scan absen pada kegiatan atau event
                            sekolah</div>
                    </div>
                    <label class="scfg-toggle" for="wa_notif_event_enabled">
                        <input type="checkbox" id="wa_notif_event_enabled" name="wa_notif_event_enabled" value="1"
                            {{ old('wa_notif_event_enabled', $sekolah->wa_notif_event_enabled ?? false) ? 'checked' : '' }}>
                        <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                    </label>
                </div>

                {{-- Notif Ambang Poin Tatib --}}
                <div class="scfg-wa-item">
                    <div class="scfg-wa-ico" style="background:#fff7ed;color:#c2410c;"><i
                            class="fas fa-exclamation-triangle"></i></div>
                    <div class="scfg-wa-info">
                        <div class="scfg-wa-title">Peringatan Poin Pelanggaran</div>
                        <div class="scfg-wa-desc">Kirim WA ke orang tua saat poin pelanggaran siswa mencapai batas ambang
                            yang ditentukan</div>
                    </div>
                    <label class="scfg-toggle" for="wa_notif_tatib_enabled">
                        <input type="checkbox" id="wa_notif_tatib_enabled" name="wa_notif_tatib_enabled" value="1"
                            {{ old('wa_notif_tatib_enabled', $sekolah->wa_notif_tatib_enabled ?? false) ? 'checked' : '' }}>
                        <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                    </label>
                </div>

                {{-- Notif Laporan Kehadiran Guru --}}
                <div class="scfg-wa-item" style="border-bottom:none;align-items:flex-start;flex-wrap:wrap;gap:0;">
                    {{-- Baris toggle --}}
                    <div
                        style="display:flex;align-items:center;gap:12px;width:100%;padding-bottom:10px;border-bottom:1px dashed #e2e8f0;margin-bottom:10px;">
                        <div class="scfg-wa-ico" style="background:#e0f2fe;color:#0369a1;flex-shrink:0;"><i
                                class="fas fa-chalkboard-teacher"></i></div>
                        <div class="scfg-wa-info">
                            <div class="scfg-wa-title">Laporan Kehadiran Guru</div>
                            <div class="scfg-wa-desc">Kirim WA ke penerima yang ditentukan saat siswa/GTK mengirim laporan
                                kehadiran guru</div>
                        </div>
                        <label class="scfg-toggle" for="wa_notif_laporan_guru_enabled" style="flex-shrink:0;">
                            <input type="checkbox" id="wa_notif_laporan_guru_enabled"
                                name="wa_notif_laporan_guru_enabled" value="1"
                                onchange="toggleLaporanGuruNomor(this)"
                                {{ old('wa_notif_laporan_guru_enabled', $sekolah->wa_notif_laporan_guru_enabled ?? false) ? 'checked' : '' }}>
                            <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                        </label>
                    </div>
                    {{-- Field nomor penerima (muncul saat toggle ON) --}}
                    <div id="laporanGuruNomorWrap"
                        style="width:100%;display:{{ old('wa_notif_laporan_guru_enabled', $sekolah->wa_notif_laporan_guru_enabled ?? false) ? 'block' : 'none' }};">
                        <label class="scfg-lbl" for="wa_notif_laporan_guru_nomor" style="margin-bottom:5px;">
                            Nomor Penerima Notifikasi
                            <span style="font-weight:400;color:#64748b;margin-left:4px;">(bisa lebih dari 1)</span>
                        </label>
                        @php
                            $nomorLaporanGuru = $sekolah->wa_notif_laporan_guru_nomor ?? [];
                            $nomorLaporanGuruText = is_array($nomorLaporanGuru)
                                ? implode("\n", $nomorLaporanGuru)
                                : $nomorLaporanGuru;
                            $nomorLaporanGuruOld = old('wa_notif_laporan_guru_nomor', $nomorLaporanGuruText);
                        @endphp
                        <textarea id="wa_notif_laporan_guru_nomor" name="wa_notif_laporan_guru_nomor" rows="3"
                            class="scfg-inp @error('wa_notif_laporan_guru_nomor') iserr @enderror"
                            placeholder="6281234567890&#10;6289876543210&#10;(isi satu nomor per baris)"
                            style="font-family:monospace;font-size:.82rem;resize:vertical;">{{ $nomorLaporanGuruOld }}</textarea>
                        @error('wa_notif_laporan_guru_nomor')
                            <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>
        </div>

        {{-- Kartu Auto Alfa --}}
        <div class="scfg-card">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-robot"></i></div>
                <h3>Auto Alfa Siswa</h3>
                <span class="scfg-opt">Opsional</span>
                <button type="button" class="scfg-help-btn" onclick="scfgHelp('auto_alfa')" title="Panduan pengisian"
                    aria-label="Panduan pengisian Auto Alfa Siswa">
                    <i class="fas fa-lightbulb"></i>
                </button>
            </div>
            <div class="scfg-cbody">

                {{-- A. Toggle Aktifkan Auto Alfa --}}
                <div
                    style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:12px 14px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;margin-bottom:12px;">
                    <div style="flex:1;">
                        <div style="font-size:.87rem;font-weight:700;color:#0f172a;">
                            <i class="fas fa-power-off" style="color:#dc2626;margin-right:6px;"></i>
                            Aktifkan Auto Alfa
                        </div>
                    </div>
                    <label class="scfg-toggle scfg-toggle-red" for="auto_alfa_enabled" style="flex-shrink:0;">
                        <input type="checkbox" id="auto_alfa_enabled" name="auto_alfa_enabled" value="1"
                            {{ old('auto_alfa_enabled', $sekolah->auto_alfa_enabled ?? false) ? 'checked' : '' }}
                            onchange="toggleAutoAlfaSection()">
                        <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                    </label>
                </div>

                {{-- Bagian yang muncul saat Auto Alfa aktif --}}
                <div id="autoAlfaDetail"
                    style="display:{{ old('auto_alfa_enabled', $sekolah->auto_alfa_enabled ?? false) ? 'block' : 'none' }};">

                    {{-- B. Batas Akhir Absen Masuk --}}
                    <div class="scfg-g2" style="margin-bottom:14px;">
                        <div class="scfg-fg">
                            <label class="scfg-lbl" for="batas_absen_masuk">
                                Batas Akhir Absen Masuk
                                <span
                                    style="font-size:.68rem;font-weight:400;color:#64748b;margin-left:4px;">(Opsional)</span>
                            </label>
                            <input type="time" id="batas_absen_masuk" name="batas_absen_masuk"
                                class="scfg-inp @error('batas_absen_masuk') iserr @enderror"
                                value="{{ old('batas_absen_masuk', $formatTimeValue($sekolah->batas_absen_masuk ?? null)) }}"
                                placeholder="09:00">
                            @error('batas_absen_masuk')
                                <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="scfg-fg">
                            <label class="scfg-lbl" for="jam_eksekusi_auto_alfa">
                                Jam Eksekusi Auto Alfa <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="jam_eksekusi_auto_alfa" name="jam_eksekusi_auto_alfa"
                                class="scfg-inp @error('jam_eksekusi_auto_alfa') iserr @enderror"
                                value="{{ old('jam_eksekusi_auto_alfa', $formatTimeValue($sekolah->jam_eksekusi_auto_alfa ?? null)) }}"
                                placeholder="08:00">
                            @error('jam_eksekusi_auto_alfa')
                                <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- C. Toggle Auto Point Pelanggaran Alfa --}}
                    <div
                        style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:12px 14px;border:1px solid #fecaca;border-radius:12px;background:#fff5f5;margin-bottom:12px;">
                        <div style="flex:1;">
                            <div style="font-size:.87rem;font-weight:700;color:#0f172a;margin-bottom:3px;">
                                <i class="fas fa-gavel" style="color:#dc2626;margin-right:6px;"></i>
                                Auto Point Pelanggaran Alfa
                            </div>
                        </div>
                        <label class="scfg-toggle scfg-toggle-red" for="auto_point_alfa_enabled" style="flex-shrink:0;">
                            <input type="checkbox" id="auto_point_alfa_enabled" name="auto_point_alfa_enabled"
                                value="1"
                                {{ old('auto_point_alfa_enabled', $sekolah->auto_point_alfa_enabled ?? false) ? 'checked' : '' }}
                                onchange="toggleAutoPointAlfa()">
                            <span class="scfg-toggle-track"><span class="scfg-toggle-thumb"></span></span>
                        </label>
                    </div>

                    {{-- D. Pilih Pasal Pelanggaran Alfa --}}
                    <div id="pasalAlfaGroup"
                        style="display:{{ old('auto_point_alfa_enabled', $sekolah->auto_point_alfa_enabled ?? false) ? 'block' : 'none' }};margin-bottom:12px;">
                        <label class="scfg-lbl">Pasal Pelanggaran untuk Auto Alfa <span
                                class="text-red-500">*</span></label>
                        <input type="hidden" name="pasal_alfa_id" id="pasal_alfa_hidden"
                            value="{{ old('pasal_alfa_id', $sekolah->pasal_alfa_id ?? '') }}">
                        <input type="text" id="pasal_alfa_search"
                            class="scfg-inp @error('pasal_alfa_id') iserr @enderror"
                            placeholder="Ketik kode atau nama pasal pelanggaran..." autocomplete="off" value="">
                        @error('pasal_alfa_id')
                            <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                        <div id="pasal_alfa_badge"
                            style="display:none;align-items:center;gap:8px;margin-top:8px;font-size:.82rem;color:#dc2626;background:#fef2f2;padding:8px 12px;border-radius:8px;border:1px solid #fecaca;">
                            <i class="fas fa-tag" style="flex-shrink:0;"></i>
                            <span id="pasal_alfa_badge_text" style="flex:1;line-height:1.4;"></span>
                            <button type="button" id="pasal_alfa_clear"
                                style="background:none;border:none;cursor:pointer;color:#ef4444;font-size:1rem;padding:0 4px;flex-shrink:0;"
                                aria-label="Hapus pilihan pasal">✕</button>
                        </div>
                    </div>

                </div>{{-- end autoAlfaDetail --}}
            </div>
        </div>
        {{-- Dropdown pasal alfa — di luar card agar tidak ter-clip overflow:hidden --}}
        <div id="pasal_alfa_dropdown"
            style="
            display:none;position:fixed;background:#fff;
            border:1px solid #e2e8f0;border-radius:10px;
            max-height:260px;overflow-y:auto;z-index:9999;
            box-shadow:0 8px 30px rgba(0,0,0,.15);">
        </div>

        {{-- ═══════════════════════════════════════════
             KARTU MODE LIBUR PANJANG
        ═══════════════════════════════════════════ --}}
        @php
            $liburMode = old('libur_mode', $sekolah->libur_mode ?? false);
            $liburDari = old('libur_dari', optional($sekolah->libur_dari)->format('Y-m-d') ?? '');
            $liburSampai = old('libur_sampai', optional($sekolah->libur_sampai)->format('Y-m-d') ?? '');
            $sedangLibur = $sekolah?->sedangLibur() ?? false;
        @endphp
        <div class="scfg-card" style="border: {{ $sedangLibur ? '2px solid #f97316' : '1px solid #e2e8f0' }};">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#ffedd5;color:#c2410c;">
                    <i class="fas fa-umbrella-beach"></i>
                </div>
                <h3>Mode Libur Panjang</h3>
                @if ($sedangLibur)
                    <span
                        style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;
                                 border-radius:20px;background:#ffedd5;color:#c2410c;font-size:.7rem;
                                 font-weight:700;border:1px solid #fed7aa;margin-left:auto;">
                        <i class="fas fa-circle" style="font-size:.45rem;"></i> AKTIF SEKARANG
                    </span>
                @endif
            </div>
            <div class="scfg-cbody">

                {{-- Info box --}}
                <div
                    style="padding:12px 14px;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;
                            font-size:.78rem;color:#c2410c;margin-bottom:14px;display:flex;gap:10px;">
                    <i class="fas fa-info-circle" style="margin-top:2px;flex-shrink:0;"></i>
                    <div>
                        <strong>Apa ini?</strong> Saat Mode Libur Panjang aktif dan hari ini berada dalam rentang tanggal
                        yang ditentukan,
                        <strong>semua proses otomatis</strong> akan berhenti:
                        Auto Alfa, Auto-fill absen, Auto Point Pelanggaran Event, serta semua
                        notifikasi WhatsApp (masuk, pulang, alfa, event, tatib).
                    </div>
                </div>

                {{-- Toggle aktifkan --}}
                <div
                    style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px;
                            padding:12px 14px;border:1px solid #e2e8f0;border-radius:12px;
                            background:#f8fafc;margin-bottom:12px;">
                    <div style="flex:1;">
                        <div style="font-size:.87rem;font-weight:700;color:#0f172a;">
                            <i class="fas fa-power-off" style="color:#c2410c;margin-right:6px;"></i>
                            Aktifkan Mode Libur Panjang
                        </div>
                    </div>
                    <label class="scfg-toggle" for="libur_mode" style="flex-shrink:0;--toggle-on:#f97316;">
                        <input type="checkbox" id="libur_mode" name="libur_mode" value="1"
                            {{ $liburMode ? 'checked' : '' }} onchange="toggleLiburSection()">
                        <span class="scfg-toggle-track" style="{{ $liburMode ? 'background:#f97316;' : '' }}">
                            <span class="scfg-toggle-thumb"></span>
                        </span>
                    </label>
                </div>

                {{-- Rentang tanggal — muncul selalu agar mudah diatur --}}
                <div id="liburDetail">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:8px;">
                        <div class="scfg-fg">
                            <label class="scfg-lbl" for="libur_dari">
                                <i class="fas fa-calendar-day" style="margin-right:4px;color:#f97316;"></i>
                                Mulai Libur <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="date" id="libur_dari" name="libur_dari"
                                class="scfg-inp @error('libur_dari') is-invalid @enderror" value="{{ $liburDari }}">
                            @error('libur_dari')
                                <div class="scfg-err">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="scfg-fg">
                            <label class="scfg-lbl" for="libur_sampai">
                                <i class="fas fa-calendar-check" style="margin-right:4px;color:#f97316;"></i>
                                Sampai Libur <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="date" id="libur_sampai" name="libur_sampai"
                                class="scfg-inp @error('libur_sampai') is-invalid @enderror"
                                value="{{ $liburSampai }}">
                            @error('libur_sampai')
                                <div class="scfg-err">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if ($sedangLibur)
                        <div
                            style="padding:10px 13px;background:#fff7ed;border:1px solid #fed7aa;
                                    border-radius:10px;font-size:.78rem;color:#c2410c;display:flex;
                                    align-items:center;gap:8px;font-weight:600;">
                            <i class="fas fa-pause-circle"></i>
                            Sistem sedang dalam mode libur.
                            Semua proses otomatis dan notifikasi WA <strong>tidak berjalan</strong>
                            hingga {{ \Carbon\Carbon::parse($sekolah->libur_sampai)->translatedFormat('d F Y') }}.
                        </div>
                    @elseif ($liburMode && $liburDari && $liburSampai)
                        <div
                            style="padding:10px 13px;background:#f0fdf4;border:1px solid #bbf7d0;
                                    border-radius:10px;font-size:.78rem;color:#15803d;display:flex;
                                    align-items:center;gap:8px;">
                            <i class="fas fa-check-circle"></i>
                            Mode libur terjadwal:
                            {{ \Carbon\Carbon::parse($liburDari)->translatedFormat('d F Y') }}
                            –
                            {{ \Carbon\Carbon::parse($liburSampai)->translatedFormat('d F Y') }}
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- Hari Efektif & Absen Otomatis (digabung) --}}
        <div class="scfg-card">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-calendar-week"></i>
                </div>
                <h3>Hari Efektif Sekolah</h3>
                <button type="button" class="scfg-help-btn" onclick="scfgHelp('hari_efektif')"
                    title="Panduan pengisian" aria-label="Panduan pengisian Hari Efektif Sekolah">
                    <i class="fas fa-lightbulb"></i>
                </button>
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
                                aria-label="Pilih {{ $h }} sebagai hari efektif">
                            <div class="scfg-hbox"><span class="shd" aria-hidden="true"></span>{{ $h }}
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('hari_efektif')
                    <div class="scfg-ferr" style="margin-top:8px;"><i
                            class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                @enderror

                {{-- Info absen otomatis --}}
                <div
                    style="margin-top:14px;padding:10px 13px;background:#fefce8;border:1px solid #fde68a;border-radius:10px;font-size:.78rem;color:#854d0e;display:flex;align-items:flex-start;gap:9px;">
                    <i class="fas fa-robot" style="margin-top:1px;flex-shrink:0;"></i>
                    <span>
                        Absen otomatis berjalan pukul <strong>08:00</strong> setiap hari.
                        Saat ini aktif pada:
                        <strong>{{ count($selectedHari) > 0 ? implode(', ', $selectedHari) : '— (tidak ada hari aktif)' }}</strong>.
                    </span>
                </div>
            </div>
        </div>

        {{-- Lokasi Presensi --}}
        <div class="scfg-card">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-map-marker-alt"></i>
                </div>
                <h3>Lokasi Presensi</h3>
                <span class="scfg-opt">Opsional</span>
                <button type="button" class="scfg-help-btn" onclick="scfgHelp('lokasi_presensi')"
                    title="Panduan pengisian" aria-label="Panduan pengisian Lokasi Presensi">
                    <i class="fas fa-lightbulb"></i>
                </button>
            </div>
            <div class="scfg-cbody">
                <div class="scfg-mwrap">
                    <div id="scfgMap" role="application" aria-label="Peta lokasi sekolah"></div>
                    <div class="scfg-mstatus">
                        <span><i class="fas fa-mouse-pointer" aria-hidden="true"></i> Klik peta atau gunakan
                            pencarian</span>
                        <span id="scfgCoord" aria-live="polite">Lat: -, Lng: -</span>
                    </div>
                </div>
                <div class="scfg-g2" style="margin-bottom:10px;">
                    <div class="scfg-fg">
                        <label class="scfg-lbl">Latitude</label>
                        <input type="text" id="scfgLat" name="latitude"
                            class="scfg-inp @error('latitude') iserr @enderror"
                            value="{{ old('latitude', $sekolah->latitude ?? '') }}" placeholder="-7.6291" readonly>
                        @error('latitude')
                            <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="scfg-fg">
                        <label class="scfg-lbl">Longitude</label>
                        <input type="text" id="scfgLng" name="longitude"
                            class="scfg-inp @error('longitude') iserr @enderror"
                            value="{{ old('longitude', $sekolah->longitude ?? '') }}" placeholder="111.5230" readonly>
                        @error('longitude')
                            <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="scfg-fg">
                    <label class="scfg-lbl" for="scfgRadius">Radius Absensi (meter)</label>
                    <input type="number" id="scfgRadius" name="radius_meter" min="10" max="50000"
                        step="1" class="scfg-inp @error('radius_meter') iserr @enderror"
                        value="{{ old('radius_meter', $sekolah->radius_meter ?? config('sekolah.radius_m', 100)) }}"
                        placeholder="100">
                    @error('radius_meter')
                        <div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                    @enderror
                </div>
                <button type="button" class="scfg-clrbtn" onclick="scfgClear()">
                    <i class="fas fa-trash-alt"></i> Hapus Pin
                </button>
            </div>
        </div>

        </form>
    </div>

    {{-- Action Bar --}}
    <div class="scfg-bar">
        <button type="submit" form="scfgForm" class="scfg-sbtn" id="scfgSubmitBtn">
            <i class="fas fa-save"></i> Simpan Konfigurasi
        </button>
    </div>

    {{-- ===== Help Modal ===== --}}
    <div class="scfg-help-overlay" id="scfgHelpOverlay" role="dialog" aria-modal="true"
        aria-labelledby="scfgHelpTitle">
        <div class="scfg-help-modal">
            <div class="scfg-help-mhead">
                <div class="scfg-help-mhead-ico"><i class="fas fa-lightbulb"></i></div>
                <h4 id="scfgHelpTitle">Panduan Pengisian</h4>
                <button type="button" class="scfg-help-close" onclick="scfgHelpClose()" aria-label="Tutup panduan">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="scfg-help-mbody" id="scfgHelpBody"></div>
            <div class="scfg-help-mfoot">
                <button type="button" class="scfg-help-okbtn" onclick="scfgHelpClose()">Mengerti</button>
            </div>
        </div>
    </div>

    </div>
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
    </script>
@endpush
