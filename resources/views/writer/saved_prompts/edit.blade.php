@include('writer.original_characters._layout_start', ['title' => 'プロンプト編集'])

@php
    $prompt = $prompt ?? $savedPrompt ?? null;
@endphp

<div class="writer-form-ui">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#2D3748]">プロンプト編集</h1>
        <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
            保存済みプロンプトの条件や本文を編集します。
        </p>
    </div>

    @if (! $prompt)
        <div class="rounded-3xl border border-red-200 bg-white p-8 text-red-600">
            プロンプトデータが見つかりません。
        </div>
    @else
        <form method="POST" action="{{ route('writer.prompts.update', $prompt) }}" class="space-y-8" id="saved-prompt-form">
            @csrf
            @method('PUT')
            @include('writer.saved_prompts._form', ['prompt' => $prompt])
        </form>
    @endif
</div>

@include('writer.original_characters._layout_end')
