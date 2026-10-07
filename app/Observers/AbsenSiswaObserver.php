<?php

namespace App\Observers;

use App\Models\AbsenSiswa;
use App\Models\AcademicYear;
use App\Models\Pelanggaran;
use App\Models\Penghargaan;
use App\Models\Sekolah;
use App\Models\SubPasal;
use App\Models\TransaksiPoin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * AbsenSiswaObserver
 *
 * Menjaga konsistensi status absen ↔ poin tatib secara otomatis
 * saat admin mengubah status kehadiran siswa.
 *
 * Poin yang dikelola (deviceid = 'auto-{jenis}-{tanggal}'):
 *   auto-alfa-YYYY-MM-DD       → pelanggaran
 *   auto-terlambat-YYYY-MM-DD  → pelanggaran
 *   auto-hadir-YYYY-MM-DD      → penghargaan
 *
 * Poin manual (deviceid lain) TIDAK disentuh.
 *
 * Tabel keputusan saat status berubah:
 * ┌─────────────────┬──────────────────────────────────────────────────────┐
 * │ Perubahan       │ Aksi                                                 │
 * ├─────────────────┼──────────────────────────────────────────────────────┤
 * │ alfa → hadir    │ soft-delete poin alfa; buat/restore poin hadir       │
 * │ alfa → terlambat│ soft-delete poin alfa; buat/restore poin terlambat   │
 * │ alfa → izin/skt │ soft-delete poin alfa; tidak buat poin baru          │
 * │ terlambat → alfa│ soft-delete poin terlambat; restore poin alfa        │
 * │ terlambat → hadir│ soft-delete poin terlambat; buat/restore poin hadir │
 * │ terlambat → izin│ soft-delete poin terlambat                           │
 * │ hadir → alfa    │ soft-delete poin hadir; restore poin alfa            │
 * │ hadir → terlambat│ soft-delete poin hadir; buat/restore poin terlambat │
 * │ hadir → izin    │ soft-delete poin hadir                               │
 * │ izin/skt → alfa │ restore poin alfa (jika ada di trash)                │
 * │ izin/skt → hadir│ buat/restore poin hadir                             │
 * │ izin/skt → terlmb│ buat/restore poin terlambat                        │
 * └─────────────────┴──────────────────────────────────────────────────────┘
 */
class AbsenSiswaObserver
{
    public function updated(AbsenSiswa $absen): void
    {
        if (! $absen->wasChanged(['status_masuk', 'status'])) {
            return;
        }

        $statusBaru = $absen->status_masuk ?? $absen->status;
        $statusLama = $absen->getOriginal('status_masuk') ?? $absen->getOriginal('status');

        if ($statusLama === $statusBaru) return;

        $tanggal = $absen->tanggal instanceof Carbon
            ? $absen->tanggal->toDateString()
            : (string) $absen->tanggal;

        $siswaId     = $absen->siswa_id;
        $tahunAjaran = $this->getTahunAjaran();
        $sekolah     = Sekolah::aktif();

        // ── 1. Soft-delete poin lama berdasarkan status sebelumnya ────────────
        $this->softDeletePoin($statusLama, $siswaId, $tanggal);

        // ── 2. Buat atau restore poin baru berdasarkan status baru ────────────
        $this->buatAtauRestorePoin($statusBaru, $siswaId, $tanggal, $tahunAjaran, $absen, $sekolah);
    }

    // ── Soft-delete poin berdasarkan status ───────────────────────────────────

