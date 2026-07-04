<?php

namespace App\Http\Requests\Admin\Tag;

use Illuminate\Foundation\Http\FormRequest;

class ImportTagTextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'raw_text' => ['required', 'string', 'max:10000'],
            'status' => ['nullable', 'in:draft,published,private'],
        ];
    }
}
