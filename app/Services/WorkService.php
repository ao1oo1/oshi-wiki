<?php

namespace App\Services;

use App\Models\Work;
use App\Repositories\WorkRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $data = $this->applyReviewRule($data, false);

        // WORK_SERVICE_UNIQUE_SLUG_GUARD
        if (empty($data['slug'])) {
            $data['slug'] = 'work-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8));
        }

        if (empty($data['slug'])) {
            $data['slug'] = 'work-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(6));
        }

        if (auth()->check() && auth()->user()?->contributor_application_id && ! auth()->user()?->isSuperAdmin()) {
            $data['contributor_application_id'] = auth()->user()->contributor_application_id;
        }
        $data['status'] = $data['status'] ?? 'draft';
        $data['slug'] = $this->makeSlug($data['title']);

        $work = $this->repository->create($data);
        $this->repository->syncTags($work, $tagIds);

        return $work;
    }

    public function update(Work $work, array $data): bool
    {
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $data = $this->applyReviewRule($data, true);
        $data['status'] = $data['status'] ?? $work->status;
        $data['slug'] = $this->makeSlug($data['title']);

        $updated = $this->repository->update($work, $data);
        $this->repository->syncTags($work, $tagIds);

        return $updated;
    }

    public function delete(Work $work): bool
    {
        return $this->repository->delete($work);
    }

    public function findWithDetails(Work $work): Work
    {
        return $this->repository->findWithDetails($work);
    }

    private function makeSlug(string $title): string
    {
        $slug = Str::slug($title);

        if ($slug !== '') {
            return $slug;
        }

        return 'work-' . now()->format('YmdHis');
    }
    private function applyReviewRule(array $data, bool $isUpdate = false): array
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
