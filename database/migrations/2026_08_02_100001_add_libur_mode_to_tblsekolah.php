<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tambah kolom Mode Libur Panjang ke tblsekolah.
 *
 * Saat libur_mode = true DAN hari ini berada di antara libur_dari–libur_sampai,
 * seluruh proses otomatis (Auto Alfa, Auto-fill absen, Auto Point Pelanggaran,
 * dan semua notifikasi WA) akan berhenti beroperasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblsekolah', function (Blueprint $table) {
            // Toggle aktifkan mode libur panjang
            $table->boolean('libur_mode')
                ->default(false)
                ->after('auto_point_alfa_enabled')
                ->comment('Jika true dan hari ini dalam rentang libur, semua proses otomatis dinonaktifkan');

            // Tanggal awal libur
            $table->date('libur_dari')
                ->nullable()
                ->after('libur_mode')
                ->comment('Awal rentang libur panjang (inklusif)');

            // Tanggal akhir libur
            $table->date('libur_sampai')
                ->nullable()
                ->after('libur_dari')
                ->comment('Akhir rentang libur panjang (inklusif)');
        });
    }

    public function down(): void
    {
        Schema::table('tblsekolah', function (Blueprint $table) {
            $table->dropColumn(['libur_mode', 'libur_dari', 'libur_sampai']);
        });
    }
};
