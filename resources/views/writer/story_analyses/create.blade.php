@include('writer.original_characters._layout_start', ['title' => '文体分析の新規登録'])

<div class="writer-form-ui">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#2D3748]">
            文体分析の新規登録
        </h1>

        <p class="mt-3 text-sm font-bold leading-7 text-[#A0AEC0]">
            ストーリーを選択してプロンプトを作成し、文体分析として保存します。
        </p>
    </div>

    <form
        method="POST"
        action="{{ route('writer.story-analyses.store') }}"
    >
        @csrf

        @include('writer.story_analyses._form')
    </form>
</div>

@include('writer.original_characters._layout_end')
