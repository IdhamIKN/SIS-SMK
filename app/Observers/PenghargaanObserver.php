<?php

namespace App\Observers;

use App\Models\Penghargaan;
use App\Models\Siswa;
use App\Models\TransaksiPoin;
use App\Services\TatibPoinService;
use Illuminate\Support\Facades\Log;

/**
 * PenghargaanObserver
 *
 * Menjaga konsistensi tblpenghargaan ↔ tbltransaksi secara otomatis.
 *
 * - created       → buat / restore TransaksiPoin (hanya jika acc = 'YA')
 * - updated       → perbarui TransaksiPoin jika kolom relevan berubah (hanya jika acc = 'YA')
 * - deleted       → soft-delete TransaksiPoin terkait
 * - restored      → restore TransaksiPoin terkait (hanya jika acc = 'YA')
 * - forceDeleted  → hard-delete TransaksiPoin terkait
 */
class PenghargaanObserver
{
    public function __construct(
        private readonly TatibPoinService $tatibPoin
    ) {}

    public function created(Penghargaan $penghargaan): void
    {
        // Transaksi hanya dibuat saat penghargaan sudah di-ACC
        if ($penghargaan->acc !== 'YA') {
            return;
        }

        $siswa = $this->resolveSiswa($penghargaan);
        if (! $siswa) {
            Log::channel('sis')->warning('[PenghargaanObserver] Siswa tidak ditemukan (created), transaksi tidak dibuat', [
                'idpen' => $penghargaan->idpen, 'siswa_id' => $penghargaan->siswa_id,
            ]);
            return;
        }

        // Lempar exception agar caller (controller / command) bisa rollback transaksi DB
        $this->tatibPoin->createPenghargaanTransaction($penghargaan, $siswa);
    }

    public function updated(Penghargaan $penghargaan): void
    {
        $dirty = $penghargaan->getDirty();
        $kolomRelevant = ['poin', 'idpasal', 'tgl', 'isi', 'pelapor', 'tahun_ajaran', 'acc'];

        if (empty(array_intersect(array_keys($dirty), $kolomRelevant))) {
            return;
        }

        // Jika penghargaan belum di-ACC, tidak ada transaksi yang perlu diperbarui
        if ($penghargaan->acc !== 'YA') {
            // Jika acc berubah dari YA → non-YA (revoke), hapus transaksi
            if (array_key_exists('acc', $dirty)) {
                try {
                    TransaksiPoin::where('noreff', $this->noreff($penghargaan))->delete();
                } catch (\Throwable $e) {
                    Log::channel('sis')->error('[PenghargaanObserver] Gagal delete transaksi saat revoke', [
                        'idpen' => $penghargaan->idpen, 'error' => $e->getMessage(),
                    ]);
                }
            }
            return;
        }

        $siswa = $this->resolveSiswa($penghargaan);
        if (! $siswa) {
            Log::channel('sis')->warning('[PenghargaanObserver] Siswa tidak ditemukan (updated), transaksi tidak diperbarui', [
                'idpen' => $penghargaan->idpen, 'siswa_id' => $penghargaan->siswa_id,
            ]);
            return;
        }

        // Lempar exception agar caller bisa rollback
        $this->tatibPoin->createPenghargaanTransaction($penghargaan, $siswa);
    }

    /**
     * Soft delete → soft-delete transaksi terkait juga.
     * Dipanggil baik saat hapus manual maupun via AbsenSiswaObserver.
     */
    public function deleted(Penghargaan $penghargaan): void
    {
        try {
            TransaksiPoin::where('noreff', $this->noreff($penghargaan))->delete();
        } catch (\Throwable $e) {
            Log::channel('sis')->error('[PenghargaanObserver] Gagal soft-delete transaksi (deleted)', [
                'idpen' => $penghargaan->idpen, 'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Restore penghargaan → restore transaksi terkait juga (hanya jika acc = 'YA').
     */
    public function restored(Penghargaan $penghargaan): void
    {
        // Transaksi hanya di-restore kalau penghargaan memang sudah di-ACC
        if ($penghargaan->acc !== 'YA') {
            return;
        }

        try {
            TransaksiPoin::withTrashed()
                ->where('noreff', $this->noreff($penghargaan))
                ->restore();
        } catch (\Throwable $e) {
            Log::channel('sis')->error('[PenghargaanObserver] Gagal restore transaksi (restored)', [
                'idpen' => $penghargaan->idpen, 'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Force delete → hard-delete transaksi terkait.
     */
    public function forceDeleted(Penghargaan $penghargaan): void
    {
        try {
            TransaksiPoin::withTrashed()
                ->where('noreff', $this->noreff($penghargaan))
                ->forceDelete();
        } catch (\Throwable $e) {
            Log::channel('sis')->error('[PenghargaanObserver] Gagal force-delete transaksi (forceDeleted)', [
                'idpen' => $penghargaan->idpen, 'error' => $e->getMessage(),
            ]);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function noreff(Penghargaan $penghargaan): string
    {
        $tgl = $penghargaan->tgl instanceof \Carbon\Carbon
            ? $penghargaan->tgl
            : \Carbon\Carbon::parse($penghargaan->tgl);

        return 'RW' . $tgl->format('ymd') . $penghargaan->idpen;
    }

    private function resolveSiswa(Penghargaan $penghargaan): ?Siswa
    {
        if ($penghargaan->relationLoaded('siswa') && $penghargaan->siswa) {
            return $penghargaan->siswa;
        }
        return Siswa::find($penghargaan->siswa_id);
    }
}
