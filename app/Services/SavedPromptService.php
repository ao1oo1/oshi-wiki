<?php

namespace App\Services;

use App\Models\Character;
use App\Models\OriginalCharacter;
use App\Models\OriginalCharacterRelationship;
use App\Models\SavedPrompt;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use App\Models\WriterStoryAnalysis;
use App\Repositories\SavedPromptRepository;
use App\Support\WritingAssistLimits;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SavedPromptService
{
    public function __construct(
        private readonly SavedPromptRepository $repository,
        private readonly PromptCharacterContextBuilder $contextBuilder,
        private readonly WorkWorldbuildingPromptBuilder $worldbuildingBuilder,
        private readonly WorkStorySectionPromptBuilder $storySectionBuilder
    ) {
    }

    public function paginateForUser(
        User $user,
        array $filters = []
    ) {
        return $this->repository->paginateForUser(
            $user,
            20,
            $filters
        );
    }

    public function countForUser(User $user): int
    {
        return $this->repository->countForUser($user);
    }

    public function createForUser(
        User $user,
        array $data
    ): SavedPrompt {
        $limit = WritingAssistLimits::promptsPerUser($user);

        if (
            $limit !== null
            && $this->repository->countForUser($user) >= $limit
        ) {
            throw ValidationException::withMessages([
                'limit' =>
                    "保存プロンプトは最大{$limit}件まで登録できます。",
            ]);
        }

        $payload = $this->normalizePromptData(
            $user,
            $data
        );

        $payload['user_id'] = $user->id;
        $payload['status'] =
            $payload['status'] ?? 'active';
        $payload['category'] =
            $payload['category'] ?? 'scene';

        $payload['prompt_body'] = $this->buildPromptBody(
            $user,
            $payload
        );

        return $this->repository->create($payload);
    }

    public function createStoryAnalysisPromptForUser(
        User $user,
        array $data
    ): SavedPrompt {
        $limit = WritingAssistLimits::promptsPerUser($user);

        if (
            $limit !== null
            && $this->repository->countForUser($user) >= $limit
        ) {
            throw ValidationException::withMessages([
                'prompt_title' =>
                    "保存プロンプトは最大{$limit}件まで登録できます。",
            ]);
        }

        $title = trim(
            (string) ($data['prompt_title'] ?? '')
        );

        if ($title === '') {
            $title = 'ストーリー分析 '
                . now()->format('Y/m/d H:i');
        }

        return $this->repository->create([
            'user_id' => $user->id,
            'title' => $title,
            'category' => 'other',
            'purpose' => 'ストーリーの文体・構成分析',
            'work_source' => SavedPrompt::WORK_SOURCE_ORIGINAL,
            'work_id' => null,
            'work_story_section_id' => null,
            'selected_character_refs' => [],
            'include_relationship_timeline' => false,
            'include_work_worldbuilding' => false,
            'selected_work_worldbuilding_categories' => [],
            'writing_style' => 'other',
            'writing_style_other' => '文体分析',
            'genre' => 'other',
            'genre_other' => '文体分析',
            'synopsis' => null,
            'plot_opening' => null,
            'plot_development' => null,
            'plot_turn' => null,
            'plot_conclusion' => null,
            'use_story_length_options' => false,
            'story_length_type' => null,
            'output_plot_first' => false,
            'output_in_parts' => false,
            'selected_story_analysis_ids' => [],
            'prompt_body' => trim(
                (string) $data['prompt_body']
            ),
            'notes' => null,
            'status' => 'active',
        ]);
    }

    public function update(
        User $user,
        SavedPrompt $savedPrompt,
        array $data
    ): bool {
        $payload = $this->normalizePromptData(
            $user,
            $data
        );

        $payload['status'] =
            $payload['status'] ?? 'active';
        $payload['category'] =
            $payload['category'] ?? 'scene';

        $payload['prompt_body'] = $this->buildPromptBody(
            $user,
            $payload
        );

        return $this->repository->update(
            $savedPrompt,
            $payload
        );
    }

    public function delete(
        SavedPrompt $savedPrompt
    ): bool {
        return $this->repository->delete($savedPrompt);
    }

    public function previewForUser(
        User $user,
        array $data
    ): string {
        $payload = $this->normalizePromptData(
            $user,
            $data
        );

        return $this->buildPromptBody(
            $user,
            $payload
        );
    }

    public function recordUsage(
        SavedPrompt $savedPrompt
    ): bool {
        $savedPrompt->used_count =
            (int) $savedPrompt->used_count + 1;

        $savedPrompt->last_used_at = now();

        return $savedPrompt->save();
    }

    private function normalizePromptData(
        User $user,
        array $data
    ): array {
        $this->normalizeWork($data);
        $this->normalizeStorySection($data);
        $this->normalizeCharacterReferences($user, $data);
        $this->normalizeStoryAnalysisIds($user, $data);

        $data['include_relationship_timeline'] = (bool) (
            $data['include_relationship_timeline'] ?? false
        );

        $data['include_work_worldbuilding'] = (bool) (
            $data['include_work_worldbuilding'] ?? false
        );

        $allowedWorldbuildingCategories = array_keys(
            SavedPrompt::workWorldbuildingCategoryLabels()
        );

        $data['selected_work_worldbuilding_categories'] = collect(
            $data['selected_work_worldbuilding_categories'] ?? []
        )
            ->filter(fn ($category): bool =>
                is_string($category)
                && in_array($category, $allowedWorldbuildingCategories, true)
            )
            ->unique()
            ->values()
            ->all();

        if (
            ($data['work_source'] ?? null) !== SavedPrompt::WORK_SOURCE_V1
            || ! $data['include_work_worldbuilding']
        ) {
            $data['include_work_worldbuilding'] = false;
            $data['selected_work_worldbuilding_categories'] = [];
        }

        $data['use_story_length_options'] = (bool) (
            $data['use_story_length_options'] ?? false
        );

        if (! $data['use_story_length_options']) {
            $data['story_length_type'] = null;
            $data['output_plot_first'] = false;
            $data['output_in_parts'] = false;
        } else {
            $data['story_length_type'] = in_array(
                $data['story_length_type'] ?? null,
                ['short', 'long'],
                true
            )
                ? $data['story_length_type']
                : 'short';

            $data['output_plot_first'] = (bool) (
                $data['output_plot_first'] ?? false
            );

            $data['output_in_parts'] = (bool) (
                $data['output_in_parts'] ?? false
            );
        }

        unset($data['work_ref']);

        return $data;
    }

    private function normalizeWork(array &$data): void
    {
        $workRef = trim(
            (string) ($data['work_ref'] ?? 'original')
        );

        if (
            preg_match(
                '/^work:(\d+)$/',
                $workRef,
                $matches
            )
        ) {
            $work = Work::query()
                ->with('parentWork')
                ->where('status', 'published')
                ->find((int) $matches[1]);

            if (
                ! $work
                || (
                    $work->parent_work_id !== null
                    && $work->parentWork?->status
                        !== 'published'
                )
            ) {
                throw ValidationException::withMessages([
                    'work_ref' =>
                        '選択した公開作品が見つかりません。',
                ]);
            }

            $data['work_source'] =
                SavedPrompt::WORK_SOURCE_V1;

            $data['work_id'] = $work->id;

            return;
        }

        $data['work_source'] =
            SavedPrompt::WORK_SOURCE_ORIGINAL;

        $data['work_id'] = null;
    }

    private function normalizeStorySection(
        array &$data
    ): void {
        $legacySectionId = (int) (
            $data['work_story_section_id'] ?? 0
        );

        $requestedRanges = collect(
            $data['selected_story_event_ranges'] ?? []
        )
            ->filter(fn ($value): bool => is_string($value))
            ->unique()
            ->values();

        if (
            ($data['work_source'] ?? null)
                !== SavedPrompt::WORK_SOURCE_V1
            || empty($data['work_id'])
        ) {
            $data['work_story_section_id'] = null;
            $data['selected_story_event_ranges'] = [];

            return;
        }

        $workId = (int) $data['work_id'];

        if ($requestedRanges->isNotEmpty()) {
            $normalizedRanges = [];

            foreach ($requestedRanges as $rangeRef) {
                if (
                    ! preg_match(
                        '/^(\d+):(\d+):(\d+)$/',
                        $rangeRef,
                        $matches
                    )
                ) {
                    throw ValidationException::withMessages([
                        'selected_story_event_ranges' =>
                            '物語詳細の選択範囲が不正です。',
                    ]);
                }

                $sectionId = (int) $matches[1];
                $start = (int) $matches[2];
                $end = (int) $matches[3];

                if (
                    $sectionId <= 0
                    || $start <= 0
                    || $end < $start
                    || $end > $start + 19
                    || (($start - 1) % 20) !== 0
                ) {
                    throw ValidationException::withMessages([
                        'selected_story_event_ranges' =>
                            '物語詳細は20件単位で選択してください。',
                    ]);
                }

                $section = $this->publishedSectionForWork(
                    $sectionId,
                    $workId
                );

                if (! $section) {
                    throw ValidationException::withMessages([
                        'selected_story_event_ranges' =>
                            '選択した作品で利用できない'
                            . '章・編が含まれています。',
                    ]);
                }

                $eventCount = $section->events()->count();

                if ($start > $eventCount || $end > $eventCount) {
                    throw ValidationException::withMessages([
                        'selected_story_event_ranges' =>
                            '物語詳細の選択範囲が'
                            . '登録件数を超えています。',
                    ]);
                }

                $normalizedRanges[] = [
                    'section_id' => $section->id,
                    'start' => $start,
                    'end' => $end,
                ];
            }

            $data['work_story_section_id'] = null;
            $data['selected_story_event_ranges'] =
                collect($normalizedRanges)
                    ->sortBy([
                        ['section_id', 'asc'],
                        ['start', 'asc'],
                    ])
                    ->values()
                    ->all();

            return;
        }

        $data['selected_story_event_ranges'] = [];

        if ($legacySectionId <= 0) {
            $data['work_story_section_id'] = null;

            return;
        }

        $section = $this->publishedSectionForWork(
            $legacySectionId,
            $workId
        );

        if (! $section) {
            throw ValidationException::withMessages([
                'work_story_section_id' =>
                    '選択した作品で利用できる公開章・編が'
                    . '見つかりません。',
            ]);
        }

        $data['work_story_section_id'] = $section->id;
    }

    private function publishedSectionForWork(
        int $sectionId,
        int $workId
    ): ?WorkStorySection {
        return WorkStorySection::query()
            ->whereKey($sectionId)
            ->where('work_id', $workId)
            ->where('status', 'published')
            ->whereHas(
                'work',
                function ($query): void {
                    $query
                        ->where('status', 'published')
                        ->where(
                            function ($query): void {
                                $query
                                    ->whereNull(
                                        'parent_work_id'
                                    )
                                    ->orWhereHas(
                                        'parentWork',
                                        fn ($parentQuery) =>
                                            $parentQuery->where(
                                                'status',
                                                'published'
                                            )
                                    );
                            }
                        );
                }
            )
            ->first();
    }

    private function normalizeCharacterReferences(
        User $user,
        array &$data
    ): void {
        $candidateRefs = collect(
            $data['selected_character_refs'] ?? []
        )
            ->filter(
                fn ($value): bool =>
                    is_string($value)
                    && preg_match(
                        '/^(original|v1):\d+$/',
                        $value
                    )
            )
            ->unique()
            ->values();

        $allowedRefs = [];

        $originalIds = $this->idsBySource(
            $candidateRefs,
            'original'
        );

        if ($originalIds !== []) {
            $allowedOriginalIds = OriginalCharacter::query()
                ->where('user_id', $user->id)
                ->whereIn('id', $originalIds)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            foreach ($allowedOriginalIds as $id) {
                $allowedRefs[] = 'original:' . $id;
            }
        }

        $v1Ids = $this->idsBySource(
            $candidateRefs,
            'v1'
        );

        if (
            $v1Ids !== []
            && ($data['work_source'] ?? null)
                === SavedPrompt::WORK_SOURCE_V1
            && ! empty($data['work_id'])
        ) {
            $selectedWorkId = (int) $data['work_id'];

            $allowedV1Ids = Character::query()
                ->whereIn('id', $v1Ids)
                ->where('status', 'published')
                ->whereHas(
                    'linkedWorks',
                    function ($query) use ($selectedWorkId): void {
                        $query
                            ->where('works.id', $selectedWorkId)
                            ->where('works.status', 'published');
                    }
                )
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            foreach ($allowedV1Ids as $id) {
                $allowedRefs[] = 'v1:' . $id;
            }
        }

        $data['selected_character_refs'] =
            array_values(array_unique($allowedRefs));
    }

    private function normalizeStoryAnalysisIds(
        User $user,
        array &$data
    ): void {
        $requestedIds = collect(
            $data['selected_story_analysis_ids'] ?? []
        )
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->take(10)
            ->values()
            ->all();

        if ($requestedIds === []) {
            $data['selected_story_analysis_ids'] = [];

            return;
        }

        $allowedIds = WriterStoryAnalysis::query()
            ->forUser($user)
            ->whereIn('id', $requestedIds)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if (count($allowedIds) !== count($requestedIds)) {
            throw ValidationException::withMessages([
                'selected_story_analysis_ids' =>
                    '選択した文体分析の中に、利用できないデータが含まれています。',
            ]);
        }

        /*
         * チェックボックスで送られた順番を維持する。
         */
        $data['selected_story_analysis_ids'] =
            array_values(
                array_filter(
                    $requestedIds,
                    fn (int $id): bool =>
                        in_array($id, $allowedIds, true)
                )
            );
    }

    private function idsBySource(
        Collection $refs,
        string $source
    ): array {
        return $refs
            ->filter(
                fn ($ref): bool =>
                    str_starts_with(
                        $ref,
                        $source . ':'
                    )
            )
            ->map(
                fn ($ref): int =>
                    (int) str_replace(
                        $source . ':',
                        '',
                        $ref
                    )
            )
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function buildPromptBody(
        User $user,
        array $data
    ): string {
        $workName = $this->resolveWorkName($data);

        $context = $this->contextBuilder->build(
            $user,
            $data['selected_character_refs'] ?? []
        );

        $style = $this->labelFrom(
            SavedPrompt::writingStyleLabels(),
            $data['writing_style'] ?? null,
            $data['writing_style_other'] ?? null
        );

        $genre = $this->labelFrom(
            SavedPrompt::genreLabels(),
            $data['genre'] ?? null,
            $data['genre_other'] ?? null
        );

        $synopsis = $this->safeText(
            $data['synopsis'] ?? null
        );

        $plotOpening = $this->safeText(
            $data['plot_opening'] ?? null
        );

        $plotDevelopment = $this->safeText(
            $data['plot_development'] ?? null
        );

        $plotTurn = $this->safeText(
            $data['plot_turn'] ?? null
        );

        $plotConclusion = $this->safeText(
            $data['plot_conclusion'] ?? null
        );

        $notes = $this->safeText(
            $data['notes'] ?? null
        );

        $storyAnalysisText =
            $this->buildStoryAnalysisText(
                $user,
                $data['selected_story_analysis_ids'] ?? []
            );

        $workWorldbuildingText = (
            (bool) ($data['include_work_worldbuilding'] ?? false)
            && ($data['work_source'] ?? null) === SavedPrompt::WORK_SOURCE_V1
        )
            ? $this->worldbuildingBuilder->build(
                ! empty($data['work_id']) ? (int) $data['work_id'] : null,
                $data['selected_work_worldbuilding_categories'] ?? []
            )
            : '';

        $storySectionText =
            $this->storySectionBuilder->build(
                ! empty($data['work_story_section_id'])
                    ? (int) $data['work_story_section_id']
                    : null,
                $data['selected_story_event_ranges'] ?? []
            );

        $relationshipText =
            $this->buildRelationshipsText(
                $user,
                $context['relationships'] ?? '',
                $data['selected_character_refs'] ?? [],
                (bool) (
                    $data['include_relationship_timeline']
                        ?? false
                )
            );

        $lines = [
            'あなたは小説制作に精通した敏腕編集者です。以下の情報を整理・分析し、プロット作成および小説本文の執筆に必要な要素をまとめたうえで、すぐに執筆を始められる形にしてください。',
            '',
            '【作品】',
            $workName,
        ];

        if ($workWorldbuildingText !== '') {
            $lines[] = '';
            $lines[] = '【作品設定】';
            $lines[] = $workWorldbuildingText;
        }

        if ($storySectionText !== '') {
            $lines[] = '';
            $lines[] = '【参照する章・編】';
            $lines[] = $storySectionText;
        }

        $lines = array_merge($lines, [
            '',
            '【登場人物詳細】',
            $context['characters'] ?: '指定なし',
            '',
            '【関係性】',
            $relationshipText ?: '指定なし',
            '',
            '【参考にする保存済み文体分析】',
            $storyAnalysisText ?: '指定なし',
            '',
            '【作風】',
            $style ?: '指定なし',
            '',
            '【ジャンル】',
            $genre ?: '指定なし',
            '',
            '【あらすじ】',
            $synopsis ?: '指定なし',
            '',
            '【起】',
            $plotOpening ?: '指定なし',
            '',
            '【承】',
            $plotDevelopment ?: '指定なし',
            '',
            '【転】',
            $plotTurn ?: '指定なし',
            '',
            '【結】',
            $plotConclusion ?: '指定なし',
            '',
            '【出力条件】',
            '・上記の設定を守ってください。',
            '・登場人物の一人称、口調、性格、関係性を反映してください。',
            '・選択した文体分析の特徴を参考にし、文章のリズム、描写、会話、視点、語彙の傾向へ反映してください。',
            '・文体分析に記載された文章そのものを転載せず、特徴だけを反映してください。',
            '・登録情報にない設定は断定しないでください。',
            '・不足している情報は、自然な範囲で補ってください。',
        ]);

        if ($notes !== '') {
            $lines[] = '';
            $lines[] = '【備考】';
            $lines[] = $notes;
        }

        $this->appendStoryLengthOptions(
            $lines,
            $data
        );

        return implode(PHP_EOL, $lines);
    }

    private function resolveWorkName(array $data): string
    {
        if (
            ($data['work_source'] ?? null)
                !== SavedPrompt::WORK_SOURCE_V1
            || empty($data['work_id'])
        ) {
            return 'オリジナル';
        }

        $work = Work::query()
            ->with('parentWork')
            ->where('status', 'published')
            ->find((int) $data['work_id']);

        if (
            ! $work
            || (
                $work->parent_work_id !== null
                && $work->parentWork?->status
                    !== 'published'
            )
        ) {
            return '参照できない作品';
        }

        return $work->parentWork
            ? $work->parentWork->title
                . ' ＞ '
                . $work->title
            : $work->title;
    }

    private function buildStoryAnalysisText(
        User $user,
        array $analysisIds
    ): string {
        $analysisIds = collect($analysisIds)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->take(10)
            ->values()
            ->all();

        if ($analysisIds === []) {
            return '';
        }

        $analyses = WriterStoryAnalysis::query()
            ->forUser($user)
            ->whereIn('id', $analysisIds)
            ->get()
            ->sortBy(
                function (
                    WriterStoryAnalysis $analysis
                ) use ($analysisIds): int {
                    $position = array_search(
                        (int) $analysis->id,
                        $analysisIds,
                        true
                    );

                    return $position === false
                        ? PHP_INT_MAX
                        : $position;
                }
            )
            ->values();

        $blocks = [];

        foreach ($analyses as $index => $analysis) {
            $storyTitles = collect(
                $analysis->story_snapshot ?? []
            )
                ->map(function ($snapshot): string {
                    if (! is_array($snapshot)) {
                        return '';
                    }

                    $title = trim(
                        (string) (
                            $snapshot['title'] ?? ''
                        )
                    );

                    if ($title === '') {
                        return '';
                    }

                    $episodeNumber =
                        $snapshot['episode_number'] ?? null;

                    if ($episodeNumber !== null) {
                        return '第'
                            . (int) $episodeNumber
                            . '話：'
                            . $title;
                    }

                    return $title;
                })
                ->filter()
                ->implode('、');

            $analysisResult = trim(
                (string) $analysis->analysis_result
            );

            if ($analysisResult === '') {
                continue;
            }

            $lines = [
                '■ 文体分析' . ($index + 1),
                '管理名：' . $analysis->title,
            ];

            if ($storyTitles !== '') {
                $lines[] =
                    '分析対象ストーリー：'
                    . $storyTitles;
            }

            $lines[] = '分析結果：';
            $lines[] = $analysisResult;

            $blocks[] = implode(PHP_EOL, $lines);
        }

        return implode(
            PHP_EOL . PHP_EOL,
            $blocks
        );
    }

    private function appendStoryLengthOptions(
        array &$lines,
        array $data
    ): void {
        if (
            ! (bool) (
                $data['use_story_length_options']
                    ?? false
            )
        ) {
            return;
        }

        $storyLengthType =
            $data['story_length_type'] ?? 'short';

        $lines[] = '';
        $lines[] = '【長編・短編設定】';

        if ($storyLengthType === 'long') {
            $lines[] = '形式：長編・全10話';
            $lines[] = '・全10話構成にしてください。';
            $lines[] = '・1話あたり約10,000字の完成本文を想定してください。';
            $lines[] = '・各話を「起」「承」「転」「結」の4パートに分けてください。';
            $lines[] = '・各パートは約2,500字を想定してください。';
            $lines[] = '・10話を通して、人物の成長、関係性の変化、伏線と回収を設計してください。';
            $lines[] = '・各話の終わりには、次話につながる引きを入れてください。';
        } else {
            $lines[] = '形式：短編・1話完結';
            $lines[] = '・完成本文は全体で約10,000字を想定してください。';
            $lines[] = '・全体を「起」「承」「転」「結」の4パートに分けてください。';
            $lines[] = '・各パートは約2,500字を想定してください。';
            $lines[] = '・1話の中で主要な問題と感情の変化を完結させてください。';
        }

        if (
            (bool) ($data['output_plot_first'] ?? false)
        ) {
            $lines[] = '・完成本文を書く前に、詳細なプロットを先に出力してください。';
            $lines[] = '・プロットには、場面、登場人物、出来事、感情変化、次の場面へのつなぎを含めてください。';
        }

        if (
            (bool) ($data['output_in_parts'] ?? false)
        ) {
            $lines[] = '・「起」「承」「転」「結」を一度にまとめず、それぞれ明確に分けて順番に出力してください。';
        }
    }

    private function labelFrom(
        array $labels,
        ?string $value,
        ?string $other
    ): string {
        if ($value === 'other') {
            return trim((string) $other);
        }

        return $labels[$value] ?? '';
    }

    private function safeText(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function buildRelationshipsText(
        User $user,
        string $relationshipText,
        array $characterRefs,
        bool $includeTimeline
    ): string {
        $relationshipText = trim($relationshipText);

        if (! $includeTimeline) {
            return $relationshipText;
        }

        $selectedRefs = $this->parseCharacterRefs(
            $characterRefs
        );

        if ($selectedRefs === []) {
            return $relationshipText;
        }

        $relationships =
            OriginalCharacterRelationship::query()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->with([
                    'fromCharacter',
                    'toCharacter',
                    'fromV1Character.work',
                    'toV1Character.work',
                ])
                ->get();

        $timelineBlocks = [];

        foreach ($relationships as $relationship) {
            $fromRef = $relationship->fromReference();
            $toRef = $relationship->toReference();

            if (
                ! $fromRef
                || ! $toRef
                || ! in_array(
                    $fromRef,
                    $selectedRefs,
                    true
                )
                || ! in_array(
                    $toRef,
                    $selectedRefs,
                    true
                )
            ) {
                continue;
            }

            $items = collect(
                $relationship->timeline_items ?? []
            )
                ->filter(function ($item): bool {
                    if (! is_array($item)) {
                        return false;
                    }

                    return trim(
                        (string) ($item['period'] ?? '')
                    ) !== ''
                        || trim(
                            (string) (
                                $item['content'] ?? ''
                            )
                        ) !== '';
                })
                ->values();

            if ($items->isEmpty()) {
                continue;
            }

            $lines = [
                $relationship->fromDisplayName()
                    . ' → '
                    . $relationship->toDisplayName()
                    . ' の年表：',
            ];

            foreach ($items as $item) {
                $period = trim(
                    (string) ($item['period'] ?? '')
                );

                $content = trim(
                    (string) ($item['content'] ?? '')
                );

                $lines[] = '・'
                    . (
                        $period !== ''
                            ? $period
                            : '時期未入力'
                    )
                    . '：'
                    . (
                        $content !== ''
                            ? $content
                            : '内容未入力'
                    );
            }

            $timelineBlocks[] = implode(
                PHP_EOL,
                $lines
            );
        }

        if ($timelineBlocks === []) {
            return $relationshipText;
        }

        $sections = [];

        if ($relationshipText !== '') {
            $sections[] = $relationshipText;
        }

        $sections[] = '【関係性年表】'
            . PHP_EOL
            . implode(
                PHP_EOL . PHP_EOL,
                $timelineBlocks
            );

        return implode(
            PHP_EOL . PHP_EOL,
            $sections
        );
    }

    private function parseCharacterRefs(
        array $characterRefs
    ): array {
        return collect($characterRefs)
            ->filter(
                fn ($ref): bool =>
                    is_string($ref)
                    && preg_match(
                        '/^(original|v1):\d+$/',
                        trim($ref)
                    )
            )
            ->map(fn ($ref): string => trim($ref))
            ->unique()
            ->values()
            ->all();
    }
}
