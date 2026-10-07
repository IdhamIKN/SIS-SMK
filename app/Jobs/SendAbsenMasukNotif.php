<?php

namespace App\Jobs;

use App\Models\AbsenSiswa;
use App\Models\Sekolah;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendAbsenMasukNotif implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly AbsenSiswa $absen
    ) {}

    public function handle(WhatsappService $wa): void
    {
        $sekolah = Sekolah::aktif();

        if (! $sekolah?->wa_notif_masuk_enabled) {
            Log::channel('sis')->info('[AbsenMasuk] WA dinonaktifkan, skip.', ['absen_id' => $this->absen->id]);
            return;
        }

        if ($sekolah->sedangLibur($this->absen->tanggal)) {
            Log::channel('sis')->info('[AbsenMasuk] Mode libur, skip.', ['absen_id' => $this->absen->id]);
            return;
        }

        $siswa = $this->absen->siswa;
        $kelas = $this->absen->kelas;

        if (! $siswa) {
            Log::channel('sis')->warning('[AbsenMasuk] Siswa tidak ditemukan.', ['absen_id' => $this->absen->id]);
            return;
        }

        $pesan = $this->buildPesan($siswa, $kelas, $sekolah);
        if (! $pesan) {
            return;
        }

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
            $terkirim = $wa->send($siswa->no_hp_ortu1, $pesan, 'absen_masuk', $this->absen->id);

            if ($terkirim) {
                $this->absen->update(['wa_terkirim_ortu' => true]);
                Log::channel('sis')->info('[AbsenMasuk] WA terkirim ke ortu1.', [
                    'absen_id' => $this->absen->id,
                    'no_hp'    => $siswa->no_hp_ortu1,
                ]);
                return; // sukses — tidak perlu kirim ke ortu2
            }

            Log::channel('sis')->warning('[AbsenMasuk] Ortu1 gagal, fallback ke ortu2.', [
                'absen_id' => $this->absen->id,
                'no_hp'    => $siswa->no_hp_ortu1,
            ]);
        }

        // Ortu1 kosong atau gagal → fallback ke ortu2
        if ($siswa->no_hp_ortu2) {
            $terkirim = $wa->send($siswa->no_hp_ortu2, $pesan, 'absen_masuk', $this->absen->id);

            if ($terkirim) {
                $this->absen->update(['wa_terkirim_ortu' => true]);
                Log::channel('sis')->info('[AbsenMasuk] WA terkirim ke ortu2 (fallback).', [
                    'absen_id' => $this->absen->id,
                    'no_hp'    => $siswa->no_hp_ortu2,
                ]);
            } else {
                Log::channel('sis')->error('[AbsenMasuk] Ortu1 dan ortu2 gagal.', [
                    'absen_id' => $this->absen->id,
                    'siswa_id' => $siswa->id,
                ]);
            }
            return;
        }

        Log::channel('sis')->warning('[AbsenMasuk] Tidak ada nomor HP ortu tersedia.', [
            'absen_id' => $this->absen->id,
            'siswa_id' => $siswa->id,
        ]);
    }

    private function buildPesan($siswa, $kelas, $sekolah): ?string
    {
        $namaKelas = $kelas?->nama_kelas ?? '-';

        // Kolom baru: jam_masuk + tanggal. Fallback ke waktu_absen legacy.
        $waktuAbsen = null;
        if (! empty($this->absen->jam_masuk)) {
            $waktuAbsen = \Carbon\Carbon::parse(
                $this->absen->tanggal->format('Y-m-d') . ' ' . $this->absen->jam_masuk
            );
        } elseif ($this->absen->waktu_absen) {
            $waktuAbsen = $this->absen->waktu_absen;
        }

        if (! $waktuAbsen) {
            return null;
        }

        $waktuFormatted = $waktuAbsen->format('d/m/Y H:i');
        $tanggal        = $waktuAbsen->format('d/m/Y');

        $statusMasuk = $this->absen->status_masuk ?? $this->absen->status ?? 'hadir';

        if ($statusMasuk === 'terlambat') {
            return WhatsappService::templateAbsenTerlambat(
                $siswa->nama_lengkap,
                $namaKelas,
                $waktuFormatted,
                $this->hitungMenitTerlambat($waktuAbsen, $sekolah),
                $tanggal
            );
        }

        return WhatsappService::templateAbsenMasuk(
            $siswa->nama_lengkap,
            $namaKelas,
            $waktuFormatted
        );
    }

    private function hitungMenitTerlambat(Carbon $waktuAbsen, $sekolah): int
    {
        $raw = $sekolah?->batas_tepat_waktu;
        if (! $raw) {
            return 0;
        }

        $timestamp = strtotime((string) $raw);
        if ($timestamp === false) {
            return 0;
        }

        $batas = Carbon::instance($waktuAbsen)->startOfDay()
            ->setTimeFromTimeString(date('H:i:s', $timestamp));

        return $waktuAbsen->lte($batas)
            ? 0
            : (int) ceil($waktuAbsen->diffInSeconds($batas) / 60);
    }
}
