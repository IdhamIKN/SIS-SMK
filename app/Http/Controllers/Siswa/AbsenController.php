<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AbsenSiswa;
use App\Models\PenugasanPkl;
use App\Models\Siswa;
use App\Services\AbsenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * AbsenController — pola unified (1 record per siswa per hari).
 *
 * Absen masuk  → firstOrCreate record → update kolom _masuk
 * Absen pulang → find record yang ada → update kolom _pulang
 */
class AbsenController extends Controller
{
    public function __construct(protected AbsenService $absenService) {}

    // ──────────────────────────────────────────────────────────────────────
    // Halaman Utama
    // ──────────────────────────────────────────────────────────────────────

    public function index(Request $request): View|RedirectResponse
    {
        Log::channel('sis')->info('[Absen] Halaman absen unified', [
            'user_id' => $request->user()->id,
        ]);

        $siswa = $request->user()->siswa()->firstOrFail();

        // ── Redirect ke absensi PKL jika siswa sedang aktif PKL hari ini ──
        $tanggal = now()->toDateString();
        $sedangPkl = PenugasanPkl::where('siswa_id', $siswa->id)
            ->where('status', 'aktif')
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal)
            ->exists();

        if ($sedangPkl) {
            return redirect()->route('siswa.pkl.absen.index');
        }

        $status             = $this->absenService->statusHariIni($siswa->id, $tanggal);
        $shift              = $this->absenService->getShiftPagi();
        $lokasiSekolah      = $this->absenService->getLokasiSekolah();
        $radiusAbsensi      = $this->absenService->getRadiusAbsensi();
        $jadwalAbsensiLabel = $this->absenService->getJadwalAktifLabel();

        return view('siswa.absen.index', compact(
            'status',
            'shift',
            'lokasiSekolah',
            'radiusAbsensi',
            'jadwalAbsensiLabel'
        ));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Store (masuk / pulang)
    // ──────────────────────────────────────────────────────────────────────

    public function store(Request $request, string $jenis): JsonResponse
    {
        $jenisUcase = ucfirst($jenis);

        Log::channel('sis')->info("[Absen {$jenisUcase}] Mulai", [
            'user_id'  => $request->user()->id,
            'ip'       => $request->ip(),
            'jenis'    => $jenis,
            'lat'      => $request->input('latitude') ?? 'N/A',
            'lng'      => $request->input('longitude') ?? 'N/A',
            'now'      => now()->format('Y-m-d H:i:s'),
            'has_foto' => $request->hasFile('foto_selfie'),
        ]);

        try {
            // 1. Validasi input
            $validated = $this->absenService->validateAbsenRequest($request);

            // 2. Pastikan siswa ada
            $siswa = $request->user()->siswa;
            if (! $siswa) {
                return response()->json(['success' => false, 'error' => 'Data siswa tidak ditemukan'], 422);
            }

            // 3. Validasi jenis
            if (! in_array($jenis, ['masuk', 'pulang'], true)) {
                return response()->json(['success' => false, 'error' => 'Jenis absen tidak valid'], 422);
            }

            // 4. Shift & izin pulang cepat
            $tanggal          = now()->toDateString();
            $shift            = $this->absenService->getShiftPagi();
            $bolehPulangCepat = $jenis === 'pulang'
                && $this->absenService->punyaIzinPulangCepatDisetujui($siswa->id, $tanggal);

            // 5. Validasi waktu
            $errorWaktu = $this->absenService->validasiWaktu($jenis, $bolehPulangCepat, $shift);
            if ($errorWaktu) {
                return response()->json(['success' => false, 'error' => $errorWaktu], 422);
            }

            // 6. Validasi kondisi (sudah masuk/pulang?)
            $statusHariIni = $this->absenService->statusHariIni($siswa->id, $tanggal);
            $errorKondisi  = $this->absenService->validasiKondisiAbsen(
                $jenis,
                $statusHariIni['sudahMasuk'],
                $statusHariIni['sudahPulang']
            );
            if ($errorKondisi) {
                return response()->json(['success' => false, 'error' => $errorKondisi], 422);
            }

            // 7. Hitung jarak
            $jarak         = $this->absenService->hitungJarakSekolah(
                (float) $validated['latitude'],
                (float) $validated['longitude']
            );
            $radiusAbsensi = $this->absenService->getRadiusAbsensi();

            if ($jarak > $radiusAbsensi) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Lokasi terlalu jauh dari sekolah ('
                        . round($jarak) . 'm, maksimal ' . $radiusAbsensi . 'm)',
                ], 422);
            }

            // 8. Simpan foto
            $fotoPath = $this->absenService->simpanFotoSelfie($request->file('foto_selfie'));

