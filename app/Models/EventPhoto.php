<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EventPhoto extends Model
{
    protected $table = 'event_photos';

    protected $fillable = [
        'event_id',
        'path',
        'original_name',
        'urutan',
        'uploaded_by',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * URL publik foto
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    /**
     * Path absolut di filesystem
     */
    public function getAbsolutePathAttribute(): string
    {
        return Storage::path($this->path);
    }
}
