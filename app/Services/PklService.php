<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\JurnalHarianPkl;
use App\Models\LokasiPkl;
use App\Models\Penghargaan;
use App\Models\Pelanggaran;
use App\Models\PengajuanIzin;
use App\Models\PenugasanPkl;
use App\Models\Siswa;
use App\Models\SubPasal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * PklService — logika bisnis utama untuk fitur PKL.
 *
 * Tanggung jawab:
 *  - assignSiswa()      → tugaskan satu/banyak siswa ke lokasi PKL + buat PengajuanIzin otomatis
 *  - batalkanPenugasan() → batalkan penugasan + batalkan/hapus izin PKL
 *  - selesaikanPenugasan() → tandai penugasan sebagai selesai
 *  - siswaAktifPklPadaTanggal() → kumpulkan ID siswa yang sedang PKL pada hari tertentu
 *  - beriPoinHarian()   → jalankan auto poin PKL (hadir/terlambat/alfa) untuk satu tanggal
 */
class PklService
{
    public function __construct(
        private readonly AttendanceSyncService $attendanceSync
    ) {}

    // ══════════════════════════════════════════════════════════════════════
    // 1. ASSIGN SISWA KE LOKASI PKL
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Tugaskan beberapa siswa ke satu lokasi PKL.
     *
     * Untuk setiap siswa:
     *  1. Buat record PenugasanPkl
     *  2. Buat PengajuanIzin jenis='pkl' (langsung disetujui) untuk range tanggal
     *  3. Sync absensi hari-hari yang sudah lewat (jika tanggal_mulai ≤ hari ini)
     *
     * @param  array{
     *   siswa_ids: int[],
     *   lokasi_pkl_id: int,
     *   gtk_id: ?int,
     *   academic_year_id: ?int,
     *   tanggal_mulai: string,
     *   tanggal_selesai: string,
     *   catatan: ?string,
     * } $data
     * @param  int $adminId
     * @return array{ berhasil: PenugasanPkl[], gagal: array[] }
     */
    public function assignSiswa(array $data, int $adminId): array
    {
        $berhasil = [];
        $gagal    = [];

        foreach ($data['siswa_ids'] as $siswaId) {
            try {
                $penugasan = $this->assignSatuSiswa(
                    siswaId: (int) $siswaId,
                    data: $data,
                    adminId: $adminId,
                );
                $berhasil[] = $penugasan;
            } catch (\Throwable $e) {
                $siswa = Siswa::find($siswaId);
                Log::channel('sis')->error('[PklService] Gagal assign siswa', [
                    'siswa_id' => $siswaId,
                    'error'    => $e->getMessage(),
                ]);
                $gagal[] = [
                    'siswa_id'   => $siswaId,
                    'nama'       => $siswa?->nama_lengkap ?? "Siswa #{$siswaId}",
                    'error'      => $e->getMessage(),
                ];
            }
        }

        return compact('berhasil', 'gagal');
    }

