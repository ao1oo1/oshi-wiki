<x-guest-layout>
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-[#2D3748]">執筆補助ツールログイン</h1>
        <p class="mt-3 text-sm font-bold leading-7 text-[#A0AEC0]">
            執筆補助ツール会員としてログインできます。
        </p>
    </div>

    <div class="mb-8 grid grid-cols-2 gap-3">
        <a href="{{ route('writer.login') }}"
           class="flex min-h-[58px] items-center justify-center whitespace-nowrap rounded-2xl bg-[#FED7E2] px-3 py-3 text-center text-[12px] font-bold text-[#2D3748] hover:opacity-90 sm:text-[13px] md:text-sm">
            執筆補助ツール会員
        </a>

        <a href="{{ route('writer.register') }}"
           class="flex min-h-[58px] items-center justify-center whitespace-nowrap rounded-2xl border border-[#CBD5E0] bg-white px-3 py-3 text-center text-[12px] font-bold text-[#2D3748] hover:bg-[#F7FAFC] sm:text-[13px] md:text-sm">
            新規登録
        </a>
    </div>

    <form method="POST" action="{{ route('writer.login') }}">
        @csrf

        <div>
            <x-input-label for="email" value="メールアドレス" />
            <x-text-input id="email"
                          class="mt-1 block w-full"
                          type="email"
                          name="email"
                          :value="old('email')"
                          required
                          autofocus
                          autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="パスワード" />

            <x-text-input id="password"
                          class="mt-1 block w-full"
                          type="password"
                          name="password"
                          required
                          autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 text-right">
            <a href="{{ route('password.request') }}"
               class="text-xs font-bold text-[#A0AEC0] underline underline-offset-4 hover:text-[#2D3748]">
                パスワードを忘れた方はこちら
            </a>
        </div>

        <div class="mt-4 block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me"
                       type="checkbox"
                       class="rounded border-gray-300 text-[#FED7E2] shadow-sm focus:ring-[#FED7E2]"
                       name="remember">
                <span class="ms-2 text-sm text-[#A0AEC0]">ログイン状態を保持する</span>
            </label>
        </div>

        <div class="mt-6 flex items-center justify-end gap-4">
            <button type="submit"
                    class="inline-flex items-center rounded-2xl bg-[#2D3748] px-8 py-3 text-sm font-bold tracking-widest text-white hover:opacity-90">
                ログイン
            </button>
        </div>

        <div class="mt-5 text-center">
            <a href="{{ route('admin.login') }}"
               class="text-xs font-bold text-[#A0AEC0] underline underline-offset-4 hover:text-[#2D3748]">
                管理者ログインはこちら
            </a>
        </div>
    </form>
</x-guest-layout>
