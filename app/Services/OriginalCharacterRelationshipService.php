<?php

namespace App\Services;

use App\Models\OriginalCharacter;
use App\Models\OriginalCharacterRelationship;
use App\Models\User;
use App\Repositories\OriginalCharacterRelationshipRepository;
use App\Support\WritingAssistLimits;
use Illuminate\Validation\ValidationException;

class OriginalCharacterRelationshipService
{
    public function __construct(
        private readonly OriginalCharacterRelationshipRepository $repository
    ) {
    }

    public function paginateForUser(User $user)
    {
        return $this->repository->paginateForUser($user);
    }

    public function countForUser(User $user): int
    {
        return $this->repository->countForUser($user);
    }

    public function createForUser(User $user, array $data): OriginalCharacterRelationship
    {
        $data = $this->normalizeTimelineItems($data);

        $limit = WritingAssistLimits::relationshipsPerUser($user);

        if ($limit !== null && $this->repository->countForUser($user) >= $limit) {
            throw ValidationException::withMessages([
                'limit' => "関係性は最大{$limit}件まで登録できます。",
            ]);
        }

        $resolved = $this->resolveRelationshipCharacters($user, $data);

        $payload = array_merge($data, $resolved);
        unset($payload['from_character_ref'], $payload['to_character_ref']);

        $payload['user_id'] = $user->id;
        $payload['status'] = $payload['status'] ?? 'active';

        return $this->repository->create($payload);
    }

    public function updateForUser(User $user, OriginalCharacterRelationship $relationship, array $data): bool
    {
        $data = $this->normalizeTimelineItems($data);

        $resolved = $this->resolveRelationshipCharacters($user, $data);

        $payload = array_merge($data, $resolved);
        unset($payload['from_character_ref'], $payload['to_character_ref']);

        return $this->repository->update($relationship, $payload);
    }

    public function delete(OriginalCharacterRelationship $relationship): bool
    {
        return $this->repository->delete($relationship);
    }

    private function resolveRelationshipCharacters(User $user, array $data): array
    {
        $from = $this->resolveCharacterRef($user, (string) ($data['from_character_ref'] ?? ''), 'from_character_ref');
        $to = $this->resolveCharacterRef($user, (string) ($data['to_character_ref'] ?? ''), 'to_character_ref');

        if ($from['id'] === $to['id']) {
            throw ValidationException::withMessages([
                'to_character_ref' => '同じキャラクター同士の関係性は登録できません。',
            ]);
        }

        return [
            'from_character_source' => OriginalCharacterRelationship::SOURCE_ORIGINAL,
            'to_character_source' => OriginalCharacterRelationship::SOURCE_ORIGINAL,

            'from_original_character_id' => $from['id'],
            'to_original_character_id' => $to['id'],

            'from_character_id' => null,
            'to_character_id' => null,
        ];
    }

    private function resolveCharacterRef(User $user, string $ref, string $field): array
    {
        if (! preg_match('/^original:\d+$/', $ref)) {
            throw ValidationException::withMessages([
                $field => '自分で登録したオリジナルキャラクターを選択してください。',
            ]);
        }

        [, $id] = explode(':', $ref, 2);
        $id = (int) $id;

        $character = OriginalCharacter::query()
            ->where('user_id', $user->id)
            ->find($id);

        if (! $character) {
            throw ValidationException::withMessages([
                $field => 'オリジナルキャラクターが見つかりません。',
            ]);
        }

        return [
            'source' => OriginalCharacterRelationship::SOURCE_ORIGINAL,
            'id' => $character->id,
        ];
    }

    private function normalizeTimelineItems(array $data): array
    {
        $items = $data['timeline_items'] ?? [];

        if (! is_array($items)) {
            $data['timeline_items'] = [];
            return $data;
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $period = trim((string) ($item['period'] ?? ''));
            $content = trim((string) ($item['content'] ?? ''));

            if ($period === '' && $content === '') {
                continue;
            }

            $normalized[] = [
                'period' => $period,
                'content' => $content,
            ];

            if (count($normalized) >= 10) {
                break;
            }
        }

        $data['timeline_items'] = $normalized;

        return $data;
    }
}
