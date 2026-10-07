<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblSubPasal extends Model
{
    protected $table = 'tblsubpasal';

    protected $primaryKey = 'idpasal';
    public $incrementing = false;

    protected $fillable = [
        'idpasal',
        'pasal',
        'skormin',
        'skormax',
        'thnajaran',
    ];

    public $timestamps = false;

    public function pasal()
    {
        return $this->belongsTo(TblPasal::class, 'idpasal', 'idpasal');
    }
}

