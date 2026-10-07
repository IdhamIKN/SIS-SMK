@extends('layouts.app')

@section('title', 'Kelas Diarsipkan')

@push('styles')
    @include('components.izin-styles')
    <style>
        .arsip-page {
            padding-bottom: calc(var(--footer-h, 60px) + 84px);
        }

        .arsip-strip {
            padding: 20px 20px 28px;
            background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 62%, #0ea5e9 100%);
            position: relative;
            overflow: hidden;
        }

        .arsip-strip h2 {
            font-size: 1.3rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .arsip-strip p {
            font-size: .8rem;
            color: rgba(255, 255, 255, .72);
            margin: 0;
        }

        .arsip-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255, 255, 255, .85);
            font-size: .8rem;
            font-weight: 700;
            text-decoration: none;
            margin-bottom: 12px;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.2);
            padding: 4px 12px;
            border-radius: 20px;
        }

        .arsip-filter {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 16px;
            margin: 14px 16px 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }

        .arsip-search {
            position: relative;
        }

        .arsip-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: .85rem;
            pointer-events: none;
        }

        .arsip-input {
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
        }

        .arsip-input:focus {
            border-color: #1d4ed8;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(29,78,216,.1);
        }

        .arsip-filter-btn {
            width: 100%;
            margin-top: 8px;
            padding: 11px 16px;
            background: #1d4ed8;
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

        .arsip-list {
            padding: 0 16px;
        }

        .arsip-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            margin-bottom: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
            overflow: hidden;
            opacity: .92;
        }

        .arsip-card-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px 10px;
        }

        .arsip-avatar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #dbeafe;
            color: #1d4ed8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .arsip-name {
            margin: 0 0 2px;
            font-size: .92rem;
            font-weight: 800;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #64748b;
        }

        .arsip-sub {
            margin: 0;
            font-size: .72rem;
            color: #94a3b8;
        }

        .arsip-badge {
            font-size: .65rem;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
            background: #fef3c7;
            color: #92400e;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .arsip-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            font-size: .75rem;
            color: #94a3b8;
            padding: 0 16px;
        }

        .arsip-meta span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .arsip-actions {
            display: flex;
            gap: 8px;
            padding: 10px 16px 14px;
        }

        .arsip-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            white-space: nowrap;
        }

        .arsip-btn.restore {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .arsip-empty {
            padding: 50px 20px;
            text-align: center;
        }

        .arsip-empty i {
            font-size: 3rem;
            color: #bfdbfe;
            margin-bottom: 12px;
        }

        .arsip-empty h3 {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .arsip-empty p {
            font-size: .84rem;
            color: #64748b;
        }

        .arsip-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 16px 0;
        }

        .arsip-page-chip {
            padding: 7px 14px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 700;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #475569;
            text-decoration: none;
        }

        .arsip-page-chip.active {
            background: #1d4ed8;
            color: #fff;
            border-color: #1d4ed8;
        }

        .arsip-count-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.2);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 600;
            color: rgba(255,255,255,.92);
            margin-bottom: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="arsip-page">
        <div class="arsip-strip">
            <a href="{{ route('kelas.index') }}" class="arsip-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Kelas
            </a>
            <div class="arsip-count-chip">
                <i class="fas fa-archive"></i> {{ $kelas->total() }} kelas diarsipkan
            </div>
            <h2><i class="fas fa-archive"></i> Kelas Diarsipkan</h2>
            <p>Kelas yang diarsipkan tidak tampil di daftar aktif. Data tetap tersimpan dan bisa dipulihkan.</p>
        </div>

        <div class="arsip-filter">
            <form method="GET" action="{{ route('kelas.archived') }}">
                <div class="arsip-search">
                    <i class="fas fa-search"></i>
                    <input class="arsip-input" type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama kelas...">
                </div>
                <button type="submit" class="arsip-filter-btn"><i class="fas fa-search"></i> Cari</button>
            </form>
        </div>

        @if ($kelas->count() > 0)
            <div class="arsip-list">
                @foreach ($kelas as $k)
                    <div class="arsip-card">
                        <div class="arsip-card-head">
                            <div class="arsip-avatar"><i class="fas fa-school"></i></div>
                            <div style="flex:1;min-width:0;">
                                <h3 class="arsip-name">{{ $k->nama_kelas }}</h3>
                                <p class="arsip-sub">Tingkat {{ $k->tingkat }}</p>
                            </div>
                            <span class="arsip-badge">
                                <i class="fas fa-archive"></i> Arsip
                            </span>
                        </div>
                        <div class="arsip-meta">
                            <span><i class="fas fa-graduation-cap"></i> {{ $k->jurusan?->nama_jurusan ?? '-' }}</span>
                            <span><i class="fas fa-user-tie"></i> {{ $k->waliKelas?->nama_lengkap ?? 'Belum ada wali' }}</span>
                            <span><i class="fas fa-users"></i> {{ $k->siswa_count }} siswa</span>
                            <span><i class="fas fa-calendar-times"></i> Diarsipkan {{ $k->deleted_at->diffForHumans() }}</span>
                        </div>
                        <div class="arsip-actions">
                            <button type="button" class="arsip-btn restore"
                                onclick="confirmRestore('{{ route('kelas.restore', $k->id) }}', '{{ addslashes($k->nama_kelas) }}')">
                                <i class="fas fa-undo-alt"></i> Pulihkan
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($kelas->hasPages())
                <div class="arsip-pagination">
                    @if (! $kelas->onFirstPage())
                        <a href="{{ $kelas->previousPageUrl() }}" class="arsip-page-chip">Sebelumnya</a>
                    @endif
                    <span class="arsip-page-chip active">{{ $kelas->currentPage() }} / {{ $kelas->lastPage() }}</span>
                    @if ($kelas->hasMorePages())
                        <a href="{{ $kelas->nextPageUrl() }}" class="arsip-page-chip">Berikutnya</a>
                    @endif
                </div>
            @endif
        @else
            <div class="arsip-empty">
                <i class="fas fa-archive"></i>
                <h3>Tidak ada kelas diarsipkan</h3>
                <p>Semua kelas masih aktif. Kelas yang diarsipkan akan muncul di sini.</p>
            </div>
        @endif
    </div>

    <form id="restoreForm" method="POST" style="display:none;">
        @csrf
    </form>
@endsection

@push('scripts')
    <script>
        function confirmRestore(url, nama) {
            Swal.fire({
                title: 'Pulihkan Kelas?',
                html: 'Kelas <strong>' + nama + '</strong> akan dipulihkan dan aktif kembali.',
                icon: 'question',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonColor: '#15803d',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '<i class="fas fa-undo-alt"></i> Pulihkan',
                cancelButtonText: 'Batal',
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.getElementById('restoreForm');
                    form.action = url;
                    form.submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: '{{ addslashes(session('success')) }}',
                    confirmButtonColor: '#1d4ed8',
                    timer: 3000,
                    timerProgressBar: true,
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: '{{ addslashes(session('error')) }}',
                    confirmButtonColor: '#1d4ed8',
                });
            @endif
        });
    </script>
@endpush
