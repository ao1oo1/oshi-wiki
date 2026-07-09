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

    $selectedStyle = old('writing_style', $savedPrompt->writing_style ?? 'dream_novel');
    $selectedGenre = old('genre', $savedPrompt->genre ?? 'love_comedy');
@endphp

<div class="space-y-8">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">STEP 1</p>
            <h3 class="mt-1 text-2xl font-bold text-[#2D3748]">基本情報</h3>
            <p class="mt-2 text-sm leading-7 text-[#718096]">
                保存するプロンプトのタイトルと、どの作品向けに作るかを選択します。
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="{{ $labelClass }}">タイトル <span class="text-red-500">*</span></label>
                <input type="text"
                       name="title"
                       value="{{ old('title', $savedPrompt->title ?? '') }}"
                       class="{{ $inputClass }}"
                       placeholder="例：会話シーン作成用"
                       required>
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
                <select id="work-ref" name="work_ref" class="{{ $inputClass }}" required>
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
                <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
                    v1で登録した作品、またはオリジナルを選択できます。
                </p>
            </div>

            <div>
                <label class="{{ $labelClass }}">用途</label>
                <input type="text"
                       name="purpose"
                       value="{{ old('purpose', $savedPrompt->purpose ?? '') }}"
                       class="{{ $inputClass }}"
                       placeholder="例：会話シーンを書くとき">
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">STEP 2</p>
            <h3 class="mt-1 text-2xl font-bold text-[#2D3748]">登場人物</h3>
            <p class="mt-2 text-sm leading-7 text-[#718096]">
                プロンプトに入れたいキャラクターを選択します。選んだキャラクターの登録情報が、生成されるプロンプト本文に自動で反映されます。
            </p>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl bg-[#FFF8FA] p-5">
                <h4 class="text-lg font-bold text-[#2D3748]">オリジナルキャラクター</h4>
                <p class="mt-1 text-sm font-bold text-[#A0AEC0]">v2で登録したキャラクター</p>

                <div class="mt-5 max-h-96 space-y-3 overflow-y-auto pr-1">
                    @forelse ($originalCharacters as $character)
                        @php($ref = 'original:' . $character->id)
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-[#E2E8F0] bg-white p-4 hover:bg-[#FFF1F5]">
                            <input type="checkbox"
                                   name="selected_character_refs[]"
                                   value="{{ $ref }}"
                                   class="mt-1 rounded border-[#CBD5E0] text-[#FED7E2] focus:ring-[#FED7E2]"
                                   @checked(in_array($ref, $selectedRefs, true))>
                            <span>
                                <span class="block font-bold text-[#2D3748]">{{ $character->name }}</span>
                                <span class="mt-1 block text-xs font-bold text-[#A0AEC0]">
                                    {{ $character->first_person ? '一人称：' . $character->first_person : '登録情報をプロンプトに反映' }}
                                </span>
                            </span>
                        </label>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[#CBD5E0] bg-white p-5 text-sm font-bold text-[#A0AEC0]">
                            オリジナルキャラクターが未登録です。
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl bg-[#F7FAFC] p-5">
                <div class="flex flex-col justify-between gap-2 md:flex-row md:items-end">
                    <div>
                        <h4 class="text-lg font-bold text-[#2D3748]">作品キャラクター</h4>
                        <p class="mt-1 text-sm font-bold text-[#A0AEC0]">
                            作品名で選んだ作品のキャラクターのみ表示されます。
                        </p>
                    </div>
                    <p id="official-character-count" class="text-sm font-bold text-[#A0AEC0]"></p>
                </div>

                <div id="official-character-empty" class="mt-5 hidden rounded-2xl border border-dashed border-[#CBD5E0] bg-white p-5 text-sm font-bold text-[#A0AEC0]">
                    作品を選択すると、その作品に登録されているキャラクターが表示されます。
                </div>

                <div class="mt-5 max-h-96 space-y-3 overflow-y-auto pr-1">
                    @foreach ($officialCharacters as $character)
                        @php($ref = 'v1_character:' . $character->id)
                        <label class="official-character-option flex cursor-pointer items-start gap-3 rounded-2xl border border-[#E2E8F0] bg-white p-4 hover:bg-[#FFF1F5]"
                               data-work-id="{{ $character->work_id }}">
                            <input type="checkbox"
                                   name="selected_character_refs[]"
                                   value="{{ $ref }}"
                                   class="mt-1 rounded border-[#CBD5E0] text-[#FED7E2] focus:ring-[#FED7E2]"
                                   @checked(in_array($ref, $selectedRefs, true))>
                            <span>
                                <span class="block font-bold text-[#2D3748]">{{ $character->name }}</span>
                                <span class="mt-1 block text-xs font-bold text-[#A0AEC0]">
                                    {{ $character->work?->title ?: '作品未設定' }}
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">STEP 3</p>
            <h3 class="mt-1 text-2xl font-bold text-[#2D3748]">作風・ジャンル</h3>
            <p class="mt-2 text-sm leading-7 text-[#718096]">
                小説の雰囲気を指定します。「その他」を選んだ場合のみ、自由入力欄が使えます。
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="{{ $labelClass }}">作風 <span class="text-red-500">*</span></label>
                <select id="writing-style" name="writing_style" class="{{ $inputClass }}" required>
                    @foreach ($writingStyleLabels as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStyle === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="writing-style-other-wrap">
                <label class="{{ $labelClass }}">作風：その他</label>
                <input type="text"
                       name="writing_style_other"
                       value="{{ old('writing_style_other', $savedPrompt->writing_style_other ?? '') }}"
                       class="{{ $inputClass }}"
                       placeholder="例：童話風、脚本風、三人称文芸調">
            </div>

            <div>
                <label class="{{ $labelClass }}">ジャンル <span class="text-red-500">*</span></label>
                <select id="genre" name="genre" class="{{ $inputClass }}" required>
                    @foreach ($genreLabels as $value => $label)
                        <option value="{{ $value }}" @selected($selectedGenre === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="genre-other-wrap">
                <label class="{{ $labelClass }}">ジャンル：その他</label>
                <input type="text"
                       name="genre_other"
                       value="{{ old('genre_other', $savedPrompt->genre_other ?? '') }}"
                       class="{{ $inputClass }}"
                       placeholder="例：友情、成長、ミステリーラブ">
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">STEP 4</p>
            <h3 class="mt-1 text-2xl font-bold text-[#2D3748]">あらすじ・起承転結</h3>
            <p class="mt-2 text-sm leading-7 text-[#718096]">
                任意入力です。空欄のまま保存した場合は、プロンプト本文では「指定なし」として扱われます。
            </p>
        </div>

        <div class="grid gap-6">
            <div>
                <label class="{{ $labelClass }}">あらすじ</label>
                <textarea name="synopsis"
                          rows="5"
                          class="{{ $textareaClass }}"
                          placeholder="物語全体の流れや場面の前提">{{ old('synopsis', $savedPrompt->synopsis ?? '') }}</textarea>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="{{ $labelClass }}">起</label>
                    <textarea name="plot_opening"
                              rows="5"
                              class="{{ $textareaClass }}"
                              placeholder="物語の始まり">{{ old('plot_opening', $savedPrompt->plot_opening ?? '') }}</textarea>
                </div>

                <div>
                    <label class="{{ $labelClass }}">承</label>
                    <textarea name="plot_development"
                              rows="5"
                              class="{{ $textareaClass }}"
                              placeholder="展開・深まり">{{ old('plot_development', $savedPrompt->plot_development ?? '') }}</textarea>
                </div>

                <div>
                    <label class="{{ $labelClass }}">転</label>
                    <textarea name="plot_turn"
                              rows="5"
                              class="{{ $textareaClass }}"
                              placeholder="変化・山場">{{ old('plot_turn', $savedPrompt->plot_turn ?? '') }}</textarea>
                </div>

                <div>
                    <label class="{{ $labelClass }}">結</label>
                    <textarea name="plot_conclusion"
                              rows="5"
                              class="{{ $textareaClass }}"
                              placeholder="締め・余韻">{{ old('plot_conclusion', $savedPrompt->plot_conclusion ?? '') }}</textarea>
                </div>
            </div>

            <div>
                <label class="{{ $labelClass }}">備考</label>
                <textarea name="notes"
                          rows="5"
                          class="{{ $textareaClass }}"
                          placeholder="避けたい表現、補足、出力条件など">{{ old('notes', $savedPrompt->notes ?? '') }}</textarea>
            </div>
        </div>
    </section>

    <input type="hidden" name="category" value="scene">

    <div class="rounded-3xl bg-[#FFF1F5] px-6 py-5 text-sm font-bold leading-7 text-[#4A5568]">
        保存すると、選択した作品・登場人物・作風・ジャンル・あらすじ・起承転結をもとに、AIへ貼り付けるプロンプト本文が自動生成されます。
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit"
                onclick="setPromptStatus('active')"
                class="rounded-2xl bg-[#FED7E2] px-6 py-3 text-base font-bold text-[#2D3748] shadow-sm hover:opacity-90">
            保存する
        </button>

        <button type="submit"
                onclick="setPromptStatus('draft')"
                class="rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 text-base font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
            下書きで保存
        </button>

        <a href="{{ route('writer.prompts.index') }}" class="rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 text-base font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
            一覧へ戻る
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const workRef = document.getElementById('work-ref');
        const officialOptions = Array.from(document.querySelectorAll('.official-character-option'));
        const officialEmpty = document.getElementById('official-character-empty');
        const officialCount = document.getElementById('official-character-count');

        const writingStyle = document.getElementById('writing-style');
        const writingStyleOtherWrap = document.getElementById('writing-style-other-wrap');

        const genre = document.getElementById('genre');
        const genreOtherWrap = document.getElementById('genre-other-wrap');

        function selectedWorkId() {
            if (!workRef || !workRef.value.startsWith('work:')) {
                return null;
            }

            return workRef.value.replace('work:', '');
        }

        function updateOfficialCharacters() {
            const currentWorkId = selectedWorkId();
            let visibleCount = 0;

            officialOptions.forEach((option) => {
                const checkbox = option.querySelector('input[type="checkbox"]');
                const shouldShow = currentWorkId !== null && option.dataset.workId === currentWorkId;

                option.classList.toggle('hidden', !shouldShow);

                if (!shouldShow && checkbox && !checkbox.checked) {
                    checkbox.checked = false;
                }

                if (shouldShow) {
                    visibleCount += 1;
                }
            });

            if (officialEmpty) {
                officialEmpty.classList.toggle('hidden', visibleCount > 0);
            }

            if (officialCount) {
                officialCount.textContent = visibleCount > 0 ? `${visibleCount}件表示中` : '';
            }
        }

        function updateOtherFields() {
            if (writingStyleOtherWrap && writingStyle) {
                writingStyleOtherWrap.classList.toggle('hidden', writingStyle.value !== 'other');
            }

            if (genreOtherWrap && genre) {
                genreOtherWrap.classList.toggle('hidden', genre.value !== 'other');
            }
        }

        window.setPromptStatus = function (status) {
            const statusSelect = document.querySelector('select[name="status"]');

            if (statusSelect) {
                statusSelect.value = status;
            }
        };

        workRef?.addEventListener('change', updateOfficialCharacters);
        writingStyle?.addEventListener('change', updateOtherFields);
        genre?.addEventListener('change', updateOtherFields);

        updateOfficialCharacters();
        updateOtherFields();
    });
</script>
