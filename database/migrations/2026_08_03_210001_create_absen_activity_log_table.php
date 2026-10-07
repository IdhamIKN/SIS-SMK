<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit log untuk setiap aksi admin terhadap data absensi:
 *   - tambah_manual, edit_manual, buat_izin, setujui_izin, tolak_izin
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absen_activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('aksi', 50);                          // tambah_manual|edit_manual|buat_izin|...
            $table->foreignId('absen_siswa_id')->nullable()->constrained('absen_siswa')->onDelete('set null');
            $table->foreignId('izin_id')->nullable()->references('id')->on('pengajuan_izin')->onDelete('set null');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('dilakukan_oleh')->constrained('users')->onDelete('cascade');
            $table->json('data_lama')->nullable();                // snapshot sebelum perubahan
            $table->json('data_baru')->nullable();                // snapshot sesudah perubahan
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'created_at']);
            $table->index(['absen_siswa_id']);
            $table->index('aksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absen_activity_log');
    }
};
