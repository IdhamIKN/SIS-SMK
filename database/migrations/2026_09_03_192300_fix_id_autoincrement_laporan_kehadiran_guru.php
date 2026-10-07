<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pastikan kolom id pada laporan_kehadiran_guru adalah PRIMARY KEY AUTO_INCREMENT.
     * Diperlukan jika tabel kehilangan PRIMARY KEY atau AUTO_INCREMENT pada kolom id.
     */
    public function up(): void
    {
        $col = DB::select("
            SELECT COLUMN_KEY, EXTRA
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'laporan_kehadiran_guru'
              AND COLUMN_NAME  = 'id'
        ");

        if (empty($col)) {
            return;
        }

        $isPrimary       = strtoupper($col[0]->COLUMN_KEY) === 'PRI';
        $isAutoIncrement = str_contains(strtolower($col[0]->EXTRA), 'auto_increment');

        if ($isPrimary && $isAutoIncrement) {
            return; // sudah benar
        }

        $max = DB::table('laporan_kehadiran_guru')->max('id') ?? 0;

        // Jika belum PK, tambahkan dulu
        if (!$isPrimary) {
            // Drop PK yang mungkin ada di kolom lain dulu (berjaga-jaga)
            $hasPk = DB::select("
                SELECT COUNT(*) as cnt
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA    = DATABASE()
                  AND TABLE_NAME      = 'laporan_kehadiran_guru'
                  AND CONSTRAINT_TYPE = 'PRIMARY KEY'
            ");

            if ($hasPk[0]->cnt > 0) {
                DB::statement('ALTER TABLE `laporan_kehadiran_guru` DROP PRIMARY KEY');
            }

            DB::statement('ALTER TABLE `laporan_kehadiran_guru` MODIFY `id` BIGINT UNSIGNED NOT NULL, ADD PRIMARY KEY (`id`)');
        }

        // Sekarang tambahkan AUTO_INCREMENT
        DB::statement('ALTER TABLE `laporan_kehadiran_guru` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE `laporan_kehadiran_guru` AUTO_INCREMENT = ' . ($max + 1));
    }

    public function down(): void
    {
        // Tidak di-revert
    }
};
