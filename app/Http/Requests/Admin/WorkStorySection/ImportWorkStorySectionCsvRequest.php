<?php

namespace App\Http\Requests\Admin\WorkStorySection;

use Illuminate\Foundation\Http\FormRequest;

class ImportWorkStorySectionCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->canManageAllAdminFeatures()
            ?? false;
    }

    public function rules(): array
    {
        return [
            'csv_file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:10240',
            ],
            'default_status' => [
                'nullable',
                'in:draft,published,private',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'csv_file' => 'CSVファイル',
            'default_status' => '初期状態',
        ];
    }
}
