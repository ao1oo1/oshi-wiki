@php
    $prompt = $prompt ?? $savedPrompt ?? null;

    $oldValue = function (string $key, $default = '') use ($prompt) {
        return old($key, $prompt?->{$key} ?? $default);
    };

    $works = $works ?? collect();

    $characters = $characters
        ?? $originalCharacters
        ?? $characterItems
        ?? collect();

    $officialCharacters = $officialCharacters ?? collect();

    $selectedCharacterRefs = old('selected_character_refs', $prompt?->selected_character_refs ?? []);

    if (! is_array($selectedCharacterRefs)) {
        $selectedCharacterRefs = [];
    }

    $workRef = old('work_ref');

    if (! $workRef && $prompt) {
        if (($prompt->work_source ?? null) === 'v1_work' && $prompt->work_id) {
            $workRef = 'work:' . $prompt->work_id;
        } else {
            $workRef = 'original';
        }
    }

    $workRef = $workRef ?: 'original';

    $writingStyle = old('writing_style', $prompt?->writing_style ?? '');
    $genre = old('genre', $prompt?->genre ?? '');
    $status = old('status', $prompt?->status ?? 'active');

    $writingStyleLabels = \App\Models\SavedPrompt::writingStyleLabels();
    $genreLabels = \App\Models\SavedPrompt::genreLabels();

    $includeTimeline = (bool) old('include_relationship_timeline', $prompt?->include_relationship_timeline ?? false);

    $officialCharacterPayload = $officialCharacters
        ->map(function ($character) {
            $workId = $character->work_id ?? $character->work?->id ?? null;

            return [
                'id' => $character->id,
                'name' => $character->name,
                'work_id' => $workId ? (string) $workId : '',
                'work_title' => $character->work?->title ?? '作品未設定',
                'ref' => 'v1_character:' . $character->id,
            ];
        })
        ->filter(fn ($character) => $character['work_id'] !== '')
        ->values();
@endphp

