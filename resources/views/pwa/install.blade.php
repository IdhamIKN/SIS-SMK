<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, viewport-fit=cover">
    <meta name="theme-color" content="#133db2">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="EduTech">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/x-icon"  href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="96x96" href="/azures/app/icons/favicon-96x96.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/azures/app/icons/apple-touch-icon.png">
    <title>Instal Aplikasi — EduTech</title>

    <link rel="stylesheet" href="{{ asset('azures/styles/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('azures/styles/style.css') }}">
    <link rel="stylesheet" href="{{ asset('azures/fonts/css/fontawesome-all.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        /* ── Reset & base ─────────────────────────────────────────── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 0 0 40px;
            color: #333;
        }

        /* ── Hero banner — identik dengan login ───────────────────── */
        .login-hero {
            position: relative;
            width: 100%;
            background: linear-gradient(135deg, #133db2 0%, #4f46e5 60%, #0ea5e9 100%);
            overflow: hidden;
            border-radius: 0 0 32px 32px;
            min-height: 200px;
        }

        .login-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, .08);
            border-radius: 50%;
        }

        .login-hero::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: -40px;
            width: 160px;
            height: 160px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 36px 20px 44px;
            text-align: center;
        }

        .hero-logo {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, .18);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            color: #fff;
            margin-bottom: 14px;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, .25);
            box-shadow: 0 4px 16px rgba(0, 0, 0, .15);
        }

        .hero-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 4px;
            letter-spacing: -.01em;
        }

        .hero-sub {
            font-size: .8rem;
            color: rgba(255, 255, 255, .75);
            margin: 0 0 20px;
        }

        /* ── Feature pills di dalam hero ─────────────────────────── */
        .features {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .feature-pill {
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 500;
            color: #fff;
            backdrop-filter: blur(4px);
        }

        .feature-pill i {
            margin-right: 4px;
        }

        /* ── Tombol lewati (pojok kanan atas) ─────────────────────── */
        .skip-btn {
            position: absolute;
            top: 14px;
            right: 16px;
            z-index: 10;
            font-size: 12px;
            color: rgba(255, 255, 255, .8);
            text-decoration: none;
            padding: 5px 12px;
            border: 1px solid rgba(255, 255, 255, .35);
            border-radius: 20px;
            backdrop-filter: blur(4px);
        }

        /* ── Card container ───────────────────────────────────────── */
        .card-container {
            width: 100%;
            max-width: 440px;
            padding: 0 16px;
        }

        .install-card {
            background: #fff;
            border-radius: 20px;
            padding: 24px 20px;
            color: #222;
            margin-bottom: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .10);
        }

        .card-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #133db2;
            margin: 0 0 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-title i {
            font-size: 16px;
        }

        /* ── Tombol install utama ─────────────────────────────────── */
        .btn-install-main {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #133db2, #4f46e5);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: transform .15s, box-shadow .15s;
            box-shadow: 0 4px 16px rgba(124, 58, 237, .4);
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
        }

        .btn-install-main:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 22px rgba(124, 58, 237, .5);
        }

        .btn-install-main:active {
            transform: translateY(0);
        }

        .btn-install-main:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* ── Status already installed ─────────────────────────────── */
        .already-installed {
            display: none;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 8px;
            padding: 8px 0;
        }

        .already-installed i {
            font-size: 40px;
            color: #22c55e;
        }

        .already-installed h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .already-installed p {
            margin: 0;
            font-size: 13px;
            color: #666;
        }

        /* ── Langkah iOS ──────────────────────────────────────────── */
        .step-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .step-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
        }

        .step-list li:last-child {
            border-bottom: none;
        }

        .step-num {
            flex-shrink: 0;
            width: 26px;
            height: 26px;
            background: #133db2;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            margin-top: 1px;
        }

        /* ── Langkah desktop ──────────────────────────────────────── */
        .desktop-hint {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }

        .desktop-hint i {
            color: #133db2;
            font-size: 22px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        /* ── Browser support badges ───────────────────────────────── */
        .browser-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 4px;
        }

        .browser-badge {
            display: flex;
            align-items: center;
            gap: 5px;
            background: #f4f6fb;
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 12px;
            color: #444;
            font-weight: 500;
        }

        .browser-badge i {
            font-size: 15px;
        }

        /* ── Link ke login ────────────────────────────────────────── */
        .login-link {
            text-align: center;
            font-size: 13px;
            color: #64748b;
            padding: 0 16px;
        }

        .login-link a {
            color: #133db2;
            font-weight: 600;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* ── Sembunyikan section yang tidak relevan ───────────────── */
        .section-android,
        .section-ios,
        .section-desktop,
        .section-other {
            display: none;
        }
    </style>
</head>

<body>

    {{-- ── Hero Banner — identik dengan halaman login ─────────────────────── --}}
    <div class="login-hero" style="width:100%;">
        <a href="{{ route('login') }}" class="skip-btn" id="btn-skip-hero"
           onclick="localStorage.setItem('EduTech-PWA-Skipped','1'); return true;">Lewati</a>
        <div class="hero-content">
            <div class="hero-logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h1 class="hero-title">EduTech</h1>
            <p class="hero-sub">Sistem Informasi Sekolah</p>
            <div class="features">
                {{-- <span class="feature-pill"><i class="fas fa-bolt"></i>Lebih Cepat</span> --}}
                {{-- <span class="feature-pill"><i class="fas fa-wifi"></i>Offline Ready</span> --}}
                {{-- <span class="feature-pill"><i class="fas fa-expand-arrows-alt"></i>Layar Penuh</span> --}}
            </div>
        </div>
    </div>

    {{-- ── Card container — overlap ke atas seperti login-card ────────────── --}}
    <div class="card-container" style="margin-top:-24px; position:relative; z-index:3; width:100%; max-width:440px;">

        {{-- Status: sudah terinstall --}}
        <div class="install-card" id="card-installed" style="display:none;">
            <div class="already-installed" style="display:flex;">
                <i class="fas fa-check-circle"></i>
                <h3>Aplikasi Sudah Terinstal!</h3>
                <p>EduTech sudah ada di layar utama Anda. Buka langsung dari sana.</p>
                <a href="{{ route('login') }}"
                    style="margin-top:8px; background:linear-gradient(135deg,#133db2,#4f46e5); color:#fff; padding:12px 28px;
                          border-radius:12px; font-weight:600; text-decoration:none; font-size:14px;">
                    <i class="fas fa-sign-in-alt me-2"></i>Buka Aplikasi
                </a>
            </div>
        </div>

        {{-- ── Android / Chrome / Edge ──────────────────────────────────── --}}
        <div class="install-card section-android" id="card-android">
            <p class="card-title"><i class="fab fa-android"></i>Android / Chrome / Edge</p>

            {{-- Tombol install (aktif jika beforeinstallprompt tersedia) --}}
            <button class="btn-install-main" id="btn-android-install" disabled style="opacity:.5; cursor:not-allowed;">
                <i class="fas fa-download"></i>Instal Sekarang
            </button>

            {{-- Panduan manual jika beforeinstallprompt tidak muncul --}}
            <div id="android-manual" style="display:none; margin-top:14px;">
                <ul class="step-list">
                    <li>
                        <span class="step-num">1</span>
                        <span>Ketuk ikon <strong>⋮ Menu</strong> (tiga titik) di pojok kanan atas browser.</span>
                    </li>
                    <li>
                        <span class="step-num">2</span>
                        <span>Pilih <strong>"Tambahkan ke layar utama"</strong> atau <strong>"Instal
                                Aplikasi"</strong>.</span>
                    </li>
                    <li>
                        <span class="step-num">3</span>
                        <span>Ketuk <strong>"Instal"</strong> / <strong>"Tambah"</strong> pada dialog konfirmasi.</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- ── iOS / Safari ─────────────────────────────────────────────── --}}
        <div class="install-card section-ios" id="card-ios">
            <p class="card-title"><i class="fab fa-apple"></i>iPhone & iPad (Safari)</p>
            <ul class="step-list">
                <li>
                    <span class="step-num">1</span>
                    <span>
                        Pastikan Anda membuka halaman ini menggunakan <strong>Safari</strong>.
                        Browser lain di iOS tidak mendukung instalasi PWA.
                    </span>
                </li>
                <li>
                    <span class="step-num">2</span>
                    <span>
                        Ketuk ikon <strong>Bagikan</strong>
                        <i class="fas fa-external-link-square-alt" style="color:#133db2;"></i>
                        di toolbar bawah Safari.
                    </span>
                </li>
                <li>
                    <span class="step-num">3</span>
                    <span>Gulir ke bawah, pilih <strong>"Tambah ke Layar Utama"</strong>
                        <i class="fas fa-plus-square" style="color:#133db2;"></i>.</span>
                </li>
                <li>
                    <span class="step-num">4</span>
                    <span>Ketuk <strong>"Tambah"</strong> di pojok kanan atas untuk selesai.</span>
                </li>
            </ul>

            <div
                style="background:#fff8e1; border-radius:10px; padding:12px; margin-top:12px;
                        font-size:12px; color:#b45309; display:flex; gap:8px; align-items:flex-start;">
                <i class="fas fa-exclamation-triangle" style="flex-shrink:0; margin-top:2px;"></i>
                <span>Jika Anda membuka halaman ini dari Chrome atau Firefox di iOS,
                    salin URL-nya dan buka ulang di <strong>Safari</strong>.</span>
            </div>
        </div>

        {{-- ── Desktop (Chrome / Edge / Brave) ─────────────────────────── --}}
        <div class="install-card section-desktop" id="card-desktop">
            <p class="card-title"><i class="fas fa-desktop"></i>Desktop — Chrome / Edge / Brave</p>

            <button class="btn-install-main" id="btn-desktop-install" disabled style="opacity:.5; cursor:not-allowed;">
                <i class="fas fa-download"></i>Instal Aplikasi
            </button>

            <div id="desktop-manual" style="display:none; margin-top:14px;">
                <div class="desktop-hint">
                    <i class="fas fa-info-circle"></i>
                    <span>
                        Lihat ikon <strong>instal</strong>
                        <i class="fas fa-plus-square" style="color:#133db2;"></i>
                        di address bar (ujung kanan), atau buka menu browser
                        dan pilih <strong>"Instal EduTech…"</strong>.
                    </span>
                </div>
            </div>
        </div>

        {{-- ── Browser lain (Firefox, Samsung, Opera, dll.) ────────────── --}}
        <div class="install-card section-other" id="card-other">
            <p class="card-title"><i class="fas fa-globe"></i>Browser Lainnya</p>
            <p style="font-size:14px; color:#555; margin:0 0 14px; line-height:1.6;">
                Browser Anda mungkin belum mendukung instalasi langsung.
                Untuk pengalaman terbaik, buka halaman ini menggunakan salah satu browser berikut:
            </p>
            <div class="browser-badges">
                <span class="browser-badge"><i class="fab fa-chrome" style="color:#4285F4;"></i>Chrome</span>
                <span class="browser-badge"><i class="fab fa-edge" style="color:#0078d4;"></i>Edge</span>
                <span class="browser-badge"><i class="fab fa-safari" style="color:#006CFF;"></i>Safari</span>
                <span class="browser-badge"><i class="fab fa-opera" style="color:#FF1B2D;"></i>Opera</span>
                <span class="browser-badge"><i class="fab fa-samsung" style="color:#1428A0;"></i>Samsung</span>
            </div>
        </div>

        {{-- ── Link ke login ─────────────────────────────────────────────── --}}
        <p class="login-link">
            Sudah punya akun?
            <a href="{{ route('login') }}">Masuk ke EduTech</a>
        </p>

        {{-- ── Tombol Lanjutkan tanpa install ──────────────────────────────── --}}
        <div style="text-align:center; padding:0 16px 8px;">
            <button id="btn-skip-install"
                onclick="localStorage.setItem('EduTech-PWA-Skipped','1'); window.location.href='{{ route('login') }}';"
                style="background:none; border:1.5px solid #cbd5e1; border-radius:12px;
                       color:#64748b; font-size:13px; font-weight:500; padding:11px 24px;
                       cursor:pointer; width:100%; font-family:'Poppins',sans-serif;
                       transition:background .15s, color .15s;">
                <i class="fas fa-times-circle" style="margin-right:6px; color:#94a3b8;"></i>
                Lanjutkan tanpa menginstall
            </button>
            <p style="margin:8px 0 0; font-size:11px; color:#94a3b8; line-height:1.5;">
                Sistem akan berjalan di browser biasa tanpa fitur offline &amp; layar penuh.
            </p>
        </div>
    </div>

    <script>
        (function() {
            'use strict';

            var PWA_KEY = 'EduTech-PWA-Prompt';

            // ── 1. Deteksi platform ───────────────────────────────────────────
            var ua = navigator.userAgent;
            var isIOS = /iphone|ipad|ipod/i.test(ua);
            var isAndroid = /android/i.test(ua);
            var isChromium = !!window.chrome && !/edge/i.test(ua); // Chrome / Brave / Opera Chromium
            var isEdge = /edg\//i.test(ua);
            var isSafari = /safari/i.test(ua) && !/chrome|crios|fxios/i.test(ua);
            var isDesktop = !isIOS && !isAndroid;
            var isSupportedDesktop = isDesktop && (isChromium || isEdge);

            // ── 2. Deteksi sudah terinstall ───────────────────────────────────
            var displayModes = ['standalone', 'fullscreen', 'minimal-ui'];
            var isInstalled = displayModes.some(function(m) {
                return window.matchMedia('(display-mode: ' + m + ')').matches;
            });
            if (window.navigator.standalone === true) isInstalled = true;
            if (localStorage.getItem(PWA_KEY) === 'installed') isInstalled = true;

            // ── 3. Tampilkan card yang sesuai ─────────────────────────────────
            function showCard(id) {
                var el = document.getElementById(id);
                if (el) el.style.display = 'block';
            }

            function showSection(cls) {
                document.querySelectorAll('.' + cls).forEach(function(el) {
                    el.style.display = 'block';
                });
            }

            if (isInstalled) {
                showCard('card-installed');
                localStorage.setItem(PWA_KEY, 'installed');
            } else if (isIOS) {
                showSection('section-ios');
            } else if (isAndroid) {
                showSection('section-android');
            } else if (isSupportedDesktop) {
                showSection('section-desktop');
            } else {
                showSection('section-other');
            }

            // ── 4. beforeinstallprompt (Android + Desktop Chromium/Edge) ─────
            var deferredPrompt = null;

            window.addEventListener('beforeinstallprompt', function(e) {
                e.preventDefault();
                deferredPrompt = e;

                // Aktifkan tombol install
                ['btn-android-install', 'btn-desktop-install'].forEach(function(id) {
                    var btn = document.getElementById(id);
                    if (btn) {
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        btn.style.cursor = 'pointer';
                    }
                });
            });

            // Klik tombol install
            ['btn-android-install', 'btn-desktop-install'].forEach(function(id) {
                var btn = document.getElementById(id);
                if (!btn) return;

                btn.addEventListener('click', function() {
                    if (!deferredPrompt) return;
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then(function(result) {
                        if (result.outcome === 'accepted') {
                            localStorage.setItem(PWA_KEY, 'installed');
                        }
                        deferredPrompt = null;
                    });
                });
            });

            // ── 5. Jika beforeinstallprompt tidak muncul dalam 4 detik ───────
            //       (misalnya sudah pernah dismiss, atau browser versi lama)
            //       tampilkan panduan manual
            setTimeout(function() {
                if (!deferredPrompt && !isInstalled && !isIOS) {
                    var manualAndroid = document.getElementById('android-manual');
                    var manualDesktop = document.getElementById('desktop-manual');
                    if (manualAndroid) manualAndroid.style.display = 'block';
                    if (manualDesktop) manualDesktop.style.display = 'block';

                    // Sembunyikan tombol yang masih disabled
                    ['btn-android-install', 'btn-desktop-install'].forEach(function(id) {
                        var btn = document.getElementById(id);
                        if (btn && btn.disabled) btn.style.display = 'none';
                    });
                }
            }, 4000);

            // ── 6. appinstalled event ─────────────────────────────────────────
            window.addEventListener('appinstalled', function() {
                localStorage.setItem(PWA_KEY, 'installed');
                // Refresh halaman → card "sudah terinstall" akan muncul
                setTimeout(function() {
                    window.location.reload();
                }, 500);
            });

            // ── 7. Registrasi service worker ─────────────────────────────────
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/_service-worker.js', {
                    scope: '/'
                });
            }
        })();
    </script>

</body>

</html>
