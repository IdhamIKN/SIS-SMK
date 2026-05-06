<?php

namespace App\Http\Controllers\GTK;

use App\Http\Controllers\Controller;
use App\Http\Requests\LaporanKehadiranStoreRequest;
use App\Models\JadwalKbm;
use App\Models\LaporanKehadiran;
use App\Services\LaporanKehadiranService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LaporanKehadiranController extends Controller
{
    public function index(Request $request): View
    {
        Log::channel('gtk')->info('[LaporanKehadiran] Halaman laporan kehadiran GTK', [
            'user_id' => $request->user()->id,
        ]);

        $user = $request->user();
        $tanggal = $request->get('tanggal', now()->toDateString());
        $kelasId = $request->get('kelas_id');
        $gtkId = $request->get('gtk_id');

        $query = LaporanKehadiranGuru::with(['gtk', 'kelas', 'jadwalKbm', 'dilaporkanOlehSiswa']);

        // Filter berdasarkan role
        if ($user->hasRole('gtk')) {
            // GTK hanya melihat laporan kelas yang dia ajar
            $gtkKelasIds = $user->gtk ? $user->gtk->kelasWali()->pluck('id')->toArray() : [];
            $gtkJadwalKelasIds = $user->gtk ? JadwalKBM::where('gtk_id', $user->gtk->id)->pluck('kelas_id')->toArray() : [];
            $semuaKelasIds = array_unique(array_merge($gtkKelasIds, $gtkJadwalKelasIds));
            if (! empty($semuaKelasIds)) {
                $query->whereIn('kelas_id', $semuaKelasIds);
            }
        }

        // Filter tanggal
        $query->where('tanggal', $tanggal);

        // Filter kelas
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        // Filter GTK
        if ($gtkId) {
            $query->where('gtk_id', $gtkId);
        }

        $laporanKehadiran = $query->orderBy('jam_ke')->paginate(20);

        // Stats untuk dashboard
        $stats = $this->getStats($tanggal, $kelasId, $gtkId, $user);

        // Data untuk filter
        $kelas = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $gtkList = GTK::orderBy('nama_lengkap')->get();

        return view('gtk.laporan_kehadiran.index', compact(
            'laporanKehadiran',
            'stats',
            'kelas',
            'gtkList',
            'tanggal',
            'kelasId',
            'gtkId'
        ));
    }

    private function getStats(string $tanggal, ?int $kelasId, ?int $gtkId, $user): array
    {
        $query = LaporanKehadiranGuru::where('tanggal', $tanggal);

        if ($user->hasRole('gtk')) {
            $gtkKelasIds = $user->gtk ? $user->gtk->kelasWali()->pluck('id')->toArray() : [];
            $gtkJadwalKelasIds = JadwalKBM::where('gtk_id', $user->gtk->id)->pluck('kelas_id')->toArray();
            $semuaKelasIds = array_unique(array_merge($gtkKelasIds, $gtkJadwalKelasIds));
            $query->whereIn('kelas_id', $semuaKelasIds);
        }

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        if ($gtkId) {
            $query->where('gtk_id', $gtkId);
        }

        $totalLaporan = $query->count();
        $statusStats = $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Hitung kelas yang belum ada laporan sama sekali
        $kelasQuery = Kelas::query();
        if ($user->hasRole('gtk')) {
            $gtkKelasIds = $user->gtk ? $user->gtk->kelasWali()->pluck('id')->toArray() : [];
            $gtkJadwalKelasIds = $user->gtk ? JadwalKBM::where('gtk_id', $user->gtk->id)->pluck('kelas_id')->toArray() : [];
            $semuaKelasIds = array_unique(array_merge($gtkKelasIds, $gtkJadwalKelasIds));
            if (! empty($semuaKelasIds)) {
                $query->whereIn('kelas_id', $semuaKelasIds);
            }
        }

        $totalKelas = $kelasQuery->count();
        $kelasDenganLaporan = $query->distinct('kelas_id')->count('kelas_id');
        $kelasBelumLapor = $totalKelas - $kelasDenganLaporan;

        return [
            'total_laporan' => $totalLaporan,
            'total_kelas' => $totalKelas,
            'kelas_dengan_laporan' => $kelasDenganLaporan,
            'kelas_belum_lapor' => $kelasBelumLapor,
            'hijau' => $statusStats['hijau'] ?? 0,
            'kuning' => $statusStats['kuning'] ?? 0,
            'merah' => $statusStats['merah'] ?? 0,
            'abu' => $statusStats['abu'] ?? 0,
            'biru' => $statusStats['biru'] ?? 0,
            'pink' => $statusStats['pink'] ?? 0,
            'orange' => $statusStats['orange'] ?? 0,
            'putih' => $statusStats['putih'] ?? 0,
        ];
    }

    public function create(Request $request): View
    {
        Log::channel('gtk')->info('[LaporanKehadiran] Halaman buat laporan kehadiran', [
            'user_id' => $request->user()->id,
        ]);

        $user = $request->user();
        $tanggal = $request->get('tanggal', now()->toDateString());

        // Ambil jadwal KBM hari ini untuk GTK ini
        $jadwalQuery = JadwalKBM::with(['kelas', 'gtk'])
            ->where('hari', Carbon::parse($tanggal)->locale('id')->dayName)
            ->orderBy('jam_ke');

        if ($user->hasRole('gtk') && $user->gtk) {
            $jadwalQuery->where('gtk_id', $user->gtk->id);
        }

        $jadwalHariIni = $jadwalQuery->get();

        // Cek jadwal yang sudah dilaporkan
        $sudahDilaporkan = LaporanKehadiranGuru::where('tanggal', $tanggal)
            ->pluck('jadwal_kbm_id')
            ->toArray();

        $jadwalBelumLapor = $jadwalHariIni->filter(function ($jadwal) use ($sudahDilaporkan) {
            return ! in_array($jadwal->id, $sudahDilaporkan);
        });

        return view('gtk.laporan_kehadiran.create', compact('jadwalBelumLapor', 'tanggal'));
    }

    public function store(LaporanKehadiranStoreRequest $request): RedirectResponse
    {
        Log::channel('gtk')->info('[LaporanKehadiran] Mulai simpan laporan kehadiran', [
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
        ]);

        $validated = $request->validated();

        $request->validate([
            'jadwal_kbm_id' => 'required|exists:jadwal_kbm,id',
            'status' => 'required|in:hijau,kuning,merah,abu,biru,pink',
            'catatan' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $tanggal = now()->toDateString();

        // Ambil data jadwal
        $jadwal = JadwalKBM::findOrFail($request->jadwal_kbm_id);

        // Validasi akses: GTK harus mengajar kelas ini atau wali kelas
        if ($user->hasRole('gtk')) {
            $bolehLapor = $user->gtk && ($jadwal->gtk_id === $user->gtk->id ||
                          $user->gtk->kelasWali()->where('kelas.id', $jadwal->kelas_id)->exists());

            if (! $bolehLapor) {
                Log::channel('gtk')->warning('[LaporanKehadiran] Akses ditolak', [
                    'user_id' => $user->id,
                    'jadwal_id' => $jadwal->id,
                ]);

                return back()->withErrors(['error' => 'Anda tidak memiliki akses untuk melaporkan jadwal ini']);
            }
        }

        // Cek sudah lapor untuk jadwal ini hari ini
        $sudahLapor = LaporanKehadiranGuru::where('jadwal_kbm_id', $jadwal->id)
            ->where('tanggal', $tanggal)
            ->exists();

        if ($sudahLapor) {
            Log::channel('gtk')->warning('[LaporanKehadiran] Sudah lapor jadwal ini', [
                'jadwal_id' => $jadwal->id,
                'tanggal' => $tanggal,
            ]);

            return back()->withErrors(['error' => 'Jadwal ini sudah dilaporkan hari ini']);
        }

        try {
            $laporan = LaporanKehadiranGuru::create([
                'jadwal_kbm_id' => $jadwal->id,
                'gtk_id' => $jadwal->gtk_id,
                'kelas_id' => $jadwal->kelas_id,
                'tanggal' => $tanggal,
                'jam_ke' => $jadwal->jam_ke,
                'status' => $request->status,
                'waktu_laporan' => now(),
                'catatan' => $request->catatan,
            ]);

            Log::channel('gtk')->info('[LaporanKehadiran] Berhasil simpan laporan', [
                'laporan_id' => $laporan->id,
                'jadwal_id' => $jadwal->id,
                'status' => $request->status,
            ]);

            return redirect()->route('kehadiran-guru.laporan')
                ->with('success', 'Laporan kehadiran berhasil dikirim!');

        } catch (\Exception $e) {
            Log::channel('gtk')->error('[LaporanKehadiran] Gagal simpan laporan', [
                'jadwal_id' => $jadwal->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan, silakan coba lagi']);
        }
    }

    public function laporOlehSiswa(Request $request): RedirectResponse
    {
        Log::channel('gtk')->info('[LaporanKehadiran] Siswa lapor kehadiran guru', [
            'user_id' => $request->user()->id,
        ]);

        $request->validate([
            'jadwal_kbm_id' => 'required|exists:jadwal_kbm,id',
            'status' => 'required|in:merah,abu,biru,pink',
            'catatan' => 'nullable|string|max:500',
        ]);

        $siswa = $request->user()->siswa;
        $tanggal = now()->toDateString();

        // Ambil data jadwal
        $jadwal = JadwalKBM::findOrFail($request->jadwal_kbm_id);

        // Validasi siswa berada di kelas yang benar
        if ($siswa->kelas_id !== $jadwal->kelas_id) {
            return back()->withErrors(['error' => 'Anda tidak berada di kelas ini']);
        }

        // Cek sudah ada laporan untuk jadwal ini hari ini
        $sudahLapor = LaporanKehadiranGuru::where('jadwal_kbm_id', $jadwal->id)
            ->where('tanggal', $tanggal)
            ->exists();

        if ($sudahLapor) {
            return back()->withErrors(['error' => 'Jadwal ini sudah dilaporkan']);
        }

        try {
            $laporan = LaporanKehadiranGuru::create([
                'jadwal_kbm_id' => $jadwal->id,
                'gtk_id' => $jadwal->gtk_id,
                'kelas_id' => $jadwal->kelas_id,
                'tanggal' => $tanggal,
                'jam_ke' => $jadwal->jam_ke,
                'status' => $request->status,
                'dilaporkan_oleh_siswa_id' => $siswa->id,
                'waktu_laporan' => now(),
                'catatan' => $request->catatan,
            ]);

            Log::channel('gtk')->info('[LaporanKehadiran] Siswa berhasil lapor', [
                'laporan_id' => $laporan->id,
                'siswa_id' => $siswa->id,
                'status' => $request->status,
            ]);

            return back()->with('success', 'Laporan berhasil dikirim!');

        } catch (\Exception $e) {
            Log::channel('gtk')->error('[LaporanKehadiran] Siswa gagal lapor', [
                'siswa_id' => $siswa->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan, silakan coba lagi']);
        }
    }

    public function show(Request $request, LaporanKehadiranGuru $laporanKehadiran): View
    {
        // Pastikan hanya yang berhak bisa lihat
        if ($request->user()->hasRole('gtk')) {
            $gtkKelasIds = $request->user()->gtk->kelasWali()->pluck('id')->toArray();
            $gtkJadwalKelasIds = JadwalKBM::where('gtk_id', $request->user()->gtk->id)->pluck('kelas_id')->toArray();
            $semuaKelasIds = array_unique(array_merge($gtkKelasIds, $gtkJadwalKelasIds));

            if (! in_array($laporanKehadiran->kelas_id, $semuaKelasIds)) {
                abort(403);
            }
        }

        return view('gtk.laporan_kehadiran.show', compact('laporanKehadiran'));
    }

    public function rekap(Request $request): View
    {
        $tanggalMulai = $request->get('tanggal_mulai', now()->subDays(30)->format('Y-m-d'));
        $tanggalSelesai = $request->get('tanggal_selesai', now()->format('Y-m-d'));
        $kelasId = $request->get('kelas_id');
        $gtkId = $request->get('gtk_id');

        $query = LaporanKehadiranGuru::with(['gtk', 'kelas', 'jadwalKbm'])
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);

        // Filter berdasarkan role
        $user = $request->user();
        if ($user->hasRole('gtk')) {
            $gtkKelasIds = $user->gtk ? $user->gtk->kelasWali()->pluck('id')->toArray() : [];
            $gtkJadwalKelasIds = $user->gtk ? JadwalKBM::where('gtk_id', $user->gtk->id)->pluck('kelas_id')->toArray() : [];
            $semuaKelasIds = array_unique(array_merge($gtkKelasIds, $gtkJadwalKelasIds));
            if (! empty($semuaKelasIds)) {
                $query->whereIn('kelas_id', $semuaKelasIds);
            }
        }

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        if ($gtkId) {
            $query->where('gtk_id', $gtkId);
        }

        $rekapData = $query->orderBy('tanggal', 'desc')
            ->orderBy('jam_ke')
            ->get();

        // Hitung statistik
        $stats = [
            'total_laporan' => $rekapData->count(),
            'hijau' => $rekapData->where('status', 'hijau')->count(),
            'kuning' => $rekapData->where('status', 'kuning')->count(),
            'merah' => $rekapData->where('status', 'merah')->count(),
            'abu' => $rekapData->where('status', 'abu')->count(),
            'biru' => $rekapData->where('status', 'biru')->count(),
            'pink' => $rekapData->where('status', 'pink')->count(),
            'orange' => $rekapData->where('status', 'orange')->count(),
            'putih' => $rekapData->where('status', 'putih')->count(),
        ];

        $kelas = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $gtkList = GTK::orderBy('nama_lengkap')->get();

        return view('gtk.laporan_kehadiran.rekap', compact(
            'rekapData',
            'stats',
            'kelas',
            'gtkList',
            'tanggalMulai',
            'tanggalSelesai',
            'kelasId',
            'gtkId'
        ));
    }
}
