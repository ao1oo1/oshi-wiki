<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            スタッフプロフィール設定
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="rounded bg-white p-6 shadow">
                <form method="POST" action="{{ route('admin.staff-profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">初期スタッフID</label>
                        <input
                            type="text"
                            value="{{ $user->staff_public_id }}"
                            readonly
                            class="w-full rounded border-gray-300 bg-gray-100 text-gray-600"
                        >
                        <p class="mt-1 text-xs text-gray-500">
                            初期スタッフIDは変更できません。公開プロフィールURLに使用されます。
                        </p>
                    </div>

                    <div>
                        <label for="public_username" class="mb-1 block text-sm font-medium text-gray-700">
                            ユーザーネーム
                        </label>
                        <input
                            id="public_username"
                            name="public_username"
                            type="text"
                            value="{{ old('public_username', $user->public_username ?: $user->name) }}"
                            class="w-full rounded border-gray-300"
                            required
                        >
                        @error('public_username')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="profile_icon" class="mb-1 block text-sm font-medium text-gray-700">
                            プロフィールアイコン
                        </label>

                        @if ($user->profile_icon_path)
                            <div class="mb-3">
                                <img
                                    src="{{ asset('storage/' . $user->profile_icon_path) }}"
                                    alt="プロフィールアイコン"
                                    class="h-20 w-20 rounded-full object-cover"
                                >
                            </div>
                        @endif

                        <input
                            id="profile_icon"
                            name="profile_icon"
                            type="file"
                            accept="image/*"
                            class="w-full rounded border border-gray-300 p-2"
                        >
                        <p class="mt-1 text-xs text-gray-500">jpg / png / webp / gif、2MBまで。</p>

                        @error('profile_icon')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="profile_comment" class="mb-1 block text-sm font-medium text-gray-700">
                            一言文章
                        </label>
                        <textarea
                            id="profile_comment"
                            name="profile_comment"
                            rows="5"
                            class="w-full rounded border-gray-300"
                            placeholder="自己紹介や担当ジャンルなどを入力してください。"
                        >{{ old('profile_comment', $user->profile_comment) }}</textarea>
                        @error('profile_comment')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded bg-gray-50 p-4">
                        <p class="mb-2 text-sm font-medium text-gray-700">公開プロフィールURL</p>
                        <a
                            href="{{ route('public.staff.show', $user->staff_public_id) }}"
                            target="_blank"
                            class="text-blue-700 underline"
                        >
                            {{ route('public.staff.show', $user->staff_public_id) }}
                        </a>
                    </div>

                    <div class="rounded border border-red-100 bg-red-50 p-4">
                        <p class="mb-2 text-sm font-medium text-red-700">スタッフ登用破棄申請</p>
                        <p class="mb-3 text-sm text-red-700">
                            スタッフ登用の破棄を希望する場合は、下記リンクから申請してください。
                        </p>
                        <a
                            href="mailto:official_info@oshi-wiki.com?subject=スタッフ登用破棄申請"
                            class="inline-block rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white"
                        >
                            スタッフ登用破棄申請を送る
                        </a>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="rounded bg-blue-600 px-5 py-2 font-semibold text-white hover:bg-blue-700"
                        >
                            保存する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
