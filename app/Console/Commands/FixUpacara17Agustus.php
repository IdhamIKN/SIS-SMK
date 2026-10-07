<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Perbaikan data 17 Agustus 2026 (Hari Kemerdekaan / Libur):
 *
 * 1. Hapus poin Alfa (tidak hadir tanpa keterangan) — tanggal merah, tidak masuk sekolah
 * 2. Pastikan siswa izin/sakit TIDAK mendapat pelanggaran UPACARA
 * 3. Tambah poin pelanggaran UPACARA bagi siswa yang tidak scan (bukan izin/sakit)
 *    yang belum mendapat poin (menambah yang kurang dari 41 yang sudah ada)
 *
 * Gunakan --dry-run untuk preview tanpa mengubah data.
 */
class FixUpacara17Agustus extends Command
{
    protected $signature = 'fix:upacara-17-agustus
                            {--dry-run : Preview saja, tidak ada yang diubah}';

    protected $description = 'Perbaikan data 17 Agustus 2026: hapus alfa, validasi & lengkapi pelanggaran UPACARA';

    // ── Konfigurasi ────────────────────────────────────────────────
    private const EVENT_ID      = 6006;
    private const TGL           = '2026-08-17';
    private const DEVICE_ALFA   = 'auto-alfa-2026-08-17';
    private const DEVICE_TELAT  = 'auto-terlambat-2026-08-17';
    private const DEVICE_PEL    = 'auto-event-6006';
    private const DEVICE_PEN    = 'auto-event-penghargaan-6006';
    private const PASAL_PEL     = 'B010';
    private const POIN_PEL      = 25;
    private const ISI_PEL       = 'Tidak hadir pada event: UPACARA 17 AGUSTUS 2026';
    private const NAMA_EVENT    = 'UPACARA 17 AGUSTUS 2026';

