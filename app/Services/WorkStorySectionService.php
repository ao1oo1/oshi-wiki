<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Work;
use App\Models\WorkStorySection;
use App\Repositories\WorkStorySectionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkStorySectionService
{
    public const MAX_SECTIONS_PER_WORK = 30;
    public const MAX_EVENTS_PER_SECTION = 500;

    public function __construct(
        private readonly WorkStorySectionRepository $repository
    ) {
    }

    public function allForWork(Work $work)
    {
        return $this->repository->allForWork($work);
    }

    public function create(
        Work $work,
        array $data
    ): WorkStorySection {
        if (
            $work->allStorySections()->count()
                >= self::MAX_SECTIONS_PER_WORK
        ) {
            throw ValidationException::withMessages([
                'section' =>
                    '章・編は1作品につき最大30件まで登録できます。',
            ]);
        }

        return DB::transaction(function () use (
            $work,
            $data
        ): WorkStorySection {
            [
                $sectionData,
                $events,
                $characters,
            ] = $this->splitData($data);

            $this->validateParent(
                $work,
                $sectionData['parent_section_id']
                    ?? null
            );
            $this->validateCharacters($work, $characters);

            $sectionData['work_id'] = $work->id;
            $sectionData['sort_order'] = max(
                0,
                (int) ($sectionData['sort_order'] ?? 0)
            );

            $section = $this->repository->create(
                $sectionData
            );

            $this->repository->syncEvents($section, $events);
            $this->repository->syncCharacters(
                $section,
                $characters
            );

            return $section;
        });
    }

    public function update(
        Work $work,
        WorkStorySection $section,
        array $data
    ): bool {
        $this->assertBelongsToWork($work, $section);

        return DB::transaction(function () use (
            $work,
            $section,
            $data
        ): bool {
            [
                $sectionData,
                $events,
                $characters,
            ] = $this->splitData($data);

            $this->validateParent(
                $work,
                $sectionData['parent_section_id']
                    ?? null,
                $section
            );
            $this->validateCharacters($work, $characters);

            $sectionData['sort_order'] = max(
                0,
                (int) ($sectionData['sort_order'] ?? 0)
            );

            $updated = $this->repository->update(
                $section,
                $sectionData
            );

            $this->repository->syncEvents($section, $events);
            $this->repository->syncCharacters(
                $section,
                $characters
            );

            return $updated;
        });
    }

    public function delete(
        Work $work,
        WorkStorySection $section
    ): bool {
        $this->assertBelongsToWork($work, $section);

        if ($section->childSections()->exists()) {
            throw ValidationException::withMessages([
                'section' =>
                    '子となる章・話が登録されているため削除できません。',
            ]);
        }

        return $this->repository->delete($section);
    }

    public function assertBelongsToWork(
        Work $work,
        WorkStorySection $section
    ): void {
        abort_unless(
            (int) $section->work_id === (int) $work->id,
            404
        );
    }

    private function splitData(array $data): array
    {
        $events = array_values($data['events'] ?? []);
        $characters = array_values(
            $data['section_characters'] ?? []
        );

        unset(
            $data['events'],
            $data['section_characters']
        );

        $data['parent_section_id'] = filled(
            $data['parent_section_id'] ?? null
        )
            ? (int) $data['parent_section_id']
            : null;

        return [$data, $events, $characters];
    }

    private function validateParent(
        Work $work,
        ?int $parentId,
        ?WorkStorySection $current = null
    ): void {
        if (! $parentId) {
            return;
        }

        if (
            $current
            && $parentId === (int) $current->id
        ) {
            throw ValidationException::withMessages([
                'parent_section_id' =>
                    '自分自身を親の編・部に設定できません。',
            ]);
        }

        $parent = WorkStorySection::query()->find($parentId);

        if (
            ! $parent
            || (int) $parent->work_id !== (int) $work->id
        ) {
            throw ValidationException::withMessages([
                'parent_section_id' =>
                    '同じ作品の章・編を選択してください。',
            ]);
        }

        if ($parent->parent_section_id !== null) {
            throw ValidationException::withMessages([
                'parent_section_id' =>
                    '章・編の階層は2階層までです。',
            ]);
        }

        if (
            $current
            && $current->childSections()->exists()
        ) {
            throw ValidationException::withMessages([
                'parent_section_id' =>
                    '子章を持つ項目を別の編・部の下へ移動できません。',
            ]);
        }
    }

    private function validateCharacters(
        Work $work,
        array $characters
    ): void {
        $ids = collect($characters)
            ->pluck('character_id')
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->count() !== count($characters)) {
            throw ValidationException::withMessages([
                'section_characters' =>
                    '同じキャラクターを重複登録できません。',
            ]);
        }

        if ($ids->isEmpty()) {
            return;
        }

        $allowedCount = Character::query()
            ->whereIn('id', $ids)
            ->whereHas(
                'linkedWorks',
                fn ($query) =>
                    $query->where('works.id', $work->id)
            )
            ->count();

        if ($allowedCount !== $ids->count()) {
            throw ValidationException::withMessages([
                'section_characters' =>
                    '作品に紐づくキャラクターのみ選択できます。',
            ]);
        }
    }
}
