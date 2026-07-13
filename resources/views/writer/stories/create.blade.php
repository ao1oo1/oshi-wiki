@include('writer.original_characters._layout_start', ['title' => 'ストーリー新規登録'])

@php
    $storyCount = $count ?? 0;
    $storyLimit = $limit ?? null;
    $hasReachedStoryLimit = $storyLimit !== null && $storyCount >= $storyLimit;
@endphp

<div class="writer-form-ui">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#2D3748]">
            ストーリー新規登録
        </h1>
        <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
            これまでに書いたストーリーを登録します。
        </p>
    </div>

    @if ($hasReachedStoryLimit)
        <section class="rounded-3xl border border-[#FED7E2] bg-[#FFF1F5] p-8 shadow-sm">
            <p class="text-sm font-bold text-[#A0AEC0]">
                登録上限に達しています
            </p>

            <h2 class="mt-2 text-2xl font-bold text-[#2D3748]">
                ストーリーは
                {{ number_format($storyCount) }}
                /
                {{ number_format($storyLimit) }}
                件です。
            </h2>

            <p class="mt-4 text-sm font-bold leading-7 text-[#718096]">
                一般ユーザーが登録できるストーリーは最大{{ number_format($storyLimit) }}件です。
                新しく登録する場合は、不要なストーリーを削除してください。
            </p>

            <div class="mt-6">
                <a
                    href="{{ route('writer.stories.index') }}"
                    class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90"
                >
                    一覧へ戻る
                </a>
            </div>
        </section>
    @else
        <form
            method="POST"
            action="{{ route('writer.stories.store') }}"
            class="space-y-8"
        >
            @csrf
            @include('writer.stories._form')
        </form>
    @endif
</div>

@include('writer.original_characters._layout_end')
