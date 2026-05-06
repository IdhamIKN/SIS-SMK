@extends('layouts.app')

@section('title', 'Daftar Kelas')

@push('styles')
    @include('components.izin-styles')
    <style>
        /* ─────────────────────────────────────────────────────
               SEMUA CSS di-scope ke .sw-page agar tidak menimpa
               style global layout (sidebar, bottom nav, dll.)
            ───────────────────────────────────────────────────── */

        /* Page strip */
        .sw-page .sw-strip {
            padding: 20px 20px 28px;
            background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 60%, #a78bfa 100%);
            position: relative;
            overflow: hidden;
        }

        .sw-page .sw-strip::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, .07);
            border-radius: 50%;
        }

        .sw-page .sw-strip::after {
            content: '';
            position: absolute;
            bottom: -24px;
            left: -20px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, .05);
            border-radius: 50%;
        }

        .sw-page .sw-live-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .15);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, .2);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 600;
            color: rgba(255, 255, 255, .9);
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .sw-page .sw-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #a7f3d0;
            display: inline-block;
            animation: sw-pulse 2s infinite;
        }

        @keyframes sw-pulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .4
            }
        }

        .sw-page .sw-strip h2 {
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

        .sw-page .sw-strip p {
            font-size: .8rem;
            color: rgba(255, 255, 255, .7);
            margin: 0;
            position: relative;
            z-index: 1;
        }

        /* Alerts */
        .sw-page .sw-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: .84rem;
            margin: 12px 16px 0;
        }

        .sw-page .sw-alert.ok {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .sw-page .sw-alert.err {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        /* Stat grid 3 col */
        .sw-page .sw-stats {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin: 14px 16px;
        }

        .sw-page .sw-stat-box {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 10px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .sw-page .sw-stat-box .si {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            margin: 0 auto 6px;
        }

        .sw-page .sw-stat-box .sv {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
        }

        .sw-page .sw-stat-box .sl {
            font-size: .62rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-top: 3px;
        }

        /* Search card */
        .sw-page .sw-search-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 16px;
            margin: 0 16px 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .sw-page .sw-search-wrap {
            position: relative;
            margin-bottom: 10px;
        }

        .sw-page .sw-search-wrap .sw-ico {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: .85rem;
            pointer-events: none;
        }

        .sw-page .sw-search-wrap input {
            width: 100%;
            padding: 10px 12px 10px 34px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            box-sizing: border-box;
            transition: border-color .2s, box-shadow .2s;
        }

        .sw-page .sw-search-wrap input:focus {
            border-color: #7c3aed;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .1);
        }

        .sw-page .sw-filter-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 10px;
        }

        .sw-page .sw-select {
            width: 100%;
            padding: 9px 28px 9px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: .82rem;
            font-family: inherit;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            box-sizing: border-box;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
        }

        .sw-page .sw-btn-search {
            width: 100%;
            padding: 11px 16px;
            background: #7c3aed;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin-bottom: 10px;
            transition: filter .18s;
        }

        .sw-page .sw-btn-search:hover {
            filter: brightness(1.08);
        }

        /* Kelas cards */
        .sw-page .sw-list {
            padding: 0 16px;
        }

        .sw-page .sw-item {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            margin-bottom: 10px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
            overflow: hidden;
        }

        .sw-page .sw-item-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px 10px;
        }

        .sw-page .sw-ava {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #ede9fe;
            color: #7c3aed;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .sw-page .sw-ava img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sw-page .sw-item-name {
            margin: 0 0 2px;
            font-size: .9rem;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sw-page .sw-item-sub {
            margin: 0;
            font-size: .72rem;
            color: #64748b;
        }

        .sw-page .sw-badge {
            font-size: .68rem;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
            flex-shrink: 0;
        }

        .sw-page .sw-badge.ok {
            background: #dcfce7;
            color: #15803d;
        }

        .sw-page .sw-badge.off {
            background: #fee2e2;
            color: #dc2626;
        }

        .sw-page .sw-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            font-size: .75rem;
            color: #64748b;
            padding: 0 16px;
        }

        .sw-page .sw-meta span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .sw-page .sw-actions {
            display: flex;
            gap: 8px;
            padding: 10px 16px 14px;
        }

        .sw-page .sw-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 12px;
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

        .sw-page .sw-btn:active {
            transform: scale(.96);
        }

        .sw-page .sw-btn.view {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .sw-page .sw-btn.edit {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .sw-page .sw-btn.del {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        /* Pagination */
        .sw-page .sw-pager {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 16px 0;
        }

        .sw-page .sw-page-chip {
            padding: 7px 14px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 700;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #475569;
            text-decoration: none;
        }

        .sw-page .sw-page-chip.active {
            background: #7c3aed;
            color: #fff;
            border-color: #7c3aed;
        }

        /* Empty */
        .sw-page .sw-empty {
            padding: 40px 20px;
            text-align: center;
        }

        .sw-page .sw-empty-ico {
            font-size: 3rem;
            color: #c4b5fd;
            margin-bottom: 12px;
        }

        .sw-page .sw-empty h3 {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .sw-page .sw-empty p {
            font-size: .84rem;
            color: #64748b;
            margin-bottom: 16px;
        }

        .sw-page .sw-btn-start {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 11px 20px;
            background: #7c3aed;
            color: #fff;
            border-radius: 12px;
            font-size: .85rem;
            font-weight: 700;
            text-decoration: none;
            transition: filter .18s;
        }

        .sw-page .sw-btn-start:hover {
            filter: brightness(1.08);
        }

        /* FAB — pakai z-index rendah agar tidak menutup sidebar */
        .sw-page .sw-fab {
            position: fixed;
            bottom: calc(var(--footer-h, 60px) + 16px);
            right: 16px;
            background: #7c3aed;
            color: #fff;
            padding: 13px 20px;
            border-radius: 50px;
            font-size: .875rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(124, 58, 237, .35);
            z-index: 100;
            /* rendah, di bawah sidebar layout */
            transition: all .2s;
        }

        .sw-page .sw-fab:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
        }

        .sw-page .sw-fab:active {
            transform: scale(.97);
        }
    </style>
@endpush

@section('content')
    <div class="sw-page" style="padding-bottom: calc(var(--footer-h, 60px) + 80px);">

        {{-- Page Strip --}}
        <div class="sw-strip">
            <div class="sw-live-badge">
                <span class="sw-dot"></span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <h2><i class="fas fa-school"></i> Daftar Kelas</h2>
            <p>Kelola data kelas SMKN 5 Madiun</p>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="sw-alert ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="sw-alert err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
        @endif

        {{-- Stats --}}
        <div class="sw-stats">
            <div class="sw-stat-box">
                <div class="si" style="background:#ede9fe; color:#7c3aed;"><i class="fas fa-school"></i></div>
                <div class="sv" style="color:#7c3aed;">{{ $kelas->total() ?? $kelas->count() }}</div>
                <div class="sl">Total Kelas</div>
            </div>
            <div class="sw-stat-box">
                <div class="si" style="background:#dcfce7; color:#15803d;"><i class="fas fa-users"></i></div>
                <div class="sv" style="color:#15803d;">{{ $kelas->sum('siswa_count') ?? 0 }}</div>
                <div class="sl">Total Siswa</div>
            </div>
            <div class="sw-stat-box">
                <div class="si" style="background:#fee2e2; color:#dc2626;"><i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="sv" style="color:#dc2626;">{{ $kelas->whereNotNull('wali_kelas_id')->count() }}</div>
                <div class="sl">Dengan Wali</div>
            </div>
        </div>

        {{-- Search & Filter --}}
        <div class="sw-search-card">
            <form method="GET" action="{{ route('kelas.index') }}">
                <div class="sw-search-wrap">
                    <i class="fas fa-search sw-ico"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama kelas...">
                </div>
                <div class="sw-filter-row">
                    <select name="jurusan_id" class="sw-select">
                        <option value="">Semua Jurusan</option>
                        @foreach ($jurusans as $j)
                            <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                    <select name="tingkat" class="sw-select">
                        <option value="">Semua Tingkat</option>
                        <option value="10" {{ request('tingkat') == '10' ? 'selected' : '' }}>Kelas 10</option>
                        <option value="11" {{ request('tingkat') == '11' ? 'selected' : '' }}>Kelas 11</option>
                        <option value="12" {{ request('tingkat') == '12' ? 'selected' : '' }}>Kelas 12</option>
                    </select>
                </div>
                <button type="submit" class="sw-btn-search"><i class="fas fa-search"></i> Cari Kelas</button>
            </form>
        </div>

        {{-- Daftar Kelas --}}
        @if ($kelas->count() > 0)
            <div class="sw-list">
                @foreach ($kelas as $k)
                    <div class="sw-item">
                        <div class="sw-item-head">
                            <div class="sw-ava">
                                <i class="fas fa-school"></i>
                            </div>
                            <div style="flex:1; min-width:0;">
                                <h3 class="sw-item-name">{{ $k->nama_kelas }}</h3>
                                <p class="sw-item-sub">Tingkat {{ $k->tingkat }}</p>
                            </div>
                            <span class="sw-badge ok">
                                {{ $k->siswa_count }} Siswa
                            </span>
                        </div>
                        <div class="sw-meta">
                            <span><i class="fas fa-graduation-cap"></i> {{ $k->jurusan?->nama_jurusan ?? '-' }}</span>
                            <span><i class="fas fa-user-tie"></i>
                                {{ $k->waliKelas?->nama_lengkap ?? 'Belum ada wali' }}</span>
                            @if ($k->bk)
                                <span><i class="fas fa-user-shield"></i> {{ $k->bk->nama_lengkap }}</span>
                            @endif
                        </div>
                        <div class="sw-actions">
                            <a href="{{ route('kelas.show', $k) }}" class="sw-btn view"><i class="fas fa-eye"></i>
                                Detail</a>
                            <a href="{{ route('kelas.edit', $k) }}" class="sw-btn edit"><i class="fas fa-pen"></i> Edit</a>
                            <button type="button" class="sw-btn del"
                                onclick="swDelete('{{ route('kelas.destroy', $k) }}', '{{ addslashes($k->nama_kelas) }}')">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($kelas->hasPages())
                <div class="sw-pager">
                    @if (!$kelas->onFirstPage())
                        <a href="{{ $kelas->previousPageUrl() }}" class="sw-page-chip">← Sebelumnya</a>
                    @endif
                    <span class="sw-page-chip active">{{ $kelas->currentPage() }} / {{ $kelas->lastPage() }}</span>
                    @if ($kelas->hasMorePages())
                        <a href="{{ $kelas->nextPageUrl() }}" class="sw-page-chip">Berikutnya →</a>
                    @endif
                </div>
            @endif
        @else
            <div class="sw-empty">
                <div class="sw-empty-ico"><i class="fas fa-school"></i></div>
                <h3>Belum ada data kelas</h3>
                <p>Mulai tambahkan data kelas untuk mengorganisir siswa.</p>
                <a href="{{ route('kelas.create') }}" class="sw-btn-start">
                    <i class="fas fa-plus"></i> Tambah Kelas Pertama
                </a>
            </div>
        @endif

        {{-- FAB --}}
        <a href="{{ route('kelas.create') }}" class="sw-fab">
            <i class="fas fa-plus"></i> Tambah Kelas
        </a>

    </div>

    <form id="swDeleteForm" method="POST" style="display:none;">
        @csrf @method('DELETE')
    </form>
@endsection

@push('scripts')
    <script>
        function swDelete(url, nama) {
            if (typeof Swal === 'undefined') {
                if (!confirm('Yakin menghapus ' + nama + '?')) return;
                _swSubmitDelete(url);
                return;
            }
            Swal.fire({
                title: 'Hapus Kelas?',
                html: 'Yakin menghapus <strong>' + nama +
                    '</strong>?<br><small style="color:#64748b;">Data tidak dapat dikembalikan.</small>',
                icon: 'warning',
                showCancelButton: true,
                reverseButtons: true,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'sw-page .sw-btn del',
                    cancelButton: 'sw-page .sw-btn view'
                },
                confirmButtonText: '<i class="fas fa-trash-alt"></i>&nbsp; Hapus',
                cancelButtonText: '<i class="fas fa-times"></i>&nbsp; Batal',
            }).then(r => {
                if (r.isConfirmed) _swSubmitDelete(url);
            });
        }

        function _swSubmitDelete(url) {
            const f = document.getElementById('swDeleteForm');
            f.action = url;
            f.submit();
        }
    </script>
@endpush
