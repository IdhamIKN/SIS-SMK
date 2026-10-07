<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Poin yang digunakan untuk auto pelanggaran — null berarti pakai poin_default (skormax)
            $table->unsignedSmallInteger('poin_pelanggaran_event')
                ->nullable()
                ->after('pasal_pelanggaran_id')
                ->comment('Override poin untuk auto pelanggaran; null = pakai skormax pasal');

            // Poin yang digunakan untuk auto penghargaan — null berarti pakai poin_default (skormax)
            $table->unsignedSmallInteger('poin_penghargaan_event')
                ->nullable()
                ->after('pasal_penghargaan_id')
                ->comment('Override poin untuk auto penghargaan; null = pakai skormax pasal');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['poin_pelanggaran_event', 'poin_penghargaan_event']);
        });
    }
};
