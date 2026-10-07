<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendPelanggaranTable();
        $this->normalizePenghargaanApprovalDate();
        $this->extendPenghargaanTable();
        $this->extendTransaksiTable();
        $this->createWaLogsTable();
        $this->createTatibPointNotificationsTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('tatib_point_notifications');
        Schema::dropIfExists('wa_logs');

        $this->dropColumnsIfExist('tbltransaksi', ['siswa_id', 'created_by']);
        $this->dropColumnsIfExist('tblpenghargaan', ['siswa_id', 'tahun_ajaran', 'created_by']);
        $this->dropColumnsIfExist('tblpelanggaran', ['siswa_id', 'tahun_ajaran', 'idpasal', 'created_by']);
    }

    private function extendPelanggaranTable(): void
    {
        if (! Schema::hasTable('tblpelanggaran')) {
            return;
        }

        Schema::table('tblpelanggaran', function (Blueprint $table) {
            if (! Schema::hasColumn('tblpelanggaran', 'siswa_id')) {
                $table->unsignedBigInteger('siswa_id')->nullable()->after('idpel');
            }
            if (! Schema::hasColumn('tblpelanggaran', 'tahun_ajaran')) {
                $table->string('tahun_ajaran', 9)->nullable()->after('tgl');
            }
            if (! Schema::hasColumn('tblpelanggaran', 'idpasal')) {
                $table->string('idpasal', 5)->nullable()->after('kelas');
            }
            if (! Schema::hasColumn('tblpelanggaran', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('pelapor');
            }
        });

        $this->addIndexIfMissing('tblpelanggaran', 'tblpelanggaran_siswa_tahun_index', ['siswa_id', 'tahun_ajaran']);
        $this->addIndexIfMissing('tblpelanggaran', 'tblpelanggaran_created_by_index', ['created_by']);
    }

    private function extendPenghargaanTable(): void
    {
        if (! Schema::hasTable('tblpenghargaan')) {
            return;
        }

        Schema::table('tblpenghargaan', function (Blueprint $table) {
            if (! Schema::hasColumn('tblpenghargaan', 'siswa_id')) {
                $table->unsignedBigInteger('siswa_id')->nullable()->after('idpen');
            }
            if (! Schema::hasColumn('tblpenghargaan', 'tahun_ajaran')) {
                $table->string('tahun_ajaran', 9)->nullable()->after('tgl');
            }
            if (! Schema::hasColumn('tblpenghargaan', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('acc');
            }
        });

        $this->addIndexIfMissing('tblpenghargaan', 'tblpenghargaan_siswa_tahun_index', ['siswa_id', 'tahun_ajaran']);
        $this->addIndexIfMissing('tblpenghargaan', 'tblpenghargaan_created_by_index', ['created_by']);
    }

    private function normalizePenghargaanApprovalDate(): void
    {
        if (! Schema::hasTable('tblpenghargaan') || ! Schema::hasColumn('tblpenghargaan', 'tglacc')) {
            return;
        }

        $mode = DB::selectOne('SELECT @@SESSION.sql_mode AS mode')?->mode ?? '';

        try {
            DB::statement("SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', ''), 'NO_ZERO_IN_DATE', ''), 'STRICT_TRANS_TABLES', '')");
            DB::statement('ALTER TABLE `tblpenghargaan` MODIFY `tglacc` DATETIME NULL DEFAULT NULL');
            DB::statement("UPDATE `tblpenghargaan` SET `tglacc` = NULL WHERE `tglacc` = '0000-00-00 00:00:00'");
        } finally {
            DB::statement("SET SESSION sql_mode = '".str_replace("'", "''", $mode)."'");
        }
    }

    private function extendTransaksiTable(): void
    {
        if (! Schema::hasTable('tbltransaksi')) {
            return;
        }

        Schema::table('tbltransaksi', function (Blueprint $table) {
            if (! Schema::hasColumn('tbltransaksi', 'siswa_id')) {
                $table->unsignedBigInteger('siswa_id')->nullable()->after('idtrans');
            }
            if (! Schema::hasColumn('tbltransaksi', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('userx');
            }
        });

        $this->addIndexIfMissing('tbltransaksi', 'tbltransaksi_siswa_tahun_index', ['siswa_id', 'thajaran']);
        $this->addIndexIfMissing('tbltransaksi', 'tbltransaksi_noreff_index', ['noreff']);
        $this->addIndexIfMissing('tbltransaksi', 'tbltransaksi_created_by_index', ['created_by']);
    }

    private function createWaLogsTable(): void
    {
        if (Schema::hasTable('wa_logs')) {
            return;
        }

        Schema::create('wa_logs', function (Blueprint $table) {
            $table->id();
            $table->string('no_tujuan', 30);
            $table->text('pesan');
            $table->string('jenis', 50)->default('umum');
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->string('referensi_tipe', 50)->nullable();
            $table->string('wa_mode', 30)->nullable();
            $table->timestamp('dikirim_at')->nullable();
            $table->timestamps();

            $table->index(['jenis', 'referensi_id']);
            $table->index('status');
        });
    }

    private function createTatibPointNotificationsTable(): void
    {
        if (Schema::hasTable('tatib_point_notifications')) {
            return;
        }

        Schema::create('tatib_point_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->string('tahun_ajaran', 9);
            $table->unsignedTinyInteger('batas_ke');
            $table->integer('batas_poin');
            $table->integer('total_poin')->default(0);
            $table->string('tindakan', 100)->nullable();
            $table->string('sanksi', 150)->nullable();
            $table->string('nomor_tujuan', 30)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'tahun_ajaran', 'batas_ke'], 'tatib_notif_unique_threshold');
            $table->index(['tahun_ajaran', 'status']);
        });
    }

    private function dropColumnsIfExist(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $existing = array_values(array_filter($columns, fn (string $column) => Schema::hasColumn($table, $column)));

        if ($existing === []) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($existing) {
            $table->dropColumn($existing);
        });
    }

    private function addIndexIfMissing(string $table, string $indexName, array $columns): void
    {
        if ($this->hasIndex($table, $indexName)) {
            return;
        }

        $columnList = implode('`, `', $columns);
        DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$columnList}`)");
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->contains(fn ($index) => $index->Key_name === $indexName);
    }
};
