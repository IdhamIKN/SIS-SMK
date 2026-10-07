<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubPasal extends Model
{
    use HasFactory;

    protected $table = 'tblsubpasal';

    protected $primaryKey = 'idpasal';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'idpasal',
        'pasal',
        'skormin',
        'skormax',
        'thnajaran',
    ];

    protected $casts = [
        'skormin' => 'integer',
        'skormax' => 'integer',
    ];

    public function scopePelanggaran($query)
    {
        return $query->where('idpasal', 'like', 'J%');
    }

    public function scopePenghargaan($query)
    {
        return $query->where('idpasal', 'not like', 'J%');
    }

    public function scopeTahunAjaran($query, string $tahunAjaran)
    {
        return $query->where('thnajaran', $tahunAjaran);
    }

    public function getPoinDefaultAttribute(): int
    {
        return (int) ($this->skormax ?: $this->skormin);
    }
}
