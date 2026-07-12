@include('writer.original_characters._layout_start', ['title' => 'オリジナルキャラクター管理'])

@php
    $keyword = request('keyword');

    /*
     * Controller側の変数名差異に対応。
     * 既存実装では $originalCharacters で渡している可能性があるため、
     * view側で安全に吸収する。
     */
    $characterItems = $characters
        ?? $originalCharacters
        ?? $originalCharacterList
        ?? collect();

    $characterTotal = method_exists($characterItems, 'total')
        ? $characterItems->total()
        : $characterItems->count();

    $characterLimit = auth()->user()
        ? \App\Support\WritingAssistLimits::originalCharactersPerUser(auth()->user())
        : null;

    $characterRegisteredCount = $count ?? $characterTotal;

    $characterLimitLabel = $characterLimit === null
        ? number_format($characterRegisteredCount) . ' / 制限なし'
        : number_format($characterRegisteredCount) . ' / ' . number_format($characterLimit);

    $hasReachedCharacterLimit = $characterLimit !== null
        && $characterRegisteredCount >= $characterLimit;
@endphp

<div class="mb-8">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-[#2D3748]">オリジナルキャラクター管理</h1>
            <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
                小説作成に使うオリジナルキャラクター情報を登録・管理できます。
            </p>
        </div>

        @if ($hasReachedCharacterLimit)
            <div class="inline-flex cursor-not-allowed items-center justify-center rounded-2xl bg-[#EDF2F7] px-6 py-3 font-bold text-[#A0AEC0]">
                上限に達しています
            </div>
        @else
            <a href="{{ route('writer.original-characters.create') }}"
               class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                新規登録
            </a>
        @endif
    </div>
</div>

@if ($hasReachedCharacterLimit)
    <section class="mb-8 rounded-3xl border border-[#FED7E2] bg-[#FFF1F5] p-6 shadow-sm">
        <p class="font-bold text-[#2D3748]">オリジナルキャラクターの登録上限に達しています。</p>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            新しく登録する場合は、不要なキャラクターを削除してください。
        </p>
    </section>
@endif

<div class="mb-8 grid gap-6 md:grid-cols-3">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">登録件数</p>
        <div class="mt-3 text-4xl font-bold text-[#2D3748]">
            {{ $characterLimitLabel }}
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">表示中</p>
        <div class="mt-3 text-4xl font-bold text-[#2D3748]">
            {{ number_format($characterItems->count()) }}
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">用途</p>
        <div class="mt-3 text-2xl font-bold text-[#2D3748]">
            プロンプト反映用
        </div>
    </section>
</div>

<section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
    <form method="GET" action="{{ route('writer.original-characters.index') }}" class="space-y-4">
        <div>
            <label class="mb-2 block text-sm font-bold text-[#2D3748]">キーワード検索</label>
            <input type="text"
                   name="keyword"
                   value="{{ $keyword }}"
                   placeholder="名前・読み仮名・所属・性格・背景などで検索"
                   class="w-full rounded-2xl border-[#CBD5E0] text-[#2D3748] focus:border-[#FED7E2] focus:ring-[#FED7E2]">
        </div>

        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                検索する
            </button>

            <a href="{{ route('writer.original-characters.index') }}"
               class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                条件をリセット
            </a>
        </div>
    </form>
</section>

<div class="space-y-5">
    @forelse ($characterItems as $character)
        <article class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="grid gap-3 text-sm font-bold text-[#4A5568] md:grid-cols-4">
                        <div class="rounded-2xl bg-[#F7FAFC] px-4 py-3">
                            <p class="text-xs text-[#A0AEC0]">名前</p>
                            <p class="mt-1 text-lg text-[#2D3748]">
                                <a href="{{ route('writer.original-characters.show', $character) }}" class="hover:underline">
                                    {{ $character->name ?: '未入力' }}
                                </a>
                            </p>
                        </div>

                        <div class="rounded-2xl bg-[#F7FAFC] px-4 py-3">
                            <p class="text-xs text-[#A0AEC0]">所属</p>
                            <p class="mt-1 text-[#2D3748]">{{ $character->affiliation ?: '未入力' }}</p>
                        </div>

                        <div class="rounded-2xl bg-[#F7FAFC] px-4 py-3">
                            <p class="text-xs text-[#A0AEC0]">一人称</p>
                            <p class="mt-1 text-[#2D3748]">{{ $character->first_person ?: '未入力' }}</p>
                        </div>

                        <div class="rounded-2xl bg-[#F7FAFC] px-4 py-3">
                            <p class="text-xs text-[#A0AEC0]">更新日</p>
                            <p class="mt-1 text-[#2D3748]">{{ $character->updated_at?->format('Y/m/d H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2 xl:w-52 xl:flex-col">
                    <a href="{{ route('writer.original-characters.show', $character) }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-4 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90">
                        詳細
                    </a>

                    <a href="{{ route('writer.original-characters.edit', $character) }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                        編集
                    </a>

                    <form method="POST"
                          action="{{ route('writer.original-characters.destroy', $character) }}"
                          onsubmit="return confirm('このキャラクターを削除しますか？');">
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
            <p class="text-2xl font-bold text-[#2D3748]">オリジナルキャラクターがまだありません。</p>
            <p class="mt-3 text-sm font-bold leading-7 text-[#A0AEC0]">
                まずは小説に使いたいキャラクターを登録しましょう。
            </p>

            <div class="mt-6 flex flex-col justify-center gap-3 md:flex-row">
                @if ($hasReachedCharacterLimit)
                    <div class="inline-flex cursor-not-allowed items-center justify-center rounded-2xl bg-[#EDF2F7] px-6 py-3 font-bold text-[#A0AEC0]">
                        上限に達しています
                    </div>
                @else
                    <a href="{{ route('writer.original-characters.create') }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                        オリジナルキャラクターを登録する
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

@if (method_exists($characterItems, 'hasPages') && $characterItems->hasPages())
    <div class="mt-8">
        {{ $characterItems->links() }}
    </div>
@endif

@include('writer.original_characters._layout_end')
