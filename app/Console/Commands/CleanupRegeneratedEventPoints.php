<?php

namespace App\Console\Commands;

use App\Models\EventDeletedOccurrence;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Command untuk membersihkan data di server produksi akibat bug regenerasi event turunan.
 *
 * BUG: Saat event master di-edit, event turunan yang sudah di-hard delete
 * bisa ter-generate ulang karena existingOccurrenceDates() tidak menyimpan tombstone.
 * Event yang ter-regenerate kemudian langsung diproses auto-point dalam hitungan menit,
 * menghasilkan poin pelanggaran/penghargaan yang tidak valid.
 *
 * Cleanup ini:
 * 1. Deteksi event turunan yang ter-regenerate (created_at > tanggal_mulai + threshold)
 * 2. Hapus poin pelanggaran/penghargaan dari event tersebut beserta transaksinya
 * 3. Hapus event yang ter-regenerate (jika tidak ada scan)
 * 4. Tambahkan tombstone agar tidak ter-generate ulang lagi
 *
 * PENTING: Jalankan sekali saja setelah deploy. Gunakan --dry-run untuk preview dulu.
 */
class CleanupRegeneratedEventPoints extends Command
{
    protected $signature = 'event:cleanup-regenerated
                            {--dry-run : Preview saja, tidak ada yang dihapus}
                            {--master= : Batasi ke master event ID tertentu}
                            {--days=1 : Threshold hari: event dianggap regenerated jika created_at lebih dari N hari setelah tanggal_mulai}';

    protected $description = 'Bersihkan poin dan event turunan yang ter-regenerate akibat bug recurrence';

