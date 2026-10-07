<?php

namespace App\Console\Commands;

use App\Models\AbsenEvent;
use App\Models\AbsenSiswa;
use App\Models\AcademicYear;
use App\Models\Event;
use App\Models\Pelanggaran;
use App\Models\Penghargaan;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\SubPasal;
use App\Models\TransaksiPoin;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CleanupAndReprocessAutoPointCommand
 *
 * Membersihkan dan memproses ulang sistem poin otomatis dengan langkah-langkah:
 *
 * 1. TAMPILKAN RINGKASAN DATA SAAT INI
 *    - Total pelanggaran
 *    - Total penghargaan
 *    - Total transaksi otomatis (deviceid prefix 'auto-')
 *    - Total transaksi manual (deviceid tidak 'auto-')
 *    - Grand total
 *
 * 2. PERBAIKI STATUS KEHADIRAN SISWA
 *    - Jika ada B012 di suatu hari → status_masuk = 'alfa'
 *    - Jika Alfa + ada pasal lain di hari sama → status_masuk = 'terlambat', jam_masuk = '08:00'
 *    - Jika B012 + B001 manual → hapus B012
 *
 * 3. HAPUS SEMUA DATA OTOMATIS
 *    - Soft-delete semua pelanggaran dengan deviceid prefix 'auto-'
 *    - Soft-delete semua penghargaan dengan deviceid prefix 'auto-'
 *    - Soft-delete semua transaksi dengan deviceid prefix 'auto-'
 *
 * 4. JALANKAN ULANG PROSES AUTO-POINT
 *    - Dari 1 Agustus hingga hari ini
 *    - Backfill poin harian (alfa/terlambat/hadir)
 *    - Backfill poin event
 *    - Auto ACC penghargaan sistem
 *
 * Usage:
 *   php artisan tatib:cleanup-and-reprocess-auto-point
 *   php artisan tatib:cleanup-and-reprocess-auto-point --dry-run
 *   php artisan tatib:cleanup-and-reprocess-auto-point --no-interaction
 */
class CleanupAndReprocessAutoPointCommand extends Command
{
    protected $signature = 'tatib:cleanup-and-reprocess-auto-point
                            {--dry-run       : Simulasi, tidak menyimpan ke database}
                            {--skip-step2    : Lewati step 2 (perbaiki kehadiran)}
                            {--skip-step3    : Lewati step 3 (hapus data otomatis)}
                            {--skip-step4    : Lewati step 4 (reprocess auto-point)}';

    protected $description = 'Ringkas data → Perbaiki kehadiran → Hapus otomatis → Reprocess poin dari 1 Agustus s.d. hari ini';

    private bool   $dryRun = false;
    private string $tahunAjaran;
    private string $pasalAlfa      = 'B012';
    private string $pasalTerlambat = 'B001';
    private array  $summary        = [];

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $sekolah      = Sekolah::aktif();

        if (! $sekolah) {
            $this->error('Sekolah aktif tidak ditemukan. Pastikan konfigurasi sekolah sudah ada.');
            return self::FAILURE;
        }

        // Override pasal dari config sekolah jika ada
        if ($sekolah->pasal_alfa_id) {
            $this->pasalAlfa = $sekolah->pasal_alfa_id;
        }
        if ($sekolah->pasal_terlambat_id) {
            $this->pasalTerlambat = $sekolah->pasal_terlambat_id;
        }

        $this->tahunAjaran = $this->getTahunAjaran();

        $this->info('');
        $this->line('<fg=cyan>╔════════════════════════════════════════════════════════════════╗</>');
        $this->line('<fg=cyan>║  CLEANUP & REPROCESS AUTO-POINT (1 Agustus - Hari Ini)         ║</>');
        $this->line('<fg=cyan>╚════════════════════════════════════════════════════════════════╝</>');
        $this->info("  Tahun Ajaran : {$this->tahunAjaran}");
        $this->info("  Pasal Alfa   : {$this->pasalAlfa}");
        $this->info("  Pasal Terlambat : {$this->pasalTerlambat}");
        if ($this->dryRun) {
            $this->warn('  ⚠  [DRY-RUN MODE] Tidak ada data yang disimpan ke database');
        }
        $this->info('');

