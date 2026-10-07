<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel penugasan_pkl — relasi M:M antara siswa dan lokasi PKL.
 *
 * FK dideklarasikan tanpa CONSTRAINT karena database ini menggunakan
 * pola tanpa formal PRIMARY KEY declaration (kompatibilitas legacy).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penugasan_pkl', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ── Relasi Inti (tanpa FK constraint) ─────────────────────────
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('lokasi_pkl_id');
            $table->unsignedBigInteger('gtk_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();

            // ── Periode PKL ───────────────────────────────────────────────
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            // ── Status ────────────────────────────────────────────────────
            $table->enum('status', ['aktif', 'selesai', 'batal'])->default('aktif');

            // ── Sync Izin ─────────────────────────────────────────────────
            $table->unsignedBigInteger('pengajuan_izin_id')->nullable();

            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ───────────────────────────────────────────────────
            $table->index(['lokasi_pkl_id', 'status'], 'ppkl_lokasi_status_idx');
            $table->index(['gtk_id', 'academic_year_id'], 'ppkl_gtk_ay_idx');
            $table->index(['siswa_id', 'status'], 'ppkl_siswa_status_idx');
            $table->index(['tanggal_mulai', 'tanggal_selesai'], 'ppkl_periode_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penugasan_pkl');
    }
};
