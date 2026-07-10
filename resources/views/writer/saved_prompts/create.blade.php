@include('writer.original_characters._layout_start', ['title' => 'プロンプト新規作成'])

@php
    $currentUser = auth()->user();

    $promptLimit = $currentUser
        ? \App\Support\WritingAssistLimits::promptsPerUser($currentUser)
        : null;

    $promptCount = $currentUser
        ? \App\Models\SavedPrompt::query()->where('user_id', $currentUser->id)->count()
        : 0;

    $hasReachedPromptLimit = $promptLimit !== null && $promptCount >= $promptLimit;
@endphp

<div class="writer-form-ui">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#2D3748]">プロンプト新規作成</h1>
        <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
            登場人物・作風・ジャンル・あらすじを指定して、AIに渡すためのプロンプトを作成します。
        </p>
    </div>

    @if ($hasReachedPromptLimit)
        <div class="rounded-3xl border border-[#E2E8F0] bg-white p-8 shadow-sm">
            <div class="rounded-3xl bg-[#FFF1F5] p-6">
                <p class="text-sm font-bold text-[#A0AEC0]">登録上限に達しています</p>
                <h2 class="mt-2 text-2xl font-bold text-[#2D3748]">
                    プロンプト管理は {{ number_format($promptCount) }} / {{ number_format($promptLimit) }} 件です。
                </h2>
                <p class="mt-4 text-sm font-bold leading-7 text-[#718096]">
                    一般会員が保存できるプロンプトは最大{{ number_format($promptLimit) }}件までです。
                    新しく登録する場合は、不要なデータを削除してください。
                </p>
            </div>

            <div class="mt-6">
                <a href="{{ route('writer.prompts.index') }}"
                   class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                    一覧へ戻る
                </a>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('writer.prompts.store') }}" class="space-y-8" id="saved-prompt-form">
            @csrf
            @include('writer.saved_prompts._form')
        </form>
    @endif
</div>

@include('writer.original_characters._layout_end')
