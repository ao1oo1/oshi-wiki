@include('writer.original_characters._layout_start', ['title' => 'オリジナルキャラクター編集'])

@php
    $character = $character ?? $originalCharacter ?? null;
@endphp

<div class="writer-form-ui">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#2D3748]">オリジナルキャラクター編集</h1>
        <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
            登録済みのオリジナルキャラクター情報を編集します。
        </p>
    </div>

    @if (! $character)
        <div class="rounded-3xl border border-red-200 bg-white p-8 text-red-600">
            キャラクターデータが見つかりません。
        </div>
    @else
        <form method="POST" action="{{ route('writer.original-characters.update', $character) }}" class="space-y-8">
            @csrf
            @method('PUT')
            @include('writer.original_characters._form', ['character' => $character])
        </form>
    @endif
</div>

@include('writer.original_characters._layout_end')
