<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Siswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'siswas';

    protected $fillable = [
        'nis',
        'nisn',
        'nik',
        'nokk',
        'nama_lengkap',
        'agama',
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
        'asal',
        'no_hp_siswa',
        'email',
        'no_hp_ortu1',
        'no_hp_ortu2',
        'nama_ortu1',
        'nama_ortu2',
        'nama_wali',
        'bb',
        'tb',
        'lk',
        'status_aktif',
        'noreg_legacy',
        'user_id',
        'graduation_year',
        'retention_count',
        'academic_status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'status_aktif' => 'boolean',
    ];

    /**
     * Get default password untuk siswa (menggunakan NISN)
     */
    public function getDefaultPassword(): string
    {
        return $this->nisn;
    }

    /**
     * Get hashed default password untuk siswa
     */
    public function getHashedDefaultPassword(): string
    {
        return bcrypt($this->getDefaultPassword());
    }

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

    public function isPetugasLaporanGuru(): bool
    {
        return $this->loadMissing('siswaPetugasLaporan')
            ->siswaPetugasLaporan
            ->contains('kelas_id', $this->kelas_id);
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

    public function pelanggaran(): HasMany
    {
        return $this->hasMany(Pelanggaran::class, 'siswa_id');
    }

    public function penghargaan(): HasMany
    {
        return $this->hasMany(Penghargaan::class, 'siswa_id');
    }

    public function transaksiPoin(): HasMany
    {
        return $this->hasMany(TransaksiPoin::class, 'siswa_id');
    }

    // ── Relasi PKL ─────────────────────────────────────────────────────────

    /**
     * Semua penugasan PKL siswa ini (semua status, semua tahun).
     */
    public function penugasanPkl(): HasMany
    {
        return $this->hasMany(PenugasanPkl::class, 'siswa_id');
    }

    /**
     * Penugasan PKL yang sedang aktif.
     * Hanya satu yang seharusnya aktif dalam satu waktu.
     */
    public function penugasanPklAktif()
    {
        return $this->hasOne(PenugasanPkl::class, 'siswa_id')
            ->where('status', 'aktif')
            ->latest('tanggal_mulai');
    }

    /**
     * Jurnal harian PKL siswa ini.
     */
    public function jurnalPkl(): HasMany
    {
        return $this->hasMany(JurnalHarianPkl::class, 'siswa_id');
    }

    /**
     * Apakah siswa ini sedang aktif PKL pada tanggal tertentu?
     */
    public function sedangPkl(?string $tanggal = null): bool
    {
        $tgl = $tanggal ?? now()->toDateString();
        return $this->penugasanPkl()
            ->where('status', 'aktif')
            ->where('tanggal_mulai', '<=', $tgl)
            ->where('tanggal_selesai', '>=', $tgl)
            ->exists();
    }

    public function tatibPointNotifications(): HasMany
    {
        return $this->hasMany(TatibPointNotification::class, 'siswa_id');
    }

    public function suratPanggilan(): HasMany
    {
        return $this->hasMany(SuratPanggilan::class, 'siswa_id');
    }
}
