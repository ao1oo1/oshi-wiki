<?php

namespace App\Http\Requests\Admin\CharacterRelationship;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionCharacterRelationshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'relationship_ids' => ['required', 'array', 'min:1'],
            'relationship_ids.*' => ['integer', 'exists:character_relationships,id'],
            'bulk_action' => ['required', 'in:publish,private,delete'],
        ];
    }

    public function messages(): array
    {
        return [
            'relationship_ids.required' => '一括操作する関係性を選択してください。',
            'relationship_ids.min' => '一括操作する関係性を選択してください。',
            'bulk_action.required' => '一括操作の内容を選択してください。',
        ];
    }
}
