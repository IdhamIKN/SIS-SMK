<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('path');                         // path relatif dari storage/app/public
            $table->string('original_name')->nullable();    // nama file asli
            $table->unsignedTinyInteger('urutan')->default(0); // urutan tampil
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->index('event_id');
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_photos');
    }
};
