<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model JurnalHarianPkl — catatan kegiatan harian siswa selama PKL.
 *
 * Input bersifat opsional. Satu siswa = satu jurnal per hari.
 * Guru pembimbing bisa memberikan catatan/verifikasi.
 */
class JurnalHarianPkl extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jurnal_harian_pkl';

    protected $fillable = [
        'penugasan_pkl_id',
        'siswa_id',
        'lokasi_pkl_id',
        'tanggal',
        'jam_datang',
        'jam_pulang',
        'kegiatan',
        'hasil',
        'kendala',
        'foto',
        'status_verifikasi',
        'catatan_pembimbing',
        'diverifikasi_oleh',
        'waktu_verifikasi',
    ];

    protected $casts = [
        'tanggal'          => 'date',
        'waktu_verifikasi' => 'datetime',
    ];

    // ──────────────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────────────

    public function scopeDisetujui($query)
    {
        return $query->where('status_verifikasi', 'disetujui');
    }

    public function scopePendingVerifikasi($query)
    {
        return $query->where('status_verifikasi', 'diajukan');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    public function getStatusVerifikasiLabelAttribute(): string
    {
        return match ($this->status_verifikasi) {
            'diajukan'  => 'Menunggu Verifikasi',
            'disetujui' => 'Disetujui',
            'revisi'    => 'Perlu Revisi',
            default     => $this->status_verifikasi,
        };
    }

    public function getStatusVerifikasiColorAttribute(): string
    {
        return match ($this->status_verifikasi) {
            'diajukan'  => 'yellow',
            'disetujui' => 'green',
            'revisi'    => 'red',
            default     => 'gray',
        };
    }

    public function sudahDiverifikasi(): bool
    {
        return $this->status_verifikasi !== 'diajukan';
    }

    // ──────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────

    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(PenugasanPkl::class, 'penugasan_pkl_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function lokasiPkl(): BelongsTo
    {
        return $this->belongsTo(LokasiPkl::class, 'lokasi_pkl_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
