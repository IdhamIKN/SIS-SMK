<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model AbsenSiswa — 1 record per siswa per hari.
 *
 * Kolom status & waktu_absen + jenis dipertahankan untuk kompatibilitas legacy/reporting.
 * Kolom utama baru:
 *   - jam_masuk, status_masuk, latitude_masuk, longitude_masuk, jarak_masuk,
 *     foto_selfie_masuk, lokasi_masuk, device_masuk
 *   - jam_pulang, status_pulang, latitude_pulang, longitude_pulang, jarak_pulang,
 *     foto_selfie_pulang, lokasi_pulang, device_pulang
 */
class AbsenSiswa extends Model
{
    use HasFactory;

    protected $table = 'absen_siswa';

    protected $fillable = [
        // Identitas record
        'siswa_id',
        'kelas_id',
        'tanggal',

        // ── Kolom absen MASUK ─────────────────────────────────────────
        'jam_masuk',
        'status_masuk',
        'latitude_masuk',
        'longitude_masuk',
        'jarak_masuk',
        'foto_selfie_masuk',
        'lokasi_masuk',
        'device_masuk',

        // ── Kolom absen PULANG ────────────────────────────────────────
        'jam_pulang',
        'status_pulang',
        'latitude_pulang',
        'longitude_pulang',
        'jarak_pulang',
        'foto_selfie_pulang',
        'lokasi_pulang',
        'device_pulang',

        // ── Kolom legacy / umum ───────────────────────────────────────
        'jenis',           // legacy — dipertahankan agar kolom lama tidak error
        'status',          // legacy — dipertahankan
        'waktu_absen',     // legacy — dipertahankan
        'foto_selfie',     // legacy — dipertahankan
        'latitude',        // legacy — dipertahankan
        'longitude',       // legacy — dipertahankan
        'jarak_meter',     // legacy — dipertahankan
        'diverifikasi_oleh',
        'catatan',
        'wa_terkirim_ortu',
        'idabsensi_legacy',
    ];

    protected $casts = [
        'tanggal'          => 'date',
        'waktu_absen'      => 'datetime',
        'latitude'         => 'decimal:7',
        'longitude'        => 'decimal:7',
        'latitude_masuk'   => 'decimal:7',
        'longitude_masuk'  => 'decimal:7',
        'latitude_pulang'  => 'decimal:7',
        'longitude_pulang' => 'decimal:7',
        'wa_terkirim_ortu' => 'boolean',
    ];

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    /** Apakah siswa sudah absen masuk (jam_masuk terisi). */
    public function sudahMasuk(): bool
    {
        return ! empty($this->jam_masuk);
    }

    /** Apakah siswa sudah absen pulang (jam_pulang terisi). */
    public function sudahPulang(): bool
    {
        return ! empty($this->jam_pulang);
    }

    /**
     * Status ringkas untuk ditampilkan di rekap.
     * Mengutamakan status_masuk sebagai representasi utama kehadiran hari itu.
     */
    public function statusDisplay(): string
    {
        return $this->status_masuk ?? $this->status ?? 'alfa';
    }

    // ──────────────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────────────

    /** Scope: cari record hari ini untuk siswa tertentu. */
    public function scopeHariIni($query, int $siswaId)
    {
        return $query->where('siswa_id', $siswaId)->whereDate('tanggal', now()->toDateString());
    }

    // ──────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
