<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model LokasiPkl — master data tempat PKL.
 *
 * Satu lokasi bisa menampung banyak siswa (lewat PenugasanPkl).
 * Setiap lokasi punya konfigurasi jam absen dan auto poin tersendiri.
 */
class LokasiPkl extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lokasi_pkl';

    protected $fillable = [
        // Identitas tempat
        'nama_tempat',
        'jenis_usaha',

        // Alamat
        'alamat',
        'kelurahan',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'kode_pos',

        // Koordinat GPS
        'latitude',
        'longitude',
        'radius_meter',

        // Penanggung jawab / pembimbing industri
        'nama_pj',
        'jabatan_pj',
        'no_hp_pj',
        'email_pj',
        'no_telp_kantor',
        'website',

        // Kapasitas & Foto
        'kapasitas',
        'foto',

        // Jam absen PKL
        'jam_masuk_pkl',
        'jam_pulang_pkl',
        'batas_terlambat_pkl',
        'batas_absen_masuk_pkl',

        // Konfigurasi auto poin
        'auto_poin_hadir_pkl',
        'pasal_hadir_pkl_id',
        'auto_poin_terlambat_pkl',
        'pasal_terlambat_pkl_id',
        'auto_poin_alfa_pkl',
        'pasal_alfa_pkl_id',

        // Status & relasi
        'status_aktif',
        'academic_year_id',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'latitude'              => 'decimal:7',
        'longitude'             => 'decimal:7',
        'radius_meter'          => 'integer',
        'kapasitas'             => 'integer',
        'status_aktif'          => 'boolean',
        'auto_poin_hadir_pkl'   => 'boolean',
        'auto_poin_terlambat_pkl' => 'boolean',
        'auto_poin_alfa_pkl'    => 'boolean',
    ];

    // ──────────────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    public function scopeTahunAjaran($query, int $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Jumlah siswa yang aktif PKL di lokasi ini.
     */
    public function jumlahSiswaAktif(): int
    {
        return $this->penugasan()->where('status', 'aktif')->count();
    }

    /**
     * Apakah lokasi masih punya kapasitas untuk siswa baru?
     */
    public function masihBisaMenampung(): bool
    {
        if (! $this->kapasitas) {
            return true; // kapasitas tidak dibatasi
        }
        return $this->jumlahSiswaAktif() < $this->kapasitas;
    }

    /**
     * Alamat lengkap satu baris.
     */
    public function getAlamatLengkapAttribute(): string
    {
        return collect([
            $this->alamat,
            $this->kelurahan,
            $this->kecamatan,
            $this->kabupaten,
            $this->provinsi,
        ])->filter()->implode(', ');
    }

    /**
     * Apakah lokasi punya koordinat GPS valid.
     */
    public function punyaKoordinat(): bool
    {
        return $this->latitude && $this->longitude;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────

    public function penugasan(): HasMany
    {
        return $this->hasMany(PenugasanPkl::class, 'lokasi_pkl_id');
    }

    public function jurnalHarian(): HasMany
    {
        return $this->hasMany(JurnalHarianPkl::class, 'lokasi_pkl_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pasalHadir(): BelongsTo
    {
        return $this->belongsTo(SubPasal::class, 'pasal_hadir_pkl_id', 'idpasal');
    }

    public function pasalTerlambat(): BelongsTo
    {
        return $this->belongsTo(SubPasal::class, 'pasal_terlambat_pkl_id', 'idpasal');
    }

    public function pasalAlfa(): BelongsTo
    {
        return $this->belongsTo(SubPasal::class, 'pasal_alfa_pkl_id', 'idpasal');
    }
}
