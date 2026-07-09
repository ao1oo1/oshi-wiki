<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'AI執筆補助' }} | {{ config('app.name', 'Oshi-Wiki') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8FAFC] text-[#2D3748]">
    <div class="min-h-screen md:flex">
        <aside class="w-full border-b border-[#E2E8F0] bg-white md:min-h-screen md:w-80 md:border-b-0 md:border-r">
            <div class="px-5 py-8">
                <a href="{{ route('writer.dashboard') }}" class="mb-8 flex items-center gap-3">
                    @if (file_exists(public_path('images/oshiwiki-logo.png')))
                        <img src="{{ asset('images/oshiwiki-logo.png') }}" alt="Oshi-Wiki" class="h-20 w-auto">
                    @else
                        <div class="text-2xl font-bold text-[#2D3748]">Oshi-Wiki</div>
                    @endif
                </a>

                <div class="mb-8 rounded-2xl bg-gradient-to-br from-[#FFF1F5] to-[#FFFFFF] p-5">
                    <p class="text-lg font-bold">{{ auth()->user()->name ?? 'ユーザー' }}</p>
                    <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
                        {{ auth()->user()?->isSuperAdmin() ? '最高管理者' : 'AI執筆補助ユーザー' }}
                    </p>
                </div>

                <nav class="space-y-3 text-lg font-bold">
                    <a href="{{ route('writer.dashboard') }}"
                       class="block rounded-2xl px-5 py-4 {{ request()->routeIs('writer.dashboard') ? 'bg-[#FED7E2] text-[#2D3748]' : 'text-[#2D3748] hover:bg-[#FFF1F5]' }}">
                        ダッシュボード
                    </a>

                    <a href="{{ route('writer.original-characters.index') }}"
                       class="block rounded-2xl px-5 py-4 {{ request()->routeIs('writer.original-characters.*') ? 'bg-[#FED7E2] text-[#2D3748]' : 'text-[#2D3748] hover:bg-[#FFF1F5]' }}">
                        オリジナルキャラクター
                    </a>

                    <a href="{{ route('writer.original-character-relationships.index') }}"
                       class="block rounded-2xl px-5 py-4 {{ request()->routeIs('writer.original-character-relationships.*') ? 'bg-[#FED7E2] text-[#2D3748]' : 'text-[#2D3748] hover:bg-[#FFF1F5]' }}">
                        関係性
                    </a>

                    <a href="{{ route('writer.prompts.index') }}"
                       class="block rounded-2xl px-5 py-4 {{ request()->routeIs('writer.prompts.*') ? 'bg-[#FED7E2] text-[#2D3748]' : 'text-[#2D3748] hover:bg-[#FFF1F5]' }}">
                        プロンプト管理
                    </a>

                    @if (auth()->user()?->canAccessAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="block rounded-2xl px-5 py-4 text-[#2D3748] hover:bg-[#FFF1F5]">
                            管理画面へ
                        </a>
                    @endif

                    <a href="{{ route('public.home') }}" class="block rounded-2xl px-5 py-4 text-[#2D3748] hover:bg-[#FFF1F5]">
                        公開ページ
                    </a>
                </nav>

                <form method="POST" action="{{ route('logout') }}" class="mt-8">
                    @csrf
                    <button type="submit" class="w-full rounded-2xl border border-[#CBD5E0] px-5 py-4 text-left text-lg font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                        ログアウト
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 px-5 py-8 md:px-12 md:py-12">
            <div class="mx-auto max-w-6xl">
                @if (session('success'))
                    <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-bold text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
