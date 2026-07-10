@php
    $prompt = $prompt ?? $savedPrompt ?? null;

    $oldValue = function (string $key, $default = '') use ($prompt) {
        return old($key, $prompt?->{$key} ?? $default);
    };

    $characters = $characters
        ?? $originalCharacters
        ?? $characterItems
        ?? collect();

    $selectedCharacterRefs = old('selected_character_refs', $prompt?->selected_character_refs ?? []);

    if (! is_array($selectedCharacterRefs)) {
        $selectedCharacterRefs = [];
    }
    $workRef = 'original';

    $writingStyle = old('writing_style', $prompt?->writing_style ?? '');
    $genre = old('genre', $prompt?->genre ?? '');
    $status = old('status', $prompt?->status ?? 'active');

    $writingStyleLabels = \App\Models\SavedPrompt::writingStyleLabels();
    $genreLabels = \App\Models\SavedPrompt::genreLabels();

    $includeTimeline = (bool) old('include_relationship_timeline', $prompt?->include_relationship_timeline ?? false);
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
            プロンプトの管理名と用途を設定します。
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
        <input type="hidden" name="work_ref" id="work_ref" value="original">

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
            プロンプトに反映する登場人物を選択します。自分で登録したオリジナルキャラクターを選択します。
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
        const writingStyleSelect = document.getElementById('writing_style');
        const writingStyleOtherWrap = document.getElementById('writing-style-other-wrap');
        const genreSelect = document.getElementById('genre');
        const genreOtherWrap = document.getElementById('genre-other-wrap');
        const previewButton = document.getElementById('preview-button');
        const previewCopyButton = document.getElementById('preview-copy-button');
        const previewTextarea = document.getElementById('prompt-preview');
        const previewMessage = document.getElementById('preview-message');
        const form = document.getElementById('saved-prompt-form');

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
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
        writingStyleSelect?.addEventListener('change', refreshOtherFields);
        genreSelect?.addEventListener('change', refreshOtherFields);
        previewButton?.addEventListener('click', generatePreview);
        previewCopyButton?.addEventListener('click', copyPreview);
        refreshOtherFields();
    });
</script>
