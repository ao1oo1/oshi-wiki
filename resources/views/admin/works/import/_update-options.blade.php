@php
    $fieldGroups = \App\Support\WorkCsvUpdateOptions::FIELD_GROUPS;
    $defaultFields = collect($fieldGroups)
        ->flatMap(fn (array $fields) => array_keys($fields))
        ->reject(
            fn (string $field) => in_array(
                $field,
                ['parent_work_id', 'tag_ids', 'tag_names'],
                true
            )
        )
        ->values()
        ->all();
@endphp

<section
    class="mt-6 rounded-2xl border border-[#E2E8F0] bg-white p-5"
    data-work-csv-update-options
>
    <h2 class="text-lg font-bold">既存作品の更新設定</h2>

    <p class="mt-2 text-sm leading-7 text-[#4A5568]">
        新規作品は従来どおり登録します。既存作品に一致した行だけ、
        以下の更新設定が適用されます。
    </p>

    <div class="mt-5 grid gap-6">
        <fieldset>
            <legend class="font-bold">更新方法</legend>

            <div class="mt-3 grid gap-2 md:grid-cols-2">
                @foreach ([
                    'selected' => '更新する項目を選択',
                    'all' => 'すべての通常項目を更新',
                    'non_blank' => 'CSVの空欄以外だけ更新',
                    'create_only' => '新規登録のみ',
                    'update_only' => '既存データの更新のみ',
                ] as $value => $label)
                    <label class="flex items-center gap-2">
                        <input
                            type="radio"
                            name="update_mode"
                            value="{{ $value }}"
                            @checked(old('update_mode', 'selected') === $value)
                        >
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="rounded-xl border border-[#E2E8F0] p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <legend class="font-bold">更新する項目</legend>

                <div class="flex gap-2">
                    <button
                        type="button"
                        class="oshi-btn oshi-btn-sub"
                        data-select-all
                    >
                        すべて選択
                    </button>

                    <button
                        type="button"
                        class="oshi-btn oshi-btn-sub"
                        data-clear-all
                    >
                        すべて解除
                    </button>
                </div>
            </div>

            <p class="mt-2 text-sm text-[#718096]">
                タグと親作品IDは初期状態では変更しません。
                character_idsは下の専用設定で指定します。
            </p>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                @foreach ($fieldGroups as $groupLabel => $fields)
                    <div class="rounded-xl bg-[#F7FAFC] p-4">
                        <h3 class="font-bold">{{ $groupLabel }}</h3>

                        <div class="mt-3 grid gap-2">
                            @foreach ($fields as $field => $label)
                                <label class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="update_fields[]"
                                        value="{{ $field }}"
                                        @checked(
                                            in_array(
                                                $field,
                                                old(
                                                    'update_fields',
                                                    $defaultFields
                                                ),
                                                true
                                            )
                                        )
                                    >
                                    <span>{{ $label }}</span>
                                    <code class="text-xs text-[#718096]">
                                        {{ $field }}
                                    </code>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </fieldset>

        <fieldset>
            <legend class="font-bold">CSVの空欄</legend>

            <div class="mt-3 grid gap-2">
                <label class="flex items-center gap-2">
                    <input
                        type="radio"
                        name="blank_value_mode"
                        value="keep"
                        @checked(old('blank_value_mode', 'keep') === 'keep')
                    >
                    <span>既存値を保持する（おすすめ）</span>
                </label>

                <label class="flex items-center gap-2">
                    <input
                        type="radio"
                        name="blank_value_mode"
                        value="clear"
                        @checked(old('blank_value_mode') === 'clear')
                    >
                    <span>選択した項目を空欄にする</span>
                </label>
            </div>
        </fieldset>

        <fieldset class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <legend class="font-bold text-amber-900">
                character_ids・character_names
            </legend>

            <div class="mt-3 grid gap-2">
                @foreach ([
                    'ignore' => '変更しない（おすすめ）',
                    'append' => 'CSVのキャラクターを追加',
                    'replace' => 'CSVの内容で完全に置き換える',
                    'detach' => 'CSVのキャラクターを解除',
                ] as $value => $label)
                    <label class="flex items-start gap-2">
                        <input
                            type="radio"
                            name="character_ids_mode"
                            value="{{ $value }}"
                            @checked(
                                old('character_ids_mode', 'ignore') === $value
                            )
                        >
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <p class="mt-3 text-sm font-bold text-amber-900">
                完全置換では、CSVにないキャラクターとの追加作品の紐付けが解除されます。
            </p>
        </fieldset>

        <fieldset>
            <legend class="font-bold">関連IDにエラーがある場合</legend>

            <div class="mt-3 grid gap-2">
                <label class="flex items-center gap-2">
                    <input
                        type="radio"
                        name="relation_error_mode"
                        value="skip_field"
                        @checked(
                            old('relation_error_mode', 'skip_field')
                                === 'skip_field'
                        )
                    >
                    <span>該当項目だけ更新せず、他の項目を更新</span>
                </label>

                <label class="flex items-center gap-2">
                    <input
                        type="radio"
                        name="relation_error_mode"
                        value="skip_row"
                        @checked(old('relation_error_mode') === 'skip_row')
                    >
                    <span>その行全体を登録・更新しない</span>
                </label>
            </div>
        </fieldset>

        <div class="rounded-xl bg-[#F7FAFC] p-4 text-sm leading-7">
            <strong>おすすめ設定：</strong>
            選択項目だけ更新／空欄は保持／character_idsは変更しない／
            関連IDエラーは該当項目だけスキップ
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector(
        '[data-work-csv-update-options]'
    );

    if (!root) {
        return;
    }

    const checkboxes = root.querySelectorAll(
        'input[name="update_fields[]"]'
    );

    root.querySelector('[data-select-all]')?.addEventListener(
        'click',
        () => checkboxes.forEach((checkbox) => {
            checkbox.checked = true;
        })
    );

    root.querySelector('[data-clear-all]')?.addEventListener(
        'click',
        () => checkboxes.forEach((checkbox) => {
            checkbox.checked = false;
        })
    );
});
</script>
