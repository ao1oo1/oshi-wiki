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
            if (! Schema::hasColumn('saved_prompts', 'use_story_length_options')) {
                $table->boolean('use_story_length_options')
                    ->default(false)
                    ->after('plot_conclusion');
            }

            if (! Schema::hasColumn('saved_prompts', 'story_length_type')) {
                $table->string('story_length_type')
                    ->nullable()
                    ->after('use_story_length_options');
            }

            if (! Schema::hasColumn('saved_prompts', 'output_plot_first')) {
                $table->boolean('output_plot_first')
                    ->default(true)
                    ->after('story_length_type');
            }

            if (! Schema::hasColumn('saved_prompts', 'output_in_parts')) {
                $table->boolean('output_in_parts')
                    ->default(true)
                    ->after('output_plot_first');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('saved_prompts')) {
            return;
        }

        Schema::table('saved_prompts', function (Blueprint $table): void {
            foreach ([
                'output_in_parts',
                'output_plot_first',
                'story_length_type',
                'use_story_length_options',
            ] as $column) {
                if (Schema::hasColumn('saved_prompts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
