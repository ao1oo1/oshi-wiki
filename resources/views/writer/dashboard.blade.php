@include('writer.original_characters._layout_start', ['title' => 'ダッシュボード'])

@php
    $contactFormUrl = \Illuminate\Support\Facades\Route::has('contact.create')
        ? route('contact.create')
        : (
            \Illuminate\Support\Facades\Route::has('public.contact')
                ? route('public.contact')
                : (
                    \Illuminate\Support\Facades\Route::has('contact')
                        ? route('contact')
                        : url('/contact')
                )
        );

    $dataRequestUrl = $contactFormUrl . (str_contains($contactFormUrl, '?') ? '&' : '?') . 'type=data_request';
    $contributorUrl = $contactFormUrl . (str_contains($contactFormUrl, '?') ? '&' : '?') . 'type=contributor';
@endphp

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">ダッシュボード</h2>
</div>

<div class="mb-8 grid gap-6 xl:grid-cols-3">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">Guide</p>
        <h3 class="mt-2 text-xl font-bold text-[#2D3748]">はじめての方へ</h3>
        <p class="mt-3 text-sm font-bold leading-7 text-[#4A5568]">
            オリジナルキャラクター登録から、プロンプトをコピーしてChatGPTなどのAIに貼り付けるまでの流れを確認できます。
        </p>
        <a href="{{ route('writer.guide') }}"
           class="mt-5 inline-flex rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90">
            使い方ガイドを見る
        </a>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">Request</p>
        <h3 class="mt-2 text-xl font-bold text-[#2D3748]">データ登録リクエスト</h3>
        <p class="mt-3 text-sm font-bold leading-7 text-[#4A5568]">
            登録してほしい作品・ジャンル・キャラクターがある場合は、お問い合わせフォームからお知らせください。内容確認後、追加候補として検討します。
        </p>
        <a href="{{ $dataRequestUrl }}"
           class="mt-5 inline-flex rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90">
            お問い合わせフォームへ
        </a>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">Contributor</p>
        <h3 class="mt-2 text-xl font-bold text-[#2D3748]">コントリビュータ募集</h3>
        <p class="mt-3 text-sm font-bold leading-7 text-[#4A5568]">
            Oshi-Wikiでは、作品やキャラクターデータの登録に協力してくださるコントリビュータを募集しています。応募希望の方は、お問い合わせフォームから「コントリビュータ応募」と分かる内容でご連絡ください。
        </p>
        <a href="{{ $contributorUrl }}"
           class="mt-5 inline-flex rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90">
            お問い合わせフォームへ
        </a>
    </section>
</div>

<div class="mb-8">
    <p class="text-lg font-bold text-[#2D3748]">登録状況</p>
    <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
        オリジナルキャラクター、関係性、プロンプト管理の登録状況を確認できます。
    </p>
</div>

<div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">オリジナルキャラクター</p>
        <div class="mt-4 text-5xl font-bold text-[#2D3748]">{{ number_format($originalCharacterCount) }}</div>
        <a href="{{ route('writer.original-characters.index') }}"
           class="mt-5 inline-block text-base font-bold text-blue-600 hover:underline">
            オリジナルキャラクター管理へ
        </a>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">関係性</p>
        <div class="mt-4 text-5xl font-bold text-[#2D3748]">{{ number_format($relationshipCount) }}</div>
        <a href="{{ route('writer.original-character-relationships.index') }}"
           class="mt-5 inline-block text-base font-bold text-blue-600 hover:underline">
            関係性管理へ
        </a>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">プロンプト管理</p>
        <div class="mt-4 text-5xl font-bold text-[#2D3748]">{{ number_format($promptCount) }}</div>
        <a href="{{ route('writer.prompts.index') }}"
           class="mt-5 inline-block text-base font-bold text-blue-600 hover:underline">
            プロンプト管理へ
        </a>
    </section>
</div>

