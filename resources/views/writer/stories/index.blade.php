@include('writer.original_characters._layout_start', ['title' => 'ストーリー管理'])

@php
    $filters = $filters ?? request()->all();

    $storyCount = $count ?? (
        method_exists($stories, 'total')
            ? $stories->total()
            : $stories->count()
    );

    $storyLimit = $limit ?? null;

    $storyLimitLabel = $storyLimit === null
        ? number_format($storyCount) . ' / 制限なし'
        : number_format($storyCount) . ' / ' . number_format($storyLimit);

    $hasReachedStoryLimit = $storyLimit !== null
        && $storyCount >= $storyLimit;

    $currentSort = $filters['sort'] ?? 'episode';
@endphp

<div class="mb-8">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-[#2D3748]">
                ストーリー管理
            </h1>
            <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
                これまでに書いたストーリーを登録・管理できます。
            </p>
        </div>

        <div class="flex flex-col gap-3 md:flex-row">
            <a
                href="{{ route('writer.stories.analysis') }}"
                class="inline-flex items-center justify-center rounded-2xl border border-[#FED7E2] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#FFF1F5]"
            >
                分析プロンプトを作成
            </a>

        @if ($hasReachedStoryLimit)
            <div class="inline-flex cursor-not-allowed items-center justify-center rounded-2xl bg-[#EDF2F7] px-6 py-3 font-bold text-[#A0AEC0]">
                上限に達しています
            </div>
        @else
            <a
                href="{{ route('writer.stories.create') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90"
            >
                新規登録
            </a>
        @endif
        </div>
    </div>
</div>

@if ($hasReachedStoryLimit)
    <section class="mb-8 rounded-3xl border border-[#FED7E2] bg-[#FFF1F5] p-6 shadow-sm">
        <p class="font-bold text-[#2D3748]">
            ストーリーの登録上限に達しています。
        </p>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            新しく登録する場合は、不要なストーリーを削除してください。
        </p>
    </section>
@endif

<div class="mb-8 grid gap-6 md:grid-cols-3">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">登録件数</p>
        <div class="mt-3 text-4xl font-bold text-[#2D3748]">
            {{ $storyLimitLabel }}
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">表示中</p>
        <div class="mt-3 text-4xl font-bold text-[#2D3748]">
            {{ number_format($stories->count()) }}
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">用途</p>
        <div class="mt-3 text-2xl font-bold text-[#2D3748]">
            文体分析・再現用
        </div>
    </section>
</div>

<section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
    <form
        method="GET"
        action="{{ route('writer.stories.index') }}"
        class="space-y-5"
    >
        <div>
            <label class="mb-2 block text-sm font-bold text-[#2D3748]">
                キーワード検索
            </label>

            <input
                type="text"
                name="keyword"
                value="{{ $filters['keyword'] ?? '' }}"
                placeholder="タイトル・本文・メモなどで検索"
                class="w-full rounded-2xl border-[#CBD5E0] text-[#2D3748] focus:border-[#FED7E2] focus:ring-[#FED7E2]"
            >
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-bold text-[#2D3748]">
                    状態
                </label>

                <select
                    name="status"
                    class="w-full rounded-2xl border-[#CBD5E0] text-[#2D3748] focus:border-[#FED7E2] focus:ring-[#FED7E2]"
                >
                    <option value="">すべて</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>
                        有効
                    </option>
                    <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>
                        下書き
                    </option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-[#2D3748]">
                    並び順
                </label>

                <select
                    name="sort"
                    class="w-full rounded-2xl border-[#CBD5E0] text-[#2D3748] focus:border-[#FED7E2] focus:ring-[#FED7E2]"
                >
                    <option value="episode" @selected($currentSort === 'episode')>
                        話数順
                    </option>
                    <option value="latest" @selected($currentSort === 'latest')>
                        新しい順
                    </option>
                    <option value="oldest" @selected($currentSort === 'oldest')>
                        古い順
                    </option>
                    <option value="updated" @selected($currentSort === 'updated')>
                        更新順
                    </option>
                    <option value="title" @selected($currentSort === 'title')>
                        タイトル順
                    </option>
                </select>
            </div>
        </div>

        <div class="flex flex-col gap-3 md:flex-row">
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90"
            >
                検索する
            </button>

            <a
                href="{{ route('writer.stories.index') }}"
                class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]"
            >
                条件をリセット
            </a>
        </div>
    </form>
</section>

<div class="space-y-5">
    @forelse ($stories as $story)
        <article class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        @if ($story->episode_number)
                            <span class="rounded-full bg-[#FED7E2] px-3 py-1 text-xs font-bold text-[#2D3748]">
                                第{{ number_format($story->episode_number) }}話
                            </span>
                        @endif

                        <span class="rounded-full bg-[#F7FAFC] px-3 py-1 text-xs font-bold text-[#4A5568]">
                            {{ $story->statusLabel() }}
                        </span>
                    </div>

                    <h2 class="text-2xl font-bold text-[#2D3748]">
                        <a
                            href="{{ route('writer.stories.show', $story) }}"
                            class="hover:underline"
                        >
                            {{ $story->title }}
                        </a>
                    </h2>

                    <p class="mt-4 line-clamp-4 whitespace-pre-wrap text-sm font-bold leading-7 text-[#718096]">
                        {{ \Illuminate\Support\Str::limit($story->body, 300) }}
                    </p>

                    <div class="mt-5 grid gap-3 text-sm font-bold text-[#4A5568] md:grid-cols-3">
                        <div class="rounded-2xl bg-[#F7FAFC] px-4 py-3">
                            <p class="text-xs text-[#A0AEC0]">本文文字数</p>
                            <p class="mt-1 text-[#2D3748]">
                                {{ number_format($story->bodyLength()) }}文字
                            </p>
                        </div>

                        <div class="rounded-2xl bg-[#F7FAFC] px-4 py-3">
                            <p class="text-xs text-[#A0AEC0]">作成日</p>
                            <p class="mt-1 text-[#2D3748]">
                                {{ $story->created_at?->format('Y/m/d H:i') }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-[#F7FAFC] px-4 py-3">
                            <p class="text-xs text-[#A0AEC0]">更新日</p>
                            <p class="mt-1 text-[#2D3748]">
                                {{ $story->updated_at?->format('Y/m/d H:i') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex shrink-0 flex-col gap-2 xl:w-48">
                    <a
                        href="{{ route('writer.stories.show', $story) }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-4 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90"
                    >
                        詳細
                    </a>

                    <a
                        href="{{ route('writer.stories.edit', $story) }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]"
                    >
                        編集
                    </a>

                    <form
                        method="POST"
                        action="{{ route('writer.stories.destroy', $story) }}"
                        onsubmit="return confirm('このストーリーを削除しますか？');"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="w-full rounded-2xl border border-red-200 bg-white px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50"
                        >
                            削除
                        </button>
                    </form>
                </div>
            </div>
        </article>
    @empty
        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-10 text-center shadow-sm">
            <p class="text-2xl font-bold text-[#2D3748]">
                ストーリーがまだありません。
            </p>

            <p class="mt-3 text-sm font-bold leading-7 text-[#A0AEC0]">
                これまでに書いたストーリーを登録してみましょう。
            </p>

            @unless ($hasReachedStoryLimit)
                <div class="mt-6">
                    <a
                        href="{{ route('writer.stories.create') }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90"
                    >
                        ストーリーを登録する
                    </a>
                </div>
            @endunless
        </section>
    @endforelse
</div>

@if (method_exists($stories, 'hasPages') && $stories->hasPages())
    <div class="mt-8">
        {{ $stories->links() }}
    </div>
@endif

@include('writer.original_characters._layout_end')
