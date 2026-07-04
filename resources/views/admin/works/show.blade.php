<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            作品詳細
        </h2>
    </x-slot>

    <div class="p-6">
        <div class="mx-auto max-w-6xl">
            @include('admin.partials.navigation')


            <div class="mb-6 flex gap-3">
                <a
                    href="{{ route('admin.works.index') }}"
                    style="display:inline-block;background:#4b5563;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    作品一覧へ
                </a>

                <a
                    href="{{ route('admin.works.edit', $work) }}"
                    style="display:inline-block;background:#f59e0b;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    作品編集
                </a>

                <a
                    href="{{ route('admin.characters.create', ['work_id' => $work->id]) }}"
                    style="display:inline-block;background:#2563eb;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    キャラクター追加
                </a>

                <a
                    href="{{ route('admin.character-relationships.create', ['work_id' => $work->id]) }}"
                    style="display:inline-block;background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    関係性追加
                </a>

                <form
                    method="POST"
                    action="{{ route('admin.works.destroy', $work) }}"
                    onsubmit="return confirm('この作品を削除しますか？紐づくキャラクター・関係性も削除されます。');"
                    style="display:inline-block;"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        style="display:inline-block;background:#dc2626;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;border:none;cursor:pointer;"
                    >
                        この作品を削除
                    </button>
                </form>
            </div>

            @include('admin.partials.publish-help')

            <div class="mb-4 rounded bg-red-50 px-4 py-3 text-sm text-red-800">
                この作品を削除すると、紐づくキャラクターと関係性も削除されます。
            </div>

            <div class="mb-6 rounded bg-white p-6 shadow">
                <p class="mb-2 text-sm text-gray-500">
                    作品ID：{{ $work->id }}
                </p>

                <h3 class="text-2xl font-bold">
                    {{ $work->title }}
                </h3>

                @if ($work->title_kana)
                    <p class="mt-1 text-gray-600">
                        {{ $work->title_kana }}
                    </p>
                @endif

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <h4 class="mb-1 font-semibold">ジャンル</h4>
                        <p class="rounded bg-gray-50 p-3">
                            {{ $work->genre ?: '未設定' }}
                        </p>
                    </div>

                    <div>
                        <h4 class="mb-1 font-semibold">原作媒体</h4>
                        <p class="rounded bg-gray-50 p-3">
                            {{ $work->original_media ?: '未設定' }}
                        </p>
                    </div>

                    <div>
                        <h4 class="mb-1 font-semibold">状態</h4>
                        <div class="rounded bg-gray-50 p-3">
                            @include('admin.partials.status-badge', ['status' => $work->status])
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <h4 class="mb-1 font-semibold">説明</h4>
                    <div class="whitespace-pre-wrap rounded bg-gray-50 p-4">{{ $work->description ?: '未設定' }}</div>
                </div>


                <div class="mt-5">
                    <h4 class="mb-2 font-semibold">作品タグ</h4>

                    @if ($work->tags->count())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($work->tags as $tag)
                                <span class="rounded bg-gray-100 px-3 py-1 text-sm">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600">
                            タグは未設定です。
                        </p>
                    @endif
                </div>

            </div>

            <div class="mb-6 rounded bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold">
                    登録キャラクター
                </h3>

                @if ($work->characters->count())
                    <div class="oshi-table-wrap">
                        <table class="oshi-table">
                            <thead>
                                <tr class="border-b bg-gray-50">
                                    <th class="px-4 py-2 text-left">名前</th>
                                    <th class="px-4 py-2 text-left">年齢</th>
                                    <th class="px-4 py-2 text-left">所属</th>
                                    <th class="px-4 py-2 text-left">一人称</th>
                                    <th class="px-4 py-2 text-left">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($work->characters as $character)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">
                                            {{ $character->name }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $character->age ?: '未設定' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $character->affiliation ?: '未設定' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $character->first_person ?: '未設定' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <a
                                                href="{{ route('admin.characters.show', $character) }}"
                                                style="display:inline-block;background:#16a34a;color:#ffffff;padding:6px 12px;border-radius:6px;text-decoration:none;"
                                            >
                                                詳細
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-600">
                        この作品にはまだキャラクターが登録されていません。
                    </p>
                @endif
            </div>

            <div class="rounded bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold">
                    キャラクター関係性
                </h3>

                @if ($work->characterRelationships->count())
                    <div class="oshi-table-wrap">
                        <table class="oshi-table">
                            <thead>
                                <tr class="border-b bg-gray-50">
                                    <th class="px-4 py-2 text-left">キャラクター</th>
                                    <th class="px-4 py-2 text-left">相手</th>
                                    <th class="px-4 py-2 text-left">呼ばれ方</th>
                                    <th class="px-4 py-2 text-left">関係性</th>
                                    <th class="px-4 py-2 text-left">印象・気持ち等</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($work->characterRelationships as $relation)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">
                                            {{ $relation->fromCharacter?->name }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $relation->toCharacter?->name }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $relation->called_name ?: '未設定' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $relation->relationship ?: '未設定' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $relation->impression ?: '未設定' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-600">
                        この作品にはまだ関係性が登録されていません。
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
