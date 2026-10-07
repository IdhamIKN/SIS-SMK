<?php

namespace App\Http\Controllers;

use App\Models\AbsenSiswa;
use App\Models\JadwalKBM;
use App\Models\Kelas;
use App\Models\LaporanKehadiranGuru;
use App\Models\PengajuanIzin;
use App\Models\SetJam;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RealtimePanelController extends Controller
{
    /**
     * Display the realtime panel for Kepala Sekolah & Waka
     */
    public function index(Request $request): View
    {
        try {
            $kelas = Kelas::with(['waliKelas'])
                ->orderBy('nama_kelas')
                ->get();
        } catch (\Exception $e) {
            $kelas = Kelas::orderBy('nama_kelas')->get();
        }

        return view('panel.realtime', compact('kelas'));
    }

    /**
     * API: Kembalikan daftar jam pelajaran untuk hari tertentu.
     * Dipakai JS untuk rebuild dropdown saat tanggal berubah.
     */
    public function getJamByHari(Request $request): JsonResponse
    {
        $tanggal = $request->get('tanggal', now()->toDateString());
        $hari    = $this->getHariIndonesia($tanggal);
        $jams    = SetJam::getJamByHari($hari);

        return response()->json([
            'hari' => $hari,
            'jams' => $jams->map(fn($j) => [
                'id_jam'       => $j->id_jam,
                'nama_jam'     => $j->nama_jam,
                'kelompok_jam' => $j->kelompok_jam,
                'time_in'      => $j->time_in?->format('H:i'),
                'time_out'     => $j->time_out?->format('H:i'),
                'label'        => $j->nama_jam . ' (' . ($j->time_in?->format('H:i') ?? '--') . '–' . ($j->time_out?->format('H:i') ?? '--') . ')',
            ])->values(),
        ]);
    }

    /**
     * Get current status for all classes.
     *
     * Menggunakan waktu SEKARANG (now()) sebagai acuan query jadwal KBM.
     * Dengan cara ini setiap kelas mendapat jadwal yang tepat sesuai
     * jam pelajaran masing-masing — kelas X, XI, XII bisa berbeda slot
     * karena jadwalnya dicari berdasarkan rentang jam_mulai/jam_selesai,
     * bukan berdasarkan satu id_jam tunggal.
     *
     * Parameter jam_ke masih diterima untuk keperluan "lihat jam lain"
     * (misal: kepala sekolah ingin lihat jam 1 padahal sekarang jam 3).
     * Jika jam_ke tidak dikirim → pakai now() langsung.
     */
    public function getStatus(Request $request): JsonResponse
    {
        $tanggal = $request->get('tanggal', now()->toDateString());
        $hari    = $this->getHariIndonesia($tanggal);

        // Tentukan waktu referensi:
        // - Jika jam_ke dikirim (mode manual viewer) → pakai titik tengah slot itu
        // - Jika tidak → pakai NOW() langsung (mode realtime penuh)
        if ($request->filled('jam_ke')) {
            $jamKeRef  = (int) $request->get('jam_ke');
            $jamRefRow = SetJam::find($jamKeRef);
            $jamLabel  = $this->getJamLabel($jamKeRef);

            $refTime = $jamRefRow
                ? Carbon::parse($jamRefRow->time_in->format('H:i:s'))
                ->addSeconds(
                    (int) (Carbon::parse($jamRefRow->time_in->format('H:i:s'))
                        ->diffInSeconds(Carbon::parse($jamRefRow->time_out->format('H:i:s'))) / 2)
                )->format('H:i:s')
                : now()->format('H:i:s');
        } else {
            // REALTIME: gunakan waktu sekarang persis
            // Setiap kelas akan dicocokkan dengan jadwal yang jam_mulai <= now < jam_selesai
            // sehingga kelas X yang masih jam 1 dan kelas XI yang sudah jam 2
            // keduanya mendapat data jadwal yang benar sekaligus
            $refTime  = now()->format('H:i:s');
            $jamKeRef = $this->getCurrentJamKe($hari); // hanya untuk label info
            $jamLabel = now()->format('H:i');
        }

        // Ambil SEMUA jadwal yang aktif pada waktu referensi untuk hari ini
        $jadwalByKelas = JadwalKBM::with(['gtk', 'mataPelajaran', 'kelas'])
            ->where('hari', $hari)
            ->where('jam_mulai', '<=', $refTime)
            ->where('jam_selesai', '>', $refTime)
            ->orderBy('jam_mulai')
            ->get()
            ->keyBy('kelas_id');

        // Ambil laporan kehadiran untuk jadwal-jadwal yang ditemukan
        $laporan = collect();
        if ($jadwalByKelas->isNotEmpty()) {
            $jadwalIds = $jadwalByKelas->pluck('id')->filter()->values()->all();
            $laporan = LaporanKehadiranGuru::with(['gtk', 'kelas', 'jadwalKbm.gtk', 'jadwalKbm.mataPelajaran'])
                ->where('tanggal', $tanggal)
                ->whereIn('jadwal_kbm_id', $jadwalIds)
                ->get()
                ->keyBy('kelas_id');
        }

        try {
            $kelas = Kelas::with(['waliKelas'])->get();
        } catch (\Exception $e) {
            $kelas = Kelas::all();
        }

        // Absen siswa hari ini — pola unified (1 record per siswa per hari)
        try {
            $absenSiswa = AbsenSiswa::selectRaw(
                'kelas_id,
                     SUM(CASE WHEN jam_masuk IS NOT NULL THEN 1 ELSE 0 END)  as sudah_masuk_count,
                     SUM(CASE WHEN jam_pulang IS NOT NULL THEN 1 ELSE 0 END) as sudah_pulang_count,
                     COUNT(*) as total'
            )
                ->where('tanggal', $tanggal)
                ->groupBy('kelas_id')
                ->get()
                ->keyBy('kelas_id');
        } catch (\Exception $e) {
            $absenSiswa = collect();
        }

        // Izin disetujui hari ini
        try {
            $izinStats = PengajuanIzin::selectRaw('siswa.kelas_id, pengajuan_izin.status, COUNT(*) as jumlah')
                ->join('siswa', 'pengajuan_izin.siswa_id', '=', 'siswa.id')
                ->where('pengajuan_izin.tanggal_mulai', '<=', $tanggal)
                ->where('pengajuan_izin.tanggal_selesai', '>=', $tanggal)
                ->where('pengajuan_izin.status', 'disetujui')
                ->groupBy('siswa.kelas_id', 'pengajuan_izin.status')
                ->get()
                ->groupBy('kelas_id');
        } catch (\Exception $e) {
            $izinStats = collect();
        }

        // Izin pending
        try {
            $izinPending = PengajuanIzin::selectRaw('siswa.kelas_id, COUNT(*) as jumlah')
                ->join('siswa', 'pengajuan_izin.siswa_id', '=', 'siswa.id')
                ->where('pengajuan_izin.status', 'pending')
                ->groupBy('siswa.kelas_id')
                ->get()
                ->pluck('jumlah', 'kelas_id');
        } catch (\Exception $e) {
            $izinPending = collect();
        }

        $status = [];
        foreach ($kelas as $k) {
            $laporanKelas = $laporan->get($k->id);
            $jadwalKelas  = $laporanKelas?->jadwalKbm ?: $jadwalByKelas->get($k->id);
            $guruJadwal   = $jadwalKelas?->gtk;
            $guruDisplay  = $laporanKelas?->gtk ?: $guruJadwal ?: $k->waliKelas;
            $mapelDisplay = $jadwalKelas?->mataPelajaran?->nama_mapel ?: $jadwalKelas?->mata_pelajaran;

            $kelasAbsenRow    = $absenSiswa->get($k->id);
            $sudahMasuk       = (int) ($kelasAbsenRow?->sudah_masuk_count ?? 0);
            $sudahPulang      = (int) ($kelasAbsenRow?->sudah_pulang_count ?? 0);
            $izinDisetujui    = $izinStats->get($k->id, collect())->sum('jumlah');
            $izinPendingCount = $izinPending->get($k->id, 0);

            try {
                $totalSiswa = $k->siswa()->count();
            } catch (\Exception $e) {
                $totalSiswa = 0;
            }

            $jamLabelKelas = $jadwalKelas
                ? ($jadwalKelas->jam_mulai?->format('H:i') . '–' . $jadwalKelas->jam_selesai?->format('H:i'))
                : $jamLabel;

            $status[] = [
                'kelas_id'           => $k->id,
                'kelas_nama'         => $k->nama_kelas,
                'gtk_nama'           => $guruDisplay?->nama_lengkap ?? $guruDisplay?->nama ?? '-',
                'gtk_id'             => $guruDisplay?->id,
                'guru_jadwal_nama'   => $guruJadwal?->nama_lengkap ?? $guruJadwal?->nama ?? null,
                'mata_pelajaran'     => $mapelDisplay ?: '-',
                'jadwal_jam_mulai'   => $jadwalKelas?->jam_mulai?->format('H:i'),
                'jadwal_jam_selesai' => $jadwalKelas?->jam_selesai?->format('H:i'),
                'jam_ke'             => $jamKeRef,
                'jam_label'          => $jamLabelKelas,
                'status'             => $laporanKelas ? $laporanKelas->status : 'putih',
                'status_label'       => $laporanKelas ? $laporanKelas->status_label : 'Belum Lapor',
                'waktu_laporan'      => $laporanKelas?->waktu_laporan?->format('H:i'),
                'jadwal_kbm_id'      => $jadwalKelas?->id,
                'total_siswa'        => $totalSiswa,
                'sudah_masuk'        => $sudahMasuk,
                'sudah_pulang'       => $sudahPulang,
                'belum_absen'        => max(0, $totalSiswa - $sudahMasuk - $izinDisetujui),
                'izin_disetujui'     => $izinDisetujui,
                'izin_pending'       => $izinPendingCount,
            ];
        }

        return response()->json([
            'tanggal'   => $tanggal,
            'hari'      => $hari,
            'jam_ke'    => $jamKeRef,
            'jam_label' => $jamLabel,
            'ref_time'  => $refTime,
            'status'    => $status,
        ]);
    }

    /**
     * Get monthly recap data
     */
    public function getRekapBulan(Request $request): JsonResponse
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $startDate = Carbon::createFromDate($tahun, $bulan, 1);
        $endDate   = $startDate->copy()->endOfMonth();

        $rekap = LaporanKehadiranGuru::selectRaw('kelas_id, status, COUNT(*) as jumlah')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy('kelas_id', 'status')
            ->with(['kelas'])
            ->get()
            ->groupBy('kelas_id');

        $result = [];
        foreach ($rekap as $kelasId => $statuses) {
            $kelas = $statuses->first()->kelas;
            $data  = [
                'kelas_id'      => $kelasId,
                'kelas_nama'    => $kelas->nama_kelas,
                'hijau'         => 0,
                'kuning'        => 0,
                'merah'         => 0,
                'abu'           => 0,
                'biru'          => 0,
                'pink'          => 0,
                'orange'        => 0,
                'putih'         => 0,
                'total_laporan' => 0,
            ];

            foreach ($statuses as $status) {
                $data[$status->status]   = $status->jumlah;
                $data['total_laporan'] += $status->jumlah;
            }

            $result[] = $data;
        }

        return response()->json([
            'bulan' => $bulan,
            'tahun' => $tahun,
            'rekap' => $result,
        ]);
    }

    private function getHariIndonesia(string $tanggal): string
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ][Carbon::parse($tanggal)->dayOfWeekIso] ?? 'Senin';
    }

    /**
     * Get current jam ke based on current time and schedule.
     */
    private function getCurrentJamKe(string $hari = 'Senin'): int
    {
        $now = now();

        try {
            $jams = SetJam::getJamByHari($hari);

            if ($jams->isEmpty()) {
                $jams = SetJam::where('statusjam', 1)->orderBy('time_in')->get();
            }

            $nowTime = Carbon::parse($now->format('H:i:s'));

            // 1. Cari jam yang sedang aktif
            foreach ($jams as $jam) {
                $timeIn  = Carbon::parse($jam->time_in->format('H:i:s'));
                $timeOut = Carbon::parse($jam->time_out->format('H:i:s'));

                if ($nowTime->between($timeIn, $timeOut)) {
                    return (int) $jam->id_jam;
                }
            }

            // 2. Belum mulai → ambil jam berikutnya
            foreach ($jams as $jam) {
                $timeIn = Carbon::parse($jam->time_in->format('H:i:s'));
                if ($nowTime->lt($timeIn)) {
                    return (int) $jam->id_jam;
                }
            }

            // 3. Semua sudah selesai → ambil jam terakhir
            return (int) ($jams->last()?->id_jam ?? 1);
        } catch (\Exception $e) {
            // Fallback berbasis waktu jam
            $t = $now->hour * 60 + $now->minute;

            if ($t < 7 * 60 + 30)  return 1;
            if ($t < 8 * 60)        return 2;
            if ($t < 8 * 60 + 45)   return 3;
            if ($t < 9 * 60 + 30)   return 4;
            if ($t < 9 * 60 + 45)   return 5;
            if ($t < 10 * 60 + 30)  return 6;
            if ($t < 11 * 60 + 15)  return 7;
            if ($t < 12 * 60)        return 8;
            if ($t < 12 * 60 + 30)  return 9;
            if ($t < 13 * 60 + 15)  return 10;
            if ($t < 14 * 60)        return 11;
            return 12;
        }
    }

    private function getJamLabel(int $jamKe): string
    {
        $jam = SetJam::find($jamKe);

        if (!$jam) {
            return 'Jam Ke-' . $jamKe;
        }

        return is_numeric($jam->nama_jam)
            ? 'Jam Ke-' . $jam->nama_jam
            : $jam->nama_jam;
    }
}
