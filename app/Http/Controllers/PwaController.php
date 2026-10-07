<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PwaController extends Controller
{
    /**
     * Halaman instalasi PWA.
     *
     * Logika:
     * - Jika request datang dari PWA yang sudah terinstall (display-mode standalone),
     *   browser tidak akan pernah membuka halaman ini — redirect ke dashboard.
     * - Deteksi "sudah terinstall" di sisi server tidak bisa 100% akurat karena
     *   browser tidak mengirim header khusus; deteksi utama dilakukan di JS.
     * - Halaman ini tetap dapat diakses siapa saja (tanpa auth) agar browser
     *   yang belum login pun bisa melihat panduan instalasi.
     */
    public function installPage(Request $request)
    {
        // Jika sudah login dan sudah di mode standalone → langsung ke dashboard
        // (kondisi ini jarang terpicu karena browser standalone tidak buka URL ini,
        //  tapi sebagai pengaman ekstra)
        if (auth()->check()) {
            $userAgent = $request->header('User-Agent', '');
            // Header Sec-CH-Display-Mode dikirim beberapa browser Chromium terbaru
            $displayMode = $request->header('Sec-CH-Display-Mode', '');
            if (in_array($displayMode, ['standalone', 'fullscreen', 'minimal-ui'])) {
                return redirect()->route('dashboard');
            }
        }

        return view('pwa.install');
    }

    /**
     * Halaman offline — ditampilkan service worker saat navigasi gagal
     * karena tidak ada koneksi internet.
     */
    public function offlinePage()
    {
        return view('pwa.offline');
    }
}
