<?php

namespace App\Http\Requests\Admin\Character;

use Illuminate\Foundation\Http\FormRequest;

class MergeCharacterDuplicatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageAllAdminFeatures();
    }

    public function rules(): array
    {
        return [
            'duplicate_groups' => [
                'required',
                'array',
                'min:1',
                'max:500',
            ],
            'duplicate_groups.*' => [
                'required',
                'string',
                'max:3000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'duplicate_groups.required' =>
                '統合する重複グループを選択してください。',
            'duplicate_groups.min' =>
                '統合する重複グループを選択してください。',
            'duplicate_groups.max' =>
                '一度に処理できる重複グループは500件までです。',
        ];
    }
}
