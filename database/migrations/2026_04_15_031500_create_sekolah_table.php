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
        Schema::create('sekolah', function (Blueprint $table) {
            $table->id();

            // Identitas Sistem
            $table->string('system_name')->nullable();

            // Identitas Sekolah
            $table->string('sekolah')->nullable();
            $table->text('alsekolah')->nullable();
            $table->string('telp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('kab', 100)->nullable();
            $table->string('alias', 50)->nullable();

            // Kepala Sekolah
            $table->string('nama_ks')->nullable();
            $table->string('nip_ks', 50)->nullable();

            // Wakil Kepala Sekolah
            $table->string('nama_waka')->nullable();
            $table->string('nip_waka', 50)->nullable();

            // Ketua
            $table->string('nama_ketua')->nullable();
            $table->string('nip_ketua', 50)->nullable();

            // Website & Media
            $table->string('site_url')->nullable();
            $table->string('site_logo')->nullable();
            $table->string('wasekolah', 20)->nullable();

            // Jam Sekolah
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->json('hari_efektif')->nullable();

            // Lokasi Presensi
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedInteger('radius_meter')->default(100);

            $table->timestamps();

            // Indexes
            $table->index(['sekolah']);
            $table->index(['alias']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sekolah');
    }
};