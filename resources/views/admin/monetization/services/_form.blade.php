@csrf
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label for="name" class="mb-1 block font-bold text-[#2D3748]">サービス名</label>
        <input id="name" type="text" name="name" value="{{ old('name', $service->name ?? '') }}" class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3" required>
    </div>
    <div>
        <label for="slug" class="mb-1 block font-bold text-[#2D3748]">識別子</label>
        <input id="slug" type="text" name="slug" value="{{ old('slug', $service->slug ?? '') }}" placeholder="例：dmm-tv" class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3">
        <p class="mt-1 text-sm text-[#718096]">空欄の場合はサービス名から自動生成します。</p>
    </div>
    <div>
        <label for="category" class="mb-1 block font-bold text-[#2D3748]">カテゴリ</label>
        <select id="category" name="category" class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3" required>
            @foreach ($categories as $value => $label)
                <option value="{{ $value }}" @selected(old('category', $service->category ?? 'vod') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="default_button_label" class="mb-1 block font-bold text-[#2D3748]">標準ボタン文言</label>
        <input id="default_button_label" type="text" name="default_button_label" value="{{ old('default_button_label', $service->default_button_label ?? '') }}" placeholder="例：DMM TVで見る" class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3">
    </div>
    <div>
        <label for="priority" class="mb-1 block font-bold text-[#2D3748]">表示優先順位</label>
        <input id="priority" type="number" name="priority" min="0" max="9999" value="{{ old('priority', $service->priority ?? 0) }}" class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3" required>
        <p class="mt-1 text-sm text-[#718096]">数字が小さいサービスから先に表示します。</p>
    </div>
    <div>
        <label for="is_active" class="mb-1 block font-bold text-[#2D3748]">利用状態</label>
        <select id="is_active" name="is_active" class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3" required>
            <option value="1" @selected((string) old('is_active', isset($service) ? (int) $service->is_active : 1) === '1')>有効</option>
            <option value="0" @selected((string) old('is_active', isset($service) ? (int) $service->is_active : 1) === '0')>無効</option>
        </select>
    </div>
    <div class="md:col-span-2">
        <label for="description" class="mb-1 block font-bold text-[#2D3748]">管理用説明</label>
        <textarea id="description" name="description" rows="4" class="w-full rounded-2xl border border-[#CBD5E0] px-4 py-3">{{ old('description', $service->description ?? '') }}</textarea>
    </div>
</div>
