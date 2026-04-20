<?php

namespace App\Http\Controllers\Siswa;

use App\Models\AbsenSiswa;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use App\Services\GeolocationService;
use App\Jobs\SendAbsenMasukNotif;
use App\Jobs\SendAbsenPulangNotif;

class AbsenController extends Controller
{
    public function index(Request $request): View
    {
        Log::channel('sis')->info('[Absen] Halaman absen unified', [
            'user_id' => $request->user()->id,
        ]);

        $siswa = $request->user()->siswa;
        $tanggal = now()->toDateString();

        $status = [
            'sudahMasuk' => AbsenSiswa::where('siswa_id', $siswa->id)
                ->where('tanggal', $tanggal)->where('jenis', 'masuk')->exists(),
            'sudahPulang' => AbsenSiswa::where('siswa_id', $siswa->id)
                ->where('tanggal', $tanggal)->where('jenis', 'pulang')->exists(),
        ];

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

        $request->validate([
            'foto_selfie' => 'required|image|max:2048',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $siswa = $request->user()->siswa;
        if (!$siswa) {
            return back()->withErrors(['error' => 'Data siswa tidak ditemukan']);
        }

        $tanggal = now()->toDateString();
        $shift = config('sekolah.jam_shift.pagi');

        // Validasi waktu
        $now = now();
        if ($jenis === 'masuk') {
            if ($now->lt($shift['masuk']) || $now->gt($shift['limit_masuk'])) {
                $waktuValid = date('H:i', strtotime($shift['masuk'])) . ' - ' . date('H:i', strtotime($shift['limit_masuk']));
                return back()->withErrors(['error' => "Waktu absen masuk tidak sesuai ({$waktuValid})"]);
            }
        } elseif ($jenis === 'pulang') {
            if ($now->lt($shift['pulang']) || $now->gt($shift['limit_pulang'])) {
                $waktuValid = date('H:i', strtotime($shift['pulang'])) . ' - ' . date('H:i', strtotime($shift['limit_pulang']));
                return back()->withErrors(['error' => "Waktu absen pulang tidak sesuai ({$waktuValid})"]);
            }
        } else {
            return back()->withErrors(['error' => 'Jenis absen tidak valid']);
        }

        // Cek status absen
        $sudahMasuk = AbsenSiswa::where('siswa_id', $siswa->id)
            ->where('tanggal', $tanggal)->where('jenis', 'masuk')->exists();

        $sudahPulang = AbsenSiswa::where('siswa_id', $siswa->id)
            ->where('tanggal', $tanggal)->where('jenis', 'pulang')->exists();

        if ($jenis === 'masuk' && $sudahMasuk) {
            return back()->withErrors(['error' => 'Anda sudah absen masuk hari ini']);
        }

        if ($jenis === 'pulang') {
            if (!$sudahMasuk) {
                return back()->withErrors(['error' => 'Harus absen masuk dulu sebelum pulang']);
            }
            if ($sudahPulang) {
                return back()->withErrors(['error' => 'Anda sudah absen pulang hari ini']);
            }
        }

        // Cek radius
        $jarak = GeolocationService::hitungJarak(
            $request->latitude, $request->longitude,
            config('sekolah.latitude'), config('sekolah.longitude')
        );

        if ($jarak > config('sekolah.radius_m')) {
            return back()->withErrors(['error' => 'Lokasi terlalu jauh dari sekolah (' . round($jarak) . 'm)']);
        }

        // Simpan foto
        $fotoPath = $request->file('foto_selfie')->store('absen-selfie', 'public');

        try {
            $absen = AbsenSiswa::create([
                'siswa_id' => $siswa->id,
                'kelas_id' => $siswa->kelas_id,
                'tanggal' => $tanggal,
                'jenis' => $jenis,
                'status' => 'hadir',
                'waktu_absen' => now(),
                'foto_selfie' => $fotoPath,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'jarak_meter' => $jarak,
            ]);

            Log::channel('sis')->info("[Absen {$jenisUcase}] Berhasil", ['absen_id' => $absen->id]);

            // Dispatch WA job
            $jobClass = "App\\Jobs\\SendAbsen{$jenisUcase}Notif";
            if (class_exists($jobClass)) {
                $jobClass::dispatch($absen);
            }

            $successMsg = "Absen {$jenisUcase} berhasil!";
            return back()->with('success', $successMsg);

        } catch (\Exception $e) {
            Log::channel('sis')->error("[Absen {$jenisUcase}] Gagal", [
                'siswa_id' => $siswa->id, 'error' => $e->getMessage()
            ]);
            return back()->withErrors(['error' => "Absen {$jenisUcase} gagal, coba lagi"]);
        }
    }

    public function distanceCheck(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $jarak = GeolocationService::hitungJarak(
            $request->latitude, $request->longitude,
            config('sekolah.latitude'), config('sekolah.longitude')
        );

        return response()->json([
            'valid' => $jarak <= config('sekolah.radius_m'),
            'jarak' => round($jarak),
        ]);
    }

    public function statusHariIni(Request $request)
    {
        $siswa = $request->user()->siswa;
        $tanggal = now()->toDateString();

        return response()->json([
            'sudahMasuk' => AbsenSiswa::where('siswa_id', $siswa->id)
                ->where('tanggal', $tanggal)->where('jenis', 'masuk')->exists(),
            'sudahPulang' => AbsenSiswa::where('siswa_id', $siswa->id)
                ->where('tanggal', $tanggal)->where('jenis', 'pulang')->exists(),
        ]);
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

