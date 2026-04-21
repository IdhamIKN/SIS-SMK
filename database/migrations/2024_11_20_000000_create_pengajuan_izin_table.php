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
        Schema::create('pengajuan_izin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->enum('jenis', ['izin_sakit', 'izin_pulang_cepat', 'izin_terlambat', 'izin_lainnya']);
            $table->date('tanggal_izin');
            $table->text('alasan')->nullable();
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('waktu_verifikasi')->nullable();
            $table->timestamps();
            
            $table->index(['siswa_id', 'tanggal_izin']);
            $table->index(['status', 'tanggal_izin']);
            $table->index('diverifikasi_oleh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_izin');
    }
};

