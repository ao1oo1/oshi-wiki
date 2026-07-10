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

        Schema::table('saved_prompts', function (Blueprint $table) {
            if (! Schema::hasColumn('saved_prompts', 'work_source')) {
                $table->string('work_source', 30)->default('original');
            }

            if (! Schema::hasColumn('saved_prompts', 'work_id')) {
                $table->foreignId('work_id')
                    ->nullable()
                    ->constrained('works')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('saved_prompts', 'selected_character_refs')) {
                $table->json('selected_character_refs')->nullable();
            }

            if (! Schema::hasColumn('saved_prompts', 'writing_style')) {
                $table->string('writing_style', 50)->nullable();
            }

            if (! Schema::hasColumn('saved_prompts', 'writing_style_other')) {
                $table->string('writing_style_other')->nullable();
            }

            if (! Schema::hasColumn('saved_prompts', 'genre')) {
                $table->string('genre', 50)->nullable();
            }

            if (! Schema::hasColumn('saved_prompts', 'genre_other')) {
                $table->string('genre_other')->nullable();
            }

            if (! Schema::hasColumn('saved_prompts', 'plot_opening')) {
                $table->text('plot_opening')->nullable();
            }

            if (! Schema::hasColumn('saved_prompts', 'plot_development')) {
                $table->text('plot_development')->nullable();
            }

            if (! Schema::hasColumn('saved_prompts', 'plot_turn')) {
                $table->text('plot_turn')->nullable();
            }

            if (! Schema::hasColumn('saved_prompts', 'plot_conclusion')) {
                $table->text('plot_conclusion')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('saved_prompts')) {
            return;
        }

        Schema::table('saved_prompts', function (Blueprint $table) {
            foreach ([
                'work_source',
                'work_id',
                'selected_character_refs',
                'writing_style',
                'writing_style_other',
                'genre',
                'genre_other',
                'plot_opening',
                'plot_development',
                'plot_turn',
                'plot_conclusion',
            ] as $column) {
                if (Schema::hasColumn('saved_prompts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