<div class="mt-8 grid gap-6 md:grid-cols-3">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">有効プロンプト</p>
        <div class="mt-4 text-4xl font-bold text-[#2D3748]">{{ number_format($activePromptCount) }}</div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">下書きプロンプト</p>
        <div class="mt-4 text-4xl font-bold text-[#2D3748]">{{ number_format($draftPromptCount) }}</div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">プロンプト総利用回数</p>
        <div class="mt-4 text-4xl font-bold text-[#2D3748]">{{ number_format($totalUsedCount) }}</div>
    </section>
</div>

<div class="mt-8 grid gap-6 xl:grid-cols-[1fr_360px]">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <div class="mb-5 flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <h3 class="text-xl font-bold text-[#2D3748]">最近使ったプロンプト</h3>
                <p class="mt-1 text-sm font-bold text-[#A0AEC0]">コピー利用または更新が新しい順に表示します。</p>
            </div>

            <a href="{{ route('writer.prompts.create') }}"
               class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90">
                新規作成
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-[#E2E8F0]">
            <table class="w-full table-auto text-left text-sm">
                <thead class="bg-[#F7FAFC] text-[#A0AEC0]">
                    <tr>
                        <th class="px-5 py-4">タイトル</th>
                        <th class="px-5 py-4">作品</th>
                        <th class="px-5 py-4">利用</th>
                        <th class="px-5 py-4">操作</th>
                    </tr>
                </thead>
                <tbody class="text-[#2D3748]">
                    @forelse ($recentPrompts as $prompt)
                        <tr class="border-t border-[#E2E8F0]">
                            <td class="px-5 py-4 font-bold">{{ $prompt->title }}</td>
                            <td class="px-5 py-4">{{ $prompt->workLabel() }}</td>
                            <td class="px-5 py-4">
                                <div class="font-bold">{{ number_format($prompt->used_count ?? 0) }}回</div>
                                <div class="mt-1 text-xs font-bold text-[#A0AEC0]">{{ $prompt->lastUsedLabel() }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('writer.prompts.show', $prompt) }}"
                                   class="rounded-xl border border-[#CBD5E0] px-4 py-2 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center">
                                <p class="text-base font-bold text-[#2D3748]">まだプロンプトがありません。</p>
                                <p class="mt-2 text-sm font-bold text-[#A0AEC0]">新規作成からプロンプトを作成してください。</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <aside class="space-y-6">
        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h3 class="text-xl font-bold text-[#2D3748]">ショートカット</h3>

            <div class="mt-5 space-y-3">
                <a href="{{ route('writer.original-characters.create') }}"
                   class="block rounded-2xl bg-[#FFF1F5] px-5 py-4 font-bold text-[#2D3748] hover:bg-[#FED7E2]">
                    オリジナルキャラクターを登録
                </a>

                <a href="{{ route('writer.original-character-relationships.create') }}"
                   class="block rounded-2xl bg-[#FFF1F5] px-5 py-4 font-bold text-[#2D3748] hover:bg-[#FED7E2]">
                    関係性を登録
                </a>

                <a href="{{ route('writer.prompts.create') }}"
                   class="block rounded-2xl bg-[#FFF1F5] px-5 py-4 font-bold text-[#2D3748] hover:bg-[#FED7E2]">
                    プロンプトを作成
                </a>
            </div>
        </section>

        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h3 class="text-xl font-bold text-[#2D3748]">使い方</h3>

            <ol class="mt-5 space-y-3 text-sm font-bold leading-7 text-[#4A5568]">
                <li>1. オリジナルキャラクターを登録します。</li>
                <li>2. 必要に応じて関係性を登録します。</li>
                <li>3. プロンプト管理で作品・登場人物・作風を選びます。</li>
                <li>4. 生成された本文をコピーしてAIに貼り付けます。</li>
            </ol>
        </section>
    </aside>
</div>

@include('writer.original_characters._layout_end')
