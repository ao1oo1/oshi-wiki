<?php

namespace App\Http\Requests\Writer\OriginalCharacter;

use App\Support\WritingAssistLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOriginalCharacterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessWriter() ?? false;
    }

    public function rules(): array
    {
        $noteMax = WritingAssistLimits::noteMaxLength($this->user());
        $longNoteMax = WritingAssistLimits::longNoteMaxLength($this->user());

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_kana' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'string', 'max:100'],
            'affiliation' => ['nullable', 'string', 'max:255'],
            'school_grade' => ['nullable', 'string', 'max:255'],
            'first_person' => ['nullable', 'string', 'max:255'],

            'speech_style' => ['nullable', 'string', $this->maxRule($noteMax)],
            'speech_examples' => ['nullable', 'string', $this->maxRule($noteMax)],
            'personality' => ['nullable', 'string', $this->maxRule($longNoteMax)],
            'appearance' => ['nullable', 'string', $this->maxRule($longNoteMax)],
            'background' => ['nullable', 'string', $this->maxRule($longNoteMax)],

            'is_main_character' => ['nullable', 'boolean'],
            'important_points' => ['nullable', 'string', $this->maxRule($longNoteMax)],
            'ng_points' => ['nullable', 'string', $this->maxRule($longNoteMax)],
            'notes' => ['nullable', 'string', $this->maxRule($noteMax)],
            'status' => ['nullable', Rule::in(['active', 'draft'])],
        ];
    }

    private function maxRule(?int $max): string
    {
        return $max === null ? 'max:1000000' : 'max:' . $max;
    }
}
