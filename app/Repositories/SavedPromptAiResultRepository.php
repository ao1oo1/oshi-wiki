<?php

namespace App\Repositories;

use App\Models\SavedPromptAiResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class SavedPromptAiResultRepository
{
    /**
     * 詳細画面との互換性を保つためCollectionで返す。
     * 1プロンプトにつき最大1件のみ。
     */
    public function latestForPrompt(
        User $user,
        int $savedPromptId,
        int $limit = 1
    ): Collection {
        return SavedPromptAiResult::query()
            ->forUser($user)
            ->where('saved_prompt_id', $savedPromptId)
            ->latest()
            ->limit(1)
            ->get();
    }

    /**
     * 対象プロンプトに保存されたAI結果を取得する。
     */
    public function findForPrompt(
        User $user,
        int $savedPromptId
    ): ?SavedPromptAiResult {
        return SavedPromptAiResult::query()
            ->forUser($user)
            ->where('saved_prompt_id', $savedPromptId)
            ->first();
    }

    public function create(
        array $data
    ): SavedPromptAiResult {
        return SavedPromptAiResult::query()->create($data);
    }

    public function save(
        SavedPromptAiResult $result
    ): bool {
        return $result->save();
    }

    /**
     * Writer側データは物理削除する。
     */
    public function delete(
        SavedPromptAiResult $result
    ): bool {
        return (bool) $result->delete();
    }
}
