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

    /**
     * Create a new job instance.
     */
    public function __construct(
        public AbsenEvent $absenEvent
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WhatsappService $whatsappService): void
    {
        $siswa = $this->absenEvent->siswa;
        $event = $this->absenEvent->event;

        if (! $siswa || ! $event) {
            Log::channel('wa')->warning('[WA:Event] Siswa atau event tidak ditemukan', [
                'absen_event_id' => $this->absenEvent->id,
                'siswa_id' => $this->absenEvent->siswa_id,
                'event_id' => $this->absenEvent->event_id,
            ]);

            return;
        }

        // Ambil no HP ortu pertama yang tersedia
        $noHpOrtu = $siswa->no_hp_ortu1 ?? $siswa->no_hp_ortu2;

        if (! $noHpOrtu) {
            Log::channel('wa')->warning('[WA:Event] No HP ortu tidak tersedia', [
                'absen_event_id' => $this->absenEvent->id,
                'siswa_id' => $siswa->id,
            ]);

            return;
        }

        $jenisLabel = $this->absenEvent->jenis === 'masuk' ? 'masuk' : 'pulang';

        $pesan = sprintf(
            '[SIS] Info: Anak Anda %s (%s) telah absen %s di event %s pada %s.',
            $siswa->nama_lengkap,
            $siswa->nis,
            $jenisLabel,
            $event->nama_event,
            $this->absenEvent->waktu_scan->format('d M Y H:i')
        );

        $sukses = $whatsappService->send($noHpOrtu, $pesan, 'event', $this->absenEvent->id);

        if ($sukses) {
            $this->absenEvent->update(['wa_terkirim_ortu' => true]);

            Log::channel('wa')->info('[WA:Event] Notifikasi terkirim sukses', [
                'absen_event_id' => $this->absenEvent->id,
                'event_id' => $event->id,
                'siswa_id' => $siswa->id,
                'no_hp' => $noHpOrtu,
            ]);
        }
    }
}
