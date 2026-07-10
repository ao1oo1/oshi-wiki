@include('writer.original_characters._layout_start', ['title' => '関係性新規登録'])

@php
    $currentUser = auth()->user();

    $relationshipLimit = $currentUser
        ? \App\Support\WritingAssistLimits::relationshipsPerUser($currentUser)
        : null;

    $relationshipCount = $currentUser
        ? \App\Models\OriginalCharacterRelationship::query()->where('user_id', $currentUser->id)->count()
        : 0;

    $hasReachedRelationshipLimit = $relationshipLimit !== null && $relationshipCount >= $relationshipLimit;
@endphp

<div class="writer-form-ui">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#2D3748]">関係性新規登録</h1>
        <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
            キャラクター同士の呼び方・関係性・印象を登録します。
        </p>
    </div>

    @if ($hasReachedRelationshipLimit)
        <div class="rounded-3xl border border-[#E2E8F0] bg-white p-8 shadow-sm">
            <div class="rounded-3xl bg-[#FFF1F5] p-6">
                <p class="text-sm font-bold text-[#A0AEC0]">登録上限に達しています</p>
                <h2 class="mt-2 text-2xl font-bold text-[#2D3748]">
                    関係性は {{ number_format($relationshipCount) }} / {{ number_format($relationshipLimit) }} 件です。
                </h2>
                <p class="mt-4 text-sm font-bold leading-7 text-[#718096]">
                    一般会員が登録できる関係性は最大{{ number_format($relationshipLimit) }}件までです。
                    新しく登録する場合は、不要なデータを削除してください。
                </p>
            </div>

            <div class="mt-6">
                <a href="{{ route('writer.original-character-relationships.index') }}"
                   class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                    一覧へ戻る
                </a>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('writer.original-character-relationships.store') }}" class="space-y-8">
            @csrf
            @include('writer.original_character_relationships._form')
        </form>
    @endif
</div>

@include('writer.original_characters._layout_end')
