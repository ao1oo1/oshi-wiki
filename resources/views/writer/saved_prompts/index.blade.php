@include('writer.original_characters._layout_start', ['title' => 'プロンプト管理'])

@php
    $filters = $filters ?? request()->all();

    $sortLabels = [
        'latest' => '新しい順',
        'oldest' => '古い順',
        'updated' => '更新順',
        'most_used' => 'よく使う順',
        'recently_used' => '最近使った順',
        'title_asc' => 'タイトル昇順',
        'title_desc' => 'タイトル降順',
    ];

    $currentSort = $filters['sort'] ?? 'latest';

    $statusLabels = [
        '' => 'すべて',
        'active' => '有効',
        'draft' => '下書き',
    ];

    $writingStyleLabels = \App\Models\SavedPrompt::writingStyleLabels();
    $genreLabels = \App\Models\SavedPrompt::genreLabels();

    $promptLimit = $limit ?? (
        auth()->user()
            ? \App\Support\WritingAssistLimits::promptsPerUser(auth()->user())
            : null
    );

    $promptTotal = method_exists($savedPrompts, 'total')
        ? $savedPrompts->total()
        : $savedPrompts->count();

    $promptRegisteredCount = $count ?? $promptTotal;

    $promptLimitLabel = $promptLimit === null
        ? number_format($promptRegisteredCount) . ' / 制限なし'
        : number_format($promptRegisteredCount) . ' / ' . number_format($promptLimit);

    $hasReachedPromptLimit = $promptLimit !== null
        && $promptRegisteredCount >= $promptLimit;
@endphp

