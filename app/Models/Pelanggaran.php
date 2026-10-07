<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelanggaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tblpelanggaran';

    protected $primaryKey = 'idpel';

    public $timestamps = false;

    protected $fillable = [
        'siswa_id',
        'tgl',
        'tahun_ajaran',
        'deviceid',
        'noreg',
        'nama',
        'kelas',
        'idpasal',
        'isi',
        'poin',
        'pelapor',
        'created_by',
    ];

    protected $casts = [
        'tgl' => 'datetime',
        'poin' => 'integer',
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
}
