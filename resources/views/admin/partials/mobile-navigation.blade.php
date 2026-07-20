<div class="oshi-admin-mobile-header">
    <a href="{{ route('admin.dashboard') }}" class="oshi-admin-mobile-brand">
        Oshi-Wiki 管理
    </a>

    <details class="oshi-admin-mobile-menu">
        <summary class="oshi-admin-mobile-menu-button" aria-label="管理メニューを開く">
            <span></span>
            <span></span>
            <span></span>
        </summary>

        <div class="oshi-admin-mobile-menu-panel">
            <a href="{{ route('admin.dashboard') }}">ダッシュボード</a>
            <a href="{{ route('admin.works.index') }}">作品管理</a>
            <a href="{{ route('admin.characters.index') }}">キャラクター管理</a>
            <a href="{{ route('admin.tags.index') }}">タグ管理</a>

            @if (auth()->user()?->isSuperAdmin())
                <a href="{{ route('admin.character-relationships.index') }}">関係性管理</a>
                <a href="{{ route('admin.contributor-applications.index') }}">スタッフ申請管理</a>
                <a href="{{ route('admin.review-requests.index') }}">承認申請管理</a>
                <a href="{{ route('admin.contact-messages.index') }}">お問い合わせ</a>
            @endif

                        @if (auth()->user()?->isSuperAdmin())
                <a href="{{ route('admin.monetization.services.index') }}">収益管理</a>
            @endif

            @if (auth()->user()?->canManageAllAdminFeatures())
                <a href="{{ route('admin.trash.index') }}">ゴミ箱</a>
            @endif

<a href="{{ route('admin.staff-profile.edit') }}">プロフィール</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
        </div>
    </details>
</div>
