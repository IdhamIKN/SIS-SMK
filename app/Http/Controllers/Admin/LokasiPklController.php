<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\LokasiPkl;
use App\Models\SubPasal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Services\TatibPoinService;

class LokasiPklController extends Controller
{
    public function __construct(protected TatibPoinService $tatibPoinService) {}
    // ══════════════════════════════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════════════════════════════

    public function index(Request $request): View
    {
        $search        = $request->get('search', '');
        $academicYearId = $request->get('academic_year_id', '');
        $status        = $request->get('status', '');

        $query = LokasiPkl::with(['academicYear', 'penugasan' => fn($q) => $q->where('status', 'aktif')])
            ->when($search, fn($q) => $q->where('nama_tempat', 'like', "%{$search}%")
                ->orWhere('kabupaten', 'like', "%{$search}%"))
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->when($status !== '', fn($q) => $q->where('status_aktif', $status === 'aktif'))
            ->orderByDesc('created_at');

        $lokasiList   = $query->paginate(15)->withQueryString();
        $academicYears = AcademicYear::orderByDesc('year_start')->get();

        return view('admin.pkl.lokasi.index', compact('lokasiList', 'academicYears', 'search', 'academicYearId', 'status'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // CREATE / STORE
    // ══════════════════════════════════════════════════════════════════════

    public function create(): View
    {
        $academicYears = AcademicYear::orderByDesc('year_start')->get();
        $pasalList     = SubPasal::orderBy('idpasal')->get();

        $pasalPelanggaran = $this->tatibPoinService->subPasalOptions(
            'pelanggaran',
            $this->tatibPoinService->tahunAjaranAktif()
        );

        $pasalPenghargaan = $this->tatibPoinService->subPasalOptions(
            'penghargaan',
            $this->tatibPoinService->tahunAjaranAktif()
        );

        return view('admin.pkl.lokasi.create', compact('academicYears', 'pasalList', 'pasalPelanggaran', 'pasalPenghargaan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        // Normalkan checkbox boolean (HTML form kirim "1" atau null)
        foreach (['auto_poin_hadir_pkl', 'auto_poin_terlambat_pkl', 'auto_poin_alfa_pkl', 'status_aktif'] as $key) {
            $validated[$key] = (bool) ($validated[$key] ?? false);
        }

        // Handle upload foto
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('pkl/lokasi', 'public');
        }

        $validated['created_by'] = auth()->id();

        $lokasi = LokasiPkl::create($validated);

        return redirect()
            ->route('admin.pkl.lokasi.show', $lokasi)
            ->with('success', "Lokasi PKL \"{$lokasi->nama_tempat}\" berhasil ditambahkan.");
    }

    // ══════════════════════════════════════════════════════════════════════
    // SHOW
    // ══════════════════════════════════════════════════════════════════════

    public function show(LokasiPkl $lokasiPkl): View
    {
        $lokasiPkl->load([
            'penugasan' => fn($q) => $q->with(['siswa.kelas', 'gtk'])->orderBy('status')->orderByDesc('tanggal_mulai'),
            'academicYear',
            'pasalHadir',
            'pasalTerlambat',
            'pasalAlfa',
        ]);

        // Stats
        $stats = [
            'aktif'   => $lokasiPkl->penugasan->where('status', 'aktif')->count(),
            'selesai' => $lokasiPkl->penugasan->where('status', 'selesai')->count(),
            'batal'   => $lokasiPkl->penugasan->where('status', 'batal')->count(),
        ];

        return view('admin.pkl.lokasi.show', compact('lokasiPkl', 'stats'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // EDIT / UPDATE
    // ══════════════════════════════════════════════════════════════════════

    public function edit(LokasiPkl $lokasiPkl): View
    {
        $academicYears = AcademicYear::orderByDesc('year_start')->get();
        $pasalList     = SubPasal::orderBy('idpasal')->get();

        // PENTING: harus pakai sumber data yang SAMA dengan create(),
        // kalau tidak, pasal_hadir_pkl_id / pasal_terlambat_pkl_id / pasal_alfa_pkl_id
        // yang sudah tersimpan tidak akan ditemukan di list ini,
        // sehingga badge & pasal yang sudah dipilih tidak tampil di form edit.
        $pasalPelanggaran = $this->tatibPoinService->subPasalOptions(
            'pelanggaran',
            $this->tatibPoinService->tahunAjaranAktif()
        );

        $pasalPenghargaan = $this->tatibPoinService->subPasalOptions(
            'penghargaan',
            $this->tatibPoinService->tahunAjaranAktif()
        );

        return view('admin.pkl.lokasi.edit', compact('lokasiPkl', 'academicYears', 'pasalList', 'pasalPelanggaran', 'pasalPenghargaan'));
    }

    public function update(Request $request, LokasiPkl $lokasiPkl): RedirectResponse
    {
        $validated = $this->validateRequest($request, $lokasiPkl->id);

        // Normalkan checkbox boolean
        foreach (['auto_poin_hadir_pkl', 'auto_poin_terlambat_pkl', 'auto_poin_alfa_pkl', 'status_aktif'] as $key) {
            $validated[$key] = (bool) ($validated[$key] ?? false);
        }

        if ($request->hasFile('foto')) {
            // Hapus foto lama
            if ($lokasiPkl->foto) {
                Storage::disk('public')->delete($lokasiPkl->foto);
            }
            $validated['foto'] = $request->file('foto')->store('pkl/lokasi', 'public');
        }

        $lokasiPkl->update($validated);

        return redirect()
            ->route('admin.pkl.lokasi.show', $lokasiPkl)
            ->with('success', "Lokasi PKL \"{$lokasiPkl->nama_tempat}\" berhasil diperbarui.");
    }

    // ══════════════════════════════════════════════════════════════════════
    // DESTROY
    // ══════════════════════════════════════════════════════════════════════

    public function destroy(LokasiPkl $lokasiPkl): RedirectResponse
    {
        // Cegah hapus jika masih ada penugasan aktif
        if ($lokasiPkl->penugasan()->where('status', 'aktif')->exists()) {
            return back()->withErrors(['error' => 'Tidak bisa menghapus lokasi yang masih memiliki siswa PKL aktif.']);
        }

        if ($lokasiPkl->foto) {
            Storage::disk('public')->delete($lokasiPkl->foto);
        }

        $lokasiPkl->delete();

        return redirect()
            ->route('admin.pkl.lokasi.index')
            ->with('success', "Lokasi PKL \"{$lokasiPkl->nama_tempat}\" berhasil dihapus.");
    }

    // ══════════════════════════════════════════════════════════════════════
    // API WILAYAH PROXY — Menghindari CORS saat akses dari browser localhost
    // Data diambil dari emsifa API via server Laravel, bukan langsung dari browser
    //
    // PERBAIKAN vs versi lama:
    // - Pakai Http facade (cURL) alih-alih file_get_contents(), yang butuh
    //   allow_url_fopen aktif di php.ini dan sering dimatikan di hosting.
    // - Hasil gagal/kosong TIDAK di-cache, supaya percobaan berikutnya bisa
    //   langsung retry (versi lama men-cache kegagalan selama 24 jam).
    // - Ada logging supaya kegagalan bisa diketahui dari log Laravel.
    // ══════════════════════════════════════════════════════════════════════

    public function apiWilayah(string $tipe, ?string $id = null): JsonResponse
    {
        $allowed = ['provinces', 'regencies', 'districts', 'villages'];

        if (! in_array($tipe, $allowed)) {
            return response()->json(['error' => 'Tipe tidak valid'], 400);
        }

        $cacheKey = "wilayah.{$tipe}" . ($id ? ".{$id}" : '');

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $base = 'https://emsifa.github.io/api-wilayah-indonesia/api';
        $url  = $id ? "{$base}/{$tipe}/{$id}.json" : "{$base}/{$tipe}.json";

        try {
            $response = Http::timeout(10)->retry(2, 200)->get($url);

            if (! $response->successful()) {
                Log::warning("Gagal mengambil data wilayah [{$tipe}]", [
                    'url'    => $url,
                    'status' => $response->status(),
                ]);

                // Jangan cache kegagalan — biarkan request berikutnya coba lagi
                return response()->json([]);
            }

            $data = $response->json();

            if (empty($data)) {
                return response()->json([]);
            }

            // Hanya cache kalau datanya benar-benar berhasil didapat
            Cache::put($cacheKey, $data, now()->addDay());

            return response()->json($data);
        } catch (\Throwable $e) {
            Log::error("Error mengambil data wilayah [{$tipe}]: " . $e->getMessage());

            return response()->json([]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // API — untuk select dropdown wilayah
    // ══════════════════════════════════════════════════════════════════════

    // Wilayah Indonesia diambil dari API publik emsifa.github.io
    public function apiProvinsi()
    {
        return response()->json(['url' => 'https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    private function validateRequest(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            // Identitas tempat
            'nama_tempat'   => 'required|string|max:200',
            'jenis_usaha'   => 'nullable|string|max:100',

            // Alamat
            'alamat'        => 'required|string|max:500',
            'kelurahan'     => 'nullable|string|max:100',
            'kecamatan'     => 'nullable|string|max:100',
            'kabupaten'     => 'nullable|string|max:100',
            'provinsi'      => 'nullable|string|max:100',
            'kode_pos'      => 'nullable|string|max:10',

            // Koordinat
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'radius_meter'  => 'nullable|integer|min:50|max:5000',

            // Penanggung jawab
            'nama_pj'       => 'nullable|string|max:150',
            'jabatan_pj'    => 'nullable|string|max:100',
            'no_hp_pj'      => 'nullable|string|max:20',
            'email_pj'      => 'nullable|email|max:150',
            'no_telp_kantor' => 'nullable|string|max:20',
            'website'       => 'nullable|url|max:200',

            // Kapasitas & foto
            'kapasitas'     => 'nullable|integer|min:1',
            'foto'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // Jam absen
            'jam_masuk_pkl'       => 'nullable|date_format:H:i',
            'jam_pulang_pkl'      => 'nullable|date_format:H:i',
            'batas_terlambat_pkl' => 'nullable|date_format:H:i',
            'batas_absen_masuk_pkl' => 'nullable|date_format:H:i',

            // Auto poin
            'auto_poin_hadir_pkl'      => 'nullable|boolean',
            'pasal_hadir_pkl_id'       => 'nullable|exists:tblsubpasal,idpasal',
            'auto_poin_terlambat_pkl'  => 'nullable|boolean',
            'pasal_terlambat_pkl_id'   => 'nullable|exists:tblsubpasal,idpasal',
            'auto_poin_alfa_pkl'       => 'nullable|boolean',
            'pasal_alfa_pkl_id'        => 'nullable|exists:tblsubpasal,idpasal',

            // Status
            'status_aktif'      => 'nullable|boolean',
            'academic_year_id'  => 'nullable|exists:academic_years,id',
            'catatan'           => 'nullable|string|max:1000',
        ], [
            'nama_tempat.required'       => 'Nama tempat wajib diisi.',
            'alamat.required'            => 'Alamat wajib diisi.',
            'latitude.between'           => 'Latitude tidak valid.',
            'longitude.between'          => 'Longitude tidak valid.',
            'email_pj.email'             => 'Format email penanggung jawab tidak valid.',
            'website.url'                => 'Format website tidak valid (harus diawali https://).',
            'foto.image'                 => 'File foto harus berupa gambar.',
            'foto.max'                   => 'Ukuran foto maksimal 2 MB.',
            'jam_masuk_pkl.date_format'  => 'Format jam masuk tidak valid (HH:MM).',
            'jam_pulang_pkl.date_format' => 'Format jam pulang tidak valid (HH:MM).',
        ]);
    }
}
