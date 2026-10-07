<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auto_penghargaan_rules', function (Blueprint $table) {
            $table->id();

            $table->string('nama_rule', 100)->comment('Label deskriptif rule ini');
            $table->boolean('aktif')->default(true)->comment('Apakah rule ini dijalankan?');

            // ── Trigger / Case ────────────────────────────────────────────────
            $table->string('trigger_type', 30)
                ->comment('Pemicu: full_hadir_bulanan | full_hadir_mingguan | streak_hadir | custom');
            // full_hadir_bulanan  = tidak ada alfa selama 1 bulan penuh (sesuai konfigurasi)
            // full_hadir_mingguan = tidak ada alfa selama 1 minggu penuh
            // streak_hadir        = hadir berturut-turut ≥ N hari

            // ── Parameter trigger ─────────────────────────────────────────────
            $table->unsignedTinyInteger('periode_bulan')->default(1)
                ->comment('Berapa bulan periode evaluasi (1 = bulanan, dll)');

            // Status kehadiran yang "dianggap tidak melanggar" (tetap lolos ke penghargaan)
            $table->boolean('izin_dihitung_hadir')->default(true)
                ->comment('Izin dianggap hadir (tidak membatalkan penghargaan)');
            $table->boolean('sakit_dihitung_hadir')->default(true)
                ->comment('Sakit dianggap hadir (tidak membatalkan penghargaan)');
            $table->boolean('terlambat_dihitung_hadir')->default(false)
                ->comment('Terlambat dianggap hadir (tidak membatalkan penghargaan)');

            // ── Pasal & Poin ──────────────────────────────────────────────────
            $table->string('pasal_id', 20)->comment('FK ke tblsubpasal.idpasal');
            $table->unsignedSmallInteger('poin_override')->nullable()
                ->comment('Override poin; null = pakai poin_default dari pasal');

            // ── Meta ──────────────────────────────────────────────────────────
            $table->text('keterangan')->nullable()
                ->comment('Catatan admin tentang rule ini');
            $table->integer('urutan')->default(0)
                ->comment('Urutan tampil di UI');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_penghargaan_rules');
    }
};
