<?php

namespace App\Http\Requests\Admin\CharacterRelationship;

use Illuminate\Foundation\Http\FormRequest;

class StoreCharacterRelationshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_id' => ['required', 'exists:works,id'],
            'from_character_id' => ['required', 'exists:characters,id'],
            'to_character_id' => ['required', 'exists:characters,id'],
            'called_name' => ['nullable', 'string', 'max:255'],
            'relationship' => ['nullable', 'string', 'max:255'],
            'impression' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,published,private'],
            'return_to_work_id' => ['nullable', 'exists:works,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'work_id' => '作品',
            'from_character_id' => 'キャラクター',
            'to_character_id' => '相手キャラクター',
            'called_name' => '呼ばれ方',
            'relationship' => '関係性',
            'impression' => '印象・気持ち等',
            'notes' => '補足メモ',
            'status' => '状態',
        ];
    }
}
