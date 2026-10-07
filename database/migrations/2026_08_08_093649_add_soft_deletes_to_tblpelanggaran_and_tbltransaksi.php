<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah soft delete ke tblpelanggaran dan tbltransaksi.
 *
 * Manfaat:
 * - Data tidak hilang permanen saat status absen siswa diubah (alfa → terlambat, dll.)
 * - Admin bisa memulihkan data jika terjadi kesalahan
 * - Audit trail tetap terjaga
 * - Pelanggaran::withTrashed() bisa digunakan untuk rekap historis
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblpelanggaran', function (Blueprint $table) {
            $table->softDeletes(); // kolom deleted_at TIMESTAMP NULL
        });

        Schema::table('tbltransaksi', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('tblpelanggaran', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('tbltransaksi', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
