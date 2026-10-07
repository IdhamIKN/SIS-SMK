<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EventCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kategori',
        'slug',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public static function findOrCreateByName(?string $name): ?self
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        $slug = Str::slug($name);

        if ($slug === '') {
            $slug = Str::lower(Str::random(12));
        }

        return self::firstOrCreate(
            ['slug' => $slug],
            ['nama_kategori' => $name]
        );
    }
}