    public function handle(): int
    {
        $isDryRun    = $this->option('dry-run');
        $masterId    = $this->option('master');
        $thresholdDays = (int) $this->option('days');

        $this->info('=== CLEANUP REGENERATED EVENT OCCURRENCES ===');
        $this->info('Mode: ' . ($isDryRun ? 'DRY RUN (tidak ada perubahan)' : 'LIVE (akan menghapus data)'));
        $this->info('Threshold: event created_at lebih dari ' . $thresholdDays . ' hari setelah tanggal_mulai');
        $this->newLine();

        // Temukan event turunan yang ter-regenerate:
        // created_at > tanggal_mulai + threshold DAN scan_count = 0
        // (event yang sudah ada scan tidak termasuk — itu event legitimate meski punya scan terlambat)
        $query = DB::table('events as e')
            ->whereNotNull('e.recurrence_parent_id')
            ->whereNull('e.deleted_at')
            ->whereRaw('e.created_at > DATE_ADD(e.tanggal_mulai, INTERVAL ? DAY)', [$thresholdDays])
            ->whereNotExists(function ($q) {
                $q->from('absen_event as ae')
                    ->whereColumn('ae.event_id', 'e.id')
                    ->whereNotNull('ae.waktu_masuk');
            })
            ->select('e.id', 'e.recurrence_parent_id', 'e.nama_event',
                     'e.tanggal_mulai', 'e.tanggal_selesai',
                     'e.created_at', 'e.auto_point_processed_at',
                     'e.auto_penghargaan_processed_at');

        if ($masterId) {
            $query->where('e.recurrence_parent_id', $masterId);
        }

        $suspects = $query->orderBy('e.recurrence_parent_id')->orderBy('e.tanggal_mulai')->get();

        if ($suspects->isEmpty()) {
            $this->info('Tidak ada event turunan yang mencurigakan ditemukan.');
            return self::SUCCESS;
        }

        $this->warn("Ditemukan {$suspects->count()} event turunan mencurigakan (created_at jauh setelah tanggal_mulai, tidak ada scan):");
        $this->newLine();

        $totalPelanggaranDihapus = 0;
        $totalPenghargaanDihapus = 0;
        $totalEventDihapus       = 0;
        $totalTombstone          = 0;

        foreach ($suspects as $event) {
            $devPel = 'auto-event-' . $event->id;
            $devPen = 'auto-event-penghargaan-' . $event->id;

            $pelCount = DB::table('tblpelanggaran')->where('deviceid', $devPel)->count();
            $penCount = DB::table('tblpenghargaan')->where('deviceid', $devPen)->count();

            $selisihHari = (int) floor(
                (strtotime($event->created_at) - strtotime($event->tanggal_mulai)) / 86400
            );

            $this->line(sprintf(
                '  Event #%d | Master #%d | %s | %s | created_at=%s (+%d hari) | pelanggaran=%d | penghargaan=%d',
                $event->id,
                $event->recurrence_parent_id,
                $event->nama_event,
                substr($event->tanggal_mulai, 0, 10),
                substr($event->created_at, 0, 10),
                $selisihHari,
                $pelCount,
                $penCount
            ));

            if ($isDryRun) {
                $totalPelanggaranDihapus += $pelCount;
                $totalPenghargaanDihapus += $penCount;
                $totalEventDihapus++;
                $totalTombstone++;
                continue;
            }

            DB::beginTransaction();
            try {
                // 1. Hapus transaksi terkait pelanggaran
                if ($pelCount > 0) {
                    $idpels = DB::table('tblpelanggaran')
                        ->where('deviceid', $devPel)
                        ->pluck('idpel');

                    foreach ($idpels as $idpel) {
                        DB::table('tbltransaksi')
                            ->where('noreff', 'PN' . date('ymd', strtotime($event->tanggal_selesai)) . $idpel)
                            ->orWhere('noreff', 'PN' . date('ymd', strtotime($event->auto_point_processed_at ?? $event->tanggal_selesai)) . $idpel)
                            ->delete();
                    }

                    DB::table('tblpelanggaran')->where('deviceid', $devPel)->delete();
                    $totalPelanggaranDihapus += $pelCount;
                    $this->line("    → Hapus {$pelCount} pelanggaran dari event #{$event->id}");
                }

                // 2. Hapus transaksi terkait penghargaan
                if ($penCount > 0) {
                    $idpens = DB::table('tblpenghargaan')
                        ->where('deviceid', $devPen)
                        ->pluck('idpen');

                    foreach ($idpens as $idpen) {
                        DB::table('tbltransaksi')
                            ->where('noreff', 'RW' . date('ymd', strtotime($event->tanggal_mulai)) . $idpen)
                            ->orWhere('noreff', 'RW' . date('ymd', strtotime($event->auto_penghargaan_processed_at ?? $event->tanggal_mulai)) . $idpen)
                            ->delete();
                    }

                    DB::table('tblpenghargaan')->where('deviceid', $devPen)->delete();
                    $totalPenghargaanDihapus += $penCount;
                    $this->line("    → Hapus {$penCount} penghargaan dari event #{$event->id}");
                }

                // 3. Hapus relasi event lalu hapus event itu sendiri
                DB::table('event_kelas')->where('event_id', $event->id)->delete();
                DB::table('event_siswa')->where('event_id', $event->id)->delete();
                DB::table('events')->where('id', $event->id)->delete();
                $totalEventDihapus++;
                $this->line("    → Event #{$event->id} dihapus");

                // 4. Tambah tombstone agar tidak ter-generate ulang
                DB::table('event_deleted_occurrences')->insertOrIgnore([
                    'master_event_id'  => $event->recurrence_parent_id,
                    'occurrence_date'  => substr($event->tanggal_mulai, 0, 10),
                    'deleted_event_id' => $event->id,
                    'deleted_at'       => now(),
                    'deleted_by'       => 1,
                ]);
                $totalTombstone++;
                $this->line("    → Tombstone ditambahkan untuk tanggal " . substr($event->tanggal_mulai, 0, 10));

                DB::commit();

            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("    ✗ GAGAL untuk event #{$event->id}: " . $e->getMessage());
                Log::error('[CleanupRegenerated] GAGAL event #' . $event->id . ': ' . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('=== RINGKASAN ' . ($isDryRun ? '(DRY RUN)' : '') . ' ===');
        $this->info("Event dihapus       : {$totalEventDihapus}");
        $this->info("Pelanggaran dihapus : {$totalPelanggaranDihapus}");
        $this->info("Penghargaan dihapus : {$totalPenghargaanDihapus}");
        $this->info("Tombstone ditambah  : {$totalTombstone}");

        if ($isDryRun) {
            $this->newLine();
            $this->warn('Ini adalah DRY RUN. Jalankan tanpa --dry-run untuk menerapkan perubahan.');
        }

        return self::SUCCESS;
    }
}
