<?php

namespace App\Console\Commands;

use App\Models\AbsenSiswa;
use App\Models\AcademicYear;
use App\Models\AbsenEvent;
use App\Models\Event;
use App\Models\Penghargaan;
use App\Models\Pelanggaran;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\SubPasal;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BackfillAgustusCommand
 *
 * Menjalankan backfill poin bulan Agustus (atau bulan/tahun tertentu):
 *   1. Auto poin ALFA       — dari absen_siswa status_masuk = alfa
 *   2. Auto poin TERLAMBAT  — dari absen_siswa status_masuk = terlambat
 *   3. Auto poin HADIR      — dari absen_siswa status_masuk = hadir (penghargaan)
 *   4. Auto poin PELANGGARAN EVENT — siswa tidak scan event
 *   5. Auto poin PENGHARGAAN EVENT — siswa scan masuk event
 *   6. ACC penghargaan dari sistem (hadir + event) yang belum di-ACC
 *      → penghargaan manual (deviceid != auto-*) TIDAK disentuh
 *
 * Usage:
 *   php artisan sis:backfill-agustus
 *   php artisan sis:backfill-agustus --bulan=8 --tahun=2026
 *   php artisan sis:backfill-agustus --acc-only        (hanya fix ACC, skip backfill poin)
 *   php artisan sis:backfill-agustus --no-interaction  (auto-confirm semua)
 */
class BackfillAgustusCommand extends Command
{
    protected $signature = 'sis:backfill-agustus
                            {--bulan=8        : Bulan yang diproses (1-12)}
                            {--tahun=2026     : Tahun yang diproses}
                            {--acc-only       : Hanya jalankan fix ACC, skip backfill poin}
                            {--poin-only      : Hanya backfill poin, skip fix ACC}
                            {--dry-run        : Tampilkan preview tanpa menyimpan}';

    protected $description = 'Backfill poin alfa/terlambat/hadir + poin event + fix ACC penghargaan sistem untuk bulan tertentu';

    private bool   $dryRun    = false;
    private string $tahunAjaran;
    private array  $results   = [];

    public function handle(): int
    {
        $bulan         = (int) $this->option('bulan');
        $tahun         = (int) $this->option('tahun');
        $this->dryRun  = (bool) $this->option('dry-run');
        $accOnly       = (bool) $this->option('acc-only');
        $poinOnly      = (bool) $this->option('poin-only');

        $this->tahunAjaran = $this->getTahunAjaran();
        $bulanLabel        = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');

        $this->info('');
        $this->line('<fg=cyan>╔══════════════════════════════════════════════════════╗</>');
        $this->line('<fg=cyan>║     BACKFILL POIN + FIX ACC — ' . str_pad($bulanLabel, 22) . '     ║</>');
        $this->line('<fg=cyan>╚══════════════════════════════════════════════════════╝</>');
        if ($this->dryRun) {
            $this->warn('  ⚠  MODE DRY-RUN — tidak ada data yang disimpan');
        }
        $this->info('');
        $this->line("  Bulan       : {$bulanLabel}");
        $this->line("  Tahun ajaran: {$this->tahunAjaran}");
        $this->info('');

        $sekolah = Sekolah::aktif();

        // ── 1–3: Backfill poin harian ─────────────────────────────────────
        if (! $accOnly) {
            $this->backfillPoinHarian($bulan, $tahun, $sekolah);
            $this->backfillPoinEvent($bulan, $tahun);
        }

        // ── 6: Fix ACC penghargaan sistem ─────────────────────────────────
        if (! $poinOnly) {
            $this->fixAccPenghargaanSistem($bulan, $tahun);
        }

        $this->printSummary();
        return self::SUCCESS;
    }

    // ══════════════════════════════════════════════════════════════════════
    // BACKFILL 1–3: Poin harian (alfa / terlambat / hadir)
    // ══════════════════════════════════════════════════════════════════════

