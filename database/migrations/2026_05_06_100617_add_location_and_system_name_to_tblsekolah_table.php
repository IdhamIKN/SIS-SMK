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
            $table->decimal('latitude', 10, 8)->nullable()->after('hari_efektif');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->string('system_name')->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('tblsekolah', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'system_name']);
        });
    }
};
