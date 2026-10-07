<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TransaksiPoin;
use App\Services\TatibPoinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RekapPoinController extends Controller
{
    // Jumlah kelas yang ditampilkan per halaman
    private const KELAS_PER_PAGE = 3;

    public function __construct(
        private readonly TatibPoinService $tatibPoin
    ) {}


    public function index(Request $request)
    {
        $user = $request->user();

        $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());
        $poinAwal    = $this->tatibPoin->poinAwalEdaran();
        $thresholds  = $this->tatibPoin->thresholds(); // [{batas_ke, poin, tindakan, sanksi}]

        if ($user->hasRole('siswa')) {
            $siswa = $this->siswaLogin($request);
            return redirect()->route('admin.rekap-poin.show', [
                'siswa'        => $siswa,
                'tahun_ajaran' => $tahunAjaran,
            ]);
        }

        $hasSearch  = $request->filled('search');
        $hasKelasId = $request->filled('kelas_id');
        $hasStatus  = $request->filled('status'); // filter baru

        // ── Aggregate SEMUA siswa untuk keperluan filter status ──────────
        // Kita butuh ini sebelum query siswa, agar bisa filter by poin range.
        $allAggregates = TransaksiPoin::query()
            ->select('siswa_id', DB::raw('SUM(poinp) as total_pelanggaran'), DB::raw('SUM(poinr) as total_penghargaan'))
            ->where('thajaran', $tahunAjaran)
            ->groupBy('siswa_id')
            ->get()
            ->keyBy('siswa_id');

        // ── Urutkan threshold ascending by poin (dipakai berulang kali) ──
        $sortedThresholds = collect($thresholds)->sortBy('poin')->values();

        // ── Tentukan range poin untuk filter status ──────────────────────
        $statusPoinMin     = null;
        $statusPoinMax     = null;
        $statusLabel       = null;
        $siswaIdsForStatus = [];

        if ($hasStatus) {
            $status = $request->status;

            if ($status === 'aman') {
                $statusPoinMax = 0;
                $statusLabel   = 'Aman';
            } elseif ($status === 'pantau') {
                $statusPoinMin = 1;
                // Max = threshold pertama - 1
                $statusPoinMax = ($sortedThresholds->first()['poin'] ?? 100) - 1;
                $statusLabel   = 'Pantau';
            } else {
                // status = index threshold (0-based) misal "threshold_0", "threshold_1"
                $idx     = (int) str_replace('threshold_', '', $status);
                $current = $sortedThresholds->get($idx);
                $next    = $sortedThresholds->get($idx + 1);
                if ($current) {
                    $statusPoinMin = (int) $current['poin'];
                    $statusPoinMax = $next ? (int) $next['poin'] - 1 : null;
                    $statusLabel   = $current['tindakan'] ?? ('Panggilan ke-' . $current['batas_ke']);
                }
            }

            // Siswa yang masuk range status ini
            $siswaIdsForStatus = $allAggregates->filter(function ($agg) use ($statusPoinMin, $statusPoinMax) {
                $p = (int) $agg->total_pelanggaran;
                if ($statusPoinMax === 0) {
                    // "Aman" = poin 0 persis, termasuk siswa tanpa transaksi
                    return $p === 0;
                }
                $minOk = $statusPoinMin === null || $p >= $statusPoinMin;
                $maxOk = $statusPoinMax === null || $p <= $statusPoinMax;
                return $minOk && $maxOk;
            })->keys()->toArray();

            // Untuk "Aman" tambahkan siswa yang sama sekali tidak punya transaksi
            if ($status === 'aman') {
                $siswaIdsWithTransaksi = $allAggregates->keys()->toArray();
                $siswaIdsAman = Siswa::whereNotIn('id', $siswaIdsWithTransaksi)->pluck('id')->toArray();
                $siswaIdsForStatus = array_merge($siswaIdsForStatus, $siswaIdsAman);
            }
        }

        // ── Query siswa ──────────────────────────────────────────────────
        $siswaQuery = Siswa::with('kelas')
            ->when($hasKelasId, fn($q) => $q->where('kelas_id', $request->kelas_id))
            ->when($hasSearch, function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sq) use ($search) {
                    $sq->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nis',        'like', "%{$search}%")
                        ->orWhere('nisn',       'like', "%{$search}%");
                });
            })
            ->when($hasStatus, fn($q) => $q->whereIn('id', $siswaIdsForStatus))
            ->orderBy('kelas_id')
            ->orderBy('nama_lengkap');

        $allSiswas = $siswaQuery->get();

        // ── Kelas yang tampil ────────────────────────────────────────────
        $isFiltered = $hasSearch || $hasKelasId || $hasStatus;

        if ($isFiltered) {
            $kelasIdsFromSiswa = $allSiswas->pluck('kelas_id')->unique()->sort()->values();
            $kelasCollection   = Kelas::whereIn('id', $kelasIdsFromSiswa)->orderBy('nama_kelas')->get();
            $kelasPaginated    = new \Illuminate\Pagination\LengthAwarePaginator(
                $kelasCollection,
                $kelasCollection->count(),
                $kelasCollection->count() ?: 1,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $kelasPaginated = Kelas::orderBy('nama_kelas')
                ->paginate(self::KELAS_PER_PAGE)
                ->withQueryString();
            $kelasIds  = $kelasPaginated->getCollection()->pluck('id');
            $allSiswas = Siswa::with('kelas')
                ->whereIn('kelas_id', $kelasIds)
                ->orderBy('kelas_id')
                ->orderBy('nama_lengkap')
                ->get();
        }

        // ── Aggregate poin untuk siswa yang tampil ───────────────────────
        $aggregates = TransaksiPoin::query()
            ->select('siswa_id', DB::raw('SUM(poinp) as total_pelanggaran'), DB::raw('SUM(poinr) as total_penghargaan'))
            ->where('thajaran', $tahunAjaran)
            ->whereIn('siswa_id', $allSiswas->pluck('id'))
            ->groupBy('siswa_id')
            ->get()
            ->keyBy('siswa_id');

        $siswasPerKelas = $allSiswas->groupBy('kelas_id');

        // ── Stats global ─────────────────────────────────────────────────
        $stats = [
            'total_siswa'       => Siswa::count(),
            'total_pelanggaran' => (int) TransaksiPoin::where('thajaran', $tahunAjaran)->sum('poinp'),
            'total_penghargaan' => (int) TransaksiPoin::where('thajaran', $tahunAjaran)->sum('poinr'),
        ];

        // ── Top 5 pelanggaran terbanyak ──────────────────────────────────
        $topPelanggaran = TransaksiPoin::query()
            ->select('siswa_id', DB::raw('SUM(poinp) as total'))
            ->where('thajaran', $tahunAjaran)
            ->where('poinp', '>', 0)
            ->groupBy('siswa_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('siswa.kelas')
            ->get();

        // ── Top 5 penghargaan terbanyak ──────────────────────────────────
        $topPenghargaan = TransaksiPoin::query()
            ->select('siswa_id', DB::raw('SUM(poinr) as total'))
            ->where('thajaran', $tahunAjaran)
            ->where('poinr', '>', 0)
            ->groupBy('siswa_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('siswa.kelas')
            ->get();

        // ── Hitung distribusi status untuk chip counter ──────────────────
        $totalSiswa = $stats['total_siswa'];

        $distStatus = [];
        foreach ($sortedThresholds as $idx => $t) {
            $nextT   = $sortedThresholds->get($idx + 1);
            $minPoin = (int) $t['poin'];
            $maxPoin = $nextT ? (int) $nextT['poin'] - 1 : null;
            $count   = $allAggregates->filter(function ($agg) use ($minPoin, $maxPoin) {
                $p = (int) $agg->total_pelanggaran;
                return $p >= $minPoin && ($maxPoin === null || $p <= $maxPoin);
            })->count();
            $distStatus[] = [
                'key'    => 'threshold_' . $idx,
                'label'  => $t['tindakan'] ?? ('Panggilan ' . $t['batas_ke']),
                'sanksi' => $t['sanksi'] ?? null,
                'poin'   => $minPoin,
                'count'  => $count,
            ];
        }

        // Pantau = 1 s/d threshold pertama - 1
        $firstPoin       = (int) ($sortedThresholds->first()['poin'] ?? 100);
        $countPantau     = $allAggregates->filter(fn($a) => (int) $a->total_pelanggaran >= 1 && (int) $a->total_pelanggaran < $firstPoin)->count();
        $countBermasalah = collect($distStatus)->sum('count') + $countPantau;
        $countAman       = max(0, $totalSiswa - $countBermasalah);

        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('admin.rekap_poin.index', compact(
            'kelasPaginated',
            'siswasPerKelas',
            'aggregates',
            'kelas',
            'tahunAjaran',
            'stats',
            'poinAwal',
            'thresholds',
            'distStatus',
            'countPantau',
            'countAman',
            'topPelanggaran',
            'topPenghargaan',
            'isFiltered',
            'statusLabel'
        ));
    }

    public function chart(Request $request): View
    {
        $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());
        $poinAwal    = $this->tatibPoin->poinAwalEdaran();
        $thresholds  = $this->tatibPoin->thresholds(); // Collection, sorted by poin asc, dari config dinamis

        // ── Stats ringkas — semua dari tbltransaksi ───────────────────
        $totalSiswa       = Siswa::count();
        $totalPelanggaran = (int) TransaksiPoin::where('thajaran', $tahunAjaran)->sum('poinp');
        $totalPenghargaan = (int) TransaksiPoin::where('thajaran', $tahunAjaran)->sum('poinr');

        // ── Aggregate poin pelanggaran per siswa (dari tbltransaksi) ──
        // Dipakai untuk distribusi status dan threshold counts.
        $aggAll = TransaksiPoin::query()
            ->selectRaw('siswa_id, SUM(poinp) as total_p')
            ->where('thajaran', $tahunAjaran)
            ->groupBy('siswa_id')
            ->pluck('total_p', 'siswa_id')
            ->map(fn($v) => (int) $v);

        // ── Distribusi status siswa (pakai thresholds dinamis dari config) ──
        // Urutkan threshold descending agar bisa match dari yang tertinggi dulu.
        $sortedThresholdsDesc = $thresholds->sortByDesc('poin')->values();
        $firstThresholdPoin   = (int) ($thresholds->sortBy('poin')->first()['poin'] ?? PHP_INT_MAX);

        $dist = collect(['Aman' => 0]);
        foreach ($thresholds->sortBy('poin') as $t) {
            $label        = $t['tindakan'] ?? ('Panggilan ' . $t['batas_ke']);
            $dist[$label] = 0;
        }

        foreach ($aggAll as $p) {
            $matched = false;
            foreach ($sortedThresholdsDesc as $t) {
                if ($p >= (int) $t['poin']) {
                    $label        = $t['tindakan'] ?? ('Panggilan ' . $t['batas_ke']);
                    $dist[$label] = ($dist[$label] ?? 0) + 1;
                    $matched      = true;
                    break;
                }
            }
            if (! $matched) {
                // p > 0 tapi belum capai threshold pertama = Pantau
                if ($p > 0) {
                    $dist['Pantau'] = ($dist['Pantau'] ?? 0) + 1;
                }
                // p === 0 → tidak dihitung di sini, akan masuk Aman di bawah
            }
        }

        // Hitung Aman: siswa tanpa transaksi + siswa dengan total_pelanggaran = 0
        $siswaDenganTransaksi = $aggAll->count();
        $siswaPoinNol         = $aggAll->filter(fn($p) => $p === 0)->count();
        $siswaTanpaTransaksi  = max(0, $totalSiswa - $siswaDenganTransaksi);
        $dist['Aman']         = $siswaPoinNol + $siswaTanpaTransaksi;

        // Pastikan urutan: Aman → Pantau → threshold asc
        $distOrdered = ['Aman' => $dist['Aman']];
        if (isset($dist['Pantau'])) {
            $distOrdered['Pantau'] = $dist['Pantau'];
        }
        foreach ($thresholds->sortBy('poin') as $t) {
            $label               = $t['tindakan'] ?? ('Panggilan ' . $t['batas_ke']);
            $distOrdered[$label] = $dist[$label] ?? 0;
        }
        $dist = $distOrdered;

        // ── Tren bulanan — semua dari tbltransaksi ────────────────────
        $tren = TransaksiPoin::query()
            ->selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as bulan, SUM(poinp) as pel, SUM(poinr) as pen")
            ->where('thajaran', $tahunAjaran)
            ->whereNotNull('tanggal')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // ── Pelanggaran & penghargaan per kelas — dari tbltransaksi ───
        $perKelas = TransaksiPoin::query()
            ->selectRaw('nmkelas, SUM(poinp) as pel, SUM(poinr) as pen')
            ->where('thajaran', $tahunAjaran)
            ->whereNotNull('nmkelas')
            ->where('nmkelas', '!=', '')
            ->groupBy('nmkelas')
            ->orderBy('nmkelas')
            ->get();

        // ── Top 10 pelanggaran tertinggi — hanya baris poinp > 0 ─────
        // Group by siswa lalu SUM poinp, filter siswa yang punya transaksi pelanggaran.
        $topPelanggaran = TransaksiPoin::query()
            ->selectRaw('siswa_id, SUM(poinp) as total')
            ->where('thajaran', $tahunAjaran)
            ->where('poinp', '>', 0)
            ->groupBy('siswa_id')
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->limit(10)
            ->with('siswa.kelas')
            ->get();

        // ── Top 10 penghargaan tertinggi — hanya baris poinr > 0 ─────
        $topPenghargaan = TransaksiPoin::query()
            ->selectRaw('siswa_id, SUM(poinr) as total')
            ->where('thajaran', $tahunAjaran)
            ->where('poinr', '>', 0)
            ->groupBy('siswa_id')
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->limit(10)
            ->with('siswa.kelas')
            ->get();

        // ── Top jenis pelanggaran — dari tbltransaksi join ke subpasal ─
        // Ambil idpasal yang paling sering muncul di transaksi pelanggaran,
        // lalu join ke tblsubpasal untuk mendapatkan nama pasal.
        // Pakai DB::table() bukan TransaksiPoin::query() agar terhindar dari
        // auto-inject SoftDeletes (deleted_at) dengan alias tabel yang salah.
        $topPasal = DB::table('tbltransaksi as t')
            ->selectRaw('t.idpasal, COUNT(*) as jumlah, SUM(t.poinp) as total_poin, sp.pasal as isi')
            ->leftJoin(
                DB::raw('(SELECT s1.idpasal, s1.pasal
                          FROM tblsubpasal s1
                          INNER JOIN (
                              SELECT idpasal, MAX(thnajaran) AS max_thn
                              FROM tblsubpasal
                              GROUP BY idpasal
                          ) s2 ON s1.idpasal = s2.idpasal AND s1.thnajaran = s2.max_thn
                          GROUP BY s1.idpasal, s1.pasal
                         ) sp'),
                't.idpasal',
                '=',
                'sp.idpasal'
            )
            ->where('t.thajaran', $tahunAjaran)
            ->where('t.poinp', '>', 0)
            ->whereNotNull('t.idpasal')
            ->where('t.idpasal', '!=', '')
            ->whereNull('t.deleted_at')           // soft-delete manual karena pakai DB::table
            ->groupBy('t.idpasal', 'sp.pasal')
            ->orderByDesc('jumlah')
            ->limit(8)
            ->get();

        // ── Siswa yang melampaui setiap threshold ─────────────────────
        $thresholdCounts = [];
        foreach ($thresholds->sortBy('poin') as $t) {
            $thresholdCounts[] = [
                'label'  => $t['tindakan'] ?? ('Panggilan ' . $t['batas_ke']),
                'poin'   => (int) $t['poin'],
                'jumlah' => $aggAll->filter(fn($p) => $p >= (int) $t['poin'])->count(),
            ];
        }

        // ── Daftar tahun ajaran yang tersedia ─────────────────────────
        $tahunList = TransaksiPoin::select('thajaran')
            ->distinct()
            ->orderByDesc('thajaran')
            ->pluck('thajaran');

        return view('admin.rekap_poin.chart', compact(
            'tahunAjaran',
            'poinAwal',
            'totalSiswa',
            'totalPelanggaran',
            'totalPenghargaan',
            'dist',
            'tren',
            'perKelas',
            'topPelanggaran',
            'topPenghargaan',
            'topPasal',
            'thresholdCounts',
            'tahunList'
        ));
    }

    public function show(Request $request, Siswa $siswa): View
    {
        if ($request->user()->hasRole('siswa') && $this->siswaLogin($request)->id !== $siswa->id) {
            abort(403);
        }

        $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());
        $siswa->load('kelas');

        $totalPelanggaran = $this->tatibPoin->totalPelanggaran($siswa, $tahunAjaran);
        $totalPenghargaan = $this->tatibPoin->totalPenghargaan($siswa, $tahunAjaran);
        $poinAwal         = $this->tatibPoin->poinAwalEdaran();
        $sisaPoin         = $this->tatibPoin->hitungTotalPoin($totalPelanggaran, $totalPenghargaan);
        // Semua histori dipakai untuk data chart (tren bulanan)
        $histori          = $this->tatibPoin->historiTransaksi($siswa, $tahunAjaran);
        // Histori paginated dipakai untuk tabel
        $historiPaginated = $this->tatibPoin->historiTransaksiPaginated($siswa, $tahunAjaran, 15)
            ->appends(['tahun_ajaran' => $tahunAjaran]);
        $thresholds       = $this->tatibPoin->thresholdStatus($siswa, $tahunAjaran);

        return view('admin.rekap_poin.show', compact(
            'siswa',
            'tahunAjaran',
            'totalPelanggaran',
            'totalPenghargaan',
            'poinAwal',
            'sisaPoin',
            'histori',
            'historiPaginated',
            'thresholds'
        ));
    }

    private function siswaLogin(Request $request): Siswa
    {
        $user = $request->user();

        if ($user->siswa_id) {
            return Siswa::findOrFail($user->siswa_id);
        }

        return $user->siswa()->firstOrFail();
    }
}
