@include('writer.original_characters._layout_start', ['title' => '関係性新規登録'])

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">関係性</h2>
</div>

<div class="mb-6">
    <h3 class="text-2xl font-bold text-[#2D3748]">新規登録</h3>
    <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
        登録上限：{{ $limit === null ? '制限なし' : $limit . '件まで' }}
    </p>
</div>

<form method="POST" action="{{ route('writer.original-character-relationships.store') }}" class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    @include('writer.original_character_relationships._form')
</form>

@include('writer.original_characters._layout_end')
