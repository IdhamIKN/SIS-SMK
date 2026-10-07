<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbsenEventGuru extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'absen_event_gurus';

    protected $fillable = [
        'event_guru_id',
        'gtk_id',
        'jenis',
        'waktu_scan',
        'barcode_digunakan',
        'created_by',
    ];

    protected $casts = [
        'waktu_scan' => 'datetime',
    ];

    public function eventGuru(): BelongsTo
    {
        return $this->belongsTo(EventGuru::class, 'event_guru_id');
    }

    public function gtk(): BelongsTo
    {
        return $this->belongsTo(GTK::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
