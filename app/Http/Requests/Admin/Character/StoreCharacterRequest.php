<?php

namespace App\Http\Requests\Admin\Character;

use App\Models\Character;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'linked_work_ids' => ['nullable', 'array'],
            'linked_work_ids.*' => ['integer', 'distinct', 'exists:works,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_kana' => ['nullable', 'string', 'max:255'],
            'real_name' => ['nullable', 'string', 'max:255'],
            'aliases' => ['nullable', 'string'],
            'name_english' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:100'],
            'age' => ['nullable', 'string', 'max:255'],
            'birthday' => ['nullable', 'string', 'max:255'],
            'height' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'string', 'max:255'],
            'blood_type' => ['nullable', 'string', 'max:100'],
            'birthplace' => ['nullable', 'string', 'max:255'],
            'species' => ['nullable', 'string', 'max:255'],
            'affiliation' => ['nullable', 'string', 'max:255'],
            'school_grade_class' => ['nullable', 'string', 'max:255'],
            'occupation_position' => ['nullable', 'string', 'max:255'],
            'family_structure' => ['nullable', 'string'],
            'appearance' => ['nullable', 'string'],
            'personality' => ['nullable', 'string'],
            'first_person' => ['nullable', 'string', 'max:255'],
            'second_person' => ['nullable', 'string', 'max:255'],
            'basic_tone' => ['nullable', 'string'],
            'catchphrases' => ['nullable', 'string'],
            'distinctive_speech' => ['nullable', 'string'],
            'tone_by_relationship' => ['nullable', 'string'],
            'short_quote_examples' => ['nullable', 'string'],
            'abilities' => ['nullable', 'string'],
            'background' => ['nullable', 'string'],
            'story_activities' => ['nullable', 'string'],
            'source_title' => ['nullable', 'string'],
            'source_url' => ['nullable', 'string'],
            'source_type' => ['nullable', Rule::in(array_keys(Character::SOURCE_TYPES))],
            'source_reliability' => ['nullable', Rule::in(array_keys(Character::SOURCE_RELIABILITIES))],
            'source_checked_at' => ['nullable', 'date_format:Y-m-d'],
            'spoiler_level' => ['nullable', Rule::in(array_keys(Character::SPOILER_LEVELS))],

            // 旧フォーム・旧CSVとの互換用
            'grade_class' => ['nullable', 'string', 'max:255'],
            'tone' => ['nullable', 'string'],
            'tone_examples' => ['nullable', 'string'],

            'status' => ['nullable', 'in:draft,published,private'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'return_to_work_id' => ['nullable', 'exists:works,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'work_id' => '作品',
            'linked_work_ids' => '追加で紐付ける作品',
            'name' => '名前',
            'name_kana' => '読み仮名',
            'real_name' => '本名',
            'aliases' => '別名・愛称',
            'name_english' => '英語表記',
            'gender' => '性別',
            'age' => '年齢',
            'birthday' => '生年月日・誕生日',
            'height' => '身長',
            'weight' => '体重',
            'blood_type' => '血液型',
            'birthplace' => '出身地',
            'species' => '種族',
            'affiliation' => '所属',
            'school_grade_class' => '学校・学年・クラス',
            'occupation_position' => '職業・役職',
            'family_structure' => '家族構成',
            'appearance' => '外見',
            'personality' => '性格・特徴',
            'first_person' => '一人称',
            'second_person' => '二人称',
            'basic_tone' => '基本口調',
            'catchphrases' => '口癖',
            'distinctive_speech' => '特徴的な言い回し',
            'tone_by_relationship' => '相手による口調の違い',
            'short_quote_examples' => '短いセリフ例',
            'abilities' => '能力・技・戦闘',
            'background' => '背景・経歴',
            'story_activities' => '作品内での活躍',
            'source_title' => 'ページ名・資料名',
            'source_url' => 'URL',
            'source_type' => '情報源区分',
            'source_reliability' => '信頼度',
            'source_checked_at' => '確認日',
            'spoiler_level' => 'ネタバレ',
            'grade_class' => '学年クラス',
            'tone' => '口調',
            'tone_examples' => '口調の例',
            'status' => '状態',
            'tag_ids' => 'タグ',
        ];
    }
}
