<?php

namespace App\Http\Requests\Writer\Story;

use Illuminate\Foundation\Http\FormRequest;

class GenerateStoryAnalysisPromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessWriter() ?? false;
    }

    public function rules(): array
    {
        return [
            'story_ids' => [
                'required',
                'array',
                'min:1',
                'max:50',
            ],
            'story_ids.*' => [
                'required',
                'integer',
                'distinct',
            ],
            'analysis_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'story_ids' => '分析対象のストーリー',
            'story_ids.*' => '分析対象のストーリー',
            'analysis_notes' => '分析時の追加指示',
        ];
    }

    public function messages(): array
    {
        return [
            'story_ids.required' => '分析するストーリーを1件以上選択してください。',
            'story_ids.min' => '分析するストーリーを1件以上選択してください。',
        ];
    }
}
