<?php

namespace App\Http\Requests\Admin\Character;

use Illuminate\Foundation\Http\FormRequest;

class ImportCharacterCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_id' => ['nullable', 'exists:works,id'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'default_status' => ['nullable', 'in:draft,published,private'],
        ];
    }

    public function attributes(): array
    {
        return [
            'work_id' => '登録先の作品',
            'csv_file' => 'CSVファイル',
            'default_status' => '初期状態',
        ];
    }
}
