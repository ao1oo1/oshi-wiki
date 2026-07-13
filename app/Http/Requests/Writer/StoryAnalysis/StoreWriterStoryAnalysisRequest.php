<?php

namespace App\Http\Requests\Writer\StoryAnalysis;

use App\Support\WritingAssistLimits;
use Illuminate\Foundation\Http\FormRequest;

class StoreWriterStoryAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessWriter() ?? false;
    }

    public function rules(): array
    {
        $promptRules = [
            'required',
            'string',
        ];

        $promptMax = WritingAssistLimits::promptBodyMaxLength(
            $this->user()
        );

        if ($promptMax !== null) {
            $promptRules[] = 'max:' . $promptMax;
        }

        $resultRules = [
            'nullable',
            'string',
        ];

        $resultMax = WritingAssistLimits::analysisResultMaxLength(
            $this->user()
        );

        if ($resultMax !== null) {
            $resultRules[] = 'max:' . $resultMax;
        }

        return [
            'title' => [
                'required',
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
            'analysis_prompt' => $promptRules,
            'analysis_result' => $resultRules,
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => '管理名',
            'story_ids' => '分析対象ストーリー',
            'story_ids.*' => '分析対象ストーリー',
            'analysis_notes' => '追加指示',
            'analysis_prompt' => '分析用プロンプト',
            'analysis_result' => '分析結果',
        ];
    }

    public function messages(): array
    {
        return [
            'story_ids.required' =>
                '分析対象のストーリーを選択してください。',
            'story_ids.min' =>
                '分析対象のストーリーを1件以上選択してください。',
            'analysis_prompt.required' =>
                '先に分析用プロンプトを作成してください。',
            'analysis_result.max' =>
                '分析結果は10,000文字以内で入力してください。',
        ];
    }
}