    private function softDeletePoin(string $status, int $siswaId, string $tanggal): void
    {
        try {
            match($status) {
                'alfa'      => $this->softDeletePoinPelanggaran($siswaId, 'auto-alfa-' . $tanggal),
                'terlambat' => $this->softDeletePoinPelanggaran($siswaId, 'auto-terlambat-' . $tanggal),
                'hadir'     => $this->softDeletePoinPenghargaan($siswaId, 'auto-hadir-' . $tanggal),
                default     => null,
            };
        } catch (\Throwable $e) {
            Log::channel('sis')->error('[AbsenSiswaObserver] Gagal soft-delete poin lama', [
                'siswa_id' => $siswaId, 'status' => $status, 'tanggal' => $tanggal,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    /**
     * Soft-delete pelanggaran (auto) + transaksi terkait secara langsung.
     * Memanggil $record->delete() agar PelanggaranObserver::deleted() terpanggil,
     * lalu juga soft-delete transaksi langsung sebagai safety net.
     */
    private function softDeletePoinPelanggaran(int $siswaId, string $deviceId): void
    {
        $records = Pelanggaran::where('siswa_id', $siswaId)->where('deviceid', $deviceId)->get();

        foreach ($records as $record) {
            // Ini trigger PelanggaranObserver::deleted() → soft-delete transaksi via observer
            $record->delete();
        }

        // Safety net: soft-delete transaksi yang noreff-nya cocok dengan pelanggaran ini
        // (menangani data historis sebelum observer dipasang, atau observer gagal)
        Pelanggaran::onlyTrashed()
            ->where('siswa_id', $siswaId)
            ->where('deviceid', $deviceId)
            ->each(function (Pelanggaran $p) {
                $noreff = 'PN'
                    . (\Carbon\Carbon::parse($p->tgl)->format('ymd'))
                    . $p->idpel;
                TransaksiPoin::where('noreff', $noreff)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => now()]);
            });
    }

    /**
     * Soft-delete penghargaan (auto) + transaksi terkait secara langsung.
     */
    private function softDeletePoinPenghargaan(int $siswaId, string $deviceId): void
    {
        $records = Penghargaan::where('siswa_id', $siswaId)->where('deviceid', $deviceId)->get();

        foreach ($records as $record) {
            // Ini trigger PenghargaanObserver::deleted() → soft-delete transaksi via observer
            $record->delete();
        }

        // Safety net untuk data historis
        Penghargaan::onlyTrashed()
            ->where('siswa_id', $siswaId)
            ->where('deviceid', $deviceId)
            ->each(function (Penghargaan $p) {
                $noreff = 'RW'
                    . (\Carbon\Carbon::parse($p->tgl)->format('ymd'))
                    . $p->idpen;
                TransaksiPoin::where('noreff', $noreff)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => now()]);
            });
    }

    /** @deprecated Gunakan softDeletePoinPelanggaran() */
    private function softDeleteEach(\Illuminate\Support\Collection $records): void
    {
        foreach ($records as $record) {
            $record->delete();
        }
    }

    /** @deprecated Gunakan softDeletePoinPenghargaan() */
    private function softDeleteEachPenghargaan(\Illuminate\Support\Collection $records): void
    {
        foreach ($records as $record) {
            $record->delete();
        }
    }

    // ── Buat atau restore poin berdasarkan status baru ────────────────────────

    private function buatAtauRestorePoin(
        string $statusBaru, int $siswaId, string $tanggal,
        string $tahunAjaran, AbsenSiswa $absen, ?Sekolah $sekolah
    ): void {
        try {
            match($statusBaru) {
                'alfa'      => $this->handleAlfa($siswaId, $tanggal, $tahunAjaran, $absen, $sekolah),
                'terlambat' => $this->handleTerlambat($siswaId, $tanggal, $tahunAjaran, $absen, $sekolah),
                'hadir'     => $this->handleHadir($siswaId, $tanggal, $tahunAjaran, $absen, $sekolah),
                default     => null, // izin, sakit → tidak buat poin
            };
        } catch (\Throwable $e) {
            Log::channel('sis')->error('[AbsenSiswaObserver] Gagal buat/restore poin baru', [
                'siswa_id'    => $siswaId, 'status_baru' => $statusBaru,
                'tanggal'     => $tanggal, 'error'       => $e->getMessage(),
            ]);
        }
    }

