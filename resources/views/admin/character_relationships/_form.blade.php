<div class="mb-4 rounded bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
    関係性は、同じ作品に登録されているキャラクター同士のみ登録できます。
</div>

@if ($errors->any())
    <div class="mb-4 rounded bg-red-100 px-4 py-3 text-red-800">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-4">
    <label for="work_id" class="mb-1 block font-medium">作品</label>
    <select id="work_id" name="work_id" class="w-full rounded border-gray-300" required>
        <option value="">選択してください</option>
        @foreach ($works as $work)
            <option
                value="{{ $work->id }}"
                @selected(old('work_id', $characterRelationship->work_id ?? $selectedWorkId ?? '') == $work->id)
            >
                {{ $work->title }}
            </option>
        @endforeach
    </select>
</div>

@if ($characters->count() < 2)
    <div class="mb-4 rounded bg-red-50 px-4 py-3 text-red-800">
        この作品には、関係性を登録するために必要なキャラクターが足りません。最低2人以上登録してください。
    </div>
@endif

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="mb-4">
        <label for="from_character_id" class="mb-1 block font-medium">キャラクター</label>
        <select id="from_character_id" name="from_character_id" class="w-full rounded border-gray-300" required>
            <option value="">選択してください</option>
            @foreach ($characters as $character)
                <option
                    value="{{ $character->id }}"
                    @selected(old('from_character_id', $characterRelationship->from_character_id ?? '') == $character->id)
                >
                    {{ $character->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-4">
        <label for="to_character_id" class="mb-1 block font-medium">相手キャラクター</label>
        <select id="to_character_id" name="to_character_id" class="w-full rounded border-gray-300" required>
            <option value="">選択してください</option>
            @foreach ($characters as $character)
                <option
                    value="{{ $character->id }}"
                    @selected(old('to_character_id', $characterRelationship->to_character_id ?? '') == $character->id)
                >
                    {{ $character->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="mb-4">
    <label for="called_name" class="mb-1 block font-medium">呼び方</label>
    <input
        id="called_name"
        type="text"
        name="called_name"
        value="{{ old('called_name', $characterRelationship->called_name ?? '') }}"
        class="w-full rounded border-gray-300"
        placeholder="例：〇〇くん、先輩、呼び捨て"
    >
</div>

<div class="mb-4">
    <label for="relationship" class="mb-1 block font-medium">関係性</label>
    <input
        id="relationship"
        type="text"
        name="relationship"
        value="{{ old('relationship', $characterRelationship->relationship ?? '') }}"
        class="w-full rounded border-gray-300"
        placeholder="例：同級生、幼なじみ、師弟、敵対"
    >
</div>

<div class="mb-4">
    <label for="impression" class="mb-1 block font-medium">印象・気持ち等</label>
    <textarea
        id="impression"
        name="impression"
        rows="4"
        class="w-full rounded border-gray-300"
        placeholder="例：信頼している。尊敬している。苦手意識がある。"
    >{{ old('impression', $characterRelationship->impression ?? '') }}</textarea>
</div>

<div class="mb-6">
    <label for="notes" class="mb-1 block font-medium">補足メモ</label>
    <textarea
        id="notes"
        name="notes"
        rows="3"
        class="w-full rounded border-gray-300"
    >{{ old('notes', $characterRelationship->notes ?? '') }}</textarea>
</div>

@if (auth()->user()?->isSuperAdmin())
<div class="mb-6">
    <label for="status" class="mb-1 block font-medium">状態</label>
    <p class="mb-2 text-sm text-gray-600">
        公開ページに表示したい場合は「公開」を選択してください。
    </p>
    <select id="status" name="status" class="w-full rounded border-gray-300">
        <option value="draft" @selected(old('status', $characterRelationship->status ?? 'draft') === 'draft')>下書き</option>
        <option value="published" @selected(old('status', $characterRelationship->status ?? '') === 'published')>公開</option>
        <option value="private" @selected(old('status', $characterRelationship->status ?? '') === 'private')>非公開</option>
    </select>
</div>
@else
    <div class="mb-6 rounded bg-pink-50 p-4 text-sm">
        情報入力スタッフによる登録・編集は、最高管理者への承認申請として保存されます。
    </div>
@endif

<button
    type="submit"
    style="display:inline-block;background:#2563eb;color:#ffffff;padding:10px 24px;border-radius:8px;font-weight:bold;border:none;cursor:pointer;"
    @disabled($characters->count() < 2)
>
    保存する
</button>
