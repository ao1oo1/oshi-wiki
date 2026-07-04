<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            タグ編集
        </h2>
    </x-slot>

    <div class="p-6">
        <div class="mx-auto max-w-4xl">
            @include('admin.partials.navigation')

            <div class="mb-6">
                <a
                    href="{{ route('admin.tags.index') }}"
                    style="display:inline-block;background:#4b5563;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    タグ一覧へ
                </a>
            </div>

            <div class="rounded bg-white p-6 shadow">
                @if ($errors->any())
                    <div class="mb-4 rounded bg-red-100 px-4 py-3 text-red-800">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.tags.update', $tag) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="name" class="mb-1 block font-medium">タグ名</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name', $tag->name) }}"
                            class="w-full rounded border-gray-300"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label for="type" class="mb-1 block font-medium">分類</label>
                        <input
                            id="type"
                            type="text"
                            name="type"
                            value="{{ old('type', $tag->type) }}"
                            class="w-full rounded border-gray-300"
                        >
                    </div>

                    <div class="mb-4">
                        <label for="description" class="mb-1 block font-medium">説明</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            class="w-full rounded border-gray-300"
                        >{{ old('description', $tag->description) }}</textarea>
                    </div>

                    @if (auth()->user()?->isSuperAdmin())
<div class="mb-6">
                        <label for="status" class="mb-1 block font-medium">状態</label>
                        <p class="mb-2 text-sm text-gray-600">
                            公開ページの絞り込みに使う場合は「公開」を選択してください。
                        </p>
                        <select
                            id="status"
                            name="status"
                            class="w-full rounded border-gray-300"
                        >
                            <option value="draft" @selected(old('status', $tag->status) === 'draft')>下書き</option>
                            <option value="published" @selected(old('status', $tag->status) === 'published')>公開</option>
                            <option value="private" @selected(old('status', $tag->status) === 'private')>非公開</option>
                        </select>
                    </div>
@else
    <div class="mb-6 rounded bg-pink-50 p-4 text-sm">
        情報入力スタッフによる登録・編集は、最高管理者への承認申請として保存されます。
    </div>
@endif

                    <button
                        type="submit"
                        style="display:inline-block;background:#2563eb;color:#ffffff;padding:10px 24px;border-radius:8px;font-weight:bold;border:none;cursor:pointer;"
                    >
                        保存する
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
