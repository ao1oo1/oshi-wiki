@include('writer.original_characters._layout_start', ['title' => '関係性編集'])

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">関係性</h2>
</div>

<div class="mb-6">
    <h3 class="text-2xl font-bold text-[#2D3748]">編集</h3>
</div>

<form method="POST" action="{{ route('writer.original-character-relationships.update', $relationship) }}" class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    @method('PATCH')
    @include('writer.original_character_relationships._form')
</form>

@include('writer.original_characters._layout_end')
