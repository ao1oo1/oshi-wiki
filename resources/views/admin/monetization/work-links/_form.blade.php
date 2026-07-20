@csrf

@if ($errors->any())
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
        <p class="font-bold">入力内容をご確認ください。</p>
        <ul class="mt-2 list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label for="service_id" class="oshi-label">サービス</label>
        <select id="service_id" name="service_id" class="oshi-input" required>
            <option value="">選択してください</option>
            @foreach ($services as $service)
                <option
                    value="{{ $service->id }}"
                    @selected(
                        (int) old('service_id', $link->service_id ?? 0)
                        === (int) $service->id
                    )
                >
                    {{ $service->name }}
                    {{ $service->is_active ? '' : '（無効）' }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="affiliate_program_id" class="oshi-label">
            提携プログラム
        </label>
        <select
            id="affiliate_program_id"
            name="affiliate_program_id"
            class="oshi-input"
            required
        >
            <option value="">選択してください</option>
            @foreach ($programs as $program)
                <option
                    value="{{ $program->id }}"
                    data-service-id="{{ $program->service_id }}"
                    @selected(
                        (int) old(
                            'affiliate_program_id',
                            $link->affiliate_program_id ?? 0
                        ) === (int) $program->id
                    )
                >
                    {{ $program->service?->name }}：
                    {{ $program->name }}
                    {{ $program->is_active ? '' : '（無効）' }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="product_code" class="oshi-label">商品コード</label>
        <input
            id="product_code"
            type="text"
            name="product_code"
            value="{{ old('product_code', $link->product_code ?? '') }}"
            class="oshi-input font-mono"
            required
        >
        <p class="mt-2 text-xs text-[#718096]">
            URLではなく、ASIN・作品ID・商品IDなどを入力します。
        </p>
    </div>

    <div>
        <label for="product_type" class="oshi-label">商品種別</label>
        <select id="product_type" name="product_type" class="oshi-input">
            @foreach ($productTypes as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        old('product_type', $link->product_type ?? 'series')
                        === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="title" class="oshi-label">表示タイトル</label>
        <input
            id="title"
            type="text"
            name="title"
            value="{{ old('title', $link->title ?? '') }}"
            class="oshi-input"
            placeholder="例：第1巻"
        >
    </div>

    <div>
        <label for="button_label" class="oshi-label">ボタン文言</label>
        <input
            id="button_label"
            type="text"
            name="button_label"
            value="{{ old('button_label', $link->button_label ?? '') }}"
            class="oshi-input"
            placeholder="空欄時はサービスの標準文言"
        >
    </div>

    <div>
        <label for="campaign_code" class="oshi-label">
            キャンペーンコード
        </label>
        <input
            id="campaign_code"
            type="text"
            name="campaign_code"
            value="{{ old('campaign_code', $link->campaign_code ?? '') }}"
            class="oshi-input"
        >
    </div>

    <div>
        <label for="availability_status" class="oshi-label">提供状況</label>
        <select
            id="availability_status"
            name="availability_status"
            class="oshi-input"
        >
            @foreach ($availabilityStatuses as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        old(
                            'availability_status',
                            $link->availability_status ?? 'unknown'
                        ) === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="priority" class="oshi-label">表示優先順位</label>
        <input
            id="priority"
            type="number"
            name="priority"
            min="0"
            max="9999"
            value="{{ old('priority', $link->priority ?? 0) }}"
            class="oshi-input"
            required
        >
    </div>

    <div>
        <label for="is_active" class="oshi-label">利用状態</label>
        <select id="is_active" name="is_active" class="oshi-input">
            <option value="1" @selected((string) old('is_active', isset($link) ? (int) $link->is_active : 1) === '1')>有効</option>
            <option value="0" @selected((string) old('is_active', isset($link) ? (int) $link->is_active : 1) === '0')>無効</option>
        </select>
    </div>

    <div>
        <label for="starts_at" class="oshi-label">開始日時</label>
        <input
            id="starts_at"
            type="datetime-local"
            name="starts_at"
            value="{{ old(
                'starts_at',
                isset($link) && $link->starts_at
                    ? $link->starts_at->format('Y-m-d\TH:i')
                    : ''
            ) }}"
            class="oshi-input"
        >
    </div>

    <div>
        <label for="ends_at" class="oshi-label">終了日時</label>
        <input
            id="ends_at"
            type="datetime-local"
            name="ends_at"
            value="{{ old(
                'ends_at',
                isset($link) && $link->ends_at
                    ? $link->ends_at->format('Y-m-d\TH:i')
                    : ''
            ) }}"
            class="oshi-input"
        >
    </div>

    <div class="md:col-span-2">
        <label for="verification_note" class="oshi-label">確認メモ</label>
        <textarea
            id="verification_note"
            name="verification_note"
            rows="3"
            class="oshi-input"
        >{{ old('verification_note', $link->verification_note ?? '') }}</textarea>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const service = document.getElementById('service_id');
    const program = document.getElementById('affiliate_program_id');

    if (!service || !program) return;

    const filterPrograms = function () {
        const serviceId = service.value;

        Array.from(program.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = option.dataset.serviceId !== serviceId;
        });

        const selected = program.options[program.selectedIndex];

        if (
            selected
            && selected.value
            && selected.dataset.serviceId !== serviceId
        ) {
            program.value = '';
        }
    };

    service.addEventListener('change', filterPrograms);
    filterPrograms();
});
</script>
