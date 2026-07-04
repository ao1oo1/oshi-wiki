<?php

namespace App\Services;

use App\Models\Character;
use App\Models\CharacterRelationship;
use App\Repositories\CharacterRelationshipRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class CharacterRelationshipService
{
    public function __construct(
        private readonly CharacterRelationshipRepository $repository
    ) {
    }

    public function paginate(int $perPage = 20, ?int $workId = null, ?string $keyword = null): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $workId, $keyword);
    }

    public function create(array $data): CharacterRelationship
    {
        $this->validateCharacters($data);

        $data = $this->applyReviewRule($data, false);
        $data['status'] = $data['status'] ?? 'draft';

        return $this->repository->create($data);
    }

    public function update(CharacterRelationship $characterRelationship, array $data): bool
    {
        $this->validateCharacters($data);

        $data['status'] = $data['status'] ?? $characterRelationship->status;

        $data = $this->applyReviewRule($data, true);

        return $this->repository->update($characterRelationship, $data);
    }

    public function delete(CharacterRelationship $characterRelationship): bool
    {
        return $this->repository->delete($characterRelationship);
    }

    private function validateCharacters(array $data): void
    {
        $workId = (int) $data['work_id'];
        $fromCharacterId = (int) $data['from_character_id'];
        $toCharacterId = (int) $data['to_character_id'];

        if ($fromCharacterId === $toCharacterId) {
            throw ValidationException::withMessages([
                'to_character_id' => '同じキャラクター同士の関係性は登録できません。',
            ]);
        }

        $fromCharacter = Character::query()->find($fromCharacterId);
        $toCharacter = Character::query()->find($toCharacterId);

        if (! $fromCharacter || ! $toCharacter) {
            throw ValidationException::withMessages([
                'from_character_id' => 'キャラクター情報が見つかりません。',
            ]);
        }

        if ((int) $fromCharacter->work_id !== $workId) {
            throw ValidationException::withMessages([
                'from_character_id' => '選択した作品に属していないキャラクターです。',
            ]);
        }

        if ((int) $toCharacter->work_id !== $workId) {
            throw ValidationException::withMessages([
                'to_character_id' => '選択した作品に属していない相手キャラクターです。',
            ]);
        }
    }
    private function applyReviewRule(array $data, bool $isUpdate = false): array
    {
        if (auth()->check() && auth()->user()?->isSuperAdmin()) {
            return $data;
        }

        unset($data['status']);

        $data['status'] = 'draft';
        $data['review_status'] = 'pending';

        return $data;
    }


}
