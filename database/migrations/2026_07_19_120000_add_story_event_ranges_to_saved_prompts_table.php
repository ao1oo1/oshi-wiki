<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('saved_prompts')
            || Schema::hasColumn(
                'saved_prompts',
                'selected_story_event_ranges'
            )
        ) {
            return;
        }

        Schema::table(
            'saved_prompts',
            function (Blueprint $table): void {
                $table->json('selected_story_event_ranges')
                    ->nullable()
                    ->after('work_story_section_id');
            }
        );
    }

    public function down(): void
    {
        if (
            Schema::hasTable('saved_prompts')
            && Schema::hasColumn(
                'saved_prompts',
                'selected_story_event_ranges'
            )
        ) {
            Schema::table(
                'saved_prompts',
                function (Blueprint $table): void {
                    $table->dropColumn(
                        'selected_story_event_ranges'
                    );
                }
            );
        }
    }
};
