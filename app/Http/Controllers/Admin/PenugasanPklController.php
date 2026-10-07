<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\GTK;
use App\Models\Kelas;
use App\Models\LokasiPkl;
use App\Models\PenugasanPkl;
use App\Models\Siswa;
use App\Services\PklService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenugasanPklController extends Controller
{
    public function __construct(private readonly PklService $pklService) {}

    // ══════════════════════════════════════════════════════════════════════
    // INDEX — semua penugasan (bisa filter by lokasi)
    // ══════════════════════════════════════════════════════════════════════

    public function index(Request $request): View
    {
        $lokasiId      = $request->get('lokasi_pkl_id', '');
        $status        = $request->get('status', 'aktif');
        $academicYearId = $request->get('academic_year_id', '');
        $search        = $request->get('search', '');

        $query = PenugasanPkl::with(['siswa.kelas', 'lokasiPkl', 'gtk'])
            ->when($lokasiId, fn($q) => $q->where('lokasi_pkl_id', $lokasiId))
            ->when($status,   fn($q) => $q->where('status', $status))
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('siswa', fn($sq) =>
                    $sq->where('nama_lengkap', 'like', "%{$search}%")
                       ->orWhere('nis', 'like', "%{$search}%")
                );
            })
            ->orderByDesc('tanggal_mulai');

        $penugasanList  = $query->paginate(20)->withQueryString();
        $lokasiOptions  = LokasiPkl::aktif()->orderBy('nama_tempat')->get(['id', 'nama_tempat']);
        $academicYears  = AcademicYear::orderByDesc('year_start')->get();

        return view('admin.pkl.penugasan.index', compact(
            'penugasanList', 'lokasiOptions', 'academicYears',
            'lokasiId', 'status', 'academicYearId', 'search'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // CREATE — form assign siswa ke lokasi tertentu
    // ══════════════════════════════════════════════════════════════════════

    public function create(Request $request): View
    {
        $lokasiId  = $request->get('lokasi_pkl_id');
        $lokasi    = $lokasiId ? LokasiPkl::find($lokasiId) : null;

        $lokasiOptions = LokasiPkl::aktif()->orderBy('nama_tempat')->get(['id', 'nama_tempat', 'kapasitas']);
        $kelasList     = Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas']);
        $gtkList       = GTK::where('status_aktif', true)->orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'nip']);
        $academicYear  = AcademicYear::where('is_active', true)->first();

        return view('admin.pkl.penugasan.create', compact(
            'lokasi', 'lokasiOptions', 'kelasList', 'gtkList', 'academicYear'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // STORE — proses assign multi-siswa
    // ══════════════════════════════════════════════════════════════════════

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lokasi_pkl_id'   => 'required|exists:lokasi_pkl,id',
            'siswa_ids'       => 'required|array|min:1',
            'siswa_ids.*'     => 'integer|exists:siswas,id',
            'gtk_id'          => 'nullable|exists:gtks,id',
            'academic_year_id'=> 'nullable|exists:academic_years,id',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'catatan'         => 'nullable|string|max:500',
        ], [
            'lokasi_pkl_id.required'          => 'Lokasi PKL wajib dipilih.',
            'siswa_ids.required'              => 'Pilih minimal satu siswa.',
            'tanggal_mulai.required'          => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required'        => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal'  => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $result = $this->pklService->assignSiswa($validated, auth()->id());

        $pesan = count($result['berhasil']) . ' siswa berhasil ditugaskan PKL.';

        if (! empty($result['gagal'])) {
            $namaGagal = collect($result['gagal'])->pluck('nama')->implode(', ');
            $pesan .= ' Gagal: ' . $namaGagal . '.';
            return redirect()
                ->route('admin.pkl.lokasi.show', $validated['lokasi_pkl_id'])
                ->with('warning', $pesan);
        }

        return redirect()
            ->route('admin.pkl.lokasi.show', $validated['lokasi_pkl_id'])
            ->with('success', $pesan);
    }

    // ══════════════════════════════════════════════════════════════════════
    // BATALKAN PENUGASAN
    // ══════════════════════════════════════════════════════════════════════

    public function batal(PenugasanPkl $penugasanPkl): RedirectResponse
    {
        if ($penugasanPkl->status !== 'aktif') {
            return back()->withErrors(['error' => 'Hanya penugasan aktif yang bisa dibatalkan.']);
        }

        $this->pklService->batalkanPenugasan($penugasanPkl, auth()->id());

        return back()->with('success', "Penugasan PKL {$penugasanPkl->siswa?->nama_lengkap} berhasil dibatalkan.");
    }

    // ══════════════════════════════════════════════════════════════════════
    // SELESAIKAN PENUGASAN
    // ══════════════════════════════════════════════════════════════════════

    public function selesai(PenugasanPkl $penugasanPkl): RedirectResponse
    {
        if ($penugasanPkl->status !== 'aktif') {
            return back()->withErrors(['error' => 'Hanya penugasan aktif yang bisa diselesaikan.']);
        }

        $this->pklService->selesaikanPenugasan($penugasanPkl, auth()->id());

        return back()->with('success', "Penugasan PKL {$penugasanPkl->siswa?->nama_lengkap} berhasil diselesaikan.");
    }

    // ══════════════════════════════════════════════════════════════════════
    // API — search siswa (AJAX untuk form assign)
    // ══════════════════════════════════════════════════════════════════════

    public function searchSiswa(Request $request): JsonResponse
    {
        $search   = $request->get('q', '');
        $kelasId  = $request->get('kelas_id', '');
        $lokasiId = $request->get('lokasi_pkl_id', '');

        // Ambil siswa yang SUDAH PKL aktif di tanggal range yang diminta (untuk highlight)
        // Gunakan null-coalescing (??) setelah get() karena ConvertEmptyStringsToNull middleware
        // mengubah "" menjadi null sebelum sampai ke sini, sehingga default di get() tidak terpakai.
        $tanggalMulai   = $request->get('tanggal_mulai')   ?: now()->toDateString();
        $tanggalSelesai = $request->get('tanggal_selesai') ?: now()->addYear()->toDateString();

        $sudahPklIds = PenugasanPkl::where('status', 'aktif')
            ->where('tanggal_mulai', '<=', $tanggalSelesai)
            ->where('tanggal_selesai', '>=', $tanggalMulai)
            ->pluck('siswa_id')
            ->toArray();

        $siswaList = Siswa::with('kelas:id,nama_kelas')
            ->where('status_aktif', true)
            ->whereNotNull('kelas_id')
            ->when($search, fn($q) =>
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
            )
            ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
            ->orderBy('nama_lengkap')
            ->limit(30)
            ->get(['id', 'nama_lengkap', 'nis', 'kelas_id']);

        return response()->json($siswaList->map(fn($s) => [
            'id'          => $s->id,
            'nama'        => $s->nama_lengkap,
            'nis'         => $s->nis,
            'kelas'       => $s->kelas?->nama_kelas ?? '-',
            'sudah_pkl'   => in_array($s->id, $sudahPklIds),
        ]));
    }
}
