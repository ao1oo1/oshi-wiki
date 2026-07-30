@csrf
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label for="name" class="oshi-label">スロット名</label>
        <input
            id="name"
            name="name"
            type="text"
            class="oshi-input"
            value="{{ old('name', $adSlot->name ?? '') }}"
            placeholder="例：作品詳細・ページ下部"
            required
        >
    </div>

    <div>
        <label for="monetization_service_id" class="oshi-label">
            広告サービス
        </label>
        <select
            id="monetization_service_id"
            name="monetization_service_id"
            class="oshi-input"
            required
        >
            <option value="">選択してください</option>
            @foreach ($services as $service)
                <option
                    value="{{ $service->id }}"
                    @selected(
                        (int) old(
                            'monetization_service_id',
                            $adSlot->monetization_service_id ?? 0
                        ) === $service->id
                    )
                >
                    {{ $service->name }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-sm text-[#718096]">
            有効なインプレッション課金型サービスのみ表示されます。
        </p>
    </div>

    <div>
        <label for="page_scope" class="oshi-label">対象ページ</label>
        <select
            id="page_scope"
            name="page_scope"
            class="oshi-input"
            required
        >
            @foreach ($pageScopes as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        old(
                            'page_scope',
                            $adSlot->page_scope ?? 'all_public'
                        ) === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="position" class="oshi-label">表示位置</label>
        <select
            id="position"
            name="position"
            class="oshi-input"
            required
        >
            @foreach ($positions as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        old(
                            'position',
                            $adSlot->position ?? 'page_bottom'
                        ) === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="device_type" class="oshi-label">表示端末</label>
        <select
            id="device_type"
            name="device_type"
            class="oshi-input"
            required
        >
            @foreach ($deviceTypes as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        old(
                            'device_type',
                            $adSlot->device_type ?? 'all'
                        ) === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="priority" class="oshi-label">表示順</label>
        <input
            id="priority"
            name="priority"
            type="number"
            min="0"
            max="9999"
            class="oshi-input"
            value="{{ old('priority', $adSlot->priority ?? 0) }}"
            required
        >
        <p class="mt-1 text-sm text-[#718096]">
            数字が小さい広告から先に表示します。
        </p>
    </div>

    <div>
        <label for="starts_at" class="oshi-label">表示開始日時</label>
        <input
            id="starts_at"
            name="starts_at"
            type="datetime-local"
            class="oshi-input"
            value="{{ old(
                'starts_at',
                isset($adSlot) && $adSlot->starts_at
                    ? $adSlot->starts_at->format('Y-m-d\TH:i')
                    : ''
            ) }}"
        >
    </div>

    <div>
        <label for="ends_at" class="oshi-label">表示終了日時</label>
        <input
            id="ends_at"
            name="ends_at"
            type="datetime-local"
            class="oshi-input"
            value="{{ old(
                'ends_at',
                isset($adSlot) && $adSlot->ends_at
                    ? $adSlot->ends_at->format('Y-m-d\TH:i')
                    : ''
            ) }}"
        >
    </div>

    <div>
        <label for="is_active" class="oshi-label">利用状態</label>
        <select
            id="is_active"
            name="is_active"
            class="oshi-input"
            required
        >
            <option
                value="1"
                @selected(
                    (string) old(
                        'is_active',
                        isset($adSlot) ? (int) $adSlot->is_active : 1
                    ) === '1'
                )
            >
                有効
            </option>
            <option
                value="0"
                @selected(
                    (string) old(
                        'is_active',
                        isset($adSlot) ? (int) $adSlot->is_active : 1
                    ) === '0'
                )
            >
                無効
            </option>
        </select>
    </div>
</div>
