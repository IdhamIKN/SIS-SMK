<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel lokasi_pkl — master data tempat PKL (Praktik Kerja Lapangan).
 *
 * FK dideklarasikan tanpa CONSTRAINT karena database ini menggunakan
 * pola tanpa formal PRIMARY KEY declaration (kompatibilitas legacy).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lokasi_pkl', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ── Identitas Tempat ──────────────────────────────────────────
            $table->string('nama_tempat');
            $table->string('jenis_usaha')->nullable();

            // ── Alamat Lengkap ────────────────────────────────────────────
            $table->text('alamat');
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_pos', 10)->nullable();

            // ── Koordinat GPS ─────────────────────────────────────────────
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('radius_meter')->default(200);

            // ── Penanggung Jawab / Pembimbing Industri ────────────────────
            $table->string('nama_pj')->nullable();
            $table->string('jabatan_pj')->nullable();
            $table->string('no_hp_pj')->nullable();
            $table->string('email_pj')->nullable();
            $table->string('no_telp_kantor')->nullable();
            $table->string('website')->nullable();

            // ── Kapasitas & Foto ──────────────────────────────────────────
            $table->integer('kapasitas')->nullable();
            $table->string('foto')->nullable();

            // ── Jam Absen PKL ─────────────────────────────────────────────
            $table->time('jam_masuk_pkl')->nullable();
            $table->time('jam_pulang_pkl')->nullable();
            $table->time('batas_terlambat_pkl')->nullable();
            $table->time('batas_absen_masuk_pkl')->nullable();

            // ── Konfigurasi Auto Poin PKL ─────────────────────────────────
            $table->boolean('auto_poin_hadir_pkl')->default(false);
            $table->string('pasal_hadir_pkl_id')->nullable();

            $table->boolean('auto_poin_terlambat_pkl')->default(false);
            $table->string('pasal_terlambat_pkl_id')->nullable();

            $table->boolean('auto_poin_alfa_pkl')->default(false);
            $table->string('pasal_alfa_pkl_id')->nullable();

            // ── Status & Relasi (tanpa FK constraint) ─────────────────────
            $table->boolean('status_aktif')->default(true);
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ───────────────────────────────────────────────────
            $table->index(['status_aktif', 'academic_year_id'], 'lpkl_aktif_ay_idx');
            $table->index('nama_tempat', 'lpkl_nama_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lokasi_pkl');
    }
};
