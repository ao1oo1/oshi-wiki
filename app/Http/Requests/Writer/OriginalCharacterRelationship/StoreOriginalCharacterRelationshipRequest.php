<?php

namespace App\Http\Requests\Writer\OriginalCharacterRelationship;

use App\Support\WritingAssistLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOriginalCharacterRelationshipRequest extends FormRequest
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
            'from_character_ref' => ['required', 'string', 'max:100', 'regex:/^original:\\d+$/'],
            'to_character_ref' => ['required', 'string', 'different:from_character_ref', 'max:100', 'regex:/^original:\\d+$/'],

            'called_name' => ['nullable', 'string', 'max:255'],
            'relationship_type' => ['nullable', 'string', 'max:255'],
            'impression' => ['nullable', 'string', $this->maxRule($longNoteMax)],
            'notes' => ['nullable', 'string', $this->maxRule($noteMax)],
            'status' => ['nullable', Rule::in(['active', 'draft'])],
            'timeline_items' => ['nullable', 'array', 'max:10'],
            'timeline_items.*.period' => ['nullable', 'string', 'max:255'],
            'timeline_items.*.content' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function maxRule(?int $max): string
    {
        return $max === null ? 'max:1000000' : 'max:' . $max;
    }
}
