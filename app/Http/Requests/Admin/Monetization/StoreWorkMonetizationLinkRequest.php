<?php

namespace App\Http\Requests\Admin\Monetization;

use App\Services\WorkMonetizationLinkManagementService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkMonetizationLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageAllAdminFeatures();
    }

    public function rules(): array
    {
        return [
            'service_id' => [
                'required',
                'integer',
                'exists:monetization_services,id',
            ],
            'affiliate_program_id' => [
                'required',
                'integer',
                'exists:affiliate_programs,id',
            ],
            'product_code' => [
                'required',
                'string',
                'max:255',
            ],
            'product_type' => [
                'required',
                Rule::in(array_keys(
                    WorkMonetizationLinkManagementService::PRODUCT_TYPES
                )),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'button_label' => ['nullable', 'string', 'max:100'],
            'campaign_code' => ['nullable', 'string', 'max:255'],
            'availability_status' => [
                'required',
                Rule::in(array_keys(
                    WorkMonetizationLinkManagementService::AVAILABILITY_STATUSES
                )),
            ],
            'priority' => [
                'required',
                'integer',
                'min:0',
                'max:9999',
            ],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'verification_note' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'service_id' => 'サービス',
            'affiliate_program_id' => '提携プログラム',
            'product_code' => '商品コード',
            'product_type' => '商品種別',
            'title' => '表示タイトル',
            'button_label' => 'ボタン文言',
            'campaign_code' => 'キャンペーンコード',
            'availability_status' => '提供状況',
            'priority' => '表示優先順位',
            'is_active' => '利用状態',
            'starts_at' => '開始日時',
            'ends_at' => '終了日時',
            'verification_note' => '確認メモ',
        ];
    }
}
