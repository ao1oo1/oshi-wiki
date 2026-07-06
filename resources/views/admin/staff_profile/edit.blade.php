<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            プロフィール設定
        </h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card">
            <h1 class="mb-2 text-2xl font-bold">
                プロフィール設定
            </h1>

            <p class="oshi-muted mb-6">
                公開プロフィールに表示する情報を設定できます。
            </p>

            <form method="POST" action="{{ route('admin.staff-profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="mb-5">
                    <label class="mb-1 block font-bold">
                        初期スタッフID
                    </label>
                    <input type="text" value="{{ $user->staff_public_id }}" readonly class="w-full bg-gray-100">
                    <p class="oshi-muted mt-1 text-sm">
                        初期に割り振られた唯一のIDです。変更できません。
                    </p>
                </div>

                <div class="mb-5">
                    <label for="public_username" class="mb-1 block font-bold">
                        ユーザーネーム
                    </label>
                    <input
                        id="public_username"
                        type="text"
                        name="public_username"
                        value="{{ old('public_username', $user->public_username ?: $user->name) }}"
                        class="w-full"
                        required
                    >
                    @error('public_username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-5">
                    <label for="profile_icon" class="mb-1 block font-bold">
                        プロフィールアイコン
                    </label>

                    @if ($user->profile_icon_path)
                        <div class="mb-3">
                            <img
                                src="{{ asset('storage/' . $user->profile_icon_path) }}"
                                alt="{{ $user->displayName() }}"
                                style="width:80px;height:80px;border-radius:999px;object-fit:cover;"
                            >
                        </div>
                    @endif

                    <input id="profile_icon" type="file" name="profile_icon" accept="image/*">

                    <p class="mt-2 rounded bg-pink-50 p-3 text-sm">
                        著作権に配慮し、自作イラスト・ご自身で使用権を持つ画像等に限定してください。
                        公式画像、他者のイラスト、無断転載画像の使用はご遠慮ください。
                    </p>

                    @error('profile_icon')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-5">
                    <label for="profile_comment" class="mb-1 block font-bold">
                        一言文章
                    </label>
                    <textarea
                        id="profile_comment"
                        name="profile_comment"
                        rows="4"
                        class="w-full"
                        maxlength="500"
                        placeholder="例：作品情報を少しずつ整理しています。"
                    >{{ old('profile_comment', $user->profile_comment) }}</textarea>
                    @error('profile_comment')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <button type="submit" class="oshi-btn">
                        保存する
                    </button>
                </div>
            </form>

            <div class="rounded bg-gray-50 p-4">
                <h2 class="mb-2 font-bold">
                    スタッフ登用破棄申請
                </h2>
                <p class="oshi-muted mb-3 text-sm">
                    スタッフ登用の辞退・破棄を希望する場合は、フォームより申請してください。
                </p>
                <a href="{{ route('public.contact.create', ['category' => 'staff-withdrawal']) }}" class="oshi-btn oshi-btn-sub">
                    スタッフ登用破棄申請フォームへ
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
