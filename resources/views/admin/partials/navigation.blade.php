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
        <a href="{{ route('admin.monetization.services.index') }}" class="{{ request()->routeIs('admin.monetization.*') ? 'active' : '' }}">
            収益管理
        </a>
    @endif

@if (auth()->user()?->isSuperAdmin())
        <a href="{{ route('admin.contact-messages.index') }}" class="{{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}">
            お問い合わせ受信箱
        </a>
    @endif

    <a href="{{ route('public.works.index') }}" target="_blank">
        データベース画面
    </a>

                            @if (auth()->user()?->canManageAllAdminFeatures())
                <a href="{{ route('admin.trash.index') }}"
                   class="{{ request()->routeIs('admin.trash.*') ? 'active' : '' }}">
                    ゴミ箱
                </a>
            @endif

<a href="{{ route('admin.staff-profile.edit') }}"
                   class="{{ request()->routeIs('admin.staff-profile.*') ? 'active' : '' }}">
                    プロフィール設定
                </a>

</nav>
