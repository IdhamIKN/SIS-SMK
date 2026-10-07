<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('auto_penghargaan')
                ->default(false)
                ->after('auto_point_processed_at')
                ->comment('Jika true, siswa yang scan masuk otomatis diberi poin penghargaan setelah event selesai');

            $table->string('pasal_penghargaan_id', 5)
                ->nullable()
                ->after('auto_penghargaan')
                ->comment('FK ke tblsubpasal.idpasal — pasal acuan pemberian poin penghargaan otomatis');

            $table->timestamp('auto_penghargaan_processed_at')
                ->nullable()
                ->after('pasal_penghargaan_id')
                ->comment('Timestamp saat auto-penghargaan sudah diproses (mencegah duplikasi)');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'auto_penghargaan',
                'pasal_penghargaan_id',
                'auto_penghargaan_processed_at',
            ]);
        });
    }
};
