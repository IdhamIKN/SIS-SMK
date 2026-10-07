<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventGuru extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'event_gurus';

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
        'barcode_rotate_detik',
        'barcode_value',
        'barcode_updated_at',
    ];

    protected $casts = [
        'tanggal_mulai'      => 'datetime',
        'tanggal_selesai'    => 'datetime',
        'barcode_updated_at' => 'datetime',
        'ada_absen_masuk'    => 'boolean',
        'ada_absen_pulang'   => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function absenEventGuru(): HasMany
    {
        return $this->hasMany(AbsenEventGuru::class, 'event_guru_id');
    }

    public function isActive(): bool
    {
        $now = now();
        return $now->gte($this->tanggal_mulai) && $now->lte($this->tanggal_selesai);
    }

    public function isBarcodeValid(): bool
    {
        if ($this->barcode_rotate_detik <= 0) {
            return true;
        }
        return now()->diffInSeconds($this->barcode_updated_at) <= $this->barcode_rotate_detik;
    }

    public function canAbsen(string $jenis): bool
    {
        if ($jenis === 'masuk' && ! $this->ada_absen_masuk) return false;
        if ($jenis === 'pulang' && ! $this->ada_absen_pulang) return false;
        return true;
    }

    public function hasLocation(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    public function distanceTo(float $lat, float $lng): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat - $this->lat);
        $dLng = deg2rad($lng - $this->lng);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($this->lat)) * cos(deg2rad($lat))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function isWithinRadius(float $lat, float $lng): bool
    {
        if (! $this->hasLocation()) return true;
        return $this->distanceTo($lat, $lng) <= $this->radius_meter;
    }

    public function rotateBarcode(): string
    {
        $this->barcode_value      = hash('sha256', $this->id . microtime() . random_bytes(16));
        $this->barcode_updated_at = now();
        $this->save();
        return $this->barcode_value;
    }
}
