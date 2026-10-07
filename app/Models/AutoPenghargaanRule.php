<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoPenghargaanRule extends Model
{
    protected $table = 'auto_penghargaan_rules';

    protected $fillable = [
        'nama_rule',
        'aktif',
        'trigger_type',
        'periode_bulan',
        'izin_dihitung_hadir',
        'sakit_dihitung_hadir',
        'terlambat_dihitung_hadir',
        'pasal_id',
        'poin_override',
        'keterangan',
        'urutan',
    ];

    protected $casts = [
        'aktif'                    => 'boolean',
        'periode_bulan'            => 'integer',
        'izin_dihitung_hadir'      => 'boolean',
        'sakit_dihitung_hadir'     => 'boolean',
        'terlambat_dihitung_hadir' => 'boolean',
        'poin_override'            => 'integer',
        'urutan'                   => 'integer',
    ];

    // Daftar trigger yang tersedia
    const TRIGGER_TYPES = [
        'full_hadir_bulanan'  => 'Full Hadir Bulanan — tidak ada alfa selama 1 bulan penuh',
        'full_hadir_mingguan' => 'Full Hadir Mingguan — tidak ada alfa selama 1 minggu penuh',
        'streak_hadir'        => 'Streak Hadir — hadir berturut-turut ≥ N hari',
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
