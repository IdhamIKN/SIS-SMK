{{--
    Komponen PWA Install Prompt
    ──────────────────────────
    Dua panel geser (bottom-sheet style Azures):
      #menu-install-pwa-android  — untuk Chrome / Edge / Android (pakai beforeinstallprompt)
      #menu-install-pwa-ios      — untuk Safari iOS (panduan manual Share → Add to Home Screen)

    Komponen ini hanya render HTML-nya.
    Logika show/hide dan event listener sudah ditangani oleh
    custom.js bawaan Azures (isPWA = true).
--}}

{{-- ═══════════════════════════════════════════════════════════
     PANEL ANDROID / CHROME / EDGE / DESKTOP
     Muncul otomatis via event beforeinstallprompt (Chromium)
     ═══════════════════════════════════════════════════════════ --}}
<div id="menu-install-pwa-android" class="menu menu-box-bottom rounded-m" style="display:block; min-height:280px;">

    <div class="menu-title mt-3">
        <h1 class="mb-0">Instal Aplikasi</h1>
        <p class="color-highlight mb-0 font-12">Tambahkan ke layar utama perangkat Anda</p>
        <a href="#" class="close-menu pwa-dismiss">
            <i class="fas fa-times color-red-dark font-14"></i>
        </a>
    </div>

    <div class="divider divider-margins mt-2 mb-3"></div>

    <div class="content mb-4 px-3">
        {{-- Logo identitas — gaya login: kotak gradien + ikon --}}
        <div class="d-flex align-items-center mb-4">
            <div
                style="width:52px; height:52px; flex-shrink:0;
                        background:linear-gradient(135deg,#133db2,#4f46e5,#0ea5e9);
                        border-radius:14px; display:flex; align-items:center;
                        justify-content:center; margin-right:14px;
                        box-shadow:0 4px 14px rgba(124,58,237,.35);">
                <i class="fas fa-graduation-cap" style="font-size:22px; color:#fff;"></i>
            </div>
            <div>
                <h5 class="mb-0 font-600" style="font-size:15px;">EduTech</h5>
                <span class="font-11 color-highlight">
                    <i class="fas fa-globe me-1"></i>
                    {{ config('app.url', request()->getSchemeAndHttpHost()) }}
                </span>
            </div>
        </div>

        <p class="font-14 color-theme opacity-70 mb-4">
            Instal aplikasi ini ke perangkat Anda untuk akses lebih cepat,
            tampilan layar penuh, dan tetap bisa digunakan saat koneksi terbatas.
        </p>

        <div class="d-flex gap-2">
            <a href="#" class="btn btn-full btn-m gradient-highlight rounded-s font-600 pwa-install flex-fill">
                <i class="fas fa-download me-2"></i>Instal Sekarang
            </a>
            <a href="#" class="btn btn-full btn-m btn-border color-theme rounded-s font-600 pwa-dismiss"
                style="flex:0 0 auto; width:100px;">
                Nanti
            </a>
        </div>

        {{-- Tombol "Jangan Ingatkan Lagi" --}}
        <a href="#" class="d-block text-center font-11 color-theme opacity-50 mt-3 pwa-dismiss-forever">
            Jangan ingatkan lagi
        </a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     PANEL iOS / SAFARI
     Tidak ada beforeinstallprompt — hanya panduan manual
     ═══════════════════════════════════════════════════════════ --}}
<div id="menu-install-pwa-ios" class="menu menu-box-bottom rounded-m" style="display:block; min-height:310px;">

    <div class="menu-title mt-3">
        <h1 class="mb-0">Instal di iPhone / iPad</h1>
        <p class="color-highlight mb-0 font-12">Ikuti langkah berikut di Safari</p>
        <a href="#" class="close-menu pwa-dismiss">
            <i class="fas fa-times color-red-dark font-14"></i>
        </a>
    </div>

    <div class="divider divider-margins mt-2 mb-3"></div>

    <div class="content mb-4 px-3">
        {{-- Logo identitas — gaya login: kotak gradien + ikon --}}
        <div class="d-flex align-items-center mb-4">
            <div
                style="width:52px; height:52px; flex-shrink:0;
                        background:linear-gradient(135deg,#133db2,#4f46e5,#0ea5e9);
                        border-radius:14px; display:flex; align-items:center;
                        justify-content:center; margin-right:14px;
                        box-shadow:0 4px 14px rgba(124,58,237,.35);">
                <i class="fas fa-graduation-cap" style="font-size:22px; color:#fff;"></i>
            </div>
            <div>
                <h5 class="mb-0 font-600" style="font-size:15px;">EduTech</h5>
                <span class="font-11 color-red-dark">
                    <i class="fab fa-safari me-1"></i>
                    Hanya bisa diinstal melalui Safari
                </span>
            </div>
        </div>

        {{-- Langkah 1 --}}
        <div class="d-flex align-items-start mb-3">
            <span class="badge badge-circle bg-highlight color-white me-3 font-12"
                style="width:28px;height:28px;line-height:28px;flex-shrink:0;">1</span>
            <p class="mb-0 font-14 color-theme opacity-80">
                Ketuk ikon
                <strong>Bagikan</strong>
                <i class="fas fa-external-link-square-alt ms-1 color-highlight"></i>
                di toolbar bawah Safari.
            </p>
        </div>

        {{-- Langkah 2 --}}
        <div class="d-flex align-items-start mb-3">
            <span class="badge badge-circle bg-highlight color-white me-3 font-12"
                style="width:28px;height:28px;line-height:28px;flex-shrink:0;">2</span>
            <p class="mb-0 font-14 color-theme opacity-80">
                Gulir ke bawah dan pilih
                <strong>"Tambah ke Layar Utama"</strong>
                <i class="fas fa-plus-square ms-1 color-highlight"></i>.
            </p>
        </div>

        {{-- Langkah 3 --}}
        <div class="d-flex align-items-start mb-4">
            <span class="badge badge-circle bg-highlight color-white me-3 font-12"
                style="width:28px;height:28px;line-height:28px;flex-shrink:0;">3</span>
            <p class="mb-0 font-14 color-theme opacity-80">
                Ketuk <strong>"Tambah"</strong> di pojok kanan atas untuk menyelesaikan.
            </p>
        </div>

        <a href="#" class="btn btn-full btn-m btn-border color-theme rounded-s font-600 pwa-dismiss">
            <i class="fas fa-check me-2"></i>Mengerti
        </a>

        <a href="#" class="d-block text-center font-11 color-theme opacity-50 mt-3 pwa-dismiss-forever">
            Jangan ingatkan lagi
        </a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SKRIP TAMBAHAN
     Extend perilaku custom.js:
       - pwa-dismiss-forever: simpan flag permanent di localStorage
       - Banner install untuk browser Firefox / Samsung Browser / lainnya
         yang tidak punya beforeinstallprompt (gunakan halaman /pwa-install)
     ═══════════════════════════════════════════════════════════ --}}
