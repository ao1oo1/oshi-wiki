@include('writer.original_characters._layout_start', ['title' => $savedPrompt->title])

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">保存プロンプト</h2>
</div>

<div class="mb-6 flex flex-col justify-between gap-4 xl:flex-row xl:items-start">
    <div>
        <p class="text-sm font-bold text-[#A0AEC0]">生成済みプロンプト</p>
        <h3 class="mt-1 text-3xl font-bold text-[#2D3748]">{{ $savedPrompt->title }}</h3>
        <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
            {{ $savedPrompt->workLabel() }} / {{ $savedPrompt->writingStyleLabel() }} / {{ $savedPrompt->genreLabel() }}
        </p>
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="button"
                onclick="copyPromptBody()"
                class="rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] shadow-sm hover:opacity-90">
            全文コピー
        </button>

        <a href="{{ route('writer.prompts.edit', $savedPrompt) }}"
           class="rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
            編集
        </a>

        <form method="POST" action="{{ route('writer.prompts.duplicate', $savedPrompt) }}">
            @csrf
            <button type="submit"
                    class="rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                複製
            </button>
        </form>

        <a href="{{ route('writer.prompts.index') }}"
           class="rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
            一覧へ戻る
        </a>

        <form method="POST" action="{{ route('writer.prompts.destroy', $savedPrompt) }}" onsubmit="return confirm('このプロンプトを削除しますか？');">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-2xl border border-red-300 bg-white px-6 py-3 font-bold text-red-600 hover:bg-red-50">
                削除
            </button>
        </form>
    </div>
</div>

<div id="copy-message" class="mb-6 hidden rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-bold text-green-700">
    プロンプト本文をコピーしました。
</div>

<div class="grid gap-6 xl:grid-cols-[360px_1fr]">
    <aside class="space-y-6">
        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h4 class="text-lg font-bold text-[#2D3748]">生成条件</h4>

            <dl class="mt-5 space-y-4">
                <div>
                    <dt class="text-xs font-bold text-[#A0AEC0]">作品</dt>
                    <dd class="mt-1 font-bold text-[#2D3748]">{{ $savedPrompt->workLabel() }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-[#A0AEC0]">作風</dt>
                    <dd class="mt-1 font-bold text-[#2D3748]">{{ $savedPrompt->writingStyleLabel() }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-[#A0AEC0]">ジャンル</dt>
                    <dd class="mt-1 font-bold text-[#2D3748]">{{ $savedPrompt->genreLabel() }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-[#A0AEC0]">用途</dt>
                    <dd class="mt-1 text-sm leading-7 text-[#4A5568]">{{ $savedPrompt->purpose ?: '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-[#A0AEC0]">状態</dt>
                    <dd class="mt-1">
                        <span class="rounded-full bg-[#FFF1F5] px-3 py-1 text-xs font-bold text-[#2D3748]">
                            {{ $savedPrompt->status === 'active' ? '有効' : '下書き' }}
                        </span>
                    </dd>
                </div>
            </dl>
        </section>

        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h4 class="text-lg font-bold text-[#2D3748]">あらすじ</h4>
            <div class="mt-4 whitespace-pre-line text-sm leading-7 text-[#4A5568]">
                {{ $savedPrompt->synopsis ?: '-' }}
            </div>
        </section>

        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h4 class="text-lg font-bold text-[#2D3748]">備考</h4>
            <div class="mt-4 whitespace-pre-line text-sm leading-7 text-[#4A5568]">
                {{ $savedPrompt->notes ?: '-' }}
            </div>
        </section>
    </aside>

    <div class="space-y-6">
        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-bold text-[#A0AEC0]">Plot</p>
                    <h4 class="mt-1 text-xl font-bold text-[#2D3748]">起承転結</h4>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="font-bold text-[#2D3748]">起</p>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-[#4A5568]">{{ $savedPrompt->plot_opening ?: '-' }}</p>
                </div>

                <div class="rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="font-bold text-[#2D3748]">承</p>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-[#4A5568]">{{ $savedPrompt->plot_development ?: '-' }}</p>
                </div>

                <div class="rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="font-bold text-[#2D3748]">転</p>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-[#4A5568]">{{ $savedPrompt->plot_turn ?: '-' }}</p>
                </div>

                <div class="rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="font-bold text-[#2D3748]">結</p>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-[#4A5568]">{{ $savedPrompt->plot_conclusion ?: '-' }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
            <div class="mb-5 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <p class="text-sm font-bold text-[#A0AEC0]">Generated Prompt</p>
                    <h4 class="mt-1 text-xl font-bold text-[#2D3748]">生成されたプロンプト本文</h4>
                    <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
                        この本文をコピーして、AIチャットに貼り付けて使用します。
                    </p>
                </div>

                <button type="button"
                        onclick="copyPromptBody()"
                        class="rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] shadow-sm hover:opacity-90">
                    本文をコピー
                </button>
            </div>

            <textarea id="prompt-body"
                      readonly
                      rows="30"
                      class="w-full rounded-2xl border-[#E2E8F0] bg-[#F7FAFC] px-5 py-4 text-sm leading-7 text-[#2D3748] shadow-inner focus:border-[#FED7E2] focus:ring-[#FED7E2]">{{ $savedPrompt->prompt_body }}</textarea>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button type="button"
                        onclick="selectPromptBody()"
                        class="rounded-2xl border border-[#CBD5E0] bg-white px-5 py-2 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                    本文を全選択
                </button>

                <button type="button"
                        onclick="copyPromptBody()"
                        class="rounded-2xl border border-[#CBD5E0] bg-white px-5 py-2 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                    コピー
                </button>
            </div>
        </section>
    </div>
</div>

<script>
    function promptTextarea() {
        return document.getElementById('prompt-body');
    }

    function showCopyMessage() {
        const message = document.getElementById('copy-message');

        if (!message) {
            return;
        }

        message.classList.remove('hidden');

        setTimeout(() => {
            message.classList.add('hidden');
        }, 2500);
    }

    function selectPromptBody() {
        const textarea = promptTextarea();

        if (!textarea) {
            return;
        }

        textarea.focus();
        textarea.select();
        textarea.setSelectionRange(0, 999999);
    }

    function copyPromptBody() {
        const textarea = promptTextarea();

        if (!textarea) {
            return;
        }

        const text = textarea.value;

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                showCopyMessage();
            }).catch(() => {
                selectPromptBody();
                document.execCommand('copy');
                showCopyMessage();
            });

            return;
        }

        selectPromptBody();
        document.execCommand('copy');
        showCopyMessage();
    }
</script>

@include('writer.original_characters._layout_end')
