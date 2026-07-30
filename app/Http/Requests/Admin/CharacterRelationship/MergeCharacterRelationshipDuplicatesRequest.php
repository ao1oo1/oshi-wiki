<?php

namespace App\Http\Requests\Admin\CharacterRelationship;

use Illuminate\Foundation\Http\FormRequest;

class MergeCharacterRelationshipDuplicatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageAllAdminFeatures();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'merge_all' => $this->boolean('merge_all'),
        ]);
    }

    public function rules(): array
    {
        return [
            'merge_all' => [
                'required',
                'boolean',
            ],
            'duplicate_groups' => [
                'required_if:merge_all,false',
                'array',
                'min:1',
                'max:900',
            ],
            'duplicate_groups.*' => [
                'required',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'duplicate_groups.required_if' =>
                '整理する重複グループを選択してください。',
            'duplicate_groups.min' =>
                '整理する重複グループを選択してください。',
            'duplicate_groups.max' =>
                '個別選択で一度に処理できる重複グループは'
                . '900件までです。全件整理を利用してください。',
        ];
    }
}