    private function backfillPoinHarian(int $bulan, int $tahun, $sekolah): void
    {
        $this->line('<fg=yellow>━━━ BACKFILL POIN HARIAN (alfa/terlambat/hadir) ━━━━━━━</>');

        $pasalAlfaId      = $sekolah?->pasal_alfa_id;
        $pasalTerlambatId = $sekolah?->auto_poin_terlambat_enabled ? $sekolah?->pasal_terlambat_id : null;
        $pasalHadirId     = $sekolah?->auto_poin_hadir_enabled     ? $sekolah?->pasal_hadir_id     : null;

        $this->line("  pasal_alfa_id      : " . ($pasalAlfaId      ?? '<fg=red>tidak dikonfigurasi</>'));
        $this->line("  pasal_terlambat_id : " . ($pasalTerlambatId ?? '<fg=yellow>nonaktif</>'));
        $this->line("  pasal_hadir_id     : " . ($pasalHadirId     ?? '<fg=yellow>nonaktif</>'));
        $this->info('');

        $pasalAlfa      = $pasalAlfaId      ? SubPasal::where('idpasal', $pasalAlfaId)->first()      : null;
        $pasalTerlambat = $pasalTerlambatId ? SubPasal::where('idpasal', $pasalTerlambatId)->first() : null;
        $pasalHadir     = $pasalHadirId     ? SubPasal::where('idpasal', $pasalHadirId)->first()     : null;

        // Ambil semua tanggal di bulan ini yang punya absen
        $tanggals = AbsenSiswa::selectRaw('DATE(tanggal) as tgl')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->groupByRaw('DATE(tanggal)')
            ->orderBy('tgl')
            ->pluck('tgl');

        $totalAlfa = $totalTerlambat = $totalHadir = $skipped = 0;

        foreach ($tanggals as $tgl) {
            $absenList = AbsenSiswa::with('siswa.kelas')
                ->whereDate('tanggal', $tgl)
                ->whereNotNull('status_masuk')
                ->whereIn('status_masuk', ['alfa', 'terlambat', 'hadir'])
                ->get();

            foreach ($absenList as $absen) {
                $siswa    = $absen->siswa;
                if (! $siswa) { $skipped++; continue; }

                $status = $absen->status_masuk;

                // ── ALFA ────────────────────────────────────────────────
                if ($status === 'alfa' && $pasalAlfa) {
                    $deviceId = 'auto-alfa-' . $tgl;
                    if (! Pelanggaran::where('siswa_id', $siswa->id)->where('deviceid', $deviceId)->exists()) {
                        $this->simpanPelanggaran($siswa, $tgl, $deviceId, $pasalAlfa, 'Tidak hadir (alfa)');
                        $totalAlfa++;
                    } else { $skipped++; }
                }

                // ── TERLAMBAT ───────────────────────────────────────────
                elseif ($status === 'terlambat' && $pasalTerlambat) {
                    $deviceId = 'auto-terlambat-' . $tgl;
                    if (! Pelanggaran::where('siswa_id', $siswa->id)->where('deviceid', $deviceId)->exists()) {
                        $this->simpanPelanggaran($siswa, $tgl, $deviceId, $pasalTerlambat, 'Terlambat masuk sekolah');
                        $totalTerlambat++;
                    } else { $skipped++; }
                }

                // ── HADIR ───────────────────────────────────────────────
                elseif ($status === 'hadir' && $pasalHadir) {
                    $deviceId = 'auto-hadir-' . $tgl;
                    if (! Penghargaan::where('siswa_id', $siswa->id)->where('deviceid', $deviceId)->exists()) {
                        $this->simpanPenghargaanSistem($siswa, $tgl, $deviceId, $pasalHadir,
                            'Hadir tepat waktu', 'Auto dari absensi harian');
                        $totalHadir++;
                    } else { $skipped++; }
                }
            }
        }

        $this->line("  <fg=green>✅</> Alfa baru       : {$totalAlfa}");
        $this->line("  <fg=green>✅</> Terlambat baru  : {$totalTerlambat}");
        $this->line("  <fg=green>✅</> Hadir baru      : {$totalHadir}");
        $this->line("  <fg=gray>⏭  Sudah ada/skip   : {$skipped}</>");
        $this->results['poin_alfa']      = $totalAlfa;
        $this->results['poin_terlambat'] = $totalTerlambat;
        $this->results['poin_hadir']     = $totalHadir;
        $this->info('');
    }

