<header class="oshi-public-header">
    <div class="oshi-public-header-inner">
        <a href="{{ route('public.home') }}" class="oshi-public-logo">
            <img
                src="{{ asset('images/oshiwiki-logo.svg') }}"
                alt="Oshi-Wiki"
            >
        </a>

        <nav class="oshi-public-nav">
            <a
                href="{{ route('public.home') }}"
                class="{{ request()->routeIs('public.home') ? 'is-active' : '' }}"
            >
                トップ
            </a>

            @if (Route::has('public.about'))
                <a
                    href="{{ route('public.about') }}"
                    class="{{ request()->routeIs('public.about') ? 'is-active' : '' }}"
                >
                    Oshi-Wikiとは？
                </a>
            @endif

            @if (Route::has('public.works.index'))
                <a
                    href="{{ route('public.works.index') }}"
                    class="{{ request()->routeIs('public.works.*') ? 'is-active' : '' }}"
                >
                    作品一覧
                </a>
            @endif

            @if (Route::has('public.tags.index'))
                <a
                    href="{{ route('public.tags.index') }}"
                    class="{{ request()->routeIs('public.tags.*') ? 'is-active' : '' }}"
                >
                    タグ一覧
                </a>
            @endif

            @if (Route::has('public.contact.create'))
                <a
                    href="{{ route('public.contact.create') }}"
                    class="{{ request()->routeIs('public.contact.*') ? 'is-active' : '' }}"
                >
                    お問い合わせ
                </a>
            @endif

            <a
                href="{{ route('writer.login') }}"
                class="{{ request()->routeIs('login') ? 'is-active' : '' }}"
            >
                ログイン
            </a>
        </nav>
    </div>
</header>
