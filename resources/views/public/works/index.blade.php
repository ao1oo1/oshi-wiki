<!DOCTYPE html>
<html lang="ja">
<head>
    @include('partials.google-analytics')
    @include('partials.seo-meta', [
        'pageSeoTitle' => ($isHome ?? false)
            ? 'Oshi-Wiki｜アニメ・漫画・ゲームの創作支援データベース'
            : '作品一覧｜Oshi-Wiki',
        'pageSeoDescription' => ($isHome ?? false)
            ? 'Oshi-Wikiは、アニメ・漫画・ゲーム・小説のキャラクター、人物関係、ストーリー、世界観、用語、年表を整理した創作支援データベースです。'
            : 'Oshi-Wikiに登録されたアニメ・漫画・ゲーム・小説作品を検索・閲覧できます。',
    ])


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($isHome ?? false)
        ? 'Oshi-Wiki｜アニメ・漫画・ゲームの創作支援データベース'
        : '作品一覧｜Oshi-Wiki' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Google AdSense site verification --}}
    <script
        async
        src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3916030283806562"
        crossorigin="anonymous"
    ></script>
    @if ($isHome ?? false)
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => 'Oshi-Wiki',
                'alternateName' => ['推しウィキ', 'oshi-wiki'],
                'url' => route('public.home'),
                'description' => 'アニメ・漫画・ゲーム・小説のキャラクター、人物関係、ストーリー、世界観、用語、年表を整理した創作支援データベースです。',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif
</head>
<body>


    @include('public.partials.header')

    @include(
        'public.partials.impression-ads',
        ['position' => 'page_top']
    )

    <div id="page-top"></div>

    @include(
        'partials.page-jump-navigation',
        ['position' => 'top']
    )

    <main class="oshi-container">
        <section class="oshi-hero">
            @if ($isHome ?? false)
                <h1 class="sr-only">
                    Oshi-Wiki｜アニメ・漫画・ゲームの創作支援データベース
                </h1>

                <div class="mb-8 overflow-hidden rounded-3xl">
                    <picture class="block w-full">
                        <source
                            media="(max-width: 767px)"
                            srcset="{{ asset('images/top-02.jpg') }}"
                        >
                        <img
                        src="{{ asset('images/top_img.jpg') }}"
                        alt="Oshi-Wiki アニメ・漫画・ゲームの創作支援データベース"
                        class="block h-auto w-full"
                    >
                    </picture>
                </div>
            @else
                <h1>作品一覧</h1>
            @endif
<form method="GET" action="{{ route('public.works.index') }}" class="oshi-search-box">
                <input
                    type="text"
                    name="keyword"
                    value="{{ $keyword ?? '' }}"
                    placeholder="例：作品名 キャラ名 タグ"
                    autocomplete="off"
                >

                @if (!empty($selectedTagId))
                    <input type="hidden" name="tag_id" value="{{ $selectedTagId }}">
                @endif

                <button type="submit">
                    検索
                </button>
            </form>
                <p class="oshi-public-hero-search-note">作品名・キャラクター名・タグ・説明文・章名・物語詳細をまとめて検索できます。スペース区切りでAND検索できます。</p>

