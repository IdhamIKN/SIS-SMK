<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GTK\GTKController;
use App\Http\Controllers\Siswa\SiswaController;
use App\Http\Controllers\Siswa\AbsenController;
use App\Http\Controllers\GTK\LaporanKehadiranController;

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

    // GTK Management
    Route::resource('gtk', GTKController::class)->middleware('role:superadmin|admin_tatib');

    // Siswa Management
    Route::resource('siswa', SiswaController::class)->middleware('role:superadmin|admin_tatib|bk|wali_kelas');
    Route::post('siswa/import', [SiswaController::class, 'import'])->name('siswa.import')->middleware('role:superadmin|admin_tatib');
    Route::get('siswa/export/excel', [SiswaController::class, 'export'])->name('siswa.export')->middleware('role:superadmin|admin_tatib');
    Route::get('siswa/{siswa}/export-cv', [SiswaController::class, 'exportCV'])->name('siswa.export-cv')->middleware('role:superadmin|admin_tatib|bk|wali_kelas');

    // GTK Laporan Kehadiran
    Route::prefix('gtk/laporan-kehadiran')->name('gtk.laporan-kehadiran.')->group(function () {
        Route::get('/', [LaporanKehadiranController::class, 'index'])->name('index')->middleware('role:gtk|superadmin|admin_tatib|bk');
        Route::get('/create', [LaporanKehadiranController::class, 'create'])->name('create')->middleware('role:gtk');
        Route::post('/', [LaporanKehadiranController::class, 'store'])->name('store')->middleware('role:gtk');
        Route::get('/{laporanKehadiran}', [LaporanKehadiranController::class, 'show'])->name('show')->middleware('role:gtk|superadmin|admin_tatib|bk');
    });

    // Absen Siswa (Unified)
    Route::prefix('absen')->name('absen.')->group(function () {
        Route::get('/', [AbsenController::class, 'index'])->name('index')->middleware('role:siswa');
        Route::post('/{jenis}', [AbsenController::class, 'store'])->name('store')->where(['jenis' => 'masuk|pulang'])->middleware('role:siswa');
        Route::post('/distance-check', [AbsenController::class, 'distanceCheck'])->name('distance-check')->middleware('role:siswa');
        Route::get('/status-hari-ini', [AbsenController::class, 'statusHariIni'])->name('status-hari-ini')->middleware('role:siswa');
        Route::post('/manual/{jenis}', [AbsenController::class, 'manual'])->name('manual')->where(['jenis' => 'masuk|pulang'])->middleware('role:bk|superadmin');
    });
});
