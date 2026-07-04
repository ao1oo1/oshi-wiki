<?php

namespace App\Http\Requests\Admin\Tag;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,published,private'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'タグ名',
            'type' => '分類',
            'description' => '説明',
            'status' => '状態',
        ];
    }
}
