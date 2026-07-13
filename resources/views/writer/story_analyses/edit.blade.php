@include('writer.original_characters._layout_start', ['title' => '文体分析の編集'])

<div class="writer-form-ui">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#2D3748]">
            文体分析の編集
        </h1>

        <p class="mt-3 text-sm font-bold leading-7 text-[#A0AEC0]">
            管理名、対象ストーリー、プロンプト、分析結果を編集できます。
        </p>
    </div>

    <form
        method="POST"
        action="{{ route('writer.story-analyses.update', $analysis) }}"
    >
        @csrf
        @method('PUT')

        @include('writer.story_analyses._form')
    </form>
</div>

@include('writer.original_characters._layout_end')
