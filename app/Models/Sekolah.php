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
        'jam_masuk', 'jam_pulang', 'jam_masuk_khusus', 'jam_pulang_khusus',
        'hari_efektif', 'hari_khusus', 'hari_auto_absen',
        'latitude', 'longitude', 'radius_meter',
        'jam_mulai_absensi', 'batas_tepat_waktu',
        'system_name',
        // Notifikasi WA
        'wa_notif_masuk_enabled',
        'wa_notif_pulang_enabled',
        'wa_notif_event_enabled',
        'wa_notif_tatib_enabled',
        'wa_notif_laporan_guru_enabled',
        'wa_notif_laporan_guru_nomor',
        // Auto Alfa
        'auto_alfa_enabled',
        'jam_eksekusi_auto_alfa',
        'auto_point_alfa_enabled',
        'pasal_alfa_id',
        'wa_notif_alfa_enabled',
        // Auto Poin Hadir & Terlambat
        'auto_poin_hadir_enabled',
        'pasal_hadir_id',
        'auto_poin_terlambat_enabled',
        'pasal_terlambat_id',
        // Batas absen
        'batas_absen_masuk',
        // Mode Libur Panjang
        'libur_mode',
        'libur_dari',
        'libur_sampai',
    ];

    protected $casts = [
        'wa_notif_masuk_enabled'          => 'boolean',
        'wa_notif_pulang_enabled'         => 'boolean',
        'wa_notif_event_enabled'          => 'boolean',
        'wa_notif_tatib_enabled'          => 'boolean',
        'wa_notif_laporan_guru_enabled'   => 'boolean',
        'wa_notif_laporan_guru_nomor'     => 'array',
        'auto_alfa_enabled'               => 'boolean',
        'auto_point_alfa_enabled'         => 'boolean',
        'wa_notif_alfa_enabled'           => 'boolean',
        'auto_poin_hadir_enabled'         => 'boolean',
        'auto_poin_terlambat_enabled'     => 'boolean',
        'libur_mode'                      => 'boolean',
        'libur_dari'                      => 'date',
        'libur_sampai'                    => 'date',
    ];

    // Method untuk mendapatkan data sekolah aktif
    public static function aktif()
    {
        return static::first(); // Asumsi hanya ada satu sekolah
    }

    /**
     * Cek apakah sistem sedang dalam mode libur panjang pada tanggal tertentu.
     *
     * @param  \Carbon\Carbon|string|null  $tanggal  default: hari ini
     */
    public function sedangLibur($tanggal = null): bool
    {
        if (! $this->libur_mode) {
            return false;
        }

        if (! $this->libur_dari || ! $this->libur_sampai) {
            return false;
        }

        $hari = \Illuminate\Support\Carbon::parse($tanggal ?? now())->startOfDay();

        return $hari->between(
            \Illuminate\Support\Carbon::parse($this->libur_dari)->startOfDay(),
            \Illuminate\Support\Carbon::parse($this->libur_sampai)->endOfDay()
        );
    }

    // Method untuk mendapatkan koordinat dari tblsekolah
    public function getKoordinat()
    {
        return [
            'latitude' => $this->latitude ?: config('sekolah.latitude', -7.6229526),
            'longitude' => $this->longitude ?: config('sekolah.longitude', 111.5332185),
        ];
    }
}
