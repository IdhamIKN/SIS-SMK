<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model AbsenEvent — 1 record per siswa per event.
 *
 * Kolom lama (jenis, waktu_scan, barcode_digunakan) dipertahankan untuk
 * kompatibilitas. Kolom baru memisahkan data masuk & pulang dalam 1 record.
 */
class AbsenEvent extends Model
{
    use HasFactory;

    protected $table = 'absen_event';

    protected $fillable = [
        'event_id',
        'siswa_id',

        // ── Kolom MASUK ───────────────────────────────────────────────
        'waktu_masuk',
        'barcode_masuk',
        'latitude_masuk',
        'longitude_masuk',
        'foto_selfie_masuk',
        'lokasi_masuk',

        // ── Kolom PULANG ──────────────────────────────────────────────
        'waktu_pulang',
        'barcode_pulang',
        'latitude_pulang',
        'longitude_pulang',
        'foto_selfie_pulang',
        'lokasi_pulang',

        // ── Kolom umum / legacy ───────────────────────────────────────
        'jenis',              // legacy
        'waktu_scan',         // legacy
        'barcode_digunakan',  // legacy
        'wa_terkirim_ortu',
        'created_by',
    ];

    protected $casts = [
        'waktu_scan'       => 'datetime',
        'waktu_masuk'      => 'datetime',
        'waktu_pulang'     => 'datetime',
        'wa_terkirim_ortu' => 'boolean',
    ];

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    public function sudahMasuk(): bool
    {
        return $this->waktu_masuk !== null;
    }

    public function sudahPulang(): bool
    {
        return $this->waktu_pulang !== null;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
