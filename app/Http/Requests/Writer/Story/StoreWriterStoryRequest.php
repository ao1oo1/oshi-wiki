<?php

namespace App\Http\Requests\Writer\Story;

use App\Support\WritingAssistLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWriterStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessWriter() ?? false;
    }

    public function rules(): array
    {
        $bodyMax = WritingAssistLimits::storyBodyMaxLength($this->user());
        $memoMax = WritingAssistLimits::noteMaxLength($this->user());

        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'episode_number' => [
                'nullable',
                'integer',
                'min:1',
                'max:9999',
            ],
            'body' => [
                'required',
                'string',
                $this->maxRule($bodyMax),
            ],
            'memo' => [
                'nullable',
                'string',
                $this->maxRule($memoMax),
            ],
            'status' => [
                'nullable',
                Rule::in(['active', 'draft']),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'タイトル',
            'episode_number' => '話数',
            'body' => 'ストーリー本文',
            'memo' => 'メモ',
            'status' => 'ステータス',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください。',
            'body.required' => 'ストーリー本文を入力してください。',
            'episode_number.integer' => '話数は半角数字で入力してください。',
            'episode_number.min' => '話数は1以上で入力してください。',
        ];
    }

    private function maxRule(?int $max): string
    {
        return $max === null
            ? 'max:10000000'
            : 'max:' . $max;
    }
}
