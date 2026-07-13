@include('writer.original_characters._layout_start', ['title' => $analysis->title])

<div class="writer-form-ui">
    <div class="mb-8 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-[#F7FAFC] px-3 py-1 text-xs font-bold text-[#718096]">
                    ID：{{ $analysis->id }}
                </span>

                @if ($analysis->hasAnalysisResult())
                    <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700">
                        分析結果保存済み
                    </span>
                @else
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                        分析結果未保存
                    </span>
                @endif
            </div>

            <h1 class="mt-3 text-3xl font-bold text-[#2D3748]">
                {{ $analysis->title }}
            </h1>

            <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
                更新：{{ $analysis->updated_at?->format('Y/m/d H:i') }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a
                href="{{ route('writer.story-analyses.edit', $analysis) }}"
                class="rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748]"
            >
                編集
            </a>

            <a
                href="{{ route('writer.story-analyses.index') }}"
                class="rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748]"
            >
                一覧へ戻る
            </a>
        </div>
    </div>

    <div class="space-y-8">
        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
            <h2 class="text-xl font-bold text-[#2D3748]">
                分析対象ストーリー
            </h2>

            <ul class="mt-4 space-y-2 text-sm font-bold leading-7 text-[#4A5568]">
                @foreach ($analysis->resolved_stories ?? [] as $resolved)
                    <li>
                        ・
                        @if ($resolved['story'])
                            @if ($resolved['story']->episode_number)
                                第{{ number_format($resolved['story']->episode_number) }}話：
                            @endif

                            {{ $resolved['story']->title }}

                            @if ($resolved['story']->trashed())
                                （削除済み）
                            @endif
                        @else
                            削除済みストーリー（ID：{{ $resolved['id'] }}）
                        @endif
                    </li>
                @endforeach
            </ul>

            @if ($analysis->analysis_notes)
                <div class="mt-6 rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="text-xs font-bold text-[#A0AEC0]">
                        追加指示
                    </p>

                    <p class="mt-3 whitespace-pre-wrap text-sm font-bold leading-7 text-[#4A5568]">
                        {{ $analysis->analysis_notes }}
                    </p>
                </div>
            @endif
        </section>

        <section class="rounded-3xl border border-[#FED7E2] bg-white p-6 shadow-sm md:p-8">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-[#2D3748]">
                        分析用プロンプト
                    </h2>

                    <p class="mt-2 text-xs font-bold text-[#A0AEC0]">
                        {{ number_format($analysis->promptLength()) }}文字
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
                id="analysis-prompt-text"
                class="mt-5"
                rows="30"
                readonly
            >{{ $analysis->analysis_prompt }}</textarea>

            <p
                id="copy-message"
                class="mt-4 hidden rounded-2xl bg-green-50 p-4 text-sm font-bold text-green-700"
            >
                プロンプトをコピーしました。
            </p>
        </section>

        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
            <h2 class="text-xl font-bold text-[#2D3748]">
                分析結果
            </h2>

            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                1つの文体分析につき、分析結果を1件保存できます。再保存すると上書きされます。
            </p>

            <form
                method="POST"
                action="{{ route('writer.story-analyses.result.update', $analysis) }}"
                class="mt-5"
            >
                @csrf
                @method('PATCH')

                <textarea
                    id="analysis_result"
                    name="analysis_result"
                    rows="26"
                    required
                    maxlength="{{ $analysisResultMax ?? 10000 }}"
                    placeholder="AIから返された分析結果を貼り付けてください。"
                >{{ old('analysis_result', $analysis->analysis_result) }}</textarea>

                <p class="mt-2 text-right text-xs font-bold text-[#A0AEC0]">
                    <span id="analysis-result-count">
                        {{ number_format($analysis->resultLength()) }}
                    </span>
                    /
                    {{ number_format($analysisResultMax ?? 10000) }}文字
                </p>

                <button
                    type="submit"
                    class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-4 text-lg font-bold text-[#2D3748]"
                >
                    分析結果を保存
                </button>
            </form>

            @if ($analysis->hasAnalysisResult())
                <form
                    method="POST"
                    action="{{ route('writer.story-analyses.result.destroy', $analysis) }}"
                    class="mt-4"
                    onsubmit="return confirm('保存済みの分析結果を削除しますか？');"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-red-50 px-6 py-4 font-bold text-red-700"
                    >
                        分析結果を削除
                    </button>
                </form>
            @endif
        </section>

        <section class="rounded-3xl border border-red-100 bg-white p-6 shadow-sm">
            <form
                method="POST"
                action="{{ route('writer.story-analyses.destroy', $analysis) }}"
                onsubmit="return confirm('文体分析のプロンプトと分析結果を削除しますか？');"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-2xl bg-red-50 px-6 py-4 font-bold text-red-700"
                >
                    文体分析全体を削除
                </button>
            </form>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const copyButton = document.getElementById(
        'copy-analysis-prompt'
    );

    const prompt = document.getElementById(
        'analysis-prompt-text'
    );

    const copyMessage = document.getElementById(
        'copy-message'
    );

    copyButton?.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(prompt.value);
        } catch (error) {
            prompt.focus();
            prompt.select();
            document.execCommand('copy');
        }

        copyMessage?.classList.remove('hidden');

        window.setTimeout(() => {
            copyMessage?.classList.add('hidden');
        }, 3000);
    });

    const result = document.getElementById('analysis_result');
    const resultCount = document.getElementById(
        'analysis-result-count'
    );

    const updateCount = () => {
        if (result && resultCount) {
            resultCount.textContent =
                result.value.length.toLocaleString();
        }
    };

    result?.addEventListener('input', updateCount);
    updateCount();
});
</script>

@include('writer.original_characters._layout_end')
