<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auto_pelanggaran_rules', function (Blueprint $table) {
            $table->id();

            $table->string('nama_rule', 100)->comment('Label deskriptif rule ini');
            $table->boolean('aktif')->default(true)->comment('Apakah rule ini dijalankan?');

            // ── Trigger / Case ────────────────────────────────────────────────
            $table->string('trigger_type', 30)
                ->comment('Jenis pemicu: alfa_harian | alfa_bulanan | alfa_mingguan | terlambat_berulang | custom');
            // alfa_harian     = setiap siswa alfa pada hari itu
            // alfa_bulanan    = siswa yg melebihi N alfa dalam 1 bulan
            // terlambat_berulang = siswa yg terlambat ≥ N kali dalam periode
            // custom          = admin tentukan sendiri via keterangan

            // ── Parameter trigger ─────────────────────────────────────────────
            $table->unsignedTinyInteger('threshold_hari')->nullable()
                ->comment('Batas jumlah hari/kejadian yang memicu rule (contoh: ≥3 alfa/bulan)');
            $table->unsignedTinyInteger('periode_bulan')->nullable()
                ->comment('Jangka periode evaluasi dalam bulan (1 = bulanan, 0 = harian)');

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
        Schema::dropIfExists('auto_pelanggaran_rules');
    }
};
