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
        Schema::create('absen_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->enum('jenis', ['masuk', 'pulang']);
            $table->timestamp('waktu_scan')->nullable();
            $table->string('barcode_digunakan')->nullable();
            $table->boolean('wa_terkirim_ortu')->default(false);
            $table->unsignedBigInteger('created_by')->nullable(); // admin/manual
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['event_id', 'siswa_id', 'jenis']);
            $table->index('waktu_scan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absen_event');
    }
};
