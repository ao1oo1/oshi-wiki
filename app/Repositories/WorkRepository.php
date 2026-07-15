<?php

namespace App\Repositories;

use App\Models\Work;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WorkRepository
{
    public function paginate(int $perPage = 20, ?string $keyword = null, ?int $tagId = null, ?string $status = null, ?string $exactKeyword = null): LengthAwarePaginator
    {
        return Work::query()
            ->with('tags')
            ->when($keyword, function ($query) use ($keyword): void {
                $query->where(function ($query) use ($keyword): void {
                    $query->where('title', 'like', '%' . $keyword . '%')
                        ->orWhere('title_kana', 'like', '%' . $keyword . '%')
                        ->orWhere('genre', 'like', '%' . $keyword . '%')
                        ->orWhere('original_media', 'like', '%' . $keyword . '%')
                        ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            })
            ->when($tagId, function ($query) use ($tagId): void {
                $query->whereHas('tags', function ($query) use ($tagId): void {
                    $query->where('tags.id', $tagId);
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($exactKeyword, function ($query) use ($exactKeyword): void {
                $query->where(function ($query) use ($exactKeyword): void {
                    $query->where('title', $exactKeyword)
                        ->orWhere('title_kana', $exactKeyword)
                        ->orWhere('genre', $exactKeyword)
                        ->orWhere('original_media', $exactKeyword);
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
            'canonEvents',
            'termUsages',
            'characters.tags',
            'characterRelationships.fromCharacter',
            'characterRelationships.toCharacter',
        ]);
    }

    public function syncTags(Work $work, array $tagIds): void
    {
        $work->tags()->sync($tagIds);
    }

    public function syncCanonEvents(Work $work, array $events): void
    {
        $work->canonEvents()->delete();

        foreach (array_values($events) as $index => $event) {
            $work->canonEvents()->create([
                'sort_order' => $index + 1,
                'timing' => $event['timing'] ?? null,
                'event_name' => $event['event_name'],
                'event_status' => $event['event_status'] ?? null,
                'notes' => $event['notes'] ?? null,
            ]);
        }
    }

    public function syncTermUsages(Work $work, array $terms): void
    {
        $work->termUsages()->delete();

        foreach (array_values($terms) as $index => $term) {
            $work->termUsages()->create([
                'sort_order' => $index + 1,
                'term' => $term['term'],
                'meaning' => $term['meaning'] ?? null,
                'usage_example' => $term['usage_example'] ?? null,
            ]);
        }
    }
}
