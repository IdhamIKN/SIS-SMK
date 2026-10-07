<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#133db2">
    <meta name="robots" content="noindex">
    <link rel="icon" type="image/x-icon"  href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="96x96" href="/azures/app/icons/favicon-96x96.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/azures/app/icons/apple-touch-icon.png">
    <title>Tidak Ada Koneksi — EduTech</title>

    <link rel="stylesheet" href="/azures/styles/bootstrap.css">
    <link rel="stylesheet" href="/azures/styles/style.css">
    <link rel="stylesheet" href="/azures/fonts/css/fontawesome-all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
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

        /* ── Hero — identik dengan halaman login ────────────────── */
        .login-hero {
            position: relative;
            width: 100%;
            background: linear-gradient(135deg, #133db2 0%, #4f46e5 60%, #0ea5e9 100%);
            overflow: hidden;
            border-radius: 0 0 32px 32px;
            min-height: 180px;
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
            padding: 32px 20px 44px;
            text-align: center;
        }

        /* Ikon offline menggantikan hero-logo — warna merah di atas ungu */
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
            font-size: 1.1rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 4px;
        }

        .hero-sub {
            font-size: .8rem;
            color: rgba(255, 255, 255, .75);
            margin: 0;
        }

        .offline-icon i {
            font-size: 52px;
            color: #133db2;
            /* tidak dipakai, tapi dipertahankan untuk kompatibilitas */
        }

        /* ── Teks utama ─────────────────────────────────────────── */
        h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 10px;
        }

        .subtitle {
            font-size: 14px;
            color: #64748b;
            line-height: 1.7;
            max-width: 320px;
            margin: 0 auto 32px;
        }

        /* ── Kotak status ───────────────────────────────────────── */
        .status-box {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            width: 100%;
            max-width: 340px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, .08);
            margin-bottom: 28px;
        }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }

        .status-row:last-child {
            border-bottom: none;
        }

        .status-label {
            color: #64748b;
        }

        .status-val {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .dot-red {
            background: #ef4444;
        }

        .dot-green {
            background: #22c55e;
        }

        /* ── Tombol ─────────────────────────────────────────────── */
        .btn-retry {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #133db2, #4f46e5);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 14px 32px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 4px 16px rgba(74, 137, 220, .4);
            transition: transform .15s;
            text-decoration: none;
            margin-bottom: 12px;
        }

        .btn-retry:hover {
            transform: translateY(-1px);
        }

        .btn-retry:active {
            transform: translateY(0);
        }

        .btn-retry i.fa-spin {
            animation: spin .8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .hint {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        /* ── Footer app info ────────────────────────────────────── */
        .app-info {
            margin-top: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: #94a3b8;
        }

        .app-info img {
            width: 28px;
            height: 28px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    {{-- ── Hero — identik dengan halaman login ─────────────────────────── --}}
    <div class="login-hero" style="width:100%;">
        <div class="hero-content">
            <div class="hero-logo">
                <i class="fas fa-wifi-slash" id="wifi-icon"></i>
            </div>
            <h1 class="hero-title">Tidak Ada Koneksi</h1>
            <p class="hero-sub">EduTech</p>
        </div>
    </div>

    {{-- ── Card konten — overlap ke hero seperti login-card ────────────── --}}
    <div style="width:100%; max-width:440px; padding:0 16px; margin-top:-24px; position:relative; z-index:3;">

        <div
            style="background:#fff; border-radius:20px; padding:24px 20px;
                    box-shadow:0 8px 32px rgba(0,0,0,.10); margin-bottom:16px;">

            <p style="font-size:14px; color:#64748b; line-height:1.7; margin:0 0 20px; text-align:center;">
                Periksa koneksi Wi-Fi atau data seluler, lalu coba muat ulang halaman.
            </p>

            {{-- Status koneksi --}}
            <div class="status-box" style="margin:0 0 20px;">
                <div class="status-row">
                    <span class="status-label">Status Jaringan</span>
                    <span class="status-val" id="network-status">
                        <span class="dot dot-red" id="dot-network"></span>
                        <span id="network-text">Offline</span>
                    </span>
                </div>
                <div class="status-row">
                    <span class="status-label">Halaman Terakhir</span>
                    <span class="status-val" id="last-page"
                        style="font-size:11px; max-width:160px; overflow:hidden;
                                 text-overflow:ellipsis; white-space:nowrap; direction:rtl;">—</span>
                </div>
                <div class="status-row">
                    <span class="status-label">Cache Tersedia</span>
                    <span class="status-val" id="cache-status">
                        <span class="dot dot-red" id="dot-cache"></span>
                        <span id="cache-text">Memeriksa…</span>
                    </span>
                </div>
            </div>

            {{-- Tombol coba lagi --}}
            <button class="btn-retry" id="btn-retry" onclick="retryConnection()"
                style="width:100%; justify-content:center;">
                <i class="fas fa-redo" id="retry-icon"></i>
                Coba Lagi
            </button>
            <p class="hint" id="retry-hint" style="text-align:center;">Periksa koneksi lalu tekan tombol di atas</p>
        </div>

        {{-- Footer --}}
        <div class="app-info" style="justify-content:center;">
            <img src="/azures/app/icons/icon-72x72.png" alt="EduTech">
            <span>EduTech</span>
        </div>

    </div>

    <script>
        (function() {
            'use strict';

            // ── Update status network secara real-time ────────────────────
            function updateNetworkStatus() {
                var online = navigator.onLine;
                document.getElementById('dot-network').className = 'dot ' + (online ? 'dot-green' : 'dot-red');
                document.getElementById('network-text').textContent = online ? 'Online' : 'Offline';

                if (online) {
                    // Koneksi kembali — navigasi ke halaman sebelumnya atau root
                    var hint = document.getElementById('retry-hint');
                    if (hint) hint.textContent = 'Koneksi kembali! Memuat ulang…';
                    setTimeout(function() {
                        window.location.href = document.referrer || '/';
                    }, 800);
                }
            }

            window.addEventListener('online', updateNetworkStatus);
            window.addEventListener('offline', updateNetworkStatus);
            updateNetworkStatus();

            // ── Tampilkan halaman terakhir yang dicoba ────────────────────
            var lastPage = document.getElementById('last-page');
            if (lastPage && document.referrer) {
                lastPage.textContent = document.referrer;
            }

            // ── Cek apakah ada cache tersedia ─────────────────────────────
            if ('caches' in window) {
                caches.keys().then(function(keys) {
                    var hasSisCache = keys.some(function(k) {
                        return k.startsWith('edutech-');
                    });
                    var dotCache = document.getElementById('dot-cache');
                    var textCache = document.getElementById('cache-text');
                    if (hasSisCache) {
                        dotCache.className = 'dot dot-green';
                        textCache.textContent = 'Ada (' + keys.length + ' cache)';
                    } else {
                        dotCache.className = 'dot dot-red';
                        textCache.textContent = 'Tidak ada';
                    }
                });
            } else {
                document.getElementById('cache-text').textContent = 'Tidak didukung';
            }

            // ── Tombol coba lagi ──────────────────────────────────────────
            window.retryConnection = function() {
                var icon = document.getElementById('retry-icon');
                icon.className = 'fas fa-circle-notch fa-spin';

                // Coba fetch ke server dengan cache-busting
                fetch('/manifest.json?_=' + Date.now(), {
                        cache: 'no-store'
                    })
                    .then(function(res) {
                        if (res.ok) {
                            window.location.href = document.referrer || '/';
                        } else {
                            throw new Error('Server error');
                        }
                    })
                    .catch(function() {
                        icon.className = 'fas fa-redo';
                        var hint = document.getElementById('retry-hint');
                        if (hint) hint.textContent = 'Masih offline. Periksa koneksi Anda.';
                    });
            };
        })();
    </script>

</body>

</html>