            // 9. Simpan / update record unified
            if ($jenis === 'masuk') {
                $statusMasuk = $this->absenService->tentukanStatusMasuk(now());

                $absen = $this->absenService->simpanAbsenMasuk(
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
                $absen = $this->absenService->simpanAbsenPulang(
                    $siswa->id,
                    $tanggal,
                    $fotoPath,
                    (float) $validated['latitude'],
                    (float) $validated['longitude'],
                    $jarak
                );
            }

            Log::channel('sis')->info("[Absen {$jenisUcase}] SUKSES UPDATE", ['id' => $absen->id]);

            // 10. Dispatch notifikasi (terisolasi dari response)
            try {
                $jobClass = "App\\Jobs\\SendAbsen{$jenisUcase}Notif";
                if (class_exists($jobClass)) {
                    $jobClass::dispatch($absen);
                }
            } catch (\Exception $jobException) {
                Log::channel('sis')->error("[Absen {$jenisUcase}] Job dispatch GAGAL", [
                    'error' => $jobException->getMessage(),
                ]);
            }

            return response()->json([
                'success'  => true,
                'message'  => "Absen {$jenisUcase} berhasil!",
                'redirect' => route('absen.index'),
            ]);
        } catch (ValidationException $e) {
            Log::channel('sis')->error("[Absen {$jenisUcase}] VALIDATION FAIL", ['errors' => $e->errors()]);
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('sis')->error("[Absen {$jenisUcase}] EXCEPTION", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'error' => 'Absen gagal: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // API helpers
    // ──────────────────────────────────────────────────────────────────────

    public function distanceCheck(Request $request)
    {
        $validated = $this->absenService->validateDistanceRequest($request);

        $jarak         = $this->absenService->hitungJarakSekolah(
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );
        $radiusAbsensi = $this->absenService->getRadiusAbsensi();

        return response()->json([
            'valid'  => $jarak <= $radiusAbsensi,
            'jarak'  => round($jarak),
            'radius' => $radiusAbsensi,
        ]);
    }

    public function statusHariIni(Request $request)
    {
        $siswa   = $request->user()->siswa()->firstOrFail();
        $tanggal = now()->toDateString();

        return response()->json($this->absenService->statusHariIni($siswa->id, $tanggal));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Manual Entry (Admin/BK/Wali Kelas)
    // ──────────────────────────────────────────────────────────────────────

    public function manual(Request $request, string $jenis): RedirectResponse
    {
        $this->authorize('create', AbsenSiswa::class);

        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal'  => 'required|date',
            'status'   => 'required|in:hadir,terlambat,sakit,izin,alfa',
            'catatan'  => 'nullable|string|max:500',
        ]);

        $siswa = Siswa::findOrFail($validated['siswa_id']);

        $absen = AbsenSiswa::firstOrCreate(
            ['siswa_id' => $siswa->id, 'tanggal' => $validated['tanggal']],
            ['kelas_id' => $siswa->kelas_id]
        );

        if ($jenis === 'masuk') {
            $absen->update([
                'jam_masuk'          => now()->format('H:i:s'),
                'status_masuk'       => $validated['status'],
                'catatan'            => $validated['catatan'],
                'diverifikasi_oleh'  => $request->user()->id,
                // legacy
                'jenis'              => 'masuk',
                'status'             => $validated['status'],
                'waktu_absen'        => now(),
            ]);
        } else {
            $absen->update([
                'jam_pulang'         => now()->format('H:i:s'),
                'status_pulang'      => $validated['status'],
                'catatan'            => $validated['catatan'],
                'diverifikasi_oleh'  => $request->user()->id,
            ]);
        }

        return back()->with('success', "Absen manual {$jenis} berhasil");
    }

    public function updateManual(Request $request, AbsenSiswa $absen): RedirectResponse
    {
        if (! $request->user()->hasAnyRole(['superadmin', 'admin_tatib', 'bk', 'wali_kelas'])) {
            abort(403);
        }

        $validated = $request->validate([
            'status'      => 'required|in:hadir,terlambat,sakit,izin,alfa',
            'waktu_absen' => 'nullable|date',
            'catatan'     => 'nullable|string|max:500',
        ]);

        $waktuAbsen = filled($validated['waktu_absen'] ?? null)
            ? Carbon::parse($validated['waktu_absen'], config('app.timezone', 'Asia/Jakarta'))
            : null;

        // Update kolom masuk sebagai representasi utama
        $absen->update([
            'status_masuk'      => $validated['status'],
            'jam_masuk'         => $waktuAbsen?->format('H:i:s') ?? $absen->jam_masuk,
            'status'            => $validated['status'],  // legacy
            'tanggal'           => $waktuAbsen?->toDateString() ?? $absen->tanggal,
            'waktu_absen'       => $waktuAbsen ?? $absen->waktu_absen,  // legacy
            'catatan'           => $validated['catatan'] ?? null,
            'diverifikasi_oleh' => $request->user()->id,
        ]);

        Log::channel('sis')->info('[Absen] Manual update berhasil', [
            'absen_id' => $absen->id,
            'siswa_id' => $absen->siswa_id,
            'status'   => $validated['status'],
            'user_id'  => $request->user()->id,
        ]);

        return back()->with('success', 'Data absen berhasil diperbarui.');
    }
}
