<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsenSiswa;
use App\Models\AcademicYear;
use App\Models\JurnalHarianPkl;
use App\Models\LokasiPkl;
use App\Models\PenugasanPkl;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class RekaporanPklController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════
    // REKAP PER LOKASI
    // ══════════════════════════════════════════════════════════════════════

    public function perLokasi(Request $request): View
    {
        $academicYearId = $request->get('academic_year_id', '');
        $tanggalMulai   = $request->get('tanggal_mulai', now()->startOfMonth()->toDateString());
        $tanggalSelesai = $request->get('tanggal_selesai', now()->toDateString());

        $query = LokasiPkl::with([
            'penugasan' => fn($q) => $q->with('siswa:id,nama_lengkap,nis,kelas_id')
                ->when($academicYearId, fn($sq) => $sq->where('academic_year_id', $academicYearId)),
            'academicYear',
        ])
        ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
        ->orderBy('nama_tempat');

        $lokasiList    = $query->get();
        $academicYears = AcademicYear::orderByDesc('year_start')->get();

        // Hitung statistik kehadiran per lokasi
        // Hanya menghitung absensi siswa yang memang aktif PKL di lokasi tersebut
        $lokasiStats = $lokasiList->map(function (LokasiPkl $lokasi) use ($tanggalMulai, $tanggalSelesai) {
            // Ambil siswa yang aktif PKL di lokasi ini pada range tanggal
            $siswaIds = $lokasi->penugasan
                ->filter(fn($p) =>
                    $p->status === 'aktif' ||
                    ($p->tanggal_mulai->lte($tanggalSelesai) && $p->tanggal_selesai->gte($tanggalMulai))
                )
                ->pluck('siswa_id')
                ->unique()
                ->toArray();

            if (empty($siswaIds)) {
                return [
                    'lokasi'       => $lokasi,
                    'total_siswa'  => count($lokasi->penugasan->pluck('siswa_id')->unique()),
                    'hadir'        => 0,
                    'terlambat'    => 0,
                    'alfa'         => 0,
                    'persen_hadir' => 0,
                ];
            }

            // Hitung absensi: hanya record dengan jam_masuk terisi (berarti sudah absen via AbsenPklService)
            $absenData = AbsenSiswa::whereIn('siswa_id', $siswaIds)
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                ->whereNotNull('jam_masuk') // hanya yang benar-benar hadir
                ->selectRaw('status_masuk, COUNT(*) as total')
                ->groupBy('status_masuk')
                ->pluck('total', 'status_masuk');

            $hadir     = ($absenData['hadir'] ?? 0) + ($absenData['pkl'] ?? 0);
            $terlambat = $absenData['terlambat'] ?? 0;
            $total     = $hadir + $terlambat;

            // Hitung alfa: hari kerja yang seharusnya hadir tapi tidak ada jam_masuk
            $hariKerja = $this->hitungHariKerja($tanggalMulai, $tanggalSelesai);
            $totalHariSiswa = count($siswaIds) * $hariKerja;
            $alfa = max(0, $totalHariSiswa - $hadir - $terlambat);
            $total = $total + $alfa;

            return [
                'lokasi'       => $lokasi,
                'total_siswa'  => count($siswaIds),
                'hadir'        => $hadir,
                'terlambat'    => $terlambat,
                'alfa'         => $alfa,
                'hari_kerja'   => $hariKerja,
                'persen_hadir' => $total > 0 ? round((($hadir + $terlambat) / $total) * 100, 1) : 0,
            ];
        });

        return view('admin.pkl.rekap.per-lokasi', compact(
            'lokasiStats', 'academicYears',
            'academicYearId', 'tanggalMulai', 'tanggalSelesai'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // REKAP PER SISWA
    // ══════════════════════════════════════════════════════════════════════

    public function perSiswa(Request $request): View
    {
        $lokasiId       = $request->get('lokasi_pkl_id', '');
        $academicYearId = $request->get('academic_year_id', '');
        $search         = $request->get('search', '');
        $status         = $request->get('status', '');
        $tanggalMulai   = $request->get('tanggal_mulai', now()->startOfMonth()->toDateString());
        $tanggalSelesai = $request->get('tanggal_selesai', now()->toDateString());

        $penugasanQuery = PenugasanPkl::with(['siswa.kelas', 'lokasiPkl', 'gtk'])
            ->when($lokasiId,       fn($q) => $q->where('lokasi_pkl_id', $lokasiId))
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->when($status,         fn($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('siswa', fn($sq) =>
                    $sq->where('nama_lengkap', 'like', "%{$search}%")
                       ->orWhere('nis', 'like', "%{$search}%")
                );
            })
            ->orderBy('status')
            ->orderByDesc('tanggal_mulai');

        $penugasanList = $penugasanQuery->paginate(20)->withQueryString();

        // Hitung absensi per siswa di range yang dipilih
        // Hanya hitung record dengan jam_masuk terisi (absensi nyata)
        $siswaIds = $penugasanList->pluck('siswa_id')->unique()->toArray();

        $absenMap = AbsenSiswa::whereIn('siswa_id', $siswaIds)
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->whereNotNull('jam_masuk')
            ->selectRaw('siswa_id, status_masuk, COUNT(*) as total')
            ->groupBy('siswa_id', 'status_masuk')
            ->get()
            ->groupBy('siswa_id');

        // Hitung jurnal per siswa
        $jurnalMap = JurnalHarianPkl::whereIn('siswa_id', $siswaIds)
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->selectRaw('siswa_id, COUNT(*) as total_jurnal')
            ->groupBy('siswa_id')
            ->pluck('total_jurnal', 'siswa_id');

        $lokasiOptions = LokasiPkl::orderBy('nama_tempat')->get(['id', 'nama_tempat']);
        $academicYears = AcademicYear::orderByDesc('year_start')->get();
        $hariKerja     = $this->hitungHariKerja($tanggalMulai, $tanggalSelesai);

        return view('admin.pkl.rekap.per-siswa', compact(
            'penugasanList', 'absenMap', 'jurnalMap',
            'lokasiOptions', 'academicYears',
            'lokasiId', 'academicYearId', 'search', 'status',
            'tanggalMulai', 'tanggalSelesai', 'hariKerja'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // DETAIL SISWA — absensi harian siswa PKL tertentu
    // ══════════════════════════════════════════════════════════════════════

    public function detailSiswa(Request $request, PenugasanPkl $penugasanPkl): View
    {
        $penugasanPkl->load(['siswa.kelas', 'lokasiPkl', 'gtk']);

        $tanggalMulai   = $request->get('tanggal_mulai', $penugasanPkl->tanggal_mulai->toDateString());
        $tanggalSelesai = $request->get('tanggal_selesai', min(
            $penugasanPkl->tanggal_selesai->toDateString(),
            now()->toDateString()
        ));

        // Absensi harian siswa di range penugasan
        $absenList = AbsenSiswa::where('siswa_id', $penugasanPkl->siswa_id)
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->orderBy('tanggal')
            ->get();

        // Jurnal harian
        $jurnalList = JurnalHarianPkl::where('siswa_id', $penugasanPkl->siswa_id)
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->orderBy('tanggal')
            ->get()
            ->keyBy(fn($j) => $j->tanggal->toDateString());

        // Stats ringkasan — hadir = ada jam_masuk dan status hadir/terlambat
        $stats = [
            'hadir'       => $absenList->where('status_masuk', 'hadir')->count(),
            'terlambat'   => $absenList->where('status_masuk', 'terlambat')->count(),
            'alfa'        => $absenList->whereNull('jam_masuk')->count()
                           + $absenList->where('status_masuk', 'alfa')->count(),
            'izin'        => $absenList->whereIn('status_masuk', ['izin', 'sakit'])->count(),
            'total_jurnal' => $jurnalList->count(),
        ];

        return view('admin.pkl.rekap.detail-siswa', compact(
            'penugasanPkl', 'absenList', 'jurnalList', 'stats',
            'tanggalMulai', 'tanggalSelesai'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // VERIFIKASI JURNAL (admin/guru pembimbing)
    // ══════════════════════════════════════════════════════════════════════

    public function verifikasiJurnal(Request $request, \App\Models\JurnalHarianPkl $jurnal): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'status_verifikasi'  => 'required|in:disetujui,revisi',
            'catatan_pembimbing' => 'nullable|string|max:500',
        ]);

        app(\App\Services\PklService::class)->verifikasiJurnal(
            $jurnal,
            $request->status_verifikasi,
            $request->catatan_pembimbing,
            auth()->id()
        );

        return back()->with('success', 'Jurnal berhasil diverifikasi.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Hitung perkiraan hari kerja (Senin-Jumat) dalam range tanggal.
     */
    private function hitungHariKerja(string $tanggalMulai, string $tanggalSelesai): int
    {
        $start  = Carbon::parse($tanggalMulai);
        $end    = Carbon::parse($tanggalSelesai);
        $count  = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            // 1=Senin ... 5=Jumat
            if ($current->dayOfWeek >= 1 && $current->dayOfWeek <= 5) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }
}
