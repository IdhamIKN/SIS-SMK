<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->onDelete('set null');

            // Data Pribadi
            $table->string('nis')->nullable()->unique();
            $table->string('nisn')->nullable()->unique();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_pos', 10)->nullable();

            // Kontak
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();

            // Orang Tua
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            $table->string('no_hp_ortu')->nullable();

            // Data Sekolah
            $table->string('asal_sekolah')->nullable();
            $table->string('no_ijazah')->nullable();
            $table->string('no_skhun')->nullable();
            $table->string('tahun_masuk')->nullable();
            $table->string('tahun_lulus')->nullable();

            // Status
            $table->enum('status', ['aktif', 'tidak_aktif', 'lulus', 'pindah', 'meninggal'])->default('aktif');

            // Legacy fields
            $table->string('noreg_legacy')->nullable();
            $table->string('foto')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['nis']);
            $table->index(['nisn']);
            $table->index(['kelas_id']);
            $table->index(['status']);
            $table->index(['nama_lengkap']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};