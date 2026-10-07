<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblPasal extends Model
{
    protected $table = 'tblpasal';

    protected $primaryKey = 'idpasal';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'urut',
        'idkategori',
        'idpasal',
        'status_aktif',
    ];

    public $timestamps = false;

    protected $casts = [
        'urut' => 'integer',
        'status_aktif' => 'boolean',
    ];

    public function kategori()
    {
        return $this->belongsTo(TblKategori::class, 'idkategori', 'idkategori');
    }

    public function subPasal()
    {
        return $this->hasMany(TblSubPasal::class, 'idpasal', 'idpasal');
    }
}

