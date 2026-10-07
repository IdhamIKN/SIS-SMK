<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom konfigurasi Auto Alfa dan Notifikasi WA ke tblsekolah.
     */
    public function up(): void
    {
        Schema::table('tblsekolah', function (Blueprint $table) {
            // Toggle notifikasi WA saat presensi masuk (hadir / terlambat)
            $table->boolean('wa_notif_masuk_enabled')->default(false)->after('wasekolah');

            // Toggle aktifkan fitur Auto Alfa
            $table->boolean('auto_alfa_enabled')->default(false)->after('wa_notif_masuk_enabled');

            // Jam eksekusi Auto Alfa (format HH:MM), contoh: 08:00
            $table->time('jam_eksekusi_auto_alfa')->nullable()->after('auto_alfa_enabled');

            // Toggle otomatis beri poin pelanggaran saat Auto Alfa
            $table->boolean('auto_point_alfa_enabled')->default(false)->after('jam_eksekusi_auto_alfa');

            // Pasal pelanggaran yang digunakan untuk Auto Alfa
            $table->string('pasal_alfa_id', 20)->nullable()->after('auto_point_alfa_enabled');

            // Toggle notifikasi WA kepada ortu saat siswa di-alfa otomatis
            $table->boolean('wa_notif_alfa_enabled')->default(false)->after('pasal_alfa_id');
        });
    }

    public function down(): void
    {
        Schema::table('tblsekolah', function (Blueprint $table) {
            $table->dropColumn([
                'wa_notif_masuk_enabled',
                'auto_alfa_enabled',
                'jam_eksekusi_auto_alfa',
                'auto_point_alfa_enabled',
                'pasal_alfa_id',
                'wa_notif_alfa_enabled',
            ]);
        });
    }
};
