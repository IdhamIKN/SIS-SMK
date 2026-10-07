@extends('layouts.app')

@section('title', 'Daftar Kelas')

@push('styles')
    @include('components.izin-styles')
    <style>
        .kelas-page {
            padding-bottom: calc(var(--footer-h, 60px) + 84px);
        }

        .kelas-page .kelas-strip {
            padding: 20px 20px 28px;
            background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 62%, #0ea5e9 100%);
            position: relative;
            overflow: hidden;
        }

        .kelas-page .kelas-date {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .2);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 600;
            color: rgba(255, 255, 255, .92);
            margin-bottom: 10px;
        }

        .kelas-page .kelas-date span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #a7f3d0;
            display: inline-block;
        }

        .kelas-page .kelas-strip h2 {
            font-size: 1.3rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .kelas-page .kelas-strip p {
            font-size: .8rem;
            color: rgba(255, 255, 255, .72);
            margin: 0;
        }

        .kelas-page .kelas-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: .84rem;
            margin: 12px 16px 0;
        }

        .kelas-page .kelas-alert.ok {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .kelas-page .kelas-alert.err {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .kelas-page .kelas-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin: 14px 16px;
        }

        .kelas-page .kelas-stat {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 10px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .kelas-page .kelas-stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            margin: 0 auto 6px;
        }

        .kelas-page .kelas-stat-value {
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1;
        }

        .kelas-page .kelas-stat-label {
            font-size: .62rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .kelas-page .kelas-filter {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 16px;
            margin: 0 16px 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .kelas-page .kelas-search {
            position: relative;
            margin-bottom: 10px;
        }

        .kelas-page .kelas-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: .85rem;
            pointer-events: none;
        }

        .kelas-page .kelas-input,
        .kelas-page .kelas-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            box-sizing: border-box;
        }

        .kelas-page .kelas-input {
            padding-left: 34px;
        }

        .kelas-page .kelas-input:focus,
        .kelas-page .kelas-select:focus {
            border-color: #7c3aed;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .1);
        }

        .kelas-page .kelas-filter-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 10px;
        }

        .kelas-page .kelas-filter-btn {
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
        }

        .kelas-page .kelas-list {
            padding: 0 16px;
        }

        .kelas-page .kelas-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            margin-bottom: 10px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
            overflow: hidden;
        }

        .kelas-page .kelas-card-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px 10px;
        }

        .kelas-page .kelas-avatar {
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
        }

        .kelas-page .kelas-name {
            margin: 0 0 2px;
            font-size: .92rem;
            font-weight: 800;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .kelas-page .kelas-sub {
            margin: 0;
            font-size: .72rem;
            color: #64748b;
        }

        .kelas-page .kelas-badge {
            font-size: .68rem;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
            background: #dcfce7;
            color: #15803d;
            flex-shrink: 0;
        }

        .kelas-page .kelas-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            font-size: .75rem;
            color: #64748b;
            padding: 0 16px;
        }

        .kelas-page .kelas-meta span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .kelas-page .kelas-actions {
            display: flex;
            gap: 8px;
            padding: 10px 16px 14px;
        }

        .kelas-page .kelas-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 12px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            white-space: nowrap;
        }

        .kelas-page .kelas-btn.view {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .kelas-page .kelas-btn.edit {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .kelas-page .kelas-btn.delete {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .kelas-page .kelas-empty {
            padding: 40px 20px;
            text-align: center;
        }

        .kelas-page .kelas-empty i {
            font-size: 3rem;
            color: #c4b5fd;
            margin-bottom: 12px;
        }

        .kelas-page .kelas-empty h3 {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .kelas-page .kelas-empty p {
            font-size: .84rem;
            color: #64748b;
            margin-bottom: 16px;
        }

        .kelas-page .kelas-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 16px 0;
        }

        .kelas-page .kelas-page-chip {
            padding: 7px 14px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 700;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #475569;
            text-decoration: none;
        }

        .kelas-page .kelas-page-chip.active {
            background: #7c3aed;
            color: #fff;
            border-color: #7c3aed;
        }

        .kelas-page .kelas-fab {
            position: fixed;
            bottom: calc(var(--footer-h, 60px) + 16px);
            right: 16px;
            background: #7c3aed;
            color: #fff;
            padding: 13px 20px;
            border-radius: 50px;
            font-size: .875rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            box-shadow: 0 4px 18px rgba(124, 58, 237, .28);
            z-index: 100;
        }

        @media (max-width: 420px) {
            .kelas-page .kelas-stats {
                gap: 8px;
            }

            .kelas-page .kelas-stat {
                padding: 12px 8px;
            }

            .kelas-page .kelas-filter-row {
                grid-template-columns: 1fr;
            }

            .kelas-page .kelas-actions {
                flex-wrap: wrap;
            }
        }
    </style>
@endpush

@section('content')
    <div class="kelas-page">
        <div class="kelas-strip">
            <div class="kelas-date">
                <span></span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <h2><i class="fas fa-school"></i> Daftar Kelas</h2>
            <p>Kelola rombongan belajar, wali kelas, dan guru BK.</p>
        </div>

        {{-- Notifikasi ditampilkan via SweetAlert di @push('scripts') --}}

        <div class="kelas-stats">
            <div class="kelas-stat">
                <div class="kelas-stat-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-school"></i></div>
                <div class="kelas-stat-value" style="color:#7c3aed;">{{ $kelas->total() }}</div>
                <div class="kelas-stat-label">Total Kelas</div>
            </div>
            <div class="kelas-stat">
                <div class="kelas-stat-icon" style="background:#dcfce7;color:#15803d;"><i class="fas fa-users"></i></div>
                <div class="kelas-stat-value" style="color:#15803d;">{{ $kelas->sum('siswa_count') }}</div>
                <div class="kelas-stat-label">Total Siswa</div>
            </div>
            <div class="kelas-stat">
                <div class="kelas-stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-user-tie"></i></div>
                <div class="kelas-stat-value" style="color:#dc2626;">{{ $kelas->whereNotNull('wali_kelas_id')->count() }}</div>
                <div class="kelas-stat-label">Dengan Wali</div>
            </div>
        </div>

        <div class="kelas-filter">
            <form method="GET" action="{{ route('kelas.index') }}">
                <div class="kelas-search">
                    <i class="fas fa-search"></i>
                    <input class="kelas-input" type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama kelas...">
                </div>
                <div class="kelas-filter-row">
                    <select name="jurusan_id" class="kelas-select">
                        <option value="">Semua Jurusan</option>
                        @foreach ($jurusans as $j)
                            <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                    <select name="tingkat" class="kelas-select">
                        <option value="">Semua Tingkat</option>
                        <option value="X" {{ request('tingkat') === 'X' ? 'selected' : '' }}>Kelas X</option>
                        <option value="XI" {{ request('tingkat') === 'XI' ? 'selected' : '' }}>Kelas XI</option>
                        <option value="XII" {{ request('tingkat') === 'XII' ? 'selected' : '' }}>Kelas XII</option>
                    </select>
                </div>
                <button type="submit" class="kelas-filter-btn"><i class="fas fa-search"></i> Cari Kelas</button>
            </form>
        </div>

        @if ($kelas->count() > 0)
            <div class="kelas-list">
                @foreach ($kelas as $k)
                    <div class="kelas-card">
                        <div class="kelas-card-head">
                            <div class="kelas-avatar"><i class="fas fa-school"></i></div>
                            <div style="flex:1;min-width:0;">
                                <h3 class="kelas-name">{{ $k->nama_kelas }}</h3>
                                <p class="kelas-sub">Tingkat {{ $k->tingkat }}</p>
                            </div>
                            <span class="kelas-badge">{{ $k->siswa_count }} Siswa</span>
                        </div>
                        <div class="kelas-meta">
                            <span><i class="fas fa-graduation-cap"></i> {{ $k->jurusan?->nama_jurusan ?? '-' }}</span>
                            <span><i class="fas fa-user-tie"></i> {{ $k->waliKelas?->nama_lengkap ?? 'Belum ada wali' }}</span>
                            @if ($k->bk)
                                <span><i class="fas fa-user-shield"></i> {{ $k->bk->nama_lengkap }}</span>
                            @endif
                        </div>
                        <div class="kelas-actions">
                            <a href="{{ route('kelas.show', $k) }}" class="kelas-btn view"><i class="fas fa-eye"></i> Detail</a>
                            <a href="{{ route('kelas.edit', $k) }}" class="kelas-btn edit"><i class="fas fa-pen"></i> Edit</a>
                            <button type="button" class="kelas-btn delete" onclick="deleteKelas('{{ route('kelas.destroy', $k) }}', '{{ addslashes($k->nama_kelas) }}')">
                                <i class="fas fa-archive"></i> Arsip
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($kelas->hasPages())
                <div class="kelas-pagination">
                    @if (! $kelas->onFirstPage())
                        <a href="{{ $kelas->previousPageUrl() }}" class="kelas-page-chip">Sebelumnya</a>
                    @endif
                    <span class="kelas-page-chip active">{{ $kelas->currentPage() }} / {{ $kelas->lastPage() }}</span>
                    @if ($kelas->hasMorePages())
                        <a href="{{ $kelas->nextPageUrl() }}" class="kelas-page-chip">Berikutnya</a>
                    @endif
                </div>
            @endif
        @else
            <div class="kelas-empty">
                <i class="fas fa-school"></i>
                <h3>Belum ada data kelas</h3>
                <p>Mulai tambahkan data kelas untuk mengorganisir siswa.</p>
                <a href="{{ route('kelas.create') }}" class="kelas-btn view"><i class="fas fa-plus"></i> Tambah Kelas Pertama</a>
            </div>
        @endif

        <a href="{{ route('kelas.create') }}" class="kelas-fab"><i class="fas fa-plus"></i> Tambah Kelas</a>
        <a href="{{ route('kelas.archived') }}" class="kelas-fab" style="right: auto; left: 16px; background: #1d4ed8; box-shadow: 0 4px 18px rgba(29,78,216,.28);">
            <i class="fas fa-archive"></i> Arsip
        </a>
    </div>

    <form id="kelasDeleteForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    <script>
        function submitKelasDelete(url) {
            const form = document.getElementById('kelasDeleteForm');
            form.action = url;
            form.submit();
        }

        function deleteKelas(url, nama) {
            Swal.fire({
                title: 'Arsipkan Kelas?',
                html: 'Kelas <strong>' + nama + '</strong> akan diarsipkan.<br><small style="color:#64748b;">Kelas tidak akan muncul di daftar aktif, tetapi semua data tetap tersimpan.</small>',
                icon: 'warning',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonColor: '#7c3aed',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '<i class="fas fa-archive"></i> Arsipkan',
                cancelButtonText: 'Batal',
            }).then(result => {
                if (result.isConfirmed) {
                    submitKelasDelete(url);
                }
            });
        }

        // Tampilkan notifikasi session via SweetAlert
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: '{{ addslashes(session('success')) }}',
                    confirmButtonColor: '#7c3aed',
                    timer: 3000,
                    timerProgressBar: true,
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: '{{ addslashes(session('error')) }}',
                    confirmButtonColor: '#7c3aed',
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: '{{ addslashes($errors->first()) }}',
                    confirmButtonColor: '#7c3aed',
                });
            @endif
        });
    </script>
@endpush
