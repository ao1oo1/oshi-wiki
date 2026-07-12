<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Work;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CharacterCsvImportService
{
    private const ALLOWED_STATUSES = ['draft', 'published', 'private'];

    /**
     * CSV取り込み
     *
     * - character_id / id が既存キャラクターIDと一致する場合は更新
     * - character_id / id が空、または一致する既存データがない場合は新規登録
     * - 新規登録時はCSVのIDを使わず、DBのAUTO_INCREMENTに任せる
     */
    public function import(string $path, ?int $defaultWorkId, string $defaultStatus = 'draft'): array
    {
        $content = file_get_contents($path);

        if ($content === false) {
            return [
                'imported' => 0,
                'updated' => 0,
                'created' => 0,
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
                'updated' => 0,
                'created' => 0,
                'skipped' => 0,
                'errors' => ['CSVのヘッダー行を読み込めませんでした。'],
            ];
        }

        $header = array_map(fn ($value) => $this->normalizeHeader((string) $value), $header);

        $missingHeaders = [];

        if (! in_array('work_id', $header, true)) {
            $missingHeaders[] = 'work_id';
        }

        // エクスポートCSVは character_name、従来サンプルCSVは name のため両方許可する
        if (! in_array('name', $header, true) && ! in_array('character_name', $header, true)) {
            $missingHeaders[] = 'name または character_name';
        }

        if ($missingHeaders !== []) {
            fclose($handle);

            return [
                'imported' => 0,
                'updated' => 0,
                'created' => 0,
                'skipped' => 0,
                'errors' => ['必須ヘッダーが不足しています: ' . implode(', ', $missingHeaders)],
            ];
        }

        $imported = 0;
        $updated = 0;
        $created = 0;
        $skipped = 0;
        $errors = [];
        $lineNumber = 1;

        DB::transaction(function () use (
            $handle,
            $header,
            $defaultWorkId,
            $defaultStatus,
            &$imported,
            &$updated,
            &$created,
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

                $characterId = $this->intOrNull($data['character_id'] ?? ($data['id'] ?? null));

                // CSV内の work_id を優先し、空の場合だけ画面で選択した作品IDを使う
                $csvWorkId = $this->intOrNull($data['work_id'] ?? null);
                $workId = $csvWorkId ?: $defaultWorkId;

                if (! $workId) {
                    $errors[] = "{$lineNumber}行目: work_id は必須です。";
                    continue;
                }

                if (! Work::query()->whereKey($workId)->exists()) {
                    $errors[] = "{$lineNumber}行目: 指定された work_id の作品が存在しません。";
                    continue;
                }

                $name = $this->clean($data['name'] ?? ($data['character_name'] ?? ''));

                if ($name === '') {
                    $errors[] = "{$lineNumber}行目: name は必須です。";
                    continue;
                }

                $status = $this->clean($data['status'] ?? '') ?: $defaultStatus;

                if (! in_array($status, self::ALLOWED_STATUSES, true)) {
                    $errors[] = "{$lineNumber}行目: status は draft / published / private のいずれかを指定してください。";
                    continue;
                }

                $payload = $this->payload($data, $workId, $name, $status);

                $character = $characterId
                    ? Character::query()->whereKey($characterId)->first()
                    : null;

                if ($character) {
                    $character->update($payload);
                    $updated++;
                } else {
                    Character::query()->create($payload);
                    $created++;
                }

                $imported++;
            }
        });

        fclose($handle);

        return [
            'imported' => $imported,
            'updated' => $updated,
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    private function payload(array $data, int $workId, string $name, string $status): array
    {
        $payload = [
            'work_id' => $workId,
            'name' => $name,
            'status' => $status,
        ];

        $columns = [
            'name_kana',
            'age',
            'affiliation',
            'grade_class',
            'first_person',
            'tone',
            'tone_examples',
            'personality',
            'appearance',
            'background',
            'review_status',
            'reviewed_at',
            'reviewed_by',
        ];

        foreach ($columns as $column) {
            if (! Schema::hasColumn('characters', $column)) {
                continue;
            }

            if (! array_key_exists($column, $data)) {
                continue;
            }

            $payload[$column] = $this->nullableText($data[$column]);
        }

        return $payload;
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
