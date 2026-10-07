<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Toggle + nomor penerima notif laporan kehadiran guru di tblsekolah
        Schema::table('tblsekolah', function (Blueprint $table) {
            $table->boolean('wa_notif_laporan_guru_enabled')->default(false)->after('wa_notif_tatib_enabled');
            // Nomor penerima disimpan sebagai JSON array (bisa lebih dari 1)
            $table->json('wa_notif_laporan_guru_nomor')->nullable()->after('wa_notif_laporan_guru_enabled');
        });

        // Tambah kolom wa_terkirim ke laporan_kehadiran_guru
        Schema::table('laporan_kehadiran_guru', function (Blueprint $table) {
            $table->boolean('wa_terkirim')->default(false)->after('catatan');
        });
    }

    public function down(): void
    {
        Schema::table('tblsekolah', function (Blueprint $table) {
            $table->dropColumn(['wa_notif_laporan_guru_enabled', 'wa_notif_laporan_guru_nomor']);
        });

        Schema::table('laporan_kehadiran_guru', function (Blueprint $table) {
            $table->dropColumn('wa_terkirim');
        });
    }
};
