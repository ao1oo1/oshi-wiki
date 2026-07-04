<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'title_kana' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'string', 'max:255'],
            'original_media' => ['nullable', 'string', 'max:255'],
            'official_url' => ['nullable', 'url', 'max:2048'],
            'guideline_url' => ['nullable', 'url', 'max:2048'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,published,private'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => '作品名',
            'title_kana' => '読み仮名',
            'genre' => 'ジャンル',
            'original_media' => '原作媒体',
            'official_url' => '公式URL',
            'guideline_url' => 'ガイドラインURL',
            'description' => '説明',
            'status' => '状態',
            'tag_ids' => 'タグ',
        ];
    }
}
