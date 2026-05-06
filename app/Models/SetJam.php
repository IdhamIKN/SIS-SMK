<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetJam extends Model
{
    protected $table = 'tblsetjam';

    protected $primaryKey = 'id_jam';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id_jam', 'shif', 'nama_jam', 'time_in', 'limit_in',
        'time_out', 'limit_out', 'statusjam',
    ];

    protected $casts = [
        'time_in' => 'datetime:H:i:s',
        'limit_in' => 'datetime:H:i:s',
        'time_out' => 'datetime:H:i:s',
        'limit_out' => 'datetime:H:i:s',
        'statusjam' => 'boolean',
    ];

    // Method untuk mendapatkan jam berdasarkan shift
    public static function getJamByShift(string $shift = 'Pagi')
    {
        return static::where('shif', $shift)->where('statusjam', 1)->first();
    }

    // Method untuk mendapatkan semua jam aktif
    public static function getJamAktif()
    {
        return static::where('statusjam', 1)->get();
    }
}
