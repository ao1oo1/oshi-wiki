<?php

namespace App\Services;

use App\Models\Character;
use App\Repositories\CharacterRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CharacterService
{
    public function __construct(
        private readonly CharacterRepository $repository,
        private readonly CharacterWorkLinkService $workLinkService
    ) {
    }

    public function paginate(
        int $perPage = 20,
        ?int $workId = null,
        ?string $keyword = null,
        ?int $tagId = null,
        ?string $status = null,
        ?string $exactKeyword = null
    ): LengthAwarePaginator {
        return $this->repository->paginate($perPage, $workId, $keyword, $tagId, $status, $exactKeyword);
    }

    public function create(array $data): Character
    {
        $tagIds = $data['tag_ids'] ?? [];
        $linkedWorkIds = $data['linked_work_ids'] ?? [];
        unset($data['tag_ids'], $data['linked_work_ids']);

        $data = $this->applyReviewRule($data, false);

        // CHARACTER_OWNER_SET_FIX
        if (auth()->check() && empty($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }
        // /CHARACTER_OWNER_SET_FIX
        $data['status'] = $data['status'] ?? 'draft';

        $character = $this->repository->create($data);
        $this->repository->syncTags($character, $tagIds);
        $this->workLinkService->sync(
            $character,
            $linkedWorkIds,
            (int) $character->work_id
        );

        return $character;
    }

    public function update(Character $character, array $data): bool
    {
        $tagIds = $data['tag_ids'] ?? [];
        $linkedWorkIds = $data['linked_work_ids'] ?? [];
        unset($data['tag_ids'], $data['linked_work_ids']);

        $data = $this->applyReviewRule($data, true);
        $data['status'] = $data['status'] ?? $character->status;

        $updated = $this->repository->update($character, $data);
        $this->repository->syncTags($character, $tagIds);

        $character->refresh();

        $this->workLinkService->sync(
            $character,
            $linkedWorkIds,
            (int) $character->work_id
        );

        return $updated;
    }

    public function delete(Character $character): bool
    {
        return $this->repository->delete($character);
    }

    public function findWithWork(Character $character): Character
    {
        return $this->repository->findWithWork($character);
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
