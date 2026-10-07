<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('laporan_kehadiran_guru') ||
            $this->hasIndex(['jadwal_kbm_id', 'tanggal'], true)) {
            return;
        }

        $duplicate = DB::table('laporan_kehadiran_guru')
            ->select('jadwal_kbm_id', 'tanggal')
            ->selectRaw('COUNT(*) as jumlah')
            ->groupBy('jadwal_kbm_id', 'tanggal')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate) {
            throw new \RuntimeException(
                'Tidak dapat menambahkan unique constraint laporan kehadiran guru karena masih ada laporan ganda.'
            );
        }

        DB::statement('ALTER TABLE laporan_kehadiran_guru ADD UNIQUE `laporan_kehadiran_guru_jadwal_kbm_id_tanggal_unique` (`jadwal_kbm_id`, `tanggal`)');

        if ($this->indexes()->has('laporan_kehadiran_guru_jadwal_kbm_id_tanggal_index')) {
            DB::statement('ALTER TABLE laporan_kehadiran_guru DROP INDEX `laporan_kehadiran_guru_jadwal_kbm_id_tanggal_index`');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('laporan_kehadiran_guru') ||
            ! $this->indexes()->has('laporan_kehadiran_guru_jadwal_kbm_id_tanggal_unique')) {
            return;
        }

        DB::statement('ALTER TABLE laporan_kehadiran_guru DROP INDEX `laporan_kehadiran_guru_jadwal_kbm_id_tanggal_unique`');
        DB::statement('ALTER TABLE laporan_kehadiran_guru ADD INDEX `laporan_kehadiran_guru_jadwal_kbm_id_tanggal_index` (`jadwal_kbm_id`, `tanggal`)');
    }

    private function hasIndex(array $columns, ?bool $unique = null): bool
    {
        return $this->indexes()->contains(function (array $index) use ($columns, $unique) {
            return $index['columns']->all() === $columns &&
                ($unique === null || $index['unique'] === $unique);
        });
    }

    private function indexes(): Collection
    {
        return collect(DB::select('SHOW INDEX FROM laporan_kehadiran_guru'))
            ->groupBy(fn ($index) => $index->Key_name)
            ->map(fn (Collection $rows) => [
                'unique' => (int) $rows->first()->Non_unique === 0,
                'columns' => $rows->sortBy('Seq_in_index')->pluck('Column_name')->values(),
            ]);
    }
};
