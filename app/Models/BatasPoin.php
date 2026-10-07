<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatasPoin extends Model
{
    use HasFactory;

    protected $table = 'tblbataspoin';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    public function thresholds(): array
    {
        $thresholds = [];

        for ($i = 1; $i <= 5; $i++) {
            $poin = (int) ($this->{"poin{$i}"} ?? 0);

            if ($poin <= 0) {
                continue;
            }

            $thresholds[] = [
                'batas_ke' => $i,
                'poin' => $poin,
                'tindakan' => $this->{"tindakan{$i}"} ?: null,
                'sanksi' => $this->{"sanksi{$i}"} ?: null,
            ];
        }

        return $thresholds;
    }
}