@if (!empty($keyword) || !empty($selectedTagId))
                <div style="margin-top:16px;">
                    <a href="{{ route('public.works.index') }}" class="oshi-btn oshi-btn-sub">
                        検索条件を解除
                    </a>
                </div>
            @endif
        </section>

        <section class="oshi-writing-tool-cta" aria-labelledby="writing-tool-cta-title">
            <div class="oshi-writing-tool-cta-inner">
                <div class="oshi-writing-tool-cta-copy">
                    <span class="oshi-writing-tool-cta-label">創作をもっと便利に</span>
                    <h2 id="writing-tool-cta-title">小説執筆補助ツールのご利用はこちら</h2>
                    <p>
                        オリジナルキャラクターや関係性を登録し、
                        小説執筆用のプロンプトを作成・保存できます。
                    </p>
                </div>

                <div class="oshi-writing-tool-cta-actions">
                    <a href="{{ route('register') }}" class="oshi-writing-tool-cta-button">
                        無料で新規登録する
                        <span aria-hidden="true">→</span>
                    </a>

                    <a href="{{ route('public.writing-tool.show') }}" class="oshi-writing-tool-cta-detail-link">
                        小説執筆補助ツールとは？
                    </a>
                </div>
            </div>
        </section>

        <section class="oshi-section">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="oshi-section-title">
                    タグから探す
                </h2>

                @if (($isHome ?? false) && ($tagsCount ?? 0) > 18)
                    <a href="{{ route('public.tags.index') }}" class="oshi-btn oshi-btn-sub">
                        すべて見る
                    </a>
                @endif
            </div>

            @if (($tags ?? collect())->count())
                <div class="oshi-card">
                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('public.works.index', ['keyword' => $keyword]) }}"
                            class="oshi-chip"
                            @if (empty($selectedTagId)) style="background:#FED7E2;" @endif
                        >
                            全タグ
                        </a>

                        @foreach ($tags as $tag)
                            <a
                                href="{{ route('public.works.index', array_filter(['keyword' => $keyword, 'tag_id' => $tag->id])) }}"
                                class="oshi-chip"
                                @if (($selectedTagId ?? '') == $tag->id) style="background:#FED7E2;" @endif
                            >
                                {{ $tag->name }}
                                <span class="oshi-muted">
                                    {{ ($tag->works_count ?? 0) + ($tag->characters_count ?? 0) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="oshi-card">
                    <p class="oshi-muted">
                        公開中のタグはまだありません。
                    </p>
                </div>
            @endif
        </section>

        <section class="oshi-section">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="oshi-section-title">
                    {{ ($isHome ?? false) ? '作品から探す' : '作品一覧' }}
                </h2>

                @if (($isHome ?? false) && ($worksCount ?? 0) > 9)
                    <a href="{{ route('public.works.index') }}" class="oshi-btn oshi-btn-sub">
                        作品をすべて見る
                    </a>
                @endif
            </div>

            @if ($works->count())
                <div class="oshi-card-grid">
                    @foreach ($works as $work)
                        <a class="oshi-card" href="{{ route('public.works.show', $work) }}">
                            <h3>{{ $work->title }}</h3>

                            @if ($work->title_kana)
                                <div class="oshi-muted">
                                    {{ $work->title_kana }}
                                </div>
                            @endif

                            <div class="oshi-meta">
                                {{ $work->genre ?: 'ジャンル未設定' }}
                                @if ($work->original_media)
                                    / {{ $work->original_media }}
                                @endif
                            </div>

                            @if ($work->tags->count())
                                <div style="margin-top:12px;">
                                    @foreach ($work->tags as $tag)
                                        <span class="oshi-chip">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @php
                                $matchedCharacters = $work->characters
                                    ->where('status', 'published')
                                    ->take(4);
                            @endphp

                            @if ($matchedCharacters->count())
                                <div style="margin-top:14px;">
                                    <div class="oshi-muted">
                                        登録キャラクター
                                    </div>

                                    @foreach ($matchedCharacters as $character)
                                        <span class="oshi-badge">
                                            {{ $character->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>

                @if (!($isHome ?? false) && method_exists($works, 'links'))
                    <div style="margin-top:24px;">
                        {{ $works->links() }}
                    </div>
                @endif
            @else
                <div class="oshi-card">
                    <p class="oshi-muted">
                        条件に一致する公開作品はありません。
                    </p>
                </div>
            @endif
        </section>

        @if ($isHome ?? false)
            <section class="oshi-section">
                <h2 class="oshi-section-title">
                    このサイトについて
                </h2>

                <div class="oshi-card">
                    <p>
                        Oshi-Wiki は、二次創作や創作活動のために、作品・キャラクター・関係性を整理する情報データベースです。
                        詳しくは
                        <a href="{{ route('public.about.show') }}" class="oshi-chip">Oshi-Wikiとは？</a>
                        をご確認ください。
                    </p>
                    <p>
                        公式情報・客観情報を中心に整理しています。
                    </p>
                    <p>
                        間違いの指摘や著作者による削除希望は
                        <a href="{{ route('public.contact.create') }}" class="oshi-chip">こちら</a>
                        にてお知らせください。
                    </p>
                    <p>
                        コントリビューター募集中。開発者コミュニティ
                        <a href="{{ route('public.contact.create', ['category' => 'discord']) }}" class="oshi-chip">Discord</a>
                        へのメンバーも募集しております。
                    </p>
                </div>
            </section>
        @endif
    </main>

    <div id="page-bottom"></div>

    @include(
        'partials.page-jump-navigation',
        ['position' => 'bottom']
    )
    @include(
        'public.partials.impression-ads',
        ['position' => 'page_middle']
    )

    @include(
        'public.partials.impression-ads',
        ['position' => 'page_bottom']
    )
@include('public.partials.legal-footer')

</body>
</html>
