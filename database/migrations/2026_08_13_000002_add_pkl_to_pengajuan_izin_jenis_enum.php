<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tambahkan nilai 'pkl' ke ENUM kolom jenis di tabel pengajuan_izin.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pengajuan_izin MODIFY COLUMN `jenis` ENUM('izin_sakit','izin_pulang_cepat','izin_terlambat','izin_lainnya','pkl') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE pengajuan_izin SET `jenis` = 'izin_lainnya' WHERE `jenis` = 'pkl'");
        DB::statement("ALTER TABLE pengajuan_izin MODIFY COLUMN `jenis` ENUM('izin_sakit','izin_pulang_cepat','izin_terlambat','izin_lainnya') NOT NULL");
    }
};
