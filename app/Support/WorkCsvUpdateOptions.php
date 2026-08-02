<?php

namespace App\Support;

use Illuminate\Http\Request;

final class WorkCsvUpdateOptions
{
    public const MODE_SELECTED = 'selected';
    public const MODE_ALL = 'all';
    public const MODE_NON_BLANK = 'non_blank';
    public const MODE_CREATE_ONLY = 'create_only';
    public const MODE_UPDATE_ONLY = 'update_only';

    public const BLANK_KEEP = 'keep';
    public const BLANK_CLEAR = 'clear';

    public const CHARACTER_IGNORE = 'ignore';
    public const CHARACTER_APPEND = 'append';
    public const CHARACTER_REPLACE = 'replace';
    public const CHARACTER_DETACH = 'detach';

    public const RELATION_ERROR_ROW = 'skip_row';
    public const RELATION_ERROR_FIELD = 'skip_field';

    public const PROTECTED_FIELDS = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'character_ids',
        'character_names',
        'tag_ids',
        'tag_names',
    ];

    public const RELATION_FIELDS = [
        'parent_work_id',
        'tag_ids',
        'tag_names',
        'character_ids',
        'character_names',
    ];

    public const FIELD_GROUPS = [
        'A. 基本情報' => [
            'title' => '作品名',
            'title_kana' => '作品名かな',
            'title_english' => '英語名',
            'description' => '説明',
            'status' => '公開状態',
        ],
        'B. 分類・媒体' => [
            'original_media' => '媒体',
            'genre' => 'ジャンル',
            'search_keywords' => '検索キーワード',
            'work_type' => '作品種別',
        ],
        'C. 親子作品' => [
            'parent_work_id' => '親作品ID',
        ],
        'D. タグ' => [
            'tag_ids' => 'タグID',
            'tag_names' => 'タグ名',
        ],
        'E. SEO' => [
            'seo_title' => 'SEOタイトル',
            'seo_description' => 'SEO説明',
            'seo_keywords' => 'SEOキーワード',
        ],
        'F. 収益設定' => [
            'monetization_enabled' => '収益化有効',
            'monetization_inheritance' => '収益継承方法',
        ],
        'G. 関連データ' => [
            'canon_events_json' => '公式年表',
            'term_usages_json' => '用語',
        ],
    ];

    public function __construct(
        public readonly string $mode,
        public readonly array $updateFields,
        public readonly string $blankMode,
        public readonly string $characterMode,
        public readonly string $relationErrorMode,
    ) {
    }

    public static function defaults(): self
    {
        return new self(
            mode: self::MODE_SELECTED,
            updateFields: [],
            blankMode: self::BLANK_KEEP,
            characterMode: self::CHARACTER_IGNORE,
            relationErrorMode: self::RELATION_ERROR_FIELD,
        );
    }

    public static function legacyImportDefaults(): self
    {
        return new self(
            mode: self::MODE_ALL,
            updateFields: [],
            blankMode: self::BLANK_CLEAR,
            characterMode: self::CHARACTER_REPLACE,
            relationErrorMode: self::RELATION_ERROR_ROW,
        );
    }

    public static function fromRequest(Request $request): self
    {
        $mode = (string) $request->input(
            'update_mode',
            self::MODE_SELECTED
        );

        if (! in_array($mode, [
            self::MODE_SELECTED,
            self::MODE_ALL,
            self::MODE_NON_BLANK,
            self::MODE_CREATE_ONLY,
            self::MODE_UPDATE_ONLY,
        ], true)) {
            $mode = self::MODE_SELECTED;
        }

        $blankMode = (string) $request->input(
            'blank_value_mode',
            self::BLANK_KEEP
        );

        if (! in_array($blankMode, [
            self::BLANK_KEEP,
            self::BLANK_CLEAR,
        ], true)) {
            $blankMode = self::BLANK_KEEP;
        }

        $characterMode = (string) $request->input(
            'character_ids_mode',
            self::CHARACTER_IGNORE
        );

        if (! in_array($characterMode, [
            self::CHARACTER_IGNORE,
            self::CHARACTER_APPEND,
            self::CHARACTER_REPLACE,
            self::CHARACTER_DETACH,
        ], true)) {
            $characterMode = self::CHARACTER_IGNORE;
        }

        $relationErrorMode = (string) $request->input(
            'relation_error_mode',
            self::RELATION_ERROR_FIELD
        );

        if (! in_array($relationErrorMode, [
            self::RELATION_ERROR_ROW,
            self::RELATION_ERROR_FIELD,
        ], true)) {
            $relationErrorMode = self::RELATION_ERROR_FIELD;
        }

        $allowedFields = collect(self::FIELD_GROUPS)
            ->flatMap(fn (array $fields) => array_keys($fields))
            ->values()
            ->all();

        $updateFields = collect(
            $request->input('update_fields', [])
        )
            ->filter(fn ($field) => is_string($field))
            ->intersect($allowedFields)
            ->values()
            ->all();

        return new self(
            mode: $mode,
            updateFields: $updateFields,
            blankMode: $blankMode,
            characterMode: $characterMode,
            relationErrorMode: $relationErrorMode,
        );
    }

    public function shouldCreate(): bool
    {
        return $this->mode !== self::MODE_UPDATE_ONLY;
    }

    public function shouldUpdate(): bool
    {
        return $this->mode !== self::MODE_CREATE_ONLY;
    }

    public function selected(string $field): bool
    {
        return $this->mode === self::MODE_ALL
            || $this->mode === self::MODE_NON_BLANK
            || in_array($field, $this->updateFields, true);
    }

    public function filterAttributes(array $attributes): array
    {
        $filtered = collect($attributes)
            ->except(self::PROTECTED_FIELDS);

        if ($this->mode === self::MODE_SELECTED) {
            $filtered = $filtered->only($this->updateFields);
        }

        if (
            $this->mode === self::MODE_NON_BLANK
            || $this->blankMode === self::BLANK_KEEP
        ) {
            $filtered = $filtered->reject(
                fn ($value) => $value === null || $value === ''
            );
        }

        return $filtered->all();
    }
}