    /**
     * Assign satu siswa — dijalankan dalam satu DB transaction.
     */
    private function assignSatuSiswa(int $siswaId, array $data, int $adminId): PenugasanPkl
    {
        return DB::transaction(function () use ($siswaId, $data, $adminId) {
            $siswa = Siswa::findOrFail($siswaId);

            // Cek apakah sudah ada penugasan aktif yang overlap tanggal
            $overlap = PenugasanPkl::where('siswa_id', $siswaId)
                ->where('status', 'aktif')
                ->where('tanggal_mulai', '<=', $data['tanggal_selesai'])
                ->where('tanggal_selesai', '>=', $data['tanggal_mulai'])
                ->first();

            if ($overlap) {
                throw new \RuntimeException(
                    "Siswa {$siswa->nama_lengkap} sudah memiliki penugasan PKL aktif " .
                    "({$overlap->tanggal_mulai->format('d/m/Y')} – {$overlap->tanggal_selesai->format('d/m/Y')})."
                );
            }

            // Buat / update PengajuanIzin jenis='pkl'
            $izin = PengajuanIzin::create([
                'siswa_id'          => $siswaId,
                'jenis'             => 'pkl',
                'alasan'            => 'PKL — ditugaskan ke ' . (LokasiPkl::find($data['lokasi_pkl_id'])?->nama_tempat ?? 'lokasi PKL'),
                'tanggal_mulai'     => $data['tanggal_mulai'],
                'tanggal_sampai'    => $data['tanggal_selesai'],
                'status'            => 'disetujui',
                'diverifikasi_oleh' => $adminId,
                'waktu_verifikasi'  => now(),
            ]);

            // Buat penugasan
            $penugasan = PenugasanPkl::create([
                'siswa_id'         => $siswaId,
                'lokasi_pkl_id'    => $data['lokasi_pkl_id'],
                'gtk_id'           => $data['gtk_id'] ?? null,
                'academic_year_id' => $data['academic_year_id'] ?? null,
                'tanggal_mulai'    => $data['tanggal_mulai'],
                'tanggal_selesai'  => $data['tanggal_selesai'],
                'status'           => 'aktif',
                'pengajuan_izin_id' => $izin->id,
                'catatan'          => $data['catatan'] ?? null,
                'created_by'       => $adminId,
            ]);

            // Sync absensi — dari hari ini mundur ke tanggal_mulai (untuk hari yang sudah lewat)
            $today = now()->toDateString();
            if ($data['tanggal_mulai'] <= $today) {
                $this->attendanceSync->syncApprovedIzinForDate(
                    Carbon::parse($today)
                );
            }

            Log::channel('sis')->info('[PklService] Siswa berhasil ditugaskan PKL', [
                'siswa_id'      => $siswaId,
                'lokasi_pkl_id' => $data['lokasi_pkl_id'],
                'penugasan_id'  => $penugasan->id,
                'izin_id'       => $izin->id,
                'mulai'         => $data['tanggal_mulai'],
                'selesai'       => $data['tanggal_selesai'],
            ]);

            return $penugasan;
        });
    }

    // ══════════════════════════════════════════════════════════════════════
    // 2. BATALKAN PENUGASAN
    // ══════════════════════════════════════════════════════════════════════

    public function batalkanPenugasan(PenugasanPkl $penugasan, int $adminId): void
    {
        DB::transaction(function () use ($penugasan, $adminId) {
            $penugasan->update(['status' => 'batal']);

            // Batalkan izin PKL yang terkait
            if ($penugasan->pengajuan_izin_id) {
                PengajuanIzin::where('id', $penugasan->pengajuan_izin_id)
                    ->update(['status' => 'ditolak']);
            }

            Log::channel('sis')->info('[PklService] Penugasan PKL dibatalkan', [
                'penugasan_id' => $penugasan->id,
                'siswa_id'     => $penugasan->siswa_id,
                'admin_id'     => $adminId,
            ]);
        });
    }

    // ══════════════════════════════════════════════════════════════════════
    // 3. SELESAIKAN PENUGASAN
    // ══════════════════════════════════════════════════════════════════════

    public function selesaikanPenugasan(PenugasanPkl $penugasan, int $adminId): void
    {
        DB::transaction(function () use ($penugasan, $adminId) {
            $penugasan->update([
                'status'          => 'selesai',
                'tanggal_selesai' => now()->toDateString(),
            ]);

            // Update tanggal_sampai izin PKL agar tidak terlalu jauh ke depan
            if ($penugasan->pengajuan_izin_id) {
                PengajuanIzin::where('id', $penugasan->pengajuan_izin_id)
                    ->update(['tanggal_sampai' => now()->toDateString()]);
            }

            Log::channel('sis')->info('[PklService] Penugasan PKL diselesaikan', [
                'penugasan_id' => $penugasan->id,
                'siswa_id'     => $penugasan->siswa_id,
                'admin_id'     => $adminId,
            ]);
        });
    }

