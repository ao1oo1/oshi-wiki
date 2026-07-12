<?php

namespace App\Http\Requests\Admin\CharacterRelationship;

use Illuminate\Foundation\Http\FormRequest;

class ImportCharacterRelationshipCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageAllAdminFeatures() ?? false;
    }

    public function rules(): array
    {
        return [
            'work_id' => ['nullable', 'integer', 'exists:works,id'],
            'default_status' => ['required', 'string', 'in:draft,published,private'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'work_id' => '作品',
            'default_status' => 'CSV内のstatusが空の場合の状態',
            'csv_file' => 'CSVファイル',
        ];
    }
}
