<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_panggilan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('gtk_id')->constrained('gtks')->cascadeOnDelete();
            $table->string('nomor_surat', 100)->nullable();
            $table->unsignedTinyInteger('panggilan_ke')->default(1)->comment('ke-1, ke-2, dst');
            $table->string('hari', 20);
            $table->date('tanggal_acara');
            $table->string('waktu', 40);
            $table->string('lokasi', 150);
            $table->string('menemui', 100)->nullable();
            $table->text('keperluan')->nullable();
            $table->boolean('dengan_materai')->default(true);
            $table->date('tanggal_surat');
            $table->string('tahun_ajaran', 10);
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_panggilan');
    }
};
