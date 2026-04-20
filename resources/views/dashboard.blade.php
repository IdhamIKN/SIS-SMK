@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    {{-- Greeting Header --}}
    <div class="page-title page-title-large">
        <h2 data-username="{{ auth()->user()->name }}" class="greeting-text"></h2>
        <a href="#" data-menu="menu-main"
           class="bg-fade-highlight-light shadow-xl preload-img"
           data-src="{{ auth()->user()->foto ? Storage::url(auth()->user()->foto) : asset('azures/images/avatars/5s.png') }}">
        </a>
    </div>

    {{-- Banner Card --}}
    @include('components.azures.card-header', [
        'image' => asset('azures/images/pictures/20s.jpg'),
        'height' => 210
    ])

    {{-- Info Cards Row --}}
    <div class="content mt-0">
        <div class="row mb-0">
            <div class="col-6">
                @include('components.azures.stat-card', [
                    'icon' => 'fas fa-user-check',
                    'title' => 'Hadir Hari Ini',
                    'value' => $statHadir ?? 1156,
                    'color' => 'green',
                    'showProgress' => true,
                    'progressValue' => 85
                ])
            </div>
            <div class="col-6">
                @include('components.azures.stat-card', [
                    'icon' => 'fas fa-user-times',
                    'title' => 'Alfa Bulan Ini',
                    'value' => $statAlfa ?? 23,
                    'color' => 'red',
                    'showProgress' => true,
                    'progressValue' => 15
                ])
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="content">
        <h6 class="font-700 mb-n1 text-uppercase font-12">Akses Cepat</h6>
    </div>

    <div class="content mt-0">
        <div class="row">
@include('components.azures.icon-grid-item', [
                'href' => route('absen.index'),
                'icon' => 'clipboard',
                'label' => 'Absensi',
                'color' => 'green'
            ])
            @include('components.azures.icon-grid-item', [
                'href' => route('siswa.index'),
                'icon' => 'users',
                'label' => 'Siswa',
                'color' => 'blue'
            ])
            @include('components.azures.icon-grid-item', [
                'href' => '#',
                'icon' => 'bar-chart-2',
                'label' => 'Laporan',
                'color' => 'orange'
            ])
            @include('components.azures.icon-grid-item', [
                'href' => '#',
                'icon' => 'grid',
                'label' => 'Kelas',
                'color' => 'purple'
            ])
            @include('components.azures.icon-grid-item', [
                'href' => route('gtk.index'),
                'icon' => 'user-check',
                'label' => 'GTK',
                'color' => 'teal'
            ])
            @include('components.azures.icon-grid-item', [
                'href' => '#',
                'icon' => 'calendar',
                'label' => 'Jadwal',
                'color' => 'brown'
            ])
        </div>
    </div>

    {{-- Aktivitas Terbaru --}}
    @include('components.azures.timeline', [
        'title' => 'Aktivitas Terbaru',
        'items' => $aktivitas ?? [
            [
                'icon' => 'fas fa-user-plus',
                'title' => 'Siswa baru terdaftar',
                'subtitle' => 'Ahmad Siswa berhasil mendaftar',
                'time' => '2 jam yang lalu',
                'color' => 'green'
            ],
            [
                'icon' => 'fas fa-clipboard-check',
                'title' => 'Absen berhasil',
                'subtitle' => '23 siswa sudah absen hari ini',
                'time' => '1 jam yang lalu',
                'color' => 'blue'
            ],
            [
                'icon' => 'fas fa-user-edit',
                'title' => 'Data GTK diperbarui',
                'subtitle' => 'Data guru Budi Santoso telah diperbarui',
                'time' => '3 jam yang lalu',
                'color' => 'orange'
            ]
        ],
        'emptyMessage' => 'Belum ada aktivitas hari ini'
    ])

@endsection