<script>
    (function() {
        'use strict';

        var PWA_KEY = 'EduTech-PWA-Prompt';
        var PWA_TIMEOUT_KEY = 'EduTech-PWA-Timeout-Value';

        // ── Tombol "Jangan ingatkan lagi" ─────────────────────────────────────
        document.querySelectorAll('.pwa-dismiss-forever').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                localStorage.setItem(PWA_KEY, 'install-rejected');
                localStorage.setItem(PWA_TIMEOUT_KEY, Date.now());
                // Tutup semua panel PWA
                document.querySelectorAll('#menu-install-pwa-android, #menu-install-pwa-ios')
                    .forEach(function(el) {
                        el.classList.remove('menu-active');
                    });
                var hider = document.querySelector('.menu-hider');
                if (hider) hider.classList.remove('menu-active');
            });
        });

        // ── Deteksi "sudah terinstall" via display-mode ───────────────────────
        // Jika sudah berjalan sebagai PWA standalone / minimal-ui, tandai di body
        var displayModes = ['standalone', 'fullscreen', 'minimal-ui'];
        var isInstalled = displayModes.some(function(mode) {
            return window.matchMedia('(display-mode: ' + mode + ')').matches;
        });
        // iOS Safari: cek navigator.standalone
        if (window.navigator.standalone === true) {
            isInstalled = true;
        }

        if (isInstalled) {
            document.documentElement.classList.add('pwa-installed');
            // Simpan ke localStorage agar halaman /pwa-install juga tahu
            localStorage.setItem(PWA_KEY, 'installed');
        }

        // ── Banner untuk browser lain (Firefox, Samsung, Opera mini, dll.) ────
        // Browser ini tidak meng-emit beforeinstallprompt dan bukan iOS Safari.
        // Tampilkan banner ringan yang mengarahkan ke halaman /pwa-install.
        var isChromium = !!window.chrome;
        var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
        var isFirefox = /firefox/i.test(navigator.userAgent);
        var isSamsung = /samsungbrowser/i.test(navigator.userAgent);
        var isOtherBrowser = !isChromium && !isIOS;

        if (isOtherBrowser && !isInstalled &&
            localStorage.getItem(PWA_KEY) !== 'install-rejected' &&
            localStorage.getItem(PWA_KEY) !== 'installed') {

            // Tunda sedikit agar halaman selesai render
            setTimeout(function() {
                var banner = document.getElementById('pwa-generic-banner');
                if (banner) banner.style.display = 'flex';
            }, 3500);
        }
    })();
</script>

{{-- Banner generik untuk Firefox / browser lain yang tidak support beforeinstallprompt --}}
<div id="pwa-generic-banner"
    style="display:none; position:fixed; bottom:70px; left:12px; right:12px;
            background:#fff; border-radius:12px; box-shadow:0 4px 24px rgba(0,0,0,.18);
            padding:14px 16px; align-items:center; gap:12px; z-index:9999;">
    <div
        style="width:40px; height:40px; flex-shrink:0;
                background:linear-gradient(135deg,#133db2,#4f46e5,#0ea5e9);
                border-radius:10px; display:flex; align-items:center;
                justify-content:center; box-shadow:0 3px 10px rgba(124,58,237,.3);">
        <i class="fas fa-graduation-cap" style="font-size:17px; color:#fff;"></i>
    </div>
    <div style="flex:1; min-width:0;">
        <div
            style="font-weight:600; font-size:13px; color:#222; white-space:nowrap;
                    overflow:hidden; text-overflow:ellipsis;">
            Instal EduTech
        </div>
        <div style="font-size:11px; color:#666;">Tambahkan ke layar utama Anda</div>
    </div>
    <a href="/pwa-install"
        style="background:linear-gradient(135deg,#133db2,#4f46e5); color:#fff; font-size:12px; font-weight:600;
              padding:7px 14px; border-radius:8px; text-decoration:none; white-space:nowrap;">
        Instal
    </a>
    <button
        onclick="this.closest('#pwa-generic-banner').style.display='none';
                     localStorage.setItem('EduTech-PWA-Prompt','install-rejected');"
        style="background:none; border:none; font-size:18px; color:#999;
                   cursor:pointer; padding:0; line-height:1;">×</button>
</div>
