<?php

namespace App\Services;

use App\Models\SavedPrompt;
use App\Models\SavedPromptAiResult;
use App\Models\User;
use App\Repositories\SavedPromptAiResultRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class SavedPromptAiResultService
{
    public function __construct(
        private readonly SavedPromptAiResultRepository $repository
    ) {
    }

    public function latestForPrompt(
        User $user,
        SavedPrompt $savedPrompt
    ): Collection {
        $this->assertPromptOwner($user, $savedPrompt);

        return $this->repository->latestForPrompt(
            $user,
            $savedPrompt->id,
            1
        );
    }

    /**
     * 1プロンプトにつき1件。
     * すでに登録済みの場合は既存レコードを更新する。
     */
    public function createForUser(
        User $user,
        SavedPrompt $savedPrompt,
        array $data
    ): SavedPromptAiResult {
        $this->assertPromptOwner($user, $savedPrompt);

        $resultBody = trim(
            (string) ($data['result_body'] ?? '')
        );

        if ($resultBody === '') {
            throw ValidationException::withMessages([
                'result_body' =>
                    'AIが出した結論を貼り付けてください。',
            ]);
        }

        if (mb_strlen($resultBody) > 10000) {
            throw ValidationException::withMessages([
                'result_body' =>
                    'AIが出した結論は10,000文字以内で入力してください。',
            ]);
        }

        $title = trim(
            (string) ($data['result_title'] ?? '')
        );

        $existing = $this->repository
            ->findForPrompt(
                $user,
                $savedPrompt->id
            );

        if ($existing) {
            $existing->title = $title !== ''
                ? $title
                : 'AI回答 '
                    . now()->format('Y/m/d H:i');

            $existing->prompt_snapshot = (string) (
                $savedPrompt->prompt_body ?? ''
            );

            $existing->result_body = $resultBody;

            $this->repository->save($existing);

            return $existing->refresh();
        }

        if ($title === '') {
            $title = 'AI回答 '
                . now()->format('Y/m/d H:i');
        }

        return $this->repository->create([
            'user_id' => $user->id,
            'saved_prompt_id' => $savedPrompt->id,
            'title' => $title,
            'prompt_snapshot' => (string) (
                $savedPrompt->prompt_body ?? ''
            ),
            'result_body' => $resultBody,
        ]);
    }

    public function deleteForUser(
        User $user,
        SavedPrompt $savedPrompt,
        SavedPromptAiResult $result
    ): bool {
        $this->assertPromptOwner($user, $savedPrompt);

        abort_unless(
            (int) $result->user_id === (int) $user->id
            && (int) $result->saved_prompt_id
                === (int) $savedPrompt->id,
            403
        );

        return $this->repository->delete($result);
    }

    private function assertPromptOwner(
        User $user,
        SavedPrompt $savedPrompt
    ): void {
        abort_unless(
            (int) $savedPrompt->user_id
                === (int) $user->id,
            403
        );
    }
}
