<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_kelas_legacy',
        'nama_kelas',
        'tingkat',
        'jurusan_id',
        'wali_kelas_id',
        'bk_id',
        'shift',
        'wa_group',
        'tahun_ajaran',
    ];

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(GTK::class, 'wali_kelas_id');
    }

    public function bk(): BelongsTo
    {
        return $this->belongsTo(GTK::class, 'bk_id');
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    public function jadwalKBM(): HasMany
    {
        return $this->hasMany(JadwalKBM::class);
    }

    public function laporanKehadiranGuru(): HasMany
    {
        return $this->hasMany(LaporanKehadiranGuru::class);
    }

    public function absenSiswa(): HasMany
    {
        return $this->hasMany(AbsenSiswa::class);
    }

    public function siswaPetugasLaporan(): HasMany
    {
        return $this->hasMany(SiswaPetugasLaporan::class);
    }
}
