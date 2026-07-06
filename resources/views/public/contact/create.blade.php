<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お問い合わせ | Oshi-Wiki</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/favicon.svg') }}" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">


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

            <nav class="oshi-nav">
                <a href="{{ route('public.works.index') }}">作品一覧</a>
                <a href="{{ route('public.contact.create') }}" class="active">お問い合わせ</a>
                <a href="{{ route('login') }}">管理ログイン</a>
            </nav>
        </div>
    </header>

    <main class="oshi-container">
        <section class="oshi-hero">
            <h1>
                お問い合わせ
            </h1>

            <p class="oshi-lead">
                間違いの指摘、著作者による削除希望、コントリビューター希望はこちらからお送りください。
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
                <form method="POST" action="{{ route('public.contact.store') }}">
                    @csrf

                    <div class="mb-5">
                        <label for="category" class="mb-1 block font-medium">
                            お問い合わせ種別
                        </label>
                        <select id="category" name="category" class="w-full" required>
                            <option value="correction" @selected(old('category') === 'correction')>間違いの指摘</option>
                            <option value="copyright" @selected(old('category') === 'copyright')>著作者による削除希望</option>
                            <option value="contributor" @selected(old('category') === 'contributor')>コントリビューター希望</option>
                            <option value="discord" @selected(old('category') === 'discord')>開発者コミュニティ参加希望</option>
                            <option value="other" @selected(old('category') === 'other')>その他</option>
                        </select>
                    </div>

                    <div class="mb-5">
                        <label for="target_url" class="mb-1 block font-medium">
                            対象URL
                        </label>
                        <input
                            id="target_url"
                            type="url"
                            name="target_url"
                            value="{{ old('target_url') }}"
                            class="w-full"
                            placeholder="https://..."
                        >
                        <p class="oshi-muted">
                            間違いの指摘や削除希望の場合、対象ページのURLがあると確認しやすいです。
                        </p>
                    </div>

                    <div class="mb-5">
                        <label for="name" class="mb-1 block font-medium">
                            お名前
                        </label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            class="w-full"
                        >
                    </div>

                    <div class="mb-5">
                        <label for="email" class="mb-1 block font-medium">
                            メールアドレス
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="w-full"
                        >
                        <p class="oshi-muted">
                            返信が必要な場合のみ入力してください。
                        </p>
                    </div>

                    <div class="mb-5">
                        <label for="subject" class="mb-1 block font-medium">
                            件名
                        </label>
                        <input
                            id="subject"
                            type="text"
                            name="subject"
                            value="{{ old('subject') }}"
                            class="w-full"
                            required
                        >
                    </div>

                    <div class="mb-5">
                        <label for="body" class="mb-1 block font-medium">
                            内容
                        </label>
                        <textarea
                            id="body"
                            name="body"
                            rows="10"
                            class="w-full"
                            required
                        >{{ old('body') }}</textarea>
                    </div>

                    <button type="submit" class="oshi-btn">
                        送信する
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
