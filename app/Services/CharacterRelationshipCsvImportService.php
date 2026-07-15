<?php

namespace App\Services;

use App\Models\Character;
use App\Models\CharacterRelationship;
use App\Models\Work;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class CharacterRelationshipCsvImportService
{
    private const HEADERS = [
        'relationship_id',
        'work_id',
        'from_character_id',
        'to_character_id',
        'called_name',
        'relationship',
        'impression',
        'notes',
        'status',
    ];

    public function import(string $path, string $defaultStatus = 'draft'): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('CSVファイルを開けませんでした。');
        }

        $headers = fgetcsv($handle);

        if (! is_array($headers)) {
            fclose($handle);
            throw new RuntimeException('CSVのヘッダーを読み取れませんでした。');
        }

        $headers = array_map(
            fn ($header) => trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $header)),
            $headers
        );

        foreach (self::HEADERS as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                fclose($handle);
                throw new RuntimeException("必須列「{$requiredHeader}」がありません。");
            }
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $line = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $line++;

            if ($this->isEmptyRow($values)) {
                $skipped++;
                continue;
            }

            $values = array_pad($values, count($headers), '');
            $row = array_combine($headers, array_slice($values, 0, count($headers)));

            try {
                DB::transaction(function () use ($row, $defaultStatus, &$created, &$updated): void {
                    $workId = (int) ($row['work_id'] ?? 0);
                    $fromId = (int) ($row['from_character_id'] ?? 0);
                    $toId = (int) ($row['to_character_id'] ?? 0);

                    $work = Work::query()->findOrFail($workId);
                    $from = Character::query()->findOrFail($fromId);
                    $to = Character::query()->findOrFail($toId);

                    if (
                        ! $from->isLinkedToWork($work->id)
                        || ! $to->isLinkedToWork($work->id)
                    ) {
                        throw new RuntimeException(
                            '指定した作品に両方のキャラクターが紐付いている必要があります。'
                        );
                    }

                    if ($from->id === $to->id) {
                        throw new RuntimeException('同じキャラクター同士は登録できません。');
                    }

                    $status = trim((string) ($row['status'] ?? '')) ?: $defaultStatus;

                    if (! in_array($status, ['draft', 'published', 'private'], true)) {
                        throw new RuntimeException('状態はdraft・published・privateのいずれかで指定してください。');
                    }

                    $data = [
                        'work_id' => $work->id,
                        'from_character_id' => $from->id,
                        'to_character_id' => $to->id,
                        'called_name' => $this->nullable($row['called_name'] ?? null),
                        'relationship' => $this->nullable($row['relationship'] ?? null),
                        'impression' => $this->nullable($row['impression'] ?? null),
                        'notes' => $this->nullable($row['notes'] ?? null),
                        'status' => $status,
                    ];

                    $relationshipId = (int) ($row['relationship_id'] ?? 0);

                    if ($relationshipId > 0) {
                        $model = CharacterRelationship::query()->findOrFail($relationshipId);
                        $model->update($data);
                        $updated++;
                    } else {
                        CharacterRelationship::query()->create($data);
                        $created++;
                    }
                });
            } catch (Throwable $e) {
                report($e);
                $errors[] = "{$line}行目：{$e->getMessage()}";
            }
        }

        fclose($handle);

        return compact('created', 'updated', 'skipped', 'errors');
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function isEmptyRow(array $values): bool
    {
        return collect($values)->every(fn ($value) => trim((string) $value) === '');
    }
}
