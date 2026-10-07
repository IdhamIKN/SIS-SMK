<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoPelanggaranRule extends Model
{
    protected $table = 'auto_pelanggaran_rules';

    protected $fillable = [
        'nama_rule',
        'aktif',
        'trigger_type',
        'threshold_hari',
        'periode_bulan',
        'pasal_id',
        'poin_override',
        'keterangan',
        'urutan',
    ];

    protected $casts = [
        'aktif'          => 'boolean',
        'threshold_hari' => 'integer',
        'periode_bulan'  => 'integer',
        'poin_override'  => 'integer',
        'urutan'         => 'integer',
    ];

    // Daftar trigger yang tersedia
    const TRIGGER_TYPES = [
        'alfa_harian'         => 'Alfa Harian — setiap siswa alfa pada hari itu',
        'alfa_bulanan'        => 'Alfa Bulanan — siswa melebihi N alfa dalam 1 bulan',
        'terlambat_berulang'  => 'Terlambat Berulang — terlambat ≥ N kali dalam periode',
        'custom'              => 'Custom — kondisi khusus (lihat keterangan)',
    ];

    public function pasal(): BelongsTo
    {
        return $this->belongsTo(SubPasal::class, 'pasal_id', 'idpasal');
    }

    /**
     * Poin efektif yang digunakan (override atau default pasal)
     */
    public function getPoinEfektifAttribute(): int
    {
        if ($this->poin_override !== null) {
            return $this->poin_override;
        }

        return $this->pasal?->poin_default ?? 0;
    }

    /**
     * Label trigger yang mudah dibaca
     */
    public function getTriggerLabelAttribute(): string
    {
        return self::TRIGGER_TYPES[$this->trigger_type] ?? $this->trigger_type;
    }
}
