<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penghargaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tblpenghargaan';

    protected $primaryKey = 'idpen';

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
        'ket',
        'tglacc',
        'nmacc',
        'acc',
        'created_by',
        'kirim_wa',
        'nomor_wa',
        'wa_sent_at',
        'wa_nomor_tujuan',
    ];

    protected $casts = [
        'tgl' => 'datetime',
        'tglacc' => 'datetime',
        'poin' => 'integer',
        'siswa_id' => 'integer',
        'created_by' => 'integer',
        'kirim_wa' => 'boolean',
        'wa_sent_at' => 'datetime',
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
