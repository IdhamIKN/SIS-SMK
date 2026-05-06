<?php

use App\Http\Controllers\AbsenEventController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\IzinController as AdminIzinController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GTK\GTKController;
use App\Http\Controllers\GTK\LaporanKehadiranController;
use App\Http\Controllers\Siswa\AbsenController;
use App\Http\Controllers\Siswa\IzinController;
use App\Http\Controllers\Siswa\RekapController;
use App\Http\Controllers\Siswa\SiswaController;
use App\Http\Controllers\Admin\SchoolConfigController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // User Profile
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [UserProfileController::class, 'index'])->name('index');
            Route::put('/', [UserProfileController::class, 'update'])->name('update');
            Route::post('/change-password', [UserProfileController::class, 'changePassword'])->name('change-password');
        });

        // Test route for debugging
        Route::get('/test-profile', function() {
            return 'Profile route works for role: ' . auth()->user()->getRoleNames()->first();
        })->middleware('auth');

        // Emergency route to reset admin email
        Route::get('/reset-admin-email', function() {
            $admin = \App\Models\User::where('email', 'superadmin@smkn5.id')->first();
            if (!$admin) {
                $admin = \App\Models\User::where('role_utama', 'superadmin')->first();
                if ($admin) {
                    $admin->update(['email' => 'superadmin@smkn5.id']);
                    return 'Admin email reset to: superadmin@smkn5.id';
                }
                return 'Admin user not found';
            }
            return 'Admin email is already correct: ' . $admin->email;
        });

        // Debug route to check admin user status
        Route::get('/check-admin', function() {
            $admin = \App\Models\User::where('role_utama', 'superadmin')->first();
            if ($admin) {
                return [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'phone' => $admin->phone,
                    'avatar' => $admin->avatar,
                    'role_utama' => $admin->role_utama,
                    'roles' => $admin->getRoleNames(),
                    'created_at' => $admin->created_at,
                    'updated_at' => $admin->updated_at,
                ];
            }
            return 'Admin user not found';
        });

        // Route to view recent logs
        Route::get('/debug-logs', function() {
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                $logs = file($logPath);
                $recentLogs = array_slice($logs, -20); // Get last 20 lines
                return '<pre>' . implode('', $recentLogs) . '</pre>';
            }
            return 'Log file not found';
        });

        // Emergency route to reset admin password
        Route::get('/reset-admin-password', function() {
            $admin = \App\Models\User::where('role_utama', 'superadmin')->first();
            if ($admin) {
                $admin->update(['password' => \Illuminate\Support\Facades\Hash::make('admin123')]);
                return 'Admin password reset to: admin123 for user: ' . $admin->email;
            }
            return 'Admin user not found';
        });

        // Route to list all admin users
        Route::get('/list-admins', function() {
            $admins = \App\Models\User::whereIn('role_utama', ['superadmin', 'admin_tatib'])->get();
            $result = [];
            foreach ($admins as $admin) {
                $result[] = [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'role_utama' => $admin->role_utama,
                    'roles' => $admin->getRoleNames(),
                ];
            }
            return $result;
        });

        // Test route for school config
        Route::get('/test-school-config', function() {
            try {
                $sekolah = \App\Models\Sekolah::aktif();
                return [
                    'sekolah' => $sekolah->toArray(),
                    'timestamps_enabled' => $sekolah->timestamps,
                    'fillable' => $sekolah->getFillable(),
                    'app_name' => config('app.name'),
                    'system_name' => $sekolah->system_name,
                ];
            } catch (\Exception $e) {
                return [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ];
            }
        });

    // Academic Year Management
    Route::resource('academic-years', AcademicYearController::class)->middleware('role:superadmin|admin_tatib');
    Route::patch('academic-years/{academicYear}/set-active', [AcademicYearController::class, 'setActive'])->name('academic-years.set-active')->middleware('role:superadmin|admin_tatib');
    Route::post('academic-years/{academicYear}/initialize-waves', [AcademicYearController::class, 'initializePromotionWaves'])->name('academic-years.initialize-waves')->middleware('role:superadmin|admin_tatib');

    // Kelas Management
    Route::resource('kelas', KelasController::class)->middleware('role:superadmin|admin_tatib');
    Route::get('kelas/{kela}/promote', [KelasController::class, 'promote'])->name('kelas.promote')->middleware('role:superadmin|admin_tatib');
    Route::post('kelas/{kela}/promote', [KelasController::class, 'executePromotion'])->name('kelas.execute-promotion')->middleware('role:superadmin|admin_tatib');

    // GTK Management
    Route::get('gtk/import', [GTKController::class, 'import'])->name('gtk.import')->middleware('role:superadmin|admin_tatib');
    Route::post('gtk/import', [GTKController::class, 'importProcess'])->name('gtk.import.process')->middleware('role:superadmin|admin_tatib');
    Route::get('gtk/template', [GTKController::class, 'downloadTemplate'])->name('gtk.template')->middleware('role:superadmin|admin_tatib');
    Route::resource('gtk', GTKController::class)->middleware('role:superadmin|admin_tatib');

    // Siswa Management
    Route::get('siswa/import', [SiswaController::class, 'showImportForm'])->name('siswa.import.form')->middleware('role:superadmin|admin_tatib');
    Route::post('siswa/import', [SiswaController::class, 'previewImport'])->name('siswa.import.preview')->middleware('role:superadmin|admin_tatib');
    Route::post('siswa/import/process', [SiswaController::class, 'importProcess'])->name('siswa.import.process')->middleware('role:superadmin|admin_tatib');
    Route::get('siswa/export/excel', [SiswaController::class, 'export'])->name('siswa.export')->middleware('role:superadmin|admin_tatib');
    Route::get('siswa/template', [SiswaController::class, 'downloadTemplate'])->name('siswa.template')->middleware('role:superadmin|admin_tatib');
    Route::resource('siswa', SiswaController::class)->middleware('role:superadmin|admin_tatib|bk|wali_kelas');
    Route::get('siswa/{siswa}/export-cv', [SiswaController::class, 'exportCV'])->name('siswa.export-cv')->middleware('role:superadmin|admin_tatib|bk|wali_kelas');

    // GTK Laporan Kehadiran
    Route::prefix('kehadiran-guru')->name('kehadiran-guru.')->group(function () {
        Route::get('/laporan', [LaporanKehadiranController::class, 'index'])->name('laporan')->middleware('role:gtk|superadmin|admin_tatib|bk');
        Route::get('/create', [LaporanKehadiranController::class, 'create'])->name('create')->middleware('role:gtk');
        Route::post('/laporan', [LaporanKehadiranController::class, 'store'])->name('store')->middleware('role:gtk');
        Route::get('/{laporanKehadiran}', [LaporanKehadiranController::class, 'show'])->name('show')->middleware('role:gtk|superadmin|admin_tatib|bk');
        Route::get('/{laporanKehadiran}/edit', [LaporanKehadiranController::class, 'edit'])->name('edit')->middleware('role:gtk');
        Route::put('/{laporanKehadiran}', [LaporanKehadiranController::class, 'update'])->name('update')->middleware('role:gtk');
        Route::delete('/{laporanKehadiran}', [LaporanKehadiranController::class, 'destroy'])->name('destroy')->middleware('role:gtk');
        Route::get('/rekap', [LaporanKehadiranController::class, 'rekap'])->name('rekap');
        Route::post('/siswa', [LaporanKehadiranController::class, 'laporOlehSiswa'])->name('lapor-siswa')->middleware('role:siswa');
    });

    // Absen Siswa (Unified)
    Route::prefix('absen')->name('absen.')->group(function () {
        Route::get('/', [AbsenController::class, 'index'])->name('index')->middleware('role:siswa');
        Route::get('/masuk', fn () => redirect()->route('absen.index'))->name('masuk');
        Route::get('/pulang', fn () => redirect()->route('absen.index'))->name('pulang');
        Route::post('/{jenis}', [AbsenController::class, 'store'])->name('store')->where(['jenis' => 'masuk|pulang'])->middleware('role:siswa');
        Route::post('/distance-check', [AbsenController::class, 'distanceCheck'])->name('distance-check')->middleware('role:siswa');
        Route::get('/status-hari-ini', [AbsenController::class, 'statusHariIni'])->name('status-hari-ini')->middleware('role:siswa');
        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap')->middleware('role:siswa|gtk|superadmin|admin_tatib|bk|wali_kelas');
        Route::get('/rekap/export', [RekapController::class, 'export'])->name('rekap.export')->middleware('role:gtk|superadmin|admin_tatib|bk|wali_kelas');
        Route::post('/manual/{jenis}', [AbsenController::class, 'manual'])->name('manual')->where(['jenis' => 'masuk|pulang'])->middleware('role:bk|superadmin');
    });

    // Pengajuan Izin Siswa
    Route::prefix('izin')->as('siswa.izin.')->middleware('role:siswa')->group(function () {
        Route::get('/', [IzinController::class, 'index'])->name('index');
        Route::get('/create', [IzinController::class, 'create'])->name('create');
        Route::post('/', [IzinController::class, 'store'])->name('store');
        Route::get('/{izin}', [IzinController::class, 'show'])->name('show');
        Route::get('/{izin}/edit', [IzinController::class, 'edit'])->name('edit');
        Route::put('/{izin}', [IzinController::class, 'update'])->name('update');
        Route::delete('/{izin}', [IzinController::class, 'destroy'])->name('destroy');
    });

    // Admin Verifikasi Izin
    Route::prefix('admin/izin')->as('admin.izin.')->middleware('role:superadmin|admin_tatib|bk')->group(function () {
        Route::get('/', [AdminIzinController::class, 'index'])->name('index');
        Route::patch('/{izin}', [AdminIzinController::class, 'updateStatus'])->name('update');
    });

    // School Configuration
    Route::prefix('admin/school-config')->as('admin.school-config.')->middleware('role:superadmin|admin_tatib')->group(function () {
        Route::get('/', [SchoolConfigController::class, 'index'])->name('index');
        Route::put('/', [SchoolConfigController::class, 'update'])->name('update');
    });

    // Halaman khusus siswa lapor guru tidak hadir
    Route::get('/lapor-guru-tidak-hadir', function () {
        return view('gtk.laporan_kehadiran.lapor_siswa');
    })->name('siswa.lapor-guru')->middleware('role:siswa');

    // Event Absen
    Route::prefix('event')->name('event.')->middleware('role:siswa|gtk|superadmin|admin_tatib|bk|wali_kelas')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::get('/create', [EventController::class, 'create'])->name('create')->middleware('role:superadmin|admin_tatib|bk');
        Route::post('/', [EventController::class, 'store'])->name('store')->middleware('role:superadmin|admin_tatib|bk');
        Route::get('/{event}', [EventController::class, 'show'])->name('show');
        Route::get('/{event}/edit', [EventController::class, 'edit'])->name('edit')->middleware('role:superadmin|admin_tatib|bk');
        Route::put('/{event}', [EventController::class, 'update'])->name('update')->middleware('role:superadmin|admin_tatib|bk');
        Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy')->middleware('role:superadmin|admin_tatib|bk');
        Route::post('/{event}/rotate-barcode', [EventController::class, 'rotateBarcode'])->name('rotateBarcode')->middleware('role:superadmin|admin_tatib|bk');
        Route::get('/{event}/barcode', [EventController::class, 'getBarcode'])->name('barcode');
        Route::post('/{event}/barcode', [EventController::class, 'updateBarcode'])->name('updateBarcode');
        Route::get('/{event}/barcode-stream', [EventController::class, 'streamBarcode'])->name('barcodeStream');
        Route::get('/search/kelas', [EventController::class, 'searchKelas'])->name('search.kelas');
        Route::get('/search/siswa', [EventController::class, 'searchSiswa'])->name('search.siswa');

        Route::get('/{event}/scan', [AbsenEventController::class, 'scan'])->name('scan')->middleware('role:siswa');
        Route::post('/{event}/scan', [AbsenEventController::class, 'processScan'])->name('processScan')->middleware('role:siswa');
        Route::get('/{event}/rekap', [AbsenEventController::class, 'rekap'])->name('rekap');
        Route::get('/{event}/export', [AbsenEventController::class, 'export'])->name('export')->middleware('role:superadmin|admin_tatib|bk');
    });
});
