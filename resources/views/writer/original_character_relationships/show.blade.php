@include('writer.original_characters._layout_start', ['title' => '関係性詳細'])

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">関係性</h2>
</div>

<div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
    <div>
        <h3 class="text-2xl font-bold text-[#2D3748]">
            {{ $relationship->fromDisplayName() }} → {{ $relationship->toDisplayName() }}
        </h3>
        <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
            {{ $relationship->relationship_type ?: '関係性未設定' }}
        </p>
    </div>

    <div class="flex gap-2">
        <a href="{{ route('writer.original-character-relationships.edit', $relationship) }}"
           class="rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748]">
            編集
        </a>

        <form method="POST" action="{{ route('writer.original-character-relationships.destroy', $relationship) }}" onsubmit="return confirm('この関係性を削除しますか？');">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-2xl border border-red-300 bg-white px-6 py-3 font-bold text-red-600">
                削除
            </button>
        </form>
    </div>
</div>

<div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <dl class="grid gap-5 md:grid-cols-2">
        <div>
            <dt class="text-sm font-bold text-[#A0AEC0]">キャラクター</dt>
            <dd class="mt-1 text-lg font-bold">{{ $relationship->fromDisplayName() }}</dd>
        </div>

        <div>
            <dt class="text-sm font-bold text-[#A0AEC0]">相手キャラクター</dt>
            <dd class="mt-1 text-lg font-bold">{{ $relationship->toDisplayName() }}</dd>
        </div>

        <div>
            <dt class="text-sm font-bold text-[#A0AEC0]">キャラクター種別</dt>
            <dd class="mt-1">{{ $relationship->fromSourceLabel() }}</dd>
        </div>

        <div>
            <dt class="text-sm font-bold text-[#A0AEC0]">相手種別</dt>
            <dd class="mt-1">{{ $relationship->toSourceLabel() }}</dd>
        </div>

        <div>
            <dt class="text-sm font-bold text-[#A0AEC0]">呼び方</dt>
            <dd class="mt-1">{{ $relationship->called_name ?: '-' }}</dd>
        </div>

        <div>
            <dt class="text-sm font-bold text-[#A0AEC0]">状態</dt>
            <dd class="mt-1">{{ $relationship->status === 'active' ? '有効' : '下書き' }}</dd>
        </div>
    </dl>

    <section class="mt-8 border-t border-[#E2E8F0] pt-6">
        <h4 class="text-lg font-bold">印象・気持ち</h4>
        <div class="mt-3 whitespace-pre-line text-sm leading-7 text-[#4A5568]">{{ $relationship->impression ?: '-' }}</div>
    </section>

    <section class="mt-8 border-t border-[#E2E8F0] pt-6">
        <h4 class="text-lg font-bold">備考</h4>
        <div class="mt-3 whitespace-pre-line text-sm leading-7 text-[#4A5568]">{{ $relationship->notes ?: '-' }}</div>
    </section>
</div>

<div class="mt-6">
    <a href="{{ route('writer.original-character-relationships.index') }}"
       class="rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
        一覧へ戻る
    </a>
</div>

@include('writer.original_characters._layout_end')
