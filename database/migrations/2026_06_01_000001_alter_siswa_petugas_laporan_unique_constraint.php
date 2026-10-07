<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('siswa_petugas_laporan')) {
            return;
        }

        $this->removeDuplicateReporters();

        if (! $this->hasIndex(['kelas_id', 'siswa_id'], true)) {
            DB::statement('ALTER TABLE siswa_petugas_laporan ADD UNIQUE `siswa_petugas_laporan_kelas_id_siswa_id_unique` (`kelas_id`, `siswa_id`)');
        }

        if (! $this->hasIndex(['siswa_id'])) {
            DB::statement('ALTER TABLE siswa_petugas_laporan ADD INDEX `siswa_petugas_laporan_siswa_id_index` (`siswa_id`)');
        }

        if (Schema::hasColumn('siswa_petugas_laporan', 'tanggal')) {
            foreach ($this->indexes() as $name => $index) {
                if ($index['columns']->contains('tanggal')) {
                    DB::statement("ALTER TABLE siswa_petugas_laporan DROP INDEX `{$name}`");
                }
            }

            Schema::table('siswa_petugas_laporan', function (Blueprint $table) {
                $table->dropColumn('tanggal');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('siswa_petugas_laporan') ||
            Schema::hasColumn('siswa_petugas_laporan', 'tanggal')) {
            return;
        }

        Schema::table('siswa_petugas_laporan', function (Blueprint $table) {
            $table->date('tanggal')->nullable()->after('siswa_id');
        });

        DB::table('siswa_petugas_laporan')->update([
            'tanggal' => now()->toDateString(),
        ]);

        DB::statement('ALTER TABLE siswa_petugas_laporan MODIFY `tanggal` DATE NOT NULL');
        DB::statement('ALTER TABLE siswa_petugas_laporan DROP INDEX `siswa_petugas_laporan_kelas_id_siswa_id_unique`');
        DB::statement('ALTER TABLE siswa_petugas_laporan ADD UNIQUE `siswa_petugas_laporan_kelas_siswa_tanggal_unique` (`kelas_id`, `siswa_id`, `tanggal`)');
    }

    private function removeDuplicateReporters(): void
    {
        $duplicates = DB::table('siswa_petugas_laporan')
            ->select('kelas_id', 'siswa_id')
            ->selectRaw('MAX(id) as keep_id')
            ->groupBy('kelas_id', 'siswa_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('siswa_petugas_laporan')
                ->where('kelas_id', $duplicate->kelas_id)
                ->where('siswa_id', $duplicate->siswa_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }
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
        return collect(DB::select('SHOW INDEX FROM siswa_petugas_laporan'))
            ->groupBy(fn ($index) => $index->Key_name)
            ->map(fn (Collection $rows) => [
                'unique' => (int) $rows->first()->Non_unique === 0,
                'columns' => $rows->sortBy('Seq_in_index')->pluck('Column_name')->values(),
            ]);
    }
};
