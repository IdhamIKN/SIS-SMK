<?php

namespace App\Http\Controllers\GTK;

use App\Models\LaporanKehadiranGuru;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use App\Services\GeolocationService;
use App\Services\WhatsappService;
use App\Jobs\SendLaporanKehadiranGuruNotif;

class LaporanKehadiranController extends Controller
{
    public function index(Request $request): View
    {
        Log::channel('gtk')->info('[LaporanKehadiran] Halaman laporan kehadiran GTK', [
            'user_id' => $request->user()->id,
        ]);

        $query = LaporanKehadiranGuru::with('gtk');

        // Filter berdasarkan role
        if ($request->user()->hasRole('gtk')) {
            // GTK hanya melihat laporan sendiri
            $query->where('gtk_id', $request->user()->gtk->id);
        }
        // Admin/BK bisa lihat semua

        // Filter tanggal
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        } else {
            $query->where('tanggal', now()->toDateString());
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $laporanKehadiran = $query->latest('waktu_laporan')->paginate(20);

        // Stats untuk admin/BK
        $stats = [];
        if (!$request->user()->hasRole('gtk')) {
            $tanggalFilter = $request->tanggal ?: now()->toDateString();

            $stats = [
                'hari_ini' => LaporanKehadiranGuru::where('tanggal', $tanggalFilter)->count(),
                'belum_lapor' => \App\Models\GTK::whereDoesntHave('laporanKehadiran', function($q) use ($tanggalFilter) {
                    $q->where('tanggal', $tanggalFilter);
                })->count(),
            ];
        }

        return view('gtk.laporan_kehadiran.index', compact('laporanKehadiran', 'stats'));
    }
        // Admin/BK bisa lihat semua

        // Filter tanggal
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        } else {
            $query->where('tanggal', now()->toDateString());
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $laporanKehadiran = $query->latest('waktu_laporan')->paginate(20);

        // Stats untuk admin/BK
        $stats = [];
        if (!$request->user()->hasRole('gtk')) {
            $tanggalFilter = $request->tanggal ?: now()->toDateString();

            $stats = [
                'hari_ini' => LaporanKehadiranGuru::where('tanggal', $tanggalFilter)->count(),
                'belum_lapor' => \App\Models\GTK::whereDoesntHave('laporanKehadiran', function($q) use ($tanggalFilter) {
                    $q->where('tanggal', $tanggalFilter);
                })->count(),
            ];
        }

        return view('gtk.laporan_kehadiran.index', compact('laporanKehadiran', 'stats'));
    }

    public function create(Request $request): View
    {
        Log::channel('gtk')->info('[LaporanKehadiran] Halaman buat laporan kehadiran', [
            'user_id' => $request->user()->id,
        ]);

        return view('gtk.laporan_kehadiran.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Log::channel('gtk')->info('[LaporanKehadiran] Mulai simpan laporan kehadiran', [
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
        ]);

        $request->validate([
            'jenis' => 'required|in:masuk,pulang',
            'status' => 'required|in:hadir,sakit,izin,alfa',
            'foto_selfie' => 'required_if:status,hadir|image|max:2048',
            'latitude' => 'required_if:status,hadir|numeric|between:-90,90',
            'longitude' => 'required_if:status,hadir|numeric|between:-180,180',
            'catatan' => 'nullable|string|max:500',
        ]);

        $gtk = $request->user()->gtk;
        if (!$gtk) {
            Log::channel('gtk')->warning('[LaporanKehadiran] GTK tidak ditemukan', [
                'user_id' => $request->user()->id,
            ]);
            return back()->withErrors(['error' => 'Data GTK tidak ditemukan']);
        }

        $tanggal = now()->toDateString();

        // Cek sudah lapor hari ini untuk jenis yang sama
        $sudahLapor = LaporanKehadiranGuru::where('gtk_id', $gtk->id)
            ->where('tanggal', $tanggal)
            ->where('jenis', $request->jenis)
            ->exists();

        if ($sudahLapor) {
            Log::channel('gtk')->warning('[LaporanKehadiran] Sudah lapor hari ini', [
                'gtk_id' => $gtk->id,
                'tanggal' => $tanggal,
                'jenis' => $request->jenis,
            ]);
            return back()->withErrors(['error' => 'Anda sudah melaporkan kehadiran ' . $request->jenis . ' hari ini']);
        }

        $jarak = null;
        $fotoPath = null;

        // Jika status hadir, hitung jarak dan upload foto
        if ($request->status === 'hadir') {
            $jarak = GeolocationService::hitungJarak(
                $request->latitude,
                $request->longitude,
                config('sekolah.latitude'),
                config('sekolah.longitude')
            );

            if ($jarak > config('sekolah.radius_m')) {
                Log::channel('gtk')->warning('[LaporanKehadiran] Jarak terlalu jauh', [
                    'gtk_id' => $gtk->id,
                    'jarak' => $jarak,
                    'max_radius' => config('sekolah.radius_m'),
                ]);
                return back()->withErrors(['error' => 'Lokasi Anda terlalu jauh dari sekolah']);
            }

            // Upload foto
            $fotoPath = $request->file('foto_selfie')->store('laporan-gtk-selfie', 'public');
        }

        try {
            $laporan = LaporanKehadiranGuru::create([
                'gtk_id' => $gtk->id,
                'tanggal' => $tanggal,
                'jenis' => $request->jenis,
                'status' => $request->status,
                'waktu_laporan' => now(),
                'foto_selfie' => $fotoPath,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'jarak_meter' => $jarak,
                'catatan' => $request->catatan,
            ]);

            Log::channel('gtk')->info('[LaporanKehadiran] Berhasil simpan laporan', [
                'laporan_id' => $laporan->id,
                'gtk_id' => $gtk->id,
                'status' => $request->status,
            ]);

            // Dispatch job kirim WA ke admin/bk jika status tidak hadir
            if ($request->status !== 'hadir') {
                SendLaporanKehadiranGuruNotif::dispatch($laporan);
            }

            return back()->with('success', 'Laporan kehadiran berhasil dikirim!');

        } catch (\Exception $e) {
            Log::channel('gtk')->error('[LaporanKehadiran] Gagal simpan laporan', [
                'gtk_id' => $gtk->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Terjadi kesalahan, silakan coba lagi']);
        }
    }

    public function show(Request $request, LaporanKehadiranGuru $laporanKehadiran): View
    {
        // Pastikan hanya GTK sendiri atau admin yang bisa lihat
        if ($request->user()->gtk?->id !== $laporanKehadiran->gtk_id &&
            !$request->user()->hasRole(['superadmin', 'admin_tatib', 'bk'])) {
            abort(403);
        }

        return view('gtk.laporan_kehadiran.show', compact('laporanKehadiran'));
    }
}
