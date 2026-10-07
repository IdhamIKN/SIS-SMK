<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jadwal_kbm', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_kbm', 'mata_pelajaran_id')) {
                $table->foreignId('mata_pelajaran_id')->nullable()->constrained('mata_pelajaran')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_kbm', function (Blueprint $table) {
            $table->dropForeign(['mata_pelajaran_id']);
            $table->dropColumn('mata_pelajaran_id');
        });
    }
};
