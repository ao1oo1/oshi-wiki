<?php

namespace App\Http\Requests\Admin\Character;

use Illuminate\Foundation\Http\FormRequest;

class ImportCharacterTextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_id' => ['required', 'exists:works,id'],
            'raw_text' => ['required', 'string', 'max:20000'],
            'status' => ['nullable', 'in:draft,published,private'],
        ];
    }

    public function attributes(): array
    {
        return [
            'work_id' => '作品',
            'raw_text' => 'キャラクター設定テキスト',
            'status' => '状態',
        ];
    }
}
