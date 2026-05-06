{{--
    Komponen: Side Menu (Slide dari kanan)
    Penggunaan: @include('components.azures.menu-main')
    Dipanggil via: data-menu="menu-main" di header
--}}

<div id="menu-main" class="menu menu-box-right menu-box-detached rounded-m" data-menu-width="260"
    data-menu-effect="menu-over">

    {{-- Header Menu --}}
    <div class="menu-header">
        {{-- <a href="#" data-toggle-theme class="border-right-0">
            <i class="fa font-12 color-yellow-dark fa-lightbulb"></i>
        </a>
        <a href="#" data-menu="menu-highlights" class="border-right-0">
            <i class="fa font-12 color-green-dark fa-brush"></i>
        </a> --}}
        <a href="#" class="close-menu border-right-0">
            <i class="fa font-12 color-red-dark fa-times"></i>
        </a>
    </div>

    {{-- Logo / Avatar --}}
    <div class="menu-logo text-center">
        @if (auth()->user()?->avatar)
            <a href="{{ route('profile.index') }}">
                <img class="rounded-circle shadow-l" width="80" src="{{ Storage::url(auth()->user()->avatar) }}"
                    alt="Foto Profil">
            </a>
        @else
            <a href="{{ route('profile.index') }}">
                <div class="icon icon-xxl rounded-circle bg-highlight shadow-l mx-auto">
                    <i class="fas fa-user font-30 color-white"></i>
                </div>
            </a>
        @endif
        <h1 class="pt-2 font-700 font-18">{{ config('app.name', 'SIS SMKN 5 Madiun') }}</h1>
        <h2 class="pt-1 font-600 font-16">{{ auth()->user()?->name ?? 'Pengguna' }}</h2>
        <p class="font-11 mt-n1 opacity-60">
            {{ auth()->user()?->getRoleNames()->first() ?? '' }}
        </p>
    </div>

    {{-- Menu Items --}}
    <div class="menu-items mb-4">

        {{-- ===== MENU SEMUA ROLE ===== --}}
        <h5 class="text-uppercase opacity-20 font-12 pl-3">Menu Utama</h5>

        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active-nav' : '' }}">
            <i data-feather="home" data-feather-line="1" data-feather-size="16" data-feather-color="blue-dark"
                data-feather-bg="blue-fade-light"></i>
            <span>Dashboard</span>
            <i class="fa fa-angle-right"></i>
        </a>

        {{-- ===== MENU SISWA ===== --}}
        @hasrole('siswa')
            <a href="{{ route('absen.index') }}" class="{{ request()->routeIs('absen.index') ? 'active-nav' : '' }}">
                <i data-feather="check-circle" data-feather-line="1" data-feather-size="16" data-feather-color="green-dark"
                    data-feather-bg="green-fade-light"></i>
                <span>Absen</span>
                <i class="fa fa-angle-right"></i>
            </a>
            <a href="{{ route('absen.rekap') }}" class="{{ request()->routeIs('absen.rekap') ? 'active-nav' : '' }}">
                <i data-feather="bar-chart-2" data-feather-line="1" data-feather-size="16" data-feather-color="blue-dark"
                    data-feather-bg="blue-fade-light"></i>
                <span>Rekap Kehadiran</span>
                <i class="fa fa-angle-right"></i>
            </a>
            <a href="{{ route('event.index') }}" class="{{ request()->routeIs('event.*') ? 'active-nav' : '' }}">
                <i data-feather="calendar" data-feather-line="1" data-feather-size="16" data-feather-color="red-dark"
                    data-feather-bg="red-fade-light"></i>
                <span>Event</span>
                <i class="fa fa-angle-right"></i>
            </a>
            <a href="{{ route('siswa.izin.index') }}" class="{{ request()->routeIs('siswa.izin.*') ? 'active-nav' : '' }}">
                <i data-feather="file-text" data-feather-line="1" data-feather-size="16" data-feather-color="teal-dark"
                    data-feather-bg="teal-fade-light"></i>
                <span>Izin</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('siswa.lapor-guru') }}" class="{{ request()->routeIs('siswa.lapor-guru') ? 'active-nav' : '' }}">
                <i data-feather="alert-triangle" data-feather-line="1" data-feather-size="16" data-feather-color="orange-dark"
                    data-feather-bg="orange-fade-light"></i>
                <span>Lapor Guru Tidak Hadir</span>
                <i class="fa fa-angle-right"></i>
            </a>
        @endhasrole

        {{-- ===== MENU ADMIN / STAFF ===== --}}
        @hasanyrole('superadmin|admin_tatib|bk|waka|kepala_sekolah|gtk')
            <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Manajemen</h5>

            <a href="{{ route('siswa.index') }}" class="{{ request()->routeIs('siswa.*') ? 'active-nav' : '' }}">
                <i data-feather="users" data-feather-line="1" data-feather-size="16" data-feather-color="blue-dark"
                    data-feather-bg="blue-fade-light"></i>
                <span>Data Siswa</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('gtk.index') }}" class="{{ request()->routeIs('gtk.*') && !request()->routeIs('kehadiran-guru.*') ? 'active-nav' : '' }}">
                <i data-feather="briefcase" data-feather-line="1" data-feather-size="16" data-feather-color="teal-dark"
                    data-feather-bg="teal-fade-light"></i>
                <span>Data GTK</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('kehadiran-guru.laporan') }}" class="{{ request()->routeIs('kehadiran-guru.*') ? 'active-nav' : '' }}">
                <i data-feather="clipboard-check" data-feather-line="1" data-feather-size="16" data-feather-color="green-dark"
                    data-feather-bg="green-fade-light"></i>
                <span>Laporan Kehadiran</span>
                <i class="fa fa-angle-right"></i>
            </a>


            <a href="{{ route('kelas.index') }}" class="{{ request()->routeIs('kelas.*') ? 'active-nav' : '' }}">
                <i data-feather="grid" data-feather-line="1" data-feather-size="16" data-feather-color="purple-dark"
                    data-feather-bg="purple-fade-light"></i>
                <span>Data Kelas</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('absen.rekap') }}" class="{{ request()->routeIs('absen.rekap') ? 'active-nav' : '' }}">
                <i data-feather="check-square" data-feather-line="1" data-feather-size="16" data-feather-color="green-dark"
                    data-feather-bg="green-fade-light"></i>
                <span>Rekap Absensi Siswa</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Laporan</h5>

            <a href="#" class="">
                <i data-feather="file-text" data-feather-line="1" data-feather-size="16" data-feather-color="brown-dark"
                    data-feather-bg="brown-fade-light"></i>
                <span>Laporan Kehadiran</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('event.index') }}" class="{{ request()->routeIs('event.*') ? 'active-nav' : '' }}">
                <i data-feather="calendar" data-feather-line="1" data-feather-size="16" data-feather-color="red-dark"
                    data-feather-bg="red-fade-light"></i>
                <span>Event & Absensi</span>
                <i class="fa fa-angle-right"></i>
            </a>
        @endhasanyrole

        {{-- ===== MENU SUPERADMIN ===== --}}
        @hasrole('superadmin')
            <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Sistem</h5>
            <a href="{{ route('admin.school-config.index') }}" class="{{ request()->routeIs('admin.school-config.*') ? 'active-nav' : '' }}">
                <i data-feather="settings" data-feather-line="1" data-feather-size="16" data-feather-color="blue-dark"
                    data-feather-bg="blue-fade-light"></i>
                <span>Konfigurasi Sekolah</span>
                <i class="fa fa-angle-right"></i>
            </a>
            <a href="{{ url('/log-viewer') }}" target="_blank">
                <i data-feather="terminal" data-feather-line="1" data-feather-size="16" data-feather-color="dark-dark"
                    data-feather-bg="gray-fade-light"></i>
                <span>Log Viewer</span>
                <i class="fa fa-angle-right"></i>
            </a>
        @endhasrole

        {{-- ===== SELALU ADA ===== --}}
        <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Akun</h5>

        <a href="{{ url('/test-profile') }}" class="{{ request()->routeIs('profile.*') ? 'active-nav' : '' }}"
           style="position: relative; z-index: 9999; pointer-events: auto;">
            <i data-feather="user" data-feather-line="1" data-feather-size="16" data-feather-color="magenta-dark"
                data-feather-bg="magenta-fade-light"></i>
            <span>Profil Saya</span>
            <i class="fa fa-angle-right"></i>
        </a>

        <a href="{{ route('logout') }}"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i data-feather="log-out" data-feather-line="1" data-feather-size="16" data-feather-color="red-dark"
                data-feather-bg="red-fade-light"></i>
            <span>Keluar</span>
            <i class="fa fa-angle-right"></i>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>

        <a href="#" class="close-menu">
            <i data-feather="x" data-feather-line="3" data-feather-size="16" data-feather-color="red-dark"
                data-feather-bg="red-fade-dark"></i>
            <span>Tutup Menu</span>
            <i class="fa fa-circle"></i>
        </a>

    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure profile menu link works
        const profileLink = document.querySelector('a[href="{{ route("profile.index") }}"]');
        if (profileLink) {
            profileLink.addEventListener('click', function(e) {
                e.stopPropagation();
                window.location.href = '{{ route("profile.index") }}';
            }, true); // Use capture phase
        }
    });
</script>
@endpush
