<?php

namespace App\Console\Commands;

use App\Models\AbsenSiswa;
use App\Models\Pelanggaran;
use App\Models\Penghargaan;
use App\Models\Siswa;
use App\Models\SubPasal;
use App\Models\AcademicYear;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * BackfillDailyAttendanceEvent
 *
 * Analisa perbedaan model absen antara tanggal 1-6 dan 7-sekarang.
 * Jika rentang 1-6 belum menerapkan record absen harian, lakukan backfill:
 *  - untuk siswa yang punya AbsenEvent hadir -> set AbsenSiswa status menjadi 'hadir' (atau 'terlambat' jika sudah ada B001)
 *  - buat Penghargaan untuk yang hadir (deviceid auto-event-penghargaan-{event_id})
 *  - buat Pelanggaran untuk peserta yang tidak hadir (deviceid auto-event-{event_id})
 *
 * Usage:
 *  php artisan tatib:backfill-daily-attendance-event --from=2026-08-01 --to=2026-08-12 [--dry-run]
 */
class BackfillDailyAttendanceEvent extends Command
{
    protected $signature = 'tatib:backfill-daily-attendance-event
                            {--from= : Mulai tanggal (Y-m-d)}
                            {--to=   : Sampai tanggal (Y-m-d)}
                            {--dry-run : Simulasi, tidak menyimpan ke DB}
                            {--pasal-pelanggaran= : id pasal pelanggaran (opsional)}
                            {--pasal-penghargaan= : id pasal penghargaan (opsional)}';

    protected $description = 'Backfill/analisa absen harian berdasarkan AbsenEvent untuk rentang tanggal tertentu';

    public function handle(): int
    {
        $from = $this->option('from') ? Carbon::parse($this->option('from')) : Carbon::createFromDate(now()->year, 8, 1);
        $to   = $this->option('to')   ? Carbon::parse($this->option('to'))   : Carbon::today();

        $this->info("Backfill dari {$from->toDateString()} sampai {$to->toDateString()} (dry-run={$this->option('dry-run')})");

        $period = CarbonPeriod::create($from, $to);

        // Deteksi apakah tanggal 1-6 punya model absen berbeda: cek apakah AbsenSiswa records kosong untuk rentang awal
        $earlyEnd = $from->copy()->addDays(5);
        $countEarly = AbsenSiswa::whereBetween('tanggal', [$from->toDateString(), $earlyEnd->toDateString()])->count();
        $countLater = AbsenSiswa::whereBetween('tanggal', [$earlyEnd->addDay()->toDateString(), $to->toDateString()])->count();

        $this->line("AbsenSiswa count 1-6: {$countEarly}");
        $this->line("AbsenSiswa count 7-now: {$countLater}");

        $assumeEarlyNoDaily = $countEarly === 0 && $countLater > 0;
        if ($assumeEarlyNoDaily) {
            $this->warn('Terlihat rentang awal belum memiliki record absen harian; akan menggunakan AbsenEvent sebagai sumber kebenaran.');
        } else {
            $this->info('Tidak terdeteksi perbedaan model absen secara jelas; command akan tetap mencoba backfill berdasarkan AbsenEvent.');
        }

        // Gunakan hanya tabel AbsenSiswa untuk backfill.
        $pasalPelanggaranId = $this->option('pasal-pelanggaran');
        $pasalPenghargaanId = $this->option('pasal-penghargaan');

        $pasalPelanggaran = $pasalPelanggaranId ? SubPasal::find($pasalPelanggaranId) : null;
        $pasalPenghargaan = $pasalPenghargaanId ? SubPasal::find($pasalPenghargaanId) : null;

        $students = Siswa::where('status_aktif', true)->pluck('id')->toArray();

        foreach ($period as $date) {
            $tgl = $date->toDateString();
            // Lewati akhir pekan (Sabtu & Minggu)
            if ($date->isWeekend()) {
                $this->line("Lewati akhir pekan {$tgl}");
                continue;
            }

            $this->line("Memproses tanggal {$tgl}");

            foreach ($students as $siswaId) {
                $siswa = Siswa::find($siswaId);
                if (! $siswa) continue;

                $absen = AbsenSiswa::where('siswa_id', $siswaId)->whereDate('tanggal', $tgl)->first();

                if ($absen && in_array($absen->status_masuk, ['hadir','terlambat'])) {
                    $this->line("  [Ada Absen] siswa {$siswaId} status={$absen->status_masuk}");
                    if ($pasalPenghargaan) {
                        $deviceId = 'auto-hadir-' . $tgl;
                        $exists = Penghargaan::where('siswa_id', $siswaId)->where('deviceid', $deviceId)->whereNull('deleted_at')->exists();
                        if (! $exists) {
                            $this->line("    [Buat Penghargaan] siswa {$siswaId}");
                            if (! $this->option('dry-run')) {
                                $this->simpanPenghargaanMinimal($siswa, $tgl, $deviceId, $pasalPenghargaan, null, null);
                            }
                        }
                    }

                } else {
                    $this->line("  [Buat Pelanggaran] siswa {$siswaId} tidak hadir pada {$tgl}");
                    if ($pasalPelanggaran) {
                        $deviceId = 'auto-alfa-' . $tgl;
                        $exists = Pelanggaran::where('siswa_id', $siswaId)->where('deviceid', $deviceId)->whereNull('deleted_at')->exists();
                        if (! $exists) {
                            if (! $this->option('dry-run')) {
                                $this->simpanPelanggaranMinimal($siswa, $tgl, $deviceId, $pasalPelanggaran, null, null);
                            }
                        }
                    }
                }
            }
        }

        $this->info('Selesai backfill.');
        return self::SUCCESS;
    }

