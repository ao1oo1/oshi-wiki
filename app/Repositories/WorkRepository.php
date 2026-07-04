<?php

namespace App\Repositories;

use App\Models\Work;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WorkRepository
{
    public function paginate(int $perPage = 20, ?string $keyword = null, ?int $tagId = null): LengthAwarePaginator
    {
        return Work::query()
            ->with('tags')
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('title', 'like', '%' . $keyword . '%')
                        ->orWhere('title_kana', 'like', '%' . $keyword . '%')
                        ->orWhere('genre', 'like', '%' . $keyword . '%')
                        ->orWhere('original_media', 'like', '%' . $keyword . '%')
                        ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            })
            ->when($tagId, function ($query) use ($tagId) {
                $query->whereHas('tags', function ($query) use ($tagId) {
                    $query->where('tags.id', $tagId);
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Work
    {
        return Work::create($data);
    }

    public function update(Work $work, array $data): bool
    {
        return $work->update($data);
    }

    public function delete(Work $work): bool
    {
        return $work->delete();
    }

    public function findWithDetails(Work $work): Work
    {
        return $work->load([
            'tags',
            'characters.tags',
            'characterRelationships.fromCharacter',
            'characterRelationships.toCharacter',
        ]);
    }

    public function syncTags(Work $work, array $tagIds): void
    {
        $work->tags()->sync($tagIds);
    }
}
