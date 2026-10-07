<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refactor tabel absen_event dari model "1 record per jenis (masuk/pulang)"
 * menjadi model "1 record per siswa per event" dengan kolom masuk & pulang tergabung.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absen_event', function (Blueprint $table) {
            // ── Kolom MASUK ───────────────────────────────────────────────
            $table->timestamp('waktu_masuk')->nullable()->after('siswa_id');
            $table->string('barcode_masuk')->nullable()->after('waktu_masuk');
            $table->decimal('latitude_masuk', 10, 7)->nullable()->after('barcode_masuk');
            $table->decimal('longitude_masuk', 10, 7)->nullable()->after('latitude_masuk');
            $table->string('foto_selfie_masuk')->nullable()->after('longitude_masuk');
            $table->string('lokasi_masuk')->nullable()->after('foto_selfie_masuk');

            // ── Kolom PULANG ──────────────────────────────────────────────
            $table->timestamp('waktu_pulang')->nullable()->after('lokasi_masuk');
            $table->string('barcode_pulang')->nullable()->after('waktu_pulang');
            $table->decimal('latitude_pulang', 10, 7)->nullable()->after('barcode_pulang');
            $table->decimal('longitude_pulang', 10, 7)->nullable()->after('latitude_pulang');
            $table->string('foto_selfie_pulang')->nullable()->after('longitude_pulang');
            $table->string('lokasi_pulang')->nullable()->after('foto_selfie_pulang');
        });

        // ── Unique constraint: 1 record per siswa per event ───────────────
        try {
            Schema::table('absen_event', function (Blueprint $table) {
                $table->unique(['event_id', 'siswa_id'], 'absen_event_event_siswa_unique');
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[Migration] Tidak dapat menambah unique constraint absen_event: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::table('absen_event', function (Blueprint $table) {
            try {
                $table->dropUnique('absen_event_event_siswa_unique');
            } catch (\Throwable) {}

            $table->dropColumn([
                'waktu_masuk', 'barcode_masuk',
                'latitude_masuk', 'longitude_masuk', 'foto_selfie_masuk', 'lokasi_masuk',
                'waktu_pulang', 'barcode_pulang',
                'latitude_pulang', 'longitude_pulang', 'foto_selfie_pulang', 'lokasi_pulang',
            ]);
        });
    }
};
