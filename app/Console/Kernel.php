<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;


class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Generate record absensi kosong untuk semua siswa di awal hari (05:30).
        // Harus jalan SEBELUM auto-alfa agar record sudah tersedia untuk di-update.
        $schedule->command('absen:generate-harian')
            ->dailyAt('05:30')
            ->withoutOverlapping();

        // Auto Alfa: cek setiap menit antara jam 06:00–12:00.
        // Command akan mengecek sendiri apakah jam eksekusi sudah tercapai sesuai konfigurasi sekolah.
        $schedule->command('absen:auto-alfa')
            ->everyMinute()
            ->between('06:00', '12:00')
            ->withoutOverlapping();

        // Auto-fill absensi: jalankan jam 13:00 (setelah jam belajar selesai).
        // Sengaja digeser ke 13:00 agar auto-alfa sempat jalan lebih dulu.
        $schedule->command('attendance:auto-fill')
            ->dailyAt('13:00')
            ->withoutOverlapping();

        // Update status absensi dari izin yang baru disetujui.
        $schedule->command('attendance:update-from-approved-izin')
            ->everyFiveMinutes()
            ->between('08:00', '23:59')
            ->withoutOverlapping();

        // Berikan poin pelanggaran otomatis ke siswa yang tidak scan pada event yang sudah selesai.
        $schedule->command('event:auto-point-pelanggaran')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        // Berikan poin penghargaan otomatis ke siswa yang berhasil scan masuk pada event yang sudah selesai.
        $schedule->command('event:auto-penghargaan')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        // Rotasi barcode event aktif setiap menit.
        $schedule->command('event:rotate-barcodes')
            ->everyMinute();

        // Tandai kelas yang belum ada laporan setelah 20 menit.
        $schedule->command('sis:check-orange')
            ->between('06:00', '18:00')
            ->everyMinute()
            ->withoutOverlapping();

        // Sinkronisasi tblpelanggaran -> tbltransaksi setiap 30 menit.
        $schedule->command('tatib:sync-transaksi')
            ->everyThirtyMinutes()
            ->withoutOverlapping();

        // Sinkronisasi status absen berdasarkan kombinasi pelanggaran:
        // B012(alfa) + pelanggaran lain di hari yang sama → terlambat + hapus B012
        // Jalankan setelah auto-alfa selesai (jam 10:00 ke atas)
        $schedule->command('tatib:sync-absen-dari-pelanggaran')
            ->dailyAt('10:30')
            ->withoutOverlapping();

        // Auto poin PKL harian: hadir/terlambat/alfa untuk siswa yang sedang aktif PKL.
        // Jalankan jam 16:00 (setelah jam pulang PKL pada umumnya).
        $schedule->command('pkl:auto-poin-harian')
            ->dailyAt('16:00')
            ->withoutOverlapping();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
