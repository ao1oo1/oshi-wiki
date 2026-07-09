@include('writer.original_characters._layout_start', ['title' => $savedPrompt->title])

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">保存プロンプト</h2>
</div>

<div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
    <div>
        <h3 class="text-2xl font-bold text-[#2D3748]">{{ $savedPrompt->title }}</h3>
        <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
            {{ $savedPrompt->workLabel() }} / {{ $savedPrompt->writingStyleLabel() }} / {{ $savedPrompt->genreLabel() }}
        </p>
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="button"
                onclick="copyPromptBody()"
                class="rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748]">
            本文をコピー
        </button>

        <a href="{{ route('writer.prompts.edit', $savedPrompt) }}"
           class="rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748]">
            編集
        </a>

        <form method="POST" action="{{ route('writer.prompts.destroy', $savedPrompt) }}" onsubmit="return confirm('このプロンプトを削除しますか？');">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-2xl border border-red-300 bg-white px-6 py-3 font-bold text-red-600">
                削除
            </button>
        </form>
    </div>
</div>

<div id="copy-message" class="mb-6 hidden rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-bold text-green-700">
    プロンプト本文をコピーしました。
</div>

<div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <dl class="grid gap-5 md:grid-cols-2">
        <div>
            <dt class="text-sm font-bold text-[#A0AEC0]">作品</dt>
            <dd class="mt-1">{{ $savedPrompt->workLabel() }}</dd>
        </div>

        <div>
            <dt class="text-sm font-bold text-[#A0AEC0]">作風</dt>
            <dd class="mt-1">{{ $savedPrompt->writingStyleLabel() }}</dd>
        </div>

        <div>
            <dt class="text-sm font-bold text-[#A0AEC0]">ジャンル</dt>
            <dd class="mt-1">{{ $savedPrompt->genreLabel() }}</dd>
        </div>

        <div>
            <dt class="text-sm font-bold text-[#A0AEC0]">状態</dt>
            <dd class="mt-1">{{ $savedPrompt->status === 'active' ? '有効' : '下書き' }}</dd>
        </div>
    </dl>

    <section class="mt-8 border-t border-[#E2E8F0] pt-6">
        <h4 class="text-lg font-bold">あらすじ</h4>
        <div class="mt-3 whitespace-pre-line text-sm leading-7 text-[#4A5568]">{{ $savedPrompt->synopsis ?: '-' }}</div>
    </section>

    <section class="mt-8 border-t border-[#E2E8F0] pt-6">
        <h4 class="text-lg font-bold">起承転結</h4>
        <div class="mt-3 grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl bg-[#F7FAFC] p-4">
                <p class="font-bold">起</p>
                <p class="mt-2 whitespace-pre-line text-sm leading-7">{{ $savedPrompt->plot_opening ?: '-' }}</p>
            </div>
            <div class="rounded-2xl bg-[#F7FAFC] p-4">
                <p class="font-bold">承</p>
                <p class="mt-2 whitespace-pre-line text-sm leading-7">{{ $savedPrompt->plot_development ?: '-' }}</p>
            </div>
            <div class="rounded-2xl bg-[#F7FAFC] p-4">
                <p class="font-bold">転</p>
                <p class="mt-2 whitespace-pre-line text-sm leading-7">{{ $savedPrompt->plot_turn ?: '-' }}</p>
            </div>
            <div class="rounded-2xl bg-[#F7FAFC] p-4">
                <p class="font-bold">結</p>
                <p class="mt-2 whitespace-pre-line text-sm leading-7">{{ $savedPrompt->plot_conclusion ?: '-' }}</p>
            </div>
        </div>
    </section>

    <section class="mt-8 border-t border-[#E2E8F0] pt-6">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h4 class="text-lg font-bold">生成されたプロンプト本文</h4>
            <button type="button"
                    onclick="copyPromptBody()"
                    class="rounded-xl border border-[#CBD5E0] bg-white px-4 py-2 text-sm font-bold text-[#2D3748]">
                コピー
            </button>
        </div>

        <textarea id="prompt-body" readonly rows="22" class="w-full rounded-2xl border-[#E2E8F0] bg-[#F7FAFC] px-4 py-3 text-sm leading-7 text-[#2D3748]">{{ $savedPrompt->prompt_body }}</textarea>
    </section>
</div>

<div class="mt-6">
    <a href="{{ route('writer.prompts.index') }}"
       class="rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
        一覧へ戻る
    </a>
</div>

<script>
    function copyPromptBody() {
        const textarea = document.getElementById('prompt-body');
        const message = document.getElementById('copy-message');

        textarea.select();
        textarea.setSelectionRange(0, 999999);

        navigator.clipboard.writeText(textarea.value).then(() => {
            message.classList.remove('hidden');
            setTimeout(() => message.classList.add('hidden'), 2500);
        }).catch(() => {
            document.execCommand('copy');
            message.classList.remove('hidden');
            setTimeout(() => message.classList.add('hidden'), 2500);
        });
    }
</script>

@include('writer.original_characters._layout_end')
