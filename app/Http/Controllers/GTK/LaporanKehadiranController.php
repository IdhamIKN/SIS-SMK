<?php

namespace App\Http\Controllers\GTK;

use App\Events\LaporanUpdated;
use App\Exports\LaporanKehadiranGuruExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\LaporanKehadiranStoreRequest;
use App\Http\Requests\LaporanKehadiranUpdateRequest;
use App\Jobs\SendLaporanKehadiranGuruNotif;
use App\Models\GTK;
use App\Models\JadwalKBM;
use App\Models\Kelas;
use App\Models\LaporanKehadiranGuru;
use App\Models\SetJam;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanKehadiranController extends Controller
{
    public function laporSiswa(Request $request): View
    {
        $siswa = $request->user()->siswa;

        abort_unless(
            $siswa && $siswa->isPetugasLaporanGuru(),
            403,
            'Anda tidak memiliki akses untuk melaporkan kehadiran guru.'
        );

        return view('gtk.laporan_kehadiran.lapor_siswa');
    }

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
            // GTK hanya melihat laporan milik dirinya sendiri
            if ($user->gtk) {
                $query->where('gtk_id', $user->gtk->id);
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

        $laporanKehadiran = $query->orderBy('jam_ke')->paginate(20)->withQueryString();

        // Stats untuk dashboard
        $stats = $this->getStats($tanggal, $kelasId, $gtkId, $user);

        // Data untuk filter
        $kelas = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $gtkList = GTK::orderBy('nama_lengkap')->get();

        // Apakah user memiliki role istimewa (bebas batas waktu/data)
        $isPrivileged = $this->isPrivilegedRole($user);

        return view('gtk.laporan_kehadiran.index', compact(
            'laporanKehadiran',
            'stats',
            'kelas',
            'gtkList',
            'tanggal',
            'kelasId',
            'gtkId',
            'isPrivileged',
        ));
    }

    private function getStats(string $tanggal, ?int $kelasId, ?int $gtkId, $user): array
    {
        $query = LaporanKehadiranGuru::where('tanggal', $tanggal);

        if ($user->hasRole('gtk') && $user->gtk) {
            $query->where('gtk_id', $user->gtk->id);
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
        // Untuk GTK biasa, hitung dari kelas yang dia ajar saja
        if ($user->hasRole('gtk') && $user->gtk) {
            $gtkKelasIds = JadwalKBM::where('gtk_id', $user->gtk->id)->pluck('kelas_id')->toArray();
            if (! empty($gtkKelasIds)) {
                $kelasQuery->whereIn('id', $gtkKelasIds);
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

        $user    = $request->user();
        $tanggal = $request->get('tanggal', now()->toDateString());
        $filterKelasId = $request->get('kelas_id');

        // Ambil jadwal KBM hari ini
        $jadwalQuery = JadwalKBM::with(['kelas', 'gtk'])
            ->where('hari', Carbon::parse($tanggal)->locale('id')->dayName)
            ->orderBy('jam_ke');

        // Filter berdasarkan role
        if ($user->hasRole('gtk') && $user->gtk) {
            // GTK hanya melihat jadwal yang dia ajar
            $jadwalQuery->where('gtk_id', $user->gtk->id);
        } elseif ($user->hasRole('siswa') && $user->siswa) {
            // Siswa hanya melihat jadwal kelas mereka sendiri
            $jadwalQuery->where('kelas_id', $user->siswa->kelas_id);
        }

        // Filter kelas tambahan (opsional, untuk admin/guru yang mau filter)
        if ($filterKelasId && ! $user->hasRole('siswa')) {
            $jadwalQuery->where('kelas_id', $filterKelasId);
        }

        $jadwalHariIni = $jadwalQuery->get();

        // Cek jadwal yang sudah dilaporkan
        $sudahDilaporkan = LaporanKehadiranGuru::where('tanggal', $tanggal)
            ->pluck('jadwal_kbm_id')
            ->toArray();

        $jadwalBelumLapor = $jadwalHariIni->filter(function ($jadwal) use ($sudahDilaporkan) {
            return ! in_array($jadwal->id, $sudahDilaporkan);
        });

        // Pemetaan laporan per jadwal (jika sudah dilaporkan)
        $laporanByJadwal =
            Collection::wrap(LaporanKehadiranGuru::where('tanggal', $tanggal)
                ->whereIn('jadwal_kbm_id', $jadwalHariIni->pluck('id'))
                ->get())
            ->keyBy(function ($laporan) {
                return $laporan->jadwal_kbm_id;
            });

        // Lookup nama_jam dari tblsetjam berdasarkan id_jam = jam_ke
        $jamMap = SetJam::pluck('nama_jam', 'id_jam');

        // Daftar kelas untuk filter (tidak ditampilkan ke siswa)
        $kelas = $user->hasRole('siswa') ? collect() : Kelas::orderBy('nama_kelas')->get();

        return view('gtk.laporan_kehadiran.create', compact(
            'jadwalBelumLapor',
            'tanggal',
            'laporanByJadwal',
            'jadwalHariIni',
            'jamMap',
            'kelas',
            'filterKelasId',
        ));
    }

    /**
     * Simpan laporan kehadiran dari GTK.
     * FormRequest (LaporanKehadiranStoreRequest) sudah menangani validasi —
     * tidak perlu $request->validate() kedua.
     */
    public function store(LaporanKehadiranStoreRequest $request): RedirectResponse
    {
        Log::channel('gtk')->info('[LaporanKehadiran] Mulai simpan laporan kehadiran', [
            'user_id' => $request->user()->id,
            'ip'      => $request->ip(),
        ]);

        // $request->validated() sudah berisi data yang lolos FormRequest:
        // jadwal_kbm_id, status, catatan, foto_kelas
        $validated = $request->validated();

        $user    = $request->user();
        $tanggal = now()->toDateString();

        $jadwal = JadwalKBM::findOrFail($validated['jadwal_kbm_id']);

        // Belum masuk jam pelajaran → blokir
        if ($jadwal->jam_mulai && now()->format('H:i') < $jadwal->jam_mulai->format('H:i')) {
            return back()->withErrors(['error' => 'Belum masuk jam pelajaran.']);
        }

        // Batas akhir pengiriman untuk GTK biasa: jam_selesai + 30 menit
        // Role istimewa (admin, kepsek, waka, dll.) bebas dari batas ini
        if (! $this->isPrivilegedRole($user)) {
            $jendelaGuru = $this->jendelaLaporanGuru($jadwal);
            if ($jendelaGuru && now()->gt($jendelaGuru['batas_akhir'])) {
                return back()->withErrors([
                    'error' => 'Batas waktu pengiriman laporan telah habis. '
                        . 'Laporan hanya dapat dikirim hingga pukul '
                        . $jendelaGuru['batas_akhir']->format('H:i')
                        . ' (30 menit setelah jam pelajaran selesai).',
                ]);
            }
        }

        // Validasi akses GTK
        if ($user->hasRole('gtk')) {
            $bolehLapor = $user->gtk && (
                $jadwal->gtk_id === $user->gtk->id ||
                $user->gtk->kelasWali()->where('kelas.id', $jadwal->kelas_id)->exists()
            );

            if (! $bolehLapor) {
                Log::channel('gtk')->warning('[LaporanKehadiran] Akses ditolak', [
                    'user_id'   => $user->id,
                    'jadwal_id' => $jadwal->id,
                ]);

                return back()->withErrors(['error' => 'Anda tidak memiliki akses untuk melaporkan jadwal ini.']);
            }
        }

        // Cek sudah lapor
        $sudahLapor = LaporanKehadiranGuru::where('jadwal_kbm_id', $jadwal->id)
            ->where('tanggal', $tanggal)
            ->exists();

        if ($sudahLapor) {
            Log::channel('gtk')->warning('[LaporanKehadiran] Sudah lapor jadwal ini', [
                'jadwal_id' => $jadwal->id,
                'tanggal'   => $tanggal,
            ]);

            return back()->withErrors(['error' => 'Jadwal ini sudah dilaporkan hari ini.']);
        }

        try {
            $laporan = LaporanKehadiranGuru::create([
                'jadwal_kbm_id' => $jadwal->id,
                'gtk_id'        => $jadwal->gtk_id,
                'kelas_id'      => $jadwal->kelas_id,
                'tanggal'       => $tanggal,
                'jam_ke'        => $jadwal->jam_ke,
                'status'        => $validated['status'],
                'waktu_laporan' => now(),
                'catatan'       => $validated['catatan'] ?? null,
            ]);

            broadcast(new LaporanUpdated($laporan))->toOthers();

            Log::channel('gtk')->info('[LaporanKehadiran] Berhasil simpan laporan', [
                'laporan_id' => $laporan->id,
                'jadwal_id'  => $jadwal->id,
                'status'     => $validated['status'],
            ]);

            // Dispatch notifikasi WA ke penerima yang dikonfigurasi
            SendLaporanKehadiranGuruNotif::dispatch($laporan)->delay(now()->addSeconds(2));

            return redirect()->route('kehadiran-guru.laporan')
                ->with('success', 'Laporan kehadiran berhasil dikirim!');
        } catch (QueryException $e) {
            if ($this->isLaporanJadwalSudahAda($e)) {
                return back()->withErrors(['error' => 'Jadwal ini sudah dilaporkan hari ini.']);
            }

            throw $e;
        } catch (\Exception $e) {
            Log::channel('gtk')->error('[LaporanKehadiran] Gagal simpan laporan', [
                'jadwal_id' => $jadwal->id,
                'error'     => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan, silakan coba lagi.']);
        }
    }

    public function laporOlehSiswa(Request $request): RedirectResponse
    {
        Log::channel('gtk')->info('[LaporanKehadiran] Siswa lapor kehadiran guru', [
            'user_id' => $request->user()->id,
        ]);

        $request->validate([
            'jadwal_kbm_id' => 'required|exists:jadwal_kbm,id',
            'status' => 'required|in:hijau,kuning,merah,abu,biru,pink',
            'catatan' => 'nullable|string|max:500',
        ]);

        $siswa = $request->user()->siswa;
        $tanggal = now()->toDateString();

        if (! $siswa) {
            return back()->withErrors(['error' => 'Data siswa tidak ditemukan.']);
        }

        // Ambil data jadwal
        $jadwal = JadwalKBM::findOrFail($request->jadwal_kbm_id);

        // Validasi siswa berada di kelas yang benar
        if ($siswa->kelas_id !== $jadwal->kelas_id) {
            return back()->withErrors(['error' => 'Anda tidak berada di kelas ini']);
        }

        if (($pesanJendelaLaporan = $this->pesanJendelaLaporanSiswa($jadwal))) {
            return back()->withErrors(['error' => $pesanJendelaLaporan]);
        }

        // Hanya tiga siswa yang dipilih sebagai petugas kelas yang dapat mengirim laporan.
        $punyaAkses = $siswa->siswaPetugasLaporan()
            ->where('kelas_id', $jadwal->kelas_id)
            ->exists();

        if (! $punyaAkses) {
            return back()->withErrors(['error' => 'Anda tidak memiliki akses untuk melaporkan kehadiran guru.']);
        }

        // Cek sudah ada laporan untuk jadwal ini hari ini.
        // Aturan baru: maksimal 1 laporan per jadwal+tanggal (dibuat oleh 1 dari 3 siswa yang diizinkan).
        // Jadi jika sudah ada laporan untuk jadwal tsb hari ini, petugas kelas hanya boleh mengedit.
        $existingLaporan = LaporanKehadiranGuru::where('jadwal_kbm_id', $jadwal->id)
            ->where('tanggal', $tanggal)
            ->first();

        if ($existingLaporan) {
            return back()->withErrors(['error' => 'Jadwal ini sudah dilaporkan. Gunakan fitur edit untuk memperbarui laporan.']);
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

            // Broadcast update to realtime panel
            broadcast(new LaporanUpdated($laporan))->toOthers();

            Log::channel('gtk')->info('[LaporanKehadiran] Siswa berhasil lapor', [
                'laporan_id' => $laporan->id,
                'siswa_id' => $siswa->id,
                'status' => $request->status,
            ]);

            // Dispatch notifikasi WA ke penerima yang dikonfigurasi
            SendLaporanKehadiranGuruNotif::dispatch($laporan)->delay(now()->addSeconds(2));

            return back()->with('success', 'Laporan berhasil dikirim!');
        } catch (QueryException $e) {
            if ($this->isLaporanJadwalSudahAda($e)) {
                return back()->withErrors(['error' => 'Jadwal ini sudah dilaporkan. Gunakan fitur edit untuk memperbarui laporan.']);
            }

            throw $e;
        } catch (\Exception $e) {
            Log::channel('gtk')->error('[LaporanKehadiran] Siswa gagal lapor', [
                'siswa_id' => $siswa->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan, silakan coba lagi']);
        }
    }

    /**
     * Update laporan kehadiran guru yang sudah dibuat oleh siswa.
     * Bisa dilakukan oleh salah satu dari tiga petugas kelas selama batas waktu edit aktif.
     */
    public function updateOlehSiswa(Request $request, LaporanKehadiranGuru $laporanKehadiran): RedirectResponse
    {
        Log::channel('gtk')->info('[LaporanKehadiran] Siswa edit laporan kehadiran guru', [
            'user_id'    => $request->user()->id,
            'laporan_id' => $laporanKehadiran->id,
        ]);

        $request->validate([
            'status'  => 'required|in:hijau,kuning,merah,abu,biru,pink',
            'catatan' => 'nullable|string|max:500',
        ]);

        $siswa = $request->user()->siswa;

        if (
            ! $siswa ||
            (int) $laporanKehadiran->kelas_id !== (int) $siswa->kelas_id ||
            ! $siswa->siswaPetugasLaporan()->where('kelas_id', $laporanKehadiran->kelas_id)->exists()
        ) {
            return back()->withErrors(['error' => 'Anda tidak memiliki akses untuk mengedit laporan ini.']);
        }

        if (! $laporanKehadiran->tanggal->isToday()) {
            return back()->withErrors(['error' => 'Laporan dari hari sebelumnya tidak dapat diedit.']);
        }

        $laporanKehadiran->loadMissing('jadwalKbm');
        $jadwal = $laporanKehadiran->jadwalKbm;

        if (! $jadwal || ($pesanJendelaLaporan = $this->pesanJendelaLaporanSiswa($jadwal))) {
            return back()->withErrors([
                'error' => $pesanJendelaLaporan ?? 'Jadwal pelajaran tidak ditemukan.',
            ]);
        }

        try {
            $laporanKehadiran->update([
                'status'                   => $request->status,
                'catatan'                  => $request->catatan,
                'dilaporkan_oleh_siswa_id' => $siswa->id, // catat editor terakhir
            ]);

            broadcast(new LaporanUpdated($laporanKehadiran))->toOthers();

            Log::channel('gtk')->info('[LaporanKehadiran] Siswa berhasil edit laporan', [
                'laporan_id'  => $laporanKehadiran->id,
                'siswa_id'    => $siswa->id,
                'status_baru' => $request->status,
            ]);

            return back()->with('success', 'Laporan berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::channel('gtk')->error('[LaporanKehadiran] Siswa gagal edit laporan', [
                'laporan_id' => $laporanKehadiran->id,
                'error'      => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan, silakan coba lagi.']);
        }
    }

    private function isLaporanJadwalSudahAda(QueryException $exception): bool
    {
        return str_contains(
            $exception->getMessage(),
            'laporan_kehadiran_guru_jadwal_kbm_id_tanggal_unique'
        );
    }

    /**
     * Apakah user memiliki role istimewa (tanpa batas waktu kirim/edit)?
     * Role: admin, superadmin, admin_tatib, kepsek, kurikulum, waka.
     */
    private function isPrivilegedRole($user): bool
    {
        return $user->hasAnyRole([
            'admin', 'superadmin', 'admin_tatib', 'kepsek', 'kurikulum', 'waka',
        ]);
    }

    /**
     * Jendela waktu pengiriman laporan untuk GTK (bukan role istimewa).
     * Mulai  : jam_mulai jadwal
     * Selesai: jam_selesai + 30 menit
     *
     * Mengembalikan null jika jam tidak dikonfigurasi.
     */
    private function jendelaLaporanGuru(JadwalKBM $jadwal): ?array
    {
        if (! $jadwal->jam_mulai || ! $jadwal->jam_selesai) {
            return null;
        }

        $tanggal = now()->toDateString();

        return [
            'mulai'       => Carbon::parse($tanggal . ' ' . $jadwal->jam_mulai->format('H:i:s')),
            'batas_akhir' => Carbon::parse($tanggal . ' ' . $jadwal->jam_selesai->format('H:i:s'))
                                ->addMinutes(30),
        ];
    }

    private function pesanJendelaLaporanSiswa(JadwalKBM $jadwal): ?string
    {
        if ($jadwal->hari !== now()->locale('id')->dayName) {
            return 'Jadwal ini tidak berlangsung hari ini.';
        }

        $jendela = $this->jendelaLaporanSiswa($jadwal);

        if (! $jendela) {
            return 'Jam mulai dan selesai pelajaran belum dikonfigurasi.';
        }

        if (now()->lt($jendela['mulai'])) {
            return 'Laporan baru dapat dikirim mulai pukul ' . $jendela['mulai']->format('H:i') . '.';
        }

        if (now()->gt($jendela['batas_akhir'])) {
            return 'Waktu laporan telah habis. Batas laporan adalah pukul ' . $jendela['batas_akhir']->format('H:i') . ' (15 menit setelah jam pelajaran selesai).';
        }

        return null;
    }

    private function jendelaLaporanSiswa(JadwalKBM $jadwal): ?array
    {
        if (! $jadwal->jam_mulai || ! $jadwal->jam_selesai) {
            return null;
        }

        $tanggal = now()->toDateString();

        return [
            'mulai' => Carbon::parse($tanggal . ' ' . $jadwal->jam_mulai->format('H:i:s')),
            'batas_akhir' => Carbon::parse($tanggal . ' ' . $jadwal->jam_selesai->format('H:i:s'))
                ->addMinutes(15),
        ];
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

        $laporanKehadiran->loadMissing(['gtk', 'kelas', 'jadwalKbm', 'dilaporkanOlehSiswa']);

        $isPrivileged = $this->isPrivilegedRole($request->user());

        return view('gtk.laporan_kehadiran.show', compact('laporanKehadiran', 'isPrivileged'));
    }

    /**
     * Halaman edit laporan kehadiran guru (untuk GTK).
     */
    public function edit(Request $request, LaporanKehadiranGuru $laporanKehadiran): View
    {
        $user = $request->user();

        // Pastikan hanya yang berhak bisa edit
        if ($user->hasRole('gtk')) {
            $bolehEdit = $user->gtk && (
                $laporanKehadiran->gtk_id === $user->gtk->id ||
                $user->gtk->kelasWali()->where('kelas.id', $laporanKehadiran->kelas_id)->exists()
            );

            if (! $bolehEdit) {
                abort(403, 'Anda tidak memiliki akses untuk mengedit laporan ini.');
            }

            // GTK biasa hanya boleh edit laporan hari ini
            if (! $this->isPrivilegedRole($user) && ! $laporanKehadiran->tanggal->isToday()) {
                abort(403, 'Laporan dari hari sebelumnya tidak dapat diedit. Hubungi admin jika perlu koreksi.');
            }
        }

        $laporanKehadiran->loadMissing(['gtk', 'kelas', 'jadwalKbm']);

        return view('gtk.laporan_kehadiran.edit', compact('laporanKehadiran'));
    }

    /**
     * Update laporan kehadiran guru oleh GTK.
     */
    public function update(LaporanKehadiranUpdateRequest $request, LaporanKehadiranGuru $laporanKehadiran): RedirectResponse
    {
        Log::channel('gtk')->info('[LaporanKehadiran] Update laporan kehadiran', [
            'user_id' => $request->user()->id,
            'laporan_id' => $laporanKehadiran->id,
        ]);

        $user = $request->user();

        // Pastikan hanya yang berhak bisa update
        if ($user->hasRole('gtk')) {
            $bolehEdit = $user->gtk && (
                $laporanKehadiran->gtk_id === $user->gtk->id ||
                $user->gtk->kelasWali()->where('kelas.id', $laporanKehadiran->kelas_id)->exists()
            );

            if (! $bolehEdit) {
                return back()->withErrors(['error' => 'Anda tidak memiliki akses untuk mengedit laporan ini.']);
            }

            // GTK biasa hanya boleh edit laporan hari ini
            if (! $this->isPrivilegedRole($user) && ! $laporanKehadiran->tanggal->isToday()) {
                return back()->withErrors([
                    'error' => 'Laporan dari hari sebelumnya tidak dapat diedit. Hubungi admin jika perlu koreksi.',
                ]);
            }
        }

        try {
            $laporanKehadiran->update([
                'status' => $request->status,
                'catatan' => $request->catatan,
            ]);

            broadcast(new LaporanUpdated($laporanKehadiran))->toOthers();

            Log::channel('gtk')->info('[LaporanKehadiran] Berhasil update laporan', [
                'laporan_id' => $laporanKehadiran->id,
                'status_baru' => $request->status,
            ]);

            return redirect()->route('kehadiran-guru.laporan')
                ->with('success', 'Laporan berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::channel('gtk')->error('[LaporanKehadiran] Gagal update laporan', [
                'laporan_id' => $laporanKehadiran->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan, silakan coba lagi.']);
        }
    }

    /**
     * Hapus laporan kehadiran guru (admin/authorized user only).
     */
    public function destroy(Request $request, LaporanKehadiranGuru $laporanKehadiran): RedirectResponse
    {
        Log::channel('gtk')->info('[LaporanKehadiran] Hapus laporan kehadiran', [
            'user_id' => $request->user()->id,
            'laporan_id' => $laporanKehadiran->id,
        ]);

        // Hanya admin atau user dengan permission tertentu yang boleh hapus
        if ($request->user()->hasRole('gtk')) {
            return back()->withErrors(['error' => 'Anda tidak memiliki akses untuk menghapus laporan.']);
        }

        try {
            $laporanKehadiran->delete();

            Log::channel('gtk')->info('[LaporanKehadiran] Berhasil hapus laporan', [
                'laporan_id' => $laporanKehadiran->id,
            ]);

            return redirect()->route('kehadiran-guru.laporan')
                ->with('success', 'Laporan berhasil dihapus!');
        } catch (\Exception $e) {
            Log::channel('gtk')->error('[LaporanKehadiran] Gagal hapus laporan', [
                'laporan_id' => $laporanKehadiran->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan, silakan coba lagi.']);
        }
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        abort_unless(! $request->user()->hasRole('siswa'), 403);

        $request->validate([
            'tanggal_mulai'  => 'required|date',
            'tanggal_selesai'=> 'required|date|after_or_equal:tanggal_mulai',
            'kelas_id'       => 'nullable|integer|exists:kelas,id',
            'gtk_id'         => 'nullable|integer|exists:gtks,id',
        ]);

        $fileName = 'kehadiran-guru_'
            . $request->tanggal_mulai . '_sd_' . $request->tanggal_selesai
            . '.xlsx';

        return Excel::download(
            new LaporanKehadiranGuruExport(
                tanggalMulai:  $request->tanggal_mulai,
                tanggalSelesai: $request->tanggal_selesai,
                kelasId: $request->integer('kelas_id') ?: null,
                gtkId:   $request->integer('gtk_id')   ?: null,
            ),
            $fileName
        );
    }

    public function exportPdf(Request $request): View|Response
    {
        abort_unless(! $request->user()->hasRole('siswa'), 403);

        $request->validate([
            'tanggal_mulai'  => 'required|date',
            'tanggal_selesai'=> 'required|date|after_or_equal:tanggal_mulai',
            'kelas_id'       => 'nullable|integer|exists:kelas,id',
            'gtk_id'         => 'nullable|integer|exists:gtks,id',
        ]);

        $user = $request->user();

        $query = LaporanKehadiranGuru::with(['gtk', 'kelas', 'jadwalKbm', 'dilaporkanOlehSiswa'])
            ->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai])
            ->orderBy('tanggal')
            ->orderBy('jam_ke');

        // Role GTK: batasi ke kelas yang diajar / wali
        if ($user->hasRole('gtk') && $user->gtk) {
            $kelasIds = array_unique(array_merge(
                $user->gtk->kelasWali()->pluck('id')->toArray(),
                JadwalKBM::where('gtk_id', $user->gtk->id)->pluck('kelas_id')->toArray()
            ));
            if (! empty($kelasIds)) {
                $query->whereIn('kelas_id', $kelasIds);
            }
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->integer('kelas_id'));
        }

        if ($request->filled('gtk_id')) {
            $query->where('gtk_id', $request->integer('gtk_id'));
        }

        $laporans = $query->get();

        // Label filter
        $filterGuru  = $request->filled('gtk_id') ? GTK::find($request->integer('gtk_id'))?->nama_lengkap : null;
        $filterKelas = $request->filled('kelas_id') ? Kelas::find($request->integer('kelas_id'))?->nama_kelas : null;

        // Logo
        $logoSmkPath   = public_path('images/logo/smk.png');
        $logoJatimPath = public_path('images/logo/jatim.png');
        if (! file_exists($logoSmkPath))   $logoSmkPath   = resource_path('views/pdf/logo/smk.png');
        if (! file_exists($logoJatimPath)) $logoJatimPath = resource_path('views/pdf/logo/jatim.png');
        $logoSmk   = file_exists($logoSmkPath)   ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoSmkPath))   : null;
        $logoJatim = file_exists($logoJatimPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoJatimPath)) : null;

        return view('gtk.laporan_kehadiran.cetak-pdf', [
            'laporans'       => $laporans,
            'tanggalMulai'   => $request->tanggal_mulai,
            'tanggalSelesai' => $request->tanggal_selesai,
            'filterGuru'     => $filterGuru,
            'filterKelas'    => $filterKelas,
            'cetakUser'      => $user->name,
            'logoSmk'        => $logoSmk,
            'logoJatim'      => $logoJatim,
            'printFallback'  => true,
        ]);
    }

    /**
     * Halaman grafik & rekap kehadiran guru.
     * GET /kehadiran-guru/grafik
     */
    public function grafik(Request $request): View
    {
        abort_unless(! $request->user()->hasRole('siswa'), 403);

        $user           = $request->user();
        $tanggalMulai   = $request->get('tanggal_mulai', now()->startOfMonth()->toDateString());
        $tanggalSelesai = $request->get('tanggal_selesai', now()->toDateString());
        $kelasId        = $request->integer('kelas_id') ?: null;
        $gtkId          = $request->integer('gtk_id')   ?: null;

        $allStatuses = config('status_guru.order', ['hijau','kuning','merah','abu','biru','pink','orange','putih']);
        $statusCfg   = config('status_guru.statuses', []);

        // ── Base query ───────────────────────────────────────────────
        $base = function () use ($user, $tanggalMulai, $tanggalSelesai, $kelasId, $gtkId) {
            $q = LaporanKehadiranGuru::whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);
            if ($user->hasRole('gtk') && $user->gtk) {
                $q->where('gtk_id', $user->gtk->id);
            }
            if ($kelasId) $q->where('kelas_id', $kelasId);
            if ($gtkId)   $q->where('gtk_id',   $gtkId);
            return $q;
        };

        // ── Tren per hari × status ───────────────────────────────────
        $trenRaw = (clone $base())
            ->select(DB::raw('DATE(tanggal) as tgl'), 'status', DB::raw('COUNT(*) as total'))
            ->groupBy('tgl', 'status')
            ->orderBy('tgl')
            ->get()
            ->groupBy('tgl')
            ->map(fn($rows) => $rows->pluck('total', 'status')->toArray())
            ->toArray();

        $trenDates  = array_keys($trenRaw);
        $trenSeries = [];
        foreach ($allStatuses as $st) {
            $vals = [];
            foreach ($trenDates as $d) {
                $vals[] = (int) ($trenRaw[$d][$st] ?? 0);
            }
            $trenSeries[] = ['name' => $st, 'data' => $vals];
        }

        // ── Distribusi total per status ──────────────────────────────
        $distribusi = (clone $base())
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();

        // ── Rekap per guru × status ──────────────────────────────────
        $pivotRows = (clone $base())
            ->select('gtk_id', 'status', DB::raw('COUNT(*) as total'))
            ->groupBy('gtk_id', 'status')
            ->with('gtk:id,nama_lengkap')
            ->get();

        $rekapGuru = [];
        foreach ($pivotRows->groupBy('gtk_id') as $gtkIdKey => $guruRows) {
            $nama         = $guruRows->first()->gtk?->nama_lengkap ?? 'GTK #' . $gtkIdKey;
            $statusCounts = array_fill_keys($allStatuses, 0);
            foreach ($guruRows as $row) {
                if (array_key_exists($row->status, $statusCounts)) {
                    $statusCounts[$row->status] = (int) $row->total;
                }
            }
            $rekapGuru[] = [
                'gtk_id'   => $gtkIdKey,
                'nama'     => $nama,
                'total'    => array_sum($statusCounts),
                'statuses' => $statusCounts,
            ];
        }
        usort($rekapGuru, function ($a, $b) {
            $diff = ($b['statuses']['hijau'] ?? 0) - ($a['statuses']['hijau'] ?? 0);
            return $diff !== 0 ? $diff : strcmp($a['nama'], $b['nama']);
        });

        // ── Top 10 bermasalah (bar chart) ────────────────────────────
        $topBermasalah = collect($rekapGuru)->map(function ($g) {
            $prob = ($g['statuses']['merah'] ?? 0)
                  + ($g['statuses']['kuning'] ?? 0)
                  + ($g['statuses']['orange'] ?? 0);
            return array_merge($g, ['problematic' => $prob]);
        })->filter(fn($g) => $g['problematic'] > 0)
          ->sortByDesc('problematic')
          ->take(10)
          ->values()
          ->toArray();

        // ── Top 10 terbaik (% hadir hijau tertinggi, min 5 laporan) ─
        $topTerbaik = collect($rekapGuru)->filter(fn($g) => $g['total'] >= 5)
          ->map(function ($g) {
              $hijau = $g['statuses']['hijau'] ?? 0;
              $pct   = $g['total'] > 0 ? round($hijau / $g['total'] * 100, 1) : 0;
              return array_merge($g, ['hijau_pct' => $pct, 'hijau_count' => $hijau]);
          })->sortByDesc('hijau_pct')
            ->take(10)
            ->values()
            ->toArray();

        // ── Total keseluruhan ────────────────────────────────────────
        $total = array_sum($distribusi);

        // ── Data filter dropdown ─────────────────────────────────────
        $kelas   = Kelas::with('jurusan')->orderBy('nama_kelas')->get(['id','nama_kelas','tingkat']);
        $gtkList = GTK::orderBy('nama_lengkap')->get(['id','nama_lengkap']);

        return view('gtk.laporan_kehadiran.grafik', compact(
            'tanggalMulai',
            'tanggalSelesai',
            'kelasId',
            'gtkId',
            'allStatuses',
            'statusCfg',
            'trenDates',
            'trenSeries',
            'distribusi',
            'rekapGuru',
            'topBermasalah',
            'topTerbaik',
            'total',
            'kelas',
            'gtkList',
        ));
    }

    /**
     * AJAX endpoint — data JSON untuk modal Grafik & Rekap Kehadiran Guru.
     * GET /kehadiran-guru/grafik/data?tanggal_mulai=&tanggal_selesai=&kelas_id=&gtk_id=
     */
    public function grafikData(Request $request): \Illuminate\Http\JsonResponse
    {
        abort_unless(! $request->user()->hasRole('siswa'), 403);

        $request->validate([
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'kelas_id'        => 'nullable|integer|exists:kelas,id',
            'gtk_id'          => 'nullable|integer|exists:gtks,id',
        ]);

        $user       = $request->user();
        $mulai      = $request->tanggal_mulai;
        $selesai    = $request->tanggal_selesai;
        $kelasId    = $request->integer('kelas_id') ?: null;
        $gtkId      = $request->integer('gtk_id')   ?: null;

        $allStatuses = config('status_guru.order', ['hijau','kuning','merah','abu','biru','pink','orange','putih']);

        // ── Base query (reusable closure) ─────────────────────────────
        $baseQuery = function () use ($user, $mulai, $selesai, $kelasId, $gtkId) {
            $q = LaporanKehadiranGuru::whereBetween('tanggal', [$mulai, $selesai]);
            // Role GTK hanya data miliknya sendiri
            if ($user->hasRole('gtk') && $user->gtk) {
                $q->where('gtk_id', $user->gtk->id);
            }
            if ($kelasId) $q->where('kelas_id', $kelasId);
            if ($gtkId)   $q->where('gtk_id', $gtkId);
            return $q;
        };

        // ── 1. Tren per hari × status ─────────────────────────────────
        $trenRaw = (clone $baseQuery())
            ->select(DB::raw('DATE(tanggal) as tgl'), 'status', DB::raw('COUNT(*) as total'))
            ->groupBy('tgl', 'status')
            ->orderBy('tgl')
            ->get()
            ->groupBy('tgl')
            ->map(fn($rows) => $rows->pluck('total', 'status')->toArray())
            ->toArray();

        $trenDates = array_keys($trenRaw);
        $trenSeries = [];
        foreach ($allStatuses as $st) {
            $vals = [];
            foreach ($trenDates as $d) {
                $vals[] = (int) ($trenRaw[$d][$st] ?? 0);
            }
            $trenSeries[] = ['name' => $st, 'data' => $vals];
        }

        // ── 2. Distribusi total per status (donut) ────────────────────
        $distribusi = (clone $baseQuery())
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();

        // ── 3. Rekap per guru × status ────────────────────────────────
        $pivotRows = (clone $baseQuery())
            ->select('gtk_id', 'status', DB::raw('COUNT(*) as total'))
            ->groupBy('gtk_id', 'status')
            ->with('gtk:id,nama_lengkap')
            ->get();

        $byGuru = $pivotRows->groupBy('gtk_id');
        $rekapGuru = [];
        foreach ($byGuru as $gtkIdKey => $guruRows) {
            $nama = $guruRows->first()->gtk?->nama_lengkap ?? 'GTK #' . $gtkIdKey;
            $statusCounts = array_fill_keys($allStatuses, 0);
            foreach ($guruRows as $row) {
                if (array_key_exists($row->status, $statusCounts)) {
                    $statusCounts[$row->status] = (int) $row->total;
                }
            }
            $grandTotal = array_sum($statusCounts);
            $rekapGuru[] = [
                'gtk_id'   => $gtkIdKey,
                'nama'     => $nama,
                'total'    => $grandTotal,
                'statuses' => $statusCounts,
            ];
        }
        // Urutkan: hijau DESC, nama ASC
        usort($rekapGuru, function ($a, $b) {
            $diff = ($b['statuses']['hijau'] ?? 0) - ($a['statuses']['hijau'] ?? 0);
            return $diff !== 0 ? $diff : strcmp($a['nama'], $b['nama']);
        });

        // ── 4. Ringkasan total ────────────────────────────────────────
        $total = array_sum($distribusi);

        return response()->json([
            'ok'          => true,
            'tanggalMulai'  => $mulai,
            'tanggalSelesai'=> $selesai,
            'total'       => $total,
            'trenDates'   => $trenDates,
            'trenSeries'  => $trenSeries,
            'distribusi'  => $distribusi,
            'rekapGuru'   => $rekapGuru,
            'statusOrder' => $allStatuses,
            'statusConfig'=> config('status_guru.statuses'),
        ]);
    }

    public function rekap(Request $request): View
    {
        $tanggalMulai = $request->get('tanggal_mulai', now()->subDays(30)->format('Y-m-d'));
        $tanggalSelesai = $request->get('tanggal_selesai', now()->format('Y-m-d'));
        $kelasId = $request->get('kelas_id');
        $gtkId = $request->get('gtk_id');

        $user = $request->user();

        $query = LaporanKehadiranGuru::query()
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);

        // Filter berdasarkan role
        if ($user->hasRole('gtk') && $user->gtk) {
            // GTK hanya melihat laporan milik dirinya sendiri
            $query->where('gtk_id', $user->gtk->id);
        }

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        if ($gtkId) {
            $query->where('gtk_id', $gtkId);
        }

        // Statistik dihitung dari SELURUH data terfilter (sebelum pagination).
        // Clone dulu supaya select()/groupBy() di bawah tidak ikut menempel
        // ke $query asli yang masih dipakai untuk ambil data tabel.
        $statusStats = (clone $query)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $stats = [
            'total_laporan' => array_sum($statusStats),
            'hijau'  => $statusStats['hijau'] ?? 0,
            'kuning' => $statusStats['kuning'] ?? 0,
            'merah'  => $statusStats['merah'] ?? 0,
            'abu'    => $statusStats['abu'] ?? 0,
            'biru'   => $statusStats['biru'] ?? 0,
            'pink'   => $statusStats['pink'] ?? 0,
            'orange' => $statusStats['orange'] ?? 0,
            'putih'  => $statusStats['putih'] ?? 0,
        ];

        // Data tabel — dipaginasi, filter GET (tanggal/kelas/guru) ikut
        // dibawa otomatis ke link halaman berikutnya.
        $rekapData = $query
            ->with(['gtk', 'kelas', 'jadwalKbm'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_ke')
            ->paginate(20)
            ->withQueryString();

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
