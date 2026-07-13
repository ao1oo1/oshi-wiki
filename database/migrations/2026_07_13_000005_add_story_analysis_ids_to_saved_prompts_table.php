<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('saved_prompts')) {
            return;
        }

        Schema::table('saved_prompts', function (Blueprint $table): void {
            if (
                ! Schema::hasColumn(
                    'saved_prompts',
                    'selected_story_analysis_ids'
                )
            ) {
                $table->json('selected_story_analysis_ids')
                    ->nullable()
                    ->after('output_in_parts');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('saved_prompts')) {
            return;
        }

        Schema::table('saved_prompts', function (Blueprint $table): void {
            if (
                Schema::hasColumn(
                    'saved_prompts',
                    'selected_story_analysis_ids'
                )
            ) {
                $table->dropColumn(
                    'selected_story_analysis_ids'
                );
            }
        });
    }
};
