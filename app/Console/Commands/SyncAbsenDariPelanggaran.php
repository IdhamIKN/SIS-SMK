<?php

namespace App\Console\Commands;

use App\Models\AbsenSiswa;
use App\Models\AcademicYear;
use App\Models\Pelanggaran;
use App\Models\Sekolah;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SyncAbsenDariPelanggaran
 *
 * Sinkronisasi status absensi berdasarkan data pelanggaran:
 *
 * ATURAN:
 * 1. Jika siswa punya pelanggaran B012 (alfa) DAN ada pelanggaran LAIN di hari yang sama
 *    → update absensi jadi 'terlambat' jam 08:00
 *    → hapus (soft-delete) pelanggaran B012 (auto-alfa) karena siswa ternyata masuk
 *
 * 2. Jika siswa punya pelanggaran B001 (terlambat) non-auto (absen manual/fisik)
 *    DAN juga punya pelanggaran B012 (auto-alfa)
 *    → hapus (soft-delete) pelanggaran B012
 *    → status absen dipertahankan/set terlambat
 *
 * Usage:
 *   php artisan tatib:sync-absen-dari-pelanggaran
 *   php artisan tatib:sync-absen-dari-pelanggaran --date=2026-08-11
 *   php artisan tatib:sync-absen-dari-pelanggaran --from=2026-08-01 --to=2026-08-12
 *   php artisan tatib:sync-absen-dari-pelanggaran --dry-run
 */
class SyncAbsenDariPelanggaran extends Command
{
    protected $signature = 'tatib:sync-absen-dari-pelanggaran
                            {--date=  : Tanggal spesifik YYYY-MM-DD (default: hari ini)}
                            {--from=  : Tanggal mulai range YYYY-MM-DD}
                            {--to=    : Tanggal akhir range YYYY-MM-DD}
                            {--dry-run : Simulasi, tidak menyimpan ke database}';

    protected $description = 'Sinkronisasi status absen berdasarkan kombinasi pelanggaran: B012(alfa)+lain → terlambat, B001+B012 → hapus B012';

    private string $pasalAlfa      = 'B012'; // Tidak masuk / alfa
    private string $pasalTerlambat = 'B001'; // Terlambat

    private bool $dryRun = false;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        // Ambil pasal dari config sekolah (lebih fleksibel)
        $sekolah = Sekolah::aktif();
        if ($sekolah?->pasal_alfa_id) {
            $this->pasalAlfa = $sekolah->pasal_alfa_id;
        }
        if ($sekolah?->pasal_terlambat_id) {
            $this->pasalTerlambat = $sekolah->pasal_terlambat_id;
        }

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║       SYNC ABSEN DARI PELANGGARAN                    ║');
        $this->info('╚══════════════════════════════════════════════════════╝');
        $this->info("  Pasal Alfa     : {$this->pasalAlfa}");
        $this->info("  Pasal Terlambat: {$this->pasalTerlambat}");
        if ($this->dryRun) {
            $this->warn('  [DRY RUN] Tidak ada perubahan yang disimpan.');
        }
        $this->info('');

        $tanggalList = $this->resolveTanggalList();
        if (empty($tanggalList)) {
            $this->error('Tidak ada tanggal valid untuk diproses.');
            return self::FAILURE;
        }

        $this->info('Memproses ' . count($tanggalList) . ' tanggal...');

        $totalAbsenUpdate  = 0;
        $totalAlfaHapus    = 0;
        $totalSkip         = 0;

        foreach ($tanggalList as $tanggal) {
            [$absenUpdate, $alfaHapus, $skip] = $this->prosesTanggal($tanggal);
            $totalAbsenUpdate += $absenUpdate;
            $totalAlfaHapus   += $alfaHapus;
            $totalSkip        += $skip;
        }

