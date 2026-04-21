<!DOCTYPE HTML>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'SIS-SMKN 5 Madiun'))</title>

    {{-- Azures Core CSS --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('azures/styles/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('azures/styles/style.css') }}">

    {{-- Google Fonts --}}
    <link
        href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900|Roboto:300,300i,400,400i,500,500i,700,700i,900,900i&display=swap"
        rel="stylesheet">

    {{-- FontAwesome --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('azures/fonts/css/fontawesome-all.min.css') }}">

    {{-- Slot untuk CSS tambahan per halaman --}}
    @stack('styles')

    {{-- SweetAlert2 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    </xai:function_call? <xai:function_call name="edit_file">
    <parameter name="path">sis-app/resources/views/layouts/app.blade.php
</head>

<body class="theme-light" data-highlight="blue2">

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

    </div>
    {{-- end of #page --}}

    {{-- ===== SCRIPTS ===== --}}
    <script src="{{ asset('azures/scripts/bootstrap.min.js') }}"></script>
    <script src="{{ asset('azures/scripts/custom.js') }}"></script>

    {{-- Feather Icons --}}
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        feather.replace();
    </script>

    {{-- Slot untuk script tambahan per halaman --}}
    @stack('scripts')

    {{-- SweetAlert2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </xai:function_call? Wait, multiple edits to same file in parallel. Also update TODO after. But one tool per msg?
        Rules say "Allways prefer to use multiple tools at a time per message" . Proceed with both CDN edits. Exact
        old_str from previous read_file. Previous app.blade.php has: After @stack('styles') before </head>

    And before
</body>:

{{-- Slot untuk script tambahan per halaman --}}
@stack('scripts')

</body>

So exact matches.

After success, next show.blade.php.

Update TODO in next step.

For now, parallel CDN adds. </thinking>

<xai:function_call name="edit_file">
    <parameter name="path">sis-app/resources/views/layouts/app.blade.php

        </body>

</html>
