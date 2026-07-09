<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>マイページ | {{ config('app.name', 'Oshi-Wiki') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FFFFFF] text-[#2D3748]">
    <header class="border-b border-[#E2E8F0] bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
            <a href="{{ route('public.home') }}" class="font-bold text-[#2D3748]">
                Oshi-Wiki
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded border border-[#A0AEC0] px-4 py-2 text-sm">
                    ログアウト
                </button>
            </form>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        <div class="mb-8">
            <p class="text-sm text-[#718096]">AI執筆補助</p>
            <h1 class="mt-1 text-2xl font-bold">マイページ</h1>
            <p class="mt-3 text-sm leading-7 text-[#4A5568]">
                v2ではAI本文生成は行わず、AIに貼り付けるためのプロンプト作成・保存・コピー機能を提供します。
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <section class="rounded-lg border border-[#E2E8F0] bg-white p-5">
                <h2 class="font-bold">オリジナルキャラクター</h2>
                <p class="mt-2 text-sm text-[#718096]">最大30件まで登録できます。</p>
                <a href="{{ route('writer.original-characters.index') }}" class="mt-4 inline-block rounded bg-[#FED7E2] px-4 py-2 text-sm font-bold text-[#2D3748]">
                    管理する
                </a>
            </section>

            <section class="rounded-lg border border-[#E2E8F0] bg-white p-5">
                <h2 class="font-bold">関係性</h2>
                <p class="mt-2 text-sm text-[#718096]">最大100件まで登録できます。</p>
                <a href="{{ route('writer.original-character-relationships.index') }}" class="mt-4 inline-block rounded bg-[#FED7E2] px-4 py-2 text-sm font-bold text-[#2D3748]">管理する</a>
            </section>

            <section class="rounded-lg border border-[#E2E8F0] bg-white p-5">
                <h2 class="font-bold">プロンプト</h2>
                <p class="mt-2 text-sm text-[#718096]">最大50件まで保存できます。</p>
                <a href="{{ route('writer.prompts.index') }}" class="mt-4 inline-block rounded bg-[#FED7E2] px-4 py-2 text-sm font-bold text-[#2D3748]">管理する</a>
            </section>
        </div>
    </main>
</body>
</html>
