<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AbsenSiswa;
use App\Models\JurnalHarianPkl;
use App\Models\PenugasanPkl;
use App\Services\PklService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * JurnalPklController — untuk siswa yang sedang aktif PKL.
 *
 * Hanya siswa dengan penugasan PKL aktif yang bisa mengakses controller ini.
 * Siswa yang tidak PKL akan di-redirect dengan pesan error.
 */
class JurnalPklController extends Controller
{
    public function __construct(private readonly PklService $pklService) {}

    // ══════════════════════════════════════════════════════════════════════
    // DASHBOARD PKL SISWA
    // ══════════════════════════════════════════════════════════════════════

    public function dashboard(): View|RedirectResponse
    {
        $siswa = auth()->user()->siswa;

        if (! $siswa) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Data siswa tidak ditemukan.']);
        }

        $penugasan = PenugasanPkl::with(['lokasiPkl', 'gtk'])
            ->where('siswa_id', $siswa->id)
            ->where('status', 'aktif')
            ->latest('tanggal_mulai')
            ->first();

        if (! $penugasan) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => 'Kamu tidak memiliki penugasan PKL aktif.']);
        }

        // Jurnal bulan ini
        $jurnalBulanIni = JurnalHarianPkl::where('siswa_id', $siswa->id)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->orderByDesc('tanggal')
            ->get();

        // Jurnal hari ini
        $jurnalHariIni = JurnalHarianPkl::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', today())
            ->first();

        // Absensi PKL hari ini
        $absenHariIni = AbsenSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', today())
            ->first();

        // Stats ringkasan
        $stats = [
            'total_jurnal'  => JurnalHarianPkl::where('siswa_id', $siswa->id)
                ->where('penugasan_pkl_id', $penugasan->id)->count(),
            'disetujui'     => JurnalHarianPkl::where('siswa_id', $siswa->id)
                ->where('status_verifikasi', 'disetujui')->count(),
            'revisi'        => JurnalHarianPkl::where('siswa_id', $siswa->id)
                ->where('status_verifikasi', 'revisi')->count(),
        ];

        return view('siswa.pkl.dashboard', compact(
            'penugasan', 'jurnalBulanIni', 'jurnalHariIni', 'absenHariIni', 'stats'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // LIST JURNAL SISWA
    // ══════════════════════════════════════════════════════════════════════

    public function index(): View|RedirectResponse
    {
        $siswa = auth()->user()->siswa;

        $penugasan = $this->getPenugasanAktif($siswa?->id);
        if (! $penugasan) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => 'Kamu tidak memiliki penugasan PKL aktif.']);
        }

        $jurnalList = JurnalHarianPkl::where('siswa_id', $siswa->id)
            ->where('penugasan_pkl_id', $penugasan->id)
            ->orderByDesc('tanggal')
            ->paginate(15);

        return view('siswa.pkl.jurnal.index', compact('penugasan', 'jurnalList'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // CREATE / STORE JURNAL
    // ══════════════════════════════════════════════════════════════════════

    public function create(): View|RedirectResponse
    {
        $siswa = auth()->user()->siswa;

        $penugasan = $this->getPenugasanAktif($siswa?->id);
        if (! $penugasan) {
            return redirect()->route('siswa.pkl.dashboard')
                ->withErrors(['error' => 'Kamu tidak memiliki penugasan PKL aktif.']);
        }

        // ── Cek: siswa harus sudah absen masuk PKL hari ini ──────────────
        $tanggal    = now()->toDateString();
        $absenHariIni = AbsenSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $tanggal)
            ->whereNotNull('jam_masuk')
            ->first();

        if (! $absenHariIni) {
            return redirect()->route('siswa.pkl.absen.index')
                ->withErrors(['error' => 'Kamu harus absen masuk PKL terlebih dahulu sebelum mengisi jurnal hari ini.']);
        }

        // Cek jurnal hari ini sudah ada
        $jurnalHariIni = JurnalHarianPkl::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', today())
            ->first();

        return view('siswa.pkl.jurnal.create', compact('penugasan', 'jurnalHariIni'));
    }

    public function store(Request $request): RedirectResponse
    {
        $siswa = auth()->user()->siswa;

        $penugasan = $this->getPenugasanAktif($siswa?->id);
        if (! $penugasan) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => 'Kamu tidak memiliki penugasan PKL aktif.']);
        }

        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'jam_datang' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'kegiatan'   => 'required|string|max:2000',
            'hasil'      => 'nullable|string|max:1000',
            'kendala'    => 'nullable|string|max:500',
            'foto'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ], [
            'kegiatan.required'      => 'Uraian kegiatan wajib diisi.',
            'foto.image'             => 'File foto harus berupa gambar.',
            'foto.max'               => 'Ukuran foto maksimal 3 MB.',
            'jam_datang.date_format' => 'Format jam datang tidak valid (HH:MM).',
            'jam_pulang.date_format' => 'Format jam pulang tidak valid (HH:MM).',
        ]);

        // Pastikan tanggal dalam range penugasan
        if ($validated['tanggal'] < $penugasan->tanggal_mulai->toDateString() ||
            $validated['tanggal'] > $penugasan->tanggal_selesai->toDateString()) {
            return back()->withInput()->withErrors(['tanggal' => 'Tanggal harus dalam rentang periode PKL kamu.']);
        }

        // ── Cek: jurnal hanya untuk tanggal yang sudah ada absensi PKL ──
        $absenTanggal = AbsenSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $validated['tanggal'])
            ->whereNotNull('jam_masuk')
            ->first();

        if (! $absenTanggal) {
            return back()->withInput()->withErrors([
                'tanggal' => 'Jurnal hanya bisa diisi untuk tanggal yang kamu sudah absen masuk PKL.',
            ]);
        }

        // Upload foto
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika update
            $jurnalLama = JurnalHarianPkl::where('siswa_id', $siswa->id)
                ->whereDate('tanggal', $validated['tanggal'])
                ->first();
            if ($jurnalLama?->foto) {
                Storage::disk('public')->delete($jurnalLama->foto);
            }
            $validated['foto'] = $request->file('foto')->store('pkl/jurnal', 'public');
        }

        $this->pklService->simpanJurnal($penugasan, $validated);

        return redirect()
            ->route('siswa.pkl.jurnal.index')
            ->with('success', 'Jurnal harian PKL berhasil disimpan.');
    }

    // ══════════════════════════════════════════════════════════════════════
    // EDIT / UPDATE JURNAL
    // ══════════════════════════════════════════════════════════════════════

    public function edit(JurnalHarianPkl $jurnal): View|RedirectResponse
    {
        $siswa = auth()->user()->siswa;

        // Pastikan jurnal milik siswa yang login
        if ($jurnal->siswa_id !== $siswa?->id) {
            abort(403, 'Jurnal ini bukan milikmu.');
        }

        // Jika sudah disetujui, tidak bisa diedit
        if ($jurnal->status_verifikasi === 'disetujui') {
            return back()->withErrors(['error' => 'Jurnal yang sudah disetujui tidak dapat diedit.']);
        }

        $penugasan = $jurnal->penugasan;

        return view('siswa.pkl.jurnal.edit', compact('jurnal', 'penugasan'));
    }

    public function update(Request $request, JurnalHarianPkl $jurnal): RedirectResponse
    {
        $siswa = auth()->user()->siswa;

        if ($jurnal->siswa_id !== $siswa?->id) {
            abort(403, 'Jurnal ini bukan milikmu.');
        }

        if ($jurnal->status_verifikasi === 'disetujui') {
            return back()->withErrors(['error' => 'Jurnal yang sudah disetujui tidak dapat diedit.']);
        }

        $validated = $request->validate([
            'jam_datang' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'kegiatan'   => 'required|string|max:2000',
            'hasil'      => 'nullable|string|max:1000',
            'kendala'    => 'nullable|string|max:500',
            'foto'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        if ($request->hasFile('foto')) {
            $this->pklService->hapusFotoJurnal($jurnal->foto);
            $validated['foto'] = $request->file('foto')->store('pkl/jurnal', 'public');
        }

        $validated['tanggal'] = $jurnal->tanggal->toDateString();

        $this->pklService->simpanJurnal($jurnal->penugasan, $validated);

        return redirect()
            ->route('siswa.pkl.jurnal.index')
            ->with('success', 'Jurnal harian PKL berhasil diperbarui.');
    }

    // ══════════════════════════════════════════════════════════════════════
    // REKAP JURNAL — Untuk siswa yang PKL-nya sudah SELESAI (read-only)
    // Juga bisa diakses siswa aktif sebagai ringkasan semua penugasan
    // ══════════════════════════════════════════════════════════════════════

    public function rekapJurnal(): View|RedirectResponse
    {
        $siswa = auth()->user()->siswa;

        if (! $siswa) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Data siswa tidak ditemukan.']);
        }

        // Ambil semua penugasan (aktif + selesai) — batal diabaikan
        $penugasanList = PenugasanPkl::with(['lokasiPkl', 'gtk'])
            ->where('siswa_id', $siswa->id)
            ->whereIn('status', ['aktif', 'selesai'])
            ->orderByDesc('tanggal_mulai')
            ->get();

        if ($penugasanList->isEmpty()) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => 'Kamu tidak memiliki riwayat PKL.']);
        }

        // Ambil semua jurnal siswa ini, semua penugasan
        $penugasanIds = $penugasanList->pluck('id');

        $semuaJurnal = JurnalHarianPkl::whereIn('penugasan_pkl_id', $penugasanIds)
            ->orderByDesc('tanggal')
            ->paginate(20);

        // Stats total per penugasan
        $statPerPenugasan = $penugasanList->map(function ($p) {
            $jurnalQuery = JurnalHarianPkl::where('penugasan_pkl_id', $p->id);
            return [
                'penugasan'     => $p,
                'total_jurnal'  => $jurnalQuery->count(),
                'disetujui'     => $jurnalQuery->where('status_verifikasi', 'disetujui')->count(),
                'revisi'        => $jurnalQuery->where('status_verifikasi', 'revisi')->count(),
                'menunggu'      => $jurnalQuery->where('status_verifikasi', 'diajukan')->count(),
            ];
        });

        return view('siswa.pkl.jurnal.rekap', compact(
            'penugasanList', 'semuaJurnal', 'statPerPenugasan'
        ));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    private function getPenugasanAktif(?int $siswaId): ?PenugasanPkl
    {
        if (! $siswaId) return null;

        return PenugasanPkl::with(['lokasiPkl', 'gtk'])
            ->where('siswa_id', $siswaId)
            ->where('status', 'aktif')
            ->latest('tanggal_mulai')
            ->first();
    }

    /**
     * Ambil penugasan aktif atau selesai (untuk akses rekap).
     * Digunakan pada halaman yang boleh diakses siswa selesai PKL.
     */
    private function getPenugasanApapun(?int $siswaId): ?PenugasanPkl
    {
        if (! $siswaId) return null;

        return PenugasanPkl::with(['lokasiPkl', 'gtk'])
            ->where('siswa_id', $siswaId)
            ->whereIn('status', ['aktif', 'selesai'])
            ->latest('tanggal_mulai')
            ->first();
    }
}
