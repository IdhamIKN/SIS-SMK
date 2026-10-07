<?php

namespace App\Http\Controllers;

use App\Models\AbsenSiswa;
use App\Models\Event;
use App\Models\GTK;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        Log::channel('sis')->info('[Dashboard] Access', [
            'user_id' => $user->id,
            'role' => $user->getRoleNames()->first(),
        ]);

        $data = [];

        // ── Event yang sedang aktif sekarang ──────────────────────────────
        $data['eventAktif'] = Event::where('tanggal_mulai', '<=', now())
            ->where('tanggal_selesai', '>=', now())
            ->latest('tanggal_mulai')
            ->first();

        // ── Stat untuk siswa ──────────────────────────────────────────────
        if ($user->hasRole('siswa') && $user->siswa) {
            $siswa   = $user->siswa;
            $bulanIni = now()->format('Y-m');

            $data['statHadir'] = AbsenSiswa::where('siswa_id', $siswa->id)
                ->whereIn('status_masuk', ['hadir', 'terlambat'])
                ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulanIni])
                ->count();

            $data['statAlfa'] = AbsenSiswa::where('siswa_id', $siswa->id)
                ->where(function ($q) {
                    $q->where('status_masuk', 'alfa')
                      ->orWhere(function ($q2) {
                          $q2->whereNull('status_masuk')->where('status', 'alfa');
                      });
                })
                ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulanIni])
                ->count();

            $data['statIzin'] = AbsenSiswa::where('siswa_id', $siswa->id)
                ->where(function ($q) {
                    $q->whereIn('status_masuk', ['izin', 'sakit'])
                      ->orWhere(function ($q2) {
                          $q2->whereNull('status_masuk')->whereIn('status', ['izin', 'sakit']);
                      });
                })
                ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulanIni])
                ->count();

            $data['statEvent'] = Event::where('tanggal_mulai', '<=', now())
                ->where('tanggal_selesai', '>=', now())
                ->count();
        }

        // ── Stat untuk GTK ────────────────────────────────────────────────
        if ($user->hasRole('gtk') && $user->gtk) {
            $gtk = $user->gtk;

            // Event guru aktif
            $data['eventAktifGuru'] = \App\Models\EventGuru::where('tanggal_mulai', '<=', now())
                ->where('tanggal_selesai', '>=', now())
                ->latest('tanggal_mulai')
                ->first();

            // Jenis absen berikutnya
            if ($data['eventAktifGuru']) {
                $eg = $data['eventAktifGuru'];
                $sudahMasuk = \App\Models\AbsenEventGuru::where('event_guru_id', $eg->id)
                    ->where('gtk_id', $gtk->id)->where('jenis', 'masuk')->exists();
                $sudahPulang = \App\Models\AbsenEventGuru::where('event_guru_id', $eg->id)
                    ->where('gtk_id', $gtk->id)->where('jenis', 'pulang')->exists();
                $data['guruAbsenNext'] = match (true) {
                    $sudahPulang                                   => null,
                    $sudahMasuk && $eg->ada_absen_pulang           => 'pulang',
                    ! $sudahMasuk && $eg->ada_absen_masuk          => 'masuk',
                    default                                        => null,
                };
            } else {
                $data['guruAbsenNext'] = null;
            }
        }

        // ── Stat untuk admin/non-siswa ────────────────────────────────────
        if (! $user->hasRole('siswa')) {
            $data['statHadir'] = AbsenSiswa::whereNotNull('jam_masuk')
                ->whereIn('status_masuk', ['hadir', 'terlambat'])
                ->whereDate('tanggal', today())
                ->count();

            $data['statAlfa'] = AbsenSiswa::where(function ($q) {
                    $q->where('status_masuk', 'alfa')
                      ->orWhere(function ($q2) {
                          $q2->whereNull('status_masuk')->where('status', 'alfa');
                      });
                })
                ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [now()->format('Y-m')])
                ->count();

            $data['statSiswa'] = Siswa::where('status_aktif', true)->count();
            $data['statGtk']   = GTK::where('status_aktif', true)->count();
        }

        return view('dashboard', $data);
    }
}
