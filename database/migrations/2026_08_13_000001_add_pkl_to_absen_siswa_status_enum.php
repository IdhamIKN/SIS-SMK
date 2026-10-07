<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tambahkan nilai 'pkl' ke ENUM kolom status di tabel absen_siswa.
 *
 * Kolom status_masuk sudah bertipe string(20) sehingga tidak perlu diubah.
 * Kolom status (legacy) masih ENUM dan perlu diperluas agar tidak truncate.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE absen_siswa MODIFY COLUMN `status` ENUM('hadir','terlambat','sakit','izin','alfa','pkl') NOT NULL");
    }

    public function down(): void
    {
        // Hapus data 'pkl' sebelum rollback jika ada
        DB::statement("UPDATE absen_siswa SET `status` = 'izin' WHERE `status` = 'pkl'");
        DB::statement("ALTER TABLE absen_siswa MODIFY COLUMN `status` ENUM('hadir','terlambat','sakit','izin','alfa') NOT NULL");
    }
};
