<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Tag;
use App\Models\Work;
use App\Models\WorkCanonEvent;
use App\Models\WorkTermUsage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JsonException;

class WorkCsvImportService
{
    public function __construct(private readonly WorkService $workService)
    {
    }

    public function import(string $filePath, string $defaultStatus = 'draft'): array
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

        if (! in_array('title', $header, true)) {
            fclose($handle);
            throw ValidationException::withMessages([
                'csv_file' => 'CSVに必須列 title がありません。',
            ]);
        }

        $result = [
            'imported' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($this->isEmptyRow($row)) {
                $result['skipped']++;
                continue;
            }

            $row = $this->fixRowLength($row, count($header));
            $data = array_combine($header, $row);

            if ($data === false) {
                $result['errors'][] = "{$rowNumber}行目：列数が一致しません。";
                continue;
            }

            try {
                $data = $this->normalizeData($data);
                $workId = $this->intOrNull($data['work_id'] ?? ($data['id'] ?? null));
                $existingWork = $workId ? Work::query()->with([
                    'linkedCharacters', 'tags', 'canonEvents', 'termUsages',
                ])->find($workId) : null;

                $payload = $this->buildPayload(
                    $data,
                    $header,
                    $existingWork,
                    $defaultStatus
                );

                if (
                    in_array('parent_work_title', $header, true)
                    && ! filled($payload['parent_work_id'] ?? null)
                ) {
                    $payload['parent_work_id'] =
                        $this->resolveParentWorkIdByTitle(
                            $data['parent_work_title'] ?? null,
                            $existingWork
                        );
                }

                $validator = Validator::make(
                    $payload,
                    $this->rules(),
                    $this->validationMessages(),
                    $this->validationAttributes()
                );

                if ($validator->fails()) {
                    $result['errors'][] = "{$rowNumber}行目：" .
                        implode(' / ', $validator->errors()->all());
                    continue;
                }

                $validated = $validator->validated();

                $hasCharacterColumns =
                    in_array('character_ids', $header, true)
                    || in_array('character_names', $header, true);

                $resolvedCharacterIds = $hasCharacterColumns
                    ? $this->resolveCharacterIds($data)
                    : null;

                $primaryCharacterIds = $existingWork
                    ? Character::query()
                        ->where('work_id', $existingWork->id)
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id)
                        ->all()
                    : [];

                if (
                    $hasCharacterColumns
                    && array_diff($primaryCharacterIds, $resolvedCharacterIds) !== []
                ) {
                    throw new \InvalidArgumentException(
                        'この作品を主作品にしているキャラクターは解除できません。'
                    );
                }

                DB::transaction(function () use (
                    $existingWork,
                    $validated,
                    $hasCharacterColumns,
                    $resolvedCharacterIds,
                    &$result
                ): void {
                    if ($existingWork) {
                        $this->workService->update($existingWork, $validated);
                        $work = $existingWork->refresh();
                        $result['updated']++;
                    } else {
                        $work = $this->workService->create($validated);
                        $result['created']++;
                    }

                    if ($hasCharacterColumns) {
                        $this->syncCharacters($work, $resolvedCharacterIds);
                    }
                });

                $result['imported']++;
            } catch (JsonException $exception) {
                $result['errors'][] = "{$rowNumber}行目：JSON形式が正しくありません。{$exception->getMessage()}";
            } catch (\Throwable $exception) {
                $result['errors'][] = "{$rowNumber}行目：{$exception->getMessage()}";
            }
        }

        fclose($handle);

        return $result;
    }

    private function buildPayload(
        array $data,
        array $header,
        ?Work $existingWork,
        string $defaultStatus
    ): array {
        $payload = [];

        foreach ($this->importableWorkColumns() as $column) {
            if (array_key_exists($column, $data)) {
                $payload[$column] = $data[$column];
            } elseif ($existingWork) {
                $payload[$column] = $existingWork->{$column};
            }
        }

        $payload['status'] = $payload['status']
            ?? $existingWork?->status
            ?? $defaultStatus;

        $hasTagColumns = in_array('tag_ids', $header, true)
            || in_array('tag_names', $header, true);

        $payload['tag_ids'] = $hasTagColumns
            ? $this->resolveTagIds($data)
            : ($existingWork?->tags->pluck('id')->all() ?? []);

        $payload['canon_events'] = in_array('canon_events_json', $header, true)
            ? $this->decodeRelationRows(
                $data['canon_events_json'] ?? null,
                WorkCanonEvent::class
            )
            : $this->existingRelationRows($existingWork, 'canonEvents', WorkCanonEvent::class);

        $payload['term_usages'] = in_array('term_usages_json', $header, true)
            ? $this->decodeRelationRows(
                $data['term_usages_json'] ?? null,
                WorkTermUsage::class
            )
            : $this->existingRelationRows($existingWork, 'termUsages', WorkTermUsage::class);

        return $payload;
    }

    private function rules(): array
    {
        $rules = [
            'parent_work_id' => [
                'nullable',
                'integer',
                'exists:works,id',
            ],
            'child_sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],
            'title' => ['required', 'string', 'max:255'],
            'title_kana' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'string', 'max:255'],
            'original_media' => ['nullable', 'string', 'max:255'],
            'official_url' => ['nullable', 'url', 'max:2048'],
            'guideline_url' => ['nullable', 'url', 'max:2048'],
            'media_types' => ['nullable', 'array'],
            'media_types.*' => [
                'string',
                'in:anime,manga,game,novel,goods,app,other',
            ],
            'monetization_enabled' => ['nullable', 'boolean'],
            'monetization_inheritance' => [
                'nullable',
                'string',
                'in:self,parent,self_then_parent,disabled',
            ],
            'isbn' => ['nullable', 'string', 'max:32'],
            'official_store_url' => ['nullable', 'url', 'max:2048'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,private'],
            'character_ids' => ['nullable', 'array'],
            'character_ids.*' => ['integer', 'exists:characters,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'canon_events' => ['nullable', 'array', 'max:50'],
            'term_usages' => ['nullable', 'array', 'max:50'],
        ];

        foreach ($this->importableWorkColumns() as $column) {
            if (! array_key_exists($column, $rules)) {
                $rules[$column] = ['nullable', 'string'];
            }
        }

        foreach ((new WorkCanonEvent())->getFillable() as $field) {
            if ($field !== 'work_id') {
                $rules["canon_events.*.{$field}"] = $field === 'sort_order'
                    ? ['nullable', 'integer', 'min:0']
                    : ['nullable', 'string'];
            }
        }

        foreach ((new WorkTermUsage())->getFillable() as $field) {
            if ($field !== 'work_id') {
                $rules["term_usages.*.{$field}"] = $field === 'sort_order'
                    ? ['nullable', 'integer', 'min:0']
                    : ['nullable', 'string'];
            }
        }

        return $rules;
    }

    private function importableWorkColumns(): array
    {
        return array_values(array_diff(
            Schema::getColumnListing('works'),
            [
                'id',
                'slug',
                'review_status',
                'created_by',
                'updated_by',
                'published_at',
                'created_at',
                'updated_at',
                'deleted_at',
                'helpful_count',
                'contributor_application_id',
            ]
        ));
    }

    private function decodeRelationRows(?string $json, string $modelClass): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded) || ! array_is_list($decoded)) {
            throw new JsonException('配列形式で指定してください。');
        }

        if (count($decoded) > 50) {
            throw new JsonException('登録できる件数は最大50件です。');
        }

        $fillable = array_values(array_diff(
            (new $modelClass())->getFillable(),
            ['work_id']
        ));

        return collect($decoded)
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row, int $index) use ($fillable): array {
                $filtered = array_intersect_key($row, array_flip($fillable));

                if (in_array('sort_order', $fillable, true)) {
                    $filtered['sort_order'] = $filtered['sort_order'] ?? $index;
                }

                return $filtered;
            })
            ->filter(fn (array $row) => collect($row)
                ->except('sort_order')
                ->contains(fn ($value) => $value !== null && $value !== ''))
            ->values()
            ->all();
    }

    private function existingRelationRows(
        ?Work $work,
        string $relation,
        string $modelClass
    ): array {
        if (! $work) {
            return [];
        }

        $fillable = array_values(array_diff(
            (new $modelClass())->getFillable(),
            ['work_id']
        ));

        return $work->{$relation}
            ->sortBy('sort_order')
            ->values()
            ->map(fn ($model) => $model->only($fillable))
            ->all();
    }

    private function resolveParentWorkIdByTitle(
        mixed $title,
        ?Work $existingWork = null
    ): ?int {
        $title = trim((string) $title);

        if ($title === '') {
            return null;
        }

        $query = Work::query()
            ->whereNull('parent_work_id')
            ->where('title', $title);

        if ($existingWork) {
            $query->whereKeyNot($existingWork->id);
        }

        $matches = $query->get();

        if ($matches->isEmpty()) {
            throw new \InvalidArgumentException(
                "親作品「{$title}」が見つかりません。"
            );
        }

        if ($matches->count() > 1) {
            throw new \InvalidArgumentException(
                "親作品「{$title}」は同名が複数存在するため、"
                . "parent_work_idで指定してください。"
            );
        }

        return (int) $matches->first()->id;
    }

    private function resolveCharacterIds(array $data): array
    {
        $ids = collect(preg_split('/[,、\s]+/u', (string) ($data['character_ids'] ?? '')) ?: [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->map(function (string $value): int {
                if (! ctype_digit($value)) {
                    throw new \InvalidArgumentException("character_ids に数値以外が含まれています: {$value}");
                }
                return (int) $value;
            });

        $missingIds = $ids->isEmpty()
            ? collect()
            : $ids->diff(Character::query()->whereIn('id', $ids->all())->pluck('id')->map(fn ($id) => (int) $id));

        if ($missingIds->isNotEmpty()) {
            throw new \InvalidArgumentException('存在しないキャラクターIDがあります: ' . $missingIds->implode(','));
        }

        $names = collect(preg_split('/[｜|、,\r\n]+/u', (string) ($data['character_names'] ?? '')) ?: [])
            ->map(fn ($value) => trim((string) $value))
            ->filter();

        foreach ($names as $name) {
            $matches = Character::query()->where('name', $name)->pluck('id');
            if ($matches->isEmpty()) {
                throw new \InvalidArgumentException("キャラクター名「{$name}」が見つかりません。");
            }
            if ($matches->count() > 1) {
                throw new \InvalidArgumentException("キャラクター名「{$name}」は同名が複数存在するため、character_idsで指定してください。");
            }
            $ids->push((int) $matches->first());
        }

        return $ids->filter(fn (int $id) => $id > 0)->unique()->values()->all();
    }

    private function syncCharacters(Work $work, array $characterIds): void
    {
        $syncData = [];
        foreach ($characterIds as $index => $characterId) {
            $character = Character::query()->findOrFail($characterId);
            $syncData[$characterId] = [
                'is_primary' => (int) $character->work_id === (int) $work->id,
                'sort_order' => $index,
            ];
        }
        $work->linkedCharacters()->sync($syncData);
        $work->unsetRelation('linkedCharacters');
    }

    private function resolveTagIds(array $data): array
    {
        $ids = collect(explode(',', (string) ($data['tag_ids'] ?? '')))
            ->map(fn ($value) => trim($value))
            ->filter(fn ($value) => ctype_digit($value))
            ->map(fn ($value) => (int) $value);

        $names = collect(preg_split('/[,、]/u', (string) ($data['tag_names'] ?? '')))
            ->map(fn ($value) => trim($value))
            ->filter();

        if ($names->isNotEmpty()) {
            $ids = $ids->merge(
                Tag::query()->whereIn('name', $names->all())->pluck('id')
            );
        }

        return $ids->unique()->values()->all();
    }

    private function normalizeHeader(array $header): array
    {
        return array_map(function ($value): string {
            $value = preg_replace('/^\xEF\xBB\xBF/', '', (string) $value);
            return trim($value);
        }, $header);
    }

    private function normalizeData(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $value = is_string($value) ? trim($value) : $value;
            $value = $value === '' ? null : $value;

            if ($key === 'media_types') {
                $value = $this->normalizeMediaTypes($value);
            }

            if ($key === 'monetization_enabled') {
                $value = $this->normalizeBoolean($value);
            }

            if ($key === 'monetization_inheritance') {
                $value = $this->normalizeMonetizationInheritance($value);
            }

            if ($key === 'status') {
                $value = $this->normalizeStatus($value);
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private function normalizeMediaTypes(mixed $value): ?array
    {
        if ($value === null || $value === []) {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return null;
            }

            if (
                str_starts_with($trimmed, '[')
                && str_ends_with($trimmed, ']')
            ) {
                $decoded = json_decode($trimmed, true);

                if (is_array($decoded)) {
                    $value = $decoded;
                }
            }
        }

        $items = is_array($value)
            ? $value
            : preg_split(
                '/[,\x{FF0C}\x{3001}\x{FF5C}|\r\n\/]+/u',
                (string) $value
            );

        $aliases = [
            'anime' => 'anime',
            'アニメ' => 'anime',
            'manga' => 'manga',
            '漫画' => 'manga',
            'マンガ' => 'manga',
            'コミック' => 'manga',
            'game' => 'game',
            'ゲーム' => 'game',
            'novel' => 'novel',
            '小説' => 'novel',
            'ライトノベル' => 'novel',
            'ラノベ' => 'novel',
            'goods' => 'goods',
            'グッズ' => 'goods',
            '商品' => 'goods',
            'app' => 'app',
            'アプリ' => 'app',
            'other' => 'other',
            'その他' => 'other',
        ];

        return collect($items ?: [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->map(function (string $item) use ($aliases): string {
                $lower = mb_strtolower($item);

                return $aliases[$lower]
                    ?? $aliases[$item]
                    ?? $lower;
            })
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeMonetizationInheritance(
        mixed $value
    ): ?string {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $normalized = mb_strtolower(trim((string) $value));

        return match ($normalized) {
            'self', '個別', 'この作品', '自身' => 'self',
            'parent', '親', '親作品' => 'parent',
            'self_then_parent',
            '個別優先',
            'この作品を優先',
            '自身を優先',
            '個別→親',
            '個別＞親' => 'self_then_parent',
            'disabled', '無効', '使用しない', 'なし' => 'disabled',
            default => $normalized,
        };
    }

    private function normalizeStatus(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $normalized = mb_strtolower(trim((string) $value));

        return match ($normalized) {
            'draft', '下書き' => 'draft',
            'published', '公開' => 'published',
            'private', '非公開' => 'private',
            default => $normalized,
        };
    }

    private function validationMessages(): array
    {
        return [
            'media_types.*.in' =>
                '媒体種別「:input」は使用できません。'
                . '使用可能な値：anime、manga、game、novel、'
                . 'goods、app、other'
                . '（日本語のアニメ、漫画、ゲーム、小説、'
                . 'グッズ、アプリ、その他も使用できます）。',
            'monetization_inheritance.in' =>
                '収益設定の継承方法「:input」は使用できません。'
                . '使用可能な値：self、parent、'
                . 'self_then_parent、disabled。',
            'status.in' =>
                '公開状態「:input」は使用できません。'
                . '使用可能な値：draft、published、private'
                . '（下書き、公開、非公開も使用できます）。',
            'monetization_enabled.boolean' =>
                '収益表示の有効・無効は、'
                . '1 / 0、true / false、はい / いいえ'
                . 'のいずれかで入力してください。',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'title' => '作品名',
            'media_types' => '媒体種別',
            'media_types.*' => '媒体種別',
            'monetization_enabled' => '収益表示',
            'monetization_inheritance' => '収益設定の継承方法',
            'status' => '公開状態',
        ];
    }

    private function normalizeBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        $normalized = mb_strtolower(trim((string) $value));

        return match ($normalized) {
            '1', 'true', 'yes', 'on', '有効', 'はい' => true,
            '0', 'false', 'no', 'off', '無効', 'いいえ' => false,
            default => throw new \InvalidArgumentException(
                'monetization_enabledは'
                . '1/0、true/false、有効/無効のいずれかで指定してください。'
            ),
        };
    }

    private function isEmptyRow(array $row): bool
    {
        return collect($row)->every(
            fn ($value) => trim((string) $value) === ''
        );
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

        return $value !== '' && ctype_digit($value)
            ? (int) $value
            : null;
    }
}
