<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalKBM extends Model
{
    use HasFactory;

    protected $table = 'jadwal_kbm';

    protected $fillable = [
        'kelas_id',
        'gtk_id',
        'hari',
        'jam_ke',
        'jam_mulai',
        'jam_selesai',
        'mata_pelajaran',
        'tahun_ajaran',
        'semester',
    ];

    protected $casts = [
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function gtk(): BelongsTo
    {
        return $this->belongsTo(GTK::class);
    }

    public function laporanKehadiranGuru(): HasMany
    {
        return $this->hasMany(LaporanKehadiranGuru::class);
    }
}
