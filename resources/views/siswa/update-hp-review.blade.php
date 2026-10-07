@extends('layouts.app')

@section('title', 'Review Update Nomor HP Siswa')

@push('styles')
    @include('components.izin-styles')
    <style>
        /* ---- Layout ---- */
        .rev-wrap {
            padding-bottom: calc(var(--footer-h, 60px) + 100px);
        }

        /* ---- Summary Cards ---- */
        .rev-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 14px;
        }

        @media (min-width: 480px) {
            .rev-stats { grid-template-columns: repeat(4, 1fr); }
        }

        .rev-stat {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 14px;
            padding: 14px 10px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }

        .rev-stat .icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin: 0 auto 7px;
        }

        .rev-stat .value {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1;
        }

        .rev-stat .label {
            font-size: .63rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 3px;
        }

        /* ---- Section Card ---- */
        .rev-card {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 16px;
            padding: 20px 16px;
            margin-bottom: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }

        .rev-card h3 {
            margin: 0 0 14px;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ---- Table ---- */
        .rev-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -4px;
        }

        .rev-table {
            width: 100%;
            min-width: 640px;
            border-collapse: collapse;
            font-size: .78rem;
        }

        .rev-table th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 9px 10px;
            text-align: left;
            font-weight: 700;
            color: #374151;
            white-space: nowrap;
        }

        .rev-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #1e293b;
        }

        .rev-table tr:last-child td { border-bottom: none; }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 99px;
            font-size: .7rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-update  { background: #dbeafe; color: #1d4ed8; }
        .badge-sama    { background: #f1f5f9; color: #64748b; }
        .badge-notfound{ background: #fee2e2; color: #dc2626; }

        /* Value change arrow */
        .val-old { color: #94a3b8; text-decoration: line-through; font-size: .72rem; }
        .val-new  { color: #16a34a; font-weight: 600; }
        .val-same { color: #374151; }
        .arrow    { margin: 0 4px; color: #94a3b8; }

        /* Not-found list */
        .notfound-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .notfound-list li {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: .82rem;
        }

        .notfound-list li:last-child { border-bottom: none; }
        .notfound-list .nf-name  { font-weight: 600; color: #0f172a; }
        .notfound-list .nf-alasan{ color: #ef4444; font-size: .76rem; }

        /* Action bar */
        .action-bar {
            position: fixed;
            bottom: calc(var(--footer-h, 60px));
            left: 0; right: 0;
            background: rgba(255,255,255,.97);
            backdrop-filter: blur(6px);
            border-top: 1px solid #e2e8f0;
            padding: 12px 16px;
            display: flex;
            gap: 10px;
            z-index: 50;
        }

        .btn-back {
            flex: 1;
            padding: 12px 0;
            background: #f1f5f9;
            color: #374151;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: background .2s;
        }

        .btn-back:hover { background: #e2e8f0; }

        .btn-process {
            flex: 2;
            padding: 12px 0;
            background: #16a34a;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: background .2s;
        }

        .btn-process:hover { background: #15803d; }
        .btn-process:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        /* Filter tabs */
        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 5px 12px;
            border-radius: 99px;
            font-size: .75rem;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            cursor: pointer;
            transition: all .15s;
        }

        .filter-tab.active-all    { background: #0f172a; color: #fff; border-color: #0f172a; }
        .filter-tab.active-update { background: #1d4ed8; color: #fff; border-color: #1d4ed8; }
        .filter-tab.active-sama   { background: #64748b; color: #fff; border-color: #64748b; }
    </style>
@endpush

@section('content')
<div class="izin-wrap rev-wrap">

    {{-- Page Strip --}}
    <div class="page-strip" style="background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
        <h2><i class="fas fa-clipboard-check"></i> Review Update HP Siswa</h2>
        <p>Periksa data sebelum proses update dijalankan</p>
    </div>

    {{-- ===== Summary Stats ===== --}}
    @php
        $matchedCount = count($previewRows);
        $updateCount  = collect($previewRows)->where('status', 'update')->count();
        $samaCount    = $matchedCount - $updateCount;
        $ambigCount   = count($ambiguousRows);
    @endphp

    <div class="rev-stats">
        <div class="rev-stat">
            <div class="icon" style="background:#eff6ff;">
                <i class="fas fa-file-excel" style="color:#1d4ed8;"></i>
            </div>
            <div class="value" style="color:#1d4ed8;">{{ $totalExcel }}</div>
            <div class="label">Total di Excel</div>
        </div>
        <div class="rev-stat">
            <div class="icon" style="background:#f0fdf4;">
                <i class="fas fa-check-circle" style="color:#16a34a;"></i>
            </div>
            <div class="value" style="color:#16a34a;">{{ $matchedCount }}</div>
            <div class="label">Berhasil Dicocokkan</div>
        </div>
        <div class="rev-stat">
            <div class="icon" style="background:#fff7ed;">
                <i class="fas fa-sync-alt" style="color:#ea580c;"></i>
            </div>
            <div class="value" style="color:#ea580c;">{{ $updateCount }}</div>
            <div class="label">Akan Diupdate</div>
        </div>
        <div class="rev-stat">
            <div class="icon" style="background:#fef2f2;">
                <i class="fas fa-times-circle" style="color:#dc2626;"></i>
            </div>
            <div class="value" style="color:#dc2626;">{{ count($notFoundRows) + $ambigCount }}</div>
            <div class="label">Tidak Diproses</div>
        </div>
    </div>

    {{-- ===== Tidak Ditemukan ===== --}}
    @if (count($notFoundRows) > 0)
    <div class="rev-card">
        <h3><i class="fas fa-exclamation-triangle" style="color:#dc2626;"></i> Data Tidak Ditemukan ({{ count($notFoundRows) }})</h3>
        <ul class="notfound-list">
            @foreach ($notFoundRows as $nf)
            <li>
                <div class="nf-name">{{ $nf['nama'] }}
                    @if($nf['kelas']) <span style="font-size:.72rem; color:#64748b;">({{ $nf['kelas'] }})</span> @endif
                </div>
                <div class="nf-alasan"><i class="fas fa-times-circle"></i> {{ $nf['alasan'] }}</div>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ===== Ambigu (perlu review manual) ===== --}}
    @if ($ambigCount > 0)
    <div class="rev-card">
        <h3><i class="fas fa-question-circle" style="color:#d97706;"></i> Nama Ambigu — Perlu Review Manual ({{ $ambigCount }})</h3>
        <p style="font-size:.82rem; color:#64748b; margin:0 0 12px;">
            Data ini <strong>tidak akan diproses otomatis</strong> karena sistem menemukan lebih dari satu kandidat
            yang cocok dan tidak dapat menentukan mana yang benar. Perbarui secara manual atau perbaiki nama di file Excel.
        </p>
        <ul class="notfound-list">
            @foreach ($ambiguousRows as $amb)
            <li>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px; flex-wrap:wrap;">
                    <div>
                        <div class="nf-name">
                            <i class="fas fa-question" style="color:#d97706; font-size:.75rem;"></i>
                            {{ $amb['nama'] }}
                            @if($amb['kelas']) <span style="font-size:.72rem; color:#64748b;">({{ $amb['kelas'] }})</span> @endif
                        </div>
                        <div class="nf-alasan" style="color:#d97706;">
                            <i class="fas fa-exclamation-circle"></i> {{ $amb['alasan'] }}
                        </div>
                    </div>
                </div>
                {{-- Kandidat --}}
                <div style="margin-top:8px; display:flex; flex-wrap:wrap; gap:6px;">
                    @foreach ($amb['candidates'] as $cand)
                    <div style="background:#fef3c7; border:1px solid #fde68a; border-radius:8px; padding:6px 10px; font-size:.75rem;">
                        <div style="font-weight:700; color:#92400e;">{{ $cand['nama'] }}</div>
                        <div style="color:#78350f;">{{ $cand['kelas'] }}</div>
                        <div style="color:#b45309; font-size:.7rem;">sim: {{ $cand['similarity'] }}%</div>
                    </div>
                    @endforeach
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ===== Preview Tabel ===== --}}
    <div class="rev-card">
        <h3><i class="fas fa-table" style="color:#7c3aed;"></i> Preview Data ({{ $matchedCount }} siswa)</h3>

        {{-- Filter Tabs --}}
        <div class="filter-tabs">
            <button class="filter-tab active-all" onclick="filterTable('all', this)">
                Semua ({{ $matchedCount }})
            </button>
            <button class="filter-tab" onclick="filterTable('update', this)">
                <i class="fas fa-pencil-alt"></i> Akan Diupdate ({{ $updateCount }})
            </button>
            <button class="filter-tab" onclick="filterTable('sama', this)">
                Sama / Tidak Berubah ({{ $samaCount }})
            </button>
        </div>

        <div class="rev-table-wrap">
            <table class="rev-table" id="previewTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama (DB)</th>
                        <th>Nama (Excel)</th>
                        <th>Kelas Lama</th>
                        <th>Kelas Baru</th>
                        <th>HP Siswa</th>
                        <th>HP Ortu</th>
                        <th>Cocok Via</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($previewRows as $i => $row)
                    @php
                        $isUpdate = $row['status'] === 'update';
                        $hpSiswaChanged = $row['hp_siswa_lama'] !== $row['hp_siswa_baru'];
                        $hpOrtuChanged  = $row['hp_ortu_lama']  !== $row['hp_ortu_baru'];
                        $kelasChanged   = $row['kelas_lama']    !== $row['kelas_baru'];
                    @endphp
                    <tr data-status="{{ $row['status'] }}">
                        <td style="color:#94a3b8; font-size:.72rem;">{{ $i + 1 }}</td>

                        <td style="font-weight:600; max-width:160px;">
                            {{ $row['nama_db'] }}
                        </td>

                        <td style="color:#64748b; max-width:160px; font-size:.75rem;">
                            @if (strtolower(trim($row['nama_db'])) !== strtolower(trim($row['nama_excel'])))
                                <span style="color:#f59e0b;" title="Nama berbeda (fuzzy matched)">
                                    <i class="fas fa-exclamation-circle"></i>
                                </span>
                                {{ $row['nama_excel'] }}
                            @else
                                <span style="color:#94a3b8;"><i class="fas fa-equals"></i></span>
                            @endif
                        </td>

                        <td>
                            @if ($kelasChanged)
                                <span class="val-old">{{ $row['kelas_lama'] ?: '-' }}</span>
                            @else
                                <span class="val-same">{{ $row['kelas_lama'] ?: '-' }}</span>
                            @endif
                        </td>

                        <td>
                            @if ($kelasChanged)
                                <span class="val-new">{{ $row['kelas_baru'] ?: '-' }}</span>
                            @else
                                <span class="val-same">{{ $row['kelas_baru'] ?: '-' }}</span>
                            @endif
                        </td>

                        <td style="max-width:140px;">
                            @if ($hpSiswaChanged)
                                <div class="val-old">{{ $row['hp_siswa_lama'] ?: '—' }}</div>
                                <div class="val-new">{{ $row['hp_siswa_baru'] ?: '—' }}</div>
                            @else
                                <span class="val-same">{{ $row['hp_siswa_lama'] ?: '—' }}</span>
                            @endif
                        </td>

                        <td style="max-width:140px;">
                            @if ($hpOrtuChanged)
                                <div class="val-old">{{ $row['hp_ortu_lama'] ?: '—' }}</div>
                                <div class="val-new">{{ $row['hp_ortu_baru'] ?: '—' }}</div>
                            @else
                                <span class="val-same">{{ $row['hp_ortu_lama'] ?: '—' }}</span>
                            @endif
                        </td>

                        <td>
                            @php $mt = $row['match_type'] ?? 'exact'; @endphp
                            @if ($mt === 'exact')
                                <span style="font-size:.7rem; color:#15803d;"><i class="fas fa-equals"></i> Sama persis</span>
                            @elseif ($mt === 'normalized')
                                <span style="font-size:.7rem; color:#0891b2;"><i class="fas fa-spell-check"></i> Normalisasi</span>
                            @elseif ($mt === 'fuzzy')
                                <span style="font-size:.7rem; color:#d97706;"><i class="fas fa-tilde"></i> Fuzzy</span>
                            @elseif ($mt === 'fuzzy+class')
                                <span style="font-size:.7rem; color:#7c3aed;"><i class="fas fa-layer-group"></i> Fuzzy+Kelas</span>
                            @endif
                        </td>

                        <td>
                            @if ($isUpdate)
                                <span class="badge badge-update"><i class="fas fa-pencil-alt"></i> Update</span>
                            @else
                                <span class="badge badge-sama">Sama</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($matchedCount === 0)
        <div style="text-align:center; padding:32px; color:#94a3b8;">
            <i class="fas fa-search" style="font-size:2rem; margin-bottom:8px;"></i>
            <p>Tidak ada data yang berhasil dicocokkan.</p>
        </div>
        @endif
    </div>

</div>

{{-- ===== Action Bar ===== --}}
<div class="action-bar">
    <a href="{{ route('siswa.update-hp.form') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    @if ($updateCount > 0)
    <form action="{{ route('siswa.update-hp.process') }}" method="POST" id="processForm" style="flex:2;">
        @csrf
        <button type="button" id="btnProcess" class="btn-process" style="width:100%;" onclick="confirmProcess()">
            <i class="fas fa-save"></i> Proses Update ({{ $updateCount }} data)
        </button>
    </form>
    @else
    <button class="btn-process" disabled style="flex:2;">
        <i class="fas fa-check"></i> Tidak Ada Perubahan
    </button>
    @endif
</div>

@endsection

@push('scripts')
<script>
    // ---- Filter Tabel ----
    function filterTable(status, btn) {
        // Update active tab styling
        document.querySelectorAll('.filter-tab').forEach(t => {
            t.className = 'filter-tab';
        });
        if (status === 'all')    btn.className = 'filter-tab active-all';
        if (status === 'update') btn.className = 'filter-tab active-update';
        if (status === 'sama')   btn.className = 'filter-tab active-sama';

        // Filter rows
        document.querySelectorAll('#previewTable tbody tr').forEach(tr => {
            if (status === 'all') {
                tr.style.display = '';
            } else {
                tr.style.display = tr.dataset.status === status ? '' : 'none';
            }
        });
    }

    // ---- Konfirmasi sebelum proses ----
    function confirmProcess() {
        Swal.fire({
            title: 'Konfirmasi Update',
            html: `
                <p>Anda akan memperbarui data <strong>{{ $updateCount }} siswa</strong>.</p>
                <p style="font-size:.85rem; color:#64748b; margin-top:8px;">
                    Proses ini menggunakan database transaction.<br>
                    Jika terjadi error, seluruh perubahan akan dibatalkan.
                </p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: '<i class="fas fa-save"></i> Ya, Update Sekarang',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('btnProcess').disabled = true;
                document.getElementById('btnProcess').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                document.getElementById('processForm').submit();
            }
        });
    }
</script>
@endpush
