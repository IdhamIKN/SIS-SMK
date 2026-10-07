<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Services\TatibPoinService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JurnalTatibController extends Controller
{
    public function __construct(
        private readonly TatibPoinService $tatibPoin
    ) {}

    /**
     * Halaman form cetak jurnal laporan pelanggaran/penghargaan.
     */
    public function index(Request $request): View
    {
        $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());
        $kelas       = Kelas::orderBy('nama_kelas')->get();

        return view('admin.tatib.jurnal.index', compact('kelas', 'tahunAjaran'));
    }

    /**
     * Generate dan download PDF jurnal laporan secara langsung.
     * Dipanggil dari route: GET /admin/tatib/jurnal/cetak
     *
     * Optimisasi:
     * - Single JOIN query (tidak ada N+1 Eloquent eager-load)
     * - Composite index (thajaran, tanggal) dimanfaatkan penuh
     * - $totalPoinPeriode loop dihapus (tidak dipakai blade)
     * - Sisa poin dihitung dari satu aggregate query
     * - Jika total baris > THRESHOLD_PDF → tampilkan HTML print view
     *   (DomPDF tidak mampu memuat puluhan ribu baris sekaligus)
     */
    private const THRESHOLD_PDF = 500; // maks baris flat sebelum fallback ke HTML print

    public function cetak(Request $request): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
    {
        $request->validate([
            'dari_tanggal'   => ['required', 'date'],
            'sampai_tanggal' => ['required', 'date', 'after_or_equal:dari_tanggal'],
            'jenis'          => ['required', 'in:pelanggaran,penghargaan,semua'],
            'tahun_ajaran'   => ['required', 'string', 'max:9'],
            'kelas_id'       => ['nullable', 'exists:kelas,id'],
            'show_detail'    => ['nullable', 'in:0,1'],
        ]);

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $dariTanggal   = Carbon::parse($request->dari_tanggal)->startOfDay();
        $sampaiTanggal = Carbon::parse($request->sampai_tanggal)->endOfDay();
        $jenis         = $request->jenis;
        $tahunAjaran   = $request->tahun_ajaran;
        $kelasId       = $request->kelas_id;
        // show_detail: checkbox dikirim sebagai "1" jika dicentang, tidak ada jika tidak
        $showDetail    = (bool) $request->input('show_detail', 0);

        // ── Query tunggal dengan JOIN ──────────────────────────────────────
        // Menggantikan TransaksiPoin::with(['siswa.kelas', 'creator'])
        // yang menghasilkan 3× round-trip ke database untuk setiap batch.
        // Composite index (thajaran, tanggal) memastikan range scan efisien.
        $query = DB::table('tbltransaksi AS t')
            ->join('siswas AS s', 's.id', '=', 't.siswa_id')
            ->join('kelas AS k', 'k.id', '=', 's.kelas_id')
            ->leftJoin('users AS u', 'u.id', '=', 't.created_by')
            ->select([
                't.idtrans',
                't.siswa_id',
                't.tanggal',
                't.poinp',
                't.poinr',
                't.idpasal',
                't.ket',
                's.nama_lengkap AS nama',
                's.nis',
                's.nisn',
                's.kelas_id',
                'k.nama_kelas',
                'u.name AS pelapor',
            ])
            ->where('t.thajaran', $tahunAjaran)
            ->whereBetween('t.tanggal', [
                $dariTanggal->format('Y-m-d H:i:s'),
                $sampaiTanggal->format('Y-m-d H:i:s'),
            ])
            ->whereNull('t.deleted_at');

        // Filter poin sesuai jenis laporan
        if ($jenis === 'pelanggaran') {
            $query->where('t.poinp', '>', 0);
        } elseif ($jenis === 'penghargaan') {
            $query->where('t.poinr', '>', 0);
        } else {
            $query->where(fn($q) => $q->where('t.poinp', '>', 0)->orWhere('t.poinr', '>', 0));
        }

        // Filter per kelas jika dipilih
        if ($kelasId) {
            $query->where('s.kelas_id', $kelasId);
        }

        $rawRows = $query->orderBy('t.tanggal')->get();

        // ── Normalisasi flat rows ─────────────────────────────────────────
        // Satu record bisa jadi dua baris jika punya poinp DAN poinr sekaligus.
        $allRows = collect();
        foreach ($rawRows as $item) {
            $base = [
                'siswa_id' => $item->siswa_id,
                'nama'     => $item->nama     ?? '-',
                'nis'      => $item->nis       ?? '-',
                'nisn'     => $item->nisn      ?? '-',
                'kelas'    => $item->nama_kelas ?? 'Tanpa Kelas',
                'kelas_id' => $item->kelas_id  ?? 0,
                'idpasal'  => $item->idpasal,
                'pelapor'  => $item->pelapor   ?? '-',
            ];

            if ($item->poinp > 0 && in_array($jenis, ['pelanggaran', 'semua'])) {
                $allRows->push($base + [
                    'tgl'   => $item->tanggal,
                    'jenis' => 'pelanggaran',
                    'poin'  => (int) $item->poinp,
                ]);
            }
            if ($item->poinr > 0 && in_array($jenis, ['penghargaan', 'semua'])) {
                $allRows->push($base + [
                    'tgl'   => $item->tanggal,
                    'jenis' => 'penghargaan',
                    'poin'  => (int) $item->poinr,
                ]);
            }
        }
        unset($rawRows); // bebaskan memori segera

        // ── Kelompokkan per kelas → per siswa ────────────────────────────
        $dataPerKelas = $allRows
            ->groupBy('kelas_id')
            ->map(function ($items) {
                $namaKelas = $items->first()['kelas'] ?? 'Tanpa Kelas';

                $perSiswa = $items
                    ->groupBy('siswa_id')
                    ->map(function ($siswaRows) {
                        $first = $siswaRows->first();
                        return [
                            'siswa_id' => $first['siswa_id'],
                            'nama'     => $first['nama'],
                            'nis'      => $first['nis'],
                            'nisn'     => $first['nisn'],
                            'rows'     => $siswaRows->sortBy('tgl')->values()->toArray(),
                        ];
                    })
                    ->sortBy('nama')
                    ->values();

                return [
                    'nama_kelas' => $namaKelas,
                    'items'      => $items->values(),
                    'siswa'      => $perSiswa,
                ];
            })
            ->sortBy('nama_kelas')
            ->values();

        // ── Sisa poin keseluruhan tahun ajaran (satu aggregate query) ────
        $sisaPoinMap = [];
        $siswaIds    = $allRows->pluck('siswa_id')->filter()->unique()->values();
        unset($allRows); // bebaskan memori sebelum PDF render

        if ($siswaIds->isNotEmpty()) {
            DB::table('tbltransaksi')
                ->selectRaw('siswa_id, SUM(poinp) AS total_pel, SUM(poinr) AS total_prg')
                ->where('thajaran', $tahunAjaran)
                ->whereIn('siswa_id', $siswaIds)
                ->whereNull('deleted_at')
                ->groupBy('siswa_id')
                ->get()
                ->each(function ($row) use (&$sisaPoinMap) {
                    $sisaPoinMap[$row->siswa_id] = $this->sisaPoinJurnal(
                        (int) $row->total_pel,
                        (int) $row->total_prg,
                    );
                });
        }

        // ── Data pendukung ────────────────────────────────────────────────
        $sekolah      = sekolah_data();
        $tanggalCetak = now();
        $cetakUser    = auth()->user()?->name ?? '-';

        // Logo di-embed sebagai base64 agar DomPDF tidak memerlukan akses URL
        $logoSmkPath   = public_path('images/logo/smk.png');
        $logoJatimPath = public_path('images/logo/jatim.png');
        if (! file_exists($logoSmkPath))   $logoSmkPath   = resource_path('views/pdf/logo/smk.png');
        if (! file_exists($logoJatimPath)) $logoJatimPath = resource_path('views/pdf/logo/jatim.png');
        $logoSmk   = file_exists($logoSmkPath)   ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoSmkPath))   : null;
        $logoJatim = file_exists($logoJatimPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoJatimPath)) : null;

        // ── Render PDF ────────────────────────────────────────────────────
        // Jika total baris melebihi threshold, DomPDF akan kehabisan memori.
        // Solusi: render sebagai HTML print view — browser jauh lebih efisien
        // untuk data besar. User cukup Ctrl+P untuk mencetak.
        $totalFlatRows = collect($dataPerKelas)->sum(fn($k) => collect($k['siswa'])->sum(fn($s) => count($s['rows'])));
        $useHtmlPrint  = $totalFlatRows > self::THRESHOLD_PDF;

        $viewData = [
            'dataPerKelas'  => $dataPerKelas,
            'sisaPoinMap'   => $sisaPoinMap,
            'jenis'         => $jenis,
            'tahunAjaran'   => $tahunAjaran,
            'dariTanggal'   => $dariTanggal,
            'sampaiTanggal' => $sampaiTanggal,
            'sekolah'       => $sekolah,
            'logoSmk'       => $logoSmk,
            'logoJatim'     => $logoJatim,
            'tanggalCetak'  => $tanggalCetak,
            'cetakUser'     => $cetakUser,
            'printFallback' => $useHtmlPrint,
            'showDetail'    => $showDetail,
        ];

        if ($useHtmlPrint) {
            // Kembalikan HTML langsung — browser handle print/save as PDF
            return response(
                view('admin.tatib.jurnal.cetak', $viewData)->render(),
                200,
                ['Content-Type' => 'text/html; charset=UTF-8']
            );
        }

        $pdf = Pdf::loadView('admin.tatib.jurnal.cetak', $viewData)
            ->setPaper('a4', 'landscape')
            ->setOption([
                'defaultFont'             => 'sans-serif',
                'isHtml5ParserEnabled'    => true,
                'isRemoteEnabled'         => false,
                'isFontSubsettingEnabled' => true,
                'dpi'                     => 96,
                'enable_javascript'       => false,
                'enable_remote'           => false,
            ]);

        $namaFile = 'jurnal-tatib-'
            . $dariTanggal->format('Ymd') . '-'
            . $sampaiTanggal->format('Ymd') . '.pdf';

        return $pdf->download($namaFile);
    }

    /**
     * AJAX: cari siswa untuk autocomplete cetak per-siswa.
     * Mengembalikan JSON { results: [ {id, text, nisn, kelas} ] }
     */
    public function siswaSearchJurnal(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = trim($request->get('q', ''));

        $siswa = Siswa::with('kelas')
            ->where(
                fn($sq) =>
                $sq->where('nama_lengkap', 'like', "%{$q}%")
                    ->orWhere('nis',        'like', "%{$q}%")
                    ->orWhere('nisn',       'like', "%{$q}%")
            )
            ->where('status_aktif', true)
            ->orderBy('nama_lengkap')
            ->limit(20)
            ->get()
            ->map(fn($s) => [
                'id'    => $s->id,
                'text'  => $s->nama_lengkap,
                'nisn'  => $s->nisn ?? $s->nis ?? '-',
                'kelas' => $s->kelas?->nama_kelas ?? '-',
            ]);

        return response()->json(['results' => $siswa]);
    }

    /**
     * Cetak jurnal laporan untuk satu siswa tertentu.
     * Dipanggil dari route: GET /admin/tatib/jurnal/cetak-siswa
     * Selalu menghasilkan PDF (data per-siswa pasti kecil).
     */
    public function cetakSiswa(Request $request): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'siswa_id'       => ['required', 'exists:siswas,id'],
            'dari_tanggal'   => ['required', 'date'],
            'sampai_tanggal' => ['required', 'date', 'after_or_equal:dari_tanggal'],
            'jenis'          => ['required', 'in:pelanggaran,penghargaan,semua'],
            'tahun_ajaran'   => ['required', 'string', 'max:9'],
            'show_detail'    => ['nullable', 'in:0,1'],
        ]);

        ini_set('memory_limit', '256M');
        set_time_limit(120);

        $siswaId       = (int) $request->siswa_id;
        $dariTanggal   = Carbon::parse($request->dari_tanggal)->startOfDay();
        $sampaiTanggal = Carbon::parse($request->sampai_tanggal)->endOfDay();
        $jenis         = $request->jenis;
        $tahunAjaran   = $request->tahun_ajaran;
        $showDetail    = (bool) $request->input('show_detail', 0);

        // ── Query: hanya untuk siswa ini ─────────────────────────────────
        $query = DB::table('tbltransaksi AS t')
            ->join('siswas AS s', 's.id', '=', 't.siswa_id')
            ->join('kelas AS k', 'k.id', '=', 's.kelas_id')
            ->leftJoin('users AS u', 'u.id', '=', 't.created_by')
            ->select([
                't.siswa_id',
                't.tanggal',
                't.poinp',
                't.poinr',
                't.idpasal',
                't.ket',
                's.nama_lengkap AS nama',
                's.nis',
                's.nisn',
                's.kelas_id',
                'k.nama_kelas',
                'u.name AS pelapor',
            ])
            ->where('t.thajaran', $tahunAjaran)
            ->whereBetween('t.tanggal', [
                $dariTanggal->format('Y-m-d H:i:s'),
                $sampaiTanggal->format('Y-m-d H:i:s'),
            ])
            ->where('t.siswa_id', $siswaId)
            ->whereNull('t.deleted_at');

        if ($jenis === 'pelanggaran') {
            $query->where('t.poinp', '>', 0);
        } elseif ($jenis === 'penghargaan') {
            $query->where('t.poinr', '>', 0);
        } else {
            $query->where(fn($q) => $q->where('t.poinp', '>', 0)->orWhere('t.poinr', '>', 0));
        }

        $rawRows = $query->orderBy('t.tanggal')->get();

        // ── Normalisasi flat rows ─────────────────────────────────────────
        $allRows = collect();
        foreach ($rawRows as $item) {
            $base = [
                'siswa_id' => $item->siswa_id,
                'nama'     => $item->nama      ?? '-',
                'nis'      => $item->nis        ?? '-',
                'nisn'     => $item->nisn       ?? '-',
                'kelas'    => $item->nama_kelas ?? 'Tanpa Kelas',
                'kelas_id' => $item->kelas_id   ?? 0,
                'idpasal'  => $item->idpasal,
                'pelapor'  => $item->pelapor    ?? '-',
            ];
            if ($item->poinp > 0 && in_array($jenis, ['pelanggaran', 'semua'])) {
                $allRows->push($base + ['tgl' => $item->tanggal, 'jenis' => 'pelanggaran', 'poin' => (int) $item->poinp]);
            }
            if ($item->poinr > 0 && in_array($jenis, ['penghargaan', 'semua'])) {
                $allRows->push($base + ['tgl' => $item->tanggal, 'jenis' => 'penghargaan', 'poin' => (int) $item->poinr]);
            }
        }

        // ── Struktur dataPerKelas ─────────────────────────────────────────
        $dataPerKelas = $allRows
            ->groupBy('kelas_id')
            ->map(function ($items) {
                $namaKelas = $items->first()['kelas'] ?? 'Tanpa Kelas';
                $perSiswa  = $items
                    ->groupBy('siswa_id')
                    ->map(function ($siswaRows) {
                        $first = $siswaRows->first();
                        return [
                            'siswa_id' => $first['siswa_id'],
                            'nama'     => $first['nama'],
                            'nis'      => $first['nis'],
                            'nisn'     => $first['nisn'],
                            'rows'     => $siswaRows->sortBy('tgl')->values()->toArray(),
                        ];
                    })
                    ->sortBy('nama')
                    ->values();
                return [
                    'nama_kelas' => $namaKelas,
                    'items'      => $items->values(),
                    'siswa'      => $perSiswa,
                ];
            })
            ->sortBy('nama_kelas')
            ->values();

        // ── Sisa poin tahun ajaran ────────────────────────────────────────
        $agg = DB::table('tbltransaksi')
            ->selectRaw('SUM(poinp) AS p, SUM(poinr) AS r')
            ->where('thajaran', $tahunAjaran)
            ->where('siswa_id', $siswaId)
            ->whereNull('deleted_at')
            ->first();

        $sisaPoinMap = [
            $siswaId => $this->sisaPoinJurnal(
                (int) ($agg?->p ?? 0),
                (int) ($agg?->r ?? 0),
            ),
        ];

        // ── Data pendukung ────────────────────────────────────────────────
        $sekolah      = sekolah_data();
        $tanggalCetak = now();
        $cetakUser    = auth()->user()?->name ?? '-';

        $logoSmkPath   = public_path('images/logo/smk.png');
        $logoJatimPath = public_path('images/logo/jatim.png');
        if (! file_exists($logoSmkPath))   $logoSmkPath   = resource_path('views/pdf/logo/smk.png');
        if (! file_exists($logoJatimPath)) $logoJatimPath = resource_path('views/pdf/logo/jatim.png');
        $logoSmk   = file_exists($logoSmkPath)   ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoSmkPath))   : null;
        $logoJatim = file_exists($logoJatimPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoJatimPath)) : null;

        // Ambil data identitas siswa dari hasil normalisasi
        $firstRow  = $allRows->first();
        $namaSiswa = $firstRow['nama'] ?? '-';
        $nisSiswa  = $firstRow['nis']  ?? '-';
        $nisnSiswa = $firstRow['nisn'] ?? '-';

        // Per-siswa pakai view khusus (portrait, layout ringkasan di bawah tabel)
        $pdf = Pdf::loadView('admin.tatib.jurnal.cetak-siswa', [
            'dataPerKelas'  => $dataPerKelas,
            'sisaPoinMap'   => $sisaPoinMap,
            'siswaId'       => $siswaId,
            'namaSiswa'     => $namaSiswa,
            'nisSiswa'      => $nisSiswa,
            'nisnSiswa'     => $nisnSiswa,
            'jenis'         => $jenis,
            'tahunAjaran'   => $tahunAjaran,
            'dariTanggal'   => $dariTanggal,
            'sampaiTanggal' => $sampaiTanggal,
            'sekolah'       => $sekolah,
            'logoSmk'       => $logoSmk,
            'logoJatim'     => $logoJatim,
            'tanggalCetak'  => $tanggalCetak,
            'cetakUser'     => $cetakUser,
            'showDetail'    => $showDetail,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption([
                'defaultFont'             => 'sans-serif',
                'isHtml5ParserEnabled'    => true,
                'isRemoteEnabled'         => false,
                'isFontSubsettingEnabled' => true,
                'dpi'                     => 96,
                'enable_javascript'       => false,
                'enable_remote'           => false,
            ]);

        $nisLabel = preg_replace('/[^a-z0-9]/', '-', strtolower($nisSiswa ?: 'siswa'));
        $namaFile = 'jurnal-' . $nisLabel
            . '-' . $dariTanggal->format('Ymd')
            . '-' . $sampaiTanggal->format('Ymd')
            . '.pdf';

        return $pdf->download($namaFile);
    }

    /**
     * Hitung sisa poin khusus untuk tampilan jurnal.
     * Berbeda dengan hitungTotalPoin() yang di-clamp ke minimum 0,
     * metode ini mengembalikan nilai asli (bisa negatif) agar jurnal
     * menampilkan kondisi poin yang sebenarnya ketika pelanggaran
     * melebihi poin awal + penghargaan.
     */
    private function sisaPoinJurnal(int $totalPelanggaran, int $totalPenghargaan): int
    {
        return TatibPoinService::POIN_AWAL_EDARAN + $totalPenghargaan - $totalPelanggaran;
    }
}
