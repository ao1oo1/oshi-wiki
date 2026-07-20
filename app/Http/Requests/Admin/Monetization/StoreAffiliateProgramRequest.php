<?php

namespace App\Http\Requests\Admin\Monetization;

use Illuminate\Foundation\Http\FormRequest;

class StoreAffiliateProgramRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'provider_name' => ['nullable', 'string', 'max:150'],
            'url_template' => ['required', 'string', 'max:5000'],
            'affiliate_identifier' => ['nullable', 'string', 'max:255'],
            'additional_parameters_text' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'allowed_hosts_text' => ['required', 'string', 'max:3000'],
            'code_validation_pattern' => [
                'nullable',
                'string',
                'max:500',
            ],
            'code_example' => ['nullable', 'string', 'max:255'],
            'priority' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_default' => ['required', 'boolean'],
            'is_affiliate' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'service_id' => 'サービス',
            'name' => 'プログラム名',
            'provider_name' => 'ASP・提供元',
            'url_template' => 'URLテンプレート',
            'affiliate_identifier' => 'アフィリエイト識別子',
            'additional_parameters_text' => '追加パラメータ',
            'allowed_hosts_text' => '許可ホスト',
            'code_validation_pattern' => '商品コード検証パターン',
            'code_example' => '商品コード例',
            'priority' => '表示優先順位',
            'is_default' => '既定設定',
            'is_affiliate' => '広告リンク区分',
            'is_active' => '利用状態',
            'starts_at' => '開始日時',
            'ends_at' => '終了日時',
        ];
    }
}