    public function handle(): int
    {
        $isDry = $this->option('dry-run');

        $this->info('============================================================');
        $this->info('PERBAIKAN DATA 17 AGUSTUS 2026');
        $this->info('Mode: ' . ($isDry ? 'DRY RUN (tidak ada perubahan)' : 'LIVE'));
        $this->info('============================================================');
        $this->newLine();

        $tahunAjaran = $this->getTahunAjaran();
        $this->line("Tahun ajaran: {$tahunAjaran}");
        $this->newLine();

        // ── BAGIAN 1: Hapus poin alfa ─────────────────────────────
        $this->info('── BAGIAN 1: HAPUS POIN ALFA ──');
        $this->hapusByDeviceId(self::DEVICE_ALFA, 'pelanggaran alfa', $isDry);
        $this->newLine();

        // ── BAGIAN 2: Hapus poin terlambat ────────────────────────
        $this->info('── BAGIAN 2: HAPUS POIN TERLAMBAT ──');
        $this->hapusByDeviceId(self::DEVICE_TELAT, 'pelanggaran terlambat', $isDry);
        $this->newLine();

        // ── BAGIAN 3: Cek siswa izin/sakit ───────────────────────
        $izinSakitIds = $this->getIzinSakitIds();
        $this->info('── BAGIAN 3: SISWA IZIN/SAKIT ──');
        $this->line('Siswa izin/sakit: ' . count($izinSakitIds));
        if (! empty($izinSakitIds)) {
            $this->hapusPelanggaranIzin($izinSakitIds, $isDry);
        }
        $this->newLine();

        // ── BAGIAN 4: Tambah pelanggaran UPACARA yang kurang ─────
        $this->info('── BAGIAN 4: TAMBAH PELANGGARAN UPACARA ──');
        $this->tambahPelanggaranUpacara($izinSakitIds, $tahunAjaran, $isDry);
        $this->newLine();

        // ── BAGIAN 5: Verifikasi ──────────────────────────────────
        if (! $isDry) {
            $this->info('── VERIFIKASI ──');
            $this->verifikasi($izinSakitIds);
        }

        if ($isDry) {
            $this->warn('DRY RUN selesai. Tidak ada data yang diubah.');
            $this->line('Jalankan tanpa --dry-run untuk menerapkan perubahan.');
        } else {
            $this->info('SELESAI.');
        }

        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────

    /**
     * Hapus pelanggaran + transaksi terkait berdasarkan deviceid.
     */
    private function hapusByDeviceId(string $deviceId, string $label, bool $isDry): void
    {
        $count = DB::table('tblpelanggaran')->where('deviceid', $deviceId)->count();

        $transCount = DB::selectOne("
            SELECT COUNT(*) as cnt FROM tbltransaksi t
            JOIN tblpelanggaran p ON t.noreff = CONCAT('PN', DATE_FORMAT(p.tgl,'%y%m%d'), p.idpel)
            WHERE p.deviceid = ?
        ", [$deviceId])->cnt;

        $this->line("Pelanggaran {$label} yang akan dihapus : {$count}");
        $this->line("Transaksi terkait yang akan dihapus   : {$transCount}");

        if ($count === 0) {
            $this->line("Tidak ada {$label} untuk dihapus. Skip.");
            return;
        }

        if ($isDry) {
            return;
        }

        DB::transaction(function () use ($deviceId) {
            DB::statement("
                DELETE t FROM tbltransaksi t
                JOIN tblpelanggaran p ON t.noreff = CONCAT('PN', DATE_FORMAT(p.tgl,'%y%m%d'), p.idpel)
                WHERE p.deviceid = ?
            ", [$deviceId]);

            DB::table('tblpelanggaran')->where('deviceid', $deviceId)->delete();
        });

        $this->line("✓ {$count} {$label} dan {$transCount} transaksinya dihapus.");
    }

    private function getIzinSakitIds(): array
    {
        return DB::table('absen_siswa')
            ->whereDate('tanggal', self::TGL)
            ->whereRaw("COALESCE(status_masuk, status) IN ('izin', 'sakit')")
            ->pluck('siswa_id')
            ->toArray();
    }

    private function hapusPelanggaranIzin(array $izinSakitIds, bool $isDry): void
    {
        $salah = DB::table('tblpelanggaran')
            ->where('deviceid', self::DEVICE_PEL)
            ->whereIn('siswa_id', $izinSakitIds)
            ->count();

        if ($salah === 0) {
            $this->line('Tidak ada pelanggaran UPACARA yang perlu dihapus dari siswa izin/sakit.');
            return;
        }

        $this->warn("Ada {$salah} pelanggaran UPACARA dari siswa izin/sakit yang akan dihapus.");

        if ($isDry) {
            return;
        }

        DB::transaction(function () use ($izinSakitIds) {
            // Hapus transaksi terkait
            $idpels = DB::table('tblpelanggaran')
                ->where('deviceid', self::DEVICE_PEL)
                ->whereIn('siswa_id', $izinSakitIds)
                ->pluck('idpel');

            foreach ($idpels as $idpel) {
                $tgl = DB::table('tblpelanggaran')->where('idpel', $idpel)->value('tgl');
                $noreff = 'PN' . date('ymd', strtotime($tgl)) . $idpel;
                DB::table('tbltransaksi')->where('noreff', $noreff)->delete();
            }

            DB::table('tblpelanggaran')
                ->where('deviceid', self::DEVICE_PEL)
                ->whereIn('siswa_id', $izinSakitIds)
                ->delete();
        });

        $this->line("✓ {$salah} pelanggaran UPACARA siswa izin/sakit dihapus.");
    }

    private function tambahPelanggaranUpacara(array $izinSakitIds, string $tahunAjaran, bool $isDry): void
    {
        // Semua siswa aktif
        $semuaIds = Siswa::where(function ($q) {
            $q->where('status_aktif', true)->orWhereNull('status_aktif');
        })->pluck('id')->toArray();

        // Siswa yang sudah scan
        $sudahScanIds = DB::table('absen_event')
            ->where('event_id', self::EVENT_ID)
            ->whereNotNull('waktu_masuk')
            ->pluck('siswa_id')
            ->toArray();

        // Siswa yang sudah dapat pelanggaran
        $sudahPelIds = DB::table('tblpelanggaran')
            ->where('deviceid', self::DEVICE_PEL)
            ->pluck('siswa_id')
            ->toArray();

        // Yang harus dapat = tidak scan AND bukan izin/sakit AND belum dapat poin
        $harusPelIds = array_diff($semuaIds, $sudahScanIds, $izinSakitIds);
        $belumPelIds = array_diff($harusPelIds, $sudahPelIds);

        $this->line('Total siswa aktif          : ' . count($semuaIds));
        $this->line('Sudah scan UPACARA         : ' . count($sudahScanIds));
        $this->line('Izin/sakit (dilewati)      : ' . count($izinSakitIds));
        $this->line('Sudah dapat pelanggaran    : ' . count($sudahPelIds));
        $this->line('Yang akan ditambah         : ' . count($belumPelIds));

        if (empty($belumPelIds)) {
            $this->line('Semua siswa yang perlu sudah dapat pelanggaran. Skip.');
            return;
        }

        if ($isDry) {
            return;
        }

        $added = 0;
        $gagal = 0;

        // Muat semua siswa sekaligus untuk efisiensi
        $siswaMap = Siswa::with('kelas')
            ->whereIn('id', $belumPelIds)
            ->get()
            ->keyBy('id');

        foreach ($belumPelIds as $siswaId) {
            $siswa = $siswaMap->get($siswaId);
            if (! $siswa) {
                $gagal++;
                continue;
            }

            try {
                DB::transaction(function () use ($siswa, $tahunAjaran) {
                    // Gunakan Pelanggaran::create() agar PelanggaranObserver::created()
                    // otomatis membuat transaksi di tbltransaksi
                    Pelanggaran::create([
                        'siswa_id'     => $siswa->id,
                        'tgl'          => self::TGL . ' 09:00:00',
                        'tahun_ajaran' => $tahunAjaran,
                        'deviceid'     => self::DEVICE_PEL,
                        'noreg'        => $siswa->nis ?? '',
                        'nama'         => $siswa->nama_lengkap,
                        'kelas'        => $siswa->kelas?->nama_kelas ?? '',
                        'idpasal'      => self::PASAL_PEL,
                        'isi'          => self::ISI_PEL,
                        'poin'         => self::POIN_PEL,
                        'pelapor'      => 'Sistem',
                        'created_by'   => 1,
                    ]);
                });
                $added++;
            } catch (\Throwable $e) {
                $gagal++;
                Log::error('[FixUpacara17] Gagal tambah pelanggaran siswa #' . $siswaId . ': ' . $e->getMessage(), [
                    'siswa_id' => $siswaId,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        $this->line("✓ Pelanggaran UPACARA ditambah: {$added} berhasil, {$gagal} gagal.");
    }

    private function verifikasi(array $izinSakitIds): void
    {
        $alfaSisa   = DB::table('tblpelanggaran')->where('deviceid', self::DEVICE_ALFA)->count();
        $telatSisa  = DB::table('tblpelanggaran')->where('deviceid', self::DEVICE_TELAT)->count();
        $pelTotal   = DB::table('tblpelanggaran')->where('deviceid', self::DEVICE_PEL)->count();
        $penTotal   = DB::table('tblpenghargaan')->where('deviceid', self::DEVICE_PEN)->count();
        $scanTotal  = DB::table('absen_event')->where('event_id', self::EVENT_ID)->whereNotNull('waktu_masuk')->count();

        $izinKenaPel = empty($izinSakitIds) ? 0 : DB::table('tblpelanggaran')
            ->where('deviceid', self::DEVICE_PEL)
            ->whereIn('siswa_id', $izinSakitIds)
            ->count();

        $this->line('Pelanggaran alfa sisa         : ' . $alfaSisa  . ' (harus 0)');
        $this->line('Pelanggaran terlambat sisa    : ' . $telatSisa . ' (harus 0)');
        $this->line('Pelanggaran UPACARA total     : ' . $pelTotal);
        $this->line('Penghargaan UPACARA total     : ' . $penTotal  . ' | scan = ' . $scanTotal . ($penTotal === $scanTotal ? ' ✓' : ' ✗ KURANG'));
        $this->line('Siswa izin kena pelanggaran   : ' . $izinKenaPel . ' (harus 0)');
    }

    private function getTahunAjaran(): string
    {
        try {
            $active = AcademicYear::where('is_active', true)->first();
            if ($active && $active->year_start && $active->year_end) {
                return $active->year_start . '/' . $active->year_end;
            }
        } catch (\Throwable) {}

        $year  = (int) now()->format('Y');
        $month = (int) now()->format('n');
        return $month < 7 ? ($year - 1) . '/' . $year : $year . '/' . ($year + 1);
    }
}
