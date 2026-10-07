<?php

namespace App\Observers;

use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\TransaksiPoin;
use App\Services\TatibPoinService;
use Illuminate\Support\Facades\Log;

/**
 * PelanggaranObserver
 *
 * Menjaga konsistensi tblpelanggaran ↔ tbltransaksi secara otomatis.
 *
 * - created   → buat / restore TransaksiPoin
 * - updated   → perbarui TransaksiPoin jika kolom relevan berubah
 * - deleted   → soft-delete TransaksiPoin terkait
 * - restored  → restore TransaksiPoin terkait
 * - forceDeleted → hard-delete TransaksiPoin terkait
 */
class PelanggaranObserver
{
    public function __construct(
        private readonly TatibPoinService $tatibPoin
    ) {}

    public function created(Pelanggaran $pelanggaran): void
    {
        $siswa = $this->resolveSiswa($pelanggaran);
        if (! $siswa) {
            Log::channel('sis')->warning('[PelanggaranObserver] Siswa tidak ditemukan (created), transaksi tidak dibuat', [
                'idpel' => $pelanggaran->idpel, 'siswa_id' => $pelanggaran->siswa_id,
            ]);
            return;
        }

        // Lempar exception agar caller (controller / command) bisa rollback transaksi DB
        $this->tatibPoin->createPelanggaranTransaction($pelanggaran, $siswa, $pelanggaran->isi);
    }

    public function updated(Pelanggaran $pelanggaran): void
    {
        $dirty = $pelanggaran->getDirty();
        $kolumRelevant = ['poin', 'idpasal', 'tgl', 'isi', 'pelapor', 'tahun_ajaran'];

        if (empty(array_intersect(array_keys($dirty), $kolumRelevant))) {
            return;
        }

        $siswa = $this->resolveSiswa($pelanggaran);
        if (! $siswa) {
            Log::channel('sis')->warning('[PelanggaranObserver] Siswa tidak ditemukan (updated), transaksi tidak diperbarui', [
                'idpel' => $pelanggaran->idpel, 'siswa_id' => $pelanggaran->siswa_id,
            ]);
            return;
        }

        // Lempar exception agar caller bisa rollback
        $this->tatibPoin->createPelanggaranTransaction($pelanggaran, $siswa, $pelanggaran->isi);
    }

    /**
     * Soft delete → soft-delete transaksi terkait juga.
     */
    public function deleted(Pelanggaran $pelanggaran): void
    {
        try {
            TransaksiPoin::where('noreff', $this->noreff($pelanggaran))->delete();
        } catch (\Throwable $e) {
            Log::channel('sis')->error('[PelanggaranObserver] Gagal soft-delete transaksi (deleted)', [
                'idpel' => $pelanggaran->idpel, 'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Restore pelanggaran → restore transaksi terkait juga.
     */
    public function restored(Pelanggaran $pelanggaran): void
    {
        try {
            TransaksiPoin::withTrashed()
                ->where('noreff', $this->noreff($pelanggaran))
                ->restore();
        } catch (\Throwable $e) {
            Log::channel('sis')->error('[PelanggaranObserver] Gagal restore transaksi (restored)', [
                'idpel' => $pelanggaran->idpel, 'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Force delete → hard-delete transaksi terkait.
     */
    public function forceDeleted(Pelanggaran $pelanggaran): void
    {
        try {
            TransaksiPoin::withTrashed()
                ->where('noreff', $this->noreff($pelanggaran))
                ->forceDelete();
        } catch (\Throwable $e) {
            Log::channel('sis')->error('[PelanggaranObserver] Gagal force-delete transaksi (forceDeleted)', [
                'idpel' => $pelanggaran->idpel, 'error' => $e->getMessage(),
            ]);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function noreff(Pelanggaran $pelanggaran): string
    {
        $tgl = $pelanggaran->tgl instanceof \Carbon\Carbon
            ? $pelanggaran->tgl
            : \Carbon\Carbon::parse($pelanggaran->tgl);

        return 'PN' . $tgl->format('ymd') . $pelanggaran->idpel;
    }

    private function resolveSiswa(Pelanggaran $pelanggaran): ?Siswa
    {
        if ($pelanggaran->relationLoaded('siswa') && $pelanggaran->siswa) {
            return $pelanggaran->siswa;
        }
        return Siswa::find($pelanggaran->siswa_id);
    }
}
