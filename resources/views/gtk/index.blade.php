@extends('layouts.app')

@section('title', 'Daftar GTK')

@push('styles')
    @include('components.izin-styles')
    <style>
        .stat-3-grid {
            display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 14px;
        }
        .stat-mini {
            background: #fff; border: 1px solid var(--border, #e2e8f0);
            border-radius: 14px; padding: 14px 10px; text-align: center;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .stat-mini .s-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; margin: 0 auto 6px;
        }
        .stat-mini .s-val { font-size: 1.4rem; font-weight: 800; line-height: 1; }
        .stat-mini .s-lbl { font-size: .62rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; margin-top: 3px; }

        .search-card {
            background: #fff; border: 1px solid var(--border, #e2e8f0);
            border-radius: 16px; padding: 14px 16px; margin-bottom: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .search-input-wrap { position: relative; margin-bottom: 10px; }
        .search-input-wrap .s-icon-left {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: .85rem;
        }
        .search-input-wrap input {
            width: 100%; padding: 10px 12px 10px 34px;
            border: 1px solid var(--border, #e2e8f0); border-radius: 10px;
            font-size: .875rem; font-family: inherit; color: #0f172a;
            background: #f8fafc; outline: none; box-sizing: border-box;
            transition: border-color .2s, box-shadow .2s;
        }
        .search-input-wrap input:focus {
            border-color: #0ea5e9; background: #fff; box-shadow: 0 0 0 3px rgba(14,165,233,.1);
        }
        .filter-select {
            width: 100%; padding: 9px 28px 9px 10px;
            border: 1px solid var(--border, #e2e8f0); border-radius: 10px;
            font-size: .82rem; font-family: inherit; color: #0f172a;
            background: #f8fafc; outline: none; box-sizing: border-box;
            appearance: none; -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center;
            margin-bottom: 10px;
        }
        .btn-search {
            width: 100%; padding: 11px 16px;
            background: #0ea5e9; color: #fff;
            border: none; border-radius: 10px;
            font-size: .875rem; font-weight: 700; font-family: inherit;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 7px;
            transition: filter .18s;
        }
        .btn-search:hover { filter: brightness(1.08); }

        /* GTK card */
        .gtk-avatar {
            width: 48px; height: 48px; border-radius: 12px;
            background: #e0f2fe; color: #0369a1;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; flex-shrink: 0; overflow: hidden;
        }
        .gtk-avatar img { width: 100%; height: 100%; object-fit: cover; }

        .izin-meta { display: flex; flex-wrap: wrap; gap: 8px; font-size: .75rem; color: #64748b; margin-bottom: 10px; }
        .izin-meta span { display: inline-flex; align-items: center; gap: 4px; }

        .action-group { display: flex; gap: 8px; flex-wrap: wrap; }
        .action-btn {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 7px 12px; border-radius: 8px; font-size: .78rem; font-weight: 600;
            border: none; cursor: pointer; text-decoration: none; font-family: inherit;
            transition: all .18s; white-space: nowrap;
        }
        .action-btn:active { transform: scale(.96); }
        .btn-view   { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .btn-view:hover   { background: #dbeafe; }
        .btn-edit   { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .btn-edit:hover   { background: #fef3c7; }
        .btn-delete { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .btn-delete:hover { background: #fee2e2; }

        .status-approved { background: #dcfce7; color: #15803d; }
        .status-rejected { background: #fee2e2; color: #dc2626; }

        .fab-add {
            position: fixed; bottom: calc(var(--footer-h) + 16px); right: 16px;
            background: #0ea5e9; color: #fff;
            padding: 13px 20px; border-radius: 50px;
            font-size: .875rem; font-weight: 700;
            display: inline-flex; align-items: center; gap: 7px;
            text-decoration: none; box-shadow: 0 4px 20px rgba(14,165,233,.35);
            z-index: 900; transition: all .2s;
        }
        .fab-add:hover { filter: brightness(1.08); transform: translateY(-1px); }
        .fab-add:active { transform: scale(.97); }
    </style>
@endpush

@section('content')
<div class="izin-wrap" style="padding-bottom: calc(var(--footer-h) + 88px);">

    {{-- Page Strip --}}
    <div class="page-strip page-strip-izin" style="background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
        <h2><i class="fas fa-user-tie"></i> Daftar GTK</h2>
        <p>Kelola data guru &amp; tenaga kependidikan</p>
    </div>

    @if (session('success'))
        <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert a-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
    @endif

    {{-- Stat 3 kolom --}}
    <div class="stat-3-grid">
        <div class="stat-mini">
            <div class="s-icon" style="background:#e0f2fe; color:#0369a1;"><i class="fas fa-user-tie"></i></div>
            <div class="s-val" style="color:#0369a1;">{{ $gtks->total() ?? $gtks->count() }}</div>
            <div class="s-lbl">Total GTK</div>
        </div>
        <div class="stat-mini">
            <div class="s-icon" style="background:#dcfce7; color:#15803d;"><i class="fas fa-user-check"></i></div>
            <div class="s-val" style="color:#15803d;">{{ $aktifCount ?? 0 }}</div>
            <div class="s-lbl">Aktif</div>
        </div>
        <div class="stat-mini">
            <div class="s-icon" style="background:#fee2e2; color:#dc2626;"><i class="fas fa-user-times"></i></div>
            <div class="s-val" style="color:#dc2626;">{{ $nonAktifCount ?? 0 }}</div>
            <div class="s-lbl">Non Aktif</div>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="search-card">
        <form method="GET" action="{{ route('gtk.index') }}">
            <div class="search-input-wrap">
                <i class="fas fa-search s-icon-left"></i>
                <input type="text" name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama GTK...">
            </div>
            <select name="jabatan" class="filter-select">
                <option value="">Semua Jabatan</option>
                <option value="Guru"                  {{ request('jabatan') == 'Guru'                  ? 'selected' : '' }}>Guru</option>
                <option value="Kepala Sekolah"        {{ request('jabatan') == 'Kepala Sekolah'        ? 'selected' : '' }}>Kepala Sekolah</option>
                <option value="Wakil Kepala Sekolah"  {{ request('jabatan') == 'Wakil Kepala Sekolah'  ? 'selected' : '' }}>Wakil Kepala Sekolah</option>
                <option value="BK"                    {{ request('jabatan') == 'BK'                    ? 'selected' : '' }}>Bimbingan Konseling</option>
                <option value="Tata Usaha"            {{ request('jabatan') == 'Tata Usaha'            ? 'selected' : '' }}>Tata Usaha</option>
            </select>
            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i> Cari GTK
            </button>
        </form>

        <div style="margin-top:12px; text-align:center;">
            <a href="{{ route('gtk.import') }}" class="btn-search" style="background:#10b981;">
                <i class="fas fa-upload"></i> Import GTK
            </a>
        </div>
    </div>

    {{-- Daftar GTK --}}
    @if ($gtks->count() > 0)
        @foreach ($gtks as $gtk)
            <div class="card izin-item">
                <div class="c-head">
                    <div class="gtk-avatar">
                        @if ($gtk->foto)
                            <img src="{{ Storage::url($gtk->foto) }}" alt="{{ $gtk->nama_lengkap }}">
                        @else
                            <i class="fas fa-user-tie"></i>
                        @endif
                    </div>
                    <div style="flex:1; min-width:0;">
                        <h3 style="margin:0 0 2px; font-size:.9rem; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ $gtk->nama_lengkap }}
                        </h3>
                        <p style="margin:0; font-size:.72rem; color:#64748b;">
                            {{ $gtk->kd_guru ?? '-' }}
                        </p>
                    </div>
                    <span class="hbadge badge-status {{ $gtk->status_aktif ? 'status-approved' : 'status-rejected' }}">
                        {{ $gtk->status_aktif ? 'Aktif' : 'Non Aktif' }}
                    </span>
                </div>

                <div class="c-body" style="padding:10px 16px 14px;">
                    <div class="izin-meta">
                        <span><i class="fas fa-briefcase"></i> {{ $gtk->jabatan ?? '-' }}</span>
                        @if ($gtk->mata_pelajaran)
                            <span><i class="fas fa-book-open"></i> {{ Str::limit($gtk->mata_pelajaran, 22) }}</span>
                        @endif
                        @if ($gtk->nip)
                            <span><i class="fas fa-id-card"></i> {{ $gtk->nip }}</span>
                        @endif
                    </div>
                    <div class="action-group">
                        <a href="{{ route('gtk.show', $gtk) }}" class="action-btn btn-view">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                        <a href="{{ route('gtk.edit', $gtk) }}" class="action-btn btn-edit">
                            <i class="fas fa-pen"></i> Edit
                        </a>
                        <button type="button" class="action-btn btn-delete"
                                onclick="confirmDelete('{{ route('gtk.destroy', $gtk) }}', '{{ $gtk->nama_lengkap }}')">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Pagination --}}
        @if ($gtks->hasPages())
            <div class="pagination-chips">
                @if (!$gtks->onFirstPage())
                    <a href="{{ $gtks->previousPageUrl() }}" class="page-chip">← Sebelumnya</a>
                @endif
                <span class="page-chip active">{{ $gtks->currentPage() }} / {{ $gtks->lastPage() }}</span>
                @if ($gtks->hasMorePages())
                    <a href="{{ $gtks->nextPageUrl() }}" class="page-chip">Berikutnya →</a>
                @endif
            </div>
        @endif

    @else
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-user-tie"></i></div>
            <h3 class="empty-title">Belum ada data GTK</h3>
            <p class="empty-text">Tambah guru &amp; tenaga kependidikan untuk memulai.</p>
            <a href="{{ route('gtk.create') }}" class="btn-sub">
                <i class="fas fa-plus"></i> Tambah GTK Pertama
            </a>
        </div>
    @endif

</div>

<a href="{{ route('gtk.create') }}" class="fab-add">
    <i class="fas fa-plus"></i> Tambah GTK
</a>

<form id="deleteForm" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function confirmDelete(url, nama) {
    if (typeof Swal === 'undefined') {
        if (!confirm('Yakin menghapus ' + nama + '?')) return;
        submitDelete(url);
        return;
    }
    Swal.fire({
        title: 'Hapus GTK?',
        html: 'Yakin menghapus <strong>' + nama + '</strong>?<br><small style="color:#64748b;">Data tidak dapat dikembalikan.</small>',
        icon: 'warning',
        showCancelButton: true,
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
            confirmButton: 'action-btn btn-delete',
            cancelButton: 'action-btn btn-view',
        },
        confirmButtonText: '<i class="fas fa-trash-alt"></i>&nbsp; Hapus',
        cancelButtonText: '<i class="fas fa-times"></i>&nbsp; Batal',
    }).then(result => {
        if (result.isConfirmed) submitDelete(url);
    });
}

function submitDelete(url) {
    const form = document.getElementById('deleteForm');
    form.action = url;
    form.submit();
}
</script>
@endpush