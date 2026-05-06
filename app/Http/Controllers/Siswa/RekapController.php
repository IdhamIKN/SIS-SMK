<?php

namespace App\Http\Controllers\Siswa;

use App\Exports\RekapAbsenExport;
use App\Http\Controllers\Controller;
use App\Models\AbsenEvent;
use App\Models\AbsenSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class RekapController extends Controller
{
    public function index(Request $request): View
    {
        Log::channel('sis')->info('[RekapAbsen] Halaman rekap', [
            'user_id' => $request->user()->id,
            'role' => $request->user()->getRoleNames()->first(),
            'filters' => $request->only(['tanggal_mulai', 'tanggal_selesai', 'kelas_id', 'status']),
        ]);

        $user = $request->user();
        $isSiswa = $user->hasRole('siswa');

        // Default filter 30 hari terakhir
        $tanggalMulai = $request->get('tanggal_mulai', now()->subDays(30)->format('Y-m-d'));
        $tanggalSelesai = $request->get('tanggal_selesai', now()->format('Y-m-d'));
        $kelasId = $request->get('kelas_id');
        $status = $request->get('status');

        // Query base
        $absenQuery = AbsenSiswa::with(['siswa.kelas', 'siswa.kelas.jurusan'])
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);

        $absenEventQuery = AbsenEvent::with(['siswa.kelas', 'siswa.kelas.jurusan', 'event'])
            ->whereBetween('waktu_scan', [$tanggalMulai.' 00:00:00', $tanggalSelesai.' 23:59:59']);

        // Filter berdasarkan role
        if ($isSiswa) {
            $siswaId = $user->siswa->id;
            $absenQuery->where('siswa_id', $siswaId);
            $absenEventQuery->where('siswa_id', $siswaId);
        } elseif ($user->hasRole('wali_kelas')) {
            // Wali kelas hanya lihat kelasnya
            $kelasWali = $user->gtk->kelasWali()->pluck('id');
            $absenQuery->whereIn('kelas_id', $kelasWali);
            $absenEventQuery->whereHas('siswa', fn ($q) => $q->whereIn('kelas_id', $kelasWali));
        }

        // Filter kelas (jika dipilih)
        if ($kelasId) {
            $absenQuery->where('kelas_id', $kelasId);
            $absenEventQuery->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelasId));
        }

        // Filter status
        if ($status) {
            $absenQuery->where('status', $status);
        }

        // Get data
        $absenSiswa = $absenQuery->orderBy('tanggal', 'desc')->orderBy('waktu_absen', 'desc')->get();
        $absenEvent = $absenEventQuery->orderBy('waktu_scan', 'desc')->get();

        // Statistik
        $stats = [
            'total_absen_siswa' => $absenSiswa->count(),
            'total_absen_event' => $absenEvent->count(),
            'hadir' => $absenSiswa->where('status', 'hadir')->count(),
            'izin' => $absenSiswa->where('status', 'izin')->count(),
            'sakit' => $absenSiswa->where('status', 'sakit')->count(),
            'alfa' => $absenSiswa->where('status', 'alfa')->count(),
        ];

        // Gabungkan data untuk tampilan unified
        $rekapUnified = $this->gabungkanDataRekap($absenSiswa, $absenEvent);

        // Data untuk filter
        $kelas = Kelas::with('jurusan')->orderBy('nama_kelas')->get();

        return view('siswa.rekap.index', compact(
            'rekapUnified',
            'stats',
            'kelas',
            'tanggalMulai',
            'tanggalSelesai',
            'kelasId',
            'status',
            'isSiswa'
        ));
    }

    private function gabungkanDataRekap($absenSiswa, $absenEvent)
    {
        $rekap = collect();

        // Tambahkan absen siswa biasa
        foreach ($absenSiswa as $absen) {
            $rekap->push([
                'tipe' => 'absen_siswa',
                'tanggal' => $absen->tanggal,
                'waktu' => $absen->waktu_absen,
                'siswa' => $absen->siswa,
                'kelas' => $absen->siswa->kelas,
                'jenis' => $absen->jenis,
                'status' => $absen->status,
                'keterangan' => $absen->event_name ?? 'Absen Sekolah',
                'catatan' => $absen->catatan,
                'lokasi' => null,
            ]);
        }

        // Tambahkan absen event
        foreach ($absenEvent as $absen) {
            $rekap->push([
                'tipe' => 'absen_event',
                'tanggal' => $absen->waktu_scan->format('Y-m-d'),
                'waktu' => $absen->waktu_scan,
                'siswa' => $absen->siswa,
                'kelas' => $absen->siswa->kelas,
                'jenis' => $absen->jenis,
                'status' => 'hadir', // Absen event selalu hadir
                'keterangan' => 'Event: '.$absen->event->nama_event,
                'catatan' => null,
                'lokasi' => $absen->event->lokasi,
            ]);
        }

        // Sort by waktu descending
        return $rekap->sortByDesc('waktu')->values();
    }

    public function export(Request $request)
    {
        Log::channel('sis')->info('[RekapAbsen] Export Excel', [
            'user_id' => $request->user()->id,
            'filters' => $request->only(['tanggal_mulai', 'tanggal_selesai', 'kelas_id', 'status']),
        ]);

        // Reuse logic dari index
        $tanggalMulai = $request->get('tanggal_mulai', now()->subDays(30)->format('Y-m-d'));
        $tanggalSelesai = $request->get('tanggal_selesai', now()->format('Y-m-d'));
        $kelasId = $request->get('kelas_id');
        $status = $request->get('status');

        return Excel::download(
            new RekapAbsenExport($tanggalMulai, $tanggalSelesai, $kelasId, $status),
            'rekap_absen_'.$tanggalMulai.'_sd_'.$tanggalSelesai.'.xlsx'
        );
    }
}
