<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AbsenSiswa;
use App\Models\Siswa;
use App\Services\AbsenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AbsenController extends Controller
{
    public function __construct(protected AbsenService $absenService) {}

    public function index(Request $request): View
    {
        Log::channel('sis')->info('[Absen] Halaman absen unified', [
            'user_id' => $request->user()->id,
        ]);

        $siswa = $request->user()->siswa()->firstOrFail();
        $tanggal = now()->toDateString();
        $status = $this->absenService->statusHariIni($siswa->id, $tanggal);

        return view('siswa.absen.index', compact('status'));
    }

    public function store(Request $request, string $jenis): JsonResponse
    {
        $jenisUcase = ucfirst($jenis);
        Log::channel('sis')->info("[Absen {$jenisUcase}] Mulai DEBUG", [
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
            'jenis' => $jenis,
            'lat' => $request->input('latitude') ?? 'N/A',
            'lng' => $request->input('longitude') ?? 'N/A',
            'now' => now()->format('Y-m-d H:i:s'),
            'has_foto' => $request->hasFile('foto_selfie'),
        ]);

        try {
            $validated = $this->absenService->validateAbsenRequest($request);

            $siswa = $request->user()->siswa;
            if (! $siswa) {
                return response()->json(['success' => false, 'error' => 'Data siswa tidak ditemukan'], 422);
            }

            $tanggal = now()->toDateString();
            $shift = $this->absenService->getShiftPagi();
            $bolehPulangCepat = $jenis === 'pulang'
                && $this->absenService->punyaIzinPulangCepatDisetujui($siswa->id, $tanggal);

            if (! in_array($jenis, ['masuk', 'pulang'], true)) {
                return response()->json(['success' => false, 'error' => 'Jenis absen tidak valid'], 422);
            }

            $errorWaktu = $this->absenService->validasiWaktu($jenis, $bolehPulangCepat, $shift);
            if ($errorWaktu) {
                return response()->json(['success' => false, 'error' => $errorWaktu], 422);
            }

            $statusHariIni = $this->absenService->statusHariIni($siswa->id, $tanggal);
            $errorKondisi = $this->absenService->validasiKondisiAbsen(
                $jenis,
                $statusHariIni['sudahMasuk'],
                $statusHariIni['sudahPulang']
            );
            if ($errorKondisi) {
                return response()->json(['success' => false, 'error' => $errorKondisi], 422);
            }

            $jarak = $this->absenService->hitungJarakSekolah(
                (float) $validated['latitude'],
                (float) $validated['longitude']
            );

            if ($jarak > config('sekolah.radius_m')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Lokasi terlalu jauh dari sekolah ('.round($jarak).'m)',
                ], 422);
            }

            $fotoPath = $this->absenService->simpanFotoSelfie($request->file('foto_selfie'));
            $dataAbsen = $this->absenService->buatDataAbsen(
                $siswa->id,
                $siswa->kelas_id,
                $tanggal,
                $jenis,
                $fotoPath,
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $jarak
            );

            $absen = AbsenSiswa::create($dataAbsen);
            Log::channel('sis')->info("[Absen {$jenisUcase}] SUKSES CREATE", ['id' => $absen->id]);

            $jobClass = "App\\Jobs\\SendAbsen{$jenisUcase}Notif";
            if (class_exists($jobClass)) {
                $jobClass::dispatch($absen);
            }

            return response()->json([
                'success' => true,
                'message' => "Absen {$jenisUcase} berhasil!",
                'redirect' => route('absen.index'),   // <-- sesuaikan nama route
            ]);
        } catch (ValidationException $e) {
            Log::channel('sis')->error("[Absen {$jenisUcase}] VALIDATION FAIL", ['errors' => $e->errors()]);

            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('sis')->error("[Absen {$jenisUcase}] EXCEPTION", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'error' => 'Absen gagal: '.$e->getMessage()], 500);
        }
    }

    public function distanceCheck(Request $request)
    {
        $validated = $this->absenService->validateDistanceRequest($request);

        $jarak = $this->absenService->hitungJarakSekolah(
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );

        return response()->json([
            'valid' => $jarak <= config('sekolah.radius_m'),
            'jarak' => round($jarak),
        ]);
    }

    public function statusHariIni(Request $request)
    {
        $siswa = $request->user()->siswa()->firstOrFail();
        $tanggal = now()->toDateString();

        return response()->json($this->absenService->statusHariIni($siswa->id, $tanggal));
    }

    public function manual(Request $request, string $jenis): RedirectResponse
    {
        $this->authorize('create', AbsenSiswa::class);

        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:hadir,sakit,izin,alfa',
            'catatan' => 'nullable|string|max:500',
        ]);

        $siswa = Siswa::find($validated['siswa_id']);

        AbsenSiswa::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => $siswa->kelas_id,
            'tanggal' => $validated['tanggal'],
            'jenis' => $jenis,
            'status' => $validated['status'],
            'waktu_absen' => now(),
            'catatan' => $validated['catatan'],
            'diverifikasi_oleh' => $request->user()->id,
        ]);

        return back()->with('success', "Absen manual {$jenis} berhasil");
    }
}
