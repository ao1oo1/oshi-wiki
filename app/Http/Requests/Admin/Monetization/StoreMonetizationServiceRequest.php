<?php
namespace App\Http\Requests\Admin\Monetization;

use App\Services\MonetizationServiceManagementService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMonetizationServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageAllAdminFeatures();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
            'category' => [
                'required',
                Rule::in(array_keys(
                    MonetizationServiceManagementService::CATEGORIES
                )),
            ],
            'description' => ['nullable', 'string'],
            'default_button_label' => ['nullable', 'string', 'max:100'],
            'priority' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'サービス名',
            'slug' => '識別子',
            'category' => 'カテゴリ',
            'description' => '説明',
            'default_button_label' => '標準ボタン文言',
            'priority' => '表示優先順位',
            'is_active' => '利用状態',
        ];
    }
}
