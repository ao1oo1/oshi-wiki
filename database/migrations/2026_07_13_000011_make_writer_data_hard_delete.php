<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Writer側のユーザー専用データを物理削除仕様へ変更する。
     */
    public function up(): void
    {
        /*
         * 論理削除済みのオリジナルキャラクターを参照する関係性は、
         * 参照切れを防ぐため先に物理削除する。
         */
        if (
            Schema::hasTable('original_characters')
            && Schema::hasTable('original_character_relationships')
            && Schema::hasColumn(
                'original_characters',
                'deleted_at'
            )
        ) {
            $deletedCharacterIds = DB::table(
                'original_characters'
            )
                ->whereNotNull('deleted_at')
                ->pluck('id');

            if ($deletedCharacterIds->isNotEmpty()) {
                DB::table('original_character_relationships')
                    ->whereIn(
                        'from_original_character_id',
                        $deletedCharacterIds
                    )
                    ->orWhereIn(
                        'to_original_character_id',
                        $deletedCharacterIds
                    )
                    ->delete();
            }
        }

        /*
         * 子データから先に、既存の論理削除済みレコードを消す。
         */
        $purgeOrder = [
            'saved_prompt_ai_results',
            'original_character_relationships',
            'writer_story_analyses',
            'writer_stories',
            'saved_prompts',
            'original_characters',
        ];

        foreach ($purgeOrder as $table) {
            if (
                Schema::hasTable($table)
                && Schema::hasColumn($table, 'deleted_at')
            ) {
                DB::table($table)
                    ->whereNotNull('deleted_at')
                    ->delete();
            }
        }

        /*
         * Writer側テーブルからdeleted_at列を削除する。
         */
        $tables = [
            'original_characters',
            'original_character_relationships',
            'saved_prompts',
            'saved_prompt_ai_results',
            'writer_stories',
            'writer_story_analyses',
        ];

        foreach ($tables as $table) {
            if (
                Schema::hasTable($table)
                && Schema::hasColumn($table, 'deleted_at')
            ) {
                Schema::table(
                    $table,
                    function (Blueprint $blueprint): void {
                        $blueprint->dropSoftDeletes();
                    }
                );
            }
        }
    }

    /**
     * ロールバック時は列だけ戻す。
     * 物理削除済みデータは復元できない。
     */
    public function down(): void
    {
        $tables = [
            'original_characters',
            'original_character_relationships',
            'saved_prompts',
            'saved_prompt_ai_results',
            'writer_stories',
            'writer_story_analyses',
        ];

        foreach ($tables as $table) {
            if (
                Schema::hasTable($table)
                && ! Schema::hasColumn($table, 'deleted_at')
            ) {
                Schema::table(
                    $table,
                    function (Blueprint $blueprint): void {
                        $blueprint->softDeletes();
                    }
                );
            }
        }
    }
};
