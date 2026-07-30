@php
    use Illuminate\Support\Str;

    $routeName = request()->route()?->getName();

    $privateRoute = request()->routeIs(
        'admin.*',
        'writer.*',
        'login',
        'register',
        'password.*',
        'verification.*',
        'public.contact.*',
        'public.contributor.*'
    );

    $filteredListing = request()->routeIs(
        'public.works.index',
        'public.tags.index'
    ) && request()->query();

    $seoNoindex = ($forceNoindex ?? false)
        || $privateRoute
        || $filteredListing;

    $seoCanonicalUrl = $seoCanonical ?? match (true) {
        request()->routeIs('public.home') =>
            route('public.home'),

        request()->routeIs('public.works.index') =>
            route('public.works.index'),

        request()->routeIs('public.works.show')
            && isset($work) =>
            route('public.works.show', $work),

        request()->routeIs('public.characters.show')
            && isset($character) =>
            route('public.characters.show', $character),

        request()->routeIs('public.tags.index') =>
            route('public.tags.index'),

        default => url()->current(),
    };

    $workDescription = isset($work)
        ? trim(strip_tags((string) $work->description))
        : '';

    $characterDescription = isset($character)
        ? trim(strip_tags((string) (
            $character->personality
            ?: $character->background
            ?: $character->appearance
            ?: $character->abilities
        )))
        : '';

    $seoDescriptionText = $seoDescription ?? match (true) {
        request()->routeIs('public.works.show')
            && isset($work)
            && $workDescription !== '' =>
            Str::limit($workDescription, 155, '…'),

        request()->routeIs('public.characters.show')
            && isset($character)
            && $characterDescription !== '' =>
            Str::limit(
                $character->name
                    . 'のプロフィール・人物像・関係性・作中情報。'
                    . $characterDescription,
                155,
                '…'
            ),

        request()->routeIs('public.works.show')
            && isset($work) =>
            $work->title
                . 'の作品情報、キャラクター、物語、関係性を掲載しています。',

        request()->routeIs('public.characters.show')
            && isset($character) =>
            $character->name
                . 'のプロフィール、人物像、話し方、関係性を掲載しています。',

        request()->routeIs('public.tags.index') =>
            '作品・キャラクターを分類するタグ一覧です。',

        request()->routeIs('public.about.show') =>
            'Oshi-Wikiは、漫画・アニメ・ゲーム作品の設定、キャラクター、関係性、物語を整理する創作支援データベースです。',

        request()->routeIs('public.writing-tool.show') =>
            'Oshi-Wikiの小説執筆補助ツールでできることや使い方を紹介します。',

        request()->routeIs('public.works.index', 'public.home') =>
            '漫画・アニメ・ゲーム作品の設定、キャラクター、関係性、物語を検索できる創作支援データベースです。',

        default =>
            '漫画・アニメ・ゲーム作品の設定、キャラクター、関係性、物語を整理する創作支援データベースです。',
    };

    $seoTitleText = trim(
        strip_tags(
            $seoTitle
                ?? (
                    isset($work)
                        ? $work->title . ' | Oshi-Wiki'
                        : (
                            isset($character)
                                ? $character->name . ' | Oshi-Wiki'
                                : 'Oshi-Wiki'
                        )
                )
        )
    );
@endphp

<meta
    name="description"
    content="{{ $seoDescriptionText }}"
>
<link
    rel="canonical"
    href="{{ $seoCanonicalUrl }}"
>
<meta
    name="robots"
    content="{{ $seoNoindex ? 'noindex, follow' : 'index, follow, max-image-preview:large' }}"
>

<meta property="og:type" content="website">
<meta property="og:site_name" content="Oshi-Wiki">
<meta property="og:title" content="{{ $seoTitleText }}">
<meta property="og:description" content="{{ $seoDescriptionText }}">
<meta property="og:url" content="{{ $seoCanonicalUrl }}">
<meta name="twitter:card" content="summary">
