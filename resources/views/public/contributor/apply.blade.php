<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>情報入力スタッフ申請 | Oshi-Wiki</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="oshi-header">
        <div class="oshi-container oshi-header-inner">
            <a href="{{ route('public.home') }}" class="oshi-brand">
                <img
                    src="{{ asset('images/oshi-wiki-logo.svg') }}"
                    alt="Oshi-Wiki"
                    class="oshi-public-logo-img"
                >
            </a>
        </div>
    </header>

    <main class="oshi-container">
        <section class="oshi-hero">
            <h1>
                情報入力スタッフ申請
            </h1>

            <p class="oshi-lead">
                Oshi-Wiki の作品・キャラクター情報整理に協力してくださる方向けの申請フォームです。
            </p>
        </section>

        @if (session('success'))
            <div class="oshi-card" style="background:#ecfdf5;color:#166534;margin-bottom:20px;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="oshi-card" style="background:#fef2f2;color:#991b1b;margin-bottom:20px;">
                <p style="font-weight:700;">入力内容を確認してください。</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="oshi-section">
            <div class="oshi-form-card" style="margin:0 auto;">
                <form method="POST" action="{{ route('public.contributor.apply.store') }}">
                    @csrf

                    <div class="mb-5">
                        <label for="username" class="mb-1 block font-medium">
                            ユーザーネーム
                            <span class="oshi-chip">公開されます</span>
                        </label>
                        <input
                            id="username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            class="w-full"
                            required
                        >
                    </div>

                    <div class="mb-5">
                        <label for="email" class="mb-1 block font-medium">
                            メールアドレス
                            <span class="oshi-badge">非公開です</span>
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="w-full"
                            required
                        >
                    </div>

                    <div class="mb-5">
                        <label for="discord_id" class="mb-1 block font-medium">
                            Discord ID
                            <span class="oshi-badge">任意</span>
                        </label>
                        <input
                            id="discord_id"
                            type="text"
                            name="discord_id"
                            value="{{ old('discord_id') }}"
                            class="w-full"
                            placeholder="例：username#0000 / @username"
                        >
                    </div>

                    <div class="mb-5 rounded bg-pink-50 p-4 text-sm">
                        <p>
                            申請日、登用開始日、登録作品件数、登録キャラクター件数はシステム側で保存・管理します。
                        </p>
                    </div>

                    <button type="submit" class="oshi-btn">
                        申請する
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
