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
            $table->integer('noreg_legacy')->nullable()->after('status_aktif');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->after('noreg_legacy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['noreg_legacy', 'user_id']);
        });
    }
};
