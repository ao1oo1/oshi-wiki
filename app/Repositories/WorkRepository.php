<?php

namespace App\Repositories;

use App\Models\Work;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class WorkRepository
{
    public function paginate(int|array $perPageOrFilters = 20, ?string $keyword = null, ?string $status = null, ?int $tagId = null): LengthAwarePaginator
    {
        if (is_array($perPageOrFilters)) {
            $filters = $perPageOrFilters;
            $perPage = 20;
            $keyword = $filters['keyword'] ?? null;
            $status = $filters['status'] ?? null;
            $tagId = isset($filters['tag_id']) ? (int) $filters['tag_id'] : null;
        } else {
            $perPage = $perPageOrFilters;
        }

        $query = Work::query()
            ->with('tags')
            ->latest();

        if (! empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('title_kana', 'like', "%{$keyword}%")
                    ->orWhere('genre', 'like', "%{$keyword}%")
                    ->orWhere('original_media', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if (! empty($status)) {
            $query->where('status', $status);
        }

        if (! empty($tagId)) {
            $query->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Work
    {
        $data['slug'] = $this->makeUniqueSlug($data['slug'] ?? null);

        return Work::create($data);
    }

    public function syncTags(Work $work, array $tagIds): void
    {
        $work->tags()->sync($tagIds);
    }

    public function update(Work $work, array $data): bool
    {
        if (array_key_exists('slug', $data)) {
            $slug = $data['slug'];

            if (empty($slug) || $slug !== $work->slug) {
                $data['slug'] = $this->makeUniqueSlug($slug, $work->id);
            }
        }

        return $work->update($data);
    }

    public function find(int $id): ?Work
    {
        return Work::find($id);
    }

    public function findOrFail(int $id): Work
    {
        return Work::findOrFail($id);
    }

    public function delete(Work $work): bool
    {
        return $work->delete();
    }

    private function makeUniqueSlug(?string $slug = null, ?int $ignoreId = null): string
    {
        $base = $slug ? Str::slug($slug) : '';

        if ($base === '' || preg_match('/^work-\d{14}$/', $base)) {
            $base = 'work-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8));
        }

        $candidate = $base;
        $count = 1;

        while (
            Work::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $base . '-' . $count . '-' . Str::lower(Str::random(4));
            $count++;
        }

        return $candidate;
    }
}
