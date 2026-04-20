<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyPegawai extends Model
{
    protected $table = 'tblpegawai';
    protected $primaryKey = 'kdguru';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kdguru', 'nmpegawai', 'nik', 'alpegawai', 'telp', 'jabatan',
        'deviceid', 'groupacc', 'accabsen', 'acckurikulum', 'accjurnal',
        'accbk', 'gurupiket', 'groupwa', 'accprofil', 'viewsiswa', 'status',
        'jamngajar', 'tatapmuka'
    ];

    // Relationship dengan GTK baru
    public function gtkBaru()
    {
        return $this->hasOne(GTK::class, 'kd_guru', 'kdguru');
    }
}