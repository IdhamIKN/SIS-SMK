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
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20); // "2025/2026"
            $table->year('year_start'); // 2025
            $table->year('year_end'); // 2026
            $table->boolean('is_active')->default(false);
            $table->date('promotion_deadline')->nullable();
            $table->json('promotion_waves')->nullable(); // Track promosi per tingkat
            $table->timestamps();
        });

        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('from_class_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('to_class_id')->nullable()->constrained('kelas')->onDelete('set null');
            $table->enum('promotion_type', ['promoted', 'retained', 'graduated', 'transferred', 'dropout']);
            $table->text('reason')->nullable();
            $table->date('promotion_date');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Add academic_year_id to classes
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
            $table->enum('promotion_status', ['pending', 'ready', 'promoted', 'graduated'])->default('pending');
            $table->integer('promotion_wave')->nullable(); // Urutan promosi
        });

        // Add fields to students
        Schema::table('siswas', function (Blueprint $table) {
            $table->year('graduation_year')->nullable();
            $table->integer('retention_count')->default(0);
            $table->enum('academic_status', ['active', 'retained', 'graduated', 'transferred', 'dropout'])->default('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
        Schema::dropIfExists('academic_years');

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn(['academic_year_id', 'promotion_status', 'promotion_wave']);
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn(['graduation_year', 'retention_count', 'academic_status']);
        });
    }
};
