<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'events';

    protected $fillable = [
        'created_by',
        'nama_event',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'lat',
        'lng',
        'radius_meter',
        'ada_absen_masuk',
        'ada_absen_pulang',
        'berlaku_untuk_semua',
        'mode_peserta',
        'barcode_rotate_detik',
        'barcode_value',
        'barcode_updated_at',
        'idevent_legacy',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'barcode_updated_at' => 'datetime',
        'ada_absen_masuk' => 'boolean',
        'ada_absen_pulang' => 'boolean',
        'berlaku_untuk_semua' => 'boolean',
    ];

    /**
     * Relasi ke siswa spesifik (untuk mode peserta = siswa)
     */
    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'event_siswa', 'event_id', 'siswa_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'event_kelas', 'event_id', 'kelas_id');
    }

    public function absenEvent(): HasMany
    {
        return $this->hasMany(AbsenEvent::class);
    }

    /**
     * Cek apakah event sedang aktif (berada dalam rentang waktu)
     */
    public function isActive(): bool
    {
        $now = now();

        return $now->gte($this->tanggal_mulai) && $now->lte($this->tanggal_selesai);
    }

    /**
     * Cek apakah barcode masih berlaku (belum melewati batas rotasi)
     */
    public function isBarcodeValid(): bool
    {
        if ($this->barcode_rotate_detik <= 0) {
            return true;
        }

        return now()->diffInSeconds($this->barcode_updated_at) <= $this->barcode_rotate_detik;
    }

    /**
     * Cek apakah user (siswa) boleh melakukan absen jenis tertentu di event ini
     */
    public function canAbsen(string $jenis): bool
    {
        if ($jenis === 'masuk' && ! $this->ada_absen_masuk) {
            return false;
        }
        if ($jenis === 'pulang' && ! $this->ada_absen_pulang) {
            return false;
        }

        return true;
    }

    /**
     * Cek apakah event berlaku untuk kelas tertentu (mode kelas)
     */
    public function appliesToKelas(int $kelasId): bool
    {
        if ($this->berlaku_untuk_semua) {
            return true;
        }
        if ($this->mode_peserta === 'siswa') {
            return $this->siswa()->whereHas('kelas', fn ($q) => $q->where('id', $kelasId))->exists();
        }

        return $this->kelas()->where('kelas_id', $kelasId)->exists();
    }

    /**
     * Cek apakah event berlaku untuk siswa tertentu (mode siswa)
     */
    public function appliesToSiswa(int $siswaId): bool
    {
        if ($this->berlaku_untuk_semua) {
            return true;
        }
        if ($this->mode_peserta === 'siswa') {
            return $this->siswa()->where('siswa_id', $siswaId)->exists();
        }
        // Mode kelas: cek via kelas siswa
        $siswa = Siswa::find($siswaId);
        if (! $siswa) {
            return false;
        }

        return $this->kelas()->where('kelas_id', $siswa->kelas_id)->exists();
    }

    /**
     * Cek apakah event punya lokasi (lat/lng tersedia)
     */
    public function hasLocation(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    /**
     * Hitung jarak (meter) antara event dan koordinat siswa
     * Menggunakan rumus Haversine
     */
    public function distanceTo(float $lat, float $lng): float
    {
        $earthRadius = 6371000; // meter

        $dLat = deg2rad($lat - $this->lat);
        $dLng = deg2rad($lng - $this->lng);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($this->lat)) * cos(deg2rad($lat)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Cek apakah siswa berada dalam radius yang diizinkan
     */
    public function isWithinRadius(float $lat, float $lng): bool
    {
        if (! $this->hasLocation()) {
            return true; // Jika tidak ada lokasi, bebas absen
        }

        return $this->distanceTo($lat, $lng) <= $this->radius_meter;
    }

    /**
     * Generate barcode value baru
     */
    public function rotateBarcode(): string
    {
        $this->barcode_value = hash('sha256', $this->id.microtime().random_bytes(16));
        $this->barcode_updated_at = now();
        $this->save();

        return $this->barcode_value;
    }
}
