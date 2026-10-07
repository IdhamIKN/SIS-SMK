<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\JadwalKBMImport;
use App\Models\GTK;
use App\Models\JadwalKBM;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\SetJam;
use Illuminate\Http\Request;
use App\Http\Requests\JadwalKBMGuruSearchRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class JadwalKBMController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = JadwalKBM::with(['kelas.jurusan', 'gtk', 'mataPelajaran']);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('gtk_id')) {
            $query->where('gtk_id', $request->gtk_id);
        }

        if ($request->filled('mata_pelajaran_id')) {
            $query->where('mata_pelajaran_id', $request->mata_pelajaran_id);
        }

        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        $jadwalKBM = $query->orderBy('hari')
            ->orderBy('jam_ke')
            ->paginate(20);

        $kelas        = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $gtkList      = GTK::orderBy('nama_lengkap')->get();
        $mataPelajaran = MataPelajaran::aktif()->orderBy('nama_mapel')->get();

        return view('admin.jadwal_kbm.index', compact(
            'jadwalKBM',
            'kelas',
            'gtkList',
            'mataPelajaran'
        ));
    }

    /**
     * Display jadwal per guru.
     *
     * $gtk  di sini adalah SINGLE MODEL (GTK|null), bukan Collection.
     * $gtkList adalah Collection untuk dropdown pilih guru.
     */
    public function jadwalGuru(Request $request): View
    {
        // $gtkList selalu Collection — untuk dropdown
        $gtkList = GTK::orderBy('nama_lengkap')->get();

        $gtkId = $request->get('gtk_id');

        // Jika tidak ada gtk_id atau kosong, tampilkan empty state
        if (empty($gtkId)) {
            return view('admin.jadwal_kbm.jadwal_guru', [
                'jadwalGuru' => collect(),   // Collection kosong — WAJIB di-pass
                'gtk'        => null,         // null  — single model, belum dipilih
                'gtkList'    => $gtkList,     // Collection — untuk dropdown
            ]);
        }

        // $gtk = single GTK model
        $gtk = GTK::findOrFail($gtkId);

        // $jadwalGuru = Collection of Collections (groupBy hari)
        $jadwalGuru = JadwalKBM::with(['kelas.jurusan', 'mataPelajaran'])
            ->where('gtk_id', $gtkId)
            ->orderBy('hari')
            ->orderBy('jam_ke')
            ->get()
            ->groupBy('hari');

        return view('admin.jadwal_kbm.jadwal_guru', compact(
            'jadwalGuru',  // Collection (grouped) — data jadwal
            'gtk',         // GTK model tunggal — info guru
            'gtkList'      // Collection — dropdown
        ));
    }

    /**
     * API: Get mata pelajaran by guru ID
     */
    public function getMataPelajaranByGuru(Request $request)
    {
        $gtkId = $request->get('gtk_id');

        if (!$gtkId) {
            return response()->json(['error' => 'GTK ID required'], 400);
        }

        $gtk = GTK::with('mataPelajaran')->find($gtkId);

        if (!$gtk) {
            return response()->json(['error' => 'GTK not found'], 404);
        }

        $mataPelajaran = $gtk->mataPelajaran->sortBy('nama_mapel')->values();

        if ($mataPelajaran->isEmpty()) {
            $mataPelajaran = MataPelajaran::aktif()->orderBy('nama_mapel')->get();
        }

        $kompetensiList   = $gtk->mataPelajaran->pluck('nama_mapel')->toArray();
        $kompetensiString = empty($kompetensiList) ? $gtk->mata_pelajaran : implode(', ', $kompetensiList);

        return response()->json([
            'mata_pelajaran' => $mataPelajaran->map(fn($mapel) => [
                'id'          => $mapel->id,
                'kode_mapel'  => $mapel->kode_mapel,
                'nama_mapel'  => $mapel->nama_mapel,
            ])->values(),
            'kompetensi_guru' => $kompetensiString,
            'has_relations'   => $gtk->mataPelajaran->isNotEmpty(),
        ]);
    }

    /**
     * API: Search guru for Select2 (filter nama_lengkap by q)
     */
    public function searchGuru(JadwalKBMGuruSearchRequest $request)
    {
        $q = $request->validatedGuruQ();

        $query = GTK::query();
        if ($q !== '') {
            $query->where('nama_lengkap', 'like', '%' . $q . '%');
        }

        $gurus = $query->orderBy('nama_lengkap')->limit(20)->get();

        return response()->json([
            'results' => $gurus->map(function (GTK $g) {
                return [
                    'id'   => $g->id,
                    'text' => $g->nama_lengkap . ($g->kd_guru ? ' (' . $g->kd_guru . ')' : ''),
                ];
            }),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $kelas              = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $gtkList            = GTK::orderBy('nama_lengkap')->get();
        $mataPelajaran      = MataPelajaran::aktif()->orderBy('nama_mapel')->get();
        $mataPelajaranOptions = $mataPelajaran->map(fn($mapel) => [
            'id'         => $mapel->id,
            'kode_mapel' => $mapel->kode_mapel,
            'nama_mapel' => $mapel->nama_mapel,
        ])->values();
        $jamPelajaran = SetJam::getJamAktif()->sortBy('time_in')->values();
        $jamPelajaranGrouped = SetJam::getJamAktifGrouped();

        // Alias $gtk = Collection untuk kompatibilitas view create yang mungkin pakai $gtk
        $gtk = $gtkList;

        return view('admin.jadwal_kbm.create', compact(
            'kelas',
            'gtk',
            'gtkList',
            'mataPelajaran',
            'mataPelajaranOptions',
            'jamPelajaran',
            'jamPelajaranGrouped'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kelas_id'         => 'required|exists:kelas,id',
            'gtk_id'           => 'required|exists:gtks,id',
            'mata_pelajaran_id'=> 'required|exists:mata_pelajaran,id',
            'hari'             => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_ke'           => 'required|integer|exists:tblsetjam,id_jam',
            'jam_ke_selesai'   => 'required|integer|exists:tblsetjam,id_jam',
            'tahun_ajaran'     => 'required|string|max:20',
            'semester'         => 'required|in:1,2',
        ]);

        $jamMulai   = SetJam::findOrFail($validated['jam_ke']);
        $jamSelesai = SetJam::findOrFail($validated['jam_ke_selesai']);

        $jamMulaiValue   = $jamMulai->time_in->format('H:i:s');
        $jamSelesaiValue = $jamSelesai->time_out->format('H:i:s');

        if ($jamMulaiValue >= $jamSelesaiValue) {
            return back()
                ->withErrors(['jam_ke_selesai' => 'Jam sampai harus sama atau setelah jam mulai.'])
                ->withInput();
        }

        $mataPelajaran = MataPelajaran::findOrFail($validated['mata_pelajaran_id']);
        $validated['jam_mulai']     = $jamMulaiValue;
        $validated['jam_selesai']   = $jamSelesaiValue;
        $validated['mata_pelajaran'] = $mataPelajaran->nama_mapel;
        unset($validated['jam_ke_selesai']);

        // Cek konflik jadwal kelas
        $conflict = JadwalKBM::where('kelas_id', $request->kelas_id)
            ->where('hari', $request->hari)
            ->where('jam_mulai', '<', $validated['jam_selesai'])
            ->where('jam_selesai', '>', $validated['jam_mulai'])
            ->exists();

        if ($conflict) {
            return back()
                ->withErrors(['error' => 'Jadwal bentrok dengan jadwal lain di kelas ini'])
                ->withInput();
        }

        // Cek konflik jadwal guru
        $guruConflict = JadwalKBM::where('gtk_id', $request->gtk_id)
            ->where('hari', $request->hari)
            ->where('jam_mulai', '<', $validated['jam_selesai'])
            ->where('jam_selesai', '>', $validated['jam_mulai'])
            ->exists();

        if ($guruConflict) {
            return back()
                ->withErrors(['error' => 'Guru ini sudah memiliki jadwal mengajar di waktu yang sama'])
                ->withInput();
        }

        // Validasi kompetensi
        $guruModel = GTK::with('mataPelajaran')->find($request->gtk_id);
        if ($guruModel && $guruModel->mataPelajaran->isNotEmpty()) {
            $hasCompetency = $guruModel->mataPelajaran()
                ->where('mata_pelajaran_id', $request->mata_pelajaran_id)
                ->exists();

            if (!$hasCompetency) {
                return back()
                    ->withErrors(['error' => 'Guru ini tidak memiliki kompetensi mengajar mata pelajaran ini'])
                    ->withInput();
            }
        }

        JadwalKBM::create($validated);

        return redirect()->route('admin.jadwal-kbm.index')
            ->with('success', 'Jadwal KBM berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(JadwalKBM $jadwal_kbm): View
    {
        $jadwalKBM = $jadwal_kbm;
        $jadwalKBM->load(['kelas.jurusan', 'gtk', 'mataPelajaran']);

        return view('admin.jadwal_kbm.show', compact('jadwalKBM'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JadwalKBM $jadwal_kbm): View
    {
        $jadwalKBM          = $jadwal_kbm;
        $kelas              = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $gtkList            = GTK::orderBy('nama_lengkap')->get();
        $mataPelajaran      = MataPelajaran::aktif()->orderBy('nama_mapel')->get();
        $mataPelajaranOptions = $mataPelajaran->map(fn($mapel) => [
            'id'         => $mapel->id,
            'kode_mapel' => $mapel->kode_mapel,
            'nama_mapel' => $mapel->nama_mapel,
        ])->values();
        $jamPelajaran = SetJam::getJamAktif()->sortBy('time_in')->values();
        $jamPelajaranGrouped = SetJam::getJamAktifGrouped();
        $selectedJamSelesai = $jamPelajaran
            ->first(fn($jam) => $jadwalKBM->jam_selesai
                && $jam->time_out->format('H:i:s') === $jadwalKBM->jam_selesai->format('H:i:s'))
            ?->id_jam ?? $jadwalKBM->jam_ke;

        // Alias $gtk = Collection untuk kompatibilitas view edit yang mungkin pakai $gtk
        $gtk = $gtkList;

        return view('admin.jadwal_kbm.edit', compact(
            'jadwalKBM',
            'kelas',
            'gtk',
            'gtkList',
            'mataPelajaran',
            'mataPelajaranOptions',
            'jamPelajaran',
            'jamPelajaranGrouped',
            'selectedJamSelesai'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JadwalKBM $jadwal_kbm): RedirectResponse
    {
        $jadwalKBM = $jadwal_kbm;
        $validated = $request->validate([
            'kelas_id'         => 'required|exists:kelas,id',
            'gtk_id'           => 'required|exists:gtks,id',
            'mata_pelajaran_id'=> 'required|exists:mata_pelajaran,id',
            'hari'             => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_ke'           => 'required|integer|exists:tblsetjam,id_jam',
            'jam_ke_selesai'   => 'required|integer|exists:tblsetjam,id_jam',
            'tahun_ajaran'     => 'required|string|max:20',
            'semester'         => 'required|in:1,2',
        ]);

        $jamMulai   = SetJam::findOrFail($validated['jam_ke']);
        $jamSelesai = SetJam::findOrFail($validated['jam_ke_selesai']);

        $jamMulaiValue   = $jamMulai->time_in->format('H:i:s');
        $jamSelesaiValue = $jamSelesai->time_out->format('H:i:s');

        if ($jamMulaiValue >= $jamSelesaiValue) {
            return back()
                ->withErrors(['jam_ke_selesai' => 'Jam sampai harus sama atau setelah jam mulai.'])
                ->withInput();
        }

        $mataPelajaran = MataPelajaran::findOrFail($validated['mata_pelajaran_id']);
        $validated['jam_mulai']      = $jamMulaiValue;
        $validated['jam_selesai']    = $jamSelesaiValue;
        $validated['mata_pelajaran'] = $mataPelajaran->nama_mapel;
        unset($validated['jam_ke_selesai']);

        // Cek konflik kelas (kecuali jadwal ini sendiri)
        $conflict = JadwalKBM::where('kelas_id', $request->kelas_id)
            ->where('hari', $request->hari)
            ->where('jam_mulai', '<', $validated['jam_selesai'])
            ->where('jam_selesai', '>', $validated['jam_mulai'])
            ->where('id', '!=', $jadwalKBM->id)
            ->exists();

        if ($conflict) {
            return back()
                ->withErrors(['error' => 'Jadwal bentrok dengan jadwal lain di kelas ini'])
                ->withInput();
        }

        // Cek konflik guru (kecuali jadwal ini sendiri)
        $guruConflict = JadwalKBM::where('gtk_id', $request->gtk_id)
            ->where('hari', $request->hari)
            ->where('jam_mulai', '<', $validated['jam_selesai'])
            ->where('jam_selesai', '>', $validated['jam_mulai'])
            ->where('id', '!=', $jadwalKBM->id)
            ->exists();

        if ($guruConflict) {
            return back()
                ->withErrors(['error' => 'Guru ini sudah memiliki jadwal mengajar di waktu yang sama'])
                ->withInput();
        }

        // Validasi kompetensi
        $guruModel = GTK::with('mataPelajaran')->find($request->gtk_id);
        if ($guruModel && $guruModel->mataPelajaran->isNotEmpty()) {
            $hasCompetency = $guruModel->mataPelajaran()
                ->where('mata_pelajaran_id', $request->mata_pelajaran_id)
                ->exists();

            if (!$hasCompetency) {
                return back()
                    ->withErrors(['error' => 'Guru ini tidak memiliki kompetensi mengajar mata pelajaran ini'])
                    ->withInput();
            }
        }

        $jadwalKBM->update($validated);

        return redirect()->route('admin.jadwal-kbm.index')
            ->with('success', 'Jadwal KBM berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JadwalKBM $jadwal_kbm): RedirectResponse
    {
        $jadwalKBM = $jadwal_kbm;

        if ($jadwalKBM->laporanKehadiranGuru()->exists()) {
            return back()->withErrors(['error' => 'Jadwal tidak dapat dihapus karena masih memiliki laporan kehadiran']);
        }

        $jadwalKBM->delete();

        return redirect()->route('admin.jadwal-kbm.index')
            ->with('success', 'Jadwal KBM berhasil dihapus');
    }

    /**
     * Tampilkan halaman form import jadwal KBM.
     */
    public function import(): View
    {
        $tahunAjaran = now()->year . '/' . (now()->year + 1);
        $jamList     = SetJam::getJamAktif()->sortBy('time_in')->values();

        return view('admin.jadwal_kbm.import', compact('tahunAjaran', 'jamList'));
    }

    /**
     * Proses upload & import file Excel/CSV jadwal KBM.
     */
    public function importProcess(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes'    => 'Format file harus xlsx, xls, atau csv.',
            'file.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        $import = new JadwalKBMImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }

        $summary = $import->summary();
        $message = "Import selesai. Ditambah: {$summary['created']}, diperbarui: {$summary['updated']}, dilewati: {$summary['skipped']}.";

        return redirect()
            ->route('admin.jadwal-kbm.index')
            ->with('success', $message)
            ->with('import_errors', $import->errors());
    }

    /**
     * Download template CSV untuk import jadwal KBM.
     */
    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');

            // BOM untuk Excel agar tidak garbled di Windows
            fwrite($out, "\xEF\xBB\xBF");

            // Header
            fputcsv($out, [
                'nama_kelas',
                'kd_guru',
                'nama_guru',
                'kode_mapel',
                'nama_mapel',
                'hari',
                'jam_mulai',
                'jam_selesai',
                'tahun_ajaran',
                'semester',
            ]);

            // Baris contoh 1
            fputcsv($out, [
                'X RPL 1',
                'GR001',
                'Budi Santoso',
                'MTK',
                'Matematika',
                'Senin',
                '1',
                '3',
                now()->year . '/' . (now()->year + 1),
                '1',
            ]);

            // Baris contoh 2
            fputcsv($out, [
                'XI TKJ 2',
                'GR002',
                'Siti Aminah',
                'INA',
                'Bahasa Indonesia',
                'Selasa',
                '4',
                '6',
                now()->year . '/' . (now()->year + 1),
                '1',
            ]);

            fclose($out);
        }, 'template_import_jadwal_kbm.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}