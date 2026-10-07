<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblsekolah', function (Blueprint $table) {
            // Toggle notifikasi WA saat absen pulang
            $table->boolean('wa_notif_pulang_enabled')->default(false)->after('wa_notif_masuk_enabled');

            // Toggle notifikasi WA saat absen event (absen kegiatan)
            $table->boolean('wa_notif_event_enabled')->default(false)->after('wa_notif_pulang_enabled');

            // Toggle notifikasi WA saat siswa mencapai ambang poin tatib
            $table->boolean('wa_notif_tatib_enabled')->default(false)->after('wa_notif_event_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('tblsekolah', function (Blueprint $table) {
            $table->dropColumn([
                'wa_notif_pulang_enabled',
                'wa_notif_event_enabled',
                'wa_notif_tatib_enabled',
            ]);
        });
    }
};
