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
        // Buat tabel pivot untuk relasi many-to-many antara GTK dan MataPelajaran
        Schema::create('gtk_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gtk_id')->constrained('gtks')->onDelete('cascade');
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['gtk_id', 'mata_pelajaran_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gtk_mata_pelajaran');
    }
};