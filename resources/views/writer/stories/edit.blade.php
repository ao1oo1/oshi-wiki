@include('writer.original_characters._layout_start', ['title' => 'ストーリー編集'])

<div class="writer-form-ui">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#2D3748]">
            ストーリー編集
        </h1>
        <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
            登録済みのストーリーを編集します。
        </p>
    </div>

    <form
        method="POST"
        action="{{ route('writer.stories.update', $story) }}"
        class="space-y-8"
    >
        @csrf
        @method('PUT')
        @include('writer.stories._form', ['story' => $story])
    </form>
</div>

@include('writer.original_characters._layout_end')
