<?php

use App\Http\Controllers\AbsenEventController;
use App\Http\Controllers\AbsenEventGuruController;
use App\Http\Controllers\Admin\AbsenManualController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AutoPelanggaranRuleController;
use App\Http\Controllers\Admin\AutoPenghargaanRuleController;
use App\Http\Controllers\Admin\IzinController as AdminIzinController;
use App\Http\Controllers\Admin\JadwalKBMController;
use App\Http\Controllers\Admin\JurnalTatibController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\PasalController;
use App\Http\Controllers\Admin\PelanggaranController;
use App\Http\Controllers\Admin\PenghargaanController;
use App\Http\Controllers\Admin\RekapPoinController;
use App\Http\Controllers\Admin\SetJamController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventGuruController;
use App\Http\Controllers\GTK\GTKController;
use App\Http\Controllers\Guru\JurnalMengajarController;
use App\Http\Controllers\RealtimePanelController;
use App\Http\Controllers\GTK\LaporanKehadiranController;
use App\Http\Controllers\Siswa\AbsenController;
use App\Http\Controllers\Siswa\IzinController;
use App\Http\Controllers\Siswa\RekapController;
use App\Http\Controllers\Siswa\SiswaController;
use App\Http\Controllers\Admin\SchoolConfigController;
use App\Http\Controllers\Admin\DashboardLaporanController;
use App\Http\Controllers\Admin\SiswaPetugasLaporanController;
use App\Http\Controllers\Admin\SuratPanggilanController;
use App\Http\Controllers\Admin\LokasiPklController;
use App\Http\Controllers\Admin\PenugasanPklController;
use App\Http\Controllers\Admin\RekaporanPklController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Siswa\AbsenPklController;
use App\Http\Controllers\Siswa\JurnalPklController;
use App\Http\Controllers\UserProfileController;
use App\Models\Sekolah;
use App\Models\User;
use App\Http\Controllers\PwaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

// ─── PWA: halaman install & offline (publik, tanpa auth) ───────────────────
Route::get('/pwa-install', [PwaController::class, 'installPage'])->name('pwa.install');
Route::get('/offline',     [PwaController::class, 'offlinePage'])->name('pwa.offline');

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
});