    // ══════════════════════════════════════════════════════════════════════
    // 4. QUERY SISWA PKL AKTIF
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Kembalikan array siswa_id yang sedang PKL aktif pada tanggal tertentu.
     * Digunakan oleh AttendanceSyncService untuk skip poin alfa.
     */
    public function siswaAktifPklPadaTanggal(string $tanggal): array
    {
        return PenugasanPkl::where('status', 'aktif')
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal)
            ->pluck('siswa_id')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Kembalikan collection penugasan aktif pada tanggal tertentu,
     * diindeks oleh siswa_id untuk efisiensi lookup O(1).
     *
     * @return Collection<int, PenugasanPkl>  key = siswa_id
     */
    public function penugasanAktifPadaTanggal(string $tanggal): Collection
    {
        return PenugasanPkl::with('lokasiPkl')
            ->where('status', 'aktif')
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal)
            ->get()
            ->keyBy('siswa_id');
    }

    // ══════════════════════════════════════════════════════════════════════
    // 5. AUTO POIN PKL HARIAN
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Berikan poin otomatis PKL (hadir/terlambat/alfa) untuk satu tanggal.
     *
     * Dipanggil dari:
     *  - Artisan command: pkl:auto-poin-harian
     *  - Kernel schedule (harian setelah jam absen PKL)
     *
     * Logika per siswa:
     *  - Cek record AbsenSiswa hari ini untuk siswa PKL
     *  - hadir → poin penghargaan (AbsenPklService simpan status 'hadir' atau 'terlambat')
     *  - terlambat → poin pelanggaran terlambat (jika lokasi punya auto_poin_terlambat_pkl)
     *  - alfa (tidak scan sama sekali / jam_masuk null) → poin pelanggaran alfa pkl
     *
     * @return array{ tanggal: string, hadir: int, terlambat: int, alfa: int, skip: int, gagal: int }
     */
    public function beriPoinHarian(string $tanggal): array
    {
        $result = ['tanggal' => $tanggal, 'hadir' => 0, 'terlambat' => 0, 'alfa' => 0, 'skip' => 0, 'gagal' => 0];

        $tahunAjaran  = $this->getTahunAjaran();
        $penugasanMap = $this->penugasanAktifPadaTanggal($tanggal); // keyBy siswa_id

        if ($penugasanMap->isEmpty()) {
            return $result;
        }

        $siswaIds = $penugasanMap->keys()->toArray();

        // Ambil record absen siswa PKL pada tanggal ini
        // AbsenPklService menyimpan status_masuk = 'hadir' atau 'terlambat'
        $absenMap = \App\Models\AbsenSiswa::whereDate('tanggal', $tanggal)
            ->whereIn('siswa_id', $siswaIds)
            ->get()
            ->keyBy('siswa_id');

        // Ambil data siswa sekaligus
        $siswaMap = Siswa::whereIn('id', $siswaIds)
            ->with('kelas:id,nama_kelas')
            ->get()
            ->keyBy('id');

        foreach ($penugasanMap as $siswaId => $penugasan) {
            $lokasi = $penugasan->lokasiPkl;
            $siswa  = $siswaMap->get($siswaId);
            $absen  = $absenMap->get($siswaId);

            if (! $lokasi || ! $siswa) {
                $result['skip']++;
                continue;
            }

            // Tentukan status:
            // - Jika ada record absen dengan jam_masuk terisi → gunakan status_masuk
            // - Jika tidak ada record / jam_masuk null → alfa
            if ($absen && ! empty($absen->jam_masuk)) {
                $statusMasuk = $absen->status_masuk ?? 'hadir';
                // Normalisasi: 'pkl' lama dianggap hadir
                if ($statusMasuk === 'pkl') {
                    $statusMasuk = 'hadir';
                }
            } else {
                $statusMasuk = 'alfa';
            }

            try {
                $diberikan = match (true) {
                    $statusMasuk === 'hadir' && $lokasi->auto_poin_hadir_pkl && $lokasi->pasal_hadir_pkl_id
                        => $this->beriPenghargaanPkl($siswa, $lokasi, $tanggal, $tahunAjaran, 'hadir'),

                    $statusMasuk === 'terlambat' && $lokasi->auto_poin_terlambat_pkl && $lokasi->pasal_terlambat_pkl_id
                        => $this->beriPelanggaranPkl($siswa, $lokasi, $tanggal, $tahunAjaran, 'terlambat'),

                    $statusMasuk === 'alfa' && $lokasi->auto_poin_alfa_pkl && $lokasi->pasal_alfa_pkl_id
                        => $this->beriPelanggaranPkl($siswa, $lokasi, $tanggal, $tahunAjaran, 'alfa'),

                    default => null,
                };

                if ($diberikan === true) {
                    if ($statusMasuk === 'hadir') $result['hadir']++;
                    elseif ($statusMasuk === 'terlambat') $result['terlambat']++;
                    else $result['alfa']++;
                } elseif ($diberikan === null) {
                    $result['skip']++;
                }
            } catch (\Throwable $e) {
                $result['gagal']++;
                Log::channel('sis')->error('[PklService] Gagal beri poin PKL', [
                    'siswa_id' => $siswaId,
                    'tanggal'  => $tanggal,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        Log::channel('sis')->info('[PklService] Auto poin PKL selesai', $result);
        return $result;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers — poin
    // ──────────────────────────────────────────────────────────────────────

    private function beriPenghargaanPkl(Siswa $siswa, LokasiPkl $lokasi, string $tanggal, string $tahunAjaran, string $jenis): ?bool
    {
        $deviceId = "auto-pkl-hadir-{$lokasi->id}-{$tanggal}";

        if (Penghargaan::where('siswa_id', $siswa->id)->where('deviceid', $deviceId)->exists()) {
            return null; // idempotent
        }

        $pasal = SubPasal::where('idpasal', $lokasi->pasal_hadir_pkl_id)->first();
        if (! $pasal) return null;

        DB::transaction(function () use ($siswa, $lokasi, $tanggal, $tahunAjaran, $pasal, $deviceId) {
            Penghargaan::create([
                'siswa_id'     => $siswa->id,
                'tgl'          => now(),
                'tahun_ajaran' => $tahunAjaran,
                'deviceid'     => $deviceId,
                'noreg'        => $siswa->nis ?? '',
                'nama'         => $siswa->nama_lengkap,
                'kelas'        => $siswa->kelas?->nama_kelas ?? '',
                'idpasal'      => $pasal->idpasal,
                'isi'          => "Hadir PKL di {$lokasi->nama_tempat}",
                'poin'         => $pasal->poin_default,
                'pelapor'      => 'Sistem PKL',
                'ket'          => "Auto dari absensi PKL — {$lokasi->nama_tempat}",
                'acc'          => 'YA',
                'tglacc'       => now(),
                'nmacc'        => 'Sistem',
                'created_by'   => null,
            ]);
        });

        return true;
    }

    private function beriPelanggaranPkl(Siswa $siswa, LokasiPkl $lokasi, string $tanggal, string $tahunAjaran, string $jenis): ?bool
    {
        $pasalId  = $jenis === 'alfa' ? $lokasi->pasal_alfa_pkl_id : $lokasi->pasal_terlambat_pkl_id;
        $deviceId = "auto-pkl-{$jenis}-{$lokasi->id}-{$tanggal}";
        $isiMap   = [
            'alfa'      => "Tidak hadir PKL di {$lokasi->nama_tempat}",
            'terlambat' => "Terlambat hadir PKL di {$lokasi->nama_tempat}",
        ];

        if (Pelanggaran::where('siswa_id', $siswa->id)->where('deviceid', $deviceId)->exists()) {
            return null; // idempotent
        }

        $pasal = SubPasal::where('idpasal', $pasalId)->first();
        if (! $pasal) return null;

        DB::transaction(function () use ($siswa, $lokasi, $tanggal, $tahunAjaran, $pasal, $deviceId, $jenis, $isiMap) {
            Pelanggaran::create([
                'siswa_id'     => $siswa->id,
                'tgl'          => now(),
                'tahun_ajaran' => $tahunAjaran,
                'deviceid'     => $deviceId,
                'noreg'        => $siswa->nis ?? '',
                'nama'         => $siswa->nama_lengkap,
                'kelas'        => $siswa->kelas?->nama_kelas ?? '',
                'idpasal'      => $pasal->idpasal,
                'isi'          => $isiMap[$jenis],
                'poin'         => $pasal->poin_default,
                'pelapor'      => 'Sistem PKL',
                'created_by'   => null,
            ]);
        });

        return true;
    }

    private function getTahunAjaran(): string
    {
        try {
            $active = AcademicYear::where('is_active', true)->first();
            if ($active?->year_start && $active?->year_end) {
                return $active->year_start . '/' . $active->year_end;
            }
        } catch (\Throwable) {}

        $y = (int) now()->format('Y');
        $m = (int) now()->format('n');
        return $m < 7 ? ($y - 1) . '/' . $y : $y . '/' . ($y + 1);
    }

    // ══════════════════════════════════════════════════════════════════════
    // 6. JURNAL HARIAN
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Siswa menyimpan jurnal harian PKL.
     * Bersifat upsert — jika sudah ada jurnal di hari yang sama, diperbarui.
     *
     * @param  array{
     *   tanggal: string,
     *   jam_datang: ?string,
     *   jam_pulang: ?string,
     *   kegiatan: string,
     *   hasil: ?string,
     *   kendala: ?string,
     *   foto: ?string,
     * } $data
     */
    public function simpanJurnal(PenugasanPkl $penugasan, array $data): JurnalHarianPkl
    {
        return DB::transaction(function () use ($penugasan, $data) {
            $jurnal = JurnalHarianPkl::updateOrCreate(
                [
                    'siswa_id' => $penugasan->siswa_id,
                    'tanggal'  => $data['tanggal'],
                ],
                [
                    'penugasan_pkl_id'  => $penugasan->id,
                    'lokasi_pkl_id'     => $penugasan->lokasi_pkl_id,
                    'jam_datang'        => $data['jam_datang'] ?? null,
                    'jam_pulang'        => $data['jam_pulang'] ?? null,
                    'kegiatan'          => $data['kegiatan'],
                    'hasil'             => $data['hasil'] ?? null,
                    'kendala'           => $data['kendala'] ?? null,
                    'foto'              => $data['foto'] ?? null,
                    'status_verifikasi' => 'diajukan',
                    // Reset verifikasi saat diperbarui
                    'catatan_pembimbing' => null,
                    'diverifikasi_oleh'  => null,
                    'waktu_verifikasi'   => null,
                ]
            );

            return $jurnal;
        });
    }

    /**
     * Guru pembimbing memverifikasi jurnal harian.
     *
     * @param  'disetujui'|'revisi'  $status
     */
    public function verifikasiJurnal(JurnalHarianPkl $jurnal, string $status, ?string $catatan, int $adminId): JurnalHarianPkl
    {
        $jurnal->update([
            'status_verifikasi'  => $status,
            'catatan_pembimbing' => $catatan,
            'diverifikasi_oleh'  => $adminId,
            'waktu_verifikasi'   => now(),
        ]);

        return $jurnal->fresh();
    }

    /**
     * Hapus foto lama dari storage.
     */
    public function hapusFotoJurnal(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
