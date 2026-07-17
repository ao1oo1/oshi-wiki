<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkStorySectionCsvService
{
    public const SECTION_HEADERS = [
        'story_section_id',
        'work_id',
        'work_title',
        'parent_section_id',
        'parent_section_title',
        'section_type',
        'section_number',
        'title',
        'title_kana',
        'short_label',
        'synopsis',
        'cumulative_settings',
        'notes',
        'spoiler_level',
        'sort_order',
        'status',
    ];

    public const EVENT_HEADERS = [
        'story_event_id',
        'story_section_id',
        'work_id',
        'section_title',
        'event_number',
        'title',
        'timing',
        'summary',
        'location',
        'outcome',
        'spoiler_level',
        'notes',
        'sort_order',
    ];

    public const CHARACTER_HEADERS = [
        'story_section_character_id',
        'story_section_id',
        'work_id',
        'section_title',
        'character_id',
        'character_name',
        'appearance_type',
        'age_at_section',
        'school_grade_at_section',
        'class_at_section',
        'affiliation_at_section',
        'position_at_section',
        'character_state',
        'first_appearance',
        'notes',
        'sort_order',
    ];

    public function exportSections(Work $work): string
    {
        $handle = $this->handle(self::SECTION_HEADERS);

        WorkStorySection::query()
            ->with(['work', 'parentSection'])
            ->where('work_id', $work->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->chunkById(
                200,
                function ($sections) use ($handle): void {
                    foreach ($sections as $section) {
                        fputcsv($handle, [
                            $section->id,
                            $section->work_id,
                            $section->work?->title ?? '',
                            $section->parent_section_id,
                            $section->parentSection?->title ?? '',
                            $section->section_type,
                            $section->section_number,
                            $section->title,
                            $section->title_kana,
                            $section->short_label,
                            $section->synopsis,
                            $section->cumulative_settings,
                            $section->notes,
                            $section->spoiler_level,
                            $section->sort_order,
                            $section->status,
                        ], ',', '"', '');
                    }
                }
            );

        return $this->contents($handle);
    }

    public function exportEvents(Work $work): string
    {
        $handle = $this->handle(self::EVENT_HEADERS);

        DB::table('work_story_section_events')
            ->join(
                'work_story_sections',
                'work_story_sections.id',
                '=',
                'work_story_section_events.work_story_section_id'
            )
            ->where('work_story_sections.work_id', $work->id)
            ->whereNull('work_story_sections.deleted_at')
            ->orderBy('work_story_sections.sort_order')
            ->orderBy('work_story_section_events.sort_order')
            ->select([
                'work_story_section_events.*',
                'work_story_sections.work_id',
                'work_story_sections.title as section_title',
            ])
            ->chunkById(
                500,
                function ($events) use ($handle): void {
                    foreach ($events as $event) {
                        fputcsv($handle, [
                            $event->id,
                            $event->work_story_section_id,
                            $event->work_id,
                            $event->section_title,
                            $event->event_number,
                            $event->title,
                            $event->timing,
                            $event->summary,
                            $event->location,
                            $event->outcome,
                            $event->spoiler_level,
                            $event->notes,
                            $event->sort_order,
                        ], ',', '"', '');
                    }
                },
                'work_story_section_events.id',
                'id'
            );

        return $this->contents($handle);
    }

    public function exportCharacters(Work $work): string
    {
        $handle = $this->handle(self::CHARACTER_HEADERS);

        DB::table('character_work_story_section')
            ->join(
                'work_story_sections',
                'work_story_sections.id',
                '=',
                'character_work_story_section.work_story_section_id'
            )
            ->join(
                'characters',
                'characters.id',
                '=',
                'character_work_story_section.character_id'
            )
            ->where('work_story_sections.work_id', $work->id)
            ->whereNull('work_story_sections.deleted_at')
            ->orderBy('work_story_sections.sort_order')
            ->orderBy(
                'character_work_story_section.sort_order'
            )
            ->select([
                'character_work_story_section.*',
                'work_story_sections.work_id',
                'work_story_sections.title as section_title',
                'characters.name as character_name',
            ])
            ->chunkById(
                500,
                function ($rows) use ($handle): void {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->id,
                            $row->work_story_section_id,
                            $row->work_id,
                            $row->section_title,
                            $row->character_id,
                            $row->character_name,
                            $row->appearance_type,
                            $row->age_at_section,
                            $row->school_grade_at_section,
                            $row->class_at_section,
                            $row->affiliation_at_section,
                            $row->position_at_section,
                            $row->character_state,
                            $row->first_appearance ? '1' : '0',
                            $row->notes,
                            $row->sort_order,
                        ], ',', '"', '');
                    }
                },
                'character_work_story_section.id',
                'id'
            );

        return $this->contents($handle);
    }

    public function sample(string $type): string
    {
        return match ($type) {
            'sections' => $this->csv(
                self::SECTION_HEADERS,
                [[
                    '',
                    '1',
                    '作品タイトル',
                    '',
                    '',
                    'chapter',
                    '1',
                    '第1章 物語の始まり',
                    'ダイイッショウ',
                    '1章',
                    '章の概要',
                    'この章までに登場する設定',
                    '備考',
                    'minor',
                    '1',
                    'draft',
                ]]
            ),
            'events' => $this->csv(
                self::EVENT_HEADERS,
                [[
                    '',
                    '1',
                    '1',
                    '第1章 物語の始まり',
                    '1',
                    '最初の出来事',
                    '章冒頭',
                    '出来事の詳細',
                    '学園',
                    '出来事の結果',
                    'minor',
                    '備考',
                    '1',
                ]]
            ),
            'characters' => $this->csv(
                self::CHARACTER_HEADERS,
                [[
                    '',
                    '1',
                    '1',
                    '第1章 物語の始まり',
                    '10',
                    'キャラクター名',
                    'main',
                    '16歳',
                    '1年',
                    'A組',
                    '学園',
                    '生徒',
                    '当時の状態',
                    '1',
                    '備考',
                    '1',
                ]]
            ),
            default => throw ValidationException::withMessages([
                'type' => 'CSV種別が正しくありません。',
            ]),
        };
    }

    public function import(
        string $type,
        string $path,
        Work $work,
        string $defaultStatus = 'draft'
    ): array {
        return match ($type) {
            'sections' => $this->importSections(
                $path,
                $work,
                $defaultStatus
            ),
            'events' => $this->importEvents($path, $work),
            'characters' => $this->importCharacters(
                $path,
                $work
            ),
            default => throw ValidationException::withMessages([
                'type' => 'CSV種別が正しくありません。',
            ]),
        };
    }

    private function importSections(
        string $path,
        Work $work,
        string $defaultStatus
    ): array {
        $rows = $this->rows($path, ['title']);
        $result = $this->result();

        foreach ($rows as [$line, $data]) {
            try {
                $sectionId = $this->int(
                    $data['story_section_id'] ?? null
                );

                $section = $sectionId
                    ? WorkStorySection::query()
                        ->where('work_id', $work->id)
                        ->find($sectionId)
                    : null;

                if (
                    ! $section
                    && $work->allStorySections()->count()
                        >= WorkStorySectionService::MAX_SECTIONS_PER_WORK
                ) {
                    throw new \RuntimeException(
                        '章・編は1作品につき最大30件までです。'
                    );
                }

                $parentId = $this->resolveParentId(
                    $work,
                    $data
                );

                $payload = [
                    'work_id' => $work->id,
                    'parent_section_id' => $parentId,
                    'section_type' =>
                        trim(
                            (string) (
                                $data['section_type']
                                    ?? 'chapter'
                            )
                        ) ?: 'chapter',
                    'section_number' =>
                        $this->int(
                            $data['section_number'] ?? null
                        ),
                    'title' =>
                        trim((string) $data['title']),
                    'title_kana' =>
                        $this->text(
                            $data['title_kana'] ?? null
                        ),
                    'short_label' =>
                        $this->text(
                            $data['short_label'] ?? null
                        ),
                    'synopsis' =>
                        $this->text(
                            $data['synopsis'] ?? null
                        ),
                    'cumulative_settings' =>
                        $this->text(
                            $data['cumulative_settings']
                                ?? null
                        ),
                    'notes' =>
                        $this->text(
                            $data['notes'] ?? null
                        ),
                    'spoiler_level' =>
                        trim(
                            (string) (
                                $data['spoiler_level']
                                    ?? 'none'
                            )
                        ) ?: 'none',
                    'sort_order' =>
                        $this->int(
                            $data['sort_order'] ?? null
                        ) ?? 0,
                    'status' =>
                        trim(
                            (string) (
                                $data['status']
                                    ?? $defaultStatus
                            )
                        ) ?: $defaultStatus,
                    'updated_by' => auth()->id(),
                ];

                $this->validateSectionPayload($payload);

                if ($section) {
                    $section->update($payload);
                    $result['updated']++;
                } else {
                    $payload['created_by'] = auth()->id();
                    WorkStorySection::query()->create($payload);
                    $result['created']++;
                }
            } catch (\Throwable $exception) {
                $result['errors'][] =
                    "{$line}行目：{$exception->getMessage()}";
            }
        }

        return $result;
    }

    private function importEvents(
        string $path,
        Work $work
    ): array {
        $rows = $this->rows(
            $path,
            ['story_section_id', 'title']
        );
        $result = $this->result();

        foreach ($rows as [$line, $data]) {
            try {
                $section = WorkStorySection::query()
                    ->where('work_id', $work->id)
                    ->findOrFail(
                        $this->int(
                            $data['story_section_id']
                        )
                    );

                $eventId = $this->int(
                    $data['story_event_id'] ?? null
                );

                $event = $eventId
                    ? $section->events()->find($eventId)
                    : null;

                if (
                    ! $event
                    && $section->events()->count()
                        >= WorkStorySectionService::MAX_EVENTS_PER_SECTION
                ) {
                    throw new \RuntimeException(
                        '1章につき物語詳細は最大100件です。'
                    );
                }

                $payload = [
                    'event_number' =>
                        $this->int(
                            $data['event_number'] ?? null
                        ),
                    'title' =>
                        trim((string) $data['title']),
                    'timing' =>
                        $this->text(
                            $data['timing'] ?? null
                        ),
                    'summary' =>
                        $this->text(
                            $data['summary'] ?? null
                        ),
                    'location' =>
                        $this->text(
                            $data['location'] ?? null
                        ),
                    'outcome' =>
                        $this->text(
                            $data['outcome'] ?? null
                        ),
                    'spoiler_level' =>
                        trim(
                            (string) (
                                $data['spoiler_level']
                                    ?? 'none'
                            )
                        ) ?: 'none',
                    'notes' =>
                        $this->text(
                            $data['notes'] ?? null
                        ),
                    'sort_order' =>
                        $this->int(
                            $data['sort_order'] ?? null
                        ) ?? 0,
                ];

                if ($event) {
                    $event->update($payload);
                    $result['updated']++;
                } else {
                    $section->events()->create($payload);
                    $result['created']++;
                }
            } catch (\Throwable $exception) {
                $result['errors'][] =
                    "{$line}行目：{$exception->getMessage()}";
            }
        }

        return $result;
    }

    private function importCharacters(
        string $path,
        Work $work
    ): array {
        $rows = $this->rows(
            $path,
            ['story_section_id', 'character_id']
        );
        $result = $this->result();

        foreach ($rows as [$line, $data]) {
            try {
                $section = WorkStorySection::query()
                    ->where('work_id', $work->id)
                    ->findOrFail(
                        $this->int(
                            $data['story_section_id']
                        )
                    );

                $characterId = $this->int(
                    $data['character_id']
                );

                $character = Character::query()
                    ->whereKey($characterId)
                    ->whereHas(
                        'linkedWorks',
                        fn ($query) =>
                            $query->where(
                                'works.id',
                                $work->id
                            )
                    )
                    ->firstOrFail();

                $exists = DB::table(
                    'character_work_story_section'
                )
                    ->where(
                        'work_story_section_id',
                        $section->id
                    )
                    ->where('character_id', $character->id)
                    ->exists();

                $payload = [
                    'appearance_type' =>
                        trim(
                            (string) (
                                $data['appearance_type']
                                    ?? 'appears'
                            )
                        ) ?: 'appears',
                    'age_at_section' =>
                        $this->text(
                            $data['age_at_section'] ?? null
                        ),
                    'school_grade_at_section' =>
                        $this->text(
                            $data['school_grade_at_section']
                                ?? null
                        ),
                    'class_at_section' =>
                        $this->text(
                            $data['class_at_section'] ?? null
                        ),
                    'affiliation_at_section' =>
                        $this->text(
                            $data['affiliation_at_section']
                                ?? null
                        ),
                    'position_at_section' =>
                        $this->text(
                            $data['position_at_section']
                                ?? null
                        ),
                    'character_state' =>
                        $this->text(
                            $data['character_state'] ?? null
                        ),
                    'first_appearance' =>
                        $this->bool(
                            $data['first_appearance'] ?? false
                        ),
                    'notes' =>
                        $this->text(
                            $data['notes'] ?? null
                        ),
                    'sort_order' =>
                        $this->int(
                            $data['sort_order'] ?? null
                        ) ?? 0,
                    'updated_at' => now(),
                ];

                DB::table(
                    'character_work_story_section'
                )->updateOrInsert(
                    [
                        'work_story_section_id' =>
                            $section->id,
                        'character_id' => $character->id,
                    ],
                    array_merge(
                        $payload,
                        $exists
                            ? []
                            : ['created_at' => now()]
                    )
                );

                $exists
                    ? $result['updated']++
                    : $result['created']++;
            } catch (\Throwable $exception) {
                $result['errors'][] =
                    "{$line}行目：{$exception->getMessage()}";
            }
        }

        return $result;
    }

    private function resolveParentId(
        Work $work,
        array $data
    ): ?int {
        $parentId = $this->int(
            $data['parent_section_id'] ?? null
        );

        if ($parentId) {
            return WorkStorySection::query()
                ->where('work_id', $work->id)
                ->whereNull('parent_section_id')
                ->findOrFail($parentId)
                ->id;
        }

        $title = trim(
            (string) (
                $data['parent_section_title'] ?? ''
            )
        );

        if ($title === '') {
            return null;
        }

        $matches = WorkStorySection::query()
            ->where('work_id', $work->id)
            ->whereNull('parent_section_id')
            ->where('title', $title)
            ->get();

        if ($matches->count() !== 1) {
            throw new \RuntimeException(
                "親の章・編「{$title}」を一意に特定できません。"
            );
        }

        return (int) $matches->first()->id;
    }

    private function validateSectionPayload(
        array $payload
    ): void {
        if (
            ! array_key_exists(
                $payload['section_type'],
                WorkStorySection::TYPES
            )
        ) {
            throw new \RuntimeException(
                'section_typeが正しくありません。'
            );
        }

        if (
            ! array_key_exists(
                $payload['spoiler_level'],
                WorkStorySection::SPOILER_LEVELS
            )
        ) {
            throw new \RuntimeException(
                'spoiler_levelが正しくありません。'
            );
        }

        if (
            ! in_array(
                $payload['status'],
                ['draft', 'published', 'private'],
                true
            )
        ) {
            throw new \RuntimeException(
                'statusが正しくありません。'
            );
        }
    }

    private function rows(
        string $path,
        array $required
    ): array {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'csv_file' => 'CSVファイルを開けません。',
            ]);
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            throw ValidationException::withMessages([
                'csv_file' => 'CSVファイルが空です。',
            ]);
        }

        $header = array_map(
            fn ($value): string => trim(
                preg_replace(
                    '/^\xEF\xBB\xBF/',
                    '',
                    (string) $value
                ) ?? ''
            ),
            $header
        );

        foreach ($required as $column) {
            if (! in_array($column, $header, true)) {
                fclose($handle);

                throw ValidationException::withMessages([
                    'csv_file' =>
                        "必須列 {$column} がありません。",
                ]);
            }
        }

        $rows = [];
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;

            if (
                collect($row)->every(
                    fn ($value) =>
                        trim((string) $value) === ''
                )
            ) {
                continue;
            }

            $row = count($row) < count($header)
                ? array_pad($row, count($header), null)
                : array_slice($row, 0, count($header));

            $data = array_combine($header, $row);

            if ($data !== false) {
                $rows[] = [$line, $data];
            }
        }

        fclose($handle);

        return $rows;
    }

    private function handle(array $headers)
    {
        $handle = fopen('php://temp', 'r+b');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headers, ',', '"', '');

        return $handle;
    }

    private function csv(
        array $headers,
        array $rows
    ): string {
        $handle = $this->handle($headers);

        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"', '');
        }

        return $this->contents($handle);
    }

    private function contents($handle): string
    {
        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }

    private function result(): array
    {
        return [
            'created' => 0,
            'updated' => 0,
            'errors' => [],
        ];
    }

    private function text(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function int(mixed $value): ?int
    {
        $value = trim((string) $value);

        return $value !== '' && ctype_digit($value)
            ? (int) $value
            : null;
    }

    private function bool(mixed $value): bool
    {
        return in_array(
            mb_strtolower(trim((string) $value)),
            ['1', 'true', 'yes', 'on', 'はい'],
            true
        );
    }
}
