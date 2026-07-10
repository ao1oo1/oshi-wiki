<aside class="w-64 shrink-0 border-r border-[#FED7E2] bg-white">
    <div class="sticky top-0 flex min-h-screen flex-col p-5">
        <a href="{{ route('writer.dashboard') }}"
           class="mb-8 block rounded-2xl border border-[#FED7E2] bg-[#FFF7FA] px-4 py-4 hover:opacity-80">
            <p class="text-xs font-bold text-[#A0AEC0]">
                Oshi-Wiki
            </p>
            <p class="mt-1 text-base font-bold text-[#2D3748]">
                執筆補助ツール
            </p>
        </a>

        <nav class="space-y-2">
            <a href="{{ route('writer.dashboard') }}"
               class="block rounded-2xl px-5 py-4 text-sm font-bold transition {{ request()->routeIs('writer.dashboard') ? 'bg-[#FED7E2] text-[#2D3748]' : 'text-[#2D3748] hover:bg-[#FFF1F5]' }}">
                ダッシュボード
            </a>

            <a href="{{ route('writer.original-characters.index') }}"
               class="block rounded-2xl px-5 py-4 text-sm font-bold transition {{ request()->routeIs('writer.original-characters.*') ? 'bg-[#FED7E2] text-[#2D3748]' : 'text-[#2D3748] hover:bg-[#FFF1F5]' }}">
                オリジナルキャラクター
            </a>

            <a href="{{ route('writer.original-character-relationships.index') }}"
               class="block rounded-2xl px-5 py-4 text-sm font-bold transition {{ request()->routeIs('writer.original-character-relationships.*') ? 'bg-[#FED7E2] text-[#2D3748]' : 'text-[#2D3748] hover:bg-[#FFF1F5]' }}">
                関係性
            </a>

            <a href="{{ route('writer.prompts.index') }}"
               class="block rounded-2xl px-5 py-4 text-sm font-bold transition {{ request()->routeIs('writer.prompts.*') ? 'bg-[#FED7E2] text-[#2D3748]' : 'text-[#2D3748] hover:bg-[#FFF1F5]' }}">
                保存プロンプト
            </a>

            <a href="{{ route('writer.guide') }}"
               class="block rounded-2xl px-5 py-4 text-sm font-bold transition {{ request()->routeIs('writer.guide') ? 'bg-[#FED7E2] text-[#2D3748]' : 'text-[#2D3748] hover:bg-[#FFF1F5]' }}">
                使い方ガイド
            </a>
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
