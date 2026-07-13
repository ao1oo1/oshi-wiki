<?php

namespace App\Services;

use App\Models\User;
use App\Models\WriterStory;
use App\Models\WriterStoryAnalysis;
use App\Repositories\WriterStoryAnalysisRepository;
use App\Repositories\WriterStoryRepository;
use App\Support\WritingAssistLimits;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Validation\ValidationException;

class WriterStoryAnalysisService
{
    public function __construct(
        private readonly WriterStoryAnalysisRepository $repository,
        private readonly WriterStoryRepository $storyRepository,
        private readonly WriterStoryService $storyService
    ) {
    }

    public function paginateForUser(
        User $user
    ): LengthAwarePaginator {
        $analyses = $this->repository->paginateForUser($user);

        $this->attachReferencedStories(
            $user,
            collect($analyses->items())
        );

        return $analyses;
    }

    public function prepareForDisplay(
        User $user,
        WriterStoryAnalysis $analysis
    ): WriterStoryAnalysis {
        $this->attachReferencedStories(
            $user,
            collect([$analysis])
        );

        return $analysis;
    }

    public function countForUser(User $user): int
    {
        return $this->repository->countForUser($user);
    }

    public function buildPrompt(
        User $user,
        array $storyIds,
        ?string $analysisNotes = null
    ): string {
        $storyIds = $this->normalizeStoryIds($storyIds);

        $this->validateStories($user, $storyIds);

        return $this->storyService->buildAnalysisPrompt(
            $user,
            $storyIds,
            $analysisNotes
        );
    }

    public function createForUser(
        User $user,
        array $data
    ): WriterStoryAnalysis {
        $this->ensureCanCreate($user);

        $storyIds = $this->normalizeStoryIds(
            $data['story_ids'] ?? []
        );

        $this->validateStories($user, $storyIds);

        return $this->repository->create([
            'user_id' => $user->id,
            'title' => trim((string) $data['title']),
            'selected_story_ids' => $storyIds,
            'analysis_notes' => $this->nullableTrim(
                $data['analysis_notes'] ?? null
            ),
            'analysis_prompt' => trim(
                (string) $data['analysis_prompt']
            ),
            'analysis_result' => $this->nullableTrim(
                $data['analysis_result'] ?? null
            ),
        ]);
    }

    public function update(
        User $user,
        WriterStoryAnalysis $analysis,
        array $data
    ): WriterStoryAnalysis {
        $storyIds = $this->normalizeStoryIds(
            $data['story_ids'] ?? []
        );

        $this->validateStories($user, $storyIds);

        return $this->repository->update($analysis, [
            'title' => trim((string) $data['title']),
            'selected_story_ids' => $storyIds,
            'analysis_notes' => $this->nullableTrim(
                $data['analysis_notes'] ?? null
            ),
            'analysis_prompt' => trim(
                (string) $data['analysis_prompt']
            ),
            'analysis_result' => $this->nullableTrim(
                $data['analysis_result'] ?? null
            ),
        ]);
    }

    public function updateResult(
        WriterStoryAnalysis $analysis,
        string $analysisResult
    ): WriterStoryAnalysis {
        return $this->repository->update($analysis, [
            'analysis_result' => trim($analysisResult),
        ]);
    }

    public function deleteResult(
        WriterStoryAnalysis $analysis
    ): WriterStoryAnalysis {
        return $this->repository->update($analysis, [
            'analysis_result' => null,
        ]);
    }

    public function delete(
        WriterStoryAnalysis $analysis
    ): bool {
        return $this->repository->delete($analysis);
    }

    private function ensureCanCreate(User $user): void
    {
        $limit = WritingAssistLimits::storyAnalysesPerUser($user);

        if (
            $limit !== null
            && $this->countForUser($user) >= $limit
        ) {
            throw ValidationException::withMessages([
                'title' =>
                    '文体分析は最大'
                    . number_format($limit)
                    . '件まで保存できます。'
                    . '新しく登録する場合は、'
                    . '不要な文体分析を削除してください。',
            ]);
        }
    }

    private function normalizeStoryIds(
        array $storyIds
    ): array {
        return collect($storyIds)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function validateStories(
        User $user,
        array $storyIds
    ): Collection {
        if ($storyIds === []) {
            throw ValidationException::withMessages([
                'story_ids' =>
                    '分析対象のストーリーを指定してください。',
            ]);
        }

        $stories = $this->storyRepository
            ->findSelectedForUser($user, $storyIds);

        if ($stories->count() !== count($storyIds)) {
            throw ValidationException::withMessages([
                'story_ids' =>
                    '閲覧できないストーリーが含まれています。',
            ]);
        }

        return $stories;
    }

    private function attachReferencedStories(
        User $user,
        SupportCollection $analyses
    ): void {
        $storyIds = $analyses
            ->flatMap(
                fn (WriterStoryAnalysis $analysis): array =>
                    $analysis->selected_story_ids ?? []
            )
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        $storyMap = WriterStory::query()
            ->forUser($user)
            ->whereIn('id', $storyIds)
            ->get()
            ->keyBy(
                fn (WriterStory $story): int =>
                    (int) $story->id
            );

        foreach ($analyses as $analysis) {
            $resolved = collect(
                $analysis->selected_story_ids ?? []
            )->map(
                function ($id) use ($storyMap): array {
                    $storyId = (int) $id;

                    return [
                        'id' => $storyId,
                        'story' => $storyMap->get($storyId),
                    ];
                }
            );

            $analysis->setAttribute(
                'resolved_stories',
                $resolved
            );
        }
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
