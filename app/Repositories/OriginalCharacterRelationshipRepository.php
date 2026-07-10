<?php

namespace App\Repositories;

use App\Models\OriginalCharacterRelationship;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OriginalCharacterRelationshipRepository
{
    public function paginateForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return OriginalCharacterRelationship::query()
            ->with([
                'fromCharacter',
                'toCharacter',

            ])
            ->forUser($user)
            ->latest()
            ->paginate($perPage);
    }

    public function countForUser(User $user): int
    {
        if ($user->isSuperAdmin()) {
            return OriginalCharacterRelationship::query()->count();
        }

        return OriginalCharacterRelationship::query()
            ->where('user_id', $user->id)
            ->count();
    }

    public function create(array $data): OriginalCharacterRelationship
    {
        return OriginalCharacterRelationship::create($data);
    }

    public function update(OriginalCharacterRelationship $relationship, array $data): bool
    {
        return $relationship->update($data);
    }

    public function delete(OriginalCharacterRelationship $relationship): bool
    {
        return $relationship->delete();
    }
}
