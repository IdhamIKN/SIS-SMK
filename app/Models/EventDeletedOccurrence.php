<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tombstone untuk event turunan yang dihapus.
 * Mencegah recurrence sync dari membuat ulang tanggal yang sudah dihapus user.
 */
class EventDeletedOccurrence extends Model
{
    public $timestamps = false;

    protected $table = 'event_deleted_occurrences';

    protected $fillable = [
        'master_event_id',
        'occurrence_date',
        'deleted_event_id',
        'deleted_at',
        'deleted_by',
    ];

    protected $casts = [
        'occurrence_date' => 'date',
        'deleted_at'      => 'datetime',
    ];
}
