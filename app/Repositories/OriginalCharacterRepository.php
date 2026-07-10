<?php

namespace App\Repositories;

use App\Models\OriginalCharacter;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OriginalCharacterRepository
{
    public function paginateForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return OriginalCharacter::query()
            ->forUser($user)
            ->latest()
            ->paginate($perPage);
    }

    public function countForUser(User $user): int
    {
        if ($user->isSuperAdmin()) {
            return OriginalCharacter::query()->count();
        }

        return OriginalCharacter::query()
            ->where('user_id', $user->id)
            ->count();
    }

    public function allForUser(User $user): Collection
    {
        return OriginalCharacter::query()
            ->forUser($user)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): OriginalCharacter
    {
        return OriginalCharacter::create($data);
    }

    public function update(OriginalCharacter $originalCharacter, array $data): bool
    {
        return $originalCharacter->update($data);
    }

    public function delete(OriginalCharacter $originalCharacter): bool
    {
        return $originalCharacter->delete();
    }
}
