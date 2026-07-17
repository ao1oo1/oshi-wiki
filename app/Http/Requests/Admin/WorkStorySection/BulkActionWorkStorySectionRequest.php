<?php

namespace App\Http\Requests\Admin\WorkStorySection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkActionWorkStorySectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->canManageAllAdminFeatures()
            ?? false;
    }

    public function rules(): array
    {
        return [
            'section_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'section_ids.*' => [
                'integer',
                'distinct',
                'exists:work_story_sections,id',
            ],
            'bulk_action' => [
                'required',
                Rule::in([
                    'publish',
                    'private',
                    'draft',
                    'delete',
                ]),
            ],
        ];
    }
}
