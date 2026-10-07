<?php

namespace App\Console\Commands;

use App\Models\AbsenSiswa;
use App\Models\AcademicYear;
use App\Models\Pelanggaran;
use App\Models\Penghargaan;
use App\Models\Sekolah;
use App\Models\SubPasal;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AutoPoinKehadiranHarian
 *
 * Memberikan poin otomatis berdasarkan status kehadiran harian siswa:
 *   - Hadir tepat waktu  → poin penghargaan (pasal_hadir_id)
 *   - Terlambat          → poin pelanggaran (pasal_terlambat_id)
 *   - Alfa               → poin pelanggaran (pasal_alfa_id) ← sudah ada di AutoAlfaSiswa
 *                          command ini hanya mengisi yang terlewat
 *
 * Idempotent: jalankan berulang tidak akan membuat poin ganda.
 * Backfill: gunakan --from dan --to untuk proses data lama.
 *
 * Usage:
 *   php artisan absen:auto-poin-harian                          → proses hari ini
 *   php artisan absen:auto-poin-harian --date=2026-08-05        → proses tanggal tertentu
 *   php artisan absen:auto-poin-harian --from=2026-08-01 --to=2026-08-07  → backfill range
 */
class AutoPoinKehadiranHarian extends Command
{
    protected $signature = 'absen:auto-poin-harian
                            {--date=  : Tanggal target YYYY-MM-DD (default: hari ini)}
                            {--from=  : Tanggal mulai backfill YYYY-MM-DD}
                            {--to=    : Tanggal akhir backfill YYYY-MM-DD (default: hari ini)}
                            {--force  : Proses meski hari libur}';

    protected $description = 'Beri poin otomatis (hadir/terlambat/alfa) berdasarkan status absen harian siswa';

    private function plog(string $level, string $msg, array $ctx = []): void
    {
        Log::channel('sis')->{$level}('[AutoPoinKehadiran] ' . $msg, $ctx);
    }

    public function handle(): int
    {
        $sekolah = Sekolah::aktif();

        if ($sekolah?->sedangLibur() && ! $this->option('force')) {
            $this->info('Mode Libur Panjang aktif, dilewati.');
            return self::SUCCESS;
        }

        // Tentukan range tanggal
        $tanggalList = $this->resolveTanggalList();
        if (empty($tanggalList)) {
            $this->error('Tidak ada tanggal yang valid untuk diproses.');
            return self::FAILURE;
        }

        $this->info('');
        $this->info("Memproses " . count($tanggalList) . " tanggal...");

        $totalHadir     = 0;
        $totalTerlambat = 0;
        $totalAlfa      = 0;
        $totalSkip      = 0;
        $totalGagal     = 0;

        foreach ($tanggalList as $tanggal) {
            $result = $this->prosesTonggal($tanggal, $sekolah);
            $totalHadir     += $result['hadir'];
            $totalTerlambat += $result['terlambat'];
            $totalAlfa      += $result['alfa'];
            $totalSkip      += $result['skip'];
            $totalGagal     += $result['gagal'];
        }

        $this->info('');
        $this->info("Selesai.");
        $this->line("  Hadir (penghargaan) : {$totalHadir}");
        $this->line("  Terlambat (pel.)    : {$totalTerlambat}");
        $this->line("  Alfa (pel.)         : {$totalAlfa}");
        $this->line("  Dilewati            : {$totalSkip}");
        if ($totalGagal > 0) {
            $this->error("  Gagal               : {$totalGagal}");
        }

        return self::SUCCESS;
    }

