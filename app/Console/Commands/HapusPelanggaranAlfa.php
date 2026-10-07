<?php

namespace App\Console\Commands;

use App\Models\Pelanggaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * HapusPelanggaranAlfa
 *
 * Soft-delete pelanggaran "Alfa — tidak hadir tanpa keterangan"
 * pada tanggal tertentu, bisa difilter per pasal dan/atau per siswa.
 *
 * Contoh penggunaan:
 *   php artisan tatib:hapus-alfa --tanggal=2026-09-21 --dry-run
 *   php artisan tatib:hapus-alfa --tanggal=2026-09-21 --idpasal=B012
 *   php artisan tatib:hapus-alfa --tanggal=2026-09-21 --noreg=12345
 *   php artisan tatib:hapus-alfa --tanggal=2026-09-21 --idpasal=B012 --force
 */
class HapusPelanggaranAlfa extends Command
{
    protected $signature = 'tatib:hapus-alfa
                            {--tanggal=  : Tanggal pelanggaran format YYYY-MM-DD (wajib)}
                            {--idpasal=  : Kode pasal, contoh: B012 (opsional)}
                            {--noreg=    : NIS / noreg siswa tertentu (opsional)}
                            {--force     : Jalankan tanpa konfirmasi interaktif}
                            {--dry-run   : Tampilkan total data tanpa benar-benar menghapus}';

    protected $description = 'Soft-delete pelanggaran Alfa otomatis siswa berdasarkan tanggal, pasal, dan/atau noreg';

    public function handle(): int
    {
        /* ── Ambil & validasi opsi ──────────────────────────────────── */
        $tanggal  = $this->option('tanggal') ? trim($this->option('tanggal')) : null;
        $idpasal  = $this->option('idpasal') ? trim($this->option('idpasal')) : null;
        $noreg    = $this->option('noreg')   ? trim($this->option('noreg'))   : null;
        $isDryRun = (bool) $this->option('dry-run');

        if (! $tanggal) {
            $this->error('Parameter --tanggal wajib diisi. Contoh: --tanggal=2026-09-21');
            return self::FAILURE;
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            $this->error('Format tanggal tidak valid. Gunakan YYYY-MM-DD, contoh: 2026-09-21');
            return self::FAILURE;
        }

        /* ── Build query ────────────────────────────────────────────── */
        $query = Pelanggaran::withTrashed()
            ->where('isi', 'Alfa — tidak hadir tanpa keterangan')
            ->whereDate('tgl', $tanggal);

        if ($idpasal) {
            $query->where('idpasal', $idpasal);
        }

        if ($noreg) {
            $query->where('noreg', $noreg);
        }

        /* ── Tampilkan ringkasan ─────────────────────────────────────── */
        $total         = (clone $query)->count();
        $totalAktif    = (clone $query)->whereNull('deleted_at')->count();
        $totalTerhapus = $total - $totalAktif;

        $this->newLine();
        $this->line('<fg=cyan>Kriteria pencarian:</>');
        $this->line("  Tanggal : <fg=yellow>{$tanggal}</>");
        $this->line("  idpasal : " . ($idpasal ? "<fg=yellow>{$idpasal}</>" : '<fg=gray>semua</>'));
        $this->line("  Noreg   : " . ($noreg   ? "<fg=yellow>{$noreg}</>"   : '<fg=gray>semua</>'));
        $this->newLine();

        if ($total === 0) {
            $this->warn('Tidak ada data pelanggaran yang cocok dengan kriteria di atas.');
            return self::SUCCESS;
        }

        $this->info("Total ditemukan  : <fg=yellow>{$total}</> record");
        $this->line("  - Aktif        : <fg=green>{$totalAktif}</> (akan di-soft delete)");
        $this->line("  - Sudah dihapus: <fg=gray>{$totalTerhapus}</> (dilewati)");

        if ($isDryRun) {
            $this->newLine();
            $this->warn('[DRY RUN] Tidak ada yang dihapus. Hilangkan --dry-run untuk menjalankan.');
            return self::SUCCESS;
        }

        if ($totalAktif === 0) {
            $this->newLine();
            $this->info('Semua record sudah dihapus sebelumnya. Tidak ada yang perlu dilakukan.');
            return self::SUCCESS;
        }

        /* ── Konfirmasi ─────────────────────────────────────────────── */
        $this->newLine();
        if (! $this->option('force')) {
            if (! $this->confirm("Soft-delete {$totalAktif} record aktif tersebut?", false)) {
                $this->info('Dibatalkan.');
                return self::SUCCESS;
            }
        }

        /* ── Eksekusi dalam transaksi DB ────────────────────────────── */
        $idpels   = $query->pluck('idpel')->toArray();
        $deviceId = 'auto-alfa-' . $tanggal;

        DB::transaction(function () use ($idpels, $deviceId, $noreg) {

            // 1. Hapus transaksi poin terkait di tbltransaksi via noreff (deviceid)
            //    tbltransaksi tidak punya kolom idpel — relasi hanya lewat noreff
            $txQuery = DB::table('tbltransaksi')
                ->where('noreff', $deviceId);

            if ($noreg) {
                $txQuery->where('noreg', $noreg);
            }

            $jumlahTransaksi = $txQuery->delete();

            // 2. Soft-delete pelanggaran (hanya yang belum dihapus)
            $jumlahPelanggaran = Pelanggaran::withTrashed()
                ->whereIn('idpel', $idpels)
                ->whereNull('deleted_at')
                ->delete();

            $this->newLine();
            $this->info("✓ Pelanggaran soft-deleted : {$jumlahPelanggaran} record");
            $this->info("✓ Transaksi poin dihapus   : {$jumlahTransaksi} record");
        });

        $this->newLine();
        $this->info('Selesai.');

        return self::SUCCESS;
    }
}
