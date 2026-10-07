<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah konfigurasi auto poin kehadiran ke tblsekolah:
 *
 * Hadir tepat waktu → poin penghargaan (pasal_hadir_id)
 * Terlambat         → poin pelanggaran (pasal_terlambat_id)
 * Alfa              → poin pelanggaran (pasal_alfa_id — sudah ada, tambah override poin)
 *
 * Masing-masing bisa di-toggle aktif/nonaktif secara independen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblsekolah', function (Blueprint $table) {
            // ── Poin Hadir Tepat Waktu ─────────────────────────────────────────
            $table->boolean('auto_poin_hadir_enabled')->default(false)->after('pasal_alfa_id');
            $table->string('pasal_hadir_id', 20)->nullable()->after('auto_poin_hadir_enabled');

            // ── Poin Terlambat ─────────────────────────────────────────────────
            $table->boolean('auto_poin_terlambat_enabled')->default(false)->after('pasal_hadir_id');
            $table->string('pasal_terlambat_id', 20)->nullable()->after('auto_poin_terlambat_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('tblsekolah', function (Blueprint $table) {
            $table->dropColumn([
                'auto_poin_hadir_enabled',
                'pasal_hadir_id',
                'auto_poin_terlambat_enabled',
                'pasal_terlambat_id',
            ]);
        });
    }
};
