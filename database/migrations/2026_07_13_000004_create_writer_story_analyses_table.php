<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('writer_story_analyses')) {
            return;
        }

        Schema::create(
            'writer_story_analyses',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('title');

                /*
                 * 実際に選択したストーリーID。
                 */
                $table->json('selected_story_ids');

                /*
                 * 保存時点のタイトル・話数を保持するスナップショット。
                 * 元のストーリーを編集・削除しても、
                 * 何を分析した結果か確認できる。
                 */
                $table->json('story_snapshot');

                $table->text('analysis_notes')->nullable();

                /*
                 * 選択ストーリーから生成した、
                 * AIへ貼り付ける分析用プロンプト。
                 */
                $table->longText('analysis_prompt');

                /*
                 * AIが回答した文体分析の結論。
                 */
                $table->longText('analysis_result');

                $table->timestamps();
                $table->softDeletes();

                $table->index([
                    'user_id',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('writer_story_analyses');
    }
};
