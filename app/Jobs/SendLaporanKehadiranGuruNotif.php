<?php

namespace App\Jobs;

use App\Models\LaporanKehadiranGuru;
use App\Models\Sekolah;
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

    public function handle(WhatsappService $wa): void
    {
        // Guard: cek toggle notifikasi WA laporan guru di konfigurasi sekolah
        $sekolah = Sekolah::aktif();
        if (! $sekolah?->wa_notif_laporan_guru_enabled) {
            Log::channel('gtk')->info('[LaporanKehadiran] Notifikasi WA laporan guru dinonaktifkan, skip.', [
                'laporan_id' => $this->laporan->id,
            ]);
            return;
        }

        // Ambil nomor penerima dari konfigurasi sekolah
        $nomorList = $sekolah->wa_notif_laporan_guru_nomor ?? [];

        if (empty($nomorList)) {
            Log::channel('gtk')->warning('[LaporanKehadiran] Tidak ada nomor penerima dikonfigurasi, skip.', [
                'laporan_id' => $this->laporan->id,
            ]);
            return;
        }

        $this->laporan->loadMissing(['gtk', 'kelas', 'jadwalKbm', 'dilaporkanOlehSiswa']);

        $gtk       = $this->laporan->gtk;
        $kelas     = $this->laporan->kelas;
        $pelapor   = $this->laporan->dilaporkanOlehSiswa?->nama_lengkap ?? 'GTK';
        $tanggal   = $this->laporan->tanggal->format('d/m/Y');
        $waktu     = $this->laporan->waktu_laporan->format('H:i');
        $jamKe     = $this->laporan->jam_ke;
        $status    = $this->laporan->status_label;
        $catatan   = $this->laporan->catatan;

        $pesan = WhatsappService::templateLaporanKehadiranGuru(
            $gtk?->nama_lengkap ?? '-',
            $kelas?->nama_kelas ?? '-',
            $jamKe,
            $tanggal,
            $waktu,
            $status,
            $pelapor,
            $catatan
        );

        $adaYangTerkirim = false;

        foreach ($nomorList as $nomor) {
            $nomor = trim((string) $nomor);
            if ($nomor === '') {
                continue;
            }

            $sukses = $wa->send($nomor, $pesan, 'laporan_kehadiran_guru', $this->laporan->id);

            if ($sukses) {
                $adaYangTerkirim = true;
                Log::channel('gtk')->info('[LaporanKehadiran] WA notif terkirim', [
                    'laporan_id' => $this->laporan->id,
                    'no_hp'      => $nomor,
                ]);
            } else {
                Log::channel('gtk')->warning('[LaporanKehadiran] WA notif gagal', [
                    'laporan_id' => $this->laporan->id,
                    'no_hp'      => $nomor,
                ]);
            }
        }

        if ($adaYangTerkirim) {
            $this->laporan->updateQuietly(['wa_terkirim' => true]);
        }
    }
}
