<?php

namespace App\Http\Requests\Admin\Character;

use Illuminate\Foundation\Http\FormRequest;

class StoreCharacterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_id' => ['required', 'exists:works,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_kana' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'string', 'max:255'],
            'affiliation' => ['nullable', 'string', 'max:255'],
            'grade_class' => ['nullable', 'string', 'max:255'],
            'first_person' => ['nullable', 'string', 'max:255'],
            'tone' => ['nullable', 'string'],
            'tone_examples' => ['nullable', 'string'],
            'personality' => ['nullable', 'string'],
            'appearance' => ['nullable', 'string'],
            'background' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,published,private'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
            'return_to_work_id' => ['nullable', 'exists:works,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'work_id' => '作品',
            'name' => 'キャラクター名',
            'name_kana' => '読み仮名',
            'age' => '年齢',
            'affiliation' => '所属',
            'grade_class' => '学年クラス',
            'first_person' => '一人称',
            'tone' => '口調',
            'tone_examples' => '口調の例',
            'personality' => '性格',
            'appearance' => '外見の特徴',
            'background' => '背景・経歴',
            'status' => '状態',
            'tag_ids' => 'タグ',
        ];
    }
}
