<?php

namespace App\Services;

use App\Models\Work;
use App\Repositories\WorkRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkService
{
    public function __construct(
        private readonly WorkRepository $repository
    ) {
    }

    public function paginate(int $perPage = 20, ?string $keyword = null, ?int $tagId = null): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $keyword, $tagId);
    }

    public function create(array $data): Work
    {
        return DB::transaction(function () use ($data): Work {
            [$workData, $tagIds, $canonEvents, $termUsages] = $this->splitRelatedData($data);

            $workData = $this->applyReviewRule($workData);
            $workData['status'] = $workData['status'] ?? 'draft';
            $workData['slug'] = $this->makeSlug($workData['title']);

            $work = $this->repository->create($workData);
            $this->repository->syncTags($work, $tagIds);
            $this->repository->syncCanonEvents($work, $canonEvents);
            $this->repository->syncTermUsages($work, $termUsages);

            return $work;
        });
    }

    public function update(Work $work, array $data): bool
    {
        return DB::transaction(function () use ($work, $data): bool {
            [$workData, $tagIds, $canonEvents, $termUsages] = $this->splitRelatedData($data);

            $workData = $this->applyReviewRule($workData);
            $workData['status'] = $workData['status'] ?? $work->status;
            $workData['slug'] = $this->makeSlug($workData['title'], $work->id);

            $updated = $this->repository->update($work, $workData);
            $this->repository->syncTags($work, $tagIds);
            $this->repository->syncCanonEvents($work, $canonEvents);
            $this->repository->syncTermUsages($work, $termUsages);

            return $updated;
        });
    }

    public function delete(Work $work): bool
    {
        return $this->repository->delete($work);
    }

    public function findWithDetails(Work $work): Work
    {
        return $this->repository->findWithDetails($work);
    }

    private function splitRelatedData(array $data): array
    {
        $tagIds = $data['tag_ids'] ?? [];
        $canonEvents = $this->filterCanonEvents($data['canon_events'] ?? []);
        $termUsages = $this->filterTermUsages($data['term_usages'] ?? []);

        unset($data['tag_ids'], $data['canon_events'], $data['term_usages']);

        return [$data, $tagIds, $canonEvents, $termUsages];
    }

    private function filterCanonEvents(array $events): array
    {
        return array_values(array_filter($events, function (array $event): bool {
            return trim((string) ($event['event_name'] ?? '')) !== '';
        }));
    }

    private function filterTermUsages(array $terms): array
    {
        return array_values(array_filter($terms, function (array $term): bool {
            return trim((string) ($term['term'] ?? '')) !== '';
        }));
    }

    private function makeSlug(string $title, ?int $ignoreWorkId = null): string
    {
        $base = Str::slug($title);

        if ($base === '') {
            $base = 'work-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8));
        }

        $candidate = $base;
        $count = 1;

        while (Work::query()
            ->when($ignoreWorkId, fn ($query) => $query->whereKeyNot($ignoreWorkId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $base . '-' . $count . '-' . Str::lower(Str::random(4));
            $count++;
        }

        return $candidate;
    }

    private function applyReviewRule(array $data): array
    {
        if (auth()->check() && auth()->user()?->isSuperAdmin()) {
            return $data;
        }

        unset($data['status']);
        $data['status'] = 'draft';
        $data['review_status'] = 'pending';

        return $data;
    }
}
