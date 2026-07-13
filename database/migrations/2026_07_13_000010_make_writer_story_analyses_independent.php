<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
                    'analysis_notes'
                )) {
                    $table->text('analysis_notes')
                        ->nullable()
                        ->after('selected_story_ids');
                }

                if (! Schema::hasColumn(
                    'writer_story_analyses',
                    'analysis_prompt'
                )) {
                    $table->longText('analysis_prompt')
                        ->nullable()
                        ->after('analysis_notes');
                }
            }
        );

        if (
            Schema::hasColumn(
                'writer_story_analyses',
                'saved_prompt_id'
            )
            && Schema::hasTable('saved_prompts')
        ) {
            DB::table('writer_story_analyses')
                ->whereNotNull('saved_prompt_id')
                ->whereNull('analysis_prompt')
                ->orderBy('id')
                ->chunkById(
                    100,
                    function ($analyses): void {
                        foreach ($analyses as $analysis) {
                            $prompt = DB::table('saved_prompts')
                                ->where(
                                    'id',
                                    $analysis->saved_prompt_id
                                )
                                ->value('prompt_body');

                            if ($prompt !== null) {
                                DB::table('writer_story_analyses')
                                    ->where('id', $analysis->id)
                                    ->update([
                                        'analysis_prompt' => $prompt,
                                    ]);
                            }
                        }
                    }
                );
        }

        DB::table('writer_story_analyses')
            ->whereNull('analysis_prompt')
            ->update([
                'analysis_prompt' =>
                    '保存元の分析用プロンプトを取得できませんでした。',
            ]);

        Schema::table(
            'writer_story_analyses',
            function (Blueprint $table): void {
                $table->longText('analysis_prompt')
                    ->nullable(false)
                    ->change();

                $table->longText('analysis_result')
                    ->nullable()
                    ->change();
            }
        );

        if (Schema::hasColumn(
            'writer_story_analyses',
            'saved_prompt_id'
        )) {
            Schema::table(
                'writer_story_analyses',
                function (Blueprint $table): void {
                    $table->dropConstrainedForeignId(
                        'saved_prompt_id'
                    );
                }
            );
        }
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
    }
};
