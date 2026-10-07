<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah composite index (thajaran, tanggal) dan index deleted_at
 * pada tbltransaksi untuk mempercepat query jurnal laporan tatib.
 *
 * Sebelum perbaikan ini, query WHERE tanggal BETWEEN ... AND thajaran = ...
 * menghasilkan full table scan (type=ALL) karena kolom tanggal tidak memiliki
 * index. Dengan composite index ini, MySQL langsung menggunakan range scan
 * yang spesifik sehingga query dari ~34k baris menjadi hanya baris yang relevan.
 *
 * Index deleted_at mempercepat soft-delete filter (WHERE deleted_at IS NULL).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing(
            'tbltransaksi',
            'tbltransaksi_thajaran_tanggal_index',
            ['thajaran', 'tanggal']
        );

        $this->addIndexIfMissing(
            'tbltransaksi',
            'tbltransaksi_deleted_at_index',
            ['deleted_at']
        );
    }

    public function down(): void
    {
        Schema::table('tbltransaksi', function (Blueprint $table) {
            $existing = collect(DB::select('SHOW INDEX FROM tbltransaksi'))
                ->pluck('Key_name')
                ->unique()
                ->toArray();

            if (in_array('tbltransaksi_thajaran_tanggal_index', $existing)) {
                $table->dropIndex('tbltransaksi_thajaran_tanggal_index');
            }
            if (in_array('tbltransaksi_deleted_at_index', $existing)) {
                $table->dropIndex('tbltransaksi_deleted_at_index');
            }
        });
    }

    private function addIndexIfMissing(string $table, string $indexName, array $columns): void
    {
        $existing = collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->unique()
            ->toArray();

        if (in_array($indexName, $existing)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->index($columns, $indexName);
        });
    }
};
