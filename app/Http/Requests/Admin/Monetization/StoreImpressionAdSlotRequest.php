<?php

namespace App\Http\Requests\Admin\Monetization;

use App\Services\ImpressionAdSlotService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreImpressionAdSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageAllAdminFeatures();
    }

    public function rules(): array
    {
        return [
            'monetization_service_id' => [
                'required',
                'integer',
                Rule::exists('monetization_services', 'id')
                    ->where(
                        fn ($query) => $query
                            ->where('revenue_model', 'impression')
                            ->where('is_active', true)
                            ->whereNull('deleted_at')
                    ),
            ],
            'name' => ['required', 'string', 'max:120'],
            'page_scope' => [
                'required',
                Rule::in(array_keys(ImpressionAdSlotService::PAGE_SCOPES)),
            ],
            'position' => [
                'required',
                Rule::in(array_keys(ImpressionAdSlotService::POSITIONS)),
            ],
            'device_type' => [
                'required',
                Rule::in(array_keys(ImpressionAdSlotService::DEVICE_TYPES)),
            ],
            'priority' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }

    public function attributes(): array
    {
        return [
            'monetization_service_id' => '広告サービス',
            'name' => 'スロット名',
            'page_scope' => '対象ページ',
            'position' => '表示位置',
            'device_type' => '表示端末',
            'priority' => '表示順',
            'is_active' => '利用状態',
            'starts_at' => '表示開始日時',
            'ends_at' => '表示終了日時',
        ];
    }
}
