<?php

namespace App\Jobs;

use App\Models\LaporanKehadiranGuru;
use App\Services\WhatsappService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendLaporanKehadiranGuruNotif implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly LaporanKehadiranGuru $laporan
    ) {}

    public function handle(): void
    {
        $gtk = $this->laporan->gtk;

        // Kirim ke BK atau admin
        // Untuk sekarang kirim ke nomor default sekolah
        $nomorTujuan = config('sekolah.no_hp_admin', '081234567890'); // perlu konfigurasi

        $pesan = WhatsappService::templateLaporanKehadiranGuru(
            $gtk->nama_lengkap,
            $this->laporan->jenis,
            $this->laporan->status,
            $this->laporan->waktu_laporan->format('d/m/Y H:i'),
            $this->laporan->catatan
        );

        $terkirim = app(WhatsappService::class)->send(
            $nomorTujuan,
            $pesan,
            'laporan_kehadiran_guru',
            $this->laporan->id
        );

        if ($terkirim) {
            $this->laporan->update(['wa_terkirim' => true]);
            Log::channel('gtk')->info('[LaporanKehadiran] WA notif terkirim', [
                'laporan_id' => $this->laporan->id,
                'no_hp' => $nomorTujuan,
            ]);
        }
    }
}
