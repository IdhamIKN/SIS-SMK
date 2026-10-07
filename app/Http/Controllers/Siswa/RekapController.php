<?php

namespace App\Http\Controllers\Siswa;

use App\Exports\RekapAbsenExport;
use App\Http\Controllers\Controller;
use App\Models\AbsenSiswa;
use App\Models\Kelas;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

/**
 * RekapController — pola unified (1 record per siswa per hari).
 *
 * Tabel rekap detail kehadiran menampilkan baris per record harian,
 * dengan kolom status gabungan masuk & pulang.
 */
class RekapController extends Controller
{
    private const PER_PAGE = 25;
    private const MAX_ROWS = 1000;

    // ──────────────────────────────────────────────────────────────────────
    // Helpers: batas tepat waktu & keterlambatan
    // ──────────────────────────────────────────────────────────────────────

    private function getBatasTepat(): ?Carbon
    {
        $sekolah = Sekolah::aktif();
        $raw     = $sekolah?->batas_tepat_waktu;
        if (! $raw) {
            return null;
        }
        $timestamp = strtotime((string) $raw);
        if ($timestamp === false) {
            return null;
        }
        return Carbon::today()->setTimeFromTimeString(date('H:i:s', $timestamp));
    }

    private function hitungMenitTerlambat(Carbon $waktuAbsen, ?Carbon $batasTepat): ?int
    {
        if (! $batasTepat) {
            return null;
        }
        $batasHariAbsen = Carbon::instance($waktuAbsen)->startOfDay()
            ->setTimeFromTimeString($batasTepat->format('H:i:s'));

        if ($waktuAbsen->lte($batasHariAbsen)) {
            return null;
        }
        return (int) floor($waktuAbsen->diffInSeconds($batasHariAbsen, false) * -1 / 60);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Index
    // ──────────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $hasFilter = $request->hasAny(['tanggal_mulai', 'tanggal_selesai', 'kelas_id', 'status', 'nama_siswa']);

        $tanggalMulai   = $request->get('tanggal_mulai', now()->format('Y-m-d'));
        $tanggalSelesai = $request->get('tanggal_selesai', now()->format('Y-m-d'));
        $kelasId        = $request->get('kelas_id');
        $status         = $request->get('status');
        $namaSiswa      = trim($request->get('nama_siswa', ''));

        $rekapPage = collect();
        $paginator = null;
        $stats     = [
            'total_absen_siswa' => 0,
            'hadir'             => 0,
            'terlambat'         => 0,
            'izin'              => 0,
            'sakit'             => 0,
            'alfa'              => 0,
        ];
        $kelas    = collect();
        $isSiswa  = false;
        $errorMsg = null;

        try {
            $user    = $request->user();
            $isSiswa = $user->hasRole('siswa');

            if ($isSiswa && ! $user->siswa?->id) {
                return view('siswa.rekap.index', compact(
                    'rekapPage',
                    'paginator',
                    'stats',
                    'kelas',
                    'tanggalMulai',
                    'tanggalSelesai',
                    'kelasId',
                    'status',
                    'namaSiswa',
                    'isSiswa',
                    'errorMsg',
                    'hasFilter'
                ));
            }

            // ── Query utama: 1 record per siswa per hari ─────────────────
            $absenQuery = AbsenSiswa::with([
                'siswa:id,nis,nama_lengkap,kelas_id',
                'siswa.kelas:id,nama_kelas,jurusan_id',
                'siswa.kelas.jurusan:id,nama_jurusan',
            ])
                ->whereHas('siswa')
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);

            if ($isSiswa) {
                $absenQuery->where('siswa_id', $user->siswa->id);
            } elseif ($user->hasRole('wali_kelas')) {
                $kelasWali = $user->gtk?->kelasWali()->pluck('id') ?? collect();
                if ($kelasWali->isNotEmpty()) {
                    $absenQuery->whereIn('kelas_id', $kelasWali);
                }
            }

            if ($kelasId) {
                $absenQuery->where('kelas_id', $kelasId);
            }

            if ($namaSiswa !== '') {
                $absenQuery->whereHas('siswa', function ($q) use ($namaSiswa) {
                    $q->where('nama_lengkap', 'LIKE', '%' . $namaSiswa . '%');
                });
            }

            // Filter status menggunakan kolom status_masuk (utama) atau status (legacy)
            if ($status) {
                $absenQuery->where(function ($q) use ($status) {
                    $q->where('status_masuk', $status)
                        ->orWhere(function ($q2) use ($status) {
                            $q2->whereNull('status_masuk')->where('status', $status);
                        });
                });
            }

            // ── Stats ─────────────────────────────────────────────────────
            $statsRaw = (clone $absenQuery)
                ->selectRaw("
                    COUNT(*) AS total,
                    SUM(CASE WHEN COALESCE(status_masuk, status) = 'hadir'     THEN 1 ELSE 0 END) AS hadir,
                    SUM(CASE WHEN COALESCE(status_masuk, status) = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                    SUM(CASE WHEN COALESCE(status_masuk, status) = 'izin'      THEN 1 ELSE 0 END) AS izin,
                    SUM(CASE WHEN COALESCE(status_masuk, status) = 'sakit'     THEN 1 ELSE 0 END) AS sakit,
                    SUM(CASE WHEN COALESCE(status_masuk, status) = 'alfa'      THEN 1 ELSE 0 END) AS alfa
                ")
                ->first();

            $stats = [
                'total_absen_siswa' => (int) ($statsRaw->total ?? 0),
                'hadir'             => (int) ($statsRaw->hadir ?? 0),
                'terlambat'         => (int) ($statsRaw->terlambat ?? 0),
                'izin'              => (int) ($statsRaw->izin ?? 0),
                'sakit'             => (int) ($statsRaw->sakit ?? 0),
                'alfa'              => (int) ($statsRaw->alfa ?? 0),
            ];

            $absenSiswa = $absenQuery
                ->orderBy('tanggal', 'desc')
                ->limit(self::MAX_ROWS)
                ->get();

            $rekapAll = $this->buatDataRekap($absenSiswa);

            $page      = max(1, (int) $request->get('page', 1));
            $perPage   = self::PER_PAGE;
            $total     = $rekapAll->count();
            $rekapPage = $rekapAll->slice(($page - 1) * $perPage, $perPage)->values();

            $paginator = new LengthAwarePaginator(
                $rekapPage,
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            $kelas = Kelas::with('jurusan:id,nama_jurusan')
                ->orderBy('nama_kelas')
                ->get(['id', 'nama_kelas', 'jurusan_id']);
        } catch (\Throwable $e) {
            Log::channel('sis')->error('[RekapAbsen] FATAL', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            $errorMsg = 'Terjadi kesalahan saat memuat data. Silakan coba lagi atau hubungi admin.';
        }

        return view('siswa.rekap.index', compact(
            'rekapPage',
            'paginator',
            'stats',
            'kelas',
            'tanggalMulai',
            'tanggalSelesai',
            'kelasId',
            'status',
            'namaSiswa',
            'isSiswa',
            'errorMsg',
            'hasFilter'
        ));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Build rekap data (1 row per record harian)
    // ──────────────────────────────────────────────────────────────────────

    private function buatDataRekap($absenSiswa): \Illuminate\Support\Collection
    {
        $rekap      = collect();
        $batasTepat = $this->getBatasTepat();

        foreach ($absenSiswa as $absen) {
            // Tentukan status utama: gunakan status_masuk jika ada, fallback status
            $statusMasuk  = $absen->status_masuk ?? $absen->status ?? 'alfa';
            $statusPulang = $absen->status_pulang;

            // Waktu masuk
            $jamMasuk = null;
            if (! empty($absen->jam_masuk)) {
                $jamMasuk = Carbon::parse($absen->tanggal->format('Y-m-d') . ' ' . $absen->jam_masuk);
            } elseif ($absen->waktu_absen) {
                // legacy fallback
                $jamMasuk = Carbon::parse($absen->waktu_absen);
            }

            // Waktu pulang
            $jamPulang = null;
            if (! empty($absen->jam_pulang)) {
                $jamPulang = Carbon::parse($absen->tanggal->format('Y-m-d') . ' ' . $absen->jam_pulang);
            }

            // Hitung menit terlambat
            $menitTerlambat = null;
            if ($jamMasuk && in_array($statusMasuk, ['hadir', 'terlambat'])) {
                $menitTerlambat = $this->hitungMenitTerlambat($jamMasuk, $batasTepat);
            }

            // Lokasi masuk
            $lokasiMasuk = null;
            if ($absen->latitude_masuk && $absen->longitude_masuk) {
                $lokasiMasuk = $absen->latitude_masuk . ', ' . $absen->longitude_masuk;
            } elseif ($absen->latitude && $absen->longitude) {
                // legacy
                $lokasiMasuk = $absen->latitude . ', ' . $absen->longitude;
            }

            $rekap->push([
                'id'              => $absen->id,
                'tipe'            => 'absen_siswa',
                'tanggal'         => $absen->tanggal,
                // Waktu referensi untuk sorting: pakai jam_masuk atau tanggal
                'waktu'           => $jamMasuk ?? Carbon::parse($absen->tanggal),
                'siswa'           => $absen->siswa,
                'kelas'           => $absen->siswa?->kelas,
                // Masuk
                'jam_masuk'       => $jamMasuk,
                'status_masuk'    => $statusMasuk,
                // Pulang
                'jam_pulang'      => $jamPulang,
                'status_pulang'   => $statusPulang,
                // Keterlambatan
                'menit_terlambat' => $menitTerlambat,
                // Keterangan umum
                'catatan'         => $absen->catatan,
                // Lokasi & foto masuk
                'lokasi_masuk'    => $lokasiMasuk,
                'latitude_masuk'  => $absen->latitude_masuk ?? $absen->latitude,
                'longitude_masuk' => $absen->longitude_masuk ?? $absen->longitude,
                'foto_masuk'      => $absen->foto_selfie_masuk ?? $absen->foto_selfie,
                // URL edit untuk admin
                'edit_url'        => route('admin.absen-manual.edit', $absen->id),
                // Lokasi & foto pulang
                'lokasi_pulang'   => ($absen->latitude_pulang && $absen->longitude_pulang)
                    ? $absen->latitude_pulang . ', ' . $absen->longitude_pulang
                    : null,
                'latitude_pulang'  => $absen->latitude_pulang,
                'longitude_pulang' => $absen->longitude_pulang,
                'foto_pulang'      => $absen->foto_selfie_pulang,
            ]);
        }

        return $rekap->sortByDesc('waktu')->values();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Export
    // ──────────────────────────────────────────────────────────────────────

    public function export(Request $request)
    {
        $tanggalMulai   = $request->get('tanggal_mulai', now()->format('Y-m-d'));
        $tanggalSelesai = $request->get('tanggal_selesai', now()->format('Y-m-d'));
        $kelasId        = $request->get('kelas_id');
        $status         = $request->get('status');

        return Excel::download(
            new RekapAbsenExport($tanggalMulai, $tanggalSelesai, $kelasId, $status),
            'rekap_absen_' . $tanggalMulai . '_sd_' . $tanggalSelesai . '.xlsx'
        );
    }
}
