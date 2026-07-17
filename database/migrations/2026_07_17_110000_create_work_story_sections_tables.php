<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_story_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('parent_section_id')
                ->nullable()
                ->constrained('work_story_sections')
                ->restrictOnDelete();
            $table->string('section_type', 30)->default('chapter');
            $table->unsignedSmallInteger('section_number')->nullable();
            $table->string('title', 255);
            $table->string('title_kana', 255)->nullable();
            $table->string('short_label', 100)->nullable();
            $table->text('synopsis')->nullable();
            $table->longText('cumulative_settings')->nullable();
            $table->text('notes')->nullable();
            $table->string('spoiler_level', 30)->default('none');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['work_id', 'sort_order']);
            $table->index(['work_id', 'status']);
        });

        Schema::create('work_story_section_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_story_section_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('event_number')->nullable();
            $table->string('title', 255);
            $table->string('timing', 255)->nullable();
            $table->longText('summary')->nullable();
            $table->string('location', 255)->nullable();
            $table->longText('outcome')->nullable();
            $table->string('spoiler_level', 30)->default('none');
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index([
                'work_story_section_id',
                'sort_order',
            ], 'story_section_events_sort_index');
        });

        Schema::create('character_work_story_section', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_story_section_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('character_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('appearance_type', 30)->default('appears');
            $table->string('age_at_section', 100)->nullable();
            $table->string('school_grade_at_section', 100)->nullable();
            $table->string('class_at_section', 100)->nullable();
            $table->string('affiliation_at_section', 255)->nullable();
            $table->string('position_at_section', 255)->nullable();
            $table->text('character_state')->nullable();
            $table->boolean('first_appearance')->default(false);
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(
                ['work_story_section_id', 'character_id'],
                'story_section_character_unique'
            );
            $table->index(
                ['work_story_section_id', 'sort_order'],
                'story_section_character_sort_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_work_story_section');
        Schema::dropIfExists('work_story_section_events');
        Schema::dropIfExists('work_story_sections');
    }
};
