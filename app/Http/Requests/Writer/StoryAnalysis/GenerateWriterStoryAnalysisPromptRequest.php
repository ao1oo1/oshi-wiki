<?php

namespace App\Http\Requests\Writer\StoryAnalysis;

use Illuminate\Foundation\Http\FormRequest;

class GenerateWriterStoryAnalysisPromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessWriter() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
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
            'title' => '管理名',
            'story_ids' => '分析対象ストーリー',
            'story_ids.*' => '分析対象ストーリー',
            'analysis_notes' => '追加指示',
        ];
    }

    public function messages(): array
    {
        return [
            'story_ids.required' =>
                '分析対象のストーリーを選択してください。',
            'story_ids.min' =>
                '分析対象のストーリーを1件以上選択してください。',
        ];
    }
}
