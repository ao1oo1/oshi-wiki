<?php

namespace App\Repositories;

use App\Models\Character;
use App\Models\OriginalCharacter;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

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

    public function allOfficialCharactersForUser(User $user): Collection
    {
        $query = Character::query()
            ->select('characters.*')
            ->orderBy('characters.name');

        if (Schema::hasTable('works') && Schema::hasColumn('characters', 'work_id')) {
            $query
                ->leftJoin('works', 'characters.work_id', '=', 'works.id')
                ->addSelect('works.title as work_title')
                ->orderBy('works.title');
        }

        if (! $user->isSuperAdmin() && Schema::hasColumn('characters', 'status')) {
            $query->whereIn('characters.status', ['published', 'active']);
        }

        return $query->get();
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
