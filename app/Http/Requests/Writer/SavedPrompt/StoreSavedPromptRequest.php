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

            'work_ref' => ['required', 'string', 'max:100'],
            'selected_character_refs' => ['nullable', 'array'],
            'selected_character_refs.*' => ['string', 'max:100'],

            'writing_style' => ['required', Rule::in(array_keys(SavedPrompt::writingStyleLabels()))],
            'writing_style_other' => ['nullable', 'string', 'max:255'],

            'genre' => ['required', Rule::in(array_keys(SavedPrompt::genreLabels()))],
            'genre_other' => ['nullable', 'string', 'max:255'],

            'synopsis' => $this->nullableTextRules(WritingAssistLimits::synopsisMaxLength($this->user())),
            'plot_opening' => $this->nullableTextRules(WritingAssistLimits::longNoteMaxLength($this->user())),
            'plot_development' => $this->nullableTextRules(WritingAssistLimits::longNoteMaxLength($this->user())),
            'plot_turn' => $this->nullableTextRules(WritingAssistLimits::longNoteMaxLength($this->user())),
            'plot_conclusion' => $this->nullableTextRules(WritingAssistLimits::longNoteMaxLength($this->user())),

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
