@php
    $currentWriterUser = auth()->user();

    $writerCharacterLimit = $currentWriterUser
        ? \App\Support\WritingAssistLimits::originalCharactersPerUser($currentWriterUser)
        : null;

    $writerRelationshipLimit = $currentWriterUser
        ? \App\Support\WritingAssistLimits::relationshipsPerUser($currentWriterUser)
        : null;

    $writerPromptLimit = $currentWriterUser
        ? \App\Support\WritingAssistLimits::promptsPerUser($currentWriterUser)
        : null;

    $writerCharacterCount = $currentWriterUser
        ? \App\Models\OriginalCharacter::query()->where('user_id', $currentWriterUser->id)->count()
        : 0;

    $writerRelationshipCount = $currentWriterUser
        ? \App\Models\OriginalCharacterRelationship::query()->where('user_id', $currentWriterUser->id)->count()
        : 0;

    $writerPromptCount = $currentWriterUser
        ? \App\Models\SavedPrompt::query()->where('user_id', $currentWriterUser->id)->count()
        : 0;

    $writerUsageStats = [
        [
            'label' => 'キャラクター',
            'count' => $writerCharacterCount,
            'limit' => $writerCharacterLimit,
        ],
        [
            'label' => '関係性',
            'count' => $writerRelationshipCount,
            'limit' => $writerRelationshipLimit,
        ],
        [
            'label' => 'プロンプト',
            'count' => $writerPromptCount,
            'limit' => $writerPromptLimit,
        ],
    ];

    $writerUsageLabel = function (int $count, ?int $limit): string {
        return $limit === null ? number_format($count) . ' / 制限なし' : number_format($count) . ' / ' . number_format($limit);
    };

    $writerUsagePercent = function (int $count, ?int $limit): int {
        if ($limit === null || $limit <= 0) {
            return 0;
        }

        return min(100, (int) floor(($count / $limit) * 100));
    };
@endphp

@once
    <style>
        /*
         * Writer form common UI
         * 入力フォーム画面の見た目をOshi-Wikiの管理画面デザインに統一する。
         */
        .writer-form-ui form {
            width: 100%;
        }

        .writer-form-ui label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 700;
            color: #2D3748;
        }

        .writer-form-ui input[type="text"],
        .writer-form-ui input[type="email"],
        .writer-form-ui input[type="number"],
        .writer-form-ui input[type="url"],
        .writer-form-ui input[type="date"],
        .writer-form-ui input[type="datetime-local"],
        .writer-form-ui input[type="password"],
        .writer-form-ui select,
        .writer-form-ui textarea {
            width: 100%;
            border-radius: 1rem;
            border: 1px solid #CBD5E0;
            background-color: #FFFFFF;
            color: #2D3748;
            font-weight: 700;
            box-shadow: none;
        }

        .writer-form-ui input[type="text"]:focus,
        .writer-form-ui input[type="email"]:focus,
        .writer-form-ui input[type="number"]:focus,
        .writer-form-ui input[type="url"]:focus,
        .writer-form-ui input[type="date"]:focus,
        .writer-form-ui input[type="datetime-local"]:focus,
        .writer-form-ui input[type="password"]:focus,
        .writer-form-ui select:focus,
        .writer-form-ui textarea:focus {
            border-color: #FED7E2;
            box-shadow: 0 0 0 3px rgba(254, 215, 226, 0.75);
            outline: none;
        }

        .writer-form-ui textarea {
            min-height: 9rem;
            line-height: 1.75;
        }

        .writer-form-ui #prompt-preview {
            min-height: 680px !important;
            resize: vertical;
        }

        .writer-form-ui input::placeholder,
        .writer-form-ui textarea::placeholder {
            color: #A0AEC0;
            font-weight: 700;
        }

        .writer-form-ui input[type="checkbox"],
        .writer-form-ui input[type="radio"] {
            border-color: #CBD5E0;
            color: #FED7E2;
        }

        .writer-form-ui input[type="checkbox"]:focus,
        .writer-form-ui input[type="radio"]:focus {
            box-shadow: 0 0 0 3px rgba(254, 215, 226, 0.75);
        }

        .writer-form-ui .form-help,
        .writer-form-ui .help-text,
        .writer-form-ui small {
            color: #A0AEC0;
            font-size: 0.875rem;
            font-weight: 700;
            line-height: 1.75;
        }

        .writer-form-ui .text-red-600,
        .writer-form-ui .text-red-500 {
            font-weight: 700;
        }

        .writer-form-ui button,
        .writer-form-ui a {
            transition: opacity 0.15s ease, background-color 0.15s ease, border-color 0.15s ease;
        }
    </style>
@endonce



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

                    <a href="{{ route('writer.guide') }}"
                       class="block rounded-2xl px-5 py-4 {{ request()->routeIs('writer.guide') ? 'bg-[#FED7E2] text-[#2D3748]' : 'text-[#2D3748] hover:bg-[#FFF1F5]' }}">
                        使い方ガイド
                    </a>

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
