<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblJurnalPembelajaran extends Model
{
    use HasFactory;

    protected $table = 'tbljurnalpembelajaran';

    public $timestamps = false;

    protected $fillable = [
        'jadwal_kbm_id',
        'time',
        'tipe',
        'tanggal',
        'kdguru',
        'kelas',
        'pelajaran',
        'jamke',
        'jam_mulai',
        'jam_selesai',
        'deskripsi',
        'guru',
        'siswahadir',
        'siswatdkhadir',
        'namasiswa',
        'bukti',
    ];

    protected $casts = [
        'time' => 'datetime',
        'tanggal' => 'date',
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
        'siswahadir' => 'integer',
        'siswatdkhadir' => 'integer',
    ];

    public function jadwalKbm()
    {
        return $this->belongsTo(JadwalKBM::class, 'jadwal_kbm_id');
    }
}
