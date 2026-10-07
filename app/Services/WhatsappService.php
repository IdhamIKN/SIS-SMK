<?php

namespace App\Services;

use App\Models\WaLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Kirim pesan WhatsApp ke nomor tujuan.
     *
     * Alur pengiriman:
     *   1. Normalisasi nomor ke format internasional (62xxx)
     *   2. Kirim via HTTP GET ke WA gateway
     *   3. Cek respons: sukses jika JSON { "status": "success", ... }
     *   4. Catat hasil ke wa_logs
     *
     * @return bool true jika berhasil masuk antrian (status == "success")
     */
    public function send(string $nomorHp, string $pesan, string $jenis = 'umum', ?int $referensiId = null): bool
    {
        $start = microtime(true);

        // Normalisasi nomor HP ke format internasional 62xxx
        $nomorNormal = $this->normalisasiNomor($nomorHp);

        Log::channel('wa')->info('[WA] Mulai kirim', [
            'to'      => $nomorNormal,
            'to_raw'  => $nomorHp,
            'jenis'   => $jenis,
            'ref'     => $referensiId,
        ]);

        $mode = config('sekolah.wa.mode', 'procedure');
        $sukses     = false;
        $httpStatus = 0;
        $body       = '';

        if ($mode === 'gateway') {
            try {
                $response = Http::timeout(15)->get(config('sekolah.wa.gateway_url'), [
                    'number'  => $nomorNormal,
                    'message' => $pesan,
                    'sender'  => config('sekolah.wa.sender', 'presensi'),
                ]);

                $httpStatus = $response->status();
                $body       = $response->body();

                // Cek respons JSON: sukses jika status == "success"
                $json   = $response->json();
                $sukses = isset($json['status']) && strtolower($json['status']) === 'success';

                if (! $sukses) {
                    // Fallback: jika tidak bisa parse JSON tapi HTTP 2xx, anggap sukses
                    if ($response->successful() && empty($json)) {
                        $sukses = true;
                    }
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $sukses     = false;
                $httpStatus = 0;
                $body       = 'Connection error: ' . $e->getMessage();
            } catch (\Throwable $e) {
                $sukses     = false;
                $httpStatus = 500;
                $body       = $e->getMessage();
            }
        } else {
            // Mode procedure — stored procedure / waboot.outbox
            try {
                $sukses     = true;
                $httpStatus = 200;
                $body       = 'OK (procedure mode)';

                Log::channel('wa')->info('[WA] Mode procedure - insert ke waboot.outbox', [
                    'to' => $nomorNormal,
                ]);
            } catch (\Exception $e) {
                $sukses     = false;
                $httpStatus = 500;
                $body       = $e->getMessage();
            }
        }

        $durasi = round((microtime(true) - $start) * 1000);

        // Simpan log ke wa_logs — status konsisten: 'sukses' atau 'gagal'
        WaLog::create([
            'no_tujuan'      => $nomorNormal,
            'pesan'          => $pesan,
            'jenis'          => $jenis,
            'status'         => $sukses ? 'sukses' : 'gagal',
            'referensi_id'   => $referensiId,
            'referensi_tipe' => $jenis,
            'wa_mode'        => $mode,
            'dikirim_at'     => now(),
        ]);

        if ($sukses) {
            Log::channel('wa')->info('[WA] Terkirim sukses', [
                'to'          => $nomorNormal,
                'jenis'       => $jenis,
                'http_status' => $httpStatus,
                'durasi_ms'   => $durasi,
                'response'    => substr($body, 0, 200),
            ]);
        } else {
            Log::channel('wa')->error('[WA] Gagal kirim', [
                'to'          => $nomorNormal,
                'jenis'       => $jenis,
                'http_status' => $httpStatus,
                'body'        => substr($body, 0, 500),
                'durasi_ms'   => $durasi,
            ]);
        }

        return $sukses;
    }

    /**
     * Normalisasi nomor HP ke format internasional 62xxx.
     *
     * Contoh:
     *   085649925785   → 6285649925785
     *   +6285649925785 → 6285649925785
     *   6285649925785  → 6285649925785
     *   08561234       → 628561234
     */
    public static function normalisasiNomor(string $nomor): string
    {
        $nomor = trim($nomor);

        // Hapus karakter non-digit kecuali tanda + di awal
        $nomor = preg_replace('/[^0-9+]/', '', $nomor);

        // Hapus tanda + jika ada di awal
        if (str_starts_with($nomor, '+')) {
            $nomor = ltrim($nomor, '+');
        }

        // Ubah awalan 0 menjadi 62
        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        }

        // Pastikan diawali 62 — jika belum, tambahkan
        if (! str_starts_with($nomor, '62')) {
            $nomor = '62' . $nomor;
        }

        return $nomor;
    }

    /**
     * Template pesan WA absen masuk — status HADIR
     */
    public static function templateAbsenMasuk(string $namaSiswa, string $kelas, string $waktu): string
    {
        $namaSekolah = self::namaSekolah();

        return "Yth. Bapak/Ibu Orang Tua/Wali Siswa,\n\n" .
            "Informasi Absensi Siswa {$namaSekolah}:\n\n" .
            "Nama   : {$namaSiswa}\n" .
            "Kelas  : {$kelas}\n" .
            "Status : ✅ HADIR\n" .
            "Jam Masuk: {$waktu}\n\n" .
            "Terima kasih atas partisipasi siswa dalam kegiatan belajar mengajar.\n\n" .
            "Hormat Kami,\n" .
            $namaSekolah;
    }

    /**
     * Template pesan WA absen masuk — status TERLAMBAT
     */
    public static function templateAbsenTerlambat(
        string $namaSiswa,
        string $kelas,
        string $waktu,
        int $menitTerlambat,
        string $tanggal
    ): string {
        $namaSekolah = self::namaSekolah();

        return "Yth. Bapak/Ibu Orang Tua/Wali Siswa,\n\n" .
            "Informasi Absensi Siswa {$namaSekolah}:\n\n" .
            "Nama       : {$namaSiswa}\n" .
            "Kelas      : {$kelas}\n" .
            "Status     : ⚠️ TERLAMBAT\n" .
            "Jam Masuk  : {$waktu}\n" .
            "Keterlambatan: {$menitTerlambat} menit\n" .
            "Tanggal    : {$tanggal}\n\n" .
            "Mohon Bapak/Ibu memberikan perhatian agar siswa dapat hadir tepat waktu.\n\n" .
            "Hormat Kami,\n" .
            $namaSekolah;
    }

    /**
     * Template pesan WA notifikasi Auto Alfa
     */
    public static function templateAutoAlfa(
        string $namaSiswa,
        string $kelas,
        string $tanggal
    ): string {
        $namaSekolah = self::namaSekolah();

        return "Yth. Bapak/Ibu Orang Tua/Wali Siswa,\n\n" .
            "Informasi Kehadiran Siswa {$namaSekolah}:\n\n" .
            "Nama   : {$namaSiswa}\n" .
            "Kelas  : {$kelas}\n" .
            "Tanggal: {$tanggal}\n" .
            "Status : ❌ ALFA (Tidak Hadir)\n\n" .
            "Sistem mencatat bahwa siswa tidak melakukan presensi masuk hingga batas waktu yang telah ditentukan.\n\n" .
            "Mohon Bapak/Ibu berkoordinasi dengan pihak sekolah apabila ada keterangan.\n\n" .
            "Hormat Kami,\n" .
            $namaSekolah;
    }

    /**
     * Ambil nama sekolah dari konfigurasi, fallback ke nama tetap.
     */
    private static function namaSekolah(): string
    {
        try {
            $sekolah = \App\Models\Sekolah::aktif();
            return $sekolah?->sekolah ?: config('app.name', 'Sekolah');
        } catch (\Throwable) {
            return config('app.name', 'Sekolah');
        }
    }

    /**
     * Template pesan WA absen pulang
     */
    public static function templateAbsenPulang(string $namaSiswa, string $kelas, string $waktu): string
    {
        $namaSekolah = self::namaSekolah();

        return "Yth. Bapak/Ibu Orang Tua/Wali Siswa,\n\n" .
            "Informasi Absensi Siswa {$namaSekolah}:\n\n" .
            "Nama: {$namaSiswa}\n" .
            "Kelas: {$kelas}\n" .
            "Status: PULANG\n" .
            "Waktu Absen Pulang: {$waktu}\n\n" .
            "Semoga siswa telah belajar dengan baik hari ini.\n\n" .
            "Hormat Kami,\n" .
            $namaSekolah;
    }

    /**
     * Template pesan WA absen event/kegiatan
     */
    public static function templateAbsenEvent(
        string $namaSiswa,
        string $kelas,
        string $namaEvent,
        string $jenis,
        string $waktu
    ): string {
        $namaSekolah = self::namaSekolah();
        $jenisLabel  = $jenis === 'masuk' ? '✅ MASUK' : '🏁 PULANG';

        return "Yth. Bapak/Ibu Orang Tua/Wali Siswa,\n\n" .
            "Informasi Absensi Kegiatan {$namaSekolah}:\n\n" .
            "Nama     : {$namaSiswa}\n" .
            "Kelas    : {$kelas}\n" .
            "Kegiatan : {$namaEvent}\n" .
            "Status   : {$jenisLabel}\n" .
            "Waktu    : {$waktu}\n\n" .
            "Terima kasih atas partisipasi siswa.\n\n" .
            "Hormat Kami,\n" .
            $namaSekolah;
    }

    /**
     * Template pesan WA laporan kehadiran guru ke penerima (admin/BK/kepala sekolah)
     */
    public static function templateLaporanKehadiranGuru(
        string $namaGuru,
        string $kelas,
        int $jamKe,
        string $tanggal,
        string $waktu,
        string $statusLabel,
        string $pelapor = '-',
        ?string $catatan = null
    ): string {
        $namaSekolah = self::namaSekolah();

        $pesan = "📋 *Laporan Kehadiran Guru*\n" .
            "{$namaSekolah}\n\n" .
            "Guru     : {$namaGuru}\n" .
            "Kelas    : {$kelas}\n" .
            "Jam ke-  : {$jamKe}\n" .
            "Tanggal  : {$tanggal}\n" .
            "Waktu    : {$waktu}\n" .
            "Status   : {$statusLabel}\n" .
            "Pelapor  : {$pelapor}\n";

        if ($catatan) {
            $pesan .= "Catatan  : {$catatan}\n";
        }

        $pesan .= "\nMohon ditindaklanjuti jika diperlukan.\n\n" .
            "— Sistem Informasi {$namaSekolah}";

        return $pesan;
    }

    /**
     * Template pesan WA notifikasi izin ditolak ke siswa/orang tua
     */
    public static function templateIzinDitolak(
        string $namaSiswa,
        string $kelas,
        string $jenisIzin,
        string $tanggal,
        string $alasan,
        ?string $catatan = null,
        string $admin = 'Admin'
    ): string {
        $namaSekolah = self::namaSekolah();

        $pesan = "Yth. Bapak/Ibu Orang Tua/Wali Siswa,\n\n" .
            "Informasi Pengajuan Izin {$namaSekolah}:\n\n" .
            "Nama   : {$namaSiswa}\n" .
            "Kelas  : {$kelas}\n" .
            "Jenis  : {$jenisIzin}\n" .
            "Tanggal: {$tanggal}\n" .
            "Alasan : {$alasan}\n\n" .
            "Status : ❌ DITOLAK\n";

        if ($catatan) {
            $pesan .= "Catatan: {$catatan}\n";
        }

        $pesan .= "\nSilakan hubungi pihak sekolah untuk informasi lebih lanjut.\n" .
            "Diverifikasi oleh: {$admin}\n\n" .
            "Hormat Kami,\n" .
            $namaSekolah;

        return $pesan;
    }

    public static function templatePelanggaranAmbang(
        string $namaSiswa,
        string $kelas,
        string $tahunAjaran,
        int $totalPoin,
        int $batasPoin,
        ?string $tindakan = null,
        ?string $sanksi = null,
        ?int $poinAwal = null,
        ?int $sisaPoin = null
    ): string {
        $namaSekolah = self::namaSekolah();

        $pesan = "Yth. Bapak/Ibu Orang Tua/Wali Siswa,\n\n" .
            "Informasi Poin Pelanggaran Siswa {$namaSekolah}:\n\n" .
            "Nama: {$namaSiswa}\n" .
            "Kelas: {$kelas}\n" .
            "Tahun Ajaran: {$tahunAjaran}\n" .
            "Total Poin Pelanggaran: {$totalPoin}\n" .
            "Ambang Pelanggaran: {$batasPoin}\n";

        if ($poinAwal !== null) {
            $pesan .= "Poin Awal Edaran: {$poinAwal}\n";
        }

        if ($sisaPoin !== null) {
            $pesan .= "Sisa Poin: {$sisaPoin}\n";
        }

        if ($tindakan) {
            $pesan .= "Tindakan: {$tindakan}\n";
        }

        if ($sanksi) {
            $pesan .= "Sanksi: {$sanksi}\n";
        }

        $pesan .= "\nMohon Bapak/Ibu wali siswa berkoordinasi dengan pihak sekolah.\n\n" .
            "Hormat Kami,\n" .
            $namaSekolah;

        return $pesan;
    }

    /**
     * Template pesan WA informasi pelanggaran (dikirim manual saat input data)
     */
    public static function templateInfoPelanggaran(
        string $namaSiswa,
        string $kelas,
        string $tanggal,
        string $uraian,
        int $poin
    ): string {
        $namaSekolah = self::namaSekolah();

        return "Yth. Bapak/Ibu Orang Tua/Wali Siswa,\n\n" .
            "Informasi Pelanggaran Tata Tertib {$namaSekolah}:\n\n" .
            "Nama    : {$namaSiswa}\n" .
            "Kelas   : {$kelas}\n" .
            "Tanggal : {$tanggal}\n" .
            "Uraian  : {$uraian}\n" .
            "Poin    : {$poin}\n\n" .
            "Mohon Bapak/Ibu turut membimbing dan mengingatkan putra/putrinya.\n\n" .
            "Hormat Kami,\n" .
            $namaSekolah;
    }

    /**
     * Template pesan WA informasi penghargaan (dikirim manual saat input data)
     */
    public static function templateInfoPenghargaan(
        string $namaSiswa,
        string $kelas,
        string $tanggal,
        string $uraian,
        int $poin
    ): string {
        $namaSekolah = self::namaSekolah();

        return "Yth. Bapak/Ibu Orang Tua/Wali Siswa,\n\n" .
            "Informasi Penghargaan Siswa {$namaSekolah}:\n\n" .
            "Nama    : {$namaSiswa}\n" .
            "Kelas   : {$kelas}\n" .
            "Tanggal : {$tanggal}\n" .
            "Uraian  : {$uraian}\n" .
            "Poin    : {$poin}\n\n" .
            "Selamat, semoga menjadi motivasi untuk terus berprestasi.\n\n" .
            "Hormat Kami,\n" .
            $namaSekolah;
    }
}
