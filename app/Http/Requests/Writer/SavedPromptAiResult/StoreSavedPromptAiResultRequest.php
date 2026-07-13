<?php

namespace App\Http\Requests\Writer\SavedPromptAiResult;

use Illuminate\Foundation\Http\FormRequest;

class StoreSavedPromptAiResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessWriter() ?? false;
    }

    public function rules(): array
    {
        return [
            'result_title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'result_body' => [
                'required',
                'string',
                'max:10000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'result_title' => 'AI回答の管理名',
            'result_body' => 'AIが出した結論',
        ];
    }

    public function messages(): array
    {
        return [
            'result_body.required' =>
                'AIが出した結論を貼り付けてください。',
            'result_body.max' =>
                'AIが出した結論は10,000文字以内で入力してください。',
        ];
    }
}
