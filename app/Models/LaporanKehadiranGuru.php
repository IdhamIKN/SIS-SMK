<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanKehadiranGuru extends Model
{
    use HasFactory;

    protected $fillable = [
        'gtk_id',
        'tanggal',
        'jenis',
        'status',
        'waktu_laporan',
        'catatan',
        'foto_selfie',
        'latitude',
        'longitude',
        'jarak_meter',
        'diverifikasi_oleh',
        'wa_terkirim',
        'idabsensi_guru_legacy',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_laporan' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'wa_terkirim' => 'boolean',
    ];

    public function gtk(): BelongsTo
    {
        return $this->belongsTo(GTK::class);
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