// Route::get('/assign-event-role', function () {
//     $user = \App\Models\User::find(4606);
//     $user->assignRole('Event');
//     return 'Role Event berhasil di-assign ke: ' . $user->name;
// })->middleware('auth');
// Surat Panggilan Orang Tua — route publik (di LUAR group middleware auth)
Route::get(
    'admin/tatib/surat-panggilan/{suratPanggilan}/cetak',
    [SuratPanggilanController::class, 'cetakPublik']
)
    ->name('admin.surat-panggilan.cetak-publik')
    ->middleware('signed');


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
    Route::get('/test-profile', function () {
        return 'Profile route works for role: ' . auth()->user()->getRoleNames()->first();
    })->middleware('auth');

    // Emergency route to reset admin email
    // Route::get('/reset-admin-email', function () {
    //     $admin = User::where('email', 'superadmin@smkn5.id')->first();
    //     if (!$admin) {
    //         $admin = User::where('role_utama', 'superadmin')->first();
    //         if ($admin) {
    //             $admin->update(['email' => 'superadmin@smkn5.id']);
    //             return 'Admin email reset to: superadmin@smkn5.id';
    //         }
    //         return 'Admin user not found';
    //     }
    //     return 'Admin email is already correct: ' . $admin->email;
    // });
    // Route::get('/rubah', function () {
    //     $admin = User::where('email', 'admin@smkn5.id')->first();

    //     if (!$admin) {
    //         $admin = User::where('role_utama', 'superadmin')->first();
    //     }

    //     if (!$admin) {
    //         return 'Admin user not found';
    //     }

    //     $admin->update([
    //         'password' => Hash::make('password'),
    //     ]);

    //     return 'Admin password has been reset successfully. Default password: password';
    // });

    // Debug route to check admin user status
    // Route::get('/check-admin', function () {
    //     $admin = User::where('role_utama', 'superadmin')->first();
    //     if ($admin) {
    //         return [
    //             'id' => $admin->id,
    //             'name' => $admin->name,
    //             'email' => $admin->email,
    //             'phone' => $admin->phone,
    //             'avatar' => $admin->avatar,
    //             'role_utama' => $admin->role_utama,
    //             'roles' => $admin->getRoleNames(),
    //             'created_at' => $admin->created_at,
    //             'updated_at' => $admin->updated_at,
    //         ];
    //     }
    //     return 'Admin user not found';
    // });

    // Route to view recent logs
    Route::get('/debug-logs', function () {
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            $logs = file($logPath);
            $recentLogs = array_slice($logs, -20); // Get last 20 lines
            return '<pre>' . implode('', $recentLogs) . '</pre>';
        }
        return 'Log file not found';
    });

    // // Emergency route to reset admin password
    // Route::get('/reset-admin-password', function () {
    //     $admin = User::where('role_utama', 'superadmin')->first();
    //     if ($admin) {
    //         $admin->update(['password' => \Illuminate\Support\Facades\Hash::make('admin123')]);
    //         return 'Admin password reset to: admin123 for user: ' . $admin->email;
    //     }
    //     return 'Admin user not found';
    // });

    // // Route to list all admin users
    // Route::get('/list-admins', function () {
    //     $admins = User::whereIn('role_utama', ['superadmin', 'admin_tatib'])->get();
    //     $result = [];
    //     foreach ($admins as $admin) {
    //         $result[] = [
    //             'id' => $admin->id,
    //             'name' => $admin->name,
    //             'email' => $admin->email,
    //             'role_utama' => $admin->role_utama,
    //             'roles' => $admin->getRoleNames(),
    //         ];
    //     }
    //     return $result;
    // });

    // Test route for school config
    Route::get('/test-school-config', function () {
        try {
            $sekolah = Sekolah::aktif();
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

    Route::post('gtk/{gtk}/reset-password', [GTKController::class, 'resetPassword'])
        ->name('gtk.reset-password');
    Route::post('siswa/{siswa}/reset-password', [SiswaController::class, 'resetPassword'])
        ->name('siswa.reset-password');

    // Academic Year Management
    Route::resource('academic-years', AcademicYearController::class)->middleware('permission:academic-year.view|academic-year.create|academic-year.update|academic-year.delete');
    Route::patch('academic-years/{academicYear}/set-active', [AcademicYearController::class, 'setActive'])->name('academic-years.set-active')->middleware('permission:academic-year.set-active');
    Route::post('academic-years/{academicYear}/initialize-waves', [AcademicYearController::class, 'initializePromotionWaves'])->name('academic-years.initialize-waves')->middleware('permission:academic-year.init-waves');

    // Kelas Management
    Route::resource('kelas', KelasController::class)->middleware('permission:kelas.view|kelas.create|kelas.update|kelas.delete');
    Route::get('kelas/{kela}/promote', [KelasController::class, 'promote'])->name('kelas.promote')->middleware('permission:kelas.promote');
    Route::post('kelas/{kela}/promote', [KelasController::class, 'executePromotion'])->name('kelas.execute-promotion')->middleware('permission:kelas.promote');
    Route::get('kelas-arsip', [KelasController::class, 'archived'])->name('kelas.archived')->middleware('permission:kelas.view');
    Route::post('kelas-arsip/{id}/restore', [KelasController::class, 'restore'])->name('kelas.restore')->middleware('permission:kelas.delete');

    // GTK Management
    Route::get('gtk/import', [GTKController::class, 'import'])->name('gtk.import')->middleware('permission:gtk.import');
    Route::post('gtk/import', [GTKController::class, 'importProcess'])->name('gtk.import.process')->middleware('permission:gtk.import');
    Route::get('gtk/template', [GTKController::class, 'downloadTemplate'])->name('gtk.template')->middleware('permission:gtk.import');
    Route::get('gtk/mata-pelajaran/search', [GTKController::class, 'searchMataPelajaran'])->name('gtk.mata-pelajaran.search')->middleware('permission:gtk.view');
    Route::resource('gtk', GTKController::class)->middleware('permission:gtk.view|gtk.create|gtk.update|gtk.delete');

    // Mata Pelajaran Management
    Route::resource('mata-pelajaran', MataPelajaranController::class)->middleware('permission:mapel.view|mapel.create|mapel.update|mapel.delete')->names([
        'index' => 'admin.mata-pelajaran.index',
        'create' => 'admin.mata-pelajaran.create',
        'store' => 'admin.mata-pelajaran.store',
        'show' => 'admin.mata-pelajaran.show',
        'edit' => 'admin.mata-pelajaran.edit',
        'update' => 'admin.mata-pelajaran.update',
        'destroy' => 'admin.mata-pelajaran.destroy',
    ]);

    // Set Jam Pelajaran Management
    Route::resource('set-jam', SetJamController::class)->middleware('permission:setjam.view|setjam.create|setjam.update|setjam.delete')->names([
        'index' => 'admin.set-jam.index',
        'create' => 'admin.set-jam.create',
        'store' => 'admin.set-jam.store',
        'show' => 'admin.set-jam.show',
        'edit' => 'admin.set-jam.edit',
        'update' => 'admin.set-jam.update',
        'destroy' => 'admin.set-jam.destroy',
    ]);

    // Jadwal KBM Management — route manual harus di ATAS Route::resource
    // agar tidak tertimpa oleh wildcard {jadwal_kbm}
    Route::middleware('permission:jadwal-kbm.import')->group(function () {
        Route::get('jadwal-kbm/import',    [JadwalKBMController::class, 'import'])->name('admin.jadwal-kbm.import');
        Route::post('jadwal-kbm/import',   [JadwalKBMController::class, 'importProcess'])->name('admin.jadwal-kbm.import.process');
        Route::get('jadwal-kbm/template',  [JadwalKBMController::class, 'downloadTemplate'])->name('admin.jadwal-kbm.template');
    });
    Route::get('jadwal-kbm/guru/search', [JadwalKBMController::class, 'searchGuru'])->name('admin.jadwal-kbm.guru.search')->middleware('permission:jadwal-kbm.view');

    Route::resource('jadwal-kbm', JadwalKBMController::class)->middleware('permission:jadwal-kbm.view|jadwal-kbm.create|jadwal-kbm.update|jadwal-kbm.delete')->names([
        'index' => 'admin.jadwal-kbm.index',
        'create' => 'admin.jadwal-kbm.create',
        'store' => 'admin.jadwal-kbm.store',
        'show' => 'admin.jadwal-kbm.show',
        'edit' => 'admin.jadwal-kbm.edit',
        'update' => 'admin.jadwal-kbm.update',
        'destroy' => 'admin.jadwal-kbm.destroy',
    ]);

    Route::get('jadwal-kbm-guru', [JadwalKBMController::class, 'jadwalGuru'])->name('admin.jadwal-kbm.guru')->middleware('permission:jadwal-kbm.view-guru');
    Route::get('api/mata-pelajaran-by-guru', [JadwalKBMController::class, 'getMataPelajaranByGuru'])->name('admin.jadwal-kbm.api.mata-pelajaran-by-guru')->middleware('permission:jadwal-kbm.view');


    // Siswa Management
    Route::get('siswa/import', [SiswaController::class, 'showImportForm'])->name('siswa.import.form')->middleware('permission:siswa.import');
    Route::post('siswa/import', [SiswaController::class, 'previewImport'])->name('siswa.import.preview')->middleware('permission:siswa.import');
    Route::post('siswa/import/process', [SiswaController::class, 'importProcess'])->name('siswa.import.process')->middleware('permission:siswa.import');

    // Update HP Siswa dari Excel (nama-based matching)
    Route::get('siswa/update-hp', [SiswaController::class, 'showUpdateHpForm'])->name('siswa.update-hp.form')->middleware('permission:siswa.update');
    Route::post('siswa/update-hp/preview', [SiswaController::class, 'previewUpdateHp'])->name('siswa.update-hp.preview')->middleware('permission:siswa.update');
    Route::post('siswa/update-hp/process', [SiswaController::class, 'processUpdateHp'])->name('siswa.update-hp.process')->middleware('permission:siswa.update');
    Route::get('siswa/export/excel', [SiswaController::class, 'export'])->name('siswa.export')->middleware('permission:siswa.export');
    Route::get('siswa/template', [SiswaController::class, 'downloadTemplate'])->name('siswa.template')->middleware('permission:siswa.import');
    Route::post('siswa/bulk-archive', [SiswaController::class, 'bulkArchive'])->name('siswa.bulk-archive')->middleware('permission:siswa.delete');
    Route::post('siswa/bulk-restore', [SiswaController::class, 'bulkRestore'])->name('siswa.bulk-restore')->middleware('permission:siswa.delete');
    Route::patch('siswa/{siswa}/restore', [SiswaController::class, 'restore'])->name('siswa.restore')->middleware('permission:siswa.delete');
    Route::resource('siswa', SiswaController::class)->middleware('permission:siswa.view|siswa.create|siswa.update|siswa.delete');
    Route::get('siswa/{siswa}/export-cv', [SiswaController::class, 'exportCV'])->name('siswa.export-cv')->middleware('permission:siswa.export');
    // AJAX endpoints untuk halaman siswa show
    Route::prefix('siswa/{siswa}')->name('siswa.')->middleware('permission:siswa.view')->group(function () {
        Route::get('absen-harian',          [SiswaController::class, 'absenHarian'])->name('absen-harian');
        Route::patch('absen-harian/{absen}', [SiswaController::class, 'updateAbsenHarian'])->name('absen-harian.update')->middleware('permission:absen.manual.update');
        Route::get('absen-event',            [SiswaController::class, 'absenEvent'])->name('absen-event');
        Route::patch('absen-event/{absenEvent}', [SiswaController::class, 'updateAbsenEvent'])->name('absen-event.update')->middleware('permission:event.update');
        Route::delete('pelanggaran/{pelanggaran}', [SiswaController::class, 'deletePelanggaran'])->name('pelanggaran.destroy')->middleware('permission:pelanggaran.delete');
        Route::post('pelanggaran/bulk-delete',     [SiswaController::class, 'bulkDeletePelanggaran'])->name('pelanggaran.bulk-delete')->middleware('permission:pelanggaran.delete');
        Route::delete('penghargaan/{penghargaan}', [SiswaController::class, 'deletePenghargaan'])->name('penghargaan.destroy')->middleware('permission:penghargaan.delete');
        Route::post('penghargaan/bulk-delete',     [SiswaController::class, 'bulkDeletePenghargaan'])->name('penghargaan.bulk-delete')->middleware('permission:penghargaan.delete');
        Route::post('penghargaan/bulk-approve',    [SiswaController::class, 'bulkApprovePenghargaan'])->name('penghargaan.bulk-approve')->middleware('permission:penghargaan.approve');
        Route::post('penghargaan/{penghargaan}/approve', [SiswaController::class, 'approvePenghargaan'])->name('penghargaan.approve')->middleware('permission:penghargaan.approve');
    });

    // Petugas laporan kehadiran guru dari siswa
    Route::prefix('admin/petugas-laporan-guru')->as('admin.petugas-laporan-guru.')
        ->middleware('permission:petugas-laporan.view|petugas-laporan.update')
        ->group(function () {
            Route::get('/', [SiswaPetugasLaporanController::class, 'index'])->name('index');
            Route::put('/kelas/{kelas}', [SiswaPetugasLaporanController::class, 'update'])->name('update');
        });

    // GTK Laporan Kehadiran
    Route::prefix('kehadiran-guru')->name('kehadiran-guru.')->group(function () {
        Route::get('/laporan', [LaporanKehadiranController::class, 'index'])->name('laporan')->middleware('permission:kehadiran-guru.view');
        Route::get('/create', [LaporanKehadiranController::class, 'create'])->name('create')->middleware('permission:kehadiran-guru.create');
        Route::post('/laporan', [LaporanKehadiranController::class, 'store'])->name('store')->middleware('permission:kehadiran-guru.create');
        Route::get('/rekap', [LaporanKehadiranController::class, 'rekap'])->name('rekap');
        Route::get('/export/excel', [LaporanKehadiranController::class, 'exportExcel'])->name('export.excel')->middleware('permission:kehadiran-guru.view');
        Route::get('/export/pdf', [LaporanKehadiranController::class, 'exportPdf'])->name('export.pdf')->middleware('permission:kehadiran-guru.view');
        Route::get('/grafik', [LaporanKehadiranController::class, 'grafik'])->name('grafik')->middleware('permission:kehadiran-guru.view');
        Route::get('/grafik/data', [LaporanKehadiranController::class, 'grafikData'])->name('grafik.data')->middleware('permission:kehadiran-guru.view');
        Route::get('/{laporanKehadiran}', [LaporanKehadiranController::class, 'show'])->name('show')->middleware('permission:kehadiran-guru.view');
        Route::get('/{laporanKehadiran}/edit', [LaporanKehadiranController::class, 'edit'])->name('edit')->middleware('permission:kehadiran-guru.update');
        Route::put('/{laporanKehadiran}', [LaporanKehadiranController::class, 'update'])->name('update')->middleware('permission:kehadiran-guru.update');
        Route::delete('/{laporanKehadiran}', [LaporanKehadiranController::class, 'destroy'])->name('destroy')->middleware('permission:kehadiran-guru.delete');
        Route::post('/siswa', [LaporanKehadiranController::class, 'laporOlehSiswa'])->name('lapor-siswa')->middleware('permission:kehadiran-guru.lapor-siswa');
        Route::patch('/siswa/{laporanKehadiran}', [LaporanKehadiranController::class, 'updateOlehSiswa'])->name('update-siswa')->middleware('permission:kehadiran-guru.lapor-siswa');
    });

    // Guru Jurnal Mengajar
    Route::prefix('guru/jurnal-mengajar')
        ->as('guru.jurnal-mengajar.')
        ->middleware('permission:jurnal-mengajar.view')
        ->group(function () {
            Route::get('/', [JurnalMengajarController::class, 'index'])->name('index');
            Route::get('/export/excel', [JurnalMengajarController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [JurnalMengajarController::class, 'exportPdf'])->name('export.pdf');

            Route::middleware('permission:jurnal-mengajar.create|jurnal-mengajar.update|jurnal-mengajar.delete')->group(function () {
                Route::get('/create', [JurnalMengajarController::class, 'create'])->name('create')->middleware('permission:jurnal-mengajar.create');
                Route::post('/', [JurnalMengajarController::class, 'store'])->name('store')->middleware('permission:jurnal-mengajar.create');
                Route::get('/{jurnal}/edit', [JurnalMengajarController::class, 'edit'])->name('edit')->middleware('permission:jurnal-mengajar.update');
                Route::put('/{jurnal}', [JurnalMengajarController::class, 'update'])->name('update')->middleware('permission:jurnal-mengajar.update');
                Route::delete('/{jurnal}', [JurnalMengajarController::class, 'destroy'])->name('destroy')->middleware('permission:jurnal-mengajar.delete');
            });
        });

    // Absen Siswa (Unified)
    Route::prefix('absen')->name('absen.')->group(function () {
        Route::get('/', [AbsenController::class, 'index'])->name('index')->middleware('permission:absen.view');
        Route::get('/masuk', fn() => redirect()->route('absen.index'))->name('masuk');
        Route::get('/pulang', fn() => redirect()->route('absen.index'))->name('pulang');
        Route::post('/{jenis}', [AbsenController::class, 'store'])->name('store')->where(['jenis' => 'masuk|pulang'])->middleware('permission:absen.store');
        Route::post('/distance-check', [AbsenController::class, 'distanceCheck'])->name('distance-check')->middleware('permission:absen.view');
        Route::get('/status-hari-ini', [AbsenController::class, 'statusHariIni'])->name('status-hari-ini')->middleware('permission:absen.view');
        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap')->middleware('permission:absen.rekap.view');
        Route::get('/rekap/export', [RekapController::class, 'export'])->name('rekap.export')->middleware('permission:absen.rekap.export');
        Route::patch('/{absen}/manual', [AbsenController::class, 'updateManual'])->name('manual.update')->middleware('permission:absen.manual.update');
        Route::post('/manual/{jenis}', [AbsenController::class, 'manual'])->name('manual')->where(['jenis' => 'masuk|pulang'])->middleware('permission:absen.manual.store');
    });

    // Pengajuan Izin Siswa
    Route::prefix('izin')->as('siswa.izin.')->middleware('permission:izin.view|izin.create|izin.update|izin.delete')->group(function () {
        Route::get('/', [IzinController::class, 'index'])->name('index');
        Route::get('/create', [IzinController::class, 'create'])->name('create');
        Route::post('/', [IzinController::class, 'store'])->name('store');
        Route::get('/{izin}', [IzinController::class, 'show'])->name('show');
        Route::get('/{izin}/edit', [IzinController::class, 'edit'])->name('edit');
        Route::put('/{izin}', [IzinController::class, 'update'])->name('update');
        Route::delete('/{izin}', [IzinController::class, 'destroy'])->name('destroy');
    });

    // Admin Verifikasi Izin
    Route::prefix('admin/izin')->as('admin.izin.')->middleware('permission:izin.admin.view|izin.admin.update')->group(function () {
        Route::get('/', [AdminIzinController::class, 'index'])->name('index');
        Route::patch('/{izin}', [AdminIzinController::class, 'updateStatus'])->name('update');
    });

    // Admin Absensi Manual & Izin Admin
    Route::prefix('admin/absen-manual')->as('admin.absen-manual.')
        ->middleware('permission:absen.manual.view|absen.manual.store|absen.manual.update')
        ->group(function () {
            Route::get('/', [AbsenManualController::class, 'index'])->name('index');
            // Tambah absen manual
            Route::get('/create', [AbsenManualController::class, 'create'])->name('create');
            Route::post('/', [AbsenManualController::class, 'store'])->name('store');
            // Edit absen manual
            Route::get('/{absen}/edit', [AbsenManualController::class, 'edit'])->name('edit');
            Route::patch('/{absen}', [AbsenManualController::class, 'update'])->name('update');
            // Buat izin oleh admin
            Route::get('/izin/create', [AbsenManualController::class, 'createIzin'])->name('izin.create');
            Route::post('/izin', [AbsenManualController::class, 'storeIzin'])->name('izin.store');
            // Bulk izin (buat izin untuk beberapa siswa sekaligus)
            Route::post('/izin/bulk', [AbsenManualController::class, 'bulkIzin'])->name('izin.bulk');
            // API helpers
            Route::get('/api/search-siswa', [AbsenManualController::class, 'searchSiswa'])->name('api.search-siswa');
            Route::get('/api/status-siswa', [AbsenManualController::class, 'statusSiswa'])->name('api.status-siswa');
            Route::get('/api/cek-poin-alfa', [AbsenManualController::class, 'cekPoinAlfa'])->name('api.cek-poin-alfa');
            Route::delete('/api/hapus-poin-alfa', [AbsenManualController::class, 'hapusPoinAlfa'])->name('api.hapus-poin-alfa');
            Route::get('/api/cek-poin-alfa-range', [AbsenManualController::class, 'cekPoinAlfaRange'])->name('api.cek-poin-alfa-range');
            Route::delete('/api/hapus-poin-alfa-range', [AbsenManualController::class, 'hapusPoinAlfaRange'])->name('api.hapus-poin-alfa-range');
        });



    // Group dengan middleware permission
    Route::prefix('admin/tatib/surat-panggilan')
        ->as('admin.surat-panggilan.')
        ->middleware('permission:surat-panggilan.view|surat-panggilan.create|surat-panggilan.preview|surat-panggilan.delete')
        ->group(function () {

            // ── Static / API routes dulu (sebelum /{suratPanggilan}) ──
            Route::get('/',                    [SuratPanggilanController::class, 'index'])->name('index');
            Route::get('/create',              [SuratPanggilanController::class, 'create'])->name('create');
            Route::post('/',                   [SuratPanggilanController::class, 'store'])->name('store');
            Route::get('/api/panggilan-ke',    [SuratPanggilanController::class, 'panggilanKe'])->name('api.panggilan-ke');
            Route::get('/api/preview-poin',    [SuratPanggilanController::class, 'previewPoin'])->name('api.preview-poin');
            Route::get('/api/nomor-ortu',      [SuratPanggilanController::class, 'nomorOrtu'])->name('api.nomor-ortu');

            // ── Resource routes dengan {suratPanggilan} ──
            Route::get('/{suratPanggilan}/preview', [SuratPanggilanController::class, 'preview'])->name('preview');
            Route::post('/{suratPanggilan}/kirim-wa', [SuratPanggilanController::class, 'kirimWa'])->name('kirim-wa');
            Route::delete('/{suratPanggilan}',  [SuratPanggilanController::class, 'destroy'])->name('destroy');
        });

    // Tata tertib: pelanggaran, penghargaan, dan rekap poin
    Route::prefix('admin/tatib')->as('admin.')->middleware('permission:pelanggaran.view|penghargaan.view|pasal.view|rekap-poin.view')->group(function () {
        Route::middleware('permission:pasal.view|pasal.create|pasal.update|pasal.delete|pasal.import|pasal.toggle')->group(function () {
            Route::get('pasal/import', [PasalController::class, 'import'])->name('pasal.import');
            Route::post('pasal/import', [PasalController::class, 'importProcess'])->name('pasal.import.process');
            Route::get('pasal/template', [PasalController::class, 'template'])->name('pasal.template');
            Route::patch('pasal/{pasal}/toggle', [PasalController::class, 'toggle'])->name('pasal.toggle');
            Route::resource('pasal', PasalController::class)->except(['show']);
        });

        Route::resource('pelanggaran', PelanggaranController::class)->except(['show'])->middleware('permission:pelanggaran.view|pelanggaran.create|pelanggaran.update|pelanggaran.delete');
        Route::post('pelanggaran/bulk-delete', [PelanggaranController::class, 'bulkDelete'])->name('pelanggaran.bulk-delete')->middleware('permission:pelanggaran.delete');

        Route::resource('penghargaan', PenghargaanController::class)->except(['show'])->middleware('permission:penghargaan.view|penghargaan.create|penghargaan.update|penghargaan.delete');
        Route::post('penghargaan/bulk-delete', [PenghargaanController::class, 'bulkDelete'])->name('penghargaan.bulk-delete')->middleware('permission:penghargaan.delete');
        Route::post('penghargaan/bulk-approve', [PenghargaanController::class, 'bulkApprove'])->name('penghargaan.bulk-approve')->middleware('permission:penghargaan.approve');

        // Routes untuk approval/rejection penghargaan
        Route::post('penghargaan/{penghargaan}/approve', [PenghargaanController::class, 'approve'])->name('penghargaan.approve')->middleware('permission:penghargaan.approve');
        Route::post('penghargaan/{penghargaan}/reject', [PenghargaanController::class, 'reject'])->name('penghargaan.reject')->middleware('permission:penghargaan.reject');
        Route::post('penghargaan/{penghargaan}/revoke', [PenghargaanController::class, 'revoke'])->name('penghargaan.revoke')->middleware('permission:penghargaan.revoke');

        // Jurnal laporan cetak
        Route::get('jurnal', [JurnalTatibController::class, 'index'])->name('tatib.jurnal.index')->middleware('permission:jurnal-tatib.view');
        Route::get('jurnal/cetak', [JurnalTatibController::class, 'cetak'])->name('tatib.jurnal.cetak')->middleware('permission:jurnal-tatib.cetak');
        Route::get('jurnal/cetak-siswa', [JurnalTatibController::class, 'cetakSiswa'])->name('tatib.jurnal.cetak-siswa')->middleware('permission:jurnal-tatib.cetak');
        Route::get('jurnal/siswa-search', [JurnalTatibController::class, 'siswaSearchJurnal'])->name('tatib.jurnal.siswa-search')->middleware('permission:jurnal-tatib.view');
    });

    Route::prefix('tatib')->as('admin.')->middleware('permission:rekap-poin.view')->group(function () {
        Route::get('/rekap-poin', [RekapPoinController::class, 'index'])->name('rekap-poin.index');
        Route::get('/rekap-poin/{siswa}', [RekapPoinController::class, 'show'])->name('rekap-poin.show');
        Route::get('/siswa-search', [PenghargaanController::class, 'siswaSearch'])->name('tatib.siswa-search');
        Route::get('/chart', [RekapPoinController::class, 'chart'])
            ->name('tatib.chart')
            ->middleware('permission:rekap-poin.chart');
    });

    // School Configuration
    Route::prefix('admin/school-config')->as('admin.school-config.')->middleware('permission:school-config.view|school-config.update')->group(function () {
        Route::get('/', [SchoolConfigController::class, 'index'])->name('index');
        Route::put('/', [SchoolConfigController::class, 'update'])->name('update');
    });

    // Auto Rules — Pelanggaran & Penghargaan
    Route::prefix('admin/auto-rules')->as('admin.auto-rules.')->middleware('permission:school-config.update')->group(function () {
        // Pelanggaran rules
        Route::get('/pelanggaran', [AutoPelanggaranRuleController::class, 'index'])->name('pelanggaran.index');
        Route::post('/pelanggaran', [AutoPelanggaranRuleController::class, 'store'])->name('pelanggaran.store');
        Route::put('/pelanggaran/{rule}', [AutoPelanggaranRuleController::class, 'update'])->name('pelanggaran.update');
        Route::delete('/pelanggaran/{rule}', [AutoPelanggaranRuleController::class, 'destroy'])->name('pelanggaran.destroy');
        Route::patch('/pelanggaran/{rule}/toggle', [AutoPelanggaranRuleController::class, 'toggle'])->name('pelanggaran.toggle');

        // Penghargaan rules
        Route::get('/penghargaan', [AutoPenghargaanRuleController::class, 'index'])->name('penghargaan.index');
        Route::post('/penghargaan', [AutoPenghargaanRuleController::class, 'store'])->name('penghargaan.store');
        Route::put('/penghargaan/{rule}', [AutoPenghargaanRuleController::class, 'update'])->name('penghargaan.update');
        Route::delete('/penghargaan/{rule}', [AutoPenghargaanRuleController::class, 'destroy'])->name('penghargaan.destroy');
        Route::patch('/penghargaan/{rule}/toggle', [AutoPenghargaanRuleController::class, 'toggle'])->name('penghargaan.toggle');
    });

    // Dashboard Laporan Aktivitas
    Route::prefix('admin/dashboard-laporan')->as('admin.dashboard-laporan.')->middleware('permission:dashboard-laporan.view')->group(function () {
        Route::get('/', [DashboardLaporanController::class, 'index'])->name('index');
        Route::get('/api/data', [DashboardLaporanController::class, 'apiData'])->name('api.data');
    });

    // Role & Permission Management (superadmin only)
    Route::prefix('admin/roles')->as('admin.roles.')->middleware('permission:role.view|role.create|role.update|role.delete')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('admin/permissions')->as('admin.permissions.')->middleware('permission:permission.create|permission.delete')->group(function () {
        Route::post('/', [RoleController::class, 'storePermission'])->name('store');
        Route::delete('/{permission}', [RoleController::class, 'destroyPermission'])->name('destroy');
    });

    // Halaman khusus siswa lapor guru tidak hadir
    Route::get('/lapor-guru-tidak-hadir', [LaporanKehadiranController::class, 'laporSiswa'])
        ->name('siswa.lapor-guru')
        ->middleware('permission:kehadiran-guru.lapor-siswa');

    // Validasi: hanya bisa mengirim laporan jika jam pelajaran telah dimulai
    Route::middleware(['auth'])->group(function () {
        // placeholder for potential middleware if needed
    });

    // Realtime Panel for Kepala Sekolah & Waka
    Route::prefix('panel')->name('panel.')->middleware('permission:panel.realtime')->group(function () {
        Route::get('/realtime', [RealtimePanelController::class, 'index'])->name('realtime');
        Route::get('/api/status', [RealtimePanelController::class, 'getStatus'])->name('api.status')->middleware('permission:panel.api');
        Route::get('/api/rekap-bulan', [RealtimePanelController::class, 'getRekapBulan'])->name('api.rekap-bulan')->middleware('permission:panel.api');
        // Endpoint untuk reload dropdown jam saat tanggal berubah
        Route::get('/api/jam', [RealtimePanelController::class, 'getJamByHari'])->name('api.jam')->middleware('permission:panel.api');
    });

    // Event Absen
    Route::prefix('event')->name('event.')->middleware('permission:event.view')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::get('/create', [EventController::class, 'create'])->name('create')->middleware('permission:event.create');
        Route::post('/', [EventController::class, 'store'])->name('store')->middleware('permission:event.create');
        Route::get('/{event}', [EventController::class, 'show'])->name('show');
        Route::post('/{event}/tatib-points/bulk-delete', [EventController::class, 'bulkDeleteEventTatibPoints'])
            ->name('tatib-points.bulk-delete')
            ->middleware('permission:event.update');
        Route::post('/{event}/attendance/bulk-update', [EventController::class, 'bulkUpdateAttendanceStatus'])
            ->name('attendance.bulk-update')
            ->middleware('permission:event.update');
        Route::get('/{event}/edit', [EventController::class, 'edit'])->name('edit')->middleware('permission:event.update');
        Route::put('/{event}', [EventController::class, 'update'])->name('update')->middleware('permission:event.update');
        Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy')->middleware('permission:event.delete');
        Route::post('/{event}/rotate-barcode', [EventController::class, 'rotateBarcode'])->name('rotateBarcode')->middleware('permission:event.barcode');
        Route::get('/{event}/barcode', [EventController::class, 'getBarcode'])->name('barcode')->middleware('permission:event.barcode');
        Route::post('/{event}/barcode', [EventController::class, 'updateBarcode'])->name('updateBarcode')->middleware('permission:event.barcode');
        Route::get('/{event}/barcode-stream', [EventController::class, 'barcodeStream'])->name('barcodeStream')->middleware('permission:event.barcode');
        Route::get('/search/kelas', [EventController::class, 'searchKelas'])->name('search.kelas');
        Route::get('/search/siswa', [EventController::class, 'searchSiswa'])->name('search.siswa');

        Route::get('/{event}/scan', [AbsenEventController::class, 'scan'])->name('scan')->middleware('permission:event.scan');
        Route::post('/{event}/scan', [AbsenEventController::class, 'processScan'])->name('processScan')->middleware('permission:event.scan');
        Route::get('/{event}/rekap', [AbsenEventController::class, 'rekap'])->name('rekap')->middleware('permission:event.rekap');
        Route::get('/{event}/export', [AbsenEventController::class, 'export'])->name('export')->middleware('permission:event.export');
        Route::get('/{event}/jurnal', [AbsenEventController::class, 'jurnal'])->name('jurnal')->middleware('permission:event.jurnal');
    });

    // Event Guru (sistem absensi guru terpisah dari event siswa)
    Route::prefix('event-guru')->name('event-guru.')->middleware('permission:event-guru.view')->group(function () {
        Route::get('/', [EventGuruController::class, 'index'])->name('index');
        Route::get('/create', [EventGuruController::class, 'create'])->name('create')->middleware('permission:event-guru.create');
        Route::post('/', [EventGuruController::class, 'store'])->name('store')->middleware('permission:event-guru.create');
        Route::get('/rekap', [AbsenEventGuruController::class, 'rekapIndex'])->name('rekap.index')->middleware('permission:event-guru.rekap');
        Route::get('/{eventGuru}', [EventGuruController::class, 'show'])->name('show');
        Route::get('/{eventGuru}/edit', [EventGuruController::class, 'edit'])->name('edit')->middleware('permission:event-guru.update');
        Route::put('/{eventGuru}', [EventGuruController::class, 'update'])->name('update')->middleware('permission:event-guru.update');
        Route::delete('/{eventGuru}', [EventGuruController::class, 'destroy'])->name('destroy')->middleware('permission:event-guru.delete');
        Route::post('/{eventGuru}/rotate-barcode', [EventGuruController::class, 'rotateBarcode'])->name('rotateBarcode')->middleware('permission:event-guru.barcode');
        Route::get('/{eventGuru}/barcode', [EventGuruController::class, 'getBarcode'])->name('barcode')->middleware('permission:event-guru.barcode');
        Route::post('/{eventGuru}/barcode', [EventGuruController::class, 'updateBarcode'])->name('updateBarcode')->middleware('permission:event-guru.barcode');
        Route::get('/{eventGuru}/barcode-stream', [EventGuruController::class, 'barcodeStream'])->name('barcodeStream')->middleware('permission:event-guru.barcode');
        Route::get('/{eventGuru}/scan', [AbsenEventGuruController::class, 'scan'])->name('scan')->middleware('permission:event-guru.scan');
        Route::post('/{eventGuru}/scan', [AbsenEventGuruController::class, 'processScan'])->name('processScan')->middleware('permission:event-guru.scan');
        Route::get('/{eventGuru}/rekap', [AbsenEventGuruController::class, 'rekap'])->name('rekap')->middleware('permission:event-guru.rekap');
        Route::get('/{eventGuru}/export', [AbsenEventGuruController::class, 'export'])->name('export')->middleware('permission:event-guru.export');
    });

    // ══════════════════════════════════════════════════════════════════════
    // PKL — Admin: Lokasi PKL
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/pkl/lokasi')
        ->as('admin.pkl.lokasi.')
        ->middleware('permission:pkl.view|pkl.create|pkl.update|pkl.delete')
        ->group(function () {
            Route::get('/',                  [LokasiPklController::class, 'index'])->name('index')->middleware('permission:pkl.view');
            Route::get('/create',            [LokasiPklController::class, 'create'])->name('create')->middleware('permission:pkl.create');
            Route::post('/',                 [LokasiPklController::class, 'store'])->name('store')->middleware('permission:pkl.create');
            Route::get('/{lokasiPkl}',       [LokasiPklController::class, 'show'])->name('show')->middleware('permission:pkl.view');
            Route::get('/{lokasiPkl}/edit',  [LokasiPklController::class, 'edit'])->name('edit')->middleware('permission:pkl.update');
            Route::put('/{lokasiPkl}',       [LokasiPklController::class, 'update'])->name('update')->middleware('permission:pkl.update');
            Route::delete('/{lokasiPkl}',    [LokasiPklController::class, 'destroy'])->name('destroy')->middleware('permission:pkl.delete');
            // Wilayah proxy — hindari CORS dari browser localhost/production
            Route::get('/api/wilayah/{tipe}',      [LokasiPklController::class, 'apiWilayah'])->name('api.wilayah')->where('tipe', 'provinces|regencies|districts|villages');
            Route::get('/api/wilayah/{tipe}/{id}', [LokasiPklController::class, 'apiWilayah'])->name('api.wilayah.id')->where('tipe', 'regencies|districts|villages');
        });

    // ══════════════════════════════════════════════════════════════════════
    // PKL — Admin: Penugasan Siswa
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/pkl/penugasan')
        ->as('admin.pkl.penugasan.')
        ->middleware('permission:pkl.view|pkl.create|pkl.update')
        ->group(function () {
            Route::get('/',                           [PenugasanPklController::class, 'index'])->name('index')->middleware('permission:pkl.view');
            Route::get('/create',                     [PenugasanPklController::class, 'create'])->name('create')->middleware('permission:pkl.create');
            Route::post('/',                          [PenugasanPklController::class, 'store'])->name('store')->middleware('permission:pkl.create');
            Route::patch('/{penugasanPkl}/batal',    [PenugasanPklController::class, 'batal'])->name('batal')->middleware('permission:pkl.update');
            Route::patch('/{penugasanPkl}/selesai',  [PenugasanPklController::class, 'selesai'])->name('selesai')->middleware('permission:pkl.update');
            // AJAX
            Route::get('/api/search-siswa',          [PenugasanPklController::class, 'searchSiswa'])->name('api.search-siswa')->middleware('permission:pkl.view');
        });

    // ══════════════════════════════════════════════════════════════════════
    // PKL — Admin: Rekap & Laporan
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/pkl/rekap')
        ->as('admin.pkl.rekap.')
        ->middleware('permission:pkl.view')
        ->group(function () {
            Route::get('/per-lokasi',                 [RekaporanPklController::class, 'perLokasi'])->name('per-lokasi');
            Route::get('/per-siswa',                  [RekaporanPklController::class, 'perSiswa'])->name('per-siswa');
            Route::get('/detail/{penugasanPkl}',      [RekaporanPklController::class, 'detailSiswa'])->name('detail-siswa');
            Route::patch('/jurnal/{jurnal}/verifikasi', [RekaporanPklController::class, 'verifikasiJurnal'])->name('jurnal.verifikasi')->middleware('permission:pkl.update');
        });

    // ══════════════════════════════════════════════════════════════════════
    // PKL — Siswa: Absensi, Dashboard & Jurnal Harian
    // Hanya siswa dengan penugasan PKL aktif yang bisa mengakses.
    // Controller akan redirect jika tidak punya penugasan aktif.
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('pkl')
        ->as('siswa.pkl.')
        ->middleware('permission:pkl.siswa.view')
        ->group(function () {
            // ── Absensi PKL (selfie + GPS ke lokasi PKL) ─────────────────
            Route::get('/absen',              [AbsenPklController::class, 'index'])->name('absen.index');
            Route::post('/absen/{jenis}',     [AbsenPklController::class, 'store'])->name('absen.store')->where(['jenis' => 'masuk|pulang']);
            Route::get('/absen/status',       [AbsenPklController::class, 'statusHariIni'])->name('absen.status');
            Route::post('/absen/distance-check', [AbsenPklController::class, 'distanceCheck'])->name('absen.distance-check');

            // ── Dashboard & Jurnal ────────────────────────────────────────
            Route::get('/dashboard',       [JurnalPklController::class, 'dashboard'])->name('dashboard');
            Route::get('/jurnal',          [JurnalPklController::class, 'index'])->name('jurnal.index');
            Route::get('/jurnal/rekap',    [JurnalPklController::class, 'rekapJurnal'])->name('jurnal.rekap');
            Route::get('/jurnal/create',   [JurnalPklController::class, 'create'])->name('jurnal.create');
            Route::post('/jurnal',         [JurnalPklController::class, 'store'])->name('jurnal.store');
            Route::get('/jurnal/{jurnal}/edit',   [JurnalPklController::class, 'edit'])->name('jurnal.edit');
            Route::put('/jurnal/{jurnal}',        [JurnalPklController::class, 'update'])->name('jurnal.update');
        });
});

