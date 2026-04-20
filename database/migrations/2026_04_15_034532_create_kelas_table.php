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
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_kelas_legacy')->nullable();
            $table->string('nama_kelas');
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->foreignId('jurusan_id')->constrained('jurusan');
            $table->foreignId('wali_kelas_id')->nullable()->constrained('gtks')->onDelete('set null');
            $table->foreignId('bk_id')->nullable()->constrained('gtks')->onDelete('set null');
            $table->enum('shift', ['Pagi', 'Siang']);
            $table->string('wa_group')->nullable();
            $table->string('tahun_ajaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
