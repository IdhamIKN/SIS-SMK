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
    <link rel="icon" type="image/x-icon"  href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="96x96"   href="/azures/app/icons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/azures/app/icons/icon-192x192.png">

    {{-- PWA: Web App Manifest & theme color --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#133db2">
    <meta name="msapplication-TileColor" content="#7c3aed">
    <meta name="msapplication-TileImage" content="/azures/app/icons/icon-144x144.png">

    {{-- iOS Safari --}}
    <link rel="apple-touch-icon" sizes="180x180" href="/azures/app/icons/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-title" content="EduTech">

    <title>@yield('title', 'Login') — {{ sekolah_data()['system_name'] ?? config('app.name', 'SIS') }}</title>

    <link rel="stylesheet" href="{{ asset('azures/styles/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('azures/styles/style.css') }}">
    <link
        href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900|Roboto:300,300i,400,400i,500,500i,700,700i,900,900i&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('azures/fonts/css/fontawesome-all.min.css') }}">

    @stack('styles')

    {{-- ═══ PWA Guard: redirect ke /pwa-install jika belum diinstall ═══ --}}
    {{-- Dijalankan di <head> agar redirect terjadi sebelum halaman render --}}
    <script>
        (function () {
            'use strict';
            var PWA_KEY     = 'EduTech-PWA-Prompt';
            var SKIP_KEY    = 'EduTech-PWA-Skipped';

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
</head>

<body class="theme-light">

    <div id="preloader">
        <div class="spinner-border color-highlight" role="status"></div>
    </div>

    <div id="page">

        {{-- Header minimalis untuk halaman auth --}}
        <div class="header header-fixed header-auto-show header-logo-app">
            <a href="#" class="header-title">{{ sekolah_data()['system_name'] ?? config('app.name', 'SIS') }}</a>
            <a href="#" data-toggle-theme class="header-icon header-icon-2 show-on-theme-dark"><i
                    class="fas fa-sun"></i></a>
            <a href="#" data-toggle-theme class="header-icon header-icon-2 show-on-theme-light"><i
                    class="fas fa-moon"></i></a>
        </div>

        <div class="page-content">
            @yield('content')
        </div>

    </div>

    <script src="{{ asset('azures/scripts/bootstrap.min.js') }}"></script>
    <script src="{{ asset('azures/scripts/custom.js') }}"></script>

    @stack('scripts')

</body>

</html>
