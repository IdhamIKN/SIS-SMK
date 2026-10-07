<?php

namespace App\Console\Commands;

use App\Models\Pelanggaran;
use App\Models\Penghargaan;
use App\Models\Siswa;
use App\Models\TransaksiPoin;
use App\Services\TatibPoinService;
use Illuminate\Console\Command;

/**
 * SyncTransaksiPoin
 *
 * Backfill: sinkronkan tblpelanggaran & tblpenghargaan → tbltransaksi.
 *
 * Data BARU otomatis di-sync via PelanggaranObserver / PenghargaanObserver.
 * Command ini untuk backfill data lama atau perbaikan inkonsistensi.
 *
 * Opsi:
 *   --force         : Sync ulang semua record (termasuk yang sudah ada)
 *   --missing       : Hanya sync yang belum ada di tbltransaksi (default)
 *   --delete-orphan : Hapus entri tbltransaksi yang sumbernya sudah tidak ada
 *   --fix-deleted   : Soft-delete transaksi yang sumbernya sudah di-soft-delete
 */
class SyncTransaksiPoin extends Command
{
    protected $signature = 'tatib:sync-transaksi
                            {--force         : Sync ulang semua record (updateOrCreate)}
                            {--missing       : Hanya sync yang belum ada transaksinya}
                            {--delete-orphan : Hapus transaksi PN*/RW* yang sumbernya sudah tidak ada}
                            {--fix-deleted   : Soft-delete transaksi yang sumbernya sudah di-soft-delete}';

    protected $description = 'Backfill/sinkronisasi tblpelanggaran & tblpenghargaan ke tbltransaksi.';

    public function __construct(
        private readonly TatibPoinService $tatibPoin
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║        SYNC TRANSAKSI POIN — pelanggaran+penghargaan ║');
        $this->info('╚══════════════════════════════════════════════════════╝');
        $this->info('');
        $this->info('ℹ  Data baru otomatis di-sync via PelanggaranObserver / PenghargaanObserver.');
        $this->info('   Command ini untuk backfill data lama atau perbaikan inkonsistensi.');
        $this->info('');

        $deleteOrphan = $this->option('delete-orphan');
        $fixDeleted   = $this->option('fix-deleted');
        $onlyMissing  = $this->option('missing')
            || (! $this->option('force') && ! $deleteOrphan && ! $fixDeleted);

        if ($fixDeleted) {
            $this->runFixDeleted();
        }

        if ($deleteOrphan) {
            $this->runDeleteOrphan();
        }

        $this->runSyncPelanggaran($onlyMissing);
        $this->runSyncPenghargaan($onlyMissing);

        return self::SUCCESS;
    }

    // ── Sync Pelanggaran ──────────────────────────────────────────────────

