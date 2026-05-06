<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyJurusan extends Model
{
    protected $table = 'tbljurusan';

    protected $primaryKey = 'idkom';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'idkom', 'idpro', 'kompetensi',
    ];

    // Relationship dengan Jurusan baru
    public function jurusanBaru()
    {
        return $this->hasOne(Jurusan::class, 'program_id', 'idkom');
    }
}