@if ($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-sm font-bold text-red-600">
        <p>入力内容を確認してください。</p>
        <ul class="mt-3 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<input type="hidden" name="status" id="prompt-status-input" value="{{ $status }}">

<section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="mb-6">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 1</p>
        <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">基本情報</h2>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            プロンプトの管理名と、対象にする作品を設定します。
        </p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="title">タイトル <span class="text-red-500">必須</span></label>
            <input id="title"
                   type="text"
                   name="title"
                   value="{{ $oldValue('title') }}"
                   placeholder="例：日常シーン用プロンプト"
                   required>
        </div>

        <div>
            <label for="work_ref">作品</label>
            <select id="work_ref" name="work_ref">
                <option value="original" @selected($workRef === 'original')>オリジナル</option>

                @foreach ($works as $work)
                    <option value="work:{{ $work->id }}" @selected($workRef === 'work:' . $work->id)>
                        {{ $work->title }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label for="purpose">用途・目的</label>
            <textarea id="purpose"
                      name="purpose"
                      placeholder="例：キャラクター同士の日常会話を書くためのプロンプト">{{ $oldValue('purpose') }}</textarea>
        </div>
    </div>
</section>

<section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="mb-6">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 2</p>
        <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">登場人物</h2>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            プロンプトに反映する登場人物を選択します。作品キャラクターは、選択した作品に応じて表示されます。
        </p>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl bg-[#F7FAFC] p-5">
            <h3 class="font-bold text-[#2D3748]">オリジナルキャラクター</h3>
            <p class="mt-2 text-sm font-bold leading-7 text-[#A0AEC0]">
                自分で登録したキャラクターです。
            </p>

            <div class="mt-4 max-h-[360px] space-y-3 overflow-y-auto pr-2">
                @forelse ($characters as $character)
                    @php
                        $ref = 'original:' . $character->id;
                    @endphp

                    <label class="flex items-start gap-3 rounded-2xl bg-white p-4">
                        <input type="checkbox"
                               name="selected_character_refs[]"
                               value="{{ $ref }}"
                               class="mt-1"
                               @checked(in_array($ref, $selectedCharacterRefs, true))>
                        <span>
                            <span class="block font-bold text-[#2D3748]">{{ $character->name }}</span>
                            @if ($character->affiliation || $character->age)
                                <span class="mt-1 block text-xs font-bold text-[#A0AEC0]">
                                    {{ collect([$character->age, $character->affiliation])->filter()->implode(' / ') }}
                                </span>
                            @endif
                        </span>
                    </label>
                @empty
                    <div class="rounded-2xl bg-white p-5 text-sm font-bold text-[#A0AEC0]">
                        オリジナルキャラクターがまだ登録されていません。
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl bg-[#F7FAFC] p-5">
            <h3 class="font-bold text-[#2D3748]">作品キャラクター</h3>
            <p class="mt-2 text-sm font-bold leading-7 text-[#A0AEC0]">
                選択した作品に登録されているキャラクターだけを表示します。
            </p>

            <div class="mt-4 rounded-2xl bg-white p-4 text-sm font-bold text-[#A0AEC0]" id="official-character-empty-message">
                作品を選択すると、登録済みキャラクターが表示されます。
            </div>

            <div class="mt-4 max-h-[360px] space-y-3 overflow-y-auto pr-2" id="official-character-list"></div>

            <script type="application/json" id="official-character-payload">
                {!! json_encode($officialCharacterPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
            </script>
        </div>
    </div>

    <div class="mt-6 rounded-2xl bg-[#FFF1F5] p-5">
        <label class="flex items-start gap-3">
            <input type="checkbox"
                   name="include_relationship_timeline"
                   value="1"
                   class="mt-1"
                   @checked($includeTimeline)>
            <span>
                <span class="block font-bold text-[#2D3748]">関係性の年表データもプロンプトに反映する</span>
                <span class="mt-1 block text-sm font-bold leading-7 text-[#718096]">
                    チェックすると、選択した登場人物同士に登録されている年表データもプロンプト本文に含めます。
                </span>
            </span>
        </label>
    </div>
</section>

<section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="mb-6">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 3</p>
        <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">作風・ジャンル</h2>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            AIに依頼したい文章の雰囲気を設定します。
        </p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="writing_style">作風</label>
            <select id="writing_style" name="writing_style">
                <option value="">指定なし</option>
                @foreach ($writingStyleLabels as $key => $label)
                    <option value="{{ $key }}" @selected($writingStyle === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div id="writing-style-other-wrap" style="display: {{ $writingStyle === 'other' ? 'block' : 'none' }};">
            <label for="writing_style_other">作風 その他</label>
            <input id="writing_style_other"
                   type="text"
                   name="writing_style_other"
                   value="{{ $oldValue('writing_style_other') }}"
                   placeholder="例：切ない純文学風">
        </div>

        <div>
            <label for="genre">ジャンル</label>
            <select id="genre" name="genre">
                <option value="">指定なし</option>
                @foreach ($genreLabels as $key => $label)
                    <option value="{{ $key }}" @selected($genre === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div id="genre-other-wrap" style="display: {{ $genre === 'other' ? 'block' : 'none' }};">
            <label for="genre_other">ジャンル その他</label>
            <input id="genre_other"
                   type="text"
                   name="genre_other"
                   value="{{ $oldValue('genre_other') }}"
                   placeholder="例：主従関係、成長物語など">
        </div>
    </div>
</section>

<section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="mb-6">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 4</p>
        <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">あらすじ・構成</h2>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            書きたい内容や流れを入力します。起承転結は空欄でも保存できます。
        </p>
    </div>

    <div class="space-y-5">
        <div>
            <label for="synopsis">あらすじ</label>
            <textarea id="synopsis"
                      name="synopsis"
                      placeholder="どんな話を書きたいかを入力してください">{{ $oldValue('synopsis') }}</textarea>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="plot_opening">起</label>
                <textarea id="plot_opening"
                          name="plot_opening"
                          placeholder="導入・出会い・始まり">{{ $oldValue('plot_opening') }}</textarea>
            </div>

            <div>
                <label for="plot_development">承</label>
                <textarea id="plot_development"
                          name="plot_development"
                          placeholder="関係の進展・出来事">{{ $oldValue('plot_development') }}</textarea>
            </div>

            <div>
                <label for="plot_turn">転</label>
                <textarea id="plot_turn"
                          name="plot_turn"
                          placeholder="転機・衝突・変化">{{ $oldValue('plot_turn') }}</textarea>
            </div>

            <div>
                <label for="plot_conclusion">結</label>
                <textarea id="plot_conclusion"
                          name="plot_conclusion"
                          placeholder="結末・余韻">{{ $oldValue('plot_conclusion') }}</textarea>
            </div>
        </div>

        <div>
            <label for="notes">備考</label>
            <textarea id="notes"
                      name="notes"
                      placeholder="AIに追加で守ってほしいこと、NG、補足など">{{ $oldValue('notes') }}</textarea>
        </div>
    </div>
</section>

<section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="mb-6">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 5</p>
        <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">プレビュー</h2>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            保存前に、実際に生成されるプロンプト本文を確認できます。
        </p>
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="button"
                id="preview-button"
                class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90">
            プレビュー生成
        </button>

        <button type="button"
                id="preview-copy-button"
                class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-5 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
            プレビューをコピー
        </button>
    </div>

    <p id="preview-message" class="mt-4 hidden rounded-2xl bg-[#FFF1F5] px-5 py-3 text-sm font-bold text-[#2D3748]"></p>

    <textarea id="prompt-preview"
              readonly
              class="mt-5 min-h-[680px] w-full rounded-2xl border-[#CBD5E0] bg-[#F7FAFC] p-5 font-mono text-sm leading-7 text-[#2D3748]"></textarea>
</section>

<div class="flex flex-col gap-3 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
    <p class="text-sm font-bold text-[#718096]">
        下書き保存もできます。完成したら「作成・保存する」で有効状態にしてください。
    </p>

    <div class="flex flex-col gap-3 md:flex-row">
        <a href="{{ route('writer.prompts.index') }}"
           class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
            一覧へ戻る
        </a>

        <button type="submit"
                onclick="document.getElementById('prompt-status-input').value='draft';"
                class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
            下書きにする
        </button>

        <button type="submit"
                onclick="document.getElementById('prompt-status-input').value='active';"
                class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
            作成・保存する
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const workSelect = document.getElementById('work_ref');
        const officialCharacterList = document.getElementById('official-character-list');
        const officialCharacterPayloadElement = document.getElementById('official-character-payload');
        const writingStyleSelect = document.getElementById('writing_style');
        const writingStyleOtherWrap = document.getElementById('writing-style-other-wrap');
        const genreSelect = document.getElementById('genre');
        const genreOtherWrap = document.getElementById('genre-other-wrap');
        const previewButton = document.getElementById('preview-button');
        const previewCopyButton = document.getElementById('preview-copy-button');
        const previewTextarea = document.getElementById('prompt-preview');
        const previewMessage = document.getElementById('preview-message');
        const form = document.getElementById('saved-prompt-form');

        function selectedWorkId() {
            if (!workSelect || !workSelect.value.startsWith('work:')) {
                return '';
            }

            return workSelect.value.replace('work:', '');
        }

        function officialCharacters() {
            if (!officialCharacterPayloadElement) {
                return [];
            }

            try {
                return JSON.parse(officialCharacterPayloadElement.textContent || '[]');
            } catch (error) {
                return [];
            }
        }

        function selectedOfficialRefs() {
            return Array.from(document.querySelectorAll('#official-character-list input[name="selected_character_refs[]"]:checked'))
                .map((input) => input.value);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function refreshOfficialCharacters() {
            const currentWorkId = selectedWorkId();
            const emptyMessage = document.getElementById('official-character-empty-message');

            if (!officialCharacterList) {
                return;
            }

            const previouslySelectedRefs = selectedOfficialRefs();

            officialCharacterList.innerHTML = '';

            if (currentWorkId === '') {
                if (emptyMessage) {
                    emptyMessage.textContent = '作品を選択すると、登録済みキャラクターが表示されます。';
                    emptyMessage.classList.remove('hidden');
                }

                return;
            }

            const characters = officialCharacters()
                .filter((character) => String(character.work_id) === String(currentWorkId));

            if (characters.length === 0) {
                if (emptyMessage) {
                    emptyMessage.textContent = 'この作品に登録されているキャラクターはありません。';
                    emptyMessage.classList.remove('hidden');
                }

                return;
            }

            if (emptyMessage) {
                emptyMessage.classList.add('hidden');
            }

            characters.forEach((character) => {
                const label = document.createElement('label');
                label.className = 'flex items-start gap-3 rounded-2xl bg-white p-4';

                const checked = previouslySelectedRefs.includes(character.ref) ? 'checked' : '';

                label.innerHTML = `
                    <input type="checkbox"
                           name="selected_character_refs[]"
                           value="${escapeHtml(character.ref)}"
                           class="mt-1 official-character-checkbox"
                           ${checked}>
                    <span>
                        <span class="block font-bold text-[#2D3748]">${escapeHtml(character.name)}</span>
                        <span class="mt-1 block text-xs font-bold text-[#A0AEC0]">${escapeHtml(character.work_title)}</span>
                    </span>
                `;

                officialCharacterList.appendChild(label);
            });
        }

        function refreshOtherFields() {
            if (writingStyleOtherWrap && writingStyleSelect) {
                writingStyleOtherWrap.style.display = writingStyleSelect.value === 'other' ? 'block' : 'none';
            }

            if (genreOtherWrap && genreSelect) {
                genreOtherWrap.style.display = genreSelect.value === 'other' ? 'block' : 'none';
            }
        }

        function showPreviewMessage(message) {
            if (!previewMessage) {
                return;
            }

            previewMessage.textContent = message;
            previewMessage.classList.remove('hidden');

            setTimeout(() => {
                previewMessage.classList.add('hidden');
            }, 2500);
        }

        async function generatePreview() {
            if (!form || !previewTextarea) {
                return;
            }

            const formData = new FormData(form);
            formData.delete('_method');

            previewButton.disabled = true;
            previewButton.classList.add('opacity-50');

            try {
                const response = await fetch('{{ route('writer.prompts.preview') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    previewTextarea.value = '';
                    showPreviewMessage(data.message || 'プレビュー生成に失敗しました。入力内容を確認してください。');
                    return;
                }

                previewTextarea.value = data.prompt_body || '';
                showPreviewMessage(`プレビューを生成しました。${data.length || previewTextarea.value.length}文字`);
            } catch (error) {
                previewTextarea.value = '';
                showPreviewMessage('プレビュー生成に失敗しました。');
            } finally {
                previewButton.disabled = false;
                previewButton.classList.remove('opacity-50');
            }
        }

        async function copyPreview() {
            if (!previewTextarea || !previewTextarea.value) {
                showPreviewMessage('コピーするプレビューがありません。');
                return;
            }

            try {
                await navigator.clipboard.writeText(previewTextarea.value);
            } catch (error) {
                previewTextarea.focus();
                previewTextarea.select();
                document.execCommand('copy');
            }

            showPreviewMessage('プレビューをコピーしました。');
        }

        workSelect?.addEventListener('change', refreshOfficialCharacters);
        writingStyleSelect?.addEventListener('change', refreshOtherFields);
        genreSelect?.addEventListener('change', refreshOtherFields);
        previewButton?.addEventListener('click', generatePreview);
        previewCopyButton?.addEventListener('click', copyPreview);

        refreshOfficialCharacters();
        refreshOtherFields();
    });
</script>
