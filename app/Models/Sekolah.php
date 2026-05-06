<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    protected $table = 'tblsekolah';

    protected $primaryKey = 'idsekolah';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false; // Disable timestamps for legacy table

    protected $fillable = [
        'idsekolah', 'sekolah', 'alsekolah', 'telp', 'email', 'kab',
        'disurat', 'alias', 'nama_ks', 'nip_ks', 'nama_waka', 'nip_waka',
        'nama_ketua', 'nip_ketua', 'site_url', 'site_logo', 'wasekolah',
        'jam_masuk', 'jam_pulang', 'hari_efektif', 'latitude', 'longitude', 'system_name',
    ];

    // Method untuk mendapatkan data sekolah aktif
    public static function aktif()
    {
        return static::first(); // Asumsi hanya ada satu sekolah
    }

    // Method untuk mendapatkan koordinat dari tblsekolah
    public function getKoordinat()
    {
        // Jika ada field koordinat di tabel, bisa ditambahkan
        // Untuk sekarang return default dari config
        return [
            'latitude' => config('sekolah.latitude', -7.6229526),
            'longitude' => config('sekolah.longitude', 111.5332185),
        ];
    }
}
