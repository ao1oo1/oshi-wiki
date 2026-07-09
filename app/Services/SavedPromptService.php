<?php

namespace App\Services;

use App\Models\SavedPrompt;
use App\Models\User;
use App\Models\Work;
use App\Repositories\SavedPromptRepository;
use App\Support\WritingAssistLimits;
use Illuminate\Validation\ValidationException;

class SavedPromptService
{
    public function __construct(
        private readonly SavedPromptRepository $repository,
        private readonly PromptCharacterContextBuilder $contextBuilder
    ) {
    }

    public function paginateForUser(User $user, array $filters = [])
    {
        return $this->repository->paginateForUser($user, 20, $filters);
    }

    public function countForUser(User $user): int
    {
        return $this->repository->countForUser($user);
    }

    public function createForUser(User $user, array $data): SavedPrompt
    {
        $limit = WritingAssistLimits::promptsPerUser($user);

        if ($limit !== null && $this->repository->countForUser($user) >= $limit) {
            throw ValidationException::withMessages([
                'limit' => "保存プロンプトは最大{$limit}件まで登録できます。",
            ]);
        }

        $payload = $this->normalizePromptData($data);
        $payload['user_id'] = $user->id;
        $payload['status'] = $payload['status'] ?? 'active';
        $payload['category'] = $payload['category'] ?? 'scene';
        $payload['prompt_body'] = $this->buildPromptBody($user, $payload);

        return $this->repository->create($payload);
    }

    public function update(User $user, SavedPrompt $savedPrompt, array $data): bool
    {
        $payload = $this->normalizePromptData($data);
        $payload['status'] = $payload['status'] ?? 'active';
        $payload['category'] = $payload['category'] ?? 'scene';
        $payload['prompt_body'] = $this->buildPromptBody($user, $payload);

        return $this->repository->update($savedPrompt, $payload);
    }

    public function delete(SavedPrompt $savedPrompt): bool
    {
        return $this->repository->delete($savedPrompt);
    }

    public function previewForUser(User $user, array $data): string
    {
        $payload = $this->normalizePromptData($data);

        return $this->buildPromptBody($user, $payload);
    }

    public function recordUsage(SavedPrompt $savedPrompt): bool
    {
        $savedPrompt->used_count = (int) $savedPrompt->used_count + 1;
        $savedPrompt->last_used_at = now();

        return $savedPrompt->save();
    }

    private function normalizePromptData(array $data): array
    {
        $workRef = (string) ($data['work_ref'] ?? 'original');

        $data['work_source'] = SavedPrompt::WORK_SOURCE_ORIGINAL;
        $data['work_id'] = null;

        if (str_starts_with($workRef, 'work:')) {
            $workId = (int) str_replace('work:', '', $workRef);
            $work = Work::query()->find($workId);

            if (! $work) {
                throw ValidationException::withMessages([
                    'work_ref' => '選択された作品が見つかりません。',
                ]);
            }

            $data['work_source'] = SavedPrompt::WORK_SOURCE_V1;
            $data['work_id'] = $work->id;
        }

        $data['selected_character_refs'] = array_values(array_filter(
            $data['selected_character_refs'] ?? [],
            fn ($value) => is_string($value) && str_contains($value, ':')
        ));

        unset($data['work_ref']);

        return $data;
    }

    private function buildPromptBody(User $user, array $data): string
    {
        $workName = 'オリジナル';

        if (($data['work_source'] ?? null) === SavedPrompt::WORK_SOURCE_V1 && ! empty($data['work_id'])) {
            $workName = Work::query()->find($data['work_id'])?->title ?? '選択作品';
        }

        $context = $this->contextBuilder->build($user, $data['selected_character_refs'] ?? []);

        $style = $this->labelFrom(
            SavedPrompt::writingStyleLabels(),
            $data['writing_style'] ?? null,
            $data['writing_style_other'] ?? null
        );

        $genre = $this->labelFrom(
            SavedPrompt::genreLabels(),
            $data['genre'] ?? null,
            $data['genre_other'] ?? null
        );

        $synopsis = $this->safeText($data['synopsis'] ?? null);
        $plotOpening = $this->safeText($data['plot_opening'] ?? null);
        $plotDevelopment = $this->safeText($data['plot_development'] ?? null);
        $plotTurn = $this->safeText($data['plot_turn'] ?? null);
        $plotConclusion = $this->safeText($data['plot_conclusion'] ?? null);
        $notes = $this->safeText($data['notes'] ?? null);

        $lines = [
            '以下の条件をもとに、小説本文の作成に使うためのプロンプトを作成してください。',
            '',
            '【作品】',
            $workName,
            '',
            '【登場人物詳細】',
            $context['characters'] ?: '指定なし',
            '',
            '【関係性】',
            $context['relationships'] ?: '指定なし',
            '',
            '【作風】',
            $style ?: '指定なし',
            '',
            '【ジャンル】',
            $genre ?: '指定なし',
            '',
            '【あらすじ】',
            $synopsis ?: '指定なし',
            '',
            '【起】',
            $plotOpening ?: '指定なし',
            '',
            '【承】',
            $plotDevelopment ?: '指定なし',
            '',
            '【転】',
            $plotTurn ?: '指定なし',
            '',
            '【結】',
            $plotConclusion ?: '指定なし',
            '',
            '【出力条件】',
            '・上記の設定を守ってください。',
            '・登場人物の一人称、口調、性格、関係性を反映してください。',
            '・登録情報にない設定は断定しないでください。',
            '・作品名が「オリジナル」の場合は、既存作品の固有設定を前提にしないでください。',
            '・不足している情報は、自然な範囲で補ってください。',
        ];

        if ($notes !== '') {
            $lines[] = '';
            $lines[] = '【備考】';
            $lines[] = $notes;
        }

        return implode("\n", $lines);
    }

    private function labelFrom(array $labels, ?string $value, ?string $other): string
    {
        if ($value === 'other') {
            return trim((string) $other);
        }

        return $labels[$value] ?? '';
    }

    private function safeText(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }
}
