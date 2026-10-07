<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenActivityLog extends Model
{
    protected $table = 'absen_activity_log';

    protected $fillable = [
        'aksi',
        'absen_siswa_id',
        'izin_id',
        'siswa_id',
        'dilakukan_oleh',
        'data_lama',
        'data_baru',
        'catatan',
    ];

    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
    ];

    // Label aksi untuk tampilan UI
    public function getAksiLabelAttribute(): string
    {
        return match ($this->aksi) {
            'tambah_manual'  => 'Tambah Manual',
            'edit_manual'    => 'Edit Manual',
            'buat_izin'      => 'Buat Izin (Admin)',
            'setujui_izin'   => 'Setujui Izin',
            'tolak_izin'     => 'Tolak Izin',
            default          => ucfirst(str_replace('_', ' ', $this->aksi)),
        };
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function dilakukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }

    public function absenSiswa(): BelongsTo
    {
        return $this->belongsTo(AbsenSiswa::class, 'absen_siswa_id');
    }

    public function izin(): BelongsTo
    {
        return $this->belongsTo(PengajuanIzin::class, 'izin_id');
    }
}
