<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Services\DashboardLaporanService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardLaporanController extends Controller
{
    /**
     * Halaman utama Dashboard Laporan Aktivitas.
     */
    public function index(Request $request)
    {
        // ── Resolve periode ──────────────────────────────────────────
        $periode   = $request->input('periode', 'bulanan');
        $dateFrom  = null;
        $dateTo    = null;

        if ($periode === 'custom') {
            $dateFrom = Carbon::parse($request->input('date_from', today()->subDays(30)));
            $dateTo   = Carbon::parse($request->input('date_to', today()));
        } else {
            [$dateFrom, $dateTo] = $this->resolvePeriode($periode);
        }

        // ── Filter tambahan ──────────────────────────────────────────
        $kelasId = $request->input('kelas_id');
        $tingkat = $request->input('tingkat');
        $shift   = $request->input('shift');

        // ── Jalankan service ─────────────────────────────────────────
        $svc = new DashboardLaporanService($dateFrom, $dateTo, $kelasId, $tingkat, $shift);

        $kpi              = $svc->getKpiRingkasan();
        $trenKehadiran    = $svc->getTrenKehadiran();
        $distribusiStatus = $svc->getDistribusiStatus();
        $heatmapKelas     = $svc->getHeatmapKelas();
        $distribusiJam    = $svc->getDistribusiJamMasuk();
        $rankingKelas     = $svc->getRankingKelas();

        $trenIzin         = $svc->getTrenIzin();
        $statusIzin       = $svc->getStatusIzin();
        $distribusiJenisIzin = $svc->getDistribusiJenisIzin();

        $trenPelanggaran  = $svc->getTrenPelanggaran();
        $trenPenghargaan  = $svc->getTrenPenghargaan();
        $topPelanggaran   = $svc->getTopPelanggaran();
        $topPenghargaan   = $svc->getTopPenghargaan();
        $topSiswa         = $svc->getTopSiswaPelanggaran();
        $topSiswaPenghargaan = $svc->getTopSiswaPenghargaan();
        $suratPanggilan   = $svc->getSuratPanggilan();

        $partisipasiEvent = $svc->getPartisipasiEvent();
        $kategoriEvent    = $svc->getDistribusiKategoriEvent();
        $partisipasiEventGuru = $svc->getPartisipasiEventGuru();

        $trenLaporanGuru      = $svc->getTrenLaporanGuru();
        $distribusiStatusGuru = $svc->getDistribusiStatusGuru();
        $topGuruBermasalah    = $svc->getTopGuruBermasalah();
        $rekapStatusGuru      = $svc->getRekapStatusGuruDetail();

        $trenWa            = $svc->getTrenWa();
        $statusWa          = $svc->getStatusWa();
        $distribusiJenisWa = $svc->getDistribusiJenisWa();

        // ── Data untuk filter dropdown ───────────────────────────────
        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get(['id', 'nama_kelas', 'tingkat', 'shift']);

        return view('admin.dashboard-laporan.index', compact(
            'periode',
            'dateFrom',
            'dateTo',
            'kelasId',
            'tingkat',
            'shift',
            'kpi',
            'trenKehadiran',
            'distribusiStatus',
            'heatmapKelas',
            'distribusiJam',
            'rankingKelas',
            'trenIzin',
            'statusIzin',
            'distribusiJenisIzin',
            'trenPelanggaran',
            'trenPenghargaan',
            'topPelanggaran',
            'topPenghargaan',
            'topSiswa',
            'topSiswaPenghargaan',
            'suratPanggilan',
            'partisipasiEvent',
            'kategoriEvent',
            'partisipasiEventGuru',
            'trenLaporanGuru',
            'distribusiStatusGuru',
            'topGuruBermasalah',
            'rekapStatusGuru',
            'trenWa',
            'statusWa',
            'distribusiJenisWa',
            'kelasList'
        ));
    }

    /* ──────────────────────────────────────────────────────────────
     *  API endpoint — data JSON untuk refresh via AJAX
     * ────────────────────────────────────────────────────────────── */
    public function apiData(Request $request)
    {
        $periode = $request->input('periode', 'bulanan');
        if ($periode === 'custom') {
            $dateFrom = Carbon::parse($request->input('date_from', today()->subDays(30)));
            $dateTo   = Carbon::parse($request->input('date_to', today()));
        } else {
            [$dateFrom, $dateTo] = $this->resolvePeriode($periode);
        }

        $svc = new DashboardLaporanService(
            $dateFrom,
            $dateTo,
            $request->input('kelas_id'),
            $request->input('tingkat'),
            $request->input('shift')
        );

        return response()->json([
            'kpi'               => $svc->getKpiRingkasan(),
            'trenKehadiran'     => $svc->getTrenKehadiran(),
            'distribusiStatus'  => $svc->getDistribusiStatus(),
            'distribusiJam'     => $svc->getDistribusiJamMasuk(),
            'rankingKelas'      => $svc->getRankingKelas(),
            'trenIzin'          => $svc->getTrenIzin(),
            'statusIzin'        => $svc->getStatusIzin(),
            'distribusiJenisIzin' => $svc->getDistribusiJenisIzin(),
            'trenPelanggaran'   => $svc->getTrenPelanggaran(),
            'trenPenghargaan'   => $svc->getTrenPenghargaan(),
            'topPelanggaran'    => $svc->getTopPelanggaran(),
            'topPenghargaan'    => $svc->getTopPenghargaan(),
            'topSiswa'          => $svc->getTopSiswaPelanggaran(),
            'topSiswaPenghargaan' => $svc->getTopSiswaPenghargaan(),
            'suratPanggilan'    => $svc->getSuratPanggilan(),
            'partisipasiEvent'  => $svc->getPartisipasiEvent(),
            'kategoriEvent'     => $svc->getDistribusiKategoriEvent(),
            'partisipasiEventGuru' => $svc->getPartisipasiEventGuru(),
            'distribusiStatusGuru' => $svc->getDistribusiStatusGuru(),
            'topGuruBermasalah' => $svc->getTopGuruBermasalah(),
            'rekapStatusGuru'   => $svc->getRekapStatusGuruDetail(),
            'statusWa'          => $svc->getStatusWa(),
            'distribusiJenisWa' => $svc->getDistribusiJenisWa(),
        ]);
    }

    /* ──────────────────────────────────────────────────────────────
     *  Helper: resolve rentang tanggal dari nama periode
     * ────────────────────────────────────────────────────────────── */
    private function resolvePeriode(string $periode): array
    {
        return match ($periode) {
            'harian'   => [today(),                                         today()],
            'mingguan' => [today()->startOfWeek(),                          today()->endOfWeek()->startOfDay()],
            'bulanan'  => [today()->startOfMonth(),                         today()->endOfMonth()->startOfDay()],
            'tahunan'  => [today()->startOfYear(),                          today()->endOfYear()->startOfDay()],
            default    => [today()->startOfMonth(),                         today()->endOfMonth()->startOfDay()],
        };
    }
}
