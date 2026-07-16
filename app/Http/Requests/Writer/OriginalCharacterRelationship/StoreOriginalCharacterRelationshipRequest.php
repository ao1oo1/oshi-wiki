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
        $noteMax = WritingAssistLimits::noteMaxLength(
            $this->user()
        );

        $longNoteMax = WritingAssistLimits::longNoteMaxLength(
            $this->user()
        );

        return [
            'from_work_ref' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^(original|work:\d+)$/',
            ],
            'to_work_ref' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^(original|work:\d+)$/',
            ],
            'from_character_ref' => [
                'required',
                'string',
                'max:100',
                'regex:/^(original|v1):\d+$/',
            ],
            'to_character_ref' => [
                'required',
                'string',
                'different:from_character_ref',
                'max:100',
                'regex:/^(original|v1):\d+$/',
            ],

            'called_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'relationship_type' => [
                'nullable',
                'string',
                'max:255',
            ],
            'impression' => [
                'nullable',
                'string',
                $this->maxRule($longNoteMax),
            ],
            'notes' => [
                'nullable',
                'string',
                $this->maxRule($noteMax),
            ],
            'status' => [
                'nullable',
                Rule::in(['active', 'draft']),
            ],
            'timeline_items' => [
                'nullable',
                'array',
                'max:10',
            ],
            'timeline_items.*.period' => [
                'nullable',
                'string',
                'max:255',
            ],
            'timeline_items.*.content' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'from_work_ref' => '関係元の作品',
            'to_work_ref' => '関係先の作品',
            'from_character_ref' => '関係元キャラクター',
            'to_character_ref' => '関係先キャラクター',
            'called_name' => '呼び方',
            'relationship_type' => '関係性',
            'impression' => '印象・気持ち',
            'notes' => '備考',
            'timeline_items' => '年表データ',
        ];
    }

    public function messages(): array
    {
        return [
            'from_work_ref.regex' =>
                '関係元の作品を正しく選択してください。',
            'to_work_ref.regex' =>
                '関係先の作品を正しく選択してください。',
            'from_character_ref.regex' =>
                '関係元キャラクターを正しく選択してください。',
            'to_character_ref.regex' =>
                '関係先キャラクターを正しく選択してください。',
            'to_character_ref.different' =>
                '同じキャラクター同士の関係性は登録できません。',
        ];
    }

    private function maxRule(?int $max): string
    {
        return $max === null
            ? 'max:1000000'
            : 'max:' . $max;
    }
}
