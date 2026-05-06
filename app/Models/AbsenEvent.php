<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenEvent extends Model
{
    use HasFactory;

    protected $table = 'absen_event';

    protected $fillable = [
        'event_id',
        'siswa_id',
        'jenis',
        'waktu_scan',
        'barcode_digunakan',
        'wa_terkirim_ortu',
        'created_by',
    ];

    protected $casts = [
        'waktu_scan' => 'datetime',
        'wa_terkirim_ortu' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
