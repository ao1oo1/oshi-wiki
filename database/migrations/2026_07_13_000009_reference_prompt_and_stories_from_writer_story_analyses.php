<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('writer_story_analyses')) {
            return;
        }

        Schema::table(
            'writer_story_analyses',
            function (Blueprint $table): void {
                if (! Schema::hasColumn(
                    'writer_story_analyses',
                    'saved_prompt_id'
                )) {
                    $table->foreignId('saved_prompt_id')
                        ->nullable()
                        ->after('user_id')
                        ->constrained('saved_prompts')
                        ->nullOnDelete();
                }
            }
        );

        Schema::table(
            'writer_story_analyses',
            function (Blueprint $table): void {
                $columns = array_values(array_filter([
                    Schema::hasColumn(
                        'writer_story_analyses',
                        'story_snapshot'
                    ) ? 'story_snapshot' : null,
                    Schema::hasColumn(
                        'writer_story_analyses',
                        'analysis_notes'
                    ) ? 'analysis_notes' : null,
                    Schema::hasColumn(
                        'writer_story_analyses',
                        'analysis_prompt'
                    ) ? 'analysis_prompt' : null,
                ]));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            }
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('writer_story_analyses')) {
            return;
        }

        Schema::table(
            'writer_story_analyses',
            function (Blueprint $table): void {
                if (! Schema::hasColumn(
                    'writer_story_analyses',
                    'story_snapshot'
                )) {
                    $table->json('story_snapshot')->nullable();
                }

                if (! Schema::hasColumn(
                    'writer_story_analyses',
                    'analysis_notes'
                )) {
                    $table->text('analysis_notes')->nullable();
                }

                if (! Schema::hasColumn(
                    'writer_story_analyses',
                    'analysis_prompt'
                )) {
                    $table->longText('analysis_prompt')->nullable();
                }
            }
        );

        Schema::table(
            'writer_story_analyses',
            function (Blueprint $table): void {
                if (Schema::hasColumn(
                    'writer_story_analyses',
                    'saved_prompt_id'
                )) {
                    $table->dropConstrainedForeignId(
                        'saved_prompt_id'
                    );
                }
            }
        );
    }
};
