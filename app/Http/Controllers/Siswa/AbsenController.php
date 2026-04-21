<?php

namespace App\Http\Controllers\Siswa;

use App\Models\AbsenSiswa;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use App\Services\AbsenService;

class AbsenController extends Controller
{
    public function __construct(protected AbsenService $absenService)
    {
    }

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

    public function store(Request $request, string $jenis): RedirectResponse
    {
        $jenisUcase = ucfirst($jenis);
        Log::channel('sis')->info("[Absen {$jenisUcase}] Mulai proses", [
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
            'jenis' => $jenis,
        ]);

        $validated = $this->absenService->validateAbsenRequest($request);

        $siswa = $request->user()->siswa;
        if (!$siswa) {
            return back()->withErrors(['error' => 'Data siswa tidak ditemukan']);
        }

        $tanggal = now()->toDateString();
        $shift = $this->absenService->getShiftPagi();
        $bolehPulangCepat = $jenis === 'pulang'
            && $this->absenService->punyaIzinPulangCepatDisetujui($siswa->id, $tanggal);

        // Validasi waktu
        if (!in_array($jenis, ['masuk', 'pulang'], true)) {
            return back()->withErrors(['error' => 'Jenis absen tidak valid']);
        }
        $errorWaktu = $this->absenService->validasiWaktu($jenis, $bolehPulangCepat, $shift);
        if ($errorWaktu) {
            return back()->withErrors(['error' => $errorWaktu]);
        }

        // Cek status absen
        $statusHariIni = $this->absenService->statusHariIni($siswa->id, $tanggal);
        $errorKondisi = $this->absenService->validasiKondisiAbsen(
            $jenis,
            $statusHariIni['sudahMasuk'],
            $statusHariIni['sudahPulang']
        );
        if ($errorKondisi) {
            return back()->withErrors(['error' => $errorKondisi]);
        }

        // Cek radius
        $jarak = $this->absenService->hitungJarakSekolah(
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );

        if ($jarak > config('sekolah.radius_m')) {
            return back()->withErrors(['error' => 'Lokasi terlalu jauh dari sekolah (' . round($jarak) . 'm)']);
        }

        // Simpan foto
        $fotoPath = $this->absenService->simpanFotoSelfie($request->file('foto_selfie'));

        try {
            $absen = AbsenSiswa::create($this->absenService->buatDataAbsen(
                $siswa->id,
                $siswa->kelas_id,
                $tanggal,
                $jenis,
                $fotoPath,
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $jarak
            ));

            Log::channel('sis')->info("[Absen {$jenisUcase}] Berhasil", ['absen_id' => $absen->id]);
            if ($bolehPulangCepat) {
                Log::channel('sis')->info('[Absen Pulang] Bypass jam pulang karena izin pulang cepat disetujui', [
                    'siswa_id' => $siswa->id,
                    'tanggal' => $tanggal,
                    'absen_id' => $absen->id,
                ]);
            }

            // Dispatch WA job secara dinamis (masuk / pulang)
            $jobClass = "App\\Jobs\\SendAbsen{$jenisUcase}Notif";
            if (class_exists($jobClass)) {
                $jobClass::dispatch($absen);
            }

            $successMsg = "Absen {$jenisUcase} berhasil!";
            return back()->with('success', $successMsg);

        } catch (\Exception $e) {
            $this->absenService->hapusFotoSelfie($fotoPath);
            Log::channel('sis')->error("[Absen {$jenisUcase}] Gagal", [
                'siswa_id' => $siswa->id, 'error' => $e->getMessage()
            ]);
            return back()->withErrors(['error' => "Absen {$jenisUcase} gagal, coba lagi"]);
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

        $siswa = \App\Models\Siswa::find($validated['siswa_id']);

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

