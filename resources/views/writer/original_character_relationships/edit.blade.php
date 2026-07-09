@include('writer.original_characters._layout_start', ['title' => '関係性編集'])

@php
    $relationship = $relationship ?? $originalCharacterRelationship ?? null;
@endphp

<div class="writer-form-ui">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#2D3748]">関係性編集</h1>
        <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
            登録済みの関係性を編集します。
        </p>
    </div>

    @if (! $relationship)
        <div class="rounded-3xl border border-red-200 bg-white p-8 text-red-600">
            関係性データが見つかりません。
        </div>
    @else
        <form method="POST" action="{{ route('writer.original-character-relationships.update', $relationship) }}" class="space-y-8">
            @csrf
            @method('PUT')
            @include('writer.original_character_relationships._form', ['relationship' => $relationship])
        </form>
    @endif
</div>

@include('writer.original_characters._layout_end')
