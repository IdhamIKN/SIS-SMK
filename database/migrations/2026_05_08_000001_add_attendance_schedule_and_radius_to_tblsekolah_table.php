<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tblsekolah', 'jam_masuk_khusus')) {
            Schema::table('tblsekolah', function (Blueprint $table) {
                $table->time('jam_masuk_khusus')->nullable()->after('jam_pulang');
            });
        }

        if (! Schema::hasColumn('tblsekolah', 'jam_pulang_khusus')) {
            Schema::table('tblsekolah', function (Blueprint $table) {
                $table->time('jam_pulang_khusus')->nullable()->after('jam_masuk_khusus');
            });
        }

        if (! Schema::hasColumn('tblsekolah', 'hari_khusus')) {
            Schema::table('tblsekolah', function (Blueprint $table) {
                $table->json('hari_khusus')->nullable()->after('hari_efektif');
            });
        }

        if (! Schema::hasColumn('tblsekolah', 'radius_meter')) {
            Schema::table('tblsekolah', function (Blueprint $table) {
                $table->unsignedInteger('radius_meter')->default(100)->after('longitude');
            });
        }
    }

    public function down(): void
    {
        Schema::table('tblsekolah', function (Blueprint $table) {
            $columns = [
                'jam_masuk_khusus',
                'jam_pulang_khusus',
                'hari_khusus',
            ];

            $existingColumns = array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn('tblsekolah', $column)
            );

            if ($existingColumns !== []) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
