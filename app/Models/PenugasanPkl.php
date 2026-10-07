<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Model PenugasanPkl — satu baris = satu siswa ditugaskan ke satu lokasi PKL.
 *
 * Status: aktif | selesai | batal
 * Saat dibuat, otomatis membuat PengajuanIzin jenis='pkl' range tanggal_mulai–tanggal_selesai.
 */
class PenugasanPkl extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penugasan_pkl';

    protected $fillable = [
        'siswa_id',
        'lokasi_pkl_id',
        'gtk_id',
        'academic_year_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'pengajuan_izin_id',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    // ──────────────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeAktifPadaTanggal($query, string $tanggal)
    {
        return $query
            ->where('status', 'aktif')
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function durasiHari(): int
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    /**
     * Apakah penugasan aktif pada tanggal tertentu?
     */
    public function aktifPadaTanggal(string $tanggal): bool
    {
        return $this->status === 'aktif'
            && $this->tanggal_mulai->lte(Carbon::parse($tanggal))
            && $this->tanggal_selesai->gte(Carbon::parse($tanggal));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'aktif'   => 'Aktif',
            'selesai' => 'Selesai',
            'batal'   => 'Batal',
            default   => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'aktif'   => 'green',
            'selesai' => 'blue',
            'batal'   => 'red',
            default   => 'gray',
        };
    }

    // ──────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function lokasiPkl(): BelongsTo
    {
        return $this->belongsTo(LokasiPkl::class, 'lokasi_pkl_id');
    }

    public function gtk(): BelongsTo
    {
        return $this->belongsTo(GTK::class, 'gtk_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function pengajuanIzin(): BelongsTo
    {
        return $this->belongsTo(PengajuanIzin::class, 'pengajuan_izin_id');
    }

    public function jurnalHarian(): HasMany
    {
        return $this->hasMany(JurnalHarianPkl::class, 'penugasan_pkl_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
