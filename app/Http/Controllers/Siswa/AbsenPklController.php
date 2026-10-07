<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PenugasanPkl;
use App\Services\AbsenPklService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * AbsenPklController — absensi selfie + GPS untuk siswa yang sedang PKL.
 *
 * Alur:
 *  GET  /pkl/absen         → tampilkan halaman absensi PKL (kamera + map lokasi PKL)
 *  POST /pkl/absen/{jenis} → proses absen masuk/pulang PKL
 *  GET  /pkl/absen/status  → API: status absensi PKL hari ini (polling)
 *
 * Validasi dilakukan di AbsenPklService:
 *  - Siswa harus punya penugasan PKL aktif hari ini
 *  - GPS vs koordinat LokasiPkl (bukan sekolah)
 *  - Jam vs jam_masuk_pkl / jam_pulang_pkl dari LokasiPkl
 */
class AbsenPklController extends Controller
{
    public function __construct(protected AbsenPklService $absenPklService) {}

    // ──────────────────────────────────────────────────────────────────────
    // Halaman Utama Absensi PKL
    // ──────────────────────────────────────────────────────────────────────

    public function index(Request $request): View|RedirectResponse
    {
        $siswa = $request->user()->siswa;

        if (! $siswa) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => 'Data siswa tidak ditemukan.']);
        }

        $penugasan = $this->getPenugasanAktifHariIni($siswa->id);

        if (! $penugasan) {
            return redirect()->route('absen.index')
                ->withErrors(['error' => 'Kamu tidak memiliki penugasan PKL aktif hari ini.']);
        }

        $lokasi  = $penugasan->lokasiPkl;
        $tanggal = now()->toDateString();
        $status  = $this->absenPklService->statusHariIni($siswa->id, $tanggal);
        $jadwal  = $this->absenPklService->getJadwalPkl($lokasi);
        $radius  = $this->absenPklService->getRadiusLokasi($lokasi);

        return view('siswa.pkl.absen.index', compact(
            'penugasan',
            'lokasi',
            'status',
            'jadwal',
            'radius'
        ));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Store (masuk / pulang PKL)
    // ──────────────────────────────────────────────────────────────────────

    public function store(Request $request, string $jenis): JsonResponse
    {
        $jenisUcase = ucfirst($jenis);

        Log::channel('sis')->info("[AbsenPKL {$jenisUcase}] Mulai", [
            'user_id'  => $request->user()->id,
            'ip'       => $request->ip(),
            'jenis'    => $jenis,
            'lat'      => $request->input('latitude') ?? 'N/A',
            'lng'      => $request->input('longitude') ?? 'N/A',
            'now'      => now()->format('Y-m-d H:i:s'),
            'has_foto' => $request->hasFile('foto_selfie'),
        ]);

        try {
            // 1. Validasi input (foto + GPS)
            $validated = $this->absenPklService->validateAbsenRequest($request);

            // 2. Pastikan siswa ada
            $siswa = $request->user()->siswa;
            if (! $siswa) {
                return response()->json(['success' => false, 'error' => 'Data siswa tidak ditemukan'], 422);
            }

            // 3. Validasi jenis
            if (! in_array($jenis, ['masuk', 'pulang'], true)) {
                return response()->json(['success' => false, 'error' => 'Jenis absen tidak valid'], 422);
            }

            // 4. Ambil penugasan PKL aktif hari ini
            $penugasan = $this->getPenugasanAktifHariIni($siswa->id);
            if (! $penugasan) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Kamu tidak memiliki penugasan PKL aktif hari ini.',
                ], 422);
            }

            $lokasi  = $penugasan->lokasiPkl;
            $tanggal = now()->toDateString();

            // 5. Validasi lokasi PKL punya koordinat
            if (! $lokasi->latitude || ! $lokasi->longitude) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Lokasi PKL belum memiliki koordinat GPS. Hubungi admin.',
                ], 422);
            }

            // 6. Validasi waktu
            $errorWaktu = $this->absenPklService->validasiWaktuPkl($jenis, $lokasi);
            if ($errorWaktu) {
                return response()->json(['success' => false, 'error' => $errorWaktu], 422);
            }

            // 7. Validasi kondisi (sudah masuk/pulang?)
            $statusHariIni = $this->absenPklService->statusHariIni($siswa->id, $tanggal);
            $errorKondisi  = $this->absenPklService->validasiKondisi(
                $jenis,
                $statusHariIni['sudahMasuk'],
                $statusHariIni['sudahPulang']
            );
            if ($errorKondisi) {
                return response()->json(['success' => false, 'error' => $errorKondisi], 422);
            }

            // 8. Hitung jarak ke lokasi PKL
            $jarak  = $this->absenPklService->hitungJarakLokasiPkl(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $lokasi
            );
            $radius = $this->absenPklService->getRadiusLokasi($lokasi);

            if ($jarak > $radius) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Lokasi terlalu jauh dari tempat PKL ('
                        . round($jarak) . 'm, maksimal ' . $radius . 'm)',
                ], 422);
            }

            // 9. Simpan foto
            $fotoPath = $this->absenPklService->simpanFotoSelfie($request->file('foto_selfie'));

            // 10. Simpan record absensi
            if ($jenis === 'masuk') {
                $statusMasuk = $this->absenPklService->tentukanStatusMasukPkl($lokasi);

                $absen = $this->absenPklService->simpanAbsenMasukPkl(
                    $siswa->id,
                    $siswa->kelas_id,
                    $tanggal,
                    $fotoPath,
                    (float) $validated['latitude'],
                    (float) $validated['longitude'],
                    $jarak,
                    $statusMasuk
                );
            } else {
                $absen = $this->absenPklService->simpanAbsenPulangPkl(
                    $siswa->id,
                    $tanggal,
                    $fotoPath,
                    (float) $validated['latitude'],
                    (float) $validated['longitude'],
                    $jarak
                );
            }

            Log::channel('sis')->info("[AbsenPKL {$jenisUcase}] SUKSES", [
                'absen_id' => $absen->id,
                'siswa_id' => $siswa->id,
                'lokasi'   => $lokasi->nama_tempat,
            ]);

            return response()->json([
                'success'  => true,
                'message'  => "Absen {$jenisUcase} PKL berhasil!",
                'redirect' => route('siswa.pkl.absen.index'),
            ]);

        } catch (ValidationException $e) {
            Log::channel('sis')->error("[AbsenPKL {$jenisUcase}] VALIDATION FAIL", ['errors' => $e->errors()]);
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('sis')->error("[AbsenPKL {$jenisUcase}] EXCEPTION", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'error' => 'Absen gagal: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // API: status hari ini (polling dari JS)
    // ──────────────────────────────────────────────────────────────────────

    public function statusHariIni(Request $request): JsonResponse
    {
        $siswa   = $request->user()->siswa;
        $tanggal = now()->toDateString();

        if (! $siswa) {
            return response()->json(['sudahMasuk' => false, 'sudahPulang' => false]);
        }

        return response()->json(
            $this->absenPklService->statusHariIni($siswa->id, $tanggal)
        );
    }

    // ──────────────────────────────────────────────────────────────────────
    // API: cek jarak ke lokasi PKL (sebelum foto diambil)
    // ──────────────────────────────────────────────────────────────────────

    public function distanceCheck(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $siswa = $request->user()->siswa;
        if (! $siswa) {
            return response()->json(['valid' => false, 'jarak' => 0, 'radius' => 0]);
        }

        $penugasan = $this->getPenugasanAktifHariIni($siswa->id);
        if (! $penugasan || ! $penugasan->lokasiPkl?->latitude) {
            return response()->json(['valid' => false, 'jarak' => 0, 'radius' => 0]);
        }

        $lokasi = $penugasan->lokasiPkl;
        $jarak  = $this->absenPklService->hitungJarakLokasiPkl(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $lokasi
        );
        $radius = $this->absenPklService->getRadiusLokasi($lokasi);

        return response()->json([
            'valid'  => $jarak <= $radius,
            'jarak'  => round($jarak),
            'radius' => $radius,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    private function getPenugasanAktifHariIni(int $siswaId): ?PenugasanPkl
    {
        $tanggal = now()->toDateString();

        return PenugasanPkl::with('lokasiPkl')
            ->where('siswa_id', $siswaId)
            ->where('status', 'aktif')
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal)
            ->first();
    }
}
