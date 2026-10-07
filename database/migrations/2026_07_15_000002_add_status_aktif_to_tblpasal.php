<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tblpasal', 'status_aktif')) {
            Schema::table('tblpasal', function (Blueprint $table) {
                $table->boolean('status_aktif')->default(true)->after('idpasal');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tblpasal', 'status_aktif')) {
            Schema::table('tblpasal', function (Blueprint $table) {
                $table->dropColumn('status_aktif');
            });
        }
    }
};
