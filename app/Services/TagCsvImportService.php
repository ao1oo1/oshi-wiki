<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TagCsvImportService
{
    public function __construct(private TagService $tagService)
    {
    }

    public function import(string $filePath, string $defaultStatus = 'draft'): array
    {
        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages(['csv_file' => 'CSVファイルを開けませんでした。']);
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);
            throw ValidationException::withMessages(['csv_file' => 'CSVファイルが空です。']);
        }

        $header = $this->normalizeHeader($header);

        if (! in_array('name', $header, true)) {
            fclose($handle);
            throw ValidationException::withMessages(['csv_file' => 'CSVに必須列 name がありません。']);
        }

        $imported = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($this->isEmptyRow($row)) {
                $skipped++;
                continue;
            }

            $row = $this->fixRowLength($row, count($header));
            $data = array_combine($header, $row);

            if ($data === false) {
                $errors[] = "{$rowNumber}行目：列数が一致しません。";
                continue;
            }

            $data = $this->normalizeData($data);
            $data['status'] = $data['status'] ?: $defaultStatus;

            $tagId = $this->intOrNull($data['tag_id'] ?? ($data['id'] ?? null));

            $tagData = [
                'name' => $data['name'] ?? null,
                'type' => $data['type'] ?? 'general',
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? $defaultStatus,
            ];

            $validator = Validator::make($tagData, [
                'name' => ['required', 'string', 'max:255'],
                'type' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'status' => ['required', 'in:draft,published,private'],
            ], [], ['name' => 'タグ名']);

            if ($validator->fails()) {
                $errors[] = "{$rowNumber}行目：" . implode(' / ', $validator->errors()->all());
                continue;
            }

            $existingTag = $tagId
                ? Tag::query()->whereKey($tagId)->first()
                : null;

            if ($existingTag) {
                $existingTag->update($validator->validated());
                $updated++;
            } else {
                $this->tagService->create($validator->validated());
                $created++;
            }

            $imported++;
        }

        fclose($handle);

        return compact('imported', 'created', 'updated', 'skipped', 'errors');
    }

    private function normalizeHeader(array $header): array
    {
        return array_map(function ($value) {
            $value = preg_replace('/^\xEF\xBB\xBF/', '', (string) $value);
            return trim($value);
        }, $header);
    }

    private function normalizeData(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $value = is_string($value) ? trim($value) : $value;
            $normalized[$key] = $value === '' ? null : $value;
        }

        return $normalized;
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

    private function fixRowLength(array $row, int $count): array
    {
        return count($row) < $count
            ? array_pad($row, $count, null)
            : array_slice($row, 0, $count);
    }

    private function intOrNull(mixed $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        return (int) $value;
    }
}
