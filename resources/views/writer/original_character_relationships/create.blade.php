@include('writer.original_characters._layout_start', ['title' => '関係性新規登録'])

<div class="writer-form-ui">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#2D3748]">関係性新規登録</h1>
        <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
            キャラクター同士の呼び方・関係性・印象を登録します。
        </p>
    </div>

    <form method="POST" action="{{ route('writer.original-character-relationships.store') }}" class="space-y-8">
        @csrf
        @include('writer.original_character_relationships._form')
    </form>
</div>

@include('writer.original_characters._layout_end')
