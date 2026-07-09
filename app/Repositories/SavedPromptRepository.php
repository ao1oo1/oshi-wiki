<?php

namespace App\Repositories;

use App\Models\SavedPrompt;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SavedPromptRepository
{
    public function paginateForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return SavedPrompt::query()
            ->forUser($user)
            ->latest()
            ->paginate($perPage);
    }

    public function countForUser(User $user): int
    {
        if ($user->isSuperAdmin()) {
            return SavedPrompt::query()->count();
        }

        return SavedPrompt::query()
            ->where('user_id', $user->id)
            ->count();
    }

    public function create(array $data): SavedPrompt
    {
        return SavedPrompt::create($data);
    }

    public function update(SavedPrompt $savedPrompt, array $data): bool
    {
        return $savedPrompt->update($data);
    }

    public function delete(SavedPrompt $savedPrompt): bool
    {
        return $savedPrompt->delete();
    }
}
