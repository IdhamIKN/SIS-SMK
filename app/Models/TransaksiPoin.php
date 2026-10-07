<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiPoin extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tbltransaksi';

    protected $primaryKey = 'idtrans';

    public $timestamps = false;

    protected $fillable = [
        'siswa_id',
        'tanggal',
        'tglreward',
        'nis',
        'noreg',
        'jamke',
        'kelas',
        'nmkelas',
        'smester',
        'noreff',
        'idpasal',
        'thajaran',
        'poinr',
        'poinp',
        'userx',
        'created_by',
        'pelapor',
        'ket',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'tglreward' => 'date',
        'jamke' => 'integer',
        'smester' => 'integer',
        'poinr' => 'integer',
        'poinp' => 'integer',
        'siswa_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function subPasal(): BelongsTo
    {
        return $this->belongsTo(SubPasal::class, 'idpasal', 'idpasal');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeTahunAjaran($query, string $tahunAjaran)
    {
        return $query->where('thajaran', $tahunAjaran);
    }

    /** Hanya baris pelanggaran (poinp > 0) */
    public function scopePelanggaran($query)
    {
        return $query->where('poinp', '>', 0);
    }

    /** Hanya baris penghargaan (poinr > 0) */
    public function scopePenghargaan($query)
    {
        return $query->where('poinr', '>', 0);
    }

    /** Scope untuk siswa tertentu — match siswa_id, noreg, atau nis */
    public function scopeForSiswa($query, \App\Models\Siswa $siswa)
    {
        return $query->where(function ($q) use ($siswa) {
            $q->where('siswa_id', $siswa->id);
            if ($siswa->noreg_legacy) $q->orWhere('noreg', $siswa->noreg_legacy);
            if ($siswa->nis)          $q->orWhere('nis',   $siswa->nis);
        });
    }
}
