<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_kelas_legacy',
        'nama_kelas',
        'tingkat',
        'jurusan_id',
        'wali_kelas_id',
        'bk_id',
        'shift',
        'wa_group',
        'tahun_ajaran',
        'academic_year_id',
        'promotion_status',
        'promotion_wave',
    ];

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(GTK::class, 'wali_kelas_id');
    }

    public function bk(): BelongsTo
    {
        return $this->belongsTo(GTK::class, 'bk_id');
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    public function jadwalKBM(): HasMany
    {
        return $this->hasMany(JadwalKBM::class);
    }

    public function laporanKehadiranGuru(): HasMany
    {
        return $this->hasMany(LaporanKehadiranGuru::class);
    }

    public function absenSiswa(): HasMany
    {
        return $this->hasMany(AbsenSiswa::class);
    }

    public function siswaPetugasLaporan(): HasMany
    {
        return $this->hasMany(SiswaPetugasLaporan::class);
    }

    /**
     * Get the academic year for this class
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Get student promotions from this class
     */
    public function promotionsFrom(): HasMany
    {
        return $this->hasMany(StudentPromotion::class, 'from_class_id');
    }

    /**
     * Get student promotions to this class
     */
    public function promotionsTo(): HasMany
    {
        return $this->hasMany(StudentPromotion::class, 'to_class_id');
    }

    /**
     * Check if class is ready for promotion (receiving students)
     */
    public function isReadyForPromotion(): bool
    {
        // Class must be marked as 'ready' or have no students from previous promotion
        return $this->promotion_status === 'ready' ||
               ($this->promotion_status === 'pending' && $this->siswa()->where('academic_status', 'active')->count() === 0);
    }

    /**
     * Check if class can promote students (has active students)
     */
    public function canPromoteStudents(): bool
    {
        return $this->siswa()->where('academic_status', 'active')->count() > 0;
    }

    /**
     * Get next promotion level class
     */
    public function getNextLevelClass(): ?self
    {
        if ($this->tingkat === 'XII') {
            return null; // No next level
        }

        $nextTingkat = match ($this->tingkat) {
            'X' => 'XI',
            'XI' => 'XII',
        };

        return self::where('jurusan_id', $this->jurusan_id)
            ->where('tingkat', $nextTingkat)
            ->where('academic_year_id', $this->academic_year_id)
            ->where('promotion_status', 'ready')
            ->first();
    }

    /**
     * Mark class as promoted (after all students moved out)
     */
    public function markAsPromoted(): void
    {
        $this->update(['promotion_status' => 'promoted']);
    }

    /**
     * Mark class as graduated (XII only)
     */
    public function markAsGraduated(): void
    {
        if ($this->tingkat === 'XII') {
            $this->update(['promotion_status' => 'graduated']);
        }
    }
}
