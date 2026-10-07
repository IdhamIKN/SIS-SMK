<?php

namespace App\Services;

use App\Models\AbsenSiswa;
use App\Models\AcademicYear;
use App\Models\Pelanggaran;
use App\Models\PengajuanIzin;
use App\Models\PenugasanPkl;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\SubPasal;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AttendanceSyncService — pola unified (1 record per siswa per hari).
 *
 * autoFillMissingMasuk  → pastikan semua siswa aktif punya record hari ini
 *                         yang belum hadir → status_masuk = alfa (atau izin/sakit)
 * syncApprovedIzinForDate → update status_masuk dari izin yang baru disetujui
 */
class AttendanceSyncService
{
    private const JENIS_IZIN_ABSEN_MASUK = [
        'izin_sakit',
        'izin_terlambat',
        'izin_lainnya',
        'pkl',
    ];

    public function autoFillMissingMasuk(?CarbonInterface $tanggal = null): array
    {
        $tanggalTarget = $this->dateString($tanggal);

        // ── Guard: hari auto-absen ────────────────────────────────────────
        if (! $this->isHariAutoAbsen($tanggalTarget)) {
            $namaHari = Carbon::parse($tanggalTarget)->locale('id')->isoFormat('dddd');
            Log::channel('sis')->info(
                "[AttendanceSync] Auto-fill dilewati — hari {$namaHari} ({$tanggalTarget}) bukan hari auto-absen."
            );
            return $this->emptyResult($tanggalTarget, ['skipped_hari' => true]);
        }

        // ── Guard: mode libur ─────────────────────────────────────────────
        $sekolah = Sekolah::first();
        if ($sekolah?->sedangLibur($tanggalTarget)) {
            Log::channel('sis')->info(
                "[AttendanceSync] Auto-fill dilewati — Mode Libur Panjang aktif ({$tanggalTarget})."
            );
            return $this->emptyResult($tanggalTarget, ['skipped_libur' => true]);
        }

        // ── Konfigurasi auto poin alfa ────────────────────────────────────
        $autoPoinAktif = $sekolah?->auto_point_alfa_enabled && $sekolah?->pasal_alfa_id;
        $pasalAlfa     = null;
        $tahunAjaran   = $this->getTahunAjaran();

        if ($autoPoinAktif) {
            $pasalAlfa = SubPasal::where('idpasal', $sekolah->pasal_alfa_id)
                ->orderByDesc('thnajaran')
                ->first();

            if (! $pasalAlfa) {
                Log::channel('sis')->warning(
                    "[AttendanceSync] Pasal alfa ID '{$sekolah->pasal_alfa_id}' tidak ditemukan."
                );
                $autoPoinAktif = false;
            }
        }

        $statusIzinBySiswa = $this->approvedAttendanceStatusesForDate($tanggalTarget);

        // ── Kumpulkan siswa PKL aktif hari ini (tidak dapat alfa otomatis) ─
        $siswaAktifPkl = PenugasanPkl::where('status', 'aktif')
            ->where('tanggal_mulai', '<=', $tanggalTarget)
            ->where('tanggal_selesai', '>=', $tanggalTarget)
            ->pluck('siswa_id')
            ->flip()   // jadikan key untuk O(1) lookup
            ->toArray();

        $result = [
            'tanggal'               => $tanggalTarget,
            'total_siswa'           => 0,
            'created'               => 0,
            'updated'               => 0,     // record sudah ada tapi diupdate status-nya
            'skipped_existing'      => 0,     // sudah punya jam_masuk
            'skipped_without_kelas' => 0,
            'skipped_pkl'           => 0,     // siswa sedang PKL → skip alfa sekolah
            'created_by_status'     => ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0, 'pkl' => 0],
            'poin_alfa_diberikan'   => 0,
        ];

