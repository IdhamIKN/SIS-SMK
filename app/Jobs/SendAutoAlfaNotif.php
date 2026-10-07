<?php

namespace App\Jobs;

use App\Models\AbsenSiswa;
use App\Services\WhatsappService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendAutoAlfaNotif implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly AbsenSiswa $absen
    ) {}

    public function handle(WhatsappService $wa): void
    {
        $sekolah = \App\Models\Sekolah::aktif();

        if (! $sekolah?->wa_notif_alfa_enabled) {
            Log::channel('sis')->info('[AutoAlfa] WA dinonaktifkan, skip.', ['absen_id' => $this->absen->id]);
            return;
        }

        if ($sekolah->sedangLibur($this->absen->tanggal)) {
            Log::channel('sis')->info('[AutoAlfa] Mode libur, skip.', ['absen_id' => $this->absen->id]);
            return;
        }

        $siswa = $this->absen->siswa;
        $kelas = $this->absen->kelas;

        if (! $siswa) {
            Log::channel('sis')->warning('[AutoAlfa] Siswa tidak ditemukan.', ['absen_id' => $this->absen->id]);
            return;
        }

        $tanggal = $this->absen->tanggal instanceof \Carbon\Carbon
            ? $this->absen->tanggal->format('d/m/Y')
            : \Carbon\Carbon::parse($this->absen->tanggal)->format('d/m/Y');

        $pesan = WhatsappService::templateAutoAlfa(
            $siswa->nama_lengkap,
            $kelas?->nama_kelas ?? '-',
            $tanggal
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
            $terkirim = $wa->send($siswa->no_hp_ortu1, $pesan, 'auto_alfa', $this->absen->id);

            if ($terkirim) {
                Log::channel('sis')->info('[AutoAlfa] WA terkirim ke ortu1.', [
                    'absen_id' => $this->absen->id,
                    'no_hp'    => $siswa->no_hp_ortu1,
                ]);
                return; // sukses — tidak perlu kirim ke ortu2
            }

            Log::channel('sis')->warning('[AutoAlfa] Ortu1 gagal, fallback ke ortu2.', [
                'absen_id' => $this->absen->id,
                'no_hp'    => $siswa->no_hp_ortu1,
            ]);
        }

        // Ortu1 kosong atau gagal → fallback ke ortu2
        if ($siswa->no_hp_ortu2) {
            $terkirim = $wa->send($siswa->no_hp_ortu2, $pesan, 'auto_alfa', $this->absen->id);

            if ($terkirim) {
                Log::channel('sis')->info('[AutoAlfa] WA terkirim ke ortu2 (fallback).', [
                    'absen_id' => $this->absen->id,
                    'no_hp'    => $siswa->no_hp_ortu2,
                ]);
            } else {
                Log::channel('sis')->error('[AutoAlfa] Ortu1 dan ortu2 gagal.', [
                    'absen_id' => $this->absen->id,
                    'siswa_id' => $siswa->id,
                ]);
            }
            return;
        }

        Log::channel('sis')->warning('[AutoAlfa] Tidak ada nomor HP ortu tersedia.', [
            'absen_id' => $this->absen->id,
            'siswa_id' => $siswa->id,
        ]);
    }
}
