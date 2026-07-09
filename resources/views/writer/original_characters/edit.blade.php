@include('writer.original_characters._layout_start', ['title' => 'オリジナルキャラクター編集'])

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">オリジナルキャラクター</h2>
</div>

<div class="mb-6">
    <h3 class="text-2xl font-bold text-[#2D3748]">編集</h3>
</div>

<form method="POST" action="{{ route('writer.original-characters.update', $originalCharacter) }}" class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    @method('PATCH')
    @include('writer.original_characters._form')
</form>

@include('writer.original_characters._layout_end')
