<header class="oshi-public-header">
    <div class="oshi-public-header-inner">
        <a href="/" class="oshi-public-logo" aria-label="Oshi-Wiki トップへ">
            <img src="{{ asset('images/oshiwiki-logo.png') }}" alt="Oshi-Wiki">
        </a>

        <nav class="oshi-public-nav" aria-label="公開ページメニュー">
            <a href="/works" @class(['is-active' => request()->is('works*')])>作品一覧</a>
            <a href="/tags" @class(['is-active' => request()->is('tags*')])>タグ一覧</a>
            <a href="/about" @class(['is-active' => request()->is('about*')])>このサイトについて</a>
            <a href="/contributor/apply" @class(['is-active' => request()->is('contributor/apply')])>スタッフ申請</a>
            <a href="/writer/login">ログイン</a>
        </nav>

        @include('public.partials.mobile-menu')
    </div>
</header>
