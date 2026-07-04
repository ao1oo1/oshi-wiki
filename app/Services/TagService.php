<?php

namespace App\Services;

use App\Models\Tag;
use App\Repositories\TagRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class TagService
{
    public function __construct(
        private readonly TagRepository $repository
    ) {
    }

    public function paginate(int $perPage = 20, ?string $keyword = null): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $keyword);
    }

    public function all(): Collection
    {
        return $this->repository->all();
    }

    public function create(array $data): Tag
    {
        $data['slug'] = $this->makeSlug($data['name']);
        $data['type'] = $data['type'] ?? 'general';
        $data = $this->applyReviewRule($data, false);
        $data['status'] = $data['status'] ?? 'draft';

        return $this->repository->create($data);
    }

    public function update(Tag $tag, array $data): bool
    {
        $data['slug'] = $this->makeSlug($data['name']);
        $data['type'] = $data['type'] ?? $tag->type;
        $data = $this->applyReviewRule($data, true);
        $data['status'] = $data['status'] ?? $tag->status;

        return $this->repository->update($tag, $data);
    }

    public function delete(Tag $tag): bool
    {
        return $this->repository->delete($tag);
    }

    private function makeSlug(string $name): string
    {
        $slug = Str::slug($name);

        if ($slug !== '') {
            return $slug;
        }

        return 'tag-' . now()->format('YmdHis');
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
