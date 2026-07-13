@include('writer.original_characters._layout_start', ['title' => '文体分析'])

<div class="writer-form-ui">
    <div class="mb-8 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-[#2D3748]">
                文体分析
            </h1>

            <p class="mt-3 text-sm font-bold leading-7 text-[#A0AEC0]">
                登録済みストーリーから文体分析用プロンプトを作成し、AIの分析結果を保存できます。
            </p>

            <p class="mt-2 text-sm font-bold text-[#718096]">
                保存件数：
                {{ number_format($count) }}
                /
                {{ $limit === null ? '制限なし' : number_format($limit) }}
                件
            </p>
        </div>

        @if ($canCreate)
            <a
                href="{{ route('writer.story-analyses.create') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90"
            >
                新規登録
            </a>
        @else
            <span class="inline-flex cursor-not-allowed items-center justify-center rounded-2xl bg-[#E2E8F0] px-6 py-3 font-bold text-[#A0AEC0]">
                保存上限に達しています
            </span>
        @endif
    </div>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="space-y-5">
            @forelse ($analyses as $analysis)
                <article class="rounded-3xl border border-[#E2E8F0] bg-[#F7FAFC] p-5 md:p-6">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-[#718096]">
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

                            <h2 class="mt-3 break-words text-xl font-bold text-[#2D3748]">
                                {{ $analysis->title }}
                            </h2>

                            <p class="mt-2 text-xs font-bold text-[#A0AEC0]">
                                対象ストーリー：
                                {{ number_format($analysis->storyCount()) }}件
                                ／
                                更新：
                                {{ $analysis->updated_at?->format('Y/m/d H:i') }}
                            </p>

                            <ul class="mt-4 space-y-1 text-sm font-bold leading-7 text-[#4A5568]">
                                @foreach ($analysis->resolved_stories ?? [] as $resolved)
                                    <li>
                                        ・
                                        @if ($resolved['story'])
                                            @if ($resolved['story']->episode_number)
                                                第{{ number_format($resolved['story']->episode_number) }}話：
                                            @endif

                                            {{ $resolved['story']->title }}

                                        @else
                                            削除済みストーリー（ID：{{ $resolved['id'] }}）
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a
                                href="{{ route('writer.story-analyses.show', $analysis) }}"
                                class="rounded-xl bg-[#FED7E2] px-4 py-2 text-sm font-bold text-[#2D3748]"
                            >
                                詳細
                            </a>

                            <a
                                href="{{ route('writer.story-analyses.edit', $analysis) }}"
                                class="rounded-xl border border-[#CBD5E0] bg-white px-4 py-2 text-sm font-bold text-[#2D3748]"
                            >
                                編集
                            </a>

                            <form
                                method="POST"
                                action="{{ route('writer.story-analyses.destroy', $analysis) }}"
                                onsubmit="return confirm('この文体分析を削除しますか？');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="rounded-xl bg-red-50 px-4 py-2 text-sm font-bold text-red-700"
                                >
                                    削除
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl bg-[#F7FAFC] p-10 text-center">
                    <p class="text-lg font-bold text-[#2D3748]">
                        文体分析はまだ登録されていません。
                    </p>

                    <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
                        「新規登録」からストーリーを選択し、分析用プロンプトを作成してください。
                    </p>
                </div>
            @endforelse
        </div>

        @if ($analyses->hasPages())
            <div class="mt-6">
                {{ $analyses->links() }}
            </div>
        @endif
    </section>
</div>

@include('writer.original_characters._layout_end')
