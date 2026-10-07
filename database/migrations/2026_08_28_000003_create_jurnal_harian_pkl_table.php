<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel jurnal_harian_pkl — catatan kegiatan harian siswa selama PKL.
 *
 * FK dideklarasikan tanpa CONSTRAINT karena database ini menggunakan
 * pola tanpa formal PRIMARY KEY declaration (kompatibilitas legacy).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_harian_pkl', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ── Relasi (tanpa FK constraint) ──────────────────────────────
            $table->unsignedBigInteger('penugasan_pkl_id');
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('lokasi_pkl_id');

            // ── Data Jurnal ───────────────────────────────────────────────
            $table->date('tanggal');
            $table->time('jam_datang')->nullable();
            $table->time('jam_pulang')->nullable();

            $table->text('kegiatan');
            $table->text('hasil')->nullable();
            $table->text('kendala')->nullable();
            $table->string('foto')->nullable();

            // ── Verifikasi Guru Pembimbing ────────────────────────────────
            $table->enum('status_verifikasi', ['diajukan', 'disetujui', 'revisi'])->default('diajukan');
            $table->text('catatan_pembimbing')->nullable();
            $table->unsignedBigInteger('diverifikasi_oleh')->nullable();
            $table->timestamp('waktu_verifikasi')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Unique: satu siswa satu jurnal per hari ───────────────────
            $table->unique(['siswa_id', 'tanggal'], 'jurnal_pkl_siswa_tgl_unique');

            // ── Indexes ───────────────────────────────────────────────────
            $table->index(['penugasan_pkl_id', 'tanggal'], 'jpkl_penugasan_tgl_idx');
            $table->index(['lokasi_pkl_id', 'tanggal'], 'jpkl_lokasi_tgl_idx');
            $table->index('status_verifikasi', 'jpkl_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_harian_pkl');
    }
};
