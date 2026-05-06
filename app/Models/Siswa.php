<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Siswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'siswas';

    protected $fillable = [
        'nis',
        'nisn',
        'nama_lengkap',
        'jenis_kelamin',
        'kelas_id',
        'angkatan',
        'foto',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'desa',
        'kelurahan',
        'kecamatan',
        'kabupaten',
        'kode_pos',
        'no_hp_siswa',
        'no_hp_ortu1',
        'no_hp_ortu2',
        'nama_ortu1',
        'nama_ortu2',
        'nama_wali',
        'graduation_year',
        'retention_count',
        'academic_status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'status_aktif' => 'boolean',
    ];

    protected $fillable_legacy = [
        'nama_ortu2',
        'nama_wali',
        'status_aktif',
        'noreg_legacy',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absenSiswa()
    {
        return $this->hasMany(AbsenSiswa::class);
    }

    public function absenEvent()
    {
        return $this->hasMany(AbsenEvent::class);
    }

    public function laporanKehadiranGuru()
    {
        return $this->hasMany(LaporanKehadiranGuru::class, 'dilaporkan_oleh_siswa_id');
    }

    public function siswaPetugasLaporan()
    {
        return $this->hasMany(SiswaPetugasLaporan::class);
    }

    // Relationship dengan data legacy
    public function siswaLegacy()
    {
        return $this->belongsTo(LegacySiswa::class, 'noreg_legacy', 'noreg');
    }

    // Relationship dengan absensi legacy
    public function absensiLegacy()
    {
        return $this->hasMany(LegacyAbsensi::class, 'noreg', 'noreg_legacy');
    }

    /**
     * Get all promotion history for this student
     */
    public function promotions(): HasMany
    {
        return $this->hasMany(StudentPromotion::class, 'student_id');
    }

    /**
     * Check if student can be promoted
     */
    public function canBePromoted(): bool
    {
        return $this->academic_status === 'active';
    }

    /**
     * Check if student is retained
     */
    public function isRetained(): bool
    {
        return $this->retention_count > 0;
    }

    /**
     * Get latest promotion record
     */
    public function latestPromotion(): ?StudentPromotion
    {
        return $this->promotions()->latest('promotion_date')->first();
    }

    public function pengajuanIzin()
    {
        return $this->hasMany(PengajuanIzin::class, 'siswa_id');
    }
}
