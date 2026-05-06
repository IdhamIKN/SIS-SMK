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
        Schema::create('laporan_kehadiran_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_kbm_id')->constrained('jadwal_kbm')->onDelete('cascade');
            $table->foreignId('gtk_id')->constrained('gtks')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->date('tanggal');
            $table->tinyInteger('jam_ke');
            $table->enum('status', ['hijau', 'kuning', 'merah', 'abu', 'biru', 'pink', 'orange', 'putih'])->default('putih');
            $table->foreignId('dilaporkan_oleh_siswa_id')->nullable()->constrained('siswas')->onDelete('set null');
            $table->datetime('waktu_laporan');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['jadwal_kbm_id', 'tanggal']);
            $table->index(['gtk_id', 'tanggal']);
            $table->index(['kelas_id', 'tanggal']);
            $table->index(['tanggal', 'status']);
            $table->index(['dilaporkan_oleh_siswa_id']);
        });

        // Create siswa_petugas_laporan table
        Schema::create('siswa_petugas_laporan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->date('tanggal');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['kelas_id', 'tanggal']);
            $table->index(['siswa_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_kehadiran_guru');
    }
};
