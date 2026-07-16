<?php

namespace App\Services;

use App\Models\Work;
use App\Repositories\WorkRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class WorkService
{
    public function __construct(
        private readonly WorkRepository $repository
    ) {
    }

    public function paginate(
        int $perPage = 20,
        ?string $keyword = null,
        ?int $tagId = null,
        ?string $status = null,
        ?string $exactKeyword = null
    ): LengthAwarePaginator {
        return $this->repository->paginate($perPage, $keyword, $tagId, $status, $exactKeyword);
    }

    public function create(array $data): Work
    {
        return DB::transaction(function () use ($data): Work {
            [$workData, $tagIds, $canonEvents, $termUsages] = $this->splitRelatedData($data);

            $workData = $this->applyReviewRule($workData);
            $workData = $this->normalizeParentData($workData);
            $this->validateParentWork($workData['parent_work_id'] ?? null);
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
            $workData = $this->normalizeParentData($workData);
            $this->validateParentWork(
                $workData['parent_work_id'] ?? null,
                $work
            );
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
        if ($work->childWorks()->exists()) {
            throw ValidationException::withMessages([
                'work' =>
                    '関連作品が登録されているため削除できません。'
                    . '先に子作品の親作品設定を解除するか、'
                    . '別の親作品へ変更してください。',
            ]);
        }

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

    private function normalizeParentData(array $data): array
    {
        $parentWorkId = $data['parent_work_id'] ?? null;

        $data['parent_work_id'] = filled($parentWorkId)
            ? (int) $parentWorkId
            : null;

        $data['child_sort_order'] = max(
            0,
            (int) ($data['child_sort_order'] ?? 0)
        );

        return $data;
    }

    private function validateParentWork(
        ?int $parentWorkId,
        ?Work $currentWork = null
    ): void {
        if (! $parentWorkId) {
            return;
        }

        if (
            $currentWork
            && $parentWorkId === (int) $currentWork->id
        ) {
            throw ValidationException::withMessages([
                'parent_work_id' =>
                    '自分自身を親作品に設定することはできません。',
            ]);
        }

        $parentWork = Work::query()->find($parentWorkId);

        if (! $parentWork) {
            throw ValidationException::withMessages([
                'parent_work_id' =>
                    '選択した親作品が見つかりません。',
            ]);
        }

        if ($parentWork->parent_work_id !== null) {
            throw ValidationException::withMessages([
                'parent_work_id' =>
                    '子作品を親作品として選択することはできません。',
            ]);
        }

        if (
            $currentWork
            && $currentWork->childWorks()->exists()
        ) {
            throw ValidationException::withMessages([
                'parent_work_id' =>
                    '子作品を持つ作品を別作品の子にすることはできません。',
            ]);
        }
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
