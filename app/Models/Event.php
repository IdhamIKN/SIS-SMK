<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{

    protected $table = 'events';

    protected $fillable = [
        'created_by',
        'event_category_id',
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
        'recurrence_parent_id',
        'recurrence_rule_id',
        'recurrence_sequence',
        'auto_point_pelanggaran',
        'pasal_pelanggaran_id',
        'poin_pelanggaran_event',
        'auto_point_processed_at',
        'auto_penghargaan',
        'pasal_penghargaan_id',
        'poin_penghargaan_event',
        'auto_penghargaan_processed_at',
        // Ekstrakurikuler
        'is_ekstrakurikuler',
        'pelatih_1',
        'pelatih_2',
        'pelatih_3',
        'pembina_nama',
        'pembina_nip',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'barcode_updated_at' => 'datetime',
        'ada_absen_masuk' => 'boolean',
        'ada_absen_pulang' => 'boolean',
        'berlaku_untuk_semua' => 'boolean',
        'recurrence_sequence' => 'integer',
        'auto_point_pelanggaran' => 'boolean',
        'auto_point_processed_at' => 'datetime',
        'poin_pelanggaran_event' => 'integer',
        'auto_penghargaan' => 'boolean',
        'auto_penghargaan_processed_at' => 'datetime',
        'poin_penghargaan_event' => 'integer',
        'is_ekstrakurikuler' => 'boolean',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function recurrenceRule(): BelongsTo
    {
        return $this->belongsTo(EventRecurrenceRule::class, 'recurrence_rule_id');
    }

    public function recurringParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'recurrence_parent_id');
    }

    public function recurringChildren(): HasMany
    {
        return $this->hasMany(self::class, 'recurrence_parent_id');
    }

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'event_kelas', 'event_id', 'kelas_id');
    }

    public function absenEvent(): HasMany
    {
        return $this->hasMany(AbsenEvent::class);
    }

    public function pasalPelanggaran(): BelongsTo
    {
        return $this->belongsTo(SubPasal::class, 'pasal_pelanggaran_id', 'idpasal');
    }

    public function pasalPenghargaan(): BelongsTo
    {
        return $this->belongsTo(SubPasal::class, 'pasal_penghargaan_id', 'idpasal');
    }

    public function absenEventGuru(): HasMany
    {
        return $this->hasMany(AbsenEventGuru::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(EventPhoto::class)->orderBy('urutan')->orderBy('id');
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
            return $this->siswa()->whereHas('kelas', fn($q) => $q->where('id', $kelasId))->exists();
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
     * Boot model — daftarkan event hooks.
     */
    protected static function booted(): void
    {
        // Saat child event dihapus, catat tombstone agar tidak dibuat ulang saat master di-edit.
        static::deleting(function (Event $event) {
            if (! $event->recurrence_parent_id) {
                return; // hanya child event
            }

            EventDeletedOccurrence::updateOrInsert(
                [
                    'master_event_id' => $event->recurrence_parent_id,
                    'occurrence_date' => $event->tanggal_mulai->toDateString(),
                ],
                [
                    'deleted_event_id' => $event->id,
                    'deleted_at'       => now(),
                    'deleted_by'       => auth()->id(),
                ]
            );
        });
    }

    /**
     * Generate barcode value baru
     */
    public function rotateBarcode(): string
    {
        $this->barcode_value = hash('sha256', $this->id . microtime() . random_bytes(16));
        $this->barcode_updated_at = now();
        $this->save();

        return $this->barcode_value;
    }
}
