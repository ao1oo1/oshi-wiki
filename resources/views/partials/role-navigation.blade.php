@php
    $user = auth()->user();

    /*
     * ナビゲーション種別の決定
     *
     * 重要：
     * - writer画面では writer メニューだけを表示
     * - admin画面では admin メニューだけを表示
     * - メニュー非表示だけをセキュリティにしない
     * - URL直打ちは routes/web.php の middleware で制御する
     */
    if ($user?->canAccessAdmin() && request()->routeIs('admin.*')) {
        $navigationType = 'admin';
    } elseif ($user?->isWriter() || request()->routeIs('writer.*')) {
        $navigationType = 'writer';
    } elseif ($user?->canAccessAdmin()) {
        $navigationType = 'admin';
    } else {
        $navigationType = 'writer';
    }

    $navigationItems = config("oshi_navigation.menus.{$navigationType}", []);
    $homeRoute = config("oshi_navigation.home_routes.{$navigationType}");
@endphp

<aside class="w-64 shrink-0 border-r border-[#FED7E2] bg-white">
    <div class="sticky top-0 flex min-h-screen flex-col p-5">
        <a href="{{ route($homeRoute) }}" class="mb-8 block rounded-2xl border border-[#FED7E2] bg-[#FFF7FA] px-4 py-4 hover:opacity-80">
            <p class="text-xs font-bold text-[#A0AEC0]">
                Oshi-Wiki
            </p>

            <p class="mt-1 text-base font-bold text-[#2D3748]">
                @if ($navigationType === 'admin')
                    管理画面
                @else
                    執筆補助ツール
                @endif
            </p>
        </a>

        <nav class="space-y-2">
            @foreach ($navigationItems as $item)
                @continue(! Route::has($item['route']))

                <a href="{{ route($item['route']) }}"
                   class="block rounded-2xl px-5 py-4 text-sm font-bold transition
                   {{ request()->routeIs($item['active'])
                        ? 'bg-[#FED7E2] text-[#2D3748]'
                        : 'text-[#2D3748] hover:bg-[#FFF1F5]' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="mt-auto border-t border-gray-100 pt-5">
            <a href="{{ route('public.home') }}"
               class="block rounded-2xl px-5 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#FFF1F5]">
                公開サイトを見る
            </a>

            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf

                <button type="submit"
                        class="block w-full rounded-2xl px-5 py-3 text-left text-sm font-bold text-[#2D3748] hover:bg-[#FFF1F5]">
                    ログアウト
                </button>
            </form>
        </div>
    </div>
</aside>
