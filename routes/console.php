<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-rotate barcode untuk event yang aktif setiap menit
Schedule::command('event:rotate-barcodes')->everyMinute();

// Cek kelas orange (belum ada laporan setelah 20 menit) setiap menit
Schedule::command('sis:check-orange')->everyMinute();
