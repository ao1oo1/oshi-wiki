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
            ->latest()
            ->paginate($perPage);
    }

    public function create(
        array $data
    ): WriterStoryAnalysis {
        return WriterStoryAnalysis::create($data);
    }
}
