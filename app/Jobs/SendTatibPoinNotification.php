<?php

namespace App\Jobs;

use App\Models\TatibPointNotification;
use App\Services\TatibPoinService;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTatibPoinNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public TatibPointNotification $notification
    ) {}

    public function handle(WhatsappService $wa): void
    {
        $sekolah = \App\Models\Sekolah::aktif();

        if (! $sekolah?->wa_notif_tatib_enabled) {
            Log::channel('wa')->info('[TatibPoin] WA dinonaktifkan, skip.', [
                'notification_id' => $this->notification->id,
            ]);
            return;
        }

        if ($sekolah->sedangLibur()) {
            Log::channel('wa')->info('[TatibPoin] Mode libur, skip.', [
                'notification_id' => $this->notification->id,
            ]);
            return;
        }

        $notification = $this->notification->fresh(['siswa.kelas']);

        if (! $notification || $notification->status === 'sent') {
            return;
        }

        $siswa = $notification->siswa;

        if (! $siswa) {
            $notification->update(['status' => 'skipped']);
            return;
        }

        $tatibPoinService = app(TatibPoinService::class);

        $tahunAjaran      = $notification->tahun_ajaran;
        $totalPenghargaan = $tatibPoinService->totalPenghargaan($siswa, $tahunAjaran);
        $totalPelanggaran = $notification->total_poin; // snapshot saat ambang tercapai
        $sisaPoin         = $tatibPoinService->hitungTotalPoin($totalPelanggaran, $totalPenghargaan);

        $pesan = WhatsappService::templatePelanggaranAmbang(
            $siswa->nama_lengkap,
            $siswa->kelas?->nama_kelas ?? '-',
            $notification->tahun_ajaran,
            $notification->total_poin,
            $notification->batas_poin,
            $notification->tindakan,
            $notification->sanksi,
            TatibPoinService::POIN_AWAL_EDARAN,
            $sisaPoin
        );

        // Kirim ke siswa (jika ada nomor)
        $terkirimSiswa = false;
        if ($siswa->no_hp_siswa) {
            $terkirimSiswa = $wa->send($siswa->no_hp_siswa, $pesan, 'tatib_poin', $notification->id);

            Log::channel('wa')->info('[TatibPoin] WA siswa ' . ($terkirimSiswa ? 'terkirim' : 'gagal') . '.', [
                'notification_id' => $notification->id,
                'no_hp'           => $siswa->no_hp_siswa,
            ]);
        }

        // Kirim ke ortu — ortu1 dulu, jika berhasil STOP, jika gagal/kosong fallback ke ortu2
        $terkirimOrtu = $this->kirimKeOrtu($wa, $siswa, $pesan, $notification->id);

        // Update status: sukses jika setidaknya salah satu (siswa atau ortu) berhasil
        $sukses = $terkirimSiswa || $terkirimOrtu;

        $notification->update([
            'status'  => $sukses ? 'sent' : 'failed',
            'sent_at' => $sukses ? now() : null,
        ]);

        Log::channel('wa')->info('[TatibPoin] Notifikasi selesai diproses.', [
            'notification_id' => $notification->id,
            'siswa_id'        => $siswa->id,
            'terkirim_siswa'  => $terkirimSiswa,
            'terkirim_ortu'   => $terkirimOrtu,
            'status_akhir'    => $sukses ? 'sent' : 'failed',
        ]);
    }

    /**
     * Kirim ke ortu1 dulu. Jika berhasil, STOP.
     * Jika ortu1 kosong atau gagal, coba ortu2.
     * Hanya satu nomor ortu yang menerima notifikasi.
     *
     * @return bool true jika berhasil ke salah satu ortu
     */
    private function kirimKeOrtu(WhatsappService $wa, $siswa, string $pesan, int $notifId): bool
    {
        // Coba ortu1
        if ($siswa->no_hp_ortu1) {
            $terkirim = $wa->send($siswa->no_hp_ortu1, $pesan, 'tatib_poin', $notifId);

            if ($terkirim) {
                Log::channel('wa')->info('[TatibPoin] WA terkirim ke ortu1.', [
                    'notification_id' => $notifId,
                    'no_hp'           => $siswa->no_hp_ortu1,
                ]);
                return true; // sukses — tidak perlu kirim ke ortu2
            }

            Log::channel('wa')->warning('[TatibPoin] Ortu1 gagal, fallback ke ortu2.', [
                'notification_id' => $notifId,
                'no_hp'           => $siswa->no_hp_ortu1,
            ]);
        }

        // Ortu1 kosong atau gagal → fallback ke ortu2
        if ($siswa->no_hp_ortu2) {
            $terkirim = $wa->send($siswa->no_hp_ortu2, $pesan, 'tatib_poin', $notifId);

            if ($terkirim) {
                Log::channel('wa')->info('[TatibPoin] WA terkirim ke ortu2 (fallback).', [
                    'notification_id' => $notifId,
                    'no_hp'           => $siswa->no_hp_ortu2,
                ]);
                return true;
            }

            Log::channel('wa')->error('[TatibPoin] Ortu1 dan ortu2 gagal.', [
                'notification_id' => $notifId,
                'siswa_id'        => $siswa->id,
            ]);
            return false;
        }

        Log::channel('wa')->warning('[TatibPoin] Tidak ada nomor HP ortu tersedia.', [
            'notification_id' => $notifId,
            'siswa_id'        => $siswa->id,
        ]);
        return false;
    }
}
