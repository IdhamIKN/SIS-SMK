<?php

namespace App\Console\Commands;

use App\Services\PklService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;

/**
 * AutoPoinPklHarian
 *
 * Memberikan poin otomatis PKL (hadir/terlambat/alfa) berdasarkan
 * status absensi harian siswa yang sedang aktif PKL.
 *
 * Konfigurasi poin per-lokasi PKL (bukan dari tblsekolah).
 * Idempotent — aman dijalankan berulang (cek deviceid).
 *
 * Usage:
 *   php artisan pkl:auto-poin-harian                         → proses hari ini
 *   php artisan pkl:auto-poin-harian --date=2026-08-28       → tanggal tertentu
 *   php artisan pkl:auto-poin-harian --from=2026-08-01 --to=2026-08-07  → backfill
 */
class AutoPoinPklHarian extends Command
{
    protected $signature = 'pkl:auto-poin-harian
                            {--date=  : Tanggal target YYYY-MM-DD (default: hari ini)}
                            {--from=  : Tanggal mulai backfill YYYY-MM-DD}
                            {--to=    : Tanggal akhir backfill YYYY-MM-DD}';

    protected $description = 'Beri poin otomatis PKL (hadir/terlambat/alfa) berdasarkan absensi harian siswa PKL';

    public function __construct(private readonly PklService $pklService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $tanggalList = $this->resolveTanggalList();

        if (empty($tanggalList)) {
            $this->error('Tidak ada tanggal yang valid untuk diproses.');
            return self::FAILURE;
        }

        $this->info('');
        $this->info("Memproses " . count($tanggalList) . " tanggal (auto poin PKL)...");

        $totalHadir     = 0;
        $totalTerlambat = 0;
        $totalAlfa      = 0;
        $totalSkip      = 0;
        $totalGagal     = 0;

        foreach ($tanggalList as $tanggal) {
            $result = $this->pklService->beriPoinHarian($tanggal);
            $this->line("  <fg=cyan>[{$tanggal}]</> hadir={$result['hadir']} terlambat={$result['terlambat']} alfa={$result['alfa']} skip={$result['skip']} gagal={$result['gagal']}");

            $totalHadir     += $result['hadir'];
            $totalTerlambat += $result['terlambat'];
            $totalAlfa      += $result['alfa'];
            $totalSkip      += $result['skip'];
            $totalGagal     += $result['gagal'];
        }

        $this->info('');
        $this->info('Selesai.');
        $this->line("  Hadir (penghargaan)  : {$totalHadir}");
        $this->line("  Terlambat (pel.)     : {$totalTerlambat}");
        $this->line("  Alfa PKL (pel.)      : {$totalAlfa}");
        $this->line("  Dilewati             : {$totalSkip}");

        if ($totalGagal > 0) {
            $this->error("  Gagal                : {$totalGagal}");
        }

        return self::SUCCESS;
    }

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
            return collect(CarbonPeriod::create($start, $end))
                ->map(fn($d) => $d->toDateString())
                ->toArray();
        }

        return [$date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString()];
    }
}