        try {
            // ── STEP 1 ─────────────────────────────────────────────────────────
            $this->step1DisplaySummary();

            // ── STEP 2 ─────────────────────────────────────────────────────────
            if (! $this->option('skip-step2')) {
                $this->step2FixAttendance($sekolah);
            } else {
                $this->line('<fg=yellow>⊘  SKIP: Step 2 (Perbaiki kehadiran)</> (gunakan --skip-step2)');
            }

            // ── STEP 3 ─────────────────────────────────────────────────────────
            if (! $this->option('skip-step3')) {
                $this->step3DeleteAutomatic();
            } else {
                $this->line('<fg=yellow>⊘  SKIP: Step 3 (Hapus data otomatis)</> (gunakan --skip-step3)');
            }

            // ── STEP 4 ─────────────────────────────────────────────────────────
            if (! $this->option('skip-step4')) {
                $this->step4ReprocessAutoPoint($sekolah);
            } else {
                $this->line('<fg=yellow>⊘  SKIP: Step 4 (Reprocess auto-point)</> (gunakan --skip-step4)');
            }

            $this->info('');
            $this->line('<fg=green>✅ PROSES SELESAI</>');
            $this->info('');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("GAGAL: {$e->getMessage()}");
            Log::channel('sis')->error('[CleanupAndReprocessAutoPoint] Command GAGAL', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        }
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // STEP 1 — Display Summary
    // ══════════════════════════════════════════════════════════════════════════════

    private function step1DisplaySummary(): void
    {
        $this->line('<fg=yellow>━━━ STEP 1: RINGKASAN DATA SAAT INI ━━━━━━━━━━━━━━━━━━━━━━━</>');

        // Hitung pelanggaran
        $totalPelanggaran = Pelanggaran::whereNull('deleted_at')->count();
        $this->line("  Total Pelanggaran           : <fg=cyan>{$totalPelanggaran}</>");

        // Hitung penghargaan
        $totalPenghargaan = Penghargaan::whereNull('deleted_at')->count();
        $this->line("  Total Penghargaan           : <fg=cyan>{$totalPenghargaan}</>");

        // Hitung transaksi otomatis (noreff prefix 'auto-' atau pelapor='sistem' atau created_by=null)
        $totalTransaksiAuto = TransaksiPoin::whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('noreff', 'like', 'auto-%')
                    ->orWhere('pelapor', '=', 'sistem')
                    ->orWhereNull('created_by');
            })
            ->count();
        $this->line("  Total Transaksi OTOMATIS   : <fg=green>{$totalTransaksiAuto}</>");

