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
            <a href="{{ route('admin.analytics.index') }}"
               class="{{ request()->routeIs('admin.analytics.*')
                    ? 'bg-[#FED7E2] text-[#2D3748]'
                    : 'text-[#4A5568] hover:bg-[#FFF1F5]' }}
                      flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold transition">
                <svg class="h-5 w-5 shrink-0"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     aria-hidden="true">
                    <path d="M4 19V9" />
                    <path d="M10 19V5" />
                    <path d="M16 19v-7" />
                    <path d="M22 19V3" />
                </svg>
                <span>アナリティクス</span>
            </a>
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
