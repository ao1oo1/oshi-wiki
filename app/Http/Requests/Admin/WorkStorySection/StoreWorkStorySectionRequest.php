<?php

namespace App\Http\Requests\Admin\WorkStorySection;

use App\Models\WorkStorySection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkStorySectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->canManageAllAdminFeatures()
            ?? false;
    }

    public function rules(): array
    {
        return [
            'parent_section_id' => [
                'nullable',
                'integer',
                'exists:work_story_sections,id',
            ],
            'section_type' => [
                'required',
                Rule::in(array_keys(WorkStorySection::TYPES)),
            ],
            'section_number' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],
            'title' => ['required', 'string', 'max:255'],
            'title_kana' => ['nullable', 'string', 'max:255'],
            'short_label' => ['nullable', 'string', 'max:2000'],
            'synopsis' => ['nullable', 'string', 'max:20000'],
            'cumulative_settings' => [
                'nullable',
                'string',
                'max:200000',
            ],
            'notes' => ['nullable', 'string', 'max:20000'],
            'spoiler_level' => [
                'required',
                Rule::in(
                    array_keys(
                        WorkStorySection::SPOILER_LEVELS
                    )
                ),
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],
            'status' => [
                'required',
                Rule::in(['draft', 'published', 'private']),
            ],

            'events' => ['nullable', 'array', 'max:3000'],
            'events.*.event_number' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],
            'events.*.title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'events.*.timing' => [
                'nullable',
                'string',
                'max:255',
            ],
            'events.*.summary' => [
                'nullable',
                'string',
                'max:30000',
            ],
            'events.*.location' => [
                'nullable',
                'string',
                'max:255',
            ],
            'events.*.outcome' => [
                'nullable',
                'string',
                'max:30000',
            ],
            'events.*.spoiler_level' => [
                'nullable',
                Rule::in(
                    array_keys(
                        WorkStorySection::SPOILER_LEVELS
                    )
                ),
            ],
            'events.*.notes' => [
                'nullable',
                'string',
                'max:200000',
            ],
            'events.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],

            'section_characters' => ['nullable', 'array'],
            'section_characters.*.character_id' => [
                'nullable',
                'integer',
                'exists:characters,id',
            ],
            'section_characters.*.selected' => [
                'nullable',
                'boolean',
            ],
            'section_characters.*.appearance_type' => [
                'nullable',
                Rule::in([
                    'main',
                    'appears',
                    'flashback',
                    'name_only',
                    'other',
                ]),
            ],
            'section_characters.*.age_at_section' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'section_characters.*.school_grade_at_section' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'section_characters.*.class_at_section' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'section_characters.*.affiliation_at_section' => [
                'nullable',
                'string',
                'max:255',
            ],
            'section_characters.*.position_at_section' => [
                'nullable',
                'string',
                'max:255',
            ],
            'section_characters.*.character_state' => [
                'nullable',
                'string',
                'max:200000',
            ],
            'section_characters.*.first_appearance' => [
                'nullable',
                'boolean',
            ],
            'section_characters.*.notes' => [
                'nullable',
                'string',
                'max:200000',
            ],
            'section_characters.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $events = collect($this->input('events', []))
            ->filter(function ($event): bool {
                return is_array($event)
                    && trim((string) (
                        $event['title'] ?? ''
                    )) !== '';
            })
            ->values()
            ->all();

        $characters = collect(
            $this->input('section_characters', [])
        )
            ->filter(function ($character): bool {
                return is_array($character)
                    && ! empty($character['selected'])
                    && ! empty($character['character_id']);
            })
            ->values()
            ->all();

        $this->merge([
            'events' => $events,
            'section_characters' => $characters,
        ]);
    }
}
