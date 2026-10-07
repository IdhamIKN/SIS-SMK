<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TatibPointNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'tahun_ajaran',
        'batas_ke',
        'batas_poin',
        'total_poin',
        'tindakan',
        'sanksi',
        'nomor_tujuan',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'batas_ke' => 'integer',
        'batas_poin' => 'integer',
        'total_poin' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
