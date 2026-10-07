<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_gurus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('nama_event');
            $table->text('deskripsi')->nullable();
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai');
            $table->string('lokasi')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->unsignedInteger('radius_meter')->default(100);
            $table->boolean('ada_absen_masuk')->default(true);
            $table->boolean('ada_absen_pulang')->default(true);
            $table->integer('barcode_rotate_detik')->default(0);
            $table->string('barcode_value');
            $table->timestamp('barcode_updated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index('tanggal_mulai');
            $table->index('tanggal_selesai');
            $table->index('barcode_value');
        });

        Schema::create('absen_event_gurus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_guru_id')->constrained('event_gurus')->onDelete('cascade');
            $table->foreignId('gtk_id')->constrained('gtks')->onDelete('cascade');
            $table->enum('jenis', ['masuk', 'pulang']);
            $table->timestamp('waktu_scan')->nullable();
            $table->string('barcode_digunakan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['event_guru_id', 'gtk_id', 'jenis']);
            $table->index('waktu_scan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absen_event_gurus');
        Schema::dropIfExists('event_gurus');
    }
};
