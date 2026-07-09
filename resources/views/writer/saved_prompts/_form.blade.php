@csrf

@php
    $inputClass = 'w-full rounded-2xl border-[#E2E8F0] bg-white px-4 py-3 text-[#2D3748] shadow-sm focus:border-[#FED7E2] focus:ring-[#FED7E2]';
    $labelClass = 'mb-2 block text-base font-bold text-[#2D3748]';
    $textareaClass = 'w-full rounded-2xl border-[#E2E8F0] bg-white px-4 py-3 text-[#2D3748] shadow-sm focus:border-[#FED7E2] focus:ring-[#FED7E2]';

    $savedPrompt = $savedPrompt ?? null;

    $workRef = old('work_ref');
    if (! $workRef && $savedPrompt) {
        $workRef = $savedPrompt->work_source === \App\Models\SavedPrompt::WORK_SOURCE_V1
            ? 'work:' . $savedPrompt->work_id
            : 'original';
    }
    $workRef = $workRef ?: 'original';

    $selectedRefs = old('selected_character_refs', $savedPrompt->selected_character_refs ?? []);
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="{{ $labelClass }}">タイトル <span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $savedPrompt->title ?? '') }}" class="{{ $inputClass }}" placeholder="例：会話シーン作成用" required>
    </div>

    <div>
        <label class="{{ $labelClass }}">状態</label>
        <select name="status" class="{{ $inputClass }}">
            @php($status = old('status', $savedPrompt->status ?? 'active'))
            <option value="active" @selected($status === 'active')>有効</option>
            <option value="draft" @selected($status === 'draft')>下書き</option>
        </select>
    </div>

    <div>
        <label class="{{ $labelClass }}">作品名 <span class="text-red-500">*</span></label>
        <select name="work_ref" class="{{ $inputClass }}" required>
            <option value="original" @selected($workRef === 'original')>オリジナル</option>

            @if ($works->isNotEmpty())
                <optgroup label="登録済み作品">
                    @foreach ($works as $work)
                        <option value="work:{{ $work->id }}" @selected($workRef === 'work:' . $work->id)>
                            {{ $work->title }}
                        </option>
                    @endforeach
                </optgroup>
            @endif
        </select>
    </div>

    <div>
        <label class="{{ $labelClass }}">用途</label>
        <input type="text" name="purpose" value="{{ old('purpose', $savedPrompt->purpose ?? '') }}" class="{{ $inputClass }}" placeholder="例：会話シーンを書くとき">
    </div>

    <div>
        <label class="{{ $labelClass }}">作風 <span class="text-red-500">*</span></label>
        <select name="writing_style" class="{{ $inputClass }}" required>
            @php($selectedStyle = old('writing_style', $savedPrompt->writing_style ?? 'dream_novel'))
            @foreach ($writingStyleLabels as $value => $label)
                <option value="{{ $value }}" @selected($selectedStyle === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="{{ $labelClass }}">作風：その他</label>
        <input type="text" name="writing_style_other" value="{{ old('writing_style_other', $savedPrompt->writing_style_other ?? '') }}" class="{{ $inputClass }}" placeholder="その他を選択した場合のみ入力">
    </div>

    <div>
        <label class="{{ $labelClass }}">ジャンル <span class="text-red-500">*</span></label>
        <select name="genre" class="{{ $inputClass }}" required>
            @php($selectedGenre = old('genre', $savedPrompt->genre ?? 'love_comedy'))
            @foreach ($genreLabels as $value => $label)
                <option value="{{ $value }}" @selected($selectedGenre === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="{{ $labelClass }}">ジャンル：その他</label>
        <input type="text" name="genre_other" value="{{ old('genre_other', $savedPrompt->genre_other ?? '') }}" class="{{ $inputClass }}" placeholder="その他を選択した場合のみ入力">
    </div>
</div>

<div class="mt-8">
    <label class="{{ $labelClass }}">登場人物</label>
    <select name="selected_character_refs[]" class="{{ $inputClass }}" multiple size="12">
        @if ($originalCharacters->isNotEmpty())
            <optgroup label="オリジナルキャラクター">
                @foreach ($originalCharacters as $character)
                    <option value="original:{{ $character->id }}" @selected(in_array('original:' . $character->id, $selectedRefs, true))>
                        {{ $character->name }}
                    </option>
                @endforeach
            </optgroup>
        @endif

        @if ($officialCharacters->isNotEmpty())
            <optgroup label="作品キャラクター">
                @foreach ($officialCharacters as $character)
                    <option value="v1_character:{{ $character->id }}" @selected(in_array('v1_character:' . $character->id, $selectedRefs, true))>
                        {{ $character->work?->title ? $character->work->title . ' ＞ ' . $character->name : $character->name }}
                    </option>
                @endforeach
            </optgroup>
        @endif
    </select>
    <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
        複数選択できます。Macでは command キーを押しながら選択してください。
    </p>
</div>

<div class="mt-8 grid gap-6">
    <div>
        <label class="{{ $labelClass }}">あらすじ</label>
        <textarea name="synopsis" rows="5" class="{{ $textareaClass }}" placeholder="例：物語全体の流れや場面の前提">{{ old('synopsis', $savedPrompt->synopsis ?? '') }}</textarea>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <label class="{{ $labelClass }}">起</label>
            <textarea name="plot_opening" rows="5" class="{{ $textareaClass }}" placeholder="物語の始まり">{{ old('plot_opening', $savedPrompt->plot_opening ?? '') }}</textarea>
        </div>

        <div>
            <label class="{{ $labelClass }}">承</label>
            <textarea name="plot_development" rows="5" class="{{ $textareaClass }}" placeholder="展開・深まり">{{ old('plot_development', $savedPrompt->plot_development ?? '') }}</textarea>
        </div>

        <div>
            <label class="{{ $labelClass }}">転</label>
            <textarea name="plot_turn" rows="5" class="{{ $textareaClass }}" placeholder="変化・山場">{{ old('plot_turn', $savedPrompt->plot_turn ?? '') }}</textarea>
        </div>

        <div>
            <label class="{{ $labelClass }}">結</label>
            <textarea name="plot_conclusion" rows="5" class="{{ $textareaClass }}" placeholder="締め・余韻">{{ old('plot_conclusion', $savedPrompt->plot_conclusion ?? '') }}</textarea>
        </div>
    </div>

    <div>
        <label class="{{ $labelClass }}">備考</label>
        <textarea name="notes" rows="5" class="{{ $textareaClass }}" placeholder="避けたい表現、補足、出力条件など">{{ old('notes', $savedPrompt->notes ?? '') }}</textarea>
    </div>
</div>

<input type="hidden" name="category" value="scene">

<div class="mt-8 rounded-2xl bg-[#FFF1F5] px-5 py-4 text-sm font-bold text-[#4A5568]">
    保存すると、入力内容からAIに貼り付けるためのプロンプト本文が自動生成されます。
</div>

<div class="mt-8 flex flex-wrap items-center gap-3">
    <button type="submit" class="rounded-2xl bg-[#FED7E2] px-6 py-3 text-base font-bold text-[#2D3748] shadow-sm hover:opacity-90">
        保存する
    </button>

    <a href="{{ route('writer.prompts.index') }}" class="rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 text-base font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
        一覧へ戻る
    </a>
</div>
