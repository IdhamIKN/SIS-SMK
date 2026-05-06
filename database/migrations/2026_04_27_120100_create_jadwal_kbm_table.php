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
        Schema::create('jadwal_kbm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('gtk_id')->constrained('gtks')->onDelete('cascade');
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            $table->tinyInteger('jam_ke');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('mata_pelajaran');
            $table->string('tahun_ajaran')->nullable();
            $table->enum('semester', ['1', '2'])->default('1');
            $table->timestamps();

            $table->index(['kelas_id', 'hari', 'jam_ke']);
            $table->index(['gtk_id', 'hari']);
            $table->index(['hari', 'jam_ke']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kbm');
    }
};
