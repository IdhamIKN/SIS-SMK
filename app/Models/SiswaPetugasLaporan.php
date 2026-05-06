<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiswaPetugasLaporan extends Model
{
    use HasFactory;

    protected $table = 'siswa_petugas_laporan';

    protected $fillable = [
        'kelas_id',
        'siswa_id',
        'tanggal',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
