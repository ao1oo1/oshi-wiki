<?php

namespace App\Services;

use App\Models\OriginalCharacter;
use App\Models\OriginalCharacterRelationship;
use App\Models\SavedPrompt;
use App\Models\User;
use App\Models\WriterStory;
use App\Support\WritingAssistLimits;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WriterCsvService
{
    public const TYPES = [
        'characters' => 'オリジナルキャラクター',
        'relationships' => '関係性',
        'prompts' => '保存プロンプト',
        'stories' => 'ストーリー',
    ];

    public function export(User $user, string $type): StreamedResponse
    {
        $this->assertType($type);

        return response()->streamDownload(
            function () use ($user, $type): void {
                $handle = fopen('php://output', 'wb');
                fwrite($handle, "\xEF\xBB\xBF");
                fputcsv($handle, $this->headers($type), ',', '"', '');

                foreach ($this->rowsForExport($user, $type) as $row) {
                    fputcsv($handle, $row, ',', '"', '');
                }

                fclose($handle);
            },
            'oshi-wiki-'.$type.'-'.now()->format('Ymd-His').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function sample(string $type): StreamedResponse
    {
        $this->assertType($type);

        return response()->streamDownload(
            function () use ($type): void {
                $handle = fopen('php://output', 'wb');
                fwrite($handle, "\xEF\xBB\xBF");
                fputcsv($handle, $this->headers($type), ',', '"', '');
                fputcsv($handle, $this->sampleRow($type), ',', '"', '');
                fclose($handle);
            },
            'oshi-wiki-'.$type.'-sample.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function import(
        User $user,
        string $type,
        UploadedFile $file
    ): int {
        $this->assertType($type);
        $rows = $this->readRows($file, $type);
        $this->assertLimit($user, $type, count($rows));

        return DB::transaction(function () use ($user, $type, $rows): int {
            $created = 0;
            $errors = [];

            foreach ($rows as $row) {
                try {
                    $this->create($user, $type, $row['data']);
                    $created++;
                } catch (\Throwable $e) {
                    $errors[] = $row['line'].'行目: '.$e->getMessage();
                }
            }

            if ($errors !== []) {
                throw ValidationException::withMessages([
                    'csv_file' => $errors,
                ]);
            }

            return $created;
        });
    }

    public function headers(string $type): array
    {
        return match ($type) {
            'characters' => [
                'name','name_kana','age','gender','affiliation',
                'school_grade','first_person','speech_style',
                'speech_examples','personality','appearance',
                'background','is_main_character','important_points',
                'ng_points','notes','status',
            ],
            'relationships' => [
                'from_character_name','to_character_name',
                'called_name','relationship_type','impression',
                'notes','timeline_items_json','status',
            ],
            'prompts' => [
                'title','category','purpose','synopsis','prompt_body',
                'notes','writing_style','writing_style_other',
                'genre','genre_other','plot_opening',
                'plot_development','plot_turn','plot_conclusion',
                'use_story_length_options','story_length_type',
                'output_plot_first','output_in_parts',
            ],
            'stories' => [
                'title','episode_number','body','memo','status',
            ],
        };
    }

    private function rowsForExport(User $user, string $type): iterable
    {
        if ($type === 'characters') {
            foreach (
                OriginalCharacter::query()
                    ->where('user_id', $user->id)
                    ->orderBy('id')
                    ->cursor() as $item
            ) {
                $row = $item->only($this->headers($type));
                $row['is_main_character'] =
                    $item->is_main_character ? '1' : '0';

                yield $this->ordered($type, $row);
            }

            return;
        }

        if ($type === 'relationships') {
            foreach (
                OriginalCharacterRelationship::query()
                    ->with(['fromCharacter', 'toCharacter'])
                    ->where('user_id', $user->id)
                    ->where('from_character_source', 'original')
                    ->where('to_character_source', 'original')
                    ->orderBy('id')
                    ->cursor() as $item
            ) {
                yield $this->ordered($type, [
                    'from_character_name' =>
                        $item->fromCharacter?->name ?? '',
                    'to_character_name' =>
                        $item->toCharacter?->name ?? '',
                    'called_name' => $item->called_name,
                    'relationship_type' => $item->relationship_type,
                    'impression' => $item->impression,
                    'notes' => $item->notes,
                    'timeline_items_json' => json_encode(
                        $item->timeline_items ?? [],
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    ),
                    'status' => $item->status,
                ]);
            }

            return;
        }

        if ($type === 'prompts') {
            foreach (
                SavedPrompt::query()
                    ->where('user_id', $user->id)
                    ->orderBy('id')
                    ->cursor() as $item
            ) {
                $row = $item->only($this->headers($type));

                foreach ([
                    'use_story_length_options',
                    'output_plot_first',
                    'output_in_parts',
                ] as $field) {
                    $row[$field] = $item->{$field} ? '1' : '0';
                }

                yield $this->ordered($type, $row);
            }

            return;
        }

        foreach (
            WriterStory::query()
                ->where('user_id', $user->id)
                ->orderBy('episode_number')
                ->orderBy('id')
                ->cursor() as $item
        ) {
            yield $this->ordered(
                $type,
                $item->only($this->headers($type))
            );
        }
    }

    private function readRows(
        UploadedFile $file,
        string $type
    ): array {
        $handle = fopen((string) $file->getRealPath(), 'rb');

        if (! is_resource($handle)) {
            throw ValidationException::withMessages([
                'csv_file' => 'CSVファイルを開けません。',
            ]);
        }

        $header = fgetcsv($handle, 0, ',', '"', '');

        if (! is_array($header)) {
            fclose($handle);

            throw ValidationException::withMessages([
                'csv_file' => 'CSVのヘッダー行がありません。',
            ]);
        }

        $header = array_map(
            fn ($value): string => trim(
                preg_replace('/^\xEF\xBB\xBF/', '', (string) $value)
                ?? (string) $value
            ),
            $header
        );

        $required = match ($type) {
            'characters' => ['name'],
            'relationships' => [
                'from_character_name',
                'to_character_name',
            ],
            'prompts' => ['title', 'prompt_body'],
            'stories' => ['title', 'body'],
        };

        $missing = array_diff($required, $header);

        if ($missing !== []) {
            fclose($handle);

            throw ValidationException::withMessages([
                'csv_file' =>
                    '必須列がありません: '.implode(', ', $missing),
            ]);
        }

        $rows = [];
        $line = 1;

        while (($values = fgetcsv(
            $handle,
            0,
            ',',
            '"',
            ''
        )) !== false) {
            $line++;

            if (
                collect($values)->every(
                    fn ($value): bool =>
                        trim((string) $value) === ''
                )
            ) {
                continue;
            }

            $values = array_pad($values, count($header), '');

            $rows[] = [
                'line' => $line,
                'data' => array_combine(
                    $header,
                    array_slice($values, 0, count($header))
                ),
            ];

            if (count($rows) > 2000) {
                fclose($handle);

                throw ValidationException::withMessages([
                    'csv_file' =>
                        '一度に取り込めるのは2,000行までです。',
                ]);
            }
        }

        fclose($handle);

        return $rows;
    }

    private function assertLimit(
        User $user,
        string $type,
        int $incoming
    ): void {
        $current = match ($type) {
            'characters' => OriginalCharacter::query()
                ->where('user_id', $user->id)->count(),
            'relationships' => OriginalCharacterRelationship::query()
                ->where('user_id', $user->id)->count(),
            'prompts' => SavedPrompt::query()
                ->where('user_id', $user->id)->count(),
            'stories' => WriterStory::query()
                ->where('user_id', $user->id)->count(),
        };

        $limit = match ($type) {
            'characters' =>
                WritingAssistLimits::originalCharactersPerUser($user),
            'relationships' =>
                WritingAssistLimits::relationshipsPerUser($user),
            'prompts' =>
                WritingAssistLimits::promptsPerUser($user),
            'stories' =>
                WritingAssistLimits::storiesPerUser($user),
        };

        if ($limit !== null && ($current + $incoming) > $limit) {
            throw ValidationException::withMessages([
                'csv_file' =>
                    "登録上限を超えます。現在{$current}件、"
                    ."取込予定{$incoming}件、上限{$limit}件です。",
            ]);
        }
    }

    private function create(
        User $user,
        string $type,
        array $row
    ): void {
        match ($type) {
            'characters' => $this->createCharacter($user, $row),
            'relationships' => $this->createRelationship($user, $row),
            'prompts' => $this->createPrompt($user, $row),
            'stories' => $this->createStory($user, $row),
        };
    }

    private function createCharacter(User $user, array $row): void
    {
        OriginalCharacter::query()->create([
            'user_id' => $user->id,
            'name' => $this->required($row, 'name'),
            'name_kana' => $this->nullable($row, 'name_kana'),
            'age' => $this->nullable($row, 'age'),
            'gender' => $this->nullable($row, 'gender'),
            'affiliation' => $this->nullable($row, 'affiliation'),
            'school_grade' => $this->nullable($row, 'school_grade'),
            'first_person' => $this->nullable($row, 'first_person'),
            'speech_style' => $this->nullable($row, 'speech_style'),
            'speech_examples' =>
                $this->nullable($row, 'speech_examples'),
            'personality' => $this->nullable($row, 'personality'),
            'appearance' => $this->nullable($row, 'appearance'),
            'background' => $this->nullable($row, 'background'),
            'is_main_character' =>
                $this->bool($row, 'is_main_character'),
            'important_points' =>
                $this->nullable($row, 'important_points'),
            'ng_points' => $this->nullable($row, 'ng_points'),
            'notes' => $this->nullable($row, 'notes'),
            'status' => 'active',
        ]);
    }

    private function createRelationship(User $user, array $row): void
    {
        $from = $this->findCharacter(
            $user,
            $this->required($row, 'from_character_name')
        );
        $to = $this->findCharacter(
            $user,
            $this->required($row, 'to_character_name')
        );

        $timeline = $this->nullable($row, 'timeline_items_json');
        $timelineItems = $timeline
            ? json_decode($timeline, true)
            : [];

        if (! is_array($timelineItems)) {
            throw new RuntimeException(
                'timeline_items_jsonはJSON配列で指定してください。'
            );
        }

        OriginalCharacterRelationship::query()->create([
            'user_id' => $user->id,
            'from_character_source' => 'original',
            'to_character_source' => 'original',
            'from_original_character_id' => $from->id,
            'to_original_character_id' => $to->id,
            'called_name' => $this->nullable($row, 'called_name'),
            'relationship_type' =>
                $this->nullable($row, 'relationship_type'),
            'impression' => $this->nullable($row, 'impression'),
            'notes' => $this->nullable($row, 'notes'),
            'timeline_items' => $timelineItems,
            'status' => 'active',
        ]);
    }

    private function createPrompt(User $user, array $row): void
    {
        SavedPrompt::query()->create([
            'user_id' => $user->id,
            'title' => $this->required($row, 'title'),
            'category' => $this->choice(
                $row,
                'category',
                array_keys(SavedPrompt::categoryLabels()),
                'other'
            ),
            'purpose' => $this->nullable($row, 'purpose'),
            'synopsis' => $this->nullable($row, 'synopsis'),
            'prompt_body' => $this->required($row, 'prompt_body'),
            'notes' => $this->nullable($row, 'notes'),
            'writing_style' => $this->nullableChoice(
                $row,
                'writing_style',
                array_keys(SavedPrompt::writingStyleLabels())
            ),
            'writing_style_other' =>
                $this->nullable($row, 'writing_style_other'),
            'genre' => $this->nullableChoice(
                $row,
                'genre',
                array_keys(SavedPrompt::genreLabels())
            ),
            'genre_other' => $this->nullable($row, 'genre_other'),
            'plot_opening' => $this->nullable($row, 'plot_opening'),
            'plot_development' =>
                $this->nullable($row, 'plot_development'),
            'plot_turn' => $this->nullable($row, 'plot_turn'),
            'plot_conclusion' =>
                $this->nullable($row, 'plot_conclusion'),
            'use_story_length_options' =>
                $this->bool($row, 'use_story_length_options'),
            'story_length_type' => $this->nullableChoice(
                $row,
                'story_length_type',
                ['short', 'long']
            ),
            'output_plot_first' =>
                $this->bool($row, 'output_plot_first', true),
            'output_in_parts' =>
                $this->bool($row, 'output_in_parts', true),
            'work_source' => 'original',
            'status' => 'active',
        ]);
    }

    private function createStory(User $user, array $row): void
    {
        WriterStory::query()->create([
            'user_id' => $user->id,
            'title' => $this->required($row, 'title'),
            'episode_number' =>
                $this->nullableInt($row, 'episode_number'),
            'body' => $this->required($row, 'body'),
            'memo' => $this->nullable($row, 'memo'),
            'status' => $this->choice(
                $row,
                'status',
                ['active', 'draft'],
                'active'
            ),
        ]);
    }

    private function findCharacter(
        User $user,
        string $name
    ): OriginalCharacter {
        $items = OriginalCharacter::query()
            ->where('user_id', $user->id)
            ->where('name', $name)
            ->get();

        if ($items->isEmpty()) {
            throw new RuntimeException(
                "「{$name}」が見つかりません。"
                .'先にキャラクターCSVを取り込んでください。'
            );
        }

        if ($items->count() > 1) {
            throw new RuntimeException(
                "「{$name}」が複数登録されています。"
                .'名前を一意にしてください。'
            );
        }

        return $items->first();
    }

    private function required(array $row, string $key): string
    {
        $value = $this->nullable($row, $key);

        if ($value === null) {
            throw new RuntimeException("{$key}は必須です。");
        }

        return $value;
    }

    private function nullable(array $row, string $key): ?string
    {
        $value = trim((string) ($row[$key] ?? ''));

        return $value === '' ? null : $value;
    }

    private function bool(
        array $row,
        string $key,
        bool $default = false
    ): bool {
        $value = strtolower(
            trim((string) ($row[$key] ?? ''))
        );

        if ($value === '') {
            return $default;
        }

        if (in_array($value, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($value, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        throw new RuntimeException(
            "{$key}は0または1で指定してください。"
        );
    }

    private function nullableInt(
        array $row,
        string $key
    ): ?int {
        $value = $this->nullable($row, $key);

        if ($value === null) {
            return null;
        }

        if (! ctype_digit($value)) {
            throw new RuntimeException(
                "{$key}は整数で指定してください。"
            );
        }

        return (int) $value;
    }

    private function choice(
        array $row,
        string $key,
        array $allowed,
        string $default
    ): string {
        $value = $this->nullable($row, $key) ?? $default;

        if (! in_array($value, $allowed, true)) {
            throw new RuntimeException(
                "{$key}の値が正しくありません。"
            );
        }

        return $value;
    }

    private function nullableChoice(
        array $row,
        string $key,
        array $allowed
    ): ?string {
        $value = $this->nullable($row, $key);

        if ($value === null) {
            return null;
        }

        if (! in_array($value, $allowed, true)) {
            throw new RuntimeException(
                "{$key}の値が正しくありません。"
            );
        }

        return $value;
    }

    private function ordered(string $type, array $data): array
    {
        return array_map(
            fn (string $header) => $data[$header] ?? '',
            $this->headers($type)
        );
    }

    private function sampleRow(string $type): array
    {
        return match ($type) {
            'characters' => $this->ordered($type, [
                'name' => '夢乃',
                'name_kana' => 'ゆめの',
                'age' => '18',
                'gender' => '女性',
                'is_main_character' => '1',
                'status' => 'active',
            ]),
            'relationships' => $this->ordered($type, [
                'from_character_name' => '夢乃',
                'to_character_name' => '相手の名前',
                'relationship_type' => '友人',
                'timeline_items_json' => '[]',
                'status' => 'active',
            ]),
            'prompts' => $this->ordered($type, [
                'title' => '日常シーン',
                'category' => 'scene',
                'prompt_body' => '会話を書いてください。',
                'output_plot_first' => '1',
                'output_in_parts' => '1',
            ]),
            'stories' => $this->ordered($type, [
                'title' => '第1話',
                'episode_number' => '1',
                'body' => '本文',
                'status' => 'draft',
            ]),
        };
    }

    private function assertType(string $type): void
    {
        abort_unless(
            array_key_exists($type, self::TYPES),
            404
        );
    }
}
