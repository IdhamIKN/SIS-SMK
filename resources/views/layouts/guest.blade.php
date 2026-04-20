<!DOCTYPE HTML>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Login') — {{ config('app.name', 'SIS') }}</title>

    <link rel="stylesheet" href="{{ asset('azures/styles/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('azures/styles/style.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900|Roboto:300,300i,400,400i,500,500i,700,700i,900,900i&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('azures/fonts/css/fontawesome-all.min.css') }}">

    @stack('styles')
</head>

<body class="theme-light">

<div id="preloader"><div class="spinner-border color-highlight" role="status"></div></div>

<div id="page">

    {{-- Header minimalis untuk halaman auth --}}
    <div class="header header-fixed header-auto-show header-logo-app">
        <a href="#" class="header-title">{{ config('app.name', 'SIS') }}</a>
        <a href="#" data-toggle-theme class="header-icon header-icon-2 show-on-theme-dark"><i class="fas fa-sun"></i></a>
        <a href="#" data-toggle-theme class="header-icon header-icon-2 show-on-theme-light"><i class="fas fa-moon"></i></a>
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