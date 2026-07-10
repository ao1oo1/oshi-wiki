<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContributorApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
                Rule::unique('contributor_applications', 'email')
                    ->whereNull('deleted_at')
                    ->whereIn('status', ['pending', 'active']),
            ],
            'discord_id' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'このメールアドレスはすでに登録または申請されています。別のメールアドレスで申請してください。',
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'ユーザーネーム',
            'email' => 'メールアドレス',
            'discord_id' => 'Discord ID',
        ];
    }
}
