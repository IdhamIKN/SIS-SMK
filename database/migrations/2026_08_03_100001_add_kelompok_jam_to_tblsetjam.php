<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom kelompok_jam ke tblsetjam.
 *
 * Tujuan: mendukung variasi jadwal yang jam ke-nya sama (nomor urut sama)
 * tapi waktu berbeda, seperti jadwal Jumat yang lebih pendek.
 *
 * Nilai yang tersedia:
 *   - 'reguler'  : jadwal normal Senin–Kamis (dan Sabtu jika ada)
 *   - 'jumat'    : jadwal Jumat (jam lebih pendek / khusus)
 *
 * Kolom ini nullable dengan default 'reguler' agar data lama tidak rusak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblsetjam', function (Blueprint $table) {
            $table->string('kelompok_jam', 20)
                  ->default('reguler')
                  ->after('shif')
                  ->comment('Kelompok jadwal: reguler (Senin-Kamis) atau jumat');
        });
    }

    public function down(): void
    {
        Schema::table('tblsetjam', function (Blueprint $table) {
            $table->dropColumn('kelompok_jam');
        });
    }
};
