@csrf

@if ($errors->any())
    <div
        id="validation-errors"
        class="mb-6 rounded-2xl border border-red-300 bg-red-50 p-5
               text-red-800"
        role="alert"
        aria-live="assertive"
    >
        <p class="font-bold">
            入力内容をご確認ください。
        </p>
        <p class="mt-1 text-sm">
            登録・更新できなかった理由は以下のとおりです。
        </p>
        <ul class="mt-3 list-disc space-y-1 pl-5 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@php
    $selectedRevenueModel = old(
        'revenue_model',
        $service->revenue_model ?? 'affiliate_link'
    );
    $selectedImpressionAdFormat = old(
        'impression_ad_format',
        $service->impression_ad_format ?? 'script'
    );
    $allowedScriptHosts = old(
        'allowed_script_hosts_text',
        isset($service)
            ? implode("\n", $service->allowed_script_hosts ?? [])
            : ''
    );
@endphp

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label for="name" class="mb-1 block font-bold text-[#2D3748]">
            サービス名
        </label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $service->name ?? '') }}"
            class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3"
            required
        >
    </div>

    <div>
        <label for="slug" class="mb-1 block font-bold text-[#2D3748]">
            識別子
        </label>
        <input
            id="slug"
            type="text"
            name="slug"
            value="{{ old('slug', $service->slug ?? '') }}"
            placeholder="例：shinobi-admax"
            class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3"
        >
        <p class="mt-1 text-sm text-[#718096]">
            空欄の場合はサービス名から自動生成します。
        </p>
    </div>

    <div>
        <label for="category" class="mb-1 block font-bold text-[#2D3748]">
            カテゴリ
        </label>
        <select
            id="category"
            name="category"
            class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3"
            required
        >
            @foreach ($categories as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        old('category', $service->category ?? 'other')
                        === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label
            for="revenue_model"
            class="mb-1 block font-bold text-[#2D3748]"
        >
            収益方式
        </label>
        <select
            id="revenue_model"
            name="revenue_model"
            class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3"
            required
        >
            @foreach ($revenueModels as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected($selectedRevenueModel === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label
            for="default_button_label"
            class="mb-1 block font-bold text-[#2D3748]"
        >
            標準ボタン文言
        </label>
        <input
            id="default_button_label"
            type="text"
            name="default_button_label"
            value="{{ old(
                'default_button_label',
                $service->default_button_label ?? ''
            ) }}"
            placeholder="リンク型サービスのみ使用"
            class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3"
        >
    </div>

    <div>
        <label for="priority" class="mb-1 block font-bold text-[#2D3748]">
            表示優先順位
        </label>
        <input
            id="priority"
            type="number"
            name="priority"
            min="0"
            max="9999"
            value="{{ old('priority', $service->priority ?? 0) }}"
            class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3"
            required
        >
        <p class="mt-1 text-sm text-[#718096]">
            数字が小さいサービスから先に表示します。
        </p>
    </div>

    <div>
        <label for="is_active" class="mb-1 block font-bold text-[#2D3748]">
            利用状態
        </label>
        <select
            id="is_active"
            name="is_active"
            class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3"
            required
        >
            <option
                value="1"
                @selected(
                    (string) old(
                        'is_active',
                        isset($service) ? (int) $service->is_active : 1
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
                        isset($service) ? (int) $service->is_active : 1
                    ) === '0'
                )
            >
                無効
            </option>
        </select>
    </div>

    <div
        id="impression-ad-fields"
        class="space-y-5 rounded-3xl border border-[#FBCFE8]
               bg-white p-5 md:col-span-2"
    >
        <div>
            <h3 class="text-lg font-bold text-[#2D3748]">
                インプレッション課金型広告
            </h3>
            <p class="mt-1 text-sm text-[#718096]">
                忍者AdMaxなど、表示回数に応じて収益が発生する
                外部広告コードを登録します。
            </p>
        </div>

        <div>
            <label
                for="impression_ad_format"
                class="mb-1 block font-bold text-[#2D3748]"
            >
                広告形式
            </label>
            <select
                id="impression_ad_format"
                name="impression_ad_format"
                class="w-full rounded-2xl border border-[#CBD5E0]
                       px-4 py-3"
            >
                <option
                    value="script"
                    @selected($selectedImpressionAdFormat === 'script')
                >
                    スクリプト広告
                </option>
                <option
                    value="text"
                    @selected($selectedImpressionAdFormat === 'text')
                >
                    テキスト広告
                </option>
                <option
                    value="image"
                    @selected($selectedImpressionAdFormat === 'image')
                >
                    画像広告
                </option>
            </select>
            <p class="mt-1 text-sm text-[#718096]">
                A8.netのバナー広告は「画像広告」を選択します。
            </p>
        </div>

        <div>
            <label
                for="impression_script"
                class="mb-1 block font-bold text-[#2D3748]"
            >
                広告コード
            </label>
            <textarea
                id="impression_script"
                name="impression_script"
                rows="5"
                class="w-full rounded-2xl border border-[#CBD5E0]
                       px-4 py-3 font-mono text-sm"
                placeholder='<script async src="https://adm.shinobi.jp/st/auto.js" data-admax-id="..."></script>'
            >{{ old(
                'impression_script',
                $service->impression_script ?? ''
            ) }}</textarea>
            <p class="mt-2 text-sm leading-6 text-[#718096]">
                対応形式：外部scriptタグ、A8.net等の
                テキストリンク広告、またはバナー画像広告。
                A8.netの場合、
                許可広告ホストへ <code>px.a8.net</code> と
                <code>www10.a8.net</code> を登録してください。
            </p>
            <p class="mt-1 text-sm text-[#718096]">
                選択した広告形式とコードの構造が一致しているか
                保存時に検証します。インラインJavaScriptや
                イベント属性は登録できません。
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label
                    for="ad_identifier"
                    class="mb-1 block font-bold text-[#2D3748]"
                >
                    広告ID
                </label>
                <input
                    id="ad_identifier"
                    type="text"
                    name="ad_identifier"
                    value="{{ old(
                        'ad_identifier',
                        $service->ad_identifier ?? ''
                    ) }}"
                    placeholder="c1ab53ba195c669c5a67b27cc42cbb83"
                    class="w-full rounded-2xl border border-[#CBD5E0]
                           px-4 py-3 font-mono"
                >
            </div>

            <div>
                <label
                    for="allowed_script_hosts_text"
                    class="mb-1 block font-bold text-[#2D3748]"
                >
                    許可広告ホスト
                </label>
                <textarea
                    id="allowed_script_hosts_text"
                    name="allowed_script_hosts_text"
                    rows="3"
                    class="w-full rounded-2xl border border-[#CBD5E0]
                           px-4 py-3 font-mono text-sm"
                    placeholder="adm.shinobi.jp"
                >{{ $allowedScriptHosts }}</textarea>
                <p class="mt-1 text-sm text-[#718096]">
                    1行に1件、URLではなくホスト名だけを入力します。
                </p>
            </div>
        </div>

        <div
            id="a8-image-example"
            class="rounded-2xl border border-[#E2E8F0]
                   bg-[#F7FAFC] p-4 text-sm text-[#4A5568]"
        >
            <p class="font-bold">A8.net画像広告の入力例</p>
            <pre class="mt-2 whitespace-pre-wrap break-all font-mono text-xs">&lt;a href="https://px.a8.net/..." rel="nofollow"&gt;
&lt;img border="0" width="320" height="50" alt="" src="https://www22.a8.net/..."&gt;&lt;/a&gt;
&lt;img border="0" width="1" height="1" src="https://www13.a8.net/..." alt=""&gt;</pre>
            <p class="mt-2">
                許可ホスト例：
                <code>px.a8.net</code>、
                <code>www22.a8.net</code>、
                <code>www13.a8.net</code>
            </p>
        </div>

        <div class="rounded-2xl bg-[#FFF5F7] p-4 text-sm text-[#4A5568]">
            <p class="font-bold">忍者AdMaxの入力例</p>
            <p class="mt-2 font-mono break-all">
                &lt;script async
                src="https://adm.shinobi.jp/st/auto.js"
                data-admax-id="c1ab53ba195c669c5a67b27cc42cbb83"&gt;
                &lt;/script&gt;
            </p>
            <p class="mt-2">
                許可ホスト：<code>adm.shinobi.jp</code>
            </p>
        </div>
    </div>

    <div class="md:col-span-2">
        <label
            for="description"
            class="mb-1 block font-bold text-[#2D3748]"
        >
            管理用説明
        </label>
        <textarea
            id="description"
            name="description"
            rows="4"
            class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3"
        >{{ old('description', $service->description ?? '') }}</textarea>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const revenueModel = document.getElementById('revenue_model');
    const impressionFields = document.getElementById(
        'impression-ad-fields'
    );

    const toggleImpressionFields = () => {
        const enabled = revenueModel.value === 'impression';
        impressionFields.classList.toggle('hidden', !enabled);

        impressionFields
            .querySelectorAll('input, textarea')
            .forEach((field) => {
                field.disabled = !enabled;
            });
    };

    revenueModel.addEventListener('change', toggleImpressionFields);
    toggleImpressionFields();
});
</script>
