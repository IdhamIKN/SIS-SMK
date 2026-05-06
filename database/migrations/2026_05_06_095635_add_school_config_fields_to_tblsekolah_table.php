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
        Schema::table('tblsekolah', function (Blueprint $table) {
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->json('hari_efektif')->nullable(); // Array of days like ["Senin", "Selasa", etc.]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tblsekolah', function (Blueprint $table) {
            $table->dropColumn(['jam_masuk', 'jam_pulang', 'hari_efektif']);
        });
    }
};
