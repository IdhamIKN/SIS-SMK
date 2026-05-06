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
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('desa')->nullable()->after('alamat');
            $table->string('kelurahan')->nullable()->after('desa');
            $table->string('kecamatan')->nullable()->after('kelurahan');
            $table->string('kabupaten')->nullable()->after('kecamatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn(['desa', 'kelurahan', 'kecamatan', 'kabupaten']);
        });
    }
};
