<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('work_story_section_events')
            || Schema::hasColumn(
                'work_story_section_events',
                'appearing_characters'
            )
        ) {
            return;
        }

        Schema::table(
            'work_story_section_events',
            function (Blueprint $table): void {
                $table->text('appearing_characters')
                    ->nullable()
                    ->after('summary');
            }
        );
    }

    public function down(): void
    {
        if (
            ! Schema::hasTable('work_story_section_events')
            || ! Schema::hasColumn(
                'work_story_section_events',
                'appearing_characters'
            )
        ) {
            return;
        }

        Schema::table(
            'work_story_section_events',
            function (Blueprint $table): void {
                $table->dropColumn('appearing_characters');
            }
        );
    }
};
