<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\AbsenMasukStoreRequest;
use App\Models\AbsenSiswa;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AbsenMasukController extends Controller
{
    public function index(Request $request): View
    {
        Log::channel('sis')->info('[AbsenMasuk] Halaman absen masuk', [
            'user_id' => $request->user()->id,
        ]);

        return view('siswa.absen.masuk');
    }

    public function store(AbsenMasukStoreRequest $request): RedirectResponse
    {
        Log::channel('sis')->info('[AbsenMasuk] Mulai proses absen masuk', [
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
        ]);

        $validated = $request->validated();

        $siswa = $request->user()->siswa;
        if (! $siswa) {
            Log::channel('sis')->warning('[AbsenMasuk] Siswa tidak ditemukan', [
                'user_id' => $request->user()->id,
            ]);

            return back()->withErrors(['error' => 'Data siswa tidak ditemukan']);
        }

        $tanggal = now()->toDateString();

        // Cek sudah absen masuk hari ini
        $sudahAbsen = AbsenSiswa::where('siswa_id', $siswa->id)
            ->where('tanggal', $tanggal)
            ->where('jenis', 'masuk')
            ->exists();

        if ($sudahAbsen) {
            Log::channel('sis')->warning('[AbsenMasuk] Sudah absen masuk hari ini', [
                'siswa_id' => $siswa->id,
                'tanggal' => $tanggal,
            ]);

            return back()->withErrors(['error' => 'Anda sudah absen masuk hari ini']);
        }

        // Hitung jarak ke sekolah
        $jarak = GeolocationService::hitungJarak(
            $validated['latitude'],
            $validated['longitude'],
            config('sekolah.latitude'),
            config('sekolah.longitude')
        );

        if ($jarak > config('sekolah.radius_m')) {
            Log::channel('sis')->warning('[AbsenMasuk] Jarak terlalu jauh', [
                'siswa_id' => $siswa->id,
                'jarak' => $jarak,
                'max_radius' => config('sekolah.radius_m'),
            ]);

            return back()->withErrors(['error' => 'Lokasi Anda terlalu jauh dari sekolah']);
        }

        // Upload foto
        $fotoPath = $request->file('foto_selfie')->store('absen-selfie', 'public');

        try {
            $absen = AbsenSiswa::create([
                'siswa_id' => $siswa->id,
                'kelas_id' => $siswa->kelas_id,
                'tanggal' => $tanggal,
                'jenis' => 'masuk',
                'status' => 'hadir',
                'waktu_absen' => now(),
                'foto_selfie' => $fotoPath,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'jarak_meter' => $jarak,
            ]);

            Log::channel('sis')->info('[AbsenMasuk] Berhasil absen masuk', [
                'absen_id' => $absen->id,
                'siswa_id' => $siswa->id,
            ]);

            // Dispatch job kirim WA ke ortu
            SendAbsenMasukNotif::dispatch($absen);

            return back()->with('success', 'Absen masuk berhasil!');
        } catch (\Exception $e) {
            Log::channel('sis')->error('[AbsenMasuk] Gagal simpan absen', [
                'siswa_id' => $siswa->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan, silakan coba lagi']);
        }
    }

    public function distanceCheck(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $jarak = GeolocationService::hitungJarak(
            $request->latitude,
            $request->longitude,
            config('sekolah.latitude'),
            config('sekolah.longitude')
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

        $sudahMasuk = AbsenSiswa::where('siswa_id', $siswa->id)
            ->where('tanggal', $tanggal)
            ->where('jenis', 'masuk')
            ->exists();

        $sudahPulang = AbsenSiswa::where('siswa_id', $siswa->id)
            ->where('tanggal', $tanggal)
            ->where('jenis', 'pulang')
            ->exists();

        return response()->json([
            'sudahMasuk' => $sudahMasuk,
            'sudahPulang' => $sudahPulang,
        ]);
    }

    public function manual(Request $request): RedirectResponse
    {
        // Untuk admin/BK manual entry
        $this->authorize('create', AbsenSiswa::class);

        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:hadir,sakit,izin, alfa',
            'catatan' => 'nullable|string|max:500',
        ]);

        $siswa = Siswa::find($validated['siswa_id']);

        AbsenSiswa::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => $siswa->kelas_id,
            'tanggal' => $validated['tanggal'],
            'jenis' => 'masuk',
            'status' => $validated['status'],
            'waktu_absen' => now(),
            'catatan' => $validated['catatan'],
            'diverifikasi_oleh' => $request->user()->id,
        ]);

        Log::channel('sis')->info('[AbsenMasuk] Manual entry berhasil', [
            'siswa_id' => $siswa->id,
            'status' => $validated['status'],
            'user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Absen manual berhasil ditambahkan');
    }
}
