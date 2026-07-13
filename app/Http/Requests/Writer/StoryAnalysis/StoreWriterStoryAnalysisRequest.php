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
        $resultMax = WritingAssistLimits::storyBodyMaxLength(
            $this->user()
        );

        $resultRules = [
            'required',
            'string',
        ];

        if ($resultMax !== null) {
            $resultRules[] = 'max:' . $resultMax;
        }

        return [
            'analysis_title' => [
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

            'analysis_result' => $resultRules,
        ];
    }

    public function attributes(): array
    {
        return [
            'analysis_title' => '分析結果の管理名',
            'story_ids' => '分析対象のストーリー',
            'story_ids.*' => '分析対象のストーリー',
            'analysis_notes' => '分析時の追加指示',
            'analysis_result' => 'AIが出した文体分析の結論',
        ];
    }

    public function messages(): array
    {
        return [
            'story_ids.required' =>
                '分析対象のストーリーが指定されていません。',
            'story_ids.min' =>
                '分析対象のストーリーを1件以上指定してください。',
            'analysis_result.required' =>
                'AIが出した文体分析の結論を貼り付けてください。',
        ];
    }
}
