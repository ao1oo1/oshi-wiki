<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const UNIQUE_INDEX =
        'saved_prompt_ai_results_saved_prompt_unique';

    public function up(): void
    {
        if (! Schema::hasTable('saved_prompt_ai_results')) {
            return;
        }

        /*
         * 既存データに同じプロンプトの回答が複数ある場合は、
         * 最新の1件だけを残して整理する。
         *
         * 論理削除済みデータも一意制約の対象になるため、
         * 同じsaved_prompt_idのレコードは全体で1件に統一する。
         */
        $duplicatedPromptIds = DB::table(
            'saved_prompt_ai_results'
        )
            ->select('saved_prompt_id')
            ->groupBy('saved_prompt_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('saved_prompt_id');

        foreach ($duplicatedPromptIds as $savedPromptId) {
            $keepId = DB::table('saved_prompt_ai_results')
                ->where('saved_prompt_id', $savedPromptId)
                ->max('id');

            DB::table('saved_prompt_ai_results')
                ->where('saved_prompt_id', $savedPromptId)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table(
            'saved_prompt_ai_results',
            function (Blueprint $table): void {
                $table->unique(
                    'saved_prompt_id',
                    self::UNIQUE_INDEX
                );
            }
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('saved_prompt_ai_results')) {
            return;
        }

        Schema::table(
            'saved_prompt_ai_results',
            function (Blueprint $table): void {
                $table->dropUnique(self::UNIQUE_INDEX);
            }
        );
    }
};
