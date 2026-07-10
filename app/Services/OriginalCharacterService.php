<?php

namespace App\Services;

use App\Models\OriginalCharacter;
use App\Models\User;
use App\Repositories\OriginalCharacterRepository;
use App\Support\WritingAssistLimits;
use Illuminate\Validation\ValidationException;

class OriginalCharacterService
{
    public function __construct(
        private readonly OriginalCharacterRepository $repository
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

    public function createForUser(User $user, array $data): OriginalCharacter
    {
        $limit = WritingAssistLimits::originalCharactersPerUser($user);

        if ($limit !== null && $this->repository->countForUser($user) >= $limit) {
            throw ValidationException::withMessages([
                'limit' => "オリジナルキャラクターは最大{$limit}件まで登録できます。",
            ]);
        }

        $data['user_id'] = $user->id;
        $data['status'] = $data['status'] ?? 'active';
        $data['is_main_character'] = (bool) ($data['is_main_character'] ?? false);

        return $this->repository->create($data);
    }

    public function update(OriginalCharacter $originalCharacter, array $data): bool
    {
        $data['is_main_character'] = (bool) ($data['is_main_character'] ?? false);

        return $this->repository->update($originalCharacter, $data);
    }

    public function delete(OriginalCharacter $originalCharacter): bool
    {
        return $this->repository->delete($originalCharacter);
    }
}
