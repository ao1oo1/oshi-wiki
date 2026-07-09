@include('writer.original_characters._layout_start', ['title' => 'プロンプト新規作成'])

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">プロンプト管理</h2>
</div>

<div class="mb-6">
    <h3 class="text-2xl font-bold text-[#2D3748]">新規作成</h3>
    <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
        登録上限：{{ $limit === null ? '制限なし' : $limit . '件まで' }}
    </p>
</div>

<form method="POST" action="{{ route('writer.prompts.store') }}">
    @include('writer.saved_prompts._form')
</form>

@include('writer.original_characters._layout_end')
