<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\WriterStory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class WriterStoryRepository
{
    public function paginateForUser(
        User $user,
        array $filters = [],
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = WriterStory::query()
            ->forUser($user)
            ->when(
                $filters['keyword'] ?? null,
                function ($query, string $keyword): void {
                    $query->where(function ($subQuery) use ($keyword): void {
                        $subQuery
                            ->where('title', 'like', '%' . $keyword . '%')
                            ->orWhere('body', 'like', '%' . $keyword . '%')
                            ->orWhere('memo', 'like', '%' . $keyword . '%');
                    });
                }
            )
            ->when(
                $filters['status'] ?? null,
                fn ($query, string $status) => $query->where('status', $status)
            );

        match ($filters['sort'] ?? 'episode') {
            'latest' => $query->latest(),
            'oldest' => $query->oldest(),
            'updated' => $query->orderByDesc('updated_at')->orderByDesc('id'),
            'title' => $query->orderBy('title')->orderBy('id'),
            default => $query
                ->orderByRaw('episode_number IS NULL')
                ->orderBy('episode_number')
                ->orderBy('id'),
        };

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allForUser(User $user): Collection
    {
        return WriterStory::query()
            ->forUser($user)
            ->orderByRaw('episode_number IS NULL')
            ->orderBy('episode_number')
            ->orderBy('id')
            ->get();
    }

    public function findSelectedForUser(
        User $user,
        array $storyIds
    ): Collection {
        return WriterStory::query()
            ->forUser($user)
            ->whereIn('id', $storyIds)
            ->orderByRaw('episode_number IS NULL')
            ->orderBy('episode_number')
            ->orderBy('id')
            ->get();
    }

    public function countForUser(User $user): int
    {
        return WriterStory::query()
            ->forUser($user)
            ->count();
    }

    public function create(array $data): WriterStory
    {
        return WriterStory::create($data);
    }

    public function update(WriterStory $story, array $data): bool
    {
        return $story->update($data);
    }

    public function delete(WriterStory $story): bool
    {
        return $story->delete();
    }
}
