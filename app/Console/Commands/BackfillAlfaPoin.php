<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Pelanggaran;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\SubPasal;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackfillAlfaPoin extends Command
{
    protected $signature = 'absen:backfill-alfa-poin
                            {--date= : Tanggal target format YYYY-MM-DD (default: hari ini)}
                            {--dry-run : Simulasi saja, tidak menyimpan ke database}';

    protected $description = 'Backfill poin pelanggaran untuk siswa yang sudah punya record alfa tapi belum punya poin';

    public function handle(): int
    {
        $sekolah = Sekolah::aktif();

        if (! $sekolah?->auto_point_alfa_enabled || ! $sekolah?->pasal_alfa_id) {
            $this->warn('auto_point_alfa_enabled tidak aktif atau pasal_alfa_id belum dikonfigurasi.');
            return self::FAILURE;
        }

        $tanggalStr = $this->option('date') ?? now(config('app.timezone', 'Asia/Jakarta'))->toDateString();
        $dryRun     = $this->option('dry-run');

        $this->info("Backfill poin alfa untuk tanggal: {$tanggalStr}" . ($dryRun ? ' [DRY RUN]' : ''));

        // Ambil pasal
        $pasal = SubPasal::where('idpasal', $sekolah->pasal_alfa_id)
            ->orderByDesc('thnajaran')
            ->first();

        if (! $pasal) {
            $this->error("Pasal ID '{$sekolah->pasal_alfa_id}' tidak ditemukan.");
            return self::FAILURE;
        }

        $this->info("Pasal: [{$pasal->idpasal}] {$pasal->pasal} — poin: {$pasal->poin_default}");

        $deviceId    = 'auto-alfa-' . $tanggalStr;
        $tahunAjaran = $this->getTahunAjaran();

        // Ambil semua siswa yang alfa hari ini (pola unified: cek status_masuk atau status legacy)
        $alfaSiswaIds = DB::table('absen_siswa')
            ->where(function ($q) {
                $q->where('status_masuk', 'alfa')
                  ->orWhere(function ($q2) {
                      $q2->whereNull('status_masuk')->where('status', 'alfa');
                  });
            })
            ->whereNull('jam_masuk')  // pastikan memang tidak hadir (bukan yang update manual)
            ->whereDate('tanggal', $tanggalStr)
            ->pluck('siswa_id');

        $this->info("Siswa alfa pada tanggal {$tanggalStr}: {$alfaSiswaIds->count()}");

        // Filter yang belum punya poin
        $sudahPoinIds = Pelanggaran::where('deviceid', $deviceId)
            ->pluck('siswa_id');

        $perluPoin = $alfaSiswaIds->diff($sudahPoinIds);

        $this->info("Sudah punya poin: {$sudahPoinIds->count()}");
        $this->info("Perlu dibuatkan poin: {$perluPoin->count()}");

        if ($perluPoin->isEmpty()) {
            $this->info('Semua siswa alfa sudah punya poin. Tidak ada yang perlu dibackfill.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("[DRY RUN] Akan dibuat {$perluPoin->count()} poin pelanggaran (tidak disimpan).");
            // Tampilkan sample
            $sample = Siswa::whereIn('id', $perluPoin->take(5))->with('kelas')->get();
            foreach ($sample as $s) {
                $this->line("  → {$s->nama_lengkap} ({$s->nis}) kelas {$s->kelas?->nama_kelas}");
            }
            if ($perluPoin->count() > 5) {
                $this->line("  ... dan " . ($perluPoin->count() - 5) . " siswa lainnya");
            }
            return self::SUCCESS;
        }

        // Proses backfill
        $diberikan = 0;
        $gagal     = 0;

        Siswa::whereIn('id', $perluPoin)
            ->with('kelas')
            ->chunkById(200, function ($siswaList) use (
                $tanggalStr, $deviceId, $pasal, $tahunAjaran, &$diberikan, &$gagal
            ) {
                foreach ($siswaList as $siswa) {
                    try {
                        DB::transaction(function () use ($siswa, $tanggalStr, $deviceId, $pasal, $tahunAjaran) {
                            // Double-check idempotency di dalam transaksi
                            $exists = Pelanggaran::where('deviceid', $deviceId)
                                ->where('siswa_id', $siswa->id)
                                ->exists();

                            if ($exists) return;

                            Pelanggaran::create([
                                'siswa_id'     => $siswa->id,
                                'tgl'          => $tanggalStr,
                                'tahun_ajaran' => $tahunAjaran,
                                'deviceid'     => $deviceId,
                                'noreg'        => $siswa->noreg_legacy ?? $siswa->nis ?? '',
                                'nama'         => $siswa->nama_lengkap,
                                'kelas'        => $siswa->kelas?->nama_kelas ?? '',
                                'idpasal'      => $pasal->idpasal,
                                'isi'          => 'Alfa — tidak hadir tanpa keterangan',
                                'poin'         => $pasal->poin_default ?? $pasal->skormin ?? 0,
                                'pelapor'      => 'Sistem',
                                'created_by'   => null,
                            ]);
                        });

                        $diberikan++;
                        $this->line("  ✓ {$siswa->nama_lengkap} ({$siswa->nis}) kelas {$siswa->kelas?->nama_kelas}");

                    } catch (\Throwable $e) {
                        $gagal++;
                        $this->error("  ✗ GAGAL siswa #{$siswa->id} ({$siswa->nama_lengkap}): " . $e->getMessage());
                        Log::channel('sis')->error('[BackfillAlfaPoin] GAGAL siswa #' . $siswa->id . ': ' . $e->getMessage());
                    }
                }
            });

        $this->info("\nSelesai. Poin diberikan: {$diberikan} | Gagal: {$gagal}");

        return self::SUCCESS;
    }

    private function getTahunAjaran(): string
    {
        try {
            $active = AcademicYear::where('is_active', true)->first();
            if ($active && $active->year_start && $active->year_end) {
                return $active->year_start . '/' . $active->year_end;
            }
        } catch (\Throwable) {
            // fallback
        }

        $year  = (int) now()->format('Y');
        $month = (int) now()->format('n');

        return $month < 7
            ? ($year - 1) . '/' . $year
            : $year . '/' . ($year + 1);
    }
}
