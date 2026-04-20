<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyAbsensi extends Model
{
    protected $table = 'tblabsensi';
    protected $primaryKey = 'idabsensi';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'idabsensi', 'idevent', 'tgltransaksi', 'tglabsen', 'noreg', 'nisn',
        'kelas', 'nmkelas', 'semester', 'thajaran', 'time_in', 'time_out',
        'picture_in', 'picture_out', 'hadir', 'ijin', 'sakit', 'alpha', 'pulang',
        'ket', 'userx', 'acc', 'tglacc', 'nmacc', 'lokasi',
        'latitude_longtitude_in', 'latitude_longtitude_out'
    ];

    protected $casts = [
        'tgltransaksi' => 'datetime',
        'tglabsen' => 'date',
        'time_in' => 'datetime:H:i:s',
        'time_out' => 'datetime:H:i:s',
        'hadir' => 'integer',
        'ijin' => 'integer',
        'sakit' => 'integer',
        'alpha' => 'integer',
        'pulang' => 'integer',
    ];

    // Relationship dengan Siswa baru
    public function siswaBaru()
    {
        return $this->belongsTo(Siswa::class, 'noreg', 'noreg_legacy');
    }

    // Helper untuk menentukan status
    public function getStatusAttribute()
    {
        if ($this->hadir == 1) return 'hadir';
        if ($this->ijin == 1) return 'izin';
        if ($this->sakit == 1) return 'sakit';
        if ($this->alpha == 1) return 'alfa';
        return 'unknown';
    }

    // Helper untuk menentukan jenis
    public function getJenisAttribute()
    {
        if ($this->time_in && $this->time_in != '00:00:00') {
            return 'masuk';
        }
        if ($this->time_out && $this->time_out != '00:00:00') {
            return 'pulang';
        }
        return 'unknown';
    }
}