<?php

namespace App\Http\Requests\Admin\Character;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionCharacterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'character_ids' => ['required', 'array', 'min:1'],
            'character_ids.*' => ['integer', 'exists:characters,id'],
            'bulk_action' => ['required', 'in:publish,private,delete'],
        ];
    }

    public function messages(): array
    {
        return [
            'character_ids.required' => '一括操作するキャラクターを選択してください。',
            'character_ids.min' => '一括操作するキャラクターを選択してください。',
            'bulk_action.required' => '一括操作の内容を選択してください。',
        ];
    }

    public function attributes(): array
    {
        return [
            'character_ids' => 'キャラクター',
            'bulk_action' => '一括操作',
        ];
    }
}
