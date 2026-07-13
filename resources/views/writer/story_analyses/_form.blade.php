@php
    $isEdit = isset($analysis) && $analysis;

    $selectedStoryIds = array_map(
        'intval',
        old(
            'story_ids',
            $generated['story_ids']
                ?? ($analysis?->selected_story_ids ?? [])
        )
    );

    $titleValue = old(
        'title',
        $generated['title']
            ?? ($analysis?->title ?? '')
    );

    $notesValue = old(
        'analysis_notes',
        $generated['analysis_notes']
            ?? ($analysis?->analysis_notes ?? '')
    );

    $promptValue = old(
        'analysis_prompt',
        $generated['analysis_prompt']
            ?? ($analysis?->analysis_prompt ?? '')
    );

    $resultValue = old(
        'analysis_result',
        $generated['analysis_result']
            ?? ($analysis?->analysis_result ?? '')
    );
@endphp

<div class="space-y-8">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <label for="title">
            管理名
            <span class="text-red-500">必須</span>
        </label>

        <input
            id="title"
            type="text"
            name="title"
            value="{{ $titleValue }}"
            maxlength="255"
            required
            placeholder="例：恋愛短編の文体分析"
        >
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <h2 class="text-2xl font-bold text-[#2D3748]">
            分析するストーリー
        </h2>

        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            ストーリー管理に登録した文章から、分析対象を選択してください。
        </p>

        @if ($stories->isEmpty())
            <div class="mt-6 rounded-2xl bg-amber-50 p-5">
                <p class="font-bold text-amber-800">
                    分析できるストーリーがありません。先にストーリーを登録してください。
                </p>

                <a
                    href="{{ route('writer.stories.create') }}"
                    class="mt-4 inline-flex rounded-2xl bg-[#FED7E2] px-5 py-3 font-bold text-[#2D3748]"
                >
                    ストーリーを登録する
                </a>
            </div>
        @else
            <div class="mt-6 flex flex-wrap gap-3">
                <button
                    type="button"
                    id="select-all-stories"
                    class="rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748]"
                >
                    すべて選択
                </button>

                <button
                    type="button"
                    id="clear-all-stories"
                    class="rounded-2xl border border-[#CBD5E0] bg-white px-5 py-3 text-sm font-bold text-[#2D3748]"
                >
                    すべて解除
                </button>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach ($stories as $story)
                    <label class="flex cursor-pointer items-start gap-4 rounded-3xl border border-[#E2E8F0] p-5 hover:border-[#FED7E2] hover:bg-[#FFF7FA]">
                        <input
                            type="checkbox"
                            name="story_ids[]"
                            value="{{ $story->id }}"
                            class="story-analysis-checkbox mt-1 h-5 w-5 rounded border-[#CBD5E0]"
                            @checked(in_array(
                                (int) $story->id,
                                $selectedStoryIds,
                                true
                            ))
                        >

                        <span class="min-w-0 flex-1">
                            <span class="font-bold text-[#2D3748]">
                                @if ($story->episode_number)
                                    第{{ number_format($story->episode_number) }}話：
                                @endif

                                {{ $story->title }}
                            </span>

                            <span class="mt-2 block text-xs font-bold text-[#A0AEC0]">
                                {{ number_format($story->bodyLength()) }}文字
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
        @endif
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <label for="analysis_notes">
            追加で分析してほしい内容
        </label>

        <textarea
            id="analysis_notes"
            name="analysis_notes"
            rows="7"
            maxlength="5000"
            placeholder="例：会話文のテンポや心理描写を詳しく分析してください。"
        >{{ $notesValue }}</textarea>

        @unless ($isEdit)
            <div class="mt-5">
                <button
                    type="submit"
                    formaction="{{ route('writer.story-analyses.generate-prompt') }}"
                    formmethod="POST"
                    class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-4 text-lg font-bold text-[#2D3748]"
                >
                    分析用プロンプトを作成する
                </button>
            </div>
        @endunless
    </section>

    @if ($promptValue !== '')
        <section class="rounded-3xl border border-[#FED7E2] bg-white p-6 shadow-sm md:p-8">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#2D3748]">
                        分析用プロンプト
                    </h2>

                    <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                        内容をコピーして、利用するAIへ貼り付けてください。
                    </p>
                </div>

                <button
                    type="button"
                    id="copy-analysis-prompt"
                    class="rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748]"
                >
                    プロンプトをコピー
                </button>
            </div>

            <textarea
                id="analysis_prompt"
                name="analysis_prompt"
                rows="30"
                required
            >{{ $promptValue }}</textarea>

            <p
                id="analysis-copy-message"
                class="mt-4 hidden rounded-2xl bg-green-50 p-4 text-sm font-bold text-green-700"
            >
                プロンプトをコピーしました。
            </p>
        </section>

        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
            <h2 class="text-2xl font-bold text-[#2D3748]">
                分析結果
            </h2>

            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                AIの実行結果は保存後の詳細画面でも入力できます。最大10,000文字です。
            </p>

            <textarea
                id="analysis_result"
                name="analysis_result"
                rows="24"
                maxlength="{{ $analysisResultMax ?? 10000 }}"
                placeholder="AIから返された分析結果を貼り付けてください。"
            >{{ $resultValue }}</textarea>

            <p class="mt-2 text-right text-xs font-bold text-[#A0AEC0]">
                <span id="analysis-result-count">
                    {{ number_format(mb_strlen($resultValue)) }}
                </span>
                /
                {{ number_format($analysisResultMax ?? 10000) }}文字
            </p>
        </section>

        <div class="flex flex-col gap-3 md:flex-row">
            <button
                type="submit"
                class="inline-flex flex-1 items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-4 text-lg font-bold text-[#2D3748]"
            >
                {{ $isEdit ? '変更を保存' : '文体分析を保存' }}
            </button>

            <a
                href="{{ route('writer.story-analyses.index') }}"
                class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-4 font-bold text-[#2D3748]"
            >
                一覧へ戻る
            </a>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = Array.from(
        document.querySelectorAll('.story-analysis-checkbox')
    );

    document.getElementById('select-all-stories')
        ?.addEventListener('click', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = true;
            });
        });

    document.getElementById('clear-all-stories')
        ?.addEventListener('click', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = false;
            });
        });

    const copyButton = document.getElementById(
        'copy-analysis-prompt'
    );

    const prompt = document.getElementById('analysis_prompt');
    const message = document.getElementById(
        'analysis-copy-message'
    );

    copyButton?.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(prompt.value);
        } catch (error) {
            prompt.focus();
            prompt.select();
            document.execCommand('copy');
        }

        message?.classList.remove('hidden');

        window.setTimeout(() => {
            message?.classList.add('hidden');
        }, 3000);
    });

    const result = document.getElementById('analysis_result');
    const resultCount = document.getElementById(
        'analysis-result-count'
    );

    const updateResultCount = () => {
        if (result && resultCount) {
            resultCount.textContent =
                result.value.length.toLocaleString();
        }
    };

    result?.addEventListener('input', updateResultCount);
    updateResultCount();
});
</script>
