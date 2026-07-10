<?php

namespace App\Http\Requests\Writer\SavedPrompt;

class PreviewSavedPromptRequest extends StoreSavedPromptRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['title'] = ['nullable', 'string', 'max:255'];
        $rules['writing_style'] = ['required', 'string', 'max:50'];
        $rules['genre'] = ['required', 'string', 'max:50'];

        return $rules;
    }
}
