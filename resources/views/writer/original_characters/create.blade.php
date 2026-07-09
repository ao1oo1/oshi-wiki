@include('writer.original_characters._layout_start', ['title' => 'オリジナルキャラクター新規登録'])

<div class="writer-form-ui">

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">オリジナルキャラクター</h2>
</div>

<div class="mb-6">
    <h3 class="text-2xl font-bold text-[#2D3748]">新規登録</h3>
    <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
        登録上限：{{ $limit === null ? '制限なし' : $limit . '件まで' }}
    </p>
</div>

<form data-form-screen-card-added="true" class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8 space-y-6" method="POST" action="{{ route('writer.original-characters.store') }}" class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    @include('writer.original_characters._form')
</form>

</div>
@include('writer.original_characters._layout_end')
