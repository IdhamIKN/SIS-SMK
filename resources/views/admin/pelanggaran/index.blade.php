@extends('layouts.app')

@section('title', 'Pelanggaran Siswa')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ══════════════════════════════════════════════════════════
           PELANGGARAN — Responsive (mengikuti pola /absen/rekap)
           Breakpoints: xs <480 | sm 480-767 | md 768+ | lg 1024+
           ══════════════════════════════════════════════════════════ */

        /* ── Wrapper ─────────────────────────────────────────────── */
        .pvl-wrap {
            padding: 0 12px;
            max-width: 1280px;
            margin: 0 auto;
            box-sizing: border-box;
        }
        @media (min-width: 768px)  { .pvl-wrap { padding: 0 20px; } }
        @media (min-width: 1024px) { .pvl-wrap { padding: 0 28px; } }

        /* ── Stat grid ───────────────────────────────────────────── */
        .pvl-stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }
        @media (min-width: 480px) { .pvl-stat-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (min-width: 768px) { .pvl-stat-grid { grid-template-columns: repeat(4, 1fr); gap:12px; margin-bottom:20px; } }

        .pvl-stat-card {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 10px;
            padding: 10px 8px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        @media (min-width: 768px) { .pvl-stat-card { border-radius:12px; padding:16px; } }

        .pvl-stat-val {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }
        @media (min-width: 768px) { .pvl-stat-val { font-size:1.8rem; } }

        .pvl-stat-lbl {
            font-size: .6rem;
            color: var(--text-muted, #64748b);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        @media (min-width: 768px) { .pvl-stat-lbl { font-size:.7rem; } }

        /* ── Filter section ──────────────────────────────────────── */
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
        .filter-toggle .ft-left { display:flex; align-items:center; gap:8px; }
        .filter-toggle .ft-chevron { transition:transform .2s; color:#64748b; font-size:.8rem; }
        .filter-toggle.open .ft-chevron { transform:rotate(180deg); }
        @media (min-width: 768px) { .filter-toggle { display:none; } }

        .filter-body { padding:12px 16px 16px; display:none; }
        .filter-body.open { display:block; }
        @media (min-width: 768px) { .filter-body { display:block !important; padding:16px; } }

        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            align-items: end;
        }
        @media (max-width: 479px) { .filter-grid { grid-template-columns:1fr; } }
        @media (min-width: 768px) { .filter-grid { grid-template-columns:repeat(auto-fit, minmax(160px,1fr)); gap:12px; } }

        .form-label { display:block; font-size:.8rem; font-weight:600; color:#0f172a; margin-bottom:5px; }
        .form-input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: .875rem;
            font-family: inherit;
            background: #fff;
            color: #0f172a;
            outline: none;
            box-sizing: border-box;
            -webkit-appearance: none;
        }
        .form-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }

        /* ── Table (desktop) ─────────────────────────────────────── */
        .pvl-table-wrap {
            display: none;
            overflow-x: auto;
        }
        @media (min-width: 768px) { .pvl-table-wrap { display:block; } }

        .pvl-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .82rem;
        }
        .pvl-table th {
            background: #f8fafc;
            padding: 10px 12px;
            text-align: left;
            font-size: .72rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .pvl-table td {
            padding: 11px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            color: #0f172a;
        }
        .pvl-table tr:last-child td { border-bottom: none; }
        .pvl-table tr:hover td { background: #fafafa; }

        /* ── Card list (mobile) ──────────────────────────────────── */
        .pvl-card-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 12px;
        }
        @media (min-width: 768px) { .pvl-card-list { display:none; } }

        .pvl-card-item {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #dc2626;
            border-radius: 10px;
            padding: 12px 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .pvl-rci-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 4px;
        }
        .pvl-rci-nama { font-size:.88rem; font-weight:800; color:#0f172a; }
        .pvl-rci-tgl { font-size:.72rem; color:#64748b; white-space:nowrap; flex-shrink:0; }
        .pvl-rci-meta { font-size:.73rem; color:#64748b; margin-bottom:6px; display:flex; flex-wrap:wrap; gap:4px 10px; }
        .pvl-rci-isi { font-size:.82rem; color:#374151; margin-bottom:8px; line-height:1.5; }
        .pvl-rci-chips { display:flex; flex-wrap:wrap; gap:5px; margin-bottom:8px; }

        /* ── Badge/chip ──────────────────────────────────────────── */
        .pvl-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .71rem;
            font-weight: 700;
        }
        .pvl-badge-poin { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
        .pvl-badge-pasal { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
        .pvl-badge-auto { background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; font-size:.65rem; }

        /* ── Action buttons ──────────────────────────────────────── */
        .pvl-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 5px 10px;
            border-radius: 7px;
            font-size: .75rem;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: filter .15s;
        }
        .pvl-btn:hover { filter:brightness(.92); }
        .pvl-btn-edit { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
        .pvl-btn-del  { background:#fee2e2; color:#dc2626; border:1px solid #fecaca; }

        /* ── Pagination ──────────────────────────────────────────── */
        .pvl-pagination {
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
            min-width: 34px;
            height: 34px;
            padding: 0 8px;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 600;
            border: 1.5px solid #e2e8f0;
            color: #64748b;
            background: #fff;
            text-decoration: none;
            transition: background .15s, color .15s;
            cursor: pointer;
            box-sizing: border-box;
        }
        .pg-btn.active { background:#dc2626; border-color:#dc2626; color:#fff; font-weight:700; }
        .pg-btn:hover:not(.active):not(.disabled) { background:#fee2e2; border-color:#fecaca; color:#dc2626; }
        .pg-btn.disabled { color:#cbd5e1; background:#f8fafc; cursor:not-allowed; }
        .pg-btn.pg-num { min-width:34px; }
        @media (max-width:479px) { .pg-btn.pg-num:not(.active) { display:none; } }

        /* ── Empty state ─────────────────────────────────────────── */
        .pvl-empty {
            padding: 40px 20px;
            text-align: center;
            color: #94a3b8;
        }
        .pvl-empty i { font-size:2rem; display:block; margin-bottom:10px; }
        .pvl-empty strong { display:block; font-size:.9rem; color:#64748b; }
    </style>
@endpush

@section('content')
<div class="event-wrap pvl-wrap" style="padding-top:var(--header-h,56px); padding-bottom:calc(var(--footer-h,0px) + 24px);">

    {{-- ── Page Strip ──────────────────────────────────────────────── --}}
    <div class="page-strip page-strip-event" style="--event-primary:#dc2626;">
        <div class="live-badge"><span class="live-dot" style="background:#dc2626;"></span>Tatib</div>
        <h2><i class="fas fa-exclamation-triangle"></i> Data Pelanggaran Siswa</h2>
        <p>Tahun Ajaran {{ $tahunAjaran }}</p>
    </div>

    {{-- ── Stats ───────────────────────────────────────────────────── --}}
    <div class="pvl-stat-grid" style="margin-top:14px;">
        <div class="pvl-stat-card">
            <div class="pvl-stat-val" style="color:#dc2626;">{{ $stats['total'] }}</div>
            <div class="pvl-stat-lbl">Total Data</div>
        </div>
        <div class="pvl-stat-card">
            <div class="pvl-stat-val" style="color:#c2410c;">{{ $stats['total_poin'] }}</div>
            <div class="pvl-stat-lbl">Total Poin</div>
        </div>
        <div class="pvl-stat-card">
            <div class="pvl-stat-val" style="color:#64748b;">{{ $pelanggaran->currentPage() }}</div>
            <div class="pvl-stat-lbl">Halaman</div>
        </div>
        <div class="pvl-stat-card">
            <div class="pvl-stat-val" style="color:#64748b;">{{ $pelanggaran->lastPage() }}</div>
            <div class="pvl-stat-lbl">Total Hal.</div>
        </div>
    </div>

    @if (session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;color:#15803d;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- ── Filter ───────────────────────────────────────────────────── --}}
    @php $hasFilter = request()->hasAny(['search','kelas_id','dari_tanggal','sampai_tanggal']); @endphp
    <form method="GET" action="{{ route('admin.pelanggaran.index') }}" class="filter-section" id="pvlFilter">
        <button type="button" class="filter-toggle {{ $hasFilter ? 'open' : '' }}" id="pvlToggle" onclick="toggleFilter()">
            <span class="ft-left">
                <i class="fas fa-filter"></i> Filter
                @if($hasFilter)
                    <span style="background:#fef3c7;color:#b45309;font-size:.65rem;padding:2px 8px;border-radius:20px;font-weight:700;">Aktif</span>
                @endif
            </span>
            <i class="fas fa-chevron-down ft-chevron"></i>
        </button>
        <div class="filter-body {{ $hasFilter ? 'open' : '' }}" id="pvlFilterBody">
            <div class="filter-grid">
                <div>
                    <label class="form-label">Cari</label>
                    <div style="position:relative;">
                        <input type="text" name="search" class="form-input" placeholder="Nama, NIS, uraian…"
                            value="{{ request('search') }}" style="padding-left:32px;">
                        <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.8rem;pointer-events:none;"></i>
                    </div>
                </div>
                <div>
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-input">
                        <option value="">Semua Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" @selected((string)request('kelas_id')===(string)$k->id)>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="dari_tanggal" class="form-input" value="{{ request('dari_tanggal') }}">
                </div>
                <div>
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="sampai_tanggal" class="form-input" value="{{ request('sampai_tanggal') }}">
                </div>
                <div>
                    <label class="form-label">Tahun Ajaran</label>
                    <input type="text" name="tahun_ajaran" class="form-input" value="{{ $tahunAjaran }}" maxlength="9">
                </div>
                <div style="display:flex;gap:8px;align-items:flex-end;padding-top:4px;">
                    <button type="submit" class="action-btn btn-view" style="flex:1;justify-content:center;padding:10px;">
                        <i class="fas fa-filter"></i> Terapkan
                    </button>
                    @if($hasFilter)
                        <a href="{{ route('admin.pelanggaran.index') }}" class="action-btn"
                            style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:10px 14px;text-decoration:none;">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    {{-- ── Card: Judul + Tabel/List + Pagination ───────────────────── --}}
    <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:0;">

        {{-- Card header --}}
        <div class="c-head">
            <div class="c-icon" style="background:#fee2e2;color:#dc2626;flex-shrink:0;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Daftar Pelanggaran Siswa</h3>
            <span class="hbadge">{{ $pelanggaran->total() }} data</span>
            <a href="{{ route('admin.pelanggaran.create', ['tahun_ajaran' => $tahunAjaran]) }}"
                class="action-btn btn-view" style="margin-left:auto;font-size:.75rem;padding:6px 12px;text-decoration:none;">
                <i class="fas fa-plus"></i> Tambah
            </a>
        </div>

        {{-- ── Bulk Action Toolbar ──────────────────────────────────── --}}
        <div id="pvlBulkToolbar" style="display:none; background:#fff7ed; border-bottom:1px solid #fed7aa; padding:10px 16px; align-items:center; gap:10px; flex-wrap:wrap;">
            <span style="font-size:.82rem; font-weight:700; color:#c2410c;">
                <i class="fas fa-check-square"></i> <span id="pvlSelCount">0</span> data dipilih
            </span>
            <form id="pvlBulkDeleteForm" method="POST" action="{{ route('admin.pelanggaran.bulk-delete') }}" style="margin:0;">
                @csrf
                <div id="pvlBulkDeleteInputs"></div>
                <button type="button" class="pvl-btn pvl-btn-del" style="padding:6px 14px;" onclick="pvlConfirmBulkDelete()">
                    <i class="fas fa-trash"></i> Hapus Terpilih
                </button>
            </form>
            <button type="button" onclick="pvlClearAll()" class="pvl-btn" style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;padding:6px 12px;">
                <i class="fas fa-times"></i> Batal Pilih
            </button>
        </div>

        @if($pelanggaran->isEmpty())
            <div class="pvl-empty">
                <i class="fas fa-inbox"></i>
                <strong>Tidak ada data pelanggaran</strong>
                <span style="font-size:.8rem;margin-top:4px;display:block;">Coba ubah filter pencarian.</span>
            </div>
        @else

            {{-- ══ TABEL — Desktop (≥768px) ══ --}}
            <div class="pvl-table-wrap">
                <table class="pvl-table">
                    <thead>
                        <tr>
                            <th style="width:36px;">
                                <input type="checkbox" id="pvlCheckAll" onchange="pvlToggleAll(this)"
                                    style="width:16px;height:16px;cursor:pointer;accent-color:#dc2626;">
                            </th>
                            <th style="width:28px;">#</th>
                            <th>Siswa</th>
                            <th>Tanggal</th>
                            <th>Uraian</th>
                            <th>Pasal</th>
                            <th style="text-align:center;">Poin</th>
                            <th>Pelapor</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pelanggaran as $i => $item)
                        <tr id="pvl-row-{{ $item->idpel }}">
                            <td>
                                <input type="checkbox" class="pvl-check" value="{{ $item->idpel }}" onchange="pvlOnCheck()"
                                    style="width:16px;height:16px;cursor:pointer;accent-color:#dc2626;">
                            </td>
                            <td style="color:#94a3b8;font-size:.72rem;">{{ $pelanggaran->firstItem() + $i }}</td>
                            <td>
                                <div style="font-weight:700;font-size:.85rem;">{{ $item->siswa?->nama_lengkap ?? $item->nama }}</div>
                                <div style="font-size:.72rem;color:#64748b;">{{ $item->siswa?->nis ?? $item->noreg }} · {{ $item->siswa?->kelas?->nama_kelas ?? $item->kelas }}</div>
                            </td>
                            <td style="white-space:nowrap;font-size:.8rem;color:#64748b;">
                                {{ $item->tgl?->format('d/m/Y') }}<br>
                                <span style="font-size:.68rem;">{{ $item->tgl?->format('H:i') }}</span>
                            </td>
                            <td style="max-width:220px;">
                                <div style="font-size:.82rem;line-height:1.5;color:#374151;">{{ $item->isi }}</div>
                                @if(str_starts_with($item->deviceid ?? '', 'auto-'))
                                    <span class="pvl-badge pvl-badge-auto" style="margin-top:4px;">
                                        <i class="fas fa-robot"></i> otomatis
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($item->idpasal)
                                    <span class="pvl-badge pvl-badge-pasal">{{ $item->idpasal }}</span>
                                @else
                                    <span style="color:#94a3b8;font-size:.75rem;">—</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <span class="pvl-badge pvl-badge-poin">
                                    <i class="fas fa-bolt"></i> {{ $item->poin }}
                                </span>
                            </td>
                            <td style="font-size:.78rem;color:#64748b;">{{ $item->creator?->name ?? $item->pelapor }}</td>
                            <td>
                                <div style="display:flex;gap:5px;justify-content:center;">
                                    @if($item->siswa_id)
                                    <a href="{{ route('siswa.show', $item->siswa_id) }}"
                                       class="pvl-btn" style="background:#ede9fe;color:#7c3aed;border:1px solid #ddd6fe;" title="Lihat Profil Siswa">
                                        <i class="fas fa-user"></i>
                                    </a>
                                    @endif
                                    <a href="{{ route('admin.pelanggaran.edit', $item) }}" class="pvl-btn pvl-btn-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.pelanggaran.destroy', $item) }}" id="pvl-del-{{ $item->idpel }}" style="margin:0;">
                                        @csrf @method('DELETE')
                                        <button type="button" class="pvl-btn pvl-btn-del" title="Hapus"
                                            onclick="pvlConfirmDelete('pvl-del-{{ $item->idpel }}', '{{ addslashes($item->siswa?->nama_lengkap ?? $item->nama) }}')">
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

            {{-- ══ CARD LIST — Mobile (<768px) ══ --}}
            <div class="pvl-card-list">
                @foreach($pelanggaran as $item)
                <div class="pvl-card-item" id="pvl-card-{{ $item->idpel }}">
                    <div class="pvl-rci-top">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;flex:1;min-width:0;">
                            <input type="checkbox" class="pvl-check" value="{{ $item->idpel }}" onchange="pvlOnCheck()"
                                style="width:17px;height:17px;flex-shrink:0;accent-color:#dc2626;">
                            <span class="pvl-rci-nama" style="flex:1;min-width:0;">{{ $item->siswa?->nama_lengkap ?? $item->nama }}</span>
                        </label>
                        <span class="pvl-rci-tgl">{{ $item->tgl?->format('d/m/Y') }}</span>
                    </div>
                    <div class="pvl-rci-meta">
                        <span><i class="fas fa-id-card"></i> {{ $item->siswa?->nis ?? $item->noreg }}</span>
                        <span><i class="fas fa-users"></i> {{ $item->siswa?->kelas?->nama_kelas ?? $item->kelas }}</span>
                        <span><i class="fas fa-user-check"></i> {{ $item->creator?->name ?? $item->pelapor }}</span>
                    </div>
                    <div class="pvl-rci-isi">{{ $item->isi }}</div>
                    <div class="pvl-rci-chips">
                        <span class="pvl-badge pvl-badge-poin"><i class="fas fa-bolt"></i> {{ $item->poin }} poin</span>
                        @if($item->idpasal)
                            <span class="pvl-badge pvl-badge-pasal">{{ $item->idpasal }}</span>
                        @endif
                        @if(str_starts_with($item->deviceid ?? '', 'auto-'))
                            <span class="pvl-badge pvl-badge-auto"><i class="fas fa-robot"></i> otomatis</span>
                        @endif
                    </div>
                    <div style="display:flex;gap:6px;margin-top:4px;">
                        @if($item->siswa_id)
                        <a href="{{ route('siswa.show', $item->siswa_id) }}"
                           class="pvl-btn" style="background:#ede9fe;color:#7c3aed;border:1px solid #ddd6fe;">
                            <i class="fas fa-user"></i> Profil
                        </a>
                        @endif
                        <a href="{{ route('admin.pelanggaran.edit', $item) }}" class="pvl-btn pvl-btn-edit">
                            <i class="fas fa-pen"></i> Edit
                        </a>
                        <form method="POST" action="{{ route('admin.pelanggaran.destroy', $item) }}" id="pvl-del-m-{{ $item->idpel }}" style="margin:0;">
                            @csrf @method('DELETE')
                            <button type="button" class="pvl-btn pvl-btn-del"
                                onclick="pvlConfirmDelete('pvl-del-m-{{ $item->idpel }}', '{{ addslashes($item->siswa?->nama_lengkap ?? $item->nama) }}')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>

        @endif

        {{-- ── Pagination ──────────────────────────────────────────── --}}
        @if($pelanggaran->hasPages())
            <div class="pvl-pagination">
                {{-- Prev --}}
                @if($pelanggaran->onFirstPage())
                    <span class="pg-btn disabled"><i class="fas fa-angle-left"></i></span>
                @else
                    <a href="{{ $pelanggaran->previousPageUrl() }}" class="pg-btn"><i class="fas fa-angle-left"></i></a>
                @endif

                @php
                    $pgStart = max(1, $pelanggaran->currentPage() - 2);
                    $pgEnd   = min($pelanggaran->lastPage(), $pelanggaran->currentPage() + 2);
                @endphp

                @if($pgStart > 1)
                    <a href="{{ $pelanggaran->url(1) }}" class="pg-btn pg-num">1</a>
                    @if($pgStart > 2) <span class="pg-btn pg-num" style="pointer-events:none;">…</span> @endif
                @endif

                @for($pg = $pgStart; $pg <= $pgEnd; $pg++)
                    @if($pelanggaran->currentPage() === $pg)
                        <span class="pg-btn pg-num active">{{ $pg }}</span>
                    @else
                        <a href="{{ $pelanggaran->url($pg) }}" class="pg-btn pg-num">{{ $pg }}</a>
                    @endif
                @endfor

                @if($pgEnd < $pelanggaran->lastPage())
                    @if($pgEnd < $pelanggaran->lastPage() - 1) <span class="pg-btn pg-num" style="pointer-events:none;">…</span> @endif
                    <a href="{{ $pelanggaran->url($pelanggaran->lastPage()) }}" class="pg-btn pg-num">{{ $pelanggaran->lastPage() }}</a>
                @endif

                {{-- Next --}}
                @if($pelanggaran->hasMorePages())
                    <a href="{{ $pelanggaran->nextPageUrl() }}" class="pg-btn"><i class="fas fa-angle-right"></i></a>
                @else
                    <span class="pg-btn disabled"><i class="fas fa-angle-right"></i></span>
                @endif
            </div>
            <div style="text-align:center;font-size:.72rem;color:#94a3b8;padding:4px 0 10px;">
                Halaman {{ $pelanggaran->currentPage() }} / {{ $pelanggaran->lastPage() }}
                &nbsp;·&nbsp; {{ $pelanggaran->total() }} data
            </div>
        @endif

    </div>{{-- end card --}}

</div>

@push('scripts')
<script>
function toggleFilter() {
    var btn  = document.getElementById('pvlToggle');
    var body = document.getElementById('pvlFilterBody');
    btn.classList.toggle('open');
    body.classList.toggle('open');
}

// ── Hapus satuan ──────────────────────────────────────────────────────────
function pvlConfirmDelete(formId, nama) {
    Swal.fire({
        icon: 'warning',
        title: 'Hapus Pelanggaran?',
        html: 'Data pelanggaran <strong>' + nama + '</strong> akan dihapus.<br><small style="color:#64748b;">Data di-soft-delete dan poin otomatis disesuaikan.</small>',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
        focusCancel: true,
    }).then(function(result) {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}

// ── Bulk Selection ────────────────────────────────────────────────────────
function pvlGetChecked() {
    return Array.from(document.querySelectorAll('.pvl-check:checked'));
}

function pvlOnCheck() {
    var checked = pvlGetChecked();
    var total   = document.querySelectorAll('.pvl-check').length;
    var toolbar = document.getElementById('pvlBulkToolbar');
    var counter = document.getElementById('pvlSelCount');
    var allBox  = document.getElementById('pvlCheckAll');

    counter.textContent = checked.length;
    toolbar.style.display = checked.length > 0 ? 'flex' : 'none';
    if (allBox) {
        allBox.checked       = checked.length === total;
        allBox.indeterminate = checked.length > 0 && checked.length < total;
    }

    // Highlight row
    document.querySelectorAll('.pvl-check').forEach(function(cb) {
        var row  = document.getElementById('pvl-row-' + cb.value);
        var card = document.getElementById('pvl-card-' + cb.value);
        if (row)  row.style.background  = cb.checked ? '#fff7ed' : '';
        if (card) card.style.borderLeftColor = cb.checked ? '#f97316' : '#dc2626';
    });
}

function pvlToggleAll(allBox) {
    document.querySelectorAll('.pvl-check').forEach(function(cb) {
        cb.checked = allBox.checked;
    });
    pvlOnCheck();
}

function pvlClearAll() {
    document.querySelectorAll('.pvl-check').forEach(function(cb) { cb.checked = false; });
    var allBox = document.getElementById('pvlCheckAll');
    if (allBox) { allBox.checked = false; allBox.indeterminate = false; }
    pvlOnCheck();
}

function pvlConfirmBulkDelete() {
    var ids = pvlGetChecked().map(function(cb) { return cb.value; });
    if (!ids.length) return false;

    Swal.fire({
        icon: 'warning',
        title: 'Hapus ' + ids.length + ' Pelanggaran?',
        html: '<b>' + ids.length + ' data</b> pelanggaran yang dipilih akan dihapus.<br><small style="color:#64748b;">Data di-soft-delete dan poin siswa otomatis disesuaikan.</small>',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus Semua',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
        focusCancel: true,
    }).then(function(result) {
        if (result.isConfirmed) {
            var container = document.getElementById('pvlBulkDeleteInputs');
            container.innerHTML = '';
            ids.forEach(function(id) {
                var inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
                container.appendChild(inp);
            });
            document.getElementById('pvlBulkDeleteForm').submit();
        }
    });

    return false; // cegah form submit langsung
}
</script>
@endpush
@endsection
