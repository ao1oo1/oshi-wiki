<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $staff->displayName() }} | スタッフプロフィール | Oshi-Wiki</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="oshi-header">
        <div class="oshi-container oshi-header-inner">
            <a href="{{ route('public.home') }}" class="oshi-brand">
                <img src="{{ asset('images/oshi-wiki-logo.svg') }}" alt="Oshi-Wiki" class="oshi-public-logo-img">
            </a>

            <nav class="oshi-nav">
                <a href="{{ route('public.home') }}">トップ</a>
                <a href="{{ route('public.about.show') }}">Oshi-Wikiとは？</a>
                <a href="{{ route('public.works.index') }}">作品一覧</a>
                <a href="{{ route('public.tags.index') }}">タグ一覧</a>
                <a href="{{ route('public.contact.create') }}">お問い合わせ</a>
            </nav>
        </div>
    </header>

    <main class="oshi-container">
        <section class="oshi-section">
            <div class="oshi-card">
                <div style="display:flex;gap:20px;align-items:center;flex-wrap:wrap;">
                    @if ($staff->profile_icon_path)
                        <img
                            src="{{ asset('storage/' . $staff->profile_icon_path) }}"
                            alt="{{ $staff->displayName() }}"
                            style="width:96px;height:96px;border-radius:999px;object-fit:cover;"
                        >
                    @else
                        <div style="width:96px;height:96px;border-radius:999px;background:#FED7E2;display:flex;align-items:center;justify-content:center;font-weight:700;">
                            {{ mb_substr($staff->displayName(), 0, 1) }}
                        </div>
                    @endif

                    <div>
                        <p class="oshi-muted">情報入力スタッフ</p>
                        <h1 class="text-2xl font-bold">{{ $staff->displayName() }}</h1>
                        <p class="oshi-muted">ID：{{ $staff->staff_public_id }}</p>
                    </div>
                </div>

                @if ($staff->profile_comment)
                    <div class="mt-6 rounded bg-pink-50 p-4">
                        {{ $staff->profile_comment }}
                    </div>
                @endif
            </div>
        </section>

        <section class="oshi-section">
            <h2 class="oshi-section-title">登録した作品</h2>

            @if ($works->count())
                <div class="oshi-grid">
                    @foreach ($works as $work)
                        <a href="{{ route('public.works.show', $work) }}" class="oshi-card">
                            <h3 class="font-bold">{{ $work->title }}</h3>
                            <p class="oshi-muted">{{ $work->genre }}</p>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="oshi-empty">公開中の登録作品はまだありません。</div>
            @endif
        </section>

        <section class="oshi-section">
            <h2 class="oshi-section-title">登録したキャラクター</h2>

            @if ($characters->count())
                <div class="oshi-grid">
                    @foreach ($characters as $character)
                        <a href="{{ route('public.characters.show', $character) }}" class="oshi-card">
                            <h3 class="font-bold">{{ $character->name }}</h3>
                            <p class="oshi-muted">{{ $character->work?->title }}</p>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="oshi-empty">公開中の登録キャラクターはまだありません。</div>
            @endif
        </section>
    </main>
</body>
</html>
