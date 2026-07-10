<section>
    <header>
        <h2 class="text-lg font-bold text-gray-900">
            @if (auth()->user()?->must_change_password)
                初回パスワード設定
            @else
                パスワード変更
            @endif
        </h2>

        <p class="mt-2 text-sm leading-7 text-gray-600">
            @if (auth()->user()?->must_change_password)
                仮パスワードでログインしています。ご自身で新しいパスワードを設定してください。
                設定後は一度ログアウトしますので、新しいパスワードで再度ログインしてください。
            @else
                アカウントを安全に保つため、推測されにくいパスワードを設定してください。
            @endif
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="現在のパスワード" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="新しいパスワード" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="新しいパスワード（確認）" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>
                @if (auth()->user()?->must_change_password)
                    パスワードを設定する
                @else
                    保存する
                @endif
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p class="text-sm text-gray-600">保存しました。</p>
            @endif
        </div>
    </form>
</section>
