<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->decimal('lat', 10, 8)->nullable()->after('lokasi');
            $table->decimal('lng', 11, 8)->nullable()->after('lat');
            $table->unsignedInteger('radius_meter')->default(100)->after('lng');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'radius_meter']);
        });
    }
};
