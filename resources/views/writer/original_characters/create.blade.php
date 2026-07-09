@include('writer.original_characters._layout_start', ['title' => 'オリジナルキャラクター新規登録'])

@php
    $currentUser = auth()->user();

    $characterLimit = $currentUser
        ? \App\Support\WritingAssistLimits::originalCharactersPerUser($currentUser)
        : null;

    $characterCount = $currentUser
        ? \App\Models\OriginalCharacter::query()->where('user_id', $currentUser->id)->count()
        : 0;

    $hasReachedCharacterLimit = $characterLimit !== null && $characterCount >= $characterLimit;
@endphp

<div class="writer-form-ui">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#2D3748]">オリジナルキャラクター新規登録</h1>
        <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
            小説作成に使うオリジナルキャラクター情報を登録します。
        </p>
    </div>

    @if ($hasReachedCharacterLimit)
        <div class="rounded-3xl border border-[#E2E8F0] bg-white p-8 shadow-sm">
            <div class="rounded-3xl bg-[#FFF1F5] p-6">
                <p class="text-sm font-bold text-[#A0AEC0]">登録上限に達しています</p>
                <h2 class="mt-2 text-2xl font-bold text-[#2D3748]">
                    オリジナルキャラクターは {{ number_format($characterCount) }} / {{ number_format($characterLimit) }} 件です。
                </h2>
                <p class="mt-4 text-sm font-bold leading-7 text-[#718096]">
                    一般会員が登録できるオリジナルキャラクターは最大{{ number_format($characterLimit) }}件までです。
                    新しく登録する場合は、不要なデータを削除してください。
                </p>
            </div>

            <div class="mt-6">
                <a href="{{ route('writer.original-characters.index') }}"
                   class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                    一覧へ戻る
                </a>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('writer.original-characters.store') }}" class="space-y-8">
            @csrf
            @include('writer.original_characters._form')
        </form>
    @endif
</div>

@include('writer.original_characters._layout_end')
