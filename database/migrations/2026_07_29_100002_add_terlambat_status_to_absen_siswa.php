<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambahkan status 'terlambat' ke enum kolom status di tabel absen_siswa.
     * Menggunakan ALTER TABLE langsung karena Laravel tidak mendukung modifikasi ENUM secara native.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE absen_siswa MODIFY COLUMN status ENUM('hadir','terlambat','sakit','izin','alfa') NOT NULL");
    }

    public function down(): void
    {
        // Kembalikan ke enum semula; data 'terlambat' harus sudah tidak ada
        DB::statement("ALTER TABLE absen_siswa MODIFY COLUMN status ENUM('hadir','sakit','izin','alfa') NOT NULL");
    }
};
