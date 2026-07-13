@php
    $prompt = $prompt
        ?? $savedPrompt
        ?? null;

    $aiResults = $aiResults ?? collect();
    $aiResult = $aiResults->first();

    $resultTitle = old(
        'result_title',
        $aiResult?->title ?? ''
    );

    $resultBody = old(
        'result_body',
        $aiResult?->result_body ?? ''
    );
@endphp

@if ($prompt)
    <section
        id="saved-prompt-ai-results"
        class="mt-8 rounded-3xl border border-[#FED7E2] bg-white p-5 shadow-sm sm:p-6 md:p-8"
    >
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">
                AIの回答を保存
            </p>

            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                プロット・執筆用データを保存する
            </h2>

            <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                上に表示されている生成プロンプトをAIへ貼り付け、
                AIが返したプロット、構成案、設定整理、執筆時の注意点などを
                下の欄へ貼り付けて保存できます。
            </p>

            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                AI回答は、1つのプロンプトにつき1件まで保存できます。
                すでに回答が保存されている場合は、再保存すると現在の内容を更新します。
                保存できる回答は最大20,000文字です。
            </p>

            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                保存時点の生成プロンプトも一緒に記録されるため、
                後からプロンプトを編集しても、どの指示に対する回答だったか確認できます。
            </p>
        </div>



        @if (
            $errors->has('result_title')
            || $errors->has('result_body')
        )
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm font-bold text-red-600">
                <p>
                    AI回答の入力内容を確認してください。
                </p>

                <ul class="mt-3 list-disc space-y-1 pl-5">
                    @foreach ($errors->get('result_title') as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                    @foreach ($errors->get('result_body') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route(
                'writer.prompts.ai-results.store',
                $prompt
            ) }}"
            class="min-w-0 space-y-5"
        >
            @csrf

            <div class="min-w-0">
                <label
                    for="result_title"
                    class="mb-2 block font-bold text-[#2D3748]"
                >
                    AI回答の管理名
                </label>

                <input
                    id="result_title"
                    type="text"
                    name="result_title"
                    value="{{ $resultTitle }}"
                    placeholder="例：第1案、恋愛短編プロット、全10話構成案"
                    class="block w-full min-w-0 rounded-2xl border-[#CBD5E0] text-[#2D3748] focus:border-[#FED7E2] focus:ring-[#FED7E2]"
                    style="width:100%;max-width:none;"
                >

                <p class="mt-2 text-xs font-bold leading-6 text-[#A0AEC0]">
                    空欄の場合は、保存日時から自動で名前を付けます。
                </p>
            </div>

            <div class="min-w-0">
                <label
                    for="result_body"
                    class="mb-2 block font-bold text-[#2D3748]"
                >
                    AIが出した結論
                    <span class="text-red-500">必須</span>
                </label>

                <textarea
                    id="result_body"
                    name="result_body"
                    rows="18"
                    required
                    placeholder="AIが返したプロット、構成案、キャラクターの役割、場面案、伏線、執筆時の注意点などを貼り付けてください。"
                    class="block w-full min-w-0 resize-y rounded-2xl border-[#CBD5E0] p-4 text-[#2D3748] focus:border-[#FED7E2] focus:ring-[#FED7E2] sm:p-5"
                    style="display:block;width:100%;max-width:none;min-width:0;min-height:420px;box-sizing:border-box;"
                >{{ $resultBody }}</textarea>

                <div class="mt-3 flex flex-col gap-2 text-xs font-bold text-[#A0AEC0] sm:flex-row sm:items-center sm:justify-between">
                    <p>
                        AIの回答は、そのまま貼り付けて保存できます。
                    </p>

                    <p>
                        文字数：
                        <span id="saved-prompt-ai-result-count">
                            {{ number_format(mb_strlen($resultBody)) }}
                        </span>
                        / 20,000文字
                    </p>
                </div>
            </div>

            <button
                type="submit"
                class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-4 text-base font-bold text-[#2D3748] hover:opacity-90 sm:px-6 sm:text-lg"
            >
                {{ $aiResult
                    ? 'AIが出した結論を更新する'
                    : 'AIが出した結論を保存する' }}
            </button>
        </form>
    </section>

    {{-- V3_RECOMMENDED_FOLLOWUP_PROMPTS --}}
    <section class="mt-8 rounded-3xl border border-[#E2E8F0] bg-white p-5 shadow-sm sm:p-6 md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">
                次の指示に使える例文
            </p>

            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                おすすめプロンプト
            </h2>

            <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                AIが整理したプロットや執筆用データをもとに、
                小説本文の執筆や内容確認を依頼するときに使用できます。
            </p>

            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                使用したい項目を開き、必要に応じて文章を編集してから
                「コピー」を押してください。
            </p>
        </div>

        @php
            $recommendedPrompts = [
                [
                    'title' => '「起」を執筆する',
                    'description' => '物語の導入部分を約2,500字で執筆します。',
                    'text' => 'これらのデータを踏まえ、小説家として、起承転結の「起」を2,500字程度で執筆してください。登場人物の一人称、口調、性格、関係性を守り、物語の導入として舞台・状況・目的が自然に伝わる内容にしてください。',
                ],
                [
                    'title' => '「承」を執筆する',
                    'description' => '人物関係や物語上の問題を進展させます。',
                    'text' => 'これまでの本文と整理されたデータを踏まえ、小説家として、起承転結の「承」を2,500字程度で執筆してください。人物同士の関係や物語上の問題を進展させ、「転」へ自然につながる流れにしてください。',
                ],
                [
                    'title' => '「転」を執筆する',
                    'description' => '物語の転機や大きな感情変化を描きます。',
                    'text' => 'これまでの本文と整理されたデータを踏まえ、小説家として、起承転結の「転」を2,500字程度で執筆してください。物語の転機となる出来事や感情の変化を描き、登場人物の行動に不自然な点がないようにしてください。',
                ],
                [
                    'title' => '「結」を執筆する',
                    'description' => '物語を回収し、余韻のある形で完結させます。',
                    'text' => 'これまでの本文と整理されたデータを踏まえ、小説家として、起承転結の「結」を2,500字程度で執筆してください。物語上の問題と主要な感情の変化を回収し、読後に余韻が残る形で完結させてください。',
                ],
                [
                    'title' => '詳細なプロットを作成する',
                    'description' => '場面ごとの出来事や感情変化を整理します。',
                    'text' => 'これらのデータを踏まえ、編集者として小説の詳細なプロットを作成してください。各場面について、場所、登場人物、出来事、人物の目的、感情の変化、会話の要点、伏線、次の場面へのつなぎを整理してください。',
                ],
                [
                    'title' => '会話中心のシーンを執筆する',
                    'description' => '口調や呼び方を反映した会話シーンを作成します。',
                    'text' => 'これらのデータを踏まえ、指定された場面を会話中心の小説本文として執筆してください。登場人物ごとの一人称、呼び方、口調、性格、関係性の違いが伝わるようにし、台詞だけでなく表情や仕草も適度に加えてください。',
                ],
                [
                    'title' => '設定や口調の矛盾を確認する',
                    'description' => '本文と登録設定に食い違いがないか確認します。',
                    'text' => 'これらのデータと作成した本文を照合し、設定の矛盾、時系列の不整合、呼び方の間違い、一人称や口調のずれ、人物の性格に合わない行動がないか確認してください。問題がある箇所は、理由と修正案を具体的に示してください。',
                ],
                [
                    'title' => '本文を推敲する',
                    'description' => '内容を維持しながら、読みやすさや表現を改善します。',
                    'text' => 'これらのデータを守ったまま、作成した小説本文を推敲してください。内容や出来事は大きく変更せず、読みやすさ、文章のリズム、情景描写、感情表現、会話の自然さを改善してください。修正後の本文を全文出力してください。',
                ],
            ];
        @endphp

        <div class="space-y-4">
            @foreach ($recommendedPrompts as $index => $recommendedPrompt)
                @php
                    $promptBoxId =
                        'recommended-followup-prompt-' . $index;
                @endphp

                <details class="group overflow-hidden rounded-3xl border border-[#E2E8F0] bg-white">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5 hover:bg-[#FFF7FA]">
                        <span class="min-w-0">
                            <span class="block font-bold text-[#2D3748]">
                                {{ $recommendedPrompt['title'] }}
                            </span>

                            <span class="mt-1 block text-xs font-bold leading-6 text-[#A0AEC0]">
                                {{ $recommendedPrompt['description'] }}
                            </span>
                        </span>

                        <span class="shrink-0 text-2xl font-bold text-[#A0AEC0] transition-transform group-open:rotate-45">
                            ＋
                        </span>
                    </summary>

                    <div class="border-t border-[#E2E8F0] bg-[#F7FAFC] p-4 sm:p-5">
                        <textarea
                            id="{{ $promptBoxId }}"
                            rows="6"
                            class="block w-full min-w-0 resize-y rounded-2xl border-[#CBD5E0] bg-white p-4 text-sm font-medium leading-7 text-[#2D3748] focus:border-[#FED7E2] focus:ring-[#FED7E2]"
                            style="display:block;width:100%;max-width:none;min-width:0;box-sizing:border-box;"
                        >{{ $recommendedPrompt['text'] }}</textarea>

                        <div class="mt-4 flex justify-end">
                            <button
                                type="button"
                                class="recommended-followup-copy-button inline-flex w-full items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90 sm:w-auto"
                                data-copy-target="{{ $promptBoxId }}"
                            >
                                コピー
                            </button>
                        </div>
                    </div>
                </details>
            @endforeach
        </div>

        <p
            id="recommended-followup-copy-message"
            class="mt-5 hidden rounded-2xl bg-[#FFF1F5] px-5 py-3 text-sm font-bold text-[#2D3748]"
            role="status"
        >
            プロンプトをコピーしました。
        </p>
    </section>
    {{-- /V3_RECOMMENDED_FOLLOWUP_PROMPTS --}}



    <section class="mt-8 rounded-3xl border border-[#E2E8F0] bg-white p-5 shadow-sm sm:p-6 md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">
                保存内容
            </p>

            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                保存したAI回答
            </h2>

            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                このプロンプトに保存できるAI回答は1件です。
                別の内容を保存する場合は、現在の内容を削除後、再度登録してください。
            </p>
        </div>

        @if ($aiResult)
            <article class="rounded-3xl border border-[#E2E8F0] bg-[#F7FAFC] p-5 md:p-6">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0">
                        <h3 class="break-words text-xl font-bold text-[#2D3748]">
                            {{ $aiResult->title }}
                        </h3>

                        <p class="mt-2 text-xs font-bold text-[#A0AEC0]">
                            更新日：
                            {{ $aiResult->updated_at?->format('Y/m/d H:i') }}
                            ／
                            {{ number_format($aiResult->resultLength()) }}文字
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route(
                            'writer.prompts.ai-results.destroy',
                            [
                                'prompt' => $prompt,
                                'result' => $aiResult,
                            ]
                        ) }}"
                        onsubmit="return confirm('保存したAI回答を削除しますか？');"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-2xl border border-red-200 bg-white px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 xl:w-auto"
                        >
                            削除
                        </button>
                    </form>
                </div>

                <div class="mt-5 min-w-0 rounded-2xl bg-white p-4 sm:p-5">
                    <p class="text-xs font-bold text-[#A0AEC0]">
                        AIが出した結論
                    </p>

                    <div class="mt-3 whitespace-pre-wrap break-words text-sm font-medium leading-8 text-[#2D3748]">
                        {{ $aiResult->result_body }}
                    </div>
                </div>

                <details class="mt-4 min-w-0 rounded-2xl bg-white p-4 sm:p-5">
                    <summary class="cursor-pointer font-bold text-[#2D3748]">
                        回答生成時のプロンプトを確認
                    </summary>

                    <div class="mt-4 whitespace-pre-wrap break-words font-mono text-xs leading-7 text-[#4A5568]">
                        {{ $aiResult->prompt_snapshot }}
                    </div>
                </details>
            </article>
        @else
            <div class="rounded-2xl bg-[#F7FAFC] p-8 text-center">
                <p class="font-bold text-[#2D3748]">
                    保存したAI回答はまだありません。
                </p>

                <p class="mt-2 text-sm font-bold leading-7 text-[#A0AEC0]">
                    生成したプロンプトをAIへ貼り付け、
                    返ってきたプロットや執筆用データを保存してください。
                </p>
            </div>
        @endif
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const resultBody = document.getElementById(
                'result_body'
            );

            const resultCount = document.getElementById(
                'saved-prompt-ai-result-count'
            );

            const recommendedFollowupCopyButtons =
                document.querySelectorAll(
                    '.recommended-followup-copy-button'
                );

            const recommendedFollowupCopyMessage =
                document.getElementById(
                    'recommended-followup-copy-message'
                );

            let recommendedCopyMessageTimer = null;

            const showRecommendedCopyMessage = () => {
                if (! recommendedFollowupCopyMessage) {
                    return;
                }

                recommendedFollowupCopyMessage
                    .classList
                    .remove('hidden');

                if (recommendedCopyMessageTimer) {
                    window.clearTimeout(
                        recommendedCopyMessageTimer
                    );
                }

                recommendedCopyMessageTimer =
                    window.setTimeout(() => {
                        recommendedFollowupCopyMessage
                            .classList
                            .add('hidden');
                    }, 2500);
            };

            const copyRecommendedPrompt = async (
                button
            ) => {
                const targetId =
                    button.dataset.copyTarget;

                const target =
                    document.getElementById(targetId);

                if (! target) {
                    return;
                }

                const copyText = target.value;

                try {
                    await navigator.clipboard.writeText(
                        copyText
                    );
                } catch (error) {
                    target.focus();
                    target.select();
                    document.execCommand('copy');
                }

                const originalText =
                    button.textContent.trim();

                button.textContent = 'コピーしました';

                window.setTimeout(() => {
                    button.textContent = originalText;
                }, 1800);

                showRecommendedCopyMessage();
            };

            recommendedFollowupCopyButtons.forEach(
                (button) => {
                    button.addEventListener(
                        'click',
                        () => copyRecommendedPrompt(button)
                    );
                }
            );

            const maxLength = 20000;

            const updateResultCount = () => {
                if (! resultBody || ! resultCount) {
                    return;
                }

                const normalizedValue = resultBody.value
                .replace(/\r\n/g, '\n')
                .replace(/\r/g, '\n');

            const length = Array.from(
                normalizedValue
            ).length;

                resultCount.textContent =
                    length.toLocaleString();

                resultCount.classList.toggle(
                    'text-red-600',
                    length > maxLength
                );
            };

            resultBody?.addEventListener(
                'input',
                updateResultCount
            );

            updateResultCount();
        });
    </script>
@endif
