<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('saved_prompt_ai_results')) {
            return;
        }

        Schema::create(
            'saved_prompt_ai_results',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('saved_prompt_id')
                    ->constrained('saved_prompts')
                    ->cascadeOnDelete();

                /*
                 * AI回答を識別するための管理名。
                 */
                $table->string('title');

                /*
                 * AIへ渡した生成プロンプトの保存時点の内容。
                 * 元のプロンプトを編集しても履歴を確認できる。
                 */
                $table->longText('prompt_snapshot');

                /*
                 * AIが返したプロット・構成案・執筆用データなど。
                 */
                $table->longText('result_body');

                $table->timestamps();
                $table->softDeletes();

                $table->index([
                    'user_id',
                    'saved_prompt_id',
                    'created_at',
                ], 'sp_ai_results_user_prompt_created_index');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_prompt_ai_results');
    }
};
