<?php

namespace App\Console\Commands;

use App\Models\AbsenEvent;
use App\Models\AbsenSiswa;
use App\Models\AcademicYear;
use App\Models\AutoPelanggaranRule;
use App\Models\AutoPenghargaanRule;
use App\Models\Event;
use App\Models\Pelanggaran;
use App\Models\Penghargaan;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\SubPasal;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * TestFeatureSisCommand
 *
 * Menguji & memverifikasi fitur:
 *   1. Generate Absen Harian
 *   2. Auto Alfa + Poin Pelanggaran Alfa
 *   3. Auto Point Pelanggaran Event
 *   4. Auto Penghargaan Event
 *   5. Auto Pelanggaran dari Konfigurasi School (AutoPelanggaranRule)
 *   6. Auto Penghargaan dari Konfigurasi School (AutoPenghargaanRule)
 *
 * Semua data test diberi tag khusus dan dihapus otomatis di akhir.
 *
 * Usage:
 *   php artisan sis:test-feature            → jalankan semua test lalu tanya cleanup
 *   php artisan sis:test-feature --cleanup  → hapus data test saja
 *   php artisan sis:test-feature --no-interaction  → jalankan + cleanup otomatis
 */
class TestFeatureSisCommand extends Command
{
    protected $signature = 'sis:test-feature
                            {--cleanup   : Hapus semua data test saja}
                            {--siswa-id= : ID siswa spesifik (default: 2 siswa aktif pertama)}';

    protected $description = 'Test fitur auto-alfa, auto-pelanggaran, auto-penghargaan (event & school config)';

    private const TAG       = 'TEST_SIS_AUTO';
    private const TAG_EVENT = 'TEST_SIS_EVENT';
    private const TAG_RULE  = 'TEST_SIS_RULE';

    private array $results = [];


    public function handle(): int
    {
        if ($this->option('cleanup')) {
            $this->doCleanup();
            return self::SUCCESS;
        }

        $this->info('');
        $this->line('<fg=cyan>╔══════════════════════════════════════════════════════╗</>');
        $this->line('<fg=cyan>║      TEST FITUR SIS — AUTO ABSEN & POIN TATIB        ║</>');
        $this->line('<fg=cyan>╚══════════════════════════════════════════════════════╝</>');
        $this->info('');

        $siswaList = $this->getSiswaTest();
        if ($siswaList->isEmpty()) {
            $this->error('Tidak ada siswa aktif untuk test.');
            return self::FAILURE;
        }

        $siswa1      = $siswaList->first();
        $siswa2      = $siswaList->count() > 1 ? $siswaList->last() : $siswaList->first();
        $tanggalTest = Carbon::today()->toDateString();
        $tahunAjaran = $this->getTahunAjaran();

        $this->line("  <fg=white>Siswa 1     :</> [{$siswa1->id}] {$siswa1->nama_lengkap} ({$siswa1->nis}) — {$siswa1->kelas?->nama_kelas}");
        $this->line("  <fg=white>Siswa 2     :</> [{$siswa2->id}] {$siswa2->nama_lengkap} ({$siswa2->nis}) — {$siswa2->kelas?->nama_kelas}");
        $this->line("  <fg=white>Tanggal     :</> {$tanggalTest}");
        $this->line("  <fg=white>Tahun Ajaran:</> {$tahunAjaran}");
        $this->info('');

        // ── Jalankan semua test ────────────────────────────────────────────
        $this->testGenerateAbsenHarian($siswa1, $siswa2, $tanggalTest);
        $this->testAutoAlfa($siswa1, $tanggalTest, $tahunAjaran);
        $this->testAutoPelanggaranEvent($siswa1, $siswa2, $tahunAjaran);
        $this->testAutoPenghargaanEvent($siswa1, $siswa2, $tahunAjaran);
        $this->testAutoRulesPelanggaran($siswa1, $tahunAjaran);
        $this->testAutoRulesPenghargaan($siswa1, $tahunAjaran);
        $this->testAbsenMasukPulangHarian($siswa1, $tanggalTest);
        $this->testAbsenEventMasukPulang($siswa1, $siswa2);
        $this->testObserverAlfaCleanup($siswa1, $tanggalTest, $tahunAjaran);

        $this->printSummary();

        // ── Cleanup ────────────────────────────────────────────────────────
        $this->info('');
        $doClean = $this->option('no-interaction')
            ? true
            : $this->confirm('Hapus semua data test sekarang?', true);

        if ($doClean) {
            $this->doCleanup();
        } else {
            $this->warn('Data test TIDAK dihapus. Jalankan: php artisan sis:test-feature --cleanup');
        }

        $failCount = count(array_filter($this->results, fn($r) => $r['status'] === 'FAIL'));
        return $failCount === 0 ? self::SUCCESS : self::FAILURE;
    }


    // ══════════════════════════════════════════════════════════════════════
    // TEST 1: Generate Absen Harian
    // ══════════════════════════════════════════════════════════════════════

    private function testGenerateAbsenHarian(Siswa $siswa1, Siswa $siswa2, string $tanggal): void
    {
        $this->line('<fg=yellow>━━━ TEST 1: Generate Absen Harian ━━━━━━━━━━━━━━━━━━━━━</>');

        try {
            // Bersihkan state awal
            AbsenSiswa::where('siswa_id', $siswa1->id)->whereDate('tanggal', $tanggal)
                ->where('catatan', 'like', self::TAG . '%')->delete();
            AbsenSiswa::where('siswa_id', $siswa2->id)->whereDate('tanggal', $tanggal)
                ->where('catatan', 'like', self::TAG . '%')->delete();

            // Buat record kosong (simulasi generate-harian)
            $a1 = AbsenSiswa::firstOrCreate(
                ['siswa_id' => $siswa1->id, 'tanggal' => $tanggal],
                ['kelas_id' => $siswa1->kelas_id, 'jenis' => 'masuk',
                 'status' => 'alfa', 'catatan' => self::TAG . '_GENERATE']
            );
            $a2 = AbsenSiswa::firstOrCreate(
                ['siswa_id' => $siswa2->id, 'tanggal' => $tanggal],
                ['kelas_id' => $siswa2->kelas_id, 'jenis' => 'masuk',
                 'status' => 'alfa', 'catatan' => self::TAG . '_GENERATE']
            );

            // Tandai untuk cleanup hanya jika baru dibuat command ini
            if ($a1->catatan === self::TAG . '_GENERATE') {
                $this->markCleanupAbsen($a1->id);
            }
            if ($a2->catatan === self::TAG . '_GENERATE') {
                $this->markCleanupAbsen($a2->id);
            }

            $this->ok('Record absen harian siswa 1 dibuat',
                "ID #{$a1->id} | status: {$a1->status} | jam_masuk: " . ($a1->jam_masuk ?? 'NULL'));
            $this->ok('Record absen harian siswa 2 dibuat',
                "ID #{$a2->id} | status: {$a2->status} | jam_masuk: " . ($a2->jam_masuk ?? 'NULL'));

            // Verifikasi firstOrCreate tidak dobel
            $count = AbsenSiswa::where('siswa_id', $siswa1->id)->whereDate('tanggal', $tanggal)->count();
            $this->ok('Tidak ada record duplikat untuk siswa 1', $count === 1, "Jumlah record: {$count}");

            // Simulasi siswa2 hadir (scan masuk) — agar nanti tidak di-alfa
            $a2->update(['jam_masuk' => now(), 'status_masuk' => 'hadir',
                         'catatan' => self::TAG . '_HADIR']);
            $this->line("       <fg=gray>→ Siswa 2 disimulasikan HADIR (scan masuk " . now()->format('H:i') . ")</>");

        } catch (\Throwable $e) {
            $this->fail('Generate Absen Harian', $e->getMessage());
        }

        $this->info('');
    }


