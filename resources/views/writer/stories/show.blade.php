@include('writer.original_characters._layout_start', ['title' => 'ストーリー詳細'])

<div class="mb-8">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-[#2D3748]">
                ストーリー詳細
            </h1>
            <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
                登録したストーリーを確認できます。
            </p>
        </div>
    </div>
</div>

<section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="mb-5 flex flex-wrap items-center gap-2">
        @if ($story->episode_number)
            <span class="rounded-full bg-[#FED7E2] px-3 py-1 text-xs font-bold text-[#2D3748]">
                第{{ number_format($story->episode_number) }}話
            </span>
        @endif

        <span class="rounded-full bg-[#F7FAFC] px-3 py-1 text-xs font-bold text-[#4A5568]">
            {{ $story->statusLabel() }}
        </span>
    </div>

    <h2 class="text-4xl font-bold leading-snug text-[#2D3748]">
        {{ $story->title }}
    </h2>

    <div class="mt-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl bg-[#F7FAFC] p-4">
            <p class="text-xs font-bold text-[#A0AEC0]">本文文字数</p>
            <p class="mt-2 font-bold text-[#2D3748]">
                {{ number_format($story->bodyLength()) }}文字
            </p>
        </div>

        <div class="rounded-2xl bg-[#F7FAFC] p-4">
            <p class="text-xs font-bold text-[#A0AEC0]">作成日</p>
            <p class="mt-2 font-bold text-[#2D3748]">
                {{ $story->created_at?->format('Y/m/d H:i') }}
            </p>
        </div>

        <div class="rounded-2xl bg-[#F7FAFC] p-4">
            <p class="text-xs font-bold text-[#A0AEC0]">更新日</p>
            <p class="mt-2 font-bold text-[#2D3748]">
                {{ $story->updated_at?->format('Y/m/d H:i') }}
            </p>
        </div>
    </div>
</section>

<section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <h3 class="mb-6 text-xl font-bold text-[#2D3748]">
        ストーリー本文
    </h3>

    <div class="whitespace-pre-wrap break-words rounded-2xl bg-[#F7FAFC] p-5 font-medium leading-8 text-[#2D3748]">
        {{ $story->body }}
    </div>
</section>

<section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <h3 class="mb-6 text-xl font-bold text-[#2D3748]">
        メモ
    </h3>

    <div class="whitespace-pre-wrap break-words rounded-2xl bg-[#FFF1F5] p-5 font-bold leading-8 text-[#2D3748]">
        {{ $story->memo ?: '未入力' }}
    </div>
</section>

<div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
    <div class="grid gap-3 md:grid-cols-3">
        <a
            href="{{ route('writer.stories.index') }}"
            class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]"
        >
            一覧へ戻る
        </a>

        <a
            href="{{ route('writer.stories.edit', $story) }}"
            class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90"
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
                class="w-full rounded-2xl border border-red-200 bg-white px-6 py-3 font-bold text-red-600 hover:bg-red-50"
            >
                削除
            </button>
        </form>
    </div>
</div>

@include('writer.original_characters._layout_end')
