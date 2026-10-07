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
        if (! Schema::hasTable('tbljurnalpembelajaran')) {
            Schema::create('tbljurnalpembelajaran', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('jadwal_kbm_id')->nullable()->index('tbljurnalpembelajaran_jadwal_kbm_id_index');
                $table->timestamp('time')->useCurrent()->useCurrentOnUpdate();
                $table->string('tipe', 50)->default('Luring');
                $table->date('tanggal')->nullable();
                $table->string('kdguru', 50)->nullable();
                $table->string('kelas', 100)->nullable();
                $table->string('pelajaran', 150)->nullable();
                $table->string('jamke', 15)->nullable();
                $table->time('jam_mulai')->nullable();
                $table->time('jam_selesai')->nullable();
                $table->text('deskripsi')->nullable();
                $table->string('guru', 150)->nullable();
                $table->unsignedInteger('siswahadir')->nullable();
                $table->unsignedInteger('siswatdkhadir')->nullable();
                $table->text('namasiswa')->nullable();
                $table->string('bukti', 255)->nullable();

                $table->unique(['kdguru', 'tanggal', 'kelas', 'pelajaran'], 'tbljurnalpembelajaran_unique_jurnal');
            });

            return;
        }

        Schema::table('tbljurnalpembelajaran', function (Blueprint $table) {
            if (! Schema::hasColumn('tbljurnalpembelajaran', 'jadwal_kbm_id')) {
                $table->unsignedBigInteger('jadwal_kbm_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('tbljurnalpembelajaran', 'jam_mulai')) {
                $table->time('jam_mulai')->nullable()->after('jamke');
            }

            if (! Schema::hasColumn('tbljurnalpembelajaran', 'jam_selesai')) {
                $table->time('jam_selesai')->nullable()->after('jam_mulai');
            }
        });

        if ($this->isMysql()) {
            $this->widenLegacyColumns();
            $this->ensureIndex();
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('tbljurnalpembelajaran')) {
            return;
        }

        if ($this->isMysql()) {
            if ($this->indexes()->has('tbljurnalpembelajaran_unique_jurnal')) {
                DB::statement('ALTER TABLE tbljurnalpembelajaran DROP INDEX `tbljurnalpembelajaran_unique_jurnal`');
            }

            if ($this->indexes()->has('tbljurnalpembelajaran_lookup_index')) {
                DB::statement('ALTER TABLE tbljurnalpembelajaran DROP INDEX `tbljurnalpembelajaran_lookup_index`');
            }

            if ($this->indexes()->has('tbljurnalpembelajaran_jadwal_kbm_id_index')) {
                DB::statement('ALTER TABLE tbljurnalpembelajaran DROP INDEX `tbljurnalpembelajaran_jadwal_kbm_id_index`');
            }
        }

        Schema::table('tbljurnalpembelajaran', function (Blueprint $table) {
            foreach (['jadwal_kbm_id', 'jam_mulai', 'jam_selesai'] as $column) {
                if (Schema::hasColumn('tbljurnalpembelajaran', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function widenLegacyColumns(): void
    {
        DB::statement('ALTER TABLE tbljurnalpembelajaran MODIFY `kelas` varchar(100) NULL');
        DB::statement('ALTER TABLE tbljurnalpembelajaran MODIFY `pelajaran` varchar(150) NULL');
        DB::statement('ALTER TABLE tbljurnalpembelajaran MODIFY `deskripsi` text NULL');
        DB::statement('ALTER TABLE tbljurnalpembelajaran MODIFY `guru` varchar(150) NULL');
        DB::statement('ALTER TABLE tbljurnalpembelajaran MODIFY `siswahadir` varchar(20) NULL');
        DB::statement('ALTER TABLE tbljurnalpembelajaran MODIFY `siswatdkhadir` varchar(20) NULL');
        DB::statement('ALTER TABLE tbljurnalpembelajaran MODIFY `namasiswa` text NULL');
        DB::statement('ALTER TABLE tbljurnalpembelajaran MODIFY `bukti` varchar(255) NULL');
    }

    private function ensureIndex(): void
    {
        if (! $this->indexes()->has('tbljurnalpembelajaran_jadwal_kbm_id_index') &&
            Schema::hasColumn('tbljurnalpembelajaran', 'jadwal_kbm_id')) {
            DB::statement('ALTER TABLE tbljurnalpembelajaran ADD INDEX `tbljurnalpembelajaran_jadwal_kbm_id_index` (`jadwal_kbm_id`)');
        }

        if ($this->indexes()->has('tbljurnalpembelajaran_unique_jurnal') ||
            $this->indexes()->has('tbljurnalpembelajaran_lookup_index')) {
            return;
        }

        if ($this->hasDuplicateJurnal()) {
            DB::statement('ALTER TABLE tbljurnalpembelajaran ADD INDEX `tbljurnalpembelajaran_lookup_index` (`kdguru`, `tanggal`, `kelas`, `pelajaran`)');

            return;
        }

        DB::statement('ALTER TABLE tbljurnalpembelajaran ADD UNIQUE `tbljurnalpembelajaran_unique_jurnal` (`kdguru`, `tanggal`, `kelas`, `pelajaran`)');
    }

    private function hasDuplicateJurnal(): bool
    {
        return DB::table('tbljurnalpembelajaran')
            ->select('kdguru', 'tanggal', 'kelas', 'pelajaran')
            ->whereNotNull('kdguru')
            ->whereNotNull('tanggal')
            ->whereNotNull('kelas')
            ->whereNotNull('pelajaran')
            ->groupBy('kdguru', 'tanggal', 'kelas', 'pelajaran')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }

    private function indexes(): Collection
    {
        return collect(DB::select('SHOW INDEX FROM tbljurnalpembelajaran'))
            ->groupBy(fn ($index) => $index->Key_name)
            ->map(fn (Collection $rows) => [
                'unique' => (int) $rows->first()->Non_unique === 0,
                'columns' => $rows->sortBy('Seq_in_index')->pluck('Column_name')->values(),
            ]);
    }

    private function isMysql(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
