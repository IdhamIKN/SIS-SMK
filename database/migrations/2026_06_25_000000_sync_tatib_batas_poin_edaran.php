<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tblbataspoin')) {
            return;
        }

        $data = [
            'tindakan1' => 'Panggilan Orang Tua ke-1',
            'poin1' => 100,
            'sanksi1' => 'Sisa poin 200',
            'tindakan2' => 'Panggilan Orang Tua ke-2',
            'poin2' => 200,
            'sanksi2' => 'Sisa poin 100',
            'tindakan3' => 'Panggilan Orang Tua ke-3',
            'poin3' => 275,
            'sanksi3' => 'Sisa poin 25',
            'tindakan4' => 'Point 0',
            'poin4' => 300,
            'sanksi4' => 'Tidak naik kelas / dikembalikan kepada orang tua',
            'tindakan5' => '0',
            'poin5' => 0,
            'sanksi5' => '0',
        ];

        $query = DB::table('tblbataspoin');

        if ((clone $query)->exists()) {
            $query->update($data);

            return;
        }

        $query->insert($data);
    }

    public function down(): void
    {
        // Data ambang lama dapat berbeda per sekolah, jadi rollback tidak menebak nilai sebelumnya.
    }
};
