<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SetJam extends Model
{
    protected $table = 'tblsetjam';

    protected $primaryKey = 'id_jam';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id_jam', 'shif', 'kelompok_jam', 'nama_jam',
        'time_in', 'limit_in', 'time_out', 'limit_out', 'statusjam',
    ];

    protected $casts = [
        'time_in'   => 'datetime:H:i:s',
        'limit_in'  => 'datetime:H:i:s',
        'time_out'  => 'datetime:H:i:s',
        'limit_out' => 'datetime:H:i:s',
        'statusjam' => 'boolean',
    ];

    // ── Konstanta kelompok_jam ────────────────────────────────────────────────
    const KELOMPOK_REGULER      = 'reguler';      // Senin–Kamis, Kelas 10
    const KELOMPOK_REGULER_1112 = 'reguler_1112'; // Senin–Kamis, Kelas 11-12
    const KELOMPOK_JUMAT        = 'jumat';         // Jumat semua kelas

    /**
     * Hari-hari yang menggunakan jadwal Jumat.
     */
    const HARI_JUMAT = ['Jumat'];

    /**
     * Scope: hanya jam aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('statusjam', 1);
    }

    /**
     * Scope: filter berdasarkan kelompok jam.
     */
    public function scopeKelompok(Builder $query, string $kelompok): Builder
    {
        return $query->where('kelompok_jam', $kelompok);
    }

    /**
     * Dapatkan jam aktif berdasarkan hari.
     * - Jumat         → kelompok 'jumat'
     * - Senin–Kamis   → reguler + reguler_1112
     * - Sabtu/Minggu  → reguler (fallback)
     *
     * @param  string|null $hari  e.g. 'Senin', 'Jumat' — null = semua
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getJamByHari(?string $hari = null)
    {
        $query = static::where('statusjam', 1);

        if ($hari === 'Jumat') {
            $query->where('kelompok_jam', self::KELOMPOK_JUMAT);
        } elseif ($hari !== null) {
            $query->whereIn('kelompok_jam', [
                self::KELOMPOK_REGULER,
                self::KELOMPOK_REGULER_1112,
            ]);
        }

        return $query->orderBy('time_in')->orderBy('id_jam')->get();
    }

    /**
     * Dapatkan semua jam aktif, dikelompokkan per kelompok_jam.
     * Berguna untuk dropdown yang dirender dengan <optgroup>.
     *
     * @return array{reguler: Collection, reguler_1112: Collection, jumat: Collection}
     */
    public static function getJamAktifGrouped(): array
    {
        $all = static::where('statusjam', 1)
            ->orderBy('time_in')
            ->orderBy('id_jam')
            ->get()
            ->groupBy('kelompok_jam');

        return [
            self::KELOMPOK_REGULER      => $all->get(self::KELOMPOK_REGULER,      collect()),
            self::KELOMPOK_REGULER_1112 => $all->get(self::KELOMPOK_REGULER_1112, collect()),
            self::KELOMPOK_JUMAT        => $all->get(self::KELOMPOK_JUMAT,         collect()),
        ];
    }

    /**
     * Dapatkan jam berdasarkan shift (untuk cek absensi masuk/pulang).
     * Menggunakan kelompok reguler sebagai acuan waktu shift utama.
     */
    public static function getJamByShift(string $shift = 'Pagi')
    {
        return static::where('shif', $shift)
            ->where('statusjam', 1)
            ->where('kelompok_jam', self::KELOMPOK_REGULER)
            ->first();
    }

    /**
     * Dapatkan semua jam aktif (backward compat — semua kelompok).
     */
    public static function getJamAktif()
    {
        return static::where('statusjam', 1)
            ->orderBy('time_in')
            ->orderBy('id_jam')
            ->get();
    }

    /**
     * Label singkat untuk ditampilkan di dropdown.
     * Contoh: "Jam Ke-3" atau "Jam Ke-3 (11-12)".
     */
    public function getLabelAttribute(): string
    {
        return $this->nama_jam;
    }

    /**
     * Label kelompok yang ramah pengguna.
     */
    public function getKelompokLabelAttribute(): string
    {
        return match ($this->kelompok_jam) {
            self::KELOMPOK_REGULER      => 'Reguler – Kelas 10 (Senin–Kamis)',
            self::KELOMPOK_REGULER_1112 => 'Reguler – Kelas 11 & 12 (Senin–Kamis)',
            self::KELOMPOK_JUMAT        => 'Jumat – Semua Kelas',
            default                     => ucfirst($this->kelompok_jam),
        };
    }
}

