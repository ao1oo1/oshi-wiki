<nav class="oshi-admin-nav">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        ダッシュボード
    </a>

    @if (auth()->user()?->isSuperAdmin())
        <a href="{{ route('admin.review-requests.index') }}" class="{{ request()->routeIs('admin.review-requests.*') ? 'active' : '' }}">
            承認待ち
        </a>

        <a href="{{ route('admin.contributor-applications.index') }}" class="{{ request()->routeIs('admin.contributor-applications.*') ? 'active' : '' }}">
            スタッフ申請
        </a>
    @endif

    <a href="{{ route('admin.works.index') }}" class="{{ request()->routeIs('admin.works.index') || request()->routeIs('admin.works.show') || request()->routeIs('admin.works.edit') ? 'active' : '' }}">
        作品管理
    </a>
<a href="{{ route('admin.characters.index') }}" class="{{ request()->routeIs('admin.characters.*') ? 'active' : '' }}">
        キャラクター管理
    </a>

    <a href="{{ route('admin.character-relationships.index') }}" class="{{ request()->routeIs('admin.character-relationships.*') ? 'active' : '' }}">
        関係性管理
    </a>
<a href="{{ route('admin.tags.index') }}" class="{{ request()->routeIs('admin.tags.index') || request()->routeIs('admin.tags.edit') ? 'active' : '' }}">
        タグ管理
    </a>
@if (auth()->user()?->isSuperAdmin())
        <a href="{{ route('admin.contact-messages.index') }}" class="{{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}">
            お問い合わせ受信箱
        </a>
    @endif

    <a href="{{ route('public.works.index') }}" target="_blank">
        公開ページ
    </a>

                <a href="{{ route('admin.staff-profile.edit') }}"
                   class="{{ request()->routeIs('admin.staff-profile.*') ? 'active' : '' }}">
                    プロフィール設定
                </a>

</nav>
