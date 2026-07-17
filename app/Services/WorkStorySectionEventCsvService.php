<?php

namespace App\Services;

use App\Models\WorkStorySection;
use App\Models\WorkStorySectionEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class WorkStorySectionEventCsvService
{
    public const HEADERS = [
        'story_event_id',
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

    public function export(
        WorkStorySection $section
    ): string {
        $handle = $this->handle();

        $section->events()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->chunkById(
                500,
                function ($events) use ($handle): void {
                    foreach ($events as $event) {
                        fputcsv(
                            $handle,
                            $this->row($event),
                            ',',
                            '"',
                            ''
                        );
                    }
                }
            );

        return $this->contents($handle);
    }

    public function sample(): string
    {
        $handle = $this->handle();

        fputcsv($handle, [
            '',
            '1',
            '最初の出来事',
            '章の冒頭',
            '出来事の詳しい内容を入力します。',
            '学園正門',
            '主人公が学園へ到着します。',
            'minor',
            '補足事項',
            '1',
        ], ',', '"', '');

        fputcsv($handle, [
            '',
            '2',
            '次の出来事',
            '到着後',
            '2件目の出来事の内容です。',
            '学生寮',
            '寮での生活が始まります。',
            'none',
            '',
            '2',
        ], ',', '"', '');

        return $this->contents($handle);
    }

    public function import(
        string $path,
        WorkStorySection $section
    ): array {
        [$header, $rows] = $this->readRows($path);

        $result = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $currentCount = $section->events()->count();

        $remainingCount = max(0, 500 - $currentCount);
        $newRowCount = 0;

        foreach ($rows as [$line, $data]) {
            $eventId = $this->int(
                $data['story_event_id'] ?? null
            );

            if ($eventId === null) {
                $newRowCount++;
            }
        }

        if (
            $currentCount + $newRowCount
                > WorkStorySectionService::MAX_EVENTS_PER_SECTION
        ) {
            throw ValidationException::withMessages([
                'csv_file' =>
                    '取り込み後の物語詳細が500件を超えます。'
                    . " 現在{$currentCount}件、"
                    . "新規行{$newRowCount}件です。",
            ]);
        }

        foreach ($rows as [$line, $data]) {
            try {
                if ($this->isEmptyData($data)) {
                    $result['skipped']++;
                    continue;
                }

                $title = trim(
                    (string) ($data['title'] ?? '')
                );

                if ($title === '') {
                    throw new RuntimeException(
                        'titleは必須です。'
                    );
                }

                if (mb_strlen($title) > 255) {
                    throw new RuntimeException(
                        'titleは255文字以内で入力してください。'
                    );
                }

                $eventId = $this->int(
                    $data['story_event_id'] ?? null
                );

                $event = null;

                if ($eventId !== null) {
                    $event = WorkStorySectionEvent::query()
                        ->where(
                            'work_story_section_id',
                            $section->id
                        )
                        ->find($eventId);

                    if (! $event) {
                        throw new RuntimeException(
                            '指定したstory_event_idは'
                            . 'この章の物語詳細ではありません。'
                        );
                    }
                }

                $payload = [
                    'event_number' =>
                        $this->int(
                            $data['event_number'] ?? null
                        ),
                    'title' => $title,
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
                        $this->spoilerLevel(
                            $data['spoiler_level']
                                ?? null
                        ),
                    'notes' =>
                        $this->text(
                            $data['notes'] ?? null
                        ),
                    'sort_order' =>
                        $this->int(
                            $data['sort_order'] ?? null
                        ) ?? 0,
                ];

                DB::transaction(
                    function () use (
                        $section,
                        $event,
                        $payload,
                        &$result
                    ): void {
                        if ($event) {
                            $event->update($payload);
                            $result['updated']++;

                            return;
                        }

                        $section->events()->create($payload);
                        $result['created']++;
                    }
                );
            } catch (\Throwable $exception) {
                $result['errors'][] =
                    "{$line}行目：{$exception->getMessage()}";
            }
        }

        return $result;
    }

    private function readRows(
        string $path
    ): array {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'csv_file' =>
                    'CSVファイルを開けませんでした。',
            ]);
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            throw ValidationException::withMessages([
                'csv_file' =>
                    'CSVファイルが空です。',
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

        if (! in_array('title', $header, true)) {
            fclose($handle);

            throw ValidationException::withMessages([
                'csv_file' =>
                    'CSVに必須列 title がありません。',
            ]);
        }

        $unknownHeaders = array_values(
            array_diff($header, self::HEADERS)
        );

        if ($unknownHeaders !== []) {
            fclose($handle);

            throw ValidationException::withMessages([
                'csv_file' =>
                    '使用できない列があります：'
                    . implode(', ', $unknownHeaders),
            ]);
        }

        $rows = [];
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;

            $row = count($row) < count($header)
                ? array_pad(
                    $row,
                    count($header),
                    null
                )
                : array_slice(
                    $row,
                    0,
                    count($header)
                );

            $data = array_combine($header, $row);

            if ($data !== false) {
                $rows[] = [$line, $data];
            }
        }

        fclose($handle);

        return [$header, $rows];
    }

    private function handle()
    {
        $handle = fopen('php://temp', 'r+b');

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv(
            $handle,
            self::HEADERS,
            ',',
            '"',
            ''
        );

        return $handle;
    }

    private function row(
        WorkStorySectionEvent $event
    ): array {
        return [
            $event->id,
            $event->event_number,
            $event->title,
            $event->timing,
            $event->summary,
            $event->location,
            $event->outcome,
            $event->spoiler_level,
            $event->notes,
            $event->sort_order,
        ];
    }

    private function contents($handle): string
    {
        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }

    private function isEmptyData(array $data): bool
    {
        return collect($data)->every(
            fn ($value): bool =>
                trim((string) $value) === ''
        );
    }

    private function text(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function int(mixed $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (! ctype_digit($value)) {
            throw new RuntimeException(
                '数値列に数値以外が入力されています。'
            );
        }

        return (int) $value;
    }

    private function spoilerLevel(
        mixed $value
    ): string {
        $value = trim((string) $value);

        if ($value === '') {
            return 'none';
        }

        $normalized = match ($value) {
            'なし' => 'none',
            '軽度' => 'minor',
            '重大' => 'major',
            default => $value,
        };

        if (! in_array(
            $normalized,
            ['none', 'minor', 'major'],
            true
        )) {
            throw new RuntimeException(
                'spoiler_levelはnone、minor、major'
                . 'のいずれかで入力してください。'
            );
        }

        return $normalized;
    }
}
