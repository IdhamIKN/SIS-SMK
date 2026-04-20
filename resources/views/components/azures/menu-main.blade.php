{{--
    Komponen: Side Menu (Slide dari kanan)
    Penggunaan: @include('components.azures.menu-main')
    Dipanggil via: data-menu="menu-main" di header
--}}

<div id="menu-main"
     class="menu menu-box-right menu-box-detached rounded-m"
     data-menu-width="260"
     data-menu-effect="menu-over">

    {{-- Header Menu --}}
    <div class="menu-header">
        <a href="#" data-toggle-theme class="border-right-0">
            <i class="fa font-12 color-yellow-dark fa-lightbulb"></i>
        </a>
        <a href="#" data-menu="menu-highlights" class="border-right-0">
            <i class="fa font-12 color-green-dark fa-brush"></i>
        </a>
        <a href="#" class="close-menu border-right-0">
            <i class="fa font-12 color-red-dark fa-times"></i>
        </a>
    </div>

    {{-- Logo / Avatar --}}
    <div class="menu-logo text-center">
        @if(auth()->user()?->foto)
            <a href="#">
                <img class="rounded-circle shadow-l"
                     width="80"
                     src="{{ Storage::url(auth()->user()->foto) }}"
                     alt="Foto Profil">
            </a>
        @else
            <a href="#">
                <div class="icon icon-xxl rounded-circle bg-highlight shadow-l mx-auto">
                    <i class="fas fa-user font-30 color-white"></i>
                </div>
            </a>
        @endif
        <h1 class="pt-3 font-700 font-20">{{ auth()->user()?->name ?? 'Pengguna' }}</h1>
        <p class="font-11 mt-n2 opacity-60">
            {{ auth()->user()?->getRoleNames()->first() ?? '' }}
        </p>
    </div>

    {{-- Menu Items --}}
    <div class="menu-items mb-4">

        {{-- ===== MENU SEMUA ROLE ===== --}}
        <h5 class="text-uppercase opacity-20 font-12 pl-3">Menu Utama</h5>

        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active-nav' : '' }}">
            <i data-feather="home" data-feather-line="1" data-feather-size="16"
               data-feather-color="blue-dark" data-feather-bg="blue-fade-light"></i>
            <span>Dashboard</span>
            <i class="fa fa-angle-right"></i>
        </a>

        {{-- ===== MENU SISWA ===== --}}
        @hasrole('siswa')
        <a href="{{ route('absen.index') }}" class="{{ request()->routeIs('absen.*') ? 'active-nav' : '' }}">
            <i data-feather="check-circle" data-feather-line="1" data-feather-size="16"
               data-feather-color="green-dark" data-feather-bg="green-fade-light"></i>
            <span>Absen</span>
            <i class="fa fa-angle-right"></i>
        </a>
        <a href="#" class="">
            <i data-feather="log-out" data-feather-line="1" data-feather-size="16"
               data-feather-color="orange-dark" data-feather-bg="orange-fade-light"></i>
            <span>Absen Pulang</span>
            <i class="fa fa-angle-right"></i>
        </a>
        <a href="#" class="">
            <i data-feather="bar-chart-2" data-feather-line="1" data-feather-size="16"
               data-feather-color="brown-dark" data-feather-bg="brown-fade-light"></i>
            <span>Rekap Kehadiran</span>
            <i class="fa fa-angle-right"></i>
        </a>
        @endhasrole

        {{-- ===== MENU ADMIN / STAFF ===== --}}
        @hasanyrole('superadmin|admin_tatib|bk|waka|kepala_sekolah|gtk')
        <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Manajemen</h5>

        <a href="{{ route('siswa.index') }}" class="{{ request()->routeIs('siswa.*') ? 'active-nav' : '' }}">
            <i data-feather="users" data-feather-line="1" data-feather-size="16"
               data-feather-color="blue-dark" data-feather-bg="blue-fade-light"></i>
            <span>Data Siswa</span>
            <i class="fa fa-angle-right"></i>
        </a>

        <a href="{{ route('gtk.index') }}" class="{{ request()->routeIs('gtk.*') ? 'active-nav' : '' }}">
            <i data-feather="briefcase" data-feather-line="1" data-feather-size="16"
               data-feather-color="teal-dark" data-feather-bg="teal-fade-light"></i>
            <span>Data GTK</span>
            <i class="fa fa-angle-right"></i>
        </a>

        <a href="#" class="">
            <i data-feather="grid" data-feather-line="1" data-feather-size="16"
               data-feather-color="purple-dark" data-feather-bg="purple-fade-light"></i>
            <span>Data Kelas</span>
            <i class="fa fa-angle-right"></i>
        </a>

        <a href="#" class="">
            <i data-feather="check-square" data-feather-line="1" data-feather-size="16"
               data-feather-color="green-dark" data-feather-bg="green-fade-light"></i>
            <span>Absensi</span>
            <i class="fa fa-angle-right"></i>
        </a>

        <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Laporan</h5>

        <a href="#" class="">
            <i data-feather="file-text" data-feather-line="1" data-feather-size="16"
               data-feather-color="brown-dark" data-feather-bg="brown-fade-light"></i>
            <span>Laporan Kehadiran</span>
            <i class="fa fa-angle-right"></i>
        </a>

        <a href="#" class="">
            <i data-feather="calendar" data-feather-line="1" data-feather-size="16"
               data-feather-color="red-dark" data-feather-bg="red-fade-light"></i>
            <span>Rekap Bulanan</span>
            <i class="fa fa-angle-right"></i>
        </a>
        @endhasanyrole

        {{-- ===== MENU SUPERADMIN ===== --}}
        @hasrole('superadmin')
        <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Sistem</h5>
        <a href="{{ url('/log-viewer') }}" target="_blank">
            <i data-feather="terminal" data-feather-line="1" data-feather-size="16"
               data-feather-color="dark-dark" data-feather-bg="gray-fade-light"></i>
            <span>Log Viewer</span>
            <i class="fa fa-angle-right"></i>
        </a>
        @endhasrole

        {{-- ===== SELALU ADA ===== --}}
        <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Akun</h5>

        <a href="#" class="">
            <i data-feather="user" data-feather-line="1" data-feather-size="16"
               data-feather-color="magenta-dark" data-feather-bg="magenta-fade-light"></i>
            <span>Profil Saya</span>
            <i class="fa fa-angle-right"></i>
        </a>

        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i data-feather="log-out" data-feather-line="1" data-feather-size="16"
               data-feather-color="red-dark" data-feather-bg="red-fade-light"></i>
            <span>Keluar</span>
            <i class="fa fa-angle-right"></i>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>

        <a href="#" class="close-menu">
            <i data-feather="x" data-feather-line="3" data-feather-size="16"
               data-feather-color="red-dark" data-feather-bg="red-fade-dark"></i>
            <span>Tutup Menu</span>
            <i class="fa fa-circle"></i>
        </a>

    </div>

</div>