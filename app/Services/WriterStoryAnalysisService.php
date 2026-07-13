<?php

namespace App\Services;

use App\Models\User;
use App\Models\WriterStoryAnalysis;
use App\Repositories\WriterStoryAnalysisRepository;
use App\Repositories\WriterStoryRepository;
use Illuminate\Validation\ValidationException;

class WriterStoryAnalysisService
{
    public function __construct(
        private readonly WriterStoryAnalysisRepository $repository,
        private readonly WriterStoryRepository $storyRepository,
        private readonly WriterStoryService $storyService
    ) {
    }

    public function paginateForUser(User $user)
    {
        return $this->repository->paginateForUser($user);
    }

    public function createForUser(
        User $user,
        array $data
    ): WriterStoryAnalysis {
        $storyIds = array_values(
            array_unique(
                array_map(
                    'intval',
                    $data['story_ids'] ?? []
                )
            )
        );

        $stories = $this->storyRepository->findSelectedForUser(
            $user,
            $storyIds
        );

        if ($stories->isEmpty()) {
            throw ValidationException::withMessages([
                'story_ids' =>
                    '分析対象のストーリーが見つかりませんでした。',
            ]);
        }

        if ($stories->count() !== count($storyIds)) {
            throw ValidationException::withMessages([
                'story_ids' =>
                    '閲覧できないストーリーが含まれています。',
            ]);
        }

        $analysisNotes = trim(
            (string) ($data['analysis_notes'] ?? '')
        );

        /*
         * ブラウザから巨大なプロンプト本文を送信させず、
         * 選択ストーリーからサーバー側で同じ内容を再生成する。
         */
        $analysisPrompt = $this->storyService
            ->buildAnalysisPrompt(
                $user,
                $storyIds,
                $analysisNotes !== ''
                    ? $analysisNotes
                    : null
            );

        $snapshot = $stories
            ->map(function ($story): array {
                return [
                    'id' => (int) $story->id,
                    'title' => $story->title,
                    'episode_number' =>
                        $story->episode_number !== null
                            ? (int) $story->episode_number
                            : null,
                ];
            })
            ->values()
            ->all();

        $title = trim(
            (string) ($data['analysis_title'] ?? '')
        );

        if ($title === '') {
            $title = '文体分析 '
                . now()->format('Y/m/d H:i');
        }

        return $this->repository->create([
            'user_id' => $user->id,
            'title' => $title,
            'selected_story_ids' => $storyIds,
            'story_snapshot' => $snapshot,
            'analysis_notes' =>
                $analysisNotes !== ''
                    ? $analysisNotes
                    : null,
            'analysis_prompt' => $analysisPrompt,
            'analysis_result' => trim(
                (string) $data['analysis_result']
            ),
        ]);
    }
}
