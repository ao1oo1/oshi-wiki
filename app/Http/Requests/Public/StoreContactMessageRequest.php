<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'in:correction,copyright,contributor,discord,other'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'target_url' => ['nullable', 'url', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category' => 'お問い合わせ種別',
            'name' => 'お名前',
            'email' => 'メールアドレス',
            'subject' => '件名',
            'body' => '内容',
            'target_url' => '対象URL',
        ];
    }
}
