<?php

namespace App\Http\Requests\Writer\SavedPrompt;

class PreviewSavedPromptRequest extends StoreSavedPromptRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['title'] = ['nullable', 'string', 'max:255'];

        /*
         * 作風・ジャンル・章・物語詳細などは、
         * 保存時と同じ選択肢・形式で検証する。
         */
        return $rules;
    }
}
