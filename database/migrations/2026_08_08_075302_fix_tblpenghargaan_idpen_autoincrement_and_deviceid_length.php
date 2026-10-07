<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix dua bug yang ditemukan dari test command sis:test-feature:
 *
 * 1. tblpenghargaan.idpen — tidak ada AUTO_INCREMENT sehingga insert tanpa
 *    explicit id gagal dengan "Field 'idpen' doesn't have a default value".
 *
 * 2. tblpelanggaran.deviceid VARCHAR(30) — terlalu pendek untuk string seperti
 *    "auto-event-penghargaan-XXXX". Diperbesar ke VARCHAR(80) agar aman
 *    untuk semua format deviceid yang digunakan sistem (auto-alfa, auto-event, dll).
 *    tblpenghargaan.deviceid juga ikut diperbesar.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Fix 1: tambah AUTO_INCREMENT ke tblpenghargaan.idpen
        DB::statement('ALTER TABLE tblpenghargaan MODIFY COLUMN idpen INT(10) NOT NULL AUTO_INCREMENT');

        // Fix 2: perbesar kolom deviceid di kedua tabel
        DB::statement('ALTER TABLE tblpelanggaran MODIFY COLUMN deviceid VARCHAR(80)');
        DB::statement('ALTER TABLE tblpenghargaan MODIFY COLUMN deviceid VARCHAR(80)');
    }

    public function down(): void
    {
        // Kembalikan deviceid ke ukuran semula
        DB::statement('ALTER TABLE tblpelanggaran MODIFY COLUMN deviceid VARCHAR(30)');
        DB::statement('ALTER TABLE tblpenghargaan MODIFY COLUMN deviceid VARCHAR(30)');

        // Tidak revert AUTO_INCREMENT karena bisa menyebabkan data corrupt
    }
};
