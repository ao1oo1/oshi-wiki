@include('writer.original_characters._layout_start', ['title' => 'ストーリー分析プロンプト'])

@php
    $selectedStoryIds = array_map(
        'intval',
        $selectedStoryIds ?? old('story_ids', [])
    );

    $analysisPrompt = $analysisPrompt ?? null;
    $analysisNotes = old('analysis_notes', $analysisNotes ?? '');

    $selectedStories = $stories->filter(
        fn ($story) => in_array(
            (int) $story->id,
            $selectedStoryIds,
            true
        )
    );

    $selectedBodyLength = $selectedStories->sum(
        fn ($story) => $story->bodyLength()
    );
@endphp

<div class="writer-form-ui">
    <div class="mb-8">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-[#2D3748]">
                    ストーリー分析プロンプト
                </h1>

                <p class="mt-3 text-sm font-bold leading-7 text-[#A0AEC0]">
                    登録済みストーリーから、文体・構成・会話・描写傾向を分析するためのプロンプトを作成します。
                </p>
            </div>

            <a
                href="{{ route('writer.stories.index') }}"
                class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]"
            >
                ストーリー一覧へ戻る
            </a>
        </div>
    </div>

    @if ($stories->isEmpty())
        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-10 text-center shadow-sm">
            <p class="text-2xl font-bold text-[#2D3748]">
                分析できるストーリーがありません。
            </p>

            <p class="mt-3 text-sm font-bold leading-7 text-[#A0AEC0]">
                先にストーリーを1件以上登録してください。
            </p>

            <div class="mt-6">
                <a
                    href="{{ route('writer.stories.create') }}"
                    class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90"
                >
                    ストーリーを登録する
                </a>
            </div>
        </section>
    @else
        <form
            method="POST"
            action="{{ route('writer.stories.analysis.generate') }}"
            class="space-y-8"
        >
            @csrf

            <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
                <div class="mb-6">
                    <p class="text-sm font-bold text-[#A0AEC0]">
                        STEP 1
                    </p>

                    <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                        分析するストーリーを選択
                    </h2>

                    <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                        文体の特徴を正確に分析するには、同じ作風で書いた複数のストーリーを選ぶのがおすすめです。
                    </p>
                </div>

                <div class="mb-5 flex flex-col gap-3 md:flex-row">
                    <button
                        type="button"
                        id="select-all-stories"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90"
                    >
                        すべて選択
                    </button>

                    <button
                        type="button"
                        id="clear-all-stories"
                        class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-5 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]"
                    >
                        すべて解除
                    </button>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($stories as $story)
                        <label class="story-analysis-choice flex cursor-pointer items-start gap-4 rounded-3xl border border-[#E2E8F0] bg-white p-5 transition hover:border-[#FED7E2] hover:bg-[#FFF7FA]">
                            <input
                                type="checkbox"
                                name="story_ids[]"
                                value="{{ $story->id }}"
                                class="story-analysis-checkbox mt-1 h-5 w-5 shrink-0 rounded border-[#CBD5E0] text-[#FED7E2] focus:ring-[#FED7E2]"
                                @checked(in_array(
                                    (int) $story->id,
                                    $selectedStoryIds,
                                    true
                                ))
                            >

                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-center gap-2">
                                    @if ($story->episode_number)
                                        <span class="rounded-full bg-[#FED7E2] px-3 py-1 text-xs font-bold text-[#2D3748]">
                                            第{{ number_format($story->episode_number) }}話
                                        </span>
                                    @endif

                                    <span class="rounded-full bg-[#F7FAFC] px-3 py-1 text-xs font-bold text-[#4A5568]">
                                        {{ $story->statusLabel() }}
                                    </span>
                                </span>

                                <span class="mt-3 block break-words text-lg font-bold text-[#2D3748]">
                                    {{ $story->title }}
                                </span>

                                <span class="mt-2 block text-xs font-bold text-[#A0AEC0]">
                                    {{ number_format($story->bodyLength()) }}文字
                                </span>

                                @if ($story->memo)
                                    <span class="mt-3 block line-clamp-3 text-sm font-bold leading-6 text-[#718096]">
                                        {{ $story->memo }}
                                    </span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="mt-5 rounded-2xl bg-[#F7FAFC] p-4">
                    <p class="text-sm font-bold text-[#718096]">
                        選択中：
                        <span id="selected-story-count">
                            {{ number_format(count($selectedStoryIds)) }}
                        </span>
                        件
                    </p>

                    <p class="mt-2 text-sm font-bold text-[#718096]">
                        選択本文の合計：
                        <span id="selected-story-length">
                            {{ number_format($selectedBodyLength) }}
                        </span>
                        文字
                    </p>
                </div>
            </section>

            <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
                <div class="mb-6">
                    <p class="text-sm font-bold text-[#A0AEC0]">
                        STEP 2
                    </p>

                    <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                        追加で分析してほしい内容
                    </h2>

                    <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                        特に重視してほしい点がある場合だけ入力してください。空欄でも生成できます。
                    </p>
                </div>

                <textarea
                    id="analysis_notes"
                    name="analysis_notes"
                    rows="8"
                    placeholder="例：会話文のテンポ、恋愛描写、心理描写を特に詳しく分析してください。"
                >{{ $analysisNotes }}</textarea>
            </section>

            <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-4 text-lg font-bold text-[#2D3748] hover:opacity-90"
                >
                    分析用プロンプトを作成する
                </button>
            </div>
        </form>

        @if ($analysisPrompt)
            <section
                id="analysis-result"
                class="mt-8 rounded-3xl border border-[#FED7E2] bg-white p-6 shadow-sm md:p-8"
            >
                <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <p class="text-sm font-bold text-[#A0AEC0]">
                            生成結果
                        </p>

                        <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                            AIへ貼り付ける分析用プロンプト
                        </h2>

                        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                            下の内容をコピーして、利用するAIのチャット画面へ貼り付けてください。
                        </p>
                    </div>

                    <button
                        type="button"
                        id="copy-analysis-prompt"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90"
                    >
                        プロンプトをコピー
                    </button>
                </div>

                <textarea
                    id="analysis-prompt-text"
                    readonly
                    style="min-height:720px;"
                >{{ $analysisPrompt }}</textarea>

                <div class="mt-4 rounded-2xl bg-[#F7FAFC] p-4">
                    <p class="text-sm font-bold text-[#718096]">
                        プロンプト文字数：
                        {{ number_format(mb_strlen($analysisPrompt)) }}文字
                    </p>
                </div>

                <p
                    id="analysis-copy-message"
                    class="mt-4 hidden rounded-2xl bg-green-50 px-5 py-4 text-sm font-bold text-green-700"
                >
                    プロンプトをコピーしました。
                </p>
            </section>

            <section class="mt-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
                <div class="mb-6">
                    <p class="text-sm font-bold text-[#A0AEC0]">
                        AIの回答を保存
                    </p>

                    <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                        文体分析の結論を保存する
                    </h2>

                    <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                        上で生成したプロンプトをAIへ貼り付け、
                        AIが返した文体・構成・会話・描写傾向などの分析結果を
                        下の欄へ貼り付けて保存してください。
                    </p>

                    <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                        保存した結果は、選択したストーリーの組み合わせと一緒に記録されます。
                        後から元のストーリーを編集しても、保存時点のタイトルと話数を確認できます。
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('writer.stories.analysis-results.store') }}"
                    class="space-y-5"
                >
                    @csrf

                    @foreach ($selectedStoryIds as $storyId)
                        <input
                            type="hidden"
                            name="story_ids[]"
                            value="{{ $storyId }}"
                        >
                    @endforeach

                    <textarea
                        name="analysis_notes"
                        class="hidden"
                    >{{ $analysisNotes }}</textarea>

                    <div>
                        <label for="analysis_title">
                            分析結果の管理名
                        </label>

                        <input
                            id="analysis_title"
                            type="text"
                            name="analysis_title"
                            value="{{ old('analysis_title') }}"
                            placeholder="例：会話中心の文体分析、恋愛短編の作風分析"
                        >

                        <p class="mt-2 text-xs font-bold leading-6 text-[#A0AEC0]">
                            空欄の場合は、保存日時から自動で名前を付けます。
                        </p>
                    </div>

                    <div>
                        <label for="analysis_result">
                            AIが出した文体分析の結論
                            <span class="text-red-500">必須</span>
                        </label>

                        <textarea
                            id="analysis_result"
                            name="analysis_result"
                            rows="24"
                            required
                            placeholder="AIが回答した文体分析の全文を、ここへ貼り付けてください。"
                            style="min-height:520px;"
                        >{{ old('analysis_result') }}</textarea>

                        <div class="mt-3 flex flex-col gap-2 text-xs font-bold text-[#A0AEC0] md:flex-row md:items-center md:justify-between">
                            <p>
                                AIの回答は編集せず、そのまま保存できます。
                            </p>

                            <p>
                                文字数：
                                <span id="analysis-result-count">0</span>
                                文字
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-[#FFF7FA] p-5">
                        <p class="text-sm font-bold text-[#2D3748]">
                            保存対象
                        </p>

                        <ul class="mt-3 space-y-2 text-sm font-bold leading-7 text-[#718096]">
                            @foreach ($selectedStories as $story)
                                <li>
                                    ・
                                    @if ($story->episode_number)
                                        第{{ number_format($story->episode_number) }}話：
                                    @endif
                                    {{ $story->title }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-4 text-lg font-bold text-[#2D3748] hover:opacity-90"
                    >
                        文体分析の結論を保存する
                    </button>
                </form>
            </section>
        @endif
    @endif

    @if (isset($savedAnalyses))
        <section class="mt-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
            <div class="mb-6">
                <p class="text-sm font-bold text-[#A0AEC0]">
                    保存履歴
                </p>

                <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                    保存した文体分析
                </h2>

                <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                    過去にAIが出した文体分析の結論を確認できます。
                    分析対象として選択したストーリーも一緒に保存されています。
                </p>
            </div>

            <div class="space-y-5">
                @forelse ($savedAnalyses as $savedAnalysis)
                    <article class="rounded-3xl border border-[#E2E8F0] bg-[#F7FAFC] p-5 md:p-6">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-[#2D3748]">
                                    {{ $savedAnalysis->title }}
                                </h3>

                                <p class="mt-2 text-xs font-bold text-[#A0AEC0]">
                                    保存日：
                                    {{ $savedAnalysis->created_at?->format('Y/m/d H:i') }}
                                    ／
                                    {{ number_format($savedAnalysis->storyCount()) }}作品
                                    ／
                                    {{ number_format($savedAnalysis->resultLength()) }}文字
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl bg-white p-5">
                            <p class="text-xs font-bold text-[#A0AEC0]">
                                分析対象
                            </p>

                            <ul class="mt-3 space-y-2 text-sm font-bold leading-7 text-[#4A5568]">
                                @foreach (($savedAnalysis->story_snapshot ?? []) as $snapshot)
                                    <li>
                                        ・
                                        @if (! empty($snapshot['episode_number']))
                                            第{{ number_format($snapshot['episode_number']) }}話：
                                        @endif
                                        {{ $snapshot['title'] ?? 'タイトル不明' }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        @if ($savedAnalysis->analysis_notes)
                            <div class="mt-4 rounded-2xl bg-white p-5">
                                <p class="text-xs font-bold text-[#A0AEC0]">
                                    分析時の追加指示
                                </p>

                                <p class="mt-3 whitespace-pre-wrap text-sm font-bold leading-7 text-[#4A5568]">
                                    {{ $savedAnalysis->analysis_notes }}
                                </p>
                            </div>
                        @endif

                        <div class="mt-4 rounded-2xl bg-white p-5">
                            <p class="text-xs font-bold text-[#A0AEC0]">
                                AIが出した文体分析の結論
                            </p>

                            <div class="mt-3 whitespace-pre-wrap break-words text-sm font-medium leading-8 text-[#2D3748]">
                                {{ $savedAnalysis->analysis_result }}
                            </div>
                        </div>

                        <details class="mt-4 rounded-2xl bg-white p-5">
                            <summary class="cursor-pointer font-bold text-[#2D3748]">
                                使用した分析用プロンプトを確認
                            </summary>

                            <div class="mt-4 whitespace-pre-wrap break-words font-mono text-xs leading-7 text-[#4A5568]">
                                {{ $savedAnalysis->analysis_prompt }}
                            </div>
                        </details>
                    </article>
                @empty
                    <div class="rounded-2xl bg-[#F7FAFC] p-8 text-center">
                        <p class="font-bold text-[#2D3748]">
                            保存した文体分析はまだありません。
                        </p>

                        <p class="mt-2 text-sm font-bold leading-7 text-[#A0AEC0]">
                            ストーリーを選択して分析用プロンプトを生成し、
                            AIの回答を保存してください。
                        </p>
                    </div>
                @endforelse
            </div>

            @if ($savedAnalyses->hasPages())
                <div class="mt-6">
                    {{ $savedAnalyses->links() }}
                </div>
            @endif
        </section>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const checkboxes = Array.from(
            document.querySelectorAll('.story-analysis-checkbox')
        );

        const selectAllButton = document.getElementById(
            'select-all-stories'
        );

        const clearAllButton = document.getElementById(
            'clear-all-stories'
        );

        const selectedCount = document.getElementById(
            'selected-story-count'
        );

        const selectedLength = document.getElementById(
            'selected-story-length'
        );

        const storyLengths = @json(
            $stories->mapWithKeys(
                fn ($story) => [
                    (string) $story->id => $story->bodyLength()
                ]
            )
        );

        const updateSelectionStats = () => {
            const selected = checkboxes.filter(
                checkbox => checkbox.checked
            );

            const totalLength = selected.reduce(
                (total, checkbox) => {
                    return total + Number(
                        storyLengths[checkbox.value] || 0
                    );
                },
                0
            );

            if (selectedCount) {
                selectedCount.textContent =
                    selected.length.toLocaleString();
            }

            if (selectedLength) {
                selectedLength.textContent =
                    totalLength.toLocaleString();
            }
        };

        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener(
                'change',
                updateSelectionStats
            );
        });

        if (selectAllButton) {
            selectAllButton.addEventListener('click', () => {
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = true;
                });

                updateSelectionStats();
            });
        }

        if (clearAllButton) {
            clearAllButton.addEventListener('click', () => {
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = false;
                });

                updateSelectionStats();
            });
        }

        updateSelectionStats();

        const copyButton = document.getElementById(
            'copy-analysis-prompt'
        );

        const promptText = document.getElementById(
            'analysis-prompt-text'
        );

        const copyMessage = document.getElementById(
            'analysis-copy-message'
        );

        if (copyButton && promptText) {
            copyButton.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(
                        promptText.value
                    );
                } catch (error) {
                    promptText.focus();
                    promptText.select();
                    document.execCommand('copy');
                }

                if (copyMessage) {
                    copyMessage.classList.remove('hidden');

                    window.setTimeout(() => {
                        copyMessage.classList.add('hidden');
                    }, 3000);
                }
            });
        }

        const analysisResult = document.getElementById(
            'analysis_result'
        );

        const analysisResultCount = document.getElementById(
            'analysis-result-count'
        );

        const updateAnalysisResultCount = () => {
            if (!analysisResult || !analysisResultCount) {
                return;
            }

            analysisResultCount.textContent =
                analysisResult.value.length.toLocaleString();
        };

        analysisResult?.addEventListener(
            'input',
            updateAnalysisResultCount
        );

        updateAnalysisResultCount();

        const result = document.getElementById(
            'analysis-result'
        );

        if (result) {
            result.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        }
    });
</script>

@include('writer.original_characters._layout_end')
