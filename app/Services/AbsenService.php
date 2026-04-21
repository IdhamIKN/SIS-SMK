<?php

namespace App\Services;

use App\Models\AbsenSiswa;
use App\Models\PengajuanIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AbsenService
{
    public function validateAbsenRequest(Request $request): array
    {
        return $request->validate(
            [
                'foto_selfie' => ['required', 'image', 'max:2048'],
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
            ],
            [
                'foto_selfie.required' => 'Foto selfie wajib diambil sebelum absen.',
                'foto_selfie.image' => 'Foto selfie harus berupa file gambar.',
                'foto_selfie.max' => 'Ukuran foto selfie maksimal 2MB.',
                'latitude.required' => 'Lokasi GPS belum terbaca. Aktifkan lokasi terlebih dahulu.',
                'latitude.numeric' => 'Format latitude tidak valid.',
                'latitude.between' => 'Latitude di luar batas yang diperbolehkan.',
                'longitude.required' => 'Lokasi GPS belum terbaca. Aktifkan lokasi terlebih dahulu.',
                'longitude.numeric' => 'Format longitude tidak valid.',
                'longitude.between' => 'Longitude di luar batas yang diperbolehkan.',
            ]
        );
    }

    public function validateDistanceRequest(Request $request): array
    {
        return $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);
    }

    public function getShiftPagi(): array
    {
        $jam = jam_shift_config();
        return $jam['pagi'] ?? config('sekolah.jam_shift.pagi');
    }

    public function statusHariIni(int $siswaId, string $tanggal): array
    {
        return [
            'sudahMasuk' => AbsenSiswa::where('siswa_id', $siswaId)
                ->where('tanggal', $tanggal)
                ->where('jenis', 'masuk')
                ->exists(),
            'sudahPulang' => AbsenSiswa::where('siswa_id', $siswaId)
                ->where('tanggal', $tanggal)
                ->where('jenis', 'pulang')
                ->exists(),
            'bolehPulangCepat' => $this->punyaIzinPulangCepatDisetujui($siswaId, $tanggal),
        ];
    }

    public function punyaIzinPulangCepatDisetujui(int $siswaId, string $tanggal): bool
    {
        return PengajuanIzin::query()
            ->where('siswa_id', $siswaId)
            ->where('jenis', 'izin_pulang_cepat')
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_sampai', '>=', $tanggal)
            ->exists();
    }

    public function validasiWaktu(string $jenis, bool $bolehPulangCepat, array $shift): ?string
    {
        $sekarang = now()->format('H:i:s');

        if ($jenis === 'masuk') {
            if ($sekarang < $shift['masuk'] || $sekarang > $shift['limit_masuk']) {
                $waktuValid = date('H:i', strtotime($shift['masuk'])) . ' - ' . date('H:i', strtotime($shift['limit_masuk']));
                return "Waktu absen masuk tidak sesuai ({$waktuValid})";
            }
        }

        if ($jenis === 'pulang') {
            if (!$bolehPulangCepat && ($sekarang < $shift['pulang'] || $sekarang > $shift['limit_pulang'])) {
                $waktuValid = date('H:i', strtotime($shift['pulang'])) . ' - ' . date('H:i', strtotime($shift['limit_pulang']));
                return "Waktu absen pulang tidak sesuai ({$waktuValid})";
            }
        }

        return null;
    }

    public function validasiKondisiAbsen(string $jenis, bool $sudahMasuk, bool $sudahPulang): ?string
    {
        if ($jenis === 'masuk' && $sudahMasuk) {
            return 'Anda sudah absen masuk hari ini';
        }

        if ($jenis === 'pulang') {
            if (!$sudahMasuk) {
                return 'Harus absen masuk dulu sebelum pulang';
            }

            if ($sudahPulang) {
                return 'Anda sudah absen pulang hari ini';
            }
        }

        return null;
    }

    public function hitungJarakSekolah(float $latitude, float $longitude): float
    {
        return GeolocationService::hitungJarak(
            $latitude,
            $longitude,
            config('sekolah.latitude'),
            config('sekolah.longitude')
        );
    }

    public function simpanFotoSelfie($file): string
    {
        return $file->store('absen-selfie', 'public');
    }

    public function hapusFotoSelfie(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    public function buatDataAbsen(
        int $siswaId,
        int $kelasId,
        string $tanggal,
        string $jenis,
        string $fotoPath,
        float $latitude,
        float $longitude,
        float $jarak
    ): array {
        return [
            'siswa_id' => $siswaId,
            'kelas_id' => $kelasId,
            'tanggal' => $tanggal,
            'jenis' => $jenis,
            'status' => 'hadir',
            'waktu_absen' => now(),
            'foto_selfie' => $fotoPath,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'jarak_meter' => $jarak,
        ];
    }
}
