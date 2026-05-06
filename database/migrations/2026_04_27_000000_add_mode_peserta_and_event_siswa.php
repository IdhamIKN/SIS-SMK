<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom mode_peserta ke tabel events
        Schema::table('events', function (Blueprint $table) {
            $table->enum('mode_peserta', ['kelas', 'siswa'])->default('kelas')->after('berlaku_untuk_semua');
        });

        // Buat tabel pivot event_siswa
        Schema::create('event_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_siswa');

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('mode_peserta');
        });
    }
};
