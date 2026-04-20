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
        Schema::create('absen_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas');
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->date('tanggal');
            $table->enum('jenis', ['masuk', 'pulang']);
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alfa']);
            $table->timestamp('waktu_absen')->nullable();
            $table->string('foto_selfie')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('jarak_meter')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->text('catatan')->nullable();
            $table->boolean('wa_terkirim_ortu')->default(false);

            // Mapping legacy reference
            $table->integer('idabsensi_legacy')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absen_siswa');
    }
};
