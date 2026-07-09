@csrf

@php
    $inputClass = 'w-full rounded-2xl border-[#E2E8F0] bg-white px-4 py-3 text-[#2D3748] shadow-sm focus:border-[#FED7E2] focus:ring-[#FED7E2]';
    $labelClass = 'mb-2 block text-base font-bold text-[#2D3748]';
    $textareaClass = 'w-full rounded-2xl border-[#E2E8F0] bg-white px-4 py-3 text-[#2D3748] shadow-sm focus:border-[#FED7E2] focus:ring-[#FED7E2]';

    $fromRef = old('from_character_ref');
    $toRef = old('to_character_ref');

    if (! $fromRef && isset($relationship)) {
        $fromRef = $relationship->from_character_source === \App\Models\OriginalCharacterRelationship::SOURCE_V1_CHARACTER
            ? 'v1_character:' . $relationship->from_character_id
            : 'original:' . $relationship->from_original_character_id;
    }

    if (! $toRef && isset($relationship)) {
        $toRef = $relationship->to_character_source === \App\Models\OriginalCharacterRelationship::SOURCE_V1_CHARACTER
            ? 'v1_character:' . $relationship->to_character_id
            : 'original:' . $relationship->to_original_character_id;
    }

    $totalSelectableCharacters = $characters->count() + $officialCharacters->count();
@endphp

@if ($totalSelectableCharacters < 2)
    <div class="mb-6 rounded-2xl border border-yellow-200 bg-yellow-50 px-5 py-4 text-sm font-bold text-yellow-700">
        関係性を登録するには、オリジナルキャラクターまたは作品キャラクターが2人以上必要です。
    </div>
@endif

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="{{ $labelClass }}">キャラクター <span class="text-red-500">*</span></label>
        <select name="from_character_ref" class="{{ $inputClass }}" required>
            <option value="">選択してください</option>

            @if ($characters->isNotEmpty())
                <optgroup label="オリジナルキャラクター">
                    @foreach ($characters as $character)
                        <option value="original:{{ $character->id }}" @selected($fromRef === 'original:' . $character->id)>
                            {{ $character->name }}
                        </option>
                    @endforeach
                </optgroup>
            @endif

            @if ($officialCharacters->isNotEmpty())
                <optgroup label="作品キャラクター">
                    @foreach ($officialCharacters as $character)
                        <option value="v1_character:{{ $character->id }}" @selected($fromRef === 'v1_character:' . $character->id)>
                            {{ ($character->work_title ?? null) ? $character->work_title . ' ＞ ' . $character->name : $character->name }}
                        </option>
                    @endforeach
                </optgroup>
            @endif
        </select>
    </div>

    <div>
        <label class="{{ $labelClass }}">相手キャラクター <span class="text-red-500">*</span></label>
        <select name="to_character_ref" class="{{ $inputClass }}" required>
            <option value="">選択してください</option>

            @if ($characters->isNotEmpty())
                <optgroup label="オリジナルキャラクター">
                    @foreach ($characters as $character)
                        <option value="original:{{ $character->id }}" @selected($toRef === 'original:' . $character->id)>
                            {{ $character->name }}
                        </option>
                    @endforeach
                </optgroup>
            @endif

            @if ($officialCharacters->isNotEmpty())
                <optgroup label="作品キャラクター">
                    @foreach ($officialCharacters as $character)
                        <option value="v1_character:{{ $character->id }}" @selected($toRef === 'v1_character:' . $character->id)>
                            {{ ($character->work_title ?? null) ? $character->work_title . ' ＞ ' . $character->name : $character->name }}
                        </option>
                    @endforeach
                </optgroup>
            @endif
        </select>
    </div>

    <div>
        <label class="{{ $labelClass }}">相手への呼び方</label>
        <input type="text" name="called_name" value="{{ old('called_name', $relationship->called_name ?? '') }}" class="{{ $inputClass }}" placeholder="例：名前、苗字、先生、先輩、兄さん">
    </div>

    <div>
        <label class="{{ $labelClass }}">関係性</label>
        <input type="text" name="relationship_type" value="{{ old('relationship_type', $relationship->relationship_type ?? '') }}" class="{{ $inputClass }}" placeholder="例：友人、幼なじみ、師弟、家族、敵対">
    </div>

    <div>
        <label class="{{ $labelClass }}">状態</label>
        <select name="status" class="{{ $inputClass }}">
            @php($status = old('status', $relationship->status ?? 'active'))
            <option value="active" @selected($status === 'active')>有効</option>
            <option value="draft" @selected($status === 'draft')>下書き</option>
        </select>
    </div>
</div>

<div class="mt-8 grid gap-6">
    <div>
        <label class="{{ $labelClass }}">印象・気持ち</label>
        <textarea name="impression" rows="6" class="{{ $textareaClass }}" placeholder="例：信頼している。気になる存在。苦手意識があるが放っておけない。">{{ old('impression', $relationship->impression ?? '') }}</textarea>
    </div>

    <div>
        <label class="{{ $labelClass }}">備考</label>
        <textarea name="notes" rows="5" class="{{ $textareaClass }}" placeholder="関係性を書く上で補足しておきたいこと">{{ old('notes', $relationship->notes ?? '') }}</textarea>
    </div>
</div>

<div class="mt-8 flex flex-wrap items-center gap-3">
    <button type="submit"
            class="rounded-2xl bg-[#FED7E2] px-6 py-3 text-base font-bold text-[#2D3748] shadow-sm hover:opacity-90"
            @disabled($totalSelectableCharacters < 2)>
        保存する
    </button>

    <a href="{{ route('writer.original-character-relationships.index') }}"
       class="rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 text-base font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
        一覧へ戻る
    </a>
</div>
