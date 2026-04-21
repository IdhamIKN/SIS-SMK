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
        Schema::table('pengajuan_izin', function (Blueprint $table) {
            $table->date('tanggal_mulai')->nullable()->after('tanggal_izin');
            $table->date('tanggal_sampai')->nullable()->after('tanggal_mulai');
            $table->index(['tanggal_mulai', 'tanggal_sampai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_izin', function (Blueprint $table) {
            $table->dropIndex(['tanggal_mulai', 'tanggal_sampai']);
            $table->dropColumn(['tanggal_mulai', 'tanggal_sampai']);
        });
    }
};
