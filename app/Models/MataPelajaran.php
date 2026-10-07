<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'deskripsi',
        'kategori',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function jadwalKBM(): HasMany
    {
        return $this->hasMany(JadwalKBM::class, 'mata_pelajaran_id');
    }

    public function gtk(): HasMany
    {
        return $this->hasManyThrough(GTK::class, JadwalKBM::class, 'mata_pelajaran_id', 'id', 'id', 'gtk_id');
    }

    /**
     * Scope untuk mata pelajaran aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    /**
     * Get label kategori dalam bahasa Indonesia
     */
    public function getKategoriLabelAttribute(): string
    {
        $labels = [
            'umum' => 'Mata Pelajaran Umum',
            'jurusan' => 'Mata Pelajaran Jurusan',
            'mulok' => 'Muatan Lokal',
        ];

        return $labels[$this->kategori] ?? $this->kategori;
    }
}