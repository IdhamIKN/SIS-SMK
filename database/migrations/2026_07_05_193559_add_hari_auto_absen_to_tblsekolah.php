<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblsekolah', function (Blueprint $table) {
            if (! Schema::hasColumn('tblsekolah', 'hari_auto_absen')) {
                $table->text('hari_auto_absen')->nullable()->after('hari_khusus')
                    ->comment('JSON array hari yang absen otomatis dijalankan, default Senin-Jumat');
            }
        });

        // Set default: Senin s.d. Jumat untuk semua baris yang sudah ada
        DB::table('tblsekolah')->whereNull('hari_auto_absen')->update([
            'hari_auto_absen' => json_encode(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']),
        ]);
    }

    public function down(): void
    {
        Schema::table('tblsekolah', function (Blueprint $table) {
            $table->dropColumn('hari_auto_absen');
        });
    }
};
