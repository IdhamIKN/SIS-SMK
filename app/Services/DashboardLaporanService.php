<?php

namespace App\Services;

use App\Models\AbsenSiswa;
use App\Models\AbsenEvent;
use App\Models\AbsenEventGuru;
use App\Models\Event;
use App\Models\EventGuru;
use App\Models\GTK;
use App\Models\Kelas;
use App\Models\LaporanKehadiranGuru;
use App\Models\PengajuanIzin;
use App\Models\Pelanggaran;
use App\Models\Penghargaan;
use App\Models\Siswa;
use App\Models\SuratPanggilan;
use App\Models\WaLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardLaporanService
{
    private Carbon $dateFrom;
    private Carbon $dateTo;
    private ?string $kelasId;
    private ?string $tingkat;
    private ?string $shift;

    public function __construct(
        Carbon $dateFrom,
        Carbon $dateTo,
        ?string $kelasId = null,
        ?string $tingkat = null,
        ?string $shift = null
    ) {
        $this->dateFrom = $dateFrom->startOfDay()->copy();
        $this->dateTo   = $dateTo->endOfDay()->copy();
        $this->kelasId  = $kelasId ?: null;
        $this->tingkat  = $tingkat ?: null;
        $this->shift    = $shift   ?: null;
    }

    /**
     * Base query AbsenSiswa sudah terfilter tanggal + kelas_id / tingkat / shift.
     *
     * Prioritas: jika kelas_id diisi, tingkat & shift diabaikan karena sudah spesifik.
     * Jika hanya tingkat/shift yang diisi, join ke tabel kelas untuk filter.
     */
    private function absenQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = AbsenSiswa::whereBetween('tanggal', [
            $this->dateFrom->toDateString(),
            $this->dateTo->toDateString(),
        ]);

        if ($this->kelasId) {
            $q->where('absen_siswa.kelas_id', $this->kelasId);
        } elseif ($this->tingkat || $this->shift) {
            $q->whereHas('kelas', function ($kq) {
                if ($this->tingkat) $kq->where('tingkat', $this->tingkat);
                if ($this->shift)   $kq->where('shift',   $this->shift);
            });
        }

        return $q;
    }

    /**
     * Sama seperti absenQuery() tapi return DB\Builder (untuk select raw yang complex).
     * Melakukan join ke kelas secara eksplisit agar bisa pakai kelas.tingkat / kelas.shift.
     */
    private function absenRawQuery(): \Illuminate\Database\Query\Builder
    {
        $q = DB::table('absen_siswa')
            ->whereBetween('absen_siswa.tanggal', [
                $this->dateFrom->toDateString(),
                $this->dateTo->toDateString(),
            ]);

        if ($this->kelasId) {
            $q->where('absen_siswa.kelas_id', $this->kelasId);
        } elseif ($this->tingkat || $this->shift) {
            $q->join('kelas', 'kelas.id', '=', 'absen_siswa.kelas_id');
            if ($this->tingkat) $q->where('kelas.tingkat', $this->tingkat);
            if ($this->shift)   $q->where('kelas.shift',   $this->shift);
        }

        return $q;
    }

    /**
     * Terapkan filter kelas_id / tingkat / shift ke query berbasis `tbltransaksi`
     * (dipakai oleh tren & top pelanggaran/penghargaan) dengan join ke `siswas`
     * (dan `kelas` bila perlu). Tanpa ini, filter kelas/tingkat/shift di panel
     * filter dashboard tidak berpengaruh pada seksi Tata Tertib — sebelumnya
     * seksi ini selalu menampilkan data global terlepas dari filter yang dipilih.
     *
     * Join memakai alias unik (_tsc / _tsc_k) supaya aman dipanggil walau
     * caller sudah punya join lain dengan nama alias berbeda.
     */
    private function applyStudentScope(\Illuminate\Database\Query\Builder $query, string $transaksiAlias): void
    {
        if (!$this->kelasId && !$this->tingkat && !$this->shift) {
            return;
        }

        $query->join('siswas as _tsc', "{$transaksiAlias}.siswa_id", '=', '_tsc.id');

        if ($this->kelasId) {
            $query->where('_tsc.kelas_id', $this->kelasId);
        } else {
            $query->join('kelas as _tsc_k', '_tsc.kelas_id', '=', '_tsc_k.id');
            if ($this->tingkat) $query->where('_tsc_k.tingkat', $this->tingkat);
            if ($this->shift)   $query->where('_tsc_k.shift', $this->shift);
        }
    }

    /* ──────────────────────────────────────────────────────────────
     *  1. KPI RINGKASAN
     * ────────────────────────────────────────────────────────────── */
    public function getKpiRingkasan(): array
    {
        $absenQ = $this->absenQuery();

        $hadir     = (clone $absenQ)->where('status_masuk', 'hadir')->count();
        $terlambat = (clone $absenQ)->where('status_masuk', 'terlambat')->count();
        $alfa      = (clone $absenQ)->where(function ($q) {
            $q->where('status_masuk', 'alfa')->orWhere(function ($q2) {
                $q2->whereNull('status_masuk')->where('status', 'alfa');
            });
        })->count();
        $izin      = (clone $absenQ)->where(function ($q) {
            $q->whereIn('status_masuk', ['izin', 'sakit'])->orWhere(function ($q2) {
                $q2->whereNull('status_masuk')->whereIn('status', ['izin', 'sakit']);
            });
        })->count();

        $totalSiswa = Siswa::where('status_aktif', true);
        if ($this->kelasId) {
            $totalSiswa->where('kelas_id', $this->kelasId);
        } elseif ($this->tingkat || $this->shift) {
            $totalSiswa->whereHas('kelas', function ($kq) {
                if ($this->tingkat) $kq->where('tingkat', $this->tingkat);
                if ($this->shift)   $kq->where('shift',   $this->shift);
            });
        }
        $totalSiswa = $totalSiswa->count();

        // Gunakan tbltransaksi sebagai sumber kebenaran poin
        $pelanggaranQ = DB::table('tbltransaksi as t')
            ->whereDate('t.tanggal', '>=', $this->dateFrom->toDateString())
            ->whereDate('t.tanggal', '<=', $this->dateTo->toDateString())
            ->where('t.poinp', '>', 0)
            ->whereNull('t.deleted_at');
        $this->applyStudentScope($pelanggaranQ, 't');
        $totalPelanggaran = $pelanggaranQ->count();

        $penghargaanQ = DB::table('tbltransaksi as t')
            ->whereDate('t.tanggal', '>=', $this->dateFrom->toDateString())
            ->whereDate('t.tanggal', '<=', $this->dateTo->toDateString())
            ->where('t.poinr', '>', 0)
            ->whereNull('t.deleted_at');
        $this->applyStudentScope($penghargaanQ, 't');
        $totalPenghargaan = $penghargaanQ->count();

        $waTerkirim = WaLog::whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->where('status', 'sukses')->count();
        $waGagal = WaLog::whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->whereIn('status', ['gagal', 'failed', 'error'])->count();

        $suratPanggilan = SuratPanggilan::whereBetween('tanggal_surat', [$this->dateFrom->toDateString(), $this->dateTo->toDateString()])->count();

        $izinPending = PengajuanIzin::whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->where('status', 'diajukan')->count();

        return compact(
            'hadir',
            'terlambat',
            'alfa',
            'izin',
            'totalSiswa',
            'totalPelanggaran',
            'totalPenghargaan',
            'waTerkirim',
            'waGagal',
            'suratPanggilan',
            'izinPending'
        );
    }

    /* ──────────────────────────────────────────────────────────────
     *  2. TREN KEHADIRAN SISWA (per hari/minggu/bulan)
     * ────────────────────────────────────────────────────────────── */
    public function getTrenKehadiran(): array
    {
        $q = $this->absenQuery()
            ->select(
                DB::raw('DATE(tanggal) as tgl'),
                DB::raw('SUM(CASE WHEN COALESCE(status_masuk,status)="hadir" THEN 1 ELSE 0 END) as hadir'),
                DB::raw('SUM(CASE WHEN COALESCE(status_masuk,status)="terlambat" THEN 1 ELSE 0 END) as terlambat'),
                DB::raw('SUM(CASE WHEN COALESCE(status_masuk,status)="alfa" THEN 1 ELSE 0 END) as alfa'),
                DB::raw('SUM(CASE WHEN COALESCE(status_masuk,status)="sakit" THEN 1 ELSE 0 END) as sakit'),
                DB::raw('SUM(CASE WHEN COALESCE(status_masuk,status)="izin" THEN 1 ELSE 0 END) as izin'),
                DB::raw('SUM(CASE WHEN COALESCE(status_masuk,status)="pkl" THEN 1 ELSE 0 END) as pkl')
            );

        return $q->groupBy('tgl')->orderBy('tgl')->get()->toArray();
    }

    /* ──────────────────────────────────────────────────────────────
     *  3. DISTRIBUSI STATUS KEHADIRAN (donut)
     * ────────────────────────────────────────────────────────────── */
    public function getDistribusiStatus(): array
    {
        $q = $this->absenQuery()
            ->select(DB::raw('COALESCE(status_masuk, status) as status_eff'), DB::raw('COUNT(*) as total'));

        return $q->groupBy('status_eff')->get()->pluck('total', 'status_eff')->toArray();
    }

    /* ──────────────────────────────────────────────────────────────
     *  4. HEATMAP KEHADIRAN PER KELAS
     * ────────────────────────────────────────────────────────────── */
    public function getHeatmapKelas(): array
    {
        $q = $this->absenQuery()
            ->select(
                'absen_siswa.kelas_id',
                DB::raw('DATE(absen_siswa.tanggal) as tgl'),
                DB::raw('SUM(CASE WHEN COALESCE(absen_siswa.status_masuk,absen_siswa.status) IN ("hadir","terlambat") THEN 1 ELSE 0 END) as hadir'),
                DB::raw('COUNT(*) as total')
            );

        $rows = $q->groupBy('absen_siswa.kelas_id', 'tgl')->get();

        $kelas = Kelas::pluck('nama_kelas', 'id');
        $result = [];
        foreach ($rows as $row) {
            $pct = $row->total > 0 ? round($row->hadir / $row->total * 100) : 0;
            $result[] = [
                'kelas'   => $kelas[$row->kelas_id] ?? 'Kelas #' . $row->kelas_id,
                'tanggal' => $row->tgl,
                'pct'     => $pct,
            ];
        }
        return $result;
    }

    /* ──────────────────────────────────────────────────────────────
     *  5. DISTRIBUSI JAM KEDATANGAN
     * ────────────────────────────────────────────────────────────── */
    public function getDistribusiJamMasuk(): array
    {
        $q = $this->absenQuery()
            ->select(
                DB::raw('HOUR(COALESCE(jam_masuk, waktu_absen)) as jam'),
                DB::raw('COUNT(*) as total')
            )
            ->where(function ($q) {
                $q->whereNotNull('jam_masuk')
                    ->orWhereNotNull('waktu_absen');
            })
            ->whereNotNull('jam_masuk');

        return $q->groupBy('jam')->orderBy('jam')->get()->pluck('total', 'jam')->toArray();
    }

    /* ──────────────────────────────────────────────────────────────
     *  6. RANKING KELAS (% kehadiran tertinggi/terendah)
     * ────────────────────────────────────────────────────────────── */
    public function getRankingKelas(): array
    {
        $q = $this->absenQuery()
            ->select(
                'absen_siswa.kelas_id',
                DB::raw('SUM(CASE WHEN COALESCE(absen_siswa.status_masuk,absen_siswa.status) IN ("hadir","terlambat") THEN 1 ELSE 0 END) as hadir'),
                DB::raw('COUNT(*) as total')
            );

        $rows = $q->groupBy('absen_siswa.kelas_id')->get();
        $kelas = Kelas::pluck('nama_kelas', 'id');

        return $rows->map(fn($r) => [
            'kelas' => $kelas[$r->kelas_id] ?? '#' . $r->kelas_id,
            'pct'   => $r->total > 0 ? round($r->hadir / $r->total * 100, 1) : 0,
            'hadir' => $r->hadir,
            'total' => $r->total,
        ])->sortByDesc('pct')->values()->toArray();
    }

    /* ──────────────────────────────────────────────────────────────
     *  7. TREN & STATUS PENGAJUAN IZIN
     * ────────────────────────────────────────────────────────────── */
    public function getTrenIzin(): array
    {
        return PengajuanIzin::select(
            DB::raw('DATE(created_at) as tgl'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN jenis="izin_sakit" THEN 1 ELSE 0 END) as sakit'),
            DB::raw('SUM(CASE WHEN jenis="izin_pulang_cepat" THEN 1 ELSE 0 END) as pulang_cepat'),
            DB::raw('SUM(CASE WHEN jenis="izin_terlambat" THEN 1 ELSE 0 END) as terlambat'),
            DB::raw('SUM(CASE WHEN jenis="izin_lainnya" THEN 1 ELSE 0 END) as lainnya')
        )
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->groupBy('tgl')->orderBy('tgl')->get()->toArray();
    }

    public function getStatusIzin(): array
    {
        return PengajuanIzin::select('status', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->groupBy('status')->get()->pluck('total', 'status')->toArray();
    }

    public function getDistribusiJenisIzin(): array
    {
        // kolom di tabel adalah `jenis` (bukan jenis_izin)
        return PengajuanIzin::select('jenis', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->groupBy('jenis')->get()->pluck('total', 'jenis')->toArray();
    }

    /* ──────────────────────────────────────────────────────────────
     *  8. TATIB — TREN & TOP PELANGGARAN (dari tbltransaksi)
     * ────────────────────────────────────────────────────────────── */
    public function getTrenPelanggaran(): array
    {
        $q = DB::table('tbltransaksi as t')
            ->selectRaw('DATE(t.tanggal) as tgl, COUNT(*) as total')
            ->whereDate('t.tanggal', '>=', $this->dateFrom->toDateString())
            ->whereDate('t.tanggal', '<=', $this->dateTo->toDateString())
            ->where('t.poinp', '>', 0)
            ->whereNull('t.deleted_at');

        $this->applyStudentScope($q, 't');

        return $q->groupByRaw('DATE(t.tanggal)')
            ->orderByRaw('DATE(t.tanggal)')
            ->get()->toArray();
    }

    public function getTrenPenghargaan(): array
    {
        $q = DB::table('tbltransaksi as t')
            ->selectRaw('DATE(t.tanggal) as tgl, COUNT(*) as total')
            ->whereDate('t.tanggal', '>=', $this->dateFrom->toDateString())
            ->whereDate('t.tanggal', '<=', $this->dateTo->toDateString())
            ->where('t.poinr', '>', 0)
            ->whereNull('t.deleted_at');

        $this->applyStudentScope($q, 't');

        return $q->groupByRaw('DATE(t.tanggal)')
            ->orderByRaw('DATE(t.tanggal)')
            ->get()->toArray();
    }

    /**
     * Top 10 pasal pelanggaran (poinp > 0), digabung ke tblsubpasal
     * versi terbaru per idpasal.
     */
    public function getTopPelanggaran(): array
    {
        $q = DB::table('tbltransaksi as t')
            ->leftJoin(
                DB::raw('(SELECT s1.idpasal, s1.pasal
                          FROM tblsubpasal s1
                          INNER JOIN (SELECT idpasal, MAX(thnajaran) AS max_thn FROM tblsubpasal GROUP BY idpasal) s2
                          ON s1.idpasal = s2.idpasal AND s1.thnajaran = s2.max_thn
                          GROUP BY s1.idpasal, s1.pasal) sp'),
                't.idpasal',
                '=',
                'sp.idpasal'
            )
            ->selectRaw('COALESCE(sp.pasal, t.idpasal, "Tidak Diketahui") as nama, COUNT(*) as total')
            ->whereDate('t.tanggal', '>=', $this->dateFrom->toDateString())
            ->whereDate('t.tanggal', '<=', $this->dateTo->toDateString())
            ->where('t.poinp', '>', 0)
            ->whereNull('t.deleted_at')
            ->whereNotNull('t.idpasal')
            ->where('t.idpasal', '!=', '');

        $this->applyStudentScope($q, 't');

        // Group by kolom mentah (bukan ekspresi COALESCE) agar kompatibel
        // dengan sql_mode=ONLY_FULL_GROUP_BY. Aman karena subquery `sp`
        // sudah unik per idpasal (GROUP BY s1.idpasal, s1.pasal).
        return $q->groupBy('t.idpasal', 'sp.pasal')
            ->orderByDesc('total')
            ->limit(10)
            ->get()->toArray();
    }

    /**
     * Top 10 pasal penghargaan (poinr > 0). Sama strukturnya dengan
     * getTopPelanggaran(), hanya berbeda kolom poin yang difilter.
     *
     * Catatan: query ini mengasumsikan pasal penghargaan tersimpan di
     * tabel referensi yang sama (tblsubpasal, dikaitkan via idpasal) —
     * sama seperti yang dipakai pelanggaran. Jika penghargaan punya
     * tabel/kolom referensi sendiri (mis. idpasalr / tblpasal_penghargaan),
     * sesuaikan join di bawah ini.
     */
    public function getTopPenghargaan(): array
    {
        $q = DB::table('tbltransaksi as t')
            ->leftJoin(
                DB::raw('(SELECT s1.idpasal, s1.pasal
                          FROM tblsubpasal s1
                          INNER JOIN (SELECT idpasal, MAX(thnajaran) AS max_thn FROM tblsubpasal GROUP BY idpasal) s2
                          ON s1.idpasal = s2.idpasal AND s1.thnajaran = s2.max_thn
                          GROUP BY s1.idpasal, s1.pasal) sp'),
                't.idpasal',
                '=',
                'sp.idpasal'
            )
            ->selectRaw('COALESCE(sp.pasal, t.idpasal, "Tidak Diketahui") as nama, COUNT(*) as total')
            ->whereDate('t.tanggal', '>=', $this->dateFrom->toDateString())
            ->whereDate('t.tanggal', '<=', $this->dateTo->toDateString())
            ->where('t.poinr', '>', 0)
            ->whereNull('t.deleted_at')
            ->whereNotNull('t.idpasal')
            ->where('t.idpasal', '!=', '');

        $this->applyStudentScope($q, 't');

        return $q->groupBy('t.idpasal', 'sp.pasal')
            ->orderByDesc('total')
            ->limit(10)
            ->get()->toArray();
    }

    public function getTopSiswaPelanggaran(): array
    {
        $q = DB::table('tbltransaksi as t')
            ->join('siswas as s', 't.siswa_id', '=', 's.id')
            ->leftJoin('kelas as k', 's.kelas_id', '=', 'k.id')
            ->selectRaw('s.nama_lengkap, k.nama_kelas, SUM(t.poinp) as total_poin, COUNT(*) as jumlah')
            ->whereDate('t.tanggal', '>=', $this->dateFrom->toDateString())
            ->whereDate('t.tanggal', '<=', $this->dateTo->toDateString())
            ->where('t.poinp', '>', 0)
            ->whereNull('t.deleted_at');

        if ($this->kelasId) {
            $q->where('s.kelas_id', $this->kelasId);
        } else {
            if ($this->tingkat) $q->where('k.tingkat', $this->tingkat);
            if ($this->shift)   $q->where('k.shift', $this->shift);
        }

        return $q->groupBy('s.id', 's.nama_lengkap', 'k.nama_kelas')
            ->orderByDesc('total_poin')
            ->limit(10)
            ->get()->toArray();
    }

    /**
     * Top 10 siswa dengan poin penghargaan (poinr) tertinggi.
     */
    public function getTopSiswaPenghargaan(): array
    {
        $q = DB::table('tbltransaksi as t')
            ->join('siswas as s', 't.siswa_id', '=', 's.id')
            ->leftJoin('kelas as k', 's.kelas_id', '=', 'k.id')
            ->selectRaw('s.nama_lengkap, k.nama_kelas, SUM(t.poinr) as total_poin, COUNT(*) as jumlah')
            ->whereDate('t.tanggal', '>=', $this->dateFrom->toDateString())
            ->whereDate('t.tanggal', '<=', $this->dateTo->toDateString())
            ->where('t.poinr', '>', 0)
            ->whereNull('t.deleted_at');

        if ($this->kelasId) {
            $q->where('s.kelas_id', $this->kelasId);
        } else {
            if ($this->tingkat) $q->where('k.tingkat', $this->tingkat);
            if ($this->shift)   $q->where('k.shift', $this->shift);
        }

        return $q->groupBy('s.id', 's.nama_lengkap', 'k.nama_kelas')
            ->orderByDesc('total_poin')
            ->limit(10)
            ->get()->toArray();
    }

    /* ──────────────────────────────────────────────────────────────
     *  9. SURAT PANGGILAN
     * ────────────────────────────────────────────────────────────── */
    public function getSuratPanggilan(): array
    {
        return SuratPanggilan::select(
            'panggilan_ke',
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN wa_sent_at IS NOT NULL THEN 1 ELSE 0 END) as wa_terkirim')
        )
            ->whereBetween('tanggal_surat', [$this->dateFrom->toDateString(), $this->dateTo->toDateString()])
            ->groupBy('panggilan_ke')
            ->orderBy('panggilan_ke')
            ->get()->toArray();
    }

    /* ──────────────────────────────────────────────────────────────
     *  10. EVENT SISWA — PARTISIPASI
     * ────────────────────────────────────────────────────────────── */
    public function getPartisipasiEvent(): array
    {
        $events = Event::whereBetween('tanggal_mulai', [$this->dateFrom, $this->dateTo])
            ->orWhereBetween('tanggal_selesai', [$this->dateFrom, $this->dateTo])
            ->withCount([
                // Pola unified: masuk = waktu_masuk terisi, pulang = waktu_pulang terisi
                'absenEvent as scan_masuk'  => fn($q) => $q->whereNotNull('waktu_masuk'),
                'absenEvent as scan_pulang' => fn($q) => $q->whereNotNull('waktu_pulang'),
            ])
            ->get();

        return $events->map(fn($e) => [
            'nama'        => \Illuminate\Support\Str::limit($e->nama_event, 30),
            'scan_masuk'  => $e->scan_masuk ?? 0,
            'scan_pulang' => $e->scan_pulang ?? 0,
        ])->toArray();
    }

    public function getDistribusiKategoriEvent(): array
    {
        return DB::table('events as e')
            ->leftJoin('event_categories as ec', 'e.event_category_id', '=', 'ec.id')
            ->select(DB::raw('COALESCE(ec.nama_kategori, "Tanpa Kategori") as kategori'), DB::raw('COUNT(*) as total'))
            ->whereBetween('e.tanggal_mulai', [$this->dateFrom, $this->dateTo])
            ->whereNull('e.deleted_at')
            ->groupBy('kategori')
            ->get()->pluck('total', 'kategori')->toArray();
    }

    /* ──────────────────────────────────────────────────────────────
     *  11. EVENT GURU — PARTISIPASI
     * ────────────────────────────────────────────────────────────── */
    public function getPartisipasiEventGuru(): array
    {
        $events = EventGuru::whereBetween('tanggal_mulai', [$this->dateFrom, $this->dateTo])
            ->orWhereBetween('tanggal_selesai', [$this->dateFrom, $this->dateTo])
            ->withCount([
                'absenEventGuru as scan_masuk'  => fn($q) => $q->where('jenis', 'masuk'),
                'absenEventGuru as scan_pulang' => fn($q) => $q->where('jenis', 'pulang'),
            ])
            ->get();

        return $events->map(fn($e) => [
            'nama'        => \Illuminate\Support\Str::limit($e->nama_event, 30),
            'scan_masuk'  => $e->scan_masuk ?? 0,
            'scan_pulang' => $e->scan_pulang ?? 0,
        ])->toArray();
    }

    /* ──────────────────────────────────────────────────────────────
     *  12. LAPORAN KEHADIRAN GURU (KBM)
     * ────────────────────────────────────────────────────────────── */
    public function getTrenLaporanGuru(): array
    {
        return LaporanKehadiranGuru::select(
            DB::raw('DATE(tanggal) as tgl'),
            'status',
            DB::raw('COUNT(*) as total')
        )
            ->whereBetween('tanggal', [$this->dateFrom->toDateString(), $this->dateTo->toDateString()])
            ->groupBy('tgl', 'status')
            ->orderBy('tgl')
            ->get()
            ->groupBy('tgl')
            ->map(fn($rows) => $rows->pluck('total', 'status')->toArray())
            ->toArray();
    }

    public function getDistribusiStatusGuru(): array
    {
        return LaporanKehadiranGuru::select('status', DB::raw('COUNT(*) as total'))
            ->whereBetween('tanggal', [$this->dateFrom->toDateString(), $this->dateTo->toDateString()])
            ->groupBy('status')->get()->pluck('total', 'status')->toArray();
    }

    public function getTopGuruBermasalah(): array
    {
        return LaporanKehadiranGuru::select('gtk_id', DB::raw('COUNT(*) as total'))
            ->whereBetween('tanggal', [$this->dateFrom->toDateString(), $this->dateTo->toDateString()])
            ->whereIn('status', ['merah', 'kuning', 'orange'])
            ->groupBy('gtk_id')
            ->orderByDesc('total')
            ->with('gtk:id,nama_lengkap')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'nama'  => $r->gtk?->nama_lengkap ?? 'GTK #' . $r->gtk_id,
                'total' => $r->total,
            ])->toArray();
    }

    /**
     * Rekap kehadiran semua guru, dipecah per status.
     * Returns array of:
     *   ['gtk_id' => int, 'nama' => string, 'total' => int, 'statuses' => ['hijau'=>n, 'kuning'=>n, ...]]
     * Diurutkan: hijau DESC (paling rajin di atas), fallback nama ASC.
     */
    public function getRekapStatusGuruDetail(): array
    {
        $allStatuses = config('status_guru.order', ['hijau','kuning','merah','abu','biru','pink','orange','putih']);

        // Pivot: satu baris per guru × status
        $rows = LaporanKehadiranGuru::select(
                'gtk_id',
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('tanggal', [$this->dateFrom->toDateString(), $this->dateTo->toDateString()])
            ->whereNotNull('gtk_id')
            ->groupBy('gtk_id', 'status')
            ->with('gtk:id,nama_lengkap')
            ->get();

        // Group by guru
        $byGuru = $rows->groupBy('gtk_id');

        $result = [];
        foreach ($byGuru as $gtkId => $guruRows) {
            $nama = $guruRows->first()->gtk?->nama_lengkap ?? 'GTK #' . $gtkId;
            $statusCounts = [];
            foreach ($allStatuses as $st) {
                $statusCounts[$st] = 0;
            }
            foreach ($guruRows as $row) {
                if (isset($statusCounts[$row->status])) {
                    $statusCounts[$row->status] = (int) $row->total;
                }
            }
            $grandTotal = array_sum($statusCounts);
            $result[] = [
                'gtk_id'   => $gtkId,
                'nama'     => $nama,
                'total'    => $grandTotal,
                'statuses' => $statusCounts,
            ];
        }

        // Urutkan: hijau (rajin hadir) DESC, lalu nama ASC
        usort($result, function ($a, $b) {
            $diff = ($b['statuses']['hijau'] ?? 0) - ($a['statuses']['hijau'] ?? 0);
            return $diff !== 0 ? $diff : strcmp($a['nama'], $b['nama']);
        });

        return $result;
    }

    /* ──────────────────────────────────────────────────────────────
     *  13. WHATSAPP LOGS
     * ────────────────────────────────────────────────────────────── */
    public function getTrenWa(): array
    {
        return WaLog::select(
            DB::raw('DATE(created_at) as tgl'),
            'jenis',
            DB::raw('COUNT(*) as total')
        )
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->groupBy('tgl', 'jenis')
            ->orderBy('tgl')
            ->get()
            ->groupBy('tgl')
            ->map(fn($rows) => $rows->pluck('total', 'jenis')->toArray())
            ->toArray();
    }

    public function getStatusWa(): array
    {
        return WaLog::select('status', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->groupBy('status')->get()->pluck('total', 'status')->toArray();
    }

    public function getDistribusiJenisWa(): array
    {
        return WaLog::select('jenis', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->groupBy('jenis')->orderByDesc('total')
            ->get()->pluck('total', 'jenis')->toArray();
    }
}
