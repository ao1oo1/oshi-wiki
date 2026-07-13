<?php

namespace App\Http\Requests\Writer\SavedPromptAiResult;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreSavedPromptAiResultRequest extends FormRequest
{
    private const RESULT_BODY_MAX_LENGTH = 10000;

    public function authorize(): bool
    {
        return $this->user()?->canAccessWriter() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $resultBody = $this->input('result_body');

        if (is_string($resultBody)) {
            $this->merge([
                'result_body' => str_replace(
                    ["\r\n", "\r"],
                    "\n",
                    $resultBody
                ),
            ]);
        }
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
                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ): void {
                    if (! is_string($value)) {
                        return;
                    }

                    $length = mb_strlen($value, 'UTF-8');

                    if ($length > self::RESULT_BODY_MAX_LENGTH) {
                        $fail(
                            'AIが出した結論は、日本語換算で'
                            . number_format(
                                self::RESULT_BODY_MAX_LENGTH
                            )
                            . '字以内で入力してください。'
                            . '現在は'
                            . number_format($length)
                            . '字です。'
                        );
                    }
                },
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
        ];
    }
}
