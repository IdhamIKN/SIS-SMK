@extends('layouts.app')

@section('title', 'Penghargaan Siswa')

@push('styles')
    @include('components.event-styles')
    <style>
        .pgh-wrap {
            padding: 0 12px;
            max-width: 1280px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        @media (min-width:768px) {
            .pgh-wrap {
                padding: 0 20px;
            }
        }

        @media (min-width:1024px) {
            .pgh-wrap {
                padding: 0 28px;
            }
        }

        .pgh-stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }

        @media (min-width:480px) {
            .pgh-stat-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width:768px) {
            .pgh-stat-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 12px;
                margin-bottom: 20px;
            }
        }

        .pgh-stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 8px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        @media (min-width:768px) {
            .pgh-stat-card {
                border-radius: 12px;
                padding: 16px;
            }
        }

        .pgh-stat-val {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }

        @media (min-width:768px) {
            .pgh-stat-val {
                font-size: 1.8rem;
            }
        }

        .pgh-stat-lbl {
            font-size: .6rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        @media (min-width:768px) {
            .pgh-stat-lbl {
                font-size: .7rem;
            }
        }

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

        @media (min-width:768px) {
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

        @media (min-width:768px) {
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

        @media (max-width:479px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (min-width:768px) {
            .filter-grid {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 12px;
            }
        }

        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 5px;
        }

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

        .form-input:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, .1);
        }

        .pgh-table-wrap {
            display: none;
            overflow-x: auto;
        }

        @media (min-width:768px) {
            .pgh-table-wrap {
                display: block;
            }
        }

        .pgh-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .82rem;
        }

        .pgh-table th {
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

        .pgh-table td {
            padding: 11px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            color: #0f172a;
        }

        .pgh-table tr:last-child td {
            border-bottom: none;
        }

        .pgh-table tr:hover td {
            background: #f0fdf4;
        }

        .pgh-card-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 12px;
        }

        @media (min-width:768px) {
            .pgh-card-list {
                display: none;
            }
        }

        .pgh-card-item {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #16a34a;
            border-radius: 10px;
            padding: 12px 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
        }

        .pgh-card-item.is-pending {
            border-left-color: #d97706;
        }

        .pgh-rci-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 4px;
        }

        .pgh-rci-nama {
            font-size: .88rem;
            font-weight: 800;
            color: #0f172a;
        }

        .pgh-rci-tgl {
            font-size: .72rem;
            color: #64748b;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .pgh-rci-meta {
            font-size: .73rem;
            color: #64748b;
            margin-bottom: 6px;
            display: flex;
            flex-wrap: wrap;
            gap: 4px 10px;
        }

        .pgh-rci-isi {
            font-size: .82rem;
            color: #374151;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .pgh-rci-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 8px;
        }

        .pgh-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .71rem;
            font-weight: 700;
        }

        .pgh-badge-poin {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .pgh-badge-pasal {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #7dd3fc;
        }

        .pgh-badge-approved {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .pgh-badge-pending {
            background: #fef3c7;
            color: #d97706;
            border: 1px solid #fde68a;
        }

        .pgh-badge-auto {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
            font-size: .65rem;
        }

        .pgh-btn {
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

        .pgh-btn:hover {
            filter: brightness(.92);
        }

        .pgh-btn-approve {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .pgh-btn-revoke {
            background: #fef3c7;
            color: #d97706;
            border: 1px solid #fde68a;
        }

        .pgh-btn-edit {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .pgh-btn-del {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .pgh-btn-reject {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .pgh-pagination {
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

        .pg-btn.active {
            background: #16a34a;
            border-color: #16a34a;
            color: #fff;
            font-weight: 700;
        }

        .pg-btn:hover:not(.active):not(.disabled) {
            background: #dcfce7;
            border-color: #86efac;
            color: #15803d;
        }

        .pg-btn.disabled {
            color: #cbd5e1;
            background: #f8fafc;
            cursor: not-allowed;
        }

        .pg-btn.pg-num {
            min-width: 34px;
        }

        @media (max-width:479px) {
            .pg-btn.pg-num:not(.active) {
                display: none;
            }
        }

        .pgh-empty {
            padding: 40px 20px;
            text-align: center;
            color: #94a3b8;
        }

        .pgh-empty i {
            font-size: 2rem;
            display: block;
            margin-bottom: 10px;
        }

        .pgh-empty strong {
            display: block;
            font-size: .9rem;
            color: #64748b;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap pgh-wrap"
        style="padding-top:var(--header-h,56px); padding-bottom:calc(var(--footer-h,0px) + 24px);">

        {{-- ── Page Strip ──────────────────────────────────────────────── --}}
        <div class="page-strip page-strip-event" style="--event-primary:#16a34a;">
            <div class="live-badge"><span class="live-dot" style="background:#16a34a;"></span>Tatib</div>
            <h2><i class="fas fa-award"></i> Data Penghargaan Siswa</h2>
            <p>Tahun Ajaran {{ $tahunAjaran }}</p>
        </div>

        {{-- ── Stats ───────────────────────────────────────────────────── --}}
        <div class="pgh-stat-grid" style="margin-top:14px;">
            <div class="pgh-stat-card">
                <div class="pgh-stat-val" style="color:#16a34a;">{{ $stats['total'] }}</div>
                <div class="pgh-stat-lbl">Total Data</div>
            </div>
            <div class="pgh-stat-card">
                <div class="pgh-stat-val" style="color:#15803d;">{{ $stats['total_poin'] }}</div>
                <div class="pgh-stat-lbl">Total Poin</div>
            </div>
            <div class="pgh-stat-card" style="border-color:{{ $stats['pending'] > 0 ? '#fbbf24' : '#e2e8f0' }};">
                <div class="pgh-stat-val" style="color:{{ $stats['pending'] > 0 ? '#d97706' : '#64748b' }};">
                    {{ $stats['pending'] }}
                </div>
                <div class="pgh-stat-lbl">Menunggu ACC</div>
            </div>
            <div class="pgh-stat-card">
                <div class="pgh-stat-val" style="color:#16a34a;">{{ $stats['acc'] }}</div>
                <div class="pgh-stat-lbl">Sudah ACC</div>
            </div>
        </div>

        {{-- ── Tab Filter ACC ───────────────────────────────────────────── --}}
        @php
            $tabUrl = fn($t) => request()->fullUrlWithQuery(['tab' => $t, 'page' => 1]);
        @endphp
        <div style="display:flex;gap:6px;margin-bottom:14px;flex-wrap:wrap;">
            <a href="{{ $tabUrl('semua') }}"
               style="padding:6px 14px;border-radius:20px;font-size:.78rem;font-weight:700;text-decoration:none;border:1.5px solid;
                      {{ $tab === 'semua' ? 'background:#0f172a;color:#fff;border-color:#0f172a;' : 'background:#f8fafc;color:#475569;border-color:#cbd5e1;' }}">
                Semua
                <span style="margin-left:4px;background:{{ $tab === 'semua' ? 'rgba(255,255,255,.2)' : '#e2e8f0' }};
                             color:inherit;padding:1px 6px;border-radius:10px;font-size:.72rem;">
                    {{ $stats['pending'] + $stats['acc'] }}
                </span>
            </a>
            <a href="{{ $tabUrl('pending') }}"
               style="padding:6px 14px;border-radius:20px;font-size:.78rem;font-weight:700;text-decoration:none;border:1.5px solid;
                      {{ $tab === 'pending' ? 'background:#d97706;color:#fff;border-color:#d97706;' : 'background:#fffbeb;color:#92400e;border-color:#fcd34d;' }}">
                <i class="fas fa-clock" style="font-size:.7rem;"></i> Belum ACC
                @if($stats['pending'] > 0)
                <span style="margin-left:4px;background:{{ $tab === 'pending' ? 'rgba(255,255,255,.25)' : '#fde68a' }};
                             color:inherit;padding:1px 6px;border-radius:10px;font-size:.72rem;">
                    {{ $stats['pending'] }}
                </span>
                @endif
            </a>
            <a href="{{ $tabUrl('acc') }}"
               style="padding:6px 14px;border-radius:20px;font-size:.78rem;font-weight:700;text-decoration:none;border:1.5px solid;
                      {{ $tab === 'acc' ? 'background:#16a34a;color:#fff;border-color:#16a34a;' : 'background:#f0fdf4;color:#166534;border-color:#86efac;' }}">
                <i class="fas fa-check-circle" style="font-size:.7rem;"></i> Sudah ACC
                <span style="margin-left:4px;background:{{ $tab === 'acc' ? 'rgba(255,255,255,.2)' : '#dcfce7' }};
                             color:inherit;padding:1px 6px;border-radius:10px;font-size:.72rem;">
                    {{ $stats['acc'] }}
                </span>
            </a>
        </div>

        @foreach (['success', 'info', 'error'] as $type)
            @if (session($type))
                @php
                    $bg = ['success' => '#dcfce7', 'info' => '#e0f2fe', 'error' => '#fee2e2'][$type];
                    $cl = ['success' => '#15803d', 'info' => '#0369a1', 'error' => '#dc2626'][$type];
                    $ic = ['success' => 'check-circle', 'info' => 'info-circle', 'error' => 'exclamation-circle'][$type];
                @endphp
                <div
                    style="background:{{ $bg }};color:{{ $cl }};padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;border:1px solid;">
                    <i class="fas fa-{{ $ic }}"></i> {{ session($type) }}
                </div>
            @endif
        @endforeach

        {{-- ── Filter ───────────────────────────────────────────────────── --}}
        @php $hasFilter = request()->hasAny(['search','kelas_id','siswa_id','dari_tanggal','sampai_tanggal']); @endphp
        <form method="GET" action="{{ route('admin.penghargaan.index') }}" class="filter-section" id="pghFilter">
            <button type="button" class="filter-toggle {{ $hasFilter ? 'open' : '' }}" id="pghToggle"
                onclick="togglePghFilter()">
                <span class="ft-left">
                    <i class="fas fa-filter"></i> Filter
                    @if ($hasFilter)
                        <span
                            style="background:#dcfce7;color:#15803d;font-size:.65rem;padding:2px 8px;border-radius:20px;font-weight:700;">Aktif</span>
                    @endif
                </span>
                <i class="fas fa-chevron-down ft-chevron"></i>
            </button>
            <div class="filter-body {{ $hasFilter ? 'open' : '' }}" id="pghFilterBody">
                <div class="filter-grid">
                    <div>
                        <label class="form-label">Cari</label>
                        <div style="position:relative;">
                            <input type="text" name="search" class="form-input" placeholder="Nama, NIS, uraian…"
                                value="{{ request('search') }}" style="padding-left:32px;">
                            <i class="fas fa-search"
                                style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.8rem;pointer-events:none;"></i>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" class="form-input">
                            <option value="">Semua Kelas</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}" @selected((string) request('kelas_id') === (string) $k->id)>{{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="dari_tanggal" class="form-input" value="{{ request('dari_tanggal') }}">
                    </div>
                    <div>
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="sampai_tanggal" class="form-input"
                            value="{{ request('sampai_tanggal') }}">
                    </div>
                    <div>
                        <label class="form-label">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="form-input" value="{{ $tahunAjaran }}"
                            maxlength="9">
                    </div>
                    <div style="display:flex;gap:8px;align-items:flex-end;padding-top:4px;">
                        <button type="submit" class="action-btn btn-view"
                            style="flex:1;justify-content:center;padding:10px;">
                            <i class="fas fa-filter"></i> Terapkan
                        </button>
                        @if ($hasFilter)
                            <a href="{{ route('admin.penghargaan.index') }}" class="action-btn"
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
                <div class="c-icon" style="background:#dcfce7;color:#16a34a;flex-shrink:0;">
                    <i class="fas fa-award"></i>
                </div>
                <h3>Daftar Penghargaan Siswa</h3>
                <span class="hbadge">{{ $penghargaan->total() }} data</span>
                <a href="{{ route('admin.penghargaan.create', ['tahun_ajaran' => $tahunAjaran]) }}"
                    class="action-btn btn-view"
                    style="margin-left:auto;font-size:.75rem;padding:6px 12px;text-decoration:none;">
                    <i class="fas fa-plus"></i> Tambah
                </a>
            </div>

            {{-- ── Bulk Action Toolbar ──────────────────────────────────── --}}
            <div id="pghBulkToolbar"
                style="display:none; background:#f0fdf4; border-bottom:1px solid #86efac; padding:10px 16px; align-items:center; gap:10px; flex-wrap:wrap;">
                <span style="font-size:.82rem; font-weight:700; color:#15803d;">
                    <i class="fas fa-check-square"></i> <span id="pghSelCount">0</span> data dipilih
                </span>
                {{-- Bulk Approve --}}
                @can('penghargaan.approve')
                    <form id="pghBulkApproveForm" method="POST" action="{{ route('admin.penghargaan.bulk-approve') }}"
                        style="margin:0;">
                        @csrf
                        <div id="pghBulkApproveInputs"></div>
                        <button type="button" class="pgh-btn pgh-btn-approve" style="padding:6px 14px;"
                            onclick="pghConfirmBulkApprove()">
                            <i class="fas fa-check-double"></i> ACC Terpilih
                        </button>
                    </form>
                @endcan
                {{-- Bulk Delete --}}
                @can('penghargaan.delete')
                    <form id="pghBulkDeleteForm" method="POST" action="{{ route('admin.penghargaan.bulk-delete') }}"
                        style="margin:0;">
                        @csrf
                        <div id="pghBulkDeleteInputs"></div>
                        <button type="button" class="pgh-btn pgh-btn-del" style="padding:6px 14px;"
                            onclick="pghConfirmBulkDelete()">
                            <i class="fas fa-trash"></i> Hapus Terpilih
                        </button>
                    </form>
                @endcan
                <button type="button" onclick="pghClearAll()" class="pgh-btn"
                    style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;padding:6px 12px;">
                    <i class="fas fa-times"></i> Batal Pilih
                </button>
            </div>

            @if ($penghargaan->isEmpty())
                <div class="pgh-empty">
                    <i class="fas fa-inbox"></i>
                    <strong>Tidak ada data penghargaan</strong>
                    <span style="font-size:.8rem;margin-top:4px;display:block;">Coba ubah filter pencarian.</span>
                </div>
            @else
                {{-- ══ TABEL — Desktop (≥768px) ══ --}}
                <div class="pgh-table-wrap">
                    <table class="pgh-table">
                        <thead>
                            <tr>
                                <th style="width:36px;">
                                    <input type="checkbox" id="pghCheckAll" onchange="pghToggleAll(this)"
                                        style="width:16px;height:16px;cursor:pointer;accent-color:#16a34a;">
                                </th>
                                <th style="width:28px;">#</th>
                                <th>Siswa</th>
                                <th>Tanggal</th>
                                <th>Uraian</th>
                                <th>Pasal</th>
                                <th style="text-align:center;">Poin</th>
                                <th style="text-align:center;">Status</th>
                                <th>Pelapor</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($penghargaan as $i => $item)
                                <tr id="pgh-row-{{ $item->idpen }}">
                                    <td>
                                        <input type="checkbox" class="pgh-check" value="{{ $item->idpen }}"
                                            onchange="pghOnCheck()"
                                            style="width:16px;height:16px;cursor:pointer;accent-color:#16a34a;">
                                    </td>
                                    <td style="color:#94a3b8;font-size:.72rem;">{{ $penghargaan->firstItem() + $i }}</td>
                                    <td>
                                        <div style="font-weight:700;font-size:.85rem;">
                                            {{ $item->siswa?->nama_lengkap ?? $item->nama }}</div>
                                        <div style="font-size:.72rem;color:#64748b;">
                                            {{ $item->siswa?->nis ?? $item->noreg }} ·
                                            {{ $item->siswa?->kelas?->nama_kelas ?? $item->kelas }}</div>
                                    </td>
                                    <td style="white-space:nowrap;font-size:.8rem;color:#64748b;">
                                        {{ $item->tgl?->format('d/m/Y') }}<br>
                                        <span style="font-size:.68rem;">{{ $item->tgl?->format('H:i') }}</span>
                                    </td>
                                    <td style="max-width:200px;">
                                        <div style="font-size:.82rem;line-height:1.5;color:#374151;">{{ $item->isi }}
                                        </div>
                                        @if (str_starts_with($item->deviceid ?? '', 'auto-'))
                                            <span class="pgh-badge pgh-badge-auto" style="margin-top:4px;"><i
                                                    class="fas fa-robot"></i> otomatis</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->idpasal)
                                            <span class="pgh-badge pgh-badge-pasal">{{ $item->idpasal }}</span>
                                        @else
                                            <span style="color:#94a3b8;font-size:.75rem;">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="pgh-badge pgh-badge-poin"><i class="fas fa-star"></i>
                                            {{ $item->poin }}</span>
                                    </td>
                                    <td style="text-align:center;">
                                        @if ($item->acc === 'YA')
                                            <span class="pgh-badge pgh-badge-approved"><i class="fas fa-check"></i>
                                                ACC</span>
                                        @else
                                            <span class="pgh-badge pgh-badge-pending"><i class="fas fa-clock"></i>
                                                Pending</span>
                                        @endif
                                    </td>
                                    <td style="font-size:.78rem;color:#64748b;">
                                        {{ $item->creator?->name ?? $item->pelapor }}</td>
                                    <td>
                                        <div style="display:flex;gap:4px;justify-content:center;flex-wrap:wrap;">
                                            @if ($item->acc !== 'YA')
                                                <form method="POST"
                                                    action="{{ route('admin.penghargaan.approve', $item) }}"
                                                    style="margin:0;">
                                                    @csrf
                                                    <button type="submit" class="pgh-btn pgh-btn-approve"
                                                        title="ACC">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form method="POST"
                                                    action="{{ route('admin.penghargaan.reject', $item) }}"
                                                    id="pgh-rej-{{ $item->idpen }}" style="margin:0;">
                                                    @csrf
                                                    <button type="button" class="pgh-btn pgh-btn-reject" title="Tolak"
                                                        onclick="pghConfirmReject('pgh-rej-{{ $item->idpen }}', '{{ addslashes($item->siswa?->nama_lengkap ?? $item->nama) }}')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST"
                                                    action="{{ route('admin.penghargaan.revoke', $item) }}"
                                                    style="margin:0;">
                                                    @csrf
                                                    <button type="submit" class="pgh-btn pgh-btn-revoke"
                                                        title="Batalkan ACC">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('admin.penghargaan.edit', $item) }}"
                                                class="pgh-btn pgh-btn-edit" title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            @if($item->siswa_id)
                                            <a href="{{ route('siswa.show', $item->siswa_id) }}"
                                               class="pgh-btn" style="background:#ede9fe;color:#7c3aed;border:1px solid #ddd6fe;" title="Lihat Profil Siswa">
                                                <i class="fas fa-user"></i>
                                            </a>
                                            @endif
                                            <form method="POST" action="{{ route('admin.penghargaan.destroy', $item) }}"
                                                id="pgh-del-{{ $item->idpen }}" style="margin:0;">
                                                @csrf @method('DELETE')
                                                <button type="button" class="pgh-btn pgh-btn-del" title="Hapus"
                                                    onclick="pghConfirmDelete('pgh-del-{{ $item->idpen }}', '{{ addslashes($item->siswa?->nama_lengkap ?? $item->nama) }}')">
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
                <div class="pgh-card-list">
                    @foreach ($penghargaan as $item)
                        <div class="pgh-card-item {{ $item->acc !== 'YA' ? 'is-pending' : '' }}"
                            id="pgh-card-{{ $item->idpen }}">
                            <div class="pgh-rci-top">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;flex:1;min-width:0;">
                                    <input type="checkbox" class="pgh-check" value="{{ $item->idpen }}"
                                        onchange="pghOnCheck()"
                                        style="width:17px;height:17px;flex-shrink:0;accent-color:#16a34a;">
                                    <span class="pgh-rci-nama"
                                        style="flex:1;min-width:0;">{{ $item->siswa?->nama_lengkap ?? $item->nama }}</span>
                                </label>
                                <span class="pgh-rci-tgl">{{ $item->tgl?->format('d/m/Y') }}</span>
                            </div>
                            <div class="pgh-rci-meta">
                                <span><i class="fas fa-id-card"></i> {{ $item->siswa?->nis ?? $item->noreg }}</span>
                                <span><i class="fas fa-users"></i>
                                    {{ $item->siswa?->kelas?->nama_kelas ?? $item->kelas }}</span>
                                <span><i class="fas fa-user-check"></i>
                                    {{ $item->creator?->name ?? $item->pelapor }}</span>
                            </div>
                            <div class="pgh-rci-isi">{{ $item->isi }}</div>
                            <div class="pgh-rci-chips">
                                <span class="pgh-badge pgh-badge-poin"><i class="fas fa-star"></i> {{ $item->poin }}
                                    poin</span>
                                @if ($item->idpasal)
                                    <span class="pgh-badge pgh-badge-pasal">{{ $item->idpasal }}</span>
                                @endif
                                @if ($item->acc === 'YA')
                                    <span class="pgh-badge pgh-badge-approved"><i class="fas fa-check"></i> ACC</span>
                                @else
                                    <span class="pgh-badge pgh-badge-pending"><i class="fas fa-clock"></i> Pending</span>
                                @endif
                                @if (str_starts_with($item->deviceid ?? '', 'auto-'))
                                    <span class="pgh-badge pgh-badge-auto"><i class="fas fa-robot"></i> otomatis</span>
                                @endif
                            </div>
                            <div style="display:flex;gap:5px;flex-wrap:wrap;margin-top:4px;">
                                @if ($item->acc !== 'YA')
                                    <form method="POST" action="{{ route('admin.penghargaan.approve', $item) }}"
                                        style="margin:0;">
                                        @csrf
                                        <button type="submit" class="pgh-btn pgh-btn-approve"><i
                                                class="fas fa-check"></i> ACC</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.penghargaan.reject', $item) }}"
                                        id="pgh-rej-m-{{ $item->idpen }}" style="margin:0;">
                                        @csrf
                                        <button type="button" class="pgh-btn pgh-btn-reject"
                                            onclick="pghConfirmReject('pgh-rej-m-{{ $item->idpen }}', '{{ addslashes($item->siswa?->nama_lengkap ?? $item->nama) }}')">
                                            <i class="fas fa-times"></i> Tolak
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.penghargaan.revoke', $item) }}"
                                        style="margin:0;">
                                        @csrf
                                        <button type="submit" class="pgh-btn pgh-btn-revoke"><i class="fas fa-undo"></i>
                                            Batal ACC</button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.penghargaan.edit', $item) }}" class="pgh-btn pgh-btn-edit"><i
                                        class="fas fa-pen"></i> Edit</a>
                                @if($item->siswa_id)
                                <a href="{{ route('siswa.show', $item->siswa_id) }}"
                                   class="pgh-btn" style="background:#ede9fe;color:#7c3aed;border:1px solid #ddd6fe;">
                                    <i class="fas fa-user"></i> Profil
                                </a>
                                @endif
                                <form method="POST" action="{{ route('admin.penghargaan.destroy', $item) }}"
                                    id="pgh-del-m-{{ $item->idpen }}" style="margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="button" class="pgh-btn pgh-btn-del"
                                        onclick="pghConfirmDelete('pgh-del-m-{{ $item->idpen }}', '{{ addslashes($item->siswa?->nama_lengkap ?? $item->nama) }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

            @endif

            {{-- ── Pagination ──────────────────────────────────────────── --}}
            @if ($penghargaan->hasPages())
                <div class="pgh-pagination">
                    @if ($penghargaan->onFirstPage())
                        <span class="pg-btn disabled"><i class="fas fa-angle-left"></i></span>
                    @else
                        <a href="{{ $penghargaan->previousPageUrl() }}" class="pg-btn"><i
                                class="fas fa-angle-left"></i></a>
                    @endif

                    @php
                        $pgStart = max(1, $penghargaan->currentPage() - 2);
                        $pgEnd = min($penghargaan->lastPage(), $penghargaan->currentPage() + 2);
                    @endphp

                    @if ($pgStart > 1)
                        <a href="{{ $penghargaan->url(1) }}" class="pg-btn pg-num">1</a>
                        @if ($pgStart > 2)
                            <span class="pg-btn pg-num" style="pointer-events:none;">…</span>
                        @endif
                    @endif

                    @for ($pg = $pgStart; $pg <= $pgEnd; $pg++)
                        @if ($penghargaan->currentPage() === $pg)
                            <span class="pg-btn pg-num active">{{ $pg }}</span>
                        @else
                            <a href="{{ $penghargaan->url($pg) }}" class="pg-btn pg-num">{{ $pg }}</a>
                        @endif
                    @endfor

                    @if ($pgEnd < $penghargaan->lastPage())
                        @if ($pgEnd < $penghargaan->lastPage() - 1)
                            <span class="pg-btn pg-num" style="pointer-events:none;">…</span>
                        @endif
                        <a href="{{ $penghargaan->url($penghargaan->lastPage()) }}"
                            class="pg-btn pg-num">{{ $penghargaan->lastPage() }}</a>
                    @endif

                    @if ($penghargaan->hasMorePages())
                        <a href="{{ $penghargaan->nextPageUrl() }}" class="pg-btn"><i
                                class="fas fa-angle-right"></i></a>
                    @else
                        <span class="pg-btn disabled"><i class="fas fa-angle-right"></i></span>
                    @endif
                </div>
                <div style="text-align:center;font-size:.72rem;color:#94a3b8;padding:4px 0 10px;">
                    Halaman {{ $penghargaan->currentPage() }} / {{ $penghargaan->lastPage() }}
                    &nbsp;·&nbsp; {{ $penghargaan->total() }} data
                </div>
            @endif

        </div>{{-- end card --}}

    </div>

    @push('scripts')
        <script>
            function togglePghFilter() {
                var btn = document.getElementById('pghToggle');
                var body = document.getElementById('pghFilterBody');
                btn.classList.toggle('open');
                body.classList.toggle('open');
            }

            // ── Hapus satuan ──────────────────────────────────────────────────────────
            function pghConfirmDelete(formId, nama) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus Penghargaan?',
                    html: 'Data penghargaan <strong>' + nama +
                        '</strong> akan dihapus.<br><small style="color:#64748b;">Data di-soft-delete dan poin otomatis disesuaikan.</small>',
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

            // ── Tolak satuan ──────────────────────────────────────────────────────────
            function pghConfirmReject(formId, nama) {
                Swal.fire({
                    icon: 'question',
                    title: 'Tolak Penghargaan?',
                    html: 'Penghargaan <strong>' + nama +
                        '</strong> akan ditolak dan dihapus.<br><small style="color:#64748b;">Tindakan tidak dapat dibatalkan.</small>',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-times"></i> Ya, Tolak',
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
            function pghGetChecked() {
                return Array.from(document.querySelectorAll('.pgh-check:checked'));
            }

            function pghOnCheck() {
                var checked = pghGetChecked();
                var total = document.querySelectorAll('.pgh-check').length;
                var toolbar = document.getElementById('pghBulkToolbar');
                var counter = document.getElementById('pghSelCount');
                var allBox = document.getElementById('pghCheckAll');

                counter.textContent = checked.length;
                toolbar.style.display = checked.length > 0 ? 'flex' : 'none';
                if (allBox) {
                    allBox.checked = checked.length === total;
                    allBox.indeterminate = checked.length > 0 && checked.length < total;
                }

                // Highlight row/card
                document.querySelectorAll('.pgh-check').forEach(function(cb) {
                    var row = document.getElementById('pgh-row-' + cb.value);
                    var card = document.getElementById('pgh-card-' + cb.value);
                    if (row) row.style.background = cb.checked ? '#f0fdf4' : '';
                    if (card) card.style.borderLeftColor = cb.checked ? '#22c55e' : (card.classList.contains(
                        'is-pending') ? '#d97706' : '#16a34a');
                });
            }

            function pghToggleAll(allBox) {
                document.querySelectorAll('.pgh-check').forEach(function(cb) {
                    cb.checked = allBox.checked;
                });
                pghOnCheck();
            }

            function pghClearAll() {
                document.querySelectorAll('.pgh-check').forEach(function(cb) {
                    cb.checked = false;
                });
                var allBox = document.getElementById('pghCheckAll');
                if (allBox) {
                    allBox.checked = false;
                    allBox.indeterminate = false;
                }
                pghOnCheck();
            }

            function pghInjectIds(containerEl) {
                var ids = pghGetChecked().map(function(cb) {
                    return cb.value;
                });
                containerEl.innerHTML = '';
                ids.forEach(function(id) {
                    var inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'ids[]';
                    inp.value = id;
                    containerEl.appendChild(inp);
                });
                return ids;
            }

            function pghConfirmBulkApprove() {
                var ids = pghGetChecked().map(function(cb) {
                    return cb.value;
                });
                if (!ids.length) return;

                Swal.fire({
                    icon: 'question',
                    title: 'ACC ' + ids.length + ' Penghargaan?',
                    html: '<b>' + ids.length +
                        ' penghargaan</b> yang dipilih akan di-ACC.<br><small style="color:#64748b;">Hanya yang belum di-ACC akan diproses.</small>',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-check-double"></i> Ya, ACC Semua',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                    focusCancel: true,
                }).then(function(result) {
                    if (result.isConfirmed) {
                        pghInjectIds(document.getElementById('pghBulkApproveInputs'));
                        document.getElementById('pghBulkApproveForm').submit();
                    }
                });
            }

            function pghConfirmBulkDelete() {
                var ids = pghGetChecked().map(function(cb) {
                    return cb.value;
                });
                if (!ids.length) return;

                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus ' + ids.length + ' Penghargaan?',
                    html: '<b>' + ids.length +
                        ' data</b> penghargaan yang dipilih akan dihapus.<br><small style="color:#64748b;">Data di-soft-delete dan poin siswa otomatis disesuaikan.</small>',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus Semua',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                    focusCancel: true,
                }).then(function(result) {
                    if (result.isConfirmed) {
                        pghInjectIds(document.getElementById('pghBulkDeleteInputs'));
                        document.getElementById('pghBulkDeleteForm').submit();
                    }
                });
            }
        </script>
    @endpush
@endsection
