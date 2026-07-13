<?php

namespace App\Http\Requests\Writer\StoryAnalysis;

use App\Support\WritingAssistLimits;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWriterStoryAnalysisResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessWriter() ?? false;
    }

    public function rules(): array
    {
        $rules = [
            'required',
            'string',
        ];

        $max = WritingAssistLimits::analysisResultMaxLength(
            $this->user()
        );

        if ($max !== null) {
            $rules[] = 'max:' . $max;
        }

        return [
            'analysis_result' => $rules,
        ];
    }

    public function attributes(): array
    {
        return [
            'analysis_result' => '分析結果',
        ];
    }

    public function messages(): array
    {
        return [
            'analysis_result.required' =>
                'AIの分析結果を貼り付けてください。',
            'analysis_result.max' =>
                '分析結果は10,000文字以内で入力してください。',
        ];
    }
}
