@extends('layouts.app')

@section('title', 'Jadwal KBM')

@push('styles')
    @include('components.event-styles')
    @include('admin.tatib._styles')
    <style>
        /* ═══════════════════════════════════════════════════════
           JADWAL KBM — RESPONSIVE STYLES
           Breakpoints:
             xs  : < 480px
             sm  : 480–767px
             md  : 768–1023px
             lg  : 1024px+
        ═══════════════════════════════════════════════════════ */

        /* ── Wrapper ──────────────────────────────────────────── */
        .jkbm-wrap {
            padding: 0 12px;
            max-width: 1280px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        @media (min-width: 768px) {
            .jkbm-wrap { padding: 0 20px; }
        }

        @media (min-width: 1024px) {
            .jkbm-wrap { padding: 0 28px; }
        }

        /* ── Strip ────────────────────────────────────────────── */
        .jkbm-strip {
            padding: 20px 20px 28px;
            background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0ea5e9 100%);
            position: relative;
            overflow: hidden;
            margin-bottom: 14px;
        }

        .jkbm-strip::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .jkbm-strip h2 {
            font-size: 1.3rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 4px;
            position: relative;
            z-index: 1;
        }

        .jkbm-strip p {
            font-size: .8rem;
            color: rgba(255, 255, 255, .65);
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .jkbm-live {
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

        .jkbm-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #7dd3fc;
            display: inline-block;
        }

        /* ── Filter collapsible ───────────────────────────────── */
        .filter-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 12px;
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
            .filter-toggle { display: none; }
        }

        .filter-body {
            padding: 12px 16px 16px;
            display: none;
        }

        .filter-body.open {
            display: block;
        }

        @media (min-width: 768px) {
            .filter-body { display: block !important; padding: 16px; }
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            align-items: end;
        }

        @media (max-width: 479px) {
            .filter-grid { grid-template-columns: 1fr; }
        }

        @media (min-width: 768px) {
            .filter-grid {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 12px;
            }
        }

        .form-label {
            display: block;
            font-size: .72rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 4px;
        }

        .form-input {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            background: #f8fafc;
            box-sizing: border-box;
            -webkit-appearance: none;
            appearance: none;
            outline: none;
        }

        .form-input:focus {
            border-color: #0ea5e9;
            background: #fff;
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
            .c-head { padding: 14px 18px; }
        }

        .c-head h3 {
            font-size: .9rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            flex: 1;
        }

        @media (min-width: 768px) {
            .c-head h3 { font-size: 1rem; }
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
        .jkbm-table-outer {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 767px) {
            .jkbm-table-outer { display: none; }
        }

        .jkbm-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .78rem;
            background: #fff;
        }

        .jkbm-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .jkbm-table th {
            padding: 9px 10px;
            text-align: left;
            font-size: .67rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .05em;
            white-space: nowrap;
        }

        .jkbm-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .jkbm-table tbody tr:last-child td {
            border-bottom: none;
        }

        .jkbm-table tbody tr:hover td {
            background: #f8fafc;
        }

        /* ── Card list mobile ─────────────────────────────────── */
        .jkbm-card-list {
            display: none;
        }

        @media (max-width: 767px) {
            .jkbm-card-list {
                display: flex;
                flex-direction: column;
                gap: 0;
            }
        }

        .jkbm-card-item {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            background: #fff;
        }

        .jkbm-card-item:last-child {
            border-bottom: none;
        }

        .jkbm-card-item:active {
            background: #f8fafc;
        }

        .jci-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 5px;
        }

        .jci-mapel {
            font-weight: 700;
            font-size: .88rem;
            color: #0f172a;
            flex: 1;
            min-width: 0;
        }

        .jci-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 6px;
        }

        .jci-chip {
            font-size: .7rem;
            color: #64748b;
            background: #f1f5f9;
            border-radius: 5px;
            padding: 2px 7px;
            font-weight: 600;
        }

        .jci-guru {
            font-size: .78rem;
            color: #475569;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .jci-actions {
            display: flex;
            gap: 6px;
            margin-top: 8px;
        }

        .jci-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            border-radius: 7px;
            font-size: .72rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
        }

        .jci-btn-edit {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .jci-btn-del {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }

        /* ── Day badge ────────────────────────────────────────── */
        .jkbm-day {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 2px 9px;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 700;
        }

        .jkbm-day-sen { background: #dbeafe; color: #1d4ed8; }
        .jkbm-day-sel { background: #ede9fe; color: #7c3aed; }
        .jkbm-day-rab { background: #dcfce7; color: #15803d; }
        .jkbm-day-kam { background: #fef3c7; color: #b45309; }
        .jkbm-day-jum { background: #fee2e2; color: #b91c1c; }
        .jkbm-day-sab { background: #f1f5f9; color: #475569; }

        /* ── Action buttons (tabel) ───────────────────────────── */
        .jkbm-act {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .jkbm-act-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 9px;
            border-radius: 7px;
            font-size: .72rem;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
            transition: all .15s;
        }

        .jkbm-act-edit {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .jkbm-act-del {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }

        .jkbm-act-edit:hover { background: #dbeafe; }
        .jkbm-act-del:hover  { background: #fee2e2; }

        /* ── FAB ──────────────────────────────────────────────── */
        .jkbm-fab {
            position: fixed;
            bottom: calc(var(--footer-h, 60px) + 16px);
            right: 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 20px;
            border-radius: 14px;
            font-size: .88rem;
            font-weight: 700;
            background: linear-gradient(135deg, #0369a1, #0ea5e9);
            color: #fff;
            border: none;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            box-shadow: 0 4px 16px rgba(14, 165, 233, .35);
            z-index: 100;
        }

        .jkbm-fab:hover { filter: brightness(1.08); }

        /* ── Pagination ───────────────────────────────────────── */
        .jkbm-pagination {
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

        .pg-btn:hover { background: #e2e8f0; }

        .pg-btn.active {
            background: #0369a1;
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
            .pg-num { display: none; }
            .pg-num.active { display: inline-flex; }
        }

        /* ── Empty state ──────────────────────────────────────── */
        .jkbm-empty {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }

        .jkbm-empty i {
            font-size: 2.5rem;
            opacity: .3;
            display: block;
            margin-bottom: 10px;
        }

        .jkbm-empty strong {
            display: block;
            color: #64748b;
            margin-bottom: 4px;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap jkbm-wrap"
        style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">

        {{-- ── Strip ─────────────────────────────────────────── --}}
        <div class="jkbm-strip">
            <div class="jkbm-live"><span class="jkbm-dot"></span>Manajemen Jadwal</div>
            <h2><i class="fas fa-calendar-alt"></i> Jadwal KBM</h2>
            <p>Kelola jadwal kegiatan belajar mengajar per kelas dan guru</p>
        </div>

        {{-- Notifikasi ditampilkan via SweetAlert di @push('scripts') --}}

        {{-- ── Shortcut Jadwal per Guru ───────────────────────── --}}
        <div style="margin-bottom:12px;">
            <a href="{{ route('admin.jadwal-kbm.guru') }}"
                style="display:inline-flex;align-items:center;gap:7px;padding:8px 14px;border-radius:9px;
                       background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;font-size:.8rem;
                       font-weight:700;text-decoration:none;">
                <i class="fas fa-calendar-user"></i> Lihat Jadwal per Guru (Grid)
            </a>
        </div>

        {{-- ── Filter (collapsible di mobile) ────────────────── --}}
        @php $hasFilter = request()->hasAny(['kelas_id','gtk_id','hari','mata_pelajaran_id']); @endphp
        <form method="GET" action="{{ route('admin.jadwal-kbm.index') }}" class="filter-section" id="filterForm">

            <button type="button" class="filter-toggle {{ $hasFilter ? 'open' : '' }}" id="filterToggle"
                aria-expanded="{{ $hasFilter ? 'true' : 'false' }}" aria-controls="filterBody">
                <span class="ft-left">
                    <i class="fas fa-filter"></i>
                    Filter
                    @if ($hasFilter)
                        <span style="background:#dbeafe;color:#1d4ed8;font-size:.65rem;
                                     padding:2px 8px;border-radius:20px;font-weight:700;">Aktif</span>
                    @endif
                </span>
                <i class="fas fa-chevron-down ft-chevron"></i>
            </button>

            <div class="filter-body {{ $hasFilter ? 'open' : '' }}" id="filterBody">
                <div class="filter-grid">
                    <div>
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" class="form-input">
                            <option value="">Semua Kelas</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Guru</label>
                        <select name="gtk_id" class="form-input">
                            <option value="">Semua Guru</option>
                            @foreach ($gtkList as $g)
                                <option value="{{ $g->id }}" @selected(request('gtk_id') == $g->id)>
                                    {{ $g->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Hari</label>
                        <select name="hari" class="form-input">
                            <option value="">Semua Hari</option>
                            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                                <option value="{{ $h }}" @selected(request('hari') == $h)>{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Mata Pelajaran</label>
                        <select name="mata_pelajaran_id" class="form-input">
                            <option value="">Semua Mapel</option>
                            @foreach ($mataPelajaran as $mp)
                                <option value="{{ $mp->id }}" @selected(request('mata_pelajaran_id') == $mp->id)>
                                    {{ $mp->nama_mapel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;gap:8px;align-items:flex-end;padding-top:4px;">
                        <button type="submit" class="jkbm-act-btn jkbm-act-edit"
                            style="flex:1;justify-content:center;padding:10px;">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        @if ($hasFilter)
                            <a href="{{ route('admin.jadwal-kbm.index') }}" class="jkbm-act-btn"
                                style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;padding:10px 14px;">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- ── Card / Tabel data ───────────────────────────────── --}}
        <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:0;
             background:#fff;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,.04);">

            <div class="c-head">
                <div class="c-icon" style="background:#dbeafe;color:#0369a1;flex-shrink:0;
                     width:34px;height:34px;border-radius:10px;display:flex;
                     align-items:center;justify-content:center;font-size:.9rem;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3>Daftar Jadwal KBM</h3>
                @if (!$jadwalKBM->isEmpty())
                    <span class="hbadge">{{ $jadwalKBM->total() }} jadwal</span>
                @endif
            </div>

            @if ($jadwalKBM->isEmpty())
                <div class="jkbm-empty">
                    <i class="fas fa-calendar-times"></i>
                    <strong>Belum ada jadwal</strong>
                    Belum ada data jadwal KBM yang sesuai filter.
                </div>
            @else

                {{-- ══ TABEL (tablet / desktop ≥ 768px) ══ --}}
                <div class="jkbm-table-outer">
                    <table class="jkbm-table">
                        <thead>
                            <tr>
                                <th style="width:36px;">#</th>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Kelas</th>
                                <th>Guru</th>
                                <th>Mata Pelajaran</th>
                                <th>TA / Smt</th>
                                <th style="width:100px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jadwalKBM as $i => $j)
                                @php
                                    $hariSlug = match ($j->hari) {
                                        'Senin'  => 'sen',
                                        'Selasa' => 'sel',
                                        'Rabu'   => 'rab',
                                        'Kamis'  => 'kam',
                                        'Jumat'  => 'jum',
                                        default  => 'sab',
                                    };
                                @endphp
                                <tr>
                                    <td style="color:#94a3b8;text-align:center;font-size:.72rem;">
                                        {{ $jadwalKBM->firstItem() + $i }}
                                    </td>
                                    <td>
                                        <span class="jkbm-day jkbm-day-{{ $hariSlug }}">{{ $j->hari }}</span>
                                    </td>
                                    <td style="white-space:nowrap;font-size:.76rem;">
                                        @if ($j->jam_mulai && $j->jam_selesai)
                                            {{ $j->jam_mulai->format('H:i') }} – {{ $j->jam_selesai->format('H:i') }}
                                        @elseif ($j->jam_ke)
                                            Jam {{ $j->jam_ke }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td style="font-weight:600;">
                                        {{ $j->kelas?->nama_kelas ?? '—' }}
                                        @if ($j->kelas?->jurusan?->nama_jurusan)
                                            <div style="font-size:.7rem;color:#94a3b8;">
                                                {{ $j->kelas->jurusan->nama_jurusan }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $j->gtk?->nama_lengkap ?? '—' }}
                                        @if ($j->gtk?->kd_guru)
                                            <div style="font-size:.7rem;color:#94a3b8;">{{ $j->gtk->kd_guru }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $j->mataPelajaran?->nama_mapel ?? ($j->mata_pelajaran ?? '—') }}</td>
                                    <td style="font-size:.75rem;color:#64748b;white-space:nowrap;">
                                        {{ $j->tahun_ajaran ?? '—' }} / Smt {{ $j->semester ?? '—' }}
                                    </td>
                                    <td>
                                        <div class="jkbm-act">
                                            <a href="{{ route('admin.jadwal-kbm.edit', $j) }}"
                                                class="jkbm-act-btn jkbm-act-edit">
                                                <i class="fas fa-pen"></i> Edit
                                            </a>
                                            <form method="POST"
                                                action="{{ route('admin.jadwal-kbm.destroy', $j) }}"
                                                class="form-hapus-jadwal">
                                                @csrf @method('DELETE')
                                                <button type="button"
                                                    class="jkbm-act-btn jkbm-act-del btn-hapus-jadwal"
                                                    data-nama="{{ $j->mataPelajaran?->nama_mapel ?? ($j->mata_pelajaran ?? 'Jadwal ini') }}"
                                                    data-kelas="{{ $j->kelas?->nama_kelas ?? '' }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ══ CARD LIST (mobile < 768px) ══ --}}
                <div class="jkbm-card-list">
                    @foreach ($jadwalKBM as $j)
                        @php
                            $hariSlug = match ($j->hari) {
                                'Senin'  => 'sen',
                                'Selasa' => 'sel',
                                'Rabu'   => 'rab',
                                'Kamis'  => 'kam',
                                'Jumat'  => 'jum',
                                default  => 'sab',
                            };
                        @endphp
                        <div class="jkbm-card-item">
                            <div class="jci-top">
                                <span class="jci-mapel">
                                    {{ $j->mataPelajaran?->nama_mapel ?? ($j->mata_pelajaran ?? '—') }}
                                </span>
                                <span class="jkbm-day jkbm-day-{{ $hariSlug }}">{{ $j->hari }}</span>
                            </div>
                            <div class="jci-meta">
                                @if ($j->kelas)
                                    <span class="jci-chip">
                                        <i class="fas fa-school" style="font-size:.65rem;"></i>
                                        {{ $j->kelas->nama_kelas }}
                                    </span>
                                @endif
                                @if ($j->kelas?->jurusan)
                                    <span class="jci-chip">{{ $j->kelas->jurusan->nama_jurusan }}</span>
                                @endif
                                <span class="jci-chip">
                                    @if ($j->jam_mulai && $j->jam_selesai)
                                        <i class="fas fa-clock" style="font-size:.65rem;"></i>
                                        {{ $j->jam_mulai->format('H:i') }}–{{ $j->jam_selesai->format('H:i') }}
                                    @elseif ($j->jam_ke)
                                        Jam {{ $j->jam_ke }}
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                            <div class="jci-guru">
                                <i class="fas fa-user-tie" style="font-size:.72rem;color:#94a3b8;"></i>
                                {{ $j->gtk?->nama_lengkap ?? '—' }}
                                @if ($j->gtk?->kd_guru)
                                    <span style="color:#94a3b8;font-size:.72rem;">({{ $j->gtk->kd_guru }})</span>
                                @endif
                            </div>
                            <div style="font-size:.72rem;color:#94a3b8;margin-bottom:2px;">
                                TA {{ $j->tahun_ajaran ?? '—' }} / Smt {{ $j->semester ?? '—' }}
                            </div>
                            <div class="jci-actions">
                                <a href="{{ route('admin.jadwal-kbm.edit', $j) }}" class="jci-btn jci-btn-edit">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('admin.jadwal-kbm.destroy', $j) }}"
                                    class="form-hapus-jadwal" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="button"
                                        class="jci-btn jci-btn-del btn-hapus-jadwal"
                                        data-nama="{{ $j->mataPelajaran?->nama_mapel ?? ($j->mata_pelajaran ?? 'Jadwal ini') }}"
                                        data-kelas="{{ $j->kelas?->nama_kelas ?? '' }}">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ── Pagination ───────────────────────────────── --}}
                @if ($jadwalKBM->hasPages())
                    <div class="jkbm-pagination">
                        {{-- Prev --}}
                        @if ($jadwalKBM->onFirstPage())
                            <span class="pg-btn disabled"><i class="fas fa-angle-left"></i></span>
                        @else
                            <a href="{{ $jadwalKBM->previousPageUrl() }}" class="pg-btn">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        @endif

                        @php
                            $pgStart = max(1, $jadwalKBM->currentPage() - 2);
                            $pgEnd   = min($jadwalKBM->lastPage(), $jadwalKBM->currentPage() + 2);
                        @endphp

                        @if ($pgStart > 1)
                            <a href="{{ $jadwalKBM->url(1) }}" class="pg-btn pg-num">1</a>
                            @if ($pgStart > 2)
                                <span class="pg-btn pg-num" style="pointer-events:none;">…</span>
                            @endif
                        @endif

                        @for ($pg = $pgStart; $pg <= $pgEnd; $pg++)
                            @if ($jadwalKBM->currentPage() === $pg)
                                <span class="pg-btn pg-num active">{{ $pg }}</span>
                            @else
                                <a href="{{ $jadwalKBM->url($pg) }}" class="pg-btn pg-num">{{ $pg }}</a>
                            @endif
                        @endfor

                        @if ($pgEnd < $jadwalKBM->lastPage())
                            @if ($pgEnd < $jadwalKBM->lastPage() - 1)
                                <span class="pg-btn pg-num" style="pointer-events:none;">…</span>
                            @endif
                            <a href="{{ $jadwalKBM->url($jadwalKBM->lastPage()) }}" class="pg-btn pg-num">
                                {{ $jadwalKBM->lastPage() }}
                            </a>
                        @endif

                        {{-- Next --}}
                        @if ($jadwalKBM->hasMorePages())
                            <a href="{{ $jadwalKBM->nextPageUrl() }}" class="pg-btn">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        @else
                            <span class="pg-btn disabled"><i class="fas fa-angle-right"></i></span>
                        @endif
                    </div>
                    <div style="text-align:center;font-size:.72rem;color:#94a3b8;padding:4px 0 10px;">
                        Halaman {{ $jadwalKBM->currentPage() }} / {{ $jadwalKBM->lastPage() }}
                        &nbsp;·&nbsp; {{ $jadwalKBM->total() }} data
                    </div>
                @else
                    <div style="padding:8px 14px;font-size:.72rem;color:#94a3b8;border-top:1px solid #f1f5f9;">
                        Menampilkan {{ $jadwalKBM->count() }} dari {{ $jadwalKBM->total() }} data
                    </div>
                @endif

            @endif
        </div>{{-- end card --}}

    </div>

    {{-- ── FAB Tambah ────────────────────────────────────────── --}}
    <a href="{{ route('admin.jadwal-kbm.create') }}" class="jkbm-fab">
        <i class="fas fa-plus"></i> Tambah Jadwal
    </a>

    {{-- ── Tombol Import ─────────────────────────────────────── --}}
    <a href="{{ route('admin.jadwal-kbm.import') }}"
        style="position:fixed;bottom:calc(var(--footer-h,60px) + 70px);right:16px;
               display:inline-flex;align-items:center;gap:7px;padding:10px 16px;
               border-radius:12px;font-size:.82rem;font-weight:700;
               background:#ecfdf5;color:#15803d;border:1px solid #bbf7d0;
               text-decoration:none;box-shadow:0 2px 10px rgba(21,128,61,.15);z-index:100;">
        <i class="fas fa-file-import"></i> Import
    </a>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ── Header ─────────────────────────────────────────
            const header = document.querySelector('.header-auto-show');
            if (header) header.classList.add('header-active');

            // ── Filter toggle (mobile) ─────────────────────────
            const filterToggle = document.getElementById('filterToggle');
            const filterBody   = document.getElementById('filterBody');
            if (filterToggle && filterBody) {
                filterToggle.addEventListener('click', function () {
                    const isOpen = filterBody.classList.toggle('open');
                    filterToggle.classList.toggle('open', isOpen);
                    filterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            // ── SweetAlert: konfirmasi hapus ───────────────────
            document.querySelectorAll('.btn-hapus-jadwal').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const nama  = btn.dataset.nama  || 'jadwal ini';
                    const kelas = btn.dataset.kelas ? ' (' + btn.dataset.kelas + ')' : '';
                    const form  = btn.closest('.form-hapus-jadwal');
                    Swal.fire({
                        title: 'Hapus Jadwal?',
                        html: 'Jadwal <strong>' + nama + kelas + '</strong> akan dihapus secara permanen.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#be123c',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
                        cancelButtonText: 'Batal',
                    }).then(function (result) {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });

            // ── Notifikasi session ─────────────────────────────
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json(session('success')),
                    confirmButtonColor: '#0369a1',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: @json($errors->first()),
                    confirmButtonColor: '#0369a1',
                });
            @endif

            @if (session('import_errors') && count(session('import_errors')))
                @php
                    $importErrList = array_slice(session('import_errors'), 0, 10);
                    $importErrMore = count(session('import_errors')) - 10;
                @endphp
                Swal.fire({
                    icon: 'warning',
                    title: 'Import Selesai dengan Catatan',
                    html: @json(
                        '<div style="text-align:left;font-size:.82rem;">'
                        . '<strong>' . count(session('import_errors')) . ' baris bermasalah:</strong>'
                        . '<ul style="margin:6px 0 0 16px;">'
                        . implode('', array_map(fn($e) => '<li>' . e($e) . '</li>', $importErrList))
                        . ($importErrMore > 0 ? '<li>... dan ' . $importErrMore . ' lainnya</li>' : '')
                        . '</ul></div>'
                    ),
                    confirmButtonColor: '#0369a1',
                    confirmButtonText: 'Tutup',
                });
            @endif
        });
    </script>
@endpush
