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
            $table->foreignId('gtk_id')->constrained('gtks')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('jenis', ['masuk', 'pulang']);
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alfa'])->default('hadir');
            $table->datetime('waktu_laporan');
            $table->text('catatan')->nullable();
            $table->string('foto_selfie')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('jarak_meter')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('wa_terkirim')->default(false);
            $table->unsignedBigInteger('idabsensi_guru_legacy')->nullable();
            $table->timestamps();

            $table->index(['gtk_id', 'tanggal', 'jenis']);
            $table->index(['tanggal', 'status']);
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