<div class="mb-8">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-[#2D3748]">プロンプト管理</h1>
            <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
                AIに渡すためのプロンプトを作成・保存・コピーできます。
            </p>
        </div>

        @if ($hasReachedPromptLimit)
            <div class="inline-flex cursor-not-allowed items-center justify-center rounded-2xl bg-[#EDF2F7] px-6 py-3 font-bold text-[#A0AEC0]">
                上限に達しています
            </div>
        @else
            <a href="{{ route('writer.prompts.create') }}"
               class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                新規作成
            </a>
        @endif
    </div>
</div>

@if ($hasReachedPromptLimit)
    <section class="mb-8 rounded-3xl border border-[#FED7E2] bg-[#FFF1F5] p-6 shadow-sm">
        <p class="font-bold text-[#2D3748]">プロンプトの保存上限に達しています。</p>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            新しく作成する場合は、不要なプロンプトを削除してください。
        </p>
    </section>
@endif

<div class="mb-8 grid gap-6 md:grid-cols-3">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">保存件数</p>
        <div class="mt-3 text-4xl font-bold text-[#2D3748]">
            {{ $promptLimitLabel }}
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">表示中</p>
        <div class="mt-3 text-4xl font-bold text-[#2D3748]">
            {{ number_format($savedPrompts->count()) }}
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">用途</p>
        <div class="mt-3 text-2xl font-bold text-[#2D3748]">
            コピーしてAIへ貼り付け
        </div>
    </section>
</div>

<section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
    <form method="GET" action="{{ route('writer.prompts.index') }}" class="space-y-5">
        <div>
            <label class="mb-2 block text-sm font-bold text-[#2D3748]">キーワード検索</label>
            <input type="text"
                   name="keyword"
                   value="{{ $filters['keyword'] ?? '' }}"
                   placeholder="タイトル・用途・あらすじ・本文・備考などで検索"
                   class="w-full rounded-2xl border-[#CBD5E0] text-[#2D3748] focus:border-[#FED7E2] focus:ring-[#FED7E2]">
        </div>

        <div class="grid gap-5 md:grid-cols-4">
            <div>
                <label class="mb-2 block text-sm font-bold text-[#2D3748]">作風</label>
                <select name="writing_style"
                        class="w-full rounded-2xl border-[#CBD5E0] text-[#2D3748] focus:border-[#FED7E2] focus:ring-[#FED7E2]">
                    <option value="">すべて</option>
                    @foreach ($writingStyleLabels as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['writing_style'] ?? '') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-[#2D3748]">ジャンル</label>
                <select name="genre"
                        class="w-full rounded-2xl border-[#CBD5E0] text-[#2D3748] focus:border-[#FED7E2] focus:ring-[#FED7E2]">
                    <option value="">すべて</option>
                    @foreach ($genreLabels as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['genre'] ?? '') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-[#2D3748]">状態</label>
                <select name="status"
                        class="w-full rounded-2xl border-[#CBD5E0] text-[#2D3748] focus:border-[#FED7E2] focus:ring-[#FED7E2]">
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-[#2D3748]">並び順</label>
                <select name="sort"
                        class="w-full rounded-2xl border-[#CBD5E0] text-[#2D3748] focus:border-[#FED7E2] focus:ring-[#FED7E2]">
                    @foreach ($sortLabels as $value => $label)
                        <option value="{{ $value }}" @selected($currentSort === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                検索する
            </button>

            <a href="{{ route('writer.prompts.index') }}"
               class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                条件をリセット
            </a>
        </div>
    </form>
</section>

<div class="mb-6 flex flex-wrap gap-3">
    <a href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}"
       class="rounded-full px-4 py-2 text-sm font-bold {{ $currentSort === 'latest' ? 'bg-[#FED7E2] text-[#2D3748]' : 'bg-white text-[#4A5568] border border-[#E2E8F0]' }}">
        新しい順
    </a>

    <a href="{{ request()->fullUrlWithQuery(['sort' => 'most_used']) }}"
       class="rounded-full px-4 py-2 text-sm font-bold {{ $currentSort === 'most_used' ? 'bg-[#FED7E2] text-[#2D3748]' : 'bg-white text-[#4A5568] border border-[#E2E8F0]' }}">
        よく使う順
    </a>

    <a href="{{ request()->fullUrlWithQuery(['sort' => 'recently_used']) }}"
       class="rounded-full px-4 py-2 text-sm font-bold {{ $currentSort === 'recently_used' ? 'bg-[#FED7E2] text-[#2D3748]' : 'bg-white text-[#4A5568] border border-[#E2E8F0]' }}">
        最近使った順
    </a>

    <a href="{{ request()->fullUrlWithQuery(['status' => 'draft']) }}"
       class="rounded-full px-4 py-2 text-sm font-bold {{ ($filters['status'] ?? '') === 'draft' ? 'bg-[#FED7E2] text-[#2D3748]' : 'bg-white text-[#4A5568] border border-[#E2E8F0]' }}">
        下書き
    </a>
</div>

<div class="space-y-5">
    @forelse ($savedPrompts as $prompt)
        <article class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-[#FFF1F5] px-3 py-1 text-xs font-bold text-[#2D3748]">
                            {{ $prompt->workLabel() }}
                        </span>

                        <span class="rounded-full bg-[#F7FAFC] px-3 py-1 text-xs font-bold text-[#4A5568]">
                            {{ $prompt->writingStyleLabel() ?: '作風指定なし' }}
                        </span>

                        <span class="rounded-full bg-[#F7FAFC] px-3 py-1 text-xs font-bold text-[#4A5568]">
                            {{ $prompt->genreLabel() ?: 'ジャンル指定なし' }}
                        </span>

                        @if ($prompt->include_relationship_timeline)
                            <span class="rounded-full bg-[#FFF1F5] px-3 py-1 text-xs font-bold text-[#2D3748]">
                                年表反映
                            </span>
                        @endif

                        @if ($prompt->status === 'draft')
                            <span class="rounded-full bg-[#EDF2F7] px-3 py-1 text-xs font-bold text-[#4A5568]">
                                下書き
                            </span>
                        @else
                            <span class="rounded-full bg-[#FED7E2] px-3 py-1 text-xs font-bold text-[#2D3748]">
                                有効
                            </span>
                        @endif
                    </div>

                    <h2 class="text-2xl font-bold leading-snug text-[#2D3748]">
                        <a href="{{ route('writer.prompts.show', $prompt) }}" class="hover:underline">
                            {{ $prompt->title }}
                        </a>
                    </h2>

                    @if ($prompt->synopsis)
                        <p class="mt-3 line-clamp-2 text-sm font-bold leading-7 text-[#4A5568]">
                            {{ $prompt->synopsis }}
                        </p>
                    @else
                        <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
                            あらすじ未入力
                        </p>
                    @endif

                    <div class="mt-5 grid gap-3 text-sm font-bold text-[#4A5568] md:grid-cols-3">
                        <div class="rounded-2xl bg-[#F7FAFC] px-4 py-3">
                            <p class="text-xs text-[#A0AEC0]">利用回数</p>
                            <p class="mt-1 text-lg text-[#2D3748]">{{ number_format($prompt->used_count ?? 0) }}回</p>
                        </div>

                        <div class="rounded-2xl bg-[#F7FAFC] px-4 py-3">
                            <p class="text-xs text-[#A0AEC0]">最終利用</p>
                            <p class="mt-1 text-[#2D3748]">{{ $prompt->lastUsedLabel() }}</p>
                        </div>

                        <div class="rounded-2xl bg-[#F7FAFC] px-4 py-3">
                            <p class="text-xs text-[#A0AEC0]">更新日</p>
                            <p class="mt-1 text-[#2D3748]">{{ $prompt->updated_at?->format('Y/m/d H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2 xl:w-52 xl:flex-col">
                    <a href="{{ route('writer.prompts.show', $prompt) }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-4 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90">
                        詳細
                    </a>

                    <a href="{{ route('writer.prompts.edit', $prompt) }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                        編集
                    </a>

                    <form method="POST" action="{{ route('writer.prompts.duplicate', $prompt) }}">
                        @csrf
                        <button type="submit"
                                class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                            複製
                        </button>
                    </form>

                    <form method="POST"
                          action="{{ route('writer.prompts.destroy', $prompt) }}"
                          onsubmit="return confirm('このプロンプトを削除しますか？');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full rounded-2xl border border-red-200 bg-white px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50">
                            削除
                        </button>
                    </form>
                </div>
            </div>
        </article>
    @empty
        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-10 text-center shadow-sm">
            <p class="text-2xl font-bold text-[#2D3748]">プロンプトがまだありません。</p>
            <p class="mt-3 text-sm font-bold leading-7 text-[#A0AEC0]">
                オリジナルキャラクターや関係性を登録したあと、プロンプトを作成してみましょう。
            </p>

            <div class="mt-6 flex flex-col justify-center gap-3 md:flex-row">
                @if ($hasReachedPromptLimit)
                    <div class="inline-flex cursor-not-allowed items-center justify-center rounded-2xl bg-[#EDF2F7] px-6 py-3 font-bold text-[#A0AEC0]">
                        上限に達しています
                    </div>
                @else
                    <a href="{{ route('writer.prompts.create') }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                        プロンプトを作成する
                    </a>
                @endif

                <a href="{{ route('writer.guide') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                    使い方ガイドを見る
                </a>
            </div>
        </section>
    @endforelse
</div>

@if ($savedPrompts->hasPages())
    <div class="mt-8">
        {{ $savedPrompts->links() }}
    </div>
@endif

@include('writer.original_characters._layout_end')
