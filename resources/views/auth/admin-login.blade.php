<x-guest-layout>
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-[#2D3748]">管理者ログイン</h1>
        <p class="mt-3 text-sm font-bold leading-7 text-[#A0AEC0]">
            管理者・スタッフ用のログイン画面です。<br>スタッフ登用メールに記載された仮パスワードでもログインできます。
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />    <form method="POST" action="{{ route('admin.login.store') }}">
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
            <a href="{{ route('writer.login') }}"
               class="text-xs font-bold text-[#A0AEC0] underline underline-offset-4 hover:text-[#2D3748]">
                執筆補助ツールログインはこちら
            </a>
        </div>
    </form>
</x-guest-layout>
