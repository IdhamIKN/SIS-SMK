// database/migrations/xxxx_add_wa_sent_at_to_surat_panggilan_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_panggilan', function (Blueprint $table) {
            $table->timestamp('wa_sent_at')->nullable()->after('dengan_materai');
            $table->string('wa_nomor_tujuan', 20)->nullable()->after('wa_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('surat_panggilan', function (Blueprint $table) {
            $table->dropColumn(['wa_sent_at', 'wa_nomor_tujuan']);
        });
    }
};