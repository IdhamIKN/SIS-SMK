<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Refactor tabel absen_siswa dari model "1 record per jenis (masuk/pulang)"
 * menjadi model "1 record per siswa per hari" dengan kolom masuk & pulang tergabung.
 *
 * Kolom lama dipertahankan sementara selama transisi (jenis, status, waktu_absen, dst.)
 * Kolom baru ditambahkan: jam_masuk, jam_pulang, status_masuk, status_pulang, dll.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absen_siswa', function (Blueprint $table) {
            // ── Kolom MASUK ───────────────────────────────────────────────
            $table->time('jam_masuk')->nullable()->after('kelas_id');
            $table->string('status_masuk', 20)->nullable()->after('jam_masuk');  // hadir|terlambat|sakit|izin|alfa
            $table->decimal('latitude_masuk', 10, 7)->nullable()->after('status_masuk');
            $table->decimal('longitude_masuk', 10, 7)->nullable()->after('latitude_masuk');
            $table->integer('jarak_masuk')->nullable()->after('longitude_masuk');
            $table->string('foto_selfie_masuk')->nullable()->after('jarak_masuk');
            $table->string('lokasi_masuk')->nullable()->after('foto_selfie_masuk'); // alamat teks (opsional)
            $table->string('device_masuk')->nullable()->after('lokasi_masuk');

            // ── Kolom PULANG ──────────────────────────────────────────────
            $table->time('jam_pulang')->nullable()->after('device_masuk');
            $table->string('status_pulang', 20)->nullable()->after('jam_pulang');  // hadir|cepat|terlambat
            $table->decimal('latitude_pulang', 10, 7)->nullable()->after('status_pulang');
            $table->decimal('longitude_pulang', 10, 7)->nullable()->after('latitude_pulang');
            $table->integer('jarak_pulang')->nullable()->after('longitude_pulang');
            $table->string('foto_selfie_pulang')->nullable()->after('jarak_pulang');
            $table->string('lokasi_pulang')->nullable()->after('foto_selfie_pulang');
            $table->string('device_pulang')->nullable()->after('lokasi_pulang');
        });

        // ── Tambahkan unique constraint: 1 record per siswa per hari ──────
        // Hapus dulu jika sudah ada index lama yang konflik
        try {
            Schema::table('absen_siswa', function (Blueprint $table) {
                $table->unique(['siswa_id', 'tanggal'], 'absen_siswa_siswa_tanggal_unique');
            });
        } catch (\Throwable $e) {
            // Index mungkin sudah ada atau ada data duplikat — lewati, selesaikan manual
            \Illuminate\Support\Facades\Log::warning('[Migration] Tidak dapat menambah unique constraint absen_siswa: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::table('absen_siswa', function (Blueprint $table) {
            // Hapus unique constraint
            try {
                $table->dropUnique('absen_siswa_siswa_tanggal_unique');
            } catch (\Throwable) {}

            // Hapus kolom baru
            $table->dropColumn([
                'jam_masuk', 'status_masuk',
                'latitude_masuk', 'longitude_masuk', 'jarak_masuk',
                'foto_selfie_masuk', 'lokasi_masuk', 'device_masuk',
                'jam_pulang', 'status_pulang',
                'latitude_pulang', 'longitude_pulang', 'jarak_pulang',
                'foto_selfie_pulang', 'lokasi_pulang', 'device_pulang',
            ]);
        });
    }
};