        Siswa::query()
            ->select(['id', 'kelas_id', 'nis', 'nama_lengkap', 'noreg_legacy'])
            ->where('status_aktif', true)
            ->whereNotNull('kelas_id')
            ->with(['kelas:id,nama_kelas'])
            ->orderBy('id')
            ->chunkById(200, function (Collection $students) use (
                &$result, $tanggalTarget, $statusIzinBySiswa,
                $autoPoinAktif, $pasalAlfa, $tahunAjaran, $siswaAktifPkl
            ) {
                foreach ($students as $student) {
                    $result['total_siswa']++;

                    if (! $student->kelas_id) {
                        $result['skipped_without_kelas']++;
                        continue;
                    }

                    // ── Skip siswa yang sedang PKL aktif ─────────────────
                    // Absensi PKL dikelola oleh PklService & auto-poin PKL tersendiri.
                    // Siswa PKL mendapat status 'pkl' dari PengajuanIzin, bukan 'alfa'.
                    if (array_key_exists($student->id, $siswaAktifPkl)) {
                        $result['skipped_pkl']++;
                        continue;
                    }

                    // Cek record harian sudah ada dan sudah scan masuk
                    $record = AbsenSiswa::where('siswa_id', $student->id)
                        ->whereDate('tanggal', $tanggalTarget)
                        ->first();

                    if ($record && ! empty($record->jam_masuk)) {
                        // Sudah absen masuk → skip
                        $result['skipped_existing']++;
                        continue;
                    }

                    $status      = $statusIzinBySiswa->get($student->id, 'alfa');
                    $waktuAbsen  = $this->waktuAbsen($tanggalTarget);

                    try {
                        DB::transaction(function () use (
                            $student, $tanggalTarget, $status, $waktuAbsen,
                            $autoPoinAktif, $pasalAlfa, $tahunAjaran,
                            $record, &$result
                        ) {
                            if ($record) {
                                // Record ada tapi belum masuk → update status_masuk
                                $record->update([
                                    'status_masuk' => $status,
                                    'status'       => $status,   // legacy
                                    'waktu_absen'  => $waktuAbsen, // legacy
                                    'catatan'      => $status === 'alfa' ? 'Absen Sistem.' : 'Auto.',
                                ]);
                                $result['updated']++;
                            } else {
                                // Buat record baru
                                AbsenSiswa::create([
                                    'siswa_id'     => $student->id,
                                    'kelas_id'     => $student->kelas_id,
                                    'tanggal'      => $tanggalTarget,
                                    'status_masuk' => $status,
                                    'status'       => $status,   // legacy
                                    'jenis'        => 'masuk',   // legacy
                                    'waktu_absen'  => $waktuAbsen, // legacy
                                    'catatan'      => $status === 'alfa' ? 'Absen Sistem.' : 'Auto.',
                                ]);
                                $result['created']++;
                            }

                            $result['created_by_status'][$status] = ($result['created_by_status'][$status] ?? 0) + 1;

                            // ── Poin alfa (hanya untuk non-PKL) ──────────
                            if ($status === 'alfa' && $autoPoinAktif && $pasalAlfa) {
                                $deviceId     = 'auto-alfa-' . $tanggalTarget;
                                $sudahAdaPoin = Pelanggaran::where('deviceid', $deviceId)
                                    ->where('siswa_id', $student->id)
                                    ->exists();

                                if (! $sudahAdaPoin) {
                                    Pelanggaran::create([
                                        'siswa_id'     => $student->id,
                                        'tgl'          => $tanggalTarget,
                                        'tahun_ajaran' => $tahunAjaran,
                                        'deviceid'     => $deviceId,
                                        'noreg'        => $student->noreg_legacy ?? $student->nis ?? '',
                                        'nama'         => $student->nama_lengkap,
                                        'kelas'        => $student->kelas?->nama_kelas ?? '',
                                        'idpasal'      => $pasalAlfa->idpasal,
                                        'isi'          => 'Alfa — tidak hadir tanpa keterangan',
                                        'poin'         => $pasalAlfa->poin_default ?? $pasalAlfa->skormin ?? 0,
                                        'pelapor'      => 'Sistem',
                                        'created_by'   => null,
                                    ]);

                                    $result['poin_alfa_diberikan']++;
                                }
                            }
                        });
                    } catch (\Throwable $e) {
                        Log::channel('sis')->error(
                            "[AttendanceSync] GAGAL proses siswa #{$student->id}: " . $e->getMessage(),
                            ['siswa_id' => $student->id, 'exception' => $e->getMessage()]
                        );
                    }
                }
            });

        Log::channel('sis')->info('[AttendanceSync] Auto-fill absen masuk selesai', $result);

