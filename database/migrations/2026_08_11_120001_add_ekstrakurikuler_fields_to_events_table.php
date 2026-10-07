<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // ── Ekstrakurikuler flag ──
            $table->boolean('is_ekstrakurikuler')
                ->default(false)
                ->after('auto_penghargaan_processed_at')
                ->comment('Tandai event sebagai Ekstrakurikuler');

            // ── Pelatih (opsional, maks 3) ──
            $table->string('pelatih_1')->nullable()->after('is_ekstrakurikuler');
            $table->string('pelatih_2')->nullable()->after('pelatih_1');
            $table->string('pelatih_3')->nullable()->after('pelatih_2');

            // ── Pembina ──
            $table->string('pembina_nama')->nullable()->after('pelatih_3');
            $table->string('pembina_nip')->nullable()->after('pembina_nama');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'is_ekstrakurikuler',
                'pelatih_1',
                'pelatih_2',
                'pelatih_3',
                'pembina_nama',
                'pembina_nip',
            ]);
        });
    }
};