    private function handleAlfa(int $siswaId, string $tanggal, string $tahunAjaran, AbsenSiswa $absen, ?Sekolah $sekolah): void
    {
        $deviceId = 'auto-alfa-' . $tanggal;
        $siswa    = $absen->siswa ?? \App\Models\Siswa::find($siswaId);

        // Ambil pasal & poin terkini dari school config
        $pasal    = ($sekolah?->auto_point_alfa_enabled && $sekolah?->pasal_alfa_id)
            ? $this->getPasal($sekolah->pasal_alfa_id)
            : null;
        $poinBaru = $pasal ? ($pasal->poin_default ?? $pasal->skormin ?? 0) : null;

        // Coba restore dari trash, sekaligus update pasal & poin ke config terkini
        $trashedRecords = Pelanggaran::onlyTrashed()
            ->where('siswa_id', $siswaId)->where('deviceid', $deviceId)->get();

        $restored = 0;
        foreach ($trashedRecords as $record) {
            $record->restore();

            // Setelah restore, update pasal & poin sesuai school config saat ini
            if ($pasal && $poinBaru !== null) {
                $record->update([
                    'idpasal'      => $pasal->idpasal,
                    'poin'         => $poinBaru,
                    'tahun_ajaran' => $tahunAjaran,
                ]);
                // PelanggaranObserver::updated() akan otomatis sync transaksi
            }

            $restored++;
        }

        // Jika tidak ada di trash dan fitur aktif → buat baru
        if ($restored === 0 && $pasal && $siswa) {
            Pelanggaran::create([
                'siswa_id'     => $siswaId,
                'tgl'          => Carbon::parse($tanggal),
                'tahun_ajaran' => $tahunAjaran,
                'deviceid'     => $deviceId,
                'noreg'        => $siswa->nis ?? '',
                'nama'         => $siswa->nama_lengkap,
                'kelas'        => $siswa->kelas?->nama_kelas ?? '',
                'idpasal'      => $pasal->idpasal,
                'isi'          => 'Alfa — tidak hadir tanpa keterangan',
                'poin'         => $poinBaru,
                'pelapor'      => 'Sistem',
                'created_by'   => null,
            ]);
        }

        if ($restored > 0) {
            Log::channel('sis')->info('[AbsenSiswaObserver] Poin alfa di-restore & diperbarui', [
                'siswa_id' => $siswaId, 'tanggal' => $tanggal,
                'count' => $restored, 'poin' => $poinBaru,
            ]);
        }
    }

    private function handleTerlambat(int $siswaId, string $tanggal, string $tahunAjaran, AbsenSiswa $absen, ?Sekolah $sekolah): void
    {
        $deviceId = 'auto-terlambat-' . $tanggal;
        $siswa    = $absen->siswa ?? \App\Models\Siswa::find($siswaId);

        // Ambil pasal & poin terkini dari school config
        $pasal    = ($sekolah?->auto_poin_terlambat_enabled && $sekolah?->pasal_terlambat_id)
            ? $this->getPasal($sekolah->pasal_terlambat_id)
            : null;
        $poinBaru = $pasal ? ($pasal->poin_default ?? $pasal->skormin ?? 0) : null;

        // Coba restore dari trash, sekaligus update pasal & poin ke config terkini
        $trashedRecords = Pelanggaran::onlyTrashed()
            ->where('siswa_id', $siswaId)->where('deviceid', $deviceId)->get();

        $restored = 0;
        foreach ($trashedRecords as $record) {
            $record->restore();

            // Setelah restore, update pasal & poin sesuai school config saat ini
            if ($pasal && $poinBaru !== null) {
                $record->update([
                    'idpasal'      => $pasal->idpasal,
                    'poin'         => $poinBaru,
                    'tahun_ajaran' => $tahunAjaran,
                ]);
                // PelanggaranObserver::updated() akan otomatis sync transaksi
            }

            $restored++;
        }

        // Jika tidak ada di trash dan fitur aktif → buat baru
        if ($restored === 0 && $pasal && $siswa) {
            Pelanggaran::create([
                'siswa_id'     => $siswaId,
                'tgl'          => Carbon::parse($tanggal),
                'tahun_ajaran' => $tahunAjaran,
                'deviceid'     => $deviceId,
                'noreg'        => $siswa->nis ?? '',
                'nama'         => $siswa->nama_lengkap,
                'kelas'        => $siswa->kelas?->nama_kelas ?? '',
                'idpasal'      => $pasal->idpasal,
                'isi'          => 'Terlambat masuk sekolah',
                'poin'         => $poinBaru,
                'pelapor'      => 'Sistem',
                'created_by'   => null,
            ]);
        }

        if ($restored > 0) {
            Log::channel('sis')->info('[AbsenSiswaObserver] Poin terlambat di-restore & diperbarui', [
                'siswa_id' => $siswaId, 'tanggal' => $tanggal,
                'count' => $restored, 'poin' => $poinBaru,
            ]);
        }
    }

