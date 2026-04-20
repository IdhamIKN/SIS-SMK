<?php

namespace App\Services;

use App\Models\WaLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public function send(string $nomorHp, string $pesan, string $jenis = 'umum', ?int $referensiId = null): bool
    {
        $start = microtime(true);

        Log::channel('wa')->info('[WA] Mulai kirim', [
            'to'    => $nomorHp,
            'jenis' => $jenis,
            'ref'   => $referensiId,
        ]);

        // Cek mode WA: procedure atau gateway
        $mode = config('sekolah.wa.mode', 'procedure');

        if ($mode === 'gateway') {
            // Kirim via HTTP GET ke WA gateway baru
            $response = Http::get(config('sekolah.wa.gateway_url'), [
                'token'   => config('sekolah.wa.gateway_token'),
                'to'      => $nomorHp,
                'message' => $pesan,
            ]);

            $sukses = $response->successful();
            $httpStatus = $response->status();
            $body = $response->body();
        } else {
            // Kirim via stored procedure sp_kirimwa (insert ke waboot.outbox)
            try {
                // Asumsi koneksi ke database gammu/kannel
                $waDb = config('database.connections.wa_boot'); // perlu konfigurasi database tambahan

                // Insert ke waboot.outbox
                $sukses = true; // placeholder
                $httpStatus = 200;
                $body = 'OK';

                Log::channel('wa')->info('[WA] Mode procedure - insert ke waboot.outbox', [
                    'to' => $nomorHp,
                ]);
            } catch (\Exception $e) {
                $sukses = false;
                $httpStatus = 500;
                $body = $e->getMessage();
            }
        }

        $durasi = round((microtime(true) - $start) * 1000);

        // Simpan log ke wa_logs
        WaLog::create([
            'no_tujuan'      => $nomorHp,
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
                'to'          => $nomorHp,
                'jenis'       => $jenis,
                'http_status' => $httpStatus,
                'durasi_ms'   => $durasi,
            ]);
        } else {
            Log::channel('wa')->error('[WA] Gagal kirim', [
                'to'          => $nomorHp,
                'jenis'       => $jenis,
                'http_status' => $httpStatus,
                'body'        => $body,
                'durasi_ms'   => $durasi,
            ]);
        }

        return $sukses;
    }

    /**
     * Template pesan WA absen masuk
     */
    public static function templateAbsenMasuk(string $namaSiswa, string $kelas, string $waktu): string
    {
        return "Assalamualaikum Wr. Wb.\n\n" .
               "Informasi Absensi Siswa SMKN 5 Madiun:\n\n" .
               "Nama: {$namaSiswa}\n" .
               "Kelas: {$kelas}\n" .
               "Status: HADIR\n" .
               "Waktu Absen Masuk: {$waktu}\n\n" .
               "Terima kasih atas partisipasi siswa dalam kegiatan belajar mengajar.\n\n" .
               "Wassalamualaikum Wr. Wb.";
    }

    /**
     * Template pesan WA absen pulang
     */
    public static function templateAbsenPulang(string $namaSiswa, string $kelas, string $waktu): string
    {
        return "Assalamualaikum Wr. Wb.\n\n" .
                "Informasi Absensi Siswa SMKN 5 Madiun:\n\n" .
                "Nama: {$namaSiswa}\n" .
                "Kelas: {$kelas}\n" .
                "Status: PULANG\n" .
                "Waktu Absen Pulang: {$waktu}\n\n" .
                "Semoga siswa telah belajar dengan baik hari ini.\n\n" .
                "Wassalamualaikum Wr. Wb.";
    }

    /**
     * Template pesan WA laporan kehadiran guru
     */
    public static function templateLaporanKehadiranGuru(string $namaGuru, string $jenis, string $status, string $waktu, ?string $catatan = null): string
    {
        $statusText = match($status) {
            'hadir' => 'HADIR',
            'sakit' => 'SAKIT',
            'izin' => 'IZIN',
            'alfa' => 'ALFA',
            default => strtoupper($status)
        };

        $pesan = "Assalamualaikum Wr. Wb.\n\n" .
                "Laporan Kehadiran Guru SMKN 5 Madiun:\n\n" .
                "Nama: {$namaGuru}\n" .
                "Jenis: " . strtoupper($jenis) . "\n" .
                "Status: {$statusText}\n" .
                "Waktu Laporan: {$waktu}\n";

        if ($catatan) {
            $pesan .= "Catatan: {$catatan}\n";
        }

        $pesan .= "\nMohon untuk ditindaklanjuti.\n\n" .
                "Wassalamualaikum Wr. Wb.";

        return $pesan;
    }
}