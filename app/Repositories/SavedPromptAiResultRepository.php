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
     * 論理削除済みも含め、対象プロンプトの回答を取得する。
     */
    public function findForPromptWithTrashed(
        User $user,
        int $savedPromptId
    ): ?SavedPromptAiResult {
        return SavedPromptAiResult::withTrashed()
            ->forUser($user)
            ->where('saved_prompt_id', $savedPromptId)
            ->first();
    }

    public function create(
        array $data
    ): SavedPromptAiResult {
        return SavedPromptAiResult::create($data);
    }

    public function save(
        SavedPromptAiResult $result
    ): bool {
        return $result->save();
    }

    /**
     * DBの一意制約と競合しないよう、削除時は物理削除する。
     */
    public function delete(
        SavedPromptAiResult $result
    ): bool {
        return (bool) $result->forceDelete();
    }
}
