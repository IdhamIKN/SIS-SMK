<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacySiswa extends Model
{
    protected $table = 'tblsiswa';

    protected $primaryKey = 'noreg';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'noreg', 'nisn', 'nama', 'hpsiswa', 'hportu', 'gender',
        'kelas', 'nmkelas', 'semester', 'thnpelajaran', 'statusx',
        'deviceid', 'report_guru', 'password', 'created_login',
        'created_cookies', 'photo', 'idlokasi',
    ];

    protected $casts = [
        'report_guru' => 'boolean',
    ];

    // Relationship dengan Siswa baru jika diperlukan
    public function siswaBaru()
    {
        return $this->hasOne(Siswa::class, 'noreg_legacy', 'noreg');
    }
}
