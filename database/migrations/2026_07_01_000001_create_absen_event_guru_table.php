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
        Schema::create('absen_event_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('gtk_id')->constrained('gtks')->onDelete('cascade');
            $table->enum('jenis', ['masuk', 'pulang']);
            $table->timestamp('waktu_scan')->nullable();
            $table->string('barcode_digunakan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['event_id', 'gtk_id', 'jenis']);
            $table->index('waktu_scan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absen_event_guru');
    }
};
