<?php

namespace App\Providers;

use App\Models\Sekolah;
use App\Models\SetJam;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register helper functions untuk data sekolah
        $this->registerSekolahHelpers();
    }

    /**
     * Register helper functions untuk data sekolah
     */
    private function registerSekolahHelpers(): void
    {
        // Helper untuk data sekolah
        app()->singleton('sekolah.data', function () {
            return Cache::remember('sekolah_data', 3600, function () {
                $sekolah = Sekolah::first();

                return $sekolah ? [
                    'nama' => $sekolah->sekolah,
                    'alamat' => $sekolah->alsekolah,
                    'telp' => $sekolah->telp,
                    'email' => $sekolah->email,
                    'kabupaten' => $sekolah->kab,
                    'nama_ks' => $sekolah->nama_ks,
                    'nip_ks' => $sekolah->nip_ks,
                    'nama_waka' => $sekolah->nama_waka,
                    'nip_waka' => $sekolah->nip_waka,
                    'wa_sekolah' => $sekolah->wasekolah,
                    'system_name' => $sekolah->system_name ?: 'SIS SMKN 5 Madiun',
                ] : config('sekolah.sekolah');
            });
        });

        // Helper untuk tahun ajaran aktif
        app()->singleton('sekolah.tahun_ajaran', function () {
            return Cache::remember('tahun_ajaran_aktif', 3600, function () {
                $th = DB::table('tblthajaran')->where('aktif', 'Y')->first();

                return $th ? $th->thajaran : config('sekolah.tahun_ajaran_aktif');
            });
        });

        // Helper untuk jam shift
        app()->singleton('sekolah.jam_shift', function () {
            return Cache::remember('jam_shift_config', 3600, function () {
                $jamPagi = SetJam::getJamByShift('Pagi');
                $jamSiang = SetJam::getJamByShift('Siang');

                return [
                    'pagi' => $jamPagi ? [
                        'masuk' => $jamPagi->time_in->format('H:i:s'),
                        'limit_masuk' => $jamPagi->limit_in->format('H:i:s'),
                        'pulang' => $jamPagi->time_out->format('H:i:s'),
                        'limit_pulang' => $jamPagi->limit_out->format('H:i:s'),
                    ] : config('sekolah.jam_shift.pagi'),
                    'siang' => $jamSiang ? [
                        'masuk' => $jamSiang->time_in->format('H:i:s'),
                        'limit_masuk' => $jamSiang->limit_in->format('H:i:s'),
                        'pulang' => $jamSiang->time_out->format('H:i:s'),
                        'limit_pulang' => $jamSiang->limit_out->format('H:i:s'),
                    ] : config('sekolah.jam_shift.siang'),
                ];
            });
        });

        // Helper untuk threshold alfa
        app()->singleton('sekolah.threshold_alfa', function () {
            return Cache::remember('threshold_alfa_config', 3600, function () {
                $alphas = DB::table('tblsetalpha')->orderBy('jumalpa1')->get();
                $result = [];

                foreach ($alphas as $alpha) {
                    if ($alpha->jumalpa1 > 0) {
                        $result[] = [
                            'jumlah_alfa' => $alpha->jumalpa1,
                            'tindakan' => $alpha->tindakan1,
                            'sanksi' => $alpha->sanksi1,
                        ];
                    }
                }

                return $result ?: config('sekolah.threshold_alfa');
            });
        });
    }
}
