<?php

namespace App\Console\Commands;

use App\Models\AbsenSiswa;
use App\Models\Sekolah;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * GenerateAbsenHarian
 *
 * Membuat record absensi kosong untuk semua siswa aktif di awal hari.
 * Record dibuat tanpa jam_masuk / status_masuk agar:
 *   - Siswa yang absen sendiri → update kolom masuk
 *   - Auto-alfa → update status_masuk = 'alfa' pada jam eksekusi
 *   - Admin tetap bisa melihat daftar siapa saja yang belum absen
 *
 * Jalankan setiap hari di awal jam sekolah (misal 05:30).
 */
class GenerateAbsenHarian extends Command
{
    protected $signature = 'absen:generate-harian
                            {--date= : Tanggal target YYYY-MM-DD (default: hari ini)}
                            {--force : Paksa generate meskipun hari libur}';

    protected $description = 'Generate record absensi kosong untuk semua siswa aktif di awal hari';

    public function handle(): int
    {
        $tanggalStr = $this->option('date') ?? now(config('app.timezone', 'Asia/Jakarta'))->toDateString();
        $tanggal    = Carbon::parse($tanggalStr, config('app.timezone', 'Asia/Jakarta'));

        $sekolah = Sekolah::aktif();

        // Skip jika bukan hari efektif (kecuali --force)
        if (! $this->option('force') && ! $this->isHariEfektif($tanggal, $sekolah)) {
            $namaHari = $tanggal->locale('id')->isoFormat('dddd');
            $this->info("Hari {$namaHari} bukan hari efektif, Generate Absen Harian dilewati.");
            return self::SUCCESS;
        }

        // Skip jika sedang libur panjang (kecuali --force)
        if (! $this->option('force') && $sekolah?->sedangLibur($tanggal)) {
            $this->info("Mode Libur Panjang aktif, Generate Absen Harian dilewati.");
            return self::SUCCESS;
        }

        $this->info("Generating record absensi harian untuk {$tanggalStr}...");

        $dibuat    = 0;
        $dilewati  = 0;
        $gagal     = 0;

        Siswa::query()
            ->select(['id', 'kelas_id'])
            ->where('status_aktif', true)
            ->whereNotNull('kelas_id')
            ->orderBy('id')
            ->chunkById(200, function ($siswaList) use ($tanggalStr, &$dibuat, &$dilewati, &$gagal) {
                foreach ($siswaList as $siswa) {
                    try {
                        $existing = AbsenSiswa::where('siswa_id', $siswa->id)
                            ->whereDate('tanggal', $tanggalStr)
                            ->first();

                        if ($existing) {
                            // Record sudah ada (misal: dibuat saat storeIzin untuk izin multi-hari)
                            // Jangan timpa status yang sudah ada (izin/sakit/hadir)
                            $dilewati++;
                        } else {
                            AbsenSiswa::create([
                                'siswa_id' => $siswa->id,
                                'tanggal'  => $tanggalStr,
                                'kelas_id' => $siswa->kelas_id,
                                'jenis'    => 'masuk',  // legacy
                                'status'   => 'alfa',   // legacy default — diupdate saat scan
                            ]);
                            $dibuat++;
                        }
                    } catch (\Throwable $e) {
                        $gagal++;
                        Log::channel('sis')->error(
                            "[GenerateAbsenHarian] GAGAL siswa #{$siswa->id}: " . $e->getMessage()
                        );
                    }
                }
            });

        Log::channel('sis')->info('[GenerateAbsenHarian] Selesai', [
            'tanggal'  => $tanggalStr,
            'dibuat'   => $dibuat,
            'dilewati' => $dilewati,
            'gagal'    => $gagal,
        ]);

        $this->info("Selesai. Dibuat: {$dibuat} | Dilewati (sudah ada): {$dilewati} | Gagal: {$gagal}");

        return self::SUCCESS;
    }

    private function isHariEfektif(Carbon $tanggal, ?Sekolah $sekolah): bool
    {
        $map = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];

        $namaHari  = $map[$tanggal->englishDayOfWeek] ?? '';
        $raw       = $sekolah?->hari_efektif;
        $hariAktif = $raw
            ? (is_array($raw) ? $raw : (json_decode($raw, true) ?: []))
            : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        return in_array($namaHari, $hariAktif, true);
    }
}
