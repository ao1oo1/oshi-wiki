<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkRequest extends FormRequest
{
    private const TEXT_FIELDS = [
        'description',
        'timeline_setting',
        'building_layout',
        'character_room_seat',
        'hangout_places',
        'restricted_secret_places',
        'cafeteria_store_menu',
        'daily_schedule',
        'school_dorm_rules',
        'uniform_details',
        'casual_holiday_rules',
        'duty_system',
        'class_grade_system',
        'organizations_memberships',
        'ranking_system',
        'adult_roles',
        'annual_events',
        'event_flow',
        'story_season',
        'school_location',
        'commute_environment',
        'nearby_shops',
        'climate_nature',
        'sounds',
        'symbolic_motifs',
        'required_belongings',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
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
            'status' => ['nullable', 'in:draft,published,private'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],

            'canon_events' => ['nullable', 'array', 'max:50'],
            'canon_events.*.timing' => ['nullable', 'string', 'max:255'],
            'canon_events.*.event_name' => ['nullable', 'string', 'max:255'],
            'canon_events.*.event_status' => ['nullable', 'in:occurred,allowed,not_yet,unknown'],
            'canon_events.*.notes' => ['nullable', 'string', 'max:5000'],

            'term_usages' => ['nullable', 'array', 'max:50'],
            'term_usages.*.term' => ['nullable', 'string', 'max:255'],
            'term_usages.*.meaning' => ['nullable', 'string', 'max:5000'],
            'term_usages.*.usage_example' => ['nullable', 'string', 'max:5000'],
        ];

        foreach (self::TEXT_FIELDS as $field) {
            $rules[$field] = ['nullable', 'string', 'max:20000'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'parent_work_id' => '親作品',
            'child_sort_order' => '関連作品の表示順',
            'title' => '作品名',
            'title_kana' => '読み仮名',
            'genre' => 'ジャンル',
            'original_media' => '原作媒体',
            'official_url' => '公式URL',
            'guideline_url' => 'ガイドラインURL',
            'description' => '説明',
            'timeline_setting' => '時間軸の指定',
            'status' => '状態',
            'tag_ids' => 'タグ',
            'canon_events' => '原作の重要イベント年表',
            'canon_events.*.timing' => '時期・話数',
            'canon_events.*.event_name' => '出来事',
            'canon_events.*.event_status' => '出来事の状態',
            'canon_events.*.notes' => '出来事の補足',
            'term_usages' => '用語の使用例',
            'term_usages.*.term' => '用語',
            'term_usages.*.meaning' => '意味',
            'term_usages.*.usage_example' => '使用例',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'canon_events' => $this->removeCompletelyEmptyRows($this->input('canon_events', [])),
            'term_usages' => $this->removeCompletelyEmptyRows($this->input('term_usages', [])),
        ]);
    }

    private function removeCompletelyEmptyRows(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_filter($rows, function ($row): bool {
            if (! is_array($row)) {
                return false;
            }

            foreach ($row as $value) {
                if (trim((string) $value) !== '') {
                    return true;
                }
            }

            return false;
        }));
    }
}
