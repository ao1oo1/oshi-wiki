@include('writer.original_characters._layout_start', ['title' => '関係性管理'])

@php
    $keyword = request('keyword');

    /*
     * Controller側の変数名差異に対応。
     */
    $relationshipItems = $relationships
        ?? $originalCharacterRelationships
        ?? $relationshipList
        ?? collect();

    $relationshipTotal = method_exists($relationshipItems, 'total')
        ? $relationshipItems->total()
        : $relationshipItems->count();

    $relationshipLimit = auth()->user()
        ? \App\Support\WritingAssistLimits::relationshipsPerUser(auth()->user())
        : null;

    $relationshipRegisteredCount = $count ?? $relationshipTotal;

    $relationshipLimitLabel = $relationshipLimit === null
        ? number_format($relationshipRegisteredCount) . ' / 制限なし'
        : number_format($relationshipRegisteredCount) . ' / ' . number_format($relationshipLimit);

    $hasReachedRelationshipLimit = $relationshipLimit !== null
        && $relationshipRegisteredCount >= $relationshipLimit;
@endphp

<div class="mb-8">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-[#2D3748]">関係性管理</h1>
            <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
                キャラクター同士の呼び方・関係性・印象を登録して、プロンプトに反映できます。
            </p>
        </div>

        @if ($hasReachedRelationshipLimit)
            <div class="inline-flex cursor-not-allowed items-center justify-center rounded-2xl bg-[#EDF2F7] px-6 py-3 font-bold text-[#A0AEC0]">
                上限に達しています
            </div>
        @else
            <a href="{{ route('writer.original-character-relationships.create') }}"
               class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                新規登録
            </a>
        @endif
    </div>
</div>

@if ($hasReachedRelationshipLimit)
    <section class="mb-8 rounded-3xl border border-[#FED7E2] bg-[#FFF1F5] p-6 shadow-sm">
        <p class="font-bold text-[#2D3748]">関係性の登録上限に達しています。</p>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            新しく登録する場合は、不要な関係性を削除してください。
        </p>
    </section>
@endif

<div class="mb-8 grid gap-6 md:grid-cols-3">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">登録件数</p>
        <div class="mt-3 text-4xl font-bold text-[#2D3748]">
            {{ $relationshipLimitLabel }}
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">表示中</p>
        <div class="mt-3 text-4xl font-bold text-[#2D3748]">
            {{ number_format($relationshipItems->count()) }}
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
    <form method="GET" action="{{ route('writer.original-character-relationships.index') }}" class="space-y-4">
        <div>
            <label class="mb-2 block text-sm font-bold text-[#2D3748]">キーワード検索</label>
            <input type="text"
                   name="keyword"
                   value="{{ $keyword }}"
                   placeholder="キャラクター名・呼び方・関係性・印象などで検索"
                   class="w-full rounded-2xl border-[#CBD5E0] text-[#2D3748] focus:border-[#FED7E2] focus:ring-[#FED7E2]">
        </div>

        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                検索する
            </button>

            <a href="{{ route('writer.original-character-relationships.index') }}"
               class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                条件をリセット
            </a>
        </div>
    </form>
</section>

<div class="space-y-5">
    @forelse ($relationshipItems as $relationship)
        <article class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="rounded-3xl bg-[#F7FAFC] p-5">
                        <div class="grid gap-5 md:grid-cols-[1fr_auto_1fr] md:items-center">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-xs font-bold text-[#A0AEC0]">
                                        From
                                    </p>

                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-[#4A5568]">
                                        {{ $relationship->fromSourceLabel() }}
                                    </span>
                                </div>

                                <p class="mt-2 text-2xl font-bold text-[#2D3748]">
                                    {{ $relationship->fromDisplayName() }}
                                </p>

                                @if (
                                    $relationship->from_character_source
                                        === \App\Models\OriginalCharacterRelationship::SOURCE_V1
                                    && $relationship->fromV1Character?->linkedWorks?->isNotEmpty()
                                )
                                    <p class="mt-2 text-xs font-bold text-[#A0AEC0]">
                                        {{ $relationship->fromV1Character->linkedWorks->pluck('title')->implode('／') }}
                                    </p>
                                @endif
                            </div>

                            <div class="text-center text-2xl font-bold text-[#A0AEC0]">
                                →
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-xs font-bold text-[#A0AEC0]">
                                        To
                                    </p>

                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-[#4A5568]">
                                        {{ $relationship->toSourceLabel() }}
                                    </span>
                                </div>

                                <p class="mt-2 text-2xl font-bold text-[#2D3748]">
                                    {{ $relationship->toDisplayName() }}
                                </p>

                                @if (
                                    $relationship->to_character_source
                                        === \App\Models\OriginalCharacterRelationship::SOURCE_V1
                                    && $relationship->toV1Character?->linkedWorks?->isNotEmpty()
                                )
                                    <p class="mt-2 text-xs font-bold text-[#A0AEC0]">
                                        {{ $relationship->toV1Character->linkedWorks->pluck('title')->implode('／') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 text-sm font-bold text-[#4A5568] md:grid-cols-3">
                        <div class="rounded-2xl bg-[#F7FAFC] px-4 py-3">
                            <p class="text-xs text-[#A0AEC0]">呼び方</p>
                            <p class="mt-1 text-[#2D3748]">{{ $relationship->called_name ?: '未入力' }}</p>
                        </div>

                        <div class="rounded-2xl bg-[#F7FAFC] px-4 py-3">
                            <p class="text-xs text-[#A0AEC0]">関係性</p>
                            <p class="mt-1 text-[#2D3748]">{{ $relationship->relationship_type ?: '未入力' }}</p>
                        </div>

                        <div class="rounded-2xl bg-[#F7FAFC] px-4 py-3">
                            <p class="text-xs text-[#A0AEC0]">更新日</p>
                            <p class="mt-1 text-[#2D3748]">{{ $relationship->updated_at?->format('Y/m/d H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2 xl:w-52 xl:flex-col">
                    <a href="{{ route('writer.original-character-relationships.show', $relationship) }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-4 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90">
                        詳細
                    </a>

                    <a href="{{ route('writer.original-character-relationships.edit', $relationship) }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                        編集
                    </a>

                    <form method="POST" action="{{ route('writer.original-character-relationships.duplicate', $relationship) }}">
                        @csrf
                        <button type="submit"
                                class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                            複製
                        </button>
                    </form>

                    <form method="POST"
                          action="{{ route('writer.original-character-relationships.destroy', $relationship) }}"
                          onsubmit="return confirm('この関係性を削除しますか？');">
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
            <p class="text-2xl font-bold text-[#2D3748]">関係性がまだありません。</p>
            <p class="mt-3 text-sm font-bold leading-7 text-[#A0AEC0]">
                キャラクター同士の呼び方や関係性を登録すると、プロンプトに反映しやすくなります。
            </p>

            <div class="mt-6 flex flex-col justify-center gap-3 md:flex-row">
                @if ($hasReachedRelationshipLimit)
                    <div class="inline-flex cursor-not-allowed items-center justify-center rounded-2xl bg-[#EDF2F7] px-6 py-3 font-bold text-[#A0AEC0]">
                        上限に達しています
                    </div>
                @else
                    <a href="{{ route('writer.original-character-relationships.create') }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                        関係性を登録する
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

@if (method_exists($relationshipItems, 'hasPages') && $relationshipItems->hasPages())
    <div class="mt-8">
        {{ $relationshipItems->links() }}
    </div>
@endif

@include('writer.original_characters._layout_end')
