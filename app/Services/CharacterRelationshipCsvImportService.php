<?php

namespace App\Services;

use App\Models\Character;
use App\Models\CharacterRelationship;
use App\Models\Work;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CharacterRelationshipCsvImportService
{
    private const ALLOWED_STATUSES = ['draft', 'published', 'private'];

    public function import(string $path, ?int $defaultWorkId, string $defaultStatus = 'draft'): array
    {
        $content = file_get_contents($path);

        if ($content === false) {
            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['CSVファイルを読み込めませんでした。'],
            ];
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8,SJIS-win,CP932');

        $handle = fopen('php://temp', 'r+b');
        fwrite($handle, $content);
        rewind($handle);

        $header = fgetcsv($handle);

        if (! is_array($header)) {
            fclose($handle);

            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['CSVのヘッダー行を読み込めませんでした。'],
            ];
        }

        $header = array_map(fn ($value) => $this->normalizeHeader((string) $value), $header);

        $requiredHeaders = [
            'work_id',
            'from_character_id',
            'to_character_id',
        ];

        $missingHeaders = array_values(array_diff($requiredHeaders, $header));

        if ($missingHeaders !== []) {
            fclose($handle);

            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['必須ヘッダーが不足しています: ' . implode(', ', $missingHeaders)],
            ];
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $lineNumber = 1;

        DB::transaction(function () use (
            $handle,
            $header,
            $defaultWorkId,
            $defaultStatus,
            &$imported,
            &$skipped,
            &$errors,
            &$lineNumber
        ) {
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;

                if ($this->isEmptyRow($row)) {
                    $skipped++;
                    continue;
                }

                $row = array_pad($row, count($header), '');
                $data = array_combine($header, array_slice($row, 0, count($header)));

                if (! is_array($data)) {
                    $errors[] = "{$lineNumber}行目: CSV行の読み込みに失敗しました。";
                    continue;
                }

                $workId = $defaultWorkId ?: $this->intOrNull($data['work_id'] ?? null);
                $fromCharacterId = $this->intOrNull($data['from_character_id'] ?? null);
                $toCharacterId = $this->intOrNull($data['to_character_id'] ?? null);

                if (! $workId) {
                    $errors[] = "{$lineNumber}行目: work_id は必須です。";
                    continue;
                }

                if (! $fromCharacterId) {
                    $errors[] = "{$lineNumber}行目: from_character_id は必須です。";
                    continue;
                }

                if (! $toCharacterId) {
                    $errors[] = "{$lineNumber}行目: to_character_id は必須です。";
                    continue;
                }

                if ($fromCharacterId === $toCharacterId) {
                    $errors[] = "{$lineNumber}行目: from_character_id と to_character_id には別のキャラクターを指定してください。";
                    continue;
                }

                if (! Work::query()->whereKey($workId)->exists()) {
                    $errors[] = "{$lineNumber}行目: 指定された work_id の作品が存在しません。";
                    continue;
                }

                $fromCharacter = Character::query()
                    ->whereKey($fromCharacterId)
                    ->where('work_id', $workId)
                    ->first();

                if (! $fromCharacter) {
                    $errors[] = "{$lineNumber}行目: from_character_id のキャラクターが指定作品内に存在しません。";
                    continue;
                }

                $toCharacter = Character::query()
                    ->whereKey($toCharacterId)
                    ->where('work_id', $workId)
                    ->first();

                if (! $toCharacter) {
                    $errors[] = "{$lineNumber}行目: to_character_id のキャラクターが指定作品内に存在しません。";
                    continue;
                }

                $status = $this->clean($data['status'] ?? '') ?: $defaultStatus;

                if (! in_array($status, self::ALLOWED_STATUSES, true)) {
                    $errors[] = "{$lineNumber}行目: status は draft / published / private のいずれかを指定してください。";
                    continue;
                }

                CharacterRelationship::query()->create([
                    'work_id' => $workId,
                    'from_character_id' => $fromCharacterId,
                    'to_character_id' => $toCharacterId,
                    'called_name' => $this->nullableText($data['called_name'] ?? null),
                    'relationship' => $this->nullableText($data['relationship'] ?? null),
                    'impression' => $this->nullableText($data['impression'] ?? null),
                    'notes' => $this->nullableText($data['notes'] ?? null),
                    'status' => $status,
                ]);

                $imported++;
            }
        });

        fclose($handle);

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    private function normalizeHeader(string $value): string
    {
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
}