    private function prosesTonggal(string $tanggal, ?Sekolah $sekolah): array
    {
        $result = ['hadir' => 0, 'terlambat' => 0, 'alfa' => 0, 'skip' => 0, 'gagal' => 0];

        $tahunAjaran  = $this->getTahunAjaran();

        // Konfigurasi dari sekolah
        $hadirConfig     = $this->resolveConfig($sekolah, 'hadir');
        $terlambatConfig = $this->resolveConfig($sekolah, 'terlambat');
        $alfaConfig      = $this->resolveConfig($sekolah, 'alfa');

        if (! $hadirConfig && ! $terlambatConfig && ! $alfaConfig) {
            $this->line("  <fg=yellow>[{$tanggal}]</> Semua auto poin dinonaktifkan, skip.");
            $result['skip']++;
            return $result;
        }

        $this->line("  <fg=cyan>[{$tanggal}]</> Proses...");

        // Ambil semua record absen pada tanggal ini yang sudah punya status
        AbsenSiswa::whereDate('tanggal', $tanggal)
            ->whereNotNull('status_masuk')
            ->with('siswa')
            ->orderBy('id')
            ->chunk(200, function ($records) use (
                $tanggal, $tahunAjaran, $hadirConfig, $terlambatConfig, $alfaConfig,
                &$result
            ) {
                foreach ($records as $absen) {
                    $siswa = $absen->siswa;
                    if (! $siswa) { $result['skip']++; continue; }

                    $status = $absen->status_masuk ?? $absen->status;

                    try {
                        $diberikan = match($status) {
                            'hadir'     => $hadirConfig
                                ? $this->beriPoinHadir($absen, $siswa, $tanggal, $tahunAjaran, $hadirConfig)
                                : false,
                            'terlambat' => $terlambatConfig
                                ? $this->beriPoinTerlambat($absen, $siswa, $tanggal, $tahunAjaran, $terlambatConfig)
                                : false,
                            'alfa'      => $alfaConfig
                                ? $this->beriPoinAlfa($absen, $siswa, $tanggal, $tahunAjaran, $alfaConfig)
                                : false,
                            default     => false, // izin, sakit → tidak dapat poin
                        };

                        if ($diberikan === true) {
                            $result[$status === 'hadir' ? 'hadir' : ($status === 'terlambat' ? 'terlambat' : 'alfa')]++;
                        } elseif ($diberikan === null) {
                            $result['skip']++;
                        }
                    } catch (\Throwable $e) {
                        $result['gagal']++;
                        $this->plog('error', "GAGAL siswa #{$siswa->id} tanggal {$tanggal}: " . $e->getMessage());
                    }
                }
            });

        $this->plog('info', "Selesai [{$tanggal}]", $result);
        return $result;
    }

    // ── Beri poin hadir (penghargaan) ─────────────────────────────────────

    private function beriPoinHadir(AbsenSiswa $absen, $siswa, string $tanggal, string $tahunAjaran, array $config): ?bool
    {
        $deviceId = 'auto-hadir-' . $tanggal;

        // Cek sudah ada (termasuk yang di-restore)
        if (Penghargaan::where('siswa_id', $siswa->id)->where('deviceid', $deviceId)->exists()) {
            return null; // sudah ada, skip
        }

        // Penghargaan::create() dengan acc='YA' men-trigger PenghargaanObserver::created()
        // yang otomatis sync ke tbltransaksi.
        // Jika observer gagal, exception roll back transaksi ini.
        DB::transaction(function () use ($siswa, $absen, $tanggal, $tahunAjaran, $config, $deviceId) {
            Penghargaan::create([
                'siswa_id'     => $siswa->id,
                'tgl'          => now(),
                'tahun_ajaran' => $tahunAjaran,
                'deviceid'     => $deviceId,
                'noreg'        => $siswa->nis ?? '',
                'nama'         => $siswa->nama_lengkap,
                'kelas'        => $siswa->kelas?->nama_kelas ?? '',
                'idpasal'      => $config['pasal']->idpasal,
                'isi'          => 'Hadir tepat waktu',
                'poin'         => $config['poin'],
                'pelapor'      => 'Sistem',
                'ket'          => 'Auto dari absensi harian',
                'acc'          => 'YA',
                'tglacc'       => now(),
                'nmacc'        => 'Sistem',
                'created_by'   => null,
            ]);
        });

        return true;
    }

    // ── Beri poin terlambat (pelanggaran) ─────────────────────────────────

