@include('writer.original_characters._layout_start', ['title' => 'オリジナルキャラクター新規登録'])

<div class="writer-form-ui">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#2D3748]">オリジナルキャラクター新規登録</h1>
        <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
            小説作成に使うオリジナルキャラクター情報を登録します。
        </p>
    </div>

    <form method="POST" action="{{ route('writer.original-characters.store') }}" class="space-y-8">
        @csrf
        @include('writer.original_characters._form')
    </form>
</div>

@include('writer.original_characters._layout_end')