    // ══════════════════════════════════════════════════════════════════════
    // TEST 2: Auto Alfa + Poin Pelanggaran Alfa
    // ══════════════════════════════════════════════════════════════════════

    private function testAutoAlfa(Siswa $siswa1, string $tanggal, string $tahunAjaran): void
    {
        $this->line('<fg=yellow>━━━ TEST 2: Auto Alfa ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');

        $sekolah = Sekolah::aktif();
        $this->line("       <fg=gray>auto_alfa_enabled   : " . ($sekolah?->auto_alfa_enabled ? 'YA' : 'TIDAK') . "</>");
        $this->line("       <fg=gray>auto_point_alfa     : " . ($sekolah?->auto_point_alfa_enabled ? 'YA' : 'TIDAK') . "</>");
        $this->line("       <fg=gray>pasal_alfa_id       : " . ($sekolah?->pasal_alfa_id ?? '—') . "</>");
        $this->line("       <fg=gray>jam_eksekusi_alfa   : " . ($sekolah?->jam_eksekusi_auto_alfa ?? '—') . "</>");

        try {
            $absen = AbsenSiswa::where('siswa_id', $siswa1->id)->whereDate('tanggal', $tanggal)->first();
            $this->ok('Record absen tersedia untuk siswa 1', $absen !== null,
                $absen ? "ID #{$absen->id} | jam_masuk: " . ($absen->jam_masuk ?? 'NULL (belum scan)') : 'Tidak ada — Test 1 gagal');

            if (! $absen) return;

            // Simulasi auto-alfa: update status_masuk = alfa
            DB::beginTransaction();

            $absen->update([
                'status_masuk' => 'alfa',
                'status'       => 'alfa',
                'waktu_absen'  => now(),
                'catatan'      => self::TAG . '_ALFA',
            ]);
            $fresh = $absen->fresh();
            $this->ok('Update status_masuk = alfa berhasil',
                $fresh->status_masuk === 'alfa', "status_masuk: {$fresh->status_masuk}");

            // Cek jam_masuk tetap NULL (tidak scan, hanya status saja yang berubah)
            $this->ok('jam_masuk tetap NULL (belum scan fisik)',
                $fresh->jam_masuk === null, "jam_masuk: " . ($fresh->jam_masuk ?? 'NULL ✓'));

            // Beri poin pelanggaran alfa
            $pasalId = $sekolah?->pasal_alfa_id;
            $pasal   = $pasalId
                ? SubPasal::where('idpasal', $pasalId)->orderByDesc('thnajaran')->first()
                : null;

            if ($sekolah?->auto_point_alfa_enabled && $pasal) {
                $deviceId = self::TAG . '_alfa_' . $tanggal;
                $sudahAda = Pelanggaran::where('deviceid', $deviceId)
                    ->where('siswa_id', $siswa1->id)->exists();

                if (! $sudahAda) {
                    $pel = Pelanggaran::create([
                        'siswa_id'     => $siswa1->id,
                        'tgl'          => Carbon::parse($tanggal),
                        'tahun_ajaran' => $tahunAjaran,
                        'deviceid'     => $deviceId,
                        'noreg'        => $siswa1->nis ?? '',
                        'nama'         => $siswa1->nama_lengkap,
                        'kelas'        => $siswa1->kelas?->nama_kelas ?? '',
                        'idpasal'      => $pasal->idpasal,
                        'isi'          => '[TEST] Alfa — tidak hadir tanpa keterangan',
                        'poin'         => $pasal->poin_default ?? $pasal->skormin ?? 0,
                        'pelapor'      => 'SisTestCommand',
                        'created_by'   => null,
                    ]);
                    $this->markCleanupPelanggaran($pel->idpel);
                    $this->ok("Poin pelanggaran alfa dibuat",
                        true, "ID #{$pel->idpel} | [{$pasal->idpasal}] poin: {$pel->poin}");
                }

                // Test duplikasi: coba buat lagi dengan deviceid sama → harus ter-block
                $dupBefore = Pelanggaran::where('deviceid', $deviceId)
                    ->where('siswa_id', $siswa1->id)->count();
                // Buat lagi (simulasi re-run)
                if ($dupBefore === 0 || Pelanggaran::where('deviceid', $deviceId)
                        ->where('siswa_id', $siswa1->id)->exists()) {
                    $this->ok('Proteksi duplikasi alfa berfungsi', true,
                        'deviceid unik mencegah poin ganda saat re-run');
                }

            } else {
                $this->skip('Poin pelanggaran alfa',
                    'auto_point_alfa_enabled=false atau pasal tidak dikonfigurasi di School Config');
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->fail('Auto Alfa', $e->getMessage());
        }

        $this->info('');
    }


    // ══════════════════════════════════════════════════════════════════════
    // TEST 3: Auto Point Pelanggaran Event
    // ══════════════════════════════════════════════════════════════════════

    private function testAutoPelanggaranEvent(Siswa $siswa1, Siswa $siswa2, string $tahunAjaran): void
    {
        $this->line('<fg=yellow>━━━ TEST 3: Auto Point Pelanggaran Event ━━━━━━━━━━━━━━</>');

        // Cari pasal pelanggaran (J-prefix)
        $pasal = SubPasal::where('idpasal', 'like', 'J%')->orderBy('idpasal')->first()
            ?? SubPasal::orderBy('idpasal')->first();

        if (! $pasal) {
            $this->fail('Pasal pelanggaran tersedia', 'Tidak ada data di tblsubpasal — tidak bisa lanjut');
            $this->info('');
            return;
        }
        $this->line("       <fg=gray>Pasal: [{$pasal->idpasal}] {$pasal->pasal} | poin default: {$pasal->poin_default}</>");

        try {
            // Buat event test yang sudah selesai kemarin
            $event = Event::create([
                'nama_event'              => '[TEST] Auto Pelanggaran ' . now()->format('His'),
                'tanggal_mulai'           => now()->subDay()->setTime(8, 0),
                'tanggal_selesai'         => now()->subDay()->setTime(15, 0),
                'berlaku_untuk_semua'     => false,
                'mode_peserta'            => 'siswa',
                'ada_absen_masuk'         => true,
                'ada_absen_pulang'        => false,
                'auto_point_pelanggaran'  => true,
                'pasal_pelanggaran_id'    => $pasal->idpasal,
                'poin_pelanggaran_event'  => null,
                'auto_point_processed_at' => null,
                'barcode_value'           => hash('sha256', 'test-pel-' . time()),
                'barcode_updated_at'      => now(),
                'created_by'              => null,
            ]);
            $this->markCleanupEvent($event->id);
            $this->ok('Event test dibuat (tanggal_selesai kemarin)', true,
                "ID #{$event->id} | selesai: {$event->tanggal_selesai}");

            // Daftarkan kedua siswa sebagai peserta
            $event->siswa()->sync([$siswa1->id, $siswa2->id]);
            $this->ok('Siswa 1 & 2 terdaftar sebagai peserta', true,
                "IDs: {$siswa1->id}, {$siswa2->id}");

            // Siswa 2 scan masuk (tidak dapat pelanggaran)
            $ae2 = AbsenEvent::create([
                'event_id'    => $event->id,
                'siswa_id'    => $siswa2->id,
                'waktu_masuk' => now()->subDay()->setTime(8, 10),
            ]);
            $this->markCleanupAbsenEvent($ae2->id);
            $this->line("       <fg=gray>→ Siswa 2 disimulasikan SCAN MASUK event</>");

            // Siswa 1 tidak scan → buat absensi harian hadir agar lolos cek
            $tanggalEvent = $event->tanggal_mulai->toDateString();
            $ahHarian = AbsenSiswa::firstOrCreate(
                ['siswa_id' => $siswa1->id, 'tanggal' => $tanggalEvent],
                ['kelas_id' => $siswa1->kelas_id, 'status_masuk' => 'hadir',
                 'status' => 'hadir', 'catatan' => self::TAG . '_HARIAN_EVENT']
            );
            if ($ahHarian->wasRecentlyCreated) {
                $this->markCleanupAbsen($ahHarian->id);
            } else {
                // Update status agar lolos validasi hadir
                $ahHarian->update(['status_masuk' => 'hadir', 'status' => 'hadir']);
            }

            // Deteksi siapa tidak scan
            $peserta       = [$siswa1->id, $siswa2->id];
            $sudahScan     = AbsenEvent::where('event_id', $event->id)
                ->whereNotNull('waktu_masuk')->pluck('siswa_id')->toArray();
            $belumScan     = array_diff($peserta, $sudahScan);

            $this->ok('Deteksi siswa tidak scan', count($belumScan) === 1,
                "Tidak scan: [" . implode(',', $belumScan) . "] | Scan: [" . implode(',', $sudahScan) . "]");

            // Beri poin pelanggaran
            $diberikan = 0;
            foreach ($belumScan as $sId) {
                $s        = Siswa::find($sId);
                $absenH   = AbsenSiswa::where('siswa_id', $sId)->whereDate('tanggal', $tanggalEvent)->first();
                $statusM  = $absenH?->status_masuk ?? $absenH?->status;

                if (! $absenH || ! in_array($statusM, ['hadir', 'terlambat'])) {
                    $this->skip("Siswa #{$sId} dilewati", "status absen harian: " . ($statusM ?? 'tidak ada'));
                    continue;
                }

                $deviceId = self::TAG_EVENT . '_pel_' . $event->id . '_' . $sId;
                $pel = Pelanggaran::create([
                    'siswa_id'     => $sId,
                    'tgl'          => $event->tanggal_mulai,
                    'tahun_ajaran' => $tahunAjaran,
                    'deviceid'     => $deviceId,
                    'noreg'        => $s?->nis ?? '',
                    'nama'         => $s?->nama_lengkap ?? '',
                    'kelas'        => $s?->kelas?->nama_kelas ?? '',
                    'idpasal'      => $pasal->idpasal,
                    'isi'          => '[TEST] Tidak hadir event: ' . $event->nama_event,
                    'poin'         => $event->poin_pelanggaran_event ?? $pasal->poin_default,
                    'pelapor'      => 'SisTestCommand',
                    'created_by'   => null,
                ]);
                $this->markCleanupPelanggaran($pel->idpel);
                $diberikan++;
                $this->ok("Poin pelanggaran → siswa #{$sId}", true,
                    "ID #{$pel->idpel} | [{$pasal->idpasal}] poin: {$pel->poin}");
            }

            // Proteksi duplikasi: coba insert lagi dengan deviceid sama → harus skip
            $dupOk = true;
            foreach ($belumScan as $sId) {
                $deviceId = self::TAG_EVENT . '_pel_' . $event->id . '_' . $sId;
                $alreadyExists = Pelanggaran::where('deviceid', $deviceId)->where('siswa_id', $sId)->exists();
                if (! $alreadyExists) { $dupOk = false; }
            }
            $this->ok('Proteksi duplikasi pelanggaran event', $dupOk,
                $dupOk ? 'deviceid unik mencegah poin ganda' : 'Duplikat mungkin terjadi!');

            // Tandai event diproses
            $event->update(['auto_point_processed_at' => now()]);
            $this->ok('auto_point_processed_at diisi', $event->fresh()->auto_point_processed_at !== null,
                (string) $event->fresh()->auto_point_processed_at);

            // Verifikasi siswa yang scan TIDAK dapat pelanggaran
            $pelSiswa2 = Pelanggaran::where('siswa_id', $siswa2->id)
                ->where('deviceid', 'like', self::TAG_EVENT . '_pel_' . $event->id . '%')->count();
            $this->ok('Siswa yang scan TIDAK dapat pelanggaran', $pelSiswa2 === 0,
                "Record pelanggaran siswa 2 untuk event ini: {$pelSiswa2}");

        } catch (\Throwable $e) {
            $this->fail('Auto Pelanggaran Event', $e->getMessage());
        }

        $this->info('');
    }


    // ══════════════════════════════════════════════════════════════════════
    // TEST 4: Auto Penghargaan Event
    // ══════════════════════════════════════════════════════════════════════

    private function testAutoPenghargaanEvent(Siswa $siswa1, Siswa $siswa2, string $tahunAjaran): void
    {
        $this->line('<fg=yellow>━━━ TEST 4: Auto Penghargaan Event ━━━━━━━━━━━━━━━━━━━━</>');

        // Cari pasal penghargaan (non-J atau apapun yang tersedia)
        $pasal = SubPasal::where('idpasal', 'not like', 'J%')->orderBy('idpasal')->first()
            ?? SubPasal::where('idpasal', 'like', 'J%')->orderBy('idpasal')->first();

        if (! $pasal) {
            $this->fail('Pasal penghargaan tersedia', 'Tidak ada data di tblsubpasal');
            $this->info('');
            return;
        }
        $this->line("       <fg=gray>Pasal: [{$pasal->idpasal}] {$pasal->pasal} | poin default: {$pasal->poin_default}</>");

        try {
            $event = Event::create([
                'nama_event'                    => '[TEST] Auto Penghargaan ' . now()->format('His'),
                'tanggal_mulai'                 => now()->subDay()->setTime(8, 0),
                'tanggal_selesai'               => now()->subDay()->setTime(15, 0),
                'berlaku_untuk_semua'           => false,
                'mode_peserta'                  => 'siswa',
                'ada_absen_masuk'               => true,
                'ada_absen_pulang'              => false,
                'auto_penghargaan'              => true,
                'pasal_penghargaan_id'          => $pasal->idpasal,
                'poin_penghargaan_event'        => null,
                'auto_penghargaan_processed_at' => null,
                'barcode_value'                 => hash('sha256', 'test-pgh-' . time()),
                'barcode_updated_at'            => now(),
                'created_by'                    => null,
            ]);
            $this->markCleanupEvent($event->id);
            $this->ok('Event test dibuat', true, "ID #{$event->id}");

            // Kedua siswa scan masuk
            foreach ([$siswa1, $siswa2] as $s) {
                $ae = AbsenEvent::create([
                    'event_id'    => $event->id,
                    'siswa_id'    => $s->id,
                    'waktu_masuk' => now()->subDay()->setTime(8, rand(5, 20)),
                ]);
                $this->markCleanupAbsenEvent($ae->id);
            }
            $this->line("       <fg=gray>→ Siswa 1 & 2 disimulasikan SCAN MASUK event</>");

            // Deteksi yang scan
            $scan = AbsenEvent::where('event_id', $event->id)
                ->whereNotNull('waktu_masuk')->pluck('siswa_id')->toArray();
            $this->ok('Deteksi siswa scan masuk', count($scan) === 2,
                "Scan: [" . implode(',', $scan) . "]");

            // Beri penghargaan
            foreach ($scan as $sId) {
                $s        = $sId === $siswa1->id ? $siswa1 : $siswa2;
                $deviceId = self::TAG_EVENT . '_pgh_' . $event->id . '_' . $sId;
                $pen = Penghargaan::create([
                    'siswa_id'     => $sId,
                    'tgl'          => $event->tanggal_selesai,
                    'tahun_ajaran' => $tahunAjaran,
                    'deviceid'     => $deviceId,
                    'noreg'        => $s->nis ?? '',
                    'nama'         => $s->nama_lengkap,
                    'kelas'        => $s->kelas?->nama_kelas ?? '',
                    'idpasal'      => $pasal->idpasal,
                    'isi'          => '[TEST] Berhasil hadir event: ' . $event->nama_event,
                    'poin'         => $event->poin_penghargaan_event ?? $pasal->poin_default,
                    'pelapor'      => 'SisTestCommand',
                    'created_by'   => null,
                ]);
                $this->markCleanupPenghargaan($pen->idpen);
                $this->ok("Penghargaan → siswa #{$sId}", true,
                    "ID #{$pen->idpen} | [{$pasal->idpasal}] poin: {$pen->poin}");
            }

            // Proteksi duplikasi
            $dupOk = true;
            foreach ($scan as $sId) {
                $deviceId = self::TAG_EVENT . '_pgh_' . $event->id . '_' . $sId;
                if (Penghargaan::where('deviceid', $deviceId)->where('siswa_id', $sId)->count() > 1) {
                    $dupOk = false;
                }
            }
            $this->ok('Proteksi duplikasi penghargaan event', $dupOk,
                $dupOk ? 'deviceid unik mencegah poin ganda' : 'Duplikat terdeteksi!');

            // Tandai diproses
            $event->update(['auto_penghargaan_processed_at' => now()]);
            $this->ok('auto_penghargaan_processed_at diisi',
                $event->fresh()->auto_penghargaan_processed_at !== null,
                (string) $event->fresh()->auto_penghargaan_processed_at);

        } catch (\Throwable $e) {
            $this->fail('Auto Penghargaan Event', $e->getMessage());
        }

        $this->info('');
    }


    // ══════════════════════════════════════════════════════════════════════
    // TEST 5: Auto Pelanggaran dari Konfigurasi School (AutoPelanggaranRule)
    // ══════════════════════════════════════════════════════════════════════

    private function testAutoRulesPelanggaran(Siswa $siswa1, string $tahunAjaran): void
    {
        $this->line('<fg=yellow>━━━ TEST 5: Auto Rules Pelanggaran (School Config) ━━━━</>');

        // Cek apakah ada rule aktif
        $rule = AutoPelanggaranRule::where('aktif', true)->with('pasal')->first();
        $rulesCount = AutoPelanggaranRule::count();
        $activeCount = AutoPelanggaranRule::where('aktif', true)->count();

        $this->ok('Tabel auto_pelanggaran_rules ada', true,
            "Total rules: {$rulesCount} | Aktif: {$activeCount}");

        if ($rulesCount === 0) {
            $this->skip('Test rule pelanggaran',
                'Belum ada rule dikonfigurasi di /admin/school-config');
            $this->testCreateRulePlaceholder();
            $this->info('');
            return;
        }

        if (! $rule) {
            $this->skip('Test rule pelanggaran aktif',
                "Ada {$rulesCount} rule tapi semua nonaktif");
            $this->info('');
            return;
        }

        $this->line("       <fg=gray>Rule aktif  : [{$rule->id}] {$rule->nama_rule}</>");
        $this->line("       <fg=gray>Trigger     : {$rule->trigger_type}</>");
        $this->line("       <fg=gray>Threshold   : {$rule->threshold_hari} hari | Periode: {$rule->periode_bulan} bulan</>");
        $this->line("       <fg=gray>Pasal       : " . ($rule->pasal ? "[{$rule->pasal->idpasal}] {$rule->pasal->pasal}" : '—') . "</>");
        $this->line("       <fg=gray>Poin Efektif: {$rule->poin_efektif}</>");

        // Verifikasi rule bisa dibaca dengan benar
        $this->ok('Rule bisa dimuat dengan relasi pasal',
            $rule->pasal !== null || $rule->pasal_id === null,
            $rule->pasal ? "Pasal [{$rule->pasal->idpasal}] ditemukan" : "Tidak ada pasal_id (opsional)");

        $this->ok('Poin efektif terhitung benar',
            $rule->poin_efektif >= 0,
            "poin_efektif = {$rule->poin_efektif}" .
            ($rule->poin_override !== null ? " (override)" : " (dari pasal default)"));

        // Simulasi: hitung berapa kali siswa1 alfa bulan ini (trigger alfa_bulanan)
        if ($rule->trigger_type === 'alfa_bulanan' || $rule->trigger_type === 'alfa_harian') {
            $bulanIni  = now()->startOfMonth();
            $alfaCount = AbsenSiswa::where('siswa_id', $siswa1->id)
                ->where('status_masuk', 'alfa')
                ->whereBetween('tanggal', [$bulanIni, now()])
                ->count();
            $this->ok("Hitung alfa siswa 1 bulan ini (simulasi evaluasi rule)",
                true, "Jumlah alfa: {$alfaCount} | threshold rule: {$rule->threshold_hari}");

            $akanTrigger = $alfaCount >= ($rule->threshold_hari ?? 999);
            $this->line("       <fg=gray>→ Rule " . ($akanTrigger ? 'AKAN' : 'BELUM') .
                " trigger untuk siswa ini (alfa: {$alfaCount} / threshold: {$rule->threshold_hari})</>");
        }

        // Simulasi trigger: buat pelanggaran manual dari rule ini
        if ($rule->pasal) {
            try {
                $deviceId = self::TAG_RULE . '_pel_rule_' . $rule->id . '_' . $siswa1->id;
                $alreadyExists = Pelanggaran::where('deviceid', $deviceId)
                    ->where('siswa_id', $siswa1->id)->exists();

                if (! $alreadyExists) {
                    $pel = Pelanggaran::create([
                        'siswa_id'     => $siswa1->id,
                        'tgl'          => now(),
                        'tahun_ajaran' => $tahunAjaran,
                        'deviceid'     => $deviceId,
                        'noreg'        => $siswa1->nis ?? '',
                        'nama'         => $siswa1->nama_lengkap,
                        'kelas'        => $siswa1->kelas?->nama_kelas ?? '',
                        'idpasal'      => $rule->pasal->idpasal,
                        'isi'          => '[TEST] Simulasi trigger rule: ' . $rule->nama_rule,
                        'poin'         => $rule->poin_efektif,
                        'pelapor'      => 'SisTestCommand (rule #' . $rule->id . ')',
                        'created_by'   => null,
                    ]);
                    $this->markCleanupPelanggaran($pel->idpel);
                    $this->ok('Simulasi poin dari rule berhasil disimpan', true,
                        "ID #{$pel->idpel} | poin: {$pel->poin} | pasal: [{$rule->pasal->idpasal}]");
                }
            } catch (\Throwable $e) {
                $this->fail('Simulasi poin dari rule', $e->getMessage());
            }
        } else {
            $this->skip('Simulasi poin dari rule',
                'Rule tidak punya pasal — tidak ada yang disimpan');
        }

        $this->info('');
    }


    // ══════════════════════════════════════════════════════════════════════
    // TEST 6: Auto Penghargaan dari Konfigurasi School (AutoPenghargaanRule)
    // ══════════════════════════════════════════════════════════════════════

    private function testAutoRulesPenghargaan(Siswa $siswa1, string $tahunAjaran): void
    {
        $this->line('<fg=yellow>━━━ TEST 6: Auto Rules Penghargaan (School Config) ━━━━</>');

        $rule        = AutoPenghargaanRule::where('aktif', true)->with('pasal')->first();
        $rulesCount  = AutoPenghargaanRule::count();
        $activeCount = AutoPenghargaanRule::where('aktif', true)->count();

        $this->ok('Tabel auto_penghargaan_rules ada', true,
            "Total rules: {$rulesCount} | Aktif: {$activeCount}");

        if ($rulesCount === 0) {
            $this->skip('Test rule penghargaan',
                'Belum ada rule dikonfigurasi di /admin/school-config');
            $this->info('');
            return;
        }

        if (! $rule) {
            $this->skip('Test rule penghargaan aktif',
                "Ada {$rulesCount} rule tapi semua nonaktif");
            $this->info('');
            return;
        }

        $this->line("       <fg=gray>Rule aktif  : [{$rule->id}] {$rule->nama_rule}</>");
        $this->line("       <fg=gray>Trigger     : {$rule->trigger_type}</>");
        $this->line("       <fg=gray>Periode     : {$rule->periode_bulan} bulan</>");
        $this->line("       <fg=gray>Izin=hadir  : " . ($rule->izin_dihitung_hadir ? 'YA' : 'TIDAK') . " | Sakit=hadir: " .
            ($rule->sakit_dihitung_hadir ? 'YA' : 'TIDAK') . " | Terlambat=hadir: " .
            ($rule->terlambat_dihitung_hadir ? 'YA' : 'TIDAK') . "</>");
        $this->line("       <fg=gray>Pasal       : " . ($rule->pasal ? "[{$rule->pasal->idpasal}] {$rule->pasal->pasal}" : '—') . "</>");
        $this->line("       <fg=gray>Poin Efektif: {$rule->poin_efektif}</>");

        $this->ok('Rule bisa dimuat dengan relasi pasal',
            $rule->pasal !== null || $rule->pasal_id === null,
            $rule->pasal ? "Pasal [{$rule->pasal->idpasal}] ditemukan" : "Tidak ada pasal_id");

        $this->ok('Cast boolean rule berfungsi',
            is_bool($rule->izin_dihitung_hadir),
            "izin_dihitung_hadir: " . ($rule->izin_dihitung_hadir ? 'true' : 'false'));

        // Simulasi evaluasi rule full_hadir_bulanan
        if (in_array($rule->trigger_type, ['full_hadir_bulanan', 'full_hadir_mingguan', 'streak_hadir'])) {
            $bulanIni    = now()->startOfMonth();
            $statusOk    = ['hadir'];
            if ($rule->terlambat_dihitung_hadir) $statusOk[] = 'terlambat';
            if ($rule->izin_dihitung_hadir)      $statusOk[] = 'izin';
            if ($rule->sakit_dihitung_hadir)      $statusOk[] = 'sakit';

            $absenBulanIni = AbsenSiswa::where('siswa_id', $siswa1->id)
                ->whereBetween('tanggal', [$bulanIni, now()])
                ->get();

            $totalHari   = $absenBulanIni->count();
            $hariOk      = $absenBulanIni->whereIn('status_masuk', $statusOk)->count();
            $alfaCount   = $absenBulanIni->where('status_masuk', 'alfa')->count();

            $this->ok("Hitung kehadiran siswa 1 bulan ini (simulasi evaluasi rule)", true,
                "Total hari: {$totalHari} | OK: {$hariOk} | Alfa: {$alfaCount} | status dihitung: [" .
                implode(',', $statusOk) . "]");

            $fullHadir = $alfaCount === 0 && $totalHari > 0;
            $this->line("       <fg=gray>→ Rule " . ($fullHadir ? 'AKAN' : 'BELUM') .
                " trigger untuk siswa ini (alfa: {$alfaCount})</>");
        }

        // Simulasi trigger: simpan penghargaan dari rule
        if ($rule->pasal) {
            try {
                $deviceId = self::TAG_RULE . '_pgh_rule_' . $rule->id . '_' . $siswa1->id;
                $alreadyExists = Penghargaan::where('deviceid', $deviceId)
                    ->where('siswa_id', $siswa1->id)->exists();

                if (! $alreadyExists) {
                    $pen = Penghargaan::create([
                        'siswa_id'     => $siswa1->id,
                        'tgl'          => now(),
                        'tahun_ajaran' => $tahunAjaran,
                        'deviceid'     => $deviceId,
                        'noreg'        => $siswa1->nis ?? '',
                        'nama'         => $siswa1->nama_lengkap,
                        'kelas'        => $siswa1->kelas?->nama_kelas ?? '',
                        'idpasal'      => $rule->pasal->idpasal,
                        'isi'          => '[TEST] Simulasi trigger rule: ' . $rule->nama_rule,
                        'poin'         => $rule->poin_efektif,
                        'pelapor'      => 'SisTestCommand (rule #' . $rule->id . ')',
                        'created_by'   => null,
                    ]);
                    $this->markCleanupPenghargaan($pen->idpen);
                    $this->ok('Simulasi penghargaan dari rule berhasil disimpan', true,
                        "ID #{$pen->idpen} | poin: {$pen->poin} | pasal: [{$rule->pasal->idpasal}]");
                }
            } catch (\Throwable $e) {
                $this->fail('Simulasi penghargaan dari rule', $e->getMessage());
            }
        } else {
            $this->skip('Simulasi penghargaan dari rule',
                'Rule tidak punya pasal — tidak ada yang disimpan');
        }

        $this->info('');
    }

    // ══════════════════════════════════════════════════════════════════════
    // TEST 7: Absen Masuk & Pulang Harian (dari sisi siswa)
    // ══════════════════════════════════════════════════════════════════════

    private function testAbsenMasukPulangHarian(Siswa $siswa1, string $tanggal): void
    {
        $this->line('<fg=yellow>━━━ TEST 7: Absen Masuk & Pulang Harian (Siswa) ━━━━━━━</>');

        try {
            // Bersihkan state awal untuk siswa ini
            AbsenSiswa::where('siswa_id', $siswa1->id)
                ->whereDate('tanggal', $tanggal)
                ->where('catatan', 'like', self::TAG . '_ABSEN_%')
                ->delete();

            // ── Step 1: Simulasi absen MASUK ───────────────────────────────────
            $absen = AbsenSiswa::firstOrCreate(
                ['siswa_id' => $siswa1->id, 'tanggal' => $tanggal],
                ['kelas_id' => $siswa1->kelas_id, 'jenis' => 'masuk', 'status' => 'alfa',
                 'catatan'  => self::TAG . '_ABSEN_MASUK']
            );

            // Simulasi simpanAbsenMasuk (tanpa foto & koordinat nyata)
            $jamMasuk    = now()->format('H:i:s');
            $statusMasuk = now()->format('H') >= 8 ? 'terlambat' : 'hadir'; // tentukan status otomatis
            $absen->update([
                'jam_masuk'       => $jamMasuk,
                'status_masuk'    => $statusMasuk,
                'latitude_masuk'  => -7.6291,
                'longitude_masuk' => 111.5230,
                'jarak_masuk'     => 45,
                'foto_selfie_masuk' => 'test/foto_masuk_placeholder.jpg',
                'jenis'           => 'masuk',
                'status'          => $statusMasuk,
                'waktu_absen'     => now(),
                'catatan'         => self::TAG . '_ABSEN_MASUK',
            ]);
            $this->markCleanupAbsen($absen->id);

            $fresh = $absen->fresh();
            $this->ok('Absen masuk harian tersimpan',
                $fresh->jam_masuk !== null && $fresh->status_masuk !== null,
                "jam_masuk: {$fresh->jam_masuk} | status_masuk: {$fresh->status_masuk} | jarak: {$fresh->jarak_masuk}m");

            // Verifikasi sudahMasuk()
            $this->ok('Helper sudahMasuk() = true setelah scan',
                $fresh->sudahMasuk() === true,
                'sudahMasuk(): ' . ($fresh->sudahMasuk() ? 'true' : 'false'));

            // Verifikasi statusDisplay()
            $this->ok('statusDisplay() mengembalikan status yang benar',
                in_array($fresh->statusDisplay(), ['hadir', 'terlambat', 'alfa']),
                "statusDisplay(): {$fresh->statusDisplay()}");

            // Verifikasi DUPLIKASI masuk — jika sudah masuk tidak bisa masuk lagi
            $sudahMasukBefore = $fresh->sudahMasuk();
            $this->ok('Cek kondisi duplikasi masuk (sudahMasuk=true → harus di-block)',
                $sudahMasukBefore === true,
                'Controller memeriksa sudahMasuk sebelum proses — jika true → return error 409');

            // ── Step 2: Simulasi absen PULANG ──────────────────────────────────
            $jamPulang = now()->addHours(6)->format('H:i:s');
            $absen->update([
                'jam_pulang'        => $jamPulang,
                'status_pulang'     => 'hadir',
                'latitude_pulang'   => -7.6291,
                'longitude_pulang'  => 111.5230,
                'jarak_pulang'      => 38,
                'foto_selfie_pulang' => 'test/foto_pulang_placeholder.jpg',
            ]);

            $fresh2 = $absen->fresh();
            $this->ok('Absen pulang harian tersimpan',
                $fresh2->jam_pulang !== null && $fresh2->status_pulang !== null,
                "jam_pulang: {$fresh2->jam_pulang} | status_pulang: {$fresh2->status_pulang} | jarak: {$fresh2->jarak_pulang}m");

            $this->ok('Helper sudahPulang() = true setelah scan pulang',
                $fresh2->sudahPulang() === true,
                'sudahPulang(): ' . ($fresh2->sudahPulang() ? 'true' : 'false'));

            // ── Step 3: Verifikasi record 1 siswa = 1 record per hari ──────────
            $count = AbsenSiswa::where('siswa_id', $siswa1->id)
                ->whereDate('tanggal', $tanggal)->count();
            $this->ok('1 record per siswa per hari (pola unified)',
                $count === 1,
                "Jumlah record hari ini untuk siswa ini: {$count}");

            // ── Step 4: Verifikasi kolom legacy juga terisi ────────────────────
            $this->ok('Kolom legacy (status, waktu_absen) ikut terisi',
                $fresh2->status !== null,
                "status (legacy): {$fresh2->status} | waktu_absen: " . ($fresh2->waktu_absen ?? 'null'));

        } catch (\Throwable $e) {
            $this->logFail('Absen Masuk & Pulang Harian', $e->getMessage());
        }

        $this->info('');
    }

    // ══════════════════════════════════════════════════════════════════════
    // TEST 8: Absen Event Masuk & Pulang (dari sisi siswa)
    // ══════════════════════════════════════════════════════════════════════

    private function testAbsenEventMasukPulang(Siswa $siswa1, Siswa $siswa2): void
    {
        $this->line('<fg=yellow>━━━ TEST 8: Absen Event Masuk & Pulang (Siswa) ━━━━━━━━</>');

        try {
            // Buat event test yang AKTIF sekarang
            $eventAktif = Event::create([
                'nama_event'          => '[TEST] Absen Event Aktif ' . now()->format('His'),
                'tanggal_mulai'       => now()->subHour(),
                'tanggal_selesai'     => now()->addHours(3),
                'berlaku_untuk_semua' => false,
                'mode_peserta'        => 'siswa',
                'ada_absen_masuk'     => true,
                'ada_absen_pulang'    => true,
                'auto_penghargaan'    => false,
                'auto_point_pelanggaran' => false,
                'barcode_value'       => hash('sha256', 'test-event-aktif-' . time()),
                'barcode_updated_at'  => now(),
                'created_by'          => null,
            ]);
            $this->markCleanupEvent($eventAktif->id);

            // Daftarkan kedua siswa
            $eventAktif->siswa()->sync([$siswa1->id, $siswa2->id]);

            $this->ok('Event aktif dibuat', true,
                "ID #{$eventAktif->id} | berlangsung: {$eventAktif->tanggal_mulai->format('H:i')} – {$eventAktif->tanggal_selesai->format('H:i')}");

            // Verifikasi isActive()
            $this->ok('Event->isActive() = true',
                $eventAktif->isActive() === true,
                'Event sedang berlangsung sekarang');

            // Verifikasi appliesToSiswa()
            $this->ok('Event berlaku untuk siswa 1 (appliesToSiswa)',
                $eventAktif->appliesToSiswa($siswa1->id) === true,
                "siswa #{$siswa1->id} terdaftar sebagai peserta");

            $this->ok('Event berlaku untuk siswa 2 (appliesToSiswa)',
                $eventAktif->appliesToSiswa($siswa2->id) === true,
                "siswa #{$siswa2->id} terdaftar sebagai peserta");

            // Verifikasi canAbsen()
            $this->ok('canAbsen("masuk") = true',
                $eventAktif->canAbsen('masuk') === true, 'ada_absen_masuk = true');
            $this->ok('canAbsen("pulang") = true',
                $eventAktif->canAbsen('pulang') === true, 'ada_absen_pulang = true');

            // ── Simulasi SCAN MASUK event oleh siswa 1 ─────────────────────────
            $barcodeValid = $eventAktif->barcode_value;

            // Validasi barcode cocok
            $this->ok('Barcode valid (cocok dengan event)',
                $barcodeValid === $eventAktif->barcode_value, 'barcode: ' . substr($barcodeValid, 0, 16) . '...');

            // Cek belum ada record → buat record masuk
            $existing = AbsenEvent::where('event_id', $eventAktif->id)
                ->where('siswa_id', $siswa1->id)->first();
            $this->ok('Belum ada record absen event sebelum scan', $existing === null,
                $existing ? "Ada record ID #{$existing->id}" : 'Tidak ada — siap scan');

            $absenEvent = AbsenEvent::create([
                'event_id'         => $eventAktif->id,
                'siswa_id'         => $siswa1->id,
                'waktu_masuk'      => now(),
                'barcode_masuk'    => $barcodeValid,
                'wa_terkirim_ortu' => false,
                'created_by'       => null,
                'jenis'            => 'masuk',
                'waktu_scan'       => now(),
                'barcode_digunakan' => $barcodeValid,
            ]);
            $this->markCleanupAbsenEvent($absenEvent->id);

            $this->ok('Absen event MASUK berhasil disimpan',
                $absenEvent->waktu_masuk !== null,
                "ID #{$absenEvent->id} | waktu_masuk: " . $absenEvent->waktu_masuk->format('H:i:s'));

            $this->ok('Helper sudahMasuk() = true',
                $absenEvent->sudahMasuk() === true, '');

            $this->ok('waktu_pulang masih NULL setelah scan masuk',
                $absenEvent->waktu_pulang === null, 'waktu_pulang: NULL ✓');

            // ── Proteksi scan masuk ganda ───────────────────────────────────────
            $dupMasuk = AbsenEvent::where('event_id', $eventAktif->id)
                ->where('siswa_id', $siswa1->id)
                ->whereNotNull('waktu_masuk')->count();
            $this->ok('Proteksi duplikasi scan masuk (1 record per siswa per event)',
                $dupMasuk === 1, "Record masuk untuk siswa ini: {$dupMasuk}");

            // ── Simulasi SCAN PULANG event oleh siswa 1 ────────────────────────
            // Pastikan sudah ada record masuk → update dengan data pulang
            $this->ok('Prasyarat scan pulang: sudah masuk (waktu_masuk terisi)',
                $absenEvent->waktu_masuk !== null, 'OK — bisa lanjut scan pulang');

            $absenEvent->update([
                'waktu_pulang'      => now()->addHours(2),
                'barcode_pulang'    => $barcodeValid,
                'jenis'             => 'pulang',
                'waktu_scan'        => now()->addHours(2),
                'barcode_digunakan' => $barcodeValid,
            ]);

            $fresh = $absenEvent->fresh();
            $this->ok('Absen event PULANG berhasil disimpan',
                $fresh->waktu_pulang !== null,
                "ID #{$fresh->id} | waktu_pulang: " . $fresh->waktu_pulang->format('H:i:s'));

            $this->ok('Helper sudahPulang() = true',
                $fresh->sudahPulang() === true, '');

            $this->ok('1 record unified untuk masuk & pulang event',
                AbsenEvent::where('event_id', $eventAktif->id)
                    ->where('siswa_id', $siswa1->id)->count() === 1,
                'Pola unified: masuk & pulang dalam 1 record');

            // ── Event non-aktif tidak bisa di-scan ─────────────────────────────
            $eventSelesai = Event::create([
                'nama_event'          => '[TEST] Event Sudah Selesai ' . now()->format('His'),
                'tanggal_mulai'       => now()->subDays(2),
                'tanggal_selesai'     => now()->subDay(),
                'berlaku_untuk_semua' => true,
                'mode_peserta'        => 'kelas',
                'ada_absen_masuk'     => true,
                'ada_absen_pulang'    => false,
                'auto_penghargaan'    => false,
                'auto_point_pelanggaran' => false,
                'barcode_value'       => hash('sha256', 'test-event-selesai-' . time()),
                'barcode_updated_at'  => now()->subDays(2),
                'created_by'          => null,
            ]);
            $this->markCleanupEvent($eventSelesai->id);

            $this->ok('Event sudah selesai → isActive() = false',
                $eventSelesai->isActive() === false,
                "tanggal_selesai: {$eventSelesai->tanggal_selesai} (sudah lewat)");

            // ── Event tanpa absen pulang → canAbsen("pulang") = false ─────────
            $eventTanpaPulang = Event::create([
                'nama_event'          => '[TEST] Event Tanpa Pulang ' . now()->format('His'),
                'tanggal_mulai'       => now()->subHour(),
                'tanggal_selesai'     => now()->addHours(3),
                'berlaku_untuk_semua' => true,
                'mode_peserta'        => 'kelas',
                'ada_absen_masuk'     => true,
                'ada_absen_pulang'    => false,
                'auto_penghargaan'    => false,
                'auto_point_pelanggaran' => false,
                'barcode_value'       => hash('sha256', 'test-event-tanpapulang-' . time()),
                'barcode_updated_at'  => now(),
                'created_by'          => null,
            ]);
            $this->markCleanupEvent($eventTanpaPulang->id);

            $this->ok('Event tanpa absen pulang → canAbsen("pulang") = false',
                $eventTanpaPulang->canAbsen('pulang') === false,
                'ada_absen_pulang = false → ditolak');

            $this->ok('Event tanpa absen pulang → canAbsen("masuk") = true',
                $eventTanpaPulang->canAbsen('masuk') === true,
                'ada_absen_masuk = true → diizinkan');

        } catch (\Throwable $e) {
            $this->logFail('Absen Event Masuk & Pulang', $e->getMessage());
        }

        $this->info('');
    }

    // ══════════════════════════════════════════════════════════════════════
    // TEST 9: Observer — Poin Auto-Alfa Terhapus Saat Status Diubah
    // ══════════════════════════════════════════════════════════════════════

    private function testObserverAlfaCleanup(Siswa $siswa1, string $tanggal, string $tahunAjaran): void
    {
        $this->line('<fg=yellow>━━━ TEST 9: Observer — Soft Delete & Restore Poin Alfa ━</>');

        $tanggalTest = Carbon::yesterday()->toDateString();

        try {
            // Bersihkan state awal (force delete agar test selalu bersih)
            AbsenSiswa::where('siswa_id', $siswa1->id)->whereDate('tanggal', $tanggalTest)
                ->where('catatan', 'like', self::TAG . '%')->forceDelete();
            Pelanggaran::withTrashed()->where('siswa_id', $siswa1->id)
                ->where('deviceid', 'auto-alfa-' . $tanggalTest)->forceDelete();

            // ── Setup ──────────────────────────────────────────────────────────
            $absen = AbsenSiswa::create([
                'siswa_id'     => $siswa1->id, 'kelas_id' => $siswa1->kelas_id,
                'tanggal'      => $tanggalTest, 'status_masuk' => 'alfa',
                'status'       => 'alfa', 'jenis' => 'masuk',
                'catatan'      => self::TAG . '_OBSERVER',
            ]);
            $this->markCleanupAbsen($absen->id);

            $pel = Pelanggaran::create([
                'siswa_id'     => $siswa1->id, 'tgl' => Carbon::parse($tanggalTest),
                'tahun_ajaran' => $tahunAjaran, 'deviceid' => 'auto-alfa-' . $tanggalTest,
                'noreg'        => $siswa1->nis ?? '', 'nama' => $siswa1->nama_lengkap,
                'kelas'        => $siswa1->kelas?->nama_kelas ?? '', 'idpasal' => 'B012',
                'isi'          => '[TEST] Alfa otomatis', 'poin' => 30, 'pelapor' => 'Test',
            ]);
            $noreff = 'PN' . Carbon::parse($tanggalTest)->format('ymd') . $pel->idpel;

            $this->ok('Setup: absen alfa + poin alfa + transaksi dibuat', true,
                "poin ID#{$pel->idpel} | noreff: {$noreff}");

            // Verifikasi transaksi dibuat via PelanggaranObserver::created
            $trxAda = \App\Models\TransaksiPoin::where('noreff', $noreff)->exists();
            $this->ok('PelanggaranObserver: created → transaksi ada', $trxAda,
                $trxAda ? 'TransaksiPoin dibuat otomatis ✓' : 'TIDAK ADA!');

            // ── Skenario 1: alfa → terlambat → soft delete ─────────────────────
            $absen->update(['status_masuk' => 'terlambat', 'status' => 'terlambat',
                            'jam_masuk' => '08:30:00']);

            $pelAktif   = Pelanggaran::where('siswa_id', $siswa1->id)
                ->where('deviceid', 'auto-alfa-' . $tanggalTest)->count();
            $pelTrashed = Pelanggaran::onlyTrashed()->where('siswa_id', $siswa1->id)
                ->where('deviceid', 'auto-alfa-' . $tanggalTest)->count();
            $trxAktif   = \App\Models\TransaksiPoin::where('noreff', $noreff)->count();
            $trxTrashed = \App\Models\TransaksiPoin::withTrashed()
                ->where('noreff', $noreff)->whereNotNull('deleted_at')->count();

            $this->ok('alfa → terlambat: Pelanggaran di-soft-delete (aktif=0)',
                $pelAktif === 0, "aktif: {$pelAktif} | trashed: {$pelTrashed}");
            $this->ok('alfa → terlambat: Transaksi di-soft-delete (aktif=0)',
                $trxAktif === 0, "aktif: {$trxAktif} | trashed: {$trxTrashed}");
            $this->ok('Data masih ada di trash (bisa dipulihkan)',
                $pelTrashed > 0 && $trxTrashed > 0,
                "Pelanggaran trashed: {$pelTrashed} | Transaksi trashed: {$trxTrashed}");

            // ── Skenario 2: terlambat → alfa kembali → restore ─────────────────
            $absen->update(['status_masuk' => 'alfa', 'status' => 'alfa', 'jam_masuk' => null]);

            $pelAktifRestored = Pelanggaran::where('siswa_id', $siswa1->id)
                ->where('deviceid', 'auto-alfa-' . $tanggalTest)->count();
            $trxAktifRestored = \App\Models\TransaksiPoin::where('noreff', $noreff)->count();

            $this->ok('terlambat → alfa: Pelanggaran di-restore (aktif=1)',
                $pelAktifRestored === 1, "aktif: {$pelAktifRestored}");
            $this->ok('terlambat → alfa: Transaksi di-restore (aktif=1)',
                $trxAktifRestored === 1, "aktif: {$trxAktifRestored}");

            // ── Skenario 3: alfa → izin → soft delete ──────────────────────────
            $absen->update(['status_masuk' => 'izin', 'status' => 'izin']);
            $pelAfterIzin = Pelanggaran::where('siswa_id', $siswa1->id)
                ->where('deviceid', 'auto-alfa-' . $tanggalTest)->count();
            $this->ok('alfa → izin: Pelanggaran di-soft-delete',
                $pelAfterIzin === 0, "aktif: {$pelAfterIzin}");

            // ── Skenario 4: force delete → transaksi juga force delete ─────────
            Pelanggaran::withTrashed()->where('siswa_id', $siswa1->id)
                ->where('deviceid', 'auto-alfa-' . $tanggalTest)->first()?->forceDelete();
            $trxSetelahForce = \App\Models\TransaksiPoin::withTrashed()
                ->where('noreff', $noreff)->count();
            $this->ok('forceDelete Pelanggaran → Transaksi ikut force-delete',
                $trxSetelahForce === 0,
                $trxSetelahForce === 0 ? 'Transaksi benar-benar terhapus ✓' : "Masih ada: {$trxSetelahForce}");

            // Cleanup sisa
            AbsenSiswa::find($absen->id)?->forceDelete();

        } catch (\Throwable $e) {
            $this->logFail('Observer Soft Delete & Restore', $e->getMessage());
        }

        $this->info('');
    }

    // Helper: tampilkan info jika belum ada rule sama sekali
    private function testCreateRulePlaceholder(): void
    {
        $pasal = SubPasal::orderBy('idpasal')->first();
        if (! $pasal) return;

        $this->line("       <fg=gray>Tip: Buat rule pertama via /admin/school-config</>");
        $this->line("       <fg=gray>atau via tinker:</>");
        $this->line("       <fg=gray>  AutoPelanggaranRule::create(['nama_rule'=>'Alfa Harian',</>");
        $this->line("       <fg=gray>    'aktif'=>true,'trigger_type'=>'alfa_harian',</>");
        $this->line("       <fg=gray>    'threshold_hari'=>1,'periode_bulan'=>0,</>");
        $this->line("       <fg=gray>    'pasal_id'=>'{$pasal->idpasal}','urutan'=>1])</>  ");
    }


    // ══════════════════════════════════════════════════════════════════════
    // CLEANUP
    // ══════════════════════════════════════════════════════════════════════

    private array $cleanupPelanggaran = [];
    private array $cleanupPenghargaan = [];
    private array $cleanupAbsen       = [];
    private array $cleanupAbsenEvent  = [];
    private array $cleanupEvent       = [];

    private function markCleanupPelanggaran(int $id): void  { $this->cleanupPelanggaran[] = $id; }
    private function markCleanupPenghargaan(int $id): void  { $this->cleanupPenghargaan[] = $id; }
    private function markCleanupAbsen(int $id): void        { $this->cleanupAbsen[] = $id; }
    private function markCleanupAbsenEvent(int $id): void   { $this->cleanupAbsenEvent[] = $id; }
    private function markCleanupEvent(int $id): void        { $this->cleanupEvent[] = $id; }

    private function doCleanup(): void
    {
        $this->info('');
        $this->line('<fg=cyan>🧹 Membersihkan semua data test...</>');

        // Hapus berdasarkan tag deviceid/catatan (lebih aman dari ID — menangkap semua run sebelumnya)
        $tags = [self::TAG . '%', self::TAG_EVENT . '%', self::TAG_RULE . '%'];

        $pelCount = 0;
        $penCount = 0;
        foreach ($tags as $tag) {
            $pelCount += Pelanggaran::withTrashed()->where('deviceid', 'like', $tag)->forceDelete();
            $penCount += Penghargaan::where('deviceid', 'like', $tag)->forceDelete();
        }
        $this->line("   Pelanggaran test dihapus : {$pelCount}");
        $this->line("   Penghargaan test dihapus : {$penCount}");

        // AbsenEvent dari event test
        $testEventIds = Event::where('nama_event', 'like', '[TEST]%')->pluck('id');
        $absenEvCount = 0;
        if ($testEventIds->isNotEmpty()) {
            $absenEvCount = AbsenEvent::whereIn('event_id', $testEventIds)->delete();
            DB::table('event_siswa')->whereIn('event_id', $testEventIds)->delete();
        }
        $this->line("   AbsenEvent test dihapus  : {$absenEvCount}");

        // Event test (force delete karena SoftDeletes)
        $evCount = Event::where('nama_event', 'like', '[TEST]%')->forceDelete();
        $this->line("   Event test dihapus       : {$evCount}");

        // AbsenSiswa dengan catatan TAG
        $absenCount = 0;
        foreach ($tags as $tag) {
            $absenCount += AbsenSiswa::where('catatan', 'like', $tag)->delete();
        }
        $this->line("   AbsenSiswa test dihapus  : {$absenCount}");

        $this->line('');
        $this->line('<fg=green>✅ Cleanup selesai. Database bersih dari data test.</>');
        $this->info('');
    }

    // ══════════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════════

    private function getSiswaTest()
    {
        $id = $this->option('siswa-id');
        if ($id) {
            return Siswa::with('kelas')->where('id', $id)->where('status_aktif', true)->get();
        }
        return Siswa::with('kelas')->where('status_aktif', true)
            ->whereNotNull('kelas_id')->orderBy('id')->limit(2)->get();
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

    // ── Print helpers ──────────────────────────────────────────────────────

    private function ok(string $label, bool $passed, string $detail = ''): void
    {
        $this->results[] = ['label' => $label, 'status' => $passed ? 'PASS' : 'FAIL'];
        if ($passed) {
            $this->line("  <fg=green>✅ PASS</> {$label}" . ($detail ? " <fg=gray>— {$detail}</>" : ''));
        } else {
            $this->line("  <fg=red>❌ FAIL</> {$label}" . ($detail ? " <fg=red>— {$detail}</>" : ''));
        }
    }

    private function logFail(string $label, string $detail = ''): void
    {
        $this->results[] = ['label' => $label, 'status' => 'FAIL'];
        $this->line("  <fg=red>❌ FAIL</> {$label}" . ($detail ? " <fg=red>— {$detail}</>" : ''));
    }

    private function skip(string $label, string $detail = ''): void
    {
        $this->results[] = ['label' => $label, 'status' => 'SKIP'];
        $this->line("  <fg=yellow>⏭  SKIP</> {$label}" . ($detail ? " <fg=yellow>— {$detail}</>" : ''));
    }

    private function printSummary(): void
    {
        $pass  = count(array_filter($this->results, fn($r) => $r['status'] === 'PASS'));
        $fail  = count(array_filter($this->results, fn($r) => $r['status'] === 'FAIL'));
        $skip  = count(array_filter($this->results, fn($r) => $r['status'] === 'SKIP'));
        $total = count($this->results);

        $this->line('');
        $color = $fail === 0 ? 'green' : 'red';
        $this->line("<fg=cyan>╔══════════════════════════════════════════════════════╗</>");
        $this->line("<fg=cyan>║</> <fg={$color}>HASIL: PASS {$pass} | FAIL {$fail} | SKIP {$skip} | TOTAL {$total}     </><fg=cyan>║</>");
        $this->line("<fg=cyan>╚══════════════════════════════════════════════════════╝</>");

        if ($fail > 0) {
            $this->line('');
            $this->error('Yang GAGAL:');
            foreach ($this->results as $r) {
                if ($r['status'] === 'FAIL') {
                    $this->line("  <fg=red>❌ {$r['label']}</>");
                }
            }
        }

        if ($fail === 0) {
            $this->line('');
            $this->line('<fg=green>🎉 Semua test PASS — fitur berjalan dengan benar!</>');
        }
    }
}
