<?php

namespace App\Repositories;

use App\Models\OriginalCharacterRelationship;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OriginalCharacterRelationshipRepository
{
    public function paginateForUser(
        User $user,
        int $perPage = 20
    ): LengthAwarePaginator {
        return OriginalCharacterRelationship::query()
            ->with([
                'fromCharacter',
                'toCharacter',
                'fromV1Character.work',
                'toV1Character.work',
            ])
            ->forUser($user)
            ->latest()
            ->paginate($perPage);
    }

    public function countForUser(User $user): int
    {
        /*
         * 最高管理者であってもWriter側では、
         * 本人が登録したデータだけを数える。
         */
        return OriginalCharacterRelationship::query()
            ->forUser($user)
            ->count();
    }

    public function create(
        array $data
    ): OriginalCharacterRelationship {
        return OriginalCharacterRelationship::create($data);
    }

    public function update(
        OriginalCharacterRelationship $relationship,
        array $data
    ): bool {
        return $relationship->update($data);
    }

    public function delete(
        OriginalCharacterRelationship $relationship
    ): bool {
        return $relationship->delete();
    }
}