    private function handleHadir(int $siswaId, string $tanggal, string $tahunAjaran, AbsenSiswa $absen, ?Sekolah $sekolah): void
    {
        $deviceId = 'auto-hadir-' . $tanggal;
        $siswa    = $absen->siswa ?? \App\Models\Siswa::find($siswaId);

        // Ambil pasal & poin terkini dari school config
        $pasal    = ($sekolah?->auto_poin_hadir_enabled && $sekolah?->pasal_hadir_id)
            ? $this->getPasal($sekolah->pasal_hadir_id)
            : null;
        $poinBaru = $pasal ? ($pasal->poin_default ?? $pasal->skormin ?? 0) : null;

        // Restore satu per satu, update pasal & poin ke config terkini
        $trashedRecords = Penghargaan::onlyTrashed()
            ->where('siswa_id', $siswaId)->where('deviceid', $deviceId)->get();

        $restored = 0;
        foreach ($trashedRecords as $record) {
            $record->restore();

            // Update pasal & poin sesuai school config saat ini
            if ($pasal && $poinBaru !== null) {
                $record->update([
                    'idpasal'      => $pasal->idpasal,
                    'poin'         => $poinBaru,
                    'tahun_ajaran' => $tahunAjaran,
                    // Pastikan acc terisi saat restore
                    'acc'          => 'YA',
                    'tglacc'       => $record->tglacc ?? now(),
                    'nmacc'        => $record->nmacc ?: 'Sistem',
                ]);
                // PenghargaanObserver::updated() akan otomatis sync transaksi jika acc = 'YA'
            }

            $restored++;
        }

        // Jika tidak ada di trash dan fitur aktif → buat baru
        if ($restored === 0 && $pasal && $siswa) {
            Penghargaan::create([
                'siswa_id'     => $siswaId,
                'tgl'          => Carbon::parse($tanggal),
                'tahun_ajaran' => $tahunAjaran,
                'deviceid'     => $deviceId,
                'noreg'        => $siswa->nis ?? '',
                'nama'         => $siswa->nama_lengkap,
                'kelas'        => $siswa->kelas?->nama_kelas ?? '',
                'idpasal'      => $pasal->idpasal,
                'isi'          => 'Hadir tepat waktu',
                'poin'         => $poinBaru,
                'pelapor'      => 'Sistem',
                'ket'          => 'Auto dari absensi harian',
                'acc'          => 'YA',
                'tglacc'       => now(),
                'nmacc'        => 'Sistem',
                'created_by'   => null,
            ]);
        }

        if ($restored > 0) {
            Log::channel('sis')->info('[AbsenSiswaObserver] Poin hadir di-restore & diperbarui', [
                'siswa_id' => $siswaId, 'tanggal' => $tanggal,
                'count' => $restored, 'poin' => $poinBaru,
            ]);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getPasal(string $pasalId): ?SubPasal
    {
        return SubPasal::where('idpasal', $pasalId)->orderByDesc('thnajaran')->first();
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
