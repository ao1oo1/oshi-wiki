@php
    $story = $story ?? null;

    $oldValue = function (string $key, $default = '') use ($story) {
        return old($key, $story?->{$key} ?? $default);
    };

    $status = old('status', $story?->status ?? 'active');
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm font-bold text-red-600">
        <p>入力内容を確認してください。</p>
        <ul class="mt-3 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-8">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">基本情報</p>
            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">タイトルと話数</h2>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                これまでに書いたストーリーを登録します。
                話数は任意です。
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-[minmax(0,1fr)_220px]">
            <div>
                <label for="title">
                    タイトル <span class="text-red-500">必須</span>
                </label>

                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ $oldValue('title') }}"
                    placeholder="例：第1話　はじまりの日"
                    required
                >
            </div>

            <div>
                <label for="episode_number">話数</label>

                <input
                    id="episode_number"
                    type="number"
                    name="episode_number"
                    min="1"
                    max="9999"
                    value="{{ $oldValue('episode_number') }}"
                    placeholder="例：1"
                >
            </div>
        </div>

        <div class="mt-5">
            <label for="status">ステータス</label>

            <select id="status" name="status">
                <option value="active" @selected($status === 'active')>
                    有効
                </option>
                <option value="draft" @selected($status === 'draft')>
                    下書き
                </option>
            </select>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">本文</p>
            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">ストーリー本文</h2>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                分析用プロンプトに使用したい本文を貼り付けてください。
                一般ユーザーは1件につき最大100,000文字です。
            </p>
        </div>

        <div>
            <label for="body">
                ストーリー本文 <span class="text-red-500">必須</span>
            </label>

            <textarea
                id="body"
                name="body"
                rows="24"
                required
                placeholder="ここにストーリー本文を貼り付けてください。"
                style="min-height:520px;"
            >{{ $oldValue('body') }}</textarea>

            <div class="mt-3 flex flex-col gap-2 text-xs font-bold text-[#A0AEC0] md:flex-row md:items-center md:justify-between">
                <p>
                    改行を含めてそのまま保存されます。
                </p>
                <p>
                    文字数：<span id="story-body-count">0</span>文字
                </p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">補足</p>
            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">メモ</h2>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                分析時に考慮してほしい内容や、執筆時の補足を登録できます。
            </p>
        </div>

        <div>
            <label for="memo">メモ</label>

            <textarea
                id="memo"
                name="memo"
                rows="8"
                placeholder="例：一人称視点。会話中心。静かな雰囲気を重視。"
            >{{ $oldValue('memo') }}</textarea>
        </div>
    </section>

    <div class="flex flex-col gap-3 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
        <p class="text-sm font-bold text-[#718096]">
            保存後も編集できます。
        </p>

        <div class="flex flex-col gap-3 md:flex-row">
            <a
                href="{{ route('writer.stories.index') }}"
                class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]"
            >
                一覧へ戻る
            </a>

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90"
            >
                保存する
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const body = document.getElementById('body');
        const count = document.getElementById('story-body-count');

        if (!body || !count) {
            return;
        }

        const updateCount = () => {
            count.textContent = body.value.length.toLocaleString();
        };

        body.addEventListener('input', updateCount);
        updateCount();
    });
</script>
