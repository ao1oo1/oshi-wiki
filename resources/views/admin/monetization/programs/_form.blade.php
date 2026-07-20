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
                        (int) old(
                            'service_id',
                            $program->service_id ?? request('service_id')
                        ) === (int) $service->id
                    )
                >
                    {{ $service->name }}
                    {{ $service->is_active ? '' : '（無効）' }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="name" class="oshi-label">プログラム名</label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $program->name ?? '') }}"
            class="oshi-input"
            placeholder="例：もしもアフィリエイト Amazon"
            required
        >
    </div>

    <div>
        <label for="provider_name" class="oshi-label">ASP・提供元</label>
        <input
            id="provider_name"
            type="text"
            name="provider_name"
            value="{{ old('provider_name', $program->provider_name ?? '') }}"
            class="oshi-input"
            placeholder="例：もしもアフィリエイト"
        >
    </div>

    <div>
        <label for="affiliate_identifier" class="oshi-label">
            アフィリエイト識別子
        </label>
        <input
            id="affiliate_identifier"
            type="text"
            name="affiliate_identifier"
            value="{{ old('affiliate_identifier', $program->affiliate_identifier ?? '') }}"
            class="oshi-input"
            autocomplete="off"
        >
        <p class="mt-2 text-xs text-[#718096]">
            URL全体ではなく、提携タグ・IDのみを入力します。
        </p>
    </div>

    <div class="md:col-span-2">
        <label for="url_template" class="oshi-label">URLテンプレート</label>
        <textarea
            id="url_template"
            name="url_template"
            rows="4"
            class="oshi-input font-mono text-sm"
            required
        >{{ old('url_template', $program->url_template ?? '') }}</textarea>
        <p class="mt-2 text-xs leading-6 text-[#718096]">
            httpsから始め、必ず
            <code>{product_code}</code>
            を含めてください。使用可能：
            <code>{affiliate_identifier}</code>、
            <code>{work_id}</code>、
            <code>{campaign_code}</code>、
            <code>{sub_id}</code>
        </p>
    </div>

    <div class="md:col-span-2">
        <label for="allowed_hosts_text" class="oshi-label">許可ホスト</label>
        <textarea
            id="allowed_hosts_text"
            name="allowed_hosts_text"
            rows="3"
            class="oshi-input font-mono text-sm"
            placeholder="amazon.co.jp&#10;www.amazon.co.jp"
            required
        >{{ old(
            'allowed_hosts_text',
            isset($program)
                ? implode(PHP_EOL, $program->allowed_hosts ?? [])
                : ''
        ) }}</textarea>
        <p class="mt-2 text-xs text-[#718096]">
            1行に1件、またはカンマ区切りで入力します。URLではなくホスト名のみです。
        </p>
    </div>

    <div>
        <label for="code_validation_pattern" class="oshi-label">
            商品コード検証パターン
        </label>
        <input
            id="code_validation_pattern"
            type="text"
            name="code_validation_pattern"
            value="{{ old('code_validation_pattern', $program->code_validation_pattern ?? '') }}"
            class="oshi-input font-mono text-sm"
            placeholder="/^[A-Z0-9]{10}$/"
        >
    </div>

    <div>
        <label for="code_example" class="oshi-label">商品コード例</label>
        <input
            id="code_example"
            type="text"
            name="code_example"
            value="{{ old('code_example', $program->code_example ?? '') }}"
            class="oshi-input"
            placeholder="B012345678"
        >
    </div>

    <div class="md:col-span-2">
        <label for="additional_parameters_text" class="oshi-label">
            追加パラメータ（任意）
        </label>
        <textarea
            id="additional_parameters_text"
            name="additional_parameters_text"
            rows="3"
            class="oshi-input font-mono text-sm"
            placeholder='{"source":"oshi-wiki"}'
        >{{ old(
            'additional_parameters_text',
            isset($program) && $program->additional_parameters
                ? json_encode(
                    $program->additional_parameters,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_PRETTY_PRINT
                )
                : ''
        ) }}</textarea>
        <p class="mt-2 text-xs text-[#718096]">
            JSONオブジェクト形式で入力します。v5初期表示では保存のみ行います。
        </p>
    </div>

    <div>
        <label for="priority" class="oshi-label">表示優先順位</label>
        <input
            id="priority"
            type="number"
            name="priority"
            min="0"
            max="9999"
            value="{{ old('priority', $program->priority ?? 0) }}"
            class="oshi-input"
            required
        >
    </div>

    <div>
        <label for="is_default" class="oshi-label">既定プログラム</label>
        <select id="is_default" name="is_default" class="oshi-input">
            <option value="0" @selected((string) old('is_default', isset($program) ? (int) $program->is_default : 0) === '0')>通常</option>
            <option value="1" @selected((string) old('is_default', isset($program) ? (int) $program->is_default : 0) === '1')>このサービスの既定にする</option>
        </select>
    </div>

    <div>
        <label for="is_affiliate" class="oshi-label">リンク区分</label>
        <select id="is_affiliate" name="is_affiliate" class="oshi-input">
            <option value="1" @selected((string) old('is_affiliate', isset($program) ? (int) $program->is_affiliate : 1) === '1')>広告・アフィリエイト</option>
            <option value="0" @selected((string) old('is_affiliate', isset($program) ? (int) $program->is_affiliate : 1) === '0')>報酬のない公式リンク</option>
        </select>
    </div>

    <div>
        <label for="is_active" class="oshi-label">利用状態</label>
        <select id="is_active" name="is_active" class="oshi-input">
            <option value="1" @selected((string) old('is_active', isset($program) ? (int) $program->is_active : 1) === '1')>有効</option>
            <option value="0" @selected((string) old('is_active', isset($program) ? (int) $program->is_active : 1) === '0')>無効</option>
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
                isset($program) && $program->starts_at
                    ? $program->starts_at->format('Y-m-d\TH:i')
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
                isset($program) && $program->ends_at
                    ? $program->ends_at->format('Y-m-d\TH:i')
                    : ''
            ) }}"
            class="oshi-input"
        >
    </div>
</div>
