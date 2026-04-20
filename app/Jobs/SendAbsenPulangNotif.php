<?php

namespace App\Jobs;

use App\Models\AbsenSiswa;
use App\Services\WhatsappService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendAbsenPulangNotif implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly AbsenSiswa $absen
    ) {}

    public function handle(): void
    {
        $siswa = $this->absen->siswa;
        $kelas = $this->absen->kelas;

        // Kirim ke ortu 1
        if ($siswa->no_hp_ortu1) {
            $pesan = WhatsappService::templateAbsenPulang(
                $siswa->nama_lengkap,
                $kelas->nama_kelas,
                $this->absen->waktu_absen->format('d/m/Y H:i')
            );

            $terkirim = app(WhatsappService::class)->send(
                $siswa->no_hp_ortu1,
                $pesan,
                'absen_pulang',
                $this->absen->id
            );

            if ($terkirim) {
                $this->absen->update(['wa_terkirim_ortu' => true]);
                Log::channel('sis')->info('[AbsenPulang] WA ortu1 terkirim', [
                    'absen_id' => $this->absen->id,
                    'no_hp' => $siswa->no_hp_ortu1,
                ]);
            }
        }

        // Kirim ke ortu 2 jika ada
        if ($siswa->no_hp_ortu2) {
            $pesan = WhatsappService::templateAbsenPulang(
                $siswa->nama_lengkap,
                $kelas->nama_kelas,
                $this->absen->waktu_absen->format('d/m/Y H:i')
            );

            app(WhatsappService::class)->send(
                $siswa->no_hp_ortu2,
                $pesan,
                'absen_pulang',
                $this->absen->id
            );
        }
    }
}