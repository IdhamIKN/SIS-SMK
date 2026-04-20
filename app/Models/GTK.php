<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GTK extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'kd_guru',
        'nip',
        'nuptk',
        'nama_lengkap',
        'jenis_kelamin',
        'no_hp',
        'foto',
        'mata_pelajaran',
        'jabatan',
        'status_aktif',
        'acc_absen',
        'acc_kurikulum',
        'acc_jurnal',
        'acc_bk',
        'guru_piket',
        'acc_profil',
        'group_acc',
        'view_siswa',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'acc_absen' => 'boolean',
        'acc_kurikulum' => 'boolean',
        'acc_jurnal' => 'boolean',
        'acc_bk' => 'boolean',
        'guru_piket' => 'boolean',
        'acc_profil' => 'boolean',
        'group_acc' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jadwalKBM()
    {
        return $this->hasMany(JadwalKBM::class);
    }

    public function laporanKehadiranGuru()
    {
        return $this->hasMany(LaporanKehadiranGuru::class);
    }

    public function kelasWali()
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    public function kelasBK()
    {
        return $this->hasMany(Kelas::class, 'bk_id');
    }
}