    // ══════════════════════════════════════════════════════════════════════
    // BACKFILL 4–5: Poin event (pelanggaran + penghargaan)
    // ══════════════════════════════════════════════════════════════════════

    private function backfillPoinEvent(int $bulan, int $tahun): void
    {
        $this->line('<fg=yellow>━━━ BACKFILL POIN EVENT (pelanggaran + penghargaan) ━━━</>');

        // Event yang sudah selesai di bulan ini, auto aktif
        // Catatan: TIDAK filter whereNull(processed_at) — backfill harus bisa jalan
        // berkali-kali. Duplikasi dicegah per-siswa via deviceId di dalam loop.
        $events = Event::query()
            ->where(function ($q) use ($bulan, $tahun) {
                // Event yang tanggal_mulai ATAU tanggal_selesai berada di bulan ini
                $q->where(function ($q2) use ($bulan, $tahun) {
                    $q2->whereMonth('tanggal_mulai', $bulan)
                       ->whereYear('tanggal_mulai', $tahun);
                })->orWhere(function ($q2) use ($bulan, $tahun) {
                    $q2->whereMonth('tanggal_selesai', $bulan)
                       ->whereYear('tanggal_selesai', $tahun);
                });
            })
            ->where('tanggal_selesai', '<', now())
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('auto_point_pelanggaran', true)
                       ->whereNotNull('pasal_pelanggaran_id');
                })->orWhere(function ($q2) {
                    $q2->where('auto_penghargaan', true)
                       ->whereNotNull('pasal_penghargaan_id');
                });
            })
            ->with(['pasalPelanggaran', 'pasalPenghargaan'])
            ->get();

        $this->line("  Event ditemukan : {$events->count()}");

        $totalPel = $totalPgh = $skippedEvent = 0;

        foreach ($events as $event) {
            $this->line("  <fg=gray>→ Event #{$event->id}: {$event->nama_event}</>");

            // ── Pelanggaran event ──────────────────────────────────────
            if ($event->auto_point_pelanggaran && $event->pasalPelanggaran) {
                $pesertaIds  = $this->getPesertaIds($event);
                $sudahScan   = AbsenEvent::where('event_id', $event->id)
                    ->whereNotNull('waktu_masuk')->pluck('siswa_id')->toArray();
                $belumScan   = array_diff($pesertaIds, $sudahScan);
                $tglEvent    = Carbon::parse($event->tanggal_mulai)->toDateString();
                $pasal       = $event->pasalPelanggaran;

                foreach ($belumScan as $sId) {
                    $deviceId = 'auto-event-' . $event->id;
                    if (Pelanggaran::where('siswa_id', $sId)->where('deviceid', $deviceId)->exists()) {
                        $skippedEvent++; continue;
                    }
                    $absen       = AbsenSiswa::where('siswa_id', $sId)->whereDate('tanggal', $tglEvent)->first();
                    $statusMasuk = $absen?->status_masuk ?? $absen?->status;
                    if (! $absen || ! in_array($statusMasuk, ['hadir', 'terlambat'])) {
                        $skippedEvent++; continue;
                    }
                    $siswa = Siswa::find($sId);
                    if (! $siswa) { $skippedEvent++; continue; }

                    $this->simpanPelanggaran($siswa, $tglEvent, $deviceId, $pasal,
                        'Tidak hadir pada event: ' . $event->nama_event,
                        $event->poin_pelanggaran_event, $event->created_by);
                    $totalPel++;
                }

                if (! $this->dryRun && $totalPel > 0) {
                    // Tandai hanya jika memang ada pelanggaran baru yang diproses
                    // (opsional, hanya untuk audit — tidak menghalangi backfill ulang)
                }
            }

            // ── Penghargaan event ──────────────────────────────────────
            if ($event->auto_penghargaan && $event->pasalPenghargaan) {
                $sudahScan = AbsenEvent::where('event_id', $event->id)
                    ->whereNotNull('waktu_masuk')->pluck('siswa_id')->toArray();
                $pasal     = $event->pasalPenghargaan;

                foreach ($sudahScan as $sId) {
                    $deviceId = 'auto-event-penghargaan-' . $event->id;
                    if (Penghargaan::where('siswa_id', $sId)->where('deviceid', $deviceId)->exists()) {
                        $skippedEvent++; continue;
                    }
                    $siswa = Siswa::find($sId);
                    if (! $siswa) { $skippedEvent++; continue; }

                    $this->simpanPenghargaanSistem($siswa,
                        Carbon::parse($event->tanggal_selesai)->toDateString(),
                        $deviceId, $pasal,
                        'Berhasil hadir pada event: ' . $event->nama_event,
                        'Auto dari event',
                        $event->poin_penghargaan_event, $event->created_by);
                    $totalPgh++;
                }

                if (! $this->dryRun) {
                    $event->update(['auto_penghargaan_processed_at' => now()]);
                }
            }
        }

        $this->line("  <fg=green>✅</> Pelanggaran event baru  : {$totalPel}");
        $this->line("  <fg=green>✅</> Penghargaan event baru  : {$totalPgh}");
        $this->line("  <fg=gray>⏭  Sudah ada/skip           : {$skippedEvent}</>");
        $this->results['poin_event_pelanggaran'] = $totalPel;
        $this->results['poin_event_penghargaan'] = $totalPgh;
        $this->info('');
    }

    // ══════════════════════════════════════════════════════════════════════
    // FIX ACC: Penghargaan dari sistem yang belum di-ACC
    // Hanya menyentuh deviceid = auto-hadir-* dan auto-event-penghargaan-*
    // Penghargaan manual (deviceid = web / lainnya) TIDAK disentuh sama sekali
    // ══════════════════════════════════════════════════════════════════════

    private function fixAccPenghargaanSistem(int $bulan, int $tahun): void
    {
        $this->line('<fg=yellow>━━━ FIX ACC — Penghargaan Sistem Belum Di-ACC ━━━━━━━━━</>');
        $this->line("  <fg=gray>Hanya: auto-hadir-* dan auto-event-penghargaan-*</>");
        $this->line("  <fg=gray>Penghargaan manual (pelapor bukan Sistem) TIDAK disentuh</>");
        $this->info('');

        // Preview dulu berapa yang akan di-fix
        $pending = Penghargaan::whereMonth('tgl', $bulan)
            ->whereYear('tgl', $tahun)
            ->where(function ($q) {
                $q->where('deviceid', 'like', 'auto-hadir-%')
                  ->orWhere('deviceid', 'like', 'auto-event-penghargaan-%');
            })
            ->where(function ($q) {
                $q->where('acc', '!=', 'YA')
                  ->orWhereNull('acc')
                  ->orWhere('acc', '');
            })
            ->whereNull('deleted_at')
            ->get();

        $this->line("  Ditemukan {$pending->count()} penghargaan sistem belum ACC");

        if ($pending->isEmpty()) {
            $this->line("  <fg=green>✅ Semua penghargaan sistem sudah ACC.</>");
            $this->results['acc_fixed'] = 0;
            $this->info('');
            return;
        }

        // Tampilkan breakdown
        $hadirCount = $pending->filter(fn($p) => str_starts_with($p->deviceid, 'auto-hadir-'))->count();
        $eventCount = $pending->filter(fn($p) => str_starts_with($p->deviceid, 'auto-event-penghargaan-'))->count();
        $this->line("    auto-hadir-*              : {$hadirCount}");
        $this->line("    auto-event-penghargaan-*  : {$eventCount}");
        $this->info('');

        if ($this->dryRun) {
            $this->warn("  DRY-RUN: {$pending->count()} record akan di-ACC (tidak disimpan)");
            $this->results['acc_fixed'] = 0;
            $this->info('');
            return;
        }

        $fixed   = 0;
        $gagal   = 0;
        $namaSys = 'Sistem';

        foreach ($pending as $pgh) {
            try {
                DB::transaction(function () use ($pgh, $namaSys) {
                    $pgh->update([
                        'acc'    => 'YA',
                        'tglacc' => now(),
                        'nmacc'  => $namaSys,
                    ]);
                    // PenghargaanObserver::updated() otomatis buat/update transaksi
                });
                $fixed++;
            } catch (\Throwable $e) {
                $gagal++;
                Log::channel('sis')->error('[BackfillAgustus] Gagal ACC penghargaan #' . $pgh->idpen . ': ' . $e->getMessage());
            }
        }

        $this->line("  <fg=green>✅ Berhasil di-ACC : {$fixed}</>");
        if ($gagal > 0) {
            $this->line("  <fg=red>❌ Gagal           : {$gagal}</>");
        }
        $this->results['acc_fixed'] = $fixed;
        $this->info('');
    }

    // ══════════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════════

    private function simpanPelanggaran($siswa, string $tgl, string $deviceId, $pasal,
        string $isi, ?int $poinOverride = null, ?int $createdBy = null): void
    {
        if ($this->dryRun) return;

        Pelanggaran::create([
            'siswa_id'     => $siswa->id,
            'tgl'          => Carbon::parse($tgl),
            'tahun_ajaran' => $this->tahunAjaran,
            'deviceid'     => $deviceId,
            'noreg'        => $siswa->nis ?? '',
            'nama'         => $siswa->nama_lengkap,
            'kelas'        => $siswa->kelas?->nama_kelas ?? '',
            'idpasal'      => $pasal->idpasal,
            'isi'          => $isi,
            'poin'         => $poinOverride ?? $pasal->poin_default,
            'pelapor'      => 'Sistem',
            'created_by'   => $createdBy,
        ]);
    }

    private function simpanPenghargaanSistem($siswa, string $tgl, string $deviceId, $pasal,
        string $isi, string $ket, ?int $poinOverride = null, ?int $createdBy = null): void
    {
        if ($this->dryRun) return;

        Penghargaan::create([
            'siswa_id'     => $siswa->id,
            'tgl'          => Carbon::parse($tgl),
            'tahun_ajaran' => $this->tahunAjaran,
            'deviceid'     => $deviceId,
            'noreg'        => $siswa->nis ?? '',
            'nama'         => $siswa->nama_lengkap,
            'kelas'        => $siswa->kelas?->nama_kelas ?? '',
            'idpasal'      => $pasal->idpasal,
            'isi'          => $isi,
            'poin'         => $poinOverride ?? $pasal->poin_default,
            'pelapor'      => 'Sistem',
            'ket'          => $ket,
            'acc'          => 'YA',
            'tglacc'       => now(),
            'nmacc'        => 'Sistem',
            'created_by'   => $createdBy,
        ]);
    }

    private function getPesertaIds(Event $event): array
    {
        if ($event->berlaku_untuk_semua) {
            return Siswa::where('status_aktif', true)->pluck('id')->toArray();
        }
        if ($event->mode_peserta === 'kelas') {
            $kelasIds = $event->kelas()->pluck('kelas.id')->toArray();
            return Siswa::where('status_aktif', true)->whereIn('kelas_id', $kelasIds)->pluck('id')->toArray();
        }
        return $event->siswa()->pluck('siswas.id')->toArray();
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

    private function printSummary(): void
    {
        $this->line('<fg=cyan>╔══════════════════════════════════════════════════════╗</>');
        $this->line('<fg=cyan>║  RINGKASAN                                           ║</>');
        $this->line('<fg=cyan>╚══════════════════════════════════════════════════════╝</>');
        $this->line('  Poin alfa baru            : ' . ($this->results['poin_alfa']              ?? '-'));
        $this->line('  Poin terlambat baru        : ' . ($this->results['poin_terlambat']         ?? '-'));
        $this->line('  Poin hadir baru            : ' . ($this->results['poin_hadir']             ?? '-'));
        $this->line('  Poin event pelanggaran baru: ' . ($this->results['poin_event_pelanggaran'] ?? '-'));
        $this->line('  Poin event penghargaan baru: ' . ($this->results['poin_event_penghargaan'] ?? '-'));
        $this->line('  Penghargaan di-ACC         : ' . ($this->results['acc_fixed']              ?? '-'));
        if ($this->dryRun) {
            $this->warn('  ⚠  DRY-RUN — tidak ada yang disimpan');
        }
        $this->info('');
    }
}