        return $result;
    }

    /**
     * Sync status dari izin yang disetujui.
     * Update status_masuk pada record harian yang status-nya masih alfa/belum hadir.
     */
    public function syncApprovedIzinForDate(?CarbonInterface $tanggal = null): array
    {
        $tanggalTarget = $this->dateString($tanggal);
        $waktuAbsen    = $this->waktuAbsen($tanggalTarget);
        $izinBySiswa   = $this->approvedIzinForDate($tanggalTarget)->groupBy('siswa_id');

        $result = [
            'tanggal'               => $tanggalTarget,
            'izin_count'            => $izinBySiswa->flatten(1)->count(),
            'siswa_terpengaruh'     => $izinBySiswa->count(),
            'created'               => 0,
            'updated'               => 0,
            'skipped_hadir'         => 0,
            'skipped_manual'        => 0,
            'unchanged'             => 0,
            'skipped_without_kelas' => 0,
        ];

        foreach ($izinBySiswa as $siswaId => $izinItems) {
            $status = $this->attendanceStatusFromIzinItems($izinItems);

            $absen = AbsenSiswa::where('siswa_id', $siswaId)
                ->whereDate('tanggal', $tanggalTarget)
                ->first();

            if (! $absen) {
                $student = Siswa::select(['id', 'kelas_id'])->find($siswaId);
                if (! $student?->kelas_id) {
                    $result['skipped_without_kelas']++;
                    continue;
                }

                AbsenSiswa::create([
                    'siswa_id'     => $student->id,
                    'kelas_id'     => $student->kelas_id,
                    'tanggal'      => $tanggalTarget,
                    'status_masuk' => $status,
                    'status'       => $status,
                    'jenis'        => 'masuk',
                    'waktu_absen'  => $waktuAbsen,
                    'catatan'      => 'Dibuat otomatis berdasarkan pengajuan izin yang sudah disetujui.',
                ]);
                $result['created']++;
                continue;
            }

            // Jika sudah hadir/terlambat (scan sendiri) → jangan override
            if (in_array($absen->status_masuk ?? $absen->status, ['hadir', 'terlambat'])) {
                $result['skipped_hadir']++;
                continue;
            }

            // Jika sudah diverifikasi manual → skip
            if ($absen->diverifikasi_oleh) {
                $result['skipped_manual']++;
                continue;
            }

            $currentStatus = $absen->status_masuk ?? $absen->status;
            if ($currentStatus === $status) {
                $result['unchanged']++;
                continue;
            }

            $absen->update([
                'status_masuk' => $status,
                'status'       => $status,
                'catatan'      => $absen->catatan ?: 'Status otomatis diperbarui berdasarkan izin yang disetujui.',
            ]);
            $result['updated']++;
        }

        Log::channel('sis')->info('[AttendanceSync] Sinkron izin disetujui selesai', $result);
        return $result;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    private function approvedAttendanceStatusesForDate(string $tanggal): Collection
    {
        return $this->approvedIzinForDate($tanggal)
            ->groupBy('siswa_id')
            ->map(fn (Collection $items) => $this->attendanceStatusFromIzinItems($items));
    }

    private function approvedIzinForDate(string $tanggal): Collection
    {
        return PengajuanIzin::query()
            ->where('status', 'disetujui')
            ->whereIn('jenis', self::JENIS_IZIN_ABSEN_MASUK)
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_sampai', '>=', $tanggal)
            ->get(['id', 'siswa_id', 'jenis', 'tanggal_mulai', 'tanggal_sampai']);
    }

    private function attendanceStatusFromIzinItems(Collection $items): string
    {
        // PKL menghasilkan status 'pkl'
        if ($items->contains(fn (PengajuanIzin $izin) => $izin->jenis === 'pkl')) {
            return 'pkl';
        }
        return $items->contains(fn (PengajuanIzin $izin) => $izin->jenis === 'izin_sakit')
            ? 'sakit'
            : 'izin';
    }

    private function dateString(?CarbonInterface $tanggal): string
    {
        return ($tanggal ? Carbon::instance($tanggal) : now())
            ->setTimezone(config('app.timezone', 'Asia/Jakarta'))
            ->toDateString();
    }

    private function waktuAbsen(string $tanggal): Carbon
    {
        $now = now(config('app.timezone', 'Asia/Jakarta'));
        return Carbon::parse($tanggal . ' ' . $now->format('H:i:s'), config('app.timezone', 'Asia/Jakarta'));
    }

    private function isHariAutoAbsen(string $tanggal): bool
    {
        $carbonToIndo = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];

        $namaHari = $carbonToIndo[Carbon::parse($tanggal)->englishDayOfWeek] ?? '';

        try {
            $sekolah   = Sekolah::first();
            $raw       = $sekolah?->hari_efektif;
            $hariAktif = $raw
                ? (is_array($raw) ? $raw : (json_decode($raw, true) ?: []))
                : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        } catch (\Throwable) {
            $hariAktif = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        }

        return in_array($namaHari, $hariAktif, true);
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

        return $month < 7
            ? ($year - 1) . '/' . $year
            : $year . '/' . ($year + 1);
    }

    private function emptyResult(string $tanggal, array $extra = []): array
    {
        return array_merge([
            'tanggal'               => $tanggal,
            'total_siswa'           => 0,
            'created'               => 0,
            'updated'               => 0,
            'skipped_existing'      => 0,
            'skipped_without_kelas' => 0,
            'skipped_pkl'           => 0,
            'created_by_status'     => ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0, 'pkl' => 0],
            'poin_alfa_diberikan'   => 0,
        ], $extra);
    }
}
