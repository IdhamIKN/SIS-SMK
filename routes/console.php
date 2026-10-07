<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─────────────────────────────────────────────────────────────────────────────
// Semua jadwal scheduler SIS didefinisikan di sini.
// Kernel.php (App\Console\Kernel) tidak digunakan di Laravel 11.
// ─────────────────────────────────────────────────────────────────────────────

// Generate record absensi kosong untuk semua siswa di awal hari (05:30).
// Harus jalan SEBELUM auto-alfa agar record sudah tersedia untuk di-update.
Schedule::command('absen:generate-harian')
    ->dailyAt('05:30')
    ->withoutOverlapping();

// Auto Alfa: cek setiap menit antara jam 06:00–12:00.
// Command akan mengecek sendiri apakah jam eksekusi sudah tercapai sesuai konfigurasi sekolah.
Schedule::command('absen:auto-alfa')
    ->everyMinute()
    ->between('06:00', '12:00')
    ->withoutOverlapping();

// Auto-fill absensi: jalankan jam 13:00 (setelah jam belajar selesai).
// Sengaja digeser ke 13:00 agar auto-alfa sempat jalan lebih dulu.
Schedule::command('attendance:auto-fill')
    ->dailyAt('13:00')
    ->withoutOverlapping();

// Update status absensi dari izin yang baru disetujui.
Schedule::command('attendance:update-from-approved-izin')
    ->everyFiveMinutes()
    ->between('08:00', '23:59')
    ->withoutOverlapping();

// Berikan poin pelanggaran otomatis ke siswa yang tidak scan pada event yang sudah selesai.
Schedule::command('event:auto-point-pelanggaran')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Berikan poin penghargaan otomatis ke siswa yang berhasil scan masuk pada event yang sudah selesai.
Schedule::command('event:auto-penghargaan')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Rotasi barcode event aktif setiap menit.
Schedule::command('event:rotate-barcodes')
    ->everyMinute();

// Tandai kelas yang belum ada laporan setelah 20 menit.
Schedule::command('sis:check-orange')
    ->everyMinute();

// Auto poin kehadiran harian (hadir/terlambat/alfa) — jalan setelah auto-alfa selesai
// Waktu 08:30 memberi cukup waktu untuk auto-alfa (08:59) dan generate harian (05:30)
// Siswa yang sudah punya poin dari auto-alfa tidak akan dapat poin ganda (idempotent)
Schedule::command('absen:auto-poin-harian')
    ->dailyAt('09:00')
    ->withoutOverlapping();
// Data baru sudah otomatis di-sync via PelanggaranObserver.
// Cukup dijalankan sekali sehari di malam hari sebagai safety net.
Schedule::command('tatib:sync-transaksi --missing')
    ->dailyAt('01:00')
    ->withoutOverlapping();

// Fix-deleted: soft-delete transaksi yang sumbernya sudah di-soft-delete.
// Menangani data historis yang dibuat sebelum PelanggaranObserver/PenghargaanObserver dipasang.
Schedule::command('tatib:sync-transaksi --fix-deleted')
    ->dailyAt('01:30')
    ->withoutOverlapping();
