<?php

namespace App\Http\Requests\Admin\Monetization;

use App\Services\ImpressionAdSlotService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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


    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $writerPositions = [
                    'writer_sidebar_1',
                    'writer_sidebar_2',
                    'writer_page_bottom',
                ];

                $position = (string) $this->input('position');
                $pageScope = (string) $this->input('page_scope');
                $isWriterPosition = in_array(
                    $position,
                    $writerPositions,
                    true
                );

                if ($isWriterPosition && $pageScope !== 'writer_all') {
                    $validator->errors()->add(
                        'page_scope',
                        'Writer用の表示位置では、対象ページを'
                        . '「Writer画面すべて」にしてください。'
                    );
                }

                if (! $isWriterPosition && $pageScope === 'writer_all') {
                    $validator->errors()->add(
                        'position',
                        '対象ページが「Writer画面すべて」の場合は、'
                        . 'Writer用の表示位置を選択してください。'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'monetization_service_id.required' =>
                '広告サービスを選択してください。',
            'monetization_service_id.integer' =>
                '広告サービスの値が正しくありません。',
            'monetization_service_id.exists' =>
                '選択した広告サービスは利用できません。',
            'name.required' =>
                'スロット名を入力してください。',
            'name.max' =>
                'スロット名は120文字以内で入力してください。',
            'page_scope.required' =>
                '対象ページを選択してください。',
            'page_scope.in' =>
                '対象ページの値が正しくありません。',
            'position.required' =>
                '表示位置を選択してください。',
            'position.in' =>
                '表示位置の値が正しくありません。',
            'device_type.required' =>
                '表示端末を選択してください。',
            'device_type.in' =>
                '表示端末の値が正しくありません。',
            'priority.required' =>
                '表示順を入力してください。',
            'priority.integer' =>
                '表示順は整数で入力してください。',
            'priority.min' =>
                '表示順は0以上で入力してください。',
            'priority.max' =>
                '表示順は9999以下で入力してください。',
            'is_active.required' =>
                '利用状態を選択してください。',
            'is_active.boolean' =>
                '利用状態の値が正しくありません。',
            'starts_at.date' =>
                '表示開始日時を正しい日時形式で入力してください。',
            'ends_at.date' =>
                '表示終了日時を正しい日時形式で入力してください。',
            'ends_at.after_or_equal' =>
                '表示終了日時は表示開始日時以降にしてください。',
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
