<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold">
            パスワード設定
        </h1>

        <p class="mt-3 text-sm text-gray-600">
            初回ログインのため、新しいパスワードを設定してください。
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 p-4 text-sm text-red-700">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('staff.password.initial.update') }}">
        @csrf
        @method('PATCH')

        <div>
            <x-input-label for="password" value="新しいパスワード" />
            <x-text-input
                id="password"
                class="mt-1 block w-full"
                type="password"
                name="password"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="新しいパスワード確認" />
            <x-text-input
                id="password_confirmation"
                class="mt-1 block w-full"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center">
                パスワードを設定する
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
