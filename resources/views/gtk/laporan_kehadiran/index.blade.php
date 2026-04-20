@extends('layouts.app')

@section('title', 'Laporan Kehadiran GTK')

@section('content')

    {{-- Page Title --}}
    <div class="page-title page-title-large">
        <h2 data-username="{{ auth()->user()->name }}" class="greeting-text"></h2>
        <a href="#" data-menu="menu-main"
            class="bg-fade-highlight-light shadow-xl preload-img"
            data-src="{{ auth()->user()->avatar ? Storage::url(auth()->user()->avatar) : asset('azures/images/avatars/5s.png') }}">
        </a>
    </div>

    {{-- Header Card --}}
    <div class="card header-card shape-rounded" data-card-height="120">
        <div class="card-overlay bg-highlight opacity-95"></div>
        <div class="card-overlay dark-mode-tint"></div>
        <div class="card-bg preload-img" data-src="{{ asset('azures/images/pictures/20s.jpg') }}"></div>
    </div>

    {{-- Content --}}
    <div class="card card-style">
        <div class="content mb-0">

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="font-700 mb-1">Laporan Kehadiran GTK</h4>
                    <p class="font-12 opacity-70 mb-0">Pantau laporan kehadiran guru</p>
                </div>
                @hasrole('gtk')
                    <a href="{{ route('gtk.laporan-kehadiran.create') }}" class="btn btn-m btn-full bg-highlight rounded-s text-uppercase font-900">
                        <i class="fas fa-plus me-2"></i>Buat Laporan
                    </a>
                @endhasrole
            </div>

            {{-- Stats Cards --}}
            @unlessrole('gtk')
                <div class="row mb-4">
                    <div class="col-6">
                        @include('components.azures.stat-card', [
                            'title' => 'Laporan Hari Ini',
                            'value' => $stats['hari_ini'] ?? 0,
                            'icon' => 'fas fa-calendar-day',
                            'color' => 'blue-dark'
                        ])
                    </div>
                    <div class="col-6">
                        @include('components.azures.stat-card', [
                            'title' => 'Belum Lapor',
                            'value' => $stats['belum_lapor'] ?? 0,
                            'icon' => 'fas fa-clock',
                            'color' => 'orange-dark'
                        ])
                    </div>
                </div>
            @endunlessrole

            {{-- Filter --}}
            <div class="card card-style mb-3">
                <div class="content">
                    <form method="GET" class="row g-2">
                        <div class="col-6">
                            <label class="form-label font-12">Tanggal</label>
                            <input type="date" name="tanggal" value="{{ request('tanggal', now()->toDateString()) }}" class="form-control rounded-s">
                        </div>
                        <div class="col-6">
                            <label class="form-label font-12">Status</label>
                            <select name="status" class="form-control rounded-s">
                                <option value="">Semua Status</option>
                                <option value="hadir" {{ request('status') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="sakit" {{ request('status') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="izin" {{ request('status') === 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="alfa" {{ request('status') === 'alfa' ? 'selected' : '' }}>Alfa</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-m btn-full bg-highlight rounded-s">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- List --}}
            @forelse($laporanKehadiran ?? [] as $laporan)
                @include('components.azures.list-item-large', [
                    'avatar' => $laporan->gtk->foto ? Storage::url($laporan->gtk->foto) : '<div class="bg-blue-dark rounded-s shadow-l d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="fas fa-user-tie font-18 color-white"></i></div>',
                    'title' => $laporan->gtk->nama_lengkap,
                    'subtitle' => $laporan->gtk->kd_guru . ' • ' . $laporan->waktu_laporan->format('d/m/Y H:i'),
                    'meta' => ucfirst($laporan->jenis) . ' • ' . ucfirst($laporan->status),
                    'badge' => ucfirst($laporan->status),
                    'badgeColor' => match($laporan->status) {
                        'hadir' => 'green-dark',
                        'sakit' => 'red-dark',
                        'izin' => 'orange-dark',
                        'alfa' => 'red-dark',
                        default => 'gray-dark'
                    },
                    'showActions' => true,
                    'editUrl' => null,
                    'deleteUrl' => null,
                    'customActions' => [
                        [
                            'url' => route('gtk.laporan-kehadiran.show', $laporan),
                            'icon' => 'fas fa-eye',
                            'text' => 'Lihat Detail',
                            'class' => 'btn btn-sm btn-outline-primary'
                        ]
                    ]
                ])
            @empty
                @include('components.azures.empty-state', [
                    'icon' => 'fas fa-user-tie',
                    'title' => 'Belum ada laporan kehadiran',
                    'message' => 'GTK belum membuat laporan kehadiran untuk periode ini',
                    'action' => auth()->user()->hasRole('gtk') ? '<a href="' . route('gtk.laporan-kehadiran.create') . '" class="btn btn-m bg-highlight rounded-s">Buat Laporan Pertama</a>' : null
                ])
            @endforelse

            {{-- Pagination --}}
            @if(isset($laporanKehadiran) && $laporanKehadiran->hasPages())
                @include('components.azures.pagination', ['paginator' => $laporanKehadiran])
            @endif

        </div>
    </div>

@endsection