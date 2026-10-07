<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_panggilan', function (Blueprint $table) {
            $table->timestamp('wa_sent_at')->nullable()->after('dibuat_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('surat_panggilan', function (Blueprint $table) {
            $table->dropColumn('wa_sent_at');
        });
    }
};
