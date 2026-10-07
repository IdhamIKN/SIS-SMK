<?php

namespace App\Services;

use App\Models\AbsenActivityLog;
use App\Models\AbsenSiswa;
use App\Models\Pelanggaran;
use App\Models\PengajuanIzin;
use App\Models\Sekolah;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AbsenAdminService
 *
 * Semua operasi admin terhadap data absensi menggunakan rule bisnis yang SAMA
 * dengan mekanisme absensi normal (AbsenService). Tidak ada logika baru.
 *
 * Fitur:
 *  1. tambahManual()  — buat record absensi dengan rule lengkap (status, terlambat, dst.)
 *  2. editManual()    — edit record + recalculate semua rule
 *  3. buatIzinAdmin() — buat PengajuanIzin langsung disetujui + sync absensi
 */
class AbsenAdminService
{
    public function __construct(
        protected AbsenService           $absenService,
        protected AttendanceSyncService  $attendanceSync,
    ) {}

    // ──────────────────────────────────────────────────────────────────────
    // 1. TAMBAH ABSENSI MANUAL
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Buat / update record absensi siswa untuk tanggal tertentu.
     * Menerapkan seluruh rule: status masuk, terlambat, pulang cepat, dll.
     *
     * @param  array{
     *   siswa_id: int,
     *   tanggal: string,
     *   jam_masuk: ?string,     // format HH:MM atau null
     *   jam_pulang: ?string,    // format HH:MM atau null
     *   status_masuk: ?string,  // override status; null = hitung otomatis
     *   catatan: ?string,
     * } $data
     */
    public function tambahManual(array $data, int $adminId): AbsenSiswa
    {
        $siswa   = Siswa::findOrFail($data['siswa_id']);
        $tanggal = $data['tanggal'];

        return DB::transaction(function () use ($data, $siswa, $tanggal, $adminId) {
            // Ambil/buat record harian
            $absen = AbsenSiswa::firstOrCreate(
                ['siswa_id' => $siswa->id, 'tanggal' => $tanggal],
                ['kelas_id' => $siswa->kelas_id]
            );

            $dataLama = $absen->toArray();

            // Hitung status masuk menggunakan rule yang sama dengan AbsenService
            $statusMasuk = $this->hitungStatusMasuk($data);

            $updateData = [
                'kelas_id'          => $siswa->kelas_id,
                'catatan'           => $data['catatan'] ?? null,
                'diverifikasi_oleh' => $adminId,
                // legacy columns
                'status'            => $statusMasuk,
                'jenis'             => 'masuk',
            ];

            // Update kolom masuk jika jam_masuk diberikan
            if (! empty($data['jam_masuk'])) {
                $jamMasuk = Carbon::parse($tanggal . ' ' . $data['jam_masuk']);
                $updateData['jam_masuk']    = $data['jam_masuk'] . ':00';
                $updateData['status_masuk'] = $statusMasuk;
                $updateData['waktu_absen']  = $jamMasuk;   // legacy
                $updateData['status']       = $statusMasuk; // legacy
            } elseif (array_key_exists('jam_masuk', $data) && $data['jam_masuk'] === null) {
                // Eksplisit hapus jam masuk (untuk set alpha)
                $updateData['jam_masuk']    = null;
                $updateData['status_masuk'] = $data['status_masuk'] ?? 'alfa';
                $updateData['status']       = $data['status_masuk'] ?? 'alfa';
            }

            // Update kolom pulang jika jam_pulang diberikan
            if (! empty($data['jam_pulang'])) {
                $updateData['jam_pulang']    = $data['jam_pulang'] . ':00';
                $updateData['status_pulang'] = 'hadir';
            } elseif (array_key_exists('jam_pulang', $data) && $data['jam_pulang'] === null) {
                $updateData['jam_pulang']    = null;
                $updateData['status_pulang'] = null;
            }

            $absen->update($updateData);
            $absen->refresh();

            // Audit log
            $this->catatLog(
                aksi: 'tambah_manual',
                siswaId: $siswa->id,
                adminId: $adminId,
                absenId: $absen->id,
                dataLama: $dataLama,
                dataBaru: $absen->toArray(),
                catatan: $data['catatan'] ?? null,
            );

            Log::channel('sis')->info('[AbsenAdmin] Tambah manual', [
                'absen_id'  => $absen->id,
                'siswa_id'  => $siswa->id,
                'tanggal'   => $tanggal,
                'admin_id'  => $adminId,
            ]);

            return $absen;
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // 2. EDIT ABSENSI MANUAL (Recalculate Rules)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Edit record absensi yang sudah ada, recalculate semua rule.
     *
     * @param  array{
     *   jam_masuk: ?string,
     *   jam_pulang: ?string,
     *   status_masuk: ?string,
     *   catatan: ?string,
     *   izin_jenis: ?string,           // hanya jika status izin/sakit
     *   izin_tanggal_sampai: ?string,  // hanya jika status izin/sakit
     *   izin_alasan: ?string,          // hanya jika status izin/sakit
     *   izin_bukti: ?string,           // path file yang sudah disimpan
     * } $data
     */
    public function editManual(AbsenSiswa $absen, array $data, int $adminId): AbsenSiswa
    {
        return DB::transaction(function () use ($absen, $data, $adminId) {
            $dataLama  = $absen->toArray();
            $tanggal   = $absen->tanggal->format('Y-m-d');

            // Recalculate status menggunakan rule yang sama
            $statusMasuk = $this->hitungStatusMasuk(array_merge($data, ['tanggal' => $tanggal]));

            $updateData = [
                'catatan'           => $data['catatan'] ?? $absen->catatan,
                'diverifikasi_oleh' => $adminId,
            ];

            // Jam Masuk
            if (array_key_exists('jam_masuk', $data)) {
                if (! empty($data['jam_masuk'])) {
                    $jamMasuk = Carbon::parse($tanggal . ' ' . $data['jam_masuk']);
                    $updateData['jam_masuk']    = $data['jam_masuk'] . ':00';
                    $updateData['status_masuk'] = $statusMasuk;
                    $updateData['waktu_absen']  = $jamMasuk;
                    $updateData['status']       = $statusMasuk;
                } else {
                    // Hapus jam masuk → alfa
                    $updateData['jam_masuk']    = null;
                    $updateData['status_masuk'] = $data['status_masuk'] ?? 'alfa';
                    $updateData['status']       = $data['status_masuk'] ?? 'alfa';
                    $updateData['waktu_absen']  = null;
                }
            }

            // Jam Pulang
            if (array_key_exists('jam_pulang', $data)) {
                if (! empty($data['jam_pulang'])) {
                    $updateData['jam_pulang']    = $data['jam_pulang'] . ':00';
                    $updateData['status_pulang'] = 'hadir';
                } else {
                    $updateData['jam_pulang']    = null;
                    $updateData['status_pulang'] = null;
                }
            }

            $absen->update($updateData);
            $absen->refresh();

            // ── Jika status diubah ke izin/sakit/pkl, buat atau update PengajuanIzin ──
            if (in_array($statusMasuk, ['izin', 'sakit', 'pkl'], true) && ! empty($data['izin_jenis'])) {
                $tanggalMulai  = $data['izin_tanggal_mulai'] ?? $tanggal;
                $tanggalSampai = $data['izin_tanggal_sampai'] ?? $tanggal;

                // Cari izin aktif yang sudah ada untuk siswa+tanggal ini (tidak ditolak)
                $izinExisting = PengajuanIzin::where('siswa_id', $absen->siswa_id)
                    ->where('status', '!=', 'ditolak')
                    ->where(function ($q) use ($tanggal) {
                        $q->whereDate('tanggal_mulai', '<=', $tanggal)
                          ->whereDate('tanggal_sampai', '>=', $tanggal);
                    })
                    ->first();                if ($izinExisting) {
                    // Update izin yang sudah ada — perbarui jenis & alasan jika berbeda
                    $izinExisting->update([
                        'jenis'             => $data['izin_jenis'],
                        'alasan'            => $data['izin_alasan'] ?? $izinExisting->alasan,
                        'bukti'             => $data['izin_bukti'] ?? $izinExisting->bukti,
                        'tanggal_mulai'     => $tanggalMulai,
                        'tanggal_sampai'    => $tanggalSampai,
                        'status'            => 'disetujui',
                        'diverifikasi_oleh' => $adminId,
                        'waktu_verifikasi'  => now(),
                    ]);
                } else {
                    // Buat PengajuanIzin baru langsung disetujui
                    PengajuanIzin::create([
                        'siswa_id'          => $absen->siswa_id,
                        'jenis'             => $data['izin_jenis'],
                        'alasan'            => $data['izin_alasan'] ?? '-',
                        'tanggal_mulai'     => $tanggalMulai,
                        'tanggal_sampai'    => $tanggalSampai,
                        'bukti'             => $data['izin_bukti'] ?? null,
                        'status'            => 'disetujui',
                        'diverifikasi_oleh' => $adminId,
                        'waktu_verifikasi'  => now(),
                    ]);
                }

                // Jika multi-hari, sync absensi untuk hari-hari berikutnya
                if ($tanggalSampai > $tanggal) {
                    foreach ($this->generateTanggalRange($tanggal, $tanggalSampai) as $tgl) {
                        if ($tgl === $tanggal) {
                            continue; // hari ini sudah diupdate di atas
                        }
                        $this->attendanceSync->syncApprovedIzinForDate(
                            Carbon::parse($tgl)
                        );
                    }
                }
            }

            // Audit log
            $this->catatLog(
                aksi: 'edit_manual',
                siswaId: $absen->siswa_id,
                adminId: $adminId,
                absenId: $absen->id,
                dataLama: $dataLama,
                dataBaru: $absen->toArray(),
                catatan: $data['catatan'] ?? null,
            );

            Log::channel('sis')->info('[AbsenAdmin] Edit manual', [
                'absen_id'     => $absen->id,
                'siswa_id'     => $absen->siswa_id,
                'admin_id'     => $adminId,
                'status_masuk' => $statusMasuk,
                'izin_jenis'   => $data['izin_jenis'] ?? null,
            ]);

            return $absen;
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // 3. BUAT IZIN OLEH ADMIN (Langsung Disetujui)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Admin membuat izin untuk siswa langsung dengan status disetujui.
     * Setelah dibuat, sync absensi dijalankan menggunakan AttendanceSyncService
     * yang sama dengan alur verifikasi normal.
     *
     * @param  array{
     *   siswa_id: int,
     *   jenis: string,
     *   tanggal_mulai: string,
     *   tanggal_sampai: string,
     *   alasan: string,
     * } $data
     */
    public function buatIzinAdmin(array $data, int $adminId): PengajuanIzin
    {
        return DB::transaction(function () use ($data, $adminId) {
            $izin = PengajuanIzin::create([
                'siswa_id'          => $data['siswa_id'],
                'jenis'             => $data['jenis'],
                'alasan'            => $data['alasan'],
                'tanggal_mulai'     => $data['tanggal_mulai'],
                'tanggal_sampai'    => $data['tanggal_sampai'],
                'bukti'             => $data['bukti'] ?? null,
                'status'            => 'disetujui',
                'diverifikasi_oleh' => $adminId,
                'waktu_verifikasi'  => now(),
            ]);

            // Sync absensi menggunakan rule yang sama dengan alur verifikasi normal
            // AttendanceSyncService::syncApprovedIzinForDate() akan:
            //   - Cari/buat record absensi harian
            //   - Set status_masuk = sakit/izin sesuai jenis
            //   - Tidak override jika siswa sudah hadir
            foreach ($this->generateTanggalRange($data['tanggal_mulai'], $data['tanggal_sampai']) as $tanggal) {
                $this->attendanceSync->syncApprovedIzinForDate(
                    Carbon::parse($tanggal)
                );
            }

            // Audit log
            $this->catatLog(
                aksi: 'buat_izin',
                siswaId: $data['siswa_id'],
                adminId: $adminId,
                izinId: $izin->id,
                dataLama: null,
                dataBaru: $izin->toArray(),
                catatan: $data['alasan'],
            );

            Log::channel('sis')->info('[AbsenAdmin] Buat izin admin', [
                'izin_id'  => $izin->id,
                'siswa_id' => $data['siswa_id'],
                'jenis'    => $data['jenis'],
                'admin_id' => $adminId,
            ]);

            return $izin;
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Hitung status masuk menggunakan rule yang sama dengan AbsenService.
     * Jika status_masuk diberikan secara eksplisit, gunakan itu.
     * Jika jam_masuk kosong → alfa.
     * Jika jam_masuk ada → cek batas_tepat_waktu dari konfigurasi sekolah.
     */
    private function hitungStatusMasuk(array $data): string
    {
        // Override eksplisit (termasuk 'pkl')
        if (! empty($data['status_masuk'])) {
            return $data['status_masuk'];
        }

        if (empty($data['jam_masuk'])) {
            return 'alfa';
        }

        $tanggal = $data['tanggal'] ?? now()->toDateString();
        $waktu   = Carbon::parse($tanggal . ' ' . $data['jam_masuk']);

        return $this->absenService->tentukanStatusMasuk($waktu);
    }

    /**
     * Generate array tanggal antara dua tanggal.
     *
     * @return string[]
     */
    private function generateTanggalRange(string $dari, string $sampai): array
    {
        $result  = [];
        $current = Carbon::parse($dari);
        $end     = Carbon::parse($sampai);

        while ($current->lte($end)) {
            $result[] = $current->toDateString();
            $current->addDay();
        }

        return $result;
    }

    /**
     * Catat audit log ke tabel absen_activity_log.
     */
    private function catatLog(
        string  $aksi,
        int     $siswaId,
        int     $adminId,
        ?int    $absenId   = null,
        ?int    $izinId    = null,
        ?array  $dataLama  = null,
        ?array  $dataBaru  = null,
        ?string $catatan   = null,
    ): void {
        try {
            AbsenActivityLog::create([
                'aksi'           => $aksi,
                'absen_siswa_id' => $absenId,
                'izin_id'        => $izinId,
                'siswa_id'       => $siswaId,
                'dilakukan_oleh' => $adminId,
                'data_lama'      => $dataLama,
                'data_baru'      => $dataBaru,
                'catatan'        => $catatan,
            ]);
        } catch (\Throwable $e) {
            // Log gagal tidak boleh menghentikan proses utama
            Log::channel('sis')->error('[AbsenAdmin] Gagal catat audit log: ' . $e->getMessage());
        }
    }
}
