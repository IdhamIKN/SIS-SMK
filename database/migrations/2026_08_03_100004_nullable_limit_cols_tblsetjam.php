<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ubah kolom limit_in dan limit_out di tblsetjam menjadi nullable.
 * Sebelumnya NOT NULL tanpa default — tidak praktis karena banyak jam
 * yang tidak membutuhkan batas toleransi.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE tblsetjam MODIFY limit_in TIME NULL DEFAULT NULL');
        DB::statement('ALTER TABLE tblsetjam MODIFY limit_out TIME NULL DEFAULT NULL');
    }

    public function down(): void
    {
        // Set semua null ke '00:00:00' dulu sebelum ubah ke NOT NULL
        DB::table('tblsetjam')->whereNull('limit_in')->update(['limit_in' => '00:00:00']);
        DB::table('tblsetjam')->whereNull('limit_out')->update(['limit_out' => '00:00:00']);
        DB::statement('ALTER TABLE tblsetjam MODIFY limit_in TIME NOT NULL');
        DB::statement('ALTER TABLE tblsetjam MODIFY limit_out TIME NOT NULL');
    }
};
