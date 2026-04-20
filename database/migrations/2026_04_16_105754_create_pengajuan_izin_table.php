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
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('jenis_izin', ['sakit', 'izin', 'alfa', 'kegiatan', 'lainnya']);
            $table->text('alasan');
            $table->text('keterangan')->nullable();
            $table->string('lampiran')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('tanggal_disetujui')->nullable();
            $table->text('catatan_penyetuju')->nullable();
            $table->boolean('wa_terkirim')->default(false);
            $table->timestamps();

            $table->index(['siswa_id', 'status']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
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
