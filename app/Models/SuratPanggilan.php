<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratPanggilan extends Model
{
    use HasFactory;

    protected $table = 'surat_panggilan';

    protected $fillable = [
        'siswa_id',
        'gtk_id',
        'nomor_surat',
        'panggilan_ke',
        'hari',
        'tanggal_acara',
        'waktu',
        'lokasi',
        'menemui',
        'keperluan',
        'dengan_materai',
        'tanggal_surat',
        'tahun_ajaran',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_acara'  => 'date',
        'tanggal_surat'  => 'date',
        'dengan_materai' => 'boolean',
        'panggilan_ke'   => 'integer',
        'wa_sent_at' => 'datetime',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function gtk(): BelongsTo
    {
        return $this->belongsTo(GTK::class, 'gtk_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
    
    public function getWaSudahDikirimAttribute(): bool
    {
        return $this->wa_sent_at !== null;
    }
}
