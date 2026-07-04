<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CharacterCsvImportService
{
    public function __construct(
        private CharacterService $characterService
    ) {
    }

    public function import(string $filePath, ?int $defaultWorkId = null, string $defaultStatus = 'draft'): array
    {
        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'csv_file' => 'CSVファイルを開けませんでした。',
            ]);
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            throw ValidationException::withMessages([
                'csv_file' => 'CSVファイルが空です。',
            ]);
        }

        $header = $this->normalizeHeader($header);

        $requiredColumns = ['name'];
        $missingColumns = array_diff($requiredColumns, $header);

        if (! empty($missingColumns)) {
            fclose($handle);

            throw ValidationException::withMessages([
                'csv_file' => 'CSVに必須列がありません：' . implode(', ', $missingColumns),
            ]);
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

            $workId = $data['work_id'] ?? null;
            $workId = $workId !== null && $workId !== '' ? (int) $workId : $defaultWorkId;

            $status = $data['status'] ?? null;
            $status = $status ?: $defaultStatus;

            $characterData = [
                'work_id' => $workId,
                'name' => $data['name'] ?? null,
                'name_kana' => $data['name_kana'] ?? null,
                'age' => $data['age'] ?? null,
                'affiliation' => $data['affiliation'] ?? null,
                'grade_class' => $data['grade_class'] ?? null,
                'first_person' => $data['first_person'] ?? null,
                'tone' => $data['tone'] ?? null,
                'tone_examples' => $data['tone_examples'] ?? null,
                'personality' => $data['personality'] ?? null,
                'appearance' => $data['appearance'] ?? null,
                'background' => $data['background'] ?? null,
                'status' => $status,
            ];

            $validator = Validator::make($characterData, [
                'work_id' => ['required', 'exists:works,id'],
                'name' => ['required', 'string', 'max:255'],
                'name_kana' => ['nullable', 'string', 'max:255'],
                'age' => ['nullable', 'string', 'max:255'],
                'affiliation' => ['nullable', 'string', 'max:255'],
                'grade_class' => ['nullable', 'string', 'max:255'],
                'first_person' => ['nullable', 'string', 'max:255'],
                'tone' => ['nullable', 'string'],
                'tone_examples' => ['nullable', 'string'],
                'personality' => ['nullable', 'string'],
                'appearance' => ['nullable', 'string'],
                'background' => ['nullable', 'string'],
                'status' => ['required', 'in:draft,published,private'],
            ], [], [
                'work_id' => '作品',
                'name' => '名前',
                'status' => '状態',
            ]);

            if ($validator->fails()) {
                $errors[] = "{$rowNumber}行目：" . implode(' / ', $validator->errors()->all());
                continue;
            }

            $this->characterService->create($validator->validated());
            $imported++;
        }

        fclose($handle);

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    private function normalizeHeader(array $header): array
    {
        return array_map(function ($value) {
            $value = (string) $value;
            $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
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
        if (count($row) < $count) {
            return array_pad($row, $count, null);
        }

        if (count($row) > $count) {
            return array_slice($row, 0, $count);
        }

        return $row;
    }
}
