<!DOCTYPE HTML>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $errorTitle ?? 'Terjadi Kesalahan' }} — {{ config('app.name', 'SIS') }}</title>

    {{-- Azures Core CSS --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('azures/styles/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('azures/styles/style.css') }}">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900&display=swap"
        rel="stylesheet">

    {{-- FontAwesome --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('azures/fonts/css/fontawesome-all.min.css') }}">

    <style>
        /* ────────────────────────────────────────────────
           Global reset ringan agar tidak bentrok Azures
        ──────────────────────────────────────────────── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        /* ── Page wrapper ── */
        .err-page {
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 24px 56px;
            background: #f0f2f8;
            text-align: center;
            transition: background .3s;
        }

        /* ── Glowing blob behind icon ── */
        .err-blob {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 24px;
        }

        .err-blob::before {
            content: '';
            position: absolute;
            inset: -16px;
            border-radius: 50%;
            background: var(--blob-color, rgba(99, 102, 241, .18));
            filter: blur(18px);
            animation: blob-pulse 3s ease-in-out infinite;
        }

        @keyframes blob-pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: .8;
            }

            50% {
                transform: scale(1.12);
                opacity: 1;
            }
        }

        /* ── Icon circle ── */
        .err-icon {
            position: relative;
            width: 96px;
            height: 96px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            color: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .18);
            animation: float-up 3.2s ease-in-out infinite;
        }

        @keyframes float-up {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        /* ── Gradient variants per error type ── */
        .err-icon.v-404 {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            --blob-color: rgba(99, 102, 241, .2);
        }

        .err-icon.v-403 {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --blob-color: rgba(239, 68, 68, .2);
        }

        .err-icon.v-401 {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --blob-color: rgba(245, 158, 11, .2);
        }

        .err-icon.v-419 {
            background: linear-gradient(135deg, #06b6d4 0%, #0284c7 100%);
            --blob-color: rgba(6, 182, 212, .2);
        }

        .err-icon.v-429 {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            --blob-color: rgba(249, 115, 22, .2);
        }

        .err-icon.v-500 {
            background: linear-gradient(135deg, #e84242 0%, #b91c1c 100%);
            --blob-color: rgba(232, 66, 66, .2);
        }

        .err-icon.v-503 {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            --blob-color: rgba(100, 116, 139, .2);
        }

        .err-icon.v-misc {
            background: linear-gradient(135deg, #1f6bff 0%, #6d28d9 100%);
            --blob-color: rgba(31, 107, 255, .2);
        }

        /* ── Error code number ── */
        .err-code {
            font-size: 88px;
            font-weight: 800;
            letter-spacing: -4px;
            line-height: 1;
            margin-bottom: 6px;
            background: linear-gradient(135deg, #1f6bff 0%, #6d28d9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: code-pulse 2.6s ease-in-out infinite;
        }

        @keyframes code-pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .88;
                transform: scale(.975);
            }
        }

        /* ── Divider bar ── */
        .err-divider {
            width: 52px;
            height: 4px;
            border-radius: 4px;
            background: linear-gradient(90deg, #1f6bff, #6d28d9);
            margin: 0 auto 18px;
        }

        /* ── Heading & description ── */
        .err-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 10px;
        }

        .err-desc {
            font-size: 14px;
            color: #64748b;
            max-width: 360px;
            margin: 0 auto 30px;
            line-height: 1.75;
        }

        /* ── Action buttons group ── */
        .err-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            max-width: 440px;
            width: 100%;
        }

        .err-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
        }

        .err-btn:hover {
            transform: translateY(-2px);
            opacity: .92;
        }

        .err-btn:active {
            transform: translateY(0);
            opacity: 1;
        }

        .err-btn-primary {
            background: linear-gradient(135deg, #1f6bff, #6d28d9);
            color: #fff;
            box-shadow: 0 4px 16px rgba(31, 107, 255, .35);
        }

        .err-btn-secondary {
            background: #fff;
            color: #1e293b;
            border: 1.5px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .07);
        }

        .err-btn-outline {
            background: transparent;
            color: #6d28d9;
            border: 1.5px solid #6d28d9;
        }

        /* ── App name watermark ── */
        .err-watermark {
            margin-top: 40px;
            font-size: 12px;
            color: #94a3b8;
            letter-spacing: .5px;
        }

        /* ════════════════════════════════
           Dark mode  (body.theme-dark)
        ════════════════════════════════ */
        body.theme-dark .err-page {
            background: #111827;
        }

        body.theme-dark .err-title {
            color: #f1f5f9;
        }

        body.theme-dark .err-desc {
            color: #94a3b8;
        }

        body.theme-dark .err-btn-secondary {
            background: #1e293b;
            color: #e2e8f0;
            border-color: #334155;
        }

        body.theme-dark .err-watermark {
            color: #475569;
        }
    </style>
</head>

{{-- Body class ikut preferensi Azures yang tersimpan di localStorage --}}

<body class="theme-light">

    <div class="err-page">

        {{-- ── Icon dengan efek blob ── --}}
        <div class="err-blob">
            <div class="err-icon v-{{ $iconClass ?? 'misc' }}">
                <i class="fas fa-{{ $icon ?? 'exclamation-triangle' }}"></i>
            </div>
        </div>

        {{-- ── Kode error besar ── --}}
        <div class="err-code">{{ $statusCode ?? '!!' }}</div>

        {{-- ── Garis pemisah ── --}}
        <div class="err-divider"></div>

        {{-- ── Judul & deskripsi ── --}}
        <h1 class="err-title">{{ $errorTitle ?? 'Terjadi Kesalahan' }}</h1>
        <p class="err-desc">
            {{ $errorMessage ?? 'Maaf, terjadi masalah yang tidak terduga. Silakan coba lagi atau hubungi administrator sistem.' }}
        </p>

        {{-- ── Tombol aksi ── --}}
        <div class="err-actions">

            {{-- Kembali ke halaman sebelumnya --}}

            <a href="{{ url('/') }}" class="err-btn err-btn-secondary" id="backButton">
                <i class="fas fa-arrow-left fa-sm"></i>
                Kembali
            </a>

            @auth
                {{-- Sudah login → ke dashboard --}}
                <a href="{{ route('dashboard') }}" class="err-btn err-btn-primary">
                    <i class="fas fa-home fa-sm"></i>
                    Dashboard
                </a>
            @else
                {{-- Belum login → ke beranda / login --}}
                <a href="{{ url('/') }}" class="err-btn err-btn-primary">
                    <i class="fas fa-home fa-sm"></i>
                    Beranda
                </a>
            @endauth

            @php $code = intval($statusCode ?? 0); @endphp

            @if ($code === 419)
                {{-- Session expired → login ulang --}}
                <a href="{{ route('login') }}" class="err-btn err-btn-outline">
                    <i class="fas fa-sign-in-alt fa-sm"></i>
                    Login Ulang
                </a>
            @endif

            @if ($code === 503)
                {{-- Maintenance → tombol coba lagi --}}
                <button type="button" onclick="window.location.reload()" class="err-btn err-btn-outline">
                    <i class="fas fa-redo fa-sm"></i>
                    Coba Lagi
                </button>
            @endif

            @if ($code === 500 || $code === 0)
                {{-- Server error → tombol refresh --}}
                <button type="button" onclick="window.location.reload()" class="err-btn err-btn-outline">
                    <i class="fas fa-redo fa-sm"></i>
                    Muat Ulang
                </button>
            @endif

        </div>

        {{-- ── Watermark nama aplikasi ── --}}
        <p class="err-watermark">{{ config('app.name', 'SIS') }}</p>

    </div>

    {{-- Azures scripts (termasuk dark-mode toggle dari localStorage) --}}
    <script src="{{ asset('azures/scripts/bootstrap.min.js') }}"></script>
    <script src="{{ asset('azures/scripts/custom.js') }}"></script>

    <script>
        // Sinkronkan tema dari localStorage Azures agar dark mode konsisten
        (function() {
            try {
                var savedTheme = localStorage.getItem('theme-option') || '';
                if (savedTheme === 'theme-dark') {
                    document.body.classList.replace('theme-light', 'theme-dark');
                }
            } catch (e) {
                /* localStorage tidak tersedia */
            }
        })();
        document.getElementById('backButton').addEventListener('click', function(event) {
            // Jika ada halaman sebelumnya dalam history, kembali ke sana.
            if (window.history.length > 1) {
                event.preventDefault();
                window.history.back();
            }
            // Jika tidak ada history, href="{{ url('/') }}" akan menjadi fallback.
        });
    </script>

</body>

</html>
