<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class PengajuanIzin extends Model
{
    use HasFactory;


    protected $table = 'pengajuan_izin';
    
    protected $fillable = [
        'siswa_id',
        'jenis',
        'tanggal_izin',
        'tanggal_mulai',
        'tanggal_sampai',
'alasan',
        'bukti',
        'status',
        'diverifikasi_oleh',
        'waktu_verifikasi',
    ];

    protected $casts = [
        'tanggal_izin' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_sampai' => 'date',
        'waktu_verifikasi' => 'datetime',
    ];

    const JENIS = [
        'izin_sakit' => 'Izin Sakit',
        'izin_pulang_cepat' => 'Izin Pulang Cepat',
        'izin_terlambat' => 'Izin Terlambat',
        'izin_lainnya' => 'Izin Lainnya',
    ];

    const STATUS = [
        'diajukan' => 'Diajukan',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function getJenisLabelAttribute()
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function scopeDiajukan($query)
    {
        return $query->where('status', 'diajukan');
    }

    public function scopeUntukSiswa($query, $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }
}

