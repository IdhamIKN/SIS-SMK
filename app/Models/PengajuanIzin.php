<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanIzin extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_izin';

    protected $fillable = [
        'siswa_id',
        'jenis',
        'alasan',
        'bukti',
        'tanggal_mulai',
        'tanggal_sampai',
        'status',
        'diverifikasi_oleh',
        'waktu_verifikasi',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_sampai' => 'date',
        'waktu_verifikasi' => 'datetime',
    ];

    public function scopeDiajukan($query)
    {
        return $query->where('status', 'diajukan');
    }

    public function getTanggalIzinAttribute()
    {
        return $this->tanggal_mulai;
    }

    public function isRangeJenis(): bool
    {
        return in_array($this->jenis, ['izin_sakit', 'izin_lainnya'], true);
    }

    public function getTanggalIzinFormAttribute(): ?string
    {
        return $this->tanggal_mulai?->format('Y-m-d');
    }

    public function getTanggalMulaiFormAttribute(): ?string
    {
        return $this->tanggal_mulai?->format('Y-m-d');
    }

    public function getTanggalSampaiFormAttribute(): ?string
    {
        return $this->tanggal_sampai?->format('Y-m-d');
    }

    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis) {
            'izin_sakit' => 'Izin Sakit',
            'izin_pulang_cepat' => 'Izin Pulang Cepat',
            'izin_terlambat' => 'Izin Terlambat',
            default => 'Izin Lainnya',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => 'Diajukan',
        };
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}

