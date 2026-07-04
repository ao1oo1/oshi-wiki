<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class WorkCsvImportService
{
    public function __construct(private WorkService $workService)
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

        if (! in_array('title', $header, true)) {
            fclose($handle);
            throw ValidationException::withMessages(['csv_file' => 'CSVに必須列 title がありません。']);
        }

        $imported = 0;
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

            $workData = [
                'title' => $data['title'] ?? null,
                'title_kana' => $data['title_kana'] ?? null,
                'genre' => $data['genre'] ?? null,
                'original_media' => $data['original_media'] ?? null,
                'official_url' => $data['official_url'] ?? null,
                'guideline_url' => $data['guideline_url'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? $defaultStatus,
            ];

            $validator = Validator::make($workData, [
                'title' => ['required', 'string', 'max:255'],
                'title_kana' => ['nullable', 'string', 'max:255'],
                'genre' => ['nullable', 'string', 'max:255'],
                'original_media' => ['nullable', 'string', 'max:255'],
                'official_url' => ['nullable', 'url', 'max:1000'],
                'guideline_url' => ['nullable', 'url', 'max:1000'],
                'description' => ['nullable', 'string'],
                'status' => ['required', 'in:draft,published,private'],
            ], [], ['title' => '作品名']);

            if ($validator->fails()) {
                $errors[] = "{$rowNumber}行目：" . implode(' / ', $validator->errors()->all());
                continue;
            }

            $this->workService->create($validator->validated());
            $imported++;
        }

        fclose($handle);

        return compact('imported', 'skipped', 'errors');
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
}
