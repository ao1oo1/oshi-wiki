<?php

namespace App\Http\Requests\Admin\Work;

use Illuminate\Foundation\Http\FormRequest;

class ImportWorkTextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'raw_text' => ['required', 'string', 'max:20000'],
            'status' => ['nullable', 'in:draft,published,private'],
        ];
    }
}
