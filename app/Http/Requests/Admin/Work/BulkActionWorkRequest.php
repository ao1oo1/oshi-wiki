<?php

namespace App\Http\Requests\Admin\Work;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_ids' => ['required', 'array', 'min:1'],
            'work_ids.*' => ['integer', 'exists:works,id'],
            'bulk_action' => ['required', 'in:publish,private,delete'],
        ];
    }

    public function messages(): array
    {
        return [
            'work_ids.required' => '一括操作する作品を選択してください。',
            'work_ids.min' => '一括操作する作品を選択してください。',
            'bulk_action.required' => '一括操作の内容を選択してください。',
        ];
    }
}
