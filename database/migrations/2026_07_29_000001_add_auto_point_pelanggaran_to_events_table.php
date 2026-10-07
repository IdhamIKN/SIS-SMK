<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('auto_point_pelanggaran')
                ->default(false)
                ->after('ada_absen_pulang')
                ->comment('Jika true, siswa yg tidak scan masuk otomatis diberi poin pelanggaran setelah event selesai');

            $table->string('pasal_pelanggaran_id', 5)
                ->nullable()
                ->after('auto_point_pelanggaran')
                ->comment('FK ke tblsubpasal.idpasal — pasal acuan pemberian poin otomatis');

            // Kolom untuk menandai bahwa auto-point sudah dijalankan (mencegah duplikasi)
            $table->timestamp('auto_point_processed_at')
                ->nullable()
                ->after('pasal_pelanggaran_id')
                ->comment('Timestamp saat auto-point pelanggaran sudah diproses');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'auto_point_pelanggaran',
                'pasal_pelanggaran_id',
                'auto_point_processed_at',
            ]);
        });
    }
};