// ══════════════════════════════════════════════════════════════════════════════
// Developer Panel — TERSEMBUNYI, tidak ada link di UI mana pun.
// Hanya user dengan email = DEVELOPER_EMAIL yang bisa akses.
// Jika bukan developer: menampilkan 404 (bukan 403) agar fitur tidak bocor.
// Akses manual: /dev-panel
// ══════════════════════════════════════════════════════════════════════════════
use App\Http\Controllers\Developer\ImpersonateController;

// Leave hanya butuh 'auth' — saat impersonate aktif, auth user adalah target,
// bukan developer, sehingga is_developer akan memblokir. Keamanan dijaga
// di dalam controller (leaveImpersonation hanya bisa jika session impersonate ada).
Route::middleware('auth')
    ->post('/dev-panel/impersonate/leave', [ImpersonateController::class, 'leave'])
    ->name('developer.impersonate.leave');

Route::middleware(['auth', 'is_developer'])
    ->prefix('dev-panel')
    ->name('developer.')
    ->group(function () {
        // Halaman daftar semua user untuk impersonate
        Route::get('/',                     [ImpersonateController::class, 'index'])->name('index');
        // Mulai impersonate user tertentu
        Route::post('/impersonate/{user}',  [ImpersonateController::class, 'take'])->name('impersonate.take');
    });