    private function runSyncPelanggaran(bool $onlyMissing): void
    {
        $mode = $onlyMissing ? 'hanya yang belum ada transaksinya' : 'semua (force)';
        $this->info("Sync Pelanggaran [{$mode}]...");

        $total = $skip = $gagal = 0;

        $query = Pelanggaran::orderBy('idpel');

        if ($onlyMissing) {
            $query->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('tbltransaksi')
                    ->whereNull('tbltransaksi.deleted_at')
                    ->whereRaw("tbltransaksi.noreff = CONCAT('PN', DATE_FORMAT(tblpelanggaran.tgl, '%y%m%d'), tblpelanggaran.idpel)");
            });
        }

        $pending = $query->count();
        $this->info("  Total yang akan diproses: {$pending}");

        if ($pending === 0) {
            $this->info('  ✅ Semua pelanggaran sudah tersinkronisasi.');
            $this->info('');
            return;
        }

        $bar = $this->output->createProgressBar($pending);
        $bar->start();

        $query->chunk(200, function ($rows) use (&$total, &$skip, &$gagal, $bar) {
            foreach ($rows as $pelanggaran) {
                $siswa = Siswa::find($pelanggaran->siswa_id);
                if (! $siswa) { $skip++; $bar->advance(); continue; }

                try {
                    $this->tatibPoin->createPelanggaranTransaction($pelanggaran, $siswa, $pelanggaran->isi);
                    $total++;
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    // Duplikat noreff — unique index memblok, ini normal saat --force
                    $total++; // sudah ada, count sebagai success
                } catch (\Throwable $e) {
                    $gagal++;
                    $this->newLine();
                    $this->error("  Gagal sync Pelanggaran ID {$pelanggaran->idpel}: {$e->getMessage()}");
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("  Berhasil: {$total} | Skip (siswa tidak ada): {$skip}" . ($gagal ? " | Gagal: {$gagal}" : ''));
        $this->info('');
    }

    // ── Sync Penghargaan ──────────────────────────────────────────────────

    private function runSyncPenghargaan(bool $onlyMissing): void
    {
        $mode = $onlyMissing ? 'hanya yang belum ada transaksinya' : 'semua (force)';
        $this->info("Sync Penghargaan [{$mode}]...");

        $total = $skip = $gagal = 0;

        // Hanya sync penghargaan yang sudah di-ACC
        $query = Penghargaan::where('acc', 'YA')->orderBy('idpen');

        if ($onlyMissing) {
            $query->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('tbltransaksi')
                    ->whereNull('tbltransaksi.deleted_at')
                    ->whereRaw("tbltransaksi.noreff = CONCAT('RW', DATE_FORMAT(tblpenghargaan.tgl, '%y%m%d'), tblpenghargaan.idpen)");
            });
        }

        $pending = $query->count();
        $this->info("  Total yang akan diproses: {$pending}");

        if ($pending === 0) {
            $this->info('  ✅ Semua penghargaan sudah tersinkronisasi.');
            $this->info('');
            return;
        }

        $bar = $this->output->createProgressBar($pending);
        $bar->start();

        $query->chunk(200, function ($rows) use (&$total, &$skip, &$gagal, $bar) {
            foreach ($rows as $penghargaan) {
                $siswa = Siswa::find($penghargaan->siswa_id);
                if (! $siswa) { $skip++; $bar->advance(); continue; }

                try {
                    $this->tatibPoin->createPenghargaanTransaction($penghargaan, $siswa);
                    $total++;
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    // Duplikat noreff — unique index memblok, ini normal saat --force
                    $total++; // sudah ada, count sebagai success
                } catch (\Throwable $e) {
                    $gagal++;
                    $this->newLine();
                    $this->error("  Gagal sync Penghargaan ID {$penghargaan->idpen}: {$e->getMessage()}");
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("  Berhasil: {$total} | Skip (siswa tidak ada): {$skip}" . ($gagal ? " | Gagal: {$gagal}" : ''));
        $this->info('');
    }

    // ── Fix deleted: soft-delete transaksi yang sumbernya sudah di-soft-delete ──

    private function runFixDeleted(): void
    {
        $this->info('Fix-deleted: soft-delete transaksi yang sumbernya sudah di-soft-delete...');

        // PN: pelanggaran yang sudah soft-deleted → soft-delete transaksinya juga
        $fixedPn = \Illuminate\Support\Facades\DB::update("
            UPDATE tbltransaksi t
            INNER JOIN tblpelanggaran p
                ON CONCAT('PN', DATE_FORMAT(p.tgl, '%y%m%d'), p.idpel) = t.noreff
            SET t.deleted_at = NOW()
            WHERE p.deleted_at IS NOT NULL
              AND t.deleted_at IS NULL
              AND t.noreff LIKE 'PN%'
        ");

        // RW: penghargaan yang sudah soft-deleted → soft-delete transaksinya juga
        $fixedRw = \Illuminate\Support\Facades\DB::update("
            UPDATE tbltransaksi t
            INNER JOIN tblpenghargaan p
                ON CONCAT('RW', DATE_FORMAT(p.tgl, '%y%m%d'), p.idpen) = t.noreff
            SET t.deleted_at = NOW()
            WHERE p.deleted_at IS NOT NULL
              AND t.deleted_at IS NULL
              AND t.noreff LIKE 'RW%'
        ");

        $this->info("  Transaksi PN yang diperbaiki: {$fixedPn}");
        $this->info("  Transaksi RW yang diperbaiki: {$fixedRw}");
        $this->info('');
    }

    // ── Delete orphan ─────────────────────────────────────────────────────

    private function runDeleteOrphan(): void
    {
        $this->info('Mencari transaksi PN/RW yang sumbernya sudah tidak ada (via JOIN)...');

        // PN: hapus transaksi yang tidak punya pasangan di tblpelanggaran (termasuk trashed)
        $deletedPN = \Illuminate\Support\Facades\DB::table('tbltransaksi as t')
            ->leftJoin(\Illuminate\Support\Facades\DB::raw(
                "(SELECT idpel, tgl FROM tblpelanggaran UNION ALL SELECT idpel, tgl FROM tblpelanggaran WHERE deleted_at IS NOT NULL) AS p"
            ), \Illuminate\Support\Facades\DB::raw(
                "CONCAT('PN', DATE_FORMAT(p.tgl, '%y%m%d'), p.idpel)"
            ), '=', 't.noreff')
            ->whereRaw("t.noreff LIKE 'PN%'")
            ->whereNull('p.idpel')
            ->delete();

        // Karena DELETE dengan JOIN kompleks lebih aman pakai subquery
        // Gunakan pendekatan yang lebih straightforward:
        $orphanCountPN = \Illuminate\Support\Facades\DB::statement("
            DELETE t FROM tbltransaksi t
            WHERE t.noreff LIKE 'PN%'
            AND NOT EXISTS (
                SELECT 1 FROM tblpelanggaran p
                WHERE CONCAT('PN', DATE_FORMAT(p.tgl, '%y%m%d'), p.idpel) = t.noreff
            )
        ") ? \Illuminate\Support\Facades\DB::select('SELECT ROW_COUNT() as cnt')[0]->cnt : 0;

        $orphanCountRW = \Illuminate\Support\Facades\DB::statement("
            DELETE t FROM tbltransaksi t
            WHERE t.noreff LIKE 'RW%'
            AND NOT EXISTS (
                SELECT 1 FROM tblpenghargaan p
                WHERE CONCAT('RW', DATE_FORMAT(p.tgl, '%y%m%d'), p.idpen) = t.noreff
            )
        ") ? \Illuminate\Support\Facades\DB::select('SELECT ROW_COUNT() as cnt')[0]->cnt : 0;

        $total = (int)$orphanCountPN + (int)$orphanCountRW;
        $this->info("  Transaksi orphan PN dihapus: {$orphanCountPN}");
        $this->info("  Transaksi orphan RW dihapus: {$orphanCountRW}");
        $this->info("  Total orphan dihapus: {$total}");
        $this->info('');
    }
}
