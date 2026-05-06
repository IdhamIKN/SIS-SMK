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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable(); // FK → users
            $table->string('nama_event');
            $table->text('deskripsi')->nullable();
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai');
            $table->string('lokasi')->nullable();
            $table->boolean('ada_absen_masuk')->default(true);
            $table->boolean('ada_absen_pulang')->default(true);
            $table->boolean('berlaku_untuk_semua')->default(true);
            $table->integer('barcode_rotate_detik')->default(0); // 0 = statis
            $table->string('barcode_value');
            $table->timestamp('barcode_updated_at')->nullable();
            $table->unsignedBigInteger('idevent_legacy')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['tanggal_selesai', 'barcode_rotate_detik']);
            $table->index('berlaku_untuk_semua');
            $table->index('barcode_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
