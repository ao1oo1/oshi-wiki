@include('writer.original_characters._layout_start', ['title' => '関係性詳細'])

@php
    $relationship = $relationship
        ?? $originalCharacterRelationship
        ?? null;
@endphp

@if (! $relationship)
    <div class="rounded-3xl border border-red-200 bg-white p-8 text-red-600">
        関係性データが見つかりません。
    </div>
@else
    <div class="mb-8">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-[#2D3748]">関係性詳細</h1>
                <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
                    キャラクター同士の呼び方・関係性・印象を確認できます。
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('writer.original-character-relationships.index') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-5 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                    一覧へ戻る
                </a>

                <a href="{{ route('writer.original-character-relationships.edit', $relationship) }}"
                   class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90">
                    編集
                </a>

                <form method="POST" action="{{ route('writer.original-character-relationships.duplicate', $relationship) }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-5 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                        複製
                    </button>
                </form>

                <form method="POST"
                      action="{{ route('writer.original-character-relationships.destroy', $relationship) }}"
                      class="inline"
                      onsubmit="return confirm('この関係性を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-white px-5 py-3 text-sm font-bold text-red-600 hover:bg-red-50">
                        削除
                    </button>
                </form>
            </div>
        </div>
    </div>

    <section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-5 flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-[#FFF1F5] px-3 py-1 text-xs font-bold text-[#2D3748]">
                {{ method_exists($relationship, 'fromSourceLabel') ? $relationship->fromSourceLabel() : 'From' }}
            </span>

            <span class="rounded-full bg-[#F7FAFC] px-3 py-1 text-xs font-bold text-[#4A5568]">
                →
            </span>

            <span class="rounded-full bg-[#FFF1F5] px-3 py-1 text-xs font-bold text-[#2D3748]">
                {{ method_exists($relationship, 'toSourceLabel') ? $relationship->toSourceLabel() : 'To' }}
            </span>

            @if (($relationship->status ?? 'active') === 'active')
                <span class="rounded-full bg-[#FED7E2] px-3 py-1 text-xs font-bold text-[#2D3748]">
                    有効
                </span>
            @else
                <span class="rounded-full bg-[#EDF2F7] px-3 py-1 text-xs font-bold text-[#4A5568]">
                    {{ $relationship->status }}
                </span>
            @endif
        </div>

        <div class="rounded-3xl bg-[#F7FAFC] p-6">
            <div class="grid gap-6 md:grid-cols-[1fr_auto_1fr] md:items-center">
                <div>
                    <p class="text-xs font-bold text-[#A0AEC0]">From</p>
                    <p class="mt-2 text-2xl font-bold text-[#2D3748]">
                        {{ method_exists($relationship, 'fromDisplayName') ? $relationship->fromDisplayName() : '未設定' }}
                    </p>
                </div>

                <div class="text-center text-3xl font-bold text-[#A0AEC0]">
                    →
                </div>

                <div>
                    <p class="text-xs font-bold text-[#A0AEC0]">To</p>
                    <p class="mt-2 text-2xl font-bold text-[#2D3748]">
                        {{ method_exists($relationship, 'toDisplayName') ? $relationship->toDisplayName() : '未設定' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <h2 class="mb-6 text-xl font-bold text-[#2D3748]">登録内容</h2>

        <div class="grid gap-5 md:grid-cols-2">
            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">呼び方</p>
                <p class="mt-2 font-bold text-[#2D3748]">{{ $relationship->called_name ?: '未入力' }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">関係性</p>
                <p class="mt-2 font-bold text-[#2D3748]">{{ $relationship->relationship_type ?: '未入力' }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5 md:col-span-2">
                <p class="text-xs font-bold text-[#A0AEC0]">印象・気持ち</p>
                <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $relationship->impression ?: '未入力' }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5 md:col-span-2">
                <p class="text-xs font-bold text-[#A0AEC0]">備考</p>
                <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $relationship->notes ?: '未入力' }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">作成日</p>
                <p class="mt-2 font-bold text-[#2D3748]">{{ $relationship->created_at?->format('Y/m/d H:i') }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">更新日</p>
                <p class="mt-2 font-bold text-[#2D3748]">{{ $relationship->updated_at?->format('Y/m/d H:i') }}</p>
            </div>
        </div>
    </section>
@endif

@include('writer.original_characters._layout_end')
