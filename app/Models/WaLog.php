<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_tujuan',
        'pesan',
        'jenis',
        'status',
        'referensi_id',
        'referensi_tipe',
        'wa_mode',
        'dikirim_at',
    ];

    protected $casts = [
        'dikirim_at' => 'datetime',
    ];
}