    private function simpanPenghargaanMinimal(Siswa $siswa, string $tgl, string $deviceId, ?SubPasal $pasal, ?int $poin = null, $createdBy = null): void
    {
        if (! $pasal) return;
        Penghargaan::create([
            'siswa_id' => $siswa->id,
            'tgl' => Carbon::parse($tgl),
            'tahun_ajaran' => $this->getTahunAjaran(),
            'deviceid' => $deviceId,
            'noreg' => $siswa->noreg ?? '',
            'nama' => $siswa->nama ?? '',
            'kelas' => $siswa->kelas?->nmkelas ?? '',
            'idpasal' => $pasal->idpasal,
            'isi' => 'Berhasil hadir pada event (backfill)'.
                ' ' . ($deviceId ?? ''),
            'poin' => $poin ?? $pasal->getPoinDefaultAttribute(),
            'pelapor' => 'sistem',
            'ket' => 'Auto backfill dari event',
            'acc' => 'YA',
            'tglacc' => now(),
            'nmacc' => 'Sistem',
            'created_by' => $createdBy,
        ]);
    }

    private function simpanPelanggaranMinimal(Siswa $siswa, string $tgl, string $deviceId, ?SubPasal $pasal, ?int $poin = null, $createdBy = null): void
    {
        if (! $pasal) return;
        Pelanggaran::create([
            'siswa_id' => $siswa->id,
            'tgl' => Carbon::parse($tgl),
            'tahun_ajaran' => $this->getTahunAjaran(),
            'deviceid' => $deviceId,
            'noreg' => $siswa->noreg ?? '',
            'nama' => $siswa->nama ?? '',
            'kelas' => $siswa->kelas?->nmkelas ?? '',
            'idpasal' => $pasal->idpasal,
            'isi' => 'Tidak hadir pada event (backfill)'. ' ' . ($deviceId ?? ''),
            'poin' => $poin ?? $pasal->getPoinDefaultAttribute(),
            'pelapor' => 'sistem',
            'created_by' => $createdBy,
        ]);
    }

    private function getTahunAjaran(): string
    {
        try {
            $active = AcademicYear::where('is_active', true)->first();
            if ($active && $active->year_start && $active->year_end) {
                return $active->year_start . '/' . $active->year_end;
            }
        } catch (\Throwable $e) {
            // fallback
        }

        $year  = (int) now()->format('Y');
        $month = (int) now()->format('n');

        return $month < 7
            ? ($year - 1) . '/' . $year
            : $year . '/' . ($year + 1);
    }
}
