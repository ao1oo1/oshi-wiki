<?php

namespace App\Repositories;

use App\Models\SavedPrompt;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SavedPromptRepository
{
    public function paginateForUser(User $user, int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $sort = $filters['sort'] ?? 'latest';

        $query = SavedPrompt::query()
            ->with('work')
            ->forUser($user)
            ->when($filters['keyword'] ?? null, function ($query, string $keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery
                        ->where('title', 'like', '%' . $keyword . '%')
                        ->orWhere('purpose', 'like', '%' . $keyword . '%')
                        ->orWhere('synopsis', 'like', '%' . $keyword . '%')
                        ->orWhere('prompt_body', 'like', '%' . $keyword . '%')
                        ->orWhere('notes', 'like', '%' . $keyword . '%');
                });
            })
            ->when($filters['work_source'] ?? null, function ($query, string $workSource) {
                if ($workSource === SavedPrompt::WORK_SOURCE_ORIGINAL) {
                    $query->where('work_source', SavedPrompt::WORK_SOURCE_ORIGINAL);
                }

                if ($workSource === SavedPrompt::WORK_SOURCE_V1) {
                    $query->where('work_source', SavedPrompt::WORK_SOURCE_V1);
                }
            })
            ->when($filters['work_id'] ?? null, function ($query, int|string $workId) {
                $query->where('work_id', (int) $workId);
            })
            ->when($filters['writing_style'] ?? null, function ($query, string $writingStyle) {
                $query->where('writing_style', $writingStyle);
            })
            ->when($filters['genre'] ?? null, function ($query, string $genre) {
                $query->where('genre', $genre);
            })
            ->when($filters['status'] ?? null, function ($query, string $status) {
                $query->where('status', $status);
            });

        match ($sort) {
            'oldest' => $query->oldest(),
            'updated' => $query->orderByDesc('updated_at')->orderByDesc('id'),
            'most_used' => $query->orderByDesc('used_count')->orderByDesc('last_used_at')->orderByDesc('id'),
            'recently_used' => $query->orderByDesc('last_used_at')->orderByDesc('used_count')->orderByDesc('id'),
            'title_asc' => $query->orderBy('title')->orderByDesc('id'),
            'title_desc' => $query->orderByDesc('title')->orderByDesc('id'),
            default => $query->latest(),
        };

        return $query
            ->paginate($perPage)
            ->withQueryString();
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
