<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->timestamps();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('event_category_id')
                ->nullable()
                ->after('created_by')
                ->constrained('event_categories')
                ->nullOnDelete();
            $table->foreignId('recurrence_parent_id')
                ->nullable()
                ->after('idevent_legacy')
                ->constrained('events')
                ->nullOnDelete();
            $table->unsignedInteger('recurrence_sequence')
                ->default(1)
                ->after('recurrence_parent_id');

            $table->index(['recurrence_parent_id', 'recurrence_sequence']);
        });

        Schema::create('event_recurrence_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly', 'custom']);
            $table->unsignedSmallInteger('interval')->default(1);
            $table->json('days_of_week')->nullable();
            $table->date('repeat_until')->nullable();
            $table->unsignedSmallInteger('occurrence_count')->nullable();
            $table->timestamps();

            $table->unique('event_id');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('recurrence_rule_id')
                ->nullable()
                ->after('recurrence_parent_id')
                ->constrained('event_recurrence_rules')
                ->nullOnDelete();
            $table->index('recurrence_rule_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['recurrence_rule_id']);
            $table->dropIndex(['recurrence_rule_id']);
            $table->dropColumn('recurrence_rule_id');
        });

        Schema::dropIfExists('event_recurrence_rules');

        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['event_category_id']);
            $table->dropForeign(['recurrence_parent_id']);
            $table->dropIndex(['recurrence_parent_id', 'recurrence_sequence']);
            $table->dropColumn([
                'event_category_id',
                'recurrence_parent_id',
                'recurrence_sequence',
            ]);
        });

        Schema::dropIfExists('event_categories');
    }
};
