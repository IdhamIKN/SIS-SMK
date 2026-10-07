<?php

namespace App\Jobs;

use App\Models\AbsenEvent;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEventNotifJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public AbsenEvent $absenEvent
    ) {}

    public function handle(WhatsappService $wa): void
    {
        $sekolah = \App\Models\Sekolah::aktif();

        if (! $sekolah?->wa_notif_event_enabled) {
            Log::channel('wa')->info('[Event] WA dinonaktifkan, skip.', ['absen_event_id' => $this->absenEvent->id]);
            return;
        }

        if ($sekolah->sedangLibur($this->absenEvent->waktu_masuk ?? $this->absenEvent->waktu_scan)) {
            Log::channel('wa')->info('[Event] Mode libur, skip.', ['absen_event_id' => $this->absenEvent->id]);
            return;
        }

        $siswa = $this->absenEvent->siswa;
        $event = $this->absenEvent->event;

        if (! $siswa || ! $event) {
            Log::channel('wa')->warning('[Event] Siswa atau event tidak ditemukan.', [
                'absen_event_id' => $this->absenEvent->id,
            ]);
            return;
        }

        // Tentukan jenis scan terbaru: jika waktu_pulang baru diisi → pulang, else masuk
        $isJustPulang = $this->absenEvent->waktu_pulang !== null
            && $this->absenEvent->waktu_pulang->diffInMinutes(now()) < 5;
        $jenisNotif = $isJustPulang ? 'pulang' : 'masuk';

        $waktuNotif = $isJustPulang
            ? $this->absenEvent->waktu_pulang
            : ($this->absenEvent->waktu_masuk ?? $this->absenEvent->waktu_scan);

        if (! $waktuNotif) {
            Log::channel('wa')->warning('[Event] Tidak ada waktu scan tersedia.', ['absen_event_id' => $this->absenEvent->id]);
            return;
        }

        $pesan = WhatsappService::templateAbsenEvent(
            $siswa->nama_lengkap,
            $siswa->kelas?->nama_kelas ?? '-',
            $event->nama_event,
            $jenisNotif,
            $waktuNotif->format('d/m/Y H:i')
        );

        $this->kirimKeOrtu($wa, $siswa, $pesan);
    }

    /**
     * Kirim ke ortu1 dulu. Jika berhasil, STOP.
     * Jika ortu1 kosong atau gagal, coba ortu2.
     * Hanya satu nomor yang menerima notifikasi per event.
     */
    private function kirimKeOrtu(WhatsappService $wa, $siswa, string $pesan): void
    {
        // Coba ortu1
        if ($siswa->no_hp_ortu1) {
            $terkirim = $wa->send($siswa->no_hp_ortu1, $pesan, 'event', $this->absenEvent->id);

            if ($terkirim) {
                $this->absenEvent->update(['wa_terkirim_ortu' => true]);
                Log::channel('wa')->info('[Event] WA terkirim ke ortu1.', [
                    'absen_event_id' => $this->absenEvent->id,
                    'no_hp'          => $siswa->no_hp_ortu1,
                ]);
                return; // sukses — tidak perlu kirim ke ortu2
            }

            Log::channel('wa')->warning('[Event] Ortu1 gagal, fallback ke ortu2.', [
                'absen_event_id' => $this->absenEvent->id,
                'no_hp'          => $siswa->no_hp_ortu1,
            ]);
        }

        // Ortu1 kosong atau gagal → fallback ke ortu2
        if ($siswa->no_hp_ortu2) {
            $terkirim = $wa->send($siswa->no_hp_ortu2, $pesan, 'event', $this->absenEvent->id);

            if ($terkirim) {
                $this->absenEvent->update(['wa_terkirim_ortu' => true]);
                Log::channel('wa')->info('[Event] WA terkirim ke ortu2 (fallback).', [
                    'absen_event_id' => $this->absenEvent->id,
                    'no_hp'          => $siswa->no_hp_ortu2,
                ]);
            } else {
                Log::channel('wa')->error('[Event] Ortu1 dan ortu2 gagal.', [
                    'absen_event_id' => $this->absenEvent->id,
                    'siswa_id'       => $siswa->id,
                ]);
            }
            return;
        }

        Log::channel('wa')->warning('[Event] Tidak ada nomor HP ortu tersedia.', [
            'absen_event_id' => $this->absenEvent->id,
            'siswa_id'       => $siswa->id,
        ]);
    }
}
