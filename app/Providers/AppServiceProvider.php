<?php

namespace App\Providers;

use App\Models\AbsenSiswa;
use App\Models\Penghargaan;
use App\Models\Pelanggaran;
use App\Models\Sekolah;
use App\Models\SetJam;
use App\Observers\AbsenSiswaObserver;
use App\Observers\PelanggaranObserver;
use App\Observers\PenghargaanObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
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
        // Observer: sync tblpelanggaran ↔ tbltransaksi otomatis
        Pelanggaran::observe(PelanggaranObserver::class);

        // Observer: sync tblpenghargaan ↔ tbltransaksi otomatis
        Penghargaan::observe(PenghargaanObserver::class);

        // Observer: hapus poin auto-alfa saat status absen berubah dari alfa → non-alfa
        AbsenSiswa::observe(AbsenSiswaObserver::class);

        // Rate limiter untuk proteksi spam login
        RateLimiter::for('login', function (Request $request) {
            // Gabungkan username + IP sebagai key agar satu IP tidak bisa brute-force banyak akun
            $key = 'login|' . strtolower($request->input('username', '')) . '|' . $request->ip();

            // Maks 5 percobaan per menit per kombinasi username+IP
            return Limit::perMinute(5)->by($key)->response(function () use ($key) {
                $seconds = RateLimiter::availableIn($key);

                return back()
                    ->withInput(request()->only('username'))
                    ->withErrors(['username' => 'Terlalu banyak percobaan login. Silakan tunggu beberapa saat.'])
                    ->with('throttle_seconds', $seconds);
            });
        });

        // Register helper functions untuk data sekolah
        $this->applyDatabaseSekolahConfig();
        $this->registerSekolahHelpers();
    }

    private function applyDatabaseSekolahConfig(): void
    {
        try {
            if (! Schema::hasTable('tblsekolah')) {
                return;
            }

            $sekolah = Cache::remember('sekolah_runtime_config', 3600, function () {
                return Sekolah::first();
            });

            if (! $sekolah) {
                return;
            }

            $fallbackSekolah = config('sekolah.sekolah', []);
            $systemName = $sekolah->system_name ?: config('app.name', 'SIS SMKN 5 Madiun');

            config([
                'app.name' => $systemName,
                'sekolah.system_name' => $systemName,
                'sekolah.nama' => $sekolah->sekolah ?: ($fallbackSekolah['nama'] ?? 'SMKN 5 Madiun'),
                'sekolah.alamat' => $sekolah->alsekolah ?: ($fallbackSekolah['alamat'] ?? ''),
                'sekolah.telepon' => $sekolah->telp ?: ($fallbackSekolah['telp'] ?? ''),
                'sekolah.email' => $sekolah->email ?: ($fallbackSekolah['email'] ?? ''),
                'sekolah.latitude' => $sekolah->latitude ?: config('sekolah.latitude'),
                'sekolah.longitude' => $sekolah->longitude ?: config('sekolah.longitude'),
                'sekolah.radius_m' => $sekolah->radius_meter ?: config('sekolah.radius_m'),
            ]);
        } catch (\Throwable) {
            // Saat instalasi/migrasi awal, database bisa belum siap. Gunakan config default.
        }
    }

    /**
     * Register helper functions untuk data sekolah
     */
    private function registerSekolahHelpers(): void
    {
        // Helper untuk data sekolah
        app()->singleton('sekolah.data', function () {
            return Cache::remember('sekolah_data', 3600, function () {
                try {
                    $sekolah = Schema::hasTable('tblsekolah') ? Sekolah::first() : null;
                } catch (\Throwable) {
                    $sekolah = null;
                }

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
                    'latitude' => $sekolah->latitude ?: config('sekolah.latitude'),
                    'longitude' => $sekolah->longitude ?: config('sekolah.longitude'),
                    'radius_meter' => $sekolah->radius_meter ?: config('sekolah.radius_m'),
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
                $jamPagi  = SetJam::getJamByShift('Pagi');
                $jamSiang = SetJam::getJamByShift('Siang');

                return [
                    'pagi' => $jamPagi ? [
                        'masuk'        => $jamPagi->time_in?->format('H:i:s')   ?? '07:00:00',
                        'limit_masuk'  => $jamPagi->limit_in?->format('H:i:s')  ?? '07:15:00',
                        'pulang'       => $jamPagi->time_out?->format('H:i:s')  ?? '14:45:00',
                        'limit_pulang' => $jamPagi->limit_out?->format('H:i:s') ?? '15:00:00',
                    ] : config('sekolah.jam_shift.pagi'),
                    'siang' => $jamSiang ? [
                        'masuk'        => $jamSiang->time_in?->format('H:i:s')   ?? '10:30:00',
                        'limit_masuk'  => $jamSiang->limit_in?->format('H:i:s')  ?? '10:45:00',
                        'pulang'       => $jamSiang->time_out?->format('H:i:s')  ?? '16:00:00',
                        'limit_pulang' => $jamSiang->limit_out?->format('H:i:s') ?? '16:15:00',
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
