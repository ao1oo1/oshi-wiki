<?php

namespace App\Http\Requests\Writer\OriginalCharacter;

use App\Support\WritingAssistLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOriginalCharacterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessWriter() ?? false;
    }

    public function rules(): array
    {
        $noteMax = WritingAssistLimits::noteMaxLength($this->user());
        $longNoteMax = WritingAssistLimits::longNoteMaxLength($this->user());

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_kana' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'string', 'max:100'],
            'affiliation' => ['nullable', 'string', 'max:255'],
            'school_grade' => ['nullable', 'string', 'max:255'],
            'first_person' => ['nullable', 'string', 'max:255'],

            'speech_style' => ['nullable', 'string', $this->maxRule($noteMax)],
            'speech_examples' => ['nullable', 'string', $this->maxRule($noteMax)],
            'personality' => ['nullable', 'string', $this->maxRule($longNoteMax)],
            'appearance' => ['nullable', 'string', $this->maxRule($longNoteMax)],
            'background' => ['nullable', 'string', $this->maxRule($longNoteMax)],

            'character_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
                'dimensions:max_width=4000,max_height=4000',
            ],
            'remove_image' => ['nullable', 'boolean'],

            'is_main_character' => ['nullable', 'boolean'],
            'important_points' => ['nullable', 'string', $this->maxRule($longNoteMax)],
            'ng_points' => ['nullable', 'string', $this->maxRule($longNoteMax)],
            'notes' => ['nullable', 'string', $this->maxRule($noteMax)],
            'status' => ['nullable', Rule::in(['active', 'draft'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '名前',
            'name_kana' => '読み仮名',
            'age' => '年齢',
            'gender' => '性別',
            'affiliation' => '所属',
            'school_grade' => '学年・クラス',
            'first_person' => '一人称',
            'speech_style' => '口調',
            'speech_examples' => '口調例',
            'personality' => '性格・特徴',
            'appearance' => '外見',
            'background' => '背景・経歴',
            'character_image' => 'キャラクター画像',
            'remove_image' => '画像削除',
            'is_main_character' => '主人公設定',
            'important_points' => '絶対に守りたい設定',
            'ng_points' => 'NG設定・避けたい表現',
            'notes' => '備考',
            'status' => 'ステータス',
        ];
    }

    public function messages(): array
    {
        return [
            'character_image.image' => 'キャラクター画像には画像ファイルを選択してください。',
            'character_image.mimes' => 'キャラクター画像はJPG、JPEG、PNG、WebP形式で登録してください。',
            'character_image.max' => 'キャラクター画像は4MB以下にしてください。',
            'character_image.dimensions' => 'キャラクター画像は縦横とも4,000px以下にしてください。',
        ];
    }

    private function maxRule(?int $max): string
    {
        return $max === null ? 'max:1000000' : 'max:' . $max;
    }
}
