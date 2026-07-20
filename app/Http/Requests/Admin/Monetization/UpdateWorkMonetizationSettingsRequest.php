<?php

namespace App\Http\Requests\Admin\Monetization;

use App\Services\WorkMonetizationLinkManagementService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkMonetizationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageAllAdminFeatures();
    }

    public function rules(): array
    {
        return [
            'monetization_enabled' => ['required', 'boolean'],
            'monetization_inheritance' => [
                'required',
                Rule::in(array_keys(
                    WorkMonetizationLinkManagementService::INHERITANCE_OPTIONS
                )),
            ],
            'isbn' => ['nullable', 'string', 'max:32'],
            'official_store_url' => [
                'nullable',
                'url',
                'max:2048',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'monetization_enabled' => '収益リンク表示',
            'monetization_inheritance' => '親子作品のリンク継承',
            'isbn' => 'ISBN',
            'official_store_url' => '公式販売URL',
        ];
    }
}