    private function beriPoinTerlambat(AbsenSiswa $absen, $siswa, string $tanggal, string $tahunAjaran, array $config): ?bool
    {
        $deviceId = 'auto-terlambat-' . $tanggal;

        if (Pelanggaran::where('siswa_id', $siswa->id)->where('deviceid', $deviceId)->exists()) {
            return null;
        }

        // Pelanggaran::create() men-trigger PelanggaranObserver::created()
        // yang otomatis sync ke tbltransaksi.
        DB::transaction(function () use ($siswa, $tanggal, $tahunAjaran, $config, $deviceId) {
            Pelanggaran::create([
                'siswa_id'     => $siswa->id,
                'tgl'          => now(),
                'tahun_ajaran' => $tahunAjaran,
                'deviceid'     => $deviceId,
                'noreg'        => $siswa->nis ?? '',
                'nama'         => $siswa->nama_lengkap,
                'kelas'        => $siswa->kelas?->nama_kelas ?? '',
                'idpasal'      => $config['pasal']->idpasal,
                'isi'          => 'Terlambat masuk sekolah',
                'poin'         => $config['poin'],
                'pelapor'      => 'Sistem',
                'created_by'   => null,
            ]);
        });

        return true;
    }

    // ── Beri poin alfa (pelanggaran) ──────────────────────────────────────

    private function beriPoinAlfa(AbsenSiswa $absen, $siswa, string $tanggal, string $tahunAjaran, array $config): ?bool
    {
        $deviceId = 'auto-alfa-' . $tanggal;

        if (Pelanggaran::where('siswa_id', $siswa->id)->where('deviceid', $deviceId)->exists()) {
            return null;
        }

        // Pelanggaran::create() men-trigger PelanggaranObserver::created()
        // yang otomatis sync ke tbltransaksi.
        DB::transaction(function () use ($siswa, $tanggal, $tahunAjaran, $config, $deviceId) {
            Pelanggaran::create([
                'siswa_id'     => $siswa->id,
                'tgl'          => now(),
                'tahun_ajaran' => $tahunAjaran,
                'deviceid'     => $deviceId,
                'noreg'        => $siswa->nis ?? '',
                'nama'         => $siswa->nama_lengkap,
                'kelas'        => $siswa->kelas?->nama_kelas ?? '',
                'idpasal'      => $config['pasal']->idpasal,
                'isi'          => 'Alfa — tidak hadir tanpa keterangan',
                'poin'         => $config['poin'],
                'pelapor'      => 'Sistem',
                'created_by'   => null,
            ]);
        });

        return true;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Resolve konfigurasi poin untuk satu jenis status.
     * Return null jika fitur nonaktif atau pasal tidak ditemukan.
     */
    private function resolveConfig(?Sekolah $sekolah, string $jenis): ?array
    {
        $enabledKey = match($jenis) {
            'hadir'     => 'auto_poin_hadir_enabled',
            'terlambat' => 'auto_poin_terlambat_enabled',
            'alfa'      => 'auto_point_alfa_enabled',
        };
        $pasalKey = match($jenis) {
            'hadir'     => 'pasal_hadir_id',
            'terlambat' => 'pasal_terlambat_id',
            'alfa'      => 'pasal_alfa_id',
        };

        if (! $sekolah?->{$enabledKey}) return null;

        $pasalId = $sekolah->{$pasalKey};
        if (! $pasalId) return null;

        $pasal = SubPasal::where('idpasal', $pasalId)->orderByDesc('thnajaran')->first();
        if (! $pasal) return null;

        return [
            'pasal' => $pasal,
            'poin'  => $pasal->poin_default ?? $pasal->skormin ?? 0,
        ];
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

    private function getTahunAjaran(): string
    {
        try {
            $a = AcademicYear::where('is_active', true)->first();
            if ($a?->year_start && $a?->year_end) return $a->year_start . '/' . $a->year_end;
        } catch (\Throwable) {}
        $y = (int) now()->format('Y');
        $m = (int) now()->format('n');
        return $m < 7 ? ($y - 1) . '/' . $y : $y . '/' . ($y + 1);
    }
}
