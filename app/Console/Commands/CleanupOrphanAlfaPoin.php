<?php

namespace App\Console\Commands;

use App\Models\AbsenSiswa;
use App\Models\Pelanggaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CleanupOrphanAlfaPoin
 *
 * Membersihkan poin pelanggaran auto-alfa yang "yatim":
 * yaitu siswa yang record absennya sudah BUKAN alfa (hadir/terlambat/izin/sakit)
 * tapi poin auto-alfa masih ada di tblpelanggaran.
 *
 * Kondisi ini terjadi pada data LAMA sebelum AbsenSiswaObserver dipasang.
 * Data baru sudah otomatis bersih via observer.
 *
 * Usage:
 *   php artisan tatib:cleanup-orphan-alfa        → dry-run (lihat daftar tanpa hapus)
 *   php artisan tatib:cleanup-orphan-alfa --execute  → hapus data
 */
class CleanupOrphanAlfaPoin extends Command
{
    protected $signature = 'tatib:cleanup-orphan-alfa
                            {--execute : Eksekusi hapus data (tanpa flag ini hanya dry-run)}
                            {--tahun= : Filter tahun ajaran, contoh: 2026/2027}';

    protected $description = 'Bersihkan poin auto-alfa yang siswa absennya sudah bukan alfa (data lama sebelum observer)';

    public function handle(): int
    {
        $isDryRun    = ! $this->option('execute');
        $tahunFilter = $this->option('tahun');

        $this->info('');
        $this->info($isDryRun
            ? '🔍 DRY-RUN — tidak ada data yang dihapus. Tambahkan --execute untuk hapus.'
            : '⚠  EXECUTE — data akan dihapus permanen.');
        $this->info('');

        // Cari semua poin auto-alfa (deviceid = 'auto-alfa-YYYY-MM-DD')
        $query = Pelanggaran::where('deviceid', 'like', 'auto-alfa-%');
        if ($tahunFilter) {
            $query->where('tahun_ajaran', $tahunFilter);
        }

        $total    = 0;
        $orphan   = 0;
        $deleted  = 0;

        $query->orderBy('idpel')->chunk(200, function ($poinList) use (
            $isDryRun, &$total, &$orphan, &$deleted
        ) {
            foreach ($poinList as $poin) {
                $total++;

                // Ekstrak tanggal dari deviceid: 'auto-alfa-2026-08-05' → '2026-08-05'
                $tanggal = substr($poin->deviceid, 10); // potong 'auto-alfa-'
                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                    continue; // format tidak dikenali, skip
                }

                // Cari record absen siswa pada tanggal tersebut
                $absen = AbsenSiswa::where('siswa_id', $poin->siswa_id)
                    ->whereDate('tanggal', $tanggal)
                    ->first();

                if (! $absen) {
                    // Tidak ada record absen sama sekali → biarkan (mungkin valid)
                    continue;
                }

                $statusMasuk = $absen->status_masuk ?? $absen->status;

                // Jika status absen sudah bukan alfa → poin ini orphan
                if ($statusMasuk !== 'alfa') {
                    $orphan++;
                    $this->line(sprintf(
                        '  [ORPHAN] Poin #%d | Siswa: %s (%s) | Tanggal: %s | Status absen: %s | Poin: %d',
                        $poin->idpel,
                        $poin->nama,
                        $poin->siswa_id,
                        $tanggal,
                        $statusMasuk,
                        $poin->poin
                    ));

                    if (! $isDryRun) {
                        $poin->delete(); // observer PelanggaranObserver::deleted() akan hapus transaksi juga
                        $deleted++;
                    }
                }
            }
        });

        $this->info('');
        $this->line("Total poin auto-alfa diperiksa : {$total}");
        $this->line("Ditemukan orphan               : {$orphan}");

        if ($isDryRun) {
            if ($orphan > 0) {
                $this->warn("Jalankan dengan --execute untuk menghapus {$orphan} data orphan.");
            } else {
                $this->info('✅ Tidak ada data orphan. Semua poin alfa konsisten dengan status absen.');
            }
        } else {
            $this->line("Berhasil dihapus               : {$deleted}");
            if ($deleted > 0) {
                $this->info("✅ {$deleted} poin alfa orphan berhasil dihapus.");
                Log::channel('sis')->info('[CleanupOrphanAlfaPoin] Selesai', [
                    'total_diperiksa' => $total,
                    'orphan'          => $orphan,
                    'deleted'         => $deleted,
                ]);
            } else {
                $this->info('✅ Tidak ada data yang dihapus.');
            }
        }

        $this->info('');
        return self::SUCCESS;
    }
}
