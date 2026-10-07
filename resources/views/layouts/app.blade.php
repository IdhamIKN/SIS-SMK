<!DOCTYPE HTML>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ═══ Favicon & App Icons ═══ --}}
    {{-- Favicon standar — semua browser desktop --}}
    <link rel="icon" type="image/x-icon"  href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="96x96"   href="/azures/app/icons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/azures/app/icons/icon-192x192.png">

    {{-- PWA: Web App Manifest & theme color --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#133db2">
    <meta name="msapplication-TileColor" content="#133db2">
    <meta name="msapplication-TileImage" content="/azures/app/icons/icon-144x144.png">

    {{-- iOS Safari: icon & splash screens --}}
    <link rel="apple-touch-icon" sizes="180x180" href="/azures/app/icons/apple-touch-icon.png">
    <link rel="apple-touch-startup-image" media="(device-width: 375px) and (device-height: 667px)"
        href="/azures/app/splash/iphoneregular.png">
    <link rel="apple-touch-startup-image" media="(device-width: 414px) and (device-height: 736px)"
        href="/azures/app/splash/iphoneplus.png">
    <link rel="apple-touch-startup-image" media="(device-width: 375px) and (device-height: 812px)"
        href="/azures/app/splash/iphonexs.png">
    <link rel="apple-touch-startup-image" media="(device-width: 414px) and (device-height: 896px)"
        href="/azures/app/splash/iphonexr.png">
    <link rel="apple-touch-startup-image" media="(device-width: 390px) and (device-height: 844px)"
        href="/azures/app/splash/iphonexsmax.png">
    <meta name="apple-mobile-web-app-title" content="EduTech">

    <title>@yield('title', config('app.name', 'EduTech'))</title>

    {{-- Azures Core CSS --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('azures/styles/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('azures/styles/style.css') }}">

    {{-- Google Fonts --}}
    <link
        href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900|Roboto:300,300i,400,400i,500,500i,700,700i,900,900i&display=swap"
        rel="stylesheet">

    {{-- FontAwesome --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('azures/fonts/css/fontawesome-all.min.css') }}">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    {{-- Di <head> --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- Sebelum </body>, setelah jQuery --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    {{-- Global fix: pastikan footer navigasi selalu di atas action bar --}}
    <style>
        #footer-bar {
            z-index: 100 !important;
        }
    </style>

    {{-- Slot untuk CSS tambahan per halaman --}}
    @stack('styles')
    @vite('resources/js/app.js')

    {{-- ═══ PWA Guard: redirect ke /pwa-install jika belum diinstall ═══ --}}
    {{-- Hanya berlaku untuk user yang belum login (tamu) --}}
    @guest
    <script>
        (function () {
            'use strict';
            var PWA_KEY  = 'EduTech-PWA-Prompt';
            var SKIP_KEY = 'EduTech-PWA-Skipped';

            // Jangan redirect kalau sudah berada di halaman /pwa-install atau /offline
            var path = window.location.pathname;
            if (path === '/pwa-install' || path === '/offline') return;

            // Cek apakah sudah berjalan sebagai PWA
            var displayModes = ['standalone', 'fullscreen', 'minimal-ui'];
            var isInstalled  = displayModes.some(function (m) {
                return window.matchMedia('(display-mode: ' + m + ')').matches;
            });
            if (window.navigator.standalone === true) isInstalled = true;
            if (localStorage.getItem(PWA_KEY) === 'installed') isInstalled = true;

            // Sudah terinstall → tidak perlu redirect
            if (isInstalled) return;

            // User sudah memilih "Lanjutkan tanpa install" → tidak redirect lagi
            if (localStorage.getItem(SKIP_KEY) === '1') return;

            // Belum PWA, belum skip → arahkan ke halaman install
            window.location.replace('/pwa-install');
        })();
    </script>
    @endguest
</head>

<body class="theme-light" data-highlight="blue2">

    {{-- ══ Impersonate Banner — hanya muncul saat developer sedang impersonate ══ --}}
    @if(auth()->check() && auth()->user()->isImpersonated())
    <div id="impersonate-banner" style="
        position: fixed;
        bottom: 60px;
        left: 0; right: 0;
        z-index: 99999;
        background: #dc3545;
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        text-align: center;
        padding: 6px 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 -2px 8px rgba(0,0,0,.25);
        letter-spacing: .3px;
    ">
        <span>
            🛠 Impersonate aktif sebagai
            <strong>{{ auth()->user()->name }}</strong>
            ({{ auth()->user()->role_utama }})
        </span>
        <form method="POST" action="{{ route('developer.impersonate.leave') }}" style="margin:0;">
            @csrf
            <button type="submit" style="
                background:#fff;
                color:#dc3545;
                border:none;
                border-radius:4px;
                padding:2px 10px;
                font-size:11px;
                font-weight:700;
                cursor:pointer;
                line-height:1.6;
            ">⏹ Stop</button>
        </form>
    </div>
    @endif

    {{-- Preloader --}}
    <div id="preloader">
        <div class="spinner-border color-highlight" role="status"></div>
    </div>

    <div id="page">

        {{-- ===== HEADER ===== --}}
        @include('components.azures.header')

        {{-- ===== FOOTER BAR (bottom navigation) ===== --}}
        @include('components.azures.footer-bar')

        {{-- ===== MAIN CONTENT ===== --}}
        <div class="page-content">
            {{-- Alert Messages --}}
            @include('components.azures.alerts')

            @yield('content')
        </div>
        {{-- end of page content --}}

        {{-- ===== SIDE MENU (slide dari kanan) ===== --}}
        @include('components.azures.menu-main')

        {{-- ===== MENU HIGHLIGHT / THEME COLOR ===== --}}
        @include('components.azures.menu-highlights')

        {{-- ===== PWA INSTALL PROMPTS — hanya untuk tamu (belum login) ===== --}}
        @guest
        @include('components.azures.pwa-install')
        @endguest

    </div>
    {{-- end of #page --}}

    {{-- ===== SCRIPTS ===== --}}
    <script src="{{ asset('azures/scripts/bootstrap.min.js') }}"></script>
    <script src="{{ asset('azures/scripts/custom.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Feather Icons --}}
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        feather.replace();
    </script>

    {{-- Slot untuk script tambahan per halaman --}}
    @stack('scripts')

    {{-- ===== PWA: deteksi update service worker ===== --}}
    <script>
    (function () {
        if (!('serviceWorker' in navigator)) return;

        navigator.serviceWorker.addEventListener('message', function (event) {
            if (!event.data) return;

            // SW baru aktif → minta user reload agar ikon & aset baru terpakai
            if (event.data.type === 'SW_UPDATED') {
                console.log('[PWA] Service Worker diperbarui ke:', event.data.version);

                // Tampilkan toast notifikasi update (pakai SweetAlert2 yg sudah ada)
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: 'info',
                        title: 'Aplikasi diperbarui!',
                        text: 'Logo & aset baru sudah siap. Halaman akan dimuat ulang...',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true,
                        didClose: function () {
                            window.location.reload(true);
                        }
                    });
                } else {
                    // Fallback jika SweetAlert belum load
                    setTimeout(function () { window.location.reload(true); }, 1500);
                }
            }
        });
    })();
    </script>

</body>

</html>
