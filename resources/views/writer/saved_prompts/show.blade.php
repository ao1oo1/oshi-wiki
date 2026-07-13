@include('writer.original_characters._layout_start', ['title' => 'プロンプト詳細'])

@php
    $prompt = $prompt
        ?? $savedPrompt
        ?? null;

    if (
        $prompt
        && $prompt->work_source
            === \App\Models\SavedPrompt::WORK_SOURCE_V1
    ) {
        $prompt->loadMissing('work');
    }
@endphp

@if (! $prompt)
    <div class="rounded-3xl border border-red-200 bg-white p-8 text-red-600">
        プロンプトデータが見つかりません。
    </div>
@else
    <div class="mb-8">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-[#2D3748]">プロンプト詳細</h1>
                <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
                    作成したプロンプトの条件と本文を確認・コピーできます。
                </p>
            </div>

            <div class="writer-prompt-top-actions-hidden flex flex-wrap gap-3">
                <a href="{{ route('writer.prompts.index') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-5 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                    一覧へ戻る
                </a>

                <a href="{{ route('writer.prompts.edit', $prompt) }}"
                   class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90">
                    編集
                </a>

                <form method="POST" action="{{ route('writer.prompts.duplicate', $prompt) }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-5 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                        複製
                    </button>
                </form>

                <form method="POST"
                      action="{{ route('writer.prompts.destroy', $prompt) }}"
                      class="inline"
                      onsubmit="return confirm('このプロンプトを削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-white px-5 py-3 text-sm font-bold text-red-600 hover:bg-red-50">
                        削除
                    </button>
                </form>
            </div>
        </div>
    </div>

    <section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-5 flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-[#FFF1F5] px-3 py-1 text-xs font-bold text-[#2D3748]">
                {{ $prompt->workLabel() }}
            </span>

            <span class="rounded-full bg-[#F7FAFC] px-3 py-1 text-xs font-bold text-[#4A5568]">
                {{ $prompt->writingStyleLabel() ?: '作風指定なし' }}
            </span>

            <span class="rounded-full bg-[#F7FAFC] px-3 py-1 text-xs font-bold text-[#4A5568]">
                {{ $prompt->genreLabel() ?: 'ジャンル指定なし' }}
            </span>

            @if ($prompt->include_relationship_timeline)
                <span class="rounded-full bg-[#FFF1F5] px-3 py-1 text-xs font-bold text-[#2D3748]">
                    年表データ反映
                </span>
            @endif

            @if ($prompt->use_story_length_options)
                <span class="rounded-full bg-[#FED7E2] px-3 py-1 text-xs font-bold text-[#2D3748]">
                    {{ $prompt->storyLengthLabel() }}
                </span>
            @endif

            @if (($prompt->status ?? 'active') === 'draft')
                <span class="rounded-full bg-[#EDF2F7] px-3 py-1 text-xs font-bold text-[#4A5568]">
                    下書き
                </span>
            @else
                <span class="rounded-full bg-[#FED7E2] px-3 py-1 text-xs font-bold text-[#2D3748]">
                    有効
                </span>
            @endif
        </div>

        <h2 class="text-4xl font-bold leading-snug text-[#2D3748]">
            {{ $prompt->title }}
        </h2>

        @if ($prompt->purpose)
            <p class="mt-4 whitespace-pre-wrap text-sm font-bold leading-7 text-[#4A5568]">
                {{ $prompt->purpose }}
            </p>
        @endif
    </section>

    <section class="mb-8 grid gap-6 md:grid-cols-3">
        <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <p class="text-sm font-bold text-[#A0AEC0]">利用回数</p>
            <div id="used-count-display" class="mt-3 text-4xl font-bold text-[#2D3748]">
                {{ number_format($prompt->used_count ?? 0) }}回
            </div>
        </div>

        <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <p class="text-sm font-bold text-[#A0AEC0]">最終利用</p>
            <div id="last-used-display" class="mt-3 text-2xl font-bold text-[#2D3748]">
                {{ $prompt->lastUsedLabel() }}
            </div>
        </div>

        <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <p class="text-sm font-bold text-[#A0AEC0]">更新日</p>
            <div class="mt-3 text-2xl font-bold text-[#2D3748]">
                {{ $prompt->updated_at?->format('Y/m/d H:i') }}
            </div>
        </div>
    </section>

    <section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <h3 class="mb-6 text-xl font-bold text-[#2D3748]">作成条件</h3>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">作品</p>
                <p class="mt-2 font-bold text-[#2D3748]">{{ $prompt->workLabel() }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">作風</p>
                <p class="mt-2 font-bold text-[#2D3748]">{{ $prompt->writingStyleLabel() ?: '未指定' }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">ジャンル</p>
                <p class="mt-2 font-bold text-[#2D3748]">{{ $prompt->genreLabel() ?: '未指定' }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">ステータス</p>
                <p class="mt-2 font-bold text-[#2D3748]">
                    {{ ($prompt->status ?? 'active') === 'draft' ? '下書き' : '有効' }}
                </p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">関係性年表</p>
                <p class="mt-2 font-bold text-[#2D3748]">
                    {{ $prompt->include_relationship_timeline ? 'プロンプトに反映する' : '反映しない' }}
                </p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">
                    長編・短編設定
                </p>
                <p class="mt-2 font-bold text-[#2D3748]">
                    {{ $prompt->storyLengthLabel() }}
                </p>

                @if ($prompt->use_story_length_options)
                    <div class="mt-3 space-y-1 text-sm font-bold text-[#718096]">
                        <p>
                            詳細プロット：
                            {{ $prompt->output_plot_first ? '先に出力する' : '指定なし' }}
                        </p>
                        <p>
                            起承転結：
                            {{ $prompt->output_in_parts ? '順番に分ける' : '指定なし' }}
                        </p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">
                    保存済み文体分析
                </p>

                <p class="mt-2 font-bold text-[#2D3748]">
                    {{
                        number_format(
                            count(
                                $prompt->selected_story_analysis_ids
                                    ?? []
                            )
                        )
                    }}件使用
                </p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">作成日</p>
                <p class="mt-2 font-bold text-[#2D3748]">{{ $prompt->created_at?->format('Y/m/d H:i') }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">更新日</p>
                <p class="mt-2 font-bold text-[#2D3748]">{{ $prompt->updated_at?->format('Y/m/d H:i') }}</p>
            </div>
        </div>
    </section>

    <section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <h3 class="mb-6 text-xl font-bold text-[#2D3748]">あらすじ・構成</h3>

        <div class="grid gap-5">
            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">あらすじ</p>
                <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $prompt->synopsis ?: '未入力' }}</p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div class="rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="text-xs font-bold text-[#A0AEC0]">起</p>
                    <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $prompt->plot_opening ?: '未入力' }}</p>
                </div>

                <div class="rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="text-xs font-bold text-[#A0AEC0]">承</p>
                    <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $prompt->plot_development ?: '未入力' }}</p>
                </div>

                <div class="rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="text-xs font-bold text-[#A0AEC0]">転</p>
                    <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $prompt->plot_turn ?: '未入力' }}</p>
                </div>

                <div class="rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="text-xs font-bold text-[#A0AEC0]">結</p>
                    <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $prompt->plot_conclusion ?: '未入力' }}</p>
                </div>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">備考</p>
                <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $prompt->notes ?: '未入力' }}</p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-5 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-xl font-bold text-[#2D3748]">プロンプト本文</h3>
                <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
                    ChatGPTなどのAIに貼り付けて使用します。
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="button"
                        id="copy-full-button"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90">
                    全文コピー
                </button>

                <button type="button"
                        id="copy-body-button"
                        class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-5 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                    本文をコピー
                </button>

                <button type="button"
                        id="select-body-button"
                        class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-5 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                    本文を全選択
                </button>
            </div>
        </div>

        <p id="copy-message" class="mb-4 hidden rounded-2xl bg-[#FFF1F5] px-5 py-3 text-sm font-bold text-[#2D3748]">
            コピーしました。
        </p>

        <textarea id="prompt-body"
                  readonly
                  class="min-h-[520px] w-full rounded-2xl border-[#CBD5E0] bg-[#F7FAFC] p-5 font-mono text-sm leading-7 text-[#2D3748] focus:border-[#FED7E2] focus:ring-[#FED7E2]">{{ $prompt->prompt_body }}</textarea>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const promptBody = document.getElementById('prompt-body');
            const copyMessage = document.getElementById('copy-message');
            const copyFullButton = document.getElementById('copy-full-button');
            const copyBodyButton = document.getElementById('copy-body-button');
            const selectBodyButton = document.getElementById('select-body-button');
            const usedCountDisplay = document.getElementById('used-count-display');
            const lastUsedDisplay = document.getElementById('last-used-display');

            async function recordUsage() {
                try {
                    const response = await fetch('{{ route('writer.prompts.record-usage', $prompt) }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();

                    if (data.used_count !== undefined && usedCountDisplay) {
                        usedCountDisplay.textContent = `${data.used_count}回`;
                    }

                    if (data.last_used_label && lastUsedDisplay) {
                        lastUsedDisplay.textContent = data.last_used_label;
                    }
                } catch (error) {
                    // 利用回数の記録失敗はコピー操作自体を妨げない
                }
            }

            function showCopyMessage(text) {
                if (!copyMessage) {
                    return;
                }

                copyMessage.textContent = text;
                copyMessage.classList.remove('hidden');

                setTimeout(() => {
                    copyMessage.classList.add('hidden');
                }, 2500);
            }

            async function copyText(text, message) {
                if (!text) {
                    return;
                }

                try {
                    await navigator.clipboard.writeText(text);
                } catch (error) {
                    promptBody.focus();
                    promptBody.select();
                    document.execCommand('copy');
                }

                await recordUsage();
                showCopyMessage(message);
            }

            copyFullButton?.addEventListener('click', function () {
                copyText(promptBody?.value || '', '全文をコピーしました。');
            });

            copyBodyButton?.addEventListener('click', function () {
                copyText(promptBody?.value || '', '本文をコピーしました。');
            });

            selectBodyButton?.addEventListener('click', function () {
                promptBody?.focus();
                promptBody?.select();
                showCopyMessage('本文を全選択しました。');
            });
        });
    </script>
@endif


@include(
    'writer.saved_prompts._ai_results',
    [
        'prompt' => $prompt,
        'aiResults' => $aiResults ?? collect(),
    ]
)

<div class="writer-prompt-bottom-actions mt-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2">
        <a href="{{ route('writer.prompts.edit', $prompt) }}"
           class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
            編集
        </a>

        <form method="POST"
              action="{{ route('writer.prompts.destroy', $prompt) }}"
              onsubmit="return confirm('このプロンプトを削除しますか？');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-2xl border border-red-200 bg-white px-6 py-3 font-bold text-red-600 hover:bg-red-50">
                削除
            </button>
        </form>

        <form method="POST" action="{{ route('writer.prompts.duplicate', $prompt) }}">
            @csrf
            <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                複製
            </button>
        </form>

        <a href="{{ route('writer.prompts.index') }}"
           class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
            一覧へ戻る
        </a>
    </div>
</div>


@include('writer.original_characters._layout_end')
