<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenSiswa extends Model
{
    use HasFactory;

    protected $table = 'absen_siswa';

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'tanggal',
        'jenis',
        'status',
        'waktu_absen',
        'foto_selfie',
        'latitude',
        'longitude',
        'jarak_meter',
        'diverifikasi_oleh',
        'catatan',
        'wa_terkirim_ortu',
        'idabsensi_legacy',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_absen' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'wa_terkirim_ortu' => 'boolean',
    ];

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
