<?php

namespace App\Http\Requests\Admin\WorkStorySectionEvent;

use Illuminate\Foundation\Http\FormRequest;

class ImportIndividualEventCsvRequest extends FormRequest
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
        ];
    }

    public function attributes(): array
    {
        return [
            'csv_file' => 'CSVファイル',
        ];
    }

    public function messages(): array
    {
        return [
            'csv_file.required' =>
                '取り込むCSVファイルを選択してください。',
            'csv_file.mimes' =>
                'CSV形式のファイルを選択してください。',
            'csv_file.max' =>
                'CSVファイルは10MB以内にしてください。',
        ];
    }
}
