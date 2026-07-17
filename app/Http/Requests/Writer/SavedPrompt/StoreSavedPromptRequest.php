<?php

namespace App\Http\Requests\Writer\SavedPrompt;

use App\Models\SavedPrompt;
use App\Support\WritingAssistLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSavedPromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessWriter() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', Rule::in(array_keys(SavedPrompt::categoryLabels()))],
            'purpose' => ['nullable', 'string', 'max:255'],
            'work_ref' => [
                'required',
                'string',
                'regex:/^(original|work:\d+)$/',
            ],
            'work_story_section_id' => [
                'nullable',
                'integer',
                'exists:work_story_sections,id',
            ],
            'selected_character_refs' => [
                'nullable',
                'array',
                'max:60',
            ],
            'include_relationship_timeline' => ['nullable', 'boolean'],
            'include_work_worldbuilding' => ['nullable', 'boolean'],
            'selected_work_worldbuilding_categories' => [
                'nullable',
                'array',
                'max:9',
                Rule::requiredIf(
                    fn () => $this->boolean('include_work_worldbuilding')
                ),
            ],
            'selected_work_worldbuilding_categories.*' => [
                'string',
                'distinct',
                Rule::in(array_keys(SavedPrompt::workWorldbuildingCategoryLabels())),
            ],
            'selected_character_refs.*' => [
                'string',
                'max:2000',
                'regex:/^(original|v1):\\d+$/',
            ],

            'writing_style' => ['required', Rule::in(array_keys(SavedPrompt::writingStyleLabels()))],
            'writing_style_other' => ['nullable', 'string', 'max:255'],

            'genre' => ['required', Rule::in(array_keys(SavedPrompt::genreLabels()))],
            'genre_other' => ['nullable', 'string', 'max:255'],

            'synopsis' => $this->nullableTextRules(WritingAssistLimits::synopsisMaxLength($this->user())),
            'plot_opening' => $this->nullableTextRules(WritingAssistLimits::longNoteMaxLength($this->user())),
            'plot_development' => $this->nullableTextRules(WritingAssistLimits::longNoteMaxLength($this->user())),
            'plot_turn' => $this->nullableTextRules(WritingAssistLimits::longNoteMaxLength($this->user())),
            'plot_conclusion' => $this->nullableTextRules(WritingAssistLimits::longNoteMaxLength($this->user())),

            'use_story_length_options' => ['nullable', 'boolean'],
            'story_length_type' => [
                'nullable',
                Rule::requiredIf(
                    fn () => $this->boolean('use_story_length_options')
                ),
                Rule::in(['short', 'long']),
            ],
            'output_plot_first' => ['nullable', 'boolean'],
            'output_in_parts' => ['nullable', 'boolean'],

            'selected_story_analysis_ids' => [
                'nullable',
                'array',
                'max:10',
            ],
            'selected_story_analysis_ids.*' => [
                'required',
                'integer',
                'distinct',
            ],

            'notes' => $this->nullableTextRules(WritingAssistLimits::noteMaxLength($this->user())),
            'status' => ['nullable', Rule::in(['active', 'draft'])],
        ];
    }

    private function nullableTextRules(?int $max): array
    {
        $rules = ['nullable', 'string'];

        if ($max !== null) {
            $rules[] = 'max:' . $max;
        }

        return $rules;
    }
}
