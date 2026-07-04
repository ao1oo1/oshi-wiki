<?php

namespace App\Http\Requests\Admin\Tag;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tag_ids' => ['required', 'array', 'min:1'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'bulk_action' => ['required', 'in:publish,private,delete'],
        ];
    }

    public function messages(): array
    {
        return [
            'tag_ids.required' => '一括操作するタグを選択してください。',
            'tag_ids.min' => '一括操作するタグを選択してください。',
            'bulk_action.required' => '一括操作の内容を選択してください。',
        ];
    }

    public function attributes(): array
    {
        return [
            'tag_ids' => 'タグ',
            'bulk_action' => '一括操作',
        ];
    }
}