        $this->info('');
        $this->info('Selesai.');
        $this->line("  Absensi diupdate ke terlambat : {$totalAbsenUpdate}");
        $this->line("  Pelanggaran alfa dihapus      : {$totalAlfaHapus}");
        $this->line("  Dilewati (tidak ada perubahan): {$totalSkip}");
        if ($this->dryRun) {
            $this->warn('  [DRY RUN] Tidak ada yang disimpan ke database.');
        }

        return self::SUCCESS;
    }

    /**
     * Proses satu tanggal — kembalikan [absenUpdate, alfaHapus, skip]
     */
    private function prosesTanggal(string $tanggal): array
    {
        $absenUpdate = 0;
        $alfaHapus   = 0;
        $skip        = 0;

        // Ambil semua siswa yang punya pelanggaran alfa (B012 auto) di tanggal ini
        $alfaRecords = Pelanggaran::where('idpasal', $this->pasalAlfa)
            ->whereDate('tgl', $tanggal)
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('siswa_id'); // group by siswa (edge case: >1 alfa per siswa)

        if ($alfaRecords->isEmpty()) {
            return [$absenUpdate, $alfaHapus, $skip];
        }

        $this->line("  [{$tanggal}] Siswa alfa: {$alfaRecords->count()}");

        foreach ($alfaRecords as $siswaId => $alfaGroup) {
            // Gunakan record pertama sebagai referensi (biasanya hanya satu per hari)
            $alfaPelanggaran = $alfaGroup->first();

            // Ambil semua pelanggaran siswa ini di hari yang sama (selain alfa)
            $pelanggaranLain = Pelanggaran::where('siswa_id', $siswaId)
                ->whereDate('tgl', $tanggal)
                ->whereNull('deleted_at')
                ->where('idpasal', '!=', $this->pasalAlfa)
                ->get();

            // Cek apakah ada B001 terlambat NON-auto (pelapor bukan sistem / deviceid tidak auto)
            $pelanggaranTerlambatManual = $pelanggaranLain->filter(function ($p) {
                return $p->idpasal === $this->pasalTerlambat
                    && ! str_starts_with($p->deviceid ?? '', 'auto-');
            });

            $adaPelanggaranLain          = $pelanggaranLain->isNotEmpty();
            $adaTerlambatManual          = $pelanggaranTerlambatManual->isNotEmpty();

            // ── ATURAN 1: B012 + pelanggaran LAIN (apapun) → terlambat + hapus B012 ──
            // ── ATURAN 2: B012 + B001 manual → hapus B012 ────────────────────────────
            if (! $adaPelanggaranLain && ! $adaTerlambatManual) {
                $skip++;
                continue; // Hanya alfa, tidak ada kondisi lain → biarkan
            }

            if ($this->dryRun) {
                $this->line("    [DRY] siswa_id={$siswaId} alfa akan dihapus" .
                    ($adaPelanggaranLain && ! $adaTerlambatManual ? ' + absen → terlambat 08:00' : ''));
                $alfaHapus++;
                if ($adaPelanggaranLain && ! $adaTerlambatManual) {
                    $absenUpdate++;
                }
                continue;
            }

            try {
                DB::transaction(function () use (
                    $siswaId, $tanggal, $alfaGroup,
                    $adaPelanggaranLain, $adaTerlambatManual,
                    &$absenUpdate, &$alfaHapus
                ) {
                    // 1. Hapus SEMUA pelanggaran alfa B012 siswa ini di hari ini (soft-delete)
                    //    Observer akan otomatis soft-delete transaksinya
                    foreach ($alfaGroup as $alfaRec) {
                        $alfaRec->delete();
                        $alfaHapus++;
                    }

                    // 2. Jika ada pelanggaran lain BUKAN B001 manual → set absen terlambat
                    //    (siswa masuk terlambat, bukan benar-benar alfa)
                    //    Tapi jika sudah ada B001 manual, absen mungkin sudah terlambat → skip update
                    if ($adaPelanggaranLain && ! $adaTerlambatManual) {
                        $this->updateAbsenJadiTerlambat($siswaId, $tanggal);
                        $absenUpdate++;
                    } elseif ($adaTerlambatManual) {
                        // Ada B001 manual = siswa sudah absen terlambat secara manual
                        // Pastikan status absen reflect terlambat
                        $this->updateAbsenJadiTerlambat($siswaId, $tanggal, skipIfAlreadyTerlambat: true);
                    }
                });

                Log::channel('sis')->info('[SyncAbsenDariPelanggaran] Proses berhasil', [
                    'siswa_id'              => $siswaId,
                    'tanggal'               => $tanggal,
                    'ada_pelanggaran_lain'  => $adaPelanggaranLain,
                    'ada_terlambat_manual'  => $adaTerlambatManual,
                ]);

            } catch (\Throwable $e) {
                Log::channel('sis')->error('[SyncAbsenDariPelanggaran] GAGAL', [
                    'siswa_id' => $siswaId,
                    'tanggal'  => $tanggal,
                    'error'    => $e->getMessage(),
                ]);
                $this->error("    GAGAL siswa_id={$siswaId}: {$e->getMessage()}");
            }
        }

        return [$absenUpdate, $alfaHapus, $skip];
    }

    /**
     * Update record absensi siswa menjadi terlambat.
     *
     * @param  bool  $skipIfAlreadyTerlambat  Jika true, skip jika sudah terlambat/hadir
     */
    private function updateAbsenJadiTerlambat(
        int $siswaId,
        string $tanggal,
        bool $skipIfAlreadyTerlambat = false
    ): void {
        $absen = AbsenSiswa::where('siswa_id', $siswaId)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if (! $absen) {
            // Tidak ada record absen → buat baru dengan status terlambat
            $siswa = Siswa::find($siswaId);
            if (! $siswa) return;

            AbsenSiswa::create([
                'siswa_id'     => $siswaId,
                'kelas_id'     => $siswa->kelas_id,
                'tanggal'      => $tanggal,
                'jam_masuk'    => '08:00:00',
                'status_masuk' => 'terlambat',
                'status'       => 'terlambat',      // legacy
                'jenis'        => 'masuk',           // legacy
                'catatan'      => 'Diupdate otomatis: ada pelanggaran lain di hari ini, alfa dihapus.',
            ]);
            return;
        }

        $statusSaatIni = $absen->status_masuk ?? $absen->status;

        // Jika flag skip dan sudah terlambat/hadir → tidak perlu ubah
        if ($skipIfAlreadyTerlambat && in_array($statusSaatIni, ['terlambat', 'hadir'])) {
            return;
        }

        // Jika sudah izin/sakit → jangan timpa
        if (in_array($statusSaatIni, ['izin', 'sakit'])) {
            return;
        }

        // Update ke terlambat
        // Jika sudah ada jam_masuk, pertahankan; jika tidak, set 08:00
        $jamMasuk = $absen->jam_masuk ?: '08:00:00';

        $absen->update([
            'status_masuk' => 'terlambat',
            'status'       => 'terlambat',  // legacy
            'jam_masuk'    => $jamMasuk,
            'catatan'      => trim(($absen->catatan ?? '') . ' [Auto: alfa→terlambat]'),
        ]);
        // AbsenSiswaObserver::updated() akan trigger penyesuaian poin terlambat otomatis
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function resolveTanggalList(): array
    {
        $from = $this->option('from');
        $to   = $this->option('to');
        $date = $this->option('date');

        if ($from) {
            $start = Carbon::parse($from)->startOfDay();
            $end   = $to ? Carbon::parse($to)->startOfDay() : Carbon::today();
            if ($end->lt($start)) {
                $this->error('--to harus setelah --from');
                return [];
            }
            $list = [];
            $cur  = $start->copy();
            while ($cur->lte($end)) {
                $list[] = $cur->toDateString();
                $cur->addDay();
            }
            return $list;
        }

        return [$date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString()];
    }
}
