@extends('layouts.app')
@section('title', 'Semua Penugasan PKL')

@push('styles')
    @include('components.event-styles')
    <style>
        .pkl-wrap { padding:0 12px; max-width:1200px; margin:0 auto; }
        @media(min-width:768px){ .pkl-wrap { padding:0 24px; } }
        .filter-bar { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px; margin-bottom:16px; display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; }
        .filter-bar .form-label { font-size:.75rem; font-weight:600; color:#475569; margin-bottom:4px; display:block; }
        .filter-bar .form-control { padding:8px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:.82rem; background:#fff; color:#0f172a; min-width:140px; }
        .rekap-table { width:100%; border-collapse:collapse; font-size:.8rem; }
        .rekap-table th { background:#f8fafc; padding:9px 10px; text-align:left; font-size:.68rem; font-weight:700; color:#64748b; text-transform:uppercase; border-bottom:2px solid #e2e8f0; white-space:nowrap; }
        .rekap-table td { padding:9px 10px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
        .rekap-table tr:last-child td { border-bottom:none; }
        .rekap-table tr:hover td { background:#fafbfc; }
        .badge-aktif { background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:20px; font-size:.65rem; font-weight:700; }
        .badge-selesai { background:#dbeafe; color:#1d4ed8; padding:2px 8px; border-radius:20px; font-size:.65rem; font-weight:700; }
        .badge-batal { background:#fee2e2; color:#dc2626; padding:2px 8px; border-radius:20px; font-size:.65rem; font-weight:700; }
        .action-bar { position:fixed; bottom:var(--footer-h,0); left:0; right:0; padding:10px 12px 12px; background:rgba(255,255,255,.96); backdrop-filter:blur(10px); border-top:1px solid #e2e8f0; display:flex; gap:8px; z-index:999; }
        @media(min-width:768px){ .action-bar { padding:10px 24px 12px; justify-content:flex-end; } }
        .ab-btn { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:11px 16px; border-radius:12px; font-size:.82rem; font-weight:700; border:none; cursor:pointer; text-decoration:none; font-family:inherit; }
        @media(min-width:768px){ .ab-btn { flex:unset; min-width:120px; } }
        .ab-btn-back { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
        .ab-btn-green { background:#16a34a; color:#fff; }
    </style>
@endpush

@section('content')
<div class="pkl-wrap" style="padding-top:var(--header-h,56px);padding-bottom:148px;">

    <div class="page-strip page-strip-event">
        <div class="live-badge"><span class="live-dot"></span>PKL</div>
        <h2><i class="fas fa-user-graduate"></i> Semua Penugasan PKL</h2>
        <p>Kelola penugasan siswa ke lokasi PKL.</p>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div style="background:#fffbeb;border:1px solid #fcd34d;color:#92400e;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
        </div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="filter-bar">
        <div>
            <label class="form-label">Lokasi PKL</label>
            <select name="lokasi_pkl_id" class="form-control">
                <option value="">Semua Lokasi</option>
                @foreach($lokasiOptions as $l)
                    <option value="{{ $l->id }}" {{ $lokasiId == $l->id ? 'selected' : '' }}>{{ $l->nama_tempat }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="">Semua</option>
                <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                <option value="batal" {{ $status === 'batal' ? 'selected' : '' }}>Batal</option>
            </select>
        </div>
        <div>
            <label class="form-label">Tahun Ajaran</label>
            <select name="academic_year_id" class="form-control">
                <option value="">Semua</option>
                @foreach($academicYears as $ay)
                    <option value="{{ $ay->id }}" {{ $academicYearId == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Cari Siswa</label>
            <input type="text" name="search" class="form-control" placeholder="Nama / NIS..." value="{{ $search }}">
        </div>
        <div style="display:flex;gap:8px;align-items:flex-end;">
            <button type="submit" class="action-btn btn-view" style="padding:8px 14px;"><i class="fas fa-filter"></i></button>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="card" style="border-radius:12px;overflow:hidden;">
        <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid #f1f5f9;">
            <h3 style="font-size:.9rem;font-weight:800;color:#0f172a;margin:0;flex:1;">Penugasan PKL</h3>
            <span style="background:#f1f5f9;color:#475569;font-size:.7rem;padding:3px 10px;border-radius:20px;font-weight:700;">{{ $penugasanList->total() }}</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="rekap-table">
                <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Lokasi PKL</th>
                        <th>Pembimbing</th>
                        <th>Periode</th>
                        <th>Status</th>
                        @can('pkl.update')<th>Aksi</th>@endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($penugasanList as $p)
                        <tr>
                            <td>
                                <div style="font-weight:700;font-size:.85rem;">{{ $p->siswa?->nama_lengkap ?? '-' }}</div>
                                <div style="font-size:.7rem;color:#64748b;">{{ $p->siswa?->nis }} • {{ $p->siswa?->kelas?->nama_kelas }}</div>
                            </td>
                            <td style="font-size:.8rem;">{{ $p->lokasiPkl?->nama_tempat ?? '-' }}</td>
                            <td style="font-size:.78rem;">{{ $p->gtk?->nama_lengkap ?? '-' }}</td>
                            <td style="font-size:.72rem;white-space:nowrap;">{{ $p->tanggal_mulai->format('d/m/Y') }} – {{ $p->tanggal_selesai->format('d/m/Y') }}</td>
                            <td><span class="badge-{{ $p->status }}">{{ $p->status_label }}</span></td>
                            @can('pkl.update')
                            <td>
                                <div style="display:flex;gap:4px;">
                                    <a href="{{ route('admin.pkl.rekap.detail-siswa', $p) }}" class="action-btn btn-view" style="font-size:.7rem;padding:4px 8px;text-decoration:none;"><i class="fas fa-eye"></i></a>
                                    @if($p->status === 'aktif')
                                    <form method="POST" action="{{ route('admin.pkl.penugasan.selesai', $p) }}" style="display:inline;" onsubmit="return confirm('Selesaikan PKL ini?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="action-btn btn-view" style="font-size:.7rem;padding:4px 8px;background:#dbeafe;color:#1d4ed8;border:none;"><i class="fas fa-check"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.pkl.penugasan.batal', $p) }}" style="display:inline;" onsubmit="return confirm('Batalkan penugasan ini?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="action-btn" style="font-size:.7rem;padding:4px 8px;background:#fee2e2;color:#dc2626;border:none;border-radius:6px;cursor:pointer;"><i class="fas fa-times"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                            @endcan
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;padding:32px;color:#94a3b8;">Tidak ada data penugasan PKL.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($penugasanList->hasPages())
            <div style="padding:10px 14px;border-top:1px solid #f1f5f9;display:flex;justify-content:center;gap:6px;flex-wrap:wrap;">
                @if($penugasanList->onFirstPage())
                    <span class="pg-btn disabled"><i class="fas fa-angle-left"></i></span>
                @else
                    <a href="{{ $penugasanList->previousPageUrl() }}" class="pg-btn"><i class="fas fa-angle-left"></i></a>
                @endif
                <span class="pg-btn active">{{ $penugasanList->currentPage() }}/{{ $penugasanList->lastPage() }}</span>
                @if($penugasanList->hasMorePages())
                    <a href="{{ $penugasanList->nextPageUrl() }}" class="pg-btn"><i class="fas fa-angle-right"></i></a>
                @else
                    <span class="pg-btn disabled"><i class="fas fa-angle-right"></i></span>
                @endif
            </div>
        @endif
    </div>
</div>

<div class="action-bar">
    <a href="{{ route('admin.pkl.lokasi.index') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
    @can('pkl.create')
    <a href="{{ route('admin.pkl.penugasan.create') }}" class="ab-btn ab-btn-green"><i class="fas fa-plus"></i> Assign Siswa</a>
    @endcan
</div>
@endsection