        // Hitung transaksi manual (noreff tidak 'auto-' dan pelapor tidak 'sistem' dan created_by not null)
        $totalTransaksiManual = TransaksiPoin::whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('noreff', 'not like', 'auto-%')
                    ->where('pelapor', '!=', 'sistem')
                    ->whereNotNull('created_by');
            })
            ->count();
        $this->line("  Total Transaksi MANUAL     : <fg=blue>{$totalTransaksiManual}</>");

        // Grand total
        $grandTotal = $totalPelanggaran + $totalPenghargaan + $totalTransaksiAuto + $totalTransaksiManual;
        $this->line("  <fg=yellow>─────────────────────────────</>  ");
        $this->line("  Grand Total                 : <fg=white;options=bold>{$grandTotal}</>");

        $this->summary = [
            'pelanggaran'        => $totalPelanggaran,
            'penghargaan'        => $totalPenghargaan,
            'transaksi_otomatis' => $totalTransaksiAuto,
            'transaksi_manual'   => $totalTransaksiManual,
            'grand_total'        => $grandTotal,
        ];

        $this->info('');
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // STEP 2 — Fix Attendance Status
    // ══════════════════════════════════════════════════════════════════════════════

    private function step2FixAttendance(Sekolah $sekolah): void
    {
        $this->line('<fg=yellow>━━━ STEP 2: PERBAIKI STATUS KEHADIRAN SISWA ━━━━━━━━━━━━━━</>');

        $totalAlfaToUpdate    = 0;
        $totalTerlambatUpdate = 0;
        $totalB012Deleted     = 0;

        // Ambil semua pelanggaran B012 yang belum dihapus
        $b012Records = Pelanggaran::where('idpasal', $this->pasalAlfa)
            ->whereNull('deleted_at')
            ->get()
            ->groupBy(function ($item) {
                // Group by siswa_id + tgl
                return $item->siswa_id . '|' . $item->tgl->format('Y-m-d');
            });

        if ($b012Records->isEmpty()) {
            $this->line('  Tidak ada pelanggaran ' . $this->pasalAlfa . ' untuk diproses.');
            $this->info('');
            return;
        }

        $this->line("  Memproses " . $b012Records->count() . " kombinasi siswa/tanggal...");
        $this->info('');

        foreach ($b012Records as $key => $b012Group) {
            [$siswaId, $tglStr] = explode('|', $key);
            $tgl = Carbon::parse($tglStr);

            if ($this->shouldSkipAutoPointDate($tgl, $sekolah)) {
                if ($this->dryRun) {
                    $this->line("    [DRY] siswa_id={$siswaId}, tgl={$tglStr}");
                    $this->line('          → Data otomatis weekend dilewati dan akan dihapus saat step 3.');
                    continue;
                }

                try {
                    foreach ($b012Group as $b012) {
                        $b012->delete();
                    }
                    $totalB012Deleted += $b012Group->count();
                } catch (\Throwable $e) {
                    $this->error("  ❌ Gagal hapus weekend B012 siswa {$siswaId} tgl {$tglStr}: {$e->getMessage()}");
                }

                continue;
            }

            // Cek kondisi: ada pasal lain di hari yang sama?
            $pelanggaranLain = Pelanggaran::where('siswa_id', $siswaId)
                ->whereDate('tgl', $tgl)
                ->where('idpasal', '!=', $this->pasalAlfa)
                ->whereNull('deleted_at')
                ->get();

            // Cek kondisi: ada B001 manual (bukan auto)?
            $b001Manual = $pelanggaranLain->filter(function ($p) {
                return $p->idpasal === $this->pasalTerlambat
                    && (! $p->deviceid || ! str_starts_with($p->deviceid, 'auto-'));
            });

            // ── KONDISI 1: Ada pasal lain (apapun) → terlambat + hapus B012 ──
            if ($pelanggaranLain->isNotEmpty()) {
                if ($this->dryRun) {
                    $this->line("    [DRY] siswa_id={$siswaId}, tgl={$tglStr}");
                    $this->line("          → Absen diupdate ke 'terlambat' jam 08:00");
                    $this->line("          → B012 dihapus (ada pasal lain: " . $pelanggaranLain->pluck('idpasal')->join(', ') . ")");
                    $totalTerlambatUpdate++;
                    $totalB012Deleted += $b012Group->count();
                    continue;
                }

                try {
                    DB::transaction(function () use ($siswaId, $tgl, $b012Group) {
                        // Update absen ke terlambat
                        $absen = AbsenSiswa::where('siswa_id', $siswaId)
                            ->whereDate('tanggal', $tgl)
                            ->first();

                        if ($absen) {
                            $absen->update([
                                'status_masuk' => 'terlambat',
                                'jam_masuk'    => '08:00',
                            ]);
                        }

                        // Soft-delete semua B012 di hari ini
                        foreach ($b012Group as $b012) {
                            $b012->delete();
                        }
                    });

                    $totalTerlambatUpdate++;
                    $totalB012Deleted += $b012Group->count();
                } catch (\Throwable $e) {
                    $this->error("  ❌ Gagal update siswa {$siswaId} tgl {$tglStr}: {$e->getMessage()}");
                }
            }

            // ── KONDISI 2: Ada B001 manual, hapus B012 ──
            elseif ($b001Manual->isNotEmpty()) {
                if ($this->dryRun) {
                    $this->line("    [DRY] siswa_id={$siswaId}, tgl={$tglStr}");
                    $this->line("          → B012 dihapus (ada B001 manual)");
                    $totalB012Deleted += $b012Group->count();
                    continue;
                }

                try {
                    foreach ($b012Group as $b012) {
                        $b012->delete();
                    }
                    $totalB012Deleted += $b012Group->count();
                } catch (\Throwable $e) {
                    $this->error("  ❌ Gagal hapus B012 siswa {$siswaId} tgl {$tglStr}: {$e->getMessage()}");
                }
            }
        }

        $this->line("  <fg=green>✅</> Absensi diupdate ke terlambat : {$totalTerlambatUpdate}");
        $this->line("  <fg=green>✅</> Pelanggaran B012 dihapus       : {$totalB012Deleted}");
        $this->info('');
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // STEP 3 — Delete All Automatic Data
    // ══════════════════════════════════════════════════════════════════════════════

    private function step3DeleteAutomatic(): void
    {
        $this->line('<fg=yellow>━━━ STEP 3: HAPUS SEMUA DATA OTOMATIS ━━━━━━━━━━━━━━━━━━━━</>');

        $totalPelanggaranDeleted = 0;
        $totalPenghargaanDeleted = 0;
        $totalTransaksiDeleted   = 0;

        if ($this->dryRun) {
            // Preview
            $countPel = Pelanggaran::whereNull('deleted_at')
                ->where(function ($q) {
                    $q->where('deviceid', 'like', 'auto-%')
                      ->orWhere('deviceid', 'like', 'TEST_SIS_%');
                })
                ->count();
            $countPeng = Penghargaan::whereNull('deleted_at')
                ->where(function ($q) {
                    $q->where('deviceid', 'like', 'auto-%')
                      ->orWhere('deviceid', 'like', 'TEST_SIS_%');
                })
                ->count();
            // Transaksi otomatis diidentifikasi berdasarkan noreff dari Pelanggaran (PN*) atau Penghargaan (RW*)
            $countTrans = TransaksiPoin::whereNull('deleted_at')
                ->where(function ($q) {
                    $q->where('noreff', 'like', 'PN%')
                        ->orWhere('noreff', 'like', 'RW%');
                })
                ->count();

            $this->line("  [DRY] Transaksi akan dihapus        : {$countTrans}");
            $this->line("  [DRY] Pelanggaran akan dihapus      : {$countPel}");
            $this->line("  [DRY] Penghargaan akan dihapus      : {$countPeng}");
            $this->info('');
            return;
        }

        try {
            DB::transaction(function () use (&$totalPelanggaranDeleted, &$totalPenghargaanDeleted, &$totalTransaksiDeleted) {
                // STEP 1: Kumpulkan semua noreff dari Pelanggaran & Penghargaan otomatis
                // (Ini untuk delete TransaksiPoin dengan akurat, tanpa polusi dari criteria yang terlalu luas)
                // Termasuk record otomatis yang dibuat pada weekend, karena semua data auto- harus dibersihkan.
                $pelanggaranOtomatis = Pelanggaran::whereNull('deleted_at')
                    ->where(function ($q) {
                        $q->where('deviceid', 'like', 'auto-%')
                          ->orWhere('deviceid', 'like', 'TEST_SIS_%');
                    })
                    ->select(['idpel', 'tgl'])
                    ->get();

                $penghargaanOtomatis = Penghargaan::whereNull('deleted_at')
                    ->where(function ($q) {
                        $q->where('deviceid', 'like', 'auto-%')
                          ->orWhere('deviceid', 'like', 'TEST_SIS_%');
                    })
                    ->select(['idpen', 'tgl'])
                    ->get();

                // Extract noreff dari Pelanggaran (format: PN{yymmdd}{idpel})
                $noreffPelanggaran = $pelanggaranOtomatis->map(function ($p) {
                    $tgl = $p->tgl instanceof \Carbon\Carbon ? $p->tgl : \Carbon\Carbon::parse($p->tgl);
                    return 'PN' . $tgl->format('ymd') . $p->idpel;
                })->toArray();

                // Extract noreff dari Penghargaan (format: RW{yymmdd}{idpen})
                $noreffPenghargaan = $penghargaanOtomatis->map(function ($p) {
                    $tgl = $p->tgl instanceof \Carbon\Carbon ? $p->tgl : \Carbon\Carbon::parse($p->tgl);
                    return 'RW' . $tgl->format('ymd') . $p->idpen;
                })->toArray();

                $allNoreff = array_merge($noreffPelanggaran, $noreffPenghargaan);

                // STEP 2: Hapus TransaksiPoin berdasarkan noreff (hard-delete permanent)
                if (! empty($allNoreff)) {
                    $totalTransaksiDeleted += DB::table('tbltransaksi')
                        ->whereIn('noreff', $allNoreff)
                        ->delete();
                }

                // STEP 2b: Hapus transaksi orphan dari SisTestCommand (pelanggaran asalnya
                // sudah dihapus sebelumnya sehingga noreff-nya tidak tercollect di atas)
                $totalTransaksiDeleted += DB::table('tbltransaksi')
                    ->where('pelapor', 'like', 'SisTestCommand%')
                    ->delete();

                // STEP 3: Hapus Pelanggaran otomatis hard-delete (termasuk weekend + data TEST)
                $totalPelanggaranDeleted = DB::table('tblpelanggaran')
                    ->where(function ($q) {
                        $q->where('deviceid', 'like', 'auto-%')
                          ->orWhere('deviceid', 'like', 'TEST_SIS_%');
                    })
                    ->delete();

                // STEP 4: Hapus Penghargaan otomatis hard-delete (termasuk weekend + data TEST)
                $totalPenghargaanDeleted = DB::table('tblpenghargaan')
                    ->where(function ($q) {
                        $q->where('deviceid', 'like', 'auto-%')
                          ->orWhere('deviceid', 'like', 'TEST_SIS_%');
                    })
                    ->delete();
            });

            $this->line("  <fg=green>✅</> Transaksi otomatis dihapus     : {$totalTransaksiDeleted}");
            $this->line("  <fg=green>✅</> Pelanggaran otomatis dihapus   : {$totalPelanggaranDeleted}");
            $this->line("  <fg=green>✅</> Penghargaan otomatis dihapus   : {$totalPenghargaanDeleted}");
        } catch (\Throwable $e) {
            $this->error("  ❌ Gagal menghapus data otomatis: {$e->getMessage()}");
            Log::channel('sis')->error('[CleanupAndReprocessAutoPoint] Step 3 gagal', ['error' => $e->getMessage()]);
        }

        $this->info('');
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // STEP 4 — Reprocess Auto-Point
    // ══════════════════════════════════════════════════════════════════════════════

    private function step4ReprocessAutoPoint(Sekolah $sekolah): void
    {
        $this->line('<fg=yellow>━━━ STEP 4: JALANKAN ULANG AUTO-POINT ━━━━━━━━━━━━━━━━━━━━</>');
        $this->line('  Periode: 1 Agustus - Hari Ini');

        $startDate = Carbon::createFromDate(now()->year, 8, 1);
        $endDate   = Carbon::today();

        if ($startDate->gt($endDate)) {
            $this->warn('  ⚠  Tanggal mulai lebih besar dari hari ini, proses dilewati.');
            $this->info('');
            return;
        }

        $tanggalList = CarbonPeriod::create($startDate, $endDate)->toArray();
        $this->line("  Jumlah tanggal: " . count($tanggalList));
        $this->info('');

        // Inisialisasi hasil
        $results = [
            'alfa'      => 0,
            'terlambat' => 0,
            'hadir'     => 0,
            'skipped'   => 0,
            'gagal'     => 0,
        ];

        // ── SUB-STEP 1: Auto-poin harian ───────────────────────────────────────
        $this->line('  [1/2] Memproses poin HARIAN (alfa/terlambat/hadir)...');

        $pasalAlfaId      = $sekolah->pasal_alfa_id;
        $pasalTerlambatId = $sekolah->auto_poin_terlambat_enabled ? $sekolah->pasal_terlambat_id : null;
        $pasalHadirId     = $sekolah->auto_poin_hadir_enabled     ? $sekolah->pasal_hadir_id     : null;

        $pasalAlfa      = $pasalAlfaId      ? SubPasal::where('idpasal', $pasalAlfaId)->first()      : null;
        $pasalTerlambat = $pasalTerlambatId ? SubPasal::where('idpasal', $pasalTerlambatId)->first() : null;
        $pasalHadir     = $pasalHadirId     ? SubPasal::where('idpasal', $pasalHadirId)->first()     : null;

        foreach ($tanggalList as $tgl) {
            if ($this->shouldSkipAutoPointDate($tgl, $sekolah)) {
                $this->line('  ⏭  Lewati tanggal ' . $tgl->format('Y-m-d') . ' (akhir pekan / hari libur).');
                continue;
            }

            $tglStr   = $tgl->format('Y-m-d');
            $absenList = AbsenSiswa::with('siswa.kelas')
                ->whereDate('tanggal', $tglStr)
                ->whereNotNull('status_masuk')
                ->whereIn('status_masuk', ['alfa', 'terlambat', 'hadir'])
                ->get();

            foreach ($absenList as $absen) {
                $siswa = $absen->siswa;
                if (! $siswa) {
                    $results['skipped']++;
                    continue;
                }

                try {
                    $status = $absen->status_masuk;

                    // ALFA
                    if ($status === 'alfa' && $pasalAlfa) {
                        $deviceId = 'auto-alfa-' . $tglStr;
                        // Cek duplikasi: Apakah sudah ada Pelanggaran dengan deviceId ini?
                        $exists = Pelanggaran::where('siswa_id', $siswa->id)
                            ->where('deviceid', $deviceId)
                            ->whereNull('deleted_at')
                            ->exists();

                        if (! $exists) {
                            if ($this->dryRun) {
                                $results['alfa']++;
                            } else {
                                try {
                                    $this->simpanPelanggaran(
                                        $siswa,
                                        $tglStr,
                                        $deviceId,
                                        $pasalAlfa,
                                        'Tidak hadir (alfa)'
                                    );
                                    $results['alfa']++;
                                } catch (\Throwable $saveEx) {
                                    $this->error("      ❌ Gagal buat pelanggaran alfa: {$saveEx->getMessage()}");
                                    $results['gagal']++;
                                }
                            }
                        } else {
                            $results['skipped']++;
                        }
                    }

                    // TERLAMBAT
                    elseif ($status === 'terlambat' && $pasalTerlambat) {
                        $deviceId = 'auto-terlambat-' . $tglStr;
                        // Cek duplikasi
                        $exists = Pelanggaran::where('siswa_id', $siswa->id)
                            ->where('deviceid', $deviceId)
                            ->whereNull('deleted_at')
                            ->exists();

                        if (! $exists) {
                            if ($this->dryRun) {
                                $results['terlambat']++;
                            } else {
                                try {
                                    $this->simpanPelanggaran(
                                        $siswa,
                                        $tglStr,
                                        $deviceId,
                                        $pasalTerlambat,
                                        'Terlambat masuk sekolah'
                                    );
                                    $results['terlambat']++;
                                } catch (\Throwable $saveEx) {
                                    $this->error("      ❌ Gagal buat pelanggaran terlambat: {$saveEx->getMessage()}");
                                    $results['gagal']++;
                                }
                            }
                        } else {
                            $results['skipped']++;
                        }
                    }

                    // HADIR
                    elseif ($status === 'hadir' && $pasalHadir) {
                        $deviceId = 'auto-hadir-' . $tglStr;
                        // Cek duplikasi
                        $exists = Penghargaan::where('siswa_id', $siswa->id)
                            ->where('deviceid', $deviceId)
                            ->whereNull('deleted_at')
                            ->exists();

                        if (! $exists) {
                            if ($this->dryRun) {
                                $results['hadir']++;
                            } else {
                                try {
                                    $this->simpanPenghargaan(
                                        $siswa,
                                        $tglStr,
                                        $deviceId,
                                        $pasalHadir,
                                        'Hadir tepat waktu',
                                        'Auto dari absensi harian'
                                    );
                                    $results['hadir']++;
                                } catch (\Throwable $saveEx) {
                                    $this->error("      ❌ Gagal buat penghargaan hadir: {$saveEx->getMessage()}");
                                    $results['gagal']++;
                                }
                            }
                        } else {
                            $results['skipped']++;
                        }
                    }
                } catch (\Throwable $e) {
                    $this->error("    ❌ Gagal siswa {$siswa->id} tgl {$tglStr}: {$e->getMessage()}");
                    $results['gagal']++;
                    Log::channel('sis')->error('[Step4 Harian] Error', [
                        'siswa_id' => $siswa->id,
                        'tgl'      => $tglStr,
                        'error'    => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->line("    <fg=green>✅</> Poin alfa       : {$results['alfa']}");
        $this->line("    <fg=green>✅</> Poin terlambat  : {$results['terlambat']}");
        $this->line("    <fg=green>✅</> Poin hadir      : {$results['hadir']}");
        if ($results['gagal'] > 0) {
            $this->error("    ❌ Gagal           : {$results['gagal']}");
        }
        $this->info('');

        // ── SUB-STEP 2: Auto-poin Event ────────────────────────────────────────
        $this->step4BackfillEventPoints();

        $this->line('<fg=green>✅</> Reprocess auto-point selesai');
        $this->info('');
    }

    private function step4BackfillEventPoints(): void
    {
        $this->line('  [2/2] Memproses poin EVENT (pelanggaran & penghargaan)...');

        $events = Event::query()
            ->where('tanggal_selesai', '<', now())
            ->where('nama_event', 'not like', '[TEST]%') // skip event test dari sis:test-feature
            ->where(function ($q) {
                $q->whereNotNull('pasal_pelanggaran_id')
                    ->orWhereNotNull('pasal_penghargaan_id');
            })
            ->with(['pasalPelanggaran', 'pasalPenghargaan'])
            ->get();

        if ($events->isEmpty()) {
            $this->line('  Tidak ada event yang sudah selesai dengan auto event point yang dapat diproses.');
            $this->info('');
            return;
        }

        $totalPelanggaran = 0;
        $totalPenghargaan = 0;
        $totalSkip = 0;
        $totalFlagsEnabled = 0;

        foreach ($events as $event) {
            $updated = false;

            if ($event->pasal_pelanggaran_id && ! $event->auto_point_pelanggaran) {
                $event->auto_point_pelanggaran = true;
                $updated = true;
            }

            if ($event->pasal_penghargaan_id && ! $event->auto_penghargaan) {
                $event->auto_penghargaan = true;
                $updated = true;
            }

            if ($updated) {
                if (! $this->dryRun) {
                    $event->save();
                }
                $totalFlagsEnabled++;
            }

            $this->line("    <fg=gray>→ Event #{$event->id}: {$event->nama_event}</>");

            $madeAny = false;

            if ($event->auto_point_pelanggaran && $event->pasalPelanggaran) {
                $result = $this->processEventPelanggaran($event);
                $totalPelanggaran += $result['created'];
                $totalSkip += $result['skipped'];
                $madeAny = true;
            }

            if ($event->auto_penghargaan && $event->pasalPenghargaan) {
                $result = $this->processEventPenghargaan($event);
                $totalPenghargaan += $result['created'];
                $totalSkip += $result['skipped'];
                $madeAny = true;
            }

            if ($madeAny && ! $this->dryRun) {
                $event->forceFill([
                    'auto_point_processed_at' => now(),
                    'auto_penghargaan_processed_at' => now(),
                ])->save();
            }
        }

        $this->line("    <fg=green>✅</> Pelanggaran event dibuat   : {$totalPelanggaran}");
        $this->line("    <fg=green>✅</> Penghargaan event dibuat   : {$totalPenghargaan}");
        $this->line("    <fg=yellow>⚠</> Flag auto-event diaktifkan : {$totalFlagsEnabled}");
        $this->line("    <fg=gray>⏭</> Event/record dilewati     : {$totalSkip}");
        $this->info('');
    }

    private function processEventPelanggaran(Event $event): array
    {
        $pesertaIds = $this->getPesertaIds($event);
        $sudahScan = AbsenEvent::where('event_id', $event->id)
            ->whereNotNull('waktu_masuk')
            ->pluck('siswa_id')
            ->toArray();

        $belumScan = array_diff($pesertaIds, $sudahScan);
        $tglEvent = Carbon::parse($event->tanggal_mulai)->toDateString();
        $pasal = $event->pasalPelanggaran;

        $created = 0;
        $skipped = 0;

        foreach ($belumScan as $siswaId) {
            $deviceId = 'auto-event-' . $event->id;
            if (Pelanggaran::where('siswa_id', $siswaId)
                ->where('deviceid', $deviceId)
                ->whereNull('deleted_at')
                ->exists()
            ) {
                $skipped++;
                continue;
            }

            $absen = AbsenSiswa::where('siswa_id', $siswaId)
                ->whereDate('tanggal', $tglEvent)
                ->first();

            $statusMasuk = $absen?->status_masuk ?? $absen?->status;
            if (! $absen || ! in_array($statusMasuk, ['hadir', 'terlambat'])) {
                $skipped++;
                continue;
            }

            $siswa = Siswa::find($siswaId);
            if (! $siswa) {
                $skipped++;
                continue;
            }

            if (! $this->dryRun) {
                $this->simpanPelanggaran(
                    $siswa,
                    $tglEvent,
                    $deviceId,
                    $pasal,
                    'Tidak hadir pada event: ' . $event->nama_event,
                    $event->poin_pelanggaran_event,
                    $event->created_by,
                );
            }

            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    private function processEventPenghargaan(Event $event): array
    {
        $sudahScan = AbsenEvent::where('event_id', $event->id)
            ->whereNotNull('waktu_masuk')
            ->pluck('siswa_id')
            ->toArray();

        $created = 0;
        $skipped = 0;
        $tglEvent = Carbon::parse($event->tanggal_selesai)->toDateString();
        $pasal = $event->pasalPenghargaan;

        foreach ($sudahScan as $siswaId) {
            $deviceId = 'auto-event-penghargaan-' . $event->id;
            if (Penghargaan::where('siswa_id', $siswaId)
                ->where('deviceid', $deviceId)
                ->whereNull('deleted_at')
                ->exists()
            ) {
                $skipped++;
                continue;
            }

            $siswa = Siswa::find($siswaId);
            if (! $siswa) {
                $skipped++;
                continue;
            }

            if (! $this->dryRun) {
                $this->simpanPenghargaan(
                    $siswa,
                    $tglEvent,
                    $deviceId,
                    $pasal,
                    'Berhasil hadir pada event: ' . $event->nama_event,
                    'Auto dari event',
                    $event->poin_penghargaan_event,
                    $event->created_by,
                );
            }

            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // Helper Methods
    // ══════════════════════════════════════════════════════════════════════════════

    private function simpanPelanggaran(Siswa $siswa, string $tgl, string $deviceId, SubPasal $pasal, string $isi, ?int $poinOverride = null, ?int $createdBy = null): void
    {
        if ($this->dryRun) {
            return;
        }

        try {
            Pelanggaran::create([
                'siswa_id'      => $siswa->id,
                'tgl'           => Carbon::parse($tgl),
                'tahun_ajaran'  => $this->tahunAjaran,
                'deviceid'      => $deviceId,
                'noreg'         => $siswa->noreg ?? '',
                'nama'          => $siswa->nama ?? '',
                'kelas'         => $siswa->kelas?->nmkelas ?? '',
                'idpasal'       => $pasal->idpasal,
                'isi'           => $isi,
                'poin'          => $poinOverride ?? $pasal->getPoinDefaultAttribute(),
                'pelapor'       => 'sistem',
                'created_by'    => $createdBy,
            ]);
        } catch (\Throwable $e) {
            Log::channel('sis')->error('[simpanPelanggaran] Gagal', [
                'siswa_id' => $siswa->id,
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function simpanPenghargaan(
        Siswa $siswa,
        string $tgl,
        string $deviceId,
        SubPasal $pasal,
        string $isi,
        string $ket,
        ?int $poinOverride = null,
        ?int $createdBy = null
    ): void {
        if ($this->dryRun) {
            return;
        }

        try {
            Penghargaan::create([
                'siswa_id'      => $siswa->id,
                'tgl'           => Carbon::parse($tgl),
                'tahun_ajaran'  => $this->tahunAjaran,
                'deviceid'      => $deviceId,
                'noreg'         => $siswa->noreg ?? '',
                'nama'          => $siswa->nama ?? '',
                'kelas'         => $siswa->kelas?->nmkelas ?? '',
                'idpasal'       => $pasal->idpasal,
                'isi'           => $isi,
                'poin'          => $poinOverride ?? $pasal->getPoinDefaultAttribute(),
                'pelapor'       => 'sistem',
                'ket'           => $ket,
                'acc'           => 'YA', // Auto ACC untuk poin sistem (harus string 'YA', bukan 1)
                'tglacc'        => now(),
                'nmacc'         => 'Sistem',
                'created_by'    => $createdBy,
            ]);
        } catch (\Throwable $e) {
            Log::channel('sis')->error('[simpanPenghargaan] Gagal', [
                'siswa_id' => $siswa->id,
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function getPesertaIds(Event $event): array
    {
        if ($event->berlaku_untuk_semua) {
            return Siswa::where('status_aktif', true)->pluck('id')->toArray();
        }

        if ($event->mode_peserta === 'kelas') {
            $kelasIds = $event->kelas()->pluck('kelas.id')->toArray();
            return Siswa::where('status_aktif', true)
                ->whereIn('kelas_id', $kelasIds)
                ->pluck('id')
                ->toArray();
        }

        return $event->siswa()->pluck('siswas.id')->toArray();
    }

    private function shouldSkipAutoPointDate(Carbon $date, ?Sekolah $sekolah = null): bool
    {
        if ($date->isWeekend()) {
            return true;
        }

        if ($sekolah && $sekolah->sedangLibur($date)) {
            return true;
        }

        return false;
    }

    private function getTahunAjaran(): string
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        return $academicYear?->year ?? Carbon::now()->year . '/' . (Carbon::now()->year + 1);
    }
}
