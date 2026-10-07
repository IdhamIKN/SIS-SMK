@extends('layouts.app')

@section('title', 'Manajemen Jam Pelajaran')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ── Shift Badge Colors ── */
        .shift-pagi {
            background: #fef9c3;
            color: #a16207;
        }

        .shift-siang {
            background: #ffedd5;
            color: #c2410c;
        }

        .shift-sore {
            background: #ede9fe;
            color: #6d28d9;
        }

        .shift-malam {
            background: #1e293b;
            color: #94a3b8;
        }

        /* ── Time Pill ── */
        .time-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-family: 'Courier New', monospace;
            font-size: .78rem;
            font-weight: 700;
            background: #f1f5f9;
            color: #334155;
            padding: 3px 9px;
            border-radius: 6px;
            letter-spacing: .3px;
        }

        .time-limit {
            font-size: .7rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ── Time Block ── */
        .time-block {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin: 6px 0 10px;
        }

        /* ── Badge Row ── */
        .badge-row {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        /* ── Divider ── */
        .c-divider {
            height: 1px;
            background: #f1f5f9;
            margin: 0 0 12px;
        }

        .time-col {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .time-col-label {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--text-muted);
        }

        /* ── Search Input ── */
        .filter-select[type="text"] {
            background-image: none;
        }

        /* ── Meta Row ── */
        .jam-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: .75rem;
            color: var(--text-muted);
            margin: 6px 0 10px;
        }

        .jam-meta span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Action Buttons (same as event) ── */
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

        /* ── Status Badge ── */
        .status-active {
            background: #dcfce7;
            color: #15803d;
        }

        .status-nonactive {
            background: #f1f5f9;
            color: #64748b;
        }

        /* ── Filter Card ── */
        .filter-card {
            background: #fff;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
            border: 1px solid #f1f5f9;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
        }

        .filter-field {
            flex: 1;
            min-width: 130px;
        }

        .filter-field label {
            display: block;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .filter-select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: .82rem;
            color: #334155;
            background: #f8fafc;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px #eff6ff;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
        }

        .btn-filter {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-family: inherit;
            transition: all .15s;
            text-decoration: none;
        }

        .btn-filter-primary {
            background: #1d4ed8;
            color: #fff;
        }

        .btn-filter-primary:hover {
            background: #1e40af;
        }

        .btn-filter-reset {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .btn-filter-reset:hover {
            background: #e2e8f0;
        }

        /* ── Shift Icon Colors ── */
        .icon-pagi {
            background: #fef9c3 !important;
            color: #a16207 !important;
        }

        .icon-siang {
            background: #ffedd5 !important;
            color: #c2410c !important;
        }

        .icon-sore {
            background: #ede9fe !important;
            color: #6d28d9 !important;
        }

        .icon-malam {
            background: #0f172a !important;
            color: #94a3b8 !important;
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
                <i class="fas fa-clock"></i>
                Jam Pelajaran
            </h2>
            <p>Kelola &amp; jam mulai / selesai</p>
        </div>

        {{-- ── Alerts ── --}}
        @if (session('success'))
            <div class="alert a-ok">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert a-err">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- ── Stats ── --}}
        <div class="status-bar">
            <div class="s-chip">
                <div class="ci ci-e"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="c-lbl">Total Jam</div>
                    <div class="c-val">{{ $setJam->total() }}</div>
                </div>
            </div>
            <div class="s-chip">
                <div class="ci ci-g"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="c-lbl">Aktif</div>
                    <div class="c-val">{{ $setJam->filter(fn($j) => $j->statusjam)->count() }}</div>
                </div>
            </div>
            <div class="s-chip">
                <div class="ci" style="background:#f1f5f9;color:#94a3b8;"><i class="fas fa-pause-circle"></i></div>
                <div>
                    <div class="c-lbl">Nonaktif</div>
                    <div class="c-val">{{ $setJam->filter(fn($j) => !$j->statusjam)->count() }}</div>
                </div>
            </div>
        </div>

        {{-- ── Filter ── --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('admin.set-jam.index') }}">

                {{-- Search --}}
                <div style="margin-bottom:10px;">
                    <div class="filter-field" style="max-width:100%;">
                        <label>Cari Jam</label>
                        <div style="position:relative;">
                            <i class="fas fa-search"
                                style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.75rem;pointer-events:none;"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari nama jam..." class="filter-select" style="padding-left:30px;">
                        </div>
                    </div>
                </div>

                <div class="filter-row">
                    {{-- Filter Shift --}}
                    <div class="filter-field">
                        <label>Shift</label>
                        <select name="shif" class="filter-select">
                            <option value="">Semua Shift</option>
                            <option value="Pagi" {{ request('shif') === 'Pagi' ? 'selected' : '' }}>Pagi</option>
                            <option value="Siang" {{ request('shif') === 'Siang' ? 'selected' : '' }}>Siang</option>
                            <option value="Sore" {{ request('shif') === 'Sore' ? 'selected' : '' }}>Sore</option>
                            <option value="Malam" {{ request('shif') === 'Malam' ? 'selected' : '' }}>Malam</option>
                        </select>
                    </div>

                    {{-- Filter Kelompok --}}
                    <div class="filter-field">
                        <label>Kelompok</label>
                        <select name="kelompok" class="filter-select">
                            <option value="">Semua Kelompok</option>
                            <option value="reguler" {{ request('kelompok') === 'reguler' ? 'selected' : '' }}>Reguler
                                Kls 10</option>
                            <option value="reguler_1112" {{ request('kelompok') === 'reguler_1112' ? 'selected' : '' }}>
                                Reguler Kls 11-12</option>
                            <option value="jumat" {{ request('kelompok') === 'jumat' ? 'selected' : '' }}>Jumat
                            </option>
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div class="filter-field">
                        <label>Status</label>
                        <select name="status" class="filter-select">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-filter btn-filter-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.set-jam.index') }}" class="btn-filter btn-filter-reset">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    </div>
                </div>

            </form>
        </div>

        {{-- ── Jam List ── --}}
        @if ($setJam->count() > 0)
            @foreach ($setJam as $jam)
                @php
                    $shif = strtolower($jam->shif ?? 'pagi');
                    $iconClass = "icon-{$shif}";
                    $shiftClass = "shift-{$shif}";
                    $icons = [
                        'pagi' => 'fa-sun',
                        'siang' => 'fa-cloud-sun',
                        'sore' => 'fa-cloud-moon',
                        'malam' => 'fa-moon',
                    ];
                    $icon = $icons[$shif] ?? 'fa-clock';

                    // Badge kelompok
                    $kelompokBadge = match ($jam->kelompok_jam ?? 'reguler') {
                        'reguler_1112' => ['label' => 'Kls 11-12', 'style' => 'background:#dbeafe;color:#1d4ed8;'],
                        'jumat' => ['label' => 'Jumat', 'style' => 'background:#fce7f3;color:#9d174d;'],
                        default => ['label' => 'Kls 10', 'style' => 'background:#dcfce7;color:#15803d;'],
                    };
                @endphp

                <div class="card">
                    <div class="c-head">
                        <div class="c-icon {{ $iconClass }}">
                            <i class="fas {{ $icon }}"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <h3>{{ $jam->nama_jam }}</h3>
                            <span style="font-size:.7rem; color:var(--text-muted);">ID: #{{ $jam->id_jam }}</span>
                        </div>
                    </div>

                    <div class="c-body" style="padding:12px 16px 14px;">

                        {{-- Badge Row --}}
                        <div class="badge-row">
                            <span class="hbadge {{ $shiftClass }}">{{ $jam->shif }}</span>
                            <span class="hbadge" style="font-size:.65rem; {{ $kelompokBadge['style'] }}">
                                {{ $kelompokBadge['label'] }}
                            </span>
                            <span class="hbadge badge-status {{ $jam->statusjam ? 'status-active' : 'status-nonactive' }}">
                                {{ $jam->statusjam ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <div class="c-divider"></div>

                        {{-- Time Display --}}
                        <div class="time-block">
                            <div class="time-col">
                                <div class="time-col-label"><i class="fas fa-sign-in-alt"></i> Mulai</div>
                                <div class="time-pill">
                                    <i class="fas fa-clock" style="font-size:.65rem;"></i>
                                    {{ $jam->time_in ? $jam->time_in->format('H:i') : '--:--' }}
                                </div>
                            </div>

                            <div style="display:flex; align-items:center; padding-top:14px; color:#cbd5e1;">
                                <i class="fas fa-arrow-right"></i>
                            </div>

                            <div class="time-col">
                                <div class="time-col-label"><i class="fas fa-sign-out-alt"></i> Selesai</div>
                                <div class="time-pill">
                                    <i class="fas fa-clock" style="font-size:.65rem;"></i>
                                    {{ $jam->time_out ? $jam->time_out->format('H:i') : '--:--' }}
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="action-group">
                            <a href="{{ route('admin.set-jam.show', $jam) }}" class="action-btn btn-view">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            <a href="{{ route('admin.set-jam.edit', $jam) }}" class="action-btn btn-edit">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            <button type="button" class="action-btn btn-delete"
                                onclick="confirmDelete({{ $jam->id_jam }}, '{{ str_replace('\'', '\\\'', $jam->nama_jam) }}')">
                                <i class="fas fa-trash"></i>
                            </button>

                            <form id="delete-form-{{ $jam->id_jam }}" method="POST"
                                action="{{ route('admin.set-jam.destroy', $jam) }}" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- ── Pagination ── --}}
            @if ($setJam->hasPages())
                <div class="pagination-chips">
                    @if (!$setJam->onFirstPage())
                        <a href="{{ $setJam->appends(request()->query())->previousPageUrl() }}" class="page-chip">←
                            Sebelumnya</a>
                    @endif
                    <span class="page-chip active">{{ $setJam->currentPage() }} / {{ $setJam->lastPage() }}</span>
                    @if ($setJam->hasMorePages())
                        <a href="{{ $setJam->appends(request()->query())->nextPageUrl() }}" class="page-chip">Berikutnya
                            →</a>
                    @endif
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-clock"></i></div>
                <h3 class="empty-title">Belum ada jam pelajaran</h3>
                <p class="empty-text">Tambahkan jam pelajaran pertama untuk mengatur jadwal absensi.</p>
                <a href="{{ route('admin.set-jam.create') }}" class="btn-sub">
                    <i class="fas fa-plus"></i> Tambah Jam Pertama
                </a>
            </div>
        @endif

    </div>

    {{-- ── FAB: Tambah Jam ── --}}
    <a href="{{ route('admin.set-jam.create') }}" class="fab-add">
        <i class="fas fa-plus"></i> Tambah Jam
    </a>

@endsection

@push('scripts')
    <script>
        /* ── Delete Confirmation ── */
        function confirmDelete(id, namaJam) {
            Swal.fire({
                title: 'Hapus Jam Pelajaran?',
                html: `Yakin ingin menghapus <strong>${namaJam}</strong>?<br>
                   <small style="color:#94a3b8;">Data yang terhubung dengan jadwal KBM tidak dapat dihapus.</small>`,
                icon: 'warning',
                iconColor: '#f59e0b',
                showCancelButton: true,
                confirmButtonColor: '#be123c',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'swal-popup-custom',
                    confirmButton: 'swal-btn-danger',
                    cancelButton: 'swal-btn-cancel',
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }

        /* ── Session Alerts ── */
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                timer: 4000,
                timerProgressBar: true,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
            });
        @endif

        document.addEventListener('DOMContentLoaded', function() {

            const header = document.querySelector('.header-auto-show');
            if (header) {
                header.classList.add('header-active');
            }
        });
    </script>
@endpush
