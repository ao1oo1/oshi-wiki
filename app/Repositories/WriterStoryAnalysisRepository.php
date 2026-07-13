<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\WriterStoryAnalysis;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WriterStoryAnalysisRepository
{
    public function paginateForUser(
        User $user,
        int $perPage = 10
    ): LengthAwarePaginator {
        return WriterStoryAnalysis::query()
            ->forUser($user)
            ->latest('updated_at')
            ->paginate($perPage);
    }

    public function countForUser(User $user): int
    {
        return WriterStoryAnalysis::query()
            ->forUser($user)
            ->count();
    }

    public function create(array $data): WriterStoryAnalysis
    {
        return WriterStoryAnalysis::query()->create($data);
    }

    public function update(
        WriterStoryAnalysis $analysis,
        array $data
    ): WriterStoryAnalysis {
        $analysis->update($data);

        return $analysis->refresh();
    }

    public function delete(
        WriterStoryAnalysis $analysis
    ): bool {
        return (bool) $analysis->delete();
    }
}
