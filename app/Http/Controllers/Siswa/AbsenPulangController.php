<?php

namespace App\Http\Controllers\Siswa;

use App\Models\AbsenSiswa;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use App\Services\GeolocationService;
use App\Services\WhatsappService;
use App\Jobs\SendAbsenPulangNotif;

class AbsenPulangController extends Controller
{
    public function index(Request $request): View
    {
        Log::channel('sis')->info('[AbsenPulang] Halaman absen pulang', [
            'user_id' => $request->user()->id,
        ]);

        return view('siswa.absen.pulang');
    }

    public function store(Request $request): RedirectResponse
    {
        Log::channel('sis')->info('[AbsenPulang] Mulai proses absen pulang', [
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
        ]);

        $request->validate([
            'foto_selfie' => 'required|image|max:2048',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $siswa = $request->user()->siswa;
        if (!$siswa) {
            Log::channel('sis')->warning('[AbsenPulang] Siswa tidak ditemukan', [
                'user_id' => $request->user()->id,
            ]);
            return back()->withErrors(['error' => 'Data siswa tidak ditemukan']);
        }

        $tanggal = now()->toDateString();

        // Cek sudah absen masuk hari ini (harus masuk dulu sebelum pulang)
        $sudahAbsenMasuk = AbsenSiswa::where('siswa_id', $siswa->id)
            ->where('tanggal', $tanggal)
            ->where('jenis', 'masuk')
            ->exists();

        if (!$sudahAbsenMasuk) {
            Log::channel('sis')->warning('[AbsenPulang] Belum absen masuk hari ini', [
                'siswa_id' => $siswa->id,
                'tanggal' => $tanggal,
            ]);
            return back()->withErrors(['error' => 'Anda harus absen masuk terlebih dahulu sebelum absen pulang']);
        }

        // Cek sudah absen pulang hari ini
        $sudahAbsenPulang = AbsenSiswa::where('siswa_id', $siswa->id)
            ->where('tanggal', $tanggal)
            ->where('jenis', 'pulang')
            ->exists();

        if ($sudahAbsenPulang) {
            Log::channel('sis')->warning('[AbsenPulang] Sudah absen pulang hari ini', [
                'siswa_id' => $siswa->id,
                'tanggal' => $tanggal,
            ]);
            return back()->withErrors(['error' => 'Anda sudah absen pulang hari ini']);
        }

        // Hitung jarak ke sekolah
        $jarak = GeolocationService::hitungJarak(
            $request->latitude,
            $request->longitude,
            config('sekolah.latitude'),
            config('sekolah.longitude')
        );

        if ($jarak > config('sekolah.radius_m')) {
            Log::channel('sis')->warning('[AbsenPulang] Jarak terlalu jauh', [
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
                'jenis' => 'pulang',
                'status' => 'hadir',
                'waktu_absen' => now(),
                'foto_selfie' => $fotoPath,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'jarak_meter' => $jarak,
            ]);

            Log::channel('sis')->info('[AbsenPulang] Berhasil absen pulang', [
                'absen_id' => $absen->id,
                'siswa_id' => $siswa->id,
            ]);

            // Dispatch job kirim WA ke ortu
            SendAbsenPulangNotif::dispatch($absen);

            return back()->with('success', 'Absen pulang berhasil!');

        } catch (\Exception $e) {
            Log::channel('sis')->error('[AbsenPulang] Gagal simpan absen', [
                'siswa_id' => $siswa->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Terjadi kesalahan, silakan coba lagi']);
        }
    }

    public function manual(Request $request): RedirectResponse
    {
        // Untuk admin/BK manual entry
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
            'jenis' => 'pulang',
            'status' => $validated['status'],
            'waktu_absen' => now(),
            'catatan' => $validated['catatan'],
            'diverifikasi_oleh' => $request->user()->id,
        ]);

        Log::channel('sis')->info('[AbsenPulang] Manual entry berhasil', [
            'siswa_id' => $siswa->id,
            'status' => $validated['status'],
            'user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Absen pulang manual berhasil ditambahkan');
    }
}