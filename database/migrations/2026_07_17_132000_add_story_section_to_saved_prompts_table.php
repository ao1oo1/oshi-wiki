<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'saved_prompts',
            function (Blueprint $table): void {
                if (! Schema::hasColumn(
                    'saved_prompts',
                    'work_story_section_id'
                )) {
                    $table->foreignId('work_story_section_id')
                        ->nullable()
                        ->after('work_id')
                        ->constrained('work_story_sections')
                        ->nullOnDelete();
                }
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'saved_prompts',
            function (Blueprint $table): void {
                if (Schema::hasColumn(
                    'saved_prompts',
                    'work_story_section_id'
                )) {
                    $table->dropConstrainedForeignId(
                        'work_story_section_id'
                    );
                }
            }
        );
    }
};
