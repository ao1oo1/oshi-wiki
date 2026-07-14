<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Tag;
use App\Models\Work;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CharacterCsvImportService
{
    private const ALLOWED_STATUSES = ['draft', 'published', 'private'];

    public function import(
        string $path,
        ?int $defaultWorkId,
        string $defaultStatus = 'draft'
    ): array {
        $content = file_get_contents($path);

        if ($content === false) {
            return $this->emptyResult([
                'CSVファイルを読み込めませんでした。',
            ]);
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $content = mb_convert_encoding(
            $content,
            'UTF-8',
            'UTF-8,SJIS-win,CP932'
        );

        $handle = fopen('php://temp', 'r+b');
        fwrite($handle, $content);
        rewind($handle);

        $header = fgetcsv($handle, null, ',', '"', '');

        if (! is_array($header)) {
            fclose($handle);

            return $this->emptyResult([
                'CSVのヘッダー行を読み込めませんでした。',
            ]);
        }

        $header = array_map(
            fn ($value) => $this->normalizeHeader((string) $value),
            $header
        );

        $missingHeaders = [];

        if (! in_array('work_id', $header, true)) {
            $missingHeaders[] = 'work_id';
        }

        if (
            ! in_array('name', $header, true)
            && ! in_array('character_name', $header, true)
        ) {
            $missingHeaders[] = 'name または character_name';
        }

        if ($missingHeaders !== []) {
            fclose($handle);

            return $this->emptyResult([
                '必須ヘッダーが不足しています: '
                    . implode(', ', $missingHeaders),
            ]);
        }

        $result = [
            'imported' => 0,
            'updated' => 0,
            'created' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $lineNumber = 1;

        DB::transaction(function () use (
            $handle,
            $header,
            $defaultWorkId,
            $defaultStatus,
            &$result,
            &$lineNumber
        ): void {
            while (
                ($row = fgetcsv($handle, null, ',', '"', '')) !== false
            ) {
                $lineNumber++;

                if ($this->isEmptyRow($row)) {
                    $result['skipped']++;
                    continue;
                }

                $row = array_pad($row, count($header), '');
                $data = array_combine(
                    $header,
                    array_slice($row, 0, count($header))
                );

                if (! is_array($data)) {
                    $result['errors'][] =
                        "{$lineNumber}行目: CSV行の読み込みに失敗しました。";
                    continue;
                }

                $characterId = $this->intOrNull(
                    $data['character_id'] ?? ($data['id'] ?? null)
                );

                $csvWorkId = $this->intOrNull($data['work_id'] ?? null);
                $workId = $csvWorkId ?: $defaultWorkId;

                if (! $workId) {
                    $result['errors'][] =
                        "{$lineNumber}行目: work_id は必須です。";
                    continue;
                }

                if (! Work::query()->whereKey($workId)->exists()) {
                    $result['errors'][] =
                        "{$lineNumber}行目: 指定された work_id の作品が存在しません。";
                    continue;
                }

                $name = $this->clean(
                    $data['name'] ?? ($data['character_name'] ?? '')
                );

                if ($name === '') {
                    $result['errors'][] =
                        "{$lineNumber}行目: name は必須です。";
                    continue;
                }

                $status = $this->clean($data['status'] ?? '')
                    ?: $defaultStatus;

                if (! in_array($status, self::ALLOWED_STATUSES, true)) {
                    $result['errors'][] =
                        "{$lineNumber}行目: status は draft / published / private のいずれかを指定してください。";
                    continue;
                }

                try {
                    $payload = $this->payload(
                        $data,
                        $workId,
                        $name,
                        $status
                    );
                } catch (\InvalidArgumentException $exception) {
                    $result['errors'][] =
                        "{$lineNumber}行目: {$exception->getMessage()}";
                    continue;
                }

                $character = $characterId
                    ? Character::query()->whereKey($characterId)->first()
                    : null;

                if ($character) {
                    $character->update($payload);
                    $result['updated']++;
                } else {
                    $character = Character::query()->create($payload);
                    $result['created']++;
                }

                $this->syncTags($character, $data);
                $result['imported']++;
            }
        });

        fclose($handle);

        return $result;
    }

    private function payload(
        array $data,
        int $workId,
        string $name,
        string $status
    ): array {
        $data = $this->applyLegacyAliases($data);

        $payload = [
            'work_id' => $workId,
            'name' => $name,
            'status' => $status,
        ];

        $columns = [
            'name_kana',
            'real_name',
            'aliases',
            'name_english',
            'gender',
            'age',
            'birthday',
            'height',
            'weight',
            'blood_type',
            'birthplace',
            'species',
            'affiliation',
            'school_grade_class',
            'occupation_position',
            'family_structure',
            'appearance',
            'personality',
            'first_person',
            'second_person',
            'basic_tone',
            'catchphrases',
            'distinctive_speech',
            'tone_by_relationship',
            'short_quote_examples',
            'abilities',
            'background',
            'story_activities',
            'source_title',
            'source_url',
            'source_type',
            'source_reliability',
            'source_checked_at',
            'spoiler_level',
        ];

        foreach ($columns as $column) {
            if (
                ! Schema::hasColumn('characters', $column)
                || ! array_key_exists($column, $data)
            ) {
                continue;
            }

            $value = $this->nullableText($data[$column]);

            $payload[$column] = match ($column) {
                'source_type' => $this->normalizeEnum(
                    $value,
                    Character::SOURCE_TYPES,
                    'source_type'
                ),
                'source_reliability' => $this->normalizeEnum(
                    $value,
                    Character::SOURCE_RELIABILITIES,
                    'source_reliability'
                ),
                'spoiler_level' => $this->normalizeEnum(
                    $value,
                    Character::SPOILER_LEVELS,
                    'spoiler_level'
                ),
                'source_checked_at' => $this->normalizeDate($value),
                default => $value,
            };
        }

        return $payload;
    }

    private function syncTags(Character $character, array $data): void
    {
        if (
            ! array_key_exists('tag_ids', $data)
            && ! array_key_exists('tag_names', $data)
        ) {
            return;
        }

        $tagIds = collect(
            preg_split(
                '/[,、\s]+/u',
                $this->clean($data['tag_ids'] ?? '')
            ) ?: []
        )
            ->filter(fn (string $value) => ctype_digit($value))
            ->map(fn (string $value) => (int) $value);

        $tagNames = collect(
            preg_split(
                '/[,、\n]+/u',
                $this->clean($data['tag_names'] ?? '')
            ) ?: []
        )
            ->map(fn (string $value) => trim($value))
            ->filter();

        $resolvedIds = Tag::query()
            ->whereIn('id', $tagIds->all())
            ->pluck('id');

        if ($tagNames->isNotEmpty()) {
            $resolvedIds = $resolvedIds->merge(
                Tag::query()
                    ->whereIn('name', $tagNames->all())
                    ->pluck('id')
            );
        }

        $character->tags()->sync(
            $resolvedIds->unique()->values()->all()
        );
    }

    private function applyLegacyAliases(array $data): array
    {
        $aliases = [
            'grade_class' => 'school_grade_class',
            'tone' => 'basic_tone',
            'tone_examples' => 'short_quote_examples',
        ];

        foreach ($aliases as $old => $new) {
            if (
                ! array_key_exists($new, $data)
                && array_key_exists($old, $data)
            ) {
                $data[$new] = $data[$old];
            }
        }

        return $data;
    }

    private function normalizeEnum(
        ?string $value,
        array $options,
        string $field
    ): ?string {
        if ($value === null) {
            return null;
        }

        if (array_key_exists($value, $options)) {
            return $value;
        }

        $key = array_search($value, $options, true);

        if ($key !== false) {
            return (string) $key;
        }

        throw new \InvalidArgumentException(
            "{$field} の値「{$value}」は使用できません。"
        );
    }

    private function normalizeDate(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        foreach (['Y-m-d', 'Y/m/d'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);

            if ($date && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        throw new \InvalidArgumentException(
            "source_checked_at は YYYY-MM-DD 形式で入力してください。"
        );
    }

    private function normalizeHeader(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;

        return Str::of($value)
            ->trim()
            ->lower()
            ->replace([' ', '-'], '_')
            ->toString();
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function clean(mixed $value): string
    {
        return trim((string) $value);
    }

    private function nullableText(mixed $value): ?string
    {
        $value = $this->clean($value);

        return $value === '' ? null : $value;
    }

    private function intOrNull(mixed $value): ?int
    {
        $value = $this->clean($value);

        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        return (int) $value;
    }

    private function emptyResult(array $errors): array
    {
        return [
            'imported' => 0,
            'updated' => 0,
            'created' => 0,
            'skipped' => 0,
            'errors' => $errors,
        ];
    }
}
