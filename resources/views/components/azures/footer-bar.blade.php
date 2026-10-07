<div id="footer-bar" class="footer-bar-5">

    @hasrole('siswa')
        {{-- Nav untuk Siswa --}}
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active-nav' : '' }}">
            <i data-feather="home" data-feather-line="1" data-feather-size="21" data-feather-color="blue-dark"
                data-feather-bg="blue-fade-light"></i>
            <span>Beranda</span>
        </a>
        {{-- <a href="{{ route('siswa.izin.index') }}" class="{{ request()->routeIs('siswa.izin.*') ? 'active-nav' : '' }}">
            <i data-feather="file-text" data-feather-line="1" data-feather-size="21" data-feather-color="teal-dark"
                data-feather-bg="teal-fade-light"></i>
            <span>Izin</span>
        </a> --}}
        <a href="{{ route('absen.index') }}" class="{{ request()->routeIs('absen.*') ? 'active-nav' : '' }}">
            <i data-feather="user-check" data-feather-line="1" data-feather-size="21" data-feather-color="green-dark"
                data-feather-bg="green-fade-light"></i>
            <span>Absen</span>
        </a>
        <!-- Event & Profil tetap -->
        <a href="{{ route('event.index') }}" class="{{ request()->routeIs('event.*') ? 'active-nav' : '' }}">
            <i data-feather="calendar" data-feather-line="1" data-feather-size="21" data-feather-color="red-dark"
                data-feather-bg="red-fade-light"></i>
            <span>Event</span>
        </a>
        <a href="{{ route('profile.index') }}" class="{{ request()->routeIs('profile.*') ? 'active-nav' : '' }}">
            <i data-feather="user" data-feather-line="1" data-feather-size="21" data-feather-color="brown-dark"
                data-feather-bg="brown-fade-light"></i>
            <span>Profil</span>
        </a>
    @else
        {{-- Nav untuk Staff / Admin --}}
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active-nav' : '' }}">
            <i data-feather="home" data-feather-line="1" data-feather-size="21" data-feather-color="blue-dark"
                data-feather-bg="blue-fade-light"></i>
            <span>Dashboard</span>
        </a>

        <!-- Siswa, Event, Roles, Profil tetap sama -->
        @php
            $isGtkUser = auth()->user()->gtk !== null;
        @endphp
        @if ($isGtkUser)
            {{-- Guru: arahkan ke event guru --}}
            <a href="{{ route('event-guru.index') }}"
                class="{{ request()->routeIs('event-guru.*') ? 'active-nav' : '' }}">
                <i data-feather="calendar" data-feather-line="1" data-feather-size="21" data-feather-color="red-dark"
                    data-feather-bg="red-fade-light"></i>
                <span>Event</span>
            </a>
        @else
            {{-- Admin / non-GTK: event siswa --}}
            <a href="{{ route('event.index') }}"
                class="{{ request()->routeIs('event.*') && !request()->routeIs('event-guru.*') ? 'active-nav' : '' }}">
                <i data-feather="calendar" data-feather-line="1" data-feather-size="21" data-feather-color="red-dark"
                    data-feather-bg="red-fade-light"></i>
                <span>Event</span>
            </a>
        @endif
        <a href="{{ route('siswa.index') }}" class="{{ request()->routeIs('siswa.*') ? 'active-nav' : '' }}">
            <i data-feather="users" data-feather-line="1" data-feather-size="21" data-feather-color="violet-dark"
                data-feather-bg="violet-fade-light"></i>
            <span>Siswa</span>
        </a>
        @hasrole('superadmin')
            <a href="{{ route('gtk.index') }}"
                class="{{ request()->routeIs('gtk.index.*') || request()->routeIs('gtk.index.*') ? 'active-nav' : '' }}">
                <i data-feather="briefcase" data-feather-line="1" data-feather-size="21" data-feather-color="orange-dark"
                    data-feather-bg="orange-fade-light"></i>
                <span>GTK</span>
            </a>
        @else

        @endhasrole

        <a href="{{ route('profile.index') }}" class="{{ request()->routeIs('profile.*') ? 'active-nav' : '' }}">
            <i data-feather="settings" data-feather-line="1" data-feather-size="21" data-feather-color="slate-dark"
                    data-feather-bg="slate-fade-light"></i>
            <span>Profil</span>
        </a>

    @endhasrole

</div>
