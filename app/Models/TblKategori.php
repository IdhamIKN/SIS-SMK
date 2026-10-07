<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblKategori extends Model
{
    protected $table = 'tblkategori';

    protected $fillable = [
        'idgroup',
        'idkategori',
        'kategori',
    ];

    public $timestamps = false;

    // relasi: tblkategori.idkategori -> tblpasal.idkategori
    public function pasal()
    {
        return $this->hasMany(TblPasal::class, 'idkategori', 'idkategori');
    }
}

