<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyKelas extends Model
{
    protected $table = 'tblkelas';

    protected $primaryKey = 'idkelas';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'idkelas', 'nmkelas', 'kelas', 'idjurusan', 'idwali', 'idbk',
        'color', 'shif', 'wagroup',
    ];

    // Relationship dengan Kelas baru
    public function kelasBaru()
    {
        return $this->hasOne(Kelas::class, 'id_kelas_legacy', 'idkelas');
    }

    // Relationship dengan wali kelas (legacy)
    public function waliKelas()
    {
        return $this->belongsTo(LegacyPegawai::class, 'idwali', 'kdguru');
    }

    // Relationship dengan BK (legacy)
    public function bk()
    {
        return $this->belongsTo(LegacyPegawai::class, 'idbk', 'kdguru');
    }
}
