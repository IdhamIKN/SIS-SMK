<header class="header header-fixed header-auto-show header-logo-app">

    {{-- Judul / Logo --}}
    <a href="{{ route('dashboard') }}" class="header-title" title="{{ $headerTitle ?? config('app.name', 'SIS') }}">
        {{ $headerTitle ?? config('app.name', 'SIS') }}
    </a>

    {{-- Tombol Back (opsional) --}}
    @if(isset($showBack) && $showBack)
        <a href="#" data-back-button class="header-icon header-icon-1" title="Kembali">
            <i class="fas fa-arrow-left"></i>
        </a>
    @else
        {{-- Tombol Menu --}}
        <a href="#" data-menu="menu-main" class="header-icon header-icon-1" title="Menu">
            <i class="fas fa-bars"></i>
        </a>
    @endif

    {{-- Toggle Dark Mode
    <a href="#" data-toggle-theme class="header-icon header-icon-2 show-on-theme-dark" title="Mode Terang">
        <i class="fas fa-sun"></i>
    </a>
    <a href="#" data-toggle-theme class="header-icon header-icon-2 show-on-theme-light" title="Mode Gelap">
        <i class="fas fa-moon"></i>
    </a> --}}

    {{-- Notifikasi (opsional) --}}
    @hasanyrole('superadmin|admin_tatib|bk')
        <a href="#" class="header-icon header-icon-3 position-relative" title="Notifikasi">
            <i class="fas fa-bell"></i>
            <span class="notification-badge badge-medium bg-red-dark color-white">
                3
            </span>
        </a>
    @endhasanyrole

    {{-- Avatar / Profile --}}
    {{-- <a href="#" data-menu="menu-main" class="header-icon header-icon-4 ms-auto" title="Profil">
        @if(auth()->user()?->avatar)
            <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="Avatar" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
        @else
            <div class="bg-highlight rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                <i class="fas fa-user font-16 color-white"></i>
            </div>
        @endif
    </a> --}}

</header>