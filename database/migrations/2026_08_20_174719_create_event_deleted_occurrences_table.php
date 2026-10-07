<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tombstone table untuk event turunan yang sudah dihapus.
 *
 * Tujuan: saat event master di-edit dan recurrence di-sync ulang,
 * tanggal yang pernah ada dan sudah dihapus user TIDAK akan di-generate ulang.
 *
 * Record dibuat otomatis saat child event (recurrence_parent_id IS NOT NULL) dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_deleted_occurrences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_event_id')->comment('ID event master (recurrence root)');
            $table->date('occurrence_date')->comment('Tanggal occurrence yang sudah dihapus (Y-m-d)');
            $table->unsignedBigInteger('deleted_event_id')->nullable()->comment('ID event yang dihapus (referensi historis)');
            $table->timestamp('deleted_at')->useCurrent()->comment('Kapan dihapus');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('User yang menghapus');

            $table->unique(['master_event_id', 'occurrence_date'], 'uniq_master_date');
            $table->index('master_event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_deleted_occurrences');
    }
};
