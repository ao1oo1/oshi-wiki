<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>タグ一覧 | Oshi-Wiki</title>

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

            <nav class="oshi-nav">
                <a href="{{ route('public.home') }}">トップ</a>
                <a href="{{ route('public.about.show') }}" class="{{ request()->routeIs('public.about.*') ? 'active' : '' }}">Oshi-Wikiとは？</a>
                <a href="{{ route('public.works.index') }}">作品一覧</a>
                <a href="{{ route('public.tags.index') }}" class="active">タグ一覧</a>
                <a href="{{ route('public.contact.create') }}">お問い合わせ</a>
                <a href="{{ route('login') }}">管理ログイン</a>
            </nav>
        </div>
    </header>

    <main class="oshi-container">
        <section class="oshi-hero">
            <h1>
                タグ一覧
            </h1>

            <p class="oshi-lead">
                タグから作品やキャラクター情報を探せます。
            </p>
        </section>

        <section class="oshi-section">
            @if ($tags->count())
                <div class="oshi-card">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($tags as $tag)
                            <a
                                href="{{ route('public.works.index', ['tag_id' => $tag->id]) }}"
                                class="oshi-chip"
                            >
                                {{ $tag->name }}
                                <span class="oshi-muted">
                                    {{ ($tag->works_count ?? 0) + ($tag->characters_count ?? 0) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div style="margin-top:24px;">
                    {{ $tags->links() }}
                </div>
            @else
                <div class="oshi-card">
                    <p class="oshi-muted">
                        公開中のタグはまだありません。
                    </p>
                </div>
            @endif
        </section>
    </main>
</body>
</html>
