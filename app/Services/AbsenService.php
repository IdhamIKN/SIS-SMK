<?php

namespace App\Services;

use App\Models\AbsenSiswa;
use App\Models\PengajuanIzin;
use App\Models\Sekolah;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * AbsenService — logika bisnis absensi siswa.
 *
 * Pola baru: 1 record per siswa per hari.
 * Absen masuk → cari/buat record → isi kolom jam_masuk + status_masuk, dst.
 * Absen pulang → cari record yang ada → update kolom jam_pulang + status_pulang, dst.
 */
class AbsenService
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
        ]);
    }

    public function validateDistanceRequest(Request $request): array
    {
        return $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Status Hari Ini
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Kembalikan status absensi hari ini untuk siswa tertentu.
     * Menggunakan model unified (1 record per hari).
     */
    public function statusHariIni(int $siswaId, string $tanggal): array
    {
        $absen = AbsenSiswa::where('siswa_id', $siswaId)
            ->whereDate('tanggal', $tanggal)
            ->first();

        $sudahMasuk  = $absen && ! empty($absen->jam_masuk);
        $sudahPulang = $absen && ! empty($absen->jam_pulang);

        return [
            'sudahMasuk'       => $sudahMasuk,
            'sudahPulang'      => $sudahPulang,
            'bolehPulangCepat' => $this->punyaIzinPulangCepatDisetujui($siswaId, $tanggal),
            'absen'            => $absen,       // object record (atau null)
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

    // ──────────────────────────────────────────────────────────────────────
    // Core: Simpan Absensi (unified — update record harian)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Simpan absen masuk ke record harian.
     * Buat record jika belum ada, lalu update kolom _masuk.
     */
    public function simpanAbsenMasuk(
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
            'kelas_id'        => $kelasId,
            'jam_masuk'       => now()->format('H:i:s'),
            'status_masuk'    => $statusMasuk,
            'latitude_masuk'  => $latitude,
            'longitude_masuk' => $longitude,
            'jarak_masuk'     => (int) round($jarak),
            'foto_selfie_masuk' => $fotoPath,
            // legacy columns
            'jenis'           => 'masuk',
            'status'          => $statusMasuk,
            'waktu_absen'     => now(),
            'foto_selfie'     => $fotoPath,
            'latitude'        => $latitude,
            'longitude'       => $longitude,
            'jarak_meter'     => (int) round($jarak),
        ]);

        $absen->refresh();
        return $absen;
    }

    /**
     * Simpan absen pulang ke record harian yang sudah ada.
     * Harus dipanggil SETELAH simpanAbsenMasuk berhasil.
     */
    public function simpanAbsenPulang(
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
            'jam_pulang'        => now()->format('H:i:s'),
            'status_pulang'     => 'hadir',
            'latitude_pulang'   => $latitude,
            'longitude_pulang'  => $longitude,
            'jarak_pulang'      => (int) round($jarak),
            'foto_selfie_pulang' => $fotoPath,
        ]);

        $absen->refresh();
        return $absen;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Generate Record Harian (untuk auto-generate & alfa)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Pastikan record absensi harian untuk siswa pada tanggal sudah ada.
     * Jika belum ada, buat dengan status_masuk = 'alfa'.
     * Jika sudah ada, tidak mengubah data.
     */
    public function ensureRecordHarian(int $siswaId, int $kelasId, string $tanggal): AbsenSiswa
    {
        return AbsenSiswa::firstOrCreate(
            ['siswa_id' => $siswaId, 'tanggal' => $tanggal],
            [
                'kelas_id'     => $kelasId,
                'status_masuk' => null,    // kosong — belum absen
                'status'       => 'alfa',  // legacy default
                'jenis'        => 'masuk', // legacy
            ]
        );
    }

    /**
     * Set status alfa otomatis pada record harian yang belum ada jam_masuk.
     */
    public function setStatusAlfa(int $siswaId, string $tanggal, string $catatan = 'Auto Alfa.'): bool
    {
        $updated = AbsenSiswa::where('siswa_id', $siswaId)
            ->whereDate('tanggal', $tanggal)
            ->whereNull('jam_masuk')
            ->update([
                'status_masuk' => 'alfa',
                'status'       => 'alfa',  // legacy
                'catatan'      => $catatan,
            ]);

        return $updated > 0;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Validasi Waktu & Kondisi
    // ──────────────────────────────────────────────────────────────────────

    public function validasiWaktu(string $jenis, bool $bolehPulangCepat, array $shift): ?string
    {
        $sekarang = now()->format('H:i:s');

        if ($jenis === 'masuk') {
            $sekolah = Sekolah::aktif();

            // ── Jam mulai absensi (batas paling awal) ────────────────────
            // Gunakan jam_mulai_absensi dari DB, fallback ke shift['masuk']
            $jamMulaiAbsensi = $sekolah?->jam_mulai_absensi
                ? $this->formatTime($sekolah->jam_mulai_absensi)
                : null;
            $jamMulai = $jamMulaiAbsensi ?? $shift['masuk'];

            // ── Batas akhir absen masuk ───────────────────────────────────
            // Prioritas:
            //   1. batas_absen_masuk  (kolom khusus di tblsekolah — batas akhir absen)
            //   2. jam_eksekusi_auto_alfa (siswa masih bisa absen sebelum sistem auto-alfa)
            //   3. shift['limit_masuk'] dari tblsetjam (fallback terakhir)
            //
            // PENTING: batas akhir absen ≠ batas terlambat.
            // batas_tepat_waktu  → menentukan status Hadir vs Terlambat
            // batas_absen_masuk  → menentukan apakah absen masih diizinkan
            $batasAkhirAbsen = null;

            if (! empty($sekolah?->batas_absen_masuk)) {
                $batasAkhirAbsen = $this->formatTime($sekolah->batas_absen_masuk);
            }

            if (! $batasAkhirAbsen && ! empty($sekolah?->jam_eksekusi_auto_alfa)) {
                // Gunakan jam auto-alfa dikurangi 1 menit sebagai batas akhir
                // Siswa masih bisa absen sampai tepat sebelum sistem auto-alfa berjalan
                $batasAkhirAbsen = $this->formatTime($sekolah->jam_eksekusi_auto_alfa);
            }

            if (! $batasAkhirAbsen) {
                // Fallback ke limit_masuk dari tblsetjam
                $batasAkhirAbsen = $shift['limit_masuk'];
            }

            if ($sekarang < $jamMulai || $sekarang > $batasAkhirAbsen) {
                $waktuValid = date('H:i', strtotime($jamMulai)) . ' - ' . date('H:i', strtotime($batasAkhirAbsen));
                return "Waktu absen masuk tidak sesuai ({$waktuValid})";
            }
        }

        if ($jenis === 'pulang') {
            if (! $bolehPulangCepat && ($sekarang < $shift['pulang'] || $sekarang > $shift['limit_pulang'])) {
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
            if (! $sudahMasuk) {
                return 'Harus absen masuk dulu sebelum pulang';
            }
            if ($sudahPulang) {
                return 'Anda sudah absen pulang hari ini';
            }
        }

        return null;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Status Masuk (hadir vs terlambat)
    // ──────────────────────────────────────────────────────────────────────

    public function tentukanStatusMasuk(\DateTimeInterface $waktuAbsen): string
    {
        $sekolah = Sekolah::aktif();
        $raw = $sekolah?->batas_tepat_waktu;

        if (! $raw) {
            return 'hadir';
        }

        $timestamp = strtotime((string) $raw);
        if ($timestamp === false) {
            return 'hadir';
        }

        $batas = Carbon::today()->setTimeFromTimeString(date('H:i:s', $timestamp));

        return $waktuAbsen->greaterThan($batas) ? 'terlambat' : 'hadir';
    }

    // ──────────────────────────────────────────────────────────────────────
    // Geolocation & File
    // ──────────────────────────────────────────────────────────────────────

    public function hitungJarakSekolah(float $latitude, float $longitude): float
    {
        $lokasi = $this->getLokasiSekolah();

        return GeolocationService::hitungJarak(
            $latitude,
            $longitude,
            $lokasi['latitude'],
            $lokasi['longitude']
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

    // ──────────────────────────────────────────────────────────────────────
    // Shift & Lokasi Sekolah
    // ──────────────────────────────────────────────────────────────────────

    public function getShiftPagi(?CarbonInterface $tanggal = null): array
    {
        $tanggal ??= now();
        $jam = jam_shift_config();
        $shift = $jam['pagi'] ?? config('sekolah.jam_shift.pagi');
        $sekolah = Sekolah::aktif();

        if ($sekolah) {
            $jamMasukNormal  = $this->formatTime($sekolah->jam_masuk ?? null);
            $jamPulangNormal = $this->formatTime($sekolah->jam_pulang ?? null);

            if ($jamMasukNormal) {
                $shift['masuk'] = $jamMasukNormal;
            }
            if ($jamPulangNormal) {
                $shift['pulang'] = $jamPulangNormal;
            }

            if ($this->isHariKhusus($tanggal, $sekolah)) {
                $jamMasukKhusus  = $this->formatTime($sekolah->jam_masuk_khusus ?? null);
                $jamPulangKhusus = $this->formatTime($sekolah->jam_pulang_khusus ?? null);

                if ($jamMasukKhusus) {
                    $shift['masuk'] = $jamMasukKhusus;
                }
                if ($jamPulangKhusus) {
                    $shift['pulang'] = $jamPulangKhusus;
                }
            }
        }

        return $this->normalisasiShift($shift);
    }

    public function getJadwalAktifLabel(?CarbonInterface $tanggal = null): string
    {
        $tanggal ??= now();
        $sekolah = Sekolah::aktif();

        if (! $sekolah || ! $this->isHariKhusus($tanggal, $sekolah)) {
            return 'Normal';
        }

        return 'Khusus ' . $this->namaHariIndonesia($tanggal);
    }

    public function getLokasiSekolah(): array
    {
        $sekolah = Sekolah::aktif();

        return [
            'latitude'  => $this->filledNumber($sekolah?->latitude)
                ? (float) $sekolah->latitude
                : (float) config('sekolah.latitude'),
            'longitude' => $this->filledNumber($sekolah?->longitude)
                ? (float) $sekolah->longitude
                : (float) config('sekolah.longitude'),
        ];
    }

    public function getRadiusAbsensi(): int
    {
        $sekolah = Sekolah::aktif();
        $radius  = $sekolah?->radius_meter;

        if (is_numeric($radius) && (int) $radius > 0) {
            return (int) $radius;
        }

        return (int) config('sekolah.radius_m');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    private function normalisasiShift(array $shift): array
    {
        $defaults = config('sekolah.jam_shift.pagi');
        $shift    = array_merge($defaults, array_filter($shift, fn ($value) => $value !== null));

        $shift['masuk']       = $this->formatTime($shift['masuk']) ?? $defaults['masuk'];
        $shift['limit_masuk'] = $this->resolveLimit($shift['masuk'], $shift['limit_masuk'] ?? null, 30);
        $shift['pulang']      = $this->formatTime($shift['pulang']) ?? $defaults['pulang'];
        $shift['limit_pulang'] = $this->resolveLimit($shift['pulang'], $shift['limit_pulang'] ?? null, 300);

        return $shift;
    }

    private function resolveLimit(string $start, mixed $limit, int $minutes): string
    {
        $formattedLimit  = $this->formatTime($limit);
        $startTimestamp  = strtotime($start);
        $limitTimestamp  = $formattedLimit ? strtotime($formattedLimit) : false;

        if (! $formattedLimit || $limitTimestamp === false || $startTimestamp === false || $limitTimestamp <= $startTimestamp) {
            return Carbon::createFromFormat('H:i:s', $start)->addMinutes($minutes)->format('H:i:s');
        }

        return $formattedLimit;
    }

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

    private function isHariKhusus(CarbonInterface $tanggal, \App\Models\Sekolah $sekolah): bool
    {
        $punyaJamKhusus = ($sekolah->jam_masuk_khusus ?? null) || ($sekolah->jam_pulang_khusus ?? null);
        if (! $punyaJamKhusus) {
            return false;
        }

        $hariKhususRaw = $sekolah->hari_khusus ?? null;
        $hariKhusus    = $this->decodeHari($hariKhususRaw);

        if ($hariKhusus === [] && ($hariKhususRaw === null || $hariKhususRaw === '')) {
            $hariKhusus = ['Jumat'];
        }

        return in_array($this->namaHariIndonesia($tanggal), $hariKhusus, true);
    }

    private function decodeHari(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value));
        }
        if (! is_string($value) || $value === '') {
            return [];
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? array_values(array_filter($decoded)) : [];
    }

    private function namaHariIndonesia(CarbonInterface $tanggal): string
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ][$tanggal->dayOfWeekIso];
    }

    private function filledNumber(mixed $value): bool
    {
        return $value !== null && $value !== '' && is_numeric($value);
    }
}
