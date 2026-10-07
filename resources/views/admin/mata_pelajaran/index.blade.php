@extends('layouts.app')

@section('title', 'Manajemen Mata Pelajaran')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ── Mapel-specific palette ── */
        :root {
            --mapel-fade: #eff6ff;
            --mapel-accent: #2563eb;
            --mapel-muted: #93c5fd;
        }

        /* ── Meta row ── */
        .mapel-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            font-size: .75rem;
            color: var(--text-muted);
            margin: 6px 0 10px;
        }

        .mapel-meta span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Tags ── */
        .mapel-tags {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 6px;
            font-size: .7rem;
            margin-bottom: 10px;
        }

        .tag {
            padding: 2px 9px;
            border-radius: 20px;
            font-weight: 600;
        }

        .tag-umum {
            background: #ede9fe;
            color: #6d28d9;
        }

        .tag-jurusan {
            background: #dcfce7;
            color: #15803d;
        }

        .tag-mulok {
            background: #fef3c7;
            color: #b45309;
        }

        .tag-aktif {
            background: #dcfce7;
            color: #15803d;
        }

        .tag-nonaktif {
            background: #f1f5f9;
            color: #64748b;
        }

        /* ── Action buttons ── */
        .action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 13px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            white-space: nowrap;
        }

        .action-btn:active {
            transform: scale(.96);
        }

        .btn-view {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .btn-view:hover {
            background: #dbeafe;
        }

        .btn-edit {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .btn-edit:hover {
            background: #fef3c7;
        }

        .btn-delete {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }

        .btn-delete:hover {
            background: #ffe4e6;
        }

        /* ── Status badge on card header ── */
        .status-aktif {
            background: #dcfce7;
            color: #15803d;
        }

        .status-nonaktif {
            background: #f1f5f9;
            color: #64748b;
        }

        /* ── Search / filter bar ── */
        .filter-bar {
            background: #fff;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            min-width: 140px;
        }

        .filter-group label {
            font-size: .72rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .filter-input,
        .filter-select {
            padding: 8px 11px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: .82rem;
            font-family: inherit;
            background: #f8fafc;
            color: var(--text-main, #1e293b);
            outline: none;
            transition: border-color .15s;
            width: 100%;
            box-sizing: border-box;
        }

        .filter-input:focus,
        .filter-select:focus {
            border-color: var(--mapel-accent);
            background: #fff;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .btn-filter {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-family: inherit;
            transition: all .15s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        .btn-filter-primary {
            background: var(--mapel-accent);
            color: #fff;
        }

        .btn-filter-primary:hover {
            background: #1d4ed8;
        }

        .btn-filter-reset {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .btn-filter-reset:hover {
            background: #e2e8f0;
        }

        /* ── Kode pill ── */
        .kode-pill {
            font-family: 'Courier New', monospace;
            background: #f1f5f9;
            color: #475569;
            border-radius: 6px;
            padding: 2px 7px;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .03em;
        }

        /* ── Jadwal count chip ── */
        .jadwal-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 20px;
            padding: 2px 9px;
            font-size: .72rem;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">

        {{-- ── Page Strip ── --}}
        <div class="page-strip page-strip-event">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <h2>
                <i class="fas fa-book-open"></i>
                Mata Pelajaran
            </h2>
            <p>Kelola daftar mata pelajaran sekolah</p>
        </div>

        {{-- ── Alerts ── --}}
        @if (session('success'))
            <div class="alert a-ok">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert a-err">
                <i class="fas fa-times-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- ── Stats ── --}}
        {{-- Gunakan grid 3 kolom agar tidak wrap seperti di event view yang hanya 2 chip --}}
        <div class="status-bar" style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px;">
            <div class="s-chip" style="flex:unset;">
                <div class="ci" style="background:#ede9fe; flex-shrink:0;"><i class="fas fa-book"
                        style="color:#6d28d9;"></i></div>
                <div style="min-width:0;">
                    <div class="c-lbl">Total</div>
                    <div class="c-val">{{ $mataPelajaran->total() }}</div>
                </div>
            </div>
            <div class="s-chip" style="flex:unset;">
                <div class="ci" style="background:#dcfce7; flex-shrink:0;"><i class="fas fa-check-circle"
                        style="color:#15803d;"></i></div>
                <div style="min-width:0;">
                    <div class="c-lbl">Aktif</div>
                    <div class="c-val">{{ $mataPelajaran->filter(fn($m) => $m->status_aktif)->count() }}</div>
                </div>
            </div>
            <div class="s-chip" style="flex:unset;">
                <div class="ci" style="background:#fef3c7; flex-shrink:0;"><i class="fas fa-layer-group"
                        style="color:#b45309;"></i></div>
                <div style="min-width:0;">
                    <div class="c-lbl">Kategori</div>
                    <div class="c-val">{{ $mataPelajaran->unique('kategori')->count() }}</div>
                </div>
            </div>
        </div>

        {{-- ── Filter Bar ── --}}
        <form method="GET" action="{{ route('admin.mata-pelajaran.index') }}">
            <div class="filter-bar">
                <div class="filter-group" style="flex: 2; min-width: 180px;">
                    <label><i class="fas fa-search"></i> Cari</label>
                    <input type="text" name="search" class="filter-input" placeholder="Nama atau kode mapel..."
                        value="{{ request('search') }}">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-tag"></i> Kategori</label>
                    <select name="kategori" class="filter-select">
                        <option value="">Semua</option>
                        <option value="umum" {{ request('kategori') == 'umum' ? 'selected' : '' }}>Umum</option>
                        <option value="jurusan" {{ request('kategori') == 'jurusan' ? 'selected' : '' }}>Jurusan</option>
                        <option value="mulok" {{ request('kategori') == 'mulok' ? 'selected' : '' }}>Muatan Lokal
                        </option>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-toggle-on"></i> Status</label>
                    <select name="status" class="filter-select">
                        <option value="">Semua</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-filter btn-filter-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <a href="{{ route('admin.mata-pelajaran.index') }}" class="btn-filter btn-filter-reset">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>

        {{-- ── Mapel List ── --}}
        @if ($mataPelajaran->count() > 0)
            @foreach ($mataPelajaran as $mapel)
                @php
                    $kategoriClass = match ($mapel->kategori) {
                        'jurusan' => 'tag-jurusan',
                        'mulok' => 'tag-mulok',
                        default => 'tag-umum',
                    };
                    $iconBg = match ($mapel->kategori) {
                        'jurusan' => '#dcfce7',
                        'mulok' => '#fef3c7',
                        default => '#ede9fe',
                    };
                    $iconColor = match ($mapel->kategori) {
                        'jurusan' => '#15803d',
                        'mulok' => '#b45309',
                        default => '#6d28d9',
                    };
                @endphp
                <div class="card">
                    <div class="c-head">
                        <div class="c-icon" style="background:{{ $iconBg }};">
                            <i class="fas fa-book" style="color:{{ $iconColor }};"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <h3>{{ Str::limit($mapel->nama_mapel, 38) }}</h3>
                            <span class="kode-pill">{{ $mapel->kode_mapel }}</span>
                        </div>
                        <span class="hbadge badge-status {{ $mapel->status_aktif ? 'status-aktif' : 'status-nonaktif' }}">
                            {{ $mapel->status_aktif ? 'Aktif' : 'Non-aktif' }}
                        </span>
                    </div>

                    <div class="c-body" style="padding:12px 16px 14px;">
                        <p style="color:var(--text-muted);font-size:.84rem;line-height:1.55;margin:0 0 8px;">
                            {{ $mapel->deskripsi ? Str::limit($mapel->deskripsi, 100) : 'Tidak ada deskripsi' }}
                        </p>

                        <div class="mapel-meta">
                            <span><i class="fas fa-calendar-week"></i>
                                <span class="jadwal-chip">
                                    <i class="fas fa-clock"></i> {{ $mapel->jadwalKBM()->count() }} jadwal
                                </span>
                            </span>
                        </div>

                        <div class="mapel-tags" style="margin-bottom:12px;">
                            <span class="tag {{ $kategoriClass }}">
                                <i class="fas fa-tag"></i> {{ $mapel->kategori_label }}
                            </span>
                            <span class="tag {{ $mapel->status_aktif ? 'tag-aktif' : 'tag-nonaktif' }}">
                                <i class="fas fa-{{ $mapel->status_aktif ? 'check' : 'ban' }}"></i>
                                {{ $mapel->status_aktif ? 'Aktif' : 'Non-aktif' }}
                            </span>
                        </div>

                        <div class="action-group">
                            <a href="{{ route('admin.mata-pelajaran.show', $mapel) }}" class="action-btn btn-view">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            <a href="{{ route('admin.mata-pelajaran.edit', $mapel) }}" class="action-btn btn-edit">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            <button type="button" class="action-btn btn-delete btn-hapus-mapel"
                                data-id="{{ $mapel->id }}" data-nama="{{ $mapel->nama_mapel }}"
                                data-action="{{ route('admin.mata-pelajaran.destroy', $mapel) }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- ── Pagination ── --}}
            @if ($mataPelajaran->hasPages())
                <div class="pagination-chips">
                    @if (!$mataPelajaran->onFirstPage())
                        <a href="{{ $mataPelajaran->previousPageUrl() }}" class="page-chip">← Sebelumnya</a>
                    @endif
                    <span class="page-chip active">
                        {{ $mataPelajaran->currentPage() }} / {{ $mataPelajaran->lastPage() }}
                    </span>
                    @if ($mataPelajaran->hasMorePages())
                        <a href="{{ $mataPelajaran->nextPageUrl() }}" class="page-chip">Berikutnya →</a>
                    @endif
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-book-open"></i></div>
                <h3 class="empty-title">Belum ada mata pelajaran</h3>
                <p class="empty-text">Tambahkan mata pelajaran pertama untuk mulai menyusun jadwal KBM.</p>
                <a href="{{ route('admin.mata-pelajaran.create') }}" class="btn-sub">
                    <i class="fas fa-plus"></i> Tambah Mata Pelajaran
                </a>
            </div>
        @endif

    </div>

    {{-- ── FAB ── --}}
    <a href="{{ route('admin.mata-pelajaran.create') }}" class="fab-add">
        <i class="fas fa-plus"></i> Tambah Mapel
    </a>

    {{-- Hidden delete form ── --}}
    <form id="form-hapus-mapel" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ── SweetAlert2 – delete confirmation ──────────────────────────────────
            document.querySelectorAll('.btn-hapus-mapel').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const nama = this.dataset.nama;
                    const action = this.dataset.action;

                    Swal.fire({
                        title: 'Hapus Mata Pelajaran?',
                        html: `Mata pelajaran <strong>${nama}</strong> akan dihapus permanen.<br>
                                   <small class="text-muted">Jadwal yang terhubung juga akan terpengaruh.</small>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus',
                        cancelButtonText: '<i class="fas fa-times me-1"></i> Batal',
                        confirmButtonColor: '#be123c',
                        cancelButtonColor: '#64748b',
                        reverseButtons: true,
                        focusCancel: true,
                        customClass: {
                            popup: 'swal-rounded',
                            title: 'swal-title',
                            htmlContainer: 'swal-html',
                            confirmButton: 'swal-btn-danger',
                            cancelButton: 'swal-btn-cancel',
                        },
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            const form = document.getElementById('form-hapus-mapel');
                            form.action = action;
                            form.submit();
                        }
                    });
                });
            });

            // ── SweetAlert2 – session flash via toast ──────────────────────────────
            @if (session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: @json(session('success')),
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: @json(session('error')),
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                });
            @endif

            const header = document.querySelector('.header-auto-show');
            if (header) {
                header.classList.add('header-active');
            }

        });
    </script>
@endpush
