<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Tambah UNIQUE constraint pada tbltransaksi.noreff agar tidak bisa
 * terjadi duplikat transaksi untuk sumber yang sama (PN/RW + tanggal + id).
 *
 * Sebelum menambahkan constraint, hapus dulu duplikat yang ada
 * (pertahankan idtrans terkecil per noreff).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Hapus duplikat noreff — pertahankan idtrans terkecil
        DB::statement('
            DELETE t1 FROM tbltransaksi t1
            INNER JOIN tbltransaksi t2
                ON  t1.noreff = t2.noreff
                AND t1.idtrans > t2.idtrans
        ');

        // 2. Drop index non-unique yang lama jika ada
        $indexes = collect(DB::select('SHOW INDEX FROM tbltransaksi WHERE Column_name = "noreff"'))
            ->pluck('Key_name')
            ->unique();

        foreach ($indexes as $indexName) {
            if ($indexName !== 'PRIMARY') {
                DB::statement("ALTER TABLE tbltransaksi DROP INDEX `{$indexName}`");
            }
        }

        // 3. Tambah UNIQUE index
        Schema::table('tbltransaksi', function (Blueprint $table) {
            $table->unique('noreff', 'tbltransaksi_noreff_unique');
        });
    }

    public function down(): void
    {
        Schema::table('tbltransaksi', function (Blueprint $table) {
            $table->dropUnique('tbltransaksi_noreff_unique');
            $table->index('noreff', 'tbltransaksi_noreff_index');
        });
    }
};
