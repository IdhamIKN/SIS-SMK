<?php

namespace App\Services;

use App\Models\AbsenSiswa;
use App\Models\LokasiPkl;
use App\Models\PenugasanPkl;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * AbsenPklService — logika bisnis absensi siswa di lokasi PKL.
 *
 * Berbeda dari AbsenService (sekolah), service ini:
 *  - Validasi GPS vs koordinat LokasiPkl (bukan sekolah)
 *  - Validasi jam vs jam_masuk_pkl / jam_pulang_pkl dari LokasiPkl
 *  - Status masuk: 'hadir' atau 'terlambat' (berdasarkan batas_terlambat_pkl)
 *  - Simpan ke absen_siswa (tabel yang sama) dengan status_masuk = 'hadir'/'terlambat'
 */
class AbsenPklService
{
    // ──────────────────────────────────────────────────────────────────────
    // Validasi Request
    // ──────────────────────────────────────────────────────────────────────

    public function validateAbsenRequest(Request $request): array
    {
        return $request->validate([
            'foto_selfie' => ['required', 'image', 'max:2048'],
            'latitude'    => ['required', 'numeric', 'between:-90,90'],
            'longitude'   => ['required', 'numeric', 'between:-180,180'],
        ], [
            'foto_selfie.required' => 'Foto selfie wajib diambil.',
            'foto_selfie.image'    => 'File harus berupa gambar.',
            'foto_selfie.max'      => 'Ukuran foto maksimal 2 MB.',
            'latitude.required'    => 'GPS diperlukan untuk absensi.',
            'longitude.required'   => 'GPS diperlukan untuk absensi.',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Status Hari Ini (PKL)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Status absensi PKL hari ini untuk siswa tertentu.
     * Menggunakan tabel absen_siswa yang sama — cek jam_masuk & jam_pulang.
     */
    public function statusHariIni(int $siswaId, string $tanggal): array
    {
        $absen = AbsenSiswa::where('siswa_id', $siswaId)
            ->whereDate('tanggal', $tanggal)
            ->first();

        $sudahMasuk  = $absen && ! empty($absen->jam_masuk);
        $sudahPulang = $absen && ! empty($absen->jam_pulang);

        return [
            'sudahMasuk'  => $sudahMasuk,
            'sudahPulang' => $sudahPulang,
            'absen'       => $absen,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Validasi Waktu PKL
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Validasi apakah waktu saat ini sesuai untuk absen masuk/pulang PKL.
     * Jam diambil dari konfigurasi LokasiPkl, bukan dari Sekolah.
     *
     * Fallback jika jam_masuk_pkl / jam_pulang_pkl kosong:
     *   - Masuk : 06:00 - 10:00
     *   - Pulang : 14:00 - 18:00
     */
    public function validasiWaktuPkl(string $jenis, LokasiPkl $lokasi): ?string
    {
        $sekarang = now()->format('H:i:s');

        if ($jenis === 'masuk') {
            // Jam mulai boleh absen masuk (paling awal)
            $jamMulai = $this->formatTime($lokasi->jam_masuk_pkl) ?? '06:00:00';

            // Batas akhir absen masuk
            $batasAkhir = $this->formatTime($lokasi->batas_absen_masuk_pkl)
                ?? $this->formatTime($lokasi->batas_terlambat_pkl)
                ?? $this->addMinutes($jamMulai, 240); // default +4 jam dari jam masuk

            if ($sekarang < $jamMulai) {
                return 'Belum waktunya absen masuk PKL. Mulai dari ' . date('H:i', strtotime($jamMulai));
            }

            if ($sekarang > $batasAkhir) {
                return 'Waktu absen masuk PKL sudah habis (batas: ' . date('H:i', strtotime($batasAkhir)) . ')';
            }
        }

        if ($jenis === 'pulang') {
            $jamPulang    = $this->formatTime($lokasi->jam_pulang_pkl) ?? '14:00:00';
            $limitPulang  = $this->addMinutes($jamPulang, 300); // default +5 jam dari jam pulang

            if ($sekarang < $jamPulang) {
                return 'Belum waktunya absen pulang PKL. Mulai dari ' . date('H:i', strtotime($jamPulang));
            }

            if ($sekarang > $limitPulang) {
                return 'Waktu absen pulang PKL sudah habis (batas: ' . date('H:i', strtotime($limitPulang)) . ')';
            }
        }

        return null;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Validasi Kondisi (sudah masuk/pulang?)
    // ──────────────────────────────────────────────────────────────────────

    public function validasiKondisi(string $jenis, bool $sudahMasuk, bool $sudahPulang): ?string
    {
        if ($jenis === 'masuk' && $sudahMasuk) {
            return 'Kamu sudah absen masuk PKL hari ini.';
        }

        if ($jenis === 'pulang') {
            if (! $sudahMasuk) {
                return 'Harus absen masuk PKL dulu sebelum absen pulang.';
            }
            if ($sudahPulang) {
                return 'Kamu sudah absen pulang PKL hari ini.';
            }
        }

        return null;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Hitung Jarak ke Lokasi PKL
    // ──────────────────────────────────────────────────────────────────────

    public function hitungJarakLokasiPkl(float $lat, float $lng, LokasiPkl $lokasi): float
    {
        return GeolocationService::hitungJarak(
            $lat,
            $lng,
            (float) $lokasi->latitude,
            (float) $lokasi->longitude
        );
    }

    public function getRadiusLokasi(LokasiPkl $lokasi): int
    {
        return ($lokasi->radius_meter && $lokasi->radius_meter > 0)
            ? (int) $lokasi->radius_meter
            : 300; // default 300 meter jika tidak diset
    }

    // ──────────────────────────────────────────────────────────────────────
    // Tentukan Status Masuk PKL (hadir vs terlambat)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Tentukan status masuk: 'hadir' atau 'terlambat'.
     * Berdasarkan batas_terlambat_pkl dari LokasiPkl.
     */
    public function tentukanStatusMasukPkl(LokasiPkl $lokasi): string
    {
        $batasTerlambat = $this->formatTime($lokasi->batas_terlambat_pkl);

        if (! $batasTerlambat) {
            // Jika tidak ada batas terlambat, gunakan jam_masuk_pkl + 15 menit
            $jamMasuk = $this->formatTime($lokasi->jam_masuk_pkl) ?? '07:00:00';
            $batasTerlambat = $this->addMinutes($jamMasuk, 15);
        }

        $sekarang = now()->format('H:i:s');
        return $sekarang > $batasTerlambat ? 'terlambat' : 'hadir';
    }

    // ──────────────────────────────────────────────────────────────────────
    // Simpan Absensi PKL ke absen_siswa
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Simpan absen masuk PKL ke record harian.
     * Menggunakan tabel absen_siswa yang sama dengan absen sekolah.
     * Status masuk: 'hadir' atau 'terlambat' (bukan 'pkl' — agar auto poin berjalan).
     */
    public function simpanAbsenMasukPkl(
        int    $siswaId,
        int    $kelasId,
        string $tanggal,
        string $fotoPath,
        float  $latitude,
        float  $longitude,
        float  $jarak,
        string $statusMasuk = 'hadir'
    ): AbsenSiswa {
        $absen = AbsenSiswa::firstOrCreate(
            ['siswa_id' => $siswaId, 'tanggal' => $tanggal],
            ['kelas_id' => $kelasId]
        );

        $absen->update([
            'kelas_id'          => $kelasId,
            'jam_masuk'         => now()->format('H:i:s'),
            'status_masuk'      => $statusMasuk,
            'latitude_masuk'    => $latitude,
            'longitude_masuk'   => $longitude,
            'jarak_masuk'       => (int) round($jarak),
            'foto_selfie_masuk' => $fotoPath,
            // legacy columns
            'jenis'             => 'masuk',
            'status'            => $statusMasuk,
            'waktu_absen'       => now(),
            'foto_selfie'       => $fotoPath,
            'latitude'          => $latitude,
            'longitude'         => $longitude,
            'jarak_meter'       => (int) round($jarak),
        ]);

        $absen->refresh();

        Log::channel('sis')->info('[AbsenPKL] Absen masuk tersimpan', [
            'siswa_id'    => $siswaId,
            'tanggal'     => $tanggal,
            'status'      => $statusMasuk,
            'jarak_meter' => round($jarak),
        ]);

        return $absen;
    }

    /**
     * Simpan absen pulang PKL ke record harian yang sudah ada.
     */
    public function simpanAbsenPulangPkl(
        int    $siswaId,
        string $tanggal,
        string $fotoPath,
        float  $latitude,
        float  $longitude,
        float  $jarak
    ): AbsenSiswa {
        $absen = AbsenSiswa::where('siswa_id', $siswaId)
            ->whereDate('tanggal', $tanggal)
            ->firstOrFail();

        $absen->update([
            'jam_pulang'         => now()->format('H:i:s'),
            'status_pulang'      => 'hadir',
            'latitude_pulang'    => $latitude,
            'longitude_pulang'   => $longitude,
            'jarak_pulang'       => (int) round($jarak),
            'foto_selfie_pulang' => $fotoPath,
        ]);

        $absen->refresh();

        Log::channel('sis')->info('[AbsenPKL] Absen pulang tersimpan', [
            'siswa_id'    => $siswaId,
            'tanggal'     => $tanggal,
            'jarak_meter' => round($jarak),
        ]);

        return $absen;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Simpan Foto
    // ──────────────────────────────────────────────────────────────────────

    public function simpanFotoSelfie($file): string
    {
        return $file->store('absen-pkl-selfie', 'public');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Info jam untuk ditampilkan di view
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Kembalikan array info jadwal PKL dari LokasiPkl.
     * Digunakan view untuk menampilkan jam yang valid.
     */
    public function getJadwalPkl(LokasiPkl $lokasi): array
    {
        $jamMasuk   = $this->formatTime($lokasi->jam_masuk_pkl)   ?? '06:00:00';
        $jamPulang  = $this->formatTime($lokasi->jam_pulang_pkl)  ?? '14:00:00';
        $batasMasuk = $this->formatTime($lokasi->batas_absen_masuk_pkl)
            ?? $this->formatTime($lokasi->batas_terlambat_pkl)
            ?? $this->addMinutes($jamMasuk, 240);
        $batasPulang = $this->addMinutes($jamPulang, 300);
        $batasTerlambat = $this->formatTime($lokasi->batas_terlambat_pkl)
            ?? $this->addMinutes($jamMasuk, 15);

        return [
            'masuk'          => $jamMasuk,
            'batas_masuk'    => $batasMasuk,
            'batas_terlambat' => $batasTerlambat,
            'pulang'         => $jamPulang,
            'batas_pulang'   => $batasPulang,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    private function formatTime(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('H:i:s');
        }
        if ($value === null || $value === '') {
            return null;
        }
        $timestamp = strtotime((string) $value);
        return $timestamp === false ? null : date('H:i:s', $timestamp);
    }

    private function addMinutes(string $time, int $minutes): string
    {
        return Carbon::createFromFormat('H:i:s', $time)->addMinutes($minutes)->format('H:i:s');
    }
}
