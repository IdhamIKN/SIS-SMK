@extends('layouts.app')
@section('title', 'Manajemen Lokasi PKL')

@push('styles')
    @include('components.event-styles')
    <style>
        .pkl-wrap { padding: 0 12px; max-width: 1280px; margin: 0 auto; }
        @media(min-width:768px){ .pkl-wrap { padding: 0 24px; } }

        .pkl-grid { display:grid; grid-template-columns:1fr; gap:12px; margin-bottom:16px; }
        @media(min-width:640px){ .pkl-grid { grid-template-columns:1fr 1fr; } }
        @media(min-width:1024px){ .pkl-grid { grid-template-columns:repeat(3,1fr); } }

        .lokasi-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; transition:box-shadow .15s; }
        .lokasi-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.08); }
        .lokasi-card-head { padding:14px 16px 10px; border-bottom:1px solid #f1f5f9; display:flex; align-items:flex-start; gap:10px; }
        .lokasi-icon { width:38px; height:38px; border-radius:10px; background:#fef3c7; color:#b45309; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:.9rem; }
        .lokasi-name { font-weight:800; font-size:.92rem; color:#0f172a; line-height:1.3; }
        .lokasi-type { font-size:.72rem; color:#64748b; margin-top:2px; }
        .lokasi-body { padding:10px 16px 12px; }
        .lokasi-info-row { display:flex; align-items:center; gap:6px; font-size:.75rem; color:#64748b; margin-bottom:4px; }
        .lokasi-info-row i { width:14px; text-align:center; color:#94a3b8; }
        .lokasi-footer { padding:10px 16px; border-top:1px solid #f1f5f9; display:flex; gap:6px; justify-content:flex-end; flex-wrap:wrap; }

        .badge-aktif { background:#dcfce7; color:#15803d; }
        .badge-nonaktif { background:#fee2e2; color:#dc2626; }
        .badge-siswa { background:#dbeafe; color:#1d4ed8; font-size:.65rem; padding:2px 7px; border-radius:20px; font-weight:700; }

        .filter-bar { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px; margin-bottom:16px; display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; }
        .filter-bar .form-label { font-size:.75rem; font-weight:600; color:#475569; margin-bottom:4px; display:block; }
        .filter-bar .form-input { padding:8px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:.82rem; background:#fff; color:#0f172a; min-width:160px; }
        .stat-row { display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
        .stat-chip { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:10px 16px; display:flex; align-items:center; gap:8px; }
        .stat-chip-val { font-size:1.4rem; font-weight:800; color:#0f172a; }
        .stat-chip-lbl { font-size:.68rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }

        .action-bar { position:fixed; bottom:var(--footer-h,0); left:0; right:0; padding:10px 12px 12px; background:rgba(255,255,255,.96); backdrop-filter:blur(10px); border-top:1px solid #e2e8f0; display:flex; gap:8px; z-index:999; }
        @media(min-width:768px){ .action-bar { padding:10px 24px 12px; justify-content:flex-end; } }
        .ab-btn { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:12px 16px; border-radius:12px; font-size:.82rem; font-weight:700; border:none; cursor:pointer; text-decoration:none; font-family:inherit; }
        @media(min-width:768px){ .ab-btn { flex:unset; min-width:130px; font-size:.875rem; } }
        .ab-btn-amber { background:#f59e0b; color:#fff; }
        .ab-btn-green { background:#16a34a; color:#fff; }
        .ab-btn-back { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }

        .empty-state { text-align:center; padding:48px 20px; color:#64748b; }
        .empty-state i { font-size:2.5rem; opacity:.25; display:block; margin-bottom:12px; }
    </style>
@endpush

@section('content')
<div class="pkl-wrap" style="padding-top:var(--header-h,56px);padding-bottom:148px;">

    {{-- Page strip --}}
    <div class="page-strip page-strip-event">
        <div class="live-badge"><span class="live-dot"></span>PKL</div>
        <h2><i class="fas fa-map-marked-alt"></i> Manajemen Lokasi PKL</h2>
        <p>Kelola tempat magang siswa, pembimbing, dan konfigurasi absensi per lokasi.</p>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->has('error'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
            <i class="fas fa-exclamation-triangle"></i> {{ $errors->first('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="stat-row">
        <div class="stat-chip">
            <div>
                <div class="stat-chip-val" style="color:#f59e0b;">{{ $lokasiList->total() }}</div>
                <div class="stat-chip-lbl">Total Lokasi</div>
            </div>
        </div>
        <div class="stat-chip">
            <div>
                <div class="stat-chip-val" style="color:#16a34a;">{{ $lokasiList->getCollection()->where('status_aktif',true)->count() }}</div>
                <div class="stat-chip-lbl">Aktif</div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="filter-bar" id="filterForm">
        <div>
            <label class="form-label">Cari Nama / Kota</label>
            <input type="text" name="search" class="form-input" placeholder="Nama lokasi..." value="{{ $search }}">
        </div>
        <div>
            <label class="form-label">Tahun Ajaran</label>
            <select name="academic_year_id" class="form-input">
                <option value="">Semua Tahun</option>
                @foreach($academicYears as $ay)
                    <option value="{{ $ay->id }}" {{ $academicYearId == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-input">
                <option value="">Semua</option>
                <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        <div style="display:flex;gap:8px;align-items:flex-end;">
            <button type="submit" class="action-btn btn-view" style="padding:9px 16px;">
                <i class="fas fa-filter"></i> Filter
            </button>
            @if($search || $academicYearId || $status)
                <a href="{{ route('admin.pkl.lokasi.index') }}" class="action-btn" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:9px 12px;text-decoration:none;">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </div>
    </form>

    {{-- Card Grid --}}
    @if($lokasiList->isEmpty())
        <div class="card" style="border-radius:12px;">
            <div class="empty-state">
                <i class="fas fa-map-marked-alt"></i>
                <strong style="display:block;color:#0f172a;margin-bottom:4px;">Belum ada lokasi PKL</strong>
                Tambahkan lokasi tempat magang siswa menggunakan tombol di bawah.
            </div>
        </div>
    @else
        <div class="pkl-grid">
            @foreach($lokasiList as $lokasi)
                @php $jmlAktif = $lokasi->penugasan->count(); @endphp
                <div class="lokasi-card">
                    <div class="lokasi-card-head">
                        <div class="lokasi-icon"><i class="fas fa-building"></i></div>
                        <div style="flex:1;min-width:0;">
                            <div class="lokasi-name">{{ $lokasi->nama_tempat }}</div>
                            <div class="lokasi-type">{{ $lokasi->jenis_usaha ?? 'Belum ditentukan' }}</div>
                        </div>
                        <span class="badge-status {{ $lokasi->status_aktif ? 'badge-aktif' : 'badge-nonaktif' }}" style="font-size:.65rem;padding:2px 8px;border-radius:20px;font-weight:700;flex-shrink:0;">
                            {{ $lokasi->status_aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="lokasi-body">
                        <div class="lokasi-info-row">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ collect([$lokasi->kecamatan, $lokasi->kabupaten])->filter()->implode(', ') ?: $lokasi->alamat }}</span>
                        </div>
                        @if($lokasi->nama_pj)
                        <div class="lokasi-info-row">
                            <i class="fas fa-user-tie"></i>
                            <span>{{ $lokasi->nama_pj }}{{ $lokasi->jabatan_pj ? ' — '.$lokasi->jabatan_pj : '' }}</span>
                        </div>
                        @endif
                        @if($lokasi->no_hp_pj)
                        <div class="lokasi-info-row">
                            <i class="fas fa-phone"></i>
                            <span>{{ $lokasi->no_hp_pj }}</span>
                        </div>
                        @endif
                        <div class="lokasi-info-row" style="margin-top:6px;">
                            <span class="badge-siswa"><i class="fas fa-users" style="font-size:.6rem;margin-right:3px;"></i>{{ $jmlAktif }} siswa aktif</span>
                            @if($lokasi->kapasitas)
                                <span style="font-size:.7rem;color:#94a3b8;">/ maks {{ $lokasi->kapasitas }}</span>
                            @endif
                            @if($lokasi->jam_masuk_pkl)
                                <span style="font-size:.68rem;color:#64748b;margin-left:4px;"><i class="fas fa-clock" style="font-size:.6rem;"></i> {{ \Carbon\Carbon::parse($lokasi->jam_masuk_pkl)->format('H:i') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="lokasi-footer">
                        @can('pkl.update')
                        <a href="{{ route('admin.pkl.lokasi.edit', $lokasi) }}" class="action-btn btn-edit" style="font-size:.72rem;padding:6px 10px;text-decoration:none;">
                            <i class="fas fa-pen"></i>
                        </a>
                        @endcan
                        <a href="{{ route('admin.pkl.lokasi.show', $lokasi) }}" class="action-btn btn-view" style="font-size:.72rem;padding:6px 12px;text-decoration:none;">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($lokasiList->hasPages())
            <div style="display:flex;justify-content:center;gap:6px;flex-wrap:wrap;margin-top:4px;">
                @if($lokasiList->onFirstPage())
                    <span class="pg-btn disabled"><i class="fas fa-angle-left"></i></span>
                @else
                    <a href="{{ $lokasiList->previousPageUrl() }}" class="pg-btn"><i class="fas fa-angle-left"></i></a>
                @endif
                <span class="pg-btn active">{{ $lokasiList->currentPage() }} / {{ $lokasiList->lastPage() }}</span>
                @if($lokasiList->hasMorePages())
                    <a href="{{ $lokasiList->nextPageUrl() }}" class="pg-btn"><i class="fas fa-angle-right"></i></a>
                @else
                    <span class="pg-btn disabled"><i class="fas fa-angle-right"></i></span>
                @endif
            </div>
        @endif
    @endif
</div>

<div class="action-bar">
    <a href="{{ route('dashboard') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
    <a href="{{ route('admin.pkl.rekap.per-lokasi') }}" class="ab-btn" style="background:#6366f1;color:#fff;">
        <i class="fas fa-chart-bar"></i> Rekap
    </a>
    @can('pkl.create')
    <a href="{{ route('admin.pkl.lokasi.create') }}" class="ab-btn ab-btn-amber">
        <i class="fas fa-plus"></i> Tambah Lokasi
    </a>
    @endcan
</div>
@endsection